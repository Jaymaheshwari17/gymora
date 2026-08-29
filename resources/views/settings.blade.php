@extends('layouts.dashboard-layout')

@section('dashboard-content')
<!-- Settings Content -->
<div class="flex-1 overflow-y-auto p-8 bg-[#f8f9fc]">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-1">Settings</h1>
        <p class="text-gray-500 text-sm font-medium">Manage your profile and security preferences.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Profile Settings Card -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm h-fit">
            <h2 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                <i class="fa-solid fa-user text-indigo-600"></i> Profile Details
            </h2>
            
            <form id="profile-form" onsubmit="updateProfile(event)">
                <!-- Profile Photo (Placeholder UI) -->
                <div class="flex items-center gap-5 mb-6">
                    <img src="https://ui-avatars.com/api/?name=U&background=f3f4f6&color=374151" id="settings-avatar" class="w-16 h-16 rounded-full border border-gray-200 shadow-sm object-cover">
                    <div>
                        <label class="bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-xl text-xs font-semibold cursor-pointer transition-colors shadow-sm">
                            <i class="fa-solid fa-camera mr-1"></i> Change Photo
                            <input type="file" id="profile_photo" class="hidden" accept="image/*" onchange="previewPhoto(event)">
                        </label>
                        <p class="text-[10px] text-gray-400 mt-2 font-medium">JPG, PNG or GIF. Max size of 2MB.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <!-- Full Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Full Name</label>
                        <input type="text" id="profile_name" name="name" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all" placeholder="Enter your full name">
                        <p id="error-profile_name" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                    </div>

                    <!-- Email Address -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
                        <input type="email" id="profile_email" name="email" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all" placeholder="Enter email address">
                        <p id="error-profile_email" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                    </div>

                    <!-- Mobile Number -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Mobile Number</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm font-medium">+91</span>
                            </div>
                            <input type="text" id="profile_mobile" name="mobile" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full pl-12 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all" placeholder="10-digit mobile number">
                        </div>
                        <p id="error-profile_mobile" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Gender -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Gender</label>
                            <select id="profile_gender" name="gender" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all">
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            <p id="error-profile_gender" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                        </div>

                        <!-- Date of Birth -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Date of Birth</label>
                            <input type="date" id="profile_dob" name="dob" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all">
                            <p id="error-profile_dob" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 border-t border-gray-100 pt-5 text-right">
                    <button type="submit" id="btn-profile-submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-indigo-700 transition shadow-md shadow-indigo-600/20 disabled:opacity-70 flex items-center gap-2 ml-auto">
                        <span>Save Changes</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Security / Password Card -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm h-fit">
            <h2 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                <i class="fa-solid fa-shield-halved text-[#d9229b]"></i> Security Settings
            </h2>
            
            <form id="password-form" onsubmit="changePassword(event)">
                <div class="space-y-4">
                    <!-- Current Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Current Password</label>
                        <div class="relative">
                            <input type="password" id="current_password" name="current_password" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all" placeholder="Enter current password">
                            <button type="button" onclick="togglePassword('current_password', this)" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        <p id="error-current_password" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                    </div>

                    <!-- New Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">New Password</label>
                        <div class="relative">
                            <input type="password" id="new_password" name="new_password" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all" placeholder="Minimum 8 characters">
                            <button type="button" onclick="togglePassword('new_password', this)" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        <p id="error-new_password" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm New Password</label>
                        <div class="relative">
                            <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all" placeholder="Must match new password">
                            <button type="button" onclick="togglePassword('new_password_confirmation', this)" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        <p id="error-new_password_confirmation" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                    </div>
                </div>

                <div class="mt-6 border-t border-gray-100 pt-5 text-right">
                    <button type="submit" id="btn-password-submit" class="bg-gray-900 text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-black transition shadow-md shadow-gray-900/20 disabled:opacity-70 flex items-center gap-2 ml-auto">
                        <span>Update Password</span>
                    </button>
                </div>
            </form>
        </div>
        
    </div>
    
    <!-- Gym Profile Settings Card (Only for Owners) -->
    <div id="gym-profile-section" class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm mt-8 hidden">
        <h2 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
            <i class="fa-solid fa-building text-indigo-600"></i> Gym Details
        </h2>
        
        <form id="gym-form" onsubmit="updateGymProfile(event)">
            <!-- Gym Logo -->
            <div class="flex items-center gap-5 mb-6">
                <div class="w-16 h-16 bg-indigo-50 rounded-xl flex items-center justify-center border border-gray-200 overflow-hidden shadow-sm">
                    <img id="gym-logo-preview" src="" class="w-full h-full object-cover hidden">
                    <i id="gym-logo-icon" class="fa-solid fa-dumbbell text-2xl text-indigo-300"></i>
                </div>
                <div>
                    <label class="bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-xl text-xs font-semibold cursor-pointer transition-colors shadow-sm inline-flex items-center">
                        <i class="fa-solid fa-camera mr-1.5"></i> Upload Gym Logo
                        <input type="file" id="gym_logo" name="logo" class="hidden" accept="image/*" onchange="previewGymLogo(event)">
                    </label>
                    <p class="text-[10px] text-gray-400 mt-2 font-medium">Recommended: Square image. JPG/PNG.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Gym Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Gym Name <span class="text-red-500">*</span></label>
                    <input type="text" id="gym_name" name="gym_name" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 outline-none text-sm" placeholder="Enter Gym Name">
                    <p id="error-gym_name" class="text-red-500 text-xs mt-1.5 hidden"></p>
                </div>

                <!-- Owner Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Owner Name <span class="text-red-500">*</span></label>
                    <input type="text" id="gym_owner_name" name="owner_name" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 outline-none text-sm" placeholder="Owner Name">
                    <p id="error-owner_name" class="text-red-500 text-xs mt-1.5 hidden"></p>
                </div>

                <!-- Contact Number -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Gym Contact Number</label>
                    <input type="text" id="gym_contact_number" name="contact_number" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 outline-none text-sm" placeholder="Contact Number">
                </div>

                <!-- GST Number -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">GST / Tax Number</label>
                    <input type="text" id="gym_gst_number" name="gst_number" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 outline-none text-sm" placeholder="GSTIN">
                </div>

                <!-- Instagram Link -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Instagram Link</label>
                    <input type="url" id="gym_instagram" name="instagram_link" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 outline-none text-sm" placeholder="https://instagram.com/...">
                </div>

                <!-- Facebook Link -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Facebook Link</label>
                    <input type="url" id="gym_facebook" name="facebook_link" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 outline-none text-sm" placeholder="https://facebook.com/...">
                </div>

                <!-- Gym Address (Full Width) -->
                <div class="md:col-span-2 lg:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Gym Address</label>
                    <textarea id="gym_address" name="address" rows="3" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 outline-none text-sm" placeholder="Full address for invoices"></textarea>
                </div>
            </div>

            <div class="mt-6 border-t border-gray-100 pt-5 text-right">
                <button type="submit" id="btn-gym-submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-indigo-700 transition shadow-md shadow-indigo-600/20 disabled:opacity-70 flex items-center gap-2 ml-auto">
                    <span>Save Gym Details</span>
                </button>
            </div>
        </form>
    </div>
    <!-- Bottom spacing -->
    <div class="h-10"></div>
