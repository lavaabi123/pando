<?php

namespace Modules\AppTeams\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
        return response()->json([
            "status" => 1,
            "data" => view(module("key").'::invite', [
                "team" => $team
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
        return response()->json([
            "status" => 1,
            "data" => view(module("key").'::update', [
                "member" => $member,
                "team" => $team,
                "tmember" => $tmember
            ])->render()
        ]);
    }

    /**
     * Update an existing member's permissions and status
     */
    public function save(Request $request)
    {
		
        $id = $request->input("id");
        // Find the member
        $member = TeamMembers::where('id_secure', $id)->firstOrFail();
        $team = Teams::where('id', $member->team_id)->firstOrFail();
		
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
            'email'  => 'required|email|unique:users,email,' . $member->uid,			
            'password'         => ['nullable', 'min:6', 'confirmed'],
            'id'               => 'required',
            'permissions'      => 'required|array|min:1',
            'team_permissions' => 'nullable|array',
            'status'           => 'nullable|integer',
        ], [
            'fullname.required' => __('Full name is required.'),
            'fullname.min'      => __('Full name must be at least :min characters.'),
            'username.required' => __('Username is required.'),
            'username.min'      => __('Username must be at least :min characters.'),
            'username.regex'    => __('Username must not contain spaces.'),
            'email.required'    => __('Email is required.'),
            'email.email'       => __('Please provide a valid email address.'),
            'email.unique'      => __('This email is already taken.'),
            'password.min'      => __('New password must be at least :min characters.', ['min' => 6]),
            'password.confirmed'=> __('Password confirmation does not match.'),
         ]);

        $selected_permissions = $request->input('permissions', []);
        $excluded_permissions = $request->input('team_permissions', []);

        $all_permissions = $team->permissions ?? [];

        // 1. Get permissions that are in selected_permissions
        $member_permissions = [];
        foreach ($all_permissions as $item) {
            if (
                $item['key'] !== 'appteams' &&          // Bỏ qua appteams (chữ thường)
                in_array($item['key'], $selected_permissions)
            ) {
                $member_permissions[] = $item;
            }
        }

        // 2. Add missing permissions not in excluded_permissions and not already added
        foreach ($all_permissions as $item) {
            if (
                $item['key'] !== 'appteams' &&          // Bỏ qua appteams (chữ thường)
                !in_array($item['key'], $selected_permissions) &&
                !in_array($item['key'], $excluded_permissions)
            ) {
                $member_permissions[] = $item;
            }
        }

        // Update permissions and status (if provided)
        $member->permissions = json_encode($member_permissions);

        if ($request->filled('status')) {
            $member->status = $request->input('status');
        }

        $member->save();
		
		$user = User::findOrFail($member->uid);
        $values = [
            'fullname' => $request->input('fullname'),
            'username' => $request->input('username'),
            'email'    => $request->input('email'),
            'changed'  => time()
        ];
		$user->update($values);

        return response()->json([
            "status" => 1,
            "message" => __('Member updated successfully!')
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
