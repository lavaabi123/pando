<?php

namespace Modules\AppPublishing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CalendarNote extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'calendar_notes';

    protected $guarded = [];

    protected $casts = [
        'labels' => 'array', 
        'date' => 'date', // Add this for proper date handling
    ];

    /**
     * Relationship with Account
     */
    public function account()
    {
        return $this->belongsTo(\Modules\AppChannels\Models\Accounts::class, 'account_id');
    }
    
    /**
     * Relationship with User
     * Add this relationship
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}