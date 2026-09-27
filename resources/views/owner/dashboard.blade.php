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
                <div class="flex items-center gap-3">
                    <select id="todayCourtFilter" name="court_id" onchange="filterTodayCourt(event, this.value)"
                        class="text-xs font-semibold px-2.5 py-1 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:border-cyan-500 focus:outline-none cursor-pointer">
                        <option value="all" {{ (empty($selectedCourtId) || $selectedCourtId === 'all') ? 'selected' : '' }}>All Courts ({{ $todayBookings->count() }})</option>
                        @foreach($courts as $court)
                            <option value="{{ $court->id }}" {{ $selectedCourtId == $court->id ? 'selected' : '' }}>
                                Court {{ $court->court_number }}: {{ $court->name }} ({{ $todayBookings->where('court_id', $court->id)->count() }})
                            </option>
                        @endforeach
                    </select>
                    <span class="text-xs text-theme-muted font-mono hidden sm:inline">{{ date('l, M d, Y') }}</span>
                </div>
            </div>

            <!-- Court Filter Pills for Today's Reservations -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none text-xs">
                <a href="{{ route('owner.dashboard', array_merge(request()->except('court_id'), ['court_id' => 'all'])) }}"
                    onclick="filterTodayCourt(event, 'all')"
                    data-court-filter="all"
                    class="today-court-pill px-3 py-1.5 rounded-xl font-bold transition-all whitespace-nowrap flex items-center gap-1.5 {{ (empty($selectedCourtId) || $selectedCourtId === 'all') ? 'bg-cyan-500 text-slate-950 shadow-md shadow-cyan-500/20' : 'bg-stone-200 dark:bg-stone-800 text-theme-body hover:bg-stone-300 dark:hover:bg-stone-700' }}">
                    <i class="fa-solid fa-table-tennis-paddle-ball"></i>
                    <span>All Courts</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ (empty($selectedCourtId) || $selectedCourtId === 'all') ? 'bg-slate-950 text-cyan-400' : 'bg-stone-300 dark:bg-stone-700 text-theme-muted' }}">{{ $todayBookings->count() }}</span>
                </a>
                @foreach($courts as $court)
                    @php
                        $courtCount = $todayBookings->where('court_id', $court->id)->count();
                        $isActiveCourt = ($selectedCourtId == $court->id);
                    @endphp
                    <a href="{{ route('owner.dashboard', array_merge(request()->except('court_id'), ['court_id' => $court->id])) }}"
                        onclick="filterTodayCourt(event, '{{ $court->id }}')"
                        data-court-filter="{{ $court->id }}"
                        class="today-court-pill px-3 py-1.5 rounded-xl font-bold transition-all whitespace-nowrap flex items-center gap-1.5 {{ $isActiveCourt ? 'bg-cyan-500 text-slate-950 shadow-md shadow-cyan-500/20' : 'bg-stone-200 dark:bg-stone-800 text-theme-body hover:bg-stone-300 dark:hover:bg-stone-700' }}">
                        <span>C{{ $court->court_number }}: {{ $court->name }}</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $isActiveCourt ? 'bg-slate-950 text-cyan-400' : 'bg-stone-300 dark:bg-stone-700 text-theme-muted' }}">{{ $courtCount }}</span>
                    </a>
                @endforeach
            </div>

            <div class="space-y-3" id="todayBookingsContainer">
                @php
                    $hasPastDividerShown = false;
                @endphp
                @forelse($todayBookings as $tb)
                    @if($tb->is_past && !$hasPastDividerShown && $todayBookings->where('is_past', false)->count() > 0)
                        @php $hasPastDividerShown = true; @endphp
                        <div class="today-past-divider flex items-center gap-2 pt-2 pb-1 text-theme-muted">
                            <span class="h-px bg-stone-300 dark:bg-stone-700 flex-1"></span>
                            <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-md bg-stone-200 dark:bg-stone-800 flex items-center gap-1">
                                <i class="fa-solid fa-clock-rotate-left"></i> Past Time Today
                            </span>
                            <span class="h-px bg-stone-300 dark:bg-stone-700 flex-1"></span>
                        </div>
                    @endif

                    <div class="today-booking-card p-3.5 rounded-2xl border transition-all flex items-center justify-between gap-3 {{ $tb->is_past ? 'bg-stone-100/60 dark:bg-stone-900/40 border-stone-200/60 dark:border-stone-800/60 opacity-75 hover:opacity-100' : 'bg-stone-100 dark:bg-stone-900 border-stone-200 dark:border-stone-800 shadow-sm' }} {{ (!empty($selectedCourtId) && $selectedCourtId !== 'all' && $tb->court_id != $selectedCourtId) ? 'hidden' : '' }}"
                        data-court-id="{{ $tb->court_id }}"
                        data-is-past="{{ $tb->is_past ? '1' : '0' }}">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl flex-shrink-0 flex items-center justify-center font-bold text-xs border {{ $tb->is_past ? 'bg-stone-200 dark:bg-stone-800 text-theme-muted border-stone-300 dark:border-stone-700' : 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border-cyan-500/30' }}">
                                C{{ $tb->court->court_number }}
                            </div>
                            <div class="truncate">
                                <div class="flex items-center gap-2 truncate">
                                    <h4 class="text-xs font-bold text-theme-heading truncate">{{ $tb->court->name }}</h4>
                                    @if($tb->is_past)
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-stone-200 dark:bg-stone-800 text-theme-muted uppercase">Past</span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-theme-muted truncate">
                                    <span class="font-semibold text-theme-heading font-mono">{{ date('g:i A', strtotime($tb->start_time)) }} - {{ date('g:i A', strtotime($tb->end_time)) }}</span>
                                    • {{ $tb->customer_name }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            @if($tb->booking_status === 'confirmed')
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-cyan-500/20 text-cyan-700 dark:text-cyan-400 border border-cyan-500/30 uppercase">Confirmed</span>
                            @elseif($tb->booking_status === 'pending_approval')
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-500/20 text-amber-700 dark:text-amber-400 border border-amber-500/30 uppercase">In Review</span>
                            @elseif($tb->booking_status === 'held')
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-cyan-500/20 text-cyan-700 dark:text-cyan-400 border border-cyan-500/30 uppercase">Held</span>
                            @endif

                            <button type="button"
                                onclick="openReservationModal({{ json_encode([
                                    'id' => $tb->id,
                                    'reference' => $tb->booking_reference,
                                    'court_name' => $tb->court->name,
                                    'court_number' => $tb->court->court_number,
                                    'court_type' => ucfirst($tb->court->type) . ' (' . ($tb->court->surface_type ?? 'Tournament Grade') . ')',
                                    'customer_name' => $tb->customer_name,
                                    'customer_phone' => $tb->customer_phone,
                                    'customer_email' => $tb->customer_email ?: 'None provided',
                                    'players_count' => $tb->players_count ?? 4,
                                    'booking_date' => $tb->booking_date->format('l, F j, Y'),
                                    'time_range' => date('g:i A', strtotime($tb->start_time)) . ' – ' . date('g:i A', strtotime($tb->end_time)),
                                    'total_hours' => $tb->total_hours,
                                    'rate_per_hour' => '₱' . number_format($tb->rate_per_hour, 2) . ' / hr',
                                    'formatted_amount' => $tb->formatted_amount,
                                    'payment_method' => ucwords(str_replace('_', ' ', $tb->payment_method)),
                                    'payment_status' => ucfirst($tb->payment_status),
                                    'booking_status' => $tb->booking_status,
                                    'is_past' => $tb->is_past,
                                    'receipt_url' => $tb->receipt_url,
                                    'receipt_time' => $tb->receipt_uploaded_at?->diffForHumans(),
                                    'approver' => $tb->approver?->name,
                                    'approved_at' => $tb->approved_at?->format('M d, Y g:i A'),
                                    'slots' => $tb->slots->map(fn($s) => date('g:i A', strtotime($s->slot_time)))->values()->all(),
                                    'track_url' => route('booking.track', $tb->booking_reference),
                                    'created_at' => $tb->created_at->format('M d, Y g:i A') . ' (' . $tb->created_at->diffForHumans() . ')',
                                ]) }})"
                                class="px-2.5 py-1.5 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-cyan-500 hover:text-slate-950 text-theme-body text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer"
                                title="View Reservation Details">
                                <i class="fa-solid fa-circle-info text-cyan-600 dark:text-cyan-400"></i>
                                <span>Details</span>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-theme-muted" id="noTodayBookingsMsg">
                        No reservations on court for today yet.
                    </div>
                @endforelse

                <div class="py-8 text-center text-xs text-theme-muted hidden" id="noTodayCourtFilteredMsg">
                    No reservations found for the selected court today.
                </div>
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
                    <div class="p-3 rounded-2xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 flex items-center justify-between text-xs gap-2">
                        <div class="min-w-0 truncate">
                            <div class="flex items-center gap-2 truncate">
                                <span class="font-bold text-theme-heading truncate">{{ $rb->customer_name }}</span>
                                <span class="font-mono text-[10px] text-theme-muted flex-shrink-0">({{ $rb->booking_reference }})</span>
                            </div>
                            <p class="text-[11px] text-theme-muted mt-0.5 truncate">
                                {{ $rb->court->name }} • {{ $rb->booking_date->format('M d') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <div class="text-right">
                                <div class="font-black text-theme-heading">{{ $rb->formatted_amount }}</div>
                                <span class="text-[10px] capitalize text-theme-muted">{{ str_replace('_', ' ', $rb->booking_status) }}</span>
                            </div>
                            <button type="button"
                                onclick="openReservationModal({{ json_encode([
                                    'id' => $rb->id,
                                    'reference' => $rb->booking_reference,
                                    'court_name' => $rb->court->name,
                                    'court_number' => $rb->court->court_number,
                                    'court_type' => ucfirst($rb->court->type) . ' (' . ($rb->court->surface_type ?? 'Tournament Grade') . ')',
                                    'customer_name' => $rb->customer_name,
                                    'customer_phone' => $rb->customer_phone,
                                    'customer_email' => $rb->customer_email ?: 'None provided',
                                    'players_count' => $rb->players_count ?? 4,
                                    'booking_date' => $rb->booking_date->format('l, F j, Y'),
                                    'time_range' => date('g:i A', strtotime($rb->start_time)) . ' – ' . date('g:i A', strtotime($rb->end_time)),
                                    'total_hours' => $rb->total_hours,
                                    'rate_per_hour' => '₱' . number_format($rb->rate_per_hour, 2) . ' / hr',
                                    'formatted_amount' => $rb->formatted_amount,
                                    'payment_method' => ucwords(str_replace('_', ' ', $rb->payment_method)),
                                    'payment_status' => ucfirst($rb->payment_status),
                                    'booking_status' => $rb->booking_status,
                                    'receipt_url' => $rb->receipt_url,
                                    'receipt_time' => $rb->receipt_uploaded_at?->diffForHumans(),
                                    'approver' => $rb->approver?->name,
                                    'approved_at' => $rb->approved_at?->format('M d, Y g:i A'),
                                    'slots' => $rb->slots->map(fn($s) => date('g:i A', strtotime($s->slot_time)))->values()->all(),
                                    'track_url' => route('booking.track', $rb->booking_reference),
                                    'created_at' => $rb->created_at->format('M d, Y g:i A') . ' (' . $rb->created_at->diffForHumans() . ')',
                                ]) }})"
                                class="p-1.5 rounded-lg bg-stone-200 dark:bg-stone-800 hover:bg-cyan-500 hover:text-slate-950 text-theme-muted hover:text-slate-950 text-xs transition-colors cursor-pointer"
                                title="View Details">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Comprehensive Reservation Details Modal -->
