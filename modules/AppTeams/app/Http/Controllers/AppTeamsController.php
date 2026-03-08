<?php

namespace Modules\AppTeams\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\AdminUsers\Models\Teams;
use Modules\AdminUsers\Models\TeamMembers;
use App\Models\User;
use Hash;

class AppTeamsController extends Controller
{
    public function __construct(Request $request)
    {
        $except = ['setTeamName', 'saveTeamName'];
        $action = $request->route() ? $request->route()->getActionMethod() : null;
        if (in_array($action, $except)) {
            return;
        }

        $team_id = $request->team_id;
        if ($team_id) {
            $team = \Modules\AdminUsers\Models\Teams::find($team_id);
            if ($team && empty($team->name)) {
                redirect()->route('app.teams.set_team_name')->send();
                exit;
            }
        }
    }

    public function setTeamName(Request $request)
    {
        $team = Teams::findOrFail($request->team_id);

        return view(module("key").'::set_team_name', [
            'team' => $team,
        ]);
    }

    public function saveTeamName(Request $request)
    {
        $team = Teams::findOrFail($request->team_id);

        $validated = $request->validate([
            'team_name' => 'required|string|min:2|max:50'
        ]);

        $team->name = $validated['team_name'];
        $team->save();

        return response()->json([
            'status' => 1,
            'message' => __('Team name has been updated successfully!'),
            'redirect' => route('app.teams.index')
        ]);
    }

    public function index(Request $request)
    {
        $team_id = $request->team_id;

        $totalMembers = TeamMembers::where('team_id', $team_id)->count();

        return view(module("key").'::index', [
            'total' => $totalMembers,
        ]);
    }

