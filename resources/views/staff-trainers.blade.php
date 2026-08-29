@extends('layouts.dashboard-layout')

@section('dashboard-content')
<div class="flex-1 overflow-y-auto p-8 bg-[#f8f9fc]">
    <!-- Header -->
    <div class="flex justify-between items-end mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-1">Staff & Trainers</h1>
            <p class="text-gray-500 text-sm font-medium">Manage your gym's team members and their roles.</p>
        </div>
        <div class="flex items-center gap-4">
            <select id="roleFilter" onchange="filterRole()" class="bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-medium outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent cursor-pointer shadow-sm">
                <option value="">All Roles</option>
                <option value="Staff">Staff</option>
                <option value="Trainer">Trainer</option>
            </select>
            <button onclick="openModal()" class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-700 transition flex items-center gap-2 shadow-lg shadow-indigo-600/20">
                <i class="fa-solid fa-user-plus"></i> Add Team Member
            </button>
        </div>
    </div>

    <!-- Team Data Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden p-6">
        <table id="staffTable" class="w-full text-left border-collapse" width="100%">
            <thead>
                <tr>
                    <th class="w-16">Sr No</th>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Contact</th>
                    <th class="text-center w-32">Actions</th>
                </tr>
            </thead>
            <tbody id="team-tbody">
                <!-- Data will be loaded here via JS -->
            </tbody>
        </table>
    </div>
</div>

<!-- View Member Modal -->
<div id="view-member-modal" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" onclick="closeViewModal()"></div>
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden relative z-10 w-full max-w-sm flex flex-col transform transition-all">
        <!-- Close btn -->
        <button onclick="closeViewModal()" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-800 flex items-center justify-center transition z-20">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="p-6 flex flex-col items-center text-center border-b border-gray-50 relative pt-8">
            <img id="view-photo" src="" alt="Profile" class="w-24 h-24 rounded-full object-cover shadow-md mb-4 border-4 border-white">
            <h3 id="view-name" class="text-xl font-bold text-gray-900 leading-tight mb-1">Name</h3>
            <span id="view-role-badge" class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider mb-4">Role</span>
            
            <div class="w-full flex flex-col gap-2 text-sm font-medium text-gray-600 mt-2">
                <div class="flex items-center justify-center gap-2">
                    <i class="fa-solid fa-phone text-gray-400 w-4"></i> <span id="view-mobile">Mobile</span>
                </div>
                <div class="flex items-center justify-center gap-2">
                    <i class="fa-regular fa-envelope text-gray-400 w-4"></i> <span id="view-email">Email</span>
                </div>
                <div class="flex items-center justify-center gap-4 mt-1 border-t border-gray-100 pt-2 text-xs">
                    <div class="flex items-center gap-1"><i class="fa-solid fa-venus-mars text-gray-400"></i> <span id="view-gender" class="capitalize">N/A</span></div>
                    <div class="flex items-center gap-1"><i class="fa-solid fa-cake-candles text-gray-400"></i> <span id="view-dob">N/A</span></div>
                </div>
            </div>
        </div>
        
        <div id="view-trainer-fields" class="px-6 py-5 bg-gray-50 flex-1 hidden">
            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Trainer Info</h4>
            <div class="flex justify-between items-center text-sm mb-3">
                <span class="text-gray-500 font-medium">Specialization</span>
                <span id="view-spec" class="font-bold text-gray-800"></span>
            </div>
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-500 font-medium">Experience</span>
                <span id="view-exp" class="font-bold text-gray-800"></span>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="team-modal" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
    
    <!-- Modal Content -->
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden flex flex-col max-h-[95vh]">
        
        <!-- Modal Header -->
        <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="text-lg font-bold text-gray-900" id="modal-title">Add Team Member</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 overflow-y-auto">
            <form id="team-form" onsubmit="saveTeamMember(event)">
                <input type="hidden" id="edit_id">
                
                <div class="space-y-6">
                    
                    <!-- Role Selection -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Role <span class="text-red-500">*</span></label>
                        <div class="flex gap-4">
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="role" value="staff" class="peer hidden" checked onchange="toggleTrainerFields()">
                                <div class="border-2 border-gray-100 rounded-xl p-4 flex flex-col items-center gap-2 hover:bg-gray-50 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 transition-all text-gray-400 peer-checked:text-indigo-600">
                                    <i class="fa-solid fa-user-tie text-2xl"></i>
                                    <span class="font-bold text-sm text-gray-700">Staff</span>
                                </div>
                            </label>
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="role" value="trainer" class="peer hidden" onchange="toggleTrainerFields()">
                                <div class="border-2 border-gray-100 rounded-xl p-4 flex flex-col items-center gap-2 hover:bg-gray-50 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 transition-all text-gray-400 peer-checked:text-indigo-600">
                                    <i class="fa-solid fa-dumbbell text-2xl"></i>
                                    <span class="font-bold text-sm text-gray-700">Trainer</span>
                                </div>
                            </label>
                        </div>
                        <p id="error-role" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                    </div>

                    <!-- Photo Upload -->
                    <div class="flex items-center gap-5">
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

                    <!-- Personal Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" id="name" placeholder="John Doe" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all">
                            <p id="error-name" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Mobile Number <span class="text-red-500">*</span></label>
                            <input type="text" id="mobile" placeholder="10-digit mobile" maxlength="10" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all">
                            <p id="error-mobile" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email Address <span class="text-red-500">*</span></label>
                            <input type="email" id="email" placeholder="john@example.com" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all">
                            <p id="error-email" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Gender</label>
                            <select id="gender" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all">
                                <option value="">Select</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            <p id="error-gender" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Date of Birth</label>
                            <input type="date" id="dob" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all">
                            <p id="error-dob" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                        </div>
                    </div>

                    <!-- Trainer Specific Fields (Hidden by default) -->
                    <div id="trainer-fields" class="hidden animate-fade-in">
                        <div class="p-5 bg-indigo-50/50 border border-purple-100 rounded-xl">
                            <h4 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <i class="fa-solid fa-medal text-indigo-600"></i> Trainer Details
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Specialization <span class="text-red-500">*</span></label>
                                    <input type="text" id="specialization" placeholder="e.g. Yoga, Crossfit" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all">
                                    <p id="error-specialization" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Experience (Years) <span class="text-red-500">*</span></label>
                                    <input type="number" id="experience_years" min="0" placeholder="e.g. 3" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all">
                                    <p id="error-experience_years" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-4 border-t border-gray-100">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Password <span class="text-red-500 required-star">*</span></label>
                            <input type="password" id="password" placeholder="••••••••" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all">
                            <p class="text-[10px] text-gray-500 mt-1" id="password-hint">Min 8 chars, letters, numbers & symbols</p>
                            <p id="error-password" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Confirm Password <span class="text-red-500 required-star">*</span></label>
                            <input type="password" id="password_confirmation" placeholder="••••••••" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all">
                            <p id="error-password_confirmation" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="mt-8 flex gap-3 justify-end pt-5 border-t border-gray-100">
                    <button type="button" onclick="closeModal()" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition">Cancel</button>
                    <button type="submit" id="btn-save" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition shadow-md shadow-indigo-600/20 flex items-center gap-2">
                        <span>Save Member</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('page-scripts')
