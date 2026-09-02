<!-- Topbar -->
<header class="h-[72px] bg-white border-b border-gray-100 flex items-center justify-between px-8 shrink-0 z-20">
    <!-- Left: 1-Click Quick Action Buttons -->
    <div class="flex items-center gap-3">
        <!-- New Member -->
        <a href="/members" class="bg-[#5d5fef] hover:bg-[#4d4fe0] text-white px-4 py-2 rounded-xl text-xs font-bold shadow-md shadow-[#5d5fef]/25 transition-all flex items-center gap-2 hover:-translate-y-0.5">
            <i class="fa-solid fa-user-plus text-xs"></i>
            <span>New Member</span>
        </a>

        <!-- Collect Fee -->
        <a href="/payments" class="bg-emerald-50 hover:bg-emerald-100 text-emerald-600 border border-emerald-200/80 px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 hover:-translate-y-0.5 shadow-2xs">
            <i class="fa-solid fa-file-invoice-dollar text-xs"></i>
            <span>Collect Fee</span>
        </a>

        <!-- QR Attendance -->
        <a href="/attendance" class="bg-sky-50 hover:bg-sky-100 text-sky-600 border border-sky-200/80 px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 hover:-translate-y-0.5 shadow-2xs">
            <i class="fa-solid fa-qrcode text-xs"></i>
            <span>Attendance</span>
        </a>
    </div>

    <!-- Right section -->
    <div class="flex items-center gap-7">
        <!-- Live Date & Day -->
        <div class="hidden md:flex items-center gap-3 text-xs">
            <div class="text-gray-400 text-lg">
                <i class="fa-regular fa-calendar"></i>
            </div>
            <div class="leading-tight">
                <div class="font-bold text-gray-800" id="topbar-date">30 Aug 2026</div>
                <div class="text-[11px] text-gray-400 font-medium" id="topbar-day">Sunday</div>
            </div>
        </div>
        
        <!-- Notification Bell -->
        <div class="relative">
            <button id="notification-bell" onclick="toggleNotifications()" class="relative w-10 h-10 rounded-full flex items-center justify-center text-gray-500 hover:text-gray-800 hover:bg-gray-50 transition-colors border border-gray-100">
                <i class="fa-regular fa-bell text-base"></i>
                <span id="notification-badge" class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-500 text-white text-[10px] font-black rounded-full flex items-center justify-center border-2 border-white shadow-sm">5</span>
            </button>
            
            <!-- Dropdown -->
            <div id="notification-dropdown" class="hidden absolute right-0 mt-3 w-80 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden z-50 origin-top-right transition-all">
                <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <h3 class="text-xs font-bold text-gray-900">Notifications</h3>
                    <span id="notification-count-text" class="text-[11px] font-bold text-[#5d5fef] bg-indigo-50 px-2 py-0.5 rounded-full">5 New</span>
                </div>
                <div id="notification-list" class="max-h-80 overflow-y-auto divide-y divide-gray-50">
                    <div class="p-4 hover:bg-gray-50 cursor-pointer flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-red-50 text-red-500 flex items-center justify-center text-xs shrink-0"><i class="fa-solid fa-clock"></i></div>
                        <div>
                            <div class="text-xs font-semibold text-gray-800">5 Members plans expiring soon</div>
                            <div class="text-[10px] text-gray-400 mt-0.5">2 hours ago</div>
                        </div>
                    </div>
                    <div class="p-4 hover:bg-gray-50 cursor-pointer flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center text-xs shrink-0"><i class="fa-solid fa-indian-rupee-sign"></i></div>
                        <div>
                            <div class="text-xs font-semibold text-gray-800">12 Pending fee payments due this month</div>
                            <div class="text-[10px] text-gray-400 mt-0.5">Today</div>
                        </div>
                    </div>
                </div>
                <div class="p-2.5 border-t border-gray-100 text-center bg-gray-50/50">
                    <a href="#" class="text-xs font-bold text-[#5d5fef] hover:underline">Mark all as read</a>
                </div>
            </div>
        </div>
        
        <!-- User Profile Pill -->
        <div class="relative">
            <div class="flex items-center gap-3 cursor-pointer select-none" id="profile-toggle" onclick="toggleProfileDropdown()">
                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%239ca3af'%3E%3Cpath d='M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'/%3E%3C/svg%3E" alt="Profile" class="w-10 h-10 rounded-full object-cover border border-gray-200/80 shadow-sm bg-gray-100" id="topbar-avatar">
                <div class="text-left hidden sm:block">
                    <div class="text-xs font-bold text-gray-900 leading-tight" id="topbar-name">Jay Maheshwari</div>
                    <div class="text-[11px] text-gray-400 font-medium leading-tight mt-0.5" id="topbar-role">Gym Owner</div>
                </div>
            </div>
            <script>
                (function() {
                    try {
                        var uStr = localStorage.getItem('user') || sessionStorage.getItem('user');
                        if (uStr) {
                            var u = JSON.parse(uStr);
                            if (u && u.photo) {
                                document.getElementById('topbar-avatar').src = u.photo.startsWith('http') ? u.photo : ('/' + u.photo);
                            }
                            if (u && u.name) {
                                document.getElementById('topbar-name').textContent = u.name;
                            }
                        }
                    } catch(e) {}
                })();
            </script>
            
            <!-- Profile Dropdown -->
            <div id="profile-dropdown" class="hidden absolute right-0 mt-3 w-48 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden z-50 origin-top-right transition-all">
                <a href="/settings" class="flex items-center gap-3 px-4 py-3 text-xs font-semibold text-gray-700 hover:bg-gray-50 hover:text-[#5d5fef] transition-colors">
                    <i class="fa-regular fa-user text-gray-400"></i> Settings & Profile
                </a>
                <div class="border-t border-gray-100"></div>
                <button onclick="logout()" class="w-full text-left flex items-center gap-3 px-4 py-3 text-xs font-semibold text-red-600 hover:bg-red-50 transition-colors">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </button>
            </div>
        </div>
    </div>
</header>
