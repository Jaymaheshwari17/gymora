@extends('layouts.dashboard-layout')

@section('dashboard-content')


<style>
/* Select2 Custom Clean Tailwind Styling */
.select2-container--default .select2-selection--single {
    background-color: transparent !important;
    border: none !important;
    height: 28px !important;
    display: flex !important;
    align-items: center !important;
    outline: none !important;
    box-shadow: none !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #374151 !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    line-height: 28px !important;
    padding-left: 2px !important;
    padding-right: 18px !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 28px !important;
    right: 2px !important;
}
.select2-dropdown {
    border: 1px solid #e5e7eb !important;
    border-radius: 0.75rem !important;
    box-shadow: 0 12px 28px -4px rgba(0, 0, 0, 0.12), 0 8px 10px -6px rgba(0, 0, 0, 0.08) !important;
    overflow: hidden !important;
    background: #ffffff !important;
    z-index: 99999 !important;
}
.select2-container--default .select2-search--dropdown {
    padding: 8px !important;
    background: #f9fafb !important;
    border-bottom: 1px solid #f3f4f6 !important;
}
.select2-container--default .select2-search--dropdown .select2-search__field {
    border: 1.5px solid #d1d5db !important;
    border-radius: 0.6rem !important;
    padding: 7px 12px !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    outline: none !important;
    background: #ffffff !important;
    color: #111827 !important;
    caret-color: #5d5fef !important;
}
.select2-container--default .select2-search--dropdown .select2-search__field::placeholder {
    color: #9ca3af !important;
    font-weight: 500 !important;
}
.select2-container--default .select2-search--dropdown .select2-search__field:focus {
    border-color: #5d5fef !important;
    box-shadow: 0 0 0 3px rgba(93, 95, 239, 0.18) !important;
    color: #111827 !important;
    background: #ffffff !important;
}
.select2-container--default .select2-results__option {
    padding: 8px 12px !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    color: #374151 !important;
}
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #5d5fef !important;
    color: #ffffff !important;
}
.select2-container--default .select2-results__option[aria-selected=true] {
    background-color: #eef2ff !important;
    color: #4338ca !important;
    font-weight: 700 !important;
}
</style>