<script>
    let teamData = [];
    let dataTable = null;

    // Load Team
    async function loadTeam() {
        showLoader();
        const container = document.getElementById('team-container');
        
        try {
            const response = await fetch('/api/staff-trainers', {
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();

            if (response.ok && data.success) {
                teamData = data.data;
                renderTable();
            } else {
                showError('Failed to load team members');
            }
        } catch (error) {
            console.error(error);
            showError('An error occurred while loading');
        } finally {
            hideLoader();
        }
    }

    // Render Table
    function renderTable() {
        if (dataTable) {
            dataTable.destroy();
        }

        const tbody = document.getElementById('team-tbody');
        let html = '';
        
        teamData.forEach((member, index) => {
            const isTrainer = member.role === 'trainer';
            const roleText = isTrainer ? 'Trainer' : 'Staff';
            const roleBadgeClass = isTrainer ? 'bg-indigo-100 text-indigo-600' : 'bg-blue-100 text-blue-700';
            const photoUrl = member.photo ? `/${member.photo}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(member.name)}&background=f3f4f6&color=6b7280`;

            html += `
                <tr>
                    <td class="font-bold text-gray-500">${index + 1}</td>
                    <td>
                        <img src="${photoUrl}" alt="${member.name}" class="w-10 h-10 rounded-full object-cover shadow-sm border border-gray-100">
                    </td>
                    <td>
                        <div class="font-bold text-gray-900">${member.name}</div>
                        ${member.gender ? `<div class="text-[10px] text-gray-400 capitalize mt-0.5"><i class="fa-solid fa-venus-mars"></i> ${member.gender}</div>` : ''}
                    </td>
                    <td>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider ${roleBadgeClass}">
                            ${roleText}
                        </span>
                    </td>
                    <td>
                        <div class="text-sm font-medium text-gray-900">${member.mobile}</div>
                        <div class="text-xs text-gray-500 mt-0.5">${member.email}</div>
                    </td>
                    <td class="text-center">
                        <div class="flex justify-center gap-2">
                            <button onclick='viewMember(${JSON.stringify(member).replace(/'/g, "&#39;")})' class="w-10 h-10 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 flex items-center justify-center transition" title="View Details">
                                <i class="fa-solid fa-eye text-sm"></i>
                            </button>
                            <button onclick='openEditModal(${JSON.stringify(member).replace(/'/g, "&#39;")})' class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition" title="Edit">
                                <i class="fa-solid fa-pen text-sm"></i>
                            </button>
                            <button onclick="deleteMember(${member.id})" class="w-10 h-10 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition" title="Delete">
                                <i class="fa-solid fa-trash text-sm"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        tbody.innerHTML = html;

        // Initialize DataTable
        dataTable = $('#staffTable').DataTable({
            pageLength: 10,
            ordering: true,
            responsive: true,
            language: {
                search: "",
                searchPlaceholder: "Search members..."
            },
            columnDefs: [
                { orderable: false, targets: [0, 1, 5] } // Disable sorting on Sr No, Photo, and Actions
            ],
            order: [[2, 'asc']] // Default sort by Name (index 2 now because of Sr No)
        });
    }

    function filterRole() {
        if (!dataTable) return;
        const role = document.getElementById('roleFilter').value;
        // The Role column is index 3 now (Sr No, Photo, Name, Role)
        dataTable.column(3).search(role).draw();
    }

    // View Modal Logic
    const viewModal = document.getElementById('view-member-modal');
    function viewMember(member) {
        const isTrainer = member.role === 'trainer';
        const roleBadgeClass = isTrainer ? 'bg-indigo-100 text-indigo-600' : 'bg-blue-100 text-blue-700';
        const photoUrl = member.photo ? `/${member.photo}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(member.name)}&background=f3f4f6&color=6b7280`;

        document.getElementById('view-photo').src = photoUrl;
        document.getElementById('view-name').textContent = member.name;
        
        const badge = document.getElementById('view-role-badge');
        badge.className = `px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider mb-4 ${roleBadgeClass}`;
        badge.textContent = isTrainer ? 'Trainer' : 'Staff';

        document.getElementById('view-mobile').textContent = member.mobile;
        document.getElementById('view-email').textContent = member.email;
        document.getElementById('view-gender').textContent = member.gender || 'N/A';
        document.getElementById('view-dob').textContent = member.dob ? new Date(member.dob).toLocaleDateString('en-GB') : 'N/A';

        const trainerFields = document.getElementById('view-trainer-fields');
        if (isTrainer) {
            document.getElementById('view-spec').textContent = member.specialization || 'N/A';
            document.getElementById('view-exp').textContent = member.experience_years ? member.experience_years + ' Years' : 'N/A';
            trainerFields.classList.remove('hidden');
        } else {
            trainerFields.classList.add('hidden');
        }

        viewModal.classList.remove('hidden');
    }

    function closeViewModal() {
        viewModal.classList.add('hidden');
    }

    // Dynamic UI Logic
    function toggleTrainerFields() {
        const isTrainer = document.querySelector('input[name="role"]:checked').value === 'trainer';
        const fields = document.getElementById('trainer-fields');
        if (isTrainer) {
            fields.classList.remove('hidden');
        } else {
            fields.classList.add('hidden');
            document.getElementById('specialization').value = '';
            document.getElementById('experience_years').value = '';
        }
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

    // Validation UI
    function clearErrors() {
        const fields = ['role', 'photo', 'name', 'mobile', 'email', 'gender', 'dob', 'specialization', 'experience_years', 'password', 'password_confirmation'];
        fields.forEach(id => {
            const el = document.getElementById(id);
            const err = document.getElementById('error-' + id);
            if (el && el.type !== 'file') el.classList.remove('border-red-500', 'bg-red-50');
            if (err) err.classList.add('hidden');
        });
    }

    function showErrors(errors) {
        Object.keys(errors).forEach(field => {
            const el = document.getElementById(field);
            const err = document.getElementById('error-' + field);
            if (el && el.type !== 'file' && el.type !== 'radio') {
                el.classList.add('border-red-500', 'bg-red-50');
            }
            if (err) {
                err.textContent = errors[field][0];
                err.classList.remove('hidden');
            }
        });
    }

    // Modals
    const modal = document.getElementById('team-modal');
    let isEditing = false;
    
    function openModal() {
        isEditing = false;
        document.getElementById('team-form').reset();
        document.getElementById('edit_id').value = '';
        document.getElementById('photo-preview').classList.add('hidden');
        document.getElementById('modal-title').textContent = 'Add Team Member';
        
        // Show password stars
        document.querySelectorAll('.required-star').forEach(el => el.classList.remove('hidden'));
        document.getElementById('password-hint').textContent = "Min 8 chars, letters, numbers & symbols";
        
        document.querySelector('input[name="role"][value="staff"]').checked = true;
        toggleTrainerFields();
        clearErrors();
        modal.classList.remove('hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
    }

    function openEditModal(member) {
        isEditing = true;
        clearErrors();
        document.getElementById('edit_id').value = member.id;
        document.getElementById('modal-title').textContent = 'Edit Team Member';
        
        // Set basic fields
        document.getElementById('name').value = member.name;
        document.getElementById('email').value = member.email;
        document.getElementById('mobile').value = member.mobile;
        document.getElementById('gender').value = member.gender || '';
        document.getElementById('dob').value = member.dob || '';
        
        // Role
        document.querySelector(`input[name="role"][value="${member.role}"]`).checked = true;
        toggleTrainerFields();
        
        // Trainer fields
        if (member.role === 'trainer') {
            document.getElementById('specialization').value = member.specialization || '';
            document.getElementById('experience_years').value = member.experience_years || '';
        }

        // Photo
        const preview = document.getElementById('photo-preview');
        if (member.photo) {
            preview.src = `/${member.photo}`;
            preview.classList.remove('hidden');
        } else {
            preview.classList.add('hidden');
        }

        // Passwords (optional on edit)
        document.getElementById('password').value = '';
        document.getElementById('password_confirmation').value = '';
        document.querySelectorAll('.required-star').forEach(el => el.classList.add('hidden'));
        document.getElementById('password-hint').textContent = "Leave blank to keep current password";

        modal.classList.remove('hidden');
    }

    // Save Data (Add / Edit)
    async function saveTeamMember(e) {
        e.preventDefault();
        clearErrors();
        showLoader();
        
        const form = new FormData();
        const role = document.querySelector('input[name="role"]:checked').value;
        form.append('role', role);
        form.append('name', document.getElementById('name').value);
        form.append('email', document.getElementById('email').value);
        form.append('mobile', document.getElementById('mobile').value);
        form.append('gender', document.getElementById('gender').value);
        form.append('dob', document.getElementById('dob').value);
        
        const photoFile = document.getElementById('photo').files[0];
        if (photoFile) {
            form.append('photo', photoFile);
        }

        if (role === 'trainer') {
            form.append('specialization', document.getElementById('specialization').value);
            form.append('experience_years', document.getElementById('experience_years').value);
        }

        const pass = document.getElementById('password').value;
        const passConf = document.getElementById('password_confirmation').value;
        if (pass || !isEditing) {
            form.append('password', pass);
            form.append('password_confirmation', passConf);
        }

        let url = '/api/staff-trainers';
        
        if (isEditing) {
            const id = document.getElementById('edit_id').value;
            url = `/api/staff-trainers/${id}`;
            form.append('_method', 'PUT'); // required for FormData update in Laravel
        }

        try {
            const response = await fetch(url, {
                method: 'POST', // always POST when using FormData
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                },
                body: form
            });

            const data = await response.json();

            if (response.ok && data.success) {
                showSuccess(`Member ${isEditing ? 'updated' : 'created'} successfully!`);
                closeModal();
                loadTeam();
            } else {
                if (data.errors) {
                    showErrors(data.errors);
                } else {
                    showError(data.message || 'Failed to save member.');
                }
            }
        } catch (error) {
            console.error('Error:', error);
            showError('An error occurred. Please try again.');
        } finally {
            hideLoader();
        }
    }

    // Delete Member
    function deleteMember(id) {
        confirmDelete('Delete Member?', 'Are you sure you want to remove this team member?', async () => {
            showLoader();
            try {
                const response = await fetch(`/api/staff-trainers/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    showSuccess('Member deleted successfully!');
                    loadTeam();
                } else {
                    showError(data.message || 'Failed to delete member.');
                }
            } catch (error) {
                console.error('Error:', error);
                showError('An error occurred while deleting.');
            } finally {
                hideLoader();
            }
        });
    }

    // Initial Load
    document.addEventListener('DOMContentLoaded', loadTeam);
</script>
<style>
    .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush
@endsection
