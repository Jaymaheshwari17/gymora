@extends('layouts.dashboard-layout')

@section('dashboard-content')
<div class="flex-1 overflow-y-auto p-8 bg-[#f8f9fc]">
    <div class="max-w-6xl mx-auto space-y-6">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Payments</h1>
                <p class="text-sm text-gray-500 mt-1 font-medium">Manage member fees and pending dues.</p>
            </div>
            <div class="flex items-center gap-2">
                <input type="text" id="search-payment" placeholder="Search member..." class="px-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600 outline-none">
                <select id="status-filter" onchange="fetchPayments()" class="px-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600 outline-none bg-white">
                    <option value="all">All Payments</option>
                    <option value="pending">Pending Dues</option>
                    <option value="paid">Fully Paid</option>
                </select>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Total Paid -->
            <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm hover:border-green-100 hover:-translate-y-1 hover:shadow-lg transition-all duration-300 relative overflow-hidden group">
                <div class="absolute -right-4 -bottom-4 text-green-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 transform group-hover:scale-110">
                    <i class="fa-solid fa-money-check-dollar text-6xl"></i>
                </div>
                <div class="flex items-center gap-3 mb-3 relative z-10">
                    <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center text-green-600 shrink-0 shadow-sm border border-green-100">
                        <i class="fa-solid fa-money-check-dollar text-lg"></i>
                    </div>
                    <span class="text-sm font-bold text-gray-500 uppercase tracking-wider">Total Collected</span>
                </div>
                <div class="text-3xl font-black text-green-600 relative z-10">₹<span id="stat-total-paid">0</span></div>
            </div>

            <!-- Total Due -->
            <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm hover:border-red-100 hover:-translate-y-1 hover:shadow-lg transition-all duration-300 relative overflow-hidden group">
                <div class="absolute -right-4 -bottom-4 text-red-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 transform group-hover:scale-110">
                    <i class="fa-solid fa-wallet text-6xl"></i>
                </div>
                <div class="flex items-center gap-3 mb-3 relative z-10">
                    <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center text-red-500 shrink-0 shadow-sm border border-red-100">
                        <i class="fa-solid fa-wallet text-lg"></i>
                    </div>
                    <span class="text-sm font-bold text-gray-500 uppercase tracking-wider">Pending Dues</span>
                </div>
                <div class="text-3xl font-black text-red-500 relative z-10">₹<span id="stat-total-due">0</span></div>
            </div>
        </div>

        <!-- Payments Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                            <th class="px-6 py-4">Member</th>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4">Total Amount</th>
                            <th class="px-6 py-4">Paid</th>
                            <th class="px-6 py-4">Due</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody id="payments-tbody" class="text-sm divide-y divide-gray-50">
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-400 font-medium">
                                <div class="w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                                Loading payments...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Clear Due Modal -->
<div id="clear-due-modal" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition-all scale-95 opacity-0" id="clear-due-modal-content">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
            <h3 class="text-lg font-bold text-gray-900">Record Payment</h3>
            <button onclick="closeClearDueModal()" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="clear-due-form" onsubmit="handleClearDue(event)">
                <input type="hidden" id="due-payment-id">
                
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Member Name</label>
                    <div id="due-member-name" class="font-bold text-gray-900 text-lg"></div>
                </div>
                
                <div class="mb-5 flex gap-4">
                    <div class="flex-1 bg-gray-50 p-3 rounded-xl border border-gray-100 text-center">
                        <div class="text-xs text-gray-500 font-bold uppercase tracking-wide">Total Due</div>
                        <div class="text-lg font-bold text-red-500">₹<span id="due-amount-display"></span></div>
                    </div>
                </div>

                <div class="mb-5">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Amount Paying Now (₹)</label>
                    <input type="number" id="amount_paying" required min="1" step="0.01" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600 focus:border-transparent outline-none transition-all font-bold">
                </div>
                
                <div class="flex items-center gap-3 mt-6">
                    <button type="button" onclick="closeClearDueModal()" class="flex-1 px-4 py-2.5 border border-gray-200 text-gray-700 rounded-xl text-sm font-bold hover:bg-gray-50 transition">Cancel</button>
                    <button type="submit" id="submit-due-btn" class="flex-1 px-4 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-[#6b1cb8] transition shadow-lg shadow-indigo-600/20">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('page-scripts')
<script>
let paymentsData = [];

