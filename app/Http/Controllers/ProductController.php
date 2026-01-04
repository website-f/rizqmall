<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Store;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use App\Models\VariantType;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;

class ProductController extends Controller
{
    /**
     * Show product/service/pharmacy creation form
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Please login to continue.');
        }

        $store = $user->stores()->first();
        if (!$store) {
            return redirect()->route('store.select-category')
                ->with('error', 'Please set up your store first.');
        }

        $type = $request->query('type', 'product');
        if (!in_array($type, ['product', 'service', 'pharmacy'])) {
            $type = 'product';
        }

        // Get categories for this store type
        $categories = ProductCategory::where('store_category_id', $store->store_category_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $tags = Tag::all();

        // Get variant types for product variations
        $variantTypes = VariantType::orderBy('sort_order')->get();

        return view('vendor.products.create', compact('store', 'type', 'categories', 'tags', 'variantTypes'));
    }

    /**
     * Store a new product/service/pharmacy item
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Please login to continue.');
        }

        $store = $user->stores()->first();
        if (!$store) {
            return redirect()->route('store.select-category')
                ->with('error', 'Please set up your store first.');
        }

        // Base validation
        $rules = [
            'type' => 'required|in:product,service,pharmacy',
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'required|string',
            'product_category_id' => 'nullable|exists:product_categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'product_type' => 'required|in:simple,variable',
            'regular_price' => 'required|numeric|min:0.01',
            'sale_price' => 'nullable|numeric|lt:regular_price|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'stock_quantity' => 'required_if:product_type,simple|nullable|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'allow_backorder' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
        ];

        // Type-specific validation
        if ($request->type === 'product') {
            $rules = array_merge($rules, [
                'weight' => 'nullable|numeric|min:0',
                'length' => 'nullable|numeric|min:0',
                'width' => 'nullable|numeric|min:0',
                'height' => 'nullable|numeric|min:0',
                'is_fragile' => 'nullable|boolean',
                'is_biodegradable' => 'nullable|boolean',
                'is_frozen' => 'nullable|boolean',
                'max_temperature' => 'nullable|string|max:50',
                'has_expiry' => 'nullable|boolean',
                'expiry_date' => 'nullable|date',
            ]);
        } elseif ($request->type === 'service') {
            $rules = array_merge($rules, [
                'service_duration' => 'required|integer|min:1',
                'service_availability' => 'required|in:instant,scheduled,both',
                'service_days' => 'nullable|array',
                'service_start_time' => 'nullable|date_format:H:i',
                'service_end_time' => 'nullable|date_format:H:i|after:service_start_time',
            ]);
        } elseif ($request->type === 'pharmacy') {
            $rules = array_merge($rules, [
                'requires_prescription' => 'nullable|boolean',
                'drug_code' => 'nullable|string|max:100',
                'manufacturer' => 'nullable|string|max:255',
                'active_ingredient' => 'nullable|string|max:255',
                'dosage_form' => 'nullable|string|max:100',
                'strength' => 'nullable|string|max:100',
                'has_expiry' => 'required|boolean',
                'expiry_date' => 'required_if:has_expiry,1|nullable|date',
            ]);
        }

        // Variant validation
        if ($request->product_type === 'variable') {
            $rules = array_merge($rules, [
                'variants' => 'required|array|min:1',
                'variants.*.name' => 'required|string',
                'variants.*.sku' => 'required|string|unique:product_variants,sku',
                'variants.*.price' => 'nullable|numeric|min:0',
                'variants.*.sale_price' => 'nullable|numeric|min:0',
                'variants.*.stock_quantity' => 'required|integer|min:0',
                'variants.*.options' => 'required|array',
                'variants.*.image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            ]);
        }

        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            // Create product
            $product = new Product();
            $product->store_id = $store->id;
            $product->type = $validated['type'];
            $product->product_category_id = $validated['product_category_id'] ?? null;
            $product->name = $validated['name'];
            $product->slug = $this->generateUniqueSlug($validated['name']);
            $product->short_description = $validated['short_description'] ?? null;
            $product->description = $validated['description'];
            $product->product_type = $validated['product_type'];
            $product->regular_price = $validated['regular_price'];
            $product->sale_price = $validated['sale_price'] ?? null;
            $product->cost_price = $validated['cost_price'] ?? null;
            $product->sku = $validated['sku'] ?? $this->generateSKU();
            $product->low_stock_threshold = $validated['low_stock_threshold'] ?? 5;
            $product->allow_backorder = $request->has('allow_backorder');
            $product->meta_title = $validated['meta_title'] ?? $validated['name'];
            $product->meta_description = $validated['meta_description'] ?? null;

            // Simple product inventory
            if ($validated['product_type'] === 'simple') {
                $product->stock_quantity = $validated['stock_quantity'] ?? 0;
            }

            // Type-specific fields
            if ($validated['type'] === 'product') {
                $product->weight = $validated['weight'] ?? null;
                $product->length = $validated['length'] ?? null;
                $product->width = $validated['width'] ?? null;
                $product->height = $validated['height'] ?? null;
                $product->is_fragile = $request->has('is_fragile');
                $product->is_biodegradable = $request->has('is_biodegradable');
                $product->is_frozen = $request->has('is_frozen');
                $product->max_temperature = $request->has('is_frozen') ? ($validated['max_temperature'] ?? null) : null;
                $product->has_expiry = $request->has('has_expiry');
                $product->expiry_date = $request->has('has_expiry') ? ($validated['expiry_date'] ?? null) : null;
            } elseif ($validated['type'] === 'service') {
                $product->service_duration = $validated['service_duration'];
                $product->service_availability = $validated['service_availability'];
                $product->service_days = $validated['service_days'] ?? null;
                $product->service_start_time = $validated['service_start_time'] ?? null;
                $product->service_end_time = $validated['service_end_time'] ?? null;
                $product->track_inventory = false; // Services don't track inventory
            } elseif ($validated['type'] === 'pharmacy') {
                $product->requires_prescription = $request->has('requires_prescription');
                $product->drug_code = $validated['drug_code'] ?? null;
                $product->manufacturer = $validated['manufacturer'] ?? null;
                $product->active_ingredient = $validated['active_ingredient'] ?? null;
                $product->dosage_form = $validated['dosage_form'] ?? null;
                $product->strength = $validated['strength'] ?? null;
                $product->has_expiry = $request->has('has_expiry');
                $product->expiry_date = $validated['expiry_date'] ?? null;
            }

            $product->status = 'published';
            $product->save();

            // Attach tags
            if (isset($validated['tags'])) {
                $product->tags()->sync($validated['tags']);
            }

            // Create variants
            if ($validated['product_type'] === 'variable' && isset($validated['variants'])) {
                foreach ($validated['variants'] as $index => $variantData) {
                    $variant = $product->variants()->create([
                        'sku' => $variantData['sku'],
                        'name' => $variantData['name'],
                        'price' => $variantData['price'] ?? null,
                        'sale_price' => $variantData['sale_price'] ?? null,
                        'stock_quantity' => $variantData['stock_quantity'],
                        'is_active' => true,
                    ]);

                    // Store variant options
                    if (isset($variantData['options'])) {
                        foreach ($variantData['options'] as $typeId => $optionData) {
                            $variant->options()->create([
                                'variant_type_id' => $typeId,
                                'value' => $optionData['value'],
                                'color_code' => $optionData['color_code'] ?? null,
                                'sort_order' => $index,
                            ]);
                        }
                    }

                    // Handle variant image
                    if ($request->hasFile("variants.$index.image")) {
                        $image = $request->file("variants.$index.image");
                        $path = $this->storeImage($image, 'products/variants');
                        $variant->update(['image' => $path]);
                    }
                }
            }

            // Handle product images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $this->storeImage($image, 'products');
                    $thumbnailPath = $this->createThumbnail($image, 'products/thumbnails');

                    $product->images()->create([
                        'path' => $path,
                        'thumbnail_path' => $thumbnailPath,
                        'sort_order' => $index,
                        'is_primary' => $index === 0,
                    ]);
                }
            }

            // Add specifications
            if ($request->has('specifications')) {
                foreach ($request->specifications as $index => $spec) {
                    if (!empty($spec['key']) && !empty($spec['value'])) {
                        $product->specifications()->create([
                            'spec_key' => $spec['key'],
                            'spec_value' => $spec['value'],
                            'spec_group' => $spec['group'] ?? 'General',
                            'sort_order' => $index,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('vendor.dashboard')
                ->with('success', ucfirst($validated['type']) . ' "' . $product->name . '" created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product creation failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Failed to create ' . $validated['type'] . '. Please try again.');
        }
    }

    /**
     * Show product details
     */
    public function show($slug)
    {
        $product = Product::with([
            'images',
            'variants.options.type',
            'category',
            'tags',
            'specifications',
            'store',
            'reviews.user'
        ])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        // Prepare variant data for JavaScript
        $variantData = [];
        if ($product->product_type === 'variable') {
            foreach ($product->variants as $variant) {
                $options = [];
                foreach ($variant->options as $option) {
                    $options[$option->variant_type_id] = [
                        'value' => $option->value,
                        'color_code' => $option->color_code,
                    ];
                }

                $variantData[] = [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'name' => $variant->name,
                    'price' => $variant->price ?? $product->regular_price,
                    'sale_price' => $variant->sale_price ?? $product->sale_price,
                    'stock' => $variant->stock_quantity,
                    'options' => $options,
                    'image' => $variant->image,
                ];
            }
        }

        // Get variant types used in this product
        $variantTypes = [];
        if ($product->product_type === 'variable') {
            $typeIds = $product->variants()->with('options.type')
                ->get()
                ->pluck('options')
                ->flatten()
                ->pluck('variant_type_id')
                ->unique();

            $variantTypes = VariantType::whereIn('id', $typeIds)->get();
        }

        return view('store.viewProduct', compact('product', 'variantData', 'variantTypes'));
    }

