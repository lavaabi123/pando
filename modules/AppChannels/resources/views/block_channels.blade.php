@php
$permission = $permission ?? 'appchannels';
$teamId = request()->team_id;
$query = DB::table('accounts')->where('status', 1)->where('brand_id',session('brand_id'));
//if ($teamId) $query->where('team_id', $teamId);

if (!empty($accounts) && is_array($accounts)) {
    $query->whereIn("id", $accounts);
}

$channels = $query->get();

$groups = DB::table('groups')->get();
@endphp

<div class="account_manager w-100">
    <div class="am-choice-box">
        <div class="am-selected-box rounded border pf-10 d-flex align-items-center justify-content-between">
            <div class="overflow-y-auto flex-grow-1 max-h-90 me-3">
                <button type="button" class="am-open-list-account"></button>
                <div class="am-selected-empty">
                    <div class="d-flex align-items-center">
                        <span class="svg-icons">{!! file_get_contents(public_path('img/account2.svg')) !!}</span>
                        <span class="fw-5 fs-14">{{ __("Please select a account") }}</span>
                    </div>
                </div>
                <div class="am-selected-list horizontal-scroll d-flex">
                </div>
            </div>
            <div class="am-selected-arrow">
                <i class="fal fa-chevron-up"></i>
            </div>
        </div>

        <div class="am-list-account border rounded bg-white check-wrap-all">
            <div class="p-3">
                <div class="input-group">
                    <div class="form-control">
                        <i class="fa-light fa-magnifying-glass"></i>
                        <input class="search-input" data-search="search-accounts" placeholder="{{ __("Search") }}" type="text" value="">
                    </div>
                    <span class="btn btn-icon btn-input">
                        <div class="form-check">
                            <input class="form-check-input checkbox-all" type="checkbox" value="" data-checkbox-parent=".am-list-account">
                        </div>
                    </span>
                    @if(Access::permission('appgroups'))
                    <div class="dropdown-toggle dropdown-arrow-hide btn btn-icon btn-input" data-bs-toggle="dropdown" aria-expanded="true">
                        <i class="fa-light fa-user-group"></i>
                    </div>
                    <div class="btn-group position-static">
                        <ul class="dropdown-menu dropdown-menu-end border-1 border-gray-300 px-2 w-100 max-w-180">
                            @if ($groups->count())
                                @foreach ($groups as $value)
                                    @php
                                    $accountsArr = is_array($value->accounts) ? $value->accounts : json_decode($value->accounts, true);
                                    @endphp

                                    <li>
                                        <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 text-truncate select-group"
                                            href="javascript:void(0);"
                                            data-accounts='@json($accountsArr)'>
                                            <span class="size-16 me-1 text-center"><i class="fa-light fa-user-check"></i></span>
                                            <span class="text-truncate">{{ $value->name }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            @else
                                <li>
                                    <a href="{{ url_app("groups") }}" class="btn btn-dark btn-sm wp-100">{{ __("Add new") }}</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                    @endif
                </div>
            </div>

            <div class="am-choice-body max-h-400 overflow-auto">
                @if ($channels->count())
                    @foreach ($channels as $value)
                        @can($permission . "." . strtolower($value->module))
                            <div class="search-accounts">
                                <label class="am-choice-item d-flex gap-12 border-top px-3 py-2"
                                    for="am_{{ $value->id }}"
                                    data-pid="{{ $value->pid }}"
                                    data-social-network="{{ $value->social_network }}"
                                    data-avatar="{{ Media::url($value->avatar) }}"
                                    data-username="{{ $value->username }}"
                                    data-name="{{ $value->name }}">
                                    <div class="min-w-20 text-gray-600 size-30 min-w-30 d-flex align-items-center justify-content-between position-relative">
                                        <img src="{{ Media::url($value->avatar) }}" class="wp-100 hp-100 b-r-6 border">
										<span class="position-absolute b-0 r-0">
											<div class="w-100">{!! get_social_media_icon($value->social_network) !!}</div>
										</span>
                                    </div>
                                    <div class="d-flex align-items-center flex-grow-1 text-truncate">
                                        <div class="flex-grow-1 me-2 text-truncate">
                                            <div class="text-gray-800 text-hover-primary fs-12 fw-bold text-truncate">
                                                {{ $value->name }}
                                                {!! $value->login_type != 1 ? '<span class="text-danger-400">'.__("(Unofficial)").'</span>' : '' !!}
                                            </div>
                                            <span class="text-gray-600 fw-semibold d-block fs-10 text-truncate">
                                                {{ __(ucfirst($value->social_network . " " . $value->category)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input checkbox-item" type="checkbox"
                                            id="am_{{ $value->id }}" name="accounts[]"
                                            value="{{ $value->id_secure }}" @checked(isset($accounts) && in_array($value->id, $accounts))>
                                    </div>
                                    <div class="am-choice-item-selected d-none">
                                        <div class="am-selected-item border rounded p-2 me-2 min-w-100 max-w-150 float-start mb-1"
                                            data-id="{{ $value->id_secure }}" data-network="{{ $value->social_network }}">
                                            <div class="d-flex align-items-center gap-6">
                                                <div class="min-w-20 size-20 position-relative">
                                                    <img src="{{ Media::url($value->avatar) }}"
                                                         class="d-flex wp-100 hp-100 b-r-7 {{ $value->login_type!=1 ? 'border-danger-300' : '' }}">
												    <span class="position-absolute t-8 l-8">
														<div class="w-100">{!! get_social_media_icon($value->social_network) !!}</div>
													</span>
                                                </div>
                                                <div class="d-flex align-items-center text-truncate">
                                                    <div class="text-gray-800 fs-12 fw-bold text-truncate">{{ $value->name }}</div>
                                                </div>
                                                <a href="javascript:void(0);" class="d-flex align-items-center m-r-10 remove">
                                                    <div class="text-gray-800 text-hover-danger fs-12 fw-bold ps-1"><i class="fal fa-times"></i></div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        @endcan
                    @endforeach
                @endif
            </div>

            <div class="am-choice-footer border-top pf-15">
                <a href="{{ route("app.channels.add") }}" class="btn btn-dark w-100">
                    <i class="fal fa-plus"></i> {{ __("Connect a account") }}
                </a>
            </div>
        </div>
    </div>
</div>