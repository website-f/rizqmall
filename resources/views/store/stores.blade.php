@extends('partials.app')

@section('title', 'Discover Stores - Rizqmall')

@section('content')
<style>
    /* Modern color palette */
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --orange-gradient: linear-gradient(135deg, #ee4d2d 0%, #ff6b35 100%);
        --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    /* Hero Section - Compact */
    .stores-hero {
        background: var(--primary-gradient);
        padding: 40px 0;
        position: relative;
        overflow: hidden;
        margin-bottom: 30px;
    }

    .stores-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        opacity: 0.3;
    }

    .stores-hero-content {
        position: relative;
        z-index: 1;
        color: white;
        text-align: center;
    }

    .stores-hero h1 {
        font-size: 32px;
        font-weight: 800;
        margin-bottom: 8px;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .stores-hero p {
        font-size: 16px;
        opacity: 0.95;
        margin-bottom: 0;
    }

    /* Category Directory Section - Revamped */
    .category-directory {
        background: white;
        border-radius: 20px;
        padding: 28px;
        margin-bottom: 30px;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(102, 126, 234, 0.1);
    }

    .directory-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 2px solid #f3f4f6;
    }

    .directory-title {
        font-size: 20px;
        font-weight: 800;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0;
    }

    .directory-title i {
        color: #667eea;
        font-size: 24px;
    }

    .view-all-cats {
        font-size: 14px;
        font-weight: 600;
        color: #667eea;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
        padding: 8px 14px;
        border-radius: 8px;
        background: rgba(102, 126, 234, 0.08);
    }

    .view-all-cats:hover {
        color: #764ba2;
        gap: 10px;
        background: rgba(102, 126, 234, 0.15);
    }

    .category-grid-wrapper {
        position: relative;
    }

    /* Category Grid - Horizontal Scroll on ALL screens */
    .category-grid {
        display: flex;
        gap: 16px;
        overflow-x: auto;
        overflow-y: visible;
        padding: 16px 4px 20px 4px;
        margin: -16px -4px -20px -4px;
        scroll-snap-type: x mandatory;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #667eea #e5e7eb;
        cursor: grab;
    }

    .category-grid:active {
        cursor: grabbing;
    }

    .category-grid::-webkit-scrollbar {
        height: 8px;
    }

    .category-grid::-webkit-scrollbar-track {
        background: #e5e7eb;
        border-radius: 10px;
        margin: 0 4px;
    }

    .category-grid::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
    }

    .category-grid::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #5a6fd6 0%, #6a4190 100%);
    }

    /* Category Card - Clean Simple Design */
    .category-card {
        flex: 0 0 auto;
        width: 140px;
        background: linear-gradient(180deg, #ffffff 0%, #f9fafb 100%);
        border: 2px solid #e5e7eb;
        border-radius: 16px;
        padding: 22px 14px 18px;
        text-align: center;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        scroll-snap-align: start;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
    }

    .category-card:hover {
        transform: translateY(-6px);
        border-color: #667eea;
        box-shadow: 0 12px 32px rgba(102, 126, 234, 0.2);
        background: linear-gradient(180deg, #ffffff 0%, #f0f4ff 100%);
    }

    .category-card.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: #667eea;
        transform: translateY(-4px);
        box-shadow: 0 10px 32px rgba(102, 126, 234, 0.35);
    }

    .category-icon {
        font-size: 38px;
        margin-bottom: 12px;
        line-height: 1;
        transition: transform 0.3s ease;
    }

    .category-card:hover .category-icon {
        transform: scale(1.15);
    }

    .category-name {
        font-size: 13px;
        font-weight: 700;
        color: #374151;
        margin-bottom: 8px;
        line-height: 1.3;
        word-wrap: break-word;
        max-width: 100%;
    }

    .category-card.active .category-name {
        color: white;
    }

    .category-count {
        font-size: 11px;
        color: #6b7280;
        font-weight: 600;
        background: rgba(0, 0, 0, 0.05);
        padding: 4px 12px;
        border-radius: 20px;
        white-space: nowrap;
    }

    .category-card.active .category-count {
        color: rgba(255, 255, 255, 0.9);
        background: rgba(255, 255, 255, 0.2);
    }

    /* Scroll fade indicator */
    .category-grid-wrapper::after {
        content: '';
        position: absolute;
        right: 0;
        top: 0;
        bottom: 0;
        width: 50px;
        background: linear-gradient(to left, white 20%, transparent);
        pointer-events: none;
        z-index: 1;
    }

    /* Responsive Adjustments */
    @media (min-width: 1200px) {
        .category-card {
            width: 150px;
            padding: 24px 16px 20px;
        }

        .category-icon {
            font-size: 42px;
            margin-bottom: 14px;
        }

        .category-name {
            font-size: 14px;
        }
    }

    @media (max-width: 767px) {
        .category-directory {
            padding: 20px 16px;
            border-radius: 16px;
        }

        .category-grid {
            gap: 12px;
            padding: 12px 2px 16px 2px;
            margin: -12px -2px -16px -2px;
        }

        .category-card {
            width: 120px;
            padding: 18px 10px 14px;
            border-radius: 14px;
        }

        .category-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .category-name {
            font-size: 12px;
            margin-bottom: 6px;
        }

        .category-count {
            font-size: 10px;
            padding: 3px 10px;
        }

        .directory-header {
            margin-bottom: 18px;
        }

        .directory-title {
            font-size: 18px;
        }

        .directory-title i {
            font-size: 20px;
        }
    }

    @media (max-width: 400px) {
        .category-directory {
            padding: 16px 12px;
        }

        .category-grid {
            gap: 10px;
        }

        .category-card {
            width: 105px;
            padding: 14px 8px 12px;
            border-radius: 12px;
        }

        .category-icon {
            font-size: 28px;
            margin-bottom: 8px;
        }

        .category-name {
            font-size: 11px;
        }

        .category-count {
            font-size: 9px;
            padding: 2px 8px;
        }

        .category-grid-wrapper::after {
            width: 30px;
        }
    }

    /* Search & Filter - Modern */
    .search-filter-bar {
        background: white;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    }

    .search-box-wrapper {
        position: relative;
    }

    .search-box-wrapper .search-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 18px;
        z-index: 2;
    }

    .search-input-modern {
        padding-left: 52px;
        padding-right: 120px;
        border-radius: 50px;
        border: 2px solid #e5e7eb;
        height: 54px;
        font-size: 15px;
        font-weight: 500;
        transition: all 0.3s;
        background: #f9fafb;
    }

    .search-input-modern:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        background: white;
    }

    .search-btn {
        position: absolute;
        right: 6px;
        top: 6px;
        height: 42px;
        padding: 0 28px;
        background: var(--primary-gradient);
        border: none;
        border-radius: 50px;
        color: white;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s;
        z-index: 2;
    }

    .search-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .filter-pills {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: center;
    }

    .filter-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 50px;
        border: 2px solid #e5e7eb;
        background: white;
        font-size: 14px;
        font-weight: 600;
        color: #6b7280;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .filter-pill:hover {
        border-color: #667eea;
        color: #667eea;
        background: #eff6ff;
    }

    .filter-pill.active {
        background: var(--primary-gradient);
        border-color: #667eea;
        color: white;
    }

    .filter-pill i {
        font-size: 16px;
    }

    /* Active Filters Display */
    .active-filters-bar {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
        margin-bottom: 20px;
    }

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

    .filter-tag .remove-filter:hover {
        color: #1e3a8a;
    }

    .clear-all-filters {
        font-size: 13px;
        font-weight: 600;
        color: #ef4444;
        text-decoration: none;
        padding: 6px 14px;
        border-radius: 50px;
        transition: all 0.2s;
    }

    .clear-all-filters:hover {
        background: #fee2e2;
        color: #dc2626;
    }

    /* Map Section - Sleeker */
    .map-section {
        background: white;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    }

    .map-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .map-title {
        font-size: 18px;
        font-weight: 800;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
    }

    .map-controls {
        display: flex;
        gap: 8px;
    }

    .map-btn {
        padding: 8px 16px;
        border: 2px solid #e5e7eb;
        background: white;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s;
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .map-btn:hover {
        border-color: #667eea;
        color: #667eea;
    }

    .map-btn.active {
        background: var(--primary-gradient);
        border-color: #667eea;
        color: white;
    }

    #storeMap {
        height: 400px;
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid #e5e7eb;
    }

    /* Stores Grid Header */
    .stores-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
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
        padding: 4px 12px;
        border-radius: 50px;
        font-size: 14px;
    }

    .view-toggle {
        display: flex;
        gap: 6px;
        background: #f3f4f6;
        padding: 4px;
        border-radius: 10px;
    }

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

    .view-btn:hover {
        color: #667eea;
    }

    .view-btn.active {
        background: white;
        color: #667eea;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    /* Store Cards - Modern Shopee-like */
    .store-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
        display: flex;
        flex-direction: column;
        border: 1px solid rgba(0, 0, 0, 0.09);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .store-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 6px 24px rgba(0, 0, 0, 0.12);
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
        border: 3px solid white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
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
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        border: 3px solid white;
    }

    .verified-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        background: var(--success-gradient);
        color: white;
        padding: 5px 10px;
        border-radius: 50px;
        font-size: 11px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 4px;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
    }

    .store-body {
        padding: 16px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

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

    .store-location {
        font-size: 12px;
        color: #9ca3af;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .store-stats {
        display: flex;
        gap: 16px;
        margin-bottom: 14px;
        padding-top: 12px;
        border-top: 1px solid #f3f4f6;
    }

    .stat-item {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .stat-value {
        font-size: 16px;
        font-weight: 800;
        color: #667eea;
    }

    .stat-label {
        font-size: 10px;
        color: #9ca3af;
        font-weight: 600;
        text-transform: uppercase;
    }

    .store-actions {
        margin-top: auto;
    }

    .visit-store-btn {
        width: 100%;
        padding: 10px;
        background: var(--primary-gradient);
        color: white;
        border: none;
        border-radius: 8px;
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
        box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
        color: white;
    }

    /* List View */
    .store-card.list-view {
        flex-direction: row;
    }

    .store-card.list-view .store-banner {
        min-width: 180px;
        min-height: auto;
    }

    .store-card.list-view .store-body {
        flex-direction: row;
        align-items: center;
        gap: 20px;
    }

    .store-card.list-view .store-stats {
        border-top: none;
        border-left: 1px solid #f3f4f6;
        padding-left: 20px;
        margin-bottom: 0;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    }

    .empty-icon {
        font-size: 64px;
        color: #d1d5db;
        margin-bottom: 20px;
    }

    .empty-state h4 {
        font-size: 20px;
        font-weight: 700;
        color: #6b7280;
        margin-bottom: 8px;
    }

    .empty-state p {
        color: #9ca3af;
        font-size: 14px;
        margin-bottom: 20px;
    }

    /* Responsive - Non-Category Styles */
    @media (max-width: 991px) {
        .stores-hero h1 {
            font-size: 24px;
        }

        .store-card.list-view {
            flex-direction: column;
        }

        .store-card.list-view .store-body {
            flex-direction: column;
            align-items: stretch;
        }

        .store-card.list-view .store-stats {
            border-left: none;
            border-top: 1px solid #f3f4f6;
            padding-left: 0;
            padding-top: 12px;
        }
    }

    @media (max-width: 575px) {
        #storeMap {
            height: 280px;
        }

        .filter-pills {
            justify-content: center;
        }

        .search-input-modern {
            padding-right: 52px;
        }

        .search-btn {
            padding: 0 20px;
        }

        .stores-hero {
            padding: 30px 0;
            margin-bottom: 20px;
        }

        .stores-hero h1 {
            font-size: 20px;
        }

        .stores-hero p {
            font-size: 14px;
        }
    }
</style>

<section class="py-4" style="background-color: #f5f5f5;">
    <div class="container-fluid px-3 px-md-4">
        {{-- Hero Section --}}
        <div class="stores-hero">
            <div class="stores-hero-content">
                <h1><i class="fas fa-store"></i> Discover Amazing Stores</h1>
                <p>Browse verified sellers and local businesses near you</p>
            </div>
        </div>

        {{-- Category Directory - Featured Section --}}
        <div class="category-directory">
            <div class="directory-header">
                <h2 class="directory-title">
                    <i class="fas fa-th-large"></i>
                    Shop by Category
                </h2>
                <!-- Scroll indicator hint could go here if needed, or just leave empty -->
                <div class="text-muted small d-none d-md-block" style="font-size: 13px;">
                    <i class="fas fa-arrows-alt-h me-1"></i> Scroll to see more
                </div>
            </div>

            <div class="category-grid-wrapper">
                <div class="category-grid" id="categoryGrid">
                    {{-- All Categories Option --}}
                    <a href="{{ route('stores') }}"
                        class="category-card {{ !request('category') ? 'active' : '' }}">
                        <div class="category-icon">🛍️</div>
                        <div class="category-name">All Stores</div>
                        <div class="category-count">{{ $stores->total() }} stores</div>
                    </a>

                    @foreach($categories as $category)
                    <a href="{{ route('stores', ['category' => $category->id]) }}"
                        class="category-card {{ request('category') == $category->id ? 'active' : '' }}">
                        <div class="category-icon">
                            @switch($category->name)
                            @case('Electronics')
                            📱
                            @break
                            @case('Fashion')
                            👗
                            @break
                            @case('Food & Beverages')
                            🍔
                            @break
                            @case('Home & Living')
                            🏠
                            @break
                            @case('Health & Beauty')
                            💄
                            @break
                            @case('Sports & Outdoors')
                            ⚽
                            @break
                            @case('Books & Stationery')
                            📚
                            @break
                            @case('Toys & Kids')
                            🧸
                            @break
                            @case('Automotive')
                            🚗
                            @break
                            @case('Services')
                            🔧
                            @break
                            @case('Food & Catering')
                            🍽️
                            @break
                            @default
                            🏪
                            @endswitch
                        </div>
                        <div class="category-name">{{ $category->name }}</div>
                        <div class="category-count">{{ $category->stores_count ?? 0 }} stores</div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Search & Filter Bar --}}
        <div class="search-filter-bar">
            <form action="{{ route('stores') }}" method="GET" id="searchForm">
                <div class="row g-3">
                    <div class="col-12 col-md-8">
                        <div class="search-box-wrapper">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text"
                                name="search"
                                class="form-control search-input-modern"
                                placeholder="Search stores, products, or locations..."
                                value="{{ request('search') }}">
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search me-1"></i> Search
                            </button>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="filter-pills">
                            <label class="filter-pill {{ request('verified') ? 'active' : '' }}">
                                <input type="checkbox"
                                    name="verified"
                                    value="1"
                                    {{ request('verified') ? 'checked' : '' }}
                                    onchange="this.form.submit()"
                                    style="display: none;">
                                <i class="fas fa-shield-alt"></i>
                                Verified Only
                            </label>

                            <select name="sort" class="form-select filter-pill" onchange="this.form.submit()" style="width: auto; border-radius: 50px;">
                                <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>A-Z</option>
                                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest</option>
                                <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Popular</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Hidden category input --}}
                @if(request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
            </form>

            {{-- Active Filters --}}
            @if(request()->hasAny(['search', 'category', 'verified']))
            <div class="active-filters-bar mt-3">
                @if(request('search'))
                <span class="filter-tag">
                    Search: "{{ request('search') }}"
                    <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="remove-filter">×</a>
                </span>
                @endif

                @if(request('category'))
                @php
                $selectedCategory = $categories->find(request('category'));
                @endphp
                <span class="filter-tag">
                    {{ $selectedCategory->name ?? 'Category' }}
                    <a href="{{ request()->fullUrlWithQuery(['category' => null]) }}" class="remove-filter">×</a>
                </span>
                @endif

                @if(request('verified'))
                <span class="filter-tag">
                    Verified Stores
                    <a href="{{ request()->fullUrlWithQuery(['verified' => null]) }}" class="remove-filter">×</a>
                </span>
                @endif

                <a href="{{ route('stores') }}" class="clear-all-filters">
                    <i class="fas fa-times-circle me-1"></i> Clear All
                </a>
            </div>
            @endif
        </div>

        {{-- Map Section --}}
        <div class="map-section" id="mapSection">
            <div class="map-header">
                <h3 class="map-title">
                    <i class="fas fa-map-marked-alt text-primary"></i>
                    Store Locations
                </h3>
                <div class="map-controls">
                    <button class="map-btn" id="nearbyBtn">
                        <i class="fas fa-location-arrow"></i> Nearby
                    </button>
                    <button class="map-btn active" id="toggleMapBtn" onclick="toggleMap()">
                        <i class="fas fa-eye"></i> <span id="mapToggleText">Hide</span>
                    </button>
                </div>
            </div>
            <div id="storeMap"></div>
        </div>

        {{-- Stores Grid Header --}}
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

        {{-- Stores Grid --}}
        <div class="row g-3 g-md-4" id="storesGrid">
            @forelse($stores as $store)
            <div class="col-6 col-sm-6 col-md-4 col-lg-3 col-xl-2-4 store-item">
                <div class="store-card">
                    <div class="store-banner">
                        @if($store->image)
                        <img src="{{ asset('storage/' . $store->image) }}"
                            alt="{{ $store->name }}"
                            class="store-logo">
                        @else
                        <div class="store-logo-placeholder">
                            {{ substr($store->name, 0, 1) }}
                        </div>
                        @endif

                        @if($store->is_verified)
                        <div class="verified-badge">
                            <i class="fas fa-check-circle"></i> Verified
                        </div>
                        @endif
                    </div>

                    <div class="store-body">
                        <div class="store-category-tag">
                            {{ $store->category->name ?? 'Store' }}
                        </div>
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
                    <div class="empty-icon">
                        <i class="fas fa-store-slash"></i>
                    </div>
                    <h4>No stores found</h4>
                    <p>Try adjusting your filters or search criteria</p>
                    @if(request()->hasAny(['search', 'category', 'verified']))
                    <a href="{{ route('stores') }}" class="btn btn-primary">
                        <i class="fas fa-redo me-2"></i>Clear Filters
                    </a>
                    @endif
                </div>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($stores->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $stores->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</section>

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<script>
    let map;
    let markersLayer;
    let userMarker;

    document.addEventListener('DOMContentLoaded', () => {
        initMap();
    });

    function initMap() {
        // Initialize map centered on Malaysia
        map = L.map('storeMap').setView([3.139, 101.6869], 11);

        // Add tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        // Create markers layer
        markersLayer = L.layerGroup().addTo(map);

        // Add store markers
        const stores = @json($stores -> items());

        if (stores.length > 0) {
            const bounds = [];

            stores.forEach(store => {
                if (store.latitude && store.longitude) {
                    const marker = L.marker([store.latitude, store.longitude])
                        .addTo(markersLayer)
                        .bindPopup(`
                            <div style="text-align: center; min-width: 180px;">
                                <strong style="font-size: 14px;">${store.name}</strong><br>
                                <small style="color: #666;">${store.location || ''}</small><br>
                                <a href="/stores/${store.slug}" class="btn btn-sm btn-primary mt-2" style="text-decoration: none;">
                                    Visit Store
                                </a>
                            </div>
                        `);

                    bounds.push([store.latitude, store.longitude]);
                }
            });

            // Fit map to show all markers
            if (bounds.length > 0) {
                map.fitBounds(bounds, {
                    padding: [50, 50]
                });
            }
        }

        // Nearby button functionality
        const nearbyBtn = document.getElementById('nearbyBtn');
        let watchId = null;
        let locationFound = false;

        nearbyBtn.addEventListener('click', function() {
            const btn = this;

            if (!navigator.geolocation) {
                showLocationError('Geolocation is not supported by your browser');
                return;
            }

            // Reset state
            locationFound = false;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Locating...';
            btn.disabled = true;

            // Clear any existing watch
            if (watchId !== null) {
                navigator.geolocation.clearWatch(watchId);
            }

            // Use watchPosition for more reliable location acquisition
            // This will keep trying until we get a position or timeout
            const timeoutId = setTimeout(() => {
                if (!locationFound) {
                    navigator.geolocation.clearWatch(watchId);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-location-arrow"></i> Nearby';
                    showLocationError('Unable to detect your location. Please ensure location services are enabled and try again.');
                }
            }, 15000); // 15 second overall timeout

            watchId = navigator.geolocation.watchPosition(
                // Success callback
                (pos) => {
                    if (locationFound) return; // Prevent multiple triggers
                    locationFound = true;

                    clearTimeout(timeoutId);
                    navigator.geolocation.clearWatch(watchId);

                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;

                    map.setView([lat, lng], 13);

                    if (userMarker) {
                        map.removeLayer(userMarker);
                    }

                    userMarker = L.circleMarker([lat, lng], {
                        radius: 12,
                        fillColor: "#3b82f6",
                        color: "#fff",
                        weight: 3,
                        opacity: 1,
                        fillOpacity: 0.9
                    }).addTo(map).bindPopup("<b>📍 You are here</b>").openPopup();

                    btn.innerHTML = '<i class="fas fa-check-circle"></i> Located!';
                    btn.classList.add('active');
                    btn.disabled = false;

                    // Reset button after 3 seconds
                    setTimeout(() => {
                        btn.innerHTML = '<i class="fas fa-location-arrow"></i> Nearby';
                    }, 3000);
                },
                // Error callback - only show error for permission denied
                (error) => {
                    if (error.code === error.PERMISSION_DENIED) {
                        clearTimeout(timeoutId);
                        navigator.geolocation.clearWatch(watchId);
                        locationFound = true; // Prevent further processing
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-location-arrow"></i> Nearby';
                        showLocationError('Location access was denied. Please enable location permissions in your browser settings.');
                    }
                    // For other errors (POSITION_UNAVAILABLE, TIMEOUT), let watchPosition keep trying
                    // The overall timeout will handle the failure case
                    console.log('Location attempt error (will retry):', error.message);
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        });

        function showLocationError(message) {
            // Create a nicer toast-style notification instead of alert
            const existingToast = document.querySelector('.location-toast');
            if (existingToast) existingToast.remove();

            const toast = document.createElement('div');
            toast.className = 'location-toast';
            toast.innerHTML = `
                <div style="
                    position: fixed;
                    bottom: 20px;
                    left: 50%;
                    transform: translateX(-50%);
                    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
                    color: white;
                    padding: 16px 24px;
                    border-radius: 12px;
                    box-shadow: 0 8px 24px rgba(239, 68, 68, 0.4);
                    font-weight: 600;
                    font-size: 14px;
                    z-index: 10000;
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    max-width: 90%;
                    animation: slideUp 0.3s ease;
                ">
                    <i class="fas fa-exclamation-circle" style="font-size: 18px;"></i>
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" style="
                        background: rgba(255,255,255,0.2);
                        border: none;
                        color: white;
                        padding: 4px 8px;
                        border-radius: 6px;
                        cursor: pointer;
                        margin-left: 8px;
                    ">✕</button>
                </div>
            `;
            document.body.appendChild(toast);

            // Add animation styles
            if (!document.querySelector('#location-toast-styles')) {
                const style = document.createElement('style');
                style.id = 'location-toast-styles';
                style.textContent = `
                    @keyframes slideUp {
                        from { opacity: 0; transform: translateX(-50%) translateY(20px); }
                        to { opacity: 1; transform: translateX(-50%) translateY(0); }
                    }
                `;
                document.head.appendChild(style);
            }

            // Auto-remove after 6 seconds
            setTimeout(() => toast.remove(), 6000);
        }
    }

    function toggleMap() {
        const mapElement = document.getElementById('storeMap');
        const btn = document.getElementById('toggleMapBtn');
        const text = document.getElementById('mapToggleText');

        if (mapElement.style.display === 'none') {
            mapElement.style.display = 'block';
            text.textContent = 'Hide';
            btn.classList.add('active');
            setTimeout(() => map.invalidateSize(), 100);
        } else {
            mapElement.style.display = 'none';
            text.textContent = 'Show';
            btn.classList.remove('active');
        }
    }

    function changeView(view) {
        const grid = document.getElementById('storesGrid');
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

    // Category Grid Drag-to-Scroll Functionality
    const slider = document.getElementById('categoryGrid');
    let isDown = false;
    let startX;
    let scrollLeft;

    if (slider) {
        slider.addEventListener('mousedown', (e) => {
            isDown = true;
            slider.classList.add('active');
            startX = e.pageX - slider.offsetLeft;
            scrollLeft = slider.scrollLeft;
            // Prevent default drag behavior to avoid selecting text/images
            e.preventDefault();
        });

        slider.addEventListener('mouseleave', () => {
            isDown = false;
            slider.classList.remove('active');
        });

        slider.addEventListener('mouseup', () => {
            isDown = false;
            slider.classList.remove('active');
        });

        slider.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - slider.offsetLeft;
            const walk = (x - startX) * 2; // Scroll-fast
            slider.scrollLeft = scrollLeft - walk;
        });

        // Add horizontal scroll with mouse wheel
        slider.addEventListener('wheel', (e) => {
            if (e.deltaY !== 0) {
                e.preventDefault();
                slider.scrollLeft += e.deltaY;
            }
        });

        // Prevent clicking links when dragging
        const links = slider.querySelectorAll('a');
        links.forEach(link => {
            link.addEventListener('click', (e) => {
                if (isDown) {
                    e.preventDefault();
                }
            });
        });
    }
</script>

<style>
    /* Custom grid column for 5 items per row on XL screens */
    @media (min-width: 1200px) {
        .col-xl-2-4 {
            flex: 0 0 20%;
            max-width: 20%;
        }
    }
</style>
@endpush