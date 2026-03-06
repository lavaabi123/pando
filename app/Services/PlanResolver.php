<?php

namespace App\Services;

use App\Models\User;
use Modules\AdminUsers\Models\Teams;

class PlanResolver
{
    protected $data = null;

    public function resolve($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return null;
        }

        $team = Teams::where('owner', $user->id)
            ->orWhereHas('members', function ($q) use ($user) {
                $q->where('uid', $user->id);
            })
            ->first();

        if (!$team) {
            return null;
        }

        if ($team->owner == $user->id) {

            $this->data = [
                'team_id' => $user->id,
                'plan_id' => $user->plan_id,
                'expiration_date' => $user->expiration_date,
                'is_owner' => 1
            ];

        } else {

            $owner = User::find($team->owner);

            $this->data = [
                'team_id' => $owner->id,
                'plan_id' => $owner->plan_id,
                'expiration_date' => $owner->expiration_date,
                'is_owner' => 0
            ];
        }

        return $this->data;
    }

    public function teamId()
    {
        return $this->data['team_id'] ?? null;
    }

    public function planId()
    {
        return $this->data['plan_id'] ?? null;
    }

    public function expiration()
    {
        return $this->data['expiration_date'] ?? null;
    }

    public function isOwner()
    {
        return $this->data['is_owner'] ?? 0;
    }
}