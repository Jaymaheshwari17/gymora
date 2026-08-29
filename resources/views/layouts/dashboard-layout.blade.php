@extends('layouts.app')

@section('content')
<!-- Custom Styles (Minimal, relying mostly on standard Tailwind classes for stability) -->
<style>
    .dash-sidebar { background-color: #0a0b1a; }
    .dash-main { background-color: #f8f9fc; }
    .premium-box { background-color: #111226; }
    /* Scoped Scrollbars */
    .dash-main ::-webkit-scrollbar { width: 6px; height: 6px; }
    .dash-main ::-webkit-scrollbar-track { background: transparent; }
    .dash-main ::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
    .dash-main ::-webkit-scrollbar-thumb:hover { background: #9ca3af; }

    .dash-sidebar ::-webkit-scrollbar { width: 4px; }
    .dash-sidebar ::-webkit-scrollbar-track { background: transparent; }
    .dash-sidebar ::-webkit-scrollbar-thumb { background: #1f2937; border-radius: 10px; }
    .dash-sidebar ::-webkit-scrollbar-thumb:hover { background: #374151; }
</style>

<div class="flex h-screen overflow-hidden text-gray-800 font-sans dash-main">
    
    @include('partials.sidebar')

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden">
        
        @include('partials.topbar')

        <!-- Dynamic Content -->
        @yield('dashboard-content')
        
    </main>
</div>

<!-- ===== GLOBAL TOAST NOTIFICATIONS ===== -->
<div id="toast-container" class="fixed top-5 right-5 z-[9999] flex flex-col gap-2 pointer-events-none"></div>

@push('scripts')
<script>
    // Global Authentication Check (supports Remember Me via localStorage vs sessionStorage)
    const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
    if (!token) {
        window.location.href = '/login';
    }

    const userStr = localStorage.getItem('user') || sessionStorage.getItem('user');
    let user = null;
    let gymName = 'Gymora';
    
    if (userStr) {
        try {
            user = JSON.parse(userStr);
            if (user.gym && user.gym.name) {
                gymName = user.gym.name;
            }
        } catch(e) {}
    }

    // Set Current Date manually based on image format: "17 May, 2025" and "Saturday"
    const now = new Date();
    const optionsDate = { day: 'numeric', month: 'short', year: 'numeric' };
    const optionsDay = { weekday: 'long' };
    const dateEl = document.getElementById('topbar-date');
    const dayEl = document.getElementById('topbar-day');
    if (dateEl) dateEl.textContent = now.toLocaleDateString('en-GB', optionsDate);
    if (dayEl) dayEl.textContent = now.toLocaleDateString('en-GB', optionsDay);

    if (user) {
        const topbarNameEl = document.getElementById('topbar-name');
        const sidebarGymNameEl = document.getElementById('sidebar-gym-name');
        const sidebarLogoGymNameEl = document.getElementById('sidebar-logo-gym-name');
        if (topbarNameEl) topbarNameEl.textContent = user.name;
        if (sidebarGymNameEl) sidebarGymNameEl.textContent = gymName;
        if (sidebarLogoGymNameEl) sidebarLogoGymNameEl.textContent = gymName;
        
        const roleDisplay = user.role.charAt(0).toUpperCase() + user.role.slice(1);
        const topbarRoleEl = document.getElementById('topbar-role');
        const sidebarRoleNameEl = document.getElementById('sidebar-role-name');
        if (topbarRoleEl) topbarRoleEl.textContent = 'Gym ' + roleDisplay;
        if (sidebarRoleNameEl) sidebarRoleNameEl.textContent = roleDisplay;
        
        const initials = user.name.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase();
        const gymInitials = gymName.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase();
        
        const topbarAvatar = document.getElementById('topbar-avatar');
        const sidebarGymAvatar = document.getElementById('sidebar-gym-avatar');
        
        if (topbarAvatar) {
            topbarAvatar.src = user.photo ? `/${user.photo}` : `https://ui-avatars.com/api/?name=${initials}&background=f3f4f6&color=374151`;
        }
        if (sidebarGymAvatar) sidebarGymAvatar.src = `https://ui-avatars.com/api/?name=${gymInitials}&background=8122db&color=fff`;
    }

    // Fetch fresh user from API on every page load to stay in sync with mobile app updates
    fetch('/api/user', {
        headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
    }).then(r => r.json()).then(freshUser => {
        if (!freshUser || !freshUser.name) return;
        
        // Update whichever storage was used for login
        if (localStorage.getItem('auth_token')) {
            localStorage.setItem('user', JSON.stringify(freshUser));
        } else if (sessionStorage.getItem('auth_token')) {
            sessionStorage.setItem('user', JSON.stringify(freshUser));
        }
        
        user = freshUser;
        const gName = (freshUser.gym && freshUser.gym.name) ? freshUser.gym.name : gymName;
        gymName = gName;

        const topbarNameEl = document.getElementById('topbar-name');
        const sidebarGymNameEl = document.getElementById('sidebar-gym-name');
        const sidebarLogoGymNameEl = document.getElementById('sidebar-logo-gym-name');
        if (topbarNameEl) topbarNameEl.textContent = freshUser.name;
        if (sidebarGymNameEl) sidebarGymNameEl.textContent = gName;
        if (sidebarLogoGymNameEl) sidebarLogoGymNameEl.textContent = gName;

        const roleDisplay = freshUser.role ? (freshUser.role.charAt(0).toUpperCase() + freshUser.role.slice(1)) : '';
        const topbarRoleEl = document.getElementById('topbar-role');
        const sidebarRoleNameEl = document.getElementById('sidebar-role-name');
        if (topbarRoleEl) topbarRoleEl.textContent = 'Gym ' + roleDisplay;
        if (sidebarRoleNameEl) sidebarRoleNameEl.textContent = roleDisplay;

        const initials = freshUser.name.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase();
        const gymInitials = gName.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase();
        const topbarAvatar = document.getElementById('topbar-avatar');
        const sidebarGymAvatar = document.getElementById('sidebar-gym-avatar');
        if (topbarAvatar) {
            topbarAvatar.src = freshUser.photo ? `/${freshUser.photo}` : `https://ui-avatars.com/api/?name=${initials}&background=f3f4f6&color=374151`;
        }
        if (sidebarGymAvatar) sidebarGymAvatar.src = `https://ui-avatars.com/api/?name=${gymInitials}&background=8122db&color=fff`;
    }).catch(() => {});

    function logout() {
        fetch('/api/logout', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json'
            }
        });
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        sessionStorage.removeItem('auth_token');
        sessionStorage.removeItem('user');
        window.location.href = '/login';
    }

    /**
     * showToast(message, type)
     * type: 'success' | 'error' | 'warning' | 'info'
     */
    function showToast(message, type = 'success') {
        const icons = {
            success: '<i class="fa-solid fa-circle-check text-white"></i>',
            error:   '<i class="fa-solid fa-circle-xmark text-white"></i>',
            warning: '<i class="fa-solid fa-triangle-exclamation text-white"></i>',
            info:    '<i class="fa-solid fa-circle-info text-white"></i>',
        };
        const colors = {
            success: 'bg-[#10b981]',
            error:   'bg-[#ef4444]',
            warning: 'bg-[#f59e0b]',
            info:    'bg-[#3b82f6]',
        };
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl shadow-xl text-white text-sm font-semibold ${
            colors[type] || colors.info
        } translate-x-full opacity-0 transition-all duration-300 max-w-xs`;
        toast.innerHTML = `${icons[type] || icons.info}<span>${message}</span>`;
        container.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
            });
        });

        // Auto-remove after 3s
        setTimeout(() => {
            toast.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // --- Dropdowns Logic ---
    function toggleNotifications() {
        const dropdown = document.getElementById('notification-dropdown');
        if (dropdown.classList.contains('hidden')) {
            dropdown.classList.remove('hidden');
            fetchNotifications();
        } else {
            dropdown.classList.add('hidden');
        }
    }

    function toggleProfileDropdown() {
        const dropdown = document.getElementById('profile-dropdown');
        const chevron = document.getElementById('profile-chevron');
        if (dropdown.classList.contains('hidden')) {
            dropdown.classList.remove('hidden');
            if(chevron) chevron.style.transform = 'rotate(180deg)';
        } else {
            dropdown.classList.add('hidden');
            if(chevron) chevron.style.transform = 'rotate(0deg)';
        }
    }

    // Close dropdowns on outside click
    document.addEventListener('click', function(event) {
        // Notifications
        const bell = document.getElementById('notification-bell');
        const notifDropdown = document.getElementById('notification-dropdown');
        if (bell && notifDropdown && !bell.contains(event.target) && !notifDropdown.contains(event.target)) {
            notifDropdown.classList.add('hidden');
        }
        
        // Profile
        const profileToggle = document.getElementById('profile-toggle');
        const profileDropdown = document.getElementById('profile-dropdown');
        const chevron = document.getElementById('profile-chevron');
        if (profileToggle && profileDropdown && !profileToggle.contains(event.target) && !profileDropdown.contains(event.target)) {
            profileDropdown.classList.add('hidden');
            if(chevron) chevron.style.transform = 'rotate(0deg)';
        }
    });

    async function fetchNotifications() {
        if(!token) return;
        const list = document.getElementById('notification-list');
        list.innerHTML = '<div class="p-5 text-center text-xs text-gray-400 font-medium">Loading...</div>';
        try {
            const res = await fetch('/api/dashboard/notifications', {
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            });
            const result = await res.json();
            if (res.ok && result.success) {
                renderNotifications(result.data, result.unread_count);
            }
        } catch(e) {}
    }

    function renderNotifications(data, unreadCount) {
        const badge = document.getElementById('notification-badge');
        const countText = document.getElementById('notification-count-text');
        const list = document.getElementById('notification-list');

        if (unreadCount > 0) {
            badge.textContent = unreadCount;
            badge.classList.remove('hidden');
            badge.classList.add('flex');
            countText.textContent = `${unreadCount} New`;
        } else {
            badge.classList.add('hidden');
            badge.classList.remove('flex');
            countText.textContent = `0 New`;
        }

        let html = '';
        data.forEach(n => {
            html += `
                <div class="p-4 hover:bg-gray-50 transition cursor-pointer flex gap-4">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 ${n.bg} ${n.color}">
                        <i class="fa-solid ${n.icon}"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-bold text-gray-900 truncate">${n.title}</span>
                            <span class="text-[10px] font-medium text-gray-400 shrink-0 ml-2">${n.time}</span>
                        </div>
                        <p class="text-xs text-gray-500 leading-snug line-clamp-2">${n.message}</p>
                    </div>
                </div>
            `;
        });
        list.innerHTML = html;
    }
    
    // Initial fetch to get badge count on page load
    document.addEventListener('DOMContentLoaded', fetchNotifications);
</script>
<!-- Page Specific Scripts -->
@stack('page-scripts')
@endpush
@endsection