    /**
     * List all products
     */
    public function index(Request $request)
    {
        // Vendor Dashboard: List own products
        if ($request->routeIs('vendor.products.index')) {
            $user = Auth::user();
            if (!$user) {
                return redirect()->route('login');
            }

            $store = $user->stores()->first();
            if (!$store) {
                return redirect()->route('store.select-category');
            }

            $products = Product::where('store_id', $store->id)
                ->with(['category', 'images'])
                ->latest()
                ->paginate(10);

            return view('vendor.products.index', compact('products'));
        }

        // Public: List published products
        $query = Product::with(['images' => fn($q) => $q->where('is_primary', true), 'store'])
            ->where('status', 'published');

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('product_category_id', $request->category);
        }

        // Search
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Sort
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'price_low':
                $query->orderBy('regular_price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('regular_price', 'desc');
                break;
            case 'popular':
                $query->orderBy('sold_count', 'desc');
                break;
            case 'rating':
                $query->orderBy('rating_average', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(12);

        // Get user's wishlist product IDs
        $wishlistProductIds = [];
        if (auth()->check()) {
            $wishlistProductIds = auth()->user()->wishlists()->pluck('product_id')->toArray();
        }

        return view('store.allProducts', compact('products', 'wishlistProductIds'));
    }

    /**
     * Helper: Generate unique slug
     */
    private function generateUniqueSlug($name, $id = null)
    {
        $slug = Str::slug($name);
        $count = Product::where('slug', 'like', $slug . '%')
            ->when($id, fn($q) => $q->where('id', '!=', $id))
            ->count();

        return $count > 0 ? $slug . '-' . ($count + 1) : $slug;
    }

    /**
     * Helper: Generate SKU
     */
    private function generateSKU()
    {
        return 'PRD-' . strtoupper(Str::random(8));
    }

    /**
     * Helper: Store image
     */
    private function storeImage($file, $path)
    {
        return $file->store($path, 'public');
    }

    /**
     * Helper: Create thumbnail
     */
    private function createThumbnail($file, $path)
    {
        // For now, store same image - you can use Intervention Image for proper thumbnails
        return $file->store($path, 'public');
    }

    /**
     * Show product edit form
     */
    public function edit($id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Please login to continue.');
        }

        $store = $user->stores()->first();
        if (!$store) {
            return redirect()->route('store.select-category')
                ->with('error', 'Please set up your store first.');
        }

        // Get product and ensure it belongs to this vendor's store
        $product = Product::with(['images', 'variants.options', 'tags', 'specifications'])
            ->where('id', $id)
            ->where('store_id', $store->id)
            ->firstOrFail();

        $type = $product->type;

        // Get categories for this store type
        $categories = ProductCategory::where('store_category_id', $store->store_category_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $tags = Tag::all();

        // Get variant types for product variations
        $variantTypes = VariantType::orderBy('sort_order')->get();

        return view('vendor.products.edit', compact('product', 'store', 'type', 'categories', 'tags', 'variantTypes'));
    }

    /**
     * Update product
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Please login to continue.');
        }

        $store = $user->stores()->first();
        if (!$store) {
            return redirect()->route('store.select-category')
                ->with('error', 'Please set up your store first.');
        }

        // Get product and ensure it belongs to this vendor's store
        $product = Product::where('id', $id)
            ->where('store_id', $store->id)
            ->firstOrFail();

        // Base validation (similar to store method)
        $rules = [
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'required|string',
            'product_category_id' => 'nullable|exists:product_categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'regular_price' => 'required|numeric|min:0.01',
            'sale_price' => 'nullable|numeric|lt:regular_price|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
        ];

        // Type-specific validation
        if ($product->type === 'service') {
            $rules['service_duration'] = 'nullable|integer|min:1';
            $rules['service_availability'] = 'nullable|in:instant,scheduled,both';
        } elseif ($product->type === 'pharmacy') {
            $rules['requires_prescription'] = 'nullable|boolean';
            $rules['has_expiry'] = 'nullable|boolean';
            $rules['expiry_date'] = 'nullable|date|after:today';
        }

        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();

            // Update basic fields
            $product->name = $validated['name'];
            $product->slug = Str::slug($validated['name']) . '-' . $product->id;
            $product->short_description = $validated['short_description'] ?? null;
            $product->description = $validated['description'];
            $product->product_category_id = $validated['product_category_id'] ?? null;
            $product->regular_price = $validated['regular_price'];
            $product->sale_price = $validated['sale_price'] ?? null;
            $product->cost_price = $validated['cost_price'] ?? null;

            if ($product->product_type === 'simple') {
                $product->stock_quantity = $validated['stock_quantity'] ?? 0;
            }

            $product->low_stock_threshold = $validated['low_stock_threshold'] ?? 5;

            // Update type-specific fields
            if ($product->type === 'service') {
                $product->service_duration = $validated['service_duration'] ?? null;
                $product->service_availability = $validated['service_availability'] ?? 'instant';
            } elseif ($product->type === 'pharmacy') {
                $product->requires_prescription = $validated['requires_prescription'] ?? false;
                $product->has_expiry = $validated['has_expiry'] ?? false;
                $product->expiry_date = $validated['expiry_date'] ?? null;
            }

            $product->save();

            // Update tags
            if (isset($validated['tags'])) {
                $product->tags()->sync($validated['tags']);
            }

            // Handle new images if uploaded
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $this->storeImage($image, 'products');
                    $thumbnailPath = $this->createThumbnail($image, 'products/thumbnails');

                    $product->images()->create([
                        'path' => $path,
                        'thumbnail_path' => $thumbnailPath,
                        'sort_order' => $product->images()->count() + $index,
                        'is_primary' => $product->images()->count() === 0 && $index === 0,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('vendor.products.index')
                ->with('success', 'Product "' . $product->name . '" updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product update failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Failed to update product. Please try again.');
        }
    }

    /**
     * Delete product (soft delete)
     */
    public function destroy($id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Please login to continue.');
        }

        $store = $user->stores()->first();
        if (!$store) {
            return redirect()->route('store.select-category')
                ->with('error', 'Please set up your store first.');
        }

        try {
            // Get product and ensure it belongs to this vendor's store
            $product = Product::where('id', $id)
                ->where('store_id', $store->id)
                ->firstOrFail();

            $productName = $product->name;

            // Soft delete (if using SoftDeletes trait) or change status
            if (method_exists($product, 'delete')) {
                $product->delete();
            } else {
                $product->status = 'archived';
                $product->save();
            }

            Log::info('Product deleted by vendor', [
                'product_id' => $id,
                'product_name' => $productName,
                'vendor_id' => $user->id,
                'store_id' => $store->id,
            ]);

            return redirect()->route('vendor.products.index')
                ->with('success', 'Product "' . $productName . '" has been deleted.');
        } catch (\Exception $e) {
            Log::error('Product deletion failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete product. Please try again.');
        }
    }