<div class="flex-1 overflow-y-auto p-8 bg-[#f8f9fc]">
    <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Gym Members</h1>
            <p class="text-sm text-gray-500 mt-1 font-medium">Manage all your members and their subscriptions.</p>
        </div>
        <div class="flex items-center gap-2.5 overflow-x-auto pb-1 xl:pb-0 hide-scrollbar">
            <!-- Filter 1: Membership Status & Expiry -->
            <div class="flex items-center gap-1.5 bg-white px-3 py-1.5 rounded-xl border border-gray-200 shadow-2xs shrink-0">
                <i class="fa-solid fa-circle-dot text-indigo-500 text-xs"></i>
                <select id="member-status-filter" onchange="renderTable()" class="text-xs font-bold text-gray-700 bg-transparent outline-none cursor-pointer">
                    <option value="all">All Status</option>
                    <option value="active">Active Members</option>
                    <option value="expired_month">Expired This Month</option>
                    <option value="expired">All Expired Members</option>
                    <option value="expiring">Expiring Soon (3 Days)</option>
                    <option value="new">New Members (This Month)</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <!-- Filter 2: Plan / Package -->
            <div class="flex items-center gap-1.5 bg-white px-3 py-1.5 rounded-xl border border-gray-200 shadow-2xs shrink-0">
                <i class="fa-solid fa-dumbbell text-indigo-500 text-xs"></i>
                <select id="member-plan-filter" onchange="renderTable()" class="text-xs font-bold text-gray-700 bg-transparent outline-none cursor-pointer max-w-[130px] truncate">
                    <option value="all">All Plans</option>
                    <!-- Populated dynamically via JS -->
                </select>
            </div>

            <!-- Filter 3: Member Search (Select2) -->
            <div class="flex items-center gap-1.5 bg-white px-3 py-1.5 rounded-xl border border-gray-200 shadow-2xs shrink-0">
                <i class="fa-solid fa-user text-indigo-500 text-xs"></i>
                <select id="member-search-select" class="text-xs font-bold text-gray-700 bg-transparent outline-none cursor-pointer w-40">
                    <option value="">Search Member...</option>
                    <!-- Populated dynamically via JS or DataTables -->
                </select>
            </div>

            <button onclick="openWizardModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-[11px] font-bold transition flex items-center gap-2 shadow-md shadow-indigo-600/20 cursor-pointer shrink-0">
                <i class="fa-solid fa-user-plus text-[11px]"></i> Add New Member
            </button>
        </div>
    </div>

    <!-- Active Filter Indicator Banner (Dynamic) -->
    <div id="active-filter-banner" class="hidden p-3.5 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200/90 rounded-2xl flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-orange-500 text-white flex items-center justify-center text-xs font-bold shrink-0 shadow-xs">
                <i class="fa-solid fa-filter"></i>
            </div>
            <div>
                <div class="text-xs font-extrabold text-gray-900 flex items-center gap-1.5">
                    <span>Active Filter:</span>
                    <span class="text-orange-700 bg-white/90 px-2 py-0.5 rounded-md border border-orange-200 text-xs font-black" id="filter-banner-label">Expired This Month</span>
                </div>
                <div class="text-[11px] text-gray-500 font-semibold mt-0.5" id="filter-banner-count">Showing matching members</div>
            </div>
        </div>
        <button onclick="clearStatusFilter()" class="px-3 py-1.5 bg-white hover:bg-red-50 border border-amber-200 text-gray-700 hover:text-red-600 rounded-xl text-xs font-bold transition-all shadow-2xs cursor-pointer flex items-center gap-1.5">
            <span>Clear Filter</span>
            <i class="fa-solid fa-xmark text-xs"></i>
        </button>
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
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-purple-50 flex justify-between items-center">
            <div>
                <h3 class="text-base font-extrabold text-gray-900">🔄 Renew Plan</h3>
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
                            <span class="font-bold uppercase tracking-wider text-[10px] text-indigo-600">Current / Expiring Plan</span>
                            <span id="renew-current-badge" class="font-black px-2 py-0.5 rounded text-[10px] bg-amber-100 text-amber-700">Expired</span>
                        </div>
                        <div class="font-extrabold text-sm text-gray-900" id="renew-current-plan">—</div>
                        <div class="text-gray-500 font-medium text-xs mt-0.5" id="renew-current-validity">—</div>
                    </div>

                    <!-- New Plan Start Date -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">New Plan Start Date <span class="text-red-500">*</span></label>
                        <input type="date" id="renew_start_date" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-600 outline-none text-xs font-bold bg-white" onchange="calculateRenewAmounts()">
                        <p class="text-[11px] text-gray-400 mt-1 font-medium">💡 New plan cycle starts from this date</p>
                    </div>

                    <!-- Select New Plan -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Select Plan <span class="text-red-500">*</span></label>
                        <select id="renew_plan_id" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-600 outline-none text-xs font-semibold bg-white cursor-pointer" onchange="calculateRenewAmounts()">
                            <option value="">Select Plan</option>
                        </select>
                    </div>

                    <!-- New Expiry Preview -->
                    <div class="p-3 bg-emerald-50/80 border border-emerald-100 rounded-xl text-emerald-800 text-xs font-medium flex items-center justify-between">
                        <span class="flex items-center gap-1.5 font-bold">
                            <i class="fa-solid fa-calendar-check text-emerald-600"></i> New Expiry Date:
                        </span>
                        <span class="font-black text-xs sm:text-sm text-emerald-700" id="renew-new-expiry-text">Select plan to preview</span>
                    </div>

                    <!-- Financial Breakdown -->
                    <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-200 text-xs space-y-2">
                        <div class="flex justify-between text-gray-600">
                            <span>Plan Price:</span>
                            <span class="font-bold text-gray-900">₹<span id="calc-new-price">0</span></span>
                        </div>
                        <div class="flex justify-between text-rose-500 font-semibold hidden" id="row-discount-preview">
                            <span>Discount:</span>
                            <span>-₹<span id="calc-discount-preview">0</span></span>
                        </div>
                        <div class="flex justify-between text-gray-900 font-bold border-t border-gray-200 pt-1.5">
                            <span>Total to Collect:</span>
                            <span class="text-indigo-600 font-black text-sm">₹<span id="calc-net-diff">0</span></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Discount -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Discount (₹)</label>
                            <input type="number" id="renew_discount" min="0" value="0" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-600 outline-none text-xs font-bold" oninput="calculateRenewAmounts(false)">
                        </div>
                        <!-- Amount Paid Now -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Amount Paid Now (₹)</label>
                            <input type="number" id="renew_paid" min="0" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-600 outline-none text-xs font-black text-emerald-600" oninput="calculateDueLive()">
                        </div>
                    </div>

                    <!-- Due Status -->
                    <div id="renew-due-status-box" class="p-3 rounded-xl text-xs font-medium flex items-center justify-between border transition-all bg-emerald-50 border-emerald-200 text-emerald-800">
                        <span class="flex items-center gap-1.5 font-bold">
                            <i id="renew-due-status-icon" class="fa-solid fa-circle-check text-emerald-600"></i>
                            <span id="renew-due-status-label">Full Payment</span>
                        </span>
                        <span class="font-black text-xs sm:text-sm" id="renew-due-status-amount">Remaining Due: ₹0</span>
                    </div>

                </div>
            </form>
        </div>
        
        <!-- Footer -->
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/80 flex justify-end gap-3 shrink-0">
            <button type="button" onclick="closeRenewModal()" class="px-4 py-2 text-gray-600 hover:text-gray-900 font-bold text-xs rounded-xl transition">Cancel</button>
            <button type="submit" form="renew-form" id="btn-renew-submit" class="px-5 py-2.5 bg-[#5d5fef] hover:bg-[#4d4fe0] text-white text-xs font-black rounded-xl shadow-md shadow-[#5d5fef]/25 transition-all">✅ Confirm Renewal</button>
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
                        <span>Set a password for the member to log into the Flexvora App. They can change it later.</span>
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
                                <input type="number" id="amount_received" min="0" value="0" class="w-32 px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-right font-bold text-green-600 outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition-all" oninput="amountReceivedTouched = true; calculatePending()">
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
<div id="view-member-modal" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4 sm:p-6">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeViewModal()"></div>
    
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden relative z-10 w-full max-w-4xl flex flex-col max-h-[92vh] transform transition-all border border-gray-100">
        
        <!-- Header Strip -->
        <div id="view-card-banner" class="px-6 py-4 bg-gradient-to-r from-emerald-500 to-teal-600 text-white flex items-center justify-between relative transition-colors duration-300 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white font-bold text-sm">
                    <i class="fa-solid fa-id-card"></i>
                </div>
                <div>
                    <span id="view-card-id" class="font-black text-sm tracking-wide">MEM-0001</span>
                    <span class="text-white/80 text-xs ml-2 font-medium">Member Profile & Plan Overview</span>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <span id="view-card-status" class="px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-white shadow-sm text-emerald-600">Active</span>
                <button onclick="closeViewModal()" class="w-8 h-8 rounded-full bg-black/20 hover:bg-black/30 text-white flex items-center justify-center transition backdrop-blur-md cursor-pointer">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        </div>

        <!-- Card Body (2 Columns Layout) -->
        <div class="p-6 md:p-8 overflow-y-auto grid grid-cols-1 md:grid-cols-12 gap-6 bg-[#f8f9fc]">
            
            <!-- Left Column: Member Profile & Contacts (5 cols) -->
            <div class="md:col-span-5 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-center text-center justify-between">
                <div class="w-full flex flex-col items-center">
                    <!-- Photo / Avatar -->
                    <div class="relative mb-3">
                        <img id="view-card-photo" src="" class="w-24 h-24 rounded-2xl object-cover border-4 border-indigo-50 shadow-md bg-white">
                        <div id="view-card-photo-badge" class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-emerald-500 border-2 border-white flex items-center justify-center text-white text-[10px]" title="Active Member">
                            <i class="fa-solid fa-check"></i>
                        </div>
                    </div>

                    <!-- Name -->
                    <h3 id="view-card-name" class="text-xl font-black text-gray-900 leading-tight">Name</h3>
                    <p class="text-xs text-gray-400 font-medium mt-0.5">Joined: <span id="view-card-joined" class="font-bold text-gray-700">01 Jan 2026</span></p>

                    <!-- Contact Details -->
                    <div class="w-full space-y-2 mt-5 text-left text-xs font-semibold text-gray-700">
                        <div class="flex items-center gap-2.5 p-2.5 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-phone text-xs"></i>
                            </div>
                            <span id="view-card-mobile" class="truncate font-bold">N/A</span>
                        </div>
                        <div class="flex items-center gap-2.5 p-2.5 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                                <i class="fa-regular fa-envelope text-xs"></i>
                            </div>
                            <span id="view-card-email" class="truncate font-bold">N/A</span>
                        </div>
                    </div>

                    <!-- Personal Meta (Gender / DOB) -->
                    <div class="grid grid-cols-2 gap-2 w-full mt-3 text-xs">
                        <div class="p-2.5 bg-indigo-50/50 rounded-xl text-center border border-indigo-50">
                            <span class="text-[10px] text-gray-400 font-bold uppercase block">Gender</span>
                            <span id="view-card-gender" class="font-bold text-indigo-900 capitalize">Male</span>
                        </div>
                        <div class="p-2.5 bg-indigo-50/50 rounded-xl text-center border border-indigo-50">
                            <span class="text-[10px] text-gray-400 font-bold uppercase block">Date of Birth</span>
                            <span id="view-card-dob" class="font-bold text-indigo-900">N/A</span>
                        </div>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="w-full grid grid-cols-2 gap-2 mt-5 pt-4 border-t border-gray-100">
                    <button type="button" onclick="openRenewFromActiveView()" class="px-3 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm shadow-indigo-600/20 cursor-pointer">
                        <i class="fa-solid fa-arrows-rotate text-xs"></i> Renew / Upgrade
                    </button>
                    <button type="button" onclick="openEditFromActiveView()" class="px-3 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-pen text-xs"></i> Edit Profile
                    </button>
                </div>
            </div>

            <!-- Right Column: Membership & Financial Details (7 cols) -->
            <div class="md:col-span-7 space-y-4 flex flex-col justify-between">
                
                <!-- Plan & Validity Card -->
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-3">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Active Subscription</span>
                        <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2.5 py-0.5 rounded-md"><i class="fa-solid fa-dumbbell mr-1"></i> Current Plan</span>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <div id="view-card-plan" class="text-lg font-black text-gray-900 leading-tight">Cardio and Weight (12M)</div>
                            <div class="text-xs text-indigo-700 font-bold mt-1.5 flex items-center gap-1.5">
                                <i class="fa-regular fa-calendar-check text-indigo-500"></i>
                                <span id="view-card-validity">06 Mar 2027 – 06 Mar 2028</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Health Grid -->
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-3">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Billing & Payment Summary</span>
                    
                    <div class="grid grid-cols-3 gap-2 sm:gap-3">
                        <!-- Plan Fee -->
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-200/70 text-center">
                            <span class="text-[10px] font-bold text-gray-400 uppercase block tracking-tight">Plan Fee</span>
                            <span class="text-base sm:text-lg font-black text-gray-900 block mt-0.5">₹<span id="view-card-total-amount">0</span></span>
                        </div>
                        
                        <!-- Amount Paid -->
                        <div class="p-3 bg-emerald-50/70 rounded-xl border border-emerald-100 text-center">
                            <span class="text-[10px] font-bold text-emerald-600 uppercase block tracking-tight">Amount Paid</span>
                            <span class="text-base sm:text-lg font-black text-emerald-600 block mt-0.5">₹<span id="view-card-paid">0</span></span>
                        </div>

                        <!-- Pending Due -->
                        <div class="p-3 rounded-xl text-center border transition-all" id="view-card-due-box">
                            <span class="text-[10px] font-bold uppercase block tracking-tight" id="view-card-due-label">Pending Due</span>
                            <span class="text-base sm:text-lg font-black block mt-0.5" id="view-card-due-wrapper">₹<span id="view-card-due">0</span></span>
                        </div>
                    </div>
                </div>

                <!-- Assignments: Batch & Trainer Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 text-sm">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Assigned Batch</span>
                            <div id="view-card-batch" class="text-xs font-black text-gray-900 truncate mt-0.5">No Batch Assigned</div>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 text-sm">
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Personal Trainer</span>
                            <div id="view-card-trainer" class="text-xs font-black text-gray-900 truncate mt-0.5">No Trainer Assigned</div>
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
    let amountReceivedTouched = false;
    const totalSteps = 4;

    // Wait for DOM
    document.addEventListener('DOMContentLoaded', () => {
        // Set today as default joining date
        document.getElementById('joining_date').valueAsDate = new Date();
        
        // Read URL query parameter for filter (e.g. ?filter=expired_month or ?filter=expired or ?filter=expiring or ?filter=new or ?filter=active)
        const urlParams = new URLSearchParams(window.location.search);
        let urlFilter = urlParams.get('filter');
        if (urlFilter) {
            urlFilter = urlFilter.replace(/\/$/, '').toLowerCase(); // Clean trailing slash
            const filterSelect = document.getElementById('member-status-filter');
            if (filterSelect) {
                const validFilters = ['expired', 'expired_month', 'expiring', 'new', 'active', 'inactive'];
                if (validFilters.includes(urlFilter)) {
                    filterSelect.value = urlFilter;
                }
            }
        }

        // Date range from URL (e.g. from Dashboard View All click)
        const urlStartDate = urlParams.get('start_date');
        const urlEndDate = urlParams.get('end_date');
        if (urlStartDate && urlEndDate) {
            const fromInput = document.getElementById('member-from-date');
            const toInput = document.getElementById('member-to-date');
            const clearBtn = document.getElementById('btn-clear-member-date');
            if (fromInput) fromInput.value = urlStartDate;
            if (toInput) toInput.value = urlEndDate;
            if (clearBtn) clearBtn.classList.remove('hidden');
        }

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

            // Populate Select2 Member Filter
            const searchSelect = $('#member-search-select');
            if (searchSelect.length) {
                let html = '<option></option>';
                membersData.forEach(m => {
                    const name = (m.user && m.user.name) ? m.user.name : (m.name || 'Member');
                    const phone = (m.user && m.user.mobile) ? m.user.mobile : (m.phone || '');
                    const label = phone ? `${name} (${phone})` : name;
                    html += `<option value="${name}">${label}</option>`;
                });
                searchSelect.html(html);

                if (typeof $.fn.select2 === 'function') {
                    searchSelect.select2({
                        placeholder: 'Search Member...',
                        width: '200px',
                        allowClear: true
                    }).off('change.select2Member').on('change.select2Member', function() {
                        const val = $(this).val() || '';
                        if (dataTable) {
                            dataTable.search(val).draw();
                        }
                    });
                }
            }

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

    // Clear Filter helper
    function clearStatusFilter() {
        const filterSelect = document.getElementById('member-status-filter');
        if (filterSelect) filterSelect.value = 'all';
        const planSelect = document.getElementById('member-plan-filter');
        if (planSelect) planSelect.value = 'all';
        const fromInput = document.getElementById('member-from-date');
        const toInput = document.getElementById('member-to-date');
        if (fromInput) fromInput.value = '';
        if (toInput) toInput.value = '';
        const clearDateBtn = document.getElementById('btn-clear-member-date');
        if (clearDateBtn) clearDateBtn.classList.add('hidden');
        window.history.replaceState({}, document.title, window.location.pathname);
        renderTable();
    }

    function clearMemberDateFilter() {
        const fromInput = document.getElementById('member-from-date');
        const toInput = document.getElementById('member-to-date');
        if (fromInput) fromInput.value = '';
        if (toInput) toInput.value = '';
        const clearDateBtn = document.getElementById('btn-clear-member-date');
        if (clearDateBtn) clearDateBtn.classList.add('hidden');
        renderTable();
    }

    // ---- DataTable Rendering ----
    function renderTable() {
        if (dataTable) {
            dataTable.destroy();
        }

        const statusFilter = document.getElementById('member-status-filter')?.value || 'all';
        const planFilter = document.getElementById('member-plan-filter')?.value || 'all';
        const fromDate = document.getElementById('member-from-date')?.value || '';
        const toDate = document.getElementById('member-to-date')?.value || '';

        const clearDateBtn = document.getElementById('btn-clear-member-date');
        if (clearDateBtn) {
            if (fromDate && toDate) clearDateBtn.classList.remove('hidden');
            else clearDateBtn.classList.add('hidden');
        }

        const filteredMembers = membersData.filter(member => {
            const isExpired = !!member.is_expired || member.status === 'expired' || member.dynamic_status === 'expired';
            const isExpiredThisMonth = !!member.is_expired_this_month;
            const isExpiringSoon = !!member.is_expiring_soon || member.dynamic_status === 'expiring';
            const isInactive = member.status === 'inactive' || member.dynamic_status === 'inactive';
            const isNew = !!member.is_new_this_month;
            const isActive = !isExpired && !isInactive;

            const jDate = member.joining_date || (member.created_at ? member.created_at.substring(0, 10) : '');
            const expDate = member.expiry_date || '';

            // 1. Status Filter with Date Range Support
            if (statusFilter === 'expired_month') {
                if (fromDate && toDate) {
                    if (!expDate || expDate < fromDate || expDate > toDate || !isExpired) return false;
                } else {
                    if (!isExpiredThisMonth) return false;
                }
            } else if (statusFilter === 'expired') {
                if (fromDate && toDate) {
                    if (!expDate || expDate < fromDate || expDate > toDate || !isExpired) return false;
                } else {
                    if (!isExpired) return false;
                }
            } else if (statusFilter === 'expiring') {
                if (fromDate && toDate) {
                    if (!expDate || expDate < fromDate || expDate > toDate || isExpired) return false;
                } else {
                    if (!isExpiringSoon) return false;
                }
            } else if (statusFilter === 'new') {
                if (fromDate && toDate) {
                    if (!jDate || jDate < fromDate || jDate > toDate) return false;
                } else {
                    if (!isNew) return false;
                }
            } else if (statusFilter === 'active') {
                if (fromDate && toDate) {
                    if (!isActive || (jDate && (jDate < fromDate || jDate > toDate))) return false;
                } else {
                    if (!isActive) return false;
                }
            } else if (statusFilter === 'inactive') {
                if (!isInactive) return false;
            }

            // 2. Date Range Filter when statusFilter is 'all'
            if (statusFilter === 'all' && fromDate && toDate) {
                if (!jDate || jDate < fromDate || jDate > toDate) return false;
            }

            // 3. Plan Filter
            if (planFilter !== 'all' && String(member.plan_id) !== String(planFilter)) return false;

            return true;
        });

        // Update Active Filter Banner
        const banner = document.getElementById('active-filter-banner');
        const bannerLabel = document.getElementById('filter-banner-label');
        const bannerCount = document.getElementById('filter-banner-count');
        if (banner && bannerLabel && bannerCount) {
            if (statusFilter !== 'all' || planFilter !== 'all' || (fromDate && toDate)) {
                banner.classList.remove('hidden');
                let filterName = 'Custom Filter';
                if (statusFilter === 'expired_month') filterName = 'Expired This Month';
                else if (statusFilter === 'expired') filterName = 'All Expired Members';
                else if (statusFilter === 'expiring') filterName = 'Expiring Soon (Next 3 Days)';
                else if (statusFilter === 'new') filterName = 'New Members';
                else if (statusFilter === 'active') filterName = 'Active Members Only';
                else if (statusFilter === 'inactive') filterName = 'Inactive Members';
                else if (statusFilter === 'all') filterName = 'Members in Period';

                if (fromDate && toDate) {
                    const fStr = new Date(fromDate).toLocaleDateString('en-GB', {day:'numeric', month:'short'});
                    const tStr = new Date(toDate).toLocaleDateString('en-GB', {day:'numeric', month:'short', year:'numeric'});
                    filterName += ` (${fStr} - ${tStr})`;
                }

                if (planFilter !== 'all') {
                    const planObj = plansData.find(p => String(p.id) === String(planFilter));
                    if (planObj) filterName += ` • Plan: ${planObj.display_name}`;
                }
                
                bannerLabel.textContent = filterName;
                bannerCount.textContent = `Showing ${filteredMembers.length} member(s) matching filter`;
            } else {
                banner.classList.add('hidden');
            }
        }

        const tbody = document.getElementById('members-tbody');
        let html = '';
        
        filteredMembers.forEach((member, index) => {
            const user = member.user || {};
            const plan = member.plan || {};
            const batch = member.batch || null;
            
            const isExpired = !!member.is_expired || member.status === 'expired' || member.dynamic_status === 'expired';
            const isExpiringSoon = !!member.is_expiring_soon || member.dynamic_status === 'expiring';
            const isInactive = member.status === 'inactive' || member.dynamic_status === 'inactive';

            let statusClass = 'bg-emerald-100 text-emerald-700';
            let statusText = 'ACTIVE';

            if (isInactive) {
                statusClass = 'bg-gray-100 text-gray-700';
                statusText = 'INACTIVE';
            } else if (isExpired) {
                statusClass = 'bg-rose-100 text-rose-700';
                statusText = 'EXPIRED';
            } else if (isExpiringSoon) {
                statusClass = 'bg-amber-100 text-amber-700';
                statusText = 'EXPIRING SOON';
            } else {
                statusClass = 'bg-emerald-100 text-emerald-700';
                statusText = 'ACTIVE';
            }
            
            const photoUrl = user.photo ? `/${user.photo}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name || 'U')}&background=f3f4f6&color=6b7280`;

            const planDisplay = plan && plan.plan_group_name ? `<div class="font-bold text-gray-800">${plan.plan_group_name} - ${plan.duration_months}M</div>` : '<div class="text-gray-400 text-sm">No Plan</div>';
            
            const expiryText = member.formatted_expiry_date || (member.expiry_date ? new Date(member.expiry_date).toLocaleDateString('en-GB') : null);
            let expiryBadge = '';
            if (isExpired) {
                expiryBadge = `<div class="text-[11px] text-rose-600 font-bold mt-0.5"><i class="fa-regular fa-calendar-xmark mr-1"></i>Expired: ${expiryText || 'Past date'}</div>`;
            } else if (isExpiringSoon) {
                expiryBadge = `<div class="text-[11px] text-amber-600 font-bold mt-0.5"><i class="fa-regular fa-clock mr-1"></i>Expires in ${member.days_remaining}d (${expiryText})</div>`;
            } else if (expiryText && expiryText !== 'No Expiry') {
                expiryBadge = `<div class="text-[11px] text-gray-400 font-medium mt-0.5"><i class="fa-regular fa-calendar-check mr-1"></i>Valid till: ${expiryText}</div>`;
            }

            const batchDisplay = batch ? `<div class="text-[11px] text-indigo-600 font-bold mt-1 bg-indigo-50 inline-block px-2 py-0.5 rounded border border-purple-100"><i class="fa-solid fa-layer-group"></i> ${batch.name}</div>` : '';

            const planStartForDisplay = member.plan_start_date || member.joining_date;
            const startedText = planStartForDisplay ? new Date(planStartForDisplay).toLocaleDateString('en-GB', {day:'numeric', month:'short', year:'numeric'}) : 'N/A';

            const renewBtnStyle = 'w-10 h-10 rounded-lg bg-orange-50 text-orange-600 hover:bg-orange-100 flex items-center justify-center transition shadow-sm cursor-pointer';

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
                        <!-- Plan & Batch with Floating Hover Tooltip -->
                        <div class="relative group cursor-pointer inline-block">
                            ${planDisplay}
                            ${expiryBadge}
                            ${batchDisplay}
                            
                            <!-- 🌟 Hover Floating Plan Details Card - White Theme -->
                            <div class="hidden group-hover:block absolute left-0 bottom-full mb-2 w-60 bg-white p-3 rounded-xl shadow-lg z-50 pointer-events-none border border-gray-100 text-xs">
                                <div class="flex items-center justify-between border-b border-gray-100 pb-2 mb-2">
                                    <div class="flex items-center gap-1.5 font-bold text-gray-800 text-xs truncate max-w-[140px]">
                                        <i class="fa-solid fa-dumbbell text-[#5d5fef]"></i>
                                        <span class="truncate">${plan.plan_group_name || 'Membership'}</span>
                                    </div>
                                    <span class="text-[10px] font-black px-2 py-0.5 rounded-full ${statusClass}">${statusText}</span>
                                </div>
                                <div class="space-y-1.5 text-[11px]">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-400 flex items-center gap-1"><i class="fa-regular fa-calendar-plus text-emerald-500"></i> Plan Started:</span>
                                        <span class="font-bold text-gray-800">${startedText}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-400 flex items-center gap-1"><i class="fa-regular fa-calendar-xmark text-rose-500"></i> Plan Expiry:</span>
                                        <span class="font-bold ${isExpired ? 'text-rose-500' : 'text-emerald-600'}">${expiryText || 'N/A'}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-400 flex items-center gap-1"><i class="fa-regular fa-clock text-amber-500"></i> Duration:</span>
                                        <span class="font-bold text-gray-800">${plan.duration_months ? plan.duration_months + ' Month(s)' : 'Custom'}</span>
                                    </div>
                                    <div class="flex justify-between items-center border-t border-gray-100 pt-1.5 mt-1">
                                        <span class="text-gray-400">Plan Amount:</span>
                                        <span class="font-black text-[#5d5fef]">₹${member.plan_amount || plan.amount || 0}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="px-2.5 py-1 rounded-full text-xs font-black tracking-wide ${statusClass}">
                            ${statusText}
                        </span>
                    </td>
                    <td class="text-center">
                        <div class="flex justify-center gap-2">
                            <button onclick='openRenewModal(${member.id})' class="${renewBtnStyle}" title="Renew / Extend Plan">
                                <i class="fa-solid fa-arrows-rotate text-sm"></i>
                            </button>
                            <button onclick='viewMember(${JSON.stringify(member).replace(/'/g, "&#39;")})' class="w-10 h-10 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 flex items-center justify-center transition shadow-sm cursor-pointer" title="View Details">
                                <i class="fa-solid fa-eye text-sm"></i>
                            </button>
                            <button onclick='openEditWizard(${JSON.stringify(member).replace(/'/g, "&#39;")})' class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition shadow-sm cursor-pointer" title="Edit">
                                <i class="fa-solid fa-pen text-sm"></i>
                            </button>
                            <button onclick='deleteMember(${member.id})' class="w-10 h-10 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center transition shadow-sm cursor-pointer" title="Delete">
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
            order: [[0, 'asc']]
        });
    }

    // ---- View Card Logic ----
    let currentlyViewingMember = null;

    function viewMember(member) {
        currentlyViewingMember = member;
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
        const photoBadge = document.getElementById('view-card-photo-badge');
        
        const isExpired = !!member.is_expired || member.status === 'expired' || member.dynamic_status === 'expired';
        const isExpiringSoon = !!member.is_expiring_soon || member.dynamic_status === 'expiring';
        const isInactive = member.status === 'inactive' || member.dynamic_status === 'inactive';

        banner.className = "px-6 py-4 text-white flex items-center justify-between relative transition-colors duration-300 shrink-0 ";
        statusBadge.className = "px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-white shadow-sm transition-colors duration-300 ";

        if (isInactive) {
            statusBadge.textContent = 'INACTIVE';
            banner.className += "bg-gradient-to-r from-gray-500 to-gray-600";
            statusBadge.className += "text-gray-700";
            if (photoBadge) {
                photoBadge.className = "absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-gray-500 border-2 border-white flex items-center justify-center text-white text-[10px]";
                photoBadge.innerHTML = '<i class="fa-solid fa-minus"></i>';
            }
        } else if (isExpired) {
            statusBadge.textContent = 'EXPIRED';
            banner.className += "bg-gradient-to-r from-rose-500 to-red-600";
            statusBadge.className += "text-rose-600";
            if (photoBadge) {
                photoBadge.className = "absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-rose-500 border-2 border-white flex items-center justify-center text-white text-[10px]";
                photoBadge.innerHTML = '<i class="fa-solid fa-xmark"></i>';
            }
        } else if (isExpiringSoon) {
            statusBadge.textContent = 'EXPIRING SOON';
            banner.className += "bg-gradient-to-r from-amber-500 to-orange-600";
            statusBadge.className += "text-amber-600";
            if (photoBadge) {
                photoBadge.className = "absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-amber-500 border-2 border-white flex items-center justify-center text-white text-[10px]";
                photoBadge.innerHTML = '<i class="fa-solid fa-clock"></i>';
            }
        } else {
            statusBadge.textContent = 'ACTIVE';
            banner.className += "bg-gradient-to-r from-emerald-500 to-teal-600";
            statusBadge.className += "text-emerald-600";
            if (photoBadge) {
                photoBadge.className = "absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-emerald-500 border-2 border-white flex items-center justify-center text-white text-[10px]";
                photoBadge.innerHTML = '<i class="fa-solid fa-check"></i>';
            }
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
            document.getElementById('view-card-plan').textContent = 'No Active Plan';
        }

        const planStartForView = member.plan_start_date || member.joining_date;
        if (planStartForView && plan && plan.duration_months) {
            const startDate = new Date(planStartForView);
            const expiryDate = new Date(startDate);
            expiryDate.setMonth(expiryDate.getMonth() + parseInt(plan.duration_months));
            const startStr = startDate.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
            const expiryStr = expiryDate.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
            
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const isExp = today > expiryDate;
            const diffDays = Math.ceil((expiryDate - today) / (1000 * 60 * 60 * 24));

            if (isExp) {
                document.getElementById('view-card-validity').innerHTML = `${startStr} – ${expiryStr} <span class="ml-1.5 px-2 py-0.5 rounded-md text-[10px] font-black bg-rose-100 text-rose-700 uppercase">Expired</span>`;
            } else if (diffDays <= 7) {
                document.getElementById('view-card-validity').innerHTML = `${startStr} – ${expiryStr} <span class="ml-1.5 px-2 py-0.5 rounded-md text-[10px] font-black bg-amber-100 text-amber-800 uppercase">Expires in ${diffDays}d</span>`;
            } else {
                document.getElementById('view-card-validity').innerHTML = `${startStr} – ${expiryStr} <span class="ml-1.5 px-2 py-0.5 rounded-md text-[10px] font-black bg-emerald-100 text-emerald-700 uppercase">Active</span>`;
            }
        } else {
            document.getElementById('view-card-validity').textContent = 'No validity set';
        }
        
        document.getElementById('view-card-joined').textContent = member.joining_date ? new Date(member.joining_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : 'N/A';
        
        // Calculate Paid Amount for the Current Active Plan (NOT summing all previous lifetime cycles)
        let currentPlanTotal = member.total_amount ? parseFloat(member.total_amount) : (member.plan_amount ? parseFloat(member.plan_amount) : 0);
        let currentPlanPaid = 0;
        let currentPlanDue = 0;

        if (member.payments && member.payments.length > 0) {
            const sortedPayments = [...member.payments].sort((a, b) => (parseInt(b.id) || 0) - (parseInt(a.id) || 0));
            const currentPayment = sortedPayments[0];
            currentPlanPaid = parseFloat(currentPayment.paid_amount) || 0;
            currentPlanDue = parseFloat(currentPayment.due_amount) || 0;
            if (currentPayment.total_amount) {
                currentPlanTotal = parseFloat(currentPayment.total_amount);
            }
        } else {
            currentPlanPaid = currentPlanTotal;
            currentPlanDue = 0;
        }

        document.getElementById('view-card-total-amount').textContent = currentPlanTotal.toLocaleString('en-IN');
        document.getElementById('view-card-paid').textContent = currentPlanPaid.toLocaleString('en-IN');
        
        const dueEl = document.getElementById('view-card-due');
        const dueBox = document.getElementById('view-card-due-box');
        const dueLabel = document.getElementById('view-card-due-label');
        const dueWrapper = document.getElementById('view-card-due-wrapper');
        
        if (dueBox) {
            if (currentPlanDue > 0) {
                dueBox.className = 'p-3 rounded-xl text-center border transition-all bg-amber-50/90 border-amber-200';
                if (dueLabel) dueLabel.className = 'text-[10px] font-bold uppercase block tracking-tight text-amber-700';
                if (dueWrapper) dueWrapper.className = 'text-base sm:text-lg font-black block mt-0.5 text-amber-700';
                if (dueEl) dueEl.textContent = currentPlanDue.toLocaleString('en-IN');
            } else {
                dueBox.className = 'p-3 rounded-xl text-center border transition-all bg-emerald-50/70 border-emerald-100';
                if (dueLabel) dueLabel.className = 'text-[10px] font-bold uppercase block tracking-tight text-emerald-700';
                if (dueWrapper) dueWrapper.className = 'text-base sm:text-lg font-black block mt-0.5 text-emerald-700';
                if (dueEl) dueEl.textContent = '0 (Paid)';
            }
        }

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

    function openRenewFromActiveView() {
        if (!currentlyViewingMember) return;
        const memberId = currentlyViewingMember.id;
        closeViewModal();
        openRenewModal(memberId);
    }

    function openEditFromActiveView() {
        if (!currentlyViewingMember) return;
        const memberObj = currentlyViewingMember;
        closeViewModal();
        openEditWizard(memberObj);
    }

    // ---- Wizard Logic ----
    function openWizardModal() {
        isEditing = false;
        amountReceivedTouched = false;
        document.getElementById('wizard-title').textContent = 'New Member Registration';
        document.getElementById('member-form').reset();
        document.getElementById('photo-preview').src = '';
        document.getElementById('photo-preview').classList.add('hidden');
        document.getElementById('joining_date').valueAsDate = new Date();
        document.getElementById('plan_amount').value = 0;
        document.getElementById('discount').value = 0;
        document.getElementById('amount_received').value = 0;
        document.getElementById('total_amount_display').textContent = '0';
        document.getElementById('pending_amount_display').textContent = '0';
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
        amountReceivedTouched = false;
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
        document.getElementById('discount').value = (member.discount !== undefined && member.discount !== null) ? parseFloat(member.discount) : 0;
        
        let paidAmount = 0;
        if (member.payments && member.payments.length > 0) {
            const sortedPayments = [...member.payments].sort((a, b) => (parseInt(b.id) || 0) - (parseInt(a.id) || 0));
            paidAmount = parseFloat(sortedPayments[0].paid_amount) || 0;
        }
        document.getElementById('amount_received').value = paidAmount;
        
        calculateTotal(); // Trigger calculation based on selected plan (will not overwrite amount_received because isEditing = true)

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

    // ---- Renew Plan Logic (Next Cycle Only) ----
    let activeMemberCalculatedExpiry = '';
    let todayIsoString = '';
    let currentNetDifference = 0;

    function openRenewModal(id) {
        const member = membersData.find(m => m.id === id);
        if (!member) return;

        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const plan = member.plan;
        const planStartDate = member.plan_start_date || member.joining_date;

        // ─── EXPIRY CHECK: Block renew if plan is still active ───
        if (plan && plan.duration_months && planStartDate) {
            const startDate  = new Date(planStartDate);
            const expiryDate = new Date(startDate);
            expiryDate.setMonth(expiryDate.getMonth() + parseInt(plan.duration_months));
            expiryDate.setHours(0, 0, 0, 0);

            if (expiryDate > today) {
                // Plan is still ACTIVE → block renew, show info popup
                const expiryStr = expiryDate.toLocaleDateString('en-GB', { day: '2-digit', month: 'long', year: 'numeric' });
                showPlanActiveWarning(member.user?.name || 'Member', expiryStr, plan);
                return; // ← Do NOT open renew modal
            }
        }

        // ─── Plan is EXPIRED or has no plan → open renew modal ───
        document.getElementById('renew-modal').classList.remove('hidden');
        document.getElementById('renew_member_id').value = id;
        document.getElementById('renew-member-name').textContent = member.user?.name || 'Member';

        todayIsoString = today.toISOString().split('T')[0];

        const planTitle = plan && plan.plan_group_name
            ? `${plan.plan_group_name} (${plan.duration_months}M)`
            : 'No Active Plan';
        document.getElementById('renew-current-plan').textContent = planTitle;

        if (planStartDate && plan && plan.duration_months) {
            const startDate  = new Date(planStartDate);
            const expiryDate = new Date(startDate);
            expiryDate.setMonth(expiryDate.getMonth() + parseInt(plan.duration_months));
            const startStr  = startDate.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
            const expiryStr = expiryDate.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });

            activeMemberCalculatedExpiry = todayIsoString; // expired → start today
            document.getElementById('renew-current-badge').className = 'font-black px-2 py-0.5 rounded text-[10px] bg-rose-100 text-rose-700';
            document.getElementById('renew-current-badge').textContent = 'Expired';
            document.getElementById('renew-current-validity').textContent = `Expired on: ${expiryStr}`;
        } else {
            activeMemberCalculatedExpiry = today.toISOString().split('T')[0];
            document.getElementById('renew-current-badge').className = 'font-black px-2 py-0.5 rounded text-[10px] bg-gray-100 text-gray-700';
            document.getElementById('renew-current-badge').textContent = 'No Plan';
            document.getElementById('renew-current-validity').textContent = 'No previous plan found';
        }

        document.getElementById('renew_start_date').value = activeMemberCalculatedExpiry;
        document.getElementById('renew_discount').value = 0;
        document.getElementById('renew_paid').value = '';

        const select = document.getElementById('renew_plan_id');
        select.innerHTML = '<option value="">Select Plan</option>';
        plansData.forEach(p => {
            select.innerHTML += `<option value="${p.id}" data-amount="${p.amount}" data-duration="${p.duration_months}">${p.display_name} - ₹${p.amount}</option>`;
        });

        calculateRenewAmounts(true);
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

        calculateRenewAmounts(true);
    }

    function handleRenewModeChange() {
        if (document.getElementById('opt-extend').checked) {
            document.getElementById('renew_start_date').value = activeMemberCalculatedExpiry;
        } else {
            document.getElementById('renew_start_date').value = todayIsoString;
        }
        calculateRenewAmounts(true);
    }


    function closeRenewModal() {
        document.getElementById('renew-modal').classList.add('hidden');
    }

    function calculateRenewAmounts(updatePaidInput = true) {
        const select      = document.getElementById('renew_plan_id');
        const startDateVal = document.getElementById('renew_start_date').value || todayIsoString;
        const expiryTextEl = document.getElementById('renew-new-expiry-text');

        if (!select.value) {
            document.getElementById('renew_paid').value = '';
            expiryTextEl.textContent = 'Select plan to preview';
            document.getElementById('calc-new-price').textContent = '0';
            document.getElementById('calc-net-diff').textContent = '0';
            currentNetDifference = 0;
            calculateDueLive();
            return;
        }

        const option       = select.options[select.selectedIndex];
        const planAmount   = parseFloat(option.getAttribute('data-amount'))   || 0;
        const durationMonths = parseInt(option.getAttribute('data-duration')) || 1;
        const discount     = parseFloat(document.getElementById('renew_discount').value) || 0;
        const netPlanPrice = Math.max(0, planAmount - discount);

        document.getElementById('calc-new-price').textContent = planAmount.toLocaleString('en-IN');

        const discRow = document.getElementById('row-discount-preview');
        const discEl  = document.getElementById('calc-discount-preview');
        if (discRow && discEl) {
            if (discount > 0) { discRow.classList.remove('hidden'); discEl.textContent = discount.toLocaleString('en-IN'); }
            else              { discRow.classList.add('hidden'); }
        }

        currentNetDifference = netPlanPrice;
        document.getElementById('calc-net-diff').textContent = netPlanPrice.toLocaleString('en-IN');
        if (updatePaidInput) document.getElementById('renew_paid').value = netPlanPrice;

        const sDate    = new Date(startDateVal);
        const newExpiry = new Date(sDate);
        newExpiry.setMonth(newExpiry.getMonth() + durationMonths);
        const formattedNewExpiry = newExpiry.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
        expiryTextEl.textContent = `${formattedNewExpiry} (${durationMonths} Mo)`;

        calculateDueLive();
    }

    function calculateDueLive() {
        const paidVal      = parseFloat(document.getElementById('renew_paid').value) || 0;
        const remainingDue = Math.max(0, currentNetDifference - paidVal);
        const statusBox    = document.getElementById('renew-due-status-box');
        const statusIcon   = document.getElementById('renew-due-status-icon');
        const statusLabel  = document.getElementById('renew-due-status-label');
        const statusAmount = document.getElementById('renew-due-status-amount');
        if (!statusBox) return;

        if (remainingDue > 0) {
            statusBox.className   = 'p-3 rounded-xl text-xs font-medium flex items-center justify-between border transition-all bg-amber-50 border-amber-200 text-amber-900';
            statusIcon.className  = 'fa-solid fa-triangle-exclamation text-amber-600';
            statusLabel.textContent = 'Partial Payment (Due Pending)';
            statusAmount.className  = 'font-black text-xs sm:text-sm text-amber-700';
            statusAmount.textContent = `Remaining Due: ₹${remainingDue.toLocaleString('en-IN')}`;
        } else {
            statusBox.className   = 'p-3 rounded-xl text-xs font-medium flex items-center justify-between border transition-all bg-emerald-50 border-emerald-200 text-emerald-800';
            statusIcon.className  = 'fa-solid fa-circle-check text-emerald-600';
            statusLabel.textContent = 'Full Payment';
            statusAmount.className  = 'font-black text-xs sm:text-sm text-emerald-700';
            statusAmount.textContent = 'Remaining Due: ₹0';
        }
    }

    async function submitRenew(e) {
        e.preventDefault();
        const id        = document.getElementById('renew_member_id').value;
        const planId    = document.getElementById('renew_plan_id').value;
        const startDate = document.getElementById('renew_start_date').value || todayIsoString;

        if (!planId)    { showError('Please select a plan.'); return; }
        if (!startDate) { showError('Please set a start date.'); return; }

        const payload = {
            action_type     : 'renew',
            plan_id         : planId,
            start_date      : startDate,
            discount        : document.getElementById('renew_discount').value || 0,
            amount_received : document.getElementById('renew_paid').value || 0
        };

        const btn = document.getElementById('btn-renew-submit');
        const origText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
        btn.disabled  = true;

        try {
            const res  = await fetch(`/api/members/${id}/renew`, {
                method  : 'POST',
                headers : { 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body    : JSON.stringify(payload)
            });
            const data = await res.json();
            if (res.ok && data.success) {
                showSuccess('Member plan renewed successfully! New cycle started.');
                closeRenewModal();
                fetchMembers();
            } else {
                showError(data.message || 'Failed to renew member plan.');
            }
        } catch (err) {
            showError('Network error while renewing plan.');
        } finally {
            btn.innerHTML = origText;
            btn.disabled  = false;
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
        const option = select.selectedIndex >= 0 ? select.options[select.selectedIndex] : null;
        
        let planAmount = parseFloat(option ? option.getAttribute('data-amount') : 0) || 0;
        let discount = parseFloat(document.getElementById('discount').value) || 0;
        
        document.getElementById('plan_amount').value = planAmount;
        let total = planAmount - discount;
        if(total < 0) total = 0;
        
        document.getElementById('total_amount_display').textContent = total.toLocaleString();
        
        // ONLY auto-fill amount_received with total for NEW member registration if user hasn't typed a custom amount!
        // In Edit mode (isEditing = true), NEVER overwrite the member's existing paid amount!
        if (!isEditing && !amountReceivedTouched) {
            document.getElementById('amount_received').value = total;
        }
        
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
                else if (['password', 'joining_date', 'status'].includes(field)) firstStepWithError = 2;
                else if (['plan_id', 'discount', 'amount_received'].includes(field)) firstStepWithError = 3;
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
        form.append('joining_date', document.getElementById('joining_date').value);
        form.append('batch_id', document.getElementById('batch_id').value);
        form.append('trainer_id', document.getElementById('trainer_id').value);
        
        if (!isEditing) {
            form.append('plan_id', document.getElementById('plan_id').value);
            form.append('discount', document.getElementById('discount').value);
            form.append('amount_received', document.getElementById('amount_received').value);
        } else {
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

