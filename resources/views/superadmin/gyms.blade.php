@extends('superadmin.layouts.admin-layout')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-black text-gray-900 tracking-tight font-display flex items-center gap-2.5">
                <span>Gym Clients Directory</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-indigo-50 text-[#5d5fef] border border-indigo-100">CLIENTS</span>
            </h1>
            <p class="text-gray-500 text-xs font-semibold mt-1">Manage all onboarded gyms, subscriptions (₹599/mo or ₹6,000/yr), license renewals, and account access.</p>
        </div>

        <button onclick="openAddGymModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#5d5fef] hover:bg-[#4d4fe0] text-white text-xs font-bold rounded-xl shadow-md shadow-[#5d5fef]/25 transition-all cursor-pointer">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Onboard New Gym</span>
        </button>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-col md:flex-row items-center justify-between gap-3">
        
        <!-- Search -->
        <div class="relative w-full md:w-80">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <i class="fa-solid fa-magnifying-glass text-xs"></i>
            </div>
            <input type="text" id="search-gym-input" onkeyup="handleGymSearch(event)" placeholder="Search gym name, owner, mobile, code..."
                class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-700 outline-none focus:bg-white focus:border-[#5d5fef] transition-all">
        </div>

        <!-- Status Filter Tabs -->
        <div class="flex flex-wrap items-center gap-1.5 bg-gray-50 p-1 rounded-xl text-xs font-bold border border-gray-200 w-full md:w-auto">
            <button onclick="filterGymsByStatus('all')" id="tab-status-all" class="gym-tab active px-3 py-1.5 rounded-lg transition-all bg-white text-[#5d5fef] shadow-sm">All Gyms</button>
            <button onclick="filterGymsByStatus('active')" id="tab-status-active" class="gym-tab px-3 py-1.5 rounded-lg transition-all text-gray-600 hover:text-gray-900">Active</button>
            <button onclick="filterGymsByStatus('expiring')" id="tab-status-expiring" class="gym-tab px-3 py-1.5 rounded-lg transition-all text-gray-600 hover:text-gray-900">Expiring (7d)</button>
            <button onclick="filterGymsByStatus('expired')" id="tab-status-expired" class="gym-tab px-3 py-1.5 rounded-lg transition-all text-gray-600 hover:text-gray-900">Expired</button>
            <button onclick="filterGymsByStatus('suspended')" id="tab-status-suspended" class="gym-tab px-3 py-1.5 rounded-lg transition-all text-gray-600 hover:text-gray-900">Suspended</button>
        </div>
    </div>

    <!-- Gyms Table Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-gray-50/70 border-b border-gray-100 text-gray-400 font-bold uppercase tracking-wider text-[10px]">
                        <th class="py-3.5 px-6">Gym Code & Name</th>
                        <th class="py-3.5 px-4">Owner & Contact</th>
                        <th class="py-3.5 px-4">SaaS Plan</th>
                        <th class="py-3.5 px-4">Expiry Date</th>
                        <th class="py-3.5 px-4">Gym Members</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="gyms-tbody" class="divide-y divide-gray-50 font-semibold">
                    <tr><td colspan="7" class="py-8 text-center text-gray-400">Loading gym clients...</td></tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer -->
        <div id="pagination-container" class="p-4 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
            <span id="page-info">Showing 0 gyms</span>
            <div class="flex items-center gap-1.5" id="page-buttons"></div>
        </div>
    </div>

</div>

<!-- ===== MODAL: ONBOARD NEW GYM CLIENT ===== -->
<div id="modal-add-gym" class="hidden fixed inset-0 z-50 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100">
        
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-[#5d5fef] flex items-center justify-center font-bold text-sm">
                    <i class="fa-solid fa-building-circle-check"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-gray-900 font-display">Onboard New Gym Client</h3>
                    <p class="text-[11px] text-gray-400 font-medium">Create client account and activate software license</p>
                </div>
            </div>
            <button onclick="closeAddGymModal()" class="text-gray-400 hover:text-gray-600 p-1">✕</button>
        </div>

        <form id="form-add-gym" onsubmit="submitNewGym(event)" class="space-y-3.5 text-xs">
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Gym Name *</label>
                    <input type="text" id="new-gym-name" required placeholder="e.g. Iron Pulse Fitness" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:bg-white focus:border-[#5d5fef]">
                </div>
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Owner Full Name *</label>
                    <input type="text" id="new-owner-name" required placeholder="e.g. Rahul Sharma" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:bg-white focus:border-[#5d5fef]">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Owner Email *</label>
                    <input type="email" id="new-owner-email" required placeholder="owner@gym.com" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:bg-white focus:border-[#5d5fef]">
                </div>
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Mobile Number (10 Digits) *</label>
                    <input type="text" id="new-owner-mobile" maxlength="10" required placeholder="9876543210" oninput="this.value=this.value.replace(/[^0-9]/g,'')" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:bg-white focus:border-[#5d5fef]">
                </div>
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-1">Owner Password *</label>
                <input type="password" id="new-owner-password" required placeholder="Min 6 characters (e.g. Gym@1234)" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:bg-white focus:border-[#5d5fef]">
            </div>

            <!-- SaaS Subscription Plan Selection (Monthly ₹599 or Yearly ₹6000) -->
            <div class="bg-indigo-50/60 p-3.5 rounded-xl border border-indigo-100">
                <label class="block font-bold text-indigo-950 mb-2">Select Software SaaS Package</label>
                <div class="grid grid-cols-2 gap-2.5">
                    <label class="flex items-center gap-2 p-2.5 bg-white border border-indigo-200 rounded-xl cursor-pointer hover:border-[#5d5fef]">
                        <input type="radio" name="saas_plan_choice" value="monthly" checked onchange="updatePlanFee(599, 'Monthly Plan', 'monthly')" class="accent-[#5d5fef]">
                        <div>
                            <span class="font-bold text-gray-900 block leading-tight">Monthly Plan</span>
                            <span class="text-[11px] font-bold text-[#5d5fef]">₹599 / month</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-2 p-2.5 bg-white border border-indigo-200 rounded-xl cursor-pointer hover:border-[#5d5fef]">
                        <input type="radio" name="saas_plan_choice" value="yearly" onchange="updatePlanFee(6000, 'Yearly Plan', 'yearly')" class="accent-[#5d5fef]">
                        <div>
                            <span class="font-bold text-gray-900 block leading-tight">Yearly Plan <span class="text-[9px] bg-emerald-50 text-emerald-600 px-1 rounded font-bold">BEST VALUE</span></span>
                            <span class="text-[11px] font-bold text-[#5d5fef]">₹6,000 / year</span>
                        </div>
                    </label>
                </div>

                <div class="flex items-center justify-between mt-3 pt-2 border-t border-indigo-100">
                    <span class="text-[11px] text-gray-500 font-semibold">Subscription Fee:</span>
                    <span class="text-sm font-black text-[#5d5fef]" id="display-plan-amount">₹599.00</span>
                </div>
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-1">Gym Address (Optional)</label>
                <input type="text" id="new-gym-address" placeholder="City, State" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:bg-white focus:border-[#5d5fef]">
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-gray-100">
                <button type="button" onclick="closeAddGymModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-colors">Cancel</button>
                <button type="submit" id="btn-submit-gym" class="px-5 py-2 bg-[#5d5fef] hover:bg-[#4d4fe0] text-white font-bold rounded-xl shadow-md shadow-[#5d5fef]/20 transition-all flex items-center gap-1.5">
                    <span>Activate & Save Gym</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL: RENEW SUBSCRIPTION ===== -->
<div id="modal-renew-gym" class="hidden fixed inset-0 z-50 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100">
        
        <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs">
                    <i class="fa-solid fa-arrows-rotate"></i>
                </span>
                <div>
                    <h3 class="font-bold text-sm text-gray-900 font-display">Renew Gym Subscription</h3>
                    <p class="text-[11px] text-gray-400 font-medium" id="renew-gym-title">Renewing Gym</p>
                </div>
            </div>
            <button onclick="closeRenewModal()" class="text-gray-400 hover:text-gray-600 p-1">✕</button>
        </div>

        <form id="form-renew-gym" onsubmit="submitRenewGym(event)" class="space-y-4 text-xs">
            <input type="hidden" id="renew-gym-id">

            <div class="space-y-2">
                <label class="block font-bold text-gray-700">Choose Renewal Extension</label>
                
                <label class="flex items-center justify-between p-3 bg-gray-50 border border-gray-200 rounded-xl cursor-pointer hover:border-[#5d5fef]">
                    <div class="flex items-center gap-2.5">
                        <input type="radio" name="renew_plan_choice" value="monthly" checked onchange="setRenewFee(599, 'Monthly Plan', 'monthly')" class="accent-[#5d5fef]">
                        <div>
                            <span class="font-bold text-gray-900 block">Monthly Plan</span>
                            <span class="text-[10px] text-gray-400">+1 Month License Validity</span>
                        </div>
                    </div>
                    <span class="font-black text-[#5d5fef]">₹599</span>
                </label>

                <label class="flex items-center justify-between p-3 bg-gray-50 border border-gray-200 rounded-xl cursor-pointer hover:border-[#5d5fef]">
                    <div class="flex items-center gap-2.5">
                        <input type="radio" name="renew_plan_choice" value="yearly" onchange="setRenewFee(6000, 'Yearly Plan', 'yearly')" class="accent-[#5d5fef]">
                        <div>
                            <span class="font-bold text-gray-900 block">Yearly Plan</span>
                            <span class="text-[10px] text-gray-400">+1 Year Full License Validity</span>
                        </div>
                    </div>
                    <span class="font-black text-[#5d5fef]">₹6,000</span>
                </label>
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-1">Payment Method</label>
                <select id="renew-payment-method" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:bg-white focus:border-[#5d5fef]">
                    <option value="UPI">UPI (GPay / PhonePe / Paytm)</option>
                    <option value="Bank Transfer">Bank Transfer (NEFT/IMPS)</option>
                    <option value="Cash">Cash</option>
                    <option value="Card">Card</option>
                </select>
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-gray-100">
                <button type="button" onclick="closeRenewModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-colors">Cancel</button>
                <button type="submit" id="btn-submit-renew" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md shadow-emerald-600/20 transition-all">
                    <span>Confirm Renewal & Extend</span>
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let currentStatus = 'all';
    let currentSearch = '';
    let selectedPlanName = 'Monthly Plan';
    let selectedPlanCycle = 'monthly';
    let selectedPlanAmount = 599;

    let renewPlanName = 'Monthly Plan';
    let renewPlanCycle = 'monthly';
    let renewPlanAmount = 599;

    async function loadGyms(page = 1) {
        const token = localStorage.getItem('superadmin_token') || sessionStorage.getItem('superadmin_token');
        let url = `/api/superadmin/gyms?page=${page}&status=${currentStatus}`;
        if (currentSearch) url += `&search=${encodeURIComponent(currentSearch)}`;

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
                renderGymsTable(json.data);
            }
        } catch (err) {
            console.error('Failed to load gyms:', err);
        }
    }

    function renderGymsTable(paginatedData) {
        const tbody = document.getElementById('gyms-tbody');
        const gyms = paginatedData.data;

        if (!gyms || !gyms.length) {
            tbody.innerHTML = `<tr><td colspan="7" class="py-12 text-center text-gray-400 font-medium">No gym clients match the selected filter.</td></tr>`;
            document.getElementById('page-info').textContent = 'Showing 0 gyms';
            document.getElementById('page-buttons').innerHTML = '';
            return;
        }

        tbody.innerHTML = gyms.map(gym => {
            const sub = gym.active_subscription;
            const owner = gym.owner;
            const isSuspended = gym.status === 'suspended';
            
            let statusBadge = '<span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">Active</span>';
            if (isSuspended) {
                statusBadge = '<span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-100">Suspended</span>';
            } else if (gym.subscription_end_date) {
                const expiry = new Date(gym.subscription_end_date);
                const today = new Date();
                const diffDays = Math.ceil((expiry - today) / (1000 * 60 * 60 * 24));
                if (diffDays < 0) {
                    statusBadge = '<span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-100">Expired</span>';
                } else if (diffDays <= 7) {
                    statusBadge = `<span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-600 border border-amber-100">${diffDays}d left</span>`;
                }
            }

            const expiryText = gym.subscription_end_date ? new Date(gym.subscription_end_date).toLocaleDateString('en-GB') : 'Lifetime';

            return `
                <tr class="hover:bg-gray-50/80 transition-colors">
                    <td class="py-3.5 px-6">
                        <span class="font-bold text-gray-900 block text-xs">${gym.name}</span>
                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">${gym.gym_code}</span>
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="font-bold text-gray-800 block">${owner ? owner.name : 'No Owner'}</span>
                        <span class="text-[11px] text-gray-400">${owner ? owner.mobile : gym.contact_number || 'N/A'}</span>
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="font-bold text-[#5d5fef] block">${sub ? sub.plan_name : 'Monthly Plan'}</span>
                        <span class="text-[10px] text-gray-400">₹${sub ? Number(sub.amount_paid).toLocaleString('en-IN') : '599'}</span>
                    </td>
                    <td class="py-3.5 px-4 font-bold text-gray-700">${expiryText}</td>
                    <td class="py-3.5 px-4">
                        <span class="px-2.5 py-0.5 rounded-lg bg-blue-50 text-blue-600 font-bold text-[11px]">${gym.members_count || 0} Members</span>
                    </td>
                    <td class="py-3.5 px-4">${statusBadge}</td>
                    <td class="py-3.5 px-6 text-right">
                        <div class="inline-flex items-center gap-1.5">
                            <button onclick="openRenewModal(${gym.id}, '${gym.name.replace(/'/g, "\\'")}')" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-[#5d5fef] rounded-lg font-bold text-[11px] transition-colors cursor-pointer" title="Renew Plan">
                                <i class="fa-solid fa-arrows-rotate mr-1"></i> Renew
                            </button>
                            <button onclick="toggleGymStatus(${gym.id})" class="px-2 py-1 ${isSuspended ? 'bg-emerald-50 hover:bg-emerald-100 text-emerald-600' : 'bg-rose-50 hover:bg-rose-100 text-rose-600'} rounded-lg font-bold text-[11px] transition-colors cursor-pointer" title="${isSuspended ? 'Unlock Gym' : 'Suspend Gym'}">
                                <i class="fa-solid ${isSuspended ? 'fa-lock-open' : 'fa-lock'}"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        document.getElementById('page-info').textContent = `Showing ${gyms.length} of ${paginatedData.total} gyms`;
    }

    function handleGymSearch(e) {
        currentSearch = e.target.value.trim();
        loadGyms(1);
    }

    function filterGymsByStatus(status) {
        currentStatus = status;
        document.querySelectorAll('.gym-tab').forEach(tab => {
            tab.className = 'gym-tab px-3 py-1.5 rounded-lg transition-all text-gray-600 hover:text-gray-900';
        });
        const activeTab = document.getElementById(`tab-status-${status}`);
        if (activeTab) {
            activeTab.className = 'gym-tab active px-3 py-1.5 rounded-lg transition-all bg-white text-[#5d5fef] shadow-sm font-bold';
        }
        loadGyms(1);
    }

    function updatePlanFee(amount, name, cycle) {
        selectedPlanAmount = amount;
        selectedPlanName = name;
        selectedPlanCycle = cycle;
        document.getElementById('display-plan-amount').textContent = `₹${amount.toFixed(2)}`;
    }

    function openAddGymModal() {
        document.getElementById('modal-add-gym').classList.remove('hidden');
    }

    function closeAddGymModal() {
        document.getElementById('modal-add-gym').classList.add('hidden');
    }

    async function submitNewGym(e) {
        e.preventDefault();
        const token = localStorage.getItem('superadmin_token') || sessionStorage.getItem('superadmin_token');
        const btn = document.getElementById('btn-submit-gym');

        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Activating...`;

        const payload = {
            gym_name: document.getElementById('new-gym-name').value.trim(),
            owner_name: document.getElementById('new-owner-name').value.trim(),
            email: document.getElementById('new-owner-email').value.trim(),
            mobile: document.getElementById('new-owner-mobile').value.trim(),
            password: document.getElementById('new-owner-password').value,
            address: document.getElementById('new-gym-address').value.trim(),
            plan_name: selectedPlanName,
            billing_cycle: selectedPlanCycle,
            amount_paid: selectedPlanAmount,
            payment_method: 'UPI'
        };

        try {
            const res = await fetch('/api/superadmin/gyms', {
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
                showToast('Gym client onboarded and activated successfully!', 'success');
                closeAddGymModal();
                document.getElementById('form-add-gym').reset();
                loadGyms(1);
            } else {
                showToast(data.message || 'Failed to create gym', 'error');
            }
        } catch (err) {
            showToast('Network error creating gym client', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = `<span>Activate & Save Gym</span>`;
        }
    }

    function openRenewModal(gymId, gymName) {
        document.getElementById('renew-gym-id').value = gymId;
        document.getElementById('renew-gym-title').textContent = `Renewing ${gymName}`;
        document.getElementById('modal-renew-gym').classList.remove('hidden');
    }

    function closeRenewModal() {
        document.getElementById('modal-renew-gym').classList.add('hidden');
    }

    function setRenewFee(amount, name, cycle) {
        renewPlanAmount = amount;
        renewPlanName = name;
        renewPlanCycle = cycle;
    }

    async function submitRenewGym(e) {
        e.preventDefault();
        const token = localStorage.getItem('superadmin_token') || sessionStorage.getItem('superadmin_token');
        const gymId = document.getElementById('renew-gym-id').value;
        const btn = document.getElementById('btn-submit-renew');

        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Extending...`;

        const payload = {
            plan_name: renewPlanName,
            billing_cycle: renewPlanCycle,
            amount_paid: renewPlanAmount,
            payment_method: document.getElementById('renew-payment-method').value,
            notes: 'Subscription extension via Super Admin panel'
        };

        try {
            const res = await fetch(`/api/superadmin/gyms/${gymId}/renew`, {
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
                showToast('Subscription extended successfully!', 'success');
                closeRenewModal();
                loadGyms(1);
            } else {
                showToast(data.message || 'Renewal failed', 'error');
            }
        } catch (err) {
            showToast('Network error during renewal', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = `<span>Confirm Renewal & Extend</span>`;
        }
    }

    async function toggleGymStatus(gymId) {
        if (!confirm('Are you sure you want to toggle this Gym access status?')) return;

        const token = localStorage.getItem('superadmin_token') || sessionStorage.getItem('superadmin_token');
        try {
            const res = await fetch(`/api/superadmin/gyms/${gymId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            const data = await res.json();
            if (res.ok && data.success) {
                showToast(data.message, 'success');
                loadGyms(1);
            } else {
                showToast(data.message || 'Status update failed', 'error');
            }
        } catch (err) {
            showToast('Network error updating status', 'error');
        }
    }

    document.addEventListener('DOMContentLoaded', () => loadGyms(1));
</script>
@endpush
