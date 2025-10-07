<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\StoreCategory;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StoreController extends Controller
{
    /**
     * Show store category selection (first step)
     */
    public function showCategorySelection(Request $request)
    {
        $authUserId = $request->query('user_id');
        session(['auth_user_id' => $authUserId]);

        if (!$authUserId) {
            abort(403, 'Unauthorized: user_id missing.');
        }

        // Check if user already has a store
        $existingStore = Store::where('auth_user_id', $authUserId)->first();
        if ($existingStore) {
            return redirect()->route('rizqmall.home')
                ->with('info', 'You already have a store.');
        }

        // Get active store categories
        $categories = StoreCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('store.select-category', compact('categories'));
    }

    /**
     * Show store setup form (second step)
     */
    public function showSetupForm(Request $request)
    {
        $authUserId = session('auth_user_id');
        if (!$authUserId) {
            return redirect()->route('store.select-category')
                ->with('error', 'Session expired. Please start again.');
        }

        $categoryId = $request->query('category');
        if (!$categoryId) {
            return redirect()->route('store.select-category')
                ->with('error', 'Please select a store category.');
        }

        $category = StoreCategory::findOrFail($categoryId);
        session(['selected_store_category' => $categoryId]);

        $prefill = [
            'email' => $request->query('email'),
            'name'  => $request->query('name'),
        ];

        return view('store.setup', compact('category', 'prefill'));
    }

    /**
     * Store a newly created store
     */
    public function store(Request $request)
    {
        $authUserId = session('auth_user_id');
        $storeCategoryId = session('selected_store_category');

        if (!$authUserId || !$storeCategoryId) {
            return redirect()->route('store.select-category')
                ->with('error', 'Session expired. Please start again.');
        }

        // Validation
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'image' => 'nullable|string',
            'banner' => 'nullable|string',
        ]);

        // Generate unique slug
        $slug = Str::slug($request->name);
        $uniqueSlug = $slug;
        $count = 2;
        while (Store::where('slug', $uniqueSlug)->exists()) {
            $uniqueSlug = $slug . '-' . $count++;
        }

        // Handle uploaded files
        $logoPath = null;
        $bannerPath = null;
        $storeFolder = 'stores/' . Str::uuid();

        if ($request->filled('image')) {
            $tempPath = str_replace('/storage/', '', $request->image);
            if (Storage::disk('public')->exists($tempPath)) {
                $newLogoPath = $storeFolder . '/logo_' . basename($tempPath);
                Storage::disk('public')->move($tempPath, $newLogoPath);
                $logoPath = $newLogoPath;
            }
        }

        if ($request->filled('banner')) {
            $tempPath = str_replace('/storage/', '', $request->banner);
            if (Storage::disk('public')->exists($tempPath)) {
                $newBannerPath = $storeFolder . '/banner_' . basename($tempPath);
                Storage::disk('public')->move($tempPath, $newBannerPath);
                $bannerPath = $newBannerPath;
            }
        }

        // Create Store
        $store = Store::create([
            'auth_user_id' => $authUserId,
            'store_category_id' => $storeCategoryId,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'slug' => $uniqueSlug,
            'location' => $request->location,
            'description' => $request->description,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'image' => $logoPath,
            'banner' => $bannerPath,
        ]);

        // Get store category to determine redirect
        $category = StoreCategory::find($storeCategoryId);
        
        // Redirect based on category
        if ($category->slug === 'marketplace') {
            return redirect()->route('products.create', ['store' => $store->id, 'type' => 'product'])
                ->with('success', 'Store created! Now add your first product.');
        } elseif ($category->slug === 'services') {
            return redirect()->route('products.create', ['store' => $store->id, 'type' => 'service'])
                ->with('success', 'Store created! Now add your first service.');
        } elseif ($category->slug === 'pharmacy') {
            return redirect()->route('products.create', ['store' => $store->id, 'type' => 'pharmacy'])
                ->with('success', 'Store created! Now add your first pharmacy item.');
        }

        return redirect()->route('rizqmall.home')
            ->with('success', 'Store created successfully!');
    }

    /**
     * Homepage showing all stores
     */
    public function home()
    {
        // Get ALL store categories (both active and inactive)
        $storeCategories = StoreCategory::orderBy('sort_order')->get();

        // Get featured products from all categories
        $featuredProducts = \App\Models\Product::where('status', 'published')
            ->with(['images' => fn($q) => $q->where('is_primary', true), 'store'])
            ->inRandomOrder()
            ->limit(12)
            ->get();

        return view('store.home', compact('storeCategories', 'featuredProducts'));
    }

    /**
     * Show all stores
     */
    public function stores()
    {
        $stores = Store::with(['category'])
            ->withCount(['products' => function($query) {
                $query->where('status', 'published');
            }])
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(12);

        return view('store.stores', compact('stores'));
    }

    /**
     * Show store profile
     */
    public function showProfile(Store $store)
    {
        $products = $store->products()
            ->with(['images' => fn($q) => $q->where('is_primary', true), 'category'])
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('store.store-view', compact('store', 'products'));
    }

    /**
     * Change store banner
     */
    public function changeBanner(Request $request, Store $store)
    {
        if (session('auth_user_id') != $store->auth_user_id) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'banner_path' => 'required|string',
        ]);

        $tempPath = str_replace('/storage/', '', $request->banner_path);
        $filename = basename($tempPath);
        $permanentPath = 'stores/banners/' . $filename;

        if (Storage::disk('public')->exists($tempPath)) {
            // Delete old banner
            if ($store->banner) {
                Storage::disk('public')->delete($store->banner);
            }
            
            Storage::disk('public')->move($tempPath, $permanentPath);
            $store->banner = $permanentPath;
            $store->save();
        }

        return redirect()->route('store.profile', $store->slug)
            ->with('success', 'Store banner updated successfully!');
    }
}