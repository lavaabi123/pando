@extends('layouts.app')
@include('apppublishing::header_center', [])

@section('sub_header')
    <x-sub-header 
        title="{{ __('Approval') }}" 
        description="{{ __('See all your approvals in one place for easy access and management.') }}" 
    >
    </x-sub-header>
@endsection

@section('header_end')
    <div class="compose_header position-absolute w-100 t-0 l-0 d-flex justify-content-between align-items-center zIndex-9 bg-white h-70 border-bottom px-4 d-none">
        <div class="fw-6 fs-18">{{ __("New Post") }}</div>
        <div class="fw-6 fs-18">
            <div class="btn btn-icon btn-light btn-hover-danger b-r-50 a-rotate closeCompose">
                <i class="fa-light fa-xmark"></i>
            </div>
        </div>
    </div>
    <a class="btn btn-dark btn-sm actionItem b-r-50 text-nowrap" href="{{ url_app("publishing/composer") }}" data-append-content="composer-scheduling" data-call-success="AppPubishing.openCompose();"><i class="fa-light fa-calendar-lines-pen"></i> {{ __("Compose") }}</a>
@endsection

@section('content')
    <div class="composer-scheduling position-absolute zIndex-9 wp-100 hp-100 top-0 d-none"></div>
    <div class="container">

        {{-- SELECT ALL + EXPORT TOOLBAR --}}
        <div class="d-flex align-items-center gap-3 pt-3 pb-2" id="approval-toolbar">
            {{-- Select All --}}
            <label class="d-flex align-items-center gap-2 mb-0" style="cursor:pointer;">
                <input type="checkbox" id="selectAllApprovals" style="width:20px;height:20px;border-radius:50%;cursor:pointer;accent-color:#ec4899;">
                <span class="fw-6 fs-14">{{ __('Select All') }}</span>
            </label>

            {{-- Export PDF Button --}}
            <button id="exportPdfBtn" onclick="exportApprovalsPdf()"
                    class="btn btn-sm b-r-50 text-nowrap"
                    style="background:linear-gradient(135deg,#f472b6,#ec4899);color:#fff;border:none;opacity:0.45;pointer-events:none;transition:opacity 0.2s;">
                <i class="fa-light fa-file-pdf me-1"></i> {{ __('Export selected posts as PDF') }}
            </button>

        </div>

        <div class="ajax-scroll" data-url="{{ module_url("list") }}" data-resp=".approval-list" data-scroll="document" data-call-success="initHorizontalScroll(); initApprovalCheckboxes();">

            <div class="row approval-list">
                <div class="mb-50"></div>
            </div>

            <div class="pb-30 ajax-scroll-loading d-none">
                <div class="app-loading mx-auto mt-10 pl-0 pr-0">
                    <div></div>   
                    <div></div>    
                    <div></div>    
                    <div></div>    
                </div>
            </div>
        </div>

    </div>

    {{-- Hidden PDF form --}}
    <iframe name="pdfDownloadFrame" id="pdfDownloadFrame" style="display:none;width:0;height:0;border:0;position:absolute;left:-9999px;"></iframe>

    <script>
    function initApprovalCheckboxes() {
        document.querySelectorAll('.checkbox-item').forEach(function(cb) {
            cb.removeEventListener('change', _onApprovalCheck);
            cb.addEventListener('change', _onApprovalCheck);
        });
        _updateApprovalToolbar();
    }

    function _onApprovalCheck() {
        _updateApprovalToolbar();
    }

    function _updateApprovalToolbar() {
        var all     = document.querySelectorAll('.checkbox-item');
        var checked = document.querySelectorAll('.checkbox-item:checked');
        var count   = checked.length;

        // PDF button
        var pdfBtn = document.getElementById('exportPdfBtn');
        var delBtn = document.getElementById('deleteSelectedBtn');
        if (count > 0) {
            pdfBtn.style.opacity = '1';
            pdfBtn.style.pointerEvents = 'auto';
            delBtn.style.opacity = '1';
            delBtn.style.pointerEvents = 'auto';
        } else {
            pdfBtn.style.opacity = '0.45';
            pdfBtn.style.pointerEvents = 'none';
            delBtn.style.opacity = '0.45';
            delBtn.style.pointerEvents = 'none';
        }

        // Select All state
        var sa = document.getElementById('selectAllApprovals');
        if (count === 0) {
            sa.checked = false;
            sa.indeterminate = false;
        } else if (count === all.length) {
            sa.checked = true;
            sa.indeterminate = false;
        } else {
            sa.checked = false;
            sa.indeterminate = true;
        }
    }

    // Select All toggle
    document.getElementById('selectAllApprovals').addEventListener('change', function() {
        document.querySelectorAll('.checkbox-item').forEach(function(cb) {
            cb.checked = this.checked;
        }.bind(this));
        _updateApprovalToolbar();
    });

    // Export PDF
    function exportApprovalsPdf() {
		var checked = document.querySelectorAll('.checkbox-item:checked');
		if (!checked.length) return;
		
		var ids = Array.from(checked).map(cb => cb.dataset.id || cb.value).filter(Boolean);
		
		var form = document.createElement('form');
		form.method = 'POST';
		form.action = '{{ route("app.publishing.approval.pdf") }}';
		form.target = 'pdfDownloadFrame';  // must match iframe name attribute
		form.style.display = 'none';
		
		var csrf = document.createElement('input');
		csrf.type = 'hidden';
		csrf.name = '_token';
		csrf.value = '{{ csrf_token() }}';
		form.appendChild(csrf);
		
		var idsInput = document.createElement('input');
		idsInput.type = 'hidden';
		idsInput.name = 'ids';
		idsInput.value = ids.join(',');
		form.appendChild(idsInput);
		
		document.body.appendChild(form);
		form.submit();
		document.body.removeChild(form);
	}

    // Delete selected
    function deleteSelectedApprovals() {
        var checked = document.querySelectorAll('.checkbox-item:checked');
        if (!checked.length) return;
        if (!confirm('{{ __("Are you sure to delete selected posts?") }}')) return;
        var ids = Array.from(checked).map(cb => cb.dataset.id || cb.value).filter(Boolean);
        // Use existing destroy mechanism
        fetch('{{ url_app("publishing/destroy") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ id: ids })
        }).then(() => location.reload());
    }

    // Init on first load
    document.addEventListener('DOMContentLoaded', initApprovalCheckboxes);
    </script>
@endsection
