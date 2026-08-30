@extends('layouts.dashboard-layout')

@section('dashboard-content')
<!-- html2pdf for high quality client-side PDF download -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<div class="flex-1 overflow-y-auto p-8 bg-[#f8f9fc]">
    <div class="space-y-6 max-w-7xl mx-auto">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl lg:text-3xl font-extrabold text-gray-900 tracking-tight">Billing & Invoices</h1>
                <p class="text-xs lg:text-sm text-gray-500 mt-1 font-medium">Manage member fees, download GST invoices, and track dues.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-gray-400 text-xs"></i>
                    <input type="text" id="search-payment" placeholder="Search member or mobile..." class="pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-[#5d5fef] outline-none w-52 sm:w-60 bg-white shadow-2xs font-medium">
                </div>

                <!-- Filter 1: Status Filter -->
                <div class="flex items-center gap-1.5 bg-white px-3 py-1.5 rounded-xl border border-gray-200 shadow-2xs">
                    <i class="fa-solid fa-filter text-[#5d5fef] text-xs"></i>
                    <select id="status-filter" onchange="fetchPayments()" class="text-xs font-bold text-gray-700 bg-transparent outline-none cursor-pointer">
                        <option value="all">All Payments</option>
                        <option value="pending">Pending Dues</option>
                        <option value="paid">Fully Paid</option>
                    </select>
                </div>

                <!-- Filter 2: Date Period Filter -->
                <div class="flex items-center gap-1.5 bg-white px-3 py-1.5 rounded-xl border border-gray-200 shadow-2xs">
                    <i class="fa-solid fa-calendar-days text-[#5d5fef] text-xs"></i>
                    <select id="date-filter" onchange="renderPayments()" class="text-xs font-bold text-gray-700 bg-transparent outline-none cursor-pointer">
                        <option value="all">All Time</option>
                        <option value="today">Today</option>
                        <option value="this_week">This Week</option>
                        <option value="this_month">This Month</option>
                        <option value="last_month">Last Month</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <!-- Total Collected -->
            <div class="bg-white border border-gray-100/90 rounded-2xl p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-all">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-xl shrink-0">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Total Collected</span>
                        <div class="text-3xl font-black text-gray-900 tracking-tight mt-0.5">₹<span id="stat-total-paid">0</span></div>
                    </div>
                </div>
                <span class="text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg">Collected</span>
            </div>

            <!-- Total Pending Due -->
            <div class="bg-white border border-gray-100/90 rounded-2xl p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-all">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center text-xl shrink-0">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Pending Dues</span>
                        <div class="text-3xl font-black text-rose-500 tracking-tight mt-0.5">₹<span id="stat-total-due">0</span></div>
                    </div>
                </div>
                <span class="text-[11px] font-bold text-rose-600 bg-rose-50 px-2.5 py-1 rounded-lg">Pending</span>
            </div>
        </div>

        <!-- Payments & Invoices Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100/90 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-bold text-gray-900">Payment Transactions & Tax Invoices</h2>
                <span class="text-xs text-gray-400 font-semibold" id="payment-count-display">Showing 0 records</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                            <th class="px-6 py-3.5">Invoice #</th>
                            <th class="px-6 py-3.5">Member Details</th>
                            <th class="px-6 py-3.5">Plan / Period</th>
                            <th class="px-6 py-3.5">Payment Date</th>
                            <th class="px-6 py-3.5">Total Bill</th>
                            <th class="px-6 py-3.5">Paid</th>
                            <th class="px-6 py-3.5">Due</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="payments-tbody" class="text-xs divide-y divide-gray-50 font-medium">
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-gray-400 font-medium">
                                <div class="w-7 h-7 border-3 border-[#5d5fef] border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                                Loading payments...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- ========================================== -->
