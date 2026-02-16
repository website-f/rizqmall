<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use ZipArchive;

class AdminProductImportController extends Controller
{
    public function show()
    {
        $stores = Store::orderBy('name')->get();

        return view('admin.products-import', compact('stores'));
    }

    /**
     * Remove all products from selected stores.
     */
    public function removeProducts(Request $request)
    {
        $request->validate([
            'stores' => 'required|array|min:1',
            'stores.*' => 'exists:stores,id',
        ]);

        $stores = Store::whereIn('id', $request->stores)->get();
        $totalDeleted = 0;

        DB::beginTransaction();
        try {
            foreach ($stores as $store) {
                // Count includes soft-deleted products
                $count = Product::withTrashed()->where('store_id', $store->id)->count();
                // Force delete to actually remove from DB (not just soft delete)
                Product::withTrashed()->where('store_id', $store->id)->forceDelete();
                $totalDeleted += $count;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Remove products failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to remove products: ' . $e->getMessage());
        }

        $storeNames = $stores->pluck('name')->implode(', ');
        return back()->with('success', "Removed {$totalDeleted} products from: {$storeNames}");
    }

    public function import(Request $request)
    {
        $request->validate([
            'stores' => 'required|array|min:1',
            'stores.*' => 'exists:stores,id',
            'file' => 'required|file|max:10240',
        ]);

        $stores = Store::whereIn('id', $request->stores)->get();
        if ($stores->isEmpty()) {
            return back()->with('error', 'Please select at least one store.');
        }

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['xlsx', 'csv', 'txt'])) {
            return back()->with('error', 'Unsupported file type. Upload .xlsx or .csv.');
        }

        try {
            $rows = $extension === 'xlsx'
                ? $this->readXlsx($file->getRealPath())
                : $this->readCsv($file->getRealPath());
        } catch (\Throwable $e) {
            Log::error('Import read failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to read file. Please check the format.');
        }

        if (empty($rows)) {
            return back()->with('error', 'No rows found in the file.');
        }

        // Smart header detection - find the actual header row (handles multi-row headers)
        $headerResult = $this->detectHeaderRow($rows);
        $headerRowIndex = $headerResult['index'];
        $lastHeaderRow = $headerResult['last_header_row'];
        $headers = $headerResult['merged_headers'];
        $normalizedHeaders = array_map([$this, 'normalizeHeader'], $headers);

        // Smart column mapping with fuzzy/keyword matching
        $map = $this->buildSmartColumnMap($headers, $normalizedHeaders);

        // If still no name column, try to find any column with text that looks like product names
        if (empty($map['name'])) {
            $map = $this->fallbackColumnDetection($rows, $lastHeaderRow, $headers, $map);
        }

        // Log detection results for debugging
        Log::info('Import column detection', [
            'header_row_index' => $headerRowIndex,
            'last_header_row' => $lastHeaderRow,
            'headers' => $headers,
            'map' => $map,
        ]);

        // Check if we have minimum required columns
        if (empty($map['name'])) {
            return back()->with('error', 'Could not detect a product name column. Detected headers: ' . implode(', ', array_filter($headers)))
                ->with('import_headers', $headers)
                ->with('detected_map', $map);
        }

        // If no price column found, still allow import with a default price of 0 (user can update later)
        $hasPrice = !empty($map['regular_price']) || !empty($map['sale_price']);

        // Data rows start after the LAST header row
        $dataRows = array_slice($rows, $lastHeaderRow + 1);

        $created = 0;
        $skipped = 0;
        $errors = [];
        $detectedColumns = $this->describeDetectedMap($map, $headers);
        $usedSlugs = []; // Track slugs within this import batch to avoid duplicates

