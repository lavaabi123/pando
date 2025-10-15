<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Modules\AdminUsers\Models\Teams;
use Modules\AdminUsers\Models\TeamMembers;
use Laravel\Socialite\Facades\Socialite;
use Modules\Auth\Events\AuthEvent;
use App\Models\User;
use Core;
use Auth;
use Hash;

class BrandController extends Controller
{

    public function changeBrand(Request $request)
    {
        User::where("id", session('user_id'))->update(['recent_brand_id' => $request->brand_id]);
		session(['brand_id'=>$request->brand_id]);
		$return = [
			"status" => 1,
			"error_type" => 4,
			"class" => "text-success",
			"message" => __("Brand Changed."),
		];

        return ms($return, true);
    }

}
