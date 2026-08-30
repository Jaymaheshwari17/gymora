<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $gymId = $request->user()->gym_id;
            $status = $request->query('status'); // all, pending, paid

            $query = Payment::with([
                'member' => function($q) {
                    $q->with(['user', 'plan', 'batch', 'trainer']);
                },
                'gym'
            ])->where('gym_id', $gymId);

            if ($status === 'pending') {
                $query->where('due_amount', '>', 0);
            } elseif ($status === 'paid') {
                $query->where('due_amount', '=', 0);
            }

            // Order by most recent payment date or created date
            $payments = $query->latest()->get();

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
        try {
            $gymId = $request->user()->gym_id;
            
            $request->validate([
                'amount_paying' => 'required|numeric|min:1'
            ]);

            $payment = Payment::where('id', $id)->where('gym_id', $gymId)->firstOrFail();
            $amountPaying = (float) $request->amount_paying;

            if ($amountPaying > $payment->due_amount) {
                return response()->json(['success' => false, 'message' => 'Paying amount cannot be greater than due amount'], 422);
            }

            $payment->paid_amount += $amountPaying;
            $payment->due_amount -= $amountPaying;
            
            if ($payment->due_amount == 0) {
                $payment->status = 'paid';
            } elseif ($payment->paid_amount > 0) {
                $payment->status = 'partial';
            }
            
            $payment->payment_date = now()->format('Y-m-d');
            $payment->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment updated successfully',
                'data' => $payment
            ]);
        } catch (\Exception $e) {
            Log::error('PaymentController@update: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update payment'], 500);
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