        DB::beginTransaction();
        try {
            foreach ($dataRows as $rowIndex => $row) {
                $rowNumber = $headerRowIndex + $rowIndex + 2; // 1-based

                // Smart: skip empty rows
                if ($this->isEmptyRow($row)) {
                    continue;
                }

                // Smart: skip section/category header rows (e.g. "A. SEDIA DIMINUM / READY TO DRINK")
                if ($this->isSectionRow($row, $map)) {
                    continue;
                }

                $rowData = $this->rowToAssoc($row, $normalizedHeaders);

                // Build the product name smartly (can combine multiple columns + size for uniqueness)
                $name = $this->buildProductName($rowData, $map, $headers);
                if (!$name) {
                    $skipped++;
                    $errors[] = "Row {$rowNumber}: Missing product name.";
                    continue;
                }

                // If size info exists, append to name to differentiate variants (e.g. "MINERAL WATER" 1500ML vs 600ML)
                $sizeVal = null;
                if (isset($map['size'])) {
                    $sizeKey = array_keys($rowData)[$map['size']] ?? null;
                    $sizeVal = $sizeKey !== null ? trim((string) ($rowData[$sizeKey] ?? '')) : null;
                    if ($sizeVal && strtolower($sizeVal) !== 'n/a' && $sizeVal !== '-') {
                        $testSlug = Str::slug($name);
                        if (isset($usedSlugs[$testSlug]) || Product::where('slug', $testSlug)->exists()) {
                            $name = $name . ' (' . $sizeVal . ')';
                        }
                    }
                }

                // Smart price detection
                $regularPrice = $this->toFloat($this->valueFromRow($rowData, $map, 'regular_price'));
                $salePrice = $this->toFloat($this->valueFromRow($rowData, $map, 'sale_price'));
                $costPrice = $this->toFloat($this->valueFromRow($rowData, $map, 'cost_price'));

                // If no regular price but sale price exists
                if ($regularPrice <= 0 && $salePrice > 0) {
                    $regularPrice = $salePrice;
                }

                // If no price at all and we have cost price, use that
                if ($regularPrice <= 0 && $costPrice > 0) {
                    $regularPrice = $costPrice;
                }

                // Skip rows with no price at all (likely section headers that slipped through)
                if ($regularPrice <= 0 && $hasPrice) {
                    $skipped++;
                    $errors[] = "Row {$rowNumber}: No valid price found for '{$name}'.";
                    continue;
                }

                // If no price columns exist at all, set a default
                if ($regularPrice <= 0 && !$hasPrice) {
                    $regularPrice = 0;
                }

                $description = $this->valueFromRow($rowData, $map, 'description')
                    ?: $this->valueFromRow($rowData, $map, 'short_description')
                    ?: $name;
                $shortDescription = $this->valueFromRow($rowData, $map, 'short_description');

                // Build extra description from unmapped informational columns
                $extraInfo = $this->buildExtraDescription($rowData, $map, $headers);
                if ($extraInfo && $description === $name) {
                    $description = $name . ' - ' . $extraInfo;
                }

                $type = strtolower($this->valueFromRow($rowData, $map, 'type') ?? 'product');
                if (!in_array($type, ['product', 'service', 'pharmacy'])) {
                    $type = 'product';
                }

                $productType = strtolower($this->valueFromRow($rowData, $map, 'product_type') ?? 'simple');
                if (!in_array($productType, ['simple', 'variable'])) {
                    $productType = 'simple';
                }

                $status = strtolower($this->valueFromRow($rowData, $map, 'status') ?? 'published');
                if (!in_array($status, ['draft', 'published', 'archived'])) {
                    $status = 'published';
                }

                $categoryId = $this->resolveCategory($rowData, $map);

                foreach ($stores as $store) {
                    $slug = $this->uniqueSlug($name, $store->id, $usedSlugs);
                    $usedSlugs[$slug] = true;
                    $sku = $this->uniqueSku($this->valueFromRow($rowData, $map, 'sku'), $store->id);

                    Product::create([
                        'store_id' => $store->id,
                        'product_category_id' => $categoryId,
                        'type' => $type,
                        'product_type' => $productType,
                        'name' => $name,
                        'slug' => $slug,
                        'short_description' => $shortDescription,
                        'description' => $description,
                        'regular_price' => $regularPrice,
                        'sale_price' => $salePrice > 0 ? $salePrice : null,
                        'cost_price' => $costPrice > 0 ? $costPrice : null,
                        'sku' => $sku,
                        'stock_quantity' => $this->toInt($this->valueFromRow($rowData, $map, 'stock_quantity')) ?? 0,
                        'low_stock_threshold' => $this->toInt($this->valueFromRow($rowData, $map, 'low_stock_threshold')) ?? 5,
                        'track_inventory' => $this->toBool($this->valueFromRow($rowData, $map, 'track_inventory'), true),
                        'allow_backorder' => $this->toBool($this->valueFromRow($rowData, $map, 'allow_backorder'), false),
                        'allow_bulk_order' => $this->toBool($this->valueFromRow($rowData, $map, 'allow_bulk_order'), false),
                        'minimum_order_quantity' => $this->toInt($this->valueFromRow($rowData, $map, 'minimum_order_quantity')) ?? 1,
                        'bulk_price' => $this->toFloat($this->valueFromRow($rowData, $map, 'bulk_price')) ?: null,
                        'bulk_quantity_threshold' => $this->toInt($this->valueFromRow($rowData, $map, 'bulk_quantity_threshold')) ?? null,
                        'is_preorder' => $this->toBool($this->valueFromRow($rowData, $map, 'is_preorder'), false),
                        'preorder_release_date' => $this->parseDate($this->valueFromRow($rowData, $map, 'preorder_release_date')),
                        'preorder_limit' => $this->toInt($this->valueFromRow($rowData, $map, 'preorder_limit')),
                        'lead_time_days' => $this->toInt($this->valueFromRow($rowData, $map, 'lead_time_days')),
                        'booking_fee' => $this->toFloat($this->valueFromRow($rowData, $map, 'booking_fee')) ?: null,
                        'package_price' => $this->toFloat($this->valueFromRow($rowData, $map, 'package_price')) ?: null,
                        'package_name' => $this->valueFromRow($rowData, $map, 'package_name'),
                        'package_details' => $this->valueFromRow($rowData, $map, 'package_details'),
                        'service_duration' => $this->toInt($this->valueFromRow($rowData, $map, 'service_duration')),
                        'service_availability' => $this->valueFromRow($rowData, $map, 'service_availability'),
                        'service_days' => $this->parseDays($this->valueFromRow($rowData, $map, 'service_days')),
                        'service_start_time' => $this->valueFromRow($rowData, $map, 'service_start_time'),
                        'service_end_time' => $this->valueFromRow($rowData, $map, 'service_end_time'),
                        'weight' => $this->toFloat($this->valueFromRow($rowData, $map, 'weight')) ?: null,
                        'length' => $this->toFloat($this->valueFromRow($rowData, $map, 'length')) ?: null,
                        'width' => $this->toFloat($this->valueFromRow($rowData, $map, 'width')) ?: null,
                        'height' => $this->toFloat($this->valueFromRow($rowData, $map, 'height')) ?: null,
                        'status' => $status,
                    ]);

                    $created++;
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Import failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }

        return back()->with('success', "Import completed. Created {$created} products. Skipped {$skipped} rows.")
            ->with('import_errors', $errors)
            ->with('detected_columns', $detectedColumns);
    }

    /**
     * Detect the actual header row from the first N rows.
     * Handles multi-row headers (e.g. Excel merged cells spanning 2 rows).
     * Returns merged headers combining all header rows.
     */
    private function detectHeaderRow(array $rows): array
    {
        $headerKeywords = [
            'no', 'name', 'product', 'item', 'price', 'harga', 'sku', 'barcode',
            'code', 'description', 'desc', 'brand', 'category', 'stock', 'qty',
            'quantity', 'size', 'flavour', 'flavor', 'type', 'status', 'weight',
            'colour', 'color', 'variant', 'unit', 'cost', 'sale', 'discount',
            'trade', 'retail', 'wholesale', 'distributor', 'pengedar', 'peruncit',
            'pengguna', 'consumer', 'ctn', 'carton', 'pack', 'uom', 'rsp',
        ];

        $scanLimit = min(count($rows), 15);
        $rowScores = [];

        for ($i = 0; $i < $scanLimit; $i++) {
            $row = $rows[$i];
            $nonEmptyCells = 0;
            $keywordHits = 0;
            $hasNumericOnly = 0;

            foreach ($row as $cell) {
                $val = strtolower(trim((string) $cell));
                if ($val !== '') {
                    $nonEmptyCells++;
                    foreach ($headerKeywords as $keyword) {
                        if (str_contains($val, $keyword)) {
                            $keywordHits++;
                            break;
                        }
                    }
                    if (is_numeric($val)) {
                        $hasNumericOnly++;
                    }
                }
            }

            $score = ($keywordHits * 10) + ($nonEmptyCells * 2) - ($hasNumericOnly * 3);
            $rowScores[$i] = [
                'score' => $score,
                'nonEmpty' => $nonEmptyCells,
                'keywords' => $keywordHits,
                'numeric' => $hasNumericOnly,
            ];
        }

        // Find the best header row
        $maxScore = -1;
        $bestIndex = 0;
        foreach ($rowScores as $i => $info) {
            if ($info['nonEmpty'] >= 3 && $info['score'] > $maxScore) {
                $maxScore = $info['score'];
                $bestIndex = $i;
            }
        }

        // Check if the next row(s) are also part of the header (multi-row header)
        // A row is part of the header if it has keyword hits but fewer non-empty cells,
        // and contains text that fills in gaps from the main header row
        $lastHeaderRow = $bestIndex;
        $mergedHeaders = $rows[$bestIndex];
        $numCols = count($mergedHeaders);

        for ($nextRow = $bestIndex + 1; $nextRow < min($bestIndex + 3, count($rows)); $nextRow++) {
            if (!isset($rows[$nextRow])) break;

            $nextRowData = $rows[$nextRow];
            $nextInfo = $rowScores[$nextRow] ?? null;

            // Check: does the next row have header-like keywords?
            $hasKeywords = $nextInfo && $nextInfo['keywords'] > 0;
            // Check: does the next row fill in empty columns from the current header?
            $fillsGaps = false;
            $newKeywords = 0;
            for ($c = 0; $c < min($numCols, count($nextRowData)); $c++) {
                $currentVal = trim((string) ($mergedHeaders[$c] ?? ''));
                $nextVal = trim((string) ($nextRowData[$c] ?? ''));
                if ($nextVal !== '' && ($currentVal === '' || strlen($currentVal) <= 2)) {
                    // This cell fills a gap or extends a short label (like "C" -> "C PENGEDAR / DISTRIBUTOR")
                    $fillsGaps = true;
                    $lowerNext = strtolower($nextVal);
                    foreach ($headerKeywords as $kw) {
                        if (str_contains($lowerNext, $kw)) {
                            $newKeywords++;
                            break;
                        }
                    }
                }
            }

            // It's a continuation header row if it has keyword hits and fills gaps
            if (($hasKeywords || $newKeywords > 0) && $fillsGaps) {
                // Merge: for each column, combine the values
                for ($c = 0; $c < min($numCols, count($nextRowData)); $c++) {
                    $currentVal = trim((string) ($mergedHeaders[$c] ?? ''));
                    $nextVal = trim((string) ($nextRowData[$c] ?? ''));

                    if ($currentVal === '' && $nextVal !== '') {
                        $mergedHeaders[$c] = $nextVal;
                    } elseif ($currentVal !== '' && $nextVal !== '' && strtolower($currentVal) !== strtolower($nextVal)) {
                        // Combine short label with full name (e.g. "C" + "PENGEDAR / DISTRIBUTOR" = "C PENGEDAR / DISTRIBUTOR")
                        if (strlen($currentVal) <= 3) {
                            $mergedHeaders[$c] = $currentVal . ' ' . $nextVal;
                        }
                        // If both are substantial, keep the one with more keywords
                    }
                }
                $lastHeaderRow = $nextRow;
            } else {
                break;
            }
        }

        return [
            'index' => $bestIndex,
            'last_header_row' => $lastHeaderRow,
            'score' => $maxScore,
            'merged_headers' => $mergedHeaders,
        ];
    }

    /**
     * Smart column mapping using keyword-based fuzzy matching.
     * Instead of requiring exact header names, we look for keywords within the header text.
     */
    private function buildSmartColumnMap(array $originalHeaders, array $normalizedHeaders): array
    {
        // First try exact alias matching (existing logic)
        $map = $this->buildColumnMap($normalizedHeaders);

        // If we already found name and price, return
        if (!empty($map['name']) && (!empty($map['regular_price']) || !empty($map['sale_price']))) {
            return $map;
        }

        // Keyword-based fuzzy matching on the ORIGINAL headers (before stripping special chars)
        $lowerHeaders = array_map(function ($h) {
            return strtolower(trim((string) $h));
        }, $originalHeaders);

        // Smart name detection
        if (empty($map['name'])) {
            foreach ($lowerHeaders as $i => $header) {
                // "product description", "product name", "product desc / sku", "nama produk", "item name"
                if (
                    (str_contains($header, 'product') && (str_contains($header, 'name') || str_contains($header, 'desc') || str_contains($header, 'sku'))) ||
                    (str_contains($header, 'nama') && str_contains($header, 'produk')) ||
                    (str_contains($header, 'item') && (str_contains($header, 'name') || str_contains($header, 'desc')))
                ) {
                    $map['name'] = $i;
                    break;
                }
            }
        }

        // If still no name, try single keyword matches
        if (empty($map['name'])) {
            $nameKeywords = ['product', 'produk', 'item', 'nama', 'menu', 'food', 'makanan'];
            foreach ($lowerHeaders as $i => $header) {
                // Skip headers that are clearly something else
                if (str_contains($header, 'price') || str_contains($header, 'harga') ||
                    str_contains($header, 'size') || str_contains($header, 'qty') ||
                    str_contains($header, 'stock') || str_contains($header, 'no') === ($header === 'no') ||
                    str_contains($header, 'barcode') || str_contains($header, 'brand')) {
                    continue;
                }
                foreach ($nameKeywords as $keyword) {
                    if (str_contains($header, $keyword)) {
                        $map['name'] = $i;
                        break 2;
                    }
                }
            }
        }

        // Smart price detection - look for price-related keywords
        $priceColumns = [];
        foreach ($lowerHeaders as $i => $header) {
            if (
                str_contains($header, 'price') || str_contains($header, 'harga') ||
                str_contains($header, 'pengguna') || str_contains($header, 'consumer') ||
                str_contains($header, 'retail') || str_contains($header, 'bsp') ||
                str_contains($header, 'peruncit') || str_contains($header, 'trade') ||
                str_contains($header, 'pengedar') || str_contains($header, 'distributor') ||
                str_contains($header, 'wholesale') || str_contains($header, 'cost') ||
                str_contains($header, 'kos') || str_contains($header, 'srp') ||
                str_contains($header, 'rrp') || str_contains($header, 'selling')
            ) {
                $priceColumns[] = ['index' => $i, 'header' => $header];
            }
        }

        // Assign price columns intelligently
        if (!empty($priceColumns)) {
            if (count($priceColumns) === 1) {
                // Single price column = regular price
                if (empty($map['regular_price'])) {
                    $map['regular_price'] = $priceColumns[0]['index'];
                }
            } elseif (count($priceColumns) >= 2) {
                // Multiple price columns: last one is usually consumer/retail price (regular_price)
                // First one is usually cost/distributor price
                foreach ($priceColumns as $pc) {
                    $h = $pc['header'];
                    if (str_contains($h, 'pengguna') || str_contains($h, 'consumer') ||
                        str_contains($h, 'retail') || str_contains($h, 'bsp') ||
                        str_contains($h, 'selling') || str_contains($h, 'srp') ||
                        str_contains($h, 'rrp')) {
                        $map['regular_price'] = $pc['index'];
                    } elseif (str_contains($h, 'cost') || str_contains($h, 'kos') ||
                        str_contains($h, 'pengedar') || str_contains($h, 'distributor') ||
                        str_contains($h, 'wholesale')) {
                        $map['cost_price'] = $pc['index'];
                    } elseif (str_contains($h, 'trade') || str_contains($h, 'peruncit')) {
                        // Trade price - use as sale price if available
                        if (empty($map['sale_price'])) {
                            $map['sale_price'] = $pc['index'];
                        }
                    }
                }

                // If we still don't have regular_price, use the last price column
                if (empty($map['regular_price'])) {
                    $lastPrice = end($priceColumns);
                    $map['regular_price'] = $lastPrice['index'];
                }

                // If we don't have cost_price, use the first price column (if different from regular)
                if (empty($map['cost_price']) && $priceColumns[0]['index'] !== ($map['regular_price'] ?? -1)) {
                    $map['cost_price'] = $priceColumns[0]['index'];
                }
            }
        }

        // Smart SKU/barcode detection
        if (empty($map['sku'])) {
            foreach ($lowerHeaders as $i => $header) {
                if (str_contains($header, 'barcode') || str_contains($header, 'kod') ||
                    str_contains($header, 'article') || str_contains($header, 'upc') ||
                    str_contains($header, 'ean') || str_contains($header, 'isbn')) {
                    $map['sku'] = $i;
                    break;
                }
            }
        }

        // Smart brand detection
        if (empty($map['brand'])) {
            foreach ($lowerHeaders as $i => $header) {
                if (str_contains($header, 'brand') || str_contains($header, 'jenama')) {
                    $map['brand'] = $i;
                    break;
                }
            }
        }

        // Smart flavour/variant detection (will be used to enhance product name)
        if (empty($map['flavour'])) {
            foreach ($lowerHeaders as $i => $header) {
                if (str_contains($header, 'flavour') || str_contains($header, 'flavor') ||
                    str_contains($header, 'perisa') || str_contains($header, 'variant') ||
                    str_contains($header, 'varian') || str_contains($header, 'rasa')) {
                    $map['flavour'] = $i;
                    break;
                }
            }
        }

        // Smart size detection
        if (empty($map['size'])) {
            foreach ($lowerHeaders as $i => $header) {
                if ((str_contains($header, 'size') || str_contains($header, 'saiz')) &&
                    !str_contains($header, 'ctn') && !str_contains($header, 'carton')) {
                    $map['size'] = $i;
                    break;
                }
            }
        }

        // Note: CTN SIZE / Carton size is NOT stock quantity - it's packaging info, skip mapping it

        // Smart category detection
        if (empty($map['product_category'])) {
            foreach ($lowerHeaders as $i => $header) {
                if (str_contains($header, 'kategori') || str_contains($header, 'jenis') ||
                    (str_contains($header, 'category') && !isset($map['product_category']))) {
                    $map['product_category'] = $i;
                    break;
                }
            }
        }

        return $map;
    }

    /**
     * Fallback: try to detect columns by scanning actual data values.
     */
    private function fallbackColumnDetection(array $rows, int $headerRowIndex, array $headers, array $map): array
    {
        $dataRows = array_slice($rows, $headerRowIndex + 1, 5); // Sample first 5 data rows

        if (empty($dataRows)) {
            return $map;
        }

        // For each column, analyze the data to guess what it contains
        $numCols = count($headers);
        for ($col = 0; $col < $numCols; $col++) {
            $values = [];
            foreach ($dataRows as $row) {
                $val = trim((string) ($row[$col] ?? ''));
                if ($val !== '') {
                    $values[] = $val;
                }
            }

            if (empty($values)) {
                continue;
            }

            // Check if this column has long text strings (likely product names)
            $avgLen = array_sum(array_map('strlen', $values)) / count($values);
            $allText = true;
            $allNumeric = true;
            foreach ($values as $v) {
                if (is_numeric(str_replace([',', '.', 'RM', 'rm', '$'], '', $v))) {
                    $allText = false;
                } else {
                    $allNumeric = false;
                }
            }

            // If no name column found and this column has long text (avg > 10 chars) and is mostly text
            if (empty($map['name']) && $avgLen > 10 && !$allNumeric) {
                $map['name'] = $col;
            }

            // If no price column found and this column is all numeric with reasonable values
            if (empty($map['regular_price']) && $allNumeric && !empty($values)) {
                $numValues = array_map(function ($v) {
                    return (float) str_replace([',', 'RM', 'rm', '$'], '', $v);
                }, $values);
                $avg = array_sum($numValues) / count($numValues);
                // Price-like: between 0.01 and 100000
                if ($avg > 0.01 && $avg < 100000) {
                    $map['regular_price'] = $col;
                }
            }
        }

        return $map;
    }

    /**
     * Build product name smartly from multiple columns.
     * Combines product name + flavour/variant if available.
     */
    private function buildProductName(array $rowData, array $map, array $headers): ?string
    {
        $name = $this->valueFromRow($rowData, $map, 'name');
        if (!$name) {
            return null;
        }

        // Append flavour/variant if exists and is different from "N/A"
        $flavour = null;
        if (isset($map['flavour'])) {
            $key = array_keys($rowData)[$map['flavour']] ?? null;
            $flavour = $key !== null ? ($rowData[$key] ?? null) : null;
            if (is_string($flavour)) {
                $flavour = trim($flavour);
            }
            // Clean up flavour: remove tags like "| TOP 50", "| New" first
            if ($flavour) {
                $flavour = preg_replace('/\s*\|\s*(top\s*\d+|new|baru)\s*/i', '', $flavour);
                $flavour = trim($flavour);
            }
            // Skip if empty, "N/A", or same as name
            if (!$flavour || strtolower($flavour) === 'n/a' || $flavour === '-' || strtolower($flavour) === strtolower($name)) {
                $flavour = null;
            }
        }

        if ($flavour) {
            $name = $name . ' - ' . $flavour;
        }

        return $name;
    }

    /**
     * Build extra description from unmapped informational columns (size, brand, etc.)
     */
    private function buildExtraDescription(array $rowData, array $map, array $headers): ?string
    {
        $parts = [];

        // Add brand if available
        if (isset($map['brand'])) {
            $brand = null;
            $key = array_keys($rowData)[$map['brand']] ?? null;
            $brand = $key !== null ? ($rowData[$key] ?? null) : null;
            if ($brand && trim($brand) !== '') {
                $parts[] = 'Brand: ' . trim($brand);
            }
        }

        // Add size if available
        if (isset($map['size'])) {
            $size = null;
            $key = array_keys($rowData)[$map['size']] ?? null;
            $size = $key !== null ? ($rowData[$key] ?? null) : null;
            if ($size && trim($size) !== '' && strtolower(trim($size)) !== 'n/a') {
                $parts[] = 'Size: ' . trim($size);
            }
        }

        return !empty($parts) ? implode('. ', $parts) : null;
    }

    /**
     * Check if a row is a section header (e.g. "A. SEDIA DIMINUM / READY TO DRINK")
     */
    private function isSectionRow(array $row, array $map): bool
    {
        // Count non-empty cells
        $nonEmpty = 0;
        $firstNonEmpty = null;
        foreach ($row as $i => $cell) {
            $val = trim((string) $cell);
            if ($val !== '') {
                $nonEmpty++;
                if ($firstNonEmpty === null) {
                    $firstNonEmpty = $val;
                }
            }
        }

        // If very few cells have data (1-2), it's likely a section header
        if ($nonEmpty <= 2 && $firstNonEmpty) {
            // Check patterns like "A.", "B.", "C.", "1.", "SECTION:" etc.
            if (preg_match('/^[A-Z]\.\s+/i', $firstNonEmpty) ||
                preg_match('/^[IVX]+\.\s+/i', $firstNonEmpty) ||
                preg_match('/^\d+\.\s+[A-Z\s\/]+$/i', $firstNonEmpty)) {
                return true;
            }
            // All uppercase with no numbers (likely a title/section)
            if (strtoupper($firstNonEmpty) === $firstNonEmpty && !preg_match('/\d/', $firstNonEmpty) && strlen($firstNonEmpty) > 5) {
                return true;
            }
        }

        // If we have a price column mapped, check if the price cell is empty (section rows usually have no price)
        if (isset($map['regular_price'])) {
            $priceVal = trim((string) ($row[$map['regular_price']] ?? ''));
            $nameVal = trim((string) ($row[$map['name'] ?? 0] ?? ''));

            // Has a name-like value but no price = likely section header
            if ($nameVal !== '' && $priceVal === '' && $nonEmpty <= 3) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a row is completely empty.
     */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }
        return true;
    }

    /**
     * Describe what columns were auto-detected for feedback to user.
     */
    private function describeDetectedMap(array $map, array $headers): array
    {
        $descriptions = [];
        $fieldLabels = [
            'name' => 'Product Name',
            'description' => 'Description',
            'short_description' => 'Short Description',
            'regular_price' => 'Regular Price',
            'sale_price' => 'Sale Price',
            'cost_price' => 'Cost Price',
            'sku' => 'SKU / Barcode',
            'stock_quantity' => 'Stock Quantity',
            'product_category' => 'Category',
            'brand' => 'Brand',
            'flavour' => 'Flavour / Variant',
            'size' => 'Size',
            'type' => 'Type',
            'status' => 'Status',
            'weight' => 'Weight',
        ];

        foreach ($map as $field => $colIndex) {
            $label = $fieldLabels[$field] ?? $field;
            $headerName = $headers[$colIndex] ?? "Column {$colIndex}";
            $descriptions[] = "{$label} ← \"{$headerName}\"";
        }

        return $descriptions;
    }

    // ────────────────────────────────────────────────
    // Original helper methods (kept intact)
    // ────────────────────────────────────────────────

    private function buildColumnMap(array $headers): array
    {
        $aliases = [
            'name' => ['name', 'productname', 'product', 'item', 'itemname', 'title'],
            'description' => ['description', 'desc', 'productdescription', 'details', 'longdescription'],
            'short_description' => ['shortdescription', 'shortdesc', 'summary', 'excerpt'],
            'regular_price' => ['price', 'regularprice', 'regular_price', 'sellingprice', 'baseprice', 'normalprice'],
            'sale_price' => ['saleprice', 'sale_price', 'discountprice', 'promo', 'offerprice'],
            'cost_price' => ['costprice', 'cost_price', 'buyprice'],
            'sku' => ['sku', 'productsku', 'itemsku', 'code'],
            'stock_quantity' => ['stock', 'quantity', 'qty', 'stockquantity', 'stockqty', 'inventory'],
            'low_stock_threshold' => ['lowstockthreshold', 'lowstock', 'reorderlevel', 'low_stock'],
            'type' => ['type', 'producttype', 'itemtype'],
            'product_type' => ['product_type', 'producttype', 'variation', 'simplevariable'],
            'status' => ['status', 'publishstatus'],
            'product_category' => ['category', 'productcategory', 'categoryname', 'product_category', 'category_name'],
            'product_category_id' => ['categoryid', 'productcategoryid'],
            'allow_bulk_order' => ['allow_bulk_order', 'bulk', 'bulkorder', 'bulk_order', 'allowbulk'],
            'minimum_order_quantity' => ['minimum_order_quantity', 'minimumorderquantity', 'minqty', 'minquantity'],
            'bulk_price' => ['bulk_price', 'bulkprice', 'bulkunitprice'],
            'bulk_quantity_threshold' => ['bulk_quantity_threshold', 'bulkquantitythreshold', 'bulkthreshold', 'bulkqty'],
            'is_preorder' => ['is_preorder', 'preorder', 'pre_order'],
            'preorder_release_date' => ['preorder_release_date', 'release_date', 'preorderdate', 'preorder_release'],
            'preorder_limit' => ['preorder_limit', 'preorderlimit', 'limit', 'preorderqty'],
            'lead_time_days' => ['lead_time_days', 'leadtime', 'lead_time'],
            'booking_fee' => ['booking_fee', 'bookingfee', 'deposit', 'reservationfee'],
            'package_price' => ['package_price', 'packageprice'],
            'package_name' => ['package_name', 'packagename'],
            'package_details' => ['package_details', 'packagedetails'],
            'service_duration' => ['service_duration', 'duration', 'service_time'],
            'service_availability' => ['service_availability', 'availability'],
            'service_days' => ['service_days', 'days', 'available_days'],
            'service_start_time' => ['service_start_time', 'start_time', 'service_start'],
            'service_end_time' => ['service_end_time', 'end_time', 'service_end'],
            'weight' => ['weight', 'kg'],
            'length' => ['length', 'len'],
            'width' => ['width'],
            'height' => ['height'],
            'track_inventory' => ['track_inventory', 'trackinventory'],
            'allow_backorder' => ['allow_backorder', 'backorder'],
        ];

        $map = [];
        foreach ($aliases as $field => $names) {
            foreach ($headers as $index => $header) {
                if (in_array($header, $names, true)) {
                    $map[$field] = $index;
                    break;
                }
            }
        }

        return $map;
    }

    private function resolveCategory(array $rowData, array $map): ?int
    {
        $categoryId = $this->valueFromRow($rowData, $map, 'product_category_id');
        if ($categoryId) {
            return (int) $categoryId;
        }

        $categoryName = $this->valueFromRow($rowData, $map, 'product_category');
        if (!$categoryName) {
            return null;
        }

        $slug = Str::slug($categoryName);
        $category = ProductCategory::firstOrCreate(
            ['slug' => $slug],
            ['name' => $categoryName, 'is_active' => true]
        );

        return $category->id;
    }

    private function normalizeHeader($value): string
    {
        $value = strtolower(trim((string) $value));
        $value = preg_replace('/[^a-z0-9]+/', '', $value);
        return $value ?? '';
    }

    private function rowToAssoc(array $row, array $headers): array
    {
        $data = [];
        foreach ($headers as $index => $header) {
            $data[$header] = $row[$index] ?? null;
        }
        return $data;
    }

    private function valueFromRow(array $rowData, array $map, string $field)
    {
        if (!isset($map[$field])) {
            return null;
        }
        $key = array_keys($rowData)[$map[$field]] ?? null;
        if ($key === null) {
            return null;
        }
        $value = $rowData[$key] ?? null;
        if (is_string($value)) {
            $value = trim($value);
        }
        return $value === '' ? null : $value;
    }

    private function toBool($value, bool $default = false): bool
    {
        if ($value === null) {
            return $default;
        }
        $value = strtolower((string) $value);
        return in_array($value, ['1', 'true', 'yes', 'y'], true);
    }

    private function toFloat($value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }
        return (float) str_replace([',', 'RM', 'rm', '$'], '', (string) $value);
    }

