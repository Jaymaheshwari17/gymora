@extends('layouts.dashboard-layout')

@section('dashboard-content')
<div class="flex-1 overflow-y-auto p-8 bg-[#f8f9fc]">
    <div class="max-w-7xl mx-auto space-y-6">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Diet Plans</h1>
                <p class="text-sm text-gray-500 mt-1 font-medium">Create and assign meal plans to your members.</p>
            </div>
            <button onclick="openCreateModal()" class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-indigo-700 transition flex items-center gap-2 shadow-lg shadow-indigo-600/20">
                <i class="fa-solid fa-plus"></i> Create Diet Plan
            </button>
        </div>

        <!-- Stats Bar -->
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-indigo-50 flex items-center justify-center">
                    <i class="fa-solid fa-apple-whole text-indigo-600 text-lg"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900" id="stat-total-plans">—</div>
                    <div class="text-xs text-gray-500 font-medium">Total Plans</div>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-green-50 flex items-center justify-center">
                    <i class="fa-solid fa-user-check text-[#10b981] text-lg"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900" id="stat-assigned">—</div>
                    <div class="text-xs text-gray-500 font-medium">Active Assignments</div>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 col-span-2 md:col-span-1">
                <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center">
                    <i class="fa-solid fa-utensils text-[#3b82f6] text-lg"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900" id="stat-meals">—</div>
                    <div class="text-xs text-gray-500 font-medium">Total Meal Slots</div>
                </div>
            </div>
        </div>

        <!-- Plan Cards Grid -->
        <div id="diet-plans-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <div class="col-span-full py-12 text-center text-gray-400 font-medium bg-white rounded-2xl border border-gray-100 shadow-sm">
                Loading diet plans...
            </div>
        </div>
    </div>
</div>

<!-- ===================== CREATE / EDIT MODAL ===================== -->
<div id="plan-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="closePlanModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-2xl bg-white rounded-2xl shadow-2xl flex flex-col max-h-[90vh]">

        <!-- Modal Header -->
        <div class="px-7 py-5 border-b border-gray-100 flex items-center justify-between shrink-0">
            <div>
                <h3 class="text-lg font-bold text-gray-900" id="modal-plan-title">Create Diet Plan</h3>
                <p class="text-xs text-gray-400 font-medium mt-0.5">Fill in plan details and add meal slots below</p>
            </div>
            <button onclick="closePlanModal()" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition">
                <i class="fa-solid fa-xmark text-gray-500"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-7 overflow-y-auto flex-1 space-y-6">
            <input type="hidden" id="editing-plan-id">

            <!-- Title -->
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-2 uppercase tracking-wider">Plan Title</label>
                <input type="text" id="plan-title" placeholder="e.g. Weight Loss - 1500 Cal" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-medium text-gray-800 focus:ring-2 focus:ring-indigo-600/30 focus:border-indigo-600 outline-none transition">
            </div>

            <!-- Meals -->
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-3 uppercase tracking-wider">Meal Slots</label>
                <div class="space-y-3" id="meals-container">
                    <!-- Meals rendered by JS -->
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="px-7 py-4 border-t border-gray-100 flex justify-end gap-3 shrink-0">
            <button onclick="closePlanModal()" class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-bold text-gray-600 hover:bg-gray-50 transition">Cancel</button>
            <button onclick="savePlan()" class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 transition shadow-md shadow-indigo-600/20">
                <i class="fa-solid fa-floppy-disk mr-1"></i> Save Plan
            </button>
        </div>
    </div>
</div>

<!-- ===================== VIEW MODAL ===================== -->
<div id="view-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="closeViewModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg bg-white rounded-2xl shadow-2xl flex flex-col max-h-[88vh]">
        <div class="px-7 py-5 border-b border-gray-100 flex items-center justify-between shrink-0">
            <h3 class="text-lg font-bold text-gray-900" id="view-plan-title">Plan Details</h3>
            <button onclick="closeViewModal()" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition">
                <i class="fa-solid fa-xmark text-gray-500"></i>
            </button>
        </div>
        <div class="p-7 overflow-y-auto flex-1 space-y-3" id="view-meals-container"></div>
    </div>
</div>

