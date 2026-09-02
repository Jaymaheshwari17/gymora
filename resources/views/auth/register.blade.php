@extends('layouts.app')

@section('content')
<div class="min-h-screen flex w-full bg-white">
    
    <!-- Left Panel -->
    <div class="hidden lg:flex w-1/2 relative bg-black items-center justify-center overflow-hidden">
        <img src="/background/flexvora1.png" alt="Gym Background" class="absolute inset-0 w-full h-full object-cover opacity-60">
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
    <div class="w-full lg:w-1/2 flex flex-col items-center justify-center p-4 sm:p-8 relative h-screen overflow-hidden">


        <div class="w-full max-w-xl max-h-[90vh] overflow-y-auto hide-scrollbar px-2 sm:px-4">
            
            <div class="text-center mb-6">
                <!-- Logo -->
                <div class="flex items-center justify-center gap-2.5 text-2xl font-black tracking-tight text-gray-900 mb-2">
                    <img src="{{ asset('flexvora.png') }}" class="w-8 h-8 object-cover rounded-xl shadow-sm" alt="Flexvora">
                    <span>FLEXVORA</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1">Create Account</h1>
                <p class="text-gray-500 text-sm">Join Flexvora and start your fitness journey</p>
            </div>

            <!-- General Alert Box -->
            <div id="alertBox" class="hidden mb-4 p-3 rounded-lg text-sm bg-red-50 text-red-600 border border-red-200"></div>

            <form id="registerForm" class="space-y-4" novalidate>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Full Name -->
                    <div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <i class="fa-regular fa-user"></i>
                            </div>
                            <input type="text" id="name" name="name"
                                class="form-input w-full pl-10 pr-3 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-600/20 focus:border-indigo-600 text-gray-900 placeholder-gray-400 transition-all outline-none shadow-sm text-sm"
                                placeholder="Full Name">
                        </div>
                        <p id="error-name" class="error-msg hidden text-red-500 text-xs mt-1 ml-1"></p>
                    </div>

                    <!-- Mobile -->
                    <div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <i class="fa-solid fa-phone text-sm"></i>
                            </div>
                            <input type="tel" id="mobile" name="mobile" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                class="form-input w-full pl-10 pr-3 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-600/20 focus:border-indigo-600 text-gray-900 placeholder-gray-400 transition-all outline-none shadow-sm text-sm"
                                placeholder="Mobile Number">
                        </div>
                        <p id="error-mobile" class="error-msg hidden text-red-500 text-xs mt-1 ml-1"></p>
                    </div>

                    <!-- Email -->
                    <div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <i class="fa-regular fa-envelope"></i>
                            </div>
                            <input type="email" id="email" name="email"
                                class="form-input w-full pl-10 pr-3 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-600/20 focus:border-indigo-600 text-gray-900 placeholder-gray-400 transition-all outline-none shadow-sm text-sm"
                                placeholder="Email Address">
                        </div>
                        <p id="error-email" class="error-msg hidden text-red-500 text-xs mt-1 ml-1"></p>
                    </div>

                    <!-- Gym Name -->
                    <div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <i class="fa-regular fa-building"></i>
                            </div>
                            <input type="text" id="gym_name" name="gym_name"
                                class="form-input w-full pl-10 pr-3 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-600/20 focus:border-indigo-600 text-gray-900 placeholder-gray-400 transition-all outline-none shadow-sm text-sm"
                                placeholder="Gym Name">
                        </div>
                        <p id="error-gym_name" class="error-msg hidden text-red-500 text-xs mt-1 ml-1"></p>
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <i class="fa-solid fa-lock text-sm"></i>
                            </div>
                            <input type="password" id="password" name="password" oninput="checkPasswordStrength()"
                                class="form-input w-full pl-10 pr-10 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-600/20 focus:border-indigo-600 text-gray-900 placeholder-gray-400 transition-all outline-none shadow-sm text-sm"
                                placeholder="Password">
                            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center cursor-pointer text-gray-400 hover:text-gray-600" onclick="togglePassword('password', 'eye1')">
                                <i class="fa-regular fa-eye-slash" id="eye1"></i>
                            </div>
                        </div>
                        <p id="error-password" class="error-msg hidden text-red-500 text-xs mt-1 ml-1"></p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <i class="fa-solid fa-lock text-sm"></i>
                            </div>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                class="form-input w-full pl-10 pr-10 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-600/20 focus:border-indigo-600 text-gray-900 placeholder-gray-400 transition-all outline-none shadow-sm text-sm"
                                placeholder="Confirm Password">
                            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center cursor-pointer text-gray-400 hover:text-gray-600" onclick="togglePassword('password_confirmation', 'eye2')">
                                <i class="fa-regular fa-eye-slash" id="eye2"></i>
                            </div>
                        </div>
                        <p id="error-password_confirmation" class="error-msg hidden text-red-500 text-xs mt-1 ml-1"></p>
                    </div>
                </div>

                <!-- Compact Password Strength -->
                <div class="bg-gray-50 rounded-lg p-2.5 border border-gray-100">
                    <div class="flex justify-between items-center mb-1.5">
                        <span class="text-xs text-gray-500 font-medium">Password Strength</span>
                        <span id="strengthText" class="text-xs font-semibold text-gray-400">Weak</span>
                    </div>
                    <div class="flex gap-1 h-1 w-full bg-gray-200 rounded-full overflow-hidden mb-2">
                        <div id="bar1" class="h-full w-1/4 transition-all duration-300 bg-gray-200"></div>
                        <div id="bar2" class="h-full w-1/4 transition-all duration-300 bg-gray-200"></div>
                        <div id="bar3" class="h-full w-1/4 transition-all duration-300 bg-gray-200"></div>
                        <div id="bar4" class="h-full w-1/4 transition-all duration-300 bg-gray-200"></div>
                    </div>
                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-[10px] text-gray-400">
                        <div class="flex items-center gap-1" id="req-len">
                            <i class="fa-regular fa-circle"></i> 8+ chars
                        </div>
                        <div class="flex items-center gap-1" id="req-low">
                            <i class="fa-regular fa-circle"></i> Lowercase
                        </div>
                        <div class="flex items-center gap-1" id="req-up">
                            <i class="fa-regular fa-circle"></i> Uppercase
                        </div>
                        <div class="flex items-center gap-1" id="req-num">
                            <i class="fa-regular fa-circle"></i> Number/Special
                        </div>
                    </div>
                </div>

                <!-- Terms -->
                <div class="flex items-start text-xs pt-1">
                    <label class="flex items-start text-gray-600 cursor-pointer pt-0.5 relative">
                        <input type="checkbox" id="terms" name="terms" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-600 w-3.5 h-3.5 mr-2 mt-0.5">
                        <span>I agree to the <a href="#" class="text-indigo-600 font-medium hover:text-indigo-700">Terms</a> and <a href="#" class="text-indigo-600 font-medium hover:text-indigo-700">Privacy Policy</a></span>
                    </label>
                </div>
                <p id="error-terms" class="error-msg hidden text-red-500 text-xs ml-6"></p>

                <!-- Submit -->
                <button type="submit" id="submitBtn"
                    class="w-full py-3 bg-indigo-600 text-white rounded-xl font-medium shadow-md shadow-indigo-600/30 hover:bg-indigo-700 hover:shadow-lg transition-all flex items-center justify-center gap-2 mt-2">
                    <span>Sign Up</span>
                    <i class="fa-solid fa-arrow-right text-sm"></i>
                </button>
            </form>

            <div class="text-center text-gray-600 text-sm mt-5 pb-2">
                Already have an account? 
                <a href="/login" class="text-indigo-600 font-medium hover:text-indigo-700 ml-1">Sign In</a>
            </div>
        </div>
    </div>
