@extends('layouts.dashboard-layout')

@section('dashboard-content')
<div class="flex-1 overflow-y-auto p-8 bg-[#f8f9fc]">
    <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Gym Members</h1>
            <p class="text-sm text-gray-500 mt-1 font-medium">Manage all your members and their subscriptions.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <!-- Filter 1: Membership Status -->
            <div class="flex items-center gap-1.5 bg-white px-3 py-1.5 rounded-xl border border-gray-200 shadow-2xs">
                <i class="fa-solid fa-circle-dot text-indigo-500 text-xs"></i>
                <select id="member-status-filter" onchange="renderTable()" class="text-xs font-bold text-gray-700 bg-transparent outline-none cursor-pointer">
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="expired">Expired</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <!-- Filter 2: Plan / Package -->
            <div class="flex items-center gap-1.5 bg-white px-3 py-1.5 rounded-xl border border-gray-200 shadow-2xs">
                <i class="fa-solid fa-dumbbell text-indigo-500 text-xs"></i>
                <select id="member-plan-filter" onchange="renderTable()" class="text-xs font-bold text-gray-700 bg-transparent outline-none cursor-pointer max-w-[160px] truncate">
                    <option value="all">All Plans</option>
                    <!-- Populated dynamically via JS -->
                </select>
            </div>

            <button onclick="openWizardModal()" class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-700 transition flex items-center gap-2 shadow-lg shadow-indigo-600/20 cursor-pointer">
                <i class="fa-solid fa-user-plus"></i> Add New Member
            </button>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden p-6">
        <table id="membersTable" class="w-full text-left border-collapse" width="100%">
            <thead>
                <tr>
                    <th class="w-16 text-center">Sr No</th>
                    <th>Member</th>
                    <th>Contact</th>
                    <th>Plan & Batch</th>
                    <th>Status</th>
                    <th class="text-center w-32">Actions</th>
                </tr>
            </thead>
            <tbody id="members-tbody">
                <!-- Data will be loaded here via JS -->
            </tbody>
        </table>
    </div>
    </div>
</div>

<!-- Renew Plan Modal -->
<div id="renew-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" onclick="closeRenewModal()"></div>
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden flex flex-col max-h-[92vh]">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
            <div>
                <h3 class="text-base font-extrabold text-gray-900">Renew / Extend Plan</h3>
                <p class="text-xs text-gray-500 font-medium mt-0.5" id="renew-member-name">Member Name</p>
            </div>
            <button onclick="closeRenewModal()" class="w-8 h-8 rounded-full bg-white border border-gray-200 text-gray-400 hover:text-gray-800 hover:bg-gray-100 flex items-center justify-center transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <!-- Body -->
        <div class="p-6 overflow-y-auto flex-1">
            <form id="renew-form" onsubmit="submitRenew(event)">
                <input type="hidden" id="renew_member_id">
                
                <div class="space-y-4">
                    <!-- Current Plan info -->
                    <div class="p-3.5 bg-indigo-50/70 border border-indigo-100 rounded-xl text-indigo-900 text-xs">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold uppercase tracking-wider text-[10px] text-indigo-600">Current Plan & Paid</span>
                            <span id="renew-current-badge" class="font-black px-2 py-0.5 rounded text-[10px] bg-indigo-100 text-indigo-700">Active</span>
                        </div>
                        <div class="font-extrabold text-sm text-gray-900" id="renew-current-plan">3 Months Cardio</div>
                        <div class="flex items-center justify-between text-gray-500 font-medium text-xs mt-0.5">
                            <span id="renew-current-validity">Valid: 01 Jan 2026 to 01 Apr 2026</span>
                            <span class="font-bold text-emerald-600">Paid: ₹<span id="renew-current-paid-amount">0</span></span>
                        </div>
                    </div>

                    <!-- Action Type Choice -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">Action Mode</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center gap-2 p-2.5 bg-indigo-50/70 border border-indigo-200 rounded-xl text-xs font-bold cursor-pointer hover:bg-indigo-100 text-indigo-900" id="type-upgrade-label">
                                <input type="radio" name="renew_action_type" value="upgrade" id="type-upgrade" checked onchange="handleActionTypeChange()" class="accent-[#5d5fef]">
                                <span>🔁 Upgrade / Change Plan</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold cursor-pointer hover:bg-gray-100 text-gray-700" id="type-renew-label">
                                <input type="radio" name="renew_action_type" value="renew" id="type-renew" onchange="handleActionTypeChange()" class="accent-[#5d5fef]">
                                <span>🔄 Next Cycle Renewal</span>
                            </label>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1 font-medium leading-tight" id="action-type-hint">
                            💡 <strong>Upgrade Mode:</strong> Previous payment is adjusted so only the difference is charged (no double payments!).
                        </p>
                    </div>

                    <!-- Select New Plan -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Select New Plan <span class="text-red-500">*</span></label>
                        <select id="renew_plan_id" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-600 outline-none text-xs font-semibold bg-white cursor-pointer" onchange="calculateRenewAmounts()">
                            <option value="">Select Plan</option>
                            <!-- Options injected by JS -->
                        </select>
                    </div>

                    <!-- Start Date Mode (Hidden for Upgrade, Visible for Next Cycle Renewal) -->
                    <div id="renew-start-date-container" class="hidden">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">Renewal Start Date Option</label>
                        <div class="grid grid-cols-2 gap-2 mb-2">
                            <label class="flex items-center gap-2 p-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold cursor-pointer hover:bg-gray-100" id="opt-extend-label">
                                <input type="radio" name="renew_mode" id="opt-extend" checked onchange="handleRenewModeChange()" class="accent-[#5d5fef]">
                                <span>Extend Seamlessly</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold cursor-pointer hover:bg-gray-100" id="opt-custom-label">
                                <input type="radio" name="renew_mode" id="opt-custom" onchange="handleRenewModeChange()" class="accent-[#5d5fef]">
                                <span>Start Today / Custom</span>
                            </label>
                        </div>
                        <input type="date" id="renew_start_date" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-600 outline-none text-xs font-bold bg-white" onchange="calculateRenewAmounts()">
                    </div>

                    <!-- Live New Expiry Preview Box -->
                    <div class="p-3 bg-emerald-50/80 border border-emerald-100 rounded-xl text-emerald-800 text-xs font-medium flex items-center justify-between" id="renew-new-expiry-box">
                        <span class="flex items-center gap-1.5 font-bold">
                            <i class="fa-solid fa-calendar-check text-emerald-600"></i> New Expiry Date:
                        </span>
                        <span class="font-black text-xs sm:text-sm text-emerald-700" id="renew-new-expiry-text">Select plan</span>
                    </div>

                    <!-- Financial Breakdown Box -->
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-xs space-y-1.5" id="fee-adjustment-box">
                        <div class="flex justify-between text-gray-600">
                            <span>New Plan Price:</span>
                            <span class="font-bold text-gray-900">₹<span id="calc-new-price">0</span></span>
                        </div>
                        <div class="flex justify-between text-emerald-600 font-semibold" id="row-prev-adjusted">
                            <span>Adjusted (Already Paid):</span>
                            <span>-₹<span id="calc-prev-adjusted">0</span></span>
                        </div>
                        <div class="flex justify-between text-gray-900 font-bold border-t border-gray-200 pt-1.5">
                            <span id="label-diff-collect">Difference to Collect:</span>
                            <span class="text-indigo-600 font-black text-sm">₹<span id="calc-net-diff">0</span></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-1">
                        <!-- Discount -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Discount (₹)</label>
                            <input type="number" id="renew_discount" min="0" value="0" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-600 outline-none text-xs font-bold" oninput="calculateRenewAmounts()">
                        </div>
                        
                        <!-- Paid Amount -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Amount Paid Now (₹) <span class="text-red-500">*</span></label>
                            <input type="number" id="renew_paid" required min="0" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-600 outline-none text-xs font-black text-emerald-600">
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Footer -->
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/80 flex justify-end gap-3 shrink-0">
            <button type="button" onclick="closeRenewModal()" class="px-4 py-2 text-gray-600 hover:text-gray-900 font-bold text-xs rounded-xl transition">Cancel</button>
            <button type="submit" form="renew-form" id="btn-renew-submit" class="px-5 py-2.5 bg-[#5d5fef] hover:bg-[#4d4fe0] text-white text-xs font-black rounded-xl shadow-md shadow-[#5d5fef]/25 transition-all">Confirm Renewal</button>
        </div>
    </div>
