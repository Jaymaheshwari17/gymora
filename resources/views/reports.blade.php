@extends('layouts.dashboard-layout')

@section('dashboard-content')
<div class="flex-1 overflow-y-auto p-8 bg-[#f8f9fc]">
    <div class="max-w-6xl mx-auto space-y-6">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Reports</h1>
                <p class="text-sm text-gray-500 mt-1 font-medium">Track revenue, trends, and member activity.</p>
            </div>
            <button onclick="downloadReport()" class="bg-[#10b981] text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-[#059669] transition flex items-center gap-2 shadow-lg shadow-green-900/20">
                <i class="fa-solid fa-file-excel"></i> Download Excel
            </button>
        </div>

        <!-- Filter Tabs -->
        <div class="bg-white p-1.5 rounded-xl inline-flex shadow-sm border border-gray-100">
            <button onclick="changeRange('week')" id="tab-week" class="report-tab active px-5 py-2 rounded-lg text-sm font-bold transition-all bg-indigo-600 text-white shadow-md">Week</button>
            <button onclick="changeRange('month')" id="tab-month" class="report-tab px-5 py-2 rounded-lg text-sm font-bold transition-all text-gray-500 hover:text-gray-900">Month</button>
            <button onclick="changeRange('quarter')" id="tab-quarter" class="report-tab px-5 py-2 rounded-lg text-sm font-bold transition-all text-gray-500 hover:text-gray-900">Quarter</button>
            <button onclick="changeRange('year')" id="tab-year" class="report-tab px-5 py-2 rounded-lg text-sm font-bold transition-all text-gray-500 hover:text-gray-900">Year</button>
        </div>

        <div id="report-content" class="space-y-6 hidden">
            <!-- Collected Payments -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center">
                        <i class="fa-solid fa-money-bill-wave text-green-500"></i>
                    </div>
                    <h2 class="text-lg font-bold text-gray-900">Collected Payments</h2>
                </div>
                <div class="p-6">
                    <div id="collected-payments-grid" class="flex flex-wrap gap-4">
                        <!-- Rendered via JS -->
                    </div>
                </div>
            </div>

            <!-- Total Sell -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                        <i class="fa-solid fa-chart-line text-blue-500"></i>
                    </div>
                    <h2 class="text-lg font-bold text-gray-900">Total Sell</h2>
                </div>
                <div class="p-6">
                    <div id="total-sell-grid" class="flex flex-wrap gap-4">
                        <!-- Rendered via JS -->
                    </div>
                </div>
            </div>

            <!-- New Members -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center">
                        <i class="fa-solid fa-users text-indigo-600"></i>
                    </div>
                    <h2 class="text-lg font-bold text-gray-900">New Members</h2>
                </div>
                <div class="p-6">
                    <div id="new-members-grid" class="flex flex-wrap gap-4">
                        <!-- Rendered via JS -->
                    </div>
                </div>
            </div>
        </div>

        <div id="loading-state" class="py-12 flex flex-col items-center justify-center gap-3">
            <div class="w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
            <div class="text-sm font-medium text-gray-500">Generating report data...</div>
        </div>

    </div>
</div>

<!-- Breakdown Modal -->
<div id="breakdown-modal" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" onclick="closeBreakdownModal()"></div>
    
    <!-- Modal Content -->
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden flex flex-col max-h-[85vh]">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-gray-900" id="breakdown-title">Payment Details</h3>
                <p class="text-xs text-gray-500 font-medium mt-0.5" id="breakdown-subtitle">Breakdown</p>
            </div>
            <button onclick="closeBreakdownModal()" class="w-8 h-8 rounded-full bg-white border border-gray-200 text-gray-400 hover:text-gray-800 hover:bg-gray-100 flex items-center justify-center transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <div class="p-6 overflow-y-auto flex-1 bg-white">
            <div id="breakdown-list" class="space-y-3">
                <!-- Breakdown items will be injected here -->
            </div>
        </div>
        
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end">
            <button onclick="closeBreakdownModal()" class="px-5 py-2 bg-indigo-600 text-white text-sm font-bold rounded-xl shadow-md shadow-indigo-600/20 hover:bg-indigo-700 transition-colors">Close</button>
        </div>
    </div>
</div>

@push('page-scripts')
<script>
let currentRange = 'week';
let reportData = [];

