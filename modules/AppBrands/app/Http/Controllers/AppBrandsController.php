<?php

namespace Modules\AppBrands\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Pagination\Paginator;

class AppBrandsController extends Controller
{
    protected $table = 'brands';

    public function index(Request $request)
    {
        $total = DB::table($this->table)
            ->where('team_id', $request->team_id)
            ->count();

        return view('appbrands::index', [
            'total'  => $total,
            'module' => $request->module,
        ]);
    }

    public function list(Request $request)
    {
        $search = $request->input('keyword');
        $module_name = $request->input('module_name');
        $current_page = $request->input('page') + 1;

        Paginator::currentPageResolver(fn() => $current_page);



$userId = session('user_id');

// Get role once (role=2 => Super Admin)
$role = DB::table('users')->where('id', $userId)->value('role');

if ((int)$role === 2) {
    // SUPER ADMIN: see every brand
    $query = DB::table('brands')
        ->orderBy('name');

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
        $query = DB::table('brands')
            ->where('team_id', $teamId)
            ->orderBy('name');
    } else {
        // TEAM MEMBER: see brands created by me OR assigned to me (within team)
        $query = DB::table('brands as b')
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
            ->orderBy('b.name');
    }
}
        //$query = DB::table($this->table)->where('team_id', $request->team_id);

        if ($search) {
            $query->whereAny(['name'], 'like', "%$search%");
        }

        $items = $query->paginate(30);
//print_r($items);exit;
        if ($items->total() === 0 && $current_page > 1) {
            return ms(['status' => 0]);
        }

        return ms([
            'status' => 1,
            'data' => view('appbrands::list', [
                'captions' => $items
            ])->render(),
        ]);
    }

    public function update(Request $request)
    {
        $result = DB::table($this->table)
            ->where('id_secure', $request->id)
            ->first();

        $accounts = DB::table('accounts')
            ->where('team_id', $request->team_id)
            ->get();

        return ms([
            'status' => 1,
            'data' => view('appbrands::update', compact('accounts', 'result'))->render(),
        ]);
    }

    public function save(Request $request)
    {
        $item = DB::table($this->table)
            ->where('id_secure', $request->id_secure)
            ->first();

        $validatorRules = [
            'name' => ['required', Rule::unique($this->table, 'name')],
            'image'      => 'nullable|file|mimes:jpeg,jpg,png,gif,avif,svg|max:2048',
        ];

        if ($item) {
            $validatorRules['name'] = [
                'required',
                Rule::unique($this->table, 'name')->ignore($item->id),
            ];
        }

        $validator = Validator::make($request->all(), $validatorRules);
		$image = '';
		if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $file = $request->file('image');
            $mark = \UploadFile::storeSingleFile($file, 'brands', false, '1:1');
            if ($mark) $image = $mark;
        }

        if (!$validator->passes()) {
            return ms([
                'status' => 0,
                'message' => $validator->errors()->first(),
            ]);
        }

        $values = [
            'team_id'  => $request->team_id,
            'user_id'  => session('user_id'),
            'name'     => $request->input('name'),
			'image'=>$image,
			'primary_name'=>$request->input('primary_name'),
			'primary_email'=>$request->input('primary_email'),
			'primary_number'=>$request->input('primary_number'),
			'notes'=>$request->input('notes'),
            'changed'  => time(),
        ];

        if ($item) {
			$values['image'] = !empty($image) ? $image : $item->image;
            DB::table($this->table)->where('id', $item->id)->update($values);
        } else {
            $values['id_secure'] = rand_string();
            $values['created'] = time();
            DB::table($this->table)->insert($values);
        }

        return ms(['status' => 1, 'message' => 'Succeed']);
    }

    public function status(Request $request, $status = 'active')
    {
        $ids = Arr::wrap($request->input('id'));
        $id_arr = array_filter($ids);

        if (empty($id_arr)) {
            return ms([
                'status' => 0,
                'message' => __('Please select at least one item'),
            ]);
        }

        DB::table($this->table)
            ->whereIn('id_secure', $id_arr)
            ->update(['status' => $status === 'enable' ? 1 : 0]);

        return ms(['status' => 1, 'message' => 'Succeed']);
    }

    public function destroy(Request $request)
    {
        $id_arr = id_arr($request->input('id'));

        if (empty($id_arr)) {
            return ms(['status' => 0, 'message' => __('Please select at least one item')]);
        }

        DB::table($this->table)->whereIn('id_secure', $id_arr)->delete();

        return ms(['status' => 1, 'message' => __('Succeed')]);
    }
}
