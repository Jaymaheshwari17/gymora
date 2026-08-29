@extends('layouts.dashboard-layout')

@section('dashboard-content')
<div class="flex-1 overflow-y-auto p-8 bg-[#f8f9fc]">
    <!-- Header -->
    <div class="flex justify-between items-end mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-1">Batches Management</h1>
            <p class="text-gray-500 text-sm font-medium">Create and manage your gym's timing batches.</p>
        </div>
        <button onclick="openModal()" class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-700 transition flex items-center gap-2 shadow-lg shadow-indigo-600/20">
            <i class="fa-solid fa-plus"></i> Add New Batch
        </button>
    </div>

    <!-- Batches List -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden p-6">
        <table id="batchesTable" class="w-full text-left border-collapse" width="100%">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="py-4 px-6 text-xs font-bold text-gray-500 uppercase tracking-wider w-16">Sr No</th>
                        <th class="py-4 px-6 text-xs font-bold text-gray-500 uppercase tracking-wider">Batch Name</th>
                        <th class="py-4 px-6 text-xs font-bold text-gray-500 uppercase tracking-wider">Timings</th>
                        <th class="py-4 px-6 text-xs font-bold text-gray-500 uppercase tracking-wider">Date Created</th>
                        <th class="py-4 px-6 text-xs font-bold text-gray-500 uppercase tracking-wider text-center w-32">Actions</th>
                    </tr>
                </thead>
                <tbody id="batches-tbody" class="divide-y divide-gray-50">
                    <!-- Data will be loaded here -->
                </tbody>
            </table>
            </table>
    </div>
</div>

<!-- View Batch Modal -->
<div id="view-batch-modal" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" onclick="closeViewModal()"></div>
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden relative z-10 w-full max-w-sm flex flex-col transform transition-all">
        <!-- Close btn -->
        <button onclick="closeViewModal()" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-800 flex items-center justify-center transition z-20">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="p-6 flex flex-col items-center text-center border-b border-gray-50 relative pt-8 bg-gray-50/50">
            <div class="w-16 h-16 rounded-2xl bg-indigo-100 flex items-center justify-center text-indigo-600 text-2xl shadow-sm mb-4">
                <i class="fa-solid fa-clock"></i>
            </div>
            <h3 id="view-batch-name" class="text-xl font-bold text-gray-900 leading-tight mb-2">Batch Name</h3>
            <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-green-100 text-green-700 mb-4">Active</span>
        </div>
        
        <div class="px-6 py-5 bg-white flex-1">
            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Batch Timings</h4>
            <div class="flex justify-between items-center text-sm mb-3">
                <span class="text-gray-500 font-medium">Start Time</span>
                <span id="view-start-time" class="font-bold text-gray-800"></span>
            </div>
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-500 font-medium">End Time</span>
                <span id="view-end-time" class="font-bold text-gray-800"></span>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal (Hidden by default) -->
<div id="batch-modal" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
    
    <!-- Modal Content -->
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden flex flex-col max-h-[90vh]">
        
        <!-- Modal Header -->
        <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 id="modal-title" class="text-lg font-bold text-gray-900">Add New Batch</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 overflow-y-auto">
            <form id="batch-form" onsubmit="saveBatch(event)">
                <input type="hidden" id="batch_id" name="id">

                <div class="space-y-5">
                    <!-- Batch Name -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Batch Name <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all" placeholder="e.g. Morning Batch">
                        <p id="error-name" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                    </div>

                    <!-- Timings -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Start Time</label>
                            <input type="time" id="start_time" name="start_time" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all text-gray-700">
                            <p id="error-start_time" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">End Time</label>
                            <input type="time" id="end_time" name="end_time" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm outline-none transition-all text-gray-700">
                            <p id="error-end_time" class="text-red-500 text-xs mt-1.5 hidden font-medium"></p>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="mt-8 flex gap-3 justify-end">
                    <button type="button" onclick="closeModal()" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition">Cancel</button>
                    <button type="submit" id="btn-save" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition shadow-md shadow-indigo-600/20 flex items-center gap-2">
                        <span>Save Batch</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('page-scripts')
