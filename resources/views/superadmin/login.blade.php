<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Login | Flexvora SaaS</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-[#f8f9fc] min-h-screen flex items-center justify-center p-4 selection:bg-[#5d5fef] selection:text-white font-sans text-gray-800">

    <!-- Login Card Container -->
    <div class="w-full max-w-md">
        
        <!-- Brand Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gray-900 text-white shadow-lg shadow-gray-900/10 mb-3 overflow-hidden">
                <img src="{{ asset('flexvora.png') }}" class="w-full h-full object-cover rounded-2xl" alt="Flexvora">
            </div>
            <h1 class="text-3xl font-black tracking-tight text-gray-900 font-display">FLEXVORA <span class="text-[#5d5fef]">SAAS</span></h1>
            <p class="text-gray-500 text-xs font-semibold mt-1">Platform Creator & Super Admin Control Portal</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white border border-gray-100 rounded-3xl p-8 shadow-xl shadow-indigo-950/5">
            
            <div id="login-alert" class="hidden mb-5 p-3.5 rounded-xl text-xs font-bold flex items-center gap-2.5"></div>

            <form id="admin-login-form" onsubmit="handleSuperAdminLogin(event)" class="space-y-4">
                
                <!-- Email Field -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wider">Super Admin Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-envelope text-xs"></i>
                        </div>
                        <input type="email" id="email" required
                            class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-800 text-xs font-bold focus:bg-white focus:border-[#5d5fef] focus:ring-2 focus:ring-[#5d5fef]/10 outline-none transition-all placeholder:text-gray-400" 
                            placeholder="jay@gmail.com" value="jay@gmail.com">
                    </div>
                </div>

                <!-- Password Field -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wider">Master Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-lock text-xs"></i>
                        </div>
                        <input type="password" id="password" required
                            class="w-full pl-10 pr-10 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-800 text-xs font-bold focus:bg-white focus:border-[#5d5fef] focus:ring-2 focus:ring-[#5d5fef]/10 outline-none transition-all placeholder:text-gray-400" 
                            placeholder="••••••••" value="Jay@12345">
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-700">
                            <i class="fa-solid fa-eye text-xs" id="eye-icon"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="btn-login-submit" class="w-full py-3.5 px-4 bg-[#5d5fef] hover:bg-[#4d4fe0] text-white text-xs font-extrabold rounded-xl shadow-lg shadow-[#5d5fef]/25 transition-all flex items-center justify-center gap-2 cursor-pointer mt-2">
                    <span>Unlock Platform Control</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
            </form>

            <!-- Quick info notice -->
            <div class="mt-6 pt-5 border-t border-gray-100 text-center">
                <span class="text-[11px] text-gray-400 font-medium">Default Credentials: <code class="text-[#5d5fef] font-bold">jay@gmail.com</code> / <code class="text-[#5d5fef] font-bold">Jay@12345</code></span>
            </div>
        </div>

        <div class="text-center mt-6">
            <a href="/login" class="text-xs text-gray-400 hover:text-[#5d5fef] font-bold transition-colors inline-flex items-center gap-1.5">
                <i class="fa-solid fa-dumbbell text-[10px]"></i>
                <span>Switch to Gym Owner / Staff Login &rarr;</span>
            </a>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                pwd.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        async function handleSuperAdminLogin(event) {
            event.preventDefault();
            const btn = document.getElementById('btn-login-submit');
            const alertEl = document.getElementById('login-alert');
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;

            btn.disabled = true;
            btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin text-xs"></i> Verifying Master Access...`;
            alertEl.classList.add('hidden');

            try {
                const response = await fetch('/api/superadmin/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email, password })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    localStorage.setItem('superadmin_token', data.data.token);
                    localStorage.setItem('superadmin_user', JSON.stringify(data.data.user));

                    alertEl.className = 'mb-5 p-3.5 rounded-xl text-xs font-bold flex items-center gap-2.5 bg-emerald-50 text-emerald-700 border border-emerald-200';
                    alertEl.innerHTML = `<i class="fa-solid fa-circle-check"></i> Login Successful! Redirecting...`;
                    alertEl.classList.remove('hidden');

                    setTimeout(() => {
                        window.location.href = '/admin/dashboard';
                    }, 600);
                } else {
                    alertEl.className = 'mb-5 p-3.5 rounded-xl text-xs font-bold flex items-center gap-2.5 bg-rose-50 text-rose-700 border border-rose-200';
                    alertEl.innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> ${data.message || 'Access Denied. Check credentials.'}`;
                    alertEl.classList.remove('hidden');
                    btn.disabled = false;
                    btn.innerHTML = `<span>Unlock Platform Control</span> <i class="fa-solid fa-arrow-right text-xs"></i>`;
                }
            } catch (err) {
                alertEl.className = 'mb-5 p-3.5 rounded-xl text-xs font-bold flex items-center gap-2.5 bg-rose-50 text-rose-700 border border-rose-200';
                alertEl.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> Network error connecting to platform server.`;
                alertEl.classList.remove('hidden');
                btn.disabled = false;
                btn.innerHTML = `<span>Unlock Platform Control</span> <i class="fa-solid fa-arrow-right text-xs"></i>`;
            }
        }
    </script>
</body>
</html>
