<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionEmailLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'uid',
        'type',
        'expiration_date',
        'sent_at',
    ];

    /**
     * Check whether an email of this type has already been sent
     * for this user's current billing cycle (identified by expiration_date).
     */
    public static function alreadySent(int $uid, string $type, int $expirationDate): bool
    {
        return static::where('uid', $uid)
            ->where('type', $type)
            ->where('expiration_date', $expirationDate)
            ->exists();
    }

    /**
     * Record that an email was sent.
     * insertOrIgnore ensures concurrent cron runs never create duplicates.
     */
    public static function markSent(int $uid, string $type, int $expirationDate): void
    {
        static::insertOrIgnore([
            'uid'             => $uid,
            'type'            => $type,
            'expiration_date' => $expirationDate,
            'sent_at'         => time(),
        ]);
    }
}