<!-- ===================== ASSIGN MODAL ===================== -->
<div id="assign-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="closeAssignModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-2xl shadow-2xl flex flex-col">
        <div class="px-7 py-5 border-b border-gray-100 flex items-center justify-between shrink-0">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Assign Diet Plan</h3>
                <p class="text-xs text-gray-400 font-medium mt-0.5" id="assign-plan-name"></p>
            </div>
            <button onclick="closeAssignModal()" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition">
                <i class="fa-solid fa-xmark text-gray-500"></i>
            </button>
        </div>
        <div class="p-7 space-y-5">
            <input type="hidden" id="assign-plan-id">
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-2 uppercase tracking-wider">Select Member</label>
                <select id="assign-member" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-medium text-gray-800 focus:ring-2 focus:ring-indigo-600/30 focus:border-indigo-600 outline-none transition">
                    <option value="">Loading members...</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-2 uppercase tracking-wider">Start Date</label>
                <input type="date" id="assign-start-date" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-medium text-gray-800 focus:ring-2 focus:ring-indigo-600/30 focus:border-indigo-600 outline-none transition">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-2 uppercase tracking-wider">End Date <span class="text-gray-400 font-normal normal-case">(optional)</span></label>
                <input type="date" id="assign-end-date" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-medium text-gray-800 focus:ring-2 focus:ring-indigo-600/30 focus:border-indigo-600 outline-none transition">
            </div>
        </div>
        <div class="px-7 py-4 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="closeAssignModal()" class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-bold text-gray-600 hover:bg-gray-50 transition">Cancel</button>
            <button onclick="submitAssign()" class="px-6 py-2.5 rounded-xl bg-[#10b981] text-white text-sm font-bold hover:bg-[#059669] transition shadow-md shadow-green-900/20">
                <i class="fa-solid fa-link mr-1"></i> Assign Plan
            </button>
        </div>
    </div>
</div>

<!-- ===================== DUPLICATE MODAL ===================== -->
<div id="dup-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="closeDupModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm bg-white rounded-2xl shadow-2xl">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-gray-900">Duplicate Plan</h3>
                <p class="text-xs text-gray-400 mt-0.5">Enter a name for the new copy</p>
            </div>
            <button onclick="closeDupModal()" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition"><i class="fa-solid fa-xmark text-gray-500"></i></button>
        </div>
        <div class="p-6 space-y-4">
            <input type="hidden" id="dup-plan-id">
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-2 uppercase tracking-wider">New Plan Name</label>
                <input type="text" id="dup-plan-title" placeholder="e.g. Weight Gain V2" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-medium text-gray-800 focus:ring-2 focus:ring-yellow-400/30 focus:border-yellow-400 outline-none transition">
            </div>
            <div class="flex gap-3">
                <button onclick="closeDupModal()" class="flex-1 py-2.5 rounded-xl border border-gray-200 text-sm font-bold text-gray-600 hover:bg-gray-50 transition">Cancel</button>
                <button onclick="submitDuplicate()" class="flex-1 py-2.5 rounded-xl bg-yellow-500 text-white text-sm font-bold hover:bg-yellow-600 transition">
                    <i class="fa-regular fa-copy mr-1"></i> Duplicate
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===================== DELETE CONFIRM MODAL ===================== -->
<div id="del-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="closeDelModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm bg-white rounded-2xl shadow-2xl">
        <div class="p-6 text-center">
            <div class="w-14 h-14 rounded-2xl bg-red-50 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-trash-can text-2xl text-red-500"></i>
            </div>
            <h3 class="text-base font-bold text-gray-900 mb-1">Delete Diet Plan?</h3>
            <p class="text-sm text-gray-500 mb-6">Are you sure you want to delete <span id="del-plan-name" class="font-bold text-gray-800"></span>? This cannot be undone.</p>
            <input type="hidden" id="del-plan-id">
            <div class="flex gap-3">
                <button onclick="closeDelModal()" class="flex-1 py-2.5 rounded-xl border border-gray-200 text-sm font-bold text-gray-600 hover:bg-gray-50 transition">Cancel</button>
                <button onclick="submitDelete()" class="flex-1 py-2.5 rounded-xl bg-red-500 text-white text-sm font-bold hover:bg-red-600 transition">
                    <i class="fa-solid fa-trash-can mr-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