<!-- 🌟 PROFESSIONAL GST INVOICE MODAL & VIEWER -->
<!-- ========================================== -->
<div id="invoice-modal" class="fixed inset-0 bg-black/75 backdrop-blur-sm z-[9999] hidden flex items-center justify-center p-3 sm:p-5 overflow-hidden">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl flex flex-col max-h-[94vh] overflow-hidden transform transition-all scale-95 opacity-0 border border-gray-200" id="invoice-modal-content">
        
        <!-- Modal Top Action Header (Sticky, always on top, not printed) -->
        <div class="px-5 py-3.5 bg-gray-900 text-white flex items-center justify-between no-print shrink-0 border-b border-gray-800 shadow-md">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-[#5d5fef] flex items-center justify-center text-white text-sm">
                    <i class="fa-solid fa-file-invoice"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold leading-tight" id="modal-invoice-title">Invoice Preview</h3>
                    <p class="text-[10px] text-gray-400 font-medium">1-Click PDF Download & Print</p>
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                <!-- WhatsApp Share Button -->
                <button onclick="shareInvoiceWhatsApp()" class="bg-[#25D366] hover:bg-[#20bd5a] text-white px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                    <i class="fa-brands fa-whatsapp text-sm"></i>
                    <span class="hidden sm:inline">WhatsApp</span>
                </button>
                
                <!-- Print Button -->
                <button onclick="printInvoiceDocument()" class="bg-gray-800 hover:bg-gray-700 text-white px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 border border-gray-700 cursor-pointer">
                    <i class="fa-solid fa-print text-xs"></i>
                    <span class="hidden sm:inline">Print</span>
                </button>

                <!-- 🌟 Prominent Download PDF Button -->
                <button id="btn-download-pdf" onclick="downloadInvoicePDF()" class="bg-[#5d5fef] hover:bg-[#4d4fe0] text-white px-4 py-1.5 rounded-xl text-xs font-black transition flex items-center gap-1.5 shadow-md shadow-[#5d5fef]/30 cursor-pointer">
                    <i class="fa-solid fa-download text-xs"></i>
                    <span>Download PDF</span>
                </button>

                <!-- Close Modal -->
                <button onclick="closeInvoiceModal()" class="w-8 h-8 rounded-xl bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white flex items-center justify-center text-sm ml-1 transition cursor-pointer" title="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- 📄 PRINTABLE & PDF EXPORTABLE INVOICE CARD -->
        <!-- ========================================== -->
        <div id="invoice-printable-area" class="p-8 sm:p-10 bg-white text-gray-900 font-sans overflow-y-auto flex-1">
            
            <!-- 1. Top Section: Big Title (Left) + Gym Info (Right) -->
            <div class="flex justify-between items-start mb-8">
                <!-- Left: Big Invoice Title & Gym Branding -->
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-[#4f46e5] text-white flex items-center justify-center font-bold text-xl shrink-0 shadow-sm">
                        <img id="inv-gym-logo" src="" class="w-full h-full object-cover rounded-xl" style="display: none;">
                        <i id="inv-gym-icon" class="fa-solid fa-dumbbell text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-black tracking-tight text-gray-900 leading-none">INVOICE</h1>
                        <span class="text-xs font-bold text-indigo-600 tracking-wide mt-1 block uppercase" id="inv-gym-name">GYMORA FITNESS</span>
                    </div>
                </div>

                <!-- Right: Gym Details -->
                <div class="text-right text-xs text-gray-600 leading-relaxed font-medium">
                    <div class="font-bold text-gray-900 uppercase text-xs tracking-wider" id="inv-gym-company-name">GYMORA FITNESS CLUB</div>
                    <div id="inv-gym-address">123 Fitness Boulevard, Sector 14</div>
                    <div id="inv-gym-phone">+91 98765 43210</div>
                    <div id="inv-gym-email">support@gymora.com</div>
                    <div id="inv-gym-gst-container" class="text-[11px] text-gray-400 font-bold mt-0.5">GSTIN: <span id="inv-gym-gst"></span></div>
                </div>
            </div>

            <!-- 2. Metadata Section: Billed To (Left) & Invoice Meta (Right) -->
            <div class="grid grid-cols-2 gap-6 my-6 text-xs">
                <!-- Left: Billed To -->
                <div>
                    <span class="text-indigo-600 font-bold uppercase tracking-wider text-[11px] block mb-1.5">Billed To</span>
                    <div class="text-base font-extrabold text-gray-900" id="inv-member-name">Aman Pal</div>
                    <div class="text-gray-600 font-medium mt-0.5" id="inv-member-mobile">+91 98765 43210</div>
                    <div class="text-gray-400 font-medium text-[11px] mt-0.5">Member ID: <span id="inv-member-id">#GYM-101</span></div>
                </div>

                <!-- Right: Date, Invoice #, Amount Due -->
                <div class="grid grid-cols-3 gap-2 text-right">
                    <div>
                        <span class="text-indigo-600 font-bold uppercase tracking-wider text-[10px] block mb-1">Date Issued</span>
                        <div class="font-bold text-gray-900" id="inv-date">30/08/2026</div>
                    </div>
                    <div>
                        <span class="text-indigo-600 font-bold uppercase tracking-wider text-[10px] block mb-1">Invoice Number</span>
                        <div class="font-bold text-gray-900" id="inv-number">INV-00001</div>
                    </div>
                    <div>
                        <span class="text-indigo-600 font-bold uppercase tracking-wider text-[10px] block mb-1">Amount Due</span>
                        <div class="font-black text-base text-gray-900">₹<span id="inv-top-due">0</span></div>
                    </div>
                </div>
            </div>

            <!-- Blue Accent Divider Line -->
            <div class="w-full h-[2.5px] bg-[#4f46e5] my-5"></div>

            <!-- 3. Clean Table -->
            <div class="mb-8">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider">
                            <th class="pb-3 text-left">Description</th>
                            <th class="pb-3 text-right">Rate</th>
                            <th class="pb-3 text-center">Duration</th>
                            <th class="pb-3 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs">
                        <tr>
                            <td class="py-4 pr-4">
                                <div class="font-bold text-gray-900 text-sm" id="inv-table-plan-name">12 Months Cardio and Weight Training</div>
                                <div class="text-gray-400 text-[11px] font-medium mt-0.5" id="inv-batch-timing">Full gym access & trainer guidance</div>
                            </td>
                            <td class="py-4 text-right font-medium text-gray-700">₹<span id="inv-table-rate">4,500</span></td>
                            <td class="py-4 text-center font-semibold text-gray-600" id="inv-table-duration">12 Months</td>
                            <td class="py-4 text-right font-bold text-gray-900">₹<span id="inv-table-total">4,500</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- 4. Bottom Totals & Notes -->
            <div class="flex flex-col sm:flex-row justify-between items-start gap-8 pt-4">
                <!-- Left: Notes & Terms -->
                <div class="space-y-4 max-w-sm text-xs">
                    <div>
                        <span class="text-indigo-600 font-bold uppercase tracking-wider text-[11px] block mb-1">Notes</span>
                        <p class="text-gray-500 font-medium leading-relaxed">
                            Thank you for working out with us! Stay consistent and achieve your fitness goals. 💪
                        </p>
                    </div>
                    <div>
                        <span class="text-indigo-600 font-bold uppercase tracking-wider text-[11px] block mb-1">Terms</span>
                        <p class="text-gray-400 text-[11px] font-medium leading-relaxed">
                            Fees once paid are non-refundable and non-transferable. Valid as per registered batch timings.
                        </p>
                    </div>
                </div>

                <!-- Right: Clean Totals Breakdown -->
                <div class="w-full sm:w-64 space-y-2 text-xs font-semibold">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal:</span>
                        <span class="font-bold text-gray-900">₹<span id="inv-calc-subtotal">4,500</span></span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Discount:</span>
                        <span>-₹0.00</span>
                    </div>
                    <div class="border-t border-gray-200 pt-2 flex justify-between text-sm font-bold text-gray-900">
                        <span>Total:</span>
                        <span>₹<span id="inv-calc-grand-total">4,500</span></span>
                    </div>
                    <div class="flex justify-between text-emerald-600 font-bold pt-1">
                        <span>Amount Paid:</span>
                        <span>₹<span id="inv-calc-paid">4,500</span></span>
                    </div>
                    
                    <!-- Balance Due Highlighted Bar -->
                    <div class="border-t-2 border-b-2 border-gray-900 py-2 mt-2 flex justify-between text-sm font-black text-gray-900">
                        <span>Balance Due:</span>
                        <span>₹<span id="inv-calc-due">0</span></span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Modal Bottom Action Bar (not printed) -->
        <div class="px-6 py-3.5 bg-gray-50 border-t border-gray-100 flex items-center justify-between no-print shrink-0">
            <span class="text-xs text-gray-500 font-semibold flex items-center gap-1.5">
                <i class="fa-solid fa-circle-check text-emerald-500"></i> Invoice ready to export
            </span>
            <div class="flex items-center gap-2">
                <button onclick="closeInvoiceModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl text-xs font-bold transition cursor-pointer">Close</button>
                <button onclick="downloadInvoicePDF()" class="px-5 py-2 bg-[#5d5fef] hover:bg-[#4d4fe0] text-white rounded-xl text-xs font-black transition flex items-center gap-2 shadow-md shadow-[#5d5fef]/25 cursor-pointer">
                    <i class="fa-solid fa-download text-xs"></i>
                    <span>Download PDF Now</span>
                </button>
            </div>
        </div>

    </div>
</div>

<!-- ========================================== -->
<!-- CLEAR DUE MODAL -->
<!-- ========================================== -->
<div id="clear-due-modal" class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition-all scale-95 opacity-0" id="clear-due-modal-content">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
            <h3 class="text-sm font-bold text-gray-900">Collect Due Payment</h3>
            <button onclick="closeClearDueModal()" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="clear-due-form" onsubmit="handleClearDue(event)">
                <input type="hidden" id="due-payment-id">
                
                <div class="mb-4">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wide mb-1">Member Name</label>
                    <div id="due-member-name" class="font-bold text-gray-900 text-base"></div>
                </div>

                <div class="mb-4">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wide mb-1">Total Due Amount</label>
                    <div class="text-2xl font-black text-rose-500">₹<span id="due-amount-display">0</span></div>
                </div>

                <div class="mb-5">
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Amount Paying Now (₹)</label>
                    <input type="number" step="any" id="amount_paying" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#5d5fef] outline-none font-bold text-sm text-gray-800" placeholder="Enter amount paying">
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button type="button" onclick="closeClearDueModal()" class="px-4 py-2 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-xl transition">Cancel</button>
                    <button type="submit" id="submit-due-btn" class="px-5 py-2.5 bg-[#5d5fef] hover:bg-[#4d4fe0] text-white text-xs font-bold rounded-xl transition shadow-md shadow-[#5d5fef]/25">
                        Confirm & Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('page-scripts')
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #invoice-modal, #invoice-modal * {
            visibility: visible;
        }
        #invoice-modal {
            position: fixed;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            background: white !important;
            padding: 0 !important;
            margin: 0 !important;
            display: block !important;
        }
        #invoice-modal-content {
            box-shadow: none !important;
            max-width: 100% !important;
            width: 100% !important;
            margin: 0 !important;
            transform: none !important;
            opacity: 1 !important;
        }
        .no-print {
            display: none !important;
        }
    }