<div id="reservationDetailsModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-slate-950/85 backdrop-blur-md opacity-0 pointer-events-none transition-all duration-300 overflow-y-auto">
    <div class="glass-panel p-6 sm:p-8 rounded-3xl max-w-2xl w-full border border-stone-200 dark:border-stone-800 shadow-2xl relative space-y-6 my-auto max-h-[92vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="flex items-start justify-between pb-4 border-b border-stone-200 dark:border-stone-800 gap-4">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-theme-muted">Reservation Details</span>
                    <div id="resModalStatusBadgeContainer">
                        <!-- Status Badge -->
                    </div>
                </div>
                <div class="flex items-center gap-2 mt-1">
                    <h3 class="text-xl sm:text-2xl font-mono font-black text-cyan-600 dark:text-cyan-400" id="resModalRef">PF-0000</h3>
                    <button type="button" onclick="copyModalRef()" class="text-xs text-theme-muted hover:text-cyan-500 transition-colors p-1 cursor-pointer" title="Copy Reference">
                        <i class="fa-regular fa-copy" id="resModalCopyIcon"></i>
                    </button>
                </div>
            </div>
            <!-- Close Button -->
            <button type="button" onclick="closeReservationModal()" class="shrink-0 text-theme-muted hover:text-theme-heading p-2 rounded-xl bg-stone-200/50 dark:bg-stone-800/50 hover:bg-stone-300 dark:hover:bg-stone-700 transition-colors cursor-pointer" title="Close modal">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Details Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
            <!-- Court & Schedule Card -->
            <div class="p-4 rounded-2xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 space-y-2.5">
                <h4 class="font-bold uppercase tracking-wider text-[11px] text-cyan-700 dark:text-cyan-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-table-tennis-paddle-ball"></i> Court & Timeslot
                </h4>
                <div class="space-y-1.5 text-theme-body">
                    <div class="flex justify-between">
                        <span class="text-theme-muted">Court:</span>
                        <span class="font-bold text-theme-heading" id="resModalCourt">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-theme-muted">Court Type:</span>
                        <span class="font-medium text-theme-heading" id="resModalCourtType">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-theme-muted">Date:</span>
                        <span class="font-bold text-theme-heading" id="resModalDate">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-theme-muted">Time Range:</span>
                        <span class="font-bold text-cyan-600 dark:text-cyan-400" id="resModalTimeRange">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-theme-muted">Total Hours:</span>
                        <span class="font-semibold text-theme-heading" id="resModalHours">-</span>
                    </div>
                    <div class="pt-2 border-t border-stone-200 dark:border-stone-800">
                        <span class="text-[11px] text-theme-muted block mb-1.5 font-semibold">Hourly Slots Booked:</span>
                        <div class="flex flex-wrap gap-1.5" id="resModalSlotsPills">
                            <!-- Slots pills -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Player & Payment Info Card -->
            <div class="p-4 rounded-2xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 space-y-2.5">
                <h4 class="font-bold uppercase tracking-wider text-[11px] text-amber-700 dark:text-amber-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-user-check"></i> Player & Payment
                </h4>
                <div class="space-y-1.5 text-theme-body">
                    <div class="flex justify-between">
                        <span class="text-theme-muted">Player Name:</span>
                        <span class="font-bold text-theme-heading" id="resModalPlayer">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-theme-muted">Mobile Phone:</span>
                        <a id="resModalPhoneLink" href="" class="font-mono text-cyan-600 dark:text-cyan-400 hover:underline">-</a>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-theme-muted">Email:</span>
                        <span class="text-theme-heading font-medium" id="resModalEmail">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-theme-muted">Players Count:</span>
                        <span class="font-semibold text-theme-heading" id="resModalPlayersCount">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-theme-muted">Payment Channel:</span>
                        <span class="font-bold text-theme-heading" id="resModalMethod">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-theme-muted">Payment Status:</span>
                        <span id="resModalPayStatus">-</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-stone-200 dark:border-stone-800 text-sm">
                        <span class="font-bold text-theme-heading">Total Amount:</span>
                        <span class="font-black text-cyan-600 dark:text-cyan-400" id="resModalAmount">-</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Receipt Proof Preview (if uploaded) -->
        <div id="resModalReceiptBox" class="p-4 rounded-2xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 space-y-2.5 hidden">
            <div class="flex items-center justify-between">
                <h4 class="font-bold uppercase tracking-wider text-[11px] text-theme-heading flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-amber-600 dark:text-amber-400"></i> Uploaded Payment Receipt Proof
                </h4>
                <span class="text-[11px] text-theme-muted" id="resModalReceiptTime">-</span>
            </div>
            <div class="flex items-center gap-4">
                <a id="resModalReceiptImgLink" href="" target="_blank" class="block max-w-xs overflow-hidden rounded-xl border border-stone-300 dark:border-stone-700 hover:border-cyan-500 transition-colors">
                    <img id="resModalReceiptImg" src="" alt="Receipt" class="h-28 w-auto object-cover rounded-xl">
                </a>
                <div class="text-xs text-theme-muted space-y-1">
                    <p class="text-theme-heading font-semibold">Screenshot Attached</p>
                    <p class="text-[11px]">Uploaded by customer during booking submission.</p>
                    <a id="resModalReceiptViewFull" href="" target="_blank" class="inline-flex items-center gap-1 text-cyan-600 dark:text-cyan-400 hover:underline pt-1">
                        <i class="fa-solid fa-magnifying-glass-plus"></i> View Full Resolution
                    </a>
                </div>
            </div>
        </div>

        <!-- Verification / Audit Information -->
        <div id="resModalAuditBox" class="p-3 rounded-xl bg-stone-100/70 dark:bg-stone-900/50 border border-stone-200 dark:border-stone-800 text-xs text-theme-muted flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-clock text-theme-muted"></i>
                <span>Created: <span class="text-theme-heading font-medium" id="resModalCreatedAt">-</span></span>
            </div>
            <div id="resModalApprovedByContainer" class="hidden">
                <span class="text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1">
                    <i class="fa-solid fa-circle-check"></i> Approved by <span id="resModalApproverName">-</span>
                </span>
            </div>
        </div>

        <!-- Modal Footer Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pt-4 border-t border-stone-200 dark:border-stone-800 gap-3">
            <a id="resModalTrackLink" href="" target="_blank"
                class="px-4 py-2.5 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-stone-300 dark:hover:bg-stone-700 text-xs font-bold text-theme-heading flex items-center justify-center gap-2 transition-colors">
                <i class="fa-solid fa-arrow-up-right-from-square text-cyan-600 dark:text-cyan-400"></i> Open Live Tracker Pass
            </a>

            <div class="flex items-center justify-end gap-2">
                <!-- If Pending Approval: Approve and Reject buttons -->
                <div id="resModalPendingActions" class="flex items-center gap-2 hidden">
                    <form id="resModalApproveForm" method="POST" action="">
                        @csrf
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs shadow-md shadow-cyan-500/20 flex items-center gap-1.5 transition-all cursor-pointer">
                            <i class="fa-solid fa-check"></i> Approve Booking
                        </button>
                    </form>
                    <button type="button" id="resModalRejectBtn" onclick="" class="px-3.5 py-2.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-500 font-bold text-xs border border-rose-500/30 flex items-center gap-1.5 transition-colors cursor-pointer">
                        <i class="fa-solid fa-xmark"></i> Reject
                    </button>
                </div>

                <button type="button" onclick="closeReservationModal()" class="px-4 py-2.5 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-stone-300 dark:hover:bg-stone-700 text-xs font-semibold text-theme-heading transition-colors cursor-pointer">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- High-Resolution Receipt Preview Modal -->
