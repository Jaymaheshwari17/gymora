@extends('layouts.dashboard-layout')

@section('dashboard-content')
<div class="flex-1 overflow-y-auto p-8 bg-[#f8f9fc]">
    <div class="space-y-6">
        
        <!-- Header & Top Actions -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-200 pb-5">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Attendance</h1>
                <p class="text-sm text-gray-500 mt-1 font-medium">Mark and manage daily member attendance</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="bg-white px-4 py-2.5 rounded-xl border border-gray-200 shadow-sm flex items-center gap-2">
                    <i class="fa-regular fa-calendar text-gray-400"></i>
                    <input type="date" id="attendance-date" class="text-sm font-bold text-gray-700 outline-none bg-transparent">
                </div>
                <button onclick="markBulkAttendance()" class="bg-[#5638CE] text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-700 transition flex items-center gap-2 shadow-lg shadow-[#5638CE]/30">
                    <i class="fa-solid fa-check-double"></i> Mark Bulk Attendance
                </button>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Total Members -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <i class="fa-solid fa-users text-2xl"></i>
                </div>
                <div>
                    <div class="text-sm text-gray-500 font-medium">Total Members</div>
                    <div class="text-3xl font-bold text-gray-900" id="stat-total">0</div>
                </div>
            </div>
            
            <!-- Present -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-green-50 flex items-center justify-center text-green-600">
                    <i class="fa-solid fa-user-check text-2xl"></i>
                </div>
                <div>
                    <div class="text-sm text-gray-500 font-medium">Present</div>
                    <div class="flex items-baseline gap-2">
                        <div class="text-3xl font-bold text-gray-900" id="stat-present">0</div>
                        <div class="text-sm text-gray-400 font-medium" id="stat-present-perc">(0%)</div>
                    </div>
                </div>
            </div>

            <!-- Absent -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-red-50 flex items-center justify-center text-red-500">
                    <i class="fa-solid fa-user-times text-2xl"></i>
                </div>
                <div>
                    <div class="text-sm text-gray-500 font-medium">Absent</div>
                    <div class="flex items-baseline gap-2">
                        <div class="text-3xl font-bold text-gray-900" id="stat-absent">0</div>
                        <div class="text-sm text-gray-400 font-medium" id="stat-absent-perc">(0%)</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Table Section -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                Mark Attendance - <span id="current-date-text"></span>
            </h2>

            <div class="overflow-x-auto">
                <table class="w-full text-left" id="attendanceTable">
                    <thead>
                        <tr class="text-xs text-gray-400 uppercase tracking-wider border-b border-gray-100">
                            <th class="py-3 px-4 font-bold">Member</th>
                            <th class="py-3 px-4 font-bold">Plan</th>
                            <th class="py-3 px-4 font-bold text-center">Status</th>
                            <th class="py-3 px-4 font-bold text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="attendance-tbody" class="divide-y divide-gray-50">
                        <tr>
                            <td colspan="4" class="text-center py-10 text-gray-400 font-medium text-sm">Loading members...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-6 flex justify-end">
                <button onclick="saveAllAttendance()" class="bg-[#5638CE] text-white px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-indigo-700 transition shadow-lg shadow-[#5638CE]/30">
                    <i class="fa-solid fa-save mr-2"></i> Save Attendance
                </button>
            </div>
        </div>
    </div>
</div>