<script>
    let batchesData = [];
    let dataTable = null;

    // Format Time (e.g., 14:30:00 -> 02:30 PM)
    function formatTime(timeStr) {
        if (!timeStr) return '-';
        // HTML time input value is "HH:mm" or "HH:mm:ss"
        const [hourString, minute] = timeStr.split(':');
        const hour = parseInt(hourString, 10);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const hour12 = hour % 12 || 12;
        return `${hour12}:${minute} ${ampm}`;
    }

    // Load Batches
    async function loadBatches() {
        showLoader();
        const tbody = document.getElementById('batches-tbody');
        
        try {
            const response = await fetch('/api/batches', {
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();

            if (response.ok && data.success) {
                batchesData = data.data;
                renderTable();
            } else {
                tbody.innerHTML = `<tr><td colspan="5" class="py-8 text-center text-red-500 font-medium">Failed to load batches</td></tr>`;
            }
        } catch (error) {
            console.error(error);
            tbody.innerHTML = `<tr><td colspan="5" class="py-8 text-center text-red-500 font-medium">An error occurred</td></tr>`;
        } finally {
            hideLoader();
        }
    }

    // Render Table
    function renderTable() {
        if (dataTable) {
            dataTable.destroy();
        }

        const tbody = document.getElementById('batches-tbody');
        let html = '';
        
        batchesData.forEach((batch, index) => {
            const timing = (batch.start_time || batch.end_time) 
                            ? `<span class="bg-indigo-50 text-indigo-600 border border-purple-100 px-3 py-1 rounded-lg text-xs font-semibold inline-flex items-center gap-1.5"><i class="fa-regular fa-clock"></i> ${formatTime(batch.start_time)} - ${formatTime(batch.end_time)}</span>` 
                            : '<span class="text-gray-400 text-xs font-medium italic">No timing set</span>';
            
            const date = new Date(batch.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });

            html += `
                <tr>
                    <td class="font-bold text-gray-500">${index + 1}</td>
                    <td>
                        <div class="font-bold text-gray-900">${batch.name}</div>
                    </td>
                    <td>${timing}</td>
                    <td><div class="text-sm text-gray-500 font-medium">${date}</div></td>
                    <td class="text-center">
                        <div class="flex justify-center gap-2">
                            <button onclick='viewBatch(${JSON.stringify(batch).replace(/'/g, "&#39;")})' class="w-10 h-10 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 flex items-center justify-center transition" title="View Details">
                                <i class="fa-solid fa-eye text-sm"></i>
                            </button>
                            <button onclick="editBatch(${batch.id})" class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition" title="Edit">
                                <i class="fa-solid fa-pen text-sm"></i>
                            </button>
                            <button onclick="deleteBatch(${batch.id})" class="w-10 h-10 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition" title="Delete">
                                <i class="fa-solid fa-trash text-sm"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        tbody.innerHTML = html;

        // Initialize DataTable
        dataTable = $('#batchesTable').DataTable({
            pageLength: 10,
            ordering: true,
            responsive: true,
            language: {
                search: "",
                searchPlaceholder: "Search batches..."
            },
            columnDefs: [
                { orderable: false, targets: [0, 4] } // Disable sorting on Sr No and Actions
            ],
            order: [[1, 'asc']] // Default sort by Batch Name
        });
    }

    // View Modal Logic
    const viewModal = document.getElementById('view-batch-modal');
    function viewBatch(batch) {
        document.getElementById('view-batch-name').textContent = batch.name;
        document.getElementById('view-start-time').textContent = batch.start_time ? formatTime(batch.start_time) : 'Not Set';
        document.getElementById('view-end-time').textContent = batch.end_time ? formatTime(batch.end_time) : 'Not Set';
        
        viewModal.classList.remove('hidden');
    }

    function closeViewModal() {
        viewModal.classList.add('hidden');
    }

    // Removed local showToast function since we use global showSuccess()

    // Modal logic
    const modal = document.getElementById('batch-modal');
    
    function openModal() {
        document.getElementById('modal-title').textContent = 'Add New Batch';
        document.getElementById('batch-form').reset();
        document.getElementById('batch_id').value = '';
        clearErrors();
        modal.classList.remove('hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
    }

    function editBatch(id) {
        const batch = batchesData.find(b => b.id === id);
        if (!batch) return;

        document.getElementById('modal-title').textContent = 'Edit Batch';
        document.getElementById('batch_id').value = batch.id;
        document.getElementById('name').value = batch.name;
        
        // Input time fields expect HH:mm format
        document.getElementById('start_time').value = batch.start_time ? batch.start_time.substring(0, 5) : '';
        document.getElementById('end_time').value = batch.end_time ? batch.end_time.substring(0, 5) : '';
        
        clearErrors();
        modal.classList.remove('hidden');
    }

    function clearErrors() {
        const fields = ['name', 'start_time', 'end_time'];
        fields.forEach(field => {
            const input = document.getElementById(field);
            const errorP = document.getElementById(`error-${field}`);
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

    function showErrors(errors) {
        for (const [field, messages] of Object.entries(errors)) {
            const input = document.getElementById(field);
            const errorP = document.getElementById(`error-${field}`);
            
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

    // Save Data (Create / Update)
    async function saveBatch(e) {
        e.preventDefault();
        
        const btn = document.getElementById('btn-save');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Saving...';
        btn.disabled = true;

        clearErrors();

        const id = document.getElementById('batch_id').value;
        const isUpdate = !!id;
        
        const payload = {
            name: document.getElementById('name').value,
            start_time: document.getElementById('start_time').value || null,
            end_time: document.getElementById('end_time').value || null,
        };

        const url = isUpdate ? `/api/batches/${id}` : '/api/batches';
        const method = isUpdate ? 'PUT' : 'POST';

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (response.ok && data.success) {
                showSuccess(isUpdate ? 'Batch updated successfully!' : 'Batch created successfully!');
                closeModal();
                loadBatches();
            } else {
                if (data.errors) {
                    showErrors(data.errors);
                } else {
                    alert(data.message || 'Failed to save batch.');
                }
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }

    // Delete Batch
    function deleteBatch(id) {
        confirmDelete('Delete Batch?', 'Are you sure you want to delete this batch?', async () => {
            showLoader();
            try {
                const response = await fetch(`/api/batches/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    showSuccess('Batch deleted successfully!');
                    loadBatches();
                } else {
                    showError(data.message || 'Failed to delete batch.');
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
    document.addEventListener('DOMContentLoaded', loadBatches);
</script>
@endpush
@endsection
