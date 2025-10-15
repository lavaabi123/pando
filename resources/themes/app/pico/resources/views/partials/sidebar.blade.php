<div class="sidebar hide-scroll">

    <div class="sidebar-header d-flex align-items-center px-3 position-relative">
        <div class="min-h-22px overflow-hidden">
            <a href="{{ session('login_as') == "admin" ? route("admin.dashboard") : route("app.dashboard") }}">
                <div class="logo-small">
                    <img src="{{ url( get_option("website_logo_dark", asset('public/img/logo-dark.png')) ) }}" class="max-w-180 max-h-35">
                </div>
                <div class="logo-large">
                    <img src="{{ url( get_option("website_logo_brand_dark", asset('public/img/logo-brand-dark.png')) ) }}" class="max-w-180 max-h-35">
                </div>
            </a>
        </div>

        <div class="sidebar-toggle bg-light">
            <a href="javascript:void(0)" class="d-flex align-items-center justify-content-center border-1 px-2 rounded size-24 fs-14 d-block text-gray-500"><i class="fa-duotone fa-arrow-right-to-line"></i></a>
        </div>
    </div>
@php
use Illuminate\Support\Facades\DB;

$userId = session('user_id');

// Get role once (role=2 => Super Admin)
$role = DB::table('users')->where('id', $userId)->value('role');

if ((int)$role === 2) {
    // SUPER ADMIN: see every brand
    $brands = DB::table('brands')
        ->orderBy('name')
        ->get();

} else {
    // Determine if this user is a team member and get effective team_id
    $memberRow = DB::table('team_members')
        ->select('team_id')
        ->where('uid', $userId)
        ->first();

    $isMember = (bool) $memberRow;
    $teamId   = $isMember ? $memberRow->team_id : $userId;

    if (!$isMember) {
        // TEAM ADMIN: see all brands in this team
        $brands = DB::table('brands')
            ->where('team_id', $teamId)
            ->orderBy('name')
            ->get();
    } else {
        // TEAM MEMBER: see brands created by me OR assigned to me (within team)
        $brands = DB::table('brands as b')
            ->leftJoin('user_brands as ub', function ($join) use ($userId, $teamId) {
                $join->on('ub.brand_id', '=', 'b.id')
                     ->where('ub.user_id', '=', $userId)
                     ->where('ub.team_id', '=', $teamId);
            })
            ->where('b.team_id', $teamId)
            ->where(function ($q) use ($userId) {
                $q->where('b.user_id', $userId)      // created by me
                  ->orWhereNotNull('ub.user_id');     // assigned to me
            })
            ->select('b.*')
            ->distinct()
            ->orderBy('b.name')
            ->get();
    }
}
@endphp

			<select class="form-control" id="brandSelect"
        data-control="select2"
        data-change-url="{{ route('brand.change') }}"
        data-placeholder="Select Brand">
    <option></option> {{-- allows placeholder --}}
    @if(!empty($brands))
        @foreach($brands as $brand)
            <option
                value="{{ $brand->id }}"
                {{ session('brand_id') == $brand->id ? 'selected' : '' }}

                {{-- optional UX data --}}
                data-avatar="{{ $brand->logo_url ?? '' }}"
                data-badge="{{ $brand->unread_count ?? '' }}"
                data-fav="{{ !empty($brand->is_favorite) ? 1 : 0 }}"
                data-recent="{{ !empty($brand->is_recent) ? 1 : 0 }}"
            >
                {{ $brand->name }}
            </option>
        @endforeach
    @endif
