@extends('partials.app')

@section('title', 'All Products - Rizqmall')
    
@section('content')
<section class="pt-5 pb-9">

    <div class="product-filter-container">
        <button class="btn btn-sm btn-phoenix-secondary text-body-tertiary mb-5 d-lg-none" data-phoenix-toggle="offcanvas" data-phoenix-target="#productFilterColumn"><span class="fa-solid fa-filter me-2"></span>Filter</button>
        <div class="row">
            {{-- Filter Column (Keep Static for now) --}}
            <div class="col-lg-3 col-xxl-2 ps-2 ps-xxl-3">
                <div class="phoenix-offcanvas-filter bg-body scrollbar phoenix-offcanvas phoenix-offcanvas-fixed" id="productFilterColumn" style="top: 92px">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="mb-0">Filters</h3>
                        <button class="btn d-lg-none p-0" data-phoenix-dismiss="offcanvas"><span class="uil uil-times fs-8"></span></button>
                    </div>
                    {{-- All filter accordion items left static as per request --}}
                   {{-- Availability --}}
                    <a class="btn px-0 d-block collapse-indicator" data-bs-toggle="collapse" href="#collapseAvailability" role="button" aria-expanded="true" aria-controls="collapseAvailability">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div class="fs-8 text-body-highlight">Availability</div><span class="fa-solid fa-angle-down toggle-icon text-body-quaternary"></span>
                        </div>
                    </a>
                    <div class="collapse show" id="collapseAvailability">
                        <div class="mb-2">
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="inStockInput" type="checkbox" name="color" checked>
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="inStockInput">In stock</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="preBookInput" type="checkbox" name="color">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="preBookInput">Pre-book</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="outOfStockInput" type="checkbox" name="color">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="outOfStockInput">Out of stock</label>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Color Family --}}
                    <a class="btn px-0 d-block collapse-indicator" data-bs-toggle="collapse" href="#collapseColorFamily" role="button" aria-expanded="true" aria-controls="collapseColorFamily">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div class="fs-8 text-body-highlight">Color family</div><span class="fa-solid fa-angle-down toggle-icon text-body-quaternary"></span>
                        </div>
                    </a>
                    <div class="collapse show" id="collapseColorFamily">
                        <div class="mb-2">
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="flexCheckBlack" type="checkbox" name="color" checked>
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="flexCheckBlack">Black</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="flexCheckBlue" type="checkbox" name="color">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="flexCheckBlue">Blue</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="flexCheckRed" type="checkbox" name="color">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="flexCheckRed">Red</label>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Brands --}}
                    <a class="btn px-0 d-block collapse-indicator" data-bs-toggle="collapse" href="#collapseBrands" role="button" aria-expanded="true" aria-controls="collapseBrands">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div class="fs-8 text-body-highlight">Brands</div><span class="fa-solid fa-angle-down toggle-icon text-body-quaternary"></span>
                        </div>
                    </a>
                    <div class="collapse show" id="collapseBrands">
                        <div class="mb-2">
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="flexCheckBlackberry" type="checkbox" name="brands" checked>
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="flexCheckBlackberry">
                                    Blackberry
                                </label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="flexCheckApple" type="checkbox" name="brands">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="flexCheckApple">
                                    Apple
                                </label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="flexCheckNokia" type="checkbox" name="brands">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="flexCheckNokia">
                                    Nokia
                                </label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="flexCheckSony" type="checkbox" name="brands">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="flexCheckSony">
                                    Sony
                                </label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="flexCheckLG" type="checkbox" name="brands">
                                <label class="form-check-label d-block lh-sm fs-8 text-body mb-0 fw-normal" for="flexCheckLG">
                                    LG
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Price Range --}}
                    <a class="btn px-0 d-block collapse-indicator" data-bs-toggle="collapse" href="#collapsePriceRange" role="button" aria-expanded="true" aria-controls="collapsePriceRange">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div class="fs-8 text-body-highlight">Price range</div><span class="fa-solid fa-angle-down toggle-icon text-body-quaternary"></span>
                        </div>
                    </a>
                    <div class="collapse show" id="collapsePriceRange">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="input-group me-2">
                                <input class="form-control" type="text" aria-label="First name" placeholder="Min">
                                <input class="form-control" type="text" aria-label="Last name" placeholder="Max">
                            </div>
                            <button class="btn btn-phoenix-primary px-3" type="button">Go</button>
                        </div>
                    </div>
                    
                    {{-- Rating --}}
                    <a class="btn px-0 y-4 d-block collapse-indicator" data-bs-toggle="collapse" href="#collapseRating" role="button" aria-expanded="true" aria-controls="collapseRating">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div class="fs-8 text-body-highlight">Rating</div><span class="fa-solid fa-angle-down toggle-icon text-body-quaternary"></span>
                        </div>
                    </a>
                    <div class="collapse show" id="collapseRating">
                        <div class="d-flex align-items-center mb-1">
                            <input class="form-check-input me-3" id="flexRadio1" type="radio" name="flexRadio"><span class="fa fa-star text-warning fs-9 me-1"></span><span class="fa fa-star text-warning fs-9 me-1"></span><span class="fa fa-star text-warning fs-9 me-1"></span><span class="fa fa-star text-warning fs-9 me-1"></span><span class="fa fa-star text-warning fs-9 me-1"></span>
                        </div>
                        <div class="d-flex align-items-center mb-1">
                            <input class="form-check-input me-3" id="flexRadio2" type="radio" name="flexRadio"><span class="fa fa-star text-warning fs-9 me-1"></span><span class="fa fa-star text-warning fs-9 me-1"></span><span class="fa fa-star text-warning fs-9 me-1"></span><span class="fa-regular fa-star text-warning-light fs-9 me-1" data-bs-theme="light"></span>
                            <p class="ms-1 mb-0">&amp; above</p>
                        </div>
                        <div class="d-flex align-items-center mb-1">
                            <input class="form-check-input me-3" id="flexRadio3" type="radio" name="flexRadio"><span class="fa fa-star text-warning fs-9 me-1"></span><span class="fa-regular fa-star text-warning-light fs-9 me-1" data-bs-theme="light"></span><span class="fa-regular fa-star text-warning-light fs-9 me-1" data-bs-theme="light"></span><span class="fa-regular fa-star text-warning-light fs-9 me-1" data-bs-theme="light"></span><span class="fa-regular fa-star text-warning-light fs-9 me-1" data-bs-theme="light"></span>
                            <p class="ms-1 mb-0">&amp; above </p>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <input class="form-check-input me-3" id="flexRadio5" type="radio" name="flexRadio"><span class="fa fa-star text-warning fs-9 me-1"></span><span class="fa-regular fa-star text-warning-light fs-9 me-1" data-bs-theme="light"></span><span class="fa-regular fa-star text-warning-light fs-9 me-1" data-bs-theme="light"></span><span class="fa-regular fa-star text-warning-light fs-9 me-1" data-bs-theme="light"></span><span class="fa-regular fa-star text-warning-light fs-9 me-1" data-bs-theme="light"></span>
                            <p class="ms-1 mb-0">&amp; above </p>
                        </div>
                    </div>
                    
                    {{-- Display Type (Remaining filters kept as original for template completeness) --}}
                    <a class="btn px-0 d-block collapse-indicator" data-bs-toggle="collapse" href="#collapseDisplayType" role="button" aria-expanded="true" aria-controls="collapseDisplayType">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div class="fs-8 text-body-highlight">Display type</div><span class="fa-solid fa-angle-down toggle-icon text-body-quaternary"></span>
                        </div>
                    </a>
                    <div class="collapse show" id="collapseDisplayType">
                        <div class="mb-2">
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="lcdInput" type="checkbox" name="displayType" checked>
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="lcdInput">LCD</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="ipsInput" type="checkbox" name="displayType">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="ipsInput">IPS</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="oledInput" type="checkbox" name="displayType">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="oledInput">OLED</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="amoledInput" type="checkbox" name="displayType">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="amoledInput">AMOLED</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="retinaInput" type="checkbox" name="displayType">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="retinaInput">Retina</label>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Condition --}}
                    <a class="btn px-0 d-block collapse-indicator" data-bs-toggle="collapse" href="#collapseCondition" role="button" aria-expanded="true" aria-controls="collapseCondition">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div class="fs-8 text-body-highlight">Condition</div><span class="fa-solid fa-angle-down toggle-icon text-body-quaternary"></span>
                        </div>
                    </a>
                    <div class="collapse show" id="collapseCondition">
                        <div class="mb-2">
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="newInput" type="checkbox" name="condition" checked>
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="newInput">New</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="usedInput" type="checkbox" name="condition">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="usedInput">Used</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="refurbrishedInput" type="checkbox" name="condition">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="refurbrishedInput">Refurbrished</label>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Delivery --}}
                    <a class="btn px-0 d-block collapse-indicator" data-bs-toggle="collapse" href="#collapseDelivery" role="button" aria-expanded="true" aria-controls="collapseDelivery">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div class="fs-8 text-body-highlight">Delivery</div><span class="fa-solid fa-angle-down toggle-icon text-body-quaternary"></span>
                        </div>
                    </a>
                    <div class="collapse show" id="collapseDelivery">
                        <div class="mb-2">
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="freeShippingInput" type="checkbox" name="delivery" checked>
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="freeShippingInput">Free Shipping</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="oneDayShippingInput" type="checkbox" name="delivery">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="oneDayShippingInput">One-day Shipping</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="codInput" type="checkbox" name="delivery">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="codInput">Cash on Delivery</label>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Campaign --}}
                    <a class="btn px-0 d-block collapse-indicator" data-bs-toggle="collapse" href="#collapseCampaign" role="button" aria-expanded="true" aria-controls="collapseCampaign">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div class="fs-8 text-body-highlight">Campaign</div><span class="fa-solid fa-angle-down toggle-icon text-body-quaternary"></span>
                        </div>
                    </a>
                    <div class="collapse show" id="collapseCampaign">
                        <div class="mb-2">
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="summerSaleInput" type="checkbox" name="campaign">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="summerSaleInput">Summer Sale</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="marchMadnessInput" type="checkbox" name="campaign">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="marchMadnessInput">March Madness</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="flashSaleInput" type="checkbox" name="campaign">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="flashSaleInput">Flash Sale</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="bogoBlastInput" type="checkbox" name="campaign">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="bogoBlastInput">BOGO Blast</label>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Warranty --}}
                    <a class="btn px-0 d-block collapse-indicator" data-bs-toggle="collapse" href="#collapseWarranty" role="button" aria-expanded="true" aria-controls="collapseWarranty">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div class="fs-8 text-body-highlight">Warranty</div><span class="fa-solid fa-angle-down toggle-icon text-body-quaternary"></span>
                        </div>
                    </a>
                    <div class="collapse show" id="collapseWarranty">
                        <div class="mb-2">
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="threeMonthInput" type="checkbox" name="warranty">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="threeMonthInput">3 months</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="sixMonthInput" type="checkbox" name="warranty">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="sixMonthInput">6 months</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="oneYearInput" type="checkbox" name="warranty">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="oneYearInput">1 year</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="twoYearsInput" type="checkbox" name="warranty">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="twoYearsInput">2 years</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="threeYearsInput" type="checkbox" name="warranty">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="threeYearsInput">3 years</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="fiveYearsInput" type="checkbox" name="warranty">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="fiveYearsInput">5 years</label>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Warranty Type --}}
                    <a class="btn px-0 d-block collapse-indicator" data-bs-toggle="collapse" href="#collapseWarrantyType" role="button" aria-expanded="true" aria-controls="collapseWarrantyType">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div class="fs-8 text-body-highlight">Warranty Type</div><span class="fa-solid fa-angle-down toggle-icon text-body-quaternary"></span>
                        </div>
                    </a>
                    <div class="collapse show" id="collapseWarrantyType">
                        <div class="mb-2">
                            <div class="form-check mb-0x">
                                <input class="form-check-input mt-0" id="replacementInput" type="checkbox" name="warrantyType">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="replacementInput">Replacement</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="serviceInput" type="checkbox" name="warrantyType">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="serviceInput">Service</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="partialCoveregeInput" type="checkbox" name="warrantyType">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="partialCoveregeInput">Partial Coverage</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="appleCareInput" type="checkbox" name="warrantyType">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="appleCareInput">Apple Care</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="moneyBackInput" type="checkbox" name="warrantyType">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="moneyBackInput">Money back</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="extendableInput" type="checkbox" name="warrantyType">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="extendableInput">Extendable</label>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Certification --}}
                    <a class="btn px-0 d-block collapse-indicator" data-bs-toggle="collapse" href="#collapseCertification" role="button" aria-expanded="true" aria-controls="collapseCertification">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div class="fs-8 text-body-highlight">Certification</div><span class="fa-solid fa-angle-down toggle-icon text-body-quaternary"></span>
                        </div>
                    </a>
                    <div class="collapse show" id="collapseCertification">
                        <div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="rohsInput" type="checkbox" name="certification">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="rohsInput">RoHS</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="fccInput" type="checkbox" name="certification">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="fccInput">FCC</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="conflictInput" type="checkbox" name="certification">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="conflictInput">Conflict Free</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="isoOneInput" type="checkbox" name="certification">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="isoOneInput">ISO 9001:2015</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="isoTwoInput" type="checkbox" name="certification">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="isoTwoInput">ISO 27001:2013</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input mt-0" id="isoThreeInput" type="checkbox" name="certification">
                                <label class="form-check-label d-block lh-sm fs-8 text-body fw-normal mb-0" for="isoThreeInput">IEC 61000-4-2</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="phoenix-offcanvas-backdrop d-lg-none" data-phoenix-backdrop style="top: 92px"></div>
            </div>
            
            {{-- Products Display Column --}}
            <div class="col-lg-9 col-xxl-10">
                <div class="row gx-3 gy-6 mb-8">
                    
                    @forelse ($products as $product)
                        @php
                            $firstImage = $product->images->first()->path ?? 'path/to/default-image.png';
                            $onSale = !is_null($product->sale_price) && $product->sale_price < $product->regular_price;
                            $displayPrice = $onSale ? $product->sale_price : $product->regular_price;
                            $oldPrice = $onSale ? $product->regular_price : null;
                        @endphp

                        <div class="col-12 col-sm-6 col-md-4 col-xxl-2">
                            <div class="product-card-container h-100">
                                <div class="position-relative text-decoration-none product-card h-100">
                                    <div class="d-flex flex-column justify-content-between h-100">
                                        <div>
                                            <div class="border border-1 border-translucent rounded-3 position-relative mb-3">
                                                <button class="btn btn-wish btn-wish-primary z-2 d-toggle-container" data-bs-toggle="tooltip" data-bs-placement="top" title="Add to wishlist">
                                                    <span class="fas fa-heart d-block-hover" data-fa-transform="down-1"></span>
                                                    <span class="far fa-heart d-none-hover" data-fa-transform="down-1"></span>
                                                </button>
                                                {{-- Product Image --}}
                                                <img class="img-fluid" src="{{ asset('storage/' . $firstImage) }}" alt="{{ $product->name }}" />
                                                
                                                @if ($onSale)
                                                    <span class="badge text-bg-warning fs-10 product-verified-badge">SALE</span>
                                                @endif
                                            </div>
                                            {{-- Product Name Link --}}
                                            <a class="stretched-link" href="{{ route('product.show', $product->slug) }}">
                                                <h6 class="mb-2 lh-sm line-clamp-3 product-name">{{ $product->name }}</h6>
                                            </a>
                                            {{-- Dummy Rating Display --}}
                                            <p class="fs-9">
                                                @for ($i = 0; $i < $dummyRating; $i++)
                                                    <span class="fa fa-star text-warning"></span>
                                                @endfor
                                                <span class="text-body-quaternary fw-semibold ms-1">(100 ratings)</span> {{-- Hardcoded dummy count --}}
                                            </p>
                                        </div>
                                        <div>
                                            {{-- Pricing --}}
                                            <div class="d-flex align-items-center mb-1">
                                                @if ($oldPrice)
                                                    <p class="me-2 text-body text-decoration-line-through mb-0">RM{{ number_format($oldPrice, 2) }}</p>
                                                @endif
                                                <h3 class="text-body-emphasis mb-0">RM{{ number_format($displayPrice, 2) }}</h3>
                                            </div>
                                            {{-- Dynamic stock/status info --}}
                                            <p class="text-{{ $product->stock_quantity > 0 ? 'success' : 'danger' }} fw-bold fs-9 lh-1 mb-0">
                                                {{ $product->stock_quantity > 0 ? 'In Stock' : 'Out of Stock' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-info" role="alert">
                                No published products found. Time to add some! 🚀
                            </div>
                        </div>
                    @endforelse

                </div>
                
                {{-- Pagination Links --}}
                <div class="d-flex justify-content-end">
                    {{ $products->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</section>
@endsection