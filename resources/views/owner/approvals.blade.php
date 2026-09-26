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
        <div class="overflow-x-auto">
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
                                    <button type="button" onclick="previewReceiptModal('{{ $booking->receipt_url }}', '{{ $booking->booking_reference }}', '{{ $booking->customer_name }}', '{{ $booking->formatted_amount }}')"
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
                                    <!-- Approve Button -->
                                    <form method="POST" action="{{ route('owner.approve', $booking->id) }}">
                                        @csrf
                                        <button type="submit" class="px-3.5 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-extrabold text-xs shadow-lg shadow-cyan-500/20 flex items-center gap-1.5 transition-all cursor-pointer">
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

        @if($pendingBookings->hasPages())
            <div class="p-4 border-t border-stone-200 dark:border-stone-800">
                {{ $pendingBookings->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Receipt Modal Component -->
<div id="receiptModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md opacity-0 pointer-events-none transition-all duration-300">
    <div class="glass-dropdown p-6 rounded-3xl max-w-lg w-full border border-stone-300 dark:border-stone-700 shadow-2xl relative space-y-4">
        <button type="button" onclick="closeReceiptModal()" class="absolute top-4 right-4 text-theme-muted hover:text-theme-heading p-2 cursor-pointer">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>

        <div class="border-b border-stone-200 dark:border-stone-800 pb-3">
            <h3 class="text-sm font-bold text-theme-heading" id="rcptModalTitle">Payment Receipt Verification</h3>
            <p class="text-xs text-theme-muted" id="rcptModalMeta">-</p>
        </div>

        <div class="rounded-2xl overflow-hidden bg-stone-900 border border-stone-300 dark:border-stone-700 flex items-center justify-center max-h-96">
            <img id="rcptModalImg" src="" alt="Receipt Proof" class="w-full h-full object-contain">
        </div>

        <div class="flex items-center justify-between pt-2">
            <a id="rcptModalDownload" href="" target="_blank" class="text-xs text-cyan-600 dark:text-cyan-400 hover:underline flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Open in New Tab
            </a>
            <button type="button" onclick="closeReceiptModal()" class="px-4 py-2 rounded-xl bg-stone-200 dark:bg-stone-800 text-xs text-theme-body font-semibold cursor-pointer">
                Close
            </button>
        </div>
    </div>
</div>

<!-- Reject Modal Component -->
<div id="rejectModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md opacity-0 pointer-events-none transition-all duration-300">
    <div class="glass-dropdown p-6 rounded-3xl max-w-md w-full border border-rose-500/30 shadow-2xl relative space-y-4">
        <button type="button" onclick="closeRejectModal()" class="absolute top-4 right-4 text-theme-muted hover:text-theme-heading p-2 cursor-pointer">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>

        <div class="w-12 h-12 rounded-2xl bg-rose-500/20 text-rose-500 flex items-center justify-center text-xl">
            <i class="fa-solid fa-ban"></i>
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
    function previewReceiptModal(url, ref, name, amount) {
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

    function openRejectModal(id, ref) {
        document.getElementById('rejectForm').action = `/owner/approvals/${id}/reject`;
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
</script>
@endpush
