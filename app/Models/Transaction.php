<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Notifications\TransactionCreated;
use App\TransactionStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'user_id',
        'amount',
        'currency',
        'type',
        'category',
        'status',
        'reference',
        'description',
        'metadata',
        'payment_method',
        'gateway',
        'recipient_id',
        'idempotency_key',
    ];

    protected $casts = [
        'amount' => MoneyCast::class,
        'status' => TransactionStatus::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected function transactionType(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->type,
        );
    }

    protected function transactionLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => ucfirst($this->type).' '.ucfirst($this->category)
        );
    }

    protected function direction(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->type === 'credit' ? 'in' : 'out'
        );
    }

    protected function amountNaira(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->amount
        );
    }

    protected function formattedAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => number_format($this->amount, 2).' '.$this->currency
        );
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function ledgerEntries()
    {
        return $this->hasMany(LedgerEntry::class);
    }

    public function scheduledPayment()
    {
        return $this->belongsTo(ScheduledPayment::class);
    }

    public function isPending(): bool
    {
        return $this->status === TransactionStatus::Pending;
    }

    public function isCompleted(): bool
    {
        return $this->status === TransactionStatus::Completed;
    }

    public function isFailed(): bool
    {
        return $this->status === TransactionStatus::Failed;
    }

    protected static function booted()
    {
        static::created(function (self $tx) {
            try {
                AuditLog::record(
                    $tx->user_id,
                    'transaction_created',
                    'Transaction',
                    $tx->id,
                    [
                        'amount' => $tx->amount,
                        'status' => $tx->status,
                        'category' => $tx->category,
                    ]
                );
            } catch (\Exception $e) {
                \Log::warning('AuditLog failed for transaction: '.$e->getMessage());
            }

            try {
                if ($tx->status === TransactionStatus::Completed && $tx->user) {
                    $tx->user->notify(new TransactionCreated($tx));
                }
            } catch (\Exception $e) {
                \Log::warning('Transaction notification failed: '.$e->getMessage());
            }
        });
    }
}
