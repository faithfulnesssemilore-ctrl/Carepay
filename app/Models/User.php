<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Encrypted;

//  this is the main User model - every person using the app is here
// we keep their name, email, password all those there details here 
// also keep encrypted data so if someone breaks in they cant see the real stuff

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;
    protected $table = 'users';
    protected $guard = 'web';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'pin',
        'id_document',
        'id_type',
        'id_number',
        'kyc_verified',
        'registration_complete',
        'terms_accepted',
        'profile_picture',
        'role',
        'username',
        'status'
    ];

    protected $hidden = [
        'password',
        'pin',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'pin' => 'hashed',
            'kyc_verified' => 'boolean',
            'registration_complete' => 'boolean',
            'terms_accepted' => 'boolean',
            'role' => 'integer',
            // we encrypted this to protect sensitive info - even if someone breaks in they cant see the real stuff
            'id_number' => Encrypted::class,
            'phone' => Encrypted::class,
        ];
    }

    // One wallet per user (fintech standard)
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class);
    }

    public function virtualAccount()
    {
        return $this->hasOne(VirtualAccount::class);
    }

    // get all audit logs for this user
    // tracks everything they did
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    // get transfer limits for this user
    public function limits()
    {
        return $this->hasOne(UserLimit::class);
    }

    // get all transactions sent by this user (debits)
    public function sentTransactions()
    {
        return $this->hasMany(Transaction::class, 'user_id')->where('type', 'debit');
    }

    // get all transactions received by this user (credits to recipient_id)
    public function receivedTransactions()
    {
        return $this->hasMany(Transaction::class, 'recipient_id')->where('type', 'credit');
    }

    // get all transactions (both sent and received)
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // when creating a new user, also make their limits
    protected static function booted()
    {
        static::created(function ($user) {
            // create default limits for new users
            $user->limits()->create([
                'single_transaction_limit' => 100000, // 100k
                'daily_transfer_limit' => 500000, // 500k
                'limit_reset_date' => now()->toDateString(),
            ]);
        });
    }

    // Full name helper (useful in UI)
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // check if user is admin
    public function isAdmin()
    {
        return $this->role >= 1; // 0 = regular user, 1 = admin
    }

    // check if user is moderator
    public function isModerator()
    {
        return $this->role >= 1;
    }

    // MustVerifyEmail implementation
    public function getEmailForVerification()
    {
        return $this->email;
    }

    public function hasVerifiedEmail()
    {
        return $this->email_verified_at !== null;
    }

    public function markEmailAsVerified()
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\VerifyEmail());
    }
}