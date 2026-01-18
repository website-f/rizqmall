@extends('partials.app')

@section('title', $store->name . ' - Store Profile')

@section('content')
<style>
    /* Modern Store Profile Styling */
    .store-banner-section {
        position: relative;
        height: 350px;
        border-radius: 24px;
        overflow: hidden;
        margin-bottom: -80px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
    }

    .store-banner-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .store-banner-placeholder {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .store-banner-placeholder::before {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        opacity: 0.3;
    }

    .store-banner-placeholder h3 {
        position: relative;
        z-index: 1;
        color: white;
        font-size: 32px;
        font-weight: 700;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .banner-edit-btn {
        position: absolute;
        top: 20px;
        right: 20px;
        padding: 12px 24px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: none;
        border-radius: 12px;
        font-weight: 700;
        color: #667eea;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 10;
    }

    .banner-edit-btn:hover {
        background: white;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
    }

    .store-header-card {
        background: white;
        border-radius: 24px;
        padding: 100px 40px 40px;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
        position: relative;
        z-index: 5;
    }

    .store-header-content {
        display: flex;
        align-items: flex-start;
        gap: 32px;
    }

    .store-logo-container {
        position: relative;
        flex-shrink: 0;
    }

    .store-logo {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        border: 6px solid white;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        object-fit: cover;
        background: white;
    }

    .store-logo-placeholder {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        border: 6px solid white;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 56px;
        font-weight: 800;
        color: white;
    }

    .verified-badge {
        position: absolute;
        bottom: 5px;
        right: 5px;
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 4px solid white;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
    }

    .store-details {
        flex: 1;
    }

    .store-name-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
    }

    .store-name {
        font-size: 32px;
        font-weight: 800;
        color: #1f2937;
        margin: 0;
    }

    .store-category-badge {
        padding: 6px 16px;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        color: #3b82f6;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .store-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 24px;
        margin-bottom: 16px;
    }

    .store-meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #6b7280;
        font-size: 15px;
        position: relative;
    }

    .store-meta-item i {
        font-size: 18px;
    }

    .store-meta-item.location {
        color: #ef4444;
    }

    .store-meta-item.phone {
        color: #10b981;
    }

    .store-meta-item.email {
        color: #3b82f6;
    }

    .btn-navigate {
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        border: none;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-left: 8px;
        font-size: 14px;
    }

    .btn-navigate:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    }

    .store-description {
        color: #6b7280;
        line-height: 1.6;
        margin-bottom: 20px;
        font-size: 15px;
    }

    .store-rating-section {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
    }

    .store-rating-stars {
        display: flex;
        gap: 4px;
        font-size: 20px;
        color: #fbbf24;
    }

    .store-rating-value {
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
    }

    .store-rating-count {
        color: #9ca3af;
        font-weight: 600;
    }

    .store-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 20px;
        margin-top: 32px;
        padding-top: 32px;
        border-top: 2px solid #f3f4f6;
    }

    .store-stat-card {
        text-align: center;
        padding: 20px;
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        border-radius: 16px;
        transition: all 0.3s ease;
    }

    .store-stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    }

    .store-stat-value {
        font-size: 28px;
        font-weight: 800;
        color: #3b82f6;
        display: block;
        margin-bottom: 8px;
    }

    .store-stat-label {
        font-size: 13px;
        color: #6b7280;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .store-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .btn-action {
        padding: 14px 28px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 15px;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-primary-action {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }

    .btn-primary-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
        color: white;
    }

    .btn-secondary-action {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }

    .btn-secondary-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(245, 158, 11, 0.4);
        color: white;
    }

    /* Products Section */
    .location-map-section {
        background: white;
        border-radius: 24px;
        padding: 32px;
        margin-top: 40px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .map-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .map-title {
        font-size: 24px;
        font-weight: 700;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0;
    }

    .btn-get-directions {
        padding: 12px 24px;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
    }

    .btn-get-directions:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
    }

    .store-location-map {
        height: 400px;
        border-radius: 16px;
        overflow: hidden;
        border: 2px solid #e5e7eb;
    }

    /* Navigation Modal */
    .navigation-modal .modal-content {
        border-radius: 24px;
        overflow: hidden;
    }

    .navigation-modal .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 24px 32px;
    }

    .navigation-option {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 24px;
        border: 2px solid #e5e7eb;
        border-radius: 16px;
        margin-bottom: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        color: inherit;
    }

    .navigation-option:hover {
        border-color: #3b82f6;
        background: #eff6ff;
        transform: translateX(8px);
    }

    .navigation-icon {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        flex-shrink: 0;
    }

    .google-maps-icon {
        background: linear-gradient(135deg, #4285f4 0%, #34a853 100%);
        color: white;
    }

    .waze-icon {
        background: linear-gradient(135deg, #33ccff 0%, #0099ff 100%);
        color: white;
    }

    .apple-maps-icon {
        background: linear-gradient(135deg, #000000 0%, #333333 100%);
        color: white;
    }

    .navigation-info h5 {
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 6px;
    }

    .navigation-info p {
        font-size: 14px;
        color: #6b7280;
        margin: 0;
    }

    /* Products Section */
    .products-section {
        margin-top: 60px;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 32px;
        padding-bottom: 20px;
        border-bottom: 3px solid #f3f4f6;
    }

    .section-title {
        font-size: 28px;
        font-weight: 800;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0;
    }

    .products-count-badge {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        color: #3b82f6;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 700;
    }

    /* Product Card */
    .product-card-modern {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        border: 2px solid transparent;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .product-card-modern:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12);
        border-color: #e0e7ff;
    }

    .product-image-wrapper {
        position: relative;
        aspect-ratio: 1;
        background: #f9fafb;
        overflow: hidden;
    }

    .product-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .product-card-modern:hover .product-image {
        transform: scale(1.05);
    }

    .product-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 700;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        z-index: 2;
    }

    .product-wishlist {
        position: absolute;
        top: 12px;
        right: 12px;
        width: 36px;
        height: 36px;
        background: white;
        border: none;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        z-index: 2;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .product-wishlist:hover {
        transform: scale(1.1);
        background: #fee2e2;
    }

    .product-info {
        padding: 16px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .product-category {
        font-size: 11px;
        color: #9ca3af;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .product-name {
        font-size: 15px;
        font-weight: 600;
        color: #1f2937;
        line-height: 1.4;
        margin-bottom: 8px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 42px;
    }

    .product-rating {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 12px;
        font-size: 13px;
    }

    .product-rating .stars {
        color: #fbbf24;
    }

    .product-rating .count {
        color: #9ca3af;
        font-weight: 600;
    }

    .product-pricing {
        margin-top: auto;
    }

    .product-price-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 4px;
    }

    .product-price {
        font-size: 20px;
        font-weight: 800;
        color: #3b82f6;
    }

    .product-old-price {
        font-size: 14px;
        color: #9ca3af;
        text-decoration: line-through;
    }

    .product-discount {
        background: #fee2e2;
        color: #dc2626;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
    }

    /* Empty State */
    .empty-products {
        text-align: center;
        padding: 80px 20px;
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        border-radius: 24px;
        border: 2px dashed #d1d5db;
    }

    .empty-icon {
        font-size: 80px;
        color: #d1d5db;
        margin-bottom: 24px;
    }

    .empty-title {
        font-size: 24px;
        font-weight: 700;
        color: #6b7280;
        margin-bottom: 12px;
    }

    .empty-text {
        color: #9ca3af;
        font-size: 16px;
        margin-bottom: 24px;
    }

    /* Dropzone Modal */
    .dropzone {
        border: 3px dashed #d1d5db;
        border-radius: 16px;
        padding: 40px 20px;
        text-align: center;
        background: #f9fafb;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .dropzone:hover {
        border-color: #3b82f6;
        background: #eff6ff;
    }

    .dropzone.dz-drag-hover {
        border-color: #3b82f6;
        background: #dbeafe;
    }

    .modal-content {
        border-radius: 24px;
        border: none;
        overflow: hidden;
    }

    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 24px 32px;
    }

    .modal-title {
        font-weight: 700;
        font-size: 20px;
    }

    .btn-close-white {
        filter: brightness(0) invert(1);
    }

    /* Responsive */
    @media (max-width: 991px) {
        .store-banner-section {
            height: 250px;
            margin-bottom: -60px;
        }

        .store-header-card {
            padding: 80px 24px 24px;
        }

        .store-header-content {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .store-name {
            font-size: 24px;
        }

        .store-name-row {
            flex-direction: column;
        }

        .store-meta {
            flex-direction: column;
            gap: 12px;
        }

        .store-stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .store-actions {
            width: 100%;
        }

        .btn-action {
            flex: 1;
            justify-content: center;
        }
    }

    @media (max-width: 575px) {
        .store-banner-section {
            height: 200px;
            border-radius: 16px;
        }

        .store-logo,
        .store-logo-placeholder {
            width: 100px;
            height: 100px;
        }

        .store-logo-placeholder {
            font-size: 40px;
        }

        .section-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 16px;
        }

        .section-title {
            font-size: 22px;
        }
    }
</style>

<section class="py-5">
    <div class="container-small">
        {{-- Breadcrumb --}}
        <nav class="mb-4" aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('rizqmall.home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('stores') }}">Stores</a></li>
                <li class="breadcrumb-item active">{{ $store->name }}</li>
            </ol>
        </nav>

        {{-- Store Banner --}}
        <div class="store-banner-section">
            @if ($store->banner)
            <img src="{{ asset('storage/' . $store->banner) }}" alt="{{ $store->name }} Banner"
                class="store-banner-image">
            @else
            <div class="store-banner-placeholder">
                <h3>🏪 {{ $store->name }}</h3>
            </div>
            @endif

            @if (auth()->check() && auth()->id() == $store->user_id)
            <button class="banner-edit-btn" data-bs-toggle="modal" data-bs-target="#changeBannerModal">
                <i class="fas fa-camera me-2"></i>Change Banner
            </button>
            @endif
        </div>

        {{-- Store Header Card --}}
        <div class="store-header-card">
            <div class="store-header-content">
                {{-- Store Logo --}}
                <div class="store-logo-container">
                    @if ($store->image)
                    <img src="{{ asset('storage/' . $store->image) }}" alt="{{ $store->name }}" class="store-logo">
                    @else
                    <div class="store-logo-placeholder">
                        {{ substr($store->name, 0, 1) }}
                    </div>
                    @endif

                    @if ($store->is_verified)
                    <div class="verified-badge">
                        <i class="fas fa-check text-white"></i>
                    </div>
                    @endif
                </div>

                {{-- Store Details --}}
                <div class="store-details">
                    <div class="store-name-row">
                        <h1 class="store-name">{{ $store->name }}</h1>
                        <span class="store-category-badge">
                            {{ $store->category->name ?? 'General' }}
                        </span>
                    </div>

                    <div class="store-meta">
                        @if ($store->location || ($store->latitude && $store->longitude))
                        <div class="store-meta-item location">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>{{ $store->location ?? 'Location Available' }}</span>
                            @if ($store->latitude && $store->longitude)
                            <button class="btn-navigate" data-bs-toggle="modal"
                                data-bs-target="#navigationModal" title="Get Directions">
                                <i class="fas fa-directions"></i>
                            </button>
                            @endif
                        </div>
                        @endif

                        @if ($store->phone)
                        <div class="store-meta-item phone">
                            <i class="fas fa-phone"></i>
                            <a href="tel:{{ $store->phone }}" class="text-decoration-none" style="color: inherit;">
                                {{ $store->phone }}
                            </a>
                        </div>
                        @endif
                        @if ($store->email)
                        <div class="store-meta-item email">
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:{{ $store->email }}" class="text-decoration-none"
                                style="color: inherit;">
                                {{ $store->email }}
                            </a>
                        </div>
                        @endif
                    </div>

                    @if ($store->description)
                    <p class="store-description">{{ $store->description }}</p>
                    @endif

                    <div class="store-rating-section">
                        <div class="store-rating-stars">
                            @php $rating = $store->rating_average; @endphp
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="{{ $i <= $rating ? 'fas' : 'far' }} fa-star"></i>
                                @endfor
                        </div>
                        <span class="store-rating-value">{{ number_format($rating, 1) }}</span>
                        <span class="store-rating-count">({{ $store->rating_count }} reviews)</span>
                    </div>

                    {{-- Social Media Links --}}
                    @if($store->facebook_url || $store->instagram_url || $store->twitter_url || $store->tiktok_url || $store->youtube_url || $store->whatsapp_number || $store->telegram_url || $store->website_url)
                    <div class="store-social-links mb-4">
                        <div class="d-flex flex-wrap gap-2">
                            @if($store->facebook_url)
                            <a href="{{ $store->facebook_url }}" target="_blank" class="btn btn-sm rounded-pill" style="background: #1877f2; color: white;" title="Facebook">
                                <i class="fab fa-facebook-f me-1"></i> Facebook
                            </a>
                            @endif
                            @if($store->instagram_url)
                            <a href="{{ $store->instagram_url }}" target="_blank" class="btn btn-sm rounded-pill" style="background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888); color: white;" title="Instagram">
                                <i class="fab fa-instagram me-1"></i> Instagram
                            </a>
                            @endif
                            @if($store->twitter_url)
                            <a href="{{ $store->twitter_url }}" target="_blank" class="btn btn-sm rounded-pill" style="background: #000; color: white;" title="Twitter / X">
                                <i class="fab fa-x-twitter me-1"></i> X
                            </a>
                            @endif
                            @if($store->tiktok_url)
                            <a href="{{ $store->tiktok_url }}" target="_blank" class="btn btn-sm rounded-pill" style="background: #000; color: white;" title="TikTok">
                                <i class="fab fa-tiktok me-1"></i> TikTok
                            </a>
                            @endif
                            @if($store->youtube_url)
                            <a href="{{ $store->youtube_url }}" target="_blank" class="btn btn-sm rounded-pill" style="background: #ff0000; color: white;" title="YouTube">
                                <i class="fab fa-youtube me-1"></i> YouTube
                            </a>
                            @endif
                            @if($store->whatsapp_number)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $store->whatsapp_number) }}" target="_blank" class="btn btn-sm rounded-pill" style="background: #25d366; color: white;" title="WhatsApp">
                                <i class="fab fa-whatsapp me-1"></i> WhatsApp
                            </a>
                            @endif
                            @if($store->telegram_url)
                            <a href="{{ $store->telegram_url }}" target="_blank" class="btn btn-sm rounded-pill" style="background: #0088cc; color: white;" title="Telegram">
                                <i class="fab fa-telegram me-1"></i> Telegram
                            </a>
                            @endif
                            @if($store->website_url)
                            <a href="{{ $store->website_url }}" target="_blank" class="btn btn-sm rounded-pill btn-outline-secondary" title="Website">
                                <i class="fas fa-globe me-1"></i> Website
                            </a>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Get Directions Button --}}
                    @if ($store->latitude && $store->longitude)
                    <div class="mb-4">
                        <button class="btn-action btn-primary-action" data-bs-toggle="modal"
                            data-bs-target="#navigationModal">
                            <i class="fas fa-directions"></i>
                            Get Directions
                        </button>
                    </div>
                    @endif

                    {{-- Owner Actions --}}
                    @if (auth()->check() && auth()->id() == $store->user_id)
                    <div class="store-actions">
                        <a href="{{ route('products.create', ['store' => $store->id]) }}"
                            class="btn-action btn-primary-action">
                            <i class="fas fa-plus"></i>
                            Add Product
                        </a>
                        <button class="btn-action btn-secondary-action" data-bs-toggle="modal"
                            data-bs-target="#changeBannerModal">
                            <i class="fas fa-edit"></i>
                            Edit Store
                        </button>
                        <button class="btn-action btn-primary-action" onclick="showMemberQrCode()">
                            <i class="fas fa-qrcode"></i>
                            Member QR
                        </button>
                    </div>
                    @else
                    {{-- Customer Actions --}}
                    <div class="store-actions">
                        @auth
                        @php
                        $isMember = auth()->user()->isMemberOf($store);
                        @endphp
                        @if($isMember)
                        <button class="btn-action" style="background: #10b981; color: white; cursor: default;">
                            <i class="fas fa-check-circle"></i>
                            Store Member
                        </button>
                        @else
                        <form action="{{ route('vendor.member.join', $store->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn-action btn-primary-action">
                                <i class="fas fa-user-plus"></i>
                                Join as Member
                            </button>
                        </form>
                        @endif
                        @else
                        <a href="{{ route('login') }}" class="btn-action btn-primary-action">
                            <i class="fas fa-user-plus"></i>
                            Join as Member
                        </a>
                        @endauth
                    </div>
                    @endif

                    {{-- Store Stats --}}
                    <div class="store-stats-grid">
                        <div class="store-stat-card">
                            <span class="store-stat-value">{{ $products->total() }}</span>
                            <span class="store-stat-label">Products</span>
                        </div>
                        <div class="store-stat-card">
                            <span class="store-stat-value">{{ $store->followers()->count() }}</span>
                            <span class="store-stat-label">Followers</span>
                        </div>
                        <div class="store-stat-card">
                            <span class="store-stat-value">{{ number_format($store->rating_average, 1) }}</span>
                            <span class="store-stat-label">Rating</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>




        {{-- Products Section --}}
        <div class="products-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-box-open text-primary"></i>
                    Store Products
                    <span class="products-count-badge">{{ $products->total() }}</span>
                </h2>
            </div>

            <div class="row g-3 g-lg-4">
                @forelse($products as $product)
                @php
                $firstImage = $product->images->first()->path ?? null;
                $onSale = !is_null($product->sale_price) && $product->sale_price < $product->regular_price;
                    $displayPrice = $onSale ? $product->sale_price : $product->regular_price;
                    $oldPrice = $onSale ? $product->regular_price : null;
                    $discount = $onSale
                    ? round(
                    (($product->regular_price - $product->sale_price) / $product->regular_price) * 100,
                    )
                    : 0;
                    @endphp

                    <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                        <div class="product-card-modern">
                            <div class="product-image-wrapper">
                                <a href="{{ route('product.show', $product->slug) }}">
                                    @if ($firstImage)
                                    <img class="product-image" src="{{ asset('storage/' . $firstImage) }}"
                                        alt="{{ $product->name }}">
                                    @else
                                    <img class="product-image"
                                        src="https://placehold.co/400x400/667eea/FFFFFF?text={{ substr($product->name, 0, 1) }}"
                                        alt="{{ $product->name }}">
                                    @endif
                                </a>

                                @if ($onSale)
                                <div class="product-badge">
                                    {{ $discount }}% OFF
                                </div>
                                @endif

                                <button class="product-wishlist" onclick="toggleWishlist({{ $product->id }}, this)">
                                    <i class="far fa-heart"></i>
                                </button>
                            </div>

                            <div class="product-info">
                                <div class="product-category">
                                    {{ $product->category->name ?? 'Uncategorized' }}
                                </div>

                                <a href="{{ route('product.show', $product->slug) }}" class="text-decoration-none">
                                    <h6 class="product-name">{{ $product->name }}</h6>
                                </a>

                                <div class="product-rating">
                                    <div class="stars">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star"></i>
                                            @endfor
                                    </div>
                                    <span class="count">(100)</span>
                                </div>

                                <div class="product-pricing">
                                    <div class="product-price-row">
                                        <span class="product-price">
                                            RM{{ number_format($displayPrice, 2) }}
                                        </span>
                                        @if ($oldPrice)
                                        <span class="product-old-price">
                                            RM{{ number_format($oldPrice, 2) }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="empty-products">
                            <div class="empty-icon">
                                <i class="fas fa-box-open"></i>
                            </div>
                            <h4 class="empty-title">No Products Yet</h4>
                            <p class="empty-text">This store hasn't added any products yet.</p>
                            @if (session('auth_user_id') && session('auth_user_id') == $store->auth_user_id)
                            <a href="{{ route('products.create', ['store' => $store->id]) }}"
                                class="btn-action btn-primary-action">
                                <i class="fas fa-plus me-2"></i>Add Your First Product
                            </a>
                            @endif
                        </div>
                    </div>
                    @endforelse
            </div>

            {{-- Pagination --}}
            @if ($products->hasPages())
            <div class="d-flex justify-content-center mt-5">
                {{ $products->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</section>

{{-- Navigation Modal --}}
@if ($store->latitude && $store->longitude)
<div class="modal fade navigation-modal" id="navigationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-route me-2"></i>Choose Navigation App
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-4">Select your preferred navigation app to get directions to
                    {{ $store->name }}
                </p>

                {{-- Google Maps --}}
                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $store->latitude }},{{ $store->longitude }}"
                    target="_blank" class="navigation-option">
                    <div class="navigation-icon google-maps-icon">
                        <i class="fab fa-google"></i>
                    </div>
                    <div class="navigation-info">
                        <h5>Google Maps</h5>
                        <p>Navigate with the world's most popular mapping service</p>
                    </div>
                    <i class="fas fa-chevron-right ms-auto text-muted"></i>
                </a>

                {{-- Waze --}}
                <a href="https://waze.com/ul?ll={{ $store->latitude }},{{ $store->longitude }}&navigate=yes"
                    target="_blank" class="navigation-option">
                    <div class="navigation-icon waze-icon">
                        <i class="fab fa-waze"></i>
                    </div>
                    <div class="navigation-info">
                        <h5>Waze</h5>
                        <p>Get real-time traffic updates and fastest routes</p>
                    </div>
                    <i class="fas fa-chevron-right ms-auto text-muted"></i>
                </a>

                {{-- Apple Maps (for iOS devices) --}}
                <a href="https://maps.apple.com/?daddr={{ $store->latitude }},{{ $store->longitude }}"
                    target="_blank" class="navigation-option">
                    <div class="navigation-icon apple-maps-icon">
                        <i class="fab fa-apple"></i>
                    </div>
                    <div class="navigation-info">
                        <h5>Apple Maps</h5>
                        <p>Navigate with Apple's mapping service (iOS only)</p>
                    </div>
                    <i class="fas fa-chevron-right ms-auto text-muted"></i>
                </a>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Change Banner Modal --}}
