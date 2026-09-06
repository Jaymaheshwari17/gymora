@extends('superadmin.layouts.admin-layout')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-black text-gray-900 tracking-tight font-display flex items-center gap-2.5">
                <span>Excel Exports & Financial Reports</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-indigo-50 text-[#5d5fef] border border-indigo-100">REPORTS</span>
            </h1>
            <p class="text-gray-500 text-xs font-semibold mt-1">Export clean, structured Excel spreadsheets for platform accounting, tax filings, and client directory.</p>
        </div>
    </div>

    <!-- 2 Main Export Cards (P&L Financial Sheet & Gyms Directory) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- 1. Platform P&L Excel Export -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex flex-col justify-between">
            <div>
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-[#5d5fef] flex items-center justify-center text-xl mb-4">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
                <h2 class="text-base font-bold text-gray-900 font-display">Profit & Loss (P&L) Statement</h2>
                <p class="text-xs text-gray-500 font-medium mt-1 leading-relaxed">
                    Complete spreadsheet of all SaaS income collected from gyms vs all server and platform expenses, with date, payment method, and calculated Net Profit.
                </p>

                <!-- Date Range Selection for P&L -->
                <div class="mt-5 p-4 bg-gray-50 rounded-xl border border-gray-100 space-y-2">
                    <span class="text-xs font-bold text-gray-700 block">Optional Date Filter:</span>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 block">From Date</label>
                            <input type="date" id="pl-start-date" class="w-full px-2.5 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-bold text-gray-700 outline-none focus:border-[#5d5fef]">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 block">To Date</label>
                            <input type="date" id="pl-end-date" class="w-full px-2.5 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-bold text-gray-700 outline-none focus:border-[#5d5fef]">
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                <span class="text-xs text-gray-400 font-bold"><i class="fa-solid fa-file-excel text-emerald-600 mr-1"></i> Format: .CSV / Excel</span>
                <button onclick="downloadPLExcel()" class="px-5 py-2.5 bg-[#5d5fef] hover:bg-[#4d4fe0] text-white text-xs font-bold rounded-xl shadow-md shadow-[#5d5fef]/20 transition-all flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-download"></i>
                    <span>Download P&L Sheet</span>
                </button>
            </div>
        </div>

        <!-- 2. Gyms Directory & Subscriptions Excel Export -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex flex-col justify-between">
            <div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl mb-4">
                    <i class="fa-solid fa-building-user"></i>
                </div>
                <h2 class="text-base font-bold text-gray-900 font-display">Gym Clients Directory & Licensing</h2>
                <p class="text-xs text-gray-500 font-medium mt-1 leading-relaxed">
                    Download full directory of all registered gyms, owner names, mobile numbers, active plans (₹599 / ₹6,000), expiry dates, total members, and license status.
                </p>

                <div class="mt-5 p-4 bg-blue-50/50 rounded-xl border border-blue-100/80">
                    <div class="flex items-center gap-2 text-blue-900 font-bold text-xs">
                        <i class="fa-solid fa-circle-check text-blue-600"></i>
                        <span>Includes Real-time Expiry & Member Counts</span>
                    </div>
                    <p class="text-[11px] text-blue-700 mt-1 font-medium">Use this sheet to follow up with gyms for renewals or client records.</p>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                <span class="text-xs text-gray-400 font-bold"><i class="fa-solid fa-file-excel text-emerald-600 mr-1"></i> Format: .CSV / Excel</span>
                <button onclick="downloadGymsExcel()" class="px-5 py-2.5 bg-[#5d5fef] hover:bg-[#4d4fe0] text-white text-xs font-bold rounded-xl shadow-md shadow-[#5d5fef]/20 transition-all flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-download"></i>
                    <span>Download Gyms List</span>
                </button>
            </div>
        </div>

    </div>

    <!-- Instructions / Guide Card -->
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm text-xs text-gray-700">
        <div class="flex items-center gap-2 font-bold text-sm mb-2 text-gray-900">
            <i class="fa-solid fa-circle-info text-[#5d5fef]"></i>
            <span>Compatibility Note:</span>
        </div>
        <ul class="list-disc list-inside space-y-1 text-gray-500 font-medium">
            <li>Downloaded CSV spreadsheets are directly compatible with Microsoft Excel, Google Sheets, Apple Numbers, and LibreOffice.</li>
            <li>Financial amounts are currency-formatted in Indian Rupees (INR).</li>
            <li>Dates adhere to ISO standard <code class="font-bold text-[#5d5fef]">YYYY-MM-DD</code>.</li>
        </ul>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function downloadPLExcel() {
        const start = document.getElementById('pl-start-date').value;
        const end = document.getElementById('pl-end-date').value;

        let url = '/api/superadmin/export/pl';
        if (start && end) {
            url += `?start_date=${start}&end_date=${end}`;
        }

        window.open(url, '_blank');
        showToast('P&L Excel download started!', 'success');
    }

    function downloadGymsExcel() {
        const url = '/api/superadmin/export/gyms';
        window.open(url, '_blank');
        showToast('Gyms Directory Excel download started!', 'success');
    }
</script>
@endpush
