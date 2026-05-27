<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// user limits model - enforces transaction limits per user
// prevents over-spending and fraud
// tracks daily usage and resets at midnight

class UserLimit extends Model
{
    protected $fillable = [
        'user_id',
        'single_transaction_limit',
        'daily_transfer_limit',
        'daily_transfer_used',
        'limit_reset_date',
    ];

    protected $casts = [
        'limit_reset_date' => 'date',
    ];

    // which user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // check if user can transfer this amount
    public function canTransferAmount($amount)
    {
        // reset daily if needed
        $this->resetDailyLimitIfNeeded();

        // check single transaction limit
        if ($amount > $this->single_transaction_limit) {
            return false;
        }

        // check daily limit
        if ($this->daily_transfer_used + $amount > $this->daily_transfer_limit) {
            return false;
        }

        return true;
    }

    // reset daily limit if it's a new day
    public function resetDailyLimitIfNeeded()
    {
        if ($this->limit_reset_date->toDateString() !== now()->toDateString()) {
            $this->update([
                'daily_transfer_used' => 0,
                'limit_reset_date' => now(),
            ]);
        }
    }

    // how much can they transfer today
    public function getRemainingDailyTransfer()
    {
        $this->resetDailyLimitIfNeeded();

        return $this->daily_transfer_limit - $this->daily_transfer_used;
    }

    // add to today's usage
    public function addToDaily($amount)
    {
        $this->resetDailyLimitIfNeeded();
        $this->increment('daily_transfer_used', $amount);
    }

    // get how many hours until daily limit resets
    public function getHoursUntilReset()
    {
        $nextReset = $this->limit_reset_date->copy()->addDay()->startOfDay();

        return $nextReset->diffInHours(now());
    }

    // convert daily limit to kobo (amounts stored in kobo in DB)
    public function dailyLimitInKobo()
    {
        return $this->daily_transfer_limit * 100;
    }

    // convert single transaction limit to kobo
    public function singleLimitInKobo()
    {
        return $this->single_transaction_limit * 100;
    }
}