    private function toInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        return (int) preg_replace('/[^0-9-]/', '', (string) $value);
    }

    private function parseDate($value): ?string
    {
        if (!$value) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                $base = Carbon::create(1899, 12, 30);
                return $base->addDays((int) $value)->toDateString();
            } catch (\Throwable $e) {
                return null;
            }
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parseDays($value): ?array
    {
        if (!$value) {
            return null;
        }
        $value = is_array($value) ? $value : explode(',', (string) $value);
        $days = array_map(function ($day) {
            return trim(strtolower($day));
        }, $value);
        $days = array_values(array_filter($days));
        return $days ?: null;
    }

    private function uniqueSlug(string $name, int $storeId, array $usedSlugs = []): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'product';
        }
        $slug = $base;
        $counter = 2;
        // Use withTrashed() to also check soft-deleted products (DB unique index includes them)
        while (isset($usedSlugs[$slug]) || Product::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }
        return $slug;
    }

    private function uniqueSku(?string $sku, int $storeId): ?string
    {
        $sku = $sku ? trim($sku) : null;
        if (!$sku) {
            return null;
        }
        // Convert scientific notation barcodes (e.g. "9.555031900023E+12") to full number string
        if (is_numeric($sku) && stripos($sku, 'E') !== false) {
            $sku = number_format((float) $sku, 0, '', '');
        }
        if (!Product::withTrashed()->where('sku', $sku)->exists()) {
            return $sku;
        }
        $counter = 2;
        $candidate = $sku . '-' . $storeId;
        while (Product::withTrashed()->where('sku', $candidate)->exists()) {
            $candidate = $sku . '-' . $storeId . '-' . $counter;
            $counter++;
        }
        return $candidate;
    }

    private function readCsv(string $path): array
    {
        $rows = [];
        if (!is_readable($path)) {
            return $rows;
        }

        $handle = fopen($path, 'r');
        if (!$handle) {
            return $rows;
        }

        $delimiter = $this->detectDelimiter($handle);
        rewind($handle);

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rows[] = $data;
        }

        fclose($handle);

        return $rows;
    }

    private function detectDelimiter($handle): string
    {
        $line = fgets($handle);
        if ($line === false) {
            return ',';
        }
        $delimiters = [',', ';', "\t", '|'];
        $counts = [];
        foreach ($delimiters as $delimiter) {
            $counts[$delimiter] = substr_count($line, $delimiter);
        }
        arsort($counts);
        return array_key_first($counts) ?: ',';
    }

    private function readXlsx(string $path): array
    {
        $rows = [];
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return $rows;
        }

        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml) {
            $shared = simplexml_load_string($sharedXml);
            foreach ($shared->si as $si) {
                $text = '';
                if (isset($si->t)) {
                    $text = (string) $si->t;
                } else {
                    foreach ($si->r as $run) {
                        $text .= (string) $run->t;
                    }
                }
                $sharedStrings[] = $text;
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if (!$sheetXml) {
            $zip->close();
            return $rows;
        }

        $sheet = simplexml_load_string($sheetXml);
        if (!isset($sheet->sheetData->row)) {
            $zip->close();
            return $rows;
        }

        // Determine max columns to normalize all rows to same length
        $maxCol = 0;
        foreach ($sheet->sheetData->row as $row) {
            foreach ($row->c as $c) {
                $cellRef = (string) $c['r'];
                $col = preg_replace('/\d+/', '', $cellRef);
                $colIndex = $this->columnToIndex($col);
                if ($colIndex > $maxCol) {
                    $maxCol = $colIndex;
                }
            }
        }

        foreach ($sheet->sheetData->row as $row) {
            $cells = array_fill(0, $maxCol + 1, '');
            foreach ($row->c as $c) {
                $cellRef = (string) $c['r'];
                $col = preg_replace('/\d+/', '', $cellRef);
                $colIndex = $this->columnToIndex($col);

                $value = '';
                $type = (string) $c['t'];
                if ($type === 's') {
                    $index = (int) $c->v;
                    $value = $sharedStrings[$index] ?? '';
                } elseif ($type === 'inlineStr' && isset($c->is->t)) {
                    $value = (string) $c->is->t;
                } elseif (isset($c->v)) {
                    $value = (string) $c->v;
                }
                $cells[$colIndex] = $value;
            }
            $rows[] = $cells;
        }

        $zip->close();

        return $rows;
    }

    private function columnToIndex(string $column): int
    {
        $column = strtoupper($column);
        $length = strlen($column);
        $index = 0;
        for ($i = 0; $i < $length; $i++) {
            $index = $index * 26 + (ord($column[$i]) - 64);
        }
        return $index - 1;
    }
}
