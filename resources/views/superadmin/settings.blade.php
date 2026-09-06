@extends('superadmin.layouts.admin-layout')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-black text-gray-900 tracking-tight font-display flex items-center gap-2.5">
                <span>Super Admin Settings</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-indigo-50 text-[#5d5fef] border border-indigo-100">PROFILE</span>
            </h1>
            <p class="text-gray-500 text-xs font-semibold mt-1">Manage your Super Admin profile details, contact information, and master security password.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- 1. Profile Details Card -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-3 pb-4 border-b border-gray-100 mb-5">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-[#5d5fef] flex items-center justify-center font-bold text-base">
                    <i class="fa-solid fa-user-gear"></i>
                </div>
                <div>
                    <h2 class="font-bold text-base text-gray-900 font-display">Super Admin Profile</h2>
                    <p class="text-[11px] text-gray-400 font-medium">Update your platform admin identity</p>
                </div>
            </div>

            <form id="form-sa-profile" onsubmit="updateProfile(event)" class="space-y-4 text-xs">
                
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Full Name *</label>
                    <input type="text" id="sa-name" required placeholder="e.g. Jay" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:bg-white focus:border-[#5d5fef] text-xs font-bold text-gray-800">
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Master Email Address *</label>
                    <input type="email" id="sa-email" required placeholder="jay@gmail.com" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:bg-white focus:border-[#5d5fef] text-xs font-bold text-gray-800">
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Mobile Number (10 Digits) *</label>
                    <input type="text" id="sa-mobile" maxlength="10" required placeholder="9999999999" oninput="this.value=this.value.replace(/[^0-9]/g,'')" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:bg-white focus:border-[#5d5fef] text-xs font-bold text-gray-800">
                </div>

                <div class="pt-3 border-t border-gray-100 text-right">
                    <button type="submit" id="btn-save-profile" class="px-5 py-2.5 bg-[#5d5fef] hover:bg-[#4d4fe0] text-white font-bold rounded-xl shadow-md shadow-[#5d5fef]/20 transition-all cursor-pointer">
                        <span>Save Profile Changes</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- 2. Security & Password Card -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-3 pb-4 border-b border-gray-100 mb-5">
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center font-bold text-base">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <h2 class="font-bold text-base text-gray-900 font-display">Security & Password</h2>
                    <p class="text-[11px] text-gray-400 font-medium">Change master platform login password</p>
                </div>
            </div>

            <form id="form-sa-password" onsubmit="updatePassword(event)" class="space-y-4 text-xs">
                
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Current Password *</label>
                    <input type="password" id="sa-curr-password" required placeholder="Enter current password" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:bg-white focus:border-[#5d5fef] text-xs font-medium text-gray-800">
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">New Password *</label>
                    <input type="password" id="sa-new-password" required placeholder="Min 6 characters" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:bg-white focus:border-[#5d5fef] text-xs font-medium text-gray-800">
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Confirm New Password *</label>
                    <input type="password" id="sa-confirm-password" required placeholder="Re-type new password" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:bg-white focus:border-[#5d5fef] text-xs font-medium text-gray-800">
                </div>

                <div class="pt-3 border-t border-gray-100 text-right">
                    <button type="submit" id="btn-save-password" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl shadow-md shadow-rose-600/20 transition-all cursor-pointer">
                        <span>Update Master Password</span>
                    </button>
                </div>
            </form>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    function loadCurrentProfile() {
        const saUserStr = localStorage.getItem('superadmin_user') || sessionStorage.getItem('superadmin_user');
        if (saUserStr) {
            try {
                const user = JSON.parse(saUserStr);
                document.getElementById('sa-name').value = user.name || 'Jay';
                document.getElementById('sa-email').value = user.email || 'jay@gmail.com';
                document.getElementById('sa-mobile').value = user.mobile || '9999999999';
            } catch(e) {}
        }
    }

    async function updateProfile(e) {
        e.preventDefault();
        const token = localStorage.getItem('superadmin_token') || sessionStorage.getItem('superadmin_token');
        const btn = document.getElementById('btn-save-profile');

        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Saving...`;

        const payload = {
            name: document.getElementById('sa-name').value.trim(),
            email: document.getElementById('sa-email').value.trim(),
            mobile: document.getElementById('sa-mobile').value.trim(),
        };

        try {
            const res = await fetch('/api/superadmin/settings/profile', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (res.ok && data.success) {
                localStorage.setItem('superadmin_user', JSON.stringify(data.data.user));
                showToast('Super Admin profile updated successfully!', 'success');
                
                // Update sidebar/topbar name
                document.querySelectorAll('.sa-user-name').forEach(el => el.textContent = data.data.user.name);
            } else {
                showToast(data.message || 'Failed to update profile', 'error');
            }
        } catch (err) {
            showToast('Network error updating profile', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = `<span>Save Profile Changes</span>`;
        }
    }

    async function updatePassword(e) {
        e.preventDefault();
        const token = localStorage.getItem('superadmin_token') || sessionStorage.getItem('superadmin_token');
        const btn = document.getElementById('btn-save-password');

        const current_password = document.getElementById('sa-curr-password').value;
        const password = document.getElementById('sa-new-password').value;
        const password_confirmation = document.getElementById('sa-confirm-password').value;

        if (password !== password_confirmation) {
            showToast('New passwords do not match!', 'error');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Updating...`;

        try {
            const res = await fetch('/api/superadmin/settings/password', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ current_password, password, password_confirmation })
            });

            const data = await res.json();
            if (res.ok && data.success) {
                showToast('Master password changed successfully!', 'success');
                document.getElementById('form-sa-password').reset();
            } else {
                showToast(data.message || 'Failed to change password', 'error');
            }
        } catch (err) {
            showToast('Network error updating password', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = `<span>Update Master Password</span>`;
        }
    }

    document.addEventListener('DOMContentLoaded', loadCurrentProfile);
</script>
@endpush