@push('page-scripts')
<style>
    .meal-card { transition: all 0.2s ease; }
    .meal-card:hover { transform: translateY(-1px); }
</style>
<script>
const MEAL_TYPES = [
    { key: 'breakfast',   label: 'Breakfast',    icon: 'fa-sun',           color: 'bg-yellow-50 text-yellow-500' },
    { key: 'mid_morning', label: 'Mid Morning',   icon: 'fa-mug-hot',       color: 'bg-orange-50 text-orange-500' },
    { key: 'lunch',       label: 'Lunch',         icon: 'fa-bowl-food',     color: 'bg-green-50 text-green-600'  },
    { key: 'evening',     label: 'Evening Snack', icon: 'fa-cookie-bite',   color: 'bg-blue-50 text-blue-500'   },
    { key: 'dinner',      label: 'Dinner',        icon: 'fa-moon',          color: 'bg-indigo-50 text-indigo-600'},
];

let allPlans = [];
let allMembers = [];

// ---- Load Plans ----
async function loadPlans() {
    showLoader();
    const grid = document.getElementById('diet-plans-grid');
    try {
        const res = await fetch('/api/diet-plans', {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        if (res.status === 401) { logout(); return; }
        const result = await res.json();
        if (res.ok && result.success) {
            allPlans = result.data || [];
            renderPlans(allPlans);
            updateStats(allPlans);
        } else {
            grid.innerHTML = `<div class="col-span-full py-12 text-center text-red-400 font-medium bg-white rounded-2xl border border-gray-100">Failed to load plans.</div>`;
        }
    } catch(e) {
        grid.innerHTML = `<div class="col-span-full py-12 text-center text-red-400 font-medium bg-white rounded-2xl border border-gray-100">Network error.</div>`;
    } finally { hideLoader(); }
}

function updateStats(plans) {
    document.getElementById('stat-total-plans').textContent = plans.length;
    const totalAssigned = plans.reduce((s, p) => s + (p.active_assignments || 0), 0);
    const totalMeals    = plans.reduce((s, p) => s + (p.meals_count || 0), 0);
    document.getElementById('stat-assigned').textContent = totalAssigned;
    document.getElementById('stat-meals').textContent = totalMeals;
}

function renderPlans(plans) {
    const grid = document.getElementById('diet-plans-grid');
    if (!plans.length) {
        grid.innerHTML = `
            <div class="col-span-full py-16 flex flex-col items-center gap-4 bg-white rounded-2xl border border-dashed border-gray-200">
                <div class="w-16 h-16 rounded-2xl bg-indigo-50 flex items-center justify-center">
                    <i class="fa-solid fa-apple-whole text-3xl text-indigo-600"></i>
                </div>
                <div class="text-center">
                    <div class="font-bold text-gray-700 text-base">No Diet Plans Yet</div>
                    <div class="text-sm text-gray-400 mt-1">Click "Create Diet Plan" to get started.</div>
                </div>
            </div>`;
        return;
    }

    let html = '';
    plans.forEach(p => {
        const mealsPreview = MEAL_TYPES.slice(0, 3).map(mt => {
            const meal = p.meals.find(m => m.meal_type === mt.key);
            return meal
                ? `<div class="flex items-center gap-2 text-xs"><i class="fa-solid ${mt.icon} w-4 ${mt.color.split(' ')[1]}"></i><span class="text-gray-600 truncate">${meal.food_items.substring(0,40)}${meal.food_items.length > 40 ? '…' : ''}</span></div>`
                : `<div class="flex items-center gap-2 text-xs"><i class="fa-solid ${mt.icon} w-4 text-gray-300"></i><span class="text-gray-300 italic">Not set</span></div>`;
        }).join('');

        // Assigned members chips
        const assignedHtml = p.assigned_members && p.assigned_members.length
            ? `<div class="flex flex-wrap gap-1 mt-3">${p.assigned_members.slice(0,3).map(m =>
                `<span class="text-[10px] font-bold bg-green-50 text-green-700 border border-green-200 px-2 py-0.5 rounded-full">${m.member_name}</span>`
              ).join('')}${p.assigned_members.length > 3 ? `<span class="text-[10px] font-bold bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">+${p.assigned_members.length - 3} more</span>` : ''}</div>`
            : `<div class="mt-3 text-[10px] text-gray-400 italic">No members assigned yet</div>`;

        html += `
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all flex flex-col overflow-hidden group">
                <!-- Card Top -->
                <div class="p-5 flex-1">
                    <div class="flex items-start justify-between mb-4">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-apple-whole text-indigo-600"></i>
                        </div>
                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full ${p.active_assignments > 0 ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-gray-100 text-gray-500'}">
                            ${p.active_assignments} Active
                        </span>
                    </div>
                    <h3 class="font-bold text-gray-900 text-base mb-1 leading-tight">${p.title}</h3>
                    <p class="text-xs text-gray-400 font-medium mb-3">
                        By <span class="text-gray-600 font-bold">${p.created_by_name}</span>
                        <span class="bg-indigo-50 text-indigo-600 font-bold text-[10px] px-1.5 py-0.5 rounded ml-1">${p.created_by_role}</span>
                        · ${p.meals_count} meal slots
                    </p>
                    <div class="space-y-1.5 mb-1">${mealsPreview}</div>
                    ${assignedHtml}
                </div>
                <!-- Card Footer -->
                <div class="border-t border-gray-100 px-5 py-3 flex items-center gap-2 bg-gray-50/50">
                    <button data-id="${p.id}" data-action="view" class="flex-1 py-1.5 rounded-lg text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 transition">
                        <i class="fa-regular fa-eye mr-1"></i> View
                    </button>
                    <button data-id="${p.id}" data-action="assign" class="flex-1 py-1.5 rounded-lg text-xs font-bold text-white bg-[#10b981] hover:bg-[#059669] transition">
                        <i class="fa-solid fa-link mr-1"></i> Assign
                    </button>
                    <button data-id="${p.id}" data-action="dup" data-title="${p.title}" title="Duplicate" class="w-8 h-8 rounded-lg bg-yellow-50 text-yellow-600 hover:bg-yellow-100 transition flex items-center justify-center">
                        <i class="fa-regular fa-copy text-xs"></i>
                    </button>
                    <button data-id="${p.id}" data-action="edit" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition flex items-center justify-center">
                        <i class="fa-regular fa-pen-to-square text-xs"></i>
                    </button>
                    <button data-id="${p.id}" data-action="del" data-title="${p.title}" data-active="${p.active_assignments}" class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition flex items-center justify-center">
                        <i class="fa-regular fa-trash-can text-xs"></i>
                    </button>
                </div>
            </div>`;
    });
    grid.innerHTML = html;

    // Event delegation — avoids inline onclick JS escaping issues
    grid.querySelectorAll('button[data-action]').forEach(btn => {
        btn.addEventListener('click', function() {
            const id     = parseInt(this.dataset.id);
            const action = this.dataset.action;
            if (action === 'view')   openViewModal(id);
            if (action === 'assign') openAssignModal(id);
            if (action === 'edit')   openEditModal(id);
            if (action === 'dup')    openDupModal(id, this.dataset.title);
            if (action === 'del')    openDelModal(id, this.dataset.title, parseInt(this.dataset.active));
        });
    });
}


// ---- Create / Edit Modal ----
function renderMealsBuilder(existingMeals = []) {
    const container = document.getElementById('meals-container');
    container.innerHTML = MEAL_TYPES.map(mt => {
        const existing = existingMeals.find(m => m.meal_type === mt.key);
        return `
            <div class="meal-card border border-gray-100 rounded-xl p-4 bg-gray-50/50">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-7 h-7 rounded-lg ${mt.color.split(' ')[0]} flex items-center justify-center">
                        <i class="fa-solid ${mt.icon} text-xs ${mt.color.split(' ')[1]}"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">${mt.label}</span>
                </div>
                <textarea name="food_${mt.key}" rows="2" placeholder="e.g. 2 roti, 1 bowl dal, salad..." class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs font-medium text-gray-700 focus:ring-2 focus:ring-indigo-600/20 focus:border-indigo-600 outline-none transition resize-none mb-2">${existing ? existing.food_items : ''}</textarea>
                <input type="number" name="cal_${mt.key}" placeholder="Calories (optional)" min="0" value="${existing && existing.calories ? existing.calories : ''}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs font-medium text-gray-700 focus:ring-2 focus:ring-indigo-600/20 focus:border-indigo-600 outline-none transition">
            </div>`;
    }).join('');
}

function openCreateModal() {
    document.getElementById('editing-plan-id').value = '';
    document.getElementById('plan-title').value = '';
    document.getElementById('modal-plan-title').textContent = 'Create Diet Plan';
    renderMealsBuilder([]);
    document.getElementById('plan-modal').classList.remove('hidden');
}

function openEditModal(id) {
    const plan = allPlans.find(p => p.id === id);
    if (!plan) return;
    document.getElementById('editing-plan-id').value = id;
    document.getElementById('plan-title').value = plan.title;
    document.getElementById('modal-plan-title').textContent = 'Edit Diet Plan';
    renderMealsBuilder(plan.meals);
    document.getElementById('plan-modal').classList.remove('hidden');
}

function closePlanModal() { document.getElementById('plan-modal').classList.add('hidden'); }

async function savePlan() {
    const editingId = document.getElementById('editing-plan-id').value;
    const title = document.getElementById('plan-title').value.trim();
    if (!title) { alert('Please enter a plan title.'); return; }

    const meals = [];
    MEAL_TYPES.forEach(mt => {
        const food = document.querySelector(`textarea[name="food_${mt.key}"]`).value.trim();
        const cal  = document.querySelector(`input[name="cal_${mt.key}"]`).value;
        if (food) meals.push({ meal_type: mt.key, food_items: food, calories: cal ? parseInt(cal) : null });
    });

    if (!meals.length) { alert('Please fill in at least one meal slot.'); return; }

    showLoader();
    const url    = editingId ? `/api/diet-plans/${editingId}` : '/api/diet-plans';
    const method = editingId ? 'PUT' : 'POST';

    try {
        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
            body: JSON.stringify({ title, meals })
        });
        const result = await res.json();
        if (res.ok && result.success) {
            closePlanModal();
            showSuccess(editingId ? 'Diet plan updated!' : 'Diet plan created!');
            loadPlans();
        } else {
            showError(result.message || 'Failed to save plan.');
            hideLoader();
        }
    } catch(e) { showError('Network error.'); hideLoader(); }
}

