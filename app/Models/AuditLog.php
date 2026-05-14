<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// audit log model - tracks every action users take
// for compliance and fraud detection
// required by CBN (Central Bank of Nigeria) regulations

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'changes',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'changes' => 'json',
    ];

    // user who did the action
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // easy way to log an action
    // example: AuditLog::record(
    //     userId: $user->id,
    //     action: 'transfer_sent',
    //     entityType: 'Transaction',
    //     entityId: $transaction->id,
    //     changes: ['amount' => 500]
    // );
    public static function record($userId, $action, $entityType = null, $entityId = null, $changes = null)
    {
        return static::create([
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'changes' => $changes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    // get all logs for a specific user
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // get logs for specific action type
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    // get recent logs
    public function scopeRecent($query, $limit = 50)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }
}
