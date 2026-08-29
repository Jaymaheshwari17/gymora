@extends('layouts.dashboard-layout')

@section('dashboard-content')
<div class="flex-1 overflow-y-auto p-8 bg-[#f8f9fc]">
    <div class="space-y-6">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Expense Manager</h1>
                <p class="text-sm text-gray-500 mt-1 font-medium">Track your gym's running costs and bills.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="bg-white px-4 py-2 rounded-xl border border-gray-200 text-sm font-bold shadow-sm">
                    Total: <span class="text-red-500">₹<span id="total-expenses-amount">0</span></span>
                </div>
                <button onclick="openExpenseModal()" class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-indigo-700 transition flex items-center gap-2 shadow-lg shadow-indigo-600/20">
                    <i class="fa-solid fa-plus"></i> Add Expense
                </button>
            </div>
        </div>

        <!-- Expenses List -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                            <th class="px-6 py-4">Title & Desc</th>
                            <th class="px-6 py-4">Category</th>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4">Amount</th>
                            <th class="px-6 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody id="expenses-tbody" class="text-sm divide-y divide-gray-50">
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-400 font-medium">
                                <div class="w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                                Loading expenses...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Add Expense Modal -->
<div id="expense-modal" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition-all scale-95 opacity-0" id="expense-modal-content">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
            <h3 class="text-lg font-bold text-gray-900">Add New Expense</h3>
            <button onclick="closeExpenseModal()" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="expense-form" onsubmit="handleSaveExpense(event)">
                <input type="hidden" id="expense_id">
                
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Title / For What?</label>
                    <input type="text" id="expense_title" required placeholder="e.g. Electricity Bill, Rent" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600 focus:border-transparent outline-none transition-all">
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Amount (₹)</label>
                        <input type="number" id="expense_amount" required min="1" step="0.01" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600 focus:border-transparent outline-none transition-all font-bold text-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Date</label>
                        <input type="date" id="expense_date" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600 focus:border-transparent outline-none transition-all">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Category</label>
                    <select id="expense_category" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600 focus:border-transparent outline-none transition-all">
                        <option value="Rent">Rent</option>
                        <option value="Salary">Staff Salary</option>
                        <option value="Utilities">Utilities (Electricity/Water)</option>
                        <option value="Maintenance">Maintenance & Repair</option>
                        <option value="Marketing">Marketing</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Description (Optional)</label>
                    <textarea id="expense_desc" rows="2" placeholder="Any extra details..." class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600 focus:border-transparent outline-none transition-all resize-none"></textarea>
                </div>
                
                <div class="flex items-center gap-3">
                    <button type="button" onclick="closeExpenseModal()" class="flex-1 px-4 py-2.5 border border-gray-200 text-gray-700 rounded-xl text-sm font-bold hover:bg-gray-50 transition">Cancel</button>
                    <button type="submit" id="submit-expense-btn" class="flex-1 px-4 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-[#6b1cb8] transition shadow-lg shadow-indigo-600/20">Save Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('page-scripts')
<script>
let expensesData = [];

