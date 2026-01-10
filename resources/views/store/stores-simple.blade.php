@extends('partials.app')

@section('title', 'Stores - RizqMall')

@section('content')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .stores-hero {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        padding: 60px 0 80px;
        position: relative;
        overflow: hidden;
        margin-bottom: -40px;
    }

    .stores-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.03' fill-rule='evenodd'/%3E%3C/svg%3E");
    }

    .stores-hero::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(102, 126, 234, 0.15) 0%, transparent 50%);
        animation: pulse-glow 8s ease-in-out infinite;
    }

    @keyframes pulse-glow {
        0%, 100% { transform: translate(0, 0) scale(1); opacity: 0.5; }
        50% { transform: translate(10%, 10%) scale(1.1); opacity: 0.8; }
    }

    .stores-hero-content {
        position: relative;
        z-index: 2;
        color: white;
        text-align: center;
        max-width: 800px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 8px 20px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 600;
        color: #a5b4fc;
        margin-bottom: 20px;
    }

    .hero-badge i { color: #fbbf24; }

    .stores-hero h1 {
        font-size: 48px;
        font-weight: 800;
        margin-bottom: 16px;
        background: linear-gradient(135deg, #ffffff 0%, #a5b4fc 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        line-height: 1.2;
    }

    .stores-hero p {
        font-size: 18px;
        opacity: 0.85;
        margin-bottom: 32px;
        line-height: 1.6;
        color: #cbd5e1;
    }

    .hero-search-box {
        max-width: 600px;
        margin: 0 auto;
        position: relative;
    }

    .hero-search-input {
        width: 100%;
        padding: 18px 140px 18px 55px;
        border-radius: 60px;
        border: 2px solid rgba(255, 255, 255, 0.2);
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        font-size: 16px;
        font-weight: 500;
        color: white;
        transition: all 0.3s;
    }

    .hero-search-input::placeholder { color: rgba(255, 255, 255, 0.6); }

    .hero-search-input:focus {
        outline: none;
        border-color: #667eea;
        background: rgba(255, 255, 255, 0.15);
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.2);
    }

    .hero-search-icon {
        position: absolute;
        left: 22px;
        top: 50%;
        transform: translateY(-50%);
        color: rgba(255, 255, 255, 0.6);
        font-size: 20px;
    }

    .hero-search-btn {
        position: absolute;
        right: 6px;
        top: 6px;
        height: calc(100% - 12px);
        padding: 0 32px;
        background: var(--primary-gradient);
        border: none;
        border-radius: 50px;
        color: white;
        font-weight: 700;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .hero-search-btn:hover {
        transform: scale(1.02);
        box-shadow: 0 4px 20px rgba(102, 126, 234, 0.5);
    }

    .category-directory {
        background: white;
        border-radius: 24px;
        padding: 32px;
        margin-bottom: 30px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(0, 0, 0, 0.05);
        position: relative;
        z-index: 10;
    }

    .directory-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 28px;
    }

    .directory-title {
        font-size: 22px;
        font-weight: 800;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0;
    }

    .directory-title i { color: #667eea; font-size: 26px; }

    .scroll-hint {
        font-size: 13px;
        color: #9ca3af;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .category-grid-wrapper { position: relative; }

    .category-grid {
        display: flex;
        gap: 20px;
        overflow-x: auto;
        overflow-y: visible;
        padding: 16px 4px 24px 4px;
        margin: -16px -4px -24px -4px;
        scroll-snap-type: x mandatory;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #667eea #e5e7eb;
        cursor: grab;
    }

    .category-grid:active { cursor: grabbing; }
    .category-grid::-webkit-scrollbar { height: 8px; }
    .category-grid::-webkit-scrollbar-track { background: #e5e7eb; border-radius: 10px; }
    .category-grid::-webkit-scrollbar-thumb { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; }

    .category-card {
        flex: 0 0 auto;
        width: 160px;
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 20px;
        padding: 0;
        text-align: center;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        scroll-snap-align: start;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .category-card:hover {
        transform: translateY(-8px);
        border-color: #667eea;
        box-shadow: 0 16px 40px rgba(102, 126, 234, 0.2);
    }

    .category-card.active {
        border-color: #667eea;
        box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
    }

    .category-card.active .category-info { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .category-card.active .category-name { color: white; }
    .category-card.active .category-count { color: rgba(255, 255, 255, 0.9); background: rgba(255, 255, 255, 0.2); }

    .category-image { width: 100%; height: 100px; object-fit: cover; transition: transform 0.3s ease; }
    .category-card:hover .category-image { transform: scale(1.08); }

    .category-image-wrapper {
        overflow: hidden;
        height: 100px;
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    }

    .category-info { padding: 14px 12px; background: #f9fafb; transition: all 0.3s ease; }

    .category-name { font-size: 13px; font-weight: 700; color: #374151; margin-bottom: 6px; line-height: 1.3; }

    .category-count {
        font-size: 11px;
        color: #6b7280;
        font-weight: 600;
        background: rgba(0, 0, 0, 0.05);
        padding: 4px 12px;
        border-radius: 20px;
        display: inline-block;
    }

    .category-grid-wrapper::after {
        content: '';
        position: absolute;
        right: 0;
        top: 0;
        bottom: 0;
        width: 60px;
        background: linear-gradient(to left, white 30%, transparent);
        pointer-events: none;
        z-index: 1;
    }

    @media (min-width: 1200px) {
        .category-card { width: 170px; }
        .category-image-wrapper, .category-image { height: 110px; }
        .category-name { font-size: 14px; }
    }

    @media (max-width: 767px) {
        .stores-hero { padding: 40px 0 60px; }
        .stores-hero h1 { font-size: 28px; }
        .stores-hero p { font-size: 15px; margin-bottom: 24px; }
        .hero-search-input { padding: 14px 120px 14px 45px; font-size: 14px; }
        .hero-search-btn { padding: 0 20px; font-size: 13px; }
        .hero-search-btn span { display: none; }
        .category-directory { padding: 20px 16px; border-radius: 16px; }
        .category-grid { gap: 14px; }
        .category-card { width: 130px; border-radius: 14px; }
        .category-image-wrapper, .category-image { height: 80px; }
        .category-info { padding: 10px 8px; }
        .category-name { font-size: 11px; }
        .category-count { font-size: 10px; padding: 3px 8px; }
        .directory-title { font-size: 18px; }
    }

    @media (max-width: 400px) {
        .category-card { width: 115px; }
        .category-image-wrapper, .category-image { height: 70px; }
    }

    .stores-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .stores-count {
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .stores-count .count-badge {
        background: var(--primary-gradient);
        color: white;
        padding: 4px 14px;
        border-radius: 50px;
        font-size: 14px;
    }

    .view-toggle { display: flex; gap: 6px; background: #f3f4f6; padding: 4px; border-radius: 10px; }

    .view-btn {
        width: 38px;
        height: 38px;
        border: none;
        background: transparent;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        color: #6b7280;
    }

    .view-btn:hover { color: #667eea; }
    .view-btn.active { background: white; color: #667eea; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); }

    .active-filters-bar { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin-bottom: 20px; }

    .filter-tag {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        background: #dbeafe;
        border: 1px solid #93c5fd;
        border-radius: 50px;
        font-size: 13px;
        color: #1e40af;
        font-weight: 600;
    }

    .filter-tag .remove-filter {
        color: #1e40af;
        text-decoration: none;
        font-size: 18px;
        line-height: 1;
        font-weight: 700;
        transition: color 0.2s;
        cursor: pointer;
    }

    .filter-tag .remove-filter:hover { color: #1e3a8a; }

    .clear-all-filters {
        font-size: 13px;
        font-weight: 600;
        color: #ef4444;
        text-decoration: none;
        padding: 6px 14px;
        border-radius: 50px;
        transition: all 0.2s;
    }

    .clear-all-filters:hover { background: #fee2e2; color: #dc2626; }

    .store-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
        display: flex;
        flex-direction: column;
        border: 1px solid rgba(0, 0, 0, 0.08);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .store-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
        border-color: rgba(102, 126, 234, 0.3);
    }

    .store-banner {
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        padding: 30px 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 160px;
        position: relative;
    }

    .store-logo {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        object-fit: cover;
        background: white;
        border: 4px solid white;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
    }

    .store-logo-placeholder {
        width: 90px;
        height: 90px;
        background: var(--primary-gradient);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        font-weight: 800;
        color: white;
        box-shadow: 0 6px 16px rgba(102, 126, 234, 0.3);
        border: 4px solid white;
    }

    .verified-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        background: var(--success-gradient);
        color: white;
        padding: 5px 12px;
        border-radius: 50px;
        font-size: 11px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 4px;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
    }

    .store-body { padding: 18px; flex: 1; display: flex; flex-direction: column; }

    .store-category-tag {
        font-size: 11px;
        font-weight: 700;
        color: #667eea;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }

    .store-name {
        font-size: 16px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 8px;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .store-location { font-size: 12px; color: #9ca3af; margin-bottom: 14px; display: flex; align-items: center; gap: 5px; }

    .store-stats { display: flex; gap: 16px; margin-bottom: 16px; padding-top: 14px; border-top: 1px solid #f3f4f6; }

    .stat-item { display: flex; flex-direction: column; gap: 2px; }
    .stat-value { font-size: 16px; font-weight: 800; color: #667eea; }
    .stat-label { font-size: 10px; color: #9ca3af; font-weight: 600; text-transform: uppercase; }

    .store-actions { margin-top: auto; }

    .visit-store-btn {
        width: 100%;
        padding: 12px;
        background: var(--primary-gradient);
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 700;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .visit-store-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        color: white;
    }

    .store-card.list-view { flex-direction: row; }
    .store-card.list-view .store-banner { min-width: 180px; min-height: auto; }
    .store-card.list-view .store-body { flex-direction: row; align-items: center; gap: 20px; }
    .store-card.list-view .store-stats { border-top: none; border-left: 1px solid #f3f4f6; padding-left: 20px; margin-bottom: 0; }

    .empty-state {
        text-align: center;
        padding: 80px 20px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
    }

    .empty-icon { font-size: 72px; color: #d1d5db; margin-bottom: 24px; }
    .empty-state h4 { font-size: 22px; font-weight: 700; color: #6b7280; margin-bottom: 10px; }
    .empty-state p { color: #9ca3af; font-size: 15px; margin-bottom: 24px; }

    @media (max-width: 991px) {
        .store-card.list-view { flex-direction: column; }
        .store-card.list-view .store-body { flex-direction: column; align-items: stretch; }
        .store-card.list-view .store-stats { border-left: none; border-top: 1px solid #f3f4f6; padding-left: 0; padding-top: 12px; }
    }

    @media (max-width: 575px) {
        .stores-header { justify-content: center; }
        .stores-count { width: 100%; justify-content: center; }
    }
</style>

<section style="background-color: #f5f5f5; min-height: 100vh;">
    <div class="stores-hero">
        <div class="stores-hero-content">
            <div class="hero-badge">
                <i class="fas fa-store"></i>
                Browse All Stores
            </div>
            <h1>All Stores</h1>
            <p>Find products, services, and everything you need from our verified sellers.</p>

            <form action="{{ route('stores') }}" method="GET" class="hero-search-box">
                <i class="fas fa-search hero-search-icon"></i>
                <input type="text" name="search" class="hero-search-input"
                    placeholder="Search stores, products, or services..."
                    value="{{ request('search') }}">
                @if(request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <button type="submit" class="hero-search-btn">
                    <i class="fas fa-search"></i>
                    <span>Search</span>
                </button>
            </form>
        </div>
    </div>

    <div class="container-fluid px-3 px-md-4">
        <div class="category-directory">
            <div class="directory-header">
                <h2 class="directory-title">
                    <i class="fas fa-th-large"></i>
                    Browse Categories
                </h2>
                <div class="scroll-hint d-none d-md-flex">
                    <i class="fas fa-arrows-alt-h"></i>
                    Scroll to explore
                </div>
            </div>

            <div class="category-grid-wrapper">
                <div class="category-grid" id="categoryGrid">
                    <a href="{{ route('stores') }}" class="category-card {{ !request('category') ? 'active' : '' }}">
                        <div class="category-image-wrapper">
                            <img src="{{ asset('directory/rmarketplace.jpeg') }}" alt="All Stores" class="category-image">
                        </div>
                        <div class="category-info">
                            <div class="category-name">All Stores</div>
                            <div class="category-count">{{ $stores->total() }} stores</div>
                        </div>
                    </a>

                    @foreach($categories as $category)
                    @php
                        $imageMap = [
                            'Marketplace' => 'rmarketplace.jpeg',
                            'Services' => 'rservices.jpeg',
                            'Pharmacy' => 'rhealthcare.jpeg',
                            'Booking & Rent' => 'rbooking.jpeg',
                            'Premises' => 'rpremises.jpeg',
                            'Contractors' => 'rcontractors.jpeg',
                            'Food & Catering' => 'rfood.jpeg',
                            'Hardware' => 'rhardware.jpeg',
                            'Delivery' => 'rdelivery.jpeg',
                            'Taxi & Rent' => 'rmobility.jpeg',
                        ];
                        $imageName = $imageMap[$category->name] ?? 'rmarketplace.jpeg';
                    @endphp
                    <a href="{{ route('stores', ['category' => $category->id]) }}"
                        class="category-card {{ request('category') == $category->id ? 'active' : '' }}">
                        <div class="category-image-wrapper">
                            <img src="{{ asset('directory/' . $imageName) }}" alt="{{ $category->name }}" class="category-image">
                        </div>
                        <div class="category-info">
                            <div class="category-name">{{ $category->name }}</div>
                            <div class="category-count">{{ $category->stores_count }} stores</div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>

        @if(request()->hasAny(['search', 'category']))
        <div class="active-filters-bar">
            @if(request('search'))
            <span class="filter-tag">
                <i class="fas fa-search"></i>
                "{{ request('search') }}"
                <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="remove-filter">×</a>
            </span>
            @endif

            @if(request('category'))
            @php $selectedCategory = $categories->find(request('category')); @endphp
            <span class="filter-tag">
                <i class="fas fa-folder"></i>
                {{ $selectedCategory->name ?? 'Category' }}
                <a href="{{ request()->fullUrlWithQuery(['category' => null]) }}" class="remove-filter">×</a>
            </span>
            @endif

            <a href="{{ route('stores') }}" class="clear-all-filters">
                <i class="fas fa-times-circle me-1"></i> Clear All
            </a>
        </div>
        @endif

        <div class="stores-header">
            <div class="stores-count">
                <i class="fas fa-store"></i>
                <span class="count-badge">{{ $stores->total() }}</span>
                Stores Found
            </div>
            <div class="view-toggle">
                <button class="view-btn active" data-view="grid" onclick="changeView('grid')">
                    <i class="fas fa-th"></i>
                </button>
                <button class="view-btn" data-view="list" onclick="changeView('list')">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>

        <div class="row g-3 g-md-4" id="storesGrid">
            @forelse($stores as $store)
            <div class="col-6 col-sm-6 col-md-4 col-lg-3 col-xl-2-4 store-item">
                <div class="store-card">
                    <div class="store-banner">
                        @if($store->image)
                        <img src="{{ asset('storage/' . $store->image) }}" alt="{{ $store->name }}" class="store-logo">
                        @else
                        <div class="store-logo-placeholder">{{ substr($store->name, 0, 1) }}</div>
                        @endif

                        @if($store->is_verified)
                        <div class="verified-badge">
                            <i class="fas fa-check-circle"></i> Verified
                        </div>
                        @endif
                    </div>

                    <div class="store-body">
                        <div class="store-category-tag">{{ $store->category->name ?? 'Store' }}</div>
                        <h5 class="store-name">{{ $store->name }}</h5>
                        <div class="store-location">
                            <i class="fas fa-map-marker-alt"></i>
                            {{ $store->location ?? 'Malaysia' }}
                        </div>

                        <div class="store-stats">
                            <div class="stat-item">
                                <span class="stat-value">{{ $store->products_count ?? 0 }}</span>
                                <span class="stat-label">Products</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-value">4.8</span>
                                <span class="stat-label">Rating</span>
                            </div>
                        </div>

                        <div class="store-actions">
                            <a href="{{ route('store.profile', $store->slug) }}" class="visit-store-btn">
                                <i class="fas fa-store"></i>
                                Visit Store
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-store-slash"></i></div>
                    <h4>No stores found</h4>
                    <p>Try adjusting your search or browse a different category</p>
                    @if(request()->hasAny(['search', 'category']))
                    <a href="{{ route('stores') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-redo me-2"></i>Browse All Stores
                    </a>
                    @endif
                </div>
            </div>
            @endforelse
        </div>

        @if($stores->hasPages())
        <div class="d-flex justify-content-center mt-4 pb-4">
            {{ $stores->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</section>

@endsection

@push('scripts')
<script>
    function changeView(view) {
        const items = document.querySelectorAll('.store-item');
        const buttons = document.querySelectorAll('.view-btn');

        buttons.forEach(btn => btn.classList.remove('active'));
        event.target.closest('.view-btn').classList.add('active');

        if (view === 'list') {
            items.forEach(item => {
                item.className = 'col-12 store-item';
                item.querySelector('.store-card').classList.add('list-view');
            });
        } else {
            items.forEach(item => {
                item.className = 'col-6 col-sm-6 col-md-4 col-lg-3 col-xl-2-4 store-item';
                item.querySelector('.store-card').classList.remove('list-view');
            });
        }
    }

    const slider = document.getElementById('categoryGrid');
    let isDown = false;
    let startX;
    let scrollLeft;

    if (slider) {
        slider.addEventListener('mousedown', (e) => {
            isDown = true;
            startX = e.pageX - slider.offsetLeft;
            scrollLeft = slider.scrollLeft;
            e.preventDefault();
        });

        slider.addEventListener('mouseleave', () => { isDown = false; });
        slider.addEventListener('mouseup', () => { isDown = false; });

        slider.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - slider.offsetLeft;
            slider.scrollLeft = scrollLeft - (x - startX) * 2;
        });

        slider.addEventListener('wheel', (e) => {
            if (e.deltaY !== 0) {
                e.preventDefault();
                slider.scrollLeft += e.deltaY;
            }
        });
    }
</script>

<style>
    @media (min-width: 1200px) {
        .col-xl-2-4 { flex: 0 0 20%; max-width: 20%; }
    }
</style>
@endpush
