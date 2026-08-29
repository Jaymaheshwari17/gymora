@extends('layouts.dashboard-layout')

@section('dashboard-content')
<!-- Dashboard Content -->
<div class="flex-1 overflow-y-auto p-8">
    <!-- Header Row -->
    <div class="flex justify-between items-end mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-1">Welcome back, <span id="welcome-name">...</span>! 👋</h1>
            <p class="text-gray-500 text-sm font-medium">Here's what's happening with your gym today.</p>
        </div>
        <a href="/members" class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-700 transition flex items-center gap-2 shadow-sm shadow-indigo-600/20">
            <i class="fa-solid fa-plus"></i> Add New
        </a>
    </div>

    <!-- Important Alerts -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm flex items-center justify-between hover:border-red-100 hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center text-red-500 shrink-0">
                    <i class="fa-regular fa-clock text-xl"></i>
                </div>
                <div>
                    <h3 class="text-gray-900 font-bold text-sm mb-0.5">Expiring Soon</h3>
                    <p class="text-[11px] text-gray-500 font-medium">Members whose plans end soon.</p>
                </div>
            </div>
            <div class="text-2xl font-black text-red-500" id="stat-expiring-soon">0</div>
        </div>
        
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm flex items-center justify-between hover:border-orange-100 hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-orange-50 flex items-center justify-center text-orange-500 shrink-0">
                    <i class="fa-regular fa-calendar-xmark text-xl"></i>
                </div>
                <div>
                    <h3 class="text-gray-900 font-bold text-sm mb-0.5">Expired This Month</h3>
                    <p class="text-[11px] text-gray-500 font-medium">Plans that expired recently.</p>
                </div>
            </div>
            <div class="text-2xl font-black text-orange-500" id="stat-expired-month">0</div>
        </div>
        
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm flex items-center justify-between hover:border-amber-100 hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-amber-50 flex items-center justify-center text-amber-500 shrink-0">
                    <i class="fa-solid fa-indian-rupee-sign text-xl"></i>
                </div>
                <div>
                    <h3 class="text-gray-900 font-bold text-sm mb-0.5">Due This Month</h3>
                    <p class="text-[11px] text-gray-500 font-medium">Members with pending fees.</p>
                </div>
            </div>
            <div class="text-2xl font-black text-amber-500" id="stat-due-month">0</div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Members -->
        <div class="group bg-white border border-gray-100 rounded-2xl p-6 relative overflow-hidden shadow-sm flex flex-col justify-between h-[150px] hover:border-indigo-500 hover:-translate-y-1 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center gap-3 relative z-10">
                <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
                    <i class="fa-solid fa-users-viewfinder text-lg"></i>
                </div>
                <span class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition-colors duration-300">Total Members</span>
            </div>
            <div class="relative z-10">
                <div class="text-3xl font-black text-gray-900" id="stat-members">0</div>
                <div class="text-[11px] text-gray-500 font-medium mt-1">All registered members</div>
            </div>
            <!-- Decorative icon/graphic at bottom right -->
            <i class="fa-solid fa-users absolute -right-4 -bottom-4 text-[100px] text-gray-50 opacity-80 group-hover:text-indigo-50 group-hover:scale-110 transition-transform duration-500"></i>
        </div>

        <!-- Active Members -->
        <div class="group bg-white border border-gray-100 rounded-2xl p-6 relative overflow-hidden shadow-sm flex flex-col justify-between h-[150px] hover:border-blue-500 hover:-translate-y-1 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center gap-3 relative z-10">
                <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-500 group-hover:bg-blue-500 group-hover:text-white transition-colors duration-300">
                    <i class="fa-regular fa-user text-lg"></i>
                </div>
                <span class="text-sm font-bold text-gray-900 group-hover:text-blue-600 transition-colors duration-300">Active Members</span>
            </div>
            <div class="relative z-10">
                <div class="text-3xl font-black text-gray-900" id="stat-active-members">0</div>
                <div class="text-[11px] text-gray-500 font-medium mt-1">Currently active members</div>
            </div>
            <i class="fa-solid fa-user-check absolute -right-2 -bottom-2 text-[100px] text-gray-50 opacity-80 group-hover:text-blue-50 group-hover:scale-110 transition-transform duration-500"></i>
        </div>

        <!-- Pending Fees -->
        <div class="group bg-white border border-gray-100 rounded-2xl p-6 relative overflow-hidden shadow-sm flex flex-col justify-between h-[150px] hover:border-emerald-500 hover:-translate-y-1 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center gap-3 relative z-10">
                <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500 group-hover:bg-emerald-500 group-hover:text-white transition-colors duration-300">
                    <i class="fa-solid fa-wallet text-lg"></i>
                </div>
                <span class="text-sm font-bold text-gray-900 group-hover:text-emerald-600 transition-colors duration-300">Pending Fees</span>
            </div>
            <div class="relative z-10">
                <div class="text-3xl font-black text-gray-900">₹<span id="stat-pending">0</span></div>
                <div class="text-[11px] text-gray-500 font-medium mt-1">Total pending amount</div>
            </div>
            <i class="fa-solid fa-wallet absolute -right-4 -bottom-4 text-[90px] text-gray-50 opacity-80 group-hover:text-emerald-50 group-hover:scale-110 transition-transform duration-500"></i>
        </div>

        <!-- Collected Fees -->
        <div class="group bg-white border border-gray-100 rounded-2xl p-6 relative overflow-hidden shadow-sm flex flex-col justify-between h-[150px] hover:border-purple-500 hover:-translate-y-1 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center gap-3 relative z-10">
                <div class="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center text-purple-500 group-hover:bg-purple-500 group-hover:text-white transition-colors duration-300">
                    <i class="fa-solid fa-money-check-dollar text-lg"></i>
                </div>
                <span class="text-sm font-bold text-gray-900 group-hover:text-purple-600 transition-colors duration-300">Collected Fees</span>
            </div>
            <div class="relative z-10">
                <div class="text-3xl font-black text-gray-900">₹<span id="stat-collected">0</span></div>
                <div class="text-[11px] text-gray-500 font-medium mt-1">Total collected amount</div>
            </div>
            <i class="fa-solid fa-coins absolute -right-2 -bottom-4 text-[90px] text-gray-50 opacity-80 group-hover:text-purple-50 group-hover:scale-110 transition-transform duration-500"></i>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
        <!-- Revenue Overview -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm xl:col-span-2">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-lg font-bold text-gray-900">Revenue Overview</h2>
                <select class="bg-white border border-gray-200 text-gray-600 text-xs rounded-lg px-3 py-1.5 focus:ring-1 focus:ring-indigo-600 outline-none font-medium shadow-sm">
                    <option>This Month</option>
                    <option>Last Month</option>
                </select>
            </div>
            
            <div class="flex flex-col md:flex-row gap-8">
                <!-- Line Chart Section -->
                <div class="flex-1">
                    <div class="mb-4">
                        <div class="text-xs text-gray-500 font-medium mb-1">Total Revenue</div>
                        <div class="text-2xl font-bold text-gray-900">₹ <span id="chart-total-rev">0</span></div>
                    </div>
                    <div class="h-[200px] w-full">
                        <canvas id="revenueLineChart"></canvas>
                    </div>
                </div>
                
                <!-- Collection Rate Section (Doughnut) -->
                <div class="w-full md:w-64 flex flex-col justify-center border-t md:border-t-0 md:border-l border-gray-100 pt-6 md:pt-0 md:pl-8">
                    <div class="relative h-36 w-full mb-6 flex justify-center items-center">
                        <div class="relative w-36 h-36">
                            <canvas id="collectionRateChart"></canvas>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-xl font-bold text-gray-900"><span id="collection-rate-val">0</span>%</span>
                                <span class="text-[9px] text-gray-400 font-bold uppercase tracking-wider mt-0.5 text-center px-2">Collection Rate</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-4 px-2">
                        <div class="flex justify-between items-center text-xs">
                            <div class="flex items-center gap-2 text-gray-500 font-medium">
                                <span class="w-2 h-2 rounded-full bg-indigo-600"></span> Collected
                            </div>
                            <span class="font-bold text-gray-900">₹<span id="rate-collected">0</span></span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <div class="flex items-center gap-2 text-gray-500 font-medium">
                                <span class="w-2 h-2 rounded-full bg-gray-200"></span> Pending
                            </div>
                            <span class="font-bold text-gray-900">₹<span id="rate-pending">0</span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Attendance Col -->
        <div class="flex flex-col gap-6 h-full">
            <!-- Today's Attendance -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex flex-col justify-center h-full">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-base font-bold text-gray-900">Today's Attendance</h2>
                    <a href="/attendance" class="text-xs font-bold text-indigo-600 hover:underline">View All</a>
                </div>
                <div class="flex flex-col sm:flex-row items-center gap-8">
                    <div class="relative w-36 h-36 shrink-0">
                        <canvas id="attendanceChart"></canvas>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <i class="fa-solid fa-users text-indigo-600 text-lg mb-1"></i>
                            <span class="text-xl font-bold text-gray-900"><span id="attendance-rate-center">0</span>%</span>
                            <span class="text-[9px] text-gray-400 font-bold uppercase tracking-wider mt-0.5">Present</span>
                        </div>
                    </div>
                    <div class="flex-1 space-y-5 w-full">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 text-xs font-medium text-gray-500"><span class="w-2 h-2 rounded-full bg-indigo-600"></span> Present</div>
                            <div class="text-sm font-bold text-gray-900" id="attendance-present">0</div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 text-xs font-medium text-gray-500"><span class="w-2 h-2 rounded-full bg-orange-500"></span> Absent</div>
                            <div class="text-sm font-bold text-gray-900" id="attendance-absent">0</div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 text-xs font-medium text-gray-500"><span class="w-2 h-2 rounded-full bg-gray-400"></span> Total Members</div>
                            <div class="text-sm font-bold text-gray-900" id="attendance-total">0</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lists Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Recent Members (Last 5 Days) -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600">
                        <i class="fa-solid fa-user-clock text-sm"></i>
                    </div>
                    <h2 class="text-base font-bold text-gray-900">Recently Added (Last 5 Days)</h2>
                </div>
                <a href="/members" class="text-xs font-bold text-indigo-600 hover:underline">View All</a>
            </div>
            <div class="p-0 overflow-y-auto max-h-[300px]">
                <ul id="recent-members-list" class="divide-y divide-gray-50">
                    <li class="p-6 text-center text-sm text-gray-400 font-medium">Loading...</li>
                </ul>
            </div>
        </div>

        <!-- Today's Birthdays -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-pink-100 flex items-center justify-center text-pink-500">
                        <i class="fa-solid fa-cake-candles text-sm"></i>
                    </div>
                    <h2 class="text-base font-bold text-gray-900">Today's Birthdays</h2>
                </div>
            </div>
            <div class="p-0 overflow-y-auto max-h-[300px]">
                <ul id="todays-birthdays-list" class="divide-y divide-gray-50">
                    <li class="p-6 text-center text-sm text-gray-400 font-medium">Loading...</li>
                </ul>
            </div>
        </div>
    </div>

