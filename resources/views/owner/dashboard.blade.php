@extends('layouts.owner')

@section('content')
<div class="space-y-8">
    <!-- Stat Metrics Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Revenue (Crunch Tan Accent) -->
        <div class="p-6 rounded-3xl glass-card space-y-2">
            <div class="flex items-center justify-between text-xs text-theme-muted font-semibold">
                <span>Total Confirmed Revenue</span>
                <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-700 dark:text-amber-400 flex items-center justify-center">
                    <i class="fa-solid fa-peso-sign"></i>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-theme-heading">₱{{ number_format($totalRevenue, 2) }}</div>
            <p class="text-[11px] text-theme-muted">All-time confirmed reservations</p>
        </div>

        <!-- Confirmed Bookings (Dreamland Cyan Accent) -->
        <div class="p-6 rounded-3xl glass-card space-y-2">
            <div class="flex items-center justify-between text-xs text-theme-muted font-semibold">
                <span>Confirmed Bookings</span>
                <div class="w-8 h-8 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 flex items-center justify-center">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-theme-heading">{{ $totalConfirmedCount }}</div>
            <p class="text-[11px] text-theme-muted">Total verified matches</p>
        </div>

        <!-- Pending Approvals (Crunch Tan Accent) -->
        <div class="p-6 rounded-3xl glass-card space-y-2">
            <div class="flex items-center justify-between text-xs text-theme-muted font-semibold">
                <span>Pending Approvals</span>
                <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-700 dark:text-amber-400 flex items-center justify-center">
                    <i class="fa-solid fa-file-invoice"></i>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black {{ $pendingApprovals->count() > 0 ? 'text-amber-700 dark:text-amber-400 animate-pulse' : 'text-theme-heading' }}">
                {{ $pendingApprovals->count() }}
            </div>
            <p class="text-[11px] text-theme-muted">Manual receipts awaiting review</p>
        </div>

        <!-- Active Courts (Dreamland Cyan Accent) -->
        <div class="p-6 rounded-3xl glass-card space-y-2">
            <div class="flex items-center justify-between text-xs text-theme-muted font-semibold">
                <span>Active Courts</span>
                <div class="w-8 h-8 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 flex items-center justify-center">
                    <i class="fa-solid fa-table-tennis-paddle-ball"></i>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-theme-heading">{{ $totalCourtsCount }} Courts</div>
            <p class="text-[11px] text-theme-muted">Ready for daily bookings</p>
        </div>
    </div>

    <!-- PENDING MANUAL RECEIPT APPROVALS QUEUE -->
    <div class="rounded-3xl glass-panel p-6 sm:p-7 shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-5 border-b border-stone-200 dark:border-stone-800 gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-base font-bold text-theme-heading">Manual Payment Receipts Awaiting Approval</h3>
                    @if($pendingApprovals->count() > 0)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-500 text-slate-950 animate-pulse">
                            {{ $pendingApprovals->count() }} Pending
                        </span>
                    @endif
                </div>
                <p class="text-xs text-theme-muted">Clients uploaded payment receipts via GCash/Bank. Review the image proof and confirm.</p>
            </div>
            <a href="{{ route('owner.approvals') }}" class="text-xs font-bold text-cyan-600 dark:text-cyan-400 hover:underline">
                View All Approvals &rarr;
            </a>
        </div>

        <div class="mt-4 overflow-x-auto">
            @if($pendingApprovals->count() > 0)
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-theme-muted border-b border-stone-200 dark:border-stone-800 uppercase text-[10px] tracking-wider">
                            <th class="py-3 px-3">Receipt Proof</th>
                            <th class="py-3 px-3">Booking Ref</th>
                            <th class="py-3 px-3">Guest / Player</th>
                            <th class="py-3 px-3">Court & Schedule</th>
                            <th class="py-3 px-3">Amount Due</th>
                            <th class="py-3 px-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-200 dark:divide-stone-800">
                        @foreach($pendingApprovals->take(5) as $b)
                            <tr class="hover:bg-stone-200/50 dark:hover:bg-stone-800/40 transition-colors">
                                <!-- Receipt Thumbnail -->
                                <td class="py-3 px-3">
                                    @if($b->receipt_url)
                                        <button type="button" onclick="previewReceiptModal('{{ $b->receipt_url }}', '{{ $b->booking_reference }}', '{{ $b->customer_name }}', '{{ $b->formatted_amount }}')"
                                            class="block w-12 h-14 rounded-lg overflow-hidden border border-stone-300 dark:border-stone-700 hover:border-cyan-500 group relative cursor-pointer">
                                            <img src="{{ $b->receipt_url }}" alt="Receipt" class="w-full h-full object-cover">
                                            <div class="absolute inset-0 bg-slate-950/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                <i class="fa-solid fa-magnifying-glass text-white text-[10px]"></i>
                                            </div>
                                        </button>
                                    @else
                                        <span class="text-theme-muted italic">No receipt</span>
                                    @endif
                                </td>

                                <td class="py-3 px-3 font-mono font-bold text-cyan-600 dark:text-cyan-400">
                                    {{ $b->booking_reference }}
                                </td>

                                <td class="py-3 px-3">
                                    <div class="font-bold text-theme-heading">{{ $b->customer_name }}</div>
                                    <div class="text-[11px] text-theme-muted">{{ $b->customer_phone }}</div>
                                </td>

                                <td class="py-3 px-3">
                                    <div class="font-semibold text-theme-heading">{{ $b->court->name }}</div>
                                    <div class="text-[11px] text-theme-muted">
                                        {{ $b->booking_date->format('M d, Y') }} ({{ date('g:i A', strtotime($b->start_time)) }} - {{ date('g:i A', strtotime($b->end_time)) }})
                                    </div>
                                </td>

                                <td class="py-3 px-3 font-black text-theme-heading text-sm">
                                    {{ $b->formatted_amount }}
                                </td>

                                <td class="py-3 px-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <form method="POST" action="{{ route('owner.approve', $b->id) }}">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-cyan-500 hover:bg-cyan-400 text-slate-950 text-xs font-extrabold flex items-center gap-1 shadow-md shadow-cyan-500/20 cursor-pointer">
                                                <i class="fa-solid fa-check"></i> Approve
                                            </button>
                                        </form>

                                        <button type="button" onclick="openRejectModal('{{ $b->id }}', '{{ $b->booking_reference }}')"
                                            class="px-2.5 py-1.5 rounded-lg bg-stone-200 dark:bg-stone-800 hover:bg-rose-500/20 text-theme-muted hover:text-rose-500 text-xs font-semibold border border-stone-300 dark:border-stone-700 cursor-pointer">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="py-8 text-center text-theme-muted space-y-1">
                    <i class="fa-solid fa-circle-check text-cyan-500 text-2xl mb-1"></i>
                    <p class="text-xs font-semibold text-theme-heading">All caught up!</p>
                    <p class="text-[11px]">No pending manual payment receipts awaiting owner verification.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Two-column row: Today's Bookings & Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Today's Schedule -->
        <div class="rounded-3xl glass-panel p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-stone-200 dark:border-stone-800">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-calendar-day text-cyan-600 dark:text-cyan-400"></i>
                    <h3 class="text-sm font-bold text-theme-heading">Today's Court Reservations</h3>
                </div>
                <span class="text-xs text-theme-muted font-mono">{{ date('l, M d, Y') }}</span>
            </div>

            <div class="space-y-3">
                @forelse($todayBookings as $tb)
                    <div class="p-3.5 rounded-2xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-cyan-500/10 flex items-center justify-center font-bold text-xs text-cyan-600 dark:text-cyan-400 border border-cyan-500/30">
                                C{{ $tb->court->court_number }}
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-theme-heading">{{ $tb->court->name }}</h4>
                                <p class="text-[11px] text-theme-muted">
                                    {{ date('g:i A', strtotime($tb->start_time)) }} - {{ date('g:i A', strtotime($tb->end_time)) }} • {{ $tb->customer_name }}
                                </p>
                            </div>
                        </div>
                        <div>
                            @if($tb->booking_status === 'confirmed')
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-cyan-500/20 text-cyan-700 dark:text-cyan-400 border border-cyan-500/30 uppercase">Confirmed</span>
                            @elseif($tb->booking_status === 'pending_approval')
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-500/20 text-amber-700 dark:text-amber-400 border border-amber-500/30 uppercase">In Review</span>
                            @elseif($tb->booking_status === 'held')
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-cyan-500/20 text-cyan-700 dark:text-cyan-400 border border-cyan-500/30 uppercase">Held</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-theme-muted">
                        No reservations on court for today yet.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Reservations Activity -->
        <div class="rounded-3xl glass-panel p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-stone-200 dark:border-stone-800">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-amber-600 dark:text-amber-400"></i>
                    <h3 class="text-sm font-bold text-theme-heading">Recent Activity</h3>
                </div>
                <a href="{{ route('owner.bookings.index') }}" class="text-xs font-bold text-cyan-600 dark:text-cyan-400 hover:underline">
                    View Ledger &rarr;
                </a>
            </div>

            <div class="space-y-3">
                @foreach($recentBookings as $rb)
                    <div class="p-3 rounded-2xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 flex items-center justify-between text-xs">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-theme-heading">{{ $rb->customer_name }}</span>
                                <span class="font-mono text-[10px] text-theme-muted">({{ $rb->booking_reference }})</span>
                            </div>
                            <p class="text-[11px] text-theme-muted mt-0.5">
                                {{ $rb->court->name }} • {{ $rb->booking_date->format('M d') }}
                            </p>
                        </div>
                        <div class="text-right">
                            <div class="font-black text-theme-heading">{{ $rb->formatted_amount }}</div>
                            <span class="text-[10px] capitalize text-theme-muted">{{ $rb->booking_status }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- High-Resolution Receipt Preview Modal -->
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

<!-- Reject Modal with Reason -->
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
