<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ScheduledPayment;
use App\Models\Transaction;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the user
     * Includes scheduled payments, recent transactions, and alerts
     */
    public function index()
    {
        try {
            $user = Auth::user();

            // Get upcoming scheduled payments
            $upcomingPayments = ScheduledPayment::where('user_id', $user->id)
                ->where('status', 'pending')
                ->where('scheduled_date', '>=', now())
                ->orderBy('scheduled_date', 'asc')
                ->take(3)
                ->get()
                ->map(function ($payment) {
                    return [
                        'type' => 'scheduled_payment',
                        'title' => 'Upcoming Payment',
                        'message' => $payment->description ?? 'Scheduled payment of $' . $payment->amount,
                        'date' => $payment->scheduled_date,
                        'amount' => $payment->amount
                    ];
                });

            // Get recent transactions
            $recentTransactions = Transaction::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($transaction) {
                    $type = $transaction->transaction_type;
                    $amount = abs($transaction->amount);
                    $message = ucfirst($type) . ' of $' . number_format($amount, 2);

                    return [
                        'type' => 'transaction',
                        'title' => ucfirst($type),
                        'message' => $message,
                        'date' => $transaction->created_at,
                        'amount' => $transaction->amount,
                        'status' => $transaction->status
                    ];
                });

            $notifications = [...$upcomingPayments->toArray(), ...$recentTransactions->toArray()];

            // Sort by date (most recent first)
            usort($notifications, function ($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });

            return response()->json([
                'success' => true,
                'notifications' => array_slice($notifications, 0, 10),
                'count' => count($notifications)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get count of unread notifications
     */
    public function count()
    {
        try {
            $user = Auth::user();

            $upcomingCount = ScheduledPayment::where('user_id', $user->id)
                ->where('status', 'pending')
                ->where('scheduled_date', '>=', now())
                ->count();

            $unreadTransactions = Transaction::where('user_id', $user->id)
                ->where('created_at', '>=', now()->subHours(24))
                ->count();

            return response()->json([
                'success' => true,
                'count' => $upcomingCount + $unreadTransactions,
                'upcoming_payments' => $upcomingCount,
                'recent_transactions' => $unreadTransactions
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching notification count',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
