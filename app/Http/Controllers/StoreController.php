<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function stores()
    {
        // Fetch all stores, ordered alphabetically by name
        $stores = Store::orderBy('name')->get(); 

        // Pass the fetched data to the view
        return view('store.stores', compact('stores'));
    }


     public function showSetupForm(Request $request)
    {
        // 1️⃣ Get auth_user_id from query string (sent from Sandbox)
        $authUserId = $request->query('user_id');

        if (!$authUserId) {
            abort(403, 'Unauthorized: user_id missing.');
        }

        // 2️⃣ Check if this auth_user_id already has a store
        $existingStore = Store::where('auth_user_id', $authUserId)->first();

        if ($existingStore) {
            // Redirect to store listing page if store already exists
            return redirect('/stores')
                ->with('info', 'You already have a store. Redirected to your store page.');
        }

        // 3️⃣ Store auth_user_id in session for use during form submission
        session(['auth_user_id' => $authUserId]);

        // 4️⃣ Pass user details (optional) from query to form
        $prefill = [
            'email' => $request->query('email'),
            'name'  => $request->query('name'),
        ];

        return view('store.setup', compact('prefill'));
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request)
    {
        // Get auth_user_id from session (set during showSetupForm)
        $authUserId = session('auth_user_id');
        if (!$authUserId) {
            abort(403, 'Unauthorized: session expired.');
        }

        // 1️⃣ Validation
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        // 2️⃣ Generate unique slug
        $slug = Str::slug($request->name);
        $uniqueSlug = $slug;
        $count = 2;
        while (Store::where('slug', $uniqueSlug)->exists()) {
            $uniqueSlug = $slug . '-' . $count++;
        }

        // 3️⃣ Create Store
        $store = Store::create([
            'auth_user_id' => $authUserId,
            'name' => $request->name,
            'slug' => $uniqueSlug,
            'location' => $request->location,
            'description' => $request->description,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        // 4️⃣ Redirect to Add Product page
        return redirect()->route('store.products', ['store' => $store->id])
        ->with('success', 'Store "' . $store->name . '" created successfully! Now add your first product.');
    }
   

    // Rizqmall homepage showing all stores
     public function home()
    {
        // 1. Fetch all root (parent) categories for the top navigation bar.
        $categories = Category::whereNull('parent_id')
                              ->orderBy('name', 'asc')
                              ->get();
                              
        // 2. Fetch general products for the main "Top Deals" slider.
        // We ensure the product is published, load its variants, and limit the count.
        $products = Product::where('status', 'published')
                           ->with('variants')
                           ->inRandomOrder()
                           ->limit(10)
                           ->get();
                           
        // The `$electronics` fetch logic has been removed.

        return view('store.home', compact('categories', 'products')); // Updated compact list
    }
}