</div>

@push('page-scripts')
<script>
    // Toggle Password Visibility
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // Load User Data into Profile Form
    document.addEventListener('DOMContentLoaded', () => {
        if (user) {
            document.getElementById('profile_name').value = user.name || '';
            document.getElementById('profile_email').value = user.email || '';
            document.getElementById('profile_mobile').value = user.mobile || '';
            document.getElementById('profile_gender').value = user.gender || '';
            if(user.dob) document.getElementById('profile_dob').value = user.dob.substring(0, 10);
            
            // Set Avatar
            if (user.photo) {
                document.getElementById('settings-avatar').src = '/' + user.photo;
            } else {
                const initials = user.name.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase();
                document.getElementById('settings-avatar').src = `https://ui-avatars.com/api/?name=${initials}&background=f3f4f6&color=374151&size=128`;
            }
        }
    });

    // Preview photo before upload
    function previewPhoto(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('settings-avatar').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    }

    // Helper to clear errors
    function clearErrors(prefix, fields) {
        fields.forEach(field => {
            const input = document.getElementById(field);
            const errorP = document.getElementById(`error-${prefix}_${field}`) || document.getElementById(`error-${field}`);
            if (input) {
                input.classList.remove('border-red-500', 'bg-red-50', 'focus:ring-red-500');
                input.classList.add('border-gray-200', 'bg-gray-50', 'focus:ring-indigo-600');
            }
            if (errorP) {
                errorP.textContent = '';
                errorP.classList.add('hidden');
            }
        });
    }

    // Helper to show errors
    function showErrors(prefix, errors) {
        for (const [field, messages] of Object.entries(errors)) {
            const input = document.getElementById(prefix ? `${prefix}_${field}` : field);
            const errorP = document.getElementById(prefix ? `error-${prefix}_${field}` : `error-${field}`);
            
            if (input) {
                input.classList.remove('border-gray-200', 'bg-gray-50', 'focus:ring-indigo-600');
                input.classList.add('border-red-500', 'bg-red-50', 'focus:ring-red-500');
            }
            if (errorP) {
                errorP.textContent = messages[0];
                errorP.classList.remove('hidden');
            }
        }
    }

    // Removed local showToast since we use global showSuccess()

    // Update Profile API
    async function updateProfile(e) {
        e.preventDefault();
        
        const btn = document.getElementById('btn-profile-submit');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Saving...';
        btn.disabled = true;

        const fields = ['name', 'email', 'mobile'];
        clearErrors('profile', fields);

        const formData = new FormData();
        formData.append('_method', 'PUT'); // Laravel requirement for FormData PUT
        formData.append('name', document.getElementById('profile_name').value);
        formData.append('email', document.getElementById('profile_email').value);
        formData.append('mobile', document.getElementById('profile_mobile').value);
        formData.append('gender', document.getElementById('profile_gender').value);
        formData.append('dob', document.getElementById('profile_dob').value);
        
        const photoInput = document.getElementById('profile_photo');
        if (photoInput.files.length > 0) {
            formData.append('photo', photoInput.files[0]);
        }

        try {
            const response = await fetch('/api/settings/profile', {
                method: 'POST', // Use POST with _method=PUT to support FormData
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok && data.success) {
                showSuccess('Profile updated successfully!');
                
                // Update local storage user object
                const updatedUser = { ...user, ...data.data };
                localStorage.setItem('user', JSON.stringify(updatedUser));
                user = updatedUser;
                
                // Update topbar instantly
                const topbarNameEl = document.getElementById('topbar-name');
                if (topbarNameEl) topbarNameEl.textContent = user.name;
                
                let avatarUrl = '';
                if (user.photo) {
                    avatarUrl = '/' + user.photo;
                } else {
                    const initials = user.name.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase();
                    avatarUrl = `https://ui-avatars.com/api/?name=${initials}&background=f3f4f6&color=374151`;
                }
                
                const topbarAvatar = document.getElementById('topbar-avatar');
                const settingsAvatar = document.getElementById('settings-avatar');
                if (topbarAvatar) topbarAvatar.src = avatarUrl;
                if (settingsAvatar) settingsAvatar.src = user.photo ? avatarUrl : avatarUrl + '&size=128';

            } else {
                if (data.errors) {
                    showErrors('profile', data.errors);
                } else {
                    showError(data.message || 'Failed to update profile.');
                }
            }
        } catch (error) {
            console.error('Error:', error);
            showError('An error occurred. Please try again.');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }

    // Change Password API
    async function changePassword(e) {
        e.preventDefault();
        
        const btn = document.getElementById('btn-password-submit');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Updating...';
        btn.disabled = true;

        const fields = ['current_password', 'new_password', 'new_password_confirmation'];
        clearErrors('', fields);

        const payload = {
            current_password: document.getElementById('current_password').value,
            new_password: document.getElementById('new_password').value,
            new_password_confirmation: document.getElementById('new_password_confirmation').value,
        };

        try {
            const response = await fetch('/api/settings/change-password', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (response.ok && data.success) {
                showSuccess('Password changed successfully!');
                document.getElementById('password-form').reset();
            } else {
                if (data.errors) {
                    showErrors('', data.errors);
                } else if (data.message === 'Current password does not match.') {
                    showErrors('', { current_password: [data.message] });
                } else {
                    showError(data.message || 'Failed to change password.');
                }
            }
        } catch (error) {
            console.error('Error:', error);
            showError('An error occurred. Please try again.');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }
    // Gym Profile Image Preview
    function previewGymLogo(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('gym-logo-preview').src = e.target.result;
                document.getElementById('gym-logo-preview').classList.remove('hidden');
                document.getElementById('gym-logo-icon').classList.add('hidden');
            }
            reader.readAsDataURL(file);
        }
    }

    // Load Gym Profile
    async function loadGymProfile() {
        // Show for owner or gym_owner roles
        const userRole = user?.role || '';
        if (!user || (userRole !== 'owner' && userRole !== 'gym_owner')) {
            return; // Only hide for non-owners
        }
        
        document.getElementById('gym-profile-section').classList.remove('hidden');

        try {
            const response = await fetch('/api/settings/gym', {
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                }
            });
            const result = await response.json();
            
            if (response.ok && result.success) {
                const gym = result.data;
                document.getElementById('gym_name').value = gym.gym_name || '';
                document.getElementById('gym_owner_name').value = gym.owner_name || '';
                document.getElementById('gym_contact_number').value = gym.contact_number || '';
                document.getElementById('gym_gst_number').value = gym.gst_number || '';
                document.getElementById('gym_instagram').value = gym.instagram_link || '';
                document.getElementById('gym_facebook').value = gym.facebook_link || '';
                document.getElementById('gym_address').value = gym.address || '';
                
                if (gym.logo_url) {
                    document.getElementById('gym-logo-preview').src = gym.logo_url;
                    document.getElementById('gym-logo-preview').classList.remove('hidden');
                    document.getElementById('gym-logo-icon').classList.add('hidden');
                }
            }
        } catch(e) {
            console.error('Error loading gym profile', e);
        }
    }

    // Update Gym Profile
    async function updateGymProfile(e) {
        e.preventDefault();
        
        const btn = document.getElementById('btn-gym-submit');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Saving...';
        btn.disabled = true;

        const formData = new FormData();
        formData.append('gym_name', document.getElementById('gym_name').value);
        formData.append('owner_name', document.getElementById('gym_owner_name').value);
        formData.append('contact_number', document.getElementById('gym_contact_number').value);
        formData.append('gst_number', document.getElementById('gym_gst_number').value);
        formData.append('instagram_link', document.getElementById('gym_instagram').value);
        formData.append('facebook_link', document.getElementById('gym_facebook').value);
        formData.append('address', document.getElementById('gym_address').value);
        
        const logoInput = document.getElementById('gym_logo');
        if (logoInput.files.length > 0) {
            formData.append('logo', logoInput.files[0]);
        }

        try {
            const response = await fetch('/api/settings/gym', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok && data.success) {
                showSuccess('Gym Details saved successfully!');
                
                // Update sidebar gym name instantly
                const sidebarName = document.getElementById('sidebar-logo-gym-name');
                if (sidebarName) sidebarName.textContent = data.data.gym_name;

                // Update sidebar logo instantly
                if (data.data.logo_url) {
                    const sbLogoImg = document.getElementById('sidebar-gym-logo-img');
                    const sbLogoIcon = document.getElementById('sidebar-gym-logo-icon');
                    if (sbLogoImg && sbLogoIcon) {
                        sbLogoImg.src = data.data.logo_url;
                        sbLogoImg.style.display = 'block';
                        sbLogoIcon.style.display = 'none';
                    }
                }

                // Update owner user object locally
                user.name = data.data.owner_name;
                if(user.gym) {
                    user.gym.name = data.data.gym_name;
                    if (data.data.logo_url) {
                        user.gym.logo = data.data.logo_url;
                    }
                }
                localStorage.setItem('user', JSON.stringify(user));
                
                const topbarNameEl = document.getElementById('topbar-name');
                if (topbarNameEl) topbarNameEl.textContent = user.name;
            } else {
                showError(data.message || 'Failed to save gym details.');
            }
        } catch (error) {
            console.error('Error:', error);
            showError('An error occurred. Please try again.');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }

    document.addEventListener('DOMContentLoaded', async () => {
        // If user is already loaded from localStorage, call immediately
        if (window.user || (typeof user !== 'undefined' && user)) {
            loadGymProfile();
        } else {
            // Fallback: fetch from API
            try {
                const token = localStorage.getItem('token') || sessionStorage.getItem('token');
                if (token) {
                    const res = await fetch('/api/user', { headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' } });
                    const data = await res.json();
                    if (data.success && data.data.role === 'owner') {
                        document.getElementById('gym-profile-section').classList.remove('hidden');
                        loadGymProfile();
                    }
                }
            } catch(e) {}
        }
    });
</script>
@endpush
@endsection
