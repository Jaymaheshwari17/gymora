<!-- Sidebar -->
<aside class="w-[260px] bg-white border-r border-gray-100 flex flex-col hidden md:flex shrink-0 h-screen select-none">
    <!-- Logo -->
    <div class="px-6 pt-7 pb-4 flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-xl bg-gray-900 flex items-center justify-center text-white shadow-sm shrink-0">
            <img id="sidebar-gym-logo-img" src="" class="w-full h-full object-cover rounded-xl" style="display: none;">
            <i id="sidebar-gym-logo-icon" class="fa-solid fa-dumbbell text-lg"></i>
        </div>
        <div class="flex-1 min-w-0">
            <span id="sidebar-logo-gym-name" class="font-black text-xl tracking-tight text-gray-900 leading-none block truncate">GYMORA</span>
            <span class="text-[11px] text-gray-500 font-semibold block truncate mt-1">Gym Management System</span>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 px-3.5 py-4 space-y-1 overflow-y-auto custom-sidebar-scroll">
        <!-- Dashboard -->
        <a href="/dashboard" class="group flex items-center gap-3.5 px-4 py-2.5 {{ request()->is('dashboard') ? 'bg-[#5d5fef] text-white shadow-md shadow-[#5d5fef]/25 font-bold' : 'text-gray-800 hover:text-[#5d5fef] hover:bg-indigo-50/60 font-bold' }} rounded-xl text-[13.5px] transition-all duration-150">
            <i class="fa-solid fa-table-cells-large text-base w-5 text-center {{ request()->is('dashboard') ? 'text-white' : 'text-gray-700 group-hover:text-[#5d5fef]' }} transition-colors"></i>
            <span>Dashboard</span>
        </a>

        <!-- Members -->
        <a href="/members" class="group flex items-center gap-3.5 px-4 py-2.5 {{ request()->is('members*') ? 'bg-[#5d5fef] text-white shadow-md shadow-[#5d5fef]/25 font-bold' : 'text-gray-800 hover:text-[#5d5fef] hover:bg-indigo-50/60 font-bold' }} rounded-xl text-[13.5px] transition-all duration-150">
            <i class="fa-solid fa-user-group text-base w-5 text-center {{ request()->is('members*') ? 'text-white' : 'text-gray-700 group-hover:text-[#5d5fef]' }} transition-colors"></i>
            <span>Members</span>
        </a>

        <!-- Staff / Trainers -->
        <a href="/staff" class="group flex items-center gap-3.5 px-4 py-2.5 {{ request()->is('staff*') ? 'bg-[#5d5fef] text-white shadow-md shadow-[#5d5fef]/25 font-bold' : 'text-gray-800 hover:text-[#5d5fef] hover:bg-indigo-50/60 font-bold' }} rounded-xl text-[13.5px] transition-all duration-150">
            <i class="fa-solid fa-id-badge text-base w-5 text-center {{ request()->is('staff*') ? 'text-white' : 'text-gray-700 group-hover:text-[#5d5fef]' }} transition-colors"></i>
            <span>Staff / Trainers</span>
        </a>

        <!-- Batches -->
        <a href="/batches" class="group flex items-center gap-3.5 px-4 py-2.5 {{ request()->is('batches*') ? 'bg-[#5d5fef] text-white shadow-md shadow-[#5d5fef]/25 font-bold' : 'text-gray-800 hover:text-[#5d5fef] hover:bg-indigo-50/60 font-bold' }} rounded-xl text-[13.5px] transition-all duration-150">
            <i class="fa-solid fa-layer-group text-base w-5 text-center {{ request()->is('batches*') ? 'text-white' : 'text-gray-700 group-hover:text-[#5d5fef]' }} transition-colors"></i>
            <span>Batches</span>
        </a>

        <!-- Plans -->
        <a href="/plans" class="group flex items-center gap-3.5 px-4 py-2.5 {{ request()->is('plans*') ? 'bg-[#5d5fef] text-white shadow-md shadow-[#5d5fef]/25 font-bold' : 'text-gray-800 hover:text-[#5d5fef] hover:bg-indigo-50/60 font-bold' }} rounded-xl text-[13.5px] transition-all duration-150">
            <i class="fa-solid fa-clipboard-list text-base w-5 text-center {{ request()->is('plans*') ? 'text-white' : 'text-gray-700 group-hover:text-[#5d5fef]' }} transition-colors"></i>
            <span>Plans</span>
        </a>

        <!-- Attendance -->
        <a href="/attendance" class="group flex items-center gap-3.5 px-4 py-2.5 {{ request()->is('attendance*') ? 'bg-[#5d5fef] text-white shadow-md shadow-[#5d5fef]/25 font-bold' : 'text-gray-800 hover:text-[#5d5fef] hover:bg-indigo-50/60 font-bold' }} rounded-xl text-[13.5px] transition-all duration-150">
            <i class="fa-solid fa-calendar-check text-base w-5 text-center {{ request()->is('attendance*') ? 'text-white' : 'text-gray-700 group-hover:text-[#5d5fef]' }} transition-colors"></i>
            <span>Attendance</span>
        </a>

        <!-- Payments -->
        <a href="/payments" class="group flex items-center gap-3.5 px-4 py-2.5 {{ request()->is('payments*') ? 'bg-[#5d5fef] text-white shadow-md shadow-[#5d5fef]/25 font-bold' : 'text-gray-800 hover:text-[#5d5fef] hover:bg-indigo-50/60 font-bold' }} rounded-xl text-[13.5px] transition-all duration-150">
            <i class="fa-solid fa-credit-card text-base w-5 text-center {{ request()->is('payments*') ? 'text-white' : 'text-gray-700 group-hover:text-[#5d5fef]' }} transition-colors"></i>
            <span>Payments</span>
        </a>

        <!-- Expenses -->
        <a href="/expenses" class="group flex items-center gap-3.5 px-4 py-2.5 {{ request()->is('expenses*') ? 'bg-[#5d5fef] text-white shadow-md shadow-[#5d5fef]/25 font-bold' : 'text-gray-800 hover:text-[#5d5fef] hover:bg-indigo-50/60 font-bold' }} rounded-xl text-[13.5px] transition-all duration-150">
            <i class="fa-solid fa-receipt text-base w-5 text-center {{ request()->is('expenses*') ? 'text-white' : 'text-gray-700 group-hover:text-[#5d5fef]' }} transition-colors"></i>
            <span>Expenses</span>
        </a>

        <!-- Reports -->
        <a href="/reports" class="group flex items-center gap-3.5 px-4 py-2.5 {{ request()->is('reports*') ? 'bg-[#5d5fef] text-white shadow-md shadow-[#5d5fef]/25 font-bold' : 'text-gray-800 hover:text-[#5d5fef] hover:bg-indigo-50/60 font-bold' }} rounded-xl text-[13.5px] transition-all duration-150">
            <i class="fa-solid fa-chart-column text-base w-5 text-center {{ request()->is('reports*') ? 'text-white' : 'text-gray-700 group-hover:text-[#5d5fef]' }} transition-colors"></i>
            <span>Reports</span>
        </a>

        <!-- Diet Plans -->
        <a href="/diet-plans" class="group flex items-center gap-3.5 px-4 py-2.5 {{ request()->is('diet-plans*') ? 'bg-[#5d5fef] text-white shadow-md shadow-[#5d5fef]/25 font-bold' : 'text-gray-800 hover:text-[#5d5fef] hover:bg-indigo-50/60 font-bold' }} rounded-xl text-[13.5px] transition-all duration-150">
            <i class="fa-solid fa-apple-whole text-base w-5 text-center {{ request()->is('diet-plans*') ? 'text-white' : 'text-gray-700 group-hover:text-[#5d5fef]' }} transition-colors"></i>
            <span>Diet Plans</span>
        </a>

        <!-- Workout Plans -->
        <a href="/workout-plans" class="group flex items-center gap-3.5 px-4 py-2.5 {{ request()->is('workout-plans*') ? 'bg-[#5d5fef] text-white shadow-md shadow-[#5d5fef]/25 font-bold' : 'text-gray-800 hover:text-[#5d5fef] hover:bg-indigo-50/60 font-bold' }} rounded-xl text-[13.5px] transition-all duration-150">
            <i class="fa-solid fa-dumbbell text-base w-5 text-center {{ request()->is('workout-plans*') ? 'text-white' : 'text-gray-700 group-hover:text-[#5d5fef]' }} transition-colors"></i>
            <span>Workout Plans</span>
        </a>

        <!-- Settings -->
        <a href="/settings" class="group flex items-center gap-3.5 px-4 py-2.5 {{ request()->is('settings*') ? 'bg-[#5d5fef] text-white shadow-md shadow-[#5d5fef]/25 font-bold' : 'text-gray-800 hover:text-[#5d5fef] hover:bg-indigo-50/60 font-bold' }} rounded-xl text-[13.5px] transition-all duration-150">
            <i class="fa-solid fa-gear text-base w-5 text-center {{ request()->is('settings*') ? 'text-white' : 'text-gray-700 group-hover:text-[#5d5fef]' }} transition-colors"></i>
            <span>Settings</span>
        </a>
    </nav>
</aside>
