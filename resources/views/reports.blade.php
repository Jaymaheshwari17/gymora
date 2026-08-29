@extends('layouts.dashboard-layout')

@section('dashboard-content')
<div class="flex-1 overflow-y-auto p-8 bg-[#f8f9fc]">
        <div class="space-y-6">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Reports</h1>
                <p class="text-sm text-gray-500 mt-1 font-medium">Track revenue, sales, payments and other important data.</p>
            </div>
            <button onclick="downloadReport('all')" class="bg-[#4f46e5] text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-[#4338ca] transition flex items-center gap-2 shadow-lg shadow-indigo-900/20">
                <i class="fa-solid fa-download"></i> Download Report
            </button>
        </div>

        <!-- Filter Tabs -->
        <div class="bg-white p-1.5 rounded-xl inline-flex shadow-sm border border-gray-100">
            <button onclick="changeRange('week')" id="tab-week" class="report-tab active px-5 py-2 rounded-lg text-sm font-bold transition-all bg-indigo-600 text-white shadow-md">Week</button>
            <button onclick="changeRange('month')" id="tab-month" class="report-tab px-5 py-2 rounded-lg text-sm font-bold transition-all text-gray-500 hover:text-gray-900">Month</button>
            <button onclick="changeRange('quarter')" id="tab-quarter" class="report-tab px-5 py-2 rounded-lg text-sm font-bold transition-all text-gray-500 hover:text-gray-900">Quarter</button>
            <button onclick="changeRange('year')" id="tab-year" class="report-tab px-5 py-2 rounded-lg text-sm font-bold transition-all text-gray-500 hover:text-gray-900">Year</button>
        </div>

        <div id="loading-state" class="py-12 flex flex-col items-center justify-center gap-3">
            <div class="w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
            <div class="text-sm font-medium text-gray-500">Generating report data...</div>
        </div>

        <div id="report-content" class="space-y-6 hidden">
            <!-- 5 Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                
                <!-- Total Payments -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center text-green-500">
                            <i class="fa-solid fa-wallet text-lg"></i>
                        </div>
                        <div class="text-xs text-gray-500 font-bold uppercase tracking-wider">Total Payments</div>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 flex items-center gap-1">
                        ₹<span id="stat-payments">0</span>
                    </div>
                </div>

                <!-- Total Sales -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-500">
                            <i class="fa-solid fa-chart-column text-lg"></i>
                        </div>
                        <div class="text-xs text-gray-500 font-bold uppercase tracking-wider">Total Sales</div>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 flex items-center gap-1">
                        ₹<span id="stat-sales">0</span>
                    </div>
                </div>

                <!-- New Members -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-orange-50 flex items-center justify-center text-orange-500">
                            <i class="fa-solid fa-users text-lg"></i>
                        </div>
                        <div class="text-xs text-gray-500 font-bold uppercase tracking-wider">New Members</div>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 flex items-center gap-1">
                        <span id="stat-members">0</span>
                    </div>
                </div>

                <!-- Attendance Rate -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center text-purple-500">
                            <i class="fa-solid fa-stopwatch text-lg"></i>
                        </div>
                        <div class="text-xs text-gray-500 font-bold uppercase tracking-wider">Attendance Rate</div>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 flex items-center gap-1">
                        <span id="stat-attendance">0</span>%
                    </div>
                </div>

                <!-- Total Expenses -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center text-red-500">
                            <i class="fa-solid fa-money-bill text-lg"></i>
                        </div>
                        <div class="text-xs text-gray-500 font-bold uppercase tracking-wider">Total Expenses</div>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 flex items-center gap-1">
                        ₹<span id="stat-expenses">0</span>
                    </div>
                </div>

            </div>

            <!-- ROW 1: Payments & Sales -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Payments Summary -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                    <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-wallet text-green-500"></i>
                            <h2 class="text-sm font-bold text-gray-900">Payments Summary</h2>
                        </div>
                        <button onclick="downloadReport('payments')" class="text-xs text-gray-500 hover:text-gray-900 font-medium flex items-center gap-1 bg-white border border-gray-200 px-2 py-1 rounded shadow-sm">
                            <i class="fa-solid fa-download"></i> Export
                        </button>
                    </div>
                    <div class="overflow-x-auto overflow-y-auto max-h-[350px] p-0">
                        <table class="w-full text-sm text-center">
                            <thead class="text-xs text-gray-500 bg-gray-50 font-bold sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th class="px-4 py-3 text-left rounded-l-lg">Date</th>
                                    <th class="px-4 py-3">Collected Payments (₹)</th>
                                    <th class="px-4 py-3">Refunds (₹)</th>
                                    <th class="px-4 py-3 rounded-r-lg">Net Payments (₹)</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-payments" class="font-medium text-gray-900">
                                <!-- injected by js -->
                            </tbody>
                            <tfoot class="text-green-600 font-bold bg-green-50 border-t border-green-100 sticky bottom-0 z-10">
                                <tr>
                                    <td class="px-4 py-4 text-left bg-green-50">Total</td>
                                    <td class="px-4 py-4 bg-green-50" id="tfoot-collected">0</td>
                                    <td class="px-4 py-4 bg-green-50" id="tfoot-refunds">0</td>
                                    <td class="px-4 py-4 bg-green-50" id="tfoot-net">0</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Sales Summary -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                    <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-chart-column text-blue-500"></i>
                            <h2 class="text-sm font-bold text-gray-900">Sales Summary</h2>
                        </div>
                        <button onclick="downloadReport('sales')" class="text-xs text-gray-500 hover:text-gray-900 font-medium flex items-center gap-1 bg-white border border-gray-200 px-2 py-1 rounded shadow-sm">
                            <i class="fa-solid fa-download"></i> Export
                        </button>
                    </div>
                    <div class="overflow-x-auto overflow-y-auto max-h-[350px] p-0">
                        <table class="w-full text-sm text-center">
                            <thead class="text-xs text-gray-500 bg-gray-50 font-bold sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th class="px-4 py-3 text-left rounded-l-lg">Date</th>
                                    <th class="px-4 py-3">Total Sales (₹)</th>
                                    <th class="px-4 py-3">Plan Sales (₹)</th>
                                    <th class="px-4 py-3 rounded-r-lg">Other Sales (₹)</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-sales" class="font-medium text-gray-900">
                                <!-- injected by js -->
                            </tbody>
                            <tfoot class="text-blue-600 font-bold bg-blue-50 border-t border-blue-100 sticky bottom-0 z-10">
                                <tr>
                                    <td class="px-4 py-4 text-left bg-blue-50">Total</td>
                                    <td class="px-4 py-4 bg-blue-50" id="tfoot-total-sales">0</td>
                                    <td class="px-4 py-4 bg-blue-50" id="tfoot-plan-sales">0</td>
                                    <td class="px-4 py-4 bg-blue-50" id="tfoot-other-sales">0</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ROW 2: Members, Expenses, Plans -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- New Members -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-users text-orange-500"></i>
                            <h2 class="text-sm font-bold text-gray-900">New Members</h2>
                        </div>
                        <button onclick="downloadReport('members')" class="text-xs text-gray-500 hover:text-gray-900 font-medium flex items-center gap-1 bg-white border border-gray-200 px-2 py-1 rounded shadow-sm">
                            <i class="fa-solid fa-download"></i> Export
                        </button>
                    </div>
                    <div class="overflow-x-auto overflow-y-auto max-h-[350px] p-0 flex-1">
                        <table class="w-full text-sm text-center">
                            <thead class="text-xs text-gray-500 bg-gray-50 font-bold sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th class="px-4 py-3 text-left rounded-l-lg">Date</th>
                                    <th class="px-4 py-3 rounded-r-lg">New Members</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-members" class="font-medium text-gray-900">
                                <!-- injected by js -->
                            </tbody>
                            <tfoot class="text-orange-500 font-bold bg-orange-50 border-t border-orange-100 sticky bottom-0 z-10">
                                <tr>
                                    <td class="px-4 py-4 text-left bg-orange-50">Total</td>
                                    <td class="px-4 py-4 bg-orange-50" id="tfoot-members">0</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Expenses Summary -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-money-bill text-red-500"></i>
                            <h2 class="text-sm font-bold text-gray-900">Expenses Summary</h2>
                        </div>
                        <button onclick="downloadReport('expenses')" class="text-xs text-gray-500 hover:text-gray-900 font-medium flex items-center gap-1 bg-white border border-gray-200 px-2 py-1 rounded shadow-sm">
                            <i class="fa-solid fa-download"></i> Export
                        </button>
                    </div>
                    <div class="overflow-x-auto overflow-y-auto max-h-[350px] p-0 flex-1">
                        <table class="w-full text-sm text-center">
                            <thead class="text-xs text-gray-500 bg-gray-50 font-bold sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th class="px-4 py-3 text-left rounded-l-lg">Category</th>
                                    <th class="px-4 py-3 rounded-r-lg">Amount (₹)</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-expenses" class="font-medium text-gray-900">
                                <!-- injected by js -->
                            </tbody>
                            <tfoot class="text-red-500 font-bold bg-red-50 border-t border-red-100 sticky bottom-0 z-10">
                                <tr>
                                    <td class="px-4 py-4 text-left bg-red-50">Total</td>
                                    <td class="px-4 py-4 bg-red-50" id="tfoot-total-expenses">0</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Sales by Plan -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-tags text-purple-500"></i>
                            <h2 class="text-sm font-bold text-gray-900">Sales by Plan</h2>
                        </div>
                        <button onclick="downloadReport('plans')" class="text-xs text-gray-500 hover:text-gray-900 font-medium flex items-center gap-1 bg-white border border-gray-200 px-2 py-1 rounded shadow-sm">
                            <i class="fa-solid fa-download"></i> Export
                        </button>
                    </div>
                    <div class="overflow-x-auto overflow-y-auto max-h-[350px] p-0 flex-1">
                        <table class="w-full text-sm text-center">
                            <thead class="text-xs text-gray-500 bg-gray-50 font-bold sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th class="px-4 py-3 text-left rounded-l-lg">Plan Name</th>
                                    <th class="px-4 py-3">Members</th>
                                    <th class="px-4 py-3 rounded-r-lg">Sales (₹)</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-plans" class="font-medium text-gray-900">
                                <!-- injected by js -->
                            </tbody>
                            <tfoot class="text-purple-600 font-bold bg-purple-50 border-t border-purple-100 sticky bottom-0 z-10">
                                <tr>
                                    <td class="px-4 py-4 text-left bg-purple-50">Total</td>
                                    <td class="px-4 py-4 bg-purple-50" id="tfoot-plan-members">0</td>
                                    <td class="px-4 py-4 bg-purple-50" id="tfoot-plan-total">0</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
        </div>
        
        <div class="h-10"></div>
    </div>