    /**
     * Toggle product status (publish/unpublish)
     */
    public function toggleStatus($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $store = $user->stores()->first();
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'No store found'], 403);
        }

        try {
            $product = Product::where('id', $id)
                ->where('store_id', $store->id)
                ->firstOrFail();

            $product->status = $product->status === 'published' ? 'draft' : 'published';
            $product->save();

            return response()->json([
                'success' => true,
                'status' => $product->status,
                'message' => 'Product status updated to ' . $product->status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete product image
     */
    public function deleteImage(Product $product, $image)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $store = $user->stores()->first();
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'No store found'], 403);
        }

        // Verify product belongs to user's store
        if ($product->store_id !== $store->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        try {
            $productImage = $product->images()->where('id', $image)->first();

            if (!$productImage) {
                return response()->json(['success' => false, 'message' => 'Image not found'], 404);
            }

            // Delete the file from storage if it exists
            $imagePath = str_replace('/storage/', 'public/', $productImage->image_path);
            if (\Storage::exists($imagePath)) {
                \Storage::delete($imagePath);
            }

            // Delete the database record
            $productImage->delete();

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting product image', [
                'error' => $e->getMessage(),
                'product_id' => $product->id,
                'image_id' => $image,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Get product variants
     */
    public function getVariants($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        if ($product->product_type !== 'variable') {
            return response()->json([
                'success' => false,
                'message' => 'This product does not have variants'
            ], 400);
        }

        $variants = $product->variants()
            ->with('options.type')
            ->where('is_active', true)
            ->get()
            ->map(function ($variant) use ($product) {
                return [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'sku' => $variant->sku,
                    'regular_price' => $variant->price ?? $product->regular_price,
                    'sale_price' => $variant->sale_price ?? $product->sale_price,
                    'stock_quantity' => $variant->stock_quantity,
                    'image' => $variant->image,
                    'options' => $variant->options->map(function ($option) {
                        return [
                            'type_id' => $option->variant_type_id,
                            'type_name' => $option->type->name ?? 'Option',
                            'name' => $option->value,
                            'color_code' => $option->color_code,
                        ];
                    })
                ];
            });

        return response()->json([
            'success' => true,
            'variants' => $variants
        ]);
    }
}
