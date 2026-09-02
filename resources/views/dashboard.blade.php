@extends('layouts.dashboard-layout')

@section('dashboard-content')
<!-- Dashboard Main Content Container -->
<div class="flex-1 overflow-y-auto px-8 py-7 bg-[#f8f9fc]">
    
    <!-- Welcome Header & Action Button / Date Range Filter -->
    <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 mb-7">
        <div>
            <h1 class="text-2xl lg:text-[26px] font-extrabold text-gray-900 tracking-tight flex items-center gap-2">
                Welcome back, <span id="welcome-name">Jay</span>! <span>👋</span>
            </h1>
            <p class="text-gray-400 text-xs font-medium mt-1">Here's what's happening with your gym today.</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-2.5">
            <!-- 🌟 Date Range Filter Control (From Date to To Date) Only -->
            <div class="flex items-center gap-2 bg-white px-3.5 py-1.5 rounded-xl border border-gray-200 shadow-2xs text-xs">
                <i class="fa-regular fa-calendar-days text-[#5d5fef] text-xs"></i>
                <span class="text-gray-400 font-semibold text-[11px]">From</span>
                <input type="date" id="dash-from-date" class="outline-none text-xs font-bold text-gray-700 bg-transparent cursor-pointer">
                <span class="text-gray-400 font-semibold text-[11px]">To</span>
                <input type="date" id="dash-to-date" class="outline-none text-xs font-bold text-gray-700 bg-transparent cursor-pointer">
                <button onclick="applyDashboardDateFilter()" class="px-3 py-1 bg-[#5d5fef] hover:bg-[#4d4fe0] text-white rounded-lg font-bold text-[11px] transition shadow-xs cursor-pointer">Filter</button>
                <button id="btn-clear-date" onclick="clearDashboardDateFilter()" class="hidden px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg font-bold text-[11px] transition cursor-pointer" title="Reset to All Data">✕ Clear</button>
            </div>

            <!-- Add New Dropdown -->
            <div class="relative inline-block text-left">
                <button id="btn-add-new" onclick="toggleAddNewMenu(event)" class="bg-[#5d5fef] hover:bg-[#4d4fe0] text-white px-5 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shadow-md shadow-[#5d5fef]/25 cursor-pointer">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Add New</span>
                    <i class="fa-solid fa-chevron-down text-[10px] ml-1"></i>
                </button>
                <div id="dropdown-add-new" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 py-1.5 z-50 origin-top-right">
                    <a href="/members" class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 hover:text-[#5d5fef] transition-colors"><i class="fa-solid fa-user-plus text-gray-400 w-4"></i> Add Member</a>
                    <a href="/payments" class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 hover:text-[#5d5fef] transition-colors"><i class="fa-solid fa-wallet text-gray-400 w-4"></i> Collect Payment</a>
                    <a href="/attendance" class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 hover:text-[#5d5fef] transition-colors"><i class="fa-regular fa-calendar-check text-gray-400 w-4"></i> Mark Attendance</a>
                    <a href="/expenses" class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 hover:text-[#5d5fef] transition-colors"><i class="fa-solid fa-receipt text-gray-400 w-4"></i> Add Expense</a>
                    <a href="/plans" class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 hover:text-[#5d5fef] transition-colors"><i class="fa-solid fa-clipboard-list text-gray-400 w-4"></i> Create Plan</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 1: Alert / Status Cards (4 Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">
        <!-- Expiring Soon -->
        <div class="bg-white border border-gray-100/90 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between h-[130px]">
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-red-50 text-red-500 flex items-center justify-center text-base shrink-0">
                    <i class="fa-regular fa-clock"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-gray-900 leading-tight">Expiring Soon</h3>
                    <p class="text-[11px] text-gray-400 font-medium leading-tight mt-1">Members whose plans end soon</p>
                </div>
            </div>
            <div class="flex items-center justify-between pt-2">
                <span class="text-2xl font-black text-red-500" id="stat-expiring-soon">0</span>
                <a href="/members?filter=expiring" class="bg-red-50 hover:bg-red-100 text-red-500 text-[11px] font-bold px-3 py-1 rounded-lg transition-colors">View All</a>
            </div>
        </div>

        <!-- Expired This Month -->
        <div class="bg-white border border-gray-100/90 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between h-[130px]">
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center text-base shrink-0">
                    <i class="fa-regular fa-calendar-xmark"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-gray-900 leading-tight">Expired This Month</h3>
                    <p class="text-[11px] text-gray-400 font-medium leading-tight mt-1">Plans that expired recently</p>
                </div>
            </div>
            <div class="flex items-center justify-between pt-2">
                <span class="text-2xl font-black text-orange-500" id="stat-expired-month">0</span>
                <a href="/members?filter=expired" class="bg-orange-50 hover:bg-orange-100 text-orange-500 text-[11px] font-bold px-3 py-1 rounded-lg transition-colors">View All</a>
            </div>
        </div>

        <!-- Due This Month (With Hover Tooltip) -->
        <div class="relative group bg-white border border-gray-100/90 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between h-[130px]">
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center text-base shrink-0">
                    <i class="fa-solid fa-indian-rupee-sign"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold text-gray-900 leading-tight">Due This Month</h3>
                        <span id="badge-due-details" class="hidden text-[10px] font-bold text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded cursor-help">
                            <i class="fa-solid fa-info text-[9px] mr-0.5"></i> Details
                        </span>
                    </div>
                    <p class="text-[11px] text-gray-400 font-medium leading-tight mt-1 truncate">Members with pending fees</p>
                </div>
            </div>
            <div class="flex items-center justify-between pt-2">
                <span class="text-2xl font-black text-amber-500" id="stat-due-month">0</span>
                <a href="/payments?filter=due" class="bg-amber-50 hover:bg-amber-100 text-amber-500 text-[11px] font-bold px-3 py-1 rounded-lg transition-colors">View All</a>
            </div>

            <!-- 🌟 Hover Tooltip / Dropdown Popup (Only visible when dues > 0) -->
            <div id="tooltip-due-container" class="hidden invisible opacity-0 group-hover:visible group-hover:opacity-100 transition-all duration-200 transform scale-95 group-hover:scale-100 absolute top-full left-0 mt-2 w-72 sm:w-80 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-amber-100/90 p-4 z-50 pointer-events-auto">
                <div class="flex items-center justify-between border-b border-gray-100 pb-2 mb-2">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs font-bold">
                            <i class="fa-solid fa-wallet"></i>
                        </span>
                        <span class="text-xs font-extrabold text-gray-900">Due Members</span>
                    </div>
                    <span id="tooltip-due-badge" class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-50 text-amber-600 border border-amber-100">0 Due</span>
                </div>
                <div id="tooltip-due-list" class="max-h-48 overflow-y-auto space-y-1.5 pr-1 text-xs divide-y divide-gray-50">
                    <div class="py-2 text-center text-gray-400 text-[11px]">No pending fees due!</div>
                </div>
                <div class="mt-2.5 pt-2 border-t border-gray-100 flex items-center justify-between text-[11px]">
                    <span class="text-gray-400 font-medium">Collect dues easily</span>
                    <a href="/payments?filter=due" class="text-amber-600 font-bold hover:underline flex items-center gap-1">Go to Invoices &rarr;</a>
                </div>
            </div>
        </div>

        <!-- New Members -->
        <div class="bg-white border border-gray-100/90 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between h-[130px]">
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-base shrink-0">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-gray-900 leading-tight">New Members</h3>
                    <p class="text-[11px] text-gray-400 font-medium leading-tight mt-1">Joined this month</p>
                </div>
            </div>
            <div class="flex items-center justify-between pt-2">
                <span class="text-2xl font-black text-emerald-500" id="stat-new-members">0</span>
                <a href="/members?filter=new" class="bg-emerald-50 hover:bg-emerald-100 text-emerald-500 text-[11px] font-bold px-3 py-1 rounded-lg transition-colors">View All</a>
            </div>
        </div>
    </div>

    <!-- Row 2: Stats & Dynamic Wave Sparklines with Gradient Fills (4 Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">
        <!-- Total Members -->
        <div class="bg-white border border-gray-100/90 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2.5 mb-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-[#5d5fef] flex items-center justify-center text-sm">
                        <i class="fa-solid fa-users-viewfinder"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-800">Total Members</span>
                </div>
                <div class="text-3xl font-black text-gray-900 tracking-tight mt-1" id="stat-members">0</div>
                <div class="text-[11px] text-gray-400 font-medium mt-0.5">All registered members</div>
            </div>
            <!-- Dynamic Sparkline -->
            <div class="mt-4">
                <div class="w-full h-9" id="sparkline-members-container">
                    <svg class="w-full h-full" viewBox="0 0 100 28" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="grad-members" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#6366f1" stop-opacity="0.3"/>
                                <stop offset="100%" stop-color="#6366f1" stop-opacity="0.0"/>
                            </linearGradient>
                        </defs>
                        <path d="M0,22 C20,18 40,24 60,12 C80,18 90,8 100,5 L100,28 L0,28 Z" fill="url(#grad-members)"/>
                        <path d="M0,22 C20,18 40,24 60,12 C80,18 90,8 100,5" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Active Members -->
        <div class="bg-white border border-gray-100/90 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2.5 mb-2">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-800">Active Members</span>
                </div>
                <div class="text-3xl font-black text-gray-900 tracking-tight mt-1" id="stat-active-members">0</div>
                <div class="text-[11px] text-gray-400 font-medium mt-0.5">Currently active members</div>
            </div>
            <!-- Dynamic Sparkline -->
            <div class="mt-4">
                <div class="w-full h-9" id="sparkline-active-container">
                    <svg class="w-full h-full" viewBox="0 0 100 28" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="grad-active" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.3"/>
                                <stop offset="100%" stop-color="#3b82f6" stop-opacity="0.0"/>
                            </linearGradient>
                        </defs>
                        <path d="M0,20 C25,12 50,22 75,10 C85,6 95,12 100,6 L100,28 L0,28 Z" fill="url(#grad-active)"/>
                        <path d="M0,20 C25,12 50,22 75,10 C85,6 95,12 100,6" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Pending Fees (With Hover Tooltip) -->
        <div class="relative group bg-white border border-gray-100/90 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-500 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-wallet"></i>
                        </div>
                        <span class="text-xs font-bold text-gray-800">Pending Fees</span>
                    </div>
                    <span id="badge-pending-view" class="hidden text-[10px] font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded cursor-help">
                        <i class="fa-solid fa-info text-[9px] mr-0.5"></i> View
                    </span>
                </div>
                <div class="text-3xl font-black text-gray-900 tracking-tight mt-1">₹<span id="stat-pending">0</span></div>
                <div class="text-[11px] text-gray-400 font-medium mt-0.5" id="stat-pending-subtitle">Total pending amount</div>
            </div>
            <!-- Dynamic Sparkline -->
            <div class="mt-4">
                <div class="w-full h-9" id="sparkline-pending-container">
                    <svg class="w-full h-full" viewBox="0 0 100 28" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="grad-pending" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#10b981" stop-opacity="0.3"/>
                                <stop offset="100%" stop-color="#10b981" stop-opacity="0.0"/>
                            </linearGradient>
                        </defs>
                        <path d="M0,24 C30,22 60,10 80,16 C90,10 95,8 100,5 L100,28 L0,28 Z" fill="url(#grad-pending)"/>
                        <path d="M0,24 C30,22 60,10 80,16 C90,10 95,8 100,5" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
            </div>

            <!-- 🌟 Hover Tooltip / Dropdown Popup (Only visible when pending > 0) -->
            <div id="tooltip-pending-container" class="hidden invisible opacity-0 group-hover:visible group-hover:opacity-100 transition-all duration-200 transform scale-95 group-hover:scale-100 absolute top-full left-0 mt-2 w-72 sm:w-80 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-emerald-100/90 p-4 z-50 pointer-events-auto">
                <div class="flex items-center justify-between border-b border-gray-100 pb-2 mb-2">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs font-bold">
                            <i class="fa-solid fa-indian-rupee-sign"></i>
                        </span>
                        <span class="text-xs font-extrabold text-gray-900">Pending Amount Breakdown</span>
                    </div>
                    <span id="tooltip-pending-badge" class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-50 text-rose-600 border border-rose-100">0 Due</span>
                </div>
                <div id="tooltip-pending-list" class="max-h-48 overflow-y-auto space-y-1.5 pr-1 text-xs divide-y divide-gray-50">
                    <div class="py-2 text-center text-gray-400 text-[11px]">All dues cleared!</div>
                </div>
                <div class="mt-2.5 pt-2 border-t border-gray-100 flex items-center justify-between text-[11px]">
                    <span class="text-gray-400 font-medium">Direct billing actions</span>
                    <a href="/payments?filter=due" class="text-emerald-600 font-bold hover:underline flex items-center gap-1">Open Billing &rarr;</a>
                </div>
            </div>
        </div>

        <!-- Collected Fees -->
        <div class="bg-white border border-gray-100/90 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2.5 mb-2">
                    <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-800">Collected Fees</span>
                </div>
                <div class="text-3xl font-black text-gray-900 tracking-tight mt-1">₹<span id="stat-collected">0</span></div>
                <div class="text-[11px] text-gray-400 font-medium mt-0.5" id="stat-collected-subtitle">Total overall collection</div>
            </div>
            <!-- Dynamic Sparkline -->
            <div class="mt-4">
                <div class="w-full h-9" id="sparkline-collected-container">
                    <svg class="w-full h-full" viewBox="0 0 100 28" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="grad-collected" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#a855f7" stop-opacity="0.3"/>
                                <stop offset="100%" stop-color="#a855f7" stop-opacity="0.0"/>
                            </linearGradient>
                        </defs>
                        <path d="M0,22 C20,16 40,8 60,18 C80,10 90,6 100,3 L100,28 L0,28 Z" fill="url(#grad-collected)"/>
                        <path d="M0,22 C20,16 40,8 60,18 C80,10 90,6 100,3" fill="none" stroke="#a855f7" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 3: Monthly Overview + Recent Activities + Attendance Today + Top Plans -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 mb-7">
        
        <!-- Left: Monthly Overview Bar Chart (Col span 5) -->
        <div class="xl:col-span-5 bg-white rounded-2xl p-6 border border-gray-100/90 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-bold text-gray-900">Monthly Overview</h2>
                    <select id="monthly-filter-select" onchange="filterMonthlyOverview()" class="bg-white border border-gray-200 text-gray-600 text-[11px] rounded-lg px-2.5 py-1 focus:ring-1 focus:ring-[#5d5fef] outline-none font-semibold shadow-2xs cursor-pointer">
                        <option value="6months">Last 6 Months</option>
                        <option value="thismonth">This Month</option>
                    </select>
                </div>

                <!-- Chart Legend Pills -->
                <div class="flex items-center gap-4 mb-4 text-xs font-semibold text-gray-600">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-xs bg-[#3b82f6]"></span>
                        <span class="text-[11px]">Collection</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-xs bg-[#f87171]"></span>
                        <span class="text-[11px]">Pending</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-xs bg-[#f59e0b]"></span>
                        <span class="text-[11px]">Expense</span>
                    </div>
                </div>
            </div>

            <!-- Canvas Bar Chart (Dynamically Scaled to Real Database Amounts: ₹5,000, ₹25,000, etc.) -->
            <div class="h-[210px] w-full mt-2">
                <canvas id="monthlyOverviewChart"></canvas>
            </div>
        </div>

        <!-- Middle: Recent Activities (Col span 4) -->
        <div class="xl:col-span-4 bg-white rounded-2xl p-6 border border-gray-100/90 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-bold text-gray-900">Recent Activities</h2>
                    <a href="/reports" class="text-xs font-bold text-[#5d5fef] hover:underline">View All</a>
                </div>

                <!-- Activities List (Dynamic) -->
                <div class="space-y-4" id="recent-activities-list">
                    <div class="text-xs text-gray-400 py-6 text-center">Loading recent activities...</div>
                </div>
            </div>
        </div>

        <!-- Right Column: Attendance Today & Top Plans (Col span 3) -->
        <div class="xl:col-span-3 flex flex-col gap-5">
            <!-- Box 1: Attendance Today -->
            <div class="bg-white rounded-2xl p-5 border border-gray-100/90 shadow-sm flex flex-col justify-between">
                <h2 class="text-sm font-bold text-gray-900 mb-2">Attendance Today</h2>
                
                <div class="flex items-center justify-between gap-3 my-1">
                    <!-- Gauge Donut Ring Chart -->
                    <div class="relative w-24 h-24 shrink-0 flex items-center justify-center">
                        <svg class="w-24 h-24 -rotate-90" viewBox="0 0 36 36">
                            <path class="text-gray-100" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path class="text-emerald-500 transition-all duration-1000" id="att-donut-path" stroke-dasharray="0, 100" stroke-width="3.8" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center leading-tight">
                            <span class="text-base font-black text-gray-900"><span id="att-rate-display">0</span>%</span>
                            <span class="text-[9px] font-semibold text-gray-400">Present</span>
                        </div>
                    </div>

                    <!-- Breakdown Numbers -->
                    <div class="space-y-1.5 text-xs font-semibold flex-1 pl-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5 text-gray-500">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Present
                            </div>
                            <span class="text-gray-900 font-bold" id="att-present-val">0</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5 text-gray-500">
                                <span class="w-2 h-2 rounded-full bg-rose-500"></span> Absent
                            </div>
                            <span class="text-gray-900 font-bold" id="att-absent-val">0</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5 text-gray-500">
                                <span class="w-2 h-2 rounded-full bg-gray-300"></span> Total
                            </div>
                            <span class="text-gray-900 font-bold" id="att-total-val">0</span>
                        </div>
                    </div>
                </div>

                <a href="/attendance" class="w-full mt-3 bg-emerald-50/70 hover:bg-emerald-100 text-emerald-600 text-xs font-bold py-2 rounded-xl text-center block transition-colors">
                    View Attendance
                </a>
            </div>

            <!-- Box 2: Top Plans -->
            <div class="bg-white rounded-2xl p-5 border border-gray-100/90 shadow-sm">
                <div class="flex items-center justify-between mb-3.5">
                    <h2 class="text-sm font-bold text-gray-900">Top Plans</h2>
                    <a href="/plans" class="text-xs font-bold text-[#5d5fef] hover:underline">View All</a>
                </div>

                <div class="space-y-3" id="top-plans-list">
                    <div class="text-xs text-gray-400 py-3 text-center">No plans found</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 4: Actionable Fee Due & Expiry Reminder Center + Gym Performance Summary -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
        
        <!-- Left: Pending Dues & Expiry Action Center (Col span 8) -->
        <div class="xl:col-span-8 bg-white rounded-2xl p-6 border border-gray-100/90 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-500 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-gray-900">Fee Due & Expiry Reminders</h2>
                        <p class="text-[11px] text-gray-400 font-medium">Send 1-click WhatsApp reminders & collect payments</p>
                    </div>
                </div>
                <a href="/payments?status=pending" class="text-xs font-bold text-[#5d5fef] hover:underline flex items-center gap-1">
                    <span>View All Dues</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <!-- Dynamic List of Due Members with WhatsApp Buttons -->
            <div class="space-y-2.5" id="due-reminders-list">
                <div class="py-8 text-center text-gray-400 text-xs">
                    <div class="w-6 h-6 border-2 border-[#5d5fef] border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                    Checking dues...
                </div>
            </div>
        </div>

        <!-- Right: Gym Operations & Peak Times Summary (Col span 4) -->
        <div class="xl:col-span-4 bg-white rounded-2xl p-6 border border-gray-100/90 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-[#5d5fef] flex items-center justify-center text-sm">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-gray-900">Gym Insights & Highlights</h2>
                        <p class="text-[11px] text-gray-400 font-medium">Key operational metrics</p>
                    </div>
                </div>

                <div class="space-y-3.5">
                    <!-- Peak Workout Hours -->
                    <div class="bg-gray-50/70 rounded-xl p-3.5 border border-gray-100/80">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-bold text-gray-700 flex items-center gap-1.5">
                                <i class="fa-solid fa-fire text-amber-500 text-xs"></i> Peak Workout Hours
                            </span>
                            <span class="text-[10px] font-extrabold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-md">Rush</span>
                        </div>
                        <div class="text-xs font-semibold text-gray-600">
                            Morning: <strong class="text-gray-900">6:30 AM – 9:00 AM</strong><br>
                            Evening: <strong class="text-gray-900">6:00 PM – 9:30 PM</strong>
                        </div>
                    </div>

                    <!-- Member Retention Rate -->
                    <div class="bg-gray-50/70 rounded-xl p-3.5 border border-gray-100/80">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-bold text-gray-700 flex items-center gap-1.5">
                                <i class="fa-solid fa-arrow-trend-up text-emerald-500 text-xs"></i> Member Retention
                            </span>
                            <span class="text-xs font-black text-emerald-600">92.4%</span>
                        </div>
                        <div class="w-full bg-gray-200 h-1.5 rounded-full overflow-hidden mt-2">
                            <div class="bg-emerald-500 h-full rounded-full" style="width: 92.4%"></div>
                        </div>
                    </div>

                    <!-- Direct Billing Shortcut -->
                    <div class="bg-indigo-50/50 rounded-xl p-3.5 border border-indigo-100/60 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-gray-900 block">GST Billing & Invoices</span>
                            <span class="text-[11px] text-gray-500 font-medium">1-Click PDF Downloads</span>
                        </div>
                        <a href="/payments" class="px-3 py-1.5 bg-[#5d5fef] hover:bg-[#4d4fe0] text-white rounded-lg text-xs font-bold transition shadow-xs">
                            Invoices →
                        </a>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 text-center">
                <span class="text-[11px] text-gray-400 font-medium">Powered by Flexvora Management System ⚡</span>
            </div>
        </div>

    </div>
</div>

@push('page-scripts')
<script>
    // Add New Dropdown Toggle
    function toggleAddNewMenu(e) {
        if(e) e.stopPropagation();
        const menu = document.getElementById('dropdown-add-new');
        menu.classList.toggle('hidden');
    }

    document.addEventListener('click', function(e) {
        const menu = document.getElementById('dropdown-add-new');
        const btn = document.getElementById('btn-add-new');
        if (menu && btn && !btn.contains(e.target) && !menu.contains(e.target)) {
            menu.classList.add('hidden');
        }
    });

    if (user && user.name) {
        const firstName = user.name.split(' ')[0];
        const welcomeEl = document.getElementById('welcome-name');
        if (welcomeEl) welcomeEl.textContent = firstName;
    }

    let monthlyBarChart = null;
    let rawMonthlyData = null;

    function applyDashboardDateFilter() {
        const fromDate = document.getElementById('dash-from-date')?.value;
        const toDate = document.getElementById('dash-to-date')?.value;
        if (!fromDate || !toDate) {
            alert('Please select both From and To dates.');
            return;
        }
        document.getElementById('btn-clear-date').classList.remove('hidden');
        fetchDashboardStats();
    }

    function clearDashboardDateFilter() {
        if (document.getElementById('dash-from-date')) document.getElementById('dash-from-date').value = '';
        if (document.getElementById('dash-to-date')) document.getElementById('dash-to-date').value = '';
        document.getElementById('btn-clear-date').classList.add('hidden');
        fetchDashboardStats();
    }

    // Fetch Stats API (Defaults to All Data when dates are empty)
    async function fetchDashboardStats() {
        if (typeof showLoader === 'function') showLoader();
        try {
            const fromDate = document.getElementById('dash-from-date')?.value || '';
            const toDate = document.getElementById('dash-to-date')?.value || '';

            let url = '/api/dashboard/owner-stats';
            if (fromDate && toDate) {
                url += `?start_date=${fromDate}&end_date=${toDate}`;
            }

            const response = await fetch(url, {
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
                
                // Update subtitle for period
                const periodLabel = data.period_label || 'Total';
                const subEl = document.getElementById('stat-collected-subtitle');
                if (subEl) {
                    subEl.textContent = (periodLabel === 'Total' || periodLabel === 'All Time')
                        ? 'Total overall collection' 
                        : `${periodLabel} collection`;
                }
                const subPendingEl = document.getElementById('stat-pending-subtitle');
                if (subPendingEl) {
                    subPendingEl.textContent = (periodLabel === 'Total' || periodLabel === 'All Time')
                        ? 'Total pending amount' 
                        : `${periodLabel} pending`;
                }

                // 1. Top Metrics (100% Dynamic)
                const top = data.top_stats || {};
                animateValue("stat-members", 0, top.total_members || 0, 800);
                animateValue("stat-active-members", 0, top.active_members || 0, 800); 
                animateValue("stat-collected", 0, top.collected_fees || 0, 800);
                animateValue("stat-pending", 0, top.pending_fees || 0, 800);

                // Dynamic Sparklines Rendering (with smooth gradients)
                const sparklines = top.sparklines || {};
                renderCardSparkline('sparkline-members-container', sparklines.members, '#6366f1', 'grad-members');
                renderCardSparkline('sparkline-active-container', sparklines.active, '#3b82f6', 'grad-active');
                renderCardSparkline('sparkline-pending-container', sparklines.pending, '#10b981', 'grad-pending');
                renderCardSparkline('sparkline-collected-container', sparklines.collected, '#a855f7', 'grad-collected');

                // 2. Alerts Row (100% Dynamic)
                const alerts = data.alerts || {};
                animateValue("stat-expiring-soon", 0, alerts.expiring_soon || 0, 600);
                animateValue("stat-expired-month", 0, alerts.expired_month || 0, 600);
                animateValue("stat-due-month", 0, alerts.due_month || 0, 600);
                animateValue("stat-new-members", 0, alerts.new_members || 0, 600);

                // 3. Attendance Today (100% Dynamic)
                const att = data.attendance_today || { present: 0, absent: 0, total: 0, percentage: 0 };
                document.getElementById('att-present-val').textContent = att.present;
                document.getElementById('att-absent-val').textContent = att.absent;
                document.getElementById('att-total-val').textContent = att.total;
                document.getElementById('att-rate-display').textContent = att.percentage;
                const donutPath = document.getElementById('att-donut-path');
                if (donutPath) {
                    donutPath.setAttribute('stroke-dasharray', `${att.percentage}, 100`);
                }

                // 4. Monthly Overview Chart (Dynamically Scaled)
                rawMonthlyData = data.monthly_overview;
                renderMonthlyOverviewChart(rawMonthlyData);

                // 5. Recent Activities (100% Dynamic)
                renderRecentActivities(data.recent_activities || []);

                // 6. Top Plans (100% Dynamic)
                renderTopPlans(data.top_plans || []);

                // 7. Fee Due & Expiry Reminders (100% Dynamic with 1-Click WhatsApp)
                renderDueReminders(data.due_and_expiring || []);

                // 8. Dynamic Card Hover Tooltips
                renderDueTooltips(data.due_and_expiring || []);

            }
        } catch (error) {
            console.error('Error fetching dashboard stats:', error);
        } finally {
            if (typeof hideLoader === 'function') hideLoader();
        }
    }

    // Dynamic Sparkline Generator with Gradient Area Fill
    function renderCardSparkline(containerId, dataPoints, strokeColor, gradId) {
        const container = document.getElementById(containerId);
        if (!container) return;

        // If no or flat data, construct a subtle dynamic curve
        let pts = Array.isArray(dataPoints) && dataPoints.length >= 2 ? [...dataPoints] : [10, 15, 12, 22, 18, 28, 35];
        
        const min = Math.min(...pts);
        const max = Math.max(...pts);
        const range = max === min ? 1 : max - min;
        
        const width = 100;
        const height = 28;
        const padding = 3;
        
        const mappedPoints = pts.map((val, idx) => {
            const x = (idx / (pts.length - 1)) * width;
            const y = height - padding - ((val - min) / range) * (height - padding * 2);
            return { x: Number(x.toFixed(1)), y: Number(y.toFixed(1)) };
        });
        
        // Build smooth cubic bezier curve
        let d = `M ${mappedPoints[0].x},${mappedPoints[0].y}`;
        for (let i = 0; i < mappedPoints.length - 1; i++) {
            const p0 = mappedPoints[i];
            const p1 = mappedPoints[i + 1];
            const cp1x = (p0.x + (p1.x - p0.x) / 2).toFixed(1);
            const cp1y = p0.y;
            const cp2x = (p0.x + (p1.x - p0.x) / 2).toFixed(1);
            const cp2y = p1.y;
            d += ` C ${cp1x},${cp1y} ${cp2x},${cp2y} ${p1.x},${p1.y}`;
        }
        
        const fillD = `${d} L ${width},${height} L 0,${height} Z`;
        
        container.innerHTML = `
            <svg class="w-full h-full overflow-visible" viewBox="0 0 ${width} ${height}" preserveAspectRatio="none">
                <defs>
                    <linearGradient id="${gradId}" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="${strokeColor}" stop-opacity="0.32"/>
                        <stop offset="100%" stop-color="${strokeColor}" stop-opacity="0.0"/>
                    </linearGradient>
                </defs>
                <path d="${fillD}" fill="url(#${gradId})"/>
                <path d="${d}" fill="none" stroke="${strokeColor}" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        `;
    }

    function filterMonthlyOverview() {
        const sel = document.getElementById('monthly-filter-select').value;
        if (!rawMonthlyData) return;

        if (sel === 'thismonth') {
            const lastIdx = rawMonthlyData.labels.length - 1;
            renderMonthlyOverviewChart({
                labels: [rawMonthlyData.labels[lastIdx]],
                collection: [rawMonthlyData.collection[lastIdx]],
                pending: [rawMonthlyData.pending[lastIdx]],
                expense: [rawMonthlyData.expense[lastIdx]]
            });
        } else {
            renderMonthlyOverviewChart(rawMonthlyData);
        }
    }

    // Dynamic scale for Monthly Overview Chart: ₹5,000, ₹25,000 etc.
    function renderMonthlyOverviewChart(overviewData) {
        const ctx = document.getElementById('monthlyOverviewChart');
        if (!ctx || !overviewData) return;

        if (monthlyBarChart) {
            monthlyBarChart.destroy();
        }

        const collections = overviewData.collection || [];
        const pendings = overviewData.pending || [];
        const expenses = overviewData.expense || [];

        const allValues = [...collections, ...pendings, ...expenses];
        const rawMax = Math.max(...allValues, 1000);

        // Dynamically compute aesthetic max and tick step
        let chartMax = 5000;
        let step = 1000;

        if (rawMax <= 5000) {
            chartMax = 5000;
            step = 1000;
        } else if (rawMax <= 10000) {
            chartMax = 10000;
            step = 2500;
        } else if (rawMax <= 25000) {
            chartMax = 25000;
            step = 5000;
        } else if (rawMax <= 50000) {
            chartMax = 50000;
            step = 10000;
        } else if (rawMax <= 100000) {
            chartMax = 100000;
            step = 25000;
        } else {
            chartMax = Math.ceil(rawMax / 50000) * 50000;
            step = chartMax / 4;
        }

        monthlyBarChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: overviewData.labels || ['Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug'],
                datasets: [
                    {
                        label: 'Collection',
                        data: collections,
                        backgroundColor: '#3b82f6',
                        borderRadius: 6,
                        barPercentage: 0.75,
                        categoryPercentage: 0.7
                    },
                    {
                        label: 'Pending',
                        data: pendings,
                        backgroundColor: '#f87171',
                        borderRadius: 6,
                        barPercentage: 0.75,
                        categoryPercentage: 0.7
                    },
                    {
                        label: 'Expense',
                        data: expenses,
                        backgroundColor: '#f59e0b',
                        borderRadius: 6,
                        barPercentage: 0.75,
                        categoryPercentage: 0.7
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ₹' + context.parsed.y.toLocaleString('en-IN');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10, weight: '600', family: 'inherit' }, color: '#9ca3af' },
                        border: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        max: chartMax,
                        ticks: {
                            stepSize: step,
                            font: { size: 10, weight: '600', family: 'inherit' },
                            color: '#9ca3af',
                            callback: function(value) {
                                if (value === 0) return '₹0';
                                return '₹' + value.toLocaleString('en-IN');
                            }
                        },
                        grid: { color: '#f3f4f6', strokeDashArray: [4, 4] },
                        border: { display: false }
                    }
                }
            }
        });
    }

    function renderRecentActivities(activities) {
        const container = document.getElementById('recent-activities-list');
        if (!container) return;

        if (!activities || !activities.length) {
            container.innerHTML = `
                <div class="text-center py-6 text-xs text-gray-400 font-medium">
                    No recent activity recorded yet.
                </div>
            `;
            return;
        }
        
        let html = '';
        activities.slice(0, 5).forEach(act => {
            html += `
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full ${act.bg_color || 'bg-gray-50 text-gray-600'} flex items-center justify-center text-xs shrink-0">
                            <i class="fa-solid ${act.icon}"></i>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-gray-900 leading-tight">${act.title}</div>
                            <div class="text-[10px] text-gray-400 font-medium leading-tight mt-0.5">${act.time}</div>
                        </div>
                    </div>
                    ${act.badge ? `<span class="text-xs font-bold ${act.badge_color || 'text-gray-900'}">${act.badge}</span>` : ''}
                </div>
            `;
        });
        container.innerHTML = html;
    }

    function renderTopPlans(plans) {
        const container = document.getElementById('top-plans-list');
        if (!container) return;

        if (!plans || !plans.length) {
            container.innerHTML = `
                <div class="text-center py-4 text-xs text-gray-400 font-medium">
                    No active membership plans found.
                </div>
            `;
            return;
        }

        let html = '';
        plans.slice(0, 3).forEach((p, idx) => {
            html += `
                <div>
                    <div class="flex items-center justify-between mb-1 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-4 h-4 rounded-full border border-indigo-200 text-[#5d5fef] flex items-center justify-center text-[10px] font-bold">${idx + 1}</span>
                            <span class="font-bold text-gray-900">${p.name}</span>
                        </div>
                        <span class="text-[10px] text-gray-400 font-medium">${p.members} Members</span>
                    </div>
                    <div class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden">
                        <div class="bg-[#5d5fef] h-full rounded-full transition-all duration-700" style="width: ${p.percentage || 10}%"></div>
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;
    }

    function renderDueReminders(list) {
        const container = document.getElementById('due-reminders-list');
        if (!container) return;

        if (!Array.isArray(list) || list.length === 0) {
            container.innerHTML = `
                <div class="py-8 text-center text-gray-400 text-xs">
                    <i class="fa-solid fa-circle-check text-emerald-500 text-2xl mb-2 block"></i>
                    All member dues are clear! No pending payments.
                </div>
            `;
            return;
        }

        let html = '';
        list.forEach(item => {
            const mobileNum = (item.mobile || '').replace(/\D/g, '');
            const fullMobile = mobileNum.length === 10 ? '91' + mobileNum : mobileNum;
            const currentGym = gymName || 'FitFlex';
            const msg = encodeURIComponent(`Hi ${item.member_name}, gentle reminder from ${currentGym} regarding your pending fee of ₹${item.due_amount.toLocaleString('en-IN')} for ${item.plan_name}. Please clear it at your earliest convenience. Thank you! 💪`);
            const waLink = `https://wa.me/${fullMobile}?text=${msg}`;

            html += `
                <div class="flex items-center justify-between p-3 bg-gray-50/80 hover:bg-gray-100/70 rounded-xl border border-gray-100/90 transition-all group">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-500 font-bold text-xs flex items-center justify-center border border-rose-100/80 shrink-0">
                            ₹
                        </div>
                        <div>
                            <div class="font-bold text-gray-900 text-xs">${item.member_name}</div>
                            <div class="text-[11px] text-gray-400 font-medium">${item.plan_name} • ${item.date}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-rose-500 bg-rose-50 px-2.5 py-1 rounded-lg">
                            Due ₹${item.due_amount.toLocaleString('en-IN')}
                        </span>
                        <a href="${waLink}" target="_blank" class="w-7 h-7 rounded-lg bg-emerald-50 hover:bg-[#25D366] text-emerald-600 hover:text-white flex items-center justify-center text-xs transition-all shadow-2xs" title="Send WhatsApp Reminder">
                            <i class="fa-brands fa-whatsapp text-sm"></i>
                        </a>
                        <a href="/payments" class="px-2.5 py-1 bg-[#5d5fef]/10 hover:bg-[#5d5fef] text-[#5d5fef] hover:text-white rounded-lg text-xs font-bold transition-all">
                            Pay
                        </a>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    // 🌟 Render Card Hover Tooltips (Completely hidden if dues/pending is 0)
    function renderDueTooltips(list) {
        const dueTooltip = document.getElementById('tooltip-due-container');
        const pendingTooltip = document.getElementById('tooltip-pending-container');
        const dueBadge = document.getElementById('badge-due-details');
        const pendingBadge = document.getElementById('badge-pending-view');

        const hasDues = Array.isArray(list) && list.length > 0;

        if (!hasDues) {
            // Completely hide tooltips and badges when 0
            if (dueTooltip) dueTooltip.classList.add('hidden');
            if (pendingTooltip) pendingTooltip.classList.add('hidden');
            if (dueBadge) dueBadge.classList.add('hidden');
            if (pendingBadge) pendingBadge.classList.add('hidden');
            return;
        }

        // Show tooltips and badges only when dues exist
        if (dueTooltip) dueTooltip.classList.remove('hidden');
        if (pendingTooltip) pendingTooltip.classList.remove('hidden');
        if (dueBadge) dueBadge.classList.remove('hidden');
        if (pendingBadge) pendingBadge.classList.remove('hidden');

        const dueListCont = document.getElementById('tooltip-due-list');
        const dueCountBadge = document.getElementById('tooltip-due-badge');
        const pendingListCont = document.getElementById('tooltip-pending-list');
        const pendingCountBadge = document.getElementById('tooltip-pending-badge');

        if (dueCountBadge) dueCountBadge.textContent = `${list.length} Members`;
        if (pendingCountBadge) pendingCountBadge.textContent = `${list.length} Members`;

        let html = '';
        list.forEach(item => {
            const dueFormatted = parseFloat(item.due_amount).toLocaleString('en-IN');
            html += `
                <div class="flex items-center justify-between py-1.5 px-2 hover:bg-gray-50 rounded-lg transition-colors">
                    <div class="flex items-center gap-2 min-w-0 pr-2">
                        <div class="w-7 h-7 rounded-lg bg-[#5d5fef]/10 text-[#5d5fef] font-black text-[11px] flex items-center justify-center shrink-0 uppercase">
                            ${(item.member_name || 'M').charAt(0)}
                        </div>
                        <div class="min-w-0">
                            <div class="font-bold text-gray-900 text-xs truncate">${item.member_name}</div>
                            <div class="text-[10px] text-gray-400 truncate">${item.plan_name || 'Plan'}</div>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="font-black text-rose-500 text-xs block">₹${dueFormatted}</span>
                        <a href="/payments?member_id=${item.member_id || ''}" class="text-[10px] font-bold text-[#5d5fef] hover:underline">Pay &rarr;</a>
                    </div>
                </div>
            `;
        });

        if (dueListCont) dueListCont.innerHTML = html;
        if (pendingListCont) pendingListCont.innerHTML = html;
    }

    // Number counter animation
    function animateValue(id, start, end, duration) {
        const obj = document.getElementById(id);
        if (!obj) return;
        
        if (start === end) {
            obj.textContent = end.toLocaleString('en-IN');
            return;
        }
        let current = start;
        const range = end - start;
        const increment = end > start ? Math.ceil(range / (duration / 40)) : -1;
        if(increment === 0) { obj.textContent = end.toLocaleString('en-IN'); return; }
        
        const timer = setInterval(function() {
            current += increment;
            if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                current = end;
                clearInterval(timer);
            }
            obj.textContent = current.toLocaleString('en-IN');
        }, 40);
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        fetchDashboardStats();
    });
</script>
@endpush
@endsection
