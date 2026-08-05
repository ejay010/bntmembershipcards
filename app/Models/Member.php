<?php

namespace App\Models;

use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Member extends Model
{
    /** @use HasFactory<MemberFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'tier',
        'status',
        'expires_at',
        'created_by',
        'email_sent_at',
        'apple_wallet_downloaded_at',
        'google_wallet_downloaded_at',
    ];

    protected $casts = [
        'expires_at' => 'date',
        'email_sent_at' => 'datetime',
        'apple_wallet_downloaded_at' => 'datetime',
        'google_wallet_downloaded_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Member $member) {
            $member->uuid = (string) Str::uuid();

            do {
                $number = 'BNT-'.date('Y').'-'.str_pad((string) random_int(10000, 99999), 5, '0', STR_PAD_LEFT);
            } while (static::where('membership_number', $number)->exists());

            $member->membership_number = $number;
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