</style>

<script>
let paymentsData = [];
let activeInvoiceData = null;

async function fetchPayments() {
    const status = document.getElementById('status-filter').value;
    const tbody = document.getElementById('payments-tbody');
    tbody.innerHTML = `
        <tr>
            <td colspan="9" class="px-6 py-12 text-center text-gray-400 font-medium">
                <div class="w-7 h-7 border-3 border-[#5d5fef] border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                Loading payments...
            </td>
        </tr>
    `;

    try {
        const res = await fetch(`/api/payments?status=${status}`, {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        const result = await res.json();
        
        if (res.ok && result.success) {
            paymentsData = result.data || [];
            renderPayments();
        } else {
            tbody.innerHTML = `<tr><td colspan="9" class="px-6 py-8 text-center text-rose-500 font-bold">Failed to load payments.</td></tr>`;
        }
    } catch (e) {
        tbody.innerHTML = `<tr><td colspan="9" class="px-6 py-8 text-center text-rose-500 font-bold">Error loading payments.</td></tr>`;
    }
}

function renderPayments() {
    const tbody = document.getElementById('payments-tbody');
    const search = document.getElementById('search-payment').value.toLowerCase();
    const dateFilter = document.getElementById('date-filter')?.value || 'all';
    
    let filtered = paymentsData.filter(p => {
        const name = p.member?.user?.name || '';
        const mobile = p.member?.user?.mobile || '';
        const plan = p.member?.plan?.plan_group_name || '';
        const matchesSearch = name.toLowerCase().includes(search) || mobile.includes(search) || plan.toLowerCase().includes(search);
        if (!matchesSearch) return false;

        // Date Filter Evaluation
        if (dateFilter !== 'all') {
            const rawDate = p.payment_date || p.created_at;
            if (!rawDate) return false;
            const pDate = new Date(rawDate);
            const now = new Date();

            if (dateFilter === 'today') {
                const isToday = pDate.getDate() === now.getDate() &&
                                pDate.getMonth() === now.getMonth() &&
                                pDate.getFullYear() === now.getFullYear();
                if (!isToday) return false;
            } else if (dateFilter === 'this_week') {
                const oneWeekAgo = new Date(now);
                oneWeekAgo.setDate(now.getDate() - 7);
                oneWeekAgo.setHours(0, 0, 0, 0);
                if (pDate < oneWeekAgo) return false;
            } else if (dateFilter === 'this_month') {
                if (pDate.getMonth() !== now.getMonth() || pDate.getFullYear() !== now.getFullYear()) return false;
            } else if (dateFilter === 'last_month') {
                const prevMonth = now.getMonth() === 0 ? 11 : now.getMonth() - 1;
                const prevYear = now.getMonth() === 0 ? now.getFullYear() - 1 : now.getFullYear();
                if (pDate.getMonth() !== prevMonth || pDate.getFullYear() !== prevYear) return false;
            }
        }

        return true;
    });

    let totalPaid = 0;
    let totalDue = 0;

    filtered.forEach(p => {
        totalPaid += parseFloat(p.paid_amount) || 0;
        totalDue += parseFloat(p.due_amount) || 0;
    });

    document.getElementById('stat-total-paid').textContent = totalPaid.toLocaleString('en-IN');
    document.getElementById('stat-total-due').textContent = totalDue.toLocaleString('en-IN');
    document.getElementById('payment-count-display').textContent = `Showing ${filtered.length} records`;

    if (filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="px-6 py-12 text-center text-gray-400 font-medium">No payment records found.</td></tr>`;
        return;
    }

    let html = '';
    filtered.forEach(p => {
        const statusColors = {
            'paid': 'bg-emerald-50 text-emerald-600 border border-emerald-100',
            'partial': 'bg-amber-50 text-amber-600 border border-amber-100',
            'pending': 'bg-rose-50 text-rose-600 border border-rose-100'
        };
        const stClass = statusColors[p.status] || 'bg-gray-100 text-gray-600';
        
        const dateStr = new Date(p.payment_date || p.created_at).toLocaleDateString('en-GB', {day:'numeric', month:'short', year:'numeric'});
        const invoiceNum = 'INV-' + String(p.id).padStart(5, '0');
        const planName = p.member?.plan?.plan_group_name || 'Standard Plan';
        const duration = p.member?.plan?.duration_months ? `${p.member.plan.duration_months} Mo` : '—';

        html += `
            <tr class="hover:bg-gray-50/80 transition-colors group">
                <td class="px-6 py-4 font-bold text-[#5d5fef] cursor-pointer hover:underline" onclick="directDownloadPDF(${p.id}, this)" title="Click to direct download PDF">
                    ${invoiceNum}
                </td>
                <td class="px-6 py-4">
                    <div class="font-bold text-gray-900">${p.member?.user?.name || 'Member'}</div>
                    <div class="text-[11px] text-gray-400 font-medium">${p.member?.user?.mobile || 'No Mobile'}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="font-semibold text-gray-800">${planName}</div>
                    <div class="text-[10px] text-gray-400">${duration}</div>
                </td>
                <td class="px-6 py-4 text-gray-600">${dateStr}</td>
                <td class="px-6 py-4 font-bold text-gray-900">₹${parseFloat(p.total_amount).toLocaleString('en-IN')}</td>
                <td class="px-6 py-4 font-bold text-emerald-600">₹${parseFloat(p.paid_amount).toLocaleString('en-IN')}</td>
                <td class="px-6 py-4 font-bold ${p.due_amount > 0 ? 'text-rose-500' : 'text-gray-400'}">₹${parseFloat(p.due_amount).toLocaleString('en-IN')}</td>
                <td class="px-6 py-4">
                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider ${stClass}">
                        ${p.status}
                    </span>
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <!-- 🌟 1-Click Direct Download PDF Button -->
                        <button onclick="directDownloadPDF(${p.id}, this)" class="px-3 py-1.5 bg-[#5d5fef]/10 hover:bg-[#5d5fef] text-[#5d5fef] hover:text-white rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 shadow-2xs cursor-pointer" title="Direct Download PDF">
                            <i class="fa-solid fa-file-pdf text-xs"></i>
                            <span>Download PDF</span>
                        </button>

                        <!-- Clear Due Button (if due exists) -->
                        ${p.due_amount > 0 ? `
                            <button onclick="openClearDueModal(${p.id})" class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-500 text-rose-600 hover:text-white rounded-lg text-xs font-bold transition-all flex items-center gap-1 cursor-pointer" title="Record Due Payment">
                                <i class="fa-solid fa-hand-holding-dollar text-xs"></i>
                                <span>Pay Due</span>
                            </button>
                        ` : ''}

                        <!-- Delete / Void Invalid Transaction Button -->
                        <button onclick="deletePayment(${p.id}, '${invoiceNum}')" class="w-8 h-8 rounded-lg bg-gray-50 hover:bg-red-50 text-gray-400 hover:text-red-500 flex items-center justify-center transition-all cursor-pointer" title="Delete / Void Transaction">
                            <i class="fa-solid fa-trash-can text-xs"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

document.getElementById('search-payment').addEventListener('input', renderPayments);

// ==========================================
// 🌟 POPULATE INVOICE DATA (Used by Direct Download & Preview)
// ==========================================
function populateInvoiceData(p) {
    activeInvoiceData = p;

    // Gym Details from Auth User or Payment Gym relation
    const gymObj = p.gym || (user && user.gym) || {};
    const gymTitle = gymObj.name || gymName || 'GYMORA FITNESS';
    const gymContact = gymObj.contact_number || (user ? user.mobile : '+91 98765 43210');
    const gymAddr = gymObj.address || 'Fitness Central, Main Road';
    const gymGST = gymObj.gst_number || '';

    // Populate Gym Header
    document.getElementById('inv-gym-name').textContent = gymTitle;
    document.getElementById('inv-gym-company-name').textContent = gymTitle;
    document.getElementById('inv-gym-address').textContent = gymAddr;
    document.getElementById('inv-gym-phone').textContent = gymContact;
    document.getElementById('inv-gym-email').textContent = (user && user.email) ? user.email : 'contact@gymora.com';

    if (gymGST) {
        document.getElementById('inv-gym-gst').textContent = gymGST;
        document.getElementById('inv-gym-gst-container').style.display = 'block';
    } else {
        document.getElementById('inv-gym-gst-container').style.display = 'none';
    }

    if (gymObj.logo) {
        const logoImg = document.getElementById('inv-gym-logo');
        const logoIcon = document.getElementById('inv-gym-icon');
        logoImg.src = gymObj.logo.startsWith('http') ? gymObj.logo : `/${gymObj.logo}`;
        logoImg.style.display = 'block';
        logoIcon.style.display = 'none';
    }

    // Invoice Meta
    const invNum = 'INV-' + String(p.id).padStart(5, '0');
    const dateFormatted = new Date(p.payment_date || p.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
    document.getElementById('inv-number').textContent = invNum;
    document.getElementById('inv-date').textContent = dateFormatted;
    document.getElementById('modal-invoice-title').textContent = `Invoice #${invNum}`;

    // Member Details
    const memberUser = p.member?.user || {};
    document.getElementById('inv-member-name').textContent = memberUser.name || 'Member';
    document.getElementById('inv-member-mobile').textContent = memberUser.mobile || 'No Mobile';
    document.getElementById('inv-member-id').textContent = `#GYM-${p.member_id || p.id}`;

    // Plan & Batch
    const planObj = p.member?.plan || {};
    const planName = planObj.plan_group_name || 'Standard Gym Plan';
    const planDuration = planObj.duration_months ? `${planObj.duration_months} Months` : '1 Month';
    document.getElementById('inv-table-plan-name').textContent = planName;
    document.getElementById('inv-table-duration').textContent = planDuration;
    document.getElementById('inv-table-rate').textContent = parseFloat(p.total_amount).toLocaleString('en-IN');
    document.getElementById('inv-table-total').textContent = parseFloat(p.total_amount).toLocaleString('en-IN');

    const batchObj = p.member?.batch;
    let batchTiming = 'Standard Batch Access';
    if (batchObj && (batchObj.start_time || batchObj.end_time)) {
        batchTiming = `Batch: ${batchObj.batch_name || ''} (${batchObj.start_time} - ${batchObj.end_time})`;
    }
    document.getElementById('inv-batch-timing').textContent = batchTiming;

    // Financial Calculation Breakdown (Clean & Direct)
    const total = parseFloat(p.total_amount) || 0;
    const paid = parseFloat(p.paid_amount) || 0;
    const due = parseFloat(p.due_amount) || 0;

    document.getElementById('inv-top-due').textContent = due.toLocaleString('en-IN');
    document.getElementById('inv-calc-subtotal').textContent = total.toLocaleString('en-IN');
    document.getElementById('inv-calc-grand-total').textContent = total.toLocaleString('en-IN');
    document.getElementById('inv-calc-paid').textContent = paid.toLocaleString('en-IN');
    document.getElementById('inv-calc-due').textContent = due.toLocaleString('en-IN');
}

// 🌟 1-CLICK DIRECT PDF DOWNLOAD (Without showing popup modal)
async function directDownloadPDF(paymentId, btnEl) {
    const p = paymentsData.find(x => x.id === paymentId);
    if (!p) return;

    let origHTML = '';
    if (btnEl) {
        origHTML = btnEl.innerHTML;
        btnEl.disabled = true;
        btnEl.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i> Downloading...';
    }

    // Populate data into template
    populateInvoiceData(p);

    const element = document.getElementById('invoice-printable-area');
    const invNum = 'INV-' + String(p.id).padStart(5, '0');
    const memberName = p.member?.user?.name || 'Member';
    const gymObj = p.gym || (user && user.gym) || {};
    const gymTitle = (gymObj.name || 'FitFlex').replace(/\s+/g, '_');
    const filename = `${invNum}_${memberName.replace(/\s+/g, '_')}_${gymTitle}.pdf`;

    const opt = {
        margin: [10, 10, 10, 10],
        filename: filename,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, letterRendering: true },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    try {
        await html2pdf().set(opt).from(element).save();
        showToast(`Invoice ${invNum} downloaded successfully!`, 'success');
    } catch (e) {
        console.error('PDF error:', e);
        showToast('Failed to download PDF. Try preview or print.', 'error');
    } finally {
        if (btnEl) {
            btnEl.disabled = false;
            btnEl.innerHTML = origHTML;
        }
    }
}

// Optional: Open Preview Modal
function openInvoiceModal(paymentId) {
    const p = paymentsData.find(x => x.id === paymentId);
    if (!p) return;
    
    populateInvoiceData(p);

    const modal = document.getElementById('invoice-modal');
    const content = document.getElementById('invoice-modal-content');
    modal.classList.remove('hidden');
    
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeInvoiceModal() {
    const modal = document.getElementById('invoice-modal');
    const content = document.getElementById('invoice-modal-content');
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 200);
}

// 🖨️ PRINT INVOICE
function printInvoiceDocument() {
    window.print();
}

// 📥 DOWNLOAD CRISP PDF INVOICE (From inside modal)
async function downloadInvoicePDF() {
    if (!activeInvoiceData) return;
    const btn = document.getElementById('btn-download-pdf');
    await directDownloadPDF(activeInvoiceData.id, btn);
}

// 📱 SHARE ON WHATSAPP
function shareInvoiceWhatsApp() {
    if (!activeInvoiceData) return;
    
    const m = activeInvoiceData.member?.user || {};
    let mobile = (m.mobile || '').replace(/\D/g, '');
    if (mobile.length === 10) mobile = '91' + mobile;

    const invNum = 'INV-' + String(activeInvoiceData.id).padStart(5, '0');
    const gymObj = activeInvoiceData.gym || (user && user.gym) || {};
    const gymTitle = gymObj.name || gymName || 'Gymora';
    const planName = activeInvoiceData.member?.plan?.plan_group_name || 'Gym Membership';
    const paidAmt = parseFloat(activeInvoiceData.paid_amount).toLocaleString('en-IN');
    const dueAmt = parseFloat(activeInvoiceData.due_amount).toLocaleString('en-IN');

    let msg = `*🧾 Payment Receipt - ${gymTitle}*\n\n`;
    msg += `Dear *${m.name || 'Member'}*,\n`;
    msg += `Thank you for your payment towards *${planName}*.\n\n`;
    msg += `📄 *Invoice No:* ${invNum}\n`;
    msg += `💰 *Amount Paid:* ₹${paidAmt}\n`;
    if (parseFloat(activeInvoiceData.due_amount) > 0) {
        msg += `⚠️ *Balance Due:* ₹${dueAmt}\n`;
    }
    msg += `✅ *Status:* ${activeInvoiceData.status.toUpperCase()}\n\n`;
    msg += `Stay Fit & Healthy with *${gymTitle}*! 💪`;

    const url = `https://wa.me/${mobile}?text=${encodeURIComponent(msg)}`;
    window.open(url, '_blank');
}

// ==========================================
// CLEAR DUE MODAL LOGIC
// ==========================================
function openClearDueModal(id) {
    const payment = paymentsData.find(p => p.id === id);
    if(!payment) return;
    
    document.getElementById('due-payment-id').value = id;
    document.getElementById('due-member-name').textContent = payment.member?.user?.name || 'Member';
    document.getElementById('due-amount-display').textContent = parseFloat(payment.due_amount).toLocaleString('en-IN');
    document.getElementById('amount_paying').value = parseFloat(payment.due_amount);
    document.getElementById('amount_paying').max = payment.due_amount;
    
    const modal = document.getElementById('clear-due-modal');
    const content = document.getElementById('clear-due-modal-content');
    modal.classList.remove('hidden');
    
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeClearDueModal() {
    const modal = document.getElementById('clear-due-modal');
    const content = document.getElementById('clear-due-modal-content');
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.getElementById('clear-due-form').reset();
    }, 200);
}

async function handleClearDue(e) {
    e.preventDefault();
    const btn = document.getElementById('submit-due-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1.5"></i> Processing...';
    
    const id = document.getElementById('due-payment-id').value;
    const amountPaying = document.getElementById('amount_paying').value;

    try {
        const res = await fetch(`/api/payments/${id}`, {
            method: 'PUT',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ amount_paying: amountPaying })
        });
        
        const result = await res.json();
        
        if (res.ok && result.success) {
            showToast('Due payment recorded successfully!', 'success');
            closeClearDueModal();
            fetchPayments();
        } else {
            showToast(result.message || 'Failed to update due', 'error');
        }
    } catch(err) {
        showToast('Network error occurred', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Confirm & Update';
    }
}

// 🗑️ DELETE / VOID PAYMENT TRANSACTION
async function deletePayment(id, invNum) {
    if (!confirm(`Are you sure you want to delete invoice ${invNum}? This will remove it from reports and member payment totals.`)) {
        return;
    }

    try {
        const res = await fetch(`/api/payments/${id}`, {
            method: 'DELETE',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json'
            }
        });
        const result = await res.json();
        if (res.ok && result.success) {
            showToast(`${invNum} deleted successfully!`, 'success');
            fetchPayments();
        } else {
            showToast(result.message || 'Failed to delete payment transaction', 'error');
        }
    } catch (err) {
        showToast('Network error while deleting payment', 'error');
    }
}

// Initial fetch
document.addEventListener('DOMContentLoaded', fetchPayments);
</script>
@endpush
@endsection