</div>

<!-- Multi-Step Wizard Modal -->
<div id="wizard-modal" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" onclick="closeWizardModal()"></div>
    
    <!-- Modal Content -->
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-4xl bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden flex flex-col max-h-[90vh]">
        
        <!-- Modal Header with Progress -->
        <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-xl font-bold text-gray-900" id="wizard-title">New Member Registration</h3>
                    <p class="text-sm text-gray-500 mt-1 font-medium">Please fill in the details below</p>
                </div>
                <button onclick="closeWizardModal()" class="w-8 h-8 rounded-full bg-white border border-gray-200 text-gray-400 hover:text-gray-800 hover:bg-gray-100 flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Progress Bar / Stepper -->
            <div class="flex items-center justify-between relative mt-4">
                <!-- Connecting Line -->
                <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-full h-1 bg-gray-200 rounded-full z-0"></div>
                <div id="progress-line" class="absolute left-0 top-1/2 transform -translate-y-1/2 h-1 bg-indigo-600 rounded-full z-0 transition-all duration-300" style="width: 0%;"></div>
                
                <!-- Step 1 -->
                <div class="relative z-10 flex flex-col items-center step-indicator" data-step="1">
                    <div class="w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold shadow-md shadow-indigo-600/20 border-4 border-white transition-colors duration-300">1</div>
                    <span class="text-xs font-bold text-indigo-600 mt-2 absolute -bottom-6 w-24 text-center">Personal Info</span>
                </div>
                <!-- Step 2 -->
                <div class="relative z-10 flex flex-col items-center step-indicator" data-step="2">
                    <div class="w-10 h-10 rounded-full bg-gray-200 text-gray-400 flex items-center justify-center font-bold border-4 border-white transition-colors duration-300">2</div>
                    <span class="text-xs font-bold text-gray-400 mt-2 absolute -bottom-6 w-24 text-center">Account</span>
                </div>
                <!-- Step 3 -->
                <div class="relative z-10 flex flex-col items-center step-indicator" data-step="3">
                    <div class="w-10 h-10 rounded-full bg-gray-200 text-gray-400 flex items-center justify-center font-bold border-4 border-white transition-colors duration-300">3</div>
                    <span class="text-xs font-bold text-gray-400 mt-2 absolute -bottom-6 w-32 text-center">Plan & Payment</span>
                </div>
                <!-- Step 4 -->
                <div class="relative z-10 flex flex-col items-center step-indicator" data-step="4">
                    <div class="w-10 h-10 rounded-full bg-gray-200 text-gray-400 flex items-center justify-center font-bold border-4 border-white transition-colors duration-300">4</div>
                    <span class="text-xs font-bold text-gray-400 mt-2 absolute -bottom-6 w-24 text-center">Assignment</span>
                </div>
            </div>
            <div class="h-6"></div> <!-- Spacer for absolute text -->
        </div>

        <!-- Modal Body (Form Steps) -->
        <div class="p-8 overflow-y-auto flex-1 bg-white">
            <form id="member-form">
                <input type="hidden" id="member_id">
                
                <!-- STEP 1: Personal Info -->
                <div id="step-1" class="wizard-step animate-fade-in block">
                    <h4 class="text-base font-bold text-gray-800 mb-5 flex items-center gap-2">
                        <i class="fa-regular fa-address-card text-indigo-600"></i> Personal Details
                    </h4>
                    
                    <!-- Photo Upload -->
                    <div class="flex items-center gap-5 mb-6">
                        <div class="w-20 h-20 rounded-full bg-gray-100 flex flex-col items-center justify-center text-gray-400 overflow-hidden relative shadow-sm border border-gray-200 shrink-0">
                            <img id="photo-preview" src="" class="absolute inset-0 w-full h-full object-cover hidden">
                            <i class="fa-solid fa-camera text-xl mb-1"></i>
                            <span class="text-[9px] uppercase font-bold tracking-wider">Photo</span>
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Profile Photo (Optional)</label>
                            <input type="file" id="photo" accept="image/jpeg,image/png,image/gif" onchange="previewImage(this)" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 transition-all cursor-pointer">
                            <p id="error-photo" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" id="name" placeholder="Enter member name" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all">
                            <p id="error-name" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Mobile Number <span class="text-red-500">*</span></label>
                            <input type="text" id="mobile" placeholder="10-digit mobile number" maxlength="10" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all">
                            <p id="error-mobile" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email Address <span class="text-red-500">*</span></label>
                            <input type="email" id="email" placeholder="Email address" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all">
                            <p id="error-email" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Gender</label>
                                <select id="gender" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all">
                                    <option value="">Select</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Date of Birth</label>
                                <input type="date" id="dob" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Account Security -->
                <div id="step-2" class="wizard-step animate-fade-in hidden">
                    <h4 class="text-base font-bold text-gray-800 mb-5 flex items-center gap-2">
                        <i class="fa-solid fa-lock text-indigo-600"></i> Login Credentials
                    </h4>
                    <p class="text-sm text-gray-500 mb-6 bg-indigo-50 text-indigo-700 p-4 rounded-xl border border-purple-100 flex items-start gap-3">
                        <i class="fa-solid fa-circle-info mt-0.5"></i>
                        <span>Set a password for the member to log into the Gymora App. They can change it later.</span>
                    </p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Password <span class="text-red-500 password-req">*</span></label>
                            <div class="relative">
                                <input type="password" id="password" placeholder="••••••••" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all">
                                <button type="button" onclick="togglePasswordVisibility('password', 'eye-icon-1')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                    <i id="eye-icon-1" class="fa-solid fa-eye-slash"></i>
                                </button>
                            </div>
                            <p class="text-[10px] text-gray-500 mt-1 font-medium" id="password-hint">Min 8 chars, containing letters, numbers & symbols.</p>
                            <p id="error-password" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Confirm Password <span class="text-red-500 password-req">*</span></label>
                            <div class="relative">
                                <input type="password" id="password_confirmation" placeholder="••••••••" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all">
                                <button type="button" onclick="togglePasswordVisibility('password_confirmation', 'eye-icon-2')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                    <i id="eye-icon-2" class="fa-solid fa-eye-slash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6" id="status-container" style="display: none;">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Account Status</label>
                        <select id="status" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <!-- STEP 3: Plan & Payment -->
                <div id="step-3" class="wizard-step animate-fade-in hidden">
                    <h4 class="text-base font-bold text-gray-800 mb-5 flex items-center gap-2">
                        <i class="fa-solid fa-file-invoice-dollar text-indigo-600"></i> Membership Details
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Select Plan <span class="text-red-500">*</span></label>
                            <select id="plan_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all" onchange="calculateTotal()">
                                <option value="">Select a plan...</option>
                                <!-- Plans will load here -->
                            </select>
                            <p id="error-plan_id" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Joining Date <span class="text-red-500">*</span></label>
                            <input type="date" id="joining_date" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all">
                            <p id="error-joining_date" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-5 rounded-xl border border-gray-200 space-y-4 shadow-inner">
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-semibold text-gray-600">Plan Amount</span>
                            <div class="flex items-center gap-2">
                                <span class="text-gray-400">₹</span>
                                <input type="number" id="plan_amount" readonly class="w-32 bg-transparent text-right font-bold text-gray-900 outline-none text-base" value="0">
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-semibold text-gray-600">Discount Given</span>
                            <div class="flex items-center gap-2">
                                <span class="text-gray-400">₹</span>
                                <input type="number" id="discount" min="0" value="0" class="w-32 px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-right font-bold text-red-500 outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition-all" oninput="calculateTotal()">
                            </div>
                        </div>
                        <div class="pt-4 border-t border-gray-200 flex items-center justify-between">
                            <span class="font-bold text-gray-900 text-lg">Total Payable</span>
                            <div class="flex items-center gap-2">
                                <span class="text-gray-500 font-bold text-xl">₹</span>
                                <span id="total_amount_display" class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-indigo-400">0</span>
                            </div>
                        </div>
                        <div class="pt-4 border-t border-gray-200 flex items-center justify-between">
                            <span class="font-semibold text-gray-600">Amount Received Now</span>
                            <div class="flex items-center gap-2">
                                <span class="text-gray-400">₹</span>
                                <input type="number" id="amount_received" min="0" value="0" class="w-32 px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-right font-bold text-green-600 outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition-all" oninput="calculatePending()">
                            </div>
                        </div>
                        <div class="pt-4 border-t border-gray-200 flex items-center justify-between">
                            <span class="font-bold text-gray-900 text-lg">Pending Amount</span>
                            <div class="flex items-center gap-2">
                                <span class="text-gray-500 font-bold text-xl">₹</span>
                                <span id="pending_amount_display" class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-red-400">0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 4: Assignment -->
                <div id="step-4" class="wizard-step animate-fade-in hidden">
                    <h4 class="text-base font-bold text-gray-800 mb-5 flex items-center gap-2">
                        <i class="fa-solid fa-dumbbell text-indigo-600"></i> Training Assignments
                    </h4>
                    <p class="text-sm text-gray-500 mb-6 bg-gray-50 p-4 rounded-xl border border-gray-100 flex items-start gap-3">
                        <i class="fa-solid fa-circle-info mt-0.5"></i>
                        <span>Optional: Assign the member to a specific batch and personal trainer if applicable.</span>
                    </p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Assign Batch</label>
                            <select id="batch_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all">
                                <option value="">No specific batch</option>
                                <!-- Batches will load here -->
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Assign Trainer</label>
                            <select id="trainer_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all">
                                <option value="">No personal trainer</option>
                                <!-- Trainers will load here -->
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="px-8 py-5 border-t border-gray-100 bg-gray-50 flex justify-between items-center rounded-b-2xl">
            <button type="button" id="btn-prev" onclick="changeStep(-1)" class="px-6 py-2.5 rounded-xl text-sm font-semibold text-gray-600 bg-white border border-gray-200 hover:bg-gray-100 transition hidden shadow-sm">
                <i class="fa-solid fa-arrow-left mr-2"></i> Previous
            </button>
            <div class="flex-1"></div> <!-- Spacer -->
            <button type="button" id="btn-next" onclick="changeStep(1)" class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-gray-800 hover:bg-gray-900 transition flex items-center gap-2 shadow-md">
                Next <i class="fa-solid fa-arrow-right"></i>
            </button>
            <button type="button" id="btn-save" onclick="saveMember()" class="px-8 py-2.5 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-indigo-400 hover:opacity-90 transition shadow-lg shadow-indigo-600/30 items-center gap-2 hidden">
                <i class="fa-solid fa-check"></i> Complete Registration
            </button>
        </div>
    </div>
