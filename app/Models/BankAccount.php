<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $fillable = [
        'wallet_id',
        'bank_name',
        'bank_code',
        'account_number',
        'account_name'
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    public function scheduledPayments()
    {
        return $this->hasMany(ScheduledPayment::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