    public function list(Request $request)
    {
        $search       = $request->input("keyword");
        $status       = $request->input("status");
        $team_id      = $request->team_id;
        $current_page = (int)$request->input("page", 0) + 1;
        $per_page     = 10;

        $wheres = [
            "team_id" => $team_id
        ];

        if ($status !== null && $status !== '') {
            $wheres["status"] = $status;
        }

        Paginator::currentPageResolver(function () use ($current_page) {
            return $current_page;
        });

        $query = TeamMembers::where($wheres)->with('user');

        if ($search) {
            $query->whereHas('user', function($q) use ($search) {
                $q->where('fullname', 'like', '%' . $search . '%')
                  ->orWhere('username', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $members = $query->orderByDesc('id')->paginate($per_page);

        if ($members->total() == 0 && $current_page > 1) {
            return ms([
                "status" => 0
            ]);
        }

        return response()->json([
            "status" => 1,
            "data"   => view(module('key') . '::list', [
                "members" => $members
            ])->render()
        ]);
    }

    /**
     * Show the edit form for a team member (find by id_secure)
     */
    public function invite(Request $request, $id = "")
    {
        $teamId = $request->team_id;
        $team = Teams::where('id', $teamId)->firstOrFail();
		$userId = session('user_id');
        // Team member limit check - Start
        // Get role once (role=2 => Super Admin)
        $role = DB::table('users')->where('id', $userId)->value('role');

        // ── Team Member Limit Check (skip for Super Admin) ──────────────────
        if ((int)$role !== 2) {

            // Get team owner's plan permissions via users.plan_id
            $planPermissions = DB::table('teams')
                ->join('users', 'users.id', '=', 'teams.owner')
                ->join('plans', 'plans.id', '=', 'users.plan_id')
                ->where('teams.id', $teamId)
                ->value('plans.permissions');

            $memberLimit = 0;

            if ($planPermissions) {
                $perms = json_decode($planPermissions, true) ?? [];
                foreach ($perms as $perm) {
                    if (($perm['key'] ?? '') === 'team_members') {
                        $memberLimit = (int)($perm['value'] ?? 0);
                        break;
                    }
                }
            }

            // Count existing members for this team
            $currentMemberCount = DB::table('team_members')
                ->where('team_id', $teamId)
                ->count();

            if ($memberLimit === 0) {
                return response()->json([
                    "status"  => 0,
                    "message" => "Your current plan does not support team members. Please upgrade to add team member.",
                ]);
            }

            if ($memberLimit !== -1 && $currentMemberCount >= $memberLimit) {
                return response()->json([
                    "status"  => 0,
                    "message" => "You have reached your team member limit ({$memberLimit}). Please upgrade your plan to add more members.",
                ]);
            }
        }
        
        // Team member limit check - End		
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
        return response()->json([
            "status" => 1,
            "data" => view(module("key").'::invite', [
                "team" => $team,
                "brands" => $brands,
            ])->render()
        ]);
    }

    /**
     * Show the edit form for a team member (find by id_secure)
     */
    public function update(Request $request)
    {
        $id = $request->input("id");
        $teamId = $request->team_id;
        $team = Teams::where('id', $teamId)->firstOrFail();
        $member = TeamMembers::where("id_secure", $id)->with('user')->firstOrFail();
        $tmember = User::where('id', $member->uid)->firstOrFail();
		
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
		
		$userId = $member->uid;
		// Get role once (role=2 => Super Admin)
		$role = DB::table('users')->where('id', $userId)->value('role');
		if ((int)$role === 2) {
			// SUPER ADMIN: see every brand
			$user_brands = DB::table('brands')
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
				$user_brands = DB::table('brands')
					->where('team_id', $teamId)
					->orderBy('name')
					->get();
			} else {
				// TEAM MEMBER: see brands created by me OR assigned to me (within team)
				$user_brands = DB::table('brands as b')
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
		
		
        return response()->json([
            "status" => 1,
            "data" => view(module("key").'::update', [
                "member" => $member,
                "team" => $team,
                "brands" => $brands,
				"user_brands" => $user_brands,
                "tmember" => $tmember
            ])->render()
        ]);
    }

    /**
     * Update an existing member's permissions and status
     */
  public function save(Request $request)
{
    $idSecure = $request->input('id'); // nullable on "add"
    $member   = TeamMembers::where('id_secure', $idSecure)->first();
    $isEdit   = (bool) $member;

    // If editing, we know the team from the member; otherwise require it from request
    $teamId = $isEdit ? $member->team_id : $request->input('team_id');
    $team   = Teams::where('id', $teamId)->firstOrFail();

    // Build validation rules dynamically
    $userIdToIgnore = $isEdit ? $member->uid : null;

    $rules = [
        'fullname'  => ['required', 'string', 'max:255'],
        'username'  => [
            'required', 'string', 'min:5', 'max:64', 'regex:/^\S+$/',
            Rule::unique('users', 'username')->ignore($userIdToIgnore),
        ],
        'email'     => [
            'required', 'email',
            Rule::unique('users', 'email')->ignore($userIdToIgnore),
        ],
        'password'  => [$isEdit ? 'nullable' : 'required', 'min:6', 'confirmed'],
        // on edit "id" is required; on add it's not
        'id'               => [$isEdit ? 'required' : 'nullable'],
        'permissions'      => ['required','array','min:1'],
        'team_permissions' => ['nullable','array'],
        'status'           => ['nullable','integer'],
        'brands'           => ['array'],           // brand ids
        'role_id'          => ['nullable','integer'],
        'team_id'          => [$isEdit ? 'nullable' : 'required','integer','exists:teams,id'],
    ];

    $messages = [
        'fullname.required' => __('Full name is required.'),
        'username.required' => __('Username is required.'),
        'username.min'      => __('Username must be at least :min characters.'),
        'username.regex'    => __('Username must not contain spaces.'),
        'email.required'    => __('Email is required.'),
        'email.email'       => __('Please provide a valid email address.'),
        'email.unique'      => __('This email is already taken.'),
        'password.min'      => __('New password must be at least :min characters.', ['min' => 6]),
        'password.confirmed'=> __('Password confirmation does not match.'),
    ];

    $validated = $request->validate($rules, $messages);

    // Build member permission payload (keep your existing logic)
    $selected   = $request->input('permissions', []);
    $excluded   = $request->input('team_permissions', []);
    $allPerms   = $team->permissions ?? [];

    $memberPerms = [];
    foreach ($allPerms as $item) {
        if ($item['key'] !== 'appteams' && in_array($item['key'], $selected)) {
            $memberPerms[] = $item;
        }
    }
    foreach ($allPerms as $item) {
        if ($item['key'] !== 'appteams'
            && !in_array($item['key'], $selected)
            && !in_array($item['key'], $excluded)) {
            $memberPerms[] = $item;
        }
    }

    $brandIds = $request->input('brands', []);
    $roleId   = $request->input('role_id') ?? ($isEdit ? $member->role_id : null) ?? 1;

    DB::transaction(function () use ($isEdit, $member, $teamId, $memberPerms, $request, $brandIds, $roleId) {
        // Upsert User
        if ($isEdit) {
            $user = User::findOrFail($member->uid);
            $user->fullname = $request->input('fullname');
            $user->username = $request->input('username');
            $user->email    = $request->input('email');
            if ($request->filled('password')) {
                $user->password = Hash::make($request->input('password'));
            }
            $user->changed = time();
            $user->save();

            // Update TeamMember
            $member->permissions = json_encode($memberPerms);
            if ($request->filled('status')) {
                $member->status = (int) $request->input('status');
            }
            $member->save();
        } else {
            // Create User + TeamMember
            $user = User::create([
                'fullname' => $request->input('fullname'),
                'username' => $request->input('username'),
                'email'    => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'changed'  => time(),
            ]);

            $member = TeamMembers::create([
                'id_secure'   => \Str::uuid()->toString(),
                'uid'         => $user->id,
                'team_id'     => $teamId,
                'role_id'     => $roleId,
                'permissions' => json_encode($memberPerms),
                'status'      => (int) ($request->input('status') ?? 1),
            ]);
        }

        // Sync user_brands (remove unselected, upsert selected)
        DB::table('user_brands')
            ->where('user_id', $member->uid)
            ->when(count($brandIds) > 0, fn($q) => $q->whereNotIn('brand_id', $brandIds))
            ->delete();

        foreach ($brandIds as $bid) {
            DB::table('user_brands')->updateOrInsert(
                ['user_id' => $member->uid, 'brand_id' => $bid],          // match on these
                ['ids' => rand_string(), 'role_id' => $roleId, 'team_id' => $member->team_id]  // set these
            );
        }
    });

    return response()->json([
        'status'  => 1,
        'message' => __('Member updated successfully!'),
    ]);
}
    /**
     * Change status (enable/disable) for one or multiple members
     */
    public function status(Request $request, $status = "enable")
    {
        $ids = $request->input('id');
        if (empty($ids)) {
            return response()->json([
                "status" => 0,
                "message" => __('Please select at least one item')
            ]);
        }
        if (is_string($ids)) $ids = [$ids];

        $newStatus = $status == "enable" ? 2 : 1;

        TeamMembers::whereIn('id_secure', $ids)->update(['status' => $newStatus]);
        return response()->json([
            "status" => 1,
            "message" => __('succeed')
        ]);
    }

    /**
     * Remove one or multiple members from the team
     */
    public function destroy(Request $request)
    {
        $ids = $request->input('id');
        if (empty($ids)) {
            return response()->json([
                "status" => 0,
                "message" => __('Please select at least one item')
            ]);
        }
        if (is_string($ids)) $ids = [$ids];

        TeamMembers::whereIn('id_secure', $ids)->delete();
        return response()->json([
            "status" => 1,
            "message" => __('Delete successfull')
        ]);
    }

    public function sendInvite(Request $request)
    {
        $request->validate([
            'fullname'  => 'required|string|max:255',
			'username'  => [
                'required',
                'string',
                'min:5',
                'max:64',
                'regex:/^\S+$/',
                'unique:users,username',
            ],
            'password'  => 'required|string|min:6|confirmed',
            'email'            => 'required|email',
            'team_id'          => 'required|integer|exists:teams,id',
            'permissions'      => 'required|array|min:1',
            'team_permissions' => 'nullable|array',
        ], [
            'username.regex'    => __('Username must not contain any whitespace.'),
            'username.min'      => __('Username must be at least 5 characters.'),
            'timezone.required' => __('Please select your timezone.'),
            'timezone.in'       => __('Invalid timezone.'),
        ]);

        $email = $request->input('email');
        $teamId = $request->input('team_id');
        $selected_permissions = $request->input('permissions', []);
        $excluded_permissions = $request->input('team_permissions', []);

        // Get all permissions of the team (array)
        $team = Teams::where('id', $teamId)->firstOrFail();
        $all_permissions = $team->permissions ?? []; // expect array with key/label/value

        // 1. Get permissions that are in selected_permissions
        $member_permissions = [];
        foreach ($all_permissions as $item) {
            if (in_array($item['key'], $selected_permissions)) {
                $member_permissions[] = $item;
            }
        }

        // 2. Add missing permissions not in excluded_permissions and not already added
        foreach ($all_permissions as $item) {
            if (
                !in_array($item['key'], $selected_permissions) &&
                !in_array($item['key'], $excluded_permissions)
            ) {
                $member_permissions[] = $item;
            }
        }

        // Check if user already exists
        $user = User::where('email', $email)->first();

        // Không được invite owner của team
        if ($user && $user->id == $team->owner) {
            return response()->json([
                "status" => 0,
                "message" => __('You cannot add the admin as user.')
            ]);
        }

        // Avoid duplicate invite or member
        if ($user) {
            $exists = TeamMembers::where('team_id', $teamId)
                ->where('uid', $user->id)
                ->whereIn('status', [0, 1])
                ->first();
            if ($exists) {
                return response()->json([
                    "status" => 0,
                    "message" => __('User is already added.')
                ]);
            }
        } else {
            $exists = TeamMembers::where('team_id', $teamId)
                ->where('pending', $email)
                ->where('status', 0)
                ->first();
            if ($exists) {
                return response()->json([
                    "status" => 0,
                    "message" => __('This email has already been added.')
                ]);
            }
        }

        // If the user already exists, add them directly to the team.
        if ($user) {
           $member = TeamMembers::create([
                'id_secure'   => rand_string(),
                'uid'         => $user->id,
                'team_id'     => $teamId,
                'permissions' => json_encode($member_permissions),
                'status'      => 1
            ]);
            return response()->json([
                "status" => 1,
                "message" => __('User added to the team!')
            ]);
        } else {
			$user = User::create([
				'id_secure'     => rand_string(),
				'role'          => 1,
				'login_type'    => 'direct',
				'fullname'      => $request->fullname,
				'email'         => $request->email,
				'username'      => $request->username,
				'password'      => Hash::make($request->password),
				'timezone'      => 'America/Los_Angeles',
				'avatar'        => text2img($request->fullname),
				'secret_key'    => rand_string(32),
				'status'        => 2,
				'changed'       => time(),
				'created'       => time()
			]);
			
			Teams::create([
				'id_secure'   => rand_string(),
				'owner'       => $user->id
			]);
			
            $inviteToken = \Str::random(32);
            $member = TeamMembers::create([
                'id_secure'    => rand_string(),
                'uid'          => $user->id,
                'team_id'      => $teamId,
                'permissions'  => json_encode($member_permissions),
                'status'       => 1
            ]);
			$brandIds = $request->input('brands', []);
			 DB::table('user_brands')
            ->where('user_id', $member->uid)
            ->when(count($brandIds) > 0, fn($q) => $q->whereNotIn('brand_id', $brandIds))
            ->delete();

			foreach ($brandIds as $bid) {
				DB::table('user_brands')->updateOrInsert(
					['user_id' => $member->uid,'ids' => rand_string() , 'brand_id' => $bid],
					['role_id' => 1, 'team_id' => $member->team_id]
				);
			}

            /*$inviter = auth()->user();

            \MailSender::sendByTemplate('invite', $email, [
                'team_name'    => $team->name ?? $inviter->fullname ?? '',
                'invite_url'   => route('app.teams.join', ['token' => $inviteToken]),
                'inviter_name' => $inviter->fullname ?? '',
            ]);*/
			
			if (get_option('auth_welcome_email_new_user_status', 0)) {
                \MailSender::sendByTemplate('welcome', $user->email, [
                    'fullname'  => $user->fullname,
                    'username'  => $user->username,
					'password'  => $request->password,
                    'login_url' => url('auth/login'),
                ]);
            }

            return response()->json([
                "status" => 1,
                "message" => __('User added!')
            ]);
        }
    }

    public function resendInvite(Request $request)
    {
        $id_secure = $request->input('id');

        $member = TeamMembers::where('id_secure', $id_secure)
            ->where('status', 0)
            ->first();

        if (!$member) {
            return response()->json([
                "status" => 0,
                "message" => __('This invitation is invalid or already accepted.')
            ]);
        }

        $team = Teams::find($member->team_id);

        if (!$team) {
            return response()->json([
                "status" => 0,
                "message" => __('Team not found.')
            ]);
        }

        if (empty($member->invite_token)) {
            $member->invite_token = \Str::random(32);
            $member->save();
        }

        $inviter = auth()->user();

        \MailSender::sendByTemplate('invite', $member->pending, [
            'team_name'    => $team->name ?? $inviter->fullname ?? '',
            'invite_url'   => route('app.teams.join', ['token' => $member->invite_token]),
            'inviter_name' => $inviter->fullname ?? '',
        ]);

        return response()->json([
            "status" => 1,
            "message" => __('Invitation resent successfully!')
        ]);
    }
}