@push('page-scripts')
<script>
    let currentDate = new Date().toISOString().split('T')[0];
    const datePicker = document.getElementById('attendance-date');
    datePicker.value = currentDate;
    datePicker.max = currentDate;
    updateDateText();

    let membersList = [];
    let attendanceChanges = {}; // To track changes for bulk save

    datePicker.addEventListener('change', function(e) {
        currentDate = e.target.value;
        updateDateText();
        loadAttendance();
    });
    
    function updateDateText() {
        const options = { day: '2-digit', month: 'short', year: 'numeric' };
        document.getElementById('current-date-text').textContent = new Date(currentDate).toLocaleDateString('en-GB', options);
    }

    async function loadAttendance() {
        showLoader();
        attendanceChanges = {}; // Reset changes
        const tbody = document.getElementById('attendance-tbody');
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-10 text-gray-400 font-medium text-sm">Loading...</td></tr>';
        
        try {
            const response = await fetch(`/api/attendance/members?date=${currentDate}`, {
                method: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                }
            });

            if (response.status === 401 || response.status === 403) {
                logout(); return;
            }

            const result = await response.json();
            
            if (response.ok && result.success) {
                membersList = result.data || [];
                renderTable();
                updateStats();
            } else {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center py-10 text-red-400 font-medium text-sm">Failed to load data.</td></tr>`;
            }
        } catch (error) {
            console.error(error);
            tbody.innerHTML = `<tr><td colspan="4" class="text-center py-10 text-red-400 font-medium text-sm">Network error. Please try again.</td></tr>`;
        } finally {
            hideLoader();
        }
    }

    function renderTable() {
        const tbody = document.getElementById('attendance-tbody');
        if (!membersList.length) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center py-10 text-gray-400 font-medium text-sm">No active members found.</td></tr>`;
            return;
        }

        let html = '';
        membersList.forEach(m => {
            // Apply un-saved changes if any exist in the UI state
            const currentStatus = attendanceChanges[m.id] !== undefined ? attendanceChanges[m.id] : m.status;
            
            let statusPill = `<span class="text-gray-400 font-medium text-xs bg-gray-50 px-2 py-1 rounded">Pending</span>`;
            if (currentStatus === 'P') {
                statusPill = `<span class="text-green-600 font-bold text-xs bg-green-50 px-3 py-1 rounded border border-green-200">Present</span>`;
            } else if (currentStatus === 'A') {
                statusPill = `<span class="text-red-500 font-bold text-xs bg-red-50 px-3 py-1 rounded border border-red-200">Absent</span>`;
            }

            const isP = currentStatus === 'P';
            const isA = currentStatus === 'A';
            
            // Buttons UI logic
            let btnPClass = isP ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'bg-white border-gray-200 text-gray-500 hover:bg-gray-50';
            const btnPChecked = isP ? '<i class="fa-solid fa-circle-check text-indigo-600 mr-1.5"></i>' : '<i class="fa-regular fa-circle mr-1.5 text-gray-300"></i>';
            const btnPDisabledAttr = isA ? 'disabled' : '';
            if (isA) btnPClass += ' opacity-50 cursor-not-allowed pointer-events-none';

            let btnAClass = isA ? 'bg-red-50 border-red-500 text-red-600' : 'bg-white border-gray-200 text-gray-500 hover:bg-gray-50';
            const btnAChecked = isA ? '<i class="fa-solid fa-circle-xmark text-red-500 mr-1.5"></i>' : '<i class="fa-regular fa-circle mr-1.5 text-gray-300"></i>';
            const btnADisabledAttr = isP ? 'disabled' : '';
            if (isP) btnAClass += ' opacity-50 cursor-not-allowed pointer-events-none';

            html += `
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="py-4 px-4">
                        <div class="flex items-center gap-3">
                            <img src="${m.photo_url}" class="w-10 h-10 rounded-full border border-gray-200 object-cover">
                            <div>
                                <div class="font-bold text-gray-900 text-sm">${m.name}</div>
                                <div class="text-xs text-gray-400 font-medium mt-0.5">ID: GM${1000 + m.id}</div>
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-4 text-sm font-medium text-gray-600">${m.plan}</td>
                    <td class="py-4 px-4 text-center">${statusPill}</td>
                    <td class="py-4 px-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <button onclick="markStatus(${m.id}, 'P')" ${btnPDisabledAttr} class="flex items-center px-3 py-1.5 rounded-lg border text-xs font-bold transition-all ${btnPClass}">
                                ${btnPChecked} Present
                            </button>
                            <button onclick="markStatus(${m.id}, 'A')" ${btnADisabledAttr} class="flex items-center px-3 py-1.5 rounded-lg border text-xs font-bold transition-all ${btnAClass}">
                                ${btnAChecked} Absent
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }
    
    function markStatus(memberId, status) {
        attendanceChanges[memberId] = status;
        
        // Optimistic UI update in membersList to recalculate stats quickly
        const member = membersList.find(m => m.id === memberId);
        if(member) member.status = status; // Temporary visual update
        
        renderTable();
        updateStats();
        
        // Also save to backend immediately for instant feedback
        saveSingleAttendance(memberId, status);
    }
    
    async function saveSingleAttendance(memberId, status) {
        try {
            const response = await fetch('/api/attendance/mark', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    member_id: memberId,
                    date: currentDate,
                    status: status
                })
            });
            if (response.status === 401 || response.status === 403) {
                logout(); return;
            }
        } catch (error) {
            console.error('Error saving attendance:', error);
        }
    }

    async function markBulkAttendance() {
        // Marks all pending members as Present
        if (!membersList.length) return;
        
        let changed = false;
        membersList.forEach(m => {
            const currentStatus = attendanceChanges[m.id] !== undefined ? attendanceChanges[m.id] : m.status;
            if (currentStatus === null || currentStatus === undefined) {
                attendanceChanges[m.id] = 'P';
                m.status = 'P';
                saveSingleAttendance(m.id, 'P');
                changed = true;
            }
        });
        
        if (changed) {
            renderTable();
            updateStats();
            if(typeof showSuccess === 'function') showSuccess("All pending members marked as Present.");
        } else {
            alert("No pending members to mark.");
        }
    }

    async function saveAllAttendance() {
        if(typeof showSuccess === 'function') showSuccess("Attendance saved successfully!");
    }

    function updateStats() {
        const total = membersList.length;
        let presentCount = 0;
        let absentCount = 0;
        
        membersList.forEach(m => {
            const status = attendanceChanges[m.id] !== undefined ? attendanceChanges[m.id] : m.status;
            if (status === 'P') presentCount++;
            else if (status === 'A') absentCount++;
        });

        document.getElementById('stat-total').textContent = total;
        document.getElementById('stat-present').textContent = presentCount;
        document.getElementById('stat-absent').textContent = absentCount;
        
        const presentPerc = total > 0 ? ((presentCount / total) * 100).toFixed(2) : '0.00';
        const absentPerc = total > 0 ? ((absentCount / total) * 100).toFixed(2) : '0.00';
        
        document.getElementById('stat-present-perc').textContent = `(${presentPerc}%)`;
        document.getElementById('stat-absent-perc').textContent = `(${absentPerc}%)`;
    }

    document.addEventListener('DOMContentLoaded', function() {
        loadAttendance();
    });
</script>
@endpush
@endsection