async function fetchReportData() {
    document.getElementById('report-content').classList.add('hidden');
    document.getElementById('loading-state').classList.remove('hidden');

    try {
        const res = await fetch(`/api/reports/summary?range=${currentRange}`, {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        
        if (res.status === 401) { logout(); return; }
        
        const result = await res.json();
        if (res.ok && result.success) {
            reportData = result.data;
            renderReport();
        } else {
            showError('Failed to load report data.');
        }
    } catch(e) {
        showError('Network error while loading reports.');
    } finally {
        document.getElementById('loading-state').classList.add('hidden');
        document.getElementById('report-content').classList.remove('hidden');
    }
}

function renderReport() {
    const collectedGrid = document.getElementById('collected-payments-grid');
    const sellGrid = document.getElementById('total-sell-grid');
    const membersGrid = document.getElementById('new-members-grid');

    let htmlCollected = '';
    let htmlSell = '';
    let htmlMembers = '';

    reportData.forEach((item, index) => {
        // Collected Payments Card
        htmlCollected += `
            <div onclick="openBreakdownModal(${index}, 'collected')" class="cursor-pointer flex flex-col flex-1 min-w-[80px] bg-gray-50 p-3 rounded-xl border border-gray-100 items-center justify-center text-center hover:bg-green-50 hover:border-green-200 hover:-translate-y-1 hover:shadow-md transition-all">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">${item.label}</span>
                <span class="text-base font-bold text-gray-800">${parseFloat(item.collected).toLocaleString('en-IN', { maximumFractionDigits: 2 })}</span>
            </div>
        `;
        
        // Total Sell Card
        htmlSell += `
            <div onclick="openBreakdownModal(${index}, 'sell')" class="cursor-pointer flex flex-col flex-1 min-w-[80px] bg-gray-50 p-3 rounded-xl border border-gray-100 items-center justify-center text-center hover:bg-blue-50 hover:border-blue-200 hover:-translate-y-1 hover:shadow-md transition-all">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">${item.label}</span>
                <span class="text-base font-bold text-gray-800">${parseFloat(item.sell).toLocaleString('en-IN', { maximumFractionDigits: 2 })}</span>
            </div>
        `;

        // New Members Card
        htmlMembers += `
            <div class="flex flex-col flex-1 min-w-[80px] bg-gray-50 p-3 rounded-xl border border-gray-100 items-center justify-center text-center hover:bg-indigo-50 transition-colors">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">${item.label}</span>
                <span class="text-base font-bold text-gray-800">${item.new_members}</span>
            </div>
        `;
    });

    collectedGrid.innerHTML = htmlCollected;
    sellGrid.innerHTML = htmlSell;
    membersGrid.innerHTML = htmlMembers;
}

function changeRange(range) {
    currentRange = range;
    
    // Update Tab UI
    document.querySelectorAll('.report-tab').forEach(tab => {
        tab.classList.remove('bg-indigo-600', 'text-white', 'shadow-md', 'active');
        tab.classList.add('text-gray-500', 'hover:text-gray-900');
    });
    
    const activeTab = document.getElementById(`tab-${range}`);
    activeTab.classList.remove('text-gray-500', 'hover:text-gray-900');
    activeTab.classList.add('bg-indigo-600', 'text-white', 'shadow-md', 'active');

    fetchReportData();
}

async function downloadReport() {
    const btn = document.querySelector('button[onclick="downloadReport()"]');
    const originalContent = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Downloading...';
    btn.disabled = true;

    try {
        const res = await fetch(`/api/reports/summary?range=${currentRange}&export=true`, {
            headers: { 'Authorization': 'Bearer ' + token }
        });
        
        if (res.status === 401) { logout(); return; }
        
        if (res.ok) {
            const blob = await res.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `report_${currentRange}_${new Date().getTime()}.csv`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            a.remove();
        } else {
            showToast('Failed to download report.', 'error');
        }
    } catch(e) {
        showToast('Network error while downloading.', 'error');
    } finally {
        btn.innerHTML = originalContent;
        btn.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', fetchReportData);

function openBreakdownModal(index, type) {
    const bucket = reportData[index];
    if (!bucket || !bucket.breakdown || bucket.breakdown.length === 0) {
        showToast('No payments recorded for this period.', 'info');
        return;
    }

    const title = type === 'collected' ? 'Collected Payments Breakdown' : 'Total Sell Breakdown';
    document.getElementById('breakdown-title').textContent = title;
    document.getElementById('breakdown-subtitle').textContent = `Period: ${bucket.label}`;

    let html = '';
    bucket.breakdown.forEach(item => {
        const amountDisplay = type === 'collected' ? item.paid : item.total;
        html += `
            <div class="flex items-center justify-between p-4 bg-gray-50 border border-gray-100 rounded-xl hover:bg-white transition-colors shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-gray-900">${item.name}</div>
                        <div class="text-xs text-gray-500 font-medium"><i class="fa-solid fa-phone text-[10px] mr-1"></i>${item.mobile}</div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-base font-black text-gray-900">₹${parseFloat(amountDisplay).toLocaleString('en-IN')}</div>
                    <div class="text-[10px] text-gray-400 font-bold uppercase">${item.date}</div>
                </div>
            </div>
        `;
    });

    document.getElementById('breakdown-list').innerHTML = html;
    document.getElementById('breakdown-modal').classList.remove('hidden');
}

function closeBreakdownModal() {
    document.getElementById('breakdown-modal').classList.add('hidden');
}

</script>
@endpush
@endsection
