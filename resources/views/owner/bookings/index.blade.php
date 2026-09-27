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
        <div class="text-xs text-theme-muted font-mono">
            Total Records: {{ $bookings->total() }}
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
@endsection
