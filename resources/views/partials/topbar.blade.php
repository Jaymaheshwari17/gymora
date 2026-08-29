<!-- Topbar -->
<header class="h-20 bg-white border-b border-gray-100 flex items-center justify-between px-8 shrink-0 z-10 shadow-sm">
    <!-- Left: Menu Toggle & Search -->
    <div class="flex items-center gap-6">
        <button class="text-gray-400 hover:text-gray-700 bg-gray-50 p-2.5 rounded-xl border border-gray-100">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="relative w-80 hidden sm:block">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
            <input type="text" class="w-full pl-11 pr-4 py-2.5 bg-white border border-gray-200 rounded-full focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none text-gray-700 shadow-sm" placeholder="Search anything...">
        </div>
    </div>

    <!-- Right section -->
    <div class="flex items-center gap-8">
        <!-- Date -->
        <div class="hidden lg:flex items-center gap-3 text-sm">
            <i class="fa-regular fa-calendar text-gray-400 text-lg"></i>
            <div>
                <div class="font-semibold text-gray-800" id="topbar-date">17 May, 2025</div>
                <div class="text-xs text-gray-500 font-medium" id="topbar-day">Saturday</div>
            </div>
        </div>
        
        <!-- Notifications -->
        <div class="relative">
            <button id="notification-bell" onclick="toggleNotifications()" class="relative text-gray-400 hover:text-gray-700 transition-colors">
                <i class="fa-regular fa-bell text-xl"></i>
                <span id="notification-badge" class="hidden absolute -top-1 -right-1 w-4 h-4 bg-indigo-600 text-white text-[9px] font-bold rounded-full items-center justify-center border-2 border-white">0</span>
            </button>
            
            <!-- Dropdown -->
            <div id="notification-dropdown" class="hidden absolute right-0 mt-3 w-80 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden z-50 origin-top-right transition-all">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <h3 class="text-sm font-bold text-gray-900">Notifications</h3>
                    <span id="notification-count-text" class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">0 New</span>
                </div>
                <div id="notification-list" class="max-h-80 overflow-y-auto divide-y divide-gray-50">
                    <div class="p-5 text-center text-xs text-gray-400 font-medium">Loading...</div>
                </div>
                <div class="p-3 border-t border-gray-100 text-center bg-gray-50/50">
                    <a href="#" class="text-xs font-bold text-indigo-600 hover:underline">Mark all as read</a>
                </div>
            </div>
        </div>
        
        <!-- User Profile -->
        <div class="relative">
            <div class="flex items-center gap-3 cursor-pointer" id="profile-toggle" onclick="toggleProfileDropdown()">
                <img src="https://ui-avatars.com/api/?name=U&background=f3f4f6&color=374151" alt="Profile" class="w-10 h-10 rounded-full object-cover border border-gray-200" id="topbar-avatar">
                <div class="text-left hidden sm:block">
                    <div class="text-sm font-bold text-gray-900" id="topbar-name">Loading...</div>
                    <div class="text-xs text-gray-500 font-medium" id="topbar-role">Gym Owner</div>
                </div>
                <i class="fa-solid fa-chevron-down text-gray-400 text-[10px] ml-1 transition-transform duration-200" id="profile-chevron"></i>
            </div>
            
            <!-- Profile Dropdown -->
            <div id="profile-dropdown" class="hidden absolute right-0 mt-3 w-48 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden z-50 origin-top-right transition-all">
                <a href="/settings" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition-colors">
                    <i class="fa-regular fa-user text-gray-400"></i> Profile
                </a>
                <div class="border-t border-gray-100"></div>
                <button onclick="logout()" class="w-full text-left flex items-center gap-3 px-4 py-3 text-sm font-medium text-red-600 hover:bg-red-50 transition-colors">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </button>
            </div>
        </div>
    </div>
</header>
