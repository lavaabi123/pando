{{-- Export All Accounts PDF Button --}}
{{-- Usage: Include this in your Manage Accounts view or Reports page --}}

<div class="export-all-container" style="margin-bottom: 20px;">
    <form method="GET" action="{{ route('analytics.export.all.pdf') }}" id="exportAllForm">
        {{-- Date Range Inputs --}}
        <div class="date-range-selector" style="display: inline-block; margin-right: 15px;">
            <label for="date_range" style="margin-right: 8px; font-weight: 500;">{{ __('Date Range') }}:</label>
            <select name="date_range" id="date_range" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="last_7_days">{{ __('Last 7 Days') }}</option>
                <option value="last_30_days" selected>{{ __('Last 30 Days') }}</option>
                <option value="last_90_days">{{ __('Last 90 Days') }}</option>
                <option value="this_month">{{ __('This Month') }}</option>
                <option value="last_month">{{ __('Last Month') }}</option>
                <option value="custom">{{ __('Custom Range') }}</option>
            </select>
        </div>

        {{-- Custom Date Inputs (hidden by default) --}}
        <div class="custom-date-inputs" id="customDateInputs" style="display: none; margin-right: 15px;">
            <input type="date" name="start_date" id="start_date" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-right: 5px;">
            <span style="margin: 0 5px;">{{ __('to') }}</span>
            <input type="date" name="end_date" id="end_date" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-right: 10px;">
        </div>

        {{-- Export Button --}}
        <button type="submit" class="btn-export-all" style="
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        ">
            <svg style="width: 16px; height: 16px; margin-right: 8px; vertical-align: middle; display: inline-block;" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"></path>
            </svg>
            {{ __('Export All Analytics PDF') }}
        </button>
    </form>
</div>

<style>
.btn-export-all:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.btn-export-all:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateRangeSelect = document.getElementById('date_range');
    const customDateInputs = document.getElementById('customDateInputs');
    
    dateRangeSelect.addEventListener('change', function() {
        if (this.value === 'custom') {
            customDateInputs.style.display = 'inline-block';
            document.getElementById('start_date').required = true;
            document.getElementById('end_date').required = true;
        } else {
            customDateInputs.style.display = 'none';
            document.getElementById('start_date').required = false;
            document.getElementById('end_date').required = false;
        }
    });
    
    // Form submission handler
    document.getElementById('exportAllForm').addEventListener('submit', function(e) {
        // Show loading state
        const btn = this.querySelector('.btn-export-all');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span style="display: inline-block; width: 16px; height: 16px; border: 2px solid #fff; border-top-color: transparent; border-radius: 50%; animation: spin 0.6s linear infinite; margin-right: 8px; vertical-align: middle;"></span> Generating PDF...';
        
        // Re-enable button after a delay (in case download doesn't trigger page unload)
        setTimeout(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }, 3000);
    });
});
</script>

<style>
@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>
