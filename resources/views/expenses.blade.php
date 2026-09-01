@extends('layouts.dashboard-layout')

@section('dashboard-content')
<div class="flex-1 overflow-y-auto p-8 bg-[#f8f9fc]">
    <div class="space-y-6 max-w-7xl mx-auto">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl lg:text-3xl font-extrabold text-gray-900 tracking-tight">Expense Manager</h1>
                <p class="text-xs lg:text-sm text-gray-500 mt-1 font-medium">Track your gym's running costs, water, bills & maintenance.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <!-- Search -->
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-gray-400 text-xs"></i>
                    <input type="text" id="search-expense" oninput="filterAndRenderExpenses()" placeholder="Search title or notes..." class="pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-600 outline-none w-48 sm:w-56 bg-white shadow-2xs font-medium">
                </div>

                <!-- Category Filter -->
                <div class="flex items-center gap-1.5 bg-white px-3 py-1.5 rounded-xl border border-gray-200 shadow-2xs">
                    <i class="fa-solid fa-tags text-indigo-600 text-xs"></i>
                    <select id="category-filter" onchange="filterAndRenderExpenses()" class="text-xs font-bold text-gray-700 bg-transparent outline-none cursor-pointer">
                        <option value="all">All Categories</option>
                        <option value="RO Water">RO Water & Jars</option>
                        <option value="Cleaning">Cleaning & Sanitation</option>
                        <option value="Maintenance">Maintenance & Repairs</option>
                        <option value="Supplements">Supplements & Stock</option>
                        <option value="Rent">Rent</option>
                        <option value="Salary">Staff Salary</option>
                        <option value="Utilities">Utilities (Electricity/Water)</option>
                        <option value="Marketing">Marketing & Ads</option>
                        <option value="Software">Software & Internet</option>
                        <option value="Other">Other / Misc</option>
                    </select>
                </div>

                <div class="bg-white px-4 py-2 rounded-xl border border-gray-200 text-xs sm:text-sm font-bold shadow-2xs">
                    Total: <span class="text-red-500">₹<span id="total-expenses-amount">0</span></span>
                </div>

                <button onclick="openExpenseModal()" class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold hover:bg-indigo-700 transition flex items-center gap-2 shadow-lg shadow-indigo-600/20 cursor-pointer">
                    <i class="fa-solid fa-plus"></i> Add Expense
                </button>
            </div>
        </div>

        <!-- Expenses List -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100/90 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                            <th class="px-6 py-4">Title & Description</th>
                            <th class="px-6 py-4">Category</th>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4">Amount</th>
                            <th class="px-6 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody id="expenses-tbody" class="text-xs divide-y divide-gray-50 font-medium">
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400 font-medium">
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

<!-- Add / Edit Expense Modal -->
<div id="expense-modal" class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition-all scale-95 opacity-0" id="expense-modal-content">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
            <h3 class="text-sm font-bold text-gray-900" id="modal-title-text">Add New Expense</h3>
            <button onclick="closeExpenseModal()" class="text-gray-400 hover:text-gray-600 transition cursor-pointer">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="expense-form" onsubmit="handleSaveExpense(event)">
                <input type="hidden" id="expense_id">
                
                <div class="mb-4">
                    <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wide mb-1.5">Title / For What? <span class="text-red-500">*</span></label>
                    <input type="text" id="expense_title" required placeholder="e.g. 20L Water Can Refill, Floor Mop & Phenyl" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent outline-none transition-all">
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wide mb-1.5">Amount (₹) <span class="text-red-500">*</span></label>
                        <input type="number" id="expense_amount" required min="1" step="any" placeholder="0" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent outline-none transition-all font-bold text-red-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wide mb-1.5">Date <span class="text-red-500">*</span></label>
                        <input type="date" id="expense_date" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent outline-none transition-all">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wide mb-1.5">Category <span class="text-red-500">*</span></label>
                    <select id="expense_category" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent outline-none transition-all cursor-pointer">
                        <option value="RO Water">🚰 RO Water & Drinking Cans</option>
                        <option value="Cleaning">🧹 Cleaning & Sanitation (Housekeeping)</option>
                        <option value="Maintenance">🔧 Equipment Maintenance & Repair</option>
                        <option value="Supplements">⚡ Gym Supplements & Stock</option>
                        <option value="Rent">🏢 Rent</option>
                        <option value="Salary">👥 Staff & Trainer Salary</option>
                        <option value="Utilities">💡 Utilities (Electricity/Water Bill)</option>
                        <option value="Marketing">📢 Marketing & Advertising</option>
                        <option value="Software">💻 Software & Internet</option>
                        <option value="Other">📦 Other / Miscellaneous</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wide mb-1.5">Description (Optional)</label>
                    <textarea id="expense_desc" rows="2" placeholder="e.g. 5 Cans from FreshAqua supplier..." class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent outline-none transition-all resize-none"></textarea>
                </div>
                
                <div class="flex items-center gap-3">
                    <button type="button" onclick="closeExpenseModal()" class="flex-1 px-4 py-2.5 border border-gray-200 text-gray-700 rounded-xl text-xs font-bold hover:bg-gray-50 transition cursor-pointer">Cancel</button>
                    <button type="submit" id="submit-expense-btn" class="flex-1 px-4 py-2.5 bg-indigo-600 text-white rounded-xl text-xs font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-600/20 cursor-pointer">Save Expense</button>
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
    tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-12 text-center text-gray-400 font-medium"><div class="w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>Loading expenses...</td></tr>`;
    
    try {
        const res = await fetch(`/api/expenses`, {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        
        if (res.status === 401) { logout(); return; }
        
        const result = await res.json();
        if (res.ok && result.success) {
            expensesData = result.data || [];
            filterAndRenderExpenses();
        } else {
            showError('Failed to load expenses.');
        }
    } catch(e) {
        showError('Network error while loading expenses.');
    }
}

function filterAndRenderExpenses() {
    const search = (document.getElementById('search-expense')?.value || '').toLowerCase();
    const catFilter = document.getElementById('category-filter')?.value || 'all';

    const filtered = expensesData.filter(ex => {
        const matchesSearch = (ex.title || '').toLowerCase().includes(search) || (ex.description || '').toLowerCase().includes(search);
        if (!matchesSearch) return false;

        if (catFilter !== 'all') {
            const exCat = (ex.category || '').toLowerCase();
            const filterCat = catFilter.toLowerCase();
            if (!exCat.includes(filterCat)) return false;
        }

        return true;
    });

    renderExpenses(filtered);
}

function renderExpenses(data) {
    const tbody = document.getElementById('expenses-tbody');
    
    if (data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-12 text-center text-gray-400 font-medium">No expenses found matching the criteria.</td></tr>`;
        document.getElementById('total-expenses-amount').textContent = '0';
        return;
    }

    let html = '';
    let total = 0;

    data.forEach(ex => {
        total += parseFloat(ex.amount) || 0;
        
        const dateStr = new Date(ex.expense_date).toLocaleDateString('en-GB', {day:'numeric', month:'short', year:'numeric'});
        
        // Category Badges with Modern Styles & Icons
        const catConfig = {
            'RO Water': { label: 'RO Water & Cans', class: 'bg-cyan-50 text-cyan-700 border border-cyan-200' },
            'Cleaning': { label: 'Cleaning & Sanitation', class: 'bg-emerald-50 text-emerald-700 border border-emerald-200' },
            'Maintenance': { label: 'Maintenance & Repair', class: 'bg-amber-50 text-amber-700 border border-amber-200' },
            'Supplements': { label: 'Supplements & Stock', class: 'bg-purple-50 text-purple-700 border border-purple-200' },
            'Rent': { label: 'Rent', class: 'bg-indigo-50 text-indigo-700 border border-indigo-200' },
            'Salary': { label: 'Staff Salary', class: 'bg-blue-50 text-blue-700 border border-blue-200' },
            'Utilities': { label: 'Utilities / Electricity', class: 'bg-yellow-50 text-yellow-800 border border-yellow-200' },
            'Marketing': { label: 'Marketing', class: 'bg-pink-50 text-pink-700 border border-pink-200' },
            'Software': { label: 'Software & Internet', class: 'bg-teal-50 text-teal-700 border border-teal-200' },
            'Other': { label: 'Other / Misc', class: 'bg-gray-100 text-gray-700 border border-gray-200' }
        };

        let badgeObj = catConfig['Other'];
        for (const [k, v] of Object.entries(catConfig)) {
            if (ex.category && ex.category.toLowerCase().includes(k.toLowerCase())) {
                badgeObj = v;
                break;
            }
        }

        html += `
            <tr class="hover:bg-gray-50/80 transition-colors group">
                <td class="px-6 py-4">
                    <div class="font-bold text-gray-900">${ex.title}</div>
                    <div class="text-[11px] text-gray-400 truncate max-w-xs font-normal">${ex.description || '—'}</div>
                </td>
                <td class="px-6 py-4">
                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase tracking-wider ${badgeObj.class}">
                        ${badgeObj.label}
                    </span>
                </td>
                <td class="px-6 py-4 text-gray-600 font-semibold">${dateStr}</td>
                <td class="px-6 py-4 font-black text-rose-500">₹${parseFloat(ex.amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-1.5">
                        <button onclick="editExpense(${ex.id})" class="w-8 h-8 rounded-lg bg-gray-50 hover:bg-blue-50 text-blue-500 transition flex items-center justify-center cursor-pointer" title="Edit Expense">
                            <i class="fa-solid fa-pen text-xs"></i>
                        </button>
                        <button onclick="deleteExpense(${ex.id})" class="w-8 h-8 rounded-lg bg-gray-50 hover:bg-red-50 text-red-500 transition flex items-center justify-center cursor-pointer" title="Delete Expense">
                            <i class="fa-solid fa-trash-can text-xs"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    document.getElementById('total-expenses-amount').textContent = total.toLocaleString('en-IN', {maximumFractionDigits: 2});
}

function openExpenseModal() {
    document.getElementById('expense_id').value = '';
    document.getElementById('modal-title-text').textContent = 'Add New Expense';
    document.getElementById('expense-form').reset();
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
    document.getElementById('modal-title-text').textContent = 'Edit Expense';
    document.getElementById('expense_title').value = ex.title;
    document.getElementById('expense_amount').value = ex.amount;
    
    // Ensure date is in YYYY-MM-DD format for <input type="date">
    let dateStr = ex.expense_date;
    if (dateStr && dateStr.length > 10) {
        dateStr = dateStr.substring(0, 10);
    }
    document.getElementById('expense_date').value = dateStr;
    
    // Match category
    const catSelect = document.getElementById('expense_category');
    let matched = false;
    for (let i = 0; i < catSelect.options.length; i++) {
        if (catSelect.options[i].value.toLowerCase() === (ex.category || '').toLowerCase() || ex.category.includes(catSelect.options[i].value)) {
            catSelect.selectedIndex = i;
            matched = true;
            break;
        }
    }
    if (!matched) catSelect.value = 'Other';

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
            showSuccess(id ? 'Expense updated successfully!' : 'Expense added successfully!');
            closeExpenseModal();
            fetchExpenses();
        } else {
            showError(result.message || 'Failed to save expense.');
        }
    } catch(err) {
        showError('Network error while saving expense.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Save Expense';
    }
}

async function deleteExpense(id) {
    confirmDelete('Delete Expense?', 'Are you sure you want to delete this expense record?', async () => {
        try {
            const res = await fetch(`/api/expenses/${id}`, {
                method: 'DELETE',
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            });
            const result = await res.json();
            if (res.ok && result.success) {
                showSuccess('Expense deleted successfully!');
                fetchExpenses();
            } else {
                showError('Failed to delete expense.');
            }
        } catch(e) {
            showError('Network error while deleting expense.');
        }
    });
}

document.addEventListener('DOMContentLoaded', fetchExpenses);
</script>
@endpush
@endsection
