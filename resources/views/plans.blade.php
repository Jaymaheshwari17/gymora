@extends('layouts.dashboard-layout')

@section('dashboard-content')
<div class="flex-1 overflow-y-auto p-8 bg-[#f8f9fc]">
    <!-- Header -->
    <div class="flex justify-between items-end mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-1">Plans & Packages</h1>
            <p class="text-gray-500 text-sm font-medium">Create and manage your gym's membership plans.</p>
        </div>
        <button onclick="openModal()" class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-700 transition flex items-center gap-2 shadow-lg shadow-indigo-600/20">
            <i class="fa-solid fa-plus"></i> Add New Plan Group
        </button>
    </div>

    <!-- Plans Data Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden p-6">
        <table id="plansTable" class="w-full text-left border-collapse" width="100%">
            <thead>
                <tr>
                    <th class="w-16">Sr No</th>
                    <th>Plan Name</th>
                    <th>Duration</th>
                    <th>Amount (₹)</th>
                    <th>Status</th>
                    <th class="text-center w-32">Actions</th>
                </tr>
            </thead>
            <tbody id="plans-tbody">
                <!-- Data will be loaded here via JS -->
            </tbody>
        </table>
    </div>
</div>

<!-- View Plan Modal -->
<div id="view-plan-modal" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" onclick="closeViewModal()"></div>
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden relative z-10 w-full max-w-md flex flex-col transform transition-all">
        <!-- Close btn -->
        <button onclick="closeViewModal()" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-800 flex items-center justify-center transition z-20">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="p-6 border-b border-gray-50 bg-gradient-to-r from-indigo-600 to-pink-500 relative overflow-hidden">
            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-2xl transform translate-x-10 -translate-y-10"></div>
            <div class="absolute bottom-0 left-0 w-24 h-24 bg-black/10 rounded-full blur-xl transform -translate-x-10 translate-y-10"></div>
            
            <div class="relative z-10">
                <span id="view-status" class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-white/20 text-white mb-3 inline-block backdrop-blur-sm">Active</span>
                <h3 id="view-name" class="text-2xl font-bold text-white mb-2 leading-tight">Plan Name</h3>
                <p id="view-desc" class="text-white/80 text-sm line-clamp-2">Description</p>
            </div>
        </div>
        
        <div class="p-6 bg-gray-50 flex-1 max-h-[60vh] overflow-y-auto">
            <h4 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-tags text-indigo-600"></i> Pricing & Durations
            </h4>
            <div id="view-durations-list" class="space-y-3">
                <!-- Durations will be listed here -->
            </div>
        </div>
    </div>
</div>

<!-- Add Modal (Multiple Durations) -->
<div id="plan-modal" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
    
    <!-- Modal Content -->
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden flex flex-col max-h-[90vh]">
        
        <!-- Modal Header -->
        <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="text-lg font-bold text-gray-900">Add New Plan Group</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 overflow-y-auto">
            <form id="plan-form" onsubmit="savePlan(event)">
                <div class="space-y-6">
                    <!-- Basic Info -->
                    <div class="grid grid-cols-1 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Plan Group Name <span class="text-red-500">*</span></label>
                            <input type="text" id="plan_group_name" placeholder="e.g. Cardio & Weights" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all">
                            <p id="error-plan_group_name" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Description (Optional)</label>
                            <textarea id="description" rows="2" placeholder="Brief description of what's included..." class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all"></textarea>
                            <p id="error-description" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                        </div>
                    </div>

                    <!-- Divider -->
                    <hr class="border-gray-100">

                    <!-- Durations Section -->
                    <div>
                        <div class="flex justify-between items-end mb-3">
                            <div>
                                <h4 class="text-sm font-bold text-gray-800">Plan Durations & Pricing</h4>
                                <p class="text-xs text-gray-500 mt-0.5">Add multiple durations for this plan (e.g. 1 month, 3 months)</p>
                            </div>
                            <button type="button" onclick="addDurationRow()" class="text-indigo-600 hover:text-[#6c1ab8] text-sm font-semibold flex items-center gap-1.5 bg-indigo-50 px-3 py-1.5 rounded-lg transition">
                                <i class="fa-solid fa-plus text-xs"></i> Add Duration
                            </button>
                        </div>

                        <!-- Dynamic Rows Container -->
                        <div id="durations-container" class="space-y-3">
                            <!-- Default Row -->
                            <div class="duration-row flex items-end gap-3 bg-gray-50 p-3 rounded-xl border border-gray-100">
                                <div class="flex-1">
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Months <span class="text-red-500">*</span></label>
                                    <input type="number" min="1" class="duration-months w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none" placeholder="e.g. 1">
                                    <p class="error-duration_months text-red-500 text-xs mt-1 hidden font-medium"></p>
                                </div>
                                <div class="flex-1">
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Amount (₹) <span class="text-red-500">*</span></label>
                                    <input type="number" min="0" step="0.01" class="duration-amount w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none" placeholder="e.g. 1500">
                                    <p class="error-amount text-red-500 text-xs mt-1 hidden font-medium"></p>
                                </div>
                                <button type="button" onclick="removeRow(this)" class="w-10 h-[38px] flex items-center justify-center bg-white border border-gray-200 text-red-500 hover:bg-red-50 hover:border-red-100 rounded-lg transition shrink-0" title="Remove">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="mt-8 flex gap-3 justify-end pt-5 border-t border-gray-100">
                    <button type="button" onclick="closeModal()" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition">Cancel</button>
                    <button type="submit" id="btn-save" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition shadow-md shadow-indigo-600/20 flex items-center gap-2">
                        <span>Save Plans</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Single Plan Modal -->