</div>

<!-- View Member Card Modal -->
<div id="view-member-modal" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeViewModal()"></div>
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden relative z-10 w-full max-w-md flex flex-col transform transition-all">
        <!-- Close btn -->
        <button onclick="closeViewModal()" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-black/20 text-white hover:bg-black/40 flex items-center justify-center transition z-20 backdrop-blur-md">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <!-- Header Banner -->
        <div id="view-card-banner" class="h-32 bg-gradient-to-r from-green-400 to-green-600 relative transition-colors duration-300">
            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-20"></div>
            <div class="absolute -bottom-12 left-1/2 transform -translate-x-1/2">
                <img id="view-card-photo" src="" class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg bg-white">
            </div>
            <div class="absolute top-4 left-4 bg-white/20 backdrop-blur-md text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">
                <i class="fa-solid fa-id-card"></i> <span id="view-card-id">MEM-0001</span>
            </div>
            <div class="absolute bottom-4 right-4">
                <span id="view-card-status" class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-white shadow-sm text-green-600 transition-colors duration-300">Active</span>
            </div>
        </div>

        <div class="pt-14 pb-6 px-6 flex flex-col items-center text-center">
            <h3 id="view-card-name" class="text-2xl font-black text-gray-900 leading-tight">Name</h3>
            <div class="flex items-center gap-3 text-sm font-medium text-gray-500 mt-2">
                <div class="flex items-center gap-1"><i class="fa-solid fa-phone text-gray-400 text-xs"></i> <span id="view-card-mobile">Mobile</span></div>
                <span class="text-gray-300">&bull;</span>
                <div class="flex items-center gap-1"><i class="fa-solid fa-envelope text-gray-400 text-xs"></i> <span id="view-card-email">Email</span></div>
            </div>
            <div class="flex items-center justify-center gap-4 text-xs font-semibold text-gray-400 mt-3 border-t border-gray-100 pt-3 w-full">
                <div class="flex items-center gap-1.5"><i class="fa-solid fa-venus-mars text-indigo-600/50"></i> <span id="view-card-gender" class="capitalize">N/A</span></div>
                <div class="w-1 h-1 rounded-full bg-gray-300"></div>
                <div class="flex items-center gap-1.5"><i class="fa-solid fa-cake-candles text-indigo-600/50"></i> <span id="view-card-dob">N/A</span></div>
            </div>
        </div>
        
        <div class="bg-gray-50 p-6 space-y-4 border-t border-gray-100">
            <!-- Membership Info -->
            <div>
                <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 px-1">Membership Details</h4>
                <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm space-y-3">
                    <div class="flex justify-between items-center text-xs sm:text-sm">
                        <span class="text-gray-500 font-medium"><i class="fa-solid fa-star text-yellow-400 w-4 text-center mr-1.5"></i> Active Plan</span>
                        <span id="view-card-plan" class="font-bold text-gray-900">N/A</span>
                    </div>
                    <div class="flex justify-between items-center text-xs sm:text-sm">
                        <span class="text-gray-500 font-medium"><i class="fa-solid fa-calendar-days text-indigo-400 w-4 text-center mr-1.5"></i> Plan Validity</span>
                        <span id="view-card-validity" class="font-bold text-indigo-700">N/A</span>
                    </div>
                    <div class="flex justify-between items-center text-xs sm:text-sm">
                        <span class="text-gray-500 font-medium"><i class="fa-solid fa-clock-rotate-left text-blue-400 w-4 text-center mr-1.5"></i> Joined Gym</span>
                        <span id="view-card-joined" class="font-bold text-gray-900">N/A</span>
                    </div>
                    <div class="flex justify-between items-center text-xs sm:text-sm">
                        <span class="text-gray-500 font-medium"><i class="fa-solid fa-coins text-emerald-400 w-4 text-center mr-1.5"></i> Lifetime Paid</span>
                        <span class="font-bold text-green-600">₹<span id="view-card-paid">0</span></span>
                    </div>
                </div>
            </div>
            
            <!-- Assignments -->
            <div>
                <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 px-1">Assignments</h4>
                <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm space-y-4 text-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center shrink-0"><i class="fa-solid fa-users"></i></div>
                        <div class="flex-1">
                            <div class="text-xs text-gray-400 font-medium uppercase tracking-wider mb-0.5">Batch</div>
                            <div id="view-card-batch" class="font-bold text-gray-900 leading-tight">N/A</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-500 flex items-center justify-center shrink-0"><i class="fa-solid fa-dumbbell"></i></div>
                        <div class="flex-1">
                            <div class="text-xs text-gray-400 font-medium uppercase tracking-wider mb-0.5">Trainer</div>
                            <div id="view-card-trainer" class="font-bold text-gray-900 leading-tight">N/A</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('page-scripts')
