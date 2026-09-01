<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $gymId = $request->user()->gym_id;
            $status = $request->query('status'); // all, pending, paid
            $memberId = $request->query('member_id');

            $query = Payment::with([
                'member' => function($q) {
                    $q->with(['user', 'plan', 'batch', 'trainer']);
                },
                'gym',
                'transactions'
            ])->where('gym_id', $gymId);

            if ($status === 'pending') {
                $query->where('due_amount', '>', 0);
            } elseif ($status === 'paid') {
                $query->where('due_amount', '=', 0);
            }

            if (!empty($memberId)) {
                $query->where('member_id', $memberId);
            }

            // Order by most recent payment date or created date
            $payments = $query->latest()->get();

            // Check if any payment has paid_amount > 0 but no transaction rows yet (backfill dynamically)
            foreach ($payments as $payment) {
                if ($payment->paid_amount > 0 && $payment->transactions->isEmpty()) {
                    $tx = PaymentTransaction::create([
                        'payment_id' => $payment->id,
                        'member_id' => $payment->member_id,
                        'gym_id' => $gymId,
                        'amount' => $payment->paid_amount,
                        'payment_date' => $payment->payment_date ?: ($payment->created_at ? $payment->created_at->toDateString() : now()->toDateString()),
                        'payment_mode' => 'cash',
                        'notes' => 'Initial payment'
                    ]);
                    $payment->setRelation('transactions', collect([$tx]));
                }
            }

            return response()->json([
                'success' => true,
                'data' => $payments
            ]);
        } catch (\Exception $e) {
            Log::error('PaymentController@index: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to fetch payments'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $gymId = $request->user()->gym_id;
            
            $request->validate([
                'amount_paying' => 'required|numeric|min:1',
                'payment_date' => 'nullable|date',
                'payment_mode' => 'nullable|string|max:50',
                'notes' => 'nullable|string|max:255'
            ]);

            $payment = Payment::where('id', $id)->where('gym_id', $gymId)->firstOrFail();
            $amountPaying = (float) $request->amount_paying;

            if ($amountPaying > $payment->due_amount) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Paying amount cannot be greater than remaining due amount (₹' . number_format($payment->due_amount, 2) . ')'], 422);
            }

            $paymentDate = $request->payment_date ?: now()->toDateString();
            $paymentMode = $request->payment_mode ?: 'cash';
            $notes = $request->notes ?: 'Due payment cleared';

            // 1. If this payment had an initial paid amount with no transaction row yet, create it first
            if ($payment->paid_amount > 0 && $payment->transactions()->count() === 0) {
                PaymentTransaction::create([
                    'payment_id' => $payment->id,
                    'member_id' => $payment->member_id,
                    'gym_id' => $gymId,
                    'amount' => $payment->paid_amount,
                    'payment_date' => $payment->payment_date ?: ($payment->created_at ? $payment->created_at->toDateString() : now()->toDateString()),
                    'payment_mode' => 'cash',
                    'notes' => 'Initial payment'
                ]);
            }

            // 2. Update payment record totals
            $payment->paid_amount += $amountPaying;
            $payment->due_amount = max(0, $payment->due_amount - $amountPaying);
            
            if ($payment->due_amount <= 0) {
                $payment->status = 'paid';
            } elseif ($payment->paid_amount > 0) {
                $payment->status = 'partial';
            }
            
            $payment->payment_date = $paymentDate;
            $payment->save();

            // 3. Create new payment transaction row for this installment
            $newTransaction = PaymentTransaction::create([
                'payment_id' => $payment->id,
                'member_id' => $payment->member_id,
                'gym_id' => $gymId,
                'amount' => $amountPaying,
                'payment_date' => $paymentDate,
                'payment_mode' => $paymentMode,
                'notes' => $notes
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Due payment recorded successfully',
                'data' => $payment->load(['member.user', 'member.plan', 'transactions', 'gym']),
                'transaction' => $newTransaction
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PaymentController@update: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update payment: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $gymId = $request->user()->gym_id;
            $payment = Payment::where('id', $id)->where('gym_id', $gymId)->firstOrFail();
            $payment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Payment transaction deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('PaymentController@destroy: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete payment transaction'], 500);
        }
    }
}