</div>

@push('page-scripts')
<script>
    let activeRange = 'week';
    let reportData = null;

    function changeRange(range) {
        document.querySelectorAll('.report-tab').forEach(btn => {
            btn.classList.remove('active', 'bg-indigo-600', 'text-white', 'shadow-md');
            btn.classList.add('text-gray-500', 'hover:text-gray-900');
        });
        
        const activeBtn = document.getElementById('tab-' + range);
        activeBtn.classList.remove('text-gray-500', 'hover:text-gray-900');
        activeBtn.classList.add('active', 'bg-indigo-600', 'text-white', 'shadow-md');
        
        activeRange = range;
        loadReport();
    }

    async function loadReport() {
        document.getElementById('report-content').classList.add('hidden');
        document.getElementById('loading-state').classList.remove('hidden');

        try {
            const response = await fetch('/api/reports/summary?range=' + activeRange, {
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            });
            const data = await response.json();
            
            if (response.ok && data.success) {
                reportData = data.data;
                renderReport();
            } else {
                showError('Failed to load report data');
            }
        } catch (error) {
            console.error('Error fetching reports:', error);
            showError('Network error while loading reports');
        }

        document.getElementById('loading-state').classList.add('hidden');
        document.getElementById('report-content').classList.remove('hidden');
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-IN').format(amount);
    }

    function renderReport() {
        if (!reportData) return;

        // 1. Render Top Stats
        const stats = reportData.top_stats;
        document.getElementById('stat-payments').textContent = formatCurrency(stats.total_payments);
        document.getElementById('stat-sales').textContent = formatCurrency(stats.total_sales);
        document.getElementById('stat-members').textContent = stats.new_members;
        document.getElementById('stat-attendance').textContent = stats.attendance_rate;
        document.getElementById('stat-expenses').textContent = formatCurrency(stats.total_expenses);

        // 2. Render Payments & Sales & Members Timeseries
        const ts = reportData.timeseries;
        
        let htmlPayments = '';
        let htmlSales = '';
        let htmlMembers = '';
        
        let sumCol = 0, sumRef = 0, sumNet = 0;
        let sumTotalSales = 0, sumPlanSales = 0, sumOtherSales = 0;
        let sumNewMembers = 0;

        ts.forEach(row => {
            // Totals accumulation
            sumCol += row.collected;
            sumRef += row.refunds;
            sumNet += row.net_payments;
            
            sumTotalSales += row.total_sales;
            sumPlanSales += row.plan_sales;
            sumOtherSales += row.other_sales;
            
            sumNewMembers += row.new_members;

            // HTML Rows
            htmlPayments += `
                <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition">
                    <td class="px-4 py-3 text-left font-semibold">${row.date}</td>
                    <td class="px-4 py-3">${formatCurrency(row.collected)}</td>
                    <td class="px-4 py-3">${formatCurrency(row.refunds)}</td>
                    <td class="px-4 py-3 font-bold">${formatCurrency(row.net_payments)}</td>
                </tr>
            `;

            htmlSales += `
                <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition">
                    <td class="px-4 py-3 text-left font-semibold">${row.date}</td>
                    <td class="px-4 py-3 font-bold">${formatCurrency(row.total_sales)}</td>
                    <td class="px-4 py-3">${formatCurrency(row.plan_sales)}</td>
                    <td class="px-4 py-3">${formatCurrency(row.other_sales)}</td>
                </tr>
            `;

            htmlMembers += `
                <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition">
                    <td class="px-4 py-3 text-left font-semibold">${row.date}</td>
                    <td class="px-4 py-3 font-bold">${row.new_members}</td>
                </tr>
            `;
        });

        // Insert HTML
        document.getElementById('tbody-payments').innerHTML = htmlPayments || `<tr><td colspan="4" class="py-4 text-gray-500">No data available</td></tr>`;
        document.getElementById('tbody-sales').innerHTML = htmlSales || `<tr><td colspan="4" class="py-4 text-gray-500">No data available</td></tr>`;
        document.getElementById('tbody-members').innerHTML = htmlMembers || `<tr><td colspan="2" class="py-4 text-gray-500">No data available</td></tr>`;

        // Update Footers
        document.getElementById('tfoot-collected').textContent = formatCurrency(sumCol);
        document.getElementById('tfoot-refunds').textContent = formatCurrency(sumRef);
        document.getElementById('tfoot-net').textContent = formatCurrency(sumNet);
        
        document.getElementById('tfoot-total-sales').textContent = formatCurrency(sumTotalSales);
        document.getElementById('tfoot-plan-sales').textContent = formatCurrency(sumPlanSales);
        document.getElementById('tfoot-other-sales').textContent = formatCurrency(sumOtherSales);
        
        document.getElementById('tfoot-members').textContent = sumNewMembers;

        // 3. Render Expenses Summary
        let htmlExpenses = '';
        let sumTotalExp = 0;
        reportData.expenses_summary.forEach(row => {
            sumTotalExp += row.amount;
            htmlExpenses += `
                <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition">
                    <td class="px-4 py-3 text-left font-semibold">${row.category}</td>
                    <td class="px-4 py-3 font-bold">${formatCurrency(row.amount)}</td>
                </tr>
            `;
        });
        document.getElementById('tbody-expenses').innerHTML = htmlExpenses || `<tr><td colspan="2" class="py-4 text-gray-500">No data available</td></tr>`;
        document.getElementById('tfoot-total-expenses').textContent = formatCurrency(sumTotalExp);

        // 4. Render Sales by Plan
        let htmlPlans = '';
        let sumPlanMem = 0;
        let sumPlanTot = 0;
        reportData.sales_by_plan.forEach(row => {
            sumPlanMem += row.members;
            sumPlanTot += row.sales;
            htmlPlans += `
                <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition">
                    <td class="px-4 py-3 text-left font-semibold">${row.plan_name}</td>
                    <td class="px-4 py-3">${row.members}</td>
                    <td class="px-4 py-3 font-bold">${formatCurrency(row.sales)}</td>
                </tr>
            `;
        });
        document.getElementById('tbody-plans').innerHTML = htmlPlans || `<tr><td colspan="3" class="py-4 text-gray-500">No data available</td></tr>`;
        document.getElementById('tfoot-plan-members').textContent = sumPlanMem;
        document.getElementById('tfoot-plan-total').textContent = formatCurrency(sumPlanTot);
    }

    function downloadReport(type = 'all') {
        const url = `/api/reports/summary?range=${activeRange}&export=true&type=${type}`;
        
        // Temporarily append token for direct download auth (or use temporary auth token if using sanctum)
        // Since we are using Sanctum SPA/Bearer tokens, standard <a> tag downloads don't include Bearer headers easily.
        // We will fetch it, create a Blob, and trigger download.
        
        fetch(url, {
            headers: { 'Authorization': 'Bearer ' + token }
        })
        .then(response => {
            if (response.ok) {
                // Get filename from Content-Disposition if possible
                const disp = response.headers.get('Content-Disposition');
                let filename = `report_${type}_${activeRange}.csv`;
                if (disp && disp.includes('filename=')) {
                    filename = disp.split('filename=')[1].replace(/"/g, '');
                }
                
                return response.blob().then(blob => {
                    const blobUrl = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = blobUrl;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(blobUrl);
                    showSuccess('Export started successfully');
                });
            } else {
                showError('Export failed');
            }
        })
        .catch(() => showError('Network error during export'));
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadReport();
    });
</script>
@endpush
@endsection

