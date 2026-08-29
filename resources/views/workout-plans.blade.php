@extends('layouts.dashboard-layout')

@section('dashboard-content')
<div class="flex-1 overflow-y-auto p-8 bg-[#f8f9fc]">
    <div class="max-w-7xl mx-auto space-y-6">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Workout Plans</h1>
                <p class="text-sm text-gray-500 mt-1 font-medium">Create and assign workout routines to your members.</p>
            </div>
            <button onclick="openCreateModal()" class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-indigo-700 transition flex items-center gap-2 shadow-lg shadow-indigo-600/20">
                <i class="fa-solid fa-plus"></i> Create Workout Plan
            </button>
        </div>

        <!-- Stats Bar -->
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-indigo-50 flex items-center justify-center">
                    <i class="fa-solid fa-dumbbell text-indigo-600 text-lg"></i>
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
                    <i class="fa-solid fa-calendar-day text-[#3b82f6] text-lg"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900" id="stat-days">—</div>
                    <div class="text-xs text-gray-500 font-medium">Total Workout Days</div>
                </div>
            </div>
        </div>

        <!-- Plan Cards Grid -->
        <div id="workout-plans-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <div class="col-span-full py-12 text-center text-gray-400 font-medium bg-white rounded-2xl border border-gray-100 shadow-sm">
                Loading workout plans...
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
                <h3 class="text-lg font-bold text-gray-900" id="modal-plan-title">Create Workout Plan</h3>
                <p class="text-xs text-gray-400 font-medium mt-0.5">Fill in plan details and add daily exercises below</p>
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
                <input type="text" id="plan-title" placeholder="e.g. Muscle Gain - 5 Days Split" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-medium text-gray-800 focus:ring-2 focus:ring-indigo-600/30 focus:border-indigo-600 outline-none transition">
            </div>

            <!-- Days -->
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-3 uppercase tracking-wider">Workout Schedule</label>
                <div class="space-y-3" id="days-container">
                    <!-- Days rendered by JS -->
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
        <div class="p-7 overflow-y-auto flex-1 space-y-3" id="view-days-container"></div>
    </div>
</div>

<!-- ===================== ASSIGN MODAL ===================== -->
<div id="assign-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="closeAssignModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-2xl shadow-2xl flex flex-col">
        <div class="px-7 py-5 border-b border-gray-100 flex items-center justify-between shrink-0">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Assign Workout Plan</h3>
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
                <input type="text" id="dup-plan-title" placeholder="e.g. Muscle Gain V2" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-medium text-gray-800 focus:ring-2 focus:ring-yellow-400/30 focus:border-yellow-400 outline-none transition">
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
            <h3 class="text-base font-bold text-gray-900 mb-1">Delete Workout Plan?</h3>
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
    .day-card { transition: all 0.2s ease; }
    .day-card:hover { transform: translateY(-1px); }
</style>
<script>
const DAY_TYPES = [
    { key: 'monday',   label: 'Monday',    icon: 'fa-calendar-day', color: 'bg-blue-50 text-blue-500' },
    { key: 'tuesday',  label: 'Tuesday',   icon: 'fa-calendar-day', color: 'bg-indigo-50 text-indigo-500' },
    { key: 'wednesday',label: 'Wednesday', icon: 'fa-calendar-day', color: 'bg-indigo-50 text-indigo-600' },
    { key: 'thursday', label: 'Thursday',  icon: 'fa-calendar-day', color: 'bg-pink-50 text-pink-500' },
    { key: 'friday',   label: 'Friday',    icon: 'fa-calendar-day', color: 'bg-rose-50 text-rose-500' },
    { key: 'saturday', label: 'Saturday',  icon: 'fa-calendar-day', color: 'bg-orange-50 text-orange-500'},
    { key: 'sunday',   label: 'Sunday',    icon: 'fa-calendar-day', color: 'bg-red-50 text-red-500'},
];

let allPlans = [];
let allMembers = [];

