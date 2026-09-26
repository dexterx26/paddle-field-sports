@extends('layouts.owner')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-stone-200 dark:border-stone-800 gap-4">
        <div>
            <h2 class="text-xl font-bold text-theme-heading">All Reservations Ledger</h2>
            <p class="text-xs text-theme-muted">Comprehensive log of all court reservations, customer records, and payment states.</p>
        </div>
        <div class="text-xs text-theme-muted font-mono">
            Total Records: {{ $bookings->total() }}
        </div>
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
                    <option value="">All Statuses</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="pending_approval" {{ request('status') === 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                    <option value="held" {{ request('status') === 'held' ? 'selected' : '' }}>Held (2-Min Hold)</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
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
                                @elseif($booking->booking_status === 'rejected')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-700 dark:text-rose-300 border border-rose-500/30 uppercase">Rejected</span>
                                @elseif($booking->booking_status === 'expired')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-stone-200 dark:bg-stone-800 text-theme-muted uppercase">Expired</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('booking.track', $booking->booking_reference) }}" target="_blank"
                                    class="px-2.5 py-1.5 rounded-lg bg-stone-200 dark:bg-stone-800 hover:bg-cyan-500 hover:text-slate-950 text-theme-body font-semibold transition-colors">
                                    View Pass &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-theme-muted">
                                No reservations match your filter criteria.
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
@endsection
