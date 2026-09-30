@extends('layouts.owner')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-stone-200 dark:border-stone-800 gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h2 class="text-xl font-bold text-theme-heading">Reservations Ledger</h2>
                @if($status === 'confirmed')
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-cyan-500/15 text-cyan-700 dark:text-cyan-300 border border-cyan-500/30 uppercase tracking-wider">
                        Confirmed Only (Default)
                    </span>
                @elseif($status === 'all')
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-stone-200 dark:bg-stone-800 text-theme-heading border border-stone-300 dark:border-stone-700 uppercase tracking-wider">
                        All Statuses
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500/15 text-amber-800 dark:text-amber-300 border border-amber-500/30 uppercase tracking-wider">
                        {{ ucwords(str_replace('_', ' ', $status)) }}
                    </span>
                @endif
            </div>
            <p class="text-xs text-theme-muted mt-1">Default view displays confirmed bookings. You can search or switch to any status or all statuses at any time.</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" onclick="openManualReserveModal()"
                class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-xs shadow-lg shadow-amber-500/20 flex items-center gap-2 transition-all cursor-pointer">
                <i class="fa-solid fa-calendar-plus"></i>
                <span>Reserve / Block Court (Offline Rental)</span>
            </button>
            <div class="text-xs text-theme-muted font-mono hidden sm:block">
                Total Records: {{ $bookings->total() }}
            </div>
        </div>
    </div>

    <!-- Quick Status Filter Pills -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
        <a href="{{ route('owner.bookings.index', array_merge(request()->except('status', 'page'), ['status' => 'confirmed'])) }}"
            class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5 {{ $status === 'confirmed' ? 'bg-cyan-500 text-slate-950 shadow-md shadow-cyan-500/20' : 'bg-stone-200 dark:bg-stone-800 text-theme-body hover:bg-stone-300 dark:hover:bg-stone-700' }}">
            <span>Confirmed</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'confirmed' ? 'bg-slate-950 text-cyan-400' : 'bg-stone-300 dark:bg-stone-700 text-theme-muted' }}">{{ $statusCounts['confirmed'] ?? 0 }}</span>
        </a>

        <a href="{{ route('owner.bookings.index', array_merge(request()->except('status', 'page'), ['status' => 'all'])) }}"
            class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5 {{ $status === 'all' ? 'bg-cyan-500 text-slate-950 shadow-md shadow-cyan-500/20' : 'bg-stone-200 dark:bg-stone-800 text-theme-body hover:bg-stone-300 dark:hover:bg-stone-700' }}">
            <span>All Statuses</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'all' ? 'bg-slate-950 text-cyan-400' : 'bg-stone-300 dark:bg-stone-700 text-theme-muted' }}">{{ $statusCounts['all'] ?? 0 }}</span>
        </a>

        <a href="{{ route('owner.bookings.index', array_merge(request()->except('status', 'page'), ['status' => 'pending_approval'])) }}"
            class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5 {{ $status === 'pending_approval' ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/20' : 'bg-stone-200 dark:bg-stone-800 text-theme-body hover:bg-stone-300 dark:hover:bg-stone-700' }}">
            <span>Pending Approval</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'pending_approval' ? 'bg-slate-950 text-amber-400' : 'bg-stone-300 dark:bg-stone-700 text-theme-muted' }}">{{ $statusCounts['pending_approval'] ?? 0 }}</span>
        </a>

        <a href="{{ route('owner.bookings.index', array_merge(request()->except('status', 'page'), ['status' => 'held'])) }}"
            class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5 {{ $status === 'held' ? 'bg-cyan-500 text-slate-950 shadow-md shadow-cyan-500/20' : 'bg-stone-200 dark:bg-stone-800 text-theme-body hover:bg-stone-300 dark:hover:bg-stone-700' }}">
            <span>Held</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'held' ? 'bg-slate-950 text-cyan-400' : 'bg-stone-300 dark:bg-stone-700 text-theme-muted' }}">{{ $statusCounts['held'] ?? 0 }}</span>
        </a>

        <a href="{{ route('owner.bookings.index', array_merge(request()->except('status', 'page'), ['status' => 'cancelled'])) }}"
            class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5 {{ $status === 'cancelled' ? 'bg-rose-500 text-white shadow-md shadow-rose-500/20' : 'bg-stone-200 dark:bg-stone-800 text-theme-body hover:bg-stone-300 dark:hover:bg-stone-700' }}">
            <span>Cancelled</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'cancelled' ? 'bg-slate-950 text-rose-400' : 'bg-stone-300 dark:bg-stone-700 text-theme-muted' }}">{{ $statusCounts['cancelled'] ?? 0 }}</span>
        </a>

        <a href="{{ route('owner.bookings.index', array_merge(request()->except('status', 'page'), ['status' => 'rejected'])) }}"
            class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5 {{ $status === 'rejected' ? 'bg-rose-500 text-white shadow-md shadow-rose-500/20' : 'bg-stone-200 dark:bg-stone-800 text-theme-body hover:bg-stone-300 dark:hover:bg-stone-700' }}">
            <span>Rejected</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'rejected' ? 'bg-slate-950 text-rose-400' : 'bg-stone-300 dark:bg-stone-700 text-theme-muted' }}">{{ $statusCounts['rejected'] ?? 0 }}</span>
        </a>

        <a href="{{ route('owner.bookings.index', array_merge(request()->except('status', 'page'), ['status' => 'expired'])) }}"
            class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5 {{ $status === 'expired' ? 'bg-stone-600 text-white' : 'bg-stone-200 dark:bg-stone-800 text-theme-body hover:bg-stone-300 dark:hover:bg-stone-700' }}">
            <span>Expired</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'expired' ? 'bg-slate-950 text-stone-300' : 'bg-stone-300 dark:bg-stone-700 text-theme-muted' }}">{{ $statusCounts['expired'] ?? 0 }}</span>
        </a>
    </div>

    <!-- Filters & Search Bar -->
    <div class="p-4 rounded-3xl glass-card border border-stone-200 dark:border-stone-800">
        <form method="GET" action="{{ route('owner.bookings.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs">
            <!-- Search -->
            <div>
                <label class="block font-semibold text-theme-body mb-1">Search Ref / Name / Phone</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="e.g. PF-2026 or Juan"
                    class="w-full px-3 py-2 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 focus:border-cyan-500">
            </div>

            <!-- Court Filter -->
            <div>
                <label class="block font-semibold text-theme-body mb-1">Filter Court</label>
                <select name="court_id" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:border-cyan-500">
                    <option value="">All Courts</option>
                    @foreach($courts as $court)
                        <option value="{{ $court->id }}" {{ request('court_id') == $court->id ? 'selected' : '' }}>
                            {{ $court->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block font-semibold text-theme-body mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:border-cyan-500">
                    <option value="confirmed" {{ $status === 'confirmed' ? 'selected' : '' }}>Confirmed (Default)</option>
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="pending_approval" {{ $status === 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                    <option value="held" {{ $status === 'held' ? 'selected' : '' }}>Held (2-Min Hold)</option>
                    <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="expired" {{ $status === 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>

            <!-- Date Filter -->
            <div>
                <label class="block font-semibold text-theme-body mb-1">Filter Date</label>
                <input type="date" name="date" value="{{ request('date') }}"
                    class="w-full px-3 py-2 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:border-cyan-500">
            </div>

            <!-- Filter Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold transition-all cursor-pointer">
                    Filter
                </button>
                <a href="{{ route('owner.bookings.index') }}" class="px-3 py-2 rounded-xl bg-stone-200 dark:bg-stone-800 text-theme-body hover:bg-stone-300 dark:hover:bg-stone-700 transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Bookings Table -->
    <div class="rounded-3xl glass-panel border border-stone-200 dark:border-stone-800 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-stone-100 dark:bg-stone-900/80 text-theme-muted border-b border-stone-200 dark:border-stone-800 uppercase text-[10px] tracking-wider">
                        <th class="py-3.5 px-4">Reference</th>
                        <th class="py-3.5 px-4">Player</th>
                        <th class="py-3.5 px-4">Court</th>
                        <th class="py-3.5 px-4">Date & Time</th>
                        <th class="py-3.5 px-4">Snapshot Rate</th>
                        <th class="py-3.5 px-4">Total Amount</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200 dark:divide-stone-800">
                    @forelse($bookings as $booking)
                        <tr class="hover:bg-stone-200/50 dark:hover:bg-stone-800/40 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-cyan-600 dark:text-cyan-400">
                                <a href="{{ route('booking.track', $booking->booking_reference) }}" target="_blank" class="hover:underline">
                                    {{ $booking->booking_reference }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-theme-heading">{{ $booking->customer_name }}</div>
                                <div class="font-mono text-[11px] text-theme-muted">{{ $booking->customer_phone }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-theme-heading">{{ $booking->court->name }}</div>
                                <div class="text-[10px] text-theme-muted">{{ ucfirst($booking->court->type) }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-theme-heading">{{ $booking->booking_date->format('M d, Y') }}</div>
                                <div class="text-cyan-600 dark:text-cyan-400 text-[11px] font-semibold">
                                    {{ date('g:i A', strtotime($booking->start_time)) }} - {{ date('g:i A', strtotime($booking->end_time)) }} ({{ $booking->total_hours }}h)
                                </div>
                            </td>
                            <!-- Req #7: Snapshot rate -->
                            <td class="py-3.5 px-4 font-mono text-theme-body">
                                ₱{{ number_format($booking->rate_per_hour, 2) }}/hr
                            </td>
                            <td class="py-3.5 px-4 font-black text-theme-heading text-sm">
                                {{ $booking->formatted_amount }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($booking->booking_status === 'confirmed')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-500/20 text-cyan-700 dark:text-cyan-300 border border-cyan-500/30 uppercase">Confirmed</span>
                                @elseif($booking->booking_status === 'pending_approval')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-800 dark:text-amber-300 border border-amber-500/30 uppercase">Pending Approval</span>
                                @elseif($booking->booking_status === 'held')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-500/20 text-cyan-700 dark:text-cyan-300 border border-cyan-500/30 uppercase">Held</span>
                                @elseif($booking->booking_status === 'cancelled')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/30 uppercase" title="{{ $booking->rejection_reason ?? 'Cancelled by Court Owner' }}">Cancelled</span>
                                @elseif($booking->booking_status === 'rejected')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-700 dark:text-rose-300 border border-rose-500/30 uppercase">Rejected</span>
                                @elseif($booking->booking_status === 'expired')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-stone-200 dark:bg-stone-800 text-theme-muted uppercase">Expired</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('booking.track', $booking->booking_reference) }}" target="_blank"
                                        class="px-2.5 py-1.5 rounded-lg bg-stone-200 dark:bg-stone-800 hover:bg-cyan-500 hover:text-slate-950 text-theme-body font-semibold transition-colors" title="View Reservation Pass">
                                        Pass &rarr;
                                    </a>

                                    @if(!in_array($booking->booking_status, ['cancelled', 'rejected', 'expired']))
                                        <button type="button"
                                            onclick="openCancelBookingModal({{ $booking->id }}, '{{ addslashes($booking->booking_reference) }}', '{{ addslashes($booking->customer_name) }}', '{{ addslashes($booking->court->name) }}')"
                                            class="px-2.5 py-1.5 rounded-lg bg-rose-500/10 hover:bg-rose-500 text-rose-600 hover:text-white border border-rose-500/30 text-xs font-bold transition-all cursor-pointer flex items-center gap-1"
                                            title="Cancel reservation of this user">
                                            <i class="fa-solid fa-ban text-[10px]"></i> Cancel
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-theme-muted">
                                <div>No reservations match your filter criteria.</div>
                                @if($status === 'confirmed')
                                    <div class="mt-2 text-xs">
                                        <a href="{{ route('owner.bookings.index', array_merge(request()->except('status', 'page'), ['status' => 'all'])) }}" class="text-cyan-600 dark:text-cyan-400 hover:underline font-semibold inline-flex items-center gap-1">
                                            <i class="fa-solid fa-list-check"></i> Switch to All Statuses to see other bookings
                                        </a>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
            <div class="p-4 border-t border-stone-200 dark:border-stone-800">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
</div>

<!-- HIDDEN FORM FOR CANCELLATION -->
<form id="cancelBookingForm" method="POST" action="" class="hidden">
    @csrf
    <input type="hidden" name="reason" id="cancelBookingReason">
</form>

<!-- MANUAL COURT RESERVATION MODAL (Req #2 - Whole Court Rental / Offline Booking) -->
<div id="manualReserveModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md opacity-0 pointer-events-none transition-all duration-300" onclick="if(event.target === this) closeManualReserveModal()">
    <div class="glass-dropdown p-6 sm:p-8 rounded-3xl max-w-xl w-full border border-stone-300 dark:border-stone-700 shadow-2xl relative space-y-4 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="flex items-start justify-between pb-3 border-b border-stone-200 dark:border-stone-800 gap-3">
            <div class="min-w-0 pr-2">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-500 text-[10px] font-bold uppercase tracking-wider mb-1">
                    <i class="fa-solid fa-handshake"></i> Offline Agreement / Whole-Court
                </div>
                <h3 class="text-lg font-bold text-theme-heading">Reserve / Block Court</h3>
                <p class="text-xs text-theme-muted">Block court slots for private whole-court rentals, clinics, or offline agreements.</p>
            </div>
            <button type="button" onclick="closeManualReserveModal()" class="shrink-0 text-theme-muted hover:text-theme-heading p-2 rounded-xl hover:bg-stone-200/50 dark:hover:bg-stone-800/50 transition-colors cursor-pointer -mt-1 -mr-1" title="Close modal">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form action="{{ route('owner.bookings.reserve') }}" method="POST" id="manualReserveForm" class="space-y-4 text-xs">
            @csrf

            <!-- Court & Date -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Select Court <span class="text-rose-500">*</span></label>
                    <select name="court_id" id="manualCourtSelect" required onchange="onManualCourtOrDateChange()"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500">
                        @foreach($courts as $c)
                            <option value="{{ $c->id }}"
                                data-rate="{{ (float) $c->price_per_hour }}"
                                data-start="{{ $c->start_hour }}"
                                data-end="{{ $c->end_hour }}"
                                data-hours="{{ $c->operating_hours_label }}"
                                data-max="{{ $c->max_players }}">
                                {{ $c->name }} ({{ $c->operating_hours_label }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Reservation Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="date" id="manualDateInput" value="{{ request('date', date('Y-m-d')) }}" required onchange="onManualCourtOrDateChange()"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500">
                </div>
            </div>

            <!-- Rental Mode: Whole Day vs Specific Slots -->
            <div class="p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/30 space-y-2.5">
                <label class="block font-bold text-amber-800 dark:text-amber-400 text-xs">Rental Duration Type <span class="text-rose-500">*</span></label>
                
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center gap-2 p-2.5 rounded-xl bg-white dark:bg-stone-900 border border-amber-500/40 cursor-pointer">
                        <input type="radio" name="slot_mode" value="all_day" id="modeAllDay" checked onchange="toggleManualSlotMode()" class="text-amber-500 focus:ring-amber-500">
                        <div class="min-w-0">
                            <div class="font-bold text-theme-heading text-xs">Rent Whole Day</div>
                            <div class="text-[10px] text-theme-muted" id="allDayHoursBadge">All court hours</div>
                        </div>
                    </label>

                    <label class="flex items-center gap-2 p-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 cursor-pointer">
                        <input type="radio" name="slot_mode" value="custom" id="modeCustom" onchange="toggleManualSlotMode()" class="text-amber-500 focus:ring-amber-500">
                        <div class="min-w-0">
                            <div class="font-bold text-theme-heading text-xs">Specific Timeslots</div>
                            <div class="text-[10px] text-theme-muted">Pick individual hours</div>
                        </div>
                    </label>
                </div>

                <!-- Custom Hourly Checkboxes Container -->
                <div id="customSlotsContainer" class="hidden pt-2 space-y-2 border-t border-amber-500/20">
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="text-theme-body font-semibold">Select Timeslots to Block:</span>
                        <div class="space-x-2">
                            <button type="button" onclick="selectAllCustomSlots(true)" class="text-cyan-600 dark:text-cyan-400 hover:underline font-bold">Select All</button>
                            <span class="text-stone-400">|</span>
                            <button type="button" onclick="selectAllCustomSlots(false)" class="text-theme-muted hover:underline">Clear</button>
                        </div>
                    </div>
                    <div id="manualSlotsGrid" class="grid grid-cols-3 sm:grid-cols-4 gap-1.5 max-h-40 overflow-y-auto p-1.5 rounded-xl bg-stone-100 dark:bg-stone-900/80 border border-stone-200 dark:border-stone-800">
                        <!-- Rendered by JS -->
                    </div>
                </div>
            </div>

            <!-- Renter Details -->
            <div class="space-y-3 p-3.5 rounded-2xl bg-stone-100/80 dark:bg-stone-900/80 border border-stone-200 dark:border-stone-800">
                <div class="font-bold text-theme-heading flex items-center gap-1.5">
                    <i class="fa-solid fa-user-tag text-cyan-500"></i> Renter / Customer Information
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-theme-body mb-1">Renter Name / Group <span class="text-rose-500">*</span></label>
                        <input type="text" name="customer_name" required placeholder="e.g. Coach Carlos or Pickleball League"
                            class="w-full px-3.5 py-2 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-theme-body mb-1">Contact Phone <span class="text-rose-500">*</span></label>
                        <input type="text" name="customer_phone" required placeholder="e.g. 0917-123-4567"
                            class="w-full px-3.5 py-2 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-theme-body mb-1">Email (Optional)</label>
                        <input type="email" name="customer_email" placeholder="renter@example.com"
                            class="w-full px-3.5 py-2 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-theme-body mb-1">Est. Number of Players</label>
                        <input type="number" min="1" max="50" name="players_count" id="manualPlayersCount" value="4"
                            class="w-full px-3.5 py-2 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500">
                    </div>
                </div>
            </div>

            <!-- Pricing & Offline Payment Settlement -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3.5 rounded-2xl bg-cyan-500/10 border border-cyan-500/30">
                <div>
                    <label class="block font-bold text-cyan-800 dark:text-cyan-400 mb-1">Total Fee (₱) <span class="text-theme-muted font-normal">(Auto/Custom)</span></label>
                    <input type="number" step="0.01" min="0" name="total_amount" id="manualTotalAmount" required
                        class="w-full px-3.5 py-2 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-black text-sm focus:outline-none focus:border-cyan-500">
                    <span class="text-[10px] text-theme-muted" id="priceCalculationHint">Calculated based on court rate</span>
                </div>
                <div>
                    <label class="block font-bold text-cyan-800 dark:text-cyan-400 mb-1">Payment Status</label>
                    <select name="payment_status" class="w-full px-3.5 py-2 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-bold focus:outline-none focus:border-cyan-500">
                        <option value="paid">Paid Offline (Cash / Outside Deal)</option>
                        <option value="unpaid">Unpaid / Pay on Arrival</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-theme-body mb-1">Rental Notes & Agreement Details</label>
                <textarea name="notes" rows="2" placeholder="e.g. Whole-court rental negotiated via phone call; tournament reserved until 5 PM."
                    class="w-full px-3.5 py-2 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-stone-200 dark:border-stone-800">
                <button type="button" onclick="closeManualReserveModal()" class="px-4 py-2.5 rounded-xl bg-stone-200 dark:bg-stone-800 text-xs text-theme-body hover:bg-stone-300 dark:hover:bg-stone-700 cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs shadow-lg shadow-amber-500/20 cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-lock"></i> Confirm & Block Court
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openManualReserveModal() {
        const modal = document.getElementById('manualReserveModal');
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100', 'pointer-events-auto');
        onManualCourtOrDateChange();
    }

    function closeManualReserveModal() {
        const modal = document.getElementById('manualReserveModal');
        modal.classList.add('opacity-0', 'pointer-events-none');
        modal.classList.remove('opacity-100', 'pointer-events-auto');
    }

    function toggleManualSlotMode() {
        const isCustom = document.getElementById('modeCustom').checked;
        const container = document.getElementById('customSlotsContainer');
        if (isCustom) {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
        recalcManualPrice();
    }

    function onManualCourtOrDateChange() {
        const select = document.getElementById('manualCourtSelect');
        const opt = select.options[select.selectedIndex];
        if (!opt) return;

        const startHour = parseInt(opt.dataset.start) || 6;
        const endHour = parseInt(opt.dataset.end) || 24;
        const rate = parseFloat(opt.dataset.rate) || 150;
        const hoursLabel = opt.dataset.hours || '6:00 AM - 12:00 AM';
        const maxPlayers = opt.dataset.max || 4;

        document.getElementById('allDayHoursBadge').textContent = `${hoursLabel} (${Math.max(0, endHour - startHour)} hrs)`;
        document.getElementById('manualPlayersCount').value = maxPlayers;

        // Render slots checkboxes
        const grid = document.getElementById('manualSlotsGrid');
        grid.innerHTML = '';

        for (let h = startHour; h < endHour; h++) {
            const timeKey = String(h).padStart(2, '0') + ':00';
            const nextH = h + 1;
            const formatTime = (hour) => {
                if (hour === 0 || hour === 24) return '12:00 AM';
                if (hour === 12) return '12:00 PM';
                return (hour > 12) ? `${hour - 12}:00 PM` : `${hour}:00 AM`;
            };
            const label = `${formatTime(h)}`;

            const wrapper = document.createElement('label');
            wrapper.className = 'flex items-center gap-1.5 p-1.5 rounded-lg bg-white dark:bg-stone-800 border border-stone-300 dark:border-stone-700 hover:border-cyan-500 cursor-pointer text-[10px] select-none';
            wrapper.innerHTML = `
                <input type="checkbox" name="slots[]" value="${timeKey}" class="slot-checkbox text-cyan-500 rounded" onchange="recalcManualPrice()">
                <span class="font-medium text-theme-heading">${label}</span>
            `;
            grid.appendChild(wrapper);
        }

        recalcManualPrice();
    }

    function selectAllCustomSlots(check) {
        document.querySelectorAll('.slot-checkbox').forEach(cb => cb.checked = check);
        recalcManualPrice();
    }

    function recalcManualPrice() {
        const select = document.getElementById('manualCourtSelect');
        const opt = select.options[select.selectedIndex];
        if (!opt) return;

        const rate = parseFloat(opt.dataset.rate) || 150;
        const startHour = parseInt(opt.dataset.start) || 6;
        const endHour = parseInt(opt.dataset.end) || 24;
        const isCustom = document.getElementById('modeCustom').checked;

        let hours = 0;
        if (isCustom) {
            hours = document.querySelectorAll('.slot-checkbox:checked').length;
        } else {
            hours = Math.max(0, endHour - startHour);
        }

        const total = hours * rate;
        document.getElementById('manualTotalAmount').value = total.toFixed(2);
        document.getElementById('priceCalculationHint').textContent = `${hours} hrs × ₱${rate.toFixed(2)}/hr = ₱${total.toFixed(2)} (editable)`;
    }

    // SweetAlert2 Cancel Reservation handler (Req #3)
    function openCancelBookingModal(id, reference, customerName, courtName) {
        Swal.fire({
            title: `Cancel Reservation ${reference}?`,
            html: `
                <div class="text-left space-y-2 text-xs text-stone-600 dark:text-stone-300">
                    <p>Are you sure you want to cancel the reservation for <strong class="text-stone-900 dark:text-white">${customerName}</strong> on <strong class="text-stone-900 dark:text-white">${courtName}</strong>?</p>
                    <p class="text-rose-500 font-semibold">All reserved timeslots will immediately be released and become available for other players on the website.</p>
                </div>
            `,
            input: 'textarea',
            inputPlaceholder: 'Enter cancellation reason (optional, e.g. Customer requested cancellation / Maintenance)...',
            inputAttributes: {
                'aria-label': 'Cancellation reason'
            },
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="fa-solid fa-ban"></i> Yes, Cancel Reservation',
            cancelButtonText: 'Keep Reservation',
            customClass: {
                popup: 'rounded-3xl',
                confirmButton: 'rounded-xl font-bold text-xs px-4 py-2.5',
                cancelButton: 'rounded-xl font-bold text-xs px-4 py-2.5'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('cancelBookingForm');
                form.action = `{{ url('owner/bookings') }}/${id}/cancel`;
                document.getElementById('cancelBookingReason').value = result.value || 'Cancelled by Court Owner';
                form.submit();
            }
        });
    }

    // Auto-open manual modal if validation errors exist
    @if($errors->any() && old('slot_mode'))
        document.addEventListener('DOMContentLoaded', () => {
            openManualReserveModal();
        });
    @endif
</script>
@endpush
@endsection

