<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\StoreCategory;
use App\Services\SubscriptionService;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StoreController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Show store category selection (first step)
     */
    public function showCategorySelection()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('auth.redirect')
                ->with('error', 'Please login to create a store.');
        }

        // Check if user is a vendor
        if (!$user->is_vendor) {
            abort(403, 'Only vendor accounts can create stores.');
        }

        // Check if user already has a store
        if ($user->hasStore()) {
            return redirect()->route('vendor.dashboard')
                ->with('info', 'You already have a store.');
        }

        // Check subscription status
        if (!$user->has_active_subscription) {
            return redirect()->route('subscription.expired')
                ->with('error', 'Please activate your subscription to create a store.');
        }

        // Get active store categories
        $categories = StoreCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('store.select-category', compact('categories', 'user'));
    }

    /**
     * Show store setup form (second step)
     */
    public function showSetupForm(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->is_vendor) {
            return redirect()->route('auth.redirect')
                ->with('error', 'Please login as a vendor.');
        }

        $categoryId = $request->query('category');
        if (!$categoryId) {
            return redirect()->route('store.select-category')
                ->with('error', 'Please select a store category.');
        }

        $category = StoreCategory::findOrFail($categoryId);
        session(['selected_store_category' => $categoryId]);

        // Pre-fill with user data
        $prefill = [
            'email' => $user->email,
            'name' => $user->name,
            'phone' => $user->phone,
        ];

        return view('store.setup', compact('category', 'prefill', 'user'));
    }

    /**
     * Store a newly created store
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $storeCategoryId = session('selected_store_category');

        if (!$user || !$user->is_vendor) {
            return redirect()->route('auth.redirect')
                ->with('error', 'Authentication required.');
        }

        if (!$storeCategoryId) {
            return redirect()->route('store.select-category')
                ->with('error', 'Please start the setup process again.');
        }

        // Check if user already has a store
        if ($user->hasStore()) {
            return redirect()->route('vendor.dashboard')
                ->with('info', 'You already have a store.');
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
            'ssm_document' => 'nullable|string',
            'ic_document' => 'nullable|string',
            'business_registration_number' => 'nullable|string|max:100',
            'business_registration_no' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:100',
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
        $ssmDocPath = null;
        $icDocPath = null;
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

        if ($request->filled('ssm_document')) {
            $tempPath = str_replace('/storage/', '', $request->ssm_document);
            if (Storage::disk('public')->exists($tempPath)) {
                $newSsmPath = $storeFolder . '/ssm_' . basename($tempPath);
                Storage::disk('public')->move($tempPath, $newSsmPath);
                $ssmDocPath = $newSsmPath;
            }
        }

        if ($request->filled('ic_document')) {
            $tempPath = str_replace('/storage/', '', $request->ic_document);
            if (Storage::disk('public')->exists($tempPath)) {
                $newIcPath = $storeFolder . '/ic_' . basename($tempPath);
                Storage::disk('public')->move($tempPath, $newIcPath);
                $icDocPath = $newIcPath;
            }
        }

        // Create Store
        $store = Store::create([
            'user_id' => $user->id,
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
            'ssm_document' => $ssmDocPath,
            'ic_document' => $icDocPath,
            'business_registration_number' => $request->business_registration_number,
            'business_registration_no' => $request->business_registration_no,
            'tax_id' => $request->tax_id,
            'status' => 'active',
            'is_active' => true,
        ]);

        // Clear session
        session()->forget('selected_store_category');

        // Notify subscription system
        $this->subscriptionService->notifyEvent('store_created', [
            'user_id' => $user->subscription_user_id,
            'store_id' => $store->id,
            'store_name' => $store->name,
        ]);

        // Get store category to determine redirect
        $category = StoreCategory::find($storeCategoryId);

        // Redirect to add first product
        return redirect()->route('vendor.products.create')
            ->with('success', 'Store created successfully! Now add your first product.');
    }

    /**
     * Homepage showing all stores
     */
    public function home()
    {
        // Get ALL store categories (both active and inactive for display)
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
            ->withCount(['products' => function ($query) {
                $query->where('status', 'published');
            }])
            ->where('is_active', true)
            ->where('status', 'active')
            ->orderBy('name')
            ->paginate(12);

        return view('store.stores', compact('stores'));
    }

    /**
     * Show store profile
     */
    public function showProfile(Store $store)
    {
        // Check if store is active
        if (!$store->is_active || $store->status !== 'active') {
            abort(404, 'Store not found or inactive.');
        }

        $products = $store->products()
            ->with(['images' => fn($q) => $q->where('is_primary', true), 'category'])
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        // Check if current user is the owner
        $isOwner = Auth::check() && Auth::id() === $store->user_id;

        return view('store.store-view', compact('store', 'products', 'isOwner'));
    }

    /**
     * Show store edit form
     */
    public function edit()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $store = $user->stores()->first();

        if (!$store) {
            return redirect()->route('store.select-category');
        }

        $category = $store->category;

        return view('vendor.store.edit', compact('store', 'category'));
    }

    /**
     * Update store details
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $store = $user->stores()->first();

        if (!$store) {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'business_registration_no' => 'nullable|string|max:100',
            'business_registration_number' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:100',
        ]);

        $store->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'description' => $request->description,
            'location' => $request->location,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'business_registration_no' => $request->business_registration_no,
            'business_registration_number' => $request->business_registration_number,
            'tax_id' => $request->tax_id,
        ]);

        // Handle images if present
        if ($request->filled('image')) {
            $tempPath = str_replace('/storage/', '', $request->image);
            if (Storage::disk('public')->exists($tempPath)) {
                $newLogoPath = 'stores/' . $store->id . '/logo_' . basename($tempPath);
                Storage::disk('public')->move($tempPath, $newLogoPath);
                $store->image = $newLogoPath;
                $store->save();
            }
        }

        if ($request->filled('banner')) {
            $tempPath = str_replace('/storage/', '', $request->banner);
            if (Storage::disk('public')->exists($tempPath)) {
                $newBannerPath = 'stores/' . $store->id . '/banner_' . basename($tempPath);
                Storage::disk('public')->move($tempPath, $newBannerPath);
                $store->banner = $newBannerPath;
                $store->save();
            }
        }

        // Handle SSM document
        if ($request->filled('ssm_document')) {
            $tempPath = str_replace('/storage/', '', $request->ssm_document);
            if (Storage::disk('public')->exists($tempPath)) {
                $newSsmPath = 'stores/' . $store->id . '/ssm_' . basename($tempPath);
                Storage::disk('public')->move($tempPath, $newSsmPath);
                $store->ssm_document = $newSsmPath;
                $store->save();
            }
        }

        // Handle IC document
        if ($request->filled('ic_document')) {
            $tempPath = str_replace('/storage/', '', $request->ic_document);
            if (Storage::disk('public')->exists($tempPath)) {
                $newIcPath = 'stores/' . $store->id . '/ic_' . basename($tempPath);
                Storage::disk('public')->move($tempPath, $newIcPath);
                $store->ic_document = $newIcPath;
                $store->save();
            }
        }

        return redirect()->route('vendor.store.edit')->with('success', 'Store updated successfully.');
    }

    /**
     * Change store banner
     */
    public function changeBanner(Request $request, Store $store)
    {
        $user = Auth::user();

        if (!$user || $user->id !== $store->user_id) {
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