// ---- View Modal ----
function openViewModal(id) {
    const plan = allPlans.find(p => p.id === id);
    if (!plan) return;
    document.getElementById('view-plan-title').textContent = plan.title;
    const container = document.getElementById('view-meals-container');
    container.innerHTML = MEAL_TYPES.map(mt => {
        const meal = plan.meals.find(m => m.meal_type === mt.key);
        return `
            <div class="flex items-start gap-3 p-4 rounded-xl ${meal ? 'bg-gray-50 border border-gray-100' : 'bg-white border border-dashed border-gray-200'}">
                <div class="w-9 h-9 rounded-xl ${mt.color.split(' ')[0]} flex items-center justify-center shrink-0">
                    <i class="fa-solid ${mt.icon} ${mt.color.split(' ')[1]}"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">${mt.label}</div>
                    ${meal
                        ? `<div class="text-sm font-medium text-gray-800">${meal.food_items}</div>
                           ${meal.calories ? `<div class="text-xs text-gray-400 font-bold mt-1">${meal.calories} kcal</div>` : ''}`
                        : `<div class="text-xs text-gray-400 italic">Not configured</div>`}
                </div>
            </div>`;
    }).join('');
    
    let assignedHtml = '';
    if (plan.active_assignments > 0) {
        assignedHtml = `
            <div class="mt-4 bg-gray-50 p-4 rounded-xl border border-emerald-200">
                <div class="text-xs font-bold text-emerald-600 uppercase tracking-wider mb-2">Assigned To (${plan.active_assignments})</div>
                <div class="space-y-2">
                    ${plan.assigned_members.map(am => `
                        <div class="flex items-center gap-2 text-sm text-gray-700">
                            <i class="fa-solid fa-user-check text-emerald-500"></i> ${am.member_name}
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }
    container.innerHTML = assignedHtml + container.innerHTML;
    
    document.getElementById('view-modal').classList.remove('hidden');
}
function closeViewModal() { document.getElementById('view-modal').classList.add('hidden'); }

