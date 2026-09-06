<!-- Super Admin Sidebar -->
<aside class="w-[260px] bg-white border-r border-gray-100 flex flex-col hidden md:flex shrink-0 h-screen select-none">
    
    <!-- Platform Brand Logo Header -->
    <div class="px-6 pt-7 pb-4 flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-xl bg-gray-900 flex items-center justify-center text-white shadow-sm shrink-0 overflow-hidden">
            <img src="{{ asset('flexvora.png') }}" class="w-full h-full object-cover rounded-xl" alt="Flexvora">
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-1.5">
                <span class="font-black text-xl tracking-tight text-gray-900 leading-none block truncate">FLEXVORA</span>
                <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-indigo-50 text-[#5d5fef] border border-indigo-100">SAAS</span>
            </div>
            <span class="text-[11px] text-gray-400 font-semibold block truncate mt-1">Platform Super Admin</span>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 px-3.5 py-4 space-y-1 overflow-y-auto custom-scroll">
        
        <div class="px-3 pt-2 pb-1 text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Overview</div>

        <!-- Dashboard -->
        <a href="/admin/dashboard" class="group flex items-center gap-3.5 px-4 py-2.5 {{ request()->is('admin/dashboard*') ? 'bg-[#5d5fef] text-white shadow-md shadow-[#5d5fef]/25 font-bold' : 'text-gray-800 hover:text-[#5d5fef] hover:bg-indigo-50/60 font-bold' }} rounded-xl text-[13.5px] transition-all duration-150">
            <i class="fa-solid fa-chart-pie text-base w-5 text-center {{ request()->is('admin/dashboard*') ? 'text-white' : 'text-gray-600 group-hover:text-[#5d5fef]' }} transition-colors"></i>
            <span>SaaS Dashboard</span>
        </a>

        <!-- Gyms Management (Clients) -->
        <a href="/admin/gyms" class="group flex items-center gap-3.5 px-4 py-2.5 {{ request()->is('admin/gyms*') ? 'bg-[#5d5fef] text-white shadow-md shadow-[#5d5fef]/25 font-bold' : 'text-gray-800 hover:text-[#5d5fef] hover:bg-indigo-50/60 font-bold' }} rounded-xl text-[13.5px] transition-all duration-150">
            <i class="fa-solid fa-building-user text-base w-5 text-center {{ request()->is('admin/gyms*') ? 'text-white' : 'text-gray-600 group-hover:text-[#5d5fef]' }} transition-colors"></i>
            <span>Gym Clients</span>
        </a>

        <div class="px-3 pt-4 pb-1 text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Financials & Reports</div>

        <!-- Platform Expenses -->
        <a href="/admin/expenses" class="group flex items-center gap-3.5 px-4 py-2.5 {{ request()->is('admin/expenses*') ? 'bg-[#5d5fef] text-white shadow-md shadow-[#5d5fef]/25 font-bold' : 'text-gray-800 hover:text-[#5d5fef] hover:bg-indigo-50/60 font-bold' }} rounded-xl text-[13.5px] transition-all duration-150">
            <i class="fa-solid fa-receipt text-base w-5 text-center {{ request()->is('admin/expenses*') ? 'text-white' : 'text-gray-600 group-hover:text-[#5d5fef]' }} transition-colors"></i>
            <span>Platform Expenses</span>
        </a>

        <!-- Excel Reports & Export -->
        <a href="/admin/reports" class="group flex items-center gap-3.5 px-4 py-2.5 {{ request()->is('admin/reports*') ? 'bg-[#5d5fef] text-white shadow-md shadow-[#5d5fef]/25 font-bold' : 'text-gray-800 hover:text-[#5d5fef] hover:bg-indigo-50/60 font-bold' }} rounded-xl text-[13.5px] transition-all duration-150">
            <i class="fa-solid fa-chart-column text-base w-5 text-center {{ request()->is('admin/reports*') ? 'text-white' : 'text-gray-600 group-hover:text-[#5d5fef]' }} transition-colors"></i>
            <span>Reports & Exports</span>
        </a>

        <div class="px-3 pt-4 pb-1 text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Settings</div>

        <!-- Settings -->
        <a href="/admin/settings" class="group flex items-center gap-3.5 px-4 py-2.5 {{ request()->is('admin/settings*') ? 'bg-[#5d5fef] text-white shadow-md shadow-[#5d5fef]/25 font-bold' : 'text-gray-800 hover:text-[#5d5fef] hover:bg-indigo-50/60 font-bold' }} rounded-xl text-[13.5px] transition-all duration-150">
            <i class="fa-solid fa-gear text-base w-5 text-center {{ request()->is('admin/settings*') ? 'text-white' : 'text-gray-600 group-hover:text-[#5d5fef]' }} transition-colors"></i>
            <span>Admin Settings</span>
        </a>
    </nav>

    <!-- Super Admin User Profile Footer -->
    <div class="p-4 border-t border-gray-100 bg-gray-50/60">
        <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-[#5d5fef] flex items-center justify-center font-bold text-xs shrink-0 border border-indigo-100">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <div class="min-w-0">
                    <span class="sa-user-name font-bold text-xs text-gray-900 block truncate leading-tight">Super Admin</span>
                    <span class="text-[10px] text-emerald-600 font-bold block truncate">● Online</span>
                </div>
            </div>
            <button onclick="superAdminLogout()" title="Logout" class="p-2 text-gray-400 hover:text-red-500 hover:bg-white rounded-lg transition-colors cursor-pointer">
                <i class="fa-solid fa-right-from-bracket text-xs"></i>
            </button>
        </div>
    </div>
</aside>
