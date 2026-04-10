@extends('partials.admin')

@section('title', 'Select Store Category')

@section('content')
<style>
    .category-selection-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 16px;
    }

    .category-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 32px;
    }

    .category-card {
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 16px;
        padding: 28px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .category-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.12);
    }

    .category-card.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: #f9fafb;
    }

    .category-card.disabled:hover {
        transform: none;
        box-shadow: none;
    }

    .category-icon {
        width: 72px;
        height: 72px;
        margin: 0 auto 16px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        color: white;
        transition: all 0.3s ease;
    }

    .category-card:hover .category-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .category-card.disabled .category-icon {
        background: linear-gradient(135deg, #9ca3af 0%, #6b7280 100%) !important;
    }

    /* Unique colors per category */
    .category-card:nth-child(1) .category-icon { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); }
    .category-card:nth-child(1):hover { border-color: #3b82f6; }
    .category-card:nth-child(2) .category-icon { background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); }
    .category-card:nth-child(2):hover { border-color: #8b5cf6; }
    .category-card:nth-child(3) .category-icon { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    .category-card:nth-child(3):hover { border-color: #10b981; }
    .category-card:nth-child(4) .category-icon { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
    .category-card:nth-child(4):hover { border-color: #f59e0b; }
    .category-card:nth-child(5) .category-icon { background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%); }
    .category-card:nth-child(5):hover { border-color: #6366f1; }
    .category-card:nth-child(6) .category-icon { background: linear-gradient(135deg, #78716c 0%, #57534e 100%); }
    .category-card:nth-child(6):hover { border-color: #78716c; }
    .category-card:nth-child(7) .category-icon { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
    .category-card:nth-child(7):hover { border-color: #ef4444; }
    .category-card:nth-child(8) .category-icon { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); }
    .category-card:nth-child(8):hover { border-color: #0ea5e9; }
    .category-card:nth-child(9) .category-icon { background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%); }
    .category-card:nth-child(9):hover { border-color: #14b8a6; }
    .category-card:nth-child(10) .category-icon { background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); }
    .category-card:nth-child(10):hover { border-color: #ec4899; }

    .category-name {
        font-size: 16px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 6px;
    }

    .category-description {
        font-size: 13px;
        color: #6b7280;
        line-height: 1.5;
    }

    .coming-soon-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    .available-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    .header-section {
        text-align: center;
        margin-bottom: 32px;
    }

    .main-title {
        font-size: 36px;
        font-weight: 800;
        background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 12px;
    }

    .sub-title {
        font-size: 16px;
        color: #6b7280;
        max-width: 560px;
        margin: 0 auto;
    }

    @media (max-width: 767.98px) {
        .main-title { font-size: 26px; }
        .sub-title { font-size: 14px; }
        .category-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        .category-card { padding: 20px 14px; }
        .category-icon { width: 56px; height: 56px; font-size: 24px; border-radius: 14px; }
        .category-name { font-size: 14px; }
        .category-description { font-size: 11px; }
    }

    @media (max-width: 400px) {
        .category-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="category-selection-container">
    <div class="header-section">
        <h1 class="main-title">🏪 What Are You Selling?</h1>
        <p class="sub-title">
            Choose your store category to get started. This helps us customize your selling experience.
        </p>
    </div>

    @if(session('info'))
        <div class="alert alert-info border-0 mb-4" role="alert">
            <i class="fas fa-info-circle me-2"></i> {{ session('info') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 mb-4" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
        </div>
    @endif

    <div class="category-grid">
        @foreach($categories as $category)
            @php
                // All categories are available except contractors
                $isAvailable = $category->slug !== 'contractors';
            @endphp
            
            <div class="category-card {{ !$isAvailable ? 'disabled' : '' }}"
                 onclick="{{ $isAvailable ? "selectCategory('" . $category->id . "')" : '' }}">
                
                @if($isAvailable)
                    <span class="available-badge">✓ Available</span>
                @else
                    <span class="coming-soon-badge">Coming Soon</span>
                @endif
                
                <div class="category-icon">
                    <span class="{{ $category->icon }}"></span>
                </div>
                
                <h3 class="category-name">{{ $category->name }}</h3>
                
                @if($category->description)
                    <p class="category-description">{{ $category->description }}</p>
                @endif
            </div>
        @endforeach
    </div>
</div>

<script>
function selectCategory(categoryId) {
    window.location.href = "{{ route('store.setup') }}?category=" + categoryId;
}
</script>
@endsection