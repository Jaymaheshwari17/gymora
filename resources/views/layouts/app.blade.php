<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gymora - Stronger You, Better Tomorrow</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (via CDN instead of Vite) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'Outfit', 'sans-serif'],
                    },
                    colors: {
                        'gym-dark': '#0f111a',
                        'gym-card': '#1a1d2d',
                        'gym-pink': '#d9229b',
                        'gym-purple': '#8122db',
                        'brand': {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            accent: '#5d5fef'
                        }
                    }
                }
            }
        }
    </script>
    
    <style type="text/tailwindcss">
        @layer base {
            :root {
                --color-gym-dark: #0f111a;
                --color-gym-card: #1a1d2d;
                --color-gym-pink: #d9229b;
                --color-gym-purple: #8122db;
            }
        }
        @layer utilities {
            .bg-gym-gradient {
                background: linear-gradient(135deg, theme('colors.gym-purple') 0%, theme('colors.gym-pink') 100%);
            }
            .text-gym-gradient {
                background: linear-gradient(135deg, theme('colors.gym-purple') 0%, theme('colors.gym-pink') 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }
        }
    </style>
    
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            background-color: var(--color-gym-dark, #0f111a);
            color: #ffffff;
            font-family: 'Outfit', sans-serif;
        }
        
        /* Utility gradients */
        .bg-gym-gradient {
            background: linear-gradient(135deg, var(--color-gym-purple, #8122db) 0%, var(--color-gym-pink, #d9229b) 100%);
        }
        
        .text-gym-gradient {
            background: linear-gradient(135deg, var(--color-gym-purple, #8122db) 0%, var(--color-gym-pink, #d9229b) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #0f111a; 
        }
        ::-webkit-scrollbar-thumb {
            background: #1a1d2d; 
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #4f46e5; 
        }

        /* Custom DataTables Styling to match Tailwind */
        table.dataTable {
            border-collapse: collapse !important;
            border-spacing: 0;
            width: 100% !important;
            margin-top: 1.5rem !important;
            margin-bottom: 1.5rem !important;
        }
        table.dataTable thead th {
            border-bottom: 2px solid #f3f4f6 !important;
            padding: 1.25rem 1rem !important;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #4b5563;
            background-color: #f9fafb;
        }
        table.dataTable tbody td {
            border-bottom: 1px solid #f3f4f6 !important;
            padding: 1.25rem 1rem !important;
            font-size: 0.95rem;
            color: #374151;
            vertical-align: middle;
        }
        table.dataTable tbody tr:hover {
            background-color: #f9fafb !important;
        }
        
        /* Search and Length wrappers */
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1.5rem;
        }
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #d1d5db;
            border-radius: 0.75rem;
            padding: 0.5rem 1rem;
            outline: none;
            font-size: 0.95rem;
            margin-left: 0.75rem;
            transition: all 0.2s;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }
        .dataTables_wrapper .dataTables_length {
            margin-bottom: 1.5rem;
        }
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #d1d5db;
            border-radius: 0.75rem;
            padding: 0.5rem 2.5rem 0.5rem 1rem;
            outline: none;
            font-size: 0.95rem;
            margin: 0 0.5rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        
        /* Pagination */
        .dataTables_wrapper .dataTables_paginate {
            margin-top: 1.5rem;
            padding-top: 1rem;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: #4f46e5 !important;
            color: white !important;
            border: none !important;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
            font-weight: 600;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border: none !important;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem !important;
            margin: 0 0.25rem;
            font-weight: 500;
            color: #4b5563 !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #f3f4f6 !important;
            color: #111827 !important;
        }
        table.dataTable.no-footer {
            border-bottom: 2px solid #f3f4f6 !important;
        }
    </style>
</head>
<body class="antialiased min-h-screen">
    
    <div id="app">
        @yield('content')
    </div>

    <!-- Global Full Page Loader -->
    <div id="global-loader" class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-[9999] hidden flex items-center justify-center">
        <div class="bg-white p-6 rounded-2xl shadow-2xl flex flex-col items-center">
            <i class="fa-solid fa-circle-notch fa-spin text-indigo-600 text-4xl mb-4"></i>
            <span class="text-gray-700 font-semibold text-sm">Processing...</span>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Global SweetAlert Toast Configuration
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        // Global Success Toast
        function showSuccess(message) {
            Toast.fire({
                icon: 'success',
                title: message
            });
        }

        // Global Error Toast
        function showError(message) {
            Toast.fire({
                icon: 'error',
                title: message
            });
        }

        // Global Delete Confirmation
        function confirmDelete(title, text, callback) {
            Swal.fire({
                title: title || 'Are you sure?',
                text: text || "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    if(typeof callback === 'function') callback();
                }
            });
        }

        // Global Loader Functions
        function showLoader() {
            document.getElementById('global-loader').classList.remove('hidden');
        }
        function hideLoader() {
            document.getElementById('global-loader').classList.add('hidden');
        }
    </script>
    @stack('scripts')
</body>
</html>