</div>

<style>
/* Hide scrollbar for neatness but keep functionality */
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>

@push('scripts')
<script>
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
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

    function checkPasswordStrength() {
        const val = document.getElementById('password').value;
        let strength = 0;
        
        const hasLen = val.length >= 8;
        const hasLow = /[a-z]/.test(val);
        const hasUp = /[A-Z]/.test(val);
        const hasNumSpec = /[0-9]/.test(val) && /[^a-zA-Z0-9]/.test(val);

        if(hasLen) strength++;
        if(hasLow) strength++;
        if(hasUp) strength++;
        if(hasNumSpec) strength++;

        updateReqUi('req-len', hasLen);
        updateReqUi('req-low', hasLow);
        updateReqUi('req-up', hasUp);
        updateReqUi('req-num', hasNumSpec);

        const bars = ['bar1', 'bar2', 'bar3', 'bar4'];
        bars.forEach(b => document.getElementById(b).className = 'h-full w-1/4 transition-all duration-300 bg-gray-200');
        
        const stText = document.getElementById('strengthText');
        
        if (strength === 0) {
            stText.textContent = 'Weak';
            stText.className = 'text-xs font-semibold text-gray-400';
        } else if (strength <= 2) {
            stText.textContent = 'Weak';
            stText.className = 'text-xs font-semibold text-red-500';
            for(let i=0; i<strength; i++) document.getElementById(bars[i]).classList.replace('bg-gray-200', 'bg-red-500');
        } else if (strength === 3) {
            stText.textContent = 'Good';
            stText.className = 'text-xs font-semibold text-orange-400';
            for(let i=0; i<strength; i++) document.getElementById(bars[i]).classList.replace('bg-gray-200', 'bg-orange-400');
        } else {
            stText.textContent = 'Strong';
            stText.className = 'text-xs font-semibold text-green-500';
            for(let i=0; i<strength; i++) document.getElementById(bars[i]).classList.replace('bg-gray-200', 'bg-green-500');
        }
    }

    function updateReqUi(id, isValid) {
        const el = document.getElementById(id);
        const icon = el.querySelector('i');
        if (isValid) {
            el.classList.replace('text-gray-400', 'text-green-500');
            icon.classList.replace('fa-circle', 'fa-check-circle');
            icon.classList.replace('fa-regular', 'fa-solid');
        } else {
            el.classList.replace('text-green-500', 'text-gray-400');
            icon.classList.replace('fa-check-circle', 'fa-circle');
            icon.classList.replace('fa-solid', 'fa-regular');
        }
    }

    function clearErrors() {
        document.querySelectorAll('.error-msg').forEach(el => {
            el.textContent = '';
            el.classList.add('hidden');
        });
        
        document.querySelectorAll('.form-input').forEach(el => {
            el.classList.remove('border-red-500', 'focus:ring-red-500/20');
            el.classList.add('border-gray-200', 'focus:ring-indigo-600/20', 'focus:border-indigo-600');
        });

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

    document.getElementById('registerForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        clearErrors();
        const alertBox = document.getElementById('alertBox');
        const btn = document.getElementById('submitBtn');
        
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Processing...';
        btn.disabled = true;

        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('/api/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok && result.success) {
                localStorage.setItem('auth_token', result.data.token);
                localStorage.setItem('user', JSON.stringify(result.data.user));
                window.location.href = '/dashboard';
            } else {
                if (response.status === 422 && result.errors) {
                    for (const field in result.errors) {
                        showFieldError(field, result.errors[field][0]);
                    }
                } else {
                    let errorMsg = result.message || 'Registration failed.';
                    alertBox.textContent = errorMsg;
                    alertBox.classList.remove('hidden');
                }
            }
        } catch (error) {
            alertBox.textContent = 'An error occurred. Please try again.';
            alertBox.classList.remove('hidden');
        } finally {
            btn.innerHTML = '<span>Sign Up</span> <i class="fa-solid fa-arrow-right text-sm"></i>';
            btn.disabled = false;
        }
    });
</script>
@endpush
@endsection
