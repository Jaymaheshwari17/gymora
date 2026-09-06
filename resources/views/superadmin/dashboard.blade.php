@extends('superadmin.layouts.admin-layout')

@section('content')
<div class="space-y-6">

    <!-- Page Header with Date Filter -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-black text-gray-900 tracking-tight font-display flex items-center gap-2.5">
                <span>Platform SaaS Control</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-indigo-50 text-[#5d5fef] border border-indigo-100">SUPER ADMIN</span>
            </h1>
            <p class="text-gray-500 text-xs font-semibold mt-1">Real-time SaaS revenue, cloud server expenses, net profit, and gym client metrics.</p>
        </div>

        <!-- Date Range Filter -->
        <div class="flex flex-wrap items-center gap-2 bg-white px-3.5 py-2 rounded-2xl border border-gray-200 shadow-sm text-xs font-bold">
            <span class="text-gray-400 pl-1"><i class="fa-regular fa-calendar-days text-[#5d5fef]"></i> Date Range:</span>
            <input type="date" id="dash-start-date" class="px-2.5 py-1 bg-gray-50 border border-gray-200 rounded-xl text-gray-700 outline-none focus:bg-white focus:border-[#5d5fef] text-xs font-bold">
            <span class="text-gray-400">to</span>
            <input type="date" id="dash-end-date" class="px-2.5 py-1 bg-gray-50 border border-gray-200 rounded-xl text-gray-700 outline-none focus:bg-white focus:border-[#5d5fef] text-xs font-bold">
            <button onclick="loadDashboardData()" class="px-3.5 py-1.5 bg-[#5d5fef] hover:bg-[#4d4fe0] text-white rounded-xl transition-all shadow-md shadow-[#5d5fef]/20 cursor-pointer">
                Filter
            </button>
            <button onclick="resetDateFilter()" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition-colors cursor-pointer">
                Reset
            </button>
        </div>
    </div>

    <!-- 4 Main Financial KPI Cards (Revenue, Expenses, Net Profit, Active Clients) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        
        <!-- 1. Total SaaS Revenue -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-all flex flex-col justify-between h-[140px]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total SaaS Revenue</span>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-[#5d5fef] flex items-center justify-center text-base">
                    <i class="fa-solid fa-wallet"></i>
                </div>
            </div>
            <div>
                <div class="text-3xl font-black text-gray-900 tracking-tight">
                    ₹<span id="stat-saas-revenue">0.00</span>
                </div>
                <div class="text-[11px] text-gray-400 font-semibold mt-0.5">Total software subscriptions</div>
            </div>
        </div>

        <!-- 2. Platform Expenses -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-all flex flex-col justify-between h-[140px]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Platform Expenses</span>
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center text-base">
                    <i class="fa-solid fa-server"></i>
                </div>
            </div>
            <div>
                <div class="text-3xl font-black text-rose-600 tracking-tight">
                    ₹<span id="stat-platform-expenses">0.00</span>
                </div>
                <div class="text-[11px] text-gray-400 font-semibold mt-0.5">Hosting, SMS, APIs & Tools</div>
            </div>
        </div>

        <!-- 3. Net Profit / Loss -->
        <div id="card-net-profit" class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-all flex flex-col justify-between h-[140px]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Net Profit</span>
                <div id="icon-profit-bg" class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base">
                    <i class="fa-solid fa-chart-line" id="icon-profit"></i>
                </div>
            </div>
            <div>
                <div class="text-3xl font-black tracking-tight" id="text-profit-val">
                    ₹<span id="stat-net-profit">0.00</span>
                </div>
                <div class="flex items-center gap-2 mt-0.5">
                    <span id="badge-profit-margin" class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-600">0% Margin</span>
                    <span class="text-[11px] text-gray-400 font-semibold">After all expenses</span>
                </div>
            </div>
        </div>

        <!-- 4. Active Gym Clients -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-all flex flex-col justify-between h-[140px]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Active Gym Clients</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base">
                    <i class="fa-solid fa-building-circle-check"></i>
                </div>
            </div>
            <div>
                <div class="text-3xl font-black text-gray-900 tracking-tight">
                    <span id="stat-active-gyms">0</span> <span class="text-lg font-bold text-gray-400">/ <span id="stat-total-gyms">0</span></span>
                </div>
                <div class="text-[11px] text-gray-400 font-semibold mt-0.5">Active software licenses</div>
            </div>
        </div>
    </div>

    <!-- Row 2: Secondary Operational Badges -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm shrink-0">
                <i class="fa-regular fa-clock"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold text-gray-400 block">Expiring in 7 Days</span>
                <span class="text-base font-black text-amber-600" id="stat-expiring-soon">0 Gyms</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center font-bold text-sm shrink-0">
                <i class="fa-solid fa-ban"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold text-gray-400 block">Expired / Suspended</span>
                <span class="text-base font-black text-rose-600" id="stat-expired-gyms">0 Gyms</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center font-bold text-sm shrink-0">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold text-gray-400 block">Platform Members</span>
                <span class="text-base font-black text-blue-600" id="stat-platform-members">0 Members</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm shrink-0">
                <i class="fa-solid fa-cloud-arrow-up"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold text-gray-400 block">Platform Health</span>
                <span class="text-xs font-black text-emerald-600">● 100% Operational</span>
            </div>
        </div>
    </div>

    <!-- Row 3: Financial Comparison Bar Chart + Expense Breakdown Donut -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left: Monthly P&L Trend Bar Chart (Col 8) -->
        <div class="lg:col-span-8 bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-bold text-gray-900 font-display">Monthly Financial Overview (P&L Trend)</h2>
                    <p class="text-xs text-gray-400 font-medium">Comparison of SaaS Revenue vs Platform Expenses over the last 6 months</p>
                </div>
                <div class="flex items-center gap-3 text-xs font-semibold text-gray-600">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-[#5d5fef]"></span> Revenue</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-rose-500"></span> Expenses</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-emerald-500"></span> Profit</span>
                </div>
            </div>
            <div class="h-64 w-full">
                <canvas id="monthlyTrendChart"></canvas>
            </div>
        </div>

        <!-- Right: Expense Categories Breakdown (Col 4) -->
        <div class="lg:col-span-4 bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex flex-col justify-between">
            <div>
                <h2 class="text-sm font-bold text-gray-900 font-display mb-1">Expense Breakdown</h2>
                <p class="text-xs text-gray-400 font-medium mb-4">Distribution of platform operational costs</p>
                <div class="h-44 w-full flex items-center justify-center">
                    <canvas id="expenseDonutChart"></canvas>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-gray-100 text-center">
                <a href="/admin/expenses" class="text-xs font-bold text-[#5d5fef] hover:underline transition-colors">
                    Manage Platform Expenses &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- Row 4: Recent Subscriptions & Expiring Gyms Alert Table -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Recent Subscriptions -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-gray-900 font-display flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-[#5d5fef]"></i>
                    <span>Recent SaaS Payments</span>
                </h3>
                <a href="/admin/reports" class="text-xs font-bold text-[#5d5fef] hover:underline">View All</a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-gray-100 text-gray-400 font-bold uppercase tracking-wider text-[10px]">
                            <th class="pb-2.5">Gym Name</th>
                            <th class="pb-2.5">Plan</th>
                            <th class="pb-2.5">Amount</th>
                            <th class="pb-2.5">Date</th>
                            <th class="pb-2.5">Status</th>
                        </tr>
                    </thead>
                    <tbody id="recent-subs-tbody" class="divide-y divide-gray-50 font-semibold">
                        <tr><td colspan="5" class="py-4 text-center text-gray-400">Loading payments...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Expiring Gyms Alerts -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-gray-900 font-display flex items-center gap-2">
                    <i class="fa-solid fa-bell text-amber-500"></i>
                    <span>Subscriptions Ending Soon</span>
                </h3>
                <a href="/admin/gyms?status=expiring" class="text-xs font-bold text-[#5d5fef] hover:underline">Renew Clients &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-gray-100 text-gray-400 font-bold uppercase tracking-wider text-[10px]">
                            <th class="pb-2.5">Gym</th>
                            <th class="pb-2.5">Owner / Contact</th>
                            <th class="pb-2.5">Expiry Date</th>
                            <th class="pb-2.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody id="expiring-gyms-tbody" class="divide-y divide-gray-50 font-semibold">
                        <tr><td colspan="4" class="py-4 text-center text-gray-400">No gyms expiring soon.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    let trendChartInstance = null;
    let expenseDonutInstance = null;

    async function loadDashboardData() {
        const token = localStorage.getItem('superadmin_token') || sessionStorage.getItem('superadmin_token');
        const startDate = document.getElementById('dash-start-date').value;
        const endDate = document.getElementById('dash-end-date').value;

        let url = '/api/superadmin/dashboard-stats';
        if (startDate && endDate) {
            url += `?start_date=${startDate}&end_date=${endDate}`;
        }

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
                renderDashboard(json.data);
            }
        } catch (err) {
            console.error('Failed to load super admin stats:', err);
        }
    }

    function renderDashboard(data) {
        const fin = data.financials;
        const gyms = data.gym_stats;

        // 1. Financial Numbers
        document.getElementById('stat-saas-revenue').textContent = Number(fin.total_revenue).toLocaleString('en-IN', { minimumFractionDigits: 2 });
        document.getElementById('stat-platform-expenses').textContent = Number(fin.total_expenses).toLocaleString('en-IN', { minimumFractionDigits: 2 });
        
        const netProfitEl = document.getElementById('stat-net-profit');
        const profitValEl = document.getElementById('text-profit-val');
        const profitMarginBadge = document.getElementById('badge-profit-margin');
        const profitIconBg = document.getElementById('icon-profit-bg');

        netProfitEl.textContent = Number(Math.abs(fin.net_profit)).toLocaleString('en-IN', { minimumFractionDigits: 2 });
        
        if (fin.net_profit >= 0) {
            profitValEl.className = 'text-3xl font-black tracking-tight text-emerald-600';
            profitMarginBadge.className = 'px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-600';
            profitMarginBadge.textContent = `+${fin.profit_margin}% Margin`;
            profitIconBg.className = 'w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base';
        } else {
            profitValEl.className = 'text-3xl font-black tracking-tight text-rose-600';
            profitMarginBadge.className = 'px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-50 text-rose-600';
            profitMarginBadge.textContent = `${fin.profit_margin}% Loss`;
            profitIconBg.className = 'w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-base';
        }

        // 2. Gyms Stats
        document.getElementById('stat-active-gyms').textContent = gyms.active_gyms;
        document.getElementById('stat-total-gyms').textContent = gyms.total_gyms;
        document.getElementById('stat-expiring-soon').textContent = `${gyms.expiring_soon_gyms} Gyms`;
        document.getElementById('stat-expired-gyms').textContent = `${gyms.expired_gyms + gyms.suspended_gyms} Gyms`;
        document.getElementById('stat-platform-members').textContent = `${gyms.total_platform_members} Members`;

        // 3. Render Trend Bar Chart
        renderMonthlyChart(data.monthly_trends);

        // 4. Render Expense Donut
        renderExpenseDonut(data.expense_categories);

        // 5. Recent Subscriptions Table
        renderRecentSubs(data.recent_subscriptions);

        // 6. Expiring Gyms Table
        renderExpiringGyms(data.expiring_gyms_list);
    }

    function renderMonthlyChart(trends) {
        const ctx = document.getElementById('monthlyTrendChart').getContext('2d');
        if (trendChartInstance) trendChartInstance.destroy();

        const labels = trends.map(t => t.month);
        const revenues = trends.map(t => t.revenue);
        const expenses = trends.map(t => t.expense);
        const profits = trends.map(t => t.profit);

        trendChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'SaaS Revenue (₹)',
                        data: revenues,
                        backgroundColor: '#5d5fef',
                        borderRadius: 6,
                    },
                    {
                        label: 'Expenses (₹)',
                        data: expenses,
                        backgroundColor: '#f43f5e',
                        borderRadius: 6,
                    },
                    {
                        label: 'Net Profit (₹)',
                        data: profits,
                        backgroundColor: '#10b981',
                        borderRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            callback: value => '₹' + value.toLocaleString()
                        }
                    }
                }
            }
        });
    }

    function renderExpenseDonut(categories) {
        const ctx = document.getElementById('expenseDonutChart').getContext('2d');
        if (expenseDonutInstance) expenseDonutInstance.destroy();

        const labels = categories.length ? categories.map(c => c.category) : ['No Expenses'];
        const dataVals = categories.length ? categories.map(c => parseFloat(c.total_amount)) : [1];
        const bgColors = ['#5d5fef', '#f43f5e', '#3b82f6', '#f59e0b', '#10b981', '#8b5cf6'];

        expenseDonutInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: dataVals,
                    backgroundColor: bgColors,
                    borderWidth: 2,
                    borderColor: '#ffffff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } }
                },
                cutout: '70%'
            }
        });
    }

    function renderRecentSubs(subs) {
        const tbody = document.getElementById('recent-subs-tbody');
        if (!subs || !subs.length) {
            tbody.innerHTML = `<tr><td colspan="5" class="py-4 text-center text-gray-400">No recent subscriptions found.</td></tr>`;
            return;
        }

        tbody.innerHTML = subs.map(s => `
            <tr class="hover:bg-gray-50/80 transition-colors">
                <td class="py-2.5 font-bold text-gray-800">${s.gym ? s.gym.name : 'Unknown'}</td>
                <td class="py-2.5 text-gray-500">${s.plan_name}</td>
                <td class="py-2.5 font-bold text-[#5d5fef]">₹${Number(s.amount_paid).toLocaleString('en-IN')}</td>
                <td class="py-2.5 text-gray-400">${new Date(s.start_date || s.created_at).toLocaleDateString('en-GB')}</td>
                <td class="py-2.5"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600">Paid</span></td>
            </tr>
        `).join('');
    }

    function renderExpiringGyms(gyms) {
        const tbody = document.getElementById('expiring-gyms-tbody');
        if (!gyms || !gyms.length) {
            tbody.innerHTML = `<tr><td colspan="4" class="py-4 text-center text-gray-400">No gyms expiring soon.</td></tr>`;
            return;
        }

        tbody.innerHTML = gyms.map(g => `
            <tr class="hover:bg-gray-50/80 transition-colors">
                <td class="py-2.5 font-bold text-gray-800">${g.name}</td>
                <td class="py-2.5 text-gray-600">${g.owner ? g.owner.name + ' (' + g.owner.mobile + ')' : g.contact_number || 'N/A'}</td>
                <td class="py-2.5 font-bold text-amber-600">${new Date(g.subscription_end_date).toLocaleDateString('en-GB')}</td>
                <td class="py-2.5 text-right">
                    <a href="/admin/gyms" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-[#5d5fef] rounded-lg font-bold text-[11px] transition-colors">Renew</a>
                </td>
            </tr>
        `).join('');
    }

    function resetDateFilter() {
        document.getElementById('dash-start-date').value = '';
        document.getElementById('dash-end-date').value = '';
        loadDashboardData();
    }

    document.addEventListener('DOMContentLoaded', loadDashboardData);
</script>
@endpush
