@extends('layouts.app')

@section('content')
<div class="min-h-screen flex w-full bg-white">
    
    <!-- Left Panel -->
    <div class="hidden lg:flex w-1/2 relative bg-black items-center justify-center overflow-hidden">
        <img src="/background/gymora1.png" alt="Gym Background" class="absolute inset-0 w-full h-full object-cover opacity-60">
        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-black/10"></div>
        
        <div class="relative z-10 w-full max-w-xl p-12 mt-auto">
            <h2 class="text-4xl font-bold text-white mb-2">Stronger <span class="text-indigo-500">Every Day</span></h2>
            <p class="text-gray-300 text-lg mb-10 max-w-md">Manage your gym, members, and workouts seamlessly.</p>
            
            <div class="space-y-6">
                <!-- Feature 1 -->
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center shrink-0 backdrop-blur-md">
                        <i class="fa-solid fa-users text-white text-lg"></i>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold">Member Management</h4>
                        <p class="text-gray-400 text-sm">Easily manage your members and their progress.</p>
                    </div>
                </div>
                <!-- Feature 2 -->
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center shrink-0 backdrop-blur-md">
                        <i class="fa-regular fa-calendar-check text-indigo-500 text-lg"></i>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold">Workout Tracking</h4>
                        <p class="text-gray-400 text-sm">Track workouts, plans, and member performance.</p>
                    </div>
                </div>
                <!-- Feature 3 -->
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center shrink-0 backdrop-blur-md">
                        <i class="fa-solid fa-chart-line text-white text-lg"></i>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold">Reports & Analytics</h4>
                        <p class="text-gray-400 text-sm">Get insights that help your gym grow.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Panel -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12 relative">


        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="flex flex-col items-center justify-center mb-8">
                <div class="flex items-center gap-2 text-2xl font-bold tracking-tight text-gray-900 mb-1">
                    <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center text-white">
                        <i class="fa-solid fa-dumbbell text-sm"></i>
                    </div>
                    <span>GYMORA</span>
                </div>
                <p class="text-gray-500 text-sm font-medium">Gym Management System</p>
            </div>

            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Welcome Back!</h1>
                <p class="text-gray-500">Sign in to continue to your account</p>
            </div>

            <!-- General Alert Box -->
            <div id="alertBox" class="hidden mb-4 p-3 rounded-lg text-sm bg-red-50 text-red-600 border border-red-200"></div>

            <form id="loginForm" class="space-y-5" novalidate>
                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-regular fa-envelope"></i>
                        </div>
                        <input type="email" id="email" name="email"
                            class="form-input w-full pl-11 pr-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-600/20 focus:border-indigo-600 text-gray-900 placeholder-gray-400 transition-all outline-none shadow-sm"
                            placeholder="Enter your email">
                    </div>
                    <p id="error-email" class="error-msg hidden text-red-500 text-xs mt-1.5 ml-1"></p>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <input type="password" id="password" name="password"
                            class="form-input w-full pl-11 pr-12 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-600/20 focus:border-indigo-600 text-gray-900 placeholder-gray-400 transition-all outline-none shadow-sm"
                            placeholder="Enter your password">
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center cursor-pointer text-gray-400 hover:text-gray-600" onclick="togglePassword()">
                            <i class="fa-regular fa-eye-slash" id="eyeIcon"></i>
                        </div>
                    </div>
                    <p id="error-password" class="error-msg hidden text-red-500 text-xs mt-1.5 ml-1"></p>
                </div>

                <!-- Options -->
                <div class="flex items-center justify-between text-sm pt-2">
                    <label class="flex items-center text-gray-600 cursor-pointer">
                        <input type="checkbox" id="rememberMe" name="remember" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-600 w-4 h-4 mr-2">
                        Remember me
                    </label>
                </div>

                <!-- Submit -->
                <button type="submit" id="submitBtn"
                    class="w-full py-3.5 bg-indigo-600 text-white rounded-xl font-medium shadow-md shadow-indigo-600/30 hover:bg-indigo-700 hover:shadow-lg transition-all flex items-center justify-center gap-2 mt-4">
                    <span>Sign In</span>
                    <i class="fa-solid fa-arrow-right text-sm"></i>
                </button>
            </form>

            <div class="text-center text-gray-600 text-sm mt-8">
                Don't have an account? 
                <a href="/register" class="text-indigo-600 font-medium hover:text-indigo-700 ml-1">Sign Up</a>
            </div>
        </div>
    </div>
    <!-- Forgot Password Modal -->
    <div id="forgotPasswordModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl relative">
            <button onclick="closeForgotModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
            
            <div id="forgotStep1">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Reset Password</h3>
                <p class="text-gray-500 text-sm mb-6">Enter your email address and we'll send you an OTP to reset your password.</p>
                
                <div id="forgotAlertBox" class="hidden mb-4 p-3 rounded-lg text-sm bg-red-50 text-red-600 border border-red-200"></div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
                        <input type="email" id="forgotEmail" class="form-input w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-600/20 focus:border-indigo-600 text-gray-900 outline-none" placeholder="Enter your email">
                    </div>
                    <button id="sendOtpBtn" onclick="sendOtp()" class="w-full py-3 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition-colors">
                        Send OTP
                    </button>
                </div>
            </div>

            <div id="forgotStep2" class="hidden">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Enter OTP & New Password</h3>
                <p class="text-gray-500 text-sm mb-6">Enter the 6-digit OTP sent to your email and your new password.</p>
                
                <div id="resetAlertBox" class="hidden mb-4 p-3 rounded-lg text-sm bg-red-50 text-red-600 border border-red-200"></div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">OTP</label>
                        <input type="text" id="resetOtp" class="form-input w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-600/20 focus:border-indigo-600 text-gray-900 outline-none" placeholder="Enter 6-digit OTP" maxlength="6">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">New Password</label>
                        <input type="password" id="resetPasswordInput" class="form-input w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-600/20 focus:border-indigo-600 text-gray-900 outline-none" placeholder="New Password">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm Password</label>
                        <input type="password" id="resetPasswordConfirm" class="form-input w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-600/20 focus:border-indigo-600 text-gray-900 outline-none" placeholder="Confirm Password">
                    </div>
                    <button id="resetPassBtn" onclick="resetPassword()" class="w-full py-3 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition-colors">
                        Reset Password
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openForgotModal() {
        document.getElementById('forgotPasswordModal').classList.remove('hidden');
        document.getElementById('forgotStep1').classList.remove('hidden');
        document.getElementById('forgotStep2').classList.add('hidden');
        document.getElementById('forgotEmail').value = '';
        document.getElementById('forgotAlertBox').classList.add('hidden');
    }

    function closeForgotModal() {
        document.getElementById('forgotPasswordModal').classList.add('hidden');
    }

    async function sendOtp() {
        const email = document.getElementById('forgotEmail').value;
        const btn = document.getElementById('sendOtpBtn');
        const alertBox = document.getElementById('forgotAlertBox');

        if(!email) {
            alertBox.textContent = 'Please enter your email.';
            alertBox.classList.remove('hidden');
            return;
        }

        btn.innerHTML = 'Sending...';
        btn.disabled = true;
        alertBox.classList.add('hidden');

        try {
            const res = await fetch('/api/forgot-password', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ email })
            });
            const data = await res.json();
            if(res.ok && data.success) {
                // Next step
                document.getElementById('forgotStep1').classList.add('hidden');
                document.getElementById('forgotStep2').classList.remove('hidden');
                document.getElementById('resetAlertBox').classList.add('hidden');
                // For testing, alert the OTP
                alert('Test Mode OTP (Check Mailtrap in real app): ' + data.data.otp);
            } else {
                if (data.errors && data.errors.email) {
                    alertBox.textContent = data.errors.email[0];
                } else {
                    alertBox.textContent = data.message || 'Failed to send OTP.';
                }
                alertBox.classList.remove('hidden');
            }
        } catch (e) {
            alertBox.textContent = 'Network error.';
            alertBox.classList.remove('hidden');
        } finally {
            btn.innerHTML = 'Send OTP';
            btn.disabled = false;
        }
    }

    async function resetPassword() {
        const email = document.getElementById('forgotEmail').value;
        const otp = document.getElementById('resetOtp').value;
        const password = document.getElementById('resetPasswordInput').value;
        const password_confirmation = document.getElementById('resetPasswordConfirm').value;
        const btn = document.getElementById('resetPassBtn');
        const alertBox = document.getElementById('resetAlertBox');

        btn.innerHTML = 'Resetting...';
        btn.disabled = true;
        alertBox.classList.add('hidden');

        try {
            const res = await fetch('/api/reset-password', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ email, otp, password, password_confirmation })
            });
            const data = await res.json();
            if(res.ok && data.success) {
                alert('Password reset successful! You can now login.');
                closeForgotModal();
            } else {
                if(data.errors) {
                    alertBox.textContent = Object.values(data.errors).flat().join('\n');
                } else {
                    alertBox.textContent = data.message || 'Failed to reset password.';
                }
                alertBox.classList.remove('hidden');
            }
        } catch (e) {
            alertBox.textContent = 'Network error.';
            alertBox.classList.remove('hidden');
        } finally {
            btn.innerHTML = 'Reset Password';
            btn.disabled = false;
        }
    }

    function togglePassword() {
        const input = document.getElementById('password');
        const icon = document.getElementById('eyeIcon');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    }

    function clearErrors() {
        // Hide all error messages
        document.querySelectorAll('.error-msg').forEach(el => {
            el.textContent = '';
            el.classList.add('hidden');
        });
        
        // Remove red borders from inputs
        document.querySelectorAll('.form-input').forEach(el => {
            el.classList.remove('border-red-500', 'focus:ring-red-500/20');
            el.classList.add('border-gray-200', 'focus:ring-indigo-600/20', 'focus:border-indigo-600');
        });

        // Hide main alert box
        document.getElementById('alertBox').classList.add('hidden');
    }

    function showFieldError(field, message) {
        const errorEl = document.getElementById('error-' + field);
        const inputEl = document.getElementById(field);
        
        if (errorEl && inputEl) {
            errorEl.textContent = message;
            errorEl.classList.remove('hidden');
            
            inputEl.classList.remove('border-gray-200', 'focus:ring-indigo-600/20', 'focus:border-indigo-600');
            inputEl.classList.add('border-red-500', 'focus:ring-red-500/20');
        }
    }

    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('submitBtn');
        const alertBox = document.getElementById('alertBox');
        
        clearErrors();
        
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Processing...';
        btn.disabled = true;

        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('/api/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok && result.success) {
                const rememberMe = document.getElementById('rememberMe').checked;
                
                // Save token and user
                if (rememberMe) {
                    localStorage.setItem('auth_token', result.data.token);
                    localStorage.setItem('user', JSON.stringify(result.data.user));
                } else {
                    sessionStorage.setItem('auth_token', result.data.token);
                    sessionStorage.setItem('user', JSON.stringify(result.data.user));
                }
                
                // Role-based redirect
                const role = result.data.user.role;
                if (role === 'owner' || role === 'staff' || role === 'trainer') {
                    window.location.href = '/dashboard';
                } else if (role === 'member') {
                    window.location.href = '/dashboard'; // member dashboard (same layout, different widgets later)
                } else {
                    window.location.href = '/dashboard';
                }
            } else {
                if (response.status === 422 && result.errors) {
                    // Display inline validation errors
                    for (const field in result.errors) {
                        showFieldError(field, result.errors[field][0]);
                    }
                } else {
                    // General error
                    alertBox.textContent = result.message || 'Login failed. Please check your credentials.';
                    alertBox.classList.remove('hidden');
                }
            }
        } catch (error) {
            alertBox.textContent = 'An error occurred. Please try again.';
            alertBox.classList.remove('hidden');
        } finally {
            btn.innerHTML = '<span>Sign In</span> <i class="fa-solid fa-arrow-right text-sm"></i>';
            btn.disabled = false;
        }
    });
</script>
@endpush
@endsection
