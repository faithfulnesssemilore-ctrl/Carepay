<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
            'kyc_verified' => 'boolean',
            'registration_complete' => 'boolean',
            'terms_accepted' => 'boolean',
            'role' => 'integer',
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

    // Full name helper (useful in UI)
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}