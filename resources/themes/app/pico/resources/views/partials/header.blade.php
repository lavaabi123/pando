<div class="header">
    <div class="container1 px-3 hp-100">
        <div class="hp-100 d-flex justify-content-between">
		
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

            <div class="d-flex justify-content-between align-items-center">			
                <div class="d-block d-sm-block d-md-none">
                    <button class="btn btn-icon btn-light sidebar-toggle">
                        <i class="fa-light fa-chevron-right"></i>
                    </button>
                </div>
                @foreach(\HeaderManager::getHeaderItems('start') as $headerItem)
                    @php
                        $isVisible = $headerItem['visible'] ?? fn() => true;
                    @endphp
                    @if($isVisible())
                        {!! is_callable($headerItem['item']) ? $headerItem['item']() : $headerItem['item'] !!}
                    @endif
                @endforeach

                @yield('header_start')
            </div>

            <div class="d-flex flex-grow-1 justify-content-between wp-100">
				<ul class="top-menu d-flex align-items-center mb-0 ms-4">
					<li><a class="icons active" href="#">{!! file_get_contents(public_path('img/post.svg')) !!}</a></li>
					<li><a class="icons" href="#">{!! file_get_contents(public_path('img/notification.svg')) !!}</a></li>
					<li><a class="icons" href="#">{!! file_get_contents(public_path('img/add.svg')) !!}</a></li>
					<li><a class="icons" href="#">{!! file_get_contents(public_path('img/note.svg')) !!}</a></li>
					<li><a class="icons" href="#">{!! file_get_contents(public_path('img/inbox.svg')) !!}</a></li>
				</ul>
				
                @foreach(\HeaderManager::getHeaderItems('center') as $headerItem)
                    @php
                        $isVisible = $headerItem['visible'] ?? fn() => true;
                    @endphp
                    @if($isVisible())
                        {!! is_callable($headerItem['item']) ? $headerItem['item']() : $headerItem['item'] !!}
                    @endif
                @endforeach

                @yield('header_center')
            </div>

            <div class="d-flex align-items-center gap-16">
                @yield('header_end')

                @foreach(\HeaderManager::getHeaderItems('end') as $headerItem)
                    @php
                        $isVisible = $headerItem['visible'] ?? fn() => true;
                    @endphp
                    @if($isVisible())
                        {!! is_callable($headerItem['item']) ? $headerItem['item']() : $headerItem['item'] !!}
                    @endif
                @endforeach
                
            </div>
        </div>
    </div>
</div>