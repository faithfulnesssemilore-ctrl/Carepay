<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ScheduledPayment;

class ScheduledPaymentController extends Controller
{
    /**
     * Get all scheduled payments for the user
     */
    public function index()
    {
        try {
            $user = Auth::user();
            $payments = ScheduledPayment::where('user_id', $user->id)
                ->orderBy('scheduled_date', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'payments' => $payments
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching scheduled payments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get upcoming scheduled payments
     */
    public function upcoming()
    {
        try {
            $user = Auth::user();
            $payments = ScheduledPayment::where('user_id', $user->id)
                ->where('status', 'pending')
                ->where('scheduled_date', '>=', now())
                ->orderBy('scheduled_date', 'asc')
                ->take(5)
                ->get();

            return response()->json([
                'success' => true,
                'payments' => $payments
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching upcoming payments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new scheduled payment
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'scheduled_date' => 'required|date|after:now',
                'recipient_email' => 'nullable|email',
                'description' => 'nullable|string'
            ]);

            $user = Auth::user();
            $wallet = $user->wallet;

            if (!$wallet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wallet not found'
                ], 404);
            }

            $payment = ScheduledPayment::create([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'amount' => $validated['amount'],
                'currency' => 'USD',
                'scheduled_date' => $validated['scheduled_date'],
                'status' => 'pending',
                'description' => $validated['description'] ?? 'Scheduled payment'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Scheduled payment created',
                'payment' => $payment
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating scheduled payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a scheduled payment
     */
    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $payment = ScheduledPayment::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Scheduled payment not found'
                ], 404);
            }

            if ($payment->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update a ' . $payment->status . ' payment'
                ], 400);
            }

            $validated = $request->validate([
                'amount' => 'nullable|numeric|min:0.01',
                'scheduled_date' => 'nullable|date|after:now',
                'description' => 'nullable|string'
            ]);

            $payment->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Scheduled payment updated',
                'payment' => $payment
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating scheduled payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a scheduled payment
     */
    public function cancel($id)
    {
        try {
            $user = Auth::user();
            $payment = ScheduledPayment::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Scheduled payment not found'
                ], 404);
            }

            if ($payment->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel a ' . $payment->status . ' payment'
                ], 400);
            }

            $payment->update(['status' => 'cancelled']);

            return response()->json([
                'success' => true,
                'message' => 'Scheduled payment cancelled',
                'payment' => $payment
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling scheduled payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
