@php
$providers = \AI::getPlatforms();
$modelList = [];
foreach ($providers as $provider => $title) {
    $modelList[$provider] = \AI::getAvailableModels($provider);
}

@endphp

<div class="card shadow-none border-gray-300 mb-4">
    <div class="card-header fw-6">{{ __("AI Model Rates") }}</div>
	    <div class="card-body">
	    	<div class="card shadow-none border-gray-300 mb-4">
		        <div class="card-body">
		            <ul class="mb-0 fs-14">
		                <li>
		                    <b>{{ __("Purpose:") }}</b>
		                    {{ __("Customize the conversion rate from token to credit for each AI model to control AI usage costs in your system.") }}
		                </li>
		                <li class="mt-3">
		                    <b>{{ __("How to use:") }}</b>
		                    {{ __("For each model, enter the number of credits that will be deducted for each token used.") }}<br>
		                    <span class="text-900">{{ __("Example:") }}</span> <b>1</b> {{ __("means 1 token = 1 credit (default);") }} <b>20</b> {{ __("means 20 token = 1 credits (using this model will cost double).") }}
		                </li>
		                <li class="mt-3">
		                    <b>{{ __("Note:") }}</b>
		                    {{ __("If you leave a field blank, the system will use the default value of 1 credit/token.") }}<br>
		                    {{ __("You can adjust this rate at any time to suit your pricing strategy or cost control needs.") }}
		                </li>
		            </ul>
		        </div>
		    </div>
		@foreach($providers as $provider => $title)
	    	<div class="fw-6 mb-3 mt-20 fs-18 text-primary">{{ __($title) }}</div>
	        <div class="row">
	            @foreach($modelList[$provider] as $model => $desc)
	                <div class="col-md-12 mb-2">
	                    <div class="p-2 border rounded d-flex justify-content-between align-items-center fs-14 gap-16">
	                        <div>
	                            <div class="fw-5">{{ $model }}</div>
	                            <small class="text-muted fs-12">{{ $desc }}</small>
	                        </div>
	                        <div>
	                            <input type="number" step="0.01" min="0.01"
	                                class="form-control text-end w-70"
	                                name="credit_rates[{{ $model }}]"
	                                value="{{ $rates[$model] ?? 1 }}"
	                                required>
	                        </div>
	                    </div>
	                </div>
	            @endforeach
	        </div>
		@endforeach
	    </div>
</div>