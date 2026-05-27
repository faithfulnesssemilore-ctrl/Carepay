<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wallet extends Model
{
    use HasFactory, SoftDeletes;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'wallet';

    protected $fillable = [
        'user_id',
        'balance',
        'currency',
        'status',
        'locked',
    ];

    protected $casts = [
        'balance' => 'integer', // store in kobo
        'locked' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function ledgerEntries()
    {
        return $this->hasMany(LedgerEntry::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function getFormattedBalanceAttribute()
    {
        return number_format($this->balance / 100, 2);
    }
}