</select>
    <div class="menu d-flex flex-column align-items-lg-center flex-row-auto" id="accordionMenu">
        
        @if( isset( $sidebar['top'] ) )
            <div class="menu-top d-flex flex-column flex-column-fluid w-100 mb-2">

                @php
                    $current_tab = 0;
                @endphp

                @foreach( $sidebar['top'] as $key => $value )

                    @if( $current_tab == 0 && isset( $value['tab_name'] ) )
                        <div class="menu-item pt-2">
                            <div class="menu-heading"><span>{{ __($value['tab_name']) }}</span></div>
                        </div>
                    @endif
                
                    @if($value['tab_id'] != $current_tab && $current_tab)
                        <div class="menu-item h-1 bg-gray-200 my-2"> </div>

                        @if( isset( $value['tab_name'] ) )
                        <div class="menu-item pt-2">
                            <div class="menu-heading"><span>{{ __($value['tab_name']) }}</span></div>
                        </div>
                        @endif
                    @endif

                    @php 
                        $current_tab = $value['tab_id'];
                    @endphp

                    @if(!isset($value['sub_menu']))
                        <div class="menu-item">
                            <a class="menu-link flex-grow-1 align-items-center {{ menu_active($value['uri']) ? 'text-primary' : '' }}" href="{{ url( $value['uri'] ) }}">
                                <div class="menu-icon fs-18 {{ menu_active($value['uri']) ? 'text-primary' : 'text-gray-900' }}">
                                    <i class="{{ $value['icon'] }}" {!! Core::sidebarColor() !!}></i>
                                </div>
                                <div class="menu-title d-flex align-items-center flex-grow-1 fs-14 fw-5 text-truncate" {!! menu_active($value['uri']) ? Core::sidebarColor() : '' !!}>
                                    <div class="text-truncate">
                                        {{ __($value['name']) }}
                                    </div>
                                </div>
                            </a>
                        </div>
                    @else
                        @php
                            $sub_menus = $value['sub_menu'];
                            $is_sub_active = false;
                            foreach($sub_menus as $sub_menu) {
                                if(menu_active($sub_menu['uri'])) {
                                    $is_sub_active = true;
                                    break;
                                }
                            }
                        @endphp
                        <div class="menu-item">
                            <div class="menu-link flex-grow-1 align-items-center collapsed {{ $is_sub_active ? 'text-primary' : '' }}" data-bs-toggle="collapse" data-bs-target="#menu-{{ $key }}">
                                <div class="menu-icon fs-18 {{ $is_sub_active ? 'text-primary' : 'text-gray-900' }}">
                                    <i class="{{ $value["icon"] }}" {!! Core::sidebarColor() !!}></i>
                                </div>
                                <div class="menu-title d-flex align-items-center flex-grow-1 fs-14 fw-5" {!! $is_sub_active ? Core::sidebarColor() : '' !!}>
                                    {{ __($value["name"]) }}
                                </div>
                                <div class="menu-arrow">
                                    <i class="fa-duotone fa-plus menu-item-show"></i>
                                    <i class="fa-duotone fa-minus menu-item-hide"></i>
                                </div>
                            </div>

                            <div  id="menu-{{ $key }}" class="menu-accordion accordion-collapse collapse {{ $is_sub_active ? 'show' : '' }}" data-bs-parent="#accordionMenu">
                                @foreach($sub_menus as $sub_menu)
                                <div class="menu-item">
                                    <a class="menu-link {{ menu_active($sub_menu['uri']) ? 'active text-primary' : '' }}" href="{{ url($sub_menu['uri']) }}">
                                        <span class="menu-bullet"></span>
                                        <span class="menu-title d-flex align-items-center flex-grow-1 fs-13">{{ __($sub_menu['name']) }}</span>
                                    </a>
                                </div>
                                @endforeach
                            </div>
                        </div>

                    @endif

                @endforeach

            </div>
        @endif

        @if( isset( $sidebar['bottom'] ) )
            <div class="menu-bottom d-flex flex-column flex-column-fluid w-100 mt-auto">

                @foreach( $sidebar['bottom'] as $value )

                    <div class="menu-item">
                        <a href="{{ url( $value['uri'] ) }}" class="menu-link flex-grow-1 align-items-center">
                            <div class="menu-icon text-gray-600 fs-18">
                                <i class="{{ $value['icon'] }}" {!! Core::sidebarColor() !!}></i>
                            </div>
                            <div class="menu-title d-flex align-items-center flex-grow-1 fs-14 fw-5" {!! menu_active($value['uri']) ? Core::sidebarColor() : '' !!}>
                                {{ __($value['name']) }}
                            </div>
                        </a>
                    </div>

                @endforeach
     
                @foreach(\Core::getSidebarBlocks() as $sidebarItem)
                    <div class="sidebar-blocks">
                        @php
                            $isVisible = $sidebarItem['visible'] ?? fn() => true;
                        @endphp
                        @if($isVisible())
                            {!! is_callable($sidebarItem['item']) ? $sidebarItem['item']() : $sidebarItem['item'] !!}
                        @endif
                    </div>
                @endforeach
            </div>

        @endif
    </div>

</div>