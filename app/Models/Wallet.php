<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'balance' => MoneyCast::class, // stored in kobo
         'created_at' => 'datetime',
         'updated_at' => 'datetime',
         'status' => 'string',
        'locked' => 'boolean',
    ];
    protected function formattedBalance(): Attribute
{
    return Attribute::make(
        get: fn () => number_format($this->balance, 2)
    );
}

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

}