async function fetchPayments() {
    const tbody = document.getElementById('payments-tbody');
    const status = document.getElementById('status-filter').value;
    
    tbody.innerHTML = `<tr><td colspan="7" class="px-6 py-8 text-center text-gray-400 font-medium"><div class="w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>Loading payments...</td></tr>`;
    
    try {
        const res = await fetch(`/api/payments?status=${status}`, {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        
        if (res.status === 401) { logout(); return; }
        
        const result = await res.json();
        if (res.ok && result.success) {
            paymentsData = result.data;
            renderPayments();
        } else {
            showError('Failed to load payments.');
        }
    } catch(e) {
        showError('Network error while loading payments.');
    }
}

function renderPayments() {
    const tbody = document.getElementById('payments-tbody');
    const search = document.getElementById('search-payment').value.toLowerCase();
    
    let filtered = paymentsData.filter(p => {
        const name = p.member?.user?.name || '';
        return name.toLowerCase().includes(search);
    });

    let totalPaid = 0;
    let totalDue = 0;

    filtered.forEach(p => {
        totalPaid += parseFloat(p.paid_amount) || 0;
        totalDue += parseFloat(p.due_amount) || 0;
    });

    document.getElementById('stat-total-paid').textContent = totalPaid.toLocaleString();
    document.getElementById('stat-total-due').textContent = totalDue.toLocaleString();

    if (filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="px-6 py-8 text-center text-gray-400 font-medium">No payments found.</td></tr>`;
        return;
    }

    let html = '';
    filtered.forEach(p => {
        const statusColors = {
            'paid': 'bg-green-100 text-green-700',
            'partial': 'bg-yellow-100 text-yellow-700',
            'pending': 'bg-red-100 text-red-700'
        };
        const stClass = statusColors[p.status] || 'bg-gray-100 text-gray-700';
        
        const dateStr = new Date(p.payment_date || p.created_at).toLocaleDateString('en-GB', {day:'numeric', month:'short', year:'numeric'});
        
        let actionBtn = '';
        if (p.due_amount > 0) {
            actionBtn = `<button onclick="openClearDueModal(${p.id})" class="px-3 py-1.5 bg-indigo-600/10 text-indigo-600 hover:bg-indigo-600 hover:text-white rounded-lg text-xs font-bold transition-colors">Clear Due</button>`;
        } else {
            actionBtn = `<span class="text-xs text-gray-400 font-bold"><i class="fa-solid fa-check text-green-500 mr-1"></i> Paid</span>`;
        }

        html += `
            <tr class="hover:bg-gray-50 transition-colors group">
                <td class="px-6 py-4">
                    <div class="font-bold text-gray-900">${p.member?.user?.name || 'Unknown'}</div>
                    <div class="text-xs text-gray-500">${p.member?.user?.mobile || ''}</div>
                </td>
                <td class="px-6 py-4 text-gray-600 font-medium">${dateStr}</td>
                <td class="px-6 py-4 font-bold text-gray-900">₹${parseFloat(p.total_amount).toLocaleString()}</td>
                <td class="px-6 py-4 font-bold text-green-600">₹${parseFloat(p.paid_amount).toLocaleString()}</td>
                <td class="px-6 py-4 font-bold text-red-500">₹${parseFloat(p.due_amount).toLocaleString()}</td>
                <td class="px-6 py-4">
                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider ${stClass}">
                        ${p.status}
                    </span>
                </td>
                <td class="px-6 py-4 text-right">
                    ${actionBtn}
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

document.getElementById('search-payment').addEventListener('input', renderPayments);

function openClearDueModal(id) {
    const payment = paymentsData.find(p => p.id === id);
    if(!payment) return;
    
    document.getElementById('due-payment-id').value = id;
    document.getElementById('due-member-name').textContent = payment.member?.user?.name;
    document.getElementById('due-amount-display').textContent = parseFloat(payment.due_amount).toLocaleString();
    document.getElementById('amount_paying').value = parseFloat(payment.due_amount); // default full
    document.getElementById('amount_paying').max = payment.due_amount;
    
    const modal = document.getElementById('clear-due-modal');
    const content = document.getElementById('clear-due-modal-content');
    modal.classList.remove('hidden');
    
    // animate
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeClearDueModal() {
    const modal = document.getElementById('clear-due-modal');
    const content = document.getElementById('clear-due-modal-content');
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.getElementById('clear-due-form').reset();
    }, 200);
}

async function handleClearDue(e) {
    e.preventDefault();
    const btn = document.getElementById('submit-due-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Processing...';
    
    const id = document.getElementById('due-payment-id').value;
    const amount = document.getElementById('amount_paying').value;
    
    try {
        const res = await fetch(`/api/payments/${id}`, {
            method: 'PUT',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ amount_paying: amount })
        });
        
        const result = await res.json();
        if (res.ok && result.success) {
            showSuccess('Payment recorded successfully!');
            closeClearDueModal();
            fetchPayments(); // reload
        } else {
            showError(result.message || 'Failed to record payment.');
        }
    } catch(err) {
        showError('Network error.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Record Payment';
    }
}

document.addEventListener('DOMContentLoaded', fetchPayments);
</script>
@endpush
@endsection
