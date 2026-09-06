<!-- Super Admin Topbar -->
<header class="h-16 bg-white border-b border-gray-100 px-6 lg:px-8 flex items-center justify-between shrink-0 z-20">
    
    <!-- Left: Welcome Breadcrumb / Live Mode -->
    <div class="flex items-center gap-3">
        <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="text-xs font-bold text-gray-800">SaaS Live Platform</span>
        </div>
        <span class="text-gray-300">|</span>
        <span class="text-xs text-gray-500 font-medium" id="sa-topbar-date">Today</span>
    </div>

    <!-- Right: Quick Actions & Profile -->
    <div class="flex items-center gap-3">
        
        <!-- Quick Add Expense Shortcut -->
        <a href="/admin/expenses" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-50 hover:bg-gray-100 text-gray-700 text-xs font-bold rounded-xl border border-gray-200 transition-colors">
            <i class="fa-solid fa-receipt text-rose-500 text-xs"></i>
            <span>+ Add Expense</span>
        </a>

        <!-- Quick Add Gym Shortcut -->
        <a href="/admin/gyms" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#5d5fef] hover:bg-[#4d4fe0] text-white text-xs font-bold rounded-xl shadow-md shadow-[#5d5fef]/20 transition-colors">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>+ Onboard Gym</span>
        </a>

        <div class="h-4 w-[1px] bg-gray-200 mx-1"></div>

        <!-- Super Admin Avatar -->
        <a href="/admin/settings" class="flex items-center gap-2.5 p-1 hover:bg-gray-50 rounded-xl transition-colors">
            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-[#5d5fef] border border-indigo-100 flex items-center justify-center font-bold text-xs shadow-sm">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <div class="hidden md:block text-left">
                <span class="sa-user-name font-bold text-xs text-gray-900 block leading-none">Super Admin</span>
                <span class="text-[10px] text-[#5d5fef] font-bold block mt-0.5">Settings &rarr;</span>
            </div>
        </a>
    </div>
</header>

<script>
    const d = new Date();
    const dateStr = d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', weekday: 'short' });
    const topbarDateEl = document.getElementById('sa-topbar-date');
    if (topbarDateEl) topbarDateEl.textContent = dateStr;
</script>
