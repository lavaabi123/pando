<?php

namespace Modules\AdminPlans\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\AdminPlans\Models\Plans;
use App\Models\User;

class CheckUserPlan
{
    public function handle($request, Closure $next)
    {
        if (auth()->check()) {
            $user = auth()->user();        

            $plan = $user->plan_id ? \Modules\AdminPlans\Models\Plans::find($user->plan_id) : null;
            
            if (!$plan) {
                $ownerPlan = User::query()
                    ->join('teams', 'teams.owner', '=', 'users.id')
                    ->join('team_members', 'team_members.team_id', '=', 'teams.id')
                    ->where('team_members.uid', Auth::id())
                    ->select('users.plan_id', 'users.expiration_date')
                    ->first();

                $user = Auth::user();

                if ($ownerPlan && $user->plan_id !== $ownerPlan->plan_id) {                    
                    $user->update([
                        'plan_id' => $ownerPlan->plan_id,
                        'expiration_date' => $ownerPlan->expiration_date,
                    ]);
                    return $next($request);
                }

                $freePlan = \Modules\AdminPlans\Models\Plans::where('free_plan', 1)->where('status', 1)->first();

                $action = optional(\Route::current())->getActionName();
                preg_match('/Modules\\\\([^\\\\]+)/', $action, $matches);
                $currentModule = $matches[1] ?? null;

                $excludedModules = [
                    'Guest',
                    'Auth',
                    'Payment',
                    'AppProfile',
                ];

                if (in_array($currentModule, $excludedModules) || $user->role != 1) {
                    return $next($request);
                }

                if ($freePlan) {
                    \Plan::activateFreePlan($freePlan->id_secure);
                    return redirect()->route('app.profile', 'plan')->with('warning', __('You have been switched to the free plan.'));
                } else {
                    return redirect()->route('app.profile', 'plan')->with('warning', __('Your subscription plan is invalid or has been deleted. Please select a new plan.'));
                }
            }
        }
        return $next($request);
    }
}
