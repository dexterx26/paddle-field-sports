@extends('layouts.owner')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-stone-200 dark:border-stone-800 gap-3">
        <div>
            <h2 class="text-xl font-bold text-theme-heading">Manual Payment Receipts Queue</h2>
            <p class="text-xs text-theme-muted">Review proof screenshots uploaded by clients for GCash, Maya, or Bank Transfers.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500/15 text-amber-800 dark:text-amber-400 border border-amber-500/30">
                {{ $pendingBookings->total() }} Total Pending
            </span>
        </div>
    </div>

    <!-- Approvals Table Card -->
    <div class="rounded-3xl glass-panel border border-stone-200 dark:border-stone-800 overflow-hidden shadow-xl">
        <!-- Desktop Table View (Hidden on mobile) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-stone-100 dark:bg-stone-900/80 text-theme-muted border-b border-stone-200 dark:border-stone-800 uppercase text-[10px] tracking-wider">
                        <th class="py-4 px-4">Receipt Proof</th>
                        <th class="py-4 px-4">Reference</th>
                        <th class="py-4 px-4">Customer Info</th>
                        <th class="py-4 px-4">Court Details</th>
                        <th class="py-4 px-4">Reservation Time</th>
                        <th class="py-4 px-4">Amount Due</th>
                        <th class="py-4 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200 dark:divide-stone-800">
                    @forelse($pendingBookings as $booking)
                        <tr class="hover:bg-stone-200/50 dark:hover:bg-stone-800/40 transition-colors">
                            <!-- Receipt Thumbnail with Zoom Trigger -->
                            <td class="py-4 px-4">
                                @if($booking->receipt_url)
                                    <button type="button"
                                        data-url="{{ $booking->receipt_url }}"
                                        data-id="{{ $booking->id }}"
                                        data-reference="{{ $booking->booking_reference }}"
                                        data-customer="{{ $booking->customer_name }}"
                                        data-court="{{ $booking->court->name }}"
                                        data-schedule="{{ $booking->booking_date->format('M d, Y') }} ({{ date('g:i A', strtotime($booking->start_time)) }} - {{ date('g:i A', strtotime($booking->end_time)) }})"
                                        data-amount="{{ $booking->formatted_amount }}"
                                        onclick="handleReceiptPreviewClick(this)"
                                        class="block w-14 h-16 rounded-xl overflow-hidden border border-stone-300 dark:border-stone-700 hover:border-cyan-500 group relative shadow-md cursor-pointer">
                                        <img src="{{ $booking->receipt_url }}" alt="Receipt" class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-slate-950/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                            <i class="fa-solid fa-magnifying-glass-plus text-white text-xs"></i>
                                        </div>
                                    </button>
                                @else
                                    <span class="text-theme-muted italic">No receipt</span>
                                @endif
                            </td>

                            <td class="py-4 px-4">
                                <span class="font-mono font-bold text-cyan-600 dark:text-cyan-400 text-sm">{{ $booking->booking_reference }}</span>
                                <div class="text-[10px] text-theme-muted mt-0.5">{{ $booking->created_at->diffForHumans() }}</div>
                            </td>

                            <td class="py-4 px-4">
                                <div class="font-bold text-theme-heading text-sm">{{ $booking->customer_name }}</div>
                                <div class="font-mono text-theme-muted">{{ $booking->customer_phone }}</div>
                                @if($booking->customer_email)
                                    <div class="text-[11px] text-theme-muted">{{ $booking->customer_email }}</div>
                                @endif
                            </td>

                            <td class="py-4 px-4">
                                <div class="font-bold text-theme-heading">{{ $booking->court->name }}</div>
                                <div class="text-[11px] text-theme-muted">
                                    {{ ucfirst($booking->court->type) }} • {{ $booking->players_count }} Players
                                </div>
                            </td>

                            <td class="py-4 px-4">
                                <div class="font-semibold text-theme-heading">{{ $booking->booking_date->format('M d, Y') }}</div>
                                <div class="text-cyan-600 dark:text-cyan-400 font-bold">
                                    {{ date('g:i A', strtotime($booking->start_time)) }} - {{ date('g:i A', strtotime($booking->end_time)) }}
                                </div>
                                <div class="text-[10px] text-theme-muted">{{ $booking->total_hours }} Hours</div>
                            </td>

                            <td class="py-4 px-4">
                                <div class="font-black text-theme-heading text-base">{{ $booking->formatted_amount }}</div>
                                <div class="text-[10px] text-theme-muted">₱{{ number_format($booking->rate_per_hour, 2) }}/hr</div>
                            </td>

                            <td class="py-4 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Approve Button Form -->
                                    <form id="approve-form-{{ $booking->id }}" method="POST" action="{{ route('owner.approve', $booking->id) }}">
                                        @csrf
                                        <button type="button"
                                            data-id="{{ $booking->id }}"
                                            data-reference="{{ $booking->booking_reference }}"
                                            data-customer="{{ $booking->customer_name }}"
                                            data-court="{{ $booking->court->name }}"
                                            data-schedule="{{ $booking->booking_date->format('M d, Y') }} ({{ date('g:i A', strtotime($booking->start_time)) }} - {{ date('g:i A', strtotime($booking->end_time)) }})"
                                            data-amount="{{ $booking->formatted_amount }}"
                                            onclick="handleApproveClick(this)"
                                            class="px-3.5 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-extrabold text-xs shadow-lg shadow-cyan-500/20 flex items-center gap-1.5 transition-all cursor-pointer">
                                            <i class="fa-solid fa-check"></i> Approve
                                        </button>
                                    </form>

                                    <!-- Reject Button -->
                                    <button type="button" onclick="openRejectModal('{{ $booking->id }}', '{{ $booking->booking_reference }}')"
                                        class="px-3 py-2 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-rose-500/20 text-stone-600 dark:text-stone-400 hover:text-rose-500 font-bold text-xs border border-stone-300 dark:border-stone-700 transition-colors cursor-pointer">
                                        <i class="fa-solid fa-xmark"></i> Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center text-theme-muted">
                                <i class="fa-solid fa-circle-check text-cyan-500 text-3xl mb-2"></i>
                                <h4 class="text-sm font-bold text-theme-heading">No Pending Approvals</h4>
                                <p class="text-xs text-theme-muted mt-1">There are no client payment receipts waiting for verification.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View (Shown on screens < md, eliminates horizontal scrolling) -->
        <div class="block md:hidden p-3.5 sm:p-4 space-y-3">
            @forelse($pendingBookings as $booking)
                <div class="p-4 rounded-2xl bg-white/80 dark:bg-stone-900/80 border border-stone-200 dark:border-stone-800 shadow-sm space-y-3">
                    <!-- Top Bar: Reference, Pending Badge & Amount -->
                    <div class="flex items-start justify-between gap-2 pb-3 border-b border-stone-200/70 dark:border-stone-800/70">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-cyan-600 dark:text-cyan-400 text-sm tracking-wide">{{ $booking->booking_reference }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-800 dark:text-amber-400 border border-amber-500/30">
                                    Pending
                                </span>
                            </div>
                            <div class="text-[10px] text-theme-muted mt-0.5 flex items-center gap-1">
                                <i class="fa-regular fa-clock"></i>
                                <span>{{ $booking->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="font-black text-theme-heading text-base text-cyan-600 dark:text-cyan-400">{{ $booking->formatted_amount }}</div>
                            <div class="text-[10px] text-theme-muted">₱{{ number_format($booking->rate_per_hour, 2) }}/hr</div>
                        </div>
                    </div>

                    <!-- Middle Content: Receipt Thumbnail & Info -->
                    <div class="flex gap-3 items-start">
                        <!-- Receipt Thumbnail Trigger -->
                        <div class="shrink-0">
                            @if($booking->receipt_url)
                                <button type="button"
                                    data-url="{{ $booking->receipt_url }}"
                                    data-id="{{ $booking->id }}"
                                    data-reference="{{ $booking->booking_reference }}"
                                    data-customer="{{ $booking->customer_name }}"
                                    data-court="{{ $booking->court->name }}"
                                    data-schedule="{{ $booking->booking_date->format('M d, Y') }} ({{ date('g:i A', strtotime($booking->start_time)) }} - {{ date('g:i A', strtotime($booking->end_time)) }})"
                                    data-amount="{{ $booking->formatted_amount }}"
                                    onclick="handleReceiptPreviewClick(this)"
                                    class="block w-20 h-24 rounded-xl overflow-hidden border border-stone-300 dark:border-stone-700 hover:border-cyan-500 group relative shadow-sm cursor-pointer bg-stone-950/20">
                                    <img src="{{ $booking->receipt_url }}" alt="Receipt" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-slate-950/40 flex flex-col items-center justify-center text-white text-[10px] font-medium opacity-90 group-hover:opacity-100 transition-opacity">
                                        <i class="fa-solid fa-magnifying-glass-plus text-sm mb-0.5"></i>
                                        <span>View Proof</span>
                                    </div>
                                </button>
                            @else
                                <div class="w-20 h-24 rounded-xl border border-dashed border-stone-300 dark:border-stone-700 flex flex-col items-center justify-center text-theme-muted text-[10px] text-center p-1 bg-stone-100/50 dark:bg-stone-800/30">
                                    <i class="fa-regular fa-image text-lg mb-1"></i>
                                    <span>No receipt</span>
                                </div>
                            @endif
                        </div>

                        <!-- Customer & Reservation Details -->
                        <div class="min-w-0 flex-1 space-y-1.5">
                            <div>
                                <div class="font-bold text-theme-heading text-sm truncate">{{ $booking->customer_name }}</div>
                                <div class="flex items-center gap-1.5 text-xs text-theme-muted mt-0.5">
                                    <i class="fa-solid fa-phone text-[10px] text-cyan-600 dark:text-cyan-400"></i>
                                    <a href="tel:{{ $booking->customer_phone }}" class="font-mono hover:underline hover:text-cyan-600 dark:hover:text-cyan-400">{{ $booking->customer_phone }}</a>
                                </div>
                                @if($booking->customer_email)
                                    <div class="text-[11px] text-theme-muted truncate">{{ $booking->customer_email }}</div>
                                @endif
                            </div>

                            <div class="pt-1 border-t border-stone-200/50 dark:border-stone-800/50 space-y-0.5">
                                <div class="text-xs font-semibold text-theme-heading flex items-center gap-1.5">
                                    <i class="fa-solid fa-location-dot text-[10px] text-cyan-600 dark:text-cyan-400"></i>
                                    <span class="truncate">{{ $booking->court->name }}</span>
                                    <span class="text-[10px] text-theme-muted font-normal">({{ ucfirst($booking->court->type) }})</span>
                                </div>
                                <div class="text-xs text-theme-muted flex items-center gap-1.5">
                                    <i class="fa-regular fa-calendar text-[10px]"></i>
                                    <span>{{ $booking->booking_date->format('M d, Y') }}</span>
                                </div>
                                <div class="text-xs font-bold text-cyan-600 dark:text-cyan-400 flex items-center gap-1.5">
                                    <i class="fa-regular fa-clock text-[10px]"></i>
                                    <span>{{ date('g:i A', strtotime($booking->start_time)) }} - {{ date('g:i A', strtotime($booking->end_time)) }}</span>
                                    <span class="text-[10px] text-theme-muted font-normal">({{ $booking->total_hours }}h)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Buttons (50/50 Grid for Easy Tapping) -->
                    <div class="grid grid-cols-2 gap-2 pt-1 border-t border-stone-200/60 dark:border-stone-800/60">
                        <button type="button" onclick="openRejectModal('{{ $booking->id }}', '{{ $booking->booking_reference }}')"
                            class="w-full py-2.5 px-3 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-rose-500/20 text-stone-700 dark:text-stone-300 hover:text-rose-500 font-bold text-xs border border-stone-300 dark:border-stone-700 transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-xmark"></i> Reject
                        </button>

                        <form id="approve-form-mobile-{{ $booking->id }}" method="POST" action="{{ route('owner.approve', $booking->id) }}">
                            @csrf
                            <button type="button"
                                data-id="{{ $booking->id }}"
                                data-reference="{{ $booking->booking_reference }}"
                                data-customer="{{ $booking->customer_name }}"
                                data-court="{{ $booking->court->name }}"
                                data-schedule="{{ $booking->booking_date->format('M d, Y') }} ({{ date('g:i A', strtotime($booking->start_time)) }} - {{ date('g:i A', strtotime($booking->end_time)) }})"
                                data-amount="{{ $booking->formatted_amount }}"
                                onclick="handleApproveClick(this)"
                                class="w-full py-2.5 px-3 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-extrabold text-xs shadow-md shadow-cyan-500/20 flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                                <i class="fa-solid fa-check"></i> Approve
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="py-12 px-4 text-center text-theme-muted">
                    <i class="fa-solid fa-circle-check text-cyan-500 text-3xl mb-2"></i>
                    <h4 class="text-sm font-bold text-theme-heading">No Pending Approvals</h4>
                    <p class="text-xs text-theme-muted mt-1">There are no client payment receipts waiting for verification.</p>
                </div>
            @endforelse
        </div>

        @if($pendingBookings->hasPages())
            <div class="p-4 border-t border-stone-200 dark:border-stone-800">
                {{ $pendingBookings->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Receipt Modal Component -->
<div id="receiptModal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/85 backdrop-blur-md opacity-0 pointer-events-none transition-all duration-300">
    <div class="glass-dropdown p-4 sm:p-6 rounded-3xl max-w-lg w-full max-h-[92vh] flex flex-col border border-stone-300 dark:border-stone-700 shadow-2xl relative space-y-3 sm:space-y-4">
        <div class="flex items-start justify-between border-b border-stone-200 dark:border-stone-800 pb-3 gap-3 shrink-0">
            <div class="min-w-0 pr-2">
                <h3 class="text-sm font-bold text-theme-heading" id="rcptModalTitle">Payment Receipt Verification</h3>
                <p class="text-xs text-theme-muted" id="rcptModalMeta">-</p>
            </div>
            <button type="button" onclick="closeReceiptModal()" class="shrink-0 text-theme-muted hover:text-theme-heading p-2 rounded-xl hover:bg-stone-200/50 dark:hover:bg-stone-800/50 transition-colors cursor-pointer -mt-1 -mr-1" title="Close modal">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div class="rounded-2xl overflow-hidden bg-stone-900 border border-stone-300 dark:border-stone-700 flex items-center justify-center max-h-72 sm:max-h-96 min-h-[160px] flex-1">
            <img id="rcptModalImg" src="" alt="Receipt Proof" class="w-full h-full object-contain">
        </div>

        <div class="flex items-center justify-between pt-2 shrink-0">
            <a id="rcptModalDownload" href="" target="_blank" class="text-xs text-cyan-600 dark:text-cyan-400 hover:underline flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Open in New Tab
            </a>
            <div class="flex items-center gap-2">
                <button type="button" onclick="closeReceiptModal()" class="px-3.5 sm:px-4 py-2 rounded-xl bg-stone-200 dark:bg-stone-800 text-xs text-theme-body font-semibold cursor-pointer">
                    Close
                </button>
                <button type="button" onclick="approveFromReceiptModal()" class="px-3.5 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-extrabold text-xs shadow-md shadow-cyan-500/20 flex items-center gap-1.5 transition-all cursor-pointer">
                    <i class="fa-solid fa-check"></i> Approve
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal Component -->
<div id="rejectModal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/85 backdrop-blur-md opacity-0 pointer-events-none transition-all duration-300">
    <div class="glass-dropdown p-5 sm:p-6 rounded-3xl max-w-md w-full border border-rose-500/30 shadow-2xl relative space-y-4">
        <div class="flex items-start justify-between gap-3">
            <div class="w-12 h-12 rounded-2xl bg-rose-500/20 text-rose-500 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-ban"></i>
            </div>
            <button type="button" onclick="closeRejectModal()" class="shrink-0 text-theme-muted hover:text-theme-heading p-2 rounded-xl hover:bg-stone-200/50 dark:hover:bg-stone-800/50 transition-colors cursor-pointer -mt-1 -mr-1" title="Close modal">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div>
            <h3 class="text-base font-bold text-theme-heading">Decline Booking Reservation</h3>
            <p class="text-xs text-theme-muted mt-1" id="rejectModalRefText">Booking Ref</p>
        </div>

        <form id="rejectForm" method="POST" action="" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-theme-body mb-1.5">Reason for Rejection</label>
                <textarea name="reason" rows="3" required placeholder="e.g. Payment receipt could not be verified on GCash, or amount was incomplete."
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 text-xs focus:outline-none focus:border-rose-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3">
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 rounded-xl bg-stone-200 dark:bg-stone-800 text-xs text-theme-body hover:bg-stone-300 dark:hover:bg-stone-700 cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-rose-500 hover:bg-rose-400 text-white text-xs font-bold shadow-md shadow-rose-500/20 cursor-pointer">
                    Confirm Rejection & Release Slots
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentReceiptBooking = null;

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function handleReceiptPreviewClick(btn) {
        const data = btn.dataset;
        currentReceiptBooking = {
            id: data.id,
            reference: data.reference,
            customer: data.customer,
            court: data.court,
            schedule: data.schedule,
            amount: data.amount
        };

        document.getElementById('rcptModalImg').src = data.url;
        document.getElementById('rcptModalDownload').href = data.url;
        document.getElementById('rcptModalTitle').textContent = `Receipt Proof: ${data.reference}`;
        document.getElementById('rcptModalMeta').textContent = `${data.customer} • Total: ${data.amount}`;

        const modal = document.getElementById('receiptModal');
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100', 'pointer-events-auto');
    }

    function previewReceiptModal(url, ref, name, amount, id, court, schedule) {
        currentReceiptBooking = {
            id: id || null,
            reference: ref,
            customer: name,
            court: court || '',
            schedule: schedule || '',
            amount: amount
        };

        document.getElementById('rcptModalImg').src = url;
        document.getElementById('rcptModalDownload').href = url;
        document.getElementById('rcptModalTitle').textContent = `Receipt Proof: ${ref}`;
        document.getElementById('rcptModalMeta').textContent = `${name} • Total: ${amount}`;

        const modal = document.getElementById('receiptModal');
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100', 'pointer-events-auto');
    }

    function closeReceiptModal() {
        const modal = document.getElementById('receiptModal');
        modal.classList.add('opacity-0', 'pointer-events-none');
        modal.classList.remove('opacity-100', 'pointer-events-auto');
    }

    function approveFromReceiptModal() {
        if (!currentReceiptBooking) return;
        const b = currentReceiptBooking;
        closeReceiptModal();
        confirmApprove(b.id, b.reference, b.customer, b.court, b.schedule, b.amount);
    }

    function handleApproveClick(btn) {
        const data = btn.dataset;
        confirmApprove(data.id, data.reference, data.customer, data.court, data.schedule, data.amount);
    }

    function confirmApprove(bookingId, ref, customerName, courtName, schedule, amount) {
        Swal.fire({
            title: 'Approve Reservation?',
            html: `
                <div class="text-left space-y-3 text-xs text-stone-600 dark:text-stone-300">
                    <p>Are you sure you want to approve and confirm the reservation for <strong class="text-stone-900 dark:text-white font-bold">${escapeHtml(customerName)}</strong>?</p>
                    <div class="p-3.5 rounded-2xl bg-stone-100 dark:bg-stone-800/80 border border-stone-200 dark:border-stone-700/80 space-y-2 font-medium">
                        <div class="flex justify-between items-center pb-1.5 border-b border-stone-200/60 dark:border-stone-700/60">
                            <span class="text-theme-muted">Reference:</span>
                            <span class="font-mono font-bold text-cyan-600 dark:text-cyan-400 text-sm">${escapeHtml(ref)}</span>
                        </div>
                        ${courtName ? `
                        <div class="flex justify-between items-center">
                            <span class="text-theme-muted">Court:</span>
                            <span class="font-semibold text-stone-900 dark:text-white">${escapeHtml(courtName)}</span>
                        </div>
                        ` : ''}
                        ${schedule ? `
                        <div class="flex justify-between items-center">
                            <span class="text-theme-muted">Schedule:</span>
                            <span class="text-stone-900 dark:text-white">${escapeHtml(schedule)}</span>
                        </div>
                        ` : ''}
                        <div class="flex justify-between items-center pt-1.5 border-t border-stone-200/60 dark:border-stone-700/60">
                            <span class="text-theme-muted">Amount Due:</span>
                            <span class="font-black text-theme-heading text-sm text-cyan-600 dark:text-cyan-400">${escapeHtml(amount)}</span>
                        </div>
                    </div>
                    <div class="p-2.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-800 dark:text-emerald-300 text-[11px] flex items-start gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 mt-0.5 shrink-0"></i>
                        <span>This will verify the payment, confirm the booking, and notify the customer.</span>
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#06b6d4',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="fa-solid fa-check mr-1.5"></i> Yes, Approve Reservation',
            cancelButtonText: 'Cancel',
            customClass: {
                popup: 'rounded-3xl',
                confirmButton: 'rounded-xl font-bold text-xs px-4 py-2.5 cursor-pointer',
                cancelButton: 'rounded-xl font-bold text-xs px-4 py-2.5 cursor-pointer'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.showLoading();
                let form = document.getElementById('approve-form-' + bookingId) || document.getElementById('approve-form-mobile-' + bookingId);
                if (!form) {
                    form = document.createElement('form');
                    form.method = 'POST';
                    form.action = "{{ url('owner/approvals') }}/" + bookingId + "/approve";
                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = "{{ csrf_token() }}";
                    form.appendChild(csrf);
                    document.body.appendChild(form);
                }
                form.submit();
            }
        });
    }

    function openRejectModal(id, ref) {
        document.getElementById('rejectForm').action = "{{ url('owner/approvals') }}/" + id + "/reject";
        document.getElementById('rejectModalRefText').textContent = `Declining reservation: ${ref}. Slots will be released back to the schedule.`;

        const modal = document.getElementById('rejectModal');
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100', 'pointer-events-auto');
    }

    function closeRejectModal() {
        const modal = document.getElementById('rejectModal');
        modal.classList.add('opacity-0', 'pointer-events-none');
        modal.classList.remove('opacity-100', 'pointer-events-auto');
    }

    // Modal backdrop click and Escape key listeners
    document.getElementById('receiptModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeReceiptModal();
        }
    });

    document.getElementById('rejectModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeRejectModal();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeReceiptModal();
            closeRejectModal();
        }
    });
</script>
@endpush
