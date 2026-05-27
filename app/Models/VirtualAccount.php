<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VirtualAccount extends Model
{
    //

    protected $fillable = [
        'user_id',
        'account_number',
        'account_name',
        'bank_name',
        'provider',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
