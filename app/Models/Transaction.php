<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;
// Define transaction types
    protected $fillable = [
        'wallet_id',
        'user_id',
        'amount',
        'currency',
        'type',
        'status',
        'description',
        'recipient_id',
        'idempotency_key'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the wallet that owns this transaction
     */
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Get the user that performed this transaction
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the recipient user (for transfers)
     */
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Get all ledger entries for this transaction
     */
    public function ledgerEntries()
    {
        return $this->hasMany(LedgerEntry::class);
    }

    /**
     * Get the scheduled payment if this is from one
     */
    public function scheduledPayment()
    {
        return $this->belongsTo(ScheduledPayment::class);
    }
}