<div id="receiptModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md opacity-0 pointer-events-none transition-all duration-300">
    <div class="glass-dropdown p-6 rounded-3xl max-w-lg w-full border border-stone-300 dark:border-stone-700 shadow-2xl relative space-y-4">
        <div class="flex items-start justify-between border-b border-stone-200 dark:border-stone-800 pb-3 gap-3">
            <div class="min-w-0 pr-2">
                <h3 class="text-sm font-bold text-theme-heading" id="rcptModalTitle">Payment Receipt Verification</h3>
                <p class="text-xs text-theme-muted" id="rcptModalMeta">-</p>
            </div>
            <button type="button" onclick="closeReceiptModal()" class="shrink-0 text-theme-muted hover:text-theme-heading p-2 rounded-xl hover:bg-stone-200/50 dark:hover:bg-stone-800/50 transition-colors cursor-pointer -mt-1 -mr-1" title="Close modal">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
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
    let currentModalBooking = null;

    function openReservationModal(b) {
        currentModalBooking = b;

        document.getElementById('resModalRef').textContent = b.reference;
        document.getElementById('resModalCourt').textContent = b.court_name;
        document.getElementById('resModalCourtType').textContent = b.court_type;
        document.getElementById('resModalDate').textContent = b.booking_date;
        document.getElementById('resModalTimeRange').textContent = b.time_range;
        document.getElementById('resModalHours').textContent = `${b.total_hours} Hours`;
        
        // Slots
        const slotsContainer = document.getElementById('resModalSlotsPills');
        slotsContainer.innerHTML = '';
        if (b.slots && b.slots.length > 0) {
            b.slots.forEach(slot => {
                const span = document.createElement('span');
                span.className = 'px-2 py-0.5 rounded-lg bg-stone-200 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 text-theme-heading font-mono text-[11px] font-semibold';
                span.textContent = slot;
                slotsContainer.appendChild(span);
            });
        } else {
            slotsContainer.innerHTML = `<span class="text-theme-muted italic text-[11px]">${b.time_range}</span>`;
        }

        // Customer
        document.getElementById('resModalPlayer').textContent = b.customer_name;
        const phoneLink = document.getElementById('resModalPhoneLink');
        phoneLink.textContent = b.customer_phone;
        phoneLink.href = `tel:${b.customer_phone}`;
        document.getElementById('resModalEmail').textContent = b.customer_email || 'Not provided';
        document.getElementById('resModalPlayersCount').textContent = `${b.players_count} Players`;

        // Payment
        document.getElementById('resModalMethod').textContent = b.payment_method;
        document.getElementById('resModalAmount').textContent = b.formatted_amount;

        // Payment Status badge
        const payStatusEl = document.getElementById('resModalPayStatus');
        if (b.payment_status && b.payment_status.toLowerCase() === 'paid') {
            payStatusEl.className = 'px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30 uppercase';
            payStatusEl.textContent = 'Paid';
        } else {
            payStatusEl.className = 'px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-500/20 text-amber-700 dark:text-amber-400 border border-amber-500/30 uppercase';
            payStatusEl.textContent = 'Unpaid';
        }

        // Booking Status Badge
        const statusContainer = document.getElementById('resModalStatusBadgeContainer');
        let statusBadgeHtml = '';
        if (b.is_past) {
            statusBadgeHtml = '<span class="px-3 py-1 rounded-full text-xs font-bold bg-stone-500/20 text-stone-600 dark:text-stone-300 border border-stone-500/30 uppercase tracking-wider inline-flex items-center gap-1.5"><i class="fa-solid fa-clock-rotate-left"></i> Completed (Past)</span>';
        } else if (b.booking_status === 'confirmed') {
            statusBadgeHtml = '<span class="px-3 py-1 rounded-full text-xs font-extrabold bg-cyan-500/15 text-cyan-700 dark:text-cyan-300 border border-cyan-500/30 uppercase tracking-wider inline-flex items-center gap-1.5"><i class="fa-solid fa-circle-check"></i> Confirmed</span>';
        } else if (b.booking_status === 'pending_approval') {
            statusBadgeHtml = '<span class="px-3 py-1 rounded-full text-xs font-extrabold bg-amber-500/15 text-amber-800 dark:text-amber-300 border border-amber-500/30 uppercase tracking-wider inline-flex items-center gap-1.5"><i class="fa-solid fa-clock-rotate-left"></i> In Review</span>';
        } else if (b.booking_status === 'held') {
            statusBadgeHtml = '<span class="px-3 py-1 rounded-full text-xs font-extrabold bg-cyan-500/15 text-cyan-700 dark:text-cyan-300 border border-cyan-500/30 uppercase tracking-wider inline-flex items-center gap-1.5"><i class="fa-solid fa-stopwatch"></i> Held</span>';
        } else {
            statusBadgeHtml = `<span class="px-3 py-1 rounded-full text-xs font-extrabold bg-stone-200 dark:bg-stone-800 text-theme-muted uppercase tracking-wider">${b.booking_status}</span>`;
        }
        statusContainer.innerHTML = statusBadgeHtml;

        // Receipt Box
        const receiptBox = document.getElementById('resModalReceiptBox');
        if (b.receipt_url) {
            document.getElementById('resModalReceiptImg').src = b.receipt_url;
            document.getElementById('resModalReceiptImgLink').href = b.receipt_url;
            document.getElementById('resModalReceiptViewFull').href = b.receipt_url;
            document.getElementById('resModalReceiptTime').textContent = b.receipt_time ? `Uploaded ${b.receipt_time}` : '';
            receiptBox.classList.remove('hidden');
        } else {
            receiptBox.classList.add('hidden');
        }

        // Audit info
        document.getElementById('resModalCreatedAt').textContent = b.created_at;
        const approverContainer = document.getElementById('resModalApprovedByContainer');
        if (b.approver) {
            document.getElementById('resModalApproverName').textContent = `${b.approver} ${b.approved_at ? '(' + b.approved_at + ')' : ''}`;
            approverContainer.classList.remove('hidden');
        } else {
            approverContainer.classList.add('hidden');
        }

        // Tracker Link
        document.getElementById('resModalTrackLink').href = b.track_url;

        // Pending Approval actions
        const pendingActions = document.getElementById('resModalPendingActions');
        if (b.booking_status === 'pending_approval') {
            document.getElementById('resModalApproveForm').action = `/owner/approvals/${b.id}/approve`;
            const rejectBtn = document.getElementById('resModalRejectBtn');
            rejectBtn.onclick = function() {
                closeReservationModal();
                openRejectModal(b.id, b.reference);
            };
            pendingActions.classList.remove('hidden');
        } else {
            pendingActions.classList.add('hidden');
        }

        // Open Modal
        const modal = document.getElementById('reservationDetailsModal');
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100', 'pointer-events-auto');
    }

    function closeReservationModal() {
        const modal = document.getElementById('reservationDetailsModal');
        modal.classList.add('opacity-0', 'pointer-events-none');
        modal.classList.remove('opacity-100', 'pointer-events-auto');
    }

    function copyModalRef() {
        const ref = document.getElementById('resModalRef').textContent;
        navigator.clipboard.writeText(ref).then(() => {
            const icon = document.getElementById('resModalCopyIcon');
            icon.className = 'fa-solid fa-check text-emerald-500';
            setTimeout(() => {
                icon.className = 'fa-regular fa-copy';
            }, 1500);
        });
    }

    document.getElementById('reservationDetailsModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeReservationModal();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeReservationModal();
            closeReceiptModal();
            closeRejectModal();
        }
    });

    // Instant Court Filter for Today's Reservations
    function filterTodayCourt(e, courtId) {
        if (e && e.preventDefault) {
            e.preventDefault();
        }

        // Update active pill styling
        document.querySelectorAll('.today-court-pill').forEach(pill => {
            const isMatch = pill.getAttribute('data-court-filter') === String(courtId);
            const badge = pill.querySelector('span:last-child');
            if (isMatch) {
                pill.className = 'today-court-pill px-3 py-1.5 rounded-xl font-bold transition-all whitespace-nowrap flex items-center gap-1.5 bg-cyan-500 text-slate-950 shadow-md shadow-cyan-500/20';
                if (badge) badge.className = 'px-1.5 py-0.2 rounded-full text-[10px] bg-slate-950 text-cyan-400';
            } else {
                pill.className = 'today-court-pill px-3 py-1.5 rounded-xl font-bold transition-all whitespace-nowrap flex items-center gap-1.5 bg-stone-200 dark:bg-stone-800 text-theme-body hover:bg-stone-300 dark:hover:bg-stone-700';
                if (badge) badge.className = 'px-1.5 py-0.2 rounded-full text-[10px] bg-stone-300 dark:bg-stone-700 text-theme-muted';
            }
        });

        // Sync dropdown select element
        const selectEl = document.getElementById('todayCourtFilter');
        if (selectEl && selectEl.value !== String(courtId)) {
            selectEl.value = String(courtId);
        }

        // Update URL state without reload
        const url = new URL(window.location);
        if (courtId === 'all') {
            url.searchParams.delete('court_id');
        } else {
            url.searchParams.set('court_id', courtId);
        }
        window.history.replaceState({}, '', url);

        // Filter cards
        const cards = document.querySelectorAll('.today-booking-card');
        let visibleCount = 0;
        let visibleUpcoming = 0;
        let visiblePast = 0;

        cards.forEach(card => {
            const cardCourtId = card.getAttribute('data-court-id');
            const isPast = card.getAttribute('data-is-past') === '1';
            const matches = (courtId === 'all' || cardCourtId === String(courtId));

            if (matches) {
                card.classList.remove('hidden');
                visibleCount++;
                if (isPast) visiblePast++; else visibleUpcoming++;
            } else {
                card.classList.add('hidden');
            }
        });

        // Toggle past divider if present
        const divider = document.querySelector('.today-past-divider');
        if (divider) {
            if (visibleUpcoming > 0 && visiblePast > 0) {
                divider.classList.remove('hidden');
            } else {
                divider.classList.add('hidden');
            }
        }

        // Empty filtered message
        const emptyFiltered = document.getElementById('noTodayCourtFilteredMsg');
        if (emptyFiltered) {
            if (cards.length > 0 && visibleCount === 0) {
                emptyFiltered.classList.remove('hidden');
            } else {
                emptyFiltered.classList.add('hidden');
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const courtParam = urlParams.get('court_id');
        if (courtParam) {
            filterTodayCourt(null, courtParam);
        }
    });

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
