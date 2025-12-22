@extends('layouts.app')

@section('form', json_encode([
    'method' => 'POST'
]))

@php 
    $channels = Channels::channels();
    $brandSelected = $brandSelected ?? false;
@endphp

@section('sub_header')
    <x-sub-header 
        title="{{ __('Manage accounts') }}" 
        description="{{ __('Seamless Management for All Accounts') }}" 
        :count="$total"
    >
        <div class="d-flex gap-8">
            @if($brandSelected)
                <a class="btn btn-dark btn-sm" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#addChannelModal">
                    <span><i class="fa-light fa-plus"></i></span>
                    <span>{{ __('Add New') }}</span>
                </a>
            @else
                <button class="btn btn-dark btn-sm" onclick="showBrandRequiredMessage()" type="button">
                    <span><i class="fa-light fa-plus"></i></span>
                    <span>{{ __('Add New') }}</span>
                </button>
            @endif
        </div>
    </x-sub-header>
@endsection

@section('content')
    @if(!$brandSelected)
        <!-- Brand Selection Required Message -->
        <div class="container pb-3">
            <div class="alert alert-warning d-flex align-items-center gap-3 border-warning" role="alert">
                <i class="fa-light fa-triangle-exclamation fs-20"></i>
                <div>
                    <strong>{{ __('Brand Selection Required') }}</strong>
                    <p class="mb-0">{{ __('Please select a brand from the dropdown in the top-right corner before managing accounts.') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="container pb-3">
        <div class="d-flex align-items-center justify-content-between">
            <div class="table-info"></div>
            <div class="d-flex flex-wrap gap-8">    
                <div class="d-flex">
                    <div class="form-control form-control-sm">
                        <span class="btn btn-icon">
                            <i class="fa-duotone fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input class="ajax-scroll-filter" name="keyword" placeholder="{{ __('Search') }}" type="text" {{ !$brandSelected ? 'disabled' : '' }}>
                        <button class="btn btn-icon" {{ !$brandSelected ? 'disabled' : '' }}>
                            <div class="form-check form-check-sm mb-0">
                                <input class="form-check-input checkbox-all" id="select_all" type="checkbox" {{ !$brandSelected ? 'disabled' : '' }}>
                            </div>
                        </button>
                    </div>
                </div>
                <div class="d-flex">
                    <div class="btn-group position-static">
                        <button class="btn btn-outline btn-light btn-sm dropdown-toggle dropdown-arrow-hide" data-bs-toggle="dropdown" aria-expanded="true" {{ !$brandSelected ? 'disabled' : '' }}>
                            <i class="fa-light fa-filter"></i> {{ __("Filters") }}
                        </button>
                        <div class="dropdown-menu dropdown-menu-end border-1 border-gray-300 w-full max-w-250" data-popper-placement="bottom-end">
                            <div class="d-flex border-bottom px-3 py-2 fw-6 fs-16 gap-8">
                                <span><i class="fa-light fa-filter"></i></span>
                                <span>{{ __("Filters") }}</span>
                            </div>
                            <div class="p-3">
                                <div class="mb-3">
                                    <label class="form-label">{{ __("Status") }}</label>
                                    <select class="form-select ajax-scroll-filter" name="status">
                                        <option value="-1">{{ __("All") }}</option>
                                        <option value="1">{{ __("Active") }}</option>
                                        <option value="0">{{ __("Disconnected") }}</option>
                                        <option value="2">{{ __("Pause") }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">{{ __("Social network") }}</label>
                                    <select class="form-select ajax-scroll-filter" name="module_name">
                                        <option value="">{{ __("All") }}</option>
                                        @if( !empty( $channels ) )
                                            @foreach( $channels as $channel )
                                                @if( !empty( $channel ) && isset( $channel['items']  ) )
                                                    @foreach( $channel['items'] as $item )
                                                        <option value="{{ $item['id'] }}">{{ $item['module_name'] }}</option>
                                                    @endforeach
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex">
                    <div class="btn-group position-static">
                        <button class="btn btn-outline btn-primary btn-sm dropdown-toggle dropdown-arrow-hide" data-bs-toggle="dropdown" aria-expanded="true" {{ !$brandSelected ? 'disabled' : '' }}>
                            <i class="fa-light fa-grid-2"></i> {{ __("Actions") }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-1 border-gray-300 px-2 w-100 max-w-125" data-popper-placement="bottom-end">
                            <li>
                                <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionMultiItem" href="{{ module_url("status/active") }}" data-call-success="Main.ajaxScroll(true)">
                                    <span class="size-16 me-1 text-center"><i class="fa-light fa-check"></i></span>
                                    <span>{{ __("Active") }}</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionMultiItem" href="{{ module_url("status/pause") }}" data-call-success="Main.ajaxScroll(true)">
                                    <span class="size-16 me-1 text-center"><i class="fa-light fa-pause"></i></span>
                                    <span>{{ __("Pause") }}</span>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionMultiItem" href="{{ module_url("destroy") }}" data-call-success="Main.ajaxScroll(true)">
                                    <span class="size-16 me-1 text-center"><i class="fa-light fa-trash-can-list"></i></span>
                                    <span>{{ __("Delete") }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($brandSelected)
        <!-- Accounts List (when brand is selected) -->
        <div class="ajax-scroll container px-4" data-url="{{ module_url("list") }}" data-resp=".channel-list" data-scroll="document">
            <div class="row channel-list">
            </div>
            <div class="pb-30 ajax-scroll-loading d-none">
                <div class="app-loading mx-auto mt-100 pl-0 pr-0">
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
            </div>
        </div>
    @else
        <!-- Empty State (when no brand is selected) -->
        <div class="container px-4">
            <div class="row">
                <div class="col-12">
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <svg width="120" height="120" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="mx-auto">
                                <circle cx="60" cy="60" r="50" stroke="#E5E7EB" stroke-width="2" stroke-dasharray="8 8"/>
                                <circle cx="45" cy="45" r="8" fill="#9CA3AF"/>
                                <circle cx="75" cy="45" r="8" fill="#9CA3AF"/>
                                <circle cx="60" cy="75" r="8" fill="#9CA3AF"/>
                                <circle cx="30" cy="60" r="8" fill="#9CA3AF"/>
                                <circle cx="90" cy="60" r="8" fill="#9CA3AF"/>
                                <line x1="45" y1="45" x2="60" y2="75" stroke="#D1D5DB" stroke-width="2"/>
                                <line x1="75" y1="45" x2="60" y2="75" stroke="#D1D5DB" stroke-width="2"/>
                                <line x1="30" y1="60" x2="60" y2="75" stroke="#D1D5DB" stroke-width="2"/>
                                <line x1="90" y1="60" x2="60" y2="75" stroke="#D1D5DB" stroke-width="2"/>
                            </svg>
                        </div>
                        <h4 class="mb-3 fw-6">{{ __('No Social Accounts Connected') }}</h4>
                        <p class="text-muted mb-4">{{ __('Select a brand to manage and track all your accounts in one place.') }}</p>
                        <button class="btn btn-dark btn-lg" onclick="focusBrandDropdown()" type="button">
                            <i class="fa-light fa-arrow-up"></i>
                            {{ __('Select Brand from Top Right') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Add Channels Modal -->
    <div class="modal modal-xl fade" id="addChannelModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered1 modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header px-4">
                    <h1 class="modal-title fs-5">{{ __("Add accounts") }}</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        @if( !empty( $channels ) )
                            @foreach( $channels as $channel )
                                <div class="col-md-4 mb-4">
                                    <div class="card border-gray-300">
                                        <div class="card-body text-center d-flex flex-column justify-content-center align-items-center gap-10">
                                            <div class="d-flex align-items-center justify-content-center size-50 text-white border-1 b-r-100 fs-16" style="background-color: {{ $channel['color'] }};">
                                                <i class="{{ $channel['icon'] }}"></i>
                                            </div>
                                            <div class="fs-14 fw-5">{{ __($channel['name']) }}</div>
                                            <div>
                                                @if( !empty( $channel ) && isset( $channel['items']  ) )
                                                    @foreach( $channel['items'] as $item )
                                                        <a href="{{ url($item["uri"]) }}" class="btn btn-outline btn-sm btn-light mb-1">
                                                            <i class="fa-light fa-plus"></i> {{ __( ucfirst( str_replace("_", " ", $item["category"]) ) ) }}
                                                        </a>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inline Script - Available Immediately -->
    <style>
        .animate-pulse-brand {
            animation: pulseBrand 1s ease-in-out 3;
            position: relative;
            z-index: 9999;
        }

        @keyframes pulseBrand {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
                border-color: #3b82f6 !important;
            }
            50% {
                box-shadow: 0 0 0 10px rgba(59, 130, 246, 0);
                border-color: #3b82f6 !important;
            }
        }

        input:disabled,
        select:disabled,
        button:disabled {
            cursor: not-allowed;
            opacity: 0.6;
        }
    </style>

    <script>
        // Define functions in global scope immediately
        window.showBrandRequiredMessage = function() {
            // Check if Swal (SweetAlert2) is available
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: '{{ __("Brand Selection Required") }}',
                    html: '{{ __("Please select a brand from the dropdown in the top-right corner before adding accounts.") }}',
                    confirmButtonText: '{{ __("OK, Got it!") }}',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
            } else {
                // Fallback to native alert if SweetAlert2 is not available
                alert('{{ __("Please select a brand from the dropdown in the top-right corner before adding accounts.") }}');
            }
            
            // Highlight the brand dropdown
            window.focusBrandDropdown();
        };

        window.focusBrandDropdown = function() {
            // Try multiple possible selectors for the brand dropdown
            const possibleSelectors = [
                '.brand-selector',
                '[data-brand-dropdown]',
                'select[name="brand"]',
                '.select-brand',
                '#brand-select',
                '.dropdown-toggle:has-text("Brand")',
                'button:has-text("Brand")',
                // Common class patterns
                '[class*="brand-"]',
                '[id*="brand"]',
            ];
            
            let brandDropdown = null;
            for (const selector of possibleSelectors) {
                try {
                    brandDropdown = document.querySelector(selector);
                    if (brandDropdown) {
                        console.log('Found brand dropdown with selector:', selector);
                        break;
                    }
                } catch(e) {
                    // Invalid selector, continue
                    continue;
                }
            }
            
            if (brandDropdown) {
                // Add pulsing animation
                brandDropdown.classList.add('animate-pulse-brand');
                
                // Scroll into view
                brandDropdown.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
                
                // Try to focus
                setTimeout(function() {
                    brandDropdown.focus();
                    
                    // Try to open dropdown if it's a select
                    if (brandDropdown.tagName === 'SELECT') {
                        if (brandDropdown.showPicker) {
                            brandDropdown.showPicker();
                        }
                    }
                    // Try to trigger Bootstrap dropdown
                    else if (brandDropdown.hasAttribute('data-bs-toggle')) {
                        brandDropdown.click();
                    }
                }, 300);
                
                // Remove animation after 3 seconds
                setTimeout(function() {
                    brandDropdown.classList.remove('animate-pulse-brand');
                }, 3000);
            } else {
                console.warn('Brand dropdown not found. Please inspect your HTML and update the selector.');
                console.log('Looking for elements containing "brand" in class or id:');
                
                // Debug: Log all possible brand-related elements
                const debugElements = document.querySelectorAll('[class*="brand"], [id*="brand"]');
                debugElements.forEach(function(el) {
                    console.log('Found element:', el.tagName, el.className, el.id);
                });
            }
        };

        // Initialize when DOM is ready
        (function() {
            const brandSelected = {{ $brandSelected ? 'true' : 'false' }};
            
            // Wait for DOM to be ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initBrandCheck);
            } else {
                initBrandCheck();
            }
            
            function initBrandCheck() {
                // Prevent modal from opening if no brand selected
                if (!brandSelected) {
                    const modalTriggers = document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target="#addChannelModal"]');
                    modalTriggers.forEach(function(trigger) {
                        trigger.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            window.showBrandRequiredMessage();
                        });
                    });
                    
                    // Prevent channel links in modal from working
                    const addChannelModal = document.getElementById('addChannelModal');
                    if (addChannelModal) {
                        addChannelModal.addEventListener('show.bs.modal', function(e) {
                            if (!brandSelected) {
                                e.preventDefault();
                                window.showBrandRequiredMessage();
                            }
                        });
                    }
                }
            }
        })();
    </script>
@endsection