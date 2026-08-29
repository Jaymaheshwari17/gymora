@extends('layouts.dashboard-layout')

@section('dashboard-content')
<div class="flex-1 overflow-y-auto p-8 bg-[#f8f9fc]">
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Attendance</h1>
                <p class="text-sm text-gray-500 mt-1 font-medium">Mark daily attendance for your gym members.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="bg-white px-4 py-2.5 rounded-xl border border-gray-200 shadow-sm flex items-center gap-2">
                    <i class="fa-regular fa-calendar text-gray-400"></i>
                    <input type="date" id="attendance-date" class="text-sm font-bold text-gray-700 outline-none bg-transparent">
                </div>
            </div>
        </div>

        <!-- Data List -->
        <div id="attendance-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Data will be loaded here via JS -->
            <div class="col-span-full py-10 text-center text-gray-400 font-medium text-sm bg-white rounded-2xl border border-gray-100 shadow-sm">
                Loading members...
            </div>
        </div>
    </div>
</div>

@push('page-scripts')
<script>
    let currentDate = new Date().toISOString().split('T')[0];
    const datePicker = document.getElementById('attendance-date');
    datePicker.value = currentDate;
    datePicker.max = currentDate; // Disable future dates

    document.getElementById('attendance-date').addEventListener('change', function(e) {
        currentDate = e.target.value;
        loadAttendance();
    });

    async function loadAttendance() {
        showLoader();
        const container = document.getElementById('attendance-list');
        container.innerHTML = '<div class="col-span-full py-10 text-center text-gray-400 font-medium text-sm bg-white rounded-2xl border border-gray-100 shadow-sm">Loading...</div>';
        
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
                const members = result.data || [];
                renderAttendanceCards(members);
            } else {
                container.innerHTML = `<div class="col-span-full py-10 text-center text-red-400 font-medium text-sm bg-white rounded-2xl border border-gray-100 shadow-sm">Failed to load data.</div>`;
            }
        } catch (error) {
            console.error(error);
            container.innerHTML = `<div class="col-span-full py-10 text-center text-red-400 font-medium text-sm bg-white rounded-2xl border border-gray-100 shadow-sm">Network error. Please try again.</div>`;
        } finally {
            hideLoader();
        }
    }

    function renderAttendanceCards(members) {
        const container = document.getElementById('attendance-list');
        if (!members.length) {
            container.innerHTML = `<div class="col-span-full py-10 text-center text-gray-400 font-medium text-sm bg-white rounded-2xl border border-gray-100 shadow-sm">No active members found.</div>`;
            return;
        }

        let html = '';
        members.forEach(m => {
            const isPresent = m.is_present;
            const cardBg = isPresent ? 'bg-[#f0fdf4] border-[#10b981]/30' : 'bg-white border-gray-100';
            const btnClass = isPresent 
                ? 'bg-[#10b981] text-white shadow-md shadow-[#10b981]/30 opacity-70 cursor-not-allowed' 
                : 'bg-[#f3f4f6] text-gray-600 hover:bg-gray-200';
            const btnText = isPresent ? '<i class="fa-solid fa-check mr-1"></i> Present' : 'Mark Present';
            
            const timeHtml = m.check_in_time 
                ? `<div class="text-[11px] font-bold text-[#059669] bg-[#d1fae5] px-2 py-0.5 rounded flex items-center gap-1"><i class="fa-regular fa-clock"></i> ${m.check_in_time}</div>`
                : '<div class="text-[11px] font-bold text-gray-400 bg-gray-50 px-2 py-0.5 rounded border border-gray-100">Not Checked In</div>';

            html += `
                <div class="rounded-2xl border ${cardBg} p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between gap-4 group">
                    <div class="flex items-start gap-4">
                        <img src="${m.photo_url}" class="w-12 h-12 rounded-xl border border-gray-200 shadow-sm group-hover:scale-105 transition-transform">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-gray-900 text-sm truncate">${m.name}</h3>
                            <div class="text-xs text-gray-500 font-medium mt-0.5 truncate">${m.plan}</div>
                            <div class="mt-2 inline-flex">${timeHtml}</div>
                        </div>
                    </div>
                    <button onclick="toggleAttendance(${m.id}, ${isPresent})" class="w-full py-2 rounded-xl text-sm font-bold transition-all ${btnClass}" ${isPresent ? 'disabled' : ''}>
                        ${btnText}
                    </button>
                </div>
            `;
        });
        container.innerHTML = html;
    }

    async function toggleAttendance(memberId, currentStatus) {
        showLoader();
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
                    is_present: !currentStatus
                })
            });

            if (response.status === 401 || response.status === 403) {
                logout(); return;
            }

            const result = await response.json();
            
            if (response.ok && result.success) {
                // Reload list to get updated time and status
                loadAttendance();
            } else {
                alert(result.message || 'Failed to mark attendance');
                hideLoader();
            }
        } catch (error) {
            console.error(error);
            alert('A network error occurred.');
            hideLoader();
        }
    }

    // Load initial data
    document.addEventListener('DOMContentLoaded', function() {
        loadAttendance();
    });
</script>
@endpush
@endsection