</div>

@push('page-scripts')
<script>
    if (user) {
        const firstName = user.name.split(' ')[0];
        document.getElementById('welcome-name').textContent = firstName;
    }

    let revLineChart = null;
    let collRateChart = null;
    let attChart = null;

    // Fetch Stats API
    async function fetchStats() {
        showLoader();
        try {
            const response = await fetch('/api/dashboard/owner-stats', {
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
                const data = result.data || {};
                
                // 1. Top Stats
                const top = data.top_stats || {};
                animateValue("stat-members", 0, top.total_members || 0, 1000);
                animateValue("stat-active-members", 0, top.active_members || 0, 1000); 
                animateValue("stat-collected", 0, top.collected_fees || 0, 1000);
                animateValue("stat-pending", 0, top.pending_fees || 0, 1000);
                
                document.getElementById('chart-total-rev').textContent = (top.collected_fees || 0).toLocaleString('en-IN');
                
                // Set Collection Rate Data
                const collected = top.collected_fees || 0;
                const pending = top.pending_fees || 0;
                const totalRev = collected + pending;
                const colRate = totalRev > 0 ? Math.round((collected / totalRev) * 100) : 0;
                
                document.getElementById('rate-collected').textContent = collected.toLocaleString('en-IN');
                document.getElementById('rate-pending').textContent = pending.toLocaleString('en-IN');
                animateValue("collection-rate-val", 0, colRate, 1000);

                // 2. Charts
                const charts = data.charts || {};
                
                // Replace old revenue pie with new collection rate doughnut
                charts.collection_rate = { collected, pending };
                
                renderCharts(charts);

                if (charts.attendance) {
                    const pr = charts.attendance.present || 0;
                    const ab = charts.attendance.absent || 0;
                    document.getElementById('attendance-present').textContent = pr;
                    document.getElementById('attendance-absent').textContent = ab;
                    document.getElementById('attendance-total').textContent = pr + ab;
                    const attRate = (pr + ab) > 0 ? Math.round((pr / (pr + ab)) * 100) : 0;
                    animateValue("attendance-rate-center", 0, attRate, 1000);
                }

                // Alerts
                const alerts = data.alerts || {};
                animateValue("stat-expiring-soon", 0, alerts.expiring_soon || 0, 800);
                animateValue("stat-expired-month", 0, alerts.expired_month || 0, 800);
                animateValue("stat-due-month", 0, alerts.due_month || 0, 800);

                // Lists
                const lists = data.lists || {};
                renderRecentMembers(lists.recent_members || []);
                renderTodaysBirthdays(lists.todays_birthdays || []);

            } else {
                fallbackZeros();
            }
        } catch (error) {
            console.error(error);
            fallbackZeros();
        } finally {
            hideLoader();
        }
    }

    function fallbackZeros() {
        document.getElementById("stat-members").textContent = "0";
        document.getElementById("stat-active-members").textContent = "0";
        document.getElementById("stat-collected").textContent = "0";
        document.getElementById("stat-pending").textContent = "0";
        document.getElementById('chart-total-rev').textContent = "0";
        document.getElementById("recent-members-list").innerHTML = '<li class="p-6 text-center text-sm text-gray-400">Failed to load data</li>';
        document.getElementById("todays-birthdays-list").innerHTML = '<li class="p-6 text-center text-sm text-gray-400">Failed to load data</li>';
    }

    fetchStats();

    function renderRecentMembers(members) {
        const list = document.getElementById("recent-members-list");
        if (!members || members.length === 0) {
            list.innerHTML = `
                <li class="p-8 text-center flex flex-col items-center">
                    <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center text-gray-300 mb-3"><i class="fa-solid fa-users-slash text-xl"></i></div>
                    <div class="text-sm font-medium text-gray-500">No members registered in the last 5 days.</div>
                </li>
            `;
            return;
        }

        let html = '';
        members.forEach(m => {
            const dateStr = new Date(m.created_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
            html += `
                <li class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">
                            ${(m.user && m.user.name) ? m.user.name.charAt(0).toUpperCase() : '?'}
                        </div>
                        <div>
                            <div class="text-sm font-bold text-gray-900">${(m.user && m.user.name) ? m.user.name : 'Unknown'}</div>
                            <div class="text-[11px] text-gray-500 font-medium">Joined: ${dateStr}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-green-50 text-green-600 border border-green-100">
                            New
                        </span>
                    </div>
                </li>
            `;
        });
        list.innerHTML = html;
    }

    function renderTodaysBirthdays(users) {
        const list = document.getElementById("todays-birthdays-list");
        if (!users || users.length === 0) {
            list.innerHTML = `
                <li class="p-8 text-center flex flex-col items-center">
                    <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center text-gray-300 mb-3"><i class="fa-solid fa-calendar-xmark text-xl"></i></div>
                    <div class="text-sm font-medium text-gray-500">No birthdays today!</div>
                </li>
            `;
            return;
        }

        let html = '';
        users.forEach(u => {
            html += `
                <li class="px-6 py-4 flex items-center justify-between hover:bg-pink-50/30 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-pink-100 text-pink-500 flex items-center justify-center font-bold text-sm shadow-sm">
                            <i class="fa-solid fa-gift"></i>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-gray-900">${u.name}</div>
                            <div class="text-[11px] text-gray-500 font-medium flex items-center gap-1">
                                <i class="fa-solid fa-phone text-[9px]"></i> ${u.mobile}
                            </div>
                        </div>
                    </div>
                    <div>
                        <button class="w-8 h-8 rounded-full bg-green-50 text-green-500 flex items-center justify-center hover:bg-green-100 transition-colors" title="Send WhatsApp Greeting">
                            <i class="fa-brands fa-whatsapp text-lg"></i>
                        </button>
                    </div>
                </li>
            `;
        });
        list.innerHTML = html;
    }

    // Counter Animation
    function animateValue(id, start, end, duration) {
        const obj = document.getElementById(id);
        if (!obj) return;
        
        if (start === end) {
            obj.textContent = end.toLocaleString('en-IN');
            return;
        }
        let current = start;
        const range = end - start;
        const increment = end > start ? Math.ceil(range / (duration / 50)) : -1;
        if(increment === 0) { obj.textContent = end.toLocaleString('en-IN'); return; }
        
        const timer = setInterval(function() {
            current += increment;
            if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                current = end;
                clearInterval(timer);
            }
            obj.textContent = current.toLocaleString('en-IN');
        }, 50);
    }

    // Initialize Charts with Data
    function renderCharts(chartsData) {
        // Line Chart (Revenue) - Indigo Gradient
        const ctxRev = document.getElementById('revenueLineChart');
        if(ctxRev) {
            if (revLineChart) revLineChart.destroy();
            const gradient = ctxRev.getContext('2d').createLinearGradient(0, 0, 0, 200);
            gradient.addColorStop(0, 'rgba(79, 70, 229, 0.3)'); // Indigo fade
            gradient.addColorStop(1, 'rgba(79, 70, 229, 0)');

            const labels = chartsData.revenue_line ? chartsData.revenue_line.labels : [];
            const dataPts = chartsData.revenue_line ? chartsData.revenue_line.data : [];
            const maxVal = dataPts.length ? Math.max(...dataPts) * 1.2 : 10000;

            revLineChart = new Chart(ctxRev, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        data: dataPts,
                        borderColor: '#4f46e5', // tailwind indigo-600
                        backgroundColor: gradient,
                        borderWidth: 2,
                        pointBackgroundColor: '#4f46e5',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: {
                        callbacks: { label: function(c) { return '₹ ' + c.parsed.y.toLocaleString('en-IN'); } }
                    } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: maxVal === 0 ? 1000 : maxVal,
                            ticks: { 
                                color: '#9ca3af', font: {size: 10, weight: '500'},
                                callback: val => (val/1000) + 'K'
                            },
                            border: { display: false },
                            grid: { color: '#f3f4f6' }
                        },
                        x: {
                            ticks: { color: '#9ca3af', font: {size: 10, weight: '500'} },
                            border: { display: false },
                            grid: { display: false }
                        }
                    },
                    interaction: { intersect: false, mode: 'index' },
                }
            });
        }

        // Collection Rate (Doughnut)
        const ctxRate = document.getElementById('collectionRateChart');
        if(ctxRate) {
            if (collRateChart) collRateChart.destroy();
            
            let rateData = [0, 1];
            if (chartsData.collection_rate) {
                const c = chartsData.collection_rate.collected || 0;
                const p = chartsData.collection_rate.pending || 0;
                if (c > 0 || p > 0) rateData = [c, p];
            }

            collRateChart = new Chart(ctxRate, {
                type: 'doughnut',
                data: {
                    labels: ['Collected', 'Pending'],
                    datasets: [{
                        data: rateData,
                        backgroundColor: ['#4f46e5', '#e5e7eb'], // indigo-600, gray-200
                        borderWidth: 0,
                        cutout: '80%',
                        borderRadius: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: {enabled: false} }
                }
            });
        }

        // Doughnut Chart (Attendance)
        const ctxAtt = document.getElementById('attendanceChart');
        if(ctxAtt) {
            if (attChart) attChart.destroy();
            
            let attData = [0, 1];
            if (chartsData.attendance) {
                const pr = chartsData.attendance.present || 0;
                const ab = chartsData.attendance.absent || 0;
                if (pr > 0 || ab > 0) attData = [pr, ab];
            }

            attChart = new Chart(ctxAtt, {
                type: 'doughnut',
                data: {
                    labels: ['Present', 'Absent'],
                    datasets: [{
                        data: attData,
                        backgroundColor: ['#4f46e5', '#f97316'], // indigo-600, orange-500
                        borderWidth: 0,
                        cutout: '80%',
                        borderRadius: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false }
                    }
                }
            });
        }
    }
</script>
@endpush
@endsection
