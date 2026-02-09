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

        $headers = array_shift($rows);
        $normalizedHeaders = array_map([$this, 'normalizeHeader'], $headers);
        $map = $this->buildColumnMap($normalizedHeaders);

        if (empty($map['name']) || (empty($map['regular_price']) && empty($map['sale_price']))) {
            return back()->with('error', 'Missing required columns. Need at least Name and Price/Sale Price.')
                ->with('import_headers', $headers);
        }

        $created = 0;
        $skipped = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $rowIndex => $row) {
                $rowNumber = $rowIndex + 2; // 1-based plus header
                $rowData = $this->rowToAssoc($row, $normalizedHeaders);

                $name = $this->valueFromRow($rowData, $map, 'name');
                if (!$name) {
                    $skipped++;
                    $errors[] = "Row {$rowNumber}: Missing product name.";
                    continue;
                }

                $regularPrice = $this->toFloat($this->valueFromRow($rowData, $map, 'regular_price'));
                $salePrice = $this->toFloat($this->valueFromRow($rowData, $map, 'sale_price'));
                if ($regularPrice <= 0 && $salePrice > 0) {
                    $regularPrice = $salePrice;
                }
                if ($regularPrice <= 0) {
                    $skipped++;
                    $errors[] = "Row {$rowNumber}: Missing regular price.";
                    continue;
                }

                $description = $this->valueFromRow($rowData, $map, 'description')
                    ?: $this->valueFromRow($rowData, $map, 'short_description')
                    ?: $name;
                $shortDescription = $this->valueFromRow($rowData, $map, 'short_description');

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
                    $slug = $this->uniqueSlug($name, $store->id);
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
                        'cost_price' => $this->toFloat($this->valueFromRow($rowData, $map, 'cost_price')) ?: null,
                        'sku' => $sku,
                        'stock_quantity' => $this->toInt($this->valueFromRow($rowData, $map, 'stock_quantity')) ?? 0,
                        'low_stock_threshold' => $this->toInt($this->valueFromRow($rowData, $map, 'low_stock_threshold')) ?? 5,
                        'track_inventory' => $this->toBool($this->valueFromRow($rowData, $map, 'track_inventory'), true),
                        'allow_backorder' => $this->toBool($this->valueFromRow($rowData, $map, 'allow_backorder'), false),
                        'allow_bulk_order' => $this->toBool($this->valueFromRow($rowData, $map, 'allow_bulk_order'), false),
                        'minimum_order_quantity' => $this->toInt($this->valueFromRow($rowData, $map, 'minimum_order_quantity')),
                        'bulk_price' => $this->toFloat($this->valueFromRow($rowData, $map, 'bulk_price')) ?: null,
                        'bulk_quantity_threshold' => $this->toInt($this->valueFromRow($rowData, $map, 'bulk_quantity_threshold')),
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
            ->with('import_errors', $errors);
    }

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
        return (float) str_replace([',', 'RM', 'rm'], '', (string) $value);
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

    private function uniqueSlug(string $name, int $storeId): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'product';
        }
        $slug = $base;
        $counter = 2;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $storeId . '-' . $counter;
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
        if (!Product::where('sku', $sku)->exists()) {
            return $sku;
        }
        $counter = 2;
        $candidate = $sku . '-' . $storeId;
        while (Product::where('sku', $candidate)->exists()) {
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

        foreach ($sheet->sheetData->row as $row) {
            $cells = [];
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
            if (!empty($cells)) {
                ksort($cells);
                $rows[] = array_values($cells);
            }
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
