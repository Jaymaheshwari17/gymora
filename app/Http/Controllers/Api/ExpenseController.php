<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense;
use Illuminate\Support\Facades\Log;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        try {
            $gymId = $request->user()->gym_id;
            $expenses = Expense::where('gym_id', $gymId)
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $expenses
            ]);
        } catch (\Exception $e) {
            Log::error('ExpenseController@index: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to fetch expenses'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'amount' => 'required|numeric|min:1',
                'expense_date' => 'required|date',
                'category' => 'required|string',
                'description' => 'nullable|string'
            ]);

            $expense = Expense::create([
                'gym_id' => $request->user()->gym_id,
                'title' => $request->title,
                'amount' => $request->amount,
                'expense_date' => $request->expense_date,
                'category' => $request->category,
                'description' => $request->description
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Expense added successfully',
                'data' => $expense
            ]);
        } catch (\Exception $e) {
            Log::error('ExpenseController@store: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to add expense'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'amount' => 'required|numeric|min:1',
                'expense_date' => 'required|date',
                'category' => 'required|string',
                'description' => 'nullable|string'
            ]);

            $expense = Expense::where('id', $id)->where('gym_id', $request->user()->gym_id)->firstOrFail();
            $expense->update([
                'title' => $request->title,
                'amount' => $request->amount,
                'expense_date' => $request->expense_date,
                'category' => $request->category,
                'description' => $request->description
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Expense updated successfully',
                'data' => $expense
            ]);
        } catch (\Exception $e) {
            Log::error('ExpenseController@update: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update expense'], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $expense = Expense::where('id', $id)->where('gym_id', $request->user()->gym_id)->firstOrFail();
            $expense->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Expense deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('ExpenseController@destroy: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete expense'], 500);
        }
    }
}
