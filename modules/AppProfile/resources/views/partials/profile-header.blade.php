<div class="border-bottom mb-1 py-4 bg-polygon subHeader">
    
    <div class="container">
		<div class="d-flex justify-content-between gap-8 align-items-center">
			<div class="d-flex justify-content-start align-items-center">
				<div class="size-90 size-child b-r-100 me-3 border border-primary-200 p-1">
					<img src="{{ Media::url($user->avatar) }}" class="b-r-100">
				</div>
				<div class="d-flex flex-column">
					<div class="fw-7 fs-20 mb-2">{{ $user->fullname }}</div>

					<div class="d-flex flex-nowrap align-self-center mx-auto gap-16 fs-14 text-black">
						<div class="d-flex gap-8 align-self-center fw-5"><span class="fs-16"><i class="fw-4 fa-light fa-user"></i></span> <span>{{ $user->username }}</span></div>
						<div class="d-flex gap-8 align-self-center fw-5"><span class="fs-16"><i class="fw-4 fa-light fa-envelope"></i></span> <span>{{ $user->email }}</span></div>
						<div class="d-flex gap-8 align-self-center fw-5"><span class="fs-16"><i class="fw-4 fa-light fa-box-open"></i></span> <span>{{ $user->plan->name ?? __("No Plan") }}</span></div>
					</div>
				</div>
			</div>
			<nav class="flex items-center justify-center">
				<div class="nav nav-tabs border-bottom-0">
					<a href="{{ module_url() }}" class="nav-link fw-5 px-2 py-1 fs-14 {{ Request::segment(3) == ""?"active":"" }}" >
						<i class="fa-light fa-user me-1 fs-16 fw-4"></i> {{ __('Account') }}
					</a>
					<a href="{{ module_url('plan') }}" class="nav-link fw-5 px-2 py-1 fs-14 {{ Request::segment(3) == "plan"?"active":"" }}">
						<i class="fa-light fa-box-open me-1 fs-16 fw-4"></i> {{ __('Plan') }}
					</a>
					<a href="{{ module_url('billing') }}" class="nav-link fw-5 px-2 py-1 fs-14 {{ Request::segment(3) == "billing"?"active":"" }}">
						<i class="fa-light fa-file-invoice me-1 fs-16 fw-4"></i> {{ __('Billing') }}
					</a>
					<a href="{{ module_url('settings') }}" class="nav-link fw-5 px-2 py-1 fs-14 d-none {{ Request::segment(3) == "settings"?"active":"" }}">
						<i class="fa-light fa-gear me-1 fs-16 fw-4"></i> {{ __('Settings') }}
					</a>
				</div>
			</nav>
		</div>
    </div>

</div>