<script>
    let membersData = [];
    let plansData = [];
    let batchesData = [];
    let trainersData = [];
    let dataTable = null;
    let currentStep = 1;
    let isEditing = false;
    const totalSteps = 4;

    // Wait for DOM
    document.addEventListener('DOMContentLoaded', () => {
        // Set today as default joining date
        document.getElementById('joining_date').valueAsDate = new Date();
        loadInitialData();
    });

    async function loadInitialData() {
        showLoader();
        try {
            // Load all necessary dropdown data & members list
            await Promise.all([
                fetchMembers(),
                fetchPlans(),
                fetchBatches(),
                fetchTrainers()
            ]);
        } catch (error) {
            console.error(error);
            showError("Failed to load some resources.");
        } finally {
            hideLoader();
        }
    }

    // ---- API Fetches ----
    async function fetchMembers() {
        const res = await fetch('/api/members', { headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' } });
        const data = await res.json();
        if (data.success) {
            membersData = data.data;
            renderTable();
        }
    }

    async function fetchPlans() {
        const res = await fetch('/api/plans', { headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' } });
        const data = await res.json();
        if (data.success) {
            // Flatten the plans to show each duration
            let flatPlans = [];
            Object.keys(data.data).forEach(groupName => {
                const plans = data.data[groupName];
                plans.forEach(p => {
                    flatPlans.push({
                        ...p,
                        display_name: `${groupName} - ${p.duration_months} Month(s)`
                    });
                });
            });
            plansData = flatPlans;
            
            // Populate modal plan select
            const select = document.getElementById('plan_id');
            select.innerHTML = '<option value="">Select a plan...</option>';
            plansData.forEach(p => {
                select.innerHTML += `<option value="${p.id}" data-amount="${p.amount}">${p.display_name} (₹${p.amount})</option>`;
            });

            // Populate top header filter select
            const filterSelect = document.getElementById('member-plan-filter');
            if (filterSelect) {
                const currentVal = filterSelect.value || 'all';
                filterSelect.innerHTML = '<option value="all">All Plans</option>';
                plansData.forEach(p => {
                    filterSelect.innerHTML += `<option value="${p.id}">${p.display_name}</option>`;
                });
                filterSelect.value = currentVal;
            }
        }
    }

    async function fetchBatches() {
        const res = await fetch('/api/batches', { headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' } });
        const data = await res.json();
        if (data.success) {
            batchesData = data.data;
            const select = document.getElementById('batch_id');
            select.innerHTML = '<option value="">No specific batch</option>';
            batchesData.forEach(b => {
                const time = b.start_time ? ` (${formatTime(b.start_time)} - ${formatTime(b.end_time)})` : '';
                select.innerHTML += `<option value="${b.id}">${b.name}${time}</option>`;
            });
        }
    }

    async function fetchTrainers() {
        const res = await fetch('/api/staff-trainers', { headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' } });
        const data = await res.json();
        if (data.success) {
            // Filter trainers only
            trainersData = data.data.filter(u => u.role === 'trainer');
            const select = document.getElementById('trainer_id');
            select.innerHTML = '<option value="">No personal trainer</option>';
            trainersData.forEach(t => {
                select.innerHTML += `<option value="${t.id}">${t.name}</option>`;
            });
        }
    }

    // Formatter
    function formatTime(timeStr) {
        if (!timeStr) return '';
        const [h, m] = timeStr.split(':');
        const date = new Date();
        date.setHours(h, m, 0);
        return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
    }

    // ---- DataTable Rendering ----
    function renderTable() {
        if (dataTable) {
            dataTable.destroy();
        }

        const statusFilter = document.getElementById('member-status-filter')?.value || 'all';
        const planFilter = document.getElementById('member-plan-filter')?.value || 'all';

        const filteredMembers = membersData.filter(member => {
            if (statusFilter !== 'all' && member.status !== statusFilter) return false;
            if (planFilter !== 'all' && String(member.plan_id) !== String(planFilter)) return false;
            return true;
        });

        const tbody = document.getElementById('members-tbody');
        let html = '';
        
        filteredMembers.forEach((member, index) => {
            const user = member.user || {};
            const plan = member.plan || {};
            const batch = member.batch || null;
            
            const statusClass = member.status === 'active' ? 'bg-green-100 text-green-700' : 
                               (member.status === 'expired' ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700');
            
            const photoUrl = user.photo ? `/${user.photo}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name || 'U')}&background=f3f4f6&color=6b7280`;

            const planDisplay = plan && plan.plan_group_name ? `<div class="font-bold text-gray-800">${plan.plan_group_name} - ${plan.duration_months}M</div>` : '<div class="text-gray-400 text-sm">No Plan</div>';
            const batchDisplay = batch ? `<div class="text-[11px] text-indigo-600 font-bold mt-1 bg-indigo-50 inline-block px-2 py-0.5 rounded border border-purple-100"><i class="fa-solid fa-layer-group"></i> ${batch.name}</div>` : '';
            
            const joiningDate = new Date(member.joining_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });

            html += `
                <tr>
                    <td class="font-bold text-gray-500 text-center">${index + 1}</td>
                    <td>
                        <div class="flex items-center gap-3">
                            <img src="${photoUrl}" class="w-10 h-10 rounded-full object-cover shadow-sm border border-gray-100">
                            <div>
                                <div class="font-bold text-gray-900">${user.name || 'N/A'}</div>
                                <div class="text-xs text-gray-500 mt-0.5"><i class="fa-regular fa-envelope"></i> ${user.email || 'N/A'}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="text-sm font-medium text-gray-900"><i class="fa-solid fa-phone text-gray-400 w-4"></i> ${user.mobile || 'N/A'}</div>
                    </td>
                    <td>
                        ${planDisplay}
                        ${batchDisplay}
                    </td>
                    <td>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider ${statusClass}">
                            ${member.status}
                        </span>
                        <div class="text-[10px] text-gray-400 mt-1.5 font-medium">Joined: ${joiningDate}</div>
                    </td>
                    <td class="text-center">
                        <div class="flex justify-center gap-2">
                            <button onclick='openRenewModal(${member.id})' class="w-10 h-10 rounded-lg bg-orange-50 text-orange-600 hover:bg-orange-100 flex items-center justify-center transition shadow-sm cursor-pointer" title="Renew / Extend Plan">
                                <i class="fa-solid fa-arrows-rotate text-sm"></i>
                            </button>
                            <button onclick='viewMember(${JSON.stringify(member).replace(/'/g, "&#39;")})' class="w-10 h-10 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 flex items-center justify-center transition shadow-sm" title="View Details">
                                <i class="fa-solid fa-eye text-sm"></i>
                            </button>
                            <button onclick='openEditWizard(${JSON.stringify(member).replace(/'/g, "&#39;")})' class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition shadow-sm" title="Edit">
                                <i class="fa-solid fa-pen text-sm"></i>
                            </button>
                            <button onclick='deleteMember(${member.id})' class="w-10 h-10 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center transition shadow-sm" title="Delete">
                                <i class="fa-solid fa-trash-can text-sm"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        tbody.innerHTML = html;

        // Initialize DataTable
        dataTable = $('#membersTable').DataTable({
            pageLength: 10,
            ordering: true,
            responsive: true,
            language: {
                search: "",
                searchPlaceholder: "Search members..."
            },
            columnDefs: [
                { orderable: false, targets: [0, 1, 5] } // Disable sorting on Sr No, Member info, and Actions
            ],
            order: [[4, 'asc']] // Default sort by Status
        });
    }

    // ---- View Card Logic ----
    function viewMember(member) {
        const user = member.user || {};
        const plan = member.plan || {};
        const batch = member.batch || null;
        const trainer = member.trainer || null;

        // Photo
        const photoUrl = user.photo ? `/${user.photo}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name || 'U')}&background=f3f4f6&color=6b7280`;
        document.getElementById('view-card-photo').src = photoUrl;

        // Status Colors & Badge
        const banner = document.getElementById('view-card-banner');
        const statusBadge = document.getElementById('view-card-status');
        statusBadge.textContent = member.status;
        
        banner.className = "h-32 relative transition-colors duration-300 ";
        statusBadge.className = "px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-white shadow-sm transition-colors duration-300 ";
        
        if (member.status === 'active') {
            banner.className += "bg-gradient-to-r from-green-400 to-green-600";
            statusBadge.className += "text-green-600";
        } else if (member.status === 'expired') {
            banner.className += "bg-gradient-to-r from-orange-400 to-orange-600";
            statusBadge.className += "text-orange-600";
        } else {
            banner.className += "bg-gradient-to-r from-red-400 to-red-600";
            statusBadge.className += "text-red-600";
        }

        // Basic Info
        document.getElementById('view-card-id').textContent = `MEM-${String(member.id).padStart(4, '0')}`;
        document.getElementById('view-card-name').textContent = user.name || 'N/A';
        document.getElementById('view-card-mobile').textContent = user.mobile || 'N/A';
        document.getElementById('view-card-email').textContent = user.email || 'N/A';
        document.getElementById('view-card-gender').textContent = user.gender || 'N/A';
        document.getElementById('view-card-dob').textContent = user.dob ? new Date(user.dob).toLocaleDateString('en-GB') : 'N/A';

        // Membership Details
        if (plan && plan.plan_group_name) {
            document.getElementById('view-card-plan').textContent = `${plan.plan_group_name} (${plan.duration_months}M)`;
        } else {
            document.getElementById('view-card-plan').textContent = 'N/A';
        }

        if (member.joining_date && plan && plan.duration_months) {
            const startDate = new Date(member.joining_date);
            const expiryDate = new Date(startDate);
            expiryDate.setMonth(expiryDate.getMonth() + parseInt(plan.duration_months));
            const startStr = startDate.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
            const expiryStr = expiryDate.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
            document.getElementById('view-card-validity').textContent = `${startStr} – ${expiryStr}`;
        } else {
            document.getElementById('view-card-validity').textContent = 'N/A';
        }
        
        document.getElementById('view-card-joined').textContent = member.joining_date ? new Date(member.joining_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : 'N/A';
        
        // Calculate Paid Amount (Payments logic could be complex, assuming we have total amount_received in payments)
        // Wait, member model returns payments[]!
        let totalPaid = 0;
        if(member.payments && member.payments.length > 0) {
            totalPaid = member.payments.reduce((sum, p) => sum + parseFloat(p.paid_amount), 0);
        }
        document.getElementById('view-card-paid').textContent = totalPaid.toLocaleString();

        // Assignments
        if (batch) {
            const time = batch.start_time ? ` (${formatTime(batch.start_time)} - ${formatTime(batch.end_time)})` : '';
            document.getElementById('view-card-batch').textContent = `${batch.name}${time}`;
        } else {
            document.getElementById('view-card-batch').textContent = 'No Batch Assigned';
        }

        if (trainer) {
            document.getElementById('view-card-trainer').textContent = trainer.name || 'N/A';
        } else {
            document.getElementById('view-card-trainer').textContent = 'No Trainer Assigned';
        }

        document.getElementById('view-member-modal').classList.remove('hidden');
    }

    function closeViewModal() {
        document.getElementById('view-member-modal').classList.add('hidden');
    }

    // ---- Wizard Logic ----
    function openWizardModal() {
        isEditing = false;
        document.getElementById('wizard-title').textContent = 'New Member Registration';
        document.getElementById('member-form').reset();
        document.getElementById('photo-preview').src = '';
        document.getElementById('photo-preview').classList.add('hidden');
        document.getElementById('joining_date').valueAsDate = new Date();
        document.getElementById('plan_amount').value = 0;
        document.getElementById('total_amount_display').textContent = '0';
        document.getElementById('status-container').style.display = 'none';
        
        // Show required stars for password
        document.querySelectorAll('.password-req').forEach(el => el.classList.remove('hidden'));
        document.getElementById('password-hint').textContent = "Min 8 chars, containing letters, numbers & symbols.";
        
        clearErrors();
        currentStep = 1;
        updateWizardUI();
        document.getElementById('wizard-modal').classList.remove('hidden');
    }

    // We can populate the wizard with existing data for edit
    function openEditWizard(member) {
        isEditing = true;
        document.getElementById('wizard-title').textContent = 'Edit Member Profile';
        document.getElementById('member-form').reset();
        document.getElementById('member_id').value = member.id;
        document.getElementById('status-container').style.display = 'block';
        if(member.status) {
            document.getElementById('status').value = member.status;
        }
        
        // Populate Step 1
        if(member.user) {
            document.getElementById('name').value = member.user.name || '';
            document.getElementById('mobile').value = member.user.mobile || '';
            document.getElementById('email').value = member.user.email || '';
            document.getElementById('gender').value = member.user.gender || '';
            if(member.user.dob) document.getElementById('dob').value = member.user.dob.substring(0, 10);
            
            const preview = document.getElementById('photo-preview');
            if (member.user.photo) {
                preview.src = `/${member.user.photo}`;
                preview.classList.remove('hidden');
            } else {
                preview.classList.add('hidden');
            }
        }

        // Step 2: Passwords are optional on edit
        document.querySelectorAll('.password-req').forEach(el => el.classList.add('hidden'));
        document.getElementById('password-hint').textContent = "Leave blank if you don't want to change the password.";

        // Step 3
        if(member.plan_id) document.getElementById('plan_id').value = member.plan_id;
        if(member.joining_date) document.getElementById('joining_date').value = member.joining_date;
        if(member.discount) document.getElementById('discount').value = parseFloat(member.discount);
        
        if (member.payments && member.payments.length > 0) {
            document.getElementById('amount_received').value = parseFloat(member.payments[member.payments.length - 1].paid_amount);
        } else {
            document.getElementById('amount_received').value = 0;
        }
        
        calculateTotal(); // Trigger calculation based on selected plan

        // Step 4
        if(member.batch_id) document.getElementById('batch_id').value = member.batch_id;
        if(member.trainer_id) document.getElementById('trainer_id').value = member.trainer_id;

        clearErrors();
        currentStep = 1;
        updateWizardUI();
        document.getElementById('wizard-modal').classList.remove('hidden');
    }

    function closeWizardModal() {
        document.getElementById('wizard-modal').classList.add('hidden');
    }

    // ---- Renew / Upgrade Plan Logic ----
    let activeMemberCalculatedExpiry = '';
    let todayIsoString = '';
    let activeMemberRecentPaid = 0;

    function openRenewModal(id) {
        const member = membersData.find(m => m.id === id);
        if (!member) return;

        document.getElementById('renew-modal').classList.remove('hidden');
        document.getElementById('renew_member_id').value = id;
        document.getElementById('renew-member-name').textContent = member.user?.name || 'Member';

        // Calculate current plan validity & recent payment amount
        const plan = member.plan;
        const planTitle = plan && plan.plan_group_name ? `${plan.plan_group_name} (${plan.duration_months}M)` : 'No Active Plan';
        document.getElementById('renew-current-plan').textContent = planTitle;

        // Recent paid on current plan
        activeMemberRecentPaid = 0;
        if (member.payments && member.payments.length > 0) {
            activeMemberRecentPaid = parseFloat(member.payments[0].paid_amount) || 0;
        } else if (member.total_amount) {
            activeMemberRecentPaid = parseFloat(member.total_amount) || 0;
        }
        document.getElementById('renew-current-paid-amount').textContent = activeMemberRecentPaid.toLocaleString('en-IN');
        document.getElementById('calc-prev-adjusted').textContent = activeMemberRecentPaid.toLocaleString('en-IN');

        todayIsoString = new Date().toISOString().split('T')[0];
        let currentExpiryDate = new Date();

        if (member.joining_date && plan && plan.duration_months) {
            const startDate = new Date(member.joining_date);
            currentExpiryDate = new Date(startDate);
            currentExpiryDate.setMonth(currentExpiryDate.getMonth() + parseInt(plan.duration_months));

            const startStr = startDate.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
            const expiryStr = currentExpiryDate.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });

            if (currentExpiryDate > new Date()) {
                activeMemberCalculatedExpiry = currentExpiryDate.toISOString().split('T')[0];
                document.getElementById('renew-current-badge').className = 'font-black px-2 py-0.5 rounded text-[10px] bg-emerald-100 text-emerald-700';
                document.getElementById('renew-current-badge').textContent = 'Active Plan';
                document.getElementById('renew-current-validity').textContent = `Valid: ${startStr} to ${expiryStr}`;
            } else {
                activeMemberCalculatedExpiry = todayIsoString;
                document.getElementById('renew-current-badge').className = 'font-black px-2 py-0.5 rounded text-[10px] bg-amber-100 text-amber-700';
                document.getElementById('renew-current-badge').textContent = 'Expired';
                document.getElementById('renew-current-validity').textContent = `Expired on: ${expiryStr}`;
            }
        } else {
            activeMemberCalculatedExpiry = todayIsoString;
            document.getElementById('renew-current-badge').className = 'font-black px-2 py-0.5 rounded text-[10px] bg-gray-100 text-gray-700';
            document.getElementById('renew-current-badge').textContent = 'No Plan';
            document.getElementById('renew-current-validity').textContent = 'No previous plan validity';
        }

        // Default to Upgrade / Change Plan mode
        document.getElementById('type-upgrade').checked = true;
        handleActionTypeChange();

        document.getElementById('opt-extend').checked = true;
        document.getElementById('renew_start_date').value = activeMemberCalculatedExpiry;
        document.getElementById('renew_discount').value = 0;
        
        // Populate plan options
        const select = document.getElementById('renew_plan_id');
        select.innerHTML = '<option value="">Select Plan</option>';
        plansData.forEach(p => {
            select.innerHTML += `<option value="${p.id}" data-amount="${p.amount}" data-duration="${p.duration_months}">${p.display_name} - ₹${p.amount}</option>`;
        });
        
        document.getElementById('renew_paid').value = '';
        calculateRenewAmounts();
    }

    function handleActionTypeChange() {
        const isUpgrade = document.getElementById('type-upgrade').checked;
        const startDateContainer = document.getElementById('renew-start-date-container');
        const hintEl = document.getElementById('action-type-hint');
        const prevAdjustedRow = document.getElementById('row-prev-adjusted');
        const diffLabel = document.getElementById('label-diff-collect');
        const upgradeLabel = document.getElementById('type-upgrade-label');
        const renewLabel = document.getElementById('type-renew-label');

        if (isUpgrade) {
            startDateContainer.classList.add('hidden');
            prevAdjustedRow.classList.remove('hidden');
            diffLabel.textContent = 'Difference to Collect (Adjusted):';
            hintEl.innerHTML = `💡 <strong>Upgrade / Change Mode:</strong> Previously paid amount (₹${activeMemberRecentPaid.toLocaleString('en-IN')}) will be deducted so only the difference is charged!`;
            upgradeLabel.className = 'flex items-center gap-2 p-2.5 bg-indigo-50/90 border-2 border-indigo-400 rounded-xl text-xs font-bold cursor-pointer text-indigo-900 shadow-2xs';
            renewLabel.className = 'flex items-center gap-2 p-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold cursor-pointer text-gray-600 hover:bg-gray-100';
        } else {
            startDateContainer.classList.remove('hidden');
            prevAdjustedRow.classList.add('hidden');
            diffLabel.textContent = 'Total Amount to Collect:';
            hintEl.innerHTML = `💡 <strong>Next Cycle Renewal:</strong> Creates a fresh new payment for the next upcoming period.`;
            renewLabel.className = 'flex items-center gap-2 p-2.5 bg-indigo-50/90 border-2 border-indigo-400 rounded-xl text-xs font-bold cursor-pointer text-indigo-900 shadow-2xs';
            upgradeLabel.className = 'flex items-center gap-2 p-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold cursor-pointer text-gray-600 hover:bg-gray-100';
        }

        calculateRenewAmounts();
    }

    function handleRenewModeChange() {
        if (document.getElementById('opt-extend').checked) {
            document.getElementById('renew_start_date').value = activeMemberCalculatedExpiry;
        } else {
            document.getElementById('renew_start_date').value = todayIsoString;
        }
        calculateRenewAmounts();
    }

    function closeRenewModal() {
        document.getElementById('renew-modal').classList.add('hidden');
    }

    function calculateRenewAmounts() {
        const select = document.getElementById('renew_plan_id');
        const startDateVal = document.getElementById('renew_start_date').value || todayIsoString;
        const expiryTextEl = document.getElementById('renew-new-expiry-text');
        const isUpgrade = document.getElementById('type-upgrade').checked;

        if (!select.value) {
            document.getElementById('renew_paid').value = '';
            expiryTextEl.textContent = 'Select plan to preview';
            document.getElementById('calc-new-price').textContent = '0';
            document.getElementById('calc-net-diff').textContent = '0';
            return;
        }

        const option = select.options[select.selectedIndex];
        const planAmount = parseFloat(option.getAttribute('data-amount')) || 0;
        const durationMonths = parseInt(option.getAttribute('data-duration')) || 1;
        const discount = parseFloat(document.getElementById('renew_discount').value) || 0;
        const netPlanPrice = Math.max(0, planAmount - discount);

        document.getElementById('calc-new-price').textContent = netPlanPrice.toLocaleString('en-IN');

        let netToPay = netPlanPrice;
        if (isUpgrade) {
            netToPay = Math.max(0, netPlanPrice - activeMemberRecentPaid);
        }

        document.getElementById('calc-net-diff').textContent = netToPay.toLocaleString('en-IN');
        document.getElementById('renew_paid').value = netToPay;

        // Calculate and show new expiry date
        const sDate = new Date(isUpgrade ? todayIsoString : startDateVal);
        const newExpiry = new Date(sDate);
        newExpiry.setMonth(newExpiry.getMonth() + durationMonths);
        const formattedNewExpiry = newExpiry.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
        expiryTextEl.textContent = `${formattedNewExpiry} (${durationMonths} Mo)`;
    }

    async function submitRenew(e) {
        e.preventDefault();
        const id = document.getElementById('renew_member_id').value;
        const isUpgrade = document.getElementById('type-upgrade').checked;
        const payload = {
            action_type: isUpgrade ? 'upgrade' : 'renew',
            plan_id: document.getElementById('renew_plan_id').value,
            start_date: document.getElementById('renew_start_date').value || todayIsoString,
            discount: document.getElementById('renew_discount').value || 0,
            amount_received: document.getElementById('renew_paid').value || 0
        };

        const btn = document.getElementById('btn-renew-submit');
        const origText = btn.textContent;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
        btn.disabled = true;

        try {
            const res = await fetch(`/api/members/${id}/renew`, {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (res.ok && data.success) {
                showSuccess(isUpgrade ? 'Plan upgraded & previous payment adjusted successfully!' : 'Member renewed successfully!');
                closeRenewModal();
                fetchMembers(); // refresh table and stats
            } else {
                showError(data.message || 'Failed to update member plan.');
            }
        } catch (error) {
            showError('Network error while processing plan update.');
        } finally {
            btn.innerHTML = origText;
            btn.disabled = false;
        }
    }

    function togglePasswordVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        } else {
            input.type = "password";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    }

    function changeStep(direction) {
        // Simple Validation before moving next
        if (direction === 1) {
            if (currentStep === 1) {
                if (!document.getElementById('name').value || !document.getElementById('mobile').value || !document.getElementById('email').value) {
                    showError("Please fill out all required personal details.");
                    return;
                }
            }
            if (currentStep === 2 && !isEditing) {
                if (!document.getElementById('password').value || document.getElementById('password').value !== document.getElementById('password_confirmation').value) {
                    showError("Passwords must be provided and match.");
                    return;
                }
            }
            if (currentStep === 3) {
                if (!document.getElementById('plan_id').value || !document.getElementById('joining_date').value) {
                    showError("Please select a plan and joining date.");
                    return;
                }
            }
        }

        const newStep = currentStep + direction;
        if (newStep >= 1 && newStep <= totalSteps) {
            currentStep = newStep;
            updateWizardUI();
        }
    }

    function updateWizardUI() {
        // Show/Hide forms
        for (let i = 1; i <= totalSteps; i++) {
            const stepDiv = document.getElementById(`step-${i}`);
            if (i === currentStep) {
                stepDiv.classList.remove('hidden');
                stepDiv.classList.add('block');
            } else {
                stepDiv.classList.add('hidden');
                stepDiv.classList.remove('block');
            }
        }

        // Update progress bar
        const progressPercentage = ((currentStep - 1) / (totalSteps - 1)) * 100;
        document.getElementById('progress-line').style.width = `${progressPercentage}%`;

        // Update indicators
        document.querySelectorAll('.step-indicator').forEach(indicator => {
            const step = parseInt(indicator.getAttribute('data-step'));
            const circle = indicator.querySelector('div');
            const text = indicator.querySelector('span');
            
            if (step < currentStep) {
                // Completed
                circle.className = 'w-10 h-10 rounded-full bg-green-500 text-white flex items-center justify-center font-bold shadow-md shadow-green-900/20 border-4 border-white transition-colors duration-300';
                circle.innerHTML = '<i class="fa-solid fa-check"></i>';
                text.className = 'text-xs font-bold text-green-500 mt-2 absolute -bottom-6 w-24 text-center';
            } else if (step === currentStep) {
                // Current
                circle.className = 'w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold shadow-md shadow-indigo-600/20 border-4 border-white transition-colors duration-300 transform scale-110';
                circle.innerHTML = step;
                text.className = 'text-xs font-bold text-indigo-600 mt-2 absolute -bottom-6 w-24 text-center';
            } else {
                // Pending
                circle.className = 'w-10 h-10 rounded-full bg-gray-200 text-gray-400 flex items-center justify-center font-bold border-4 border-white transition-colors duration-300';
                circle.innerHTML = step;
                text.className = 'text-xs font-bold text-gray-400 mt-2 absolute -bottom-6 w-24 text-center';
            }
        });

        // Update Buttons
        const btnPrev = document.getElementById('btn-prev');
        const btnNext = document.getElementById('btn-next');
        const btnSave = document.getElementById('btn-save');

        if (currentStep === 1) {
            btnPrev.classList.add('hidden');
        } else {
            btnPrev.classList.remove('hidden');
        }

        if (currentStep === totalSteps) {
            btnNext.classList.add('hidden');
            btnSave.classList.remove('hidden');
            btnSave.classList.add('flex');
        } else {
            btnNext.classList.remove('hidden');
            btnNext.classList.add('flex');
            btnSave.classList.add('hidden');
            btnSave.classList.remove('flex');
        }
    }

    // Dynamic Calculations
    function calculateTotal() {
        const select = document.getElementById('plan_id');
        const option = select.options[select.selectedIndex];
        
        let planAmount = parseFloat(option ? option.getAttribute('data-amount') : 0) || 0;
        let discount = parseFloat(document.getElementById('discount').value) || 0;
        
        document.getElementById('plan_amount').value = planAmount;
        let total = planAmount - discount;
        if(total < 0) total = 0;
        
        document.getElementById('total_amount_display').textContent = total.toLocaleString();
        document.getElementById('amount_received').value = total;
        calculatePending();
    }

    function calculatePending() {
        let totalDisplay = document.getElementById('total_amount_display').textContent.replace(/,/g, '');
        let total = parseFloat(totalDisplay) || 0;
        let received = parseFloat(document.getElementById('amount_received').value) || 0;
        
        let pending = total - received;
        if (pending < 0) pending = 0;
        
        document.getElementById('pending_amount_display').textContent = pending.toLocaleString();
    }

    function previewImage(input) {
        const preview = document.getElementById('photo-preview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.src = '';
            preview.classList.add('hidden');
        }
    }

    function clearErrors() {
        const fields = ['name', 'mobile', 'email', 'password', 'plan_id', 'joining_date', 'photo'];
        fields.forEach(f => {
            const el = document.getElementById(f);
            if(el && el.type !== 'file') el.classList.remove('border-red-500', 'bg-red-50');
            const err = document.getElementById('error-'+f);
            if(err) err.classList.add('hidden');
        });
    }

    function showErrors(errors) {
        if (typeof showError === 'function') {
            showError('Please correct the highlighted errors.');
        }
        let firstStepWithError = null;

        Object.keys(errors).forEach(field => {
            const el = document.getElementById(field);
            if(el && el.type !== 'file') el.classList.add('border-red-500', 'bg-red-50');
            const err = document.getElementById('error-'+field);
            if(err) {
                err.textContent = errors[field][0];
                err.classList.remove('hidden');
            }

            // Determine which step the error belongs to in order to navigate back
            if (!firstStepWithError) {
                if (['name', 'mobile', 'email', 'photo', 'gender', 'dob'].includes(field)) firstStepWithError = 1;
                else if (['password'].includes(field)) firstStepWithError = 2;
                else if (['plan_id', 'joining_date', 'discount', 'amount_received'].includes(field)) firstStepWithError = 3;
                else if (['batch_id', 'trainer_id'].includes(field)) firstStepWithError = 4;
                else firstStepWithError = 1; // fallback
            }
        });

        if (firstStepWithError && firstStepWithError !== currentStep) {
            currentStep = firstStepWithError;
            updateWizardUI();
            showError("Please fix the validation errors.");
        }
    }

    // Save Member logic
    async function saveMember() {
        clearErrors();
        showLoader();

        const form = new FormData();
        form.append('name', document.getElementById('name').value);
        form.append('mobile', document.getElementById('mobile').value);
        form.append('email', document.getElementById('email').value);
        form.append('gender', document.getElementById('gender').value);
        form.append('dob', document.getElementById('dob').value);
        form.append('plan_id', document.getElementById('plan_id').value);
        form.append('joining_date', document.getElementById('joining_date').value);
        form.append('discount', document.getElementById('discount').value);
        form.append('batch_id', document.getElementById('batch_id').value);
        form.append('trainer_id', document.getElementById('trainer_id').value);
        form.append('amount_received', document.getElementById('amount_received').value);
        if (isEditing) {
            form.append('status', document.getElementById('status').value);
        }

        const photoFile = document.getElementById('photo').files[0];
        if (photoFile) {
            form.append('photo', photoFile);
        }

        const pass = document.getElementById('password').value;
        const passConf = document.getElementById('password_confirmation').value;
        if (pass || !isEditing) {
            form.append('password', pass);
            form.append('password_confirmation', passConf);
        }

        const id = document.getElementById('member_id').value;
        let url = '/api/members';
        
        if (isEditing) {
            url = `/api/members/${id}`;
            form.append('_method', 'PUT'); // For laravel FormData update
        }

        try {
            const res = await fetch(url, {
                method: 'POST', // Always POST for FormData
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                },
                body: form
            });

            const data = await res.json();

            if (res.ok && data.success) {
                showSuccess(`Member ${isEditing ? 'updated' : 'registered'} successfully!`);
                closeWizardModal();
                fetchMembers(); // Reload table
            } else {
                if (data.errors) {
                    showErrors(data.errors);
                } else {
                    showError(data.message || 'Failed to save member.');
                }
            }
        } catch (error) {
            console.error('Save member error:', error);
            showError("An unexpected error occurred.");
        } finally {
            hideLoader();
        }
    }

    async function deleteMember(id) {
        confirmDelete('Delete Member?', 'Are you sure you want to delete this member? This action cannot be undone.', async () => {
            try {
                const res = await fetch(`/api/members/${id}`, {
                    method: 'DELETE',
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                });
                const result = await res.json();
                if (res.ok && result.success) {
                    showSuccess('Member deleted successfully.');
                    fetchMembers();
                } else {
                    showError(result.message || 'Failed to delete member.');
                }
            } catch(e) {
                showError('Network error while deleting member.');
            }
        });
    }
</script>
<style>
    .animate-fade-in { animation: fadeIn 0.4s ease-in-out; }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateX(10px); }
        to { opacity: 1; transform: translateX(0); }
    }
</style>
@endpush
@endsection