<div id="edit-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" onclick="closeEditModal()"></div>
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="text-lg font-bold text-gray-900">Edit Plan</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="edit-form" onsubmit="updatePlan(event)">
                <input type="hidden" id="edit_plan_id">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Plan Group Name</label>
                        <input type="text" id="edit_group_name" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all">
                        <p id="error-edit_group_name" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Months</label>
                            <input type="number" id="edit_months" min="1" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all">
                            <p id="error-edit_months" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Amount (₹)</label>
                            <input type="number" id="edit_amount" min="0" step="0.01" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all">
                            <p id="error-edit_amount" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                        </div>
                    </div>
                </div>
                <div class="mt-8 flex gap-3 justify-end">
                    <button type="button" onclick="closeEditModal()" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition">Cancel</button>
                    <button type="submit" id="btn-update" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition shadow-md shadow-indigo-600/20">
                        Update Plan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('page-scripts')
<script>
    let plansData = [];
    let dataTable = null;

    // Load Plans
    async function loadPlans() {
        showLoader();
        try {
            const response = await fetch('/api/plans', {
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();

            if (response.ok && data.success) {
                plansData = data.data;
                renderTable();
            } else {
                console.error('Failed to load plans');
            }
        } catch (error) {
            console.error(error);
        } finally {
            hideLoader();
        }
    }

    // Render Table
    function renderTable() {
        if (dataTable) {
            dataTable.destroy();
        }

        const tbody = document.getElementById('plans-tbody');
        let html = '';
        
        let flatPlans = [];
        Object.keys(plansData).forEach(groupName => {
            plansData[groupName].forEach(p => {
                flatPlans.push({...p, plan_group_name: groupName});
            });
        });

        flatPlans.forEach((p, index) => {
            const isActive = p.is_active == 1;
            const statusBadge = isActive 
                ? '<span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-green-100 text-green-700">Active</span>'
                : '<span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-red-100 text-red-700">Inactive</span>';

            html += `
                <tr>
                    <td class="font-bold text-gray-500">${index + 1}</td>
                    <td>
                        <div class="font-bold text-gray-900">${p.plan_group_name}</div>
                        <div class="text-xs text-gray-500 mt-1">${p.description || 'No description'}</div>
                    </td>
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
                                <i class="fa-solid fa-calendar-days text-sm"></i>
                            </div>
                            <span class="font-semibold text-gray-700">${p.duration_months} Month(s)</span>
                        </div>
                    </td>
                    <td>
                        <span class="font-black text-gray-900 text-base">₹${Number(p.amount).toLocaleString()}</span>
                    </td>
                    <td>${statusBadge}</td>
                    <td class="text-center">
                        <div class="flex justify-center gap-2">
                            <button onclick='viewPlan(${JSON.stringify(p).replace(/'/g, "&#39;")})' class="w-10 h-10 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 flex items-center justify-center transition" title="View Details">
                                <i class="fa-solid fa-eye text-sm"></i>
                            </button>
                            <button onclick='openEditModal(${JSON.stringify(p).replace(/'/g, "&#39;")})' class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition" title="Edit Duration">
                                <i class="fa-solid fa-pen text-sm"></i>
                            </button>
                            <button onclick="deleteSinglePlan(${p.id})" class="w-10 h-10 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition" title="Delete Duration">
                                <i class="fa-solid fa-trash text-sm"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        tbody.innerHTML = html;

        // Initialize DataTable
        dataTable = $('#plansTable').DataTable({
            pageLength: 10,
            ordering: true,
            responsive: true,
            language: {
                search: "",
                searchPlaceholder: "Search plans..."
            },
            columnDefs: [
                { orderable: false, targets: [0, 5] } // Disable sorting on Sr No and Actions
            ],
            order: [[1, 'asc']] // Default sort by Plan Name
        });
    }

    // View Modal Logic
    const viewModal = document.getElementById('view-plan-modal');
    function viewPlan(plan) {
        const isActive = plan.is_active == 1;
        
        document.getElementById('view-name').textContent = plan.plan_group_name;
        document.getElementById('view-desc').textContent = plan.description || 'No description available for this plan.';
        
        const statusBadge = document.getElementById('view-status');
        if(isActive) {
            statusBadge.textContent = 'Active';
            statusBadge.className = 'px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-white/20 text-white mb-3 inline-block backdrop-blur-sm';
        } else {
            statusBadge.textContent = 'Inactive';
            statusBadge.className = 'px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-red-500/80 text-white mb-3 inline-block backdrop-blur-sm';
        }

        let durationsHtml = `
            <div class="flex items-center justify-between p-4 bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
                        <i class="fa-solid fa-calendar-days text-xl"></i>
                    </div>
                    <div>
                        <div class="font-bold text-gray-900 text-base">${plan.duration_months} Month(s)</div>
                        <div class="text-xs text-gray-500 mt-1">Duration</div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="font-black text-gray-900 text-xl">₹${Number(plan.amount).toLocaleString()}</div>
                    <div class="text-xs text-gray-500 mt-1">Amount</div>
                </div>
            </div>
        `;
        document.getElementById('view-durations-list').innerHTML = durationsHtml;

        viewModal.classList.remove('hidden');
    }

    function closeViewModal() {
        viewModal.classList.add('hidden');
    }

    // Dynamic Rows Logic
    function addDurationRow() {
        const container = document.getElementById('durations-container');
        const row = document.createElement('div');
        row.className = 'duration-row flex items-end gap-3 bg-gray-50 p-3 rounded-xl border border-gray-100 animate-fade-in';
        row.innerHTML = `
            <div class="flex-1">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Months <span class="text-red-500">*</span></label>
                <input type="number" min="1" class="duration-months w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none">
                <p class="error-duration_months text-red-500 text-xs mt-1 hidden font-medium"></p>
            </div>
            <div class="flex-1">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Amount (₹) <span class="text-red-500">*</span></label>
                <input type="number" min="0" step="0.01" class="duration-amount w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none">
                <p class="error-amount text-red-500 text-xs mt-1 hidden font-medium"></p>
            </div>
            <button type="button" onclick="removeRow(this)" class="w-10 h-[38px] flex items-center justify-center bg-white border border-gray-200 text-red-500 hover:bg-red-50 hover:border-red-100 rounded-lg transition shrink-0" title="Remove">
                <i class="fa-solid fa-trash text-xs"></i>
            </button>
        `;
        container.appendChild(row);
    }

    function removeRow(btn) {
        const rows = document.querySelectorAll('.duration-row');
        if (rows.length > 1) {
            btn.closest('.duration-row').remove();
        } else {
            showError("You must have at least one duration.");
        }
    }

    // Modals
    const modal = document.getElementById('plan-modal');
    const editModal = document.getElementById('edit-modal');
    
    function clearErrors() {
        // Clear static fields
        ['plan_group_name', 'description', 'edit_group_name', 'edit_months', 'edit_amount'].forEach(id => {
            const el = document.getElementById(id);
            const err = document.getElementById('error-' + id);
            if (el) el.classList.remove('border-red-500', 'bg-red-50');
            if (err) err.classList.add('hidden');
        });
        
        // Clear dynamic fields
        document.querySelectorAll('.duration-months, .duration-amount').forEach(el => {
            el.classList.remove('border-red-500', 'bg-red-50');
        });
        document.querySelectorAll('.error-duration_months, .error-amount').forEach(el => {
            el.classList.add('hidden');
        });
    }

    function showErrors(errors) {
        if (errors.plan_group_name) {
            document.getElementById('plan_group_name').classList.add('border-red-500', 'bg-red-50');
            const err = document.getElementById('error-plan_group_name');
            err.textContent = errors.plan_group_name[0];
            err.classList.remove('hidden');
        }
        
        const rows = document.querySelectorAll('.duration-row');
        Object.keys(errors).forEach(key => {
            if (key.startsWith('durations.')) {
                const parts = key.split('.');
                const index = parseInt(parts[1]);
                const field = parts[2];
                const row = rows[index];
                
                if (row) {
                    const input = row.querySelector(field === 'amount' ? '.duration-amount' : '.duration-months');
                    if (input) input.classList.add('border-red-500', 'bg-red-50');
                    
                    const err = row.querySelector('.error-' + field);
                    if (err) {
                        err.textContent = errors[key][0];
                        err.classList.remove('hidden');
                    }
                }
            }
        });
    }

    function showEditErrors(errors) {
        if (errors.plan_group_name) {
            document.getElementById('edit_group_name').classList.add('border-red-500', 'bg-red-50');
            const err = document.getElementById('error-edit_group_name');
            err.textContent = errors.plan_group_name[0];
            err.classList.remove('hidden');
        }
        if (errors.duration_months) {
            document.getElementById('edit_months').classList.add('border-red-500', 'bg-red-50');
            const err = document.getElementById('error-edit_months');
            err.textContent = errors.duration_months[0];
            err.classList.remove('hidden');
        }
        if (errors.amount) {
            document.getElementById('edit_amount').classList.add('border-red-500', 'bg-red-50');
            const err = document.getElementById('error-edit_amount');
            err.textContent = errors.amount[0];
            err.classList.remove('hidden');
        }
    }

    function openModal() {
        document.getElementById('plan-form').reset();
        clearErrors();
        // Reset rows to just 1 default row
        const container = document.getElementById('durations-container');
        while (container.children.length > 1) {
            container.removeChild(container.lastChild);
        }
        modal.classList.remove('hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
    }

    function openEditModal(plan) {
        clearErrors();
        document.getElementById('edit_plan_id').value = plan.id;
        document.getElementById('edit_group_name').value = plan.plan_group_name;
        document.getElementById('edit_months').value = plan.duration_months;
        document.getElementById('edit_amount').value = plan.amount;
        editModal.classList.remove('hidden');
    }

    function closeEditModal() {
        editModal.classList.add('hidden');
    }

    // Save Plans (Group)
    async function savePlan(e) {
        e.preventDefault();
        
        // Gather data
        const planGroupName = document.getElementById('plan_group_name').value;
        const description = document.getElementById('description').value;
        
        const durations = [];
        const rows = document.querySelectorAll('.duration-row');
        
        let valid = true;
        rows.forEach(row => {
            const m = row.querySelector('.duration-months').value;
            const a = row.querySelector('.duration-amount').value;
            durations.push({ duration_months: m ? parseInt(m) : null, amount: a ? parseFloat(a) : null });
        });

        clearErrors();

        const payload = {
            plan_group_name: planGroupName,
            description: description,
            durations: durations
        };

        showLoader();
        try {
            const response = await fetch('/api/plans', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (response.ok && data.success) {
                showSuccess('Plan Group created successfully!');
                closeModal();
                loadPlans();
            } else {
                if (data.errors) {
                    showErrors(data.errors);
                } else {
                    showError(data.message || 'Failed to save plans.');
                }
            }
        } catch (error) {
            console.error('Error:', error);
            showError('An error occurred. Please try again.');
        } finally {
            hideLoader();
        }
    }

    // Update Single Plan
    async function updatePlan(e) {
        e.preventDefault();
        
        const id = document.getElementById('edit_plan_id').value;
        const payload = {
            plan_group_name: document.getElementById('edit_group_name').value,
            duration_months: parseInt(document.getElementById('edit_months').value),
            amount: parseFloat(document.getElementById('edit_amount').value)
        };

        showLoader();
        try {
            const response = await fetch(`/api/plans/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (response.ok && data.success) {
                showSuccess('Plan updated successfully!');
                closeEditModal();
                loadPlans();
            } else {
                if (data.errors) {
                    showEditErrors(data.errors);
                } else {
                    showError(data.message || 'Failed to update plan.');
                }
            }
        } catch (error) {
            console.error('Error:', error);
            showError('An error occurred. Please try again.');
        } finally {
            hideLoader();
        }
    }

    // Delete Single Plan
    function deleteSinglePlan(id) {
        confirmDelete('Delete Plan?', 'Are you sure you want to delete this specific duration plan?', async () => {
            showLoader();
            try {
                const response = await fetch(`/api/plans/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    showSuccess('Plan deleted successfully!');
                    loadPlans();
                } else {
                    showError(data.message || 'Failed to delete plan.');
                }
            } catch (error) {
                console.error('Error:', error);
                showError('An error occurred while deleting.');
            } finally {
                hideLoader();
            }
        });
    }

    // Initial Load
    document.addEventListener('DOMContentLoaded', loadPlans);
</script>
<style>
    .animate-fade-in {
        animation: fadeIn 0.3s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush
@endsection
