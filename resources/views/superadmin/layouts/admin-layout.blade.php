<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flexvora SaaS Platform | Super Admin Portal</title>
    
    <!-- Google Fonts (Inter & Outfit) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- FontAwesome 6 Pro / Free CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        primary: '#5d5fef',
                        primaryHover: '#4d4fe0',
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8f9fc;
        }
        .custom-scroll::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        .custom-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 99px;
        }
        .custom-scroll::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
    </style>
</head>
<body class="bg-[#f8f9fc] text-gray-800 antialiased min-h-screen flex flex-col">

    <div class="flex h-screen overflow-hidden">
        <!-- Super Admin Sidebar -->
        @include('superadmin.partials.sidebar')

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col h-screen overflow-hidden">
            <!-- Topbar -->
            @include('superadmin.partials.topbar')

            <!-- Page Body -->
            <main class="flex-1 overflow-y-auto custom-scroll p-6 lg:p-8 bg-[#f8f9fc]">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed top-5 right-5 z-[9999] flex flex-col gap-2.5 pointer-events-none"></div>

    <script>
        // Check Super Admin Token
        const saToken = localStorage.getItem('superadmin_token') || sessionStorage.getItem('superadmin_token');
        const currentPath = window.location.pathname;

        if (!saToken && !currentPath.includes('/admin/login')) {
            window.location.href = '/admin/login';
        }

        const saUserStr = localStorage.getItem('superadmin_user') || sessionStorage.getItem('superadmin_user');
        if (saUserStr) {
            try {
                const saUser = JSON.parse(saUserStr);
                const nameEls = document.querySelectorAll('.sa-user-name');
                const emailEls = document.querySelectorAll('.sa-user-email');
                nameEls.forEach(el => el.textContent = saUser.name || 'Platform Admin');
                emailEls.forEach(el => el.textContent = saUser.email || 'jay@gmail.com');
            } catch(e) {}
        }

        // Global Toast Notification Helper
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            const bgClass = type === 'success' ? 'bg-emerald-600 text-white' : (type === 'error' ? 'bg-rose-600 text-white' : 'bg-gray-900 text-white');
            const iconClass = type === 'success' ? 'fa-circle-check' : (type === 'error' ? 'fa-circle-exclamation' : 'fa-circle-info');

            toast.className = `flex items-center gap-3 px-4 py-3 rounded-xl shadow-xl text-xs font-bold transition-all transform translate-y-2 opacity-0 pointer-events-auto ${bgClass}`;
            toast.innerHTML = `<i class="fa-solid ${iconClass} text-sm"></i><span>${message}</span>`;
            
            container.appendChild(toast);

            setTimeout(() => {
                toast.classList.remove('translate-y-2', 'opacity-0');
            }, 10);

            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-2');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        // Global Logout Helper
        function superAdminLogout() {
            localStorage.removeItem('superadmin_token');
            localStorage.removeItem('superadmin_user');
            sessionStorage.removeItem('superadmin_token');
            sessionStorage.removeItem('superadmin_user');
            showToast('Logged out successfully', 'info');
            setTimeout(() => {
                window.location.href = '/admin/login';
            }, 500);
        }
    </script>

    @stack('scripts')
</body>
</html>
