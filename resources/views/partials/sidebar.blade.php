<!-- Sidebar -->
<aside class="w-[260px] bg-white border-r border-gray-100 flex flex-col hidden md:flex shrink-0">
    <!-- Logo -->
    <div class="h-20 flex items-center px-6">
        <div class="text-xl font-bold tracking-tight flex items-center gap-2 w-full">
            <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center text-white shrink-0 shadow-sm">
                <i class="fa-solid fa-dumbbell text-sm"></i>
            </div>
            <div class="flex-1 min-w-0 flex flex-col justify-center overflow-hidden">
                <span id="sidebar-logo-gym-name" class="text-gray-900 truncate block leading-tight">GYMORA</span>
                <span class="text-[9px] text-gray-500 font-medium tracking-wide truncate block mt-0.5">Gym Management System</span>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 py-6 space-y-1 overflow-y-auto">
        <!-- Dashboard -->
        <a href="/dashboard" class="flex items-center gap-4 pl-6 pr-4 py-3 {{ request()->is('dashboard') ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600 rounded-r-xl' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 border-l-4 border-transparent' }} font-bold transition-all mr-4">
            <i class="fa-solid fa-border-all w-5 text-lg"></i>
            <span class="text-sm">Dashboard</span>
        </a>
        <!-- Members -->
        <a href="/members" class="flex items-center gap-4 pl-6 pr-4 py-3 {{ request()->is('members*') ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600 rounded-r-xl' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 border-l-4 border-transparent' }} transition-all font-bold mr-4">
            <i class="fa-solid fa-users w-5 text-lg"></i>
            <span class="text-sm">Members</span>
        </a>
        <!-- Staff / Trainers -->
        <a href="/staff" class="flex items-center justify-between pl-6 pr-4 py-3 {{ request()->is('staff*') ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600 rounded-r-xl' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 border-l-4 border-transparent' }} transition-all font-bold mr-4">
            <div class="flex items-center gap-4">
                <i class="fa-solid fa-id-badge w-5 text-lg"></i>
                <span class="text-sm">Staff / Trainers</span>
            </div>
            <i class="fa-solid fa-chevron-right text-[10px]"></i>
        </a>
        <!-- Plans -->
        <a href="/plans" class="flex items-center gap-4 pl-6 pr-4 py-3 {{ request()->is('plans*') ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600 rounded-r-xl' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 border-l-4 border-transparent' }} transition-all font-bold mr-4">
            <i class="fa-solid fa-clipboard-list w-5 text-lg"></i>
            <span class="text-sm">Plans</span>
        </a>
        <!-- Attendance -->
        <a href="/attendance" class="flex items-center gap-4 pl-6 pr-4 py-3 {{ request()->is('attendance*') ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600 rounded-r-xl' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 border-l-4 border-transparent' }} transition-all font-bold mr-4">
            <i class="fa-solid fa-calendar-check w-5 text-lg"></i>
            <span class="text-sm">Attendance</span>
        </a>
        <!-- Payments -->
        <a href="/payments" class="flex items-center gap-4 pl-6 pr-4 py-3 {{ request()->is('payments*') ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600 rounded-r-xl' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 border-l-4 border-transparent' }} transition-all font-bold mr-4">
            <i class="fa-solid fa-money-bill-wave w-5 text-lg"></i>
            <span class="text-sm">Payments</span>
        </a>
        <!-- Expenses -->
        <a href="/expenses" class="flex items-center gap-4 pl-6 pr-4 py-3 {{ request()->is('expenses*') ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600 rounded-r-xl' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 border-l-4 border-transparent' }} transition-all font-bold mr-4">
            <i class="fa-solid fa-file-invoice-dollar w-5 text-lg"></i>
            <span class="text-sm">Expenses</span>
        </a>
        <!-- Diet Plans -->
        <a href="/diet-plans" class="flex items-center gap-4 pl-6 pr-4 py-3 {{ request()->is('diet-plans*') ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600 rounded-r-xl' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 border-l-4 border-transparent' }} transition-all font-bold mr-4">
            <i class="fa-solid fa-apple-whole w-5 text-lg"></i>
            <span class="text-sm">Diet Plans</span>
        </a>
        <!-- Workout Plans -->
        <a href="/workout-plans" class="flex items-center gap-4 pl-6 pr-4 py-3 {{ request()->is('workout-plans*') ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600 rounded-r-xl' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 border-l-4 border-transparent' }} transition-all font-bold mr-4">
            <i class="fa-solid fa-dumbbell w-5 text-lg"></i>
            <span class="text-sm">Workout Plans</span>
        </a>
        <!-- Reports -->
        <a href="/reports" class="flex items-center gap-4 pl-6 pr-4 py-3 {{ request()->is('reports*') ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600 rounded-r-xl' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 border-l-4 border-transparent' }} transition-all font-bold mr-4">
            <i class="fa-solid fa-chart-line w-5 text-lg"></i>
            <span class="text-sm">Reports</span>
        </a>
    </nav>
    
    <!-- Bottom Widget -->
    <div class="p-4 mt-auto">
        <div class="bg-indigo-50/50 rounded-2xl p-4 flex items-center justify-between cursor-pointer hover:shadow-md transition-all border border-indigo-100 shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 left-0 w-1 h-full bg-indigo-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-crown text-sm"></i>
                </div>
                <div>
                    <div class="text-gray-900 text-xs font-bold" id="sidebar-premium-title">Gymora Premium</div>
                    <div class="text-indigo-600 text-[10px] font-bold mt-0.5">Upgrade your plan</div>
                </div>
            </div>
            <i class="fa-solid fa-chevron-right text-indigo-400 text-[10px]"></i>
        </div>
    </div>
</aside>