async function fetchExpenses() {
    const tbody = document.getElementById('expenses-tbody');
    tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-8 text-center text-gray-400 font-medium"><div class="w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>Loading expenses...</td></tr>`;
    
    try {
        const res = await fetch(`/api/expenses`, {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        
        if (res.status === 401) { logout(); return; }
        
        const result = await res.json();
        if (res.ok && result.success) {
            expensesData = result.data;
            renderExpenses(result.data);
        } else {
            showError('Failed to load expenses.');
        }
    } catch(e) {
        showError('Network error while loading expenses.');
    }
}

function renderExpenses(data) {
    const tbody = document.getElementById('expenses-tbody');
    
    if (data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-8 text-center text-gray-400 font-medium">No expenses recorded yet.</td></tr>`;
        document.getElementById('total-expenses-amount').textContent = '0';
        return;
    }

    let html = '';
    let total = 0;

    data.forEach(ex => {
        total += parseFloat(ex.amount);
        
        const dateStr = new Date(ex.expense_date).toLocaleDateString('en-GB', {day:'numeric', month:'short', year:'numeric'});
        
        const catColors = {
            'Rent': 'bg-indigo-100 text-indigo-700',
            'Salary': 'bg-blue-100 text-blue-700',
            'Utilities': 'bg-yellow-100 text-yellow-700',
            'Maintenance': 'bg-orange-100 text-orange-700',
            'Marketing': 'bg-pink-100 text-pink-700',
            'Other': 'bg-gray-100 text-gray-700'
        };
        const catClass = catColors[ex.category] || catColors['Other'];

        html += `
            <tr class="hover:bg-gray-50 transition-colors group">
                <td class="px-6 py-4">
                    <div class="font-bold text-gray-900">${ex.title}</div>
                    <div class="text-xs text-gray-500 truncate max-w-[200px]">${ex.description || '-'}</div>
                </td>
                <td class="px-6 py-4">
                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider ${catClass}">
                        ${ex.category}
                    </span>
                </td>
                <td class="px-6 py-4 text-gray-600 font-medium">${dateStr}</td>
                <td class="px-6 py-4 font-bold text-red-500">₹${parseFloat(ex.amount).toLocaleString()}</td>
                <td class="px-6 py-4 text-right flex justify-end gap-2">
                    <button onclick="editExpense(${ex.id})" class="w-8 h-8 rounded-lg text-blue-500 hover:bg-blue-50 transition flex items-center justify-center" title="Edit">
                        <i class="fa-solid fa-pen"></i>
                    </button>
                    <button onclick="deleteExpense(${ex.id})" class="w-8 h-8 rounded-lg text-red-500 hover:bg-red-50 transition flex items-center justify-center" title="Delete">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    document.getElementById('total-expenses-amount').textContent = total.toLocaleString('en-IN', {maximumFractionDigits: 2});
}

function openExpenseModal() {
    document.getElementById('expense_id').value = '';
    document.getElementById('expense_date').valueAsDate = new Date();
    
    const modal = document.getElementById('expense-modal');
    const content = document.getElementById('expense-modal-content');
    modal.classList.remove('hidden');
    
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function editExpense(id) {
    const ex = expensesData.find(e => e.id === id);
    if(!ex) return;
    
    document.getElementById('expense_id').value = ex.id;
    document.getElementById('expense_title').value = ex.title;
    document.getElementById('expense_amount').value = ex.amount;
    
    // Ensure date is in YYYY-MM-DD format for <input type="date">
    let dateStr = ex.expense_date;
    if (dateStr && dateStr.length > 10) {
        dateStr = dateStr.substring(0, 10);
    }
    document.getElementById('expense_date').value = dateStr;
    
    document.getElementById('expense_category').value = ex.category;
    document.getElementById('expense_desc').value = ex.description || '';
    
    const modal = document.getElementById('expense-modal');
    const content = document.getElementById('expense-modal-content');
    modal.classList.remove('hidden');
    
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeExpenseModal() {
    const modal = document.getElementById('expense-modal');
    const content = document.getElementById('expense-modal-content');
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.getElementById('expense-form').reset();
        document.getElementById('expense_id').value = '';
    }, 200);
}

async function handleSaveExpense(e) {
    e.preventDefault();
    const btn = document.getElementById('submit-expense-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Saving...';
    
    const id = document.getElementById('expense_id').value;
    const url = id ? `/api/expenses/${id}` : '/api/expenses';
    const method = id ? 'PUT' : 'POST';

    const payload = {
        title: document.getElementById('expense_title').value,
        amount: document.getElementById('expense_amount').value,
        expense_date: document.getElementById('expense_date').value,
        category: document.getElementById('expense_category').value,
        description: document.getElementById('expense_desc').value
    };
    
    try {
        const res = await fetch(url, {
            method: method,
            headers: {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        
        const result = await res.json();
        if (res.ok && result.success) {
            showSuccess('Expense saved successfully!');
            closeExpenseModal();
            fetchExpenses();
        } else {
            showError(result.message || 'Failed to save expense.');
        }
    } catch(err) {
        showError('Network error.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Save Expense';
    }
}

async function deleteExpense(id) {
    confirmDelete('Delete Expense?', 'Are you sure you want to delete this expense?', async () => {
        try {
            const res = await fetch(`/api/expenses/${id}`, {
                method: 'DELETE',
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            });
            const result = await res.json();
            if (res.ok && result.success) {
                showSuccess('Expense deleted!');
                fetchExpenses();
            } else {
                showError('Failed to delete.');
            }
        } catch(e) {
            showError('Network error.');
        }
    });
}

document.addEventListener('DOMContentLoaded', fetchExpenses);
</script>
@endpush
@endsection

