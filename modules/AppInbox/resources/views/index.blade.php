@extends('layouts.app')

@section('title', $title ?? 'Inbox')

@section('content')
<div class="container-fluid p-x-25">
    <div class="row">
        <!-- Filter Sidebar -->
        <div class="col mw-300">
            <div class="my-4">
                <h2 class="d-flex icon-primary mb-0">
                    <i class="fa-light fa-filter me-2"></i> Filter
                </h2>
            </div>
            
            <div class="b-r-30 border bg-white p-20">
                <form id="filter_form">
                    <input type="hidden" id="pagenos" name="page" value="1" />

                    <div class="px-3 pb-3 border-bottom filtered-list" style="display:none;">
                        <ul class="list-unstyled one-column scrollable mh-100 load-filter-text mb-0">
                        </ul>
                    </div>

                    <div class="accordion accordion-flush my-2" id="accordionFlushExample">
                        <!-- Brand Filter -->
                        <div class="accordion-item b-r-25 border overflow-hidden mb-3">
                            <h2 class="accordion-header" id="flush-headingBrand">
                                <div class="accordion-button fw-7 collapsed" type="button" data-bs-toggle="collapse" 
                                     data-bs-target="#flush-collapseBrand" aria-expanded="false">
                                    Brand
                                </div>
                            </h2>
                            <div id="flush-collapseBrand" class="accordion-collapse collapse" aria-labelledby="flush-headingBrand">
                                <div class="accordion-body">
                                    @if(!empty($brands_list))
                                        <ul class="list-unstyled symbol py-1">
                                            @foreach($brands_list as $brand)
                                                <li class="py-1 d-flex">
                                                    <input type="checkbox" name="brand[]" value="{{ $brand->id }}" class="me-2">
                                                    <label class="form-check-label">{{ $brand->name }}</label>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- User Filter -->
                        <div class="accordion-item b-r-25 border overflow-hidden mb-3">
                            <h2 class="accordion-header" id="flush-headingUser">
                                <div class="accordion-button fw-7 collapsed" type="button" data-bs-toggle="collapse" 
                                     data-bs-target="#flush-collapseUser" aria-expanded="false">
                                    User
                                </div>
                            </h2>
                            <div id="flush-collapseUser" class="accordion-collapse collapse" aria-labelledby="flush-headingUser">
                                <div class="accordion-body">
                                    @if(!empty($users_list))
                                        <ul class="list-unstyled symbol py-1">
                                            @foreach($users_list as $user)
                                                <li class="py-1 d-flex">
                                                    <input type="checkbox" name="users[]" value="{{ $user->id }}" class="me-2">
                                                    <label class="form-check-label">{{ $user->fullname }}</label>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Tags Filter -->
                        <div class="accordion-item b-r-25 border overflow-hidden mb-3">
                            <h2 class="accordion-header" id="flush-headingTags">
                                <div class="accordion-button fw-7 collapsed" type="button" data-bs-toggle="collapse" 
                                     data-bs-target="#flush-collapseTags" aria-expanded="false">
                                    Tags
                                </div>
                            </h2>
                            <div id="flush-collapseTags" class="accordion-collapse collapse" aria-labelledby="flush-headingTags">
                                <div class="accordion-body">
                                    @if(!empty($tags_list))
                                        <ul class="list-unstyled symbol py-1 load-tag-list">
                                            @foreach($tags_list as $tag)
                                                <li class="py-1 d-flex">
                                                    <input type="checkbox" name="tags[]" value="{{ $tag->id }}" class="me-2">
                                                    <label class="form-check-label">{{ $tag->tag_name }}</label>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Accounts Filter -->
                        <div class="accordion-item b-r-25 border overflow-hidden mb-3">
                            <h2 class="accordion-header" id="flush-headingAccounts">
                                <div class="accordion-button fw-7 collapsed" type="button" data-bs-toggle="collapse" 
                                     data-bs-target="#flush-collapseAccounts" aria-expanded="false">
                                    Profiles
                                </div>
                            </h2>
                            <div id="flush-collapseAccounts" class="accordion-collapse collapse" aria-labelledby="flush-headingAccounts">
                                <div class="accordion-body">
                                    @if(!empty($accounts))
                                        <ul class="list-unstyled symbol py-1">
                                            @foreach($accounts as $account)
                                                <li class="py-0 d-flex align-items-center">
                                                    <input type="checkbox" name="accounts[]" value="{{ $account->id }}" class="me-2">
                                                    <div class="symbol symbol-35px px-3 py-2">
                                                        <img src="{{ $account->avatar }}" style="width:25px; height:25px" 
                                                             class="rounded-circle align-self-center" alt="">
                                                    </div>
                                                    <span class="text-truncate">{{ $account->name }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="button" class="btn btn-primary w-100" onclick="applyFilter()">
                            Apply Filter
                        </button>
                        <button type="button" class="btn btn-secondary w-100 mt-2" onclick="resetFilter()">
                            Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="col">
            <div class="row">
                <!-- Inbox List -->
                <div class="col-md-5">
                    <div class="bg-white b-r-30 p-20">
                        <h3 class="mb-3">Inbox</h3>
                        <div id="inbox-list-container">
                            <!-- Inbox list will be loaded here via AJAX -->
                            <div class="text-center py-5">
                                <i class="fa-light fa-spinner fa-spin fa-3x"></i>
                                <p class="mt-3">Loading inbox...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail View -->
                <div class="col-md-7">
                    <div class="bg-white b-r-30 p-20">
                        <div id="inbox-detail-container">
                            <!-- Detail view will be loaded here via AJAX -->
                            <div class="text-center py-5 text-muted">
                                <i class="fa-light fa-inbox fa-3x"></i>
                                <p class="mt-3">Select a conversation to view details</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        loadInboxList();
    });

    function loadInboxList(page = 1) {
        const formData = $('#filter_form').serialize() + '&page=' + page;
        
        $.ajax({
            url: '{{ route("inbox.ajax_list") }}',
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#inbox-list-container').html(response.list);
                $('#inbox-detail-container').html(response.list_detail);
                
                if (response.filter_text) {
                    $('.filtered-list').show();
                    $('.load-filter-text').html(response.filter_text);
                } else {
                    $('.filtered-list').hide();
                }
            },
            error: function(xhr) {
                console.error('Error loading inbox:', xhr);
                $('#inbox-list-container').html('<div class="alert alert-danger">Error loading inbox. Please try again.</div>');
            }
        });
    }

    function applyFilter() {
        loadInboxList(1);
    }

    function resetFilter() {
        $('#filter_form')[0].reset();
        loadInboxList(1);
    }

    function pagechange(page) {
        $('#pagenos').val(page);
        loadInboxList(page);
    }
</script>
@endpush
@endsection
