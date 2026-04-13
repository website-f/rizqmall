<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\StoreCategory;
use App\Services\SubscriptionService;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        $sandboxType = $user->sandbox_type ?? session('sandbox_type');

        // Only Sandbox Usahawan can create vendor stores
        if ($sandboxType && $sandboxType !== 'usahawan') {
            return redirect()->route('rizqmall.home')
                ->with('error', 'Only Sandbox Usahawan users can create vendor stores. Your account type is Sandbox ' . ucfirst($sandboxType) . '.');
        }

        // Check store quota
        $currentStoreCount = $user->stores()->count();
        $baseQuota = (int) session('stores_quota', 1); // From SSO login
        if ($baseQuota < 1) {
            $baseQuota = 1;
        }

        // Calculate additional quota from purchases
        $additionalSlots = \App\Models\StorePurchase::where('user_id', $user->id)
            ->active()
            ->sum('store_slots_purchased');

        // Total quota
        $storeQuota = $baseQuota + $additionalSlots;

        // Only block if they actually have stores AND reached the limit
        // This prevents redirect loops when quota is 0 or misconfigured
        if ($currentStoreCount > 0 && $currentStoreCount >= $storeQuota) {
            return redirect()->route('vendor.my-stores')
                ->with('error', 'You have reached your store limit (' . $storeQuota . '). Purchase additional store slots to create more stores.');
        }

        // Note: Subscription check is handled by CheckSubscription middleware
        // No need to check again here to avoid redirect loops

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

        return view('store.setup', compact('category', 'prefill', 'user', 'categoryId'));
    }

    /**
     * Store a newly created store
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $storeCategoryId = $request->input('store_category_id') ?? session('selected_store_category');

        if (!$user || !$user->is_vendor) {
            return redirect()->route('auth.redirect')
                ->with('error', 'Authentication required.');
        }

        $sandboxType = $user->sandbox_type ?? session('sandbox_type');

        // Only Sandbox Usahawan can create vendor stores
        if ($sandboxType && $sandboxType !== 'usahawan') {
            return redirect()->route('rizqmall.home')
                ->with('error', 'Only Sandbox Usahawan users can create vendor stores.');
        }

        if (!$storeCategoryId) {
            return redirect()->route('store.select-category')
                ->with('error', 'Please start the setup process again.');
        }

        if (!$request->filled('store_category_id')) {
            $request->merge(['store_category_id' => $storeCategoryId]);
        }

        // Check store quota (1 store per RizqMall subscription + purchased slots)
        $currentStoreCount = $user->stores()->count();
        $baseQuota = (int) session('stores_quota', 1); // Default 1 store per subscription
        if ($baseQuota < 1) {
            $baseQuota = 1;
        }

        // Calculate additional quota from purchases
        $additionalSlots = \App\Models\StorePurchase::where('user_id', $user->id)
            ->active()
            ->sum('store_slots_purchased');

        // Total quota
        $storeQuota = $baseQuota + $additionalSlots;

        if ($currentStoreCount >= $storeQuota) {
            return redirect()->route('vendor.my-stores')
                ->with('error', 'You have reached your store limit (' . $storeQuota . '). Purchase additional store slots to create more stores.');
        }

        // Validation
        $validated = $request->validate([
            'store_category_id' => 'required|exists:store_categories,id',
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
            // Social Media
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'tiktok_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'whatsapp_number' => 'nullable|string|max:50',
            'telegram_url' => 'nullable|url|max:255',
            'website_url' => 'nullable|url|max:255',
        ]);

        $storeCategoryId = (int) $validated['store_category_id'];

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
        try {
            $store = DB::transaction(function () use (
                $user,
                $storeCategoryId,
                $uniqueSlug,
                $logoPath,
                $bannerPath,
                $ssmDocPath,
                $icDocPath,
                $validated
            ) {
                return Store::create([
                    'user_id' => $user->id,
                    'store_category_id' => $storeCategoryId,
                    'name' => $validated['name'],
                    'phone' => $validated['phone'],
                    'email' => $validated['email'],
                    'slug' => $uniqueSlug,
                    'location' => $validated['location'] ?? null,
                    'description' => $validated['description'] ?? null,
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                    'image' => $logoPath,
                    'banner' => $bannerPath,
                    'ssm_document' => $ssmDocPath,
                    'ic_document' => $icDocPath,
                    'business_registration_number' => $validated['business_registration_number'] ?? null,
                    'business_registration_no' => $validated['business_registration_no'] ?? null,
                    'tax_id' => $validated['tax_id'] ?? null,
                    'status' => 'active',
                    'is_active' => true,
                    // Social Media
                    'facebook_url' => $validated['facebook_url'] ?? null,
                    'instagram_url' => $validated['instagram_url'] ?? null,
                    'twitter_url' => $validated['twitter_url'] ?? null,
                    'tiktok_url' => $validated['tiktok_url'] ?? null,
                    'youtube_url' => $validated['youtube_url'] ?? null,
                    'whatsapp_number' => $validated['whatsapp_number'] ?? null,
                    'telegram_url' => $validated['telegram_url'] ?? null,
                    'website_url' => $validated['website_url'] ?? null,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Store creation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create your store. Please try again.');
        }

        // Clear session
        session()->forget('selected_store_category');

        // Notify subscription system
        $this->subscriptionService->notifyEvent('store_created', [
            'user_id' => $user->subscription_user_id,
            'store_id' => $store->id,
            'store_name' => $store->name,
        ]);

        // Get store category to determine redirect
        // Redirect to add first product
        return redirect()->route('vendor.products.create')
            ->with('success', 'Store created successfully! Now add your first product.');
    }

    /**
     * Homepage showing stores with map (full version)
     */
    public function home(Request $request)
    {
        $query = Store::with(['category'])
            ->withCount(['products' => function ($q) {
                $q->where('status', 'published');
            }])
            ->where('is_active', true)
            ->where('status', 'active');

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%")
                    ->orWhere('location', 'like', "%{$searchTerm}%")
                    ->orWhereHas('products', function ($productQuery) use ($searchTerm) {
                        $productQuery->where('name', 'like', "%{$searchTerm}%")
                            ->orWhere('description', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('store_category_id', $request->category);
        }

        // Location filter
        if ($request->filled('location')) {
            $query->where('location', 'like', "%{$request->location}%");
        }

        // Verified filter
        if ($request->filled('verified') && $request->verified == '1') {
            $query->where('is_verified', true);
        }

        // Sorting
        $sortBy = $request->get('sort', 'name');
        switch ($sortBy) {
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'popular':
                $query->orderBy('products_count', 'desc');
                break;
            default:
                $query->orderBy('name');
        }

        $stores = $query->paginate(12)->appends($request->except('page'));

        // Get categories for filter dropdown with store counts
        $categories = \App\Models\StoreCategory::withCount(['stores' => function ($q) {
            $q->where('is_active', true)->where('status', 'active');
        }])->orderBy('name')->get();

        return view('store.stores', compact('stores', 'categories'));
    }

    /**
     * Show all stores
     */
    public function stores(Request $request)
    {
        $query = Store::with(['category'])
            ->withCount(['products' => function ($q) {
                $q->where('status', 'published');
            }])
            ->where('is_active', true)
            ->where('status', 'active');

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%")
                    ->orWhere('location', 'like', "%{$searchTerm}%")
                    ->orWhereHas('products', function ($productQuery) use ($searchTerm) {
                        $productQuery->where('name', 'like', "%{$searchTerm}%")
                            ->orWhere('description', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('store_category_id', $request->category);
        }

        // Location filter
        if ($request->filled('location')) {
            $query->where('location', 'like', "%{$request->location}%");
        }

        // Verified filter
        if ($request->filled('verified') && $request->verified == '1') {
            $query->where('is_verified', true);
        }

        // Sorting
        $sortBy = $request->get('sort', 'name');
        switch ($sortBy) {
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'popular':
                $query->orderBy('products_count', 'desc');
                break;
            case 'rating':
                // Placeholder for when rating system is implemented
                $query->orderBy('name');
                break;
            default:
                $query->orderBy('name');
        }

        $stores = $query->paginate(12)->appends($request->except('page'));

        // Get categories for filter dropdown with store counts
        $categories = \App\Models\StoreCategory::withCount(['stores' => function ($q) {
            $q->where('is_active', true)->where('status', 'active');
        }])->orderBy('name')->get();

        return view('store.stores', compact('stores', 'categories'));
    }

    /**
     * Show stores page (simple version without map - for /stores route)
     */
    public function storesSimple(Request $request)
    {
        $query = Store::with(['category'])
            ->withCount(['products' => function ($q) {
                $q->where('status', 'published');
            }])
            ->where('is_active', true)
            ->where('status', 'active');

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%")
                    ->orWhere('location', 'like', "%{$searchTerm}%")
                    ->orWhereHas('products', function ($productQuery) use ($searchTerm) {
                        $productQuery->where('name', 'like', "%{$searchTerm}%")
                            ->orWhere('description', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('store_category_id', $request->category);
        }

        // Sorting
        $sortBy = $request->get('sort', 'name');
        switch ($sortBy) {
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'popular':
                $query->orderBy('products_count', 'desc');
                break;
            default:
                $query->orderBy('name');
        }

        $stores = $query->paginate(12)->appends($request->except('page'));

        // Get categories for filter dropdown with store counts
        $categories = \App\Models\StoreCategory::withCount(['stores' => function ($q) {
            $q->where('is_active', true)->where('status', 'active');
        }])->orderBy('name')->get();

        return view('store.stores-simple', compact('stores', 'categories'));
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
            // Social Media
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'tiktok_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'whatsapp_number' => 'nullable|string|max:50',
            'telegram_url' => 'nullable|url|max:255',
            'website_url' => 'nullable|url|max:255',
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
            // Social Media
            'facebook_url' => $request->facebook_url,
            'instagram_url' => $request->instagram_url,
            'twitter_url' => $request->twitter_url,
            'tiktok_url' => $request->tiktok_url,
            'youtube_url' => $request->youtube_url,
            'whatsapp_number' => $request->whatsapp_number,
            'telegram_url' => $request->telegram_url,
            'website_url' => $request->website_url,
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
