<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduledPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'user_id',
        'bank_account_id',
        'amount',
        'currency',
        'scheduled_date',
        'status',
        'description',
        'recipient_id',
    ];

    protected $casts = [
        'amount' => 'integer',  // stored in kobo
        'scheduled_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the wallet that owns this scheduled payment
     */
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Get the user that created this scheduled payment
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the recipient user (if transfer)
     */
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Get the bank account for this payment
     */
    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * Get all ledger entries for this scheduled payment
     */
    public function ledgerEntries()
    {
        return $this->hasMany(LedgerEntry::class);
    }

    /**
     * Get all transactions created from this scheduled payment
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