// ---- Load Plans ----
async function loadPlans() {
    showLoader();
    const grid = document.getElementById('workout-plans-grid');
    try {
        const res = await fetch('/api/workout-plans', {
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
    const totalDays     = plans.reduce((s, p) => s + (p.days_count || 0), 0);
    document.getElementById('stat-assigned').textContent = totalAssigned;
    document.getElementById('stat-days').textContent = totalDays;
}

function renderPlans(plans) {
    const grid = document.getElementById('workout-plans-grid');
    if (!plans.length) {
        grid.innerHTML = `
            <div class="col-span-full py-16 flex flex-col items-center gap-4 bg-white rounded-2xl border border-dashed border-gray-200">
                <div class="w-16 h-16 rounded-2xl bg-indigo-50 flex items-center justify-center">
                    <i class="fa-solid fa-dumbbell text-3xl text-indigo-600"></i>
                </div>
                <div class="text-center">
                    <div class="font-bold text-gray-700 text-base">No Workout Plans Yet</div>
                    <div class="text-sm text-gray-400 mt-1">Click "Create Workout Plan" to get started.</div>
                </div>
            </div>`;
        return;
    }

    let html = '';
    plans.forEach(p => {
        const daysPreview = DAY_TYPES.slice(0, 3).map(dt => {
            const day = p.days.find(d => d.day_label === dt.label);
            if (day && day.exercises && day.exercises.length) {
                const exStr = day.exercises.map(e => e.exercise_name || e.name).join(', ');
                return `<div class="flex items-center gap-2 text-xs"><i class="fa-solid ${dt.icon} w-4 ${dt.color.split(' ')[1]}"></i><span class="text-gray-600 truncate">${exStr.substring(0,40)}${exStr.length > 40 ? '…' : ''}</span></div>`;
            }
            return `<div class="flex items-center gap-2 text-xs"><i class="fa-solid ${dt.icon} w-4 text-gray-300"></i><span class="text-gray-300 italic">Rest Day</span></div>`;
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
                            <i class="fa-solid fa-dumbbell text-indigo-600"></i>
                        </div>
                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full ${p.active_assignments > 0 ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-gray-100 text-gray-500'}">
                            ${p.active_assignments} Active
                        </span>
                    </div>
                    <h3 class="font-bold text-gray-900 text-base mb-1 leading-tight">${p.title}</h3>
                    <p class="text-xs text-gray-400 font-medium mb-3">
                        By <span class="text-gray-600 font-bold">${p.created_by_name}</span>
                        <span class="bg-indigo-50 text-indigo-600 font-bold text-[10px] px-1.5 py-0.5 rounded ml-1">${p.created_by_role}</span>
                        · ${p.days_count} active days
                    </p>
                    <div class="space-y-1.5 mb-1">${daysPreview}</div>
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

    // Event delegation
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
function renderDaysBuilder(existingDays = []) {
    const container = document.getElementById('days-container');
    container.innerHTML = DAY_TYPES.map(dt => {
        const existing = existingDays.find(d => d.day_label === dt.label);
        
        let exercisesArray = [];
        if (existing && existing.exercises) {
            if (typeof existing.exercises === 'string') {
                exercisesArray = existing.exercises.split('\n').filter(l => l.trim()).map(l => ({ exercise_name: l.trim(), sets: '', reps: '', weight: '' }));
            } else if (Array.isArray(existing.exercises)) {
                exercisesArray = existing.exercises.map(ex => {
                    if (typeof ex === 'string') return { exercise_name: ex, sets: '', reps: '', weight: '' };
                    return { exercise_name: ex.exercise_name || ex.name || '', sets: ex.sets || '', reps: ex.reps || '', weight: ex.weight || '' };
                });
            }
        }
        if (exercisesArray.length === 0) {
            exercisesArray.push({ exercise_name: '', sets: '', reps: '', weight: '' });
        }

        let exRowsHtml = exercisesArray.map(ex => `
            <div class="flex gap-2 mb-2 items-center ex-row bg-white p-2 rounded-lg border border-gray-100">
                <input type="text" placeholder="Exercise name" class="flex-1 border border-gray-200 rounded-lg px-3 py-1.5 text-xs focus:ring-2 focus:ring-indigo-600/30 focus:border-indigo-600 outline-none" name="ex_name_${dt.key}[]" value="${ex.exercise_name}">
                <input type="number" placeholder="Sets" class="w-16 border border-gray-200 rounded-lg px-2 py-1.5 text-xs text-center focus:ring-2 focus:ring-indigo-600/30 focus:border-indigo-600 outline-none" name="ex_sets_${dt.key}[]" value="${ex.sets}">
                <input type="number" placeholder="Reps" class="w-16 border border-gray-200 rounded-lg px-2 py-1.5 text-xs text-center focus:ring-2 focus:ring-indigo-600/30 focus:border-indigo-600 outline-none" name="ex_reps_${dt.key}[]" value="${ex.reps}">
                <input type="number" placeholder="Wt(kg)" class="w-16 border border-gray-200 rounded-lg px-2 py-1.5 text-xs text-center focus:ring-2 focus:ring-indigo-600/30 focus:border-indigo-600 outline-none" name="ex_weight_${dt.key}[]" value="${ex.weight}">
                <button type="button" class="text-red-500 w-7 h-7 flex items-center justify-center hover:bg-red-50 rounded-lg transition" onclick="removeExRow(this, '${dt.key}')"><i class="fa-solid fa-xmark"></i></button>
            </div>
        `).join('');

        const hideHeader = exercisesArray.length === 0 ? 'hidden' : '';

        return `
            <div class="day-card border border-gray-100 rounded-xl p-4 bg-gray-50/50 mb-3">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg ${dt.color.split(' ')[0]} flex items-center justify-center">
                            <i class="fa-solid ${dt.icon} text-xs ${dt.color.split(' ')[1]}"></i>
                        </div>
                        <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">${dt.label}</span>
                    </div>
                    <button type="button" onclick="addExRow('${dt.key}')" class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded-md hover:bg-indigo-100 transition">+ Add Exercise</button>
                </div>
                
                <div id="ex-header-${dt.key}" class="flex gap-2 mb-1 px-1 text-[9px] font-bold text-gray-400 uppercase tracking-wider ${hideHeader}">
                    <div class="flex-1">Exercise</div>
                    <div class="w-16 text-center">Sets</div>
                    <div class="w-16 text-center">Reps</div>
                    <div class="w-16 text-center">Wt(kg)</div>
                    <div class="w-7"></div>
                </div>

                <div id="ex-container-${dt.key}">
                    ${exRowsHtml}
                </div>
                <div class="text-[10px] text-gray-400 font-medium mt-1">Leave blank if rest day</div>
            </div>`;
    }).join('');
}

function addExRow(dayKey) {
    const container = document.getElementById(`ex-container-${dayKey}`);
    const header = document.getElementById(`ex-header-${dayKey}`);
    if (header) header.classList.remove('hidden');

    const row = document.createElement('div');
    row.className = "flex gap-2 mb-2 items-center ex-row bg-white p-2 rounded-lg border border-gray-100";
    row.innerHTML = `
        <input type="text" placeholder="Exercise name" class="flex-1 border border-gray-200 rounded-lg px-3 py-1.5 text-xs focus:ring-2 focus:ring-indigo-600/30 focus:border-indigo-600 outline-none" name="ex_name_${dayKey}[]" value="">
        <input type="number" placeholder="Sets" class="w-16 border border-gray-200 rounded-lg px-2 py-1.5 text-xs text-center focus:ring-2 focus:ring-indigo-600/30 focus:border-indigo-600 outline-none" name="ex_sets_${dayKey}[]" value="">
        <input type="number" placeholder="Reps" class="w-16 border border-gray-200 rounded-lg px-2 py-1.5 text-xs text-center focus:ring-2 focus:ring-indigo-600/30 focus:border-indigo-600 outline-none" name="ex_reps_${dayKey}[]" value="">
        <input type="number" placeholder="Wt(kg)" class="w-16 border border-gray-200 rounded-lg px-2 py-1.5 text-xs text-center focus:ring-2 focus:ring-indigo-600/30 focus:border-indigo-600 outline-none" name="ex_weight_${dayKey}[]" value="">
        <button type="button" class="text-red-500 w-7 h-7 flex items-center justify-center hover:bg-red-50 rounded-lg transition" onclick="removeExRow(this, '${dayKey}')"><i class="fa-solid fa-xmark"></i></button>
    `;
    container.appendChild(row);
}

function removeExRow(btn, dayKey) {
    const container = document.getElementById(`ex-container-${dayKey}`);
    btn.parentElement.remove();
    if (container.children.length === 0) {
        const header = document.getElementById(`ex-header-${dayKey}`);
        if (header) header.classList.add('hidden');
    }
}

function openCreateModal() {
    document.getElementById('editing-plan-id').value = '';
    document.getElementById('plan-title').value = '';
    document.getElementById('modal-plan-title').textContent = 'Create Workout Plan';
    renderDaysBuilder([]);
    document.getElementById('plan-modal').classList.remove('hidden');
}

function openEditModal(id) {
    const plan = allPlans.find(p => p.id === id);
    if (!plan) return;
    document.getElementById('editing-plan-id').value = id;
    document.getElementById('plan-title').value = plan.title;
    document.getElementById('modal-plan-title').textContent = 'Edit Workout Plan';
    renderDaysBuilder(plan.days);
    document.getElementById('plan-modal').classList.remove('hidden');
}

function closePlanModal() { document.getElementById('plan-modal').classList.add('hidden'); }

async function savePlan() {
    const editingId = document.getElementById('editing-plan-id').value;
    const title = document.getElementById('plan-title').value.trim();
    if (!title) { alert('Please enter a plan title.'); return; }

    const days = [];
    DAY_TYPES.forEach(dt => {
        const names = document.querySelectorAll(`input[name="ex_name_${dt.key}[]"]`);
        const sets = document.querySelectorAll(`input[name="ex_sets_${dt.key}[]"]`);
        const reps = document.querySelectorAll(`input[name="ex_reps_${dt.key}[]"]`);
        const weights = document.querySelectorAll(`input[name="ex_weight_${dt.key}[]"]`);
        
        let exercises = [];
        for (let i = 0; i < names.length; i++) {
            if (names[i].value.trim()) {
                exercises.push({
                    exercise_name: names[i].value.trim(),
                    sets: sets[i].value ? parseInt(sets[i].value) : 0,
                    reps: reps[i].value ? parseInt(reps[i].value) : 0,
                    weight: weights[i].value ? parseFloat(weights[i].value) : 0
                });
            }
        }
        if (exercises.length > 0) {
            days.push({ day_label: dt.label, exercises });
        }
    });

    if (!days.length) { alert('Please add exercises to at least one day.'); return; }

    showLoader();
    const url    = editingId ? `/api/workout-plans/${editingId}` : '/api/workout-plans';
    const method = editingId ? 'PUT' : 'POST';

    try {
        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
            body: JSON.stringify({ title, days })
        });
        const result = await res.json();
        if (res.ok && result.success) {
            closePlanModal();
            showSuccess(editingId ? 'Workout plan updated!' : 'Workout plan created!');
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
    const container = document.getElementById('view-days-container');
    container.innerHTML = DAY_TYPES.map(dt => {
        const day = plan.days.find(d => d.day_label === dt.label);
        const exText = (day && day.exercises) ? day.exercises.map(e => `
            <div class="text-sm font-medium text-gray-800 flex items-center justify-between border-b border-gray-100 last:border-0 pb-1 mb-1">
                <div class="flex items-center gap-2"><i class="fa-solid fa-angle-right text-[10px] text-gray-400"></i> ${e.exercise_name || e.name}</div>
                <div class="text-xs text-gray-500 flex gap-3">
                    ${e.sets ? `<span><b class="text-gray-700">${e.sets}</b> Sets</span>` : ''}
                    ${e.reps ? `<span><b class="text-gray-700">${e.reps}</b> Reps</span>` : ''}
                    ${e.weight ? `<span><b class="text-gray-700">${e.weight}</b>kg</span>` : ''}
                </div>
            </div>
        `).join('') : '';
        
        return `
            <div class="flex items-start gap-3 p-4 rounded-xl ${day ? 'bg-gray-50 border border-gray-100' : 'bg-white border border-dashed border-gray-200'}">
                <div class="w-9 h-9 rounded-xl ${dt.color.split(' ')[0]} flex items-center justify-center shrink-0">
                    <i class="fa-solid ${dt.icon} ${dt.color.split(' ')[1]}"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">${dt.label}</div>
                    ${day
                        ? `<div class="space-y-1.5">${exText}</div>`
                        : `<div class="text-xs text-gray-400 italic">Rest Day</div>`}
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

    const sel = document.getElementById('assign-member');
    sel.innerHTML = '<option value="">Loading...</option>';
    try {
        const res = await fetch('/api/diet-plans/members-list', { // Using same members list endpoint
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
        const res = await fetch(`/api/workout-plans/${planId}/assign`, {
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
        const res = await fetch(`/api/workout-plans/${id}/duplicate`, {
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
        const res = await fetch(`/api/workout-plans/${id}`, {
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
