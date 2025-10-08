@extends('partials.admin')

@section('title', 'Select Store Category')

@section('content')
<style>
    .category-selection-container {
        max-width: 1200px;
        margin: 60px auto;
        padding: 0 20px;
    }
    
    .category-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 24px;
        margin-top: 40px;
    }
    
    .category-card {
        background: white;
        border: 3px solid #e5e7eb;
        border-radius: 16px;
        padding: 32px 24px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    
    .category-card:hover {
        transform: translateY(-8px);
        border-color: #3b82f6;
        box-shadow: 0 20px 40px rgba(59, 130, 246, 0.15);
    }
    
    .category-card.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: #f9fafb;
    }
    
    .category-card.disabled:hover {
        transform: none;
        border-color: #e5e7eb;
        box-shadow: none;
    }
    
    .category-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        color: white;
        transition: all 0.3s ease;
    }
    
    .category-card:hover .category-icon {
        transform: scale(1.1) rotate(5deg);
    }
    
    .category-card.disabled .category-icon {
        background: linear-gradient(135deg, #9ca3af 0%, #6b7280 100%);
    }
    
    .category-name {
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 8px;
    }
    
    .category-description {
        font-size: 14px;
        color: #6b7280;
        line-height: 1.5;
    }
    
    .coming-soon-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    
    .available-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    
    .header-section {
        text-align: center;
        margin-bottom: 40px;
    }
    
    .main-title {
        font-size: 42px;
        font-weight: 800;
        background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 16px;
    }
    
    .sub-title {
        font-size: 18px;
        color: #6b7280;
        max-width: 600px;
        margin: 0 auto;
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
                $isAvailable = in_array($category->slug, ['marketplace', 'services', 'pharmacy', 'premises', 'hardware']);
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