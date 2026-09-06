@extends('superadmin.layouts.admin-layout')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-black text-gray-900 tracking-tight font-display flex items-center gap-2.5">
                <span>Platform Expenses</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-rose-50 text-rose-600 border border-rose-100">OUTFLOW</span>
            </h1>
            <p class="text-gray-500 text-xs font-semibold mt-1">Track cloud server hosting (AWS), SMS/WhatsApp APIs, domain renewal, and operational expenses.</p>
        </div>

        <button onclick="openAddExpenseModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#5d5fef] hover:bg-[#4d4fe0] text-white text-xs font-bold rounded-xl shadow-md shadow-[#5d5fef]/25 transition-all cursor-pointer">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Record Platform Expense</span>
        </button>
    </div>

    <!-- Summary Stats Bar -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Total Platform Expenses</span>
                <div class="text-2xl font-black text-rose-600 mt-1">₹<span id="stat-total-expense-amount">0.00</span></div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center text-lg">
                <i class="fa-solid fa-receipt"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Top Expense Category</span>
                <div class="text-base font-black text-gray-900 mt-1" id="stat-top-category">Hosting & Cloud</div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-indigo-50 text-[#5d5fef] flex items-center justify-center text-lg">
                <i class="fa-solid fa-server"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Accounting Status</span>
                <div class="text-xs font-black text-emerald-600 mt-1">Auto-deducted from Net Profit</div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-calculator"></i>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-col md:flex-row items-center justify-between gap-3">
        
        <!-- Search -->
        <div class="relative w-full md:w-80">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <i class="fa-solid fa-magnifying-glass text-xs"></i>
            </div>
            <input type="text" id="search-expense-input" onkeyup="handleExpenseSearch(event)" placeholder="Search bill, vendor, description..."
                class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-700 outline-none focus:bg-white focus:border-[#5d5fef] transition-all">
        </div>

        <!-- Category Dropdown Filter -->
        <div class="flex items-center gap-2 w-full md:w-auto">
            <span class="text-xs font-bold text-gray-500">Category:</span>
            <select id="select-category-filter" onchange="filterByCategory(event)" class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 outline-none focus:bg-white focus:border-[#5d5fef]">
                <option value="all">All Categories</option>
                <option value="Hosting & Cloud">Hosting & Cloud (AWS/Cloud)</option>
                <option value="SMS & WhatsApp API">SMS & WhatsApp API Credits</option>
                <option value="Domain & SSL">Domain & SSL Certificates</option>
                <option value="Tools & Software">Tools & Software Licenses</option>
                <option value="Marketing">Marketing & Advertising</option>
                <option value="Salaries">Developer & Staff Salaries</option>
                <option value="Other">Other Expenses</option>
            </select>
        </div>
    </div>

    <!-- Expenses Table Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-gray-50/70 border-b border-gray-100 text-gray-400 font-bold uppercase tracking-wider text-[10px]">
                        <th class="py-3.5 px-6">Expense Title</th>
                        <th class="py-3.5 px-4">Category</th>
                        <th class="py-3.5 px-4">Amount (INR)</th>
                        <th class="py-3.5 px-4">Date</th>
                        <th class="py-3.5 px-4">Vendor / Payment Mode</th>
                        <th class="py-3.5 px-4">Notes</th>
                        <th class="py-3.5 px-6 text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="expenses-tbody" class="divide-y divide-gray-50 font-semibold">
                    <tr><td colspan="7" class="py-8 text-center text-gray-400">Loading platform expenses...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ===== MODAL: RECORD PLATFORM EXPENSE ===== -->
<div id="modal-add-expense" class="hidden fixed inset-0 z-50 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100">
        
        <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center font-bold text-xs">
                    <i class="fa-solid fa-receipt"></i>
                </span>
                <div>
                    <h3 class="font-bold text-sm text-gray-900 font-display">Record Platform Expense</h3>
                    <p class="text-[11px] text-gray-400 font-medium">Add server, hosting, SMS, or operational bill</p>
                </div>
            </div>
            <button onclick="closeAddExpenseModal()" class="text-gray-400 hover:text-gray-600 p-1">✕</button>
        </div>

        <form id="form-add-expense" onsubmit="submitNewExpense(event)" class="space-y-3.5 text-xs">
            
            <div>
                <label class="block font-bold text-gray-700 mb-1">Expense Title / Item *</label>
                <input type="text" id="exp-title" required placeholder="e.g. AWS EC2 Cloud Server Bill" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:bg-white focus:border-[#5d5fef]">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Category *</label>
                    <select id="exp-category" required class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:bg-white focus:border-[#5d5fef]">
                        <option value="Hosting & Cloud">Hosting & Cloud</option>
                        <option value="SMS & WhatsApp API">SMS & WhatsApp API</option>
                        <option value="Domain & SSL">Domain & SSL</option>
                        <option value="Tools & Software">Tools & Software</option>
                        <option value="Marketing">Marketing & Advertising</option>
                        <option value="Salaries">Salaries & Contractors</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Amount (₹) *</label>
                    <input type="number" step="0.01" id="exp-amount" required placeholder="e.g. 1500" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:bg-white focus:border-[#5d5fef] font-bold text-rose-600">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Expense Date *</label>
                    <input type="date" id="exp-date" required class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:bg-white focus:border-[#5d5fef]">
                </div>
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Payment Method</label>
                    <select id="exp-payment-method" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:bg-white focus:border-[#5d5fef]">
                        <option value="UPI">UPI</option>
                        <option value="Credit Card">Credit Card</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Cash">Cash</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-1">Vendor / Service Provider (Optional)</label>
                <input type="text" id="exp-vendor" placeholder="e.g. Amazon Web Services / Twilio / Hostinger" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:bg-white focus:border-[#5d5fef]">
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-1">Notes / Description (Optional)</label>
                <textarea id="exp-notes" rows="2" placeholder="Additional details..." class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:bg-white focus:border-[#5d5fef]"></textarea>
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-gray-100">
                <button type="button" onclick="closeAddExpenseModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-colors">Cancel</button>
                <button type="submit" id="btn-submit-expense" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl shadow-md shadow-rose-600/20 transition-all">
                    <span>Save Platform Expense</span>
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let currentCategory = 'all';
    let currentExpSearch = '';

    async function loadExpenses() {
        const token = localStorage.getItem('superadmin_token') || sessionStorage.getItem('superadmin_token');
        let url = `/api/superadmin/expenses?category=${currentCategory}`;
        if (currentExpSearch) url += `&search=${encodeURIComponent(currentExpSearch)}`;

        try {
            const res = await fetch(url, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            if (res.status === 401 || res.status === 403) {
                superAdminLogout();
                return;
            }

            const json = await res.json();
            if (json.success) {
                renderExpensesTable(json.data);
            }
        } catch (err) {
            console.error('Failed to load expenses:', err);
        }
    }

    function renderExpensesTable(data) {
        const tbody = document.getElementById('expenses-tbody');
        const expenses = data.expenses.data;

        document.getElementById('stat-total-expense-amount').textContent = Number(data.total_amount).toLocaleString('en-IN', { minimumFractionDigits: 2 });

        if (!expenses || !expenses.length) {
            tbody.innerHTML = `<tr><td colspan="7" class="py-12 text-center text-gray-400 font-medium">No platform expenses recorded.</td></tr>`;
            return;
        }

        tbody.innerHTML = expenses.map(exp => `
            <tr class="hover:bg-gray-50/80 transition-colors">
                <td class="py-3.5 px-6 font-bold text-gray-900">${exp.title}</td>
                <td class="py-3.5 px-4">
                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-gray-50 text-gray-700 border border-gray-200">${exp.category}</span>
                </td>
                <td class="py-3.5 px-4 font-black text-rose-600">₹${Number(exp.amount).toLocaleString('en-IN', { minimumFractionDigits: 2 })}</td>
                <td class="py-3.5 px-4 font-semibold text-gray-600">${new Date(exp.expense_date).toLocaleDateString('en-GB')}</td>
                <td class="py-3.5 px-4 text-gray-600">${exp.vendor_name ? '<strong>' + exp.vendor_name + '</strong> (' + (exp.payment_method || 'UPI') + ')' : (exp.payment_method || 'UPI')}</td>
                <td class="py-3.5 px-4 text-gray-400 truncate max-w-xs">${exp.notes || '-'}</td>
                <td class="py-3.5 px-6 text-right">
                    <button onclick="deleteExpenseItem(${exp.id})" class="p-1.5 text-rose-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer" title="Delete Expense">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function handleExpenseSearch(e) {
        currentExpSearch = e.target.value.trim();
        loadExpenses();
    }

    function filterByCategory(e) {
        currentCategory = e.target.value;
        loadExpenses();
    }

    function openAddExpenseModal() {
        document.getElementById('exp-date').value = new Date().toISOString().split('T')[0];
        document.getElementById('modal-add-expense').classList.remove('hidden');
    }

    function closeAddExpenseModal() {
        document.getElementById('modal-add-expense').classList.add('hidden');
    }

    async function submitNewExpense(e) {
        e.preventDefault();
        const token = localStorage.getItem('superadmin_token') || sessionStorage.getItem('superadmin_token');
        const btn = document.getElementById('btn-submit-expense');

        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Saving...`;

        const payload = {
            title: document.getElementById('exp-title').value.trim(),
            category: document.getElementById('exp-category').value,
            amount: document.getElementById('exp-amount').value,
            expense_date: document.getElementById('exp-date').value,
            payment_method: document.getElementById('exp-payment-method').value,
            vendor_name: document.getElementById('exp-vendor').value.trim(),
            notes: document.getElementById('exp-notes').value.trim(),
        };

        try {
            const res = await fetch('/api/superadmin/expenses', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (res.ok && data.success) {
                showToast('Platform expense recorded successfully!', 'success');
                closeAddExpenseModal();
                document.getElementById('form-add-expense').reset();
                loadExpenses();
            } else {
                showToast(data.message || 'Failed to record expense', 'error');
            }
        } catch (err) {
            showToast('Network error recording expense', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = `<span>Save Platform Expense</span>`;
        }
    }

    async function deleteExpenseItem(id) {
        if (!confirm('Are you sure you want to delete this expense record?')) return;

        const token = localStorage.getItem('superadmin_token') || sessionStorage.getItem('superadmin_token');
        try {
            const res = await fetch(`/api/superadmin/expenses/${id}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            const data = await res.json();
            if (res.ok && data.success) {
                showToast('Expense record deleted', 'success');
                loadExpenses();
            } else {
                showToast(data.message || 'Delete failed', 'error');
            }
        } catch (err) {
            showToast('Network error deleting expense', 'error');
        }
    }

    document.addEventListener('DOMContentLoaded', loadExpenses);
</script>
@endpush
