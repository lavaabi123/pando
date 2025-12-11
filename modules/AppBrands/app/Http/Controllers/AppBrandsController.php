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
			$query = DB::table('brands as b')
						->leftJoin('brands_favorites as bf', function ($join) use ($userId) {
						$join->on('bf.brand_id', '=', 'b.id')
							 ->where('bf.user_id', '=', $userId);
						})
				->select('b.*','bf.id as is_favorite')
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
				$query = DB::table('brands as b')
						->leftJoin('brands_favorites as bf', function ($join) use ($userId, $teamId) {
						$join->on('bf.brand_id', '=', 'b.id')
							 ->where('bf.user_id', '=', $userId);
						})
					->where('team_id', $teamId)
					->select('b.*','bf.id as is_favorite')
					->orderBy('name');
			} else {
				// TEAM MEMBER: see brands created by me OR assigned to me (within team)
				$query = DB::table('brands as b')
					->leftJoin('brands_favorites as bf', function ($join) use ($userId, $teamId) {
					$join->on('bf.brand_id', '=', 'b.id')
						 ->where('bf.user_id', '=', $userId);
					})
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
					->select('b.*','bf.id as is_favorite')
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

		if(!empty($request->input('from')) && $request->input('from') == 'header'){
			return ms([
				'status' => 1,
				'data' => view('appbrands::headerlist', [
					'captions' => $items
				])->render(),
			]);			
		}else{
			return ms([
				'status' => 1,
				'data' => view('appbrands::list', [
					'captions' => $items
				])->render(),
			]);			
		}
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
	
	public function changeBrand(Request $request)
	{
		try {
			$request->validate([
				'brand_id' => 'required|integer|exists:brands,id'
			]);
			
			$userId = auth()->id();
			$brandId = $request->input('brand_id');
			
			// Get brand details
			$brand = DB::table('brands')
				->select('id', 'name', 'image')
				->where('id', $brandId)
				->first();
			
			if (!$brand) {
				return response()->json([
					'success' => false,
					'message' => 'Brand not found'
				], 404);
			}
			
			// Update or insert recent history (UPSERT approach)
			// This prevents duplicates by updating if exists, inserting if not
			DB::table('brands_recents')
				->updateOrInsert(
					[
						'user_id' => $userId,
						'brand_id' => $brandId
					],
					[
						'changed' => time(),
						'created' => DB::raw('COALESCE(created, ' . time() . ')')
					]
				);
			
			// Optional: Keep only last 20 recent brands per user
			$this->cleanupOldRecents($userId, 20);
			
			// Set session data
			session([
				'brand_id' => $brand->id,
				'brand_name' => $brand->name,
				'brand_image' => $brand->image
			]);
			
			return response()->json([
				'success' => true,
				'status' => 1,
				'message' => 'Brand changed successfully',
				'brand' => [
					'id' => $brand->id,
					'name' => $brand->name,
					'image' => $brand->image
				]
			]);
			
		} catch (\Exception $e) {
			\Log::error('Brand change error: ' . $e->getMessage());
			
			return response()->json([
				'success' => false,
				'message' => $e->getMessage()
			], 500);
		}
	}

	/**
	 * Keep only the most recent N brands per user
	 */
	private function cleanupOldRecents($userId, $limit = 20)
	{
		// Get IDs of records to keep (most recent N)
		$keepIds = DB::table('brands_recents')
			->where('user_id', $userId)
			->orderBy('changed', 'desc')
			->limit($limit)
			->pluck('id');
		
		// Delete older records
		if ($keepIds->isNotEmpty()) {
			DB::table('brands_recents')
				->where('user_id', $userId)
				->whereNotIn('id', $keepIds)
				->delete();
		}
	}
	
	public function toggleFavorite(Request $request)
	{
		try {
			// Validate the request
			$request->validate([
				'brand_id' => 'required|integer|exists:brands,id'
			]);
			
			$userId = auth()->id(); // Get authenticated user ID
			$brandId = $request->input('brand_id');
			
			// Check if favorite already exists
			$favorite = DB::table('brands_favorites')
				->where('user_id', $userId)
				->where('brand_id', $brandId)
				->first();
			
			if ($favorite) {
				// Remove from favorites
				DB::table('brands_favorites')
					->where('user_id', $userId)
					->where('brand_id', $brandId)
					->delete();
				
				$isFavorite = false;
				$message = 'Removed from favorites';
			} else {
				// Add to favorites
				DB::table('brands_favorites')->insert([
					'user_id' => $userId,
					'brand_id' => $brandId,
					'changed' => now()->timestamp,
					'created' => now()->timestamp,
				]);
				
				$isFavorite = true;
				$message = 'Added to favorites';
			}
			
			return response()->json([
				'status' => 'success',
				'success' => true,
				'message' => $message,
				'is_favorite' => $isFavorite
			]);
			
		} catch (\Exception $e) {
			return response()->json([
				'status' => 'error',
				'success' => false,
				'message' => 'Failed to toggle favorite'
			], 500);
		}
	}
	
}
