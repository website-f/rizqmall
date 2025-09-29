<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Store;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class ProductController extends Controller
{
      /**
     * Show the "Add Product" form for the user's store.
     */
    public function showProductsForm($storeId, Request $request)
    {
        // 1️⃣ Make sure the store belongs to the current sandbox user
        $authUserId = session('auth_user_id');
        if (!$authUserId) {
            return redirect()->route('store.setup')
                             ->with('error', 'Session expired. Please set up your store again.');
        }

        $store = Store::where('id', $storeId)
                      ->where('auth_user_id', $authUserId)
                      ->first();

        if (!$store) {
            return redirect()->route('store.setup')
                             ->with('error', 'Store not found or unauthorized.');
        }

        // 2️⃣ Load dropdown data
        $categories = Category::select('id', 'name')->get();
        $tags = Tag::select('id', 'name')->get();

        return view('store.products', compact('categories', 'tags', 'store'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request, $storeId)
    {
        $authUserId = session('auth_user_id');
        if (!$authUserId) {
            return redirect()->route('store.setup')
                             ->with('error', 'Session expired. Please log in from Sandbox again.');
        }

        // 1️⃣ Validate and verify store ownership
        $store = Store::where('id', $storeId)
                      ->where('auth_user_id', $authUserId)
                      ->first();

        if (!$store) {
            return back()->with('error', 'Store not found or unauthorized.');
        }

        // 2️⃣ Validation
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'regular_price' => 'required|numeric|min:0.01',
            'sale_price' => 'nullable|numeric|lt:regular_price|min:0',
            'stock_quantity' => 'required|integer|min:0',

            // Attributes
            'max_temperature' => 'nullable|string|max:50',
            'expiry_date' => 'nullable|date',

            // Advanced IDs
            'product_id_type' => 'nullable|string|max:50',
            'product_id_value' => 'nullable|string|max:255',

            // Variants
            'variants' => 'nullable|array',
            'variants.*.name' => 'required_with:variants|string|max:255',
            'variants.*.price' => 'nullable|numeric',
            'variants.*.stock_quantity' => 'required_with:variants|integer|min:0',
        ]);

        // 3️⃣ Create product
        $product = new Product();
        $product->store_id = $store->id;
        $product->category_id = $validated['category_id'] ?? null;
        $product->name = $validated['name'];
        $product->slug = Str::slug($validated['name']);
        $product->description = $validated['description'];
        $product->regular_price = $validated['regular_price'];
        $product->sale_price = $validated['sale_price'] ?? null;
        $product->stock_quantity = $validated['stock_quantity'];
        $product->status = 'published';

        // Attributes
        $product->is_fragile = $request->has('is_fragile');
        $product->is_biodegradable = $request->has('is_biodegradable');
        $product->is_frozen = $request->has('is_frozen');
        $product->max_temperature = $request->has('is_frozen') ? ($validated['max_temperature'] ?? null) : null;
        $product->expiry_date = $request->has('has_expiry') ? ($validated['expiry_date'] ?? null) : null;

        // Advanced IDs
        $product->product_id_type = $validated['product_id_type'] ?? null;
        $product->product_id_value = $validated['product_id_value'] ?? null;

        $product->save();

        // 4️⃣ Attach tags
        if (isset($validated['tags'])) {
            $product->tags()->sync($validated['tags']);
        }

        // 5️⃣ Variants
        if (isset($validated['variants'])) {
            foreach ($validated['variants'] as $variantData) {
                $product->variants()->create([
                    'name' => $variantData['name'],
                    'price' => $variantData['price'],
                    'stock_quantity' => $variantData['stock_quantity'],
                ]);
            }
        }

        // 6️⃣ Redirect to store homepage
        return redirect()->route('rizqmall.home')
                         ->with('success', 'Product "' . $product->name . '" published successfully!');
    }
}