@if (auth()->check() && auth()->id() == $store->user_id)
<div class="modal fade" id="changeBannerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form id="bannerDropzoneForm" action="{{ route('vendor.store.changeBanner', $store->id) }}" method="POST"
            class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-camera me-2"></i>Change Store Banner
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="bannerDropzone" class="dropzone">
                    <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                    <h5 class="mb-2">Drag & Drop Your Banner Here</h5>
                    <p class="text-muted mb-3">or click to browse</p>
                    <p class="text-muted small mb-0">Recommended: 1920x350px, Max 5MB (JPG, PNG)</p>
                </div>
                <input type="hidden" name="banner_path" id="banner_path">
            </div>
            <div class="modal-footer border-0 p-4">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="uploadBannerBtn">
                    <i class="fas fa-upload me-2"></i>Upload Banner
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Member QR Code Modal --}}
<div class="modal fade" id="memberQrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title bg-primary text-white p-3 w-100 rounded-top mb-0" style="margin: -1px -1px 0 -1px;">
                    <i class="fas fa-qrcode me-2"></i>Member Recruitment QR
                    <button type="button" class="btn-close btn-close-white float-end" data-bs-dismiss="modal"></button>
                </h5>
            </div>
            <div class="modal-body text-center p-5">
                <h4 class="mb-3 font-weight-bold text-dark">Recruit New Members</h4>
                <p class="text-muted mb-4">Ask customers to scan this QR code to join your store as a member.</p>

                <div id="qrCodeContainer" class="mb-4 d-flex justify-content-center align-items-center" style="min-height: 250px;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <div class="text-center mb-3">
                    <button type="button" class="btn btn-outline-dark btn-sm" onclick="downloadQrCode()">
                        <i class="fas fa-download me-2"></i>Download QR Code
                    </button>
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text bg-light">Referral Code</span>
                    <input type="text" class="form-control text-center font-weight-bold" id="storeRefCode" readonly>
                    <button class="btn btn-outline-primary" type="button" onclick="copyRefCode()">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>

                <p class="small text-muted">Status: <span class="badge bg-success">Active</span></p>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
    if (typeof Dropzone !== 'undefined') {
        Dropzone.autoDiscover = false;
    }

    // ... (rest of the script)

    // Show Member QR Code
    function showMemberQrCode() {
        const modal = new bootstrap.Modal(document.getElementById('memberQrModal'));
        modal.show();

        // fetch QR code
        const qrContainer = document.getElementById('qrCodeContainer');
        const refInput = document.getElementById('storeRefCode');

        // Clear previous QR
        qrContainer.innerHTML = '';

        // Fetch Ref Code details
        fetch(`{{ route('vendor.member.ref-code', $store->id) }}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    refInput.value = data.ref_code;

                    // Generate QR Code Client-side
                    new QRCode(qrContainer, {
                        text: data.join_url,
                        width: 200,
                        height: 200,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.H
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching QR:', error);
                qrContainer.innerHTML = '<p class="text-danger">Failed to load info</p>';
            });
    }

    // Download QR Code
    function downloadQrCode() {
        const qrContainer = document.getElementById('qrCodeContainer');
        let src = null;
        const img = qrContainer.querySelector('img');
        const canvas = qrContainer.querySelector('canvas');

        if (img && img.src) {
            src = img.src;
        } else if (canvas) {
            src = canvas.toDataURL("image/png");
        }

        if (src) {
            const link = document.createElement('a');
            link.href = src;
            link.download = '{{ $store->slug }}-member-qr.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } else {
            alert('QR Code not ready.');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Initialize Dropzone for banner upload (only if element exists - for store owners)
        const bannerDropzoneEl = document.getElementById('bannerDropzone');
        if (bannerDropzoneEl) {
            const bannerDropzone = new Dropzone("#bannerDropzone", {
                url: "{{ route('uploads.temp') }}",
                paramName: 'file',
                maxFiles: 1,
                acceptedFiles: 'image/*',
                maxFilesize: 5, // MB
                addRemoveLinks: true,
                dictDefaultMessage: '',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                init: function() {
                    this.on("success", function(file, response) {
                        document.getElementById('banner_path').value = response.path;
                        document.getElementById('uploadBannerBtn').disabled = false;
                    });
                    this.on("removedfile", function() {
                        document.getElementById('banner_path').value = '';
                        document.getElementById('uploadBannerBtn').disabled = true;
                    });
                    this.on("error", function(file, message) {
                        alert('Upload failed: ' + message);
                        this.removeFile(file);
                    });
                }
            });

            // Form submission validation
            const bannerForm = document.getElementById('bannerDropzoneForm');
            if (bannerForm) {
                bannerForm.addEventListener('submit', function(e) {
                    if (!document.getElementById('banner_path').value) {
                        e.preventDefault();
                        alert('Please upload a banner image first.');
                    }
                });
            }
        }
    });

    // Wishlist toggle function
    function toggleWishlist(productId, button) {
        button.classList.toggle('active');
        const icon = button.querySelector('i');
        icon.classList.toggle('far');
        icon.classList.toggle('fas');

        // TODO: Implement actual wishlist API call
        console.log('Toggle wishlist for product:', productId);
    }



    function copyRefCode() {
        const copyText = document.getElementById("storeRefCode");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value);

        // Show toaster or alert
        // assuming standard verify toaster is available or just alert
        alert("Referral code copied: " + copyText.value);
    }
</script>
@endpush