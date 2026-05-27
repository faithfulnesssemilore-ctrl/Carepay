<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

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
        'status',
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
            'id_number' => 'encrypted',
            'phone' => 'encrypted',
        ];
    }

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

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function limits()
    {
        return $this->hasOne(UserLimit::class);
    }

    public function sentTransactions()
    {
        return $this->hasMany(Transaction::class, 'user_id')->where('type', 'debit');
    }

    public function receivedTransactions()
    {
        return $this->hasMany(Transaction::class, 'recipient_id')->where('type', 'credit');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    protected static function booted(): void
    {
        static::created(function ($user) {
            $user->wallet()->create([
                'balance' => 0,
                'currency' => 'NGN',
            ]);

            $user->limits()->create([
                'single_transaction_limit' => 100000,
                'daily_transfer_limit' => 500000,
                'daily_transfer_used' => 0,
                'limit_reset_date' => now()->toDateString(),
            ]);

            $user->virtualAccount()->create([
                'account_number' => 'VIRT'.str_pad($user->id, 10, '0', STR_PAD_LEFT),
                'account_name' => $user->first_name.' '.$user->last_name,
                'bank_name' => 'CarePay Virtual Account',
                'provider' => 'carepay',
            ]);
        });
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function isAdmin()
    {
        return $this->role >= 1;
    }

    public function isModerator()
    {
        return $this->role >= 1;
    }

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
        $this->notify(new \App\Notifications\VerifyEmail);
    }
}