// ---- Assign Modal ----
async function openAssignModal(planId) {
    const plan = allPlans.find(p => p.id === planId);
    document.getElementById('assign-plan-id').value = planId;
    document.getElementById('assign-plan-name').textContent = `Assigning: ${plan?.title || ''}`;
    document.getElementById('assign-start-date').value = new Date().toISOString().split('T')[0];
    document.getElementById('assign-end-date').value = '';
    document.getElementById('assign-modal').classList.remove('hidden');

    // Load members if not already loaded
    const sel = document.getElementById('assign-member');
    sel.innerHTML = '<option value="">Loading...</option>';
    try {
        const res = await fetch('/api/diet-plans/members-list', {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        const result = await res.json();
        if (res.ok && result.success) {
            allMembers = result.data || [];
            sel.innerHTML = '<option value="">-- Select Member --</option>' +
                allMembers.map(m => `<option value="${m.id}">${m.name}</option>`).join('');
        }
    } catch(e) { sel.innerHTML = '<option value="">Failed to load members</option>'; }
}
function closeAssignModal() { document.getElementById('assign-modal').classList.add('hidden'); }

async function submitAssign() {
    const planId    = document.getElementById('assign-plan-id').value;
    const memberId  = document.getElementById('assign-member').value;
    const startDate = document.getElementById('assign-start-date').value;
    const endDate   = document.getElementById('assign-end-date').value;

    if (!memberId) { alert('Please select a member.'); return; }
    if (!startDate) { alert('Please select a start date.'); return; }

    showLoader();
    try {
        const res = await fetch(`/api/diet-plans/${planId}/assign`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
            body: JSON.stringify({ member_id: memberId, start_date: startDate, end_date: endDate || null })
        });
        const result = await res.json();
        if (res.ok && result.success) {
            closeAssignModal();
            showSuccess('Plan assigned successfully!');
            loadPlans();
        } else {
            showError(result.message || 'Failed to assign plan.');
            hideLoader();
        }
    } catch(e) { showError('Network error.'); hideLoader(); }
}

// ---- Duplicate Modal ----
function openDupModal(id, title) {
    document.getElementById('dup-plan-id').value = id;
    document.getElementById('dup-plan-title').value = title + ' (Copy)';
    document.getElementById('dup-modal').classList.remove('hidden');
    setTimeout(() => { document.getElementById('dup-plan-title').select(); }, 100);
}
function closeDupModal() { document.getElementById('dup-modal').classList.add('hidden'); }

async function submitDuplicate() {
    const id    = document.getElementById('dup-plan-id').value;
    const title = document.getElementById('dup-plan-title').value.trim();
    if (!title) { alert('Please enter a plan name.'); return; }
    closeDupModal();
    showLoader();
    try {
        const res = await fetch(`/api/diet-plans/${id}/duplicate`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
            body: JSON.stringify({ title })
        });
        const result = await res.json();
        if (res.ok && result.success) {
            showSuccess('Plan duplicated successfully!');
            loadPlans();
        } else {
            showError(result.message || 'Failed to duplicate plan.');
            hideLoader();
        }
    } catch(e) { showError('Network error.'); hideLoader(); }
}

// ---- Delete Modal ----
function openDelModal(id, title, activeCount) {
    if (activeCount > 0) {
        alert(`Cannot delete: this plan is currently assigned to ${activeCount} active member(s). Remove assignments first.`);
        return;
    }
    document.getElementById('del-plan-id').value = id;
    document.getElementById('del-plan-name').textContent = title;
    document.getElementById('del-modal').classList.remove('hidden');
}
function closeDelModal() { document.getElementById('del-modal').classList.add('hidden'); }

async function submitDelete() {
    const id = document.getElementById('del-plan-id').value;
    closeDelModal();
    showLoader();
    try {
        const res = await fetch(`/api/diet-plans/${id}`, {
            method: 'DELETE',
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        const result = await res.json();
        if (res.ok && result.success) {
            showSuccess('Plan deleted successfully!');
            loadPlans();
        } else {
            showError(result.message || 'Failed to delete plan.');
            hideLoader();
        }
    } catch(e) { showError('Network error.'); hideLoader(); }
}

document.addEventListener('DOMContentLoaded', loadPlans);
</script>
@endpush
@endsection
