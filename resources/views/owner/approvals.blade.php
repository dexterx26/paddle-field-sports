@extends('layouts.owner')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-stone-200 dark:border-stone-800 gap-3">
        <div>
            <h2 class="text-xl font-bold text-theme-heading">Verification & Approvals Queue</h2>
            <p class="text-xs text-theme-muted">Review manual payment proof screenshots for Court Reservations and Player Credit Top-Ups.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500/15 text-amber-800 dark:text-amber-400 border border-amber-500/30">
                {{ $pendingBookings->total() + $pendingTopups->total() }} Total Pending
            </span>
        </div>
    </div>

    <!-- TABS NAVIGATION -->
    <div class="flex items-center gap-3 border-b border-stone-200 dark:border-stone-800 pb-2">
        <button type="button" onclick="switchApprovalsTab('bookings')" id="tabBtnBookings"
            class="px-4 py-2.5 rounded-2xl text-xs font-black flex items-center gap-2 transition-all cursor-pointer bg-cyan-500 text-slate-950 shadow-md shadow-cyan-500/20">
            <i class="fa-solid fa-receipt"></i>
            <span>Court Reservations</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-950 text-white font-bold ml-1">
                {{ $pendingBookings->total() }}
            </span>
        </button>

        <button type="button" onclick="switchApprovalsTab('topups')" id="tabBtnTopups"
            class="px-4 py-2.5 rounded-2xl text-xs font-bold flex items-center gap-2 transition-all cursor-pointer bg-stone-200 dark:bg-stone-800 text-theme-body hover:bg-stone-300 dark:hover:bg-stone-700">
            <i class="fa-solid fa-coins text-amber-500"></i>
            <span>Credit Top-Up Requests</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] bg-amber-500/20 text-amber-800 dark:text-amber-300 font-bold ml-1">
                {{ $pendingTopups->total() }}
            </span>
        </button>
    </div>

    <!-- TAB 1: COURT RESERVATIONS QUEUE -->
    <div id="tabContentBookings" class="space-y-4">
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
                                                class="px-3.5 py-1.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold shadow-md shadow-cyan-500/20 flex items-center gap-1.5 transition-all cursor-pointer">
                                                <i class="fa-solid fa-check"></i> Approve
                                            </button>
                                        </form>

                                        <!-- Reject Trigger Button -->
                                        <button type="button"
                                            onclick="openRejectModal({{ $booking->id }}, '{{ $booking->booking_reference }}')"
                                            class="px-3 py-1.5 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-rose-500 hover:text-white text-rose-500 font-bold border border-rose-500/30 flex items-center gap-1.5 transition-colors cursor-pointer">
                                            <i class="fa-solid fa-xmark"></i> Reject
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-theme-muted">
                                    <i class="fa-solid fa-circle-check text-cyan-500 text-3xl mb-2"></i>
                                    <h4 class="text-sm font-bold text-theme-heading">No Pending Booking Approvals</h4>
                                    <p class="text-xs text-theme-muted mt-1">There are no client payment receipts waiting for reservation verification.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card Layout -->
            <div class="block md:hidden divide-y divide-stone-200 dark:divide-stone-800">
                @forelse($pendingBookings as $booking)
                    <div class="p-4 space-y-3.5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <span class="font-mono font-bold text-cyan-600 dark:text-cyan-400 text-sm">{{ $booking->booking_reference }}</span>
                                <div class="text-[10px] text-theme-muted">{{ $booking->created_at->diffForHumans() }}</div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-500/30">
                                Pending Approval
                            </span>
                        </div>

                        <div class="flex gap-3">
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
                                    class="shrink-0 w-20 h-24 rounded-2xl overflow-hidden border border-stone-300 dark:border-stone-700 relative group shadow-md cursor-pointer">
                                    <img src="{{ $booking->receipt_url }}" alt="Receipt" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-slate-950/40 flex items-center justify-center">
                                        <i class="fa-solid fa-magnifying-glass-plus text-white text-xs"></i>
                                    </div>
                                </button>
                            @endif

                            <div class="flex-1 space-y-1 text-xs">
                                <div class="font-extrabold text-theme-heading text-sm">{{ $booking->customer_name }}</div>
                                <div class="text-theme-muted font-mono">{{ $booking->customer_phone }}</div>
                                <div class="text-theme-heading font-semibold mt-1">
                                    <span class="text-cyan-600 dark:text-cyan-400">{{ $booking->court->name }}</span>
                                    • {{ $booking->booking_date->format('M d') }}
                                </div>
                                <div class="text-theme-muted text-[11px]">
                                    {{ date('g:i A', strtotime($booking->start_time)) }} - {{ date('g:i A', strtotime($booking->end_time)) }}
                                </div>
                                <div class="font-black text-theme-heading text-sm text-cyan-600 pt-1">
                                    {{ $booking->formatted_amount }}
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-stone-200/60 dark:border-stone-800/60">
                            <button type="button"
                                onclick="openRejectModal({{ $booking->id }}, '{{ $booking->booking_reference }}')"
                                class="w-full py-2.5 px-3 rounded-xl bg-stone-200 dark:bg-stone-800 text-rose-500 font-bold text-xs border border-rose-500/30 flex items-center justify-center gap-1.5 transition-colors">
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
                    {{ $pendingBookings->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- TAB 2: CREDIT TOP-UP REQUESTS QUEUE -->
    <div id="tabContentTopups" class="hidden space-y-4">
        <div class="rounded-3xl glass-panel border border-stone-200 dark:border-stone-800 overflow-hidden shadow-xl">
            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-stone-100 dark:bg-stone-900/80 text-theme-muted border-b border-stone-200 dark:border-stone-800 uppercase text-[10px] tracking-wider">
                            <th class="py-4 px-4">Receipt Proof</th>
                            <th class="py-4 px-4">Reference</th>
                            <th class="py-4 px-4">Player Details</th>
                            <th class="py-4 px-4">Credits Requested</th>
                            <th class="py-4 px-4">Payment Amount</th>
                            <th class="py-4 px-4">Conversion Rate</th>
                            <th class="py-4 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-200 dark:divide-stone-800">
                        @forelse($pendingTopups as $topup)
                            <tr class="hover:bg-stone-200/50 dark:hover:bg-stone-800/40 transition-colors">
                                <!-- Receipt Thumbnail -->
                                <td class="py-4 px-4">
                                    @if($topup->receipt_url)
                                        <button type="button"
                                            data-url="{{ $topup->receipt_url }}"
                                            data-id="{{ $topup->id }}"
                                            data-reference="{{ $topup->reference }}"
                                            data-customer="{{ $topup->user->name ?? 'Player' }}"
                                            data-court="Credit Top-Up Wallet"
                                            data-schedule="Top-Up Request ({{ $topup->formatted_credits }})"
                                            data-amount="{{ $topup->formatted_amount_php }}"
                                            onclick="handleReceiptPreviewClick(this)"
                                            class="block w-14 h-16 rounded-xl overflow-hidden border border-amber-500/40 hover:border-amber-500 group relative shadow-md cursor-pointer">
                                            <img src="{{ $topup->receipt_url }}" alt="Proof" class="w-full h-full object-cover">
                                            <div class="absolute inset-0 bg-slate-950/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                <i class="fa-solid fa-magnifying-glass-plus text-white text-xs"></i>
                                            </div>
                                        </button>
                                    @else
                                        <span class="text-theme-muted italic">No receipt</span>
                                    @endif
                                </td>

                                <td class="py-4 px-4">
                                    <span class="font-mono font-bold text-amber-600 dark:text-amber-400 text-sm">{{ $topup->reference }}</span>
                                    <div class="text-[10px] text-theme-muted mt-0.5">{{ $topup->created_at->diffForHumans() }}</div>
                                    @if($topup->customer_notes)
                                        <div class="text-[10px] text-theme-muted italic truncate max-w-xs mt-0.5">Note: {{ $topup->customer_notes }}</div>
                                    @endif
                                </td>

                                <td class="py-4 px-4">
                                    <div class="font-bold text-theme-heading text-sm">{{ $topup->user->name ?? 'Player' }}</div>
                                    <div class="font-mono text-theme-muted">{{ $topup->user->phone ?? 'No phone' }}</div>
                                    <div class="text-[11px] text-theme-muted">{{ $topup->user->email ?? 'No email' }}</div>
                                    <div class="mt-1">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-stone-200 dark:bg-stone-800 text-theme-muted">
                                            Current Balance: {{ number_format($topup->user->credits ?? 0, 1) }} Credits
                                        </span>
                                    </div>
                                </td>

                                <td class="py-4 px-4">
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-amber-500/15 text-amber-800 dark:text-amber-300 border border-amber-500/30 font-black text-sm">
                                        <i class="fa-solid fa-coins text-amber-500"></i>
                                        <span>+{{ $topup->formatted_credits }}</span>
                                    </div>
                                </td>

                                <td class="py-4 px-4">
                                    <div class="font-black text-theme-heading text-base">{{ $topup->formatted_amount_php }}</div>
                                    <span class="text-[10px] uppercase font-bold text-cyan-700 dark:text-cyan-400 bg-cyan-500/10 px-2 py-0.5 rounded-md">
                                        {{ str_replace('_', ' ', $topup->payment_method) }}
                                    </span>
                                </td>

                                <td class="py-4 px-4">
                                    <div class="text-theme-muted font-medium">₱{{ number_format($topup->conversion_rate, 2) }}</div>
                                    <div class="text-[10px] text-theme-muted">per 1 Credit</div>
                                </td>

                                <td class="py-4 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Approve Topup Form -->
                                        <form id="approve-topup-form-{{ $topup->id }}" method="POST" action="{{ route('owner.approvals.credits.approve', $topup->id) }}">
                                            @csrf
                                            <button type="button"
                                                data-id="{{ $topup->id }}"
                                                data-ref="{{ $topup->reference }}"
                                                data-user="{{ $topup->user->name ?? 'Player' }}"
                                                data-credits="{{ $topup->formatted_credits }}"
                                                data-amount="{{ $topup->formatted_amount_php }}"
                                                onclick="handleApproveTopupClick(this)"
                                                class="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black shadow-md shadow-amber-500/20 flex items-center gap-1.5 transition-all cursor-pointer">
                                                <i class="fa-solid fa-check"></i> Approve Top-Up
                                            </button>
                                        </form>

                                        <!-- Reject Trigger Button -->
                                        <button type="button"
                                            onclick="openRejectTopupModal({{ $topup->id }}, '{{ $topup->reference }}', '{{ $topup->user->name ?? 'Player' }}', '{{ $topup->formatted_credits }}')"
                                            class="px-3 py-1.5 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-rose-500 hover:text-white text-rose-500 font-bold border border-rose-500/30 flex items-center gap-1.5 transition-colors cursor-pointer">
                                            <i class="fa-solid fa-xmark"></i> Reject
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-theme-muted">
                                    <i class="fa-solid fa-coins text-amber-500 text-3xl mb-2"></i>
                                    <h4 class="text-sm font-bold text-theme-heading">No Pending Credit Top-Up Requests</h4>
                                    <p class="text-xs text-theme-muted mt-1">There are no player credit top-up requests waiting for confirmation.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card Layout -->
            <div class="block md:hidden divide-y divide-stone-200 dark:divide-stone-800">
                @forelse($pendingTopups as $topup)
                    <div class="p-4 space-y-3.5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <span class="font-mono font-bold text-amber-600 dark:text-amber-400 text-sm">{{ $topup->reference }}</span>
                                <div class="text-[10px] text-theme-muted">{{ $topup->created_at->diffForHumans() }}</div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-500/30">
                                Pending Credit Check
                            </span>
                        </div>

                        <div class="flex gap-3">
                            @if($topup->receipt_url)
                                <button type="button"
                                    data-url="{{ $topup->receipt_url }}"
                                    data-id="{{ $topup->id }}"
                                    data-reference="{{ $topup->reference }}"
                                    data-customer="{{ $topup->user->name ?? 'Player' }}"
                                    data-court="Credit Top-Up Wallet"
                                    data-schedule="Top-Up Request ({{ $topup->formatted_credits }})"
                                    data-amount="{{ $topup->formatted_amount_php }}"
                                    onclick="handleReceiptPreviewClick(this)"
                                    class="shrink-0 w-20 h-24 rounded-2xl overflow-hidden border border-amber-500/40 relative group shadow-md cursor-pointer">
                                    <img src="{{ $topup->receipt_url }}" alt="Proof" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-slate-950/40 flex items-center justify-center">
                                        <i class="fa-solid fa-magnifying-glass-plus text-white text-xs"></i>
                                    </div>
                                </button>
                            @endif

                            <div class="flex-1 space-y-1 text-xs">
                                <div class="font-extrabold text-theme-heading text-sm">{{ $topup->user->name ?? 'Player' }}</div>
                                <div class="text-theme-muted font-mono">{{ $topup->user->phone ?? 'No phone' }}</div>
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg bg-amber-500/15 text-amber-800 dark:text-amber-300 font-bold text-xs mt-1">
                                    <i class="fa-solid fa-coins text-amber-500"></i>
                                    <span>+{{ $topup->formatted_credits }}</span>
                                </div>
                                <div class="font-black text-theme-heading text-sm text-cyan-600 pt-1">
                                    {{ $topup->formatted_amount_php }}
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-stone-200/60 dark:border-stone-800/60">
                            <button type="button"
                                onclick="openRejectTopupModal({{ $topup->id }}, '{{ $topup->reference }}', '{{ $topup->user->name ?? 'Player' }}', '{{ $topup->formatted_credits }}')"
                                class="w-full py-2.5 px-3 rounded-xl bg-stone-200 dark:bg-stone-800 text-rose-500 font-bold text-xs border border-rose-500/30 flex items-center justify-center gap-1.5 transition-colors">
                                <i class="fa-solid fa-xmark"></i> Reject
                            </button>

                            <form id="approve-topup-form-mobile-{{ $topup->id }}" method="POST" action="{{ route('owner.approvals.credits.approve', $topup->id) }}">
                                @csrf
                                <button type="button"
                                    data-id="{{ $topup->id }}"
                                    data-ref="{{ $topup->reference }}"
                                    data-user="{{ $topup->user->name ?? 'Player' }}"
                                    data-credits="{{ $topup->formatted_credits }}"
                                    data-amount="{{ $topup->formatted_amount_php }}"
                                    onclick="handleApproveTopupClick(this)"
                                    class="w-full py-2.5 px-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs shadow-md shadow-amber-500/20 flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                                    <i class="fa-solid fa-check"></i> Approve Top-Up
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="py-12 px-4 text-center text-theme-muted">
                        <i class="fa-solid fa-coins text-amber-500 text-3xl mb-2"></i>
                        <h4 class="text-sm font-bold text-theme-heading">No Pending Credit Top-Up Requests</h4>
                        <p class="text-xs text-theme-muted mt-1">There are no player credit top-up requests waiting for confirmation.</p>
                    </div>
                @endforelse
            </div>

            @if($pendingTopups->hasPages())
                <div class="p-4 border-t border-stone-200 dark:border-stone-800">
                    {{ $pendingTopups->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
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
            </div>
        </div>
    </div>
</div>

<!-- Booking Reject Modal Component -->
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

<!-- Credit Top-Up Reject Modal Component -->
<div id="rejectTopupModal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/85 backdrop-blur-md opacity-0 pointer-events-none transition-all duration-300">
    <div class="glass-dropdown p-5 sm:p-6 rounded-3xl max-w-md w-full border border-rose-500/30 shadow-2xl relative space-y-4">
        <div class="flex items-start justify-between gap-3">
            <div class="w-12 h-12 rounded-2xl bg-rose-500/20 text-rose-500 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-coins"></i>
            </div>
            <button type="button" onclick="closeRejectTopupModal()" class="shrink-0 text-theme-muted hover:text-theme-heading p-2 rounded-xl hover:bg-stone-200/50 dark:hover:bg-stone-800/50 transition-colors cursor-pointer -mt-1 -mr-1" title="Close modal">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div>
            <h3 class="text-base font-bold text-theme-heading">Decline Credit Top-Up Request</h3>
            <p class="text-xs text-theme-muted mt-1" id="rejectTopupModalMeta">Top-Up Ref</p>
        </div>

        <form id="rejectTopupForm" method="POST" action="" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-theme-body mb-1.5">Reason for Rejection</label>
                <textarea name="reason" rows="3" required placeholder="e.g. Transfer receipt is not visible on account or reference number did not match."
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 text-xs focus:outline-none focus:border-rose-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3">
                <button type="button" onclick="closeRejectTopupModal()" class="px-4 py-2 rounded-xl bg-stone-200 dark:bg-stone-800 text-xs text-theme-body hover:bg-stone-300 dark:hover:bg-stone-700 cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-rose-500 hover:bg-rose-400 text-white text-xs font-bold shadow-md shadow-rose-500/20 cursor-pointer">
                    Confirm Rejection
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function escapeHtml(string) {
        if (!string) return '';
        const entityMap = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
        return String(string).replace(/[&<>"']/g, s => entityMap[s]);
    }

    // Tab Switching Logic
    function switchApprovalsTab(tabName) {
        const btnBookings = document.getElementById('tabBtnBookings');
        const btnTopups = document.getElementById('tabBtnTopups');
        const contentBookings = document.getElementById('tabContentBookings');
        const contentTopups = document.getElementById('tabContentTopups');

        if (tabName === 'topups') {
            btnBookings.className = 'px-4 py-2.5 rounded-2xl text-xs font-bold flex items-center gap-2 transition-all cursor-pointer bg-stone-200 dark:bg-stone-800 text-theme-body hover:bg-stone-300 dark:hover:bg-stone-700';
            btnTopups.className = 'px-4 py-2.5 rounded-2xl text-xs font-black flex items-center gap-2 transition-all cursor-pointer bg-amber-500 text-slate-950 shadow-md shadow-amber-500/20';

            contentBookings.classList.add('hidden');
            contentTopups.classList.remove('hidden');
            window.location.hash = 'topups';
        } else {
            btnTopups.className = 'px-4 py-2.5 rounded-2xl text-xs font-bold flex items-center gap-2 transition-all cursor-pointer bg-stone-200 dark:bg-stone-800 text-theme-body hover:bg-stone-300 dark:hover:bg-stone-700';
            btnBookings.className = 'px-4 py-2.5 rounded-2xl text-xs font-black flex items-center gap-2 transition-all cursor-pointer bg-cyan-500 text-slate-950 shadow-md shadow-cyan-500/20';

            contentTopups.classList.add('hidden');
            contentBookings.classList.remove('hidden');
            window.location.hash = 'bookings';
        }
    }

    // Auto-detect tab on page load
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        if (window.location.hash === '#topups' || urlParams.has('topups_page')) {
            switchApprovalsTab('topups');
        }
    });

    // Receipt Preview Modal
    function handleReceiptPreviewClick(button) {
        const url = button.dataset.url;
        const ref = button.dataset.reference;
        const customer = button.dataset.customer;
        const court = button.dataset.court;
        const schedule = button.dataset.schedule;
        const amount = button.dataset.amount;

        document.getElementById('rcptModalImg').src = url;
        document.getElementById('rcptModalDownload').href = url;
        document.getElementById('rcptModalTitle').textContent = `Receipt Proof: ${ref}`;
        document.getElementById('rcptModalMeta').textContent = `${customer} • ${court} • ${schedule} • ${amount}`;

        const modal = document.getElementById('receiptModal');
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100', 'pointer-events-auto');
    }

    function closeReceiptModal() {
        const modal = document.getElementById('receiptModal');
        modal.classList.add('opacity-0', 'pointer-events-none');
        modal.classList.remove('opacity-100', 'pointer-events-auto');
    }

    // Reservation Approval SweetAlert
    function handleApproveClick(button) {
        const bookingId = button.dataset.id;
        const ref = button.dataset.reference;
        const customerName = button.dataset.customer;
        const courtName = button.dataset.court;
        const schedule = button.dataset.schedule;
        const amount = button.dataset.amount;

        Swal.fire({
            title: '<span class="text-lg font-bold text-theme-heading">Approve Booking Reservation?</span>',
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
                if (form) {
                    form.submit();
                }
            }
        });
    }

    // Credit Top-Up Approval SweetAlert
    function handleApproveTopupClick(button) {
        const topupId = button.dataset.id;
        const ref = button.dataset.ref;
        const userName = button.dataset.user;
        const credits = button.dataset.credits;
        const amount = button.dataset.amount;

        Swal.fire({
            title: '<span class="text-lg font-bold text-theme-heading">Approve Credit Top-Up?</span>',
            html: `
                <div class="text-left space-y-3 text-xs text-stone-600 dark:text-stone-300">
                    <p>Confirm verified payment receipt for player <strong class="text-stone-900 dark:text-white font-bold">${escapeHtml(userName)}</strong>?</p>
                    <div class="p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/20 space-y-2 font-medium">
                        <div class="flex justify-between items-center pb-1.5 border-b border-amber-500/20">
                            <span class="text-theme-muted">Top-Up Ref:</span>
                            <span class="font-mono font-bold text-amber-600 dark:text-amber-400 text-sm">${escapeHtml(ref)}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-theme-muted">Credits to Deposit:</span>
                            <span class="font-black text-amber-600 dark:text-amber-400 text-sm flex items-center gap-1">
                                <i class="fa-solid fa-coins"></i> +${escapeHtml(credits)}
                            </span>
                        </div>
                        <div class="flex justify-between items-center pt-1.5 border-t border-amber-500/20">
                            <span class="text-theme-muted">Payment Verified:</span>
                            <span class="font-black text-theme-heading text-sm">${escapeHtml(amount)}</span>
                        </div>
                    </div>
                    <div class="p-2.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-800 dark:text-emerald-300 text-[11px] flex items-start gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 mt-0.5 shrink-0"></i>
                        <span>This will immediately add the credits to the player's wallet balance.</span>
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="fa-solid fa-check mr-1.5"></i> Yes, Approve & Credit Wallet',
            cancelButtonText: 'Cancel',
            customClass: {
                popup: 'rounded-3xl',
                confirmButton: 'rounded-xl font-bold text-xs px-4 py-2.5 cursor-pointer',
                cancelButton: 'rounded-xl font-bold text-xs px-4 py-2.5 cursor-pointer'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.showLoading();
                let form = document.getElementById('approve-topup-form-' + topupId) || document.getElementById('approve-topup-form-mobile-' + topupId);
                if (form) {
                    form.submit();
                }
            }
        });
    }

    // Reservation Reject Modal
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

    // Credit Top-Up Reject Modal
    function openRejectTopupModal(id, ref, user, credits) {
        document.getElementById('rejectTopupForm').action = "{{ url('owner/approvals/credits') }}/" + id + "/reject";
        document.getElementById('rejectTopupModalMeta').textContent = `Declining top-up ${ref} for ${user} (${credits}). No credits will be deposited.`;

        const modal = document.getElementById('rejectTopupModal');
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100', 'pointer-events-auto');
    }

    function closeRejectTopupModal() {
        const modal = document.getElementById('rejectTopupModal');
        modal.classList.add('opacity-0', 'pointer-events-none');
        modal.classList.remove('opacity-100', 'pointer-events-auto');
    }

    // Modal backdrop click and Escape key listeners
    document.getElementById('receiptModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeReceiptModal();
    });

    document.getElementById('rejectModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeRejectModal();
    });

    document.getElementById('rejectTopupModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeRejectTopupModal();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeReceiptModal();
            closeRejectModal();
            closeRejectTopupModal();
        }
    });
</script>
@endpush
