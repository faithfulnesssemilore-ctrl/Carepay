<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LedgerEntry extends Model
{
    //
    protected $fillable = [
        'wallet_id',
        'transaction_id',
        'entry_type',
        'amount',
        'currency',
        'description'
    ];
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'wallet_id', 'id');
    }
}
