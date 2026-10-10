@extends('layouts.app')

@section('content')
<!-- Hero Section (Tan & Cyan Athletic Club Theme) -->
<section class="relative pt-12 pb-24 overflow-hidden">
    <!-- Ambient glowing gradients: Warm Haze, Crunch & Dreamland Cyan -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-96 bg-gradient-to-b from-amber-600/10 via-cyan-500/10 to-transparent blur-3xl pointer-events-none -z-10"></div>
    <div class="absolute top-40 right-10 w-80 h-80 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none -z-10"></div>
    <div class="absolute top-20 left-10 w-72 h-72 bg-amber-600/10 rounded-full blur-3xl pointer-events-none -z-10"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <!-- Hero Text Content -->
            <div class="lg:col-span-7 space-y-6">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-stone-200/80 border border-amber-600/30 text-xs font-semibold text-amber-800 shadow-inner">
                    <span class="w-2 h-2 rounded-full bg-cyan-500 animate-pulse"></span>
                    <span>Bacal 3, Talavera's Premier Pickleball Destination</span>
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-theme-heading tracking-tight leading-[1.1]">
                    Smash, Rally & Win at <br>
                    <span class="bg-gradient-to-r from-amber-600 via-cyan-600 dark:via-cyan-400 to-cyan-500 bg-clip-text text-transparent">
                        Paddle Field
                    </span>
                </h1>

                <p class="text-base sm:text-lg text-theme-body max-w-2xl font-normal leading-relaxed">
                    Experience tournament-certified cushioned acrylic courts, high-lux floodlighting, and effortless real-time booking. No mandatory registration — book as a guest in under 60 seconds!
                </p>

                <!-- Value Highlights -->
                <div class="grid grid-cols-3 gap-4 pt-2 max-w-lg">
                    <div class="p-3.5 rounded-2xl glass-card">
                        <div class="text-xl sm:text-2xl font-black text-cyan-600">{{ $courts->count() }} Courts</div>
                        <div class="text-[11px] text-theme-muted font-medium mt-0.5">All Indoor Pro</div>
                    </div>
                    <div class="p-3.5 rounded-2xl glass-card">
                        <div class="text-xl sm:text-2xl font-black text-amber-700 dark:text-amber-400">{{ max(1, (int) round(($settings->holding_duration_seconds ?: 120) / 60)) }}-Min Hold</div>
                        <div class="text-[11px] text-theme-muted font-medium mt-0.5">Real-Time Sync</div>
                    </div>
                    <div class="p-3.5 rounded-2xl glass-card">
                        <div class="text-xl sm:text-2xl font-black text-cyan-600 dark:text-cyan-400">Guest Ready</div>
                        <div class="text-[11px] text-theme-muted font-medium mt-0.5">Instant Checkout</div>
                    </div>
                </div>

                <!-- CTAs -->
                <div class="flex flex-wrap items-center gap-4 pt-4">
                    <a href="#booking-engine" class="px-7 py-3.5 rounded-2xl bg-gradient-to-r from-cyan-500 to-cyan-600 hover:from-cyan-400 hover:to-cyan-500 text-slate-950 font-black text-sm shadow-xl shadow-cyan-500/25 flex items-center gap-2.5 transition-all hover:scale-105">
                        <i class="fa-solid fa-calendar-days text-base"></i>
                        <span>Select Timeslot to Book</span>
                    </a>
                    <a href="#courts-section" class="px-6 py-3.5 rounded-2xl bg-stone-200 dark:bg-stone-800 hover:bg-stone-300 dark:hover:bg-stone-700 text-theme-heading font-bold text-sm border border-stone-300 dark:border-stone-700 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-eye text-theme-muted"></i>
                        <span>Explore Courts & Rates</span>
                    </a>
                </div>
            </div>

            <!-- Hero Feature Image / Card -->
            <div class="lg:col-span-5 relative">
                <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-stone-300 dark:border-stone-700 group">
                    <img src="{{ asset('storage/courts/court-1.jpg') }}" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1554068865-24cecd4e34b8?auto=format&fit=crop&w=1000&q=80';" alt="Paddle Field Sports Center Pickleball Court" class="w-full h-80 sm:h-96 object-cover group-hover:scale-105 transition-transform duration-700">
                    <div class="absolute inset-0 bg-gradient-to-t from-stone-950 via-stone-950/20 to-transparent"></div>

                    <!-- Overlay Info Card -->
                    <div class="absolute bottom-5 left-5 right-5 p-4 rounded-2xl glass-dropdown border border-stone-300 dark:border-stone-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-cyan-500/20 text-cyan-700 dark:text-cyan-300 border border-cyan-500/30 uppercase">Championship Center</span>
                                <h4 class="text-sm font-bold text-theme-heading mt-1">Court 1 - Tournament Grade</h4>
                                <p class="text-xs text-theme-muted">Pro Cushion Acrylic • 4 Players Max</p>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-theme-muted block">From</span>
                                <span class="text-lg font-black text-cyan-600">₱150<span class="text-xs text-theme-muted font-normal">/hr</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- REAL-TIME INTERACTIVE BOOKING ENGINE -->
<section id="booking-engine" class="py-16 bg-stone-100/70 dark:bg-stone-900/40 border-y border-stone-200 dark:border-stone-800 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/10 text-cyan-700 dark:text-cyan-300 border border-cyan-500/20 text-xs font-bold uppercase tracking-wider mb-2">
                    <i class="fa-solid fa-clock"></i> Drag or Click to Add / Remove Timeslots
                </div>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-theme-heading tracking-tight">Court Reservation Schedule</h2>
                <p class="text-sm text-theme-body mt-1 max-w-xl">
                    Click or drag across slots to select your booking block. Click or drag over selected slots to remove them (clicking a middle time trims the latest slot!).
                </p>
            </div>

            <!-- Date Selector Controls -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2 bg-stone-200 dark:bg-stone-900 border border-stone-300 dark:border-stone-700 p-1.5 rounded-2xl">
                    <button type="button" id="prevDayBtn" class="w-9 h-9 rounded-xl hover:bg-stone-300 dark:hover:bg-stone-800 text-theme-heading flex items-center justify-center transition-colors">
                        <i class="fa-solid fa-chevron-left text-xs"></i>
                    </button>
                    <div class="px-3 text-center">
                        <input type="date" id="selectedDateInput" value="{{ $selectedDate }}" min="{{ date('Y-m-d') }}"
                            class="bg-transparent text-theme-heading font-bold text-sm text-center focus:outline-none cursor-pointer">
                    </div>
                    <button type="button" id="nextDayBtn" class="w-9 h-9 rounded-xl hover:bg-stone-300 dark:hover:bg-stone-800 text-theme-heading flex items-center justify-center transition-colors">
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                    </button>
                </div>

                <!-- Quick Date Pills & Calendar View Option -->
                <div class="flex items-center gap-1.5">
                    <button type="button" class="quick-date-btn px-3 py-2 rounded-xl text-xs font-bold transition-all {{ $selectedDate === date('Y-m-d') ? 'bg-cyan-500 text-slate-950 shadow-md' : 'bg-stone-200 dark:bg-stone-800 text-theme-heading hover:bg-stone-300 dark:hover:bg-stone-700' }}" data-date="{{ date('Y-m-d') }}">
                        Today
                    </button>
                    <button type="button" class="quick-date-btn px-3 py-2 rounded-xl text-xs font-bold transition-all {{ $selectedDate === date('Y-m-d', strtotime('+1 day')) ? 'bg-cyan-500 text-slate-950 shadow-md' : 'bg-stone-200 dark:bg-stone-800 text-theme-heading hover:bg-stone-300 dark:hover:bg-stone-700' }}" data-date="{{ date('Y-m-d', strtotime('+1 day')) }}">
                        Tomorrow
                    </button>
                    <button type="button" id="openCalendarModalBtn"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 bg-gradient-to-r from-cyan-500/15 via-blue-500/15 to-indigo-500/15 hover:from-cyan-500/25 hover:to-indigo-500/25 text-cyan-700 dark:text-cyan-300 border border-cyan-500/30 hover:border-cyan-500 shadow-sm cursor-pointer"
                        title="Open interactive monthly calendar">
                        <i class="fa-solid fa-calendar-days text-xs"></i>
                        <span>Calendar View</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Legend / Status Key -->
        <div class="flex flex-wrap items-center justify-between gap-4 p-4 rounded-2xl glass-card mb-8 text-xs">
            <div class="flex flex-wrap items-center gap-6">
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded-md bg-white dark:bg-stone-800 border border-stone-300 dark:border-stone-600"></span>
                    <span class="text-theme-body font-medium">Available (Click/Drag)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded-md bg-gradient-to-r from-cyan-500 to-cyan-600 shadow-sm shadow-cyan-500/50"></span>
                    <span class="text-cyan-700 dark:text-cyan-300 font-bold">Your Selection (Click/Drag to Remove)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded-md bg-amber-500/20 border border-amber-500/50"></span>
                    <span class="text-amber-700 dark:text-amber-300 font-medium">Held ({{ max(1, (int) round(($settings->holding_duration_seconds ?: 120) / 60)) }}-Min Checkout)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded-md bg-indigo-500/20 border border-indigo-500/50"></span>
                    <span class="text-indigo-700 dark:text-indigo-300 font-medium">Pending Approval</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded-md bg-rose-500/20 border border-rose-500/50"></span>
                    <span class="text-rose-700 dark:text-rose-300 font-medium">Booked / Reserved</span>
                </div>
            </div>
            <div class="text-[11px] text-theme-muted flex items-center gap-1.5">
                <i class="fa-solid fa-rotate text-cyan-600 dark:text-cyan-400"></i> Auto-synced via Reverb
            </div>
        </div>

        <!-- Main Booking Grid Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Timeline & Court Grid (8 Columns) -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Courts Container -->
                <div id="courtsScheduleContainer" class="space-y-6">
                    @foreach($availability as $courtData)
                        @php $c = $courtData['court']; @endphp
                        <div id="booking-court-{{ $c['id'] }}" class="court-schedule-card p-5 sm:p-6 rounded-3xl glass-panel shadow-xl transition-all" data-court-id="{{ $c['id'] }}">
                            <!-- Court Header -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-stone-200">
                                <div class="flex items-center gap-4">
                                    <img src="{{ $c['display_image'] }}" alt="{{ $c['name'] }}" class="w-16 h-16 rounded-2xl object-cover border border-stone-200 dark:border-stone-800 shadow-md">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h3 class="text-base sm:text-lg font-extrabold text-theme-heading">{{ $c['name'] }}</h3>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $c['type'] === 'indoor' ? 'bg-cyan-500/20 text-cyan-700 dark:text-cyan-300 border border-cyan-500/30' : 'bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-500/30' }} uppercase">
                                                {{ ucfirst($c['type']) }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-theme-muted mt-0.5">
                                            {{ $c['surface_type'] }} • Max {{ $c['max_players'] }} Players
                                        </p>
                                    </div>
                                </div>
                                <div class="text-left sm:text-right">
                                    <div class="text-xs text-theme-muted">Hourly Rate</div>
                                    <div class="text-lg font-black text-cyan-600 dark:text-cyan-400">{{ $c['formatted_price'] }}<span class="text-xs text-theme-muted font-normal">/hr</span></div>
                                </div>
                            </div>

                            <!-- Hourly Timeslots Grid -->
                            <div class="mt-4">
                                <div class="text-xs font-semibold text-theme-muted mb-2.5 flex items-center justify-between">
                                    <span>Available 1-Hour Time Slots (Click or Drag to Add/Remove):</span>
                                    <span class="text-[11px] text-theme-muted font-bold text-amber-700">6:00 AM to 12:00 AM Midnight</span>
                                </div>
                                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2 court-slots-grid" data-court-id="{{ $c['id'] }}" data-court-name="{{ $c['name'] }}" data-rate="{{ $c['price_per_hour'] }}" data-max-players="{{ $c['max_players'] }}">
                                    @foreach($courtData['slots'] as $slotKey => $slot)
                                        @php
                                            $status = $slot['status'];
                                            $isAvailable = $status === 'available';
                                        @endphp
                                        <button type="button"
                                            class="court-slot p-2.5 rounded-xl text-center text-xs font-semibold transition-all relative
                                                {{ $isAvailable ? 'slot-available cursor-pointer' : '' }}
                                                {{ $status === 'held' ? 'slot-held cursor-pointer select-none opacity-80' : '' }}
                                                {{ $status === 'pending_approval' ? 'slot-pending cursor-not-allowed select-none opacity-80' : '' }}
                                                {{ $status === 'confirmed' ? 'slot-booked cursor-not-allowed select-none opacity-80' : '' }}"
                                            data-court-id="{{ $c['id'] }}"
                                            data-slot-time="{{ $slot['time'] }}"
                                            data-display-time="{{ $slot['display_time'] }}"
                                            data-display-range="{{ $slot['display_range'] }}"
                                            data-status="{{ $status }}"
                                            data-remaining-seconds="{{ $slot['remaining_seconds'] }}">
                                            <div class="font-bold text-xs">{{ $slot['display_time'] }}</div>
                                            <div class="text-[10px] opacity-85 mt-0.5 slot-status-label">
                                                @if($status === 'available')
                                                    <span class="text-cyan-600 dark:text-cyan-400">Open</span>
                                                @elseif($status === 'held')
                                                    <span class="text-amber-700 dark:text-amber-300 flex items-center justify-center gap-1">
                                                        <i class="fa-regular fa-hourglass-half text-[9px] animate-spin"></i>
                                                        <span class="slot-timer" data-seconds="{{ $slot['remaining_seconds'] }}">{{ floor($slot['remaining_seconds'] / 60) }}:{{ sprintf('%02d', $slot['remaining_seconds'] % 60) }}</span>
                                                    </span>
                                                @elseif($status === 'pending_approval')
                                                    <span class="text-indigo-700 dark:text-indigo-300">In Review</span>
                                                @elseif($status === 'confirmed')
                                                    <span class="text-rose-700 dark:text-rose-400">Booked</span>
                                                @endif
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Booking Summary & Guest Form Card (4 Columns Sticky) -->
            <div class="lg:col-span-4 sticky top-28">
                <div class="p-6 rounded-3xl glass-panel shadow-2xl relative overflow-hidden">
                    <!-- Top Gradient Line -->
                    <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-amber-600 via-cyan-500 to-cyan-400"></div>

                    <div class="flex items-center justify-between pb-4 border-b border-stone-200 dark:border-stone-800">
                        <h3 class="text-base font-extrabold text-theme-heading flex items-center gap-2">
                            <i class="fa-solid fa-receipt text-cyan-600 dark:text-cyan-400"></i> Reservation Summary
                        </h3>
                        <span id="paymentModeBadge" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                            @if($settings->payment_mode === 'paymongo')
                                bg-emerald-500/20 text-emerald-800 dark:text-emerald-300 border border-emerald-500/30
                            @elseif($settings->payment_mode === 'xendit')
                                bg-cyan-500/20 text-cyan-800 dark:text-cyan-300 border border-cyan-500/30
                            @else
                                bg-amber-500/20 text-amber-800 dark:text-amber-300 border border-amber-500/30
                            @endif">
                            @if($settings->payment_mode === 'paymongo')
                                <i class="fa-solid fa-bolt text-emerald-600 mr-1"></i> PayMongo Auto
                            @elseif($settings->payment_mode === 'xendit')
                                <i class="fa-solid fa-bolt text-cyan-600 mr-1"></i> Xendit Auto
                            @else
                                Manual GCash
                            @endif
                        </span>
                    </div>

                    <!-- Active Reservation Hold Quick Resume Banner -->
                    <div id="activeHoldBanner" class="hidden p-4 rounded-2xl bg-gradient-to-r from-emerald-500/15 via-teal-500/15 to-cyan-500/15 border border-emerald-500/40 space-y-3 mt-4 mb-2 shadow-lg">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-emerald-800 dark:text-emerald-300 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                                Active Reservation Hold
                            </span>
                            <span id="activeHoldBannerTimer" class="font-mono font-black text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-800 dark:text-emerald-300 border border-emerald-500/30">02:00</span>
                        </div>
                        <div class="text-xs text-theme-body flex items-center justify-between">
                            <span id="activeHoldBannerCourt" class="font-semibold">-</span>
                            <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400" id="activeHoldBannerRef">-</span>
                        </div>
                        <button type="button" onclick="openHoldCheckoutModal()" class="w-full py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-black text-xs shadow-md shadow-emerald-500/20 flex items-center justify-center gap-2 cursor-pointer transition-transform hover:scale-[1.01]">
                            <i class="fa-solid fa-credit-card"></i>
                            <span>Resume Payment & View Summary</span>
                        </button>
                    </div>

                    <!-- Placeholder state when no slot selected -->
                    <div id="noSelectionNotice" class="py-12 text-center">
                        <div class="w-14 h-14 rounded-2xl bg-stone-200 dark:bg-stone-800 text-theme-muted flex items-center justify-center text-2xl mx-auto mb-3 border border-stone-300 dark:border-stone-700">
                            <i class="fa-solid fa-arrow-pointer"></i>
                        </div>
                        <h4 class="text-sm font-bold text-theme-heading mb-1">No Timeslots Selected</h4>
                        <p class="text-xs text-theme-muted max-w-xs mx-auto">
                            Click or drag across open slots on any court above. Click again or drag to remove slots!
                        </p>
                    </div>

                    <!-- Active Selection Form (Hidden until slots selected) -->
                    <div id="activeSelectionBox" class="hidden space-y-5 pt-4">
                        <!-- Summary Details -->
                        <div class="p-4 rounded-2xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 space-y-2.5 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-theme-muted">Court:</span>
                                <span id="summaryCourtName" class="font-bold text-theme-heading">-</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-theme-muted">Date:</span>
                                <span id="summaryDate" class="font-bold text-theme-heading">-</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-theme-muted">Duration:</span>
                                <span id="summaryDuration" class="font-bold text-cyan-600 dark:text-cyan-400">-</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-theme-muted">Selected Slots:</span>
                                <span id="summarySlotsList" class="font-semibold text-theme-heading text-right">-</span>
                            </div>
                            <div class="pt-2 border-t border-stone-200 dark:border-stone-800 flex items-center justify-between text-sm">
                                <span class="font-bold text-theme-heading">Total Amount:</span>
                                <span id="summaryTotalAmount" class="font-black text-cyan-600 dark:text-cyan-400 text-lg">₱0.00</span>
                            </div>
                            <div class="text-[10px] text-theme-muted italic text-right flex items-center justify-between">
                                <span class="text-cyan-600 dark:text-cyan-400 font-semibold cursor-pointer hover:underline" onclick="removeLatestSlotAction()">
                                    <i class="fa-solid fa-minus text-[9px]"></i> Trim latest slot
                                </span>
                                <span>Rate: <strong id="summaryRateSnapshot">₱0</strong>/hr locked</span>
                            </div>
                        </div>

                        <!-- Customer / Guest Details Form (Req #2, #6) -->
                        <div class="space-y-3.5">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-theme-heading uppercase tracking-wider">Player / Guest Info</label>
                                @guest
                                    <span class="text-[10px] text-cyan-700 dark:text-cyan-300 font-semibold bg-cyan-500/10 px-2 py-0.5 rounded-full border border-cyan-500/20">Guest Booking Ready</span>
                                @endguest
                            </div>

                            <div>
                                <label class="block text-[11px] font-medium text-theme-body mb-1">Your Full Name <span class="text-rose-500">*</span></label>
                                <input type="text" id="custName" required placeholder="e.g. Juan dela Cruz"
                                    value="{{ Auth::check() ? Auth::user()->name : '' }}"
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 text-xs focus:outline-none focus:border-cyan-500 font-medium">
                            </div>

                            <div>
                                <label class="block text-[11px] font-medium text-theme-body mb-1">Mobile Phone Number <span class="text-rose-500">*</span></label>
                                <input type="tel" id="custPhone" required placeholder="e.g. 0917-123-4567"
                                    value="{{ Auth::check() ? Auth::user()->phone : '' }}"
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 text-xs focus:outline-none focus:border-cyan-500 font-medium">
                            </div>

                            <div>
                                <label class="block text-[11px] font-medium text-theme-body mb-1">Email Address <span class="text-theme-muted">(Optional)</span></label>
                                <input type="email" id="custEmail" placeholder="juan@example.com"
                                    value="{{ Auth::check() ? Auth::user()->email : '' }}"
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 text-xs focus:outline-none focus:border-cyan-500 font-medium">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-medium text-theme-body mb-1">Player Count</label>
                                    <select id="custPlayersCount" class="w-full px-3 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading text-xs focus:outline-none focus:border-cyan-500 font-medium">
                                        <option value="2">2 Players (Singles)</option>
                                        <option value="4" selected>4 Players (Doubles)</option>
                                        <option value="6">6 Players (Group)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-medium text-theme-body mb-1">Match Type</label>
                                    <select id="custMatchType" class="w-full px-3 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading text-xs focus:outline-none focus:border-cyan-500 font-medium">
                                        <option value="Recreational">Recreational</option>
                                        <option value="Competitive Drill">Competitive</option>
                                        <option value="Coaching">Coaching</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-medium text-theme-body mb-1">Special Requests / Notes</label>
                                <textarea id="custNotes" rows="2" placeholder="e.g. need 2 paddle rentals, coaching cones"
                                    class="w-full px-3.5 py-2 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 text-xs focus:outline-none focus:border-cyan-500 font-medium"></textarea>
                            </div>
                        </div>

                        <!-- Payment Flow Sections Based on Owner Setting (Manual GCash, Xendit, or PayMongo) -->
                        @php
                            $holdSecs = (int) ($settings->holding_duration_seconds ?: 120);
                            $holdMins = max(1, (int) round($holdSecs / 60));
                        @endphp
                        @if($settings->payment_mode === 'paymongo')
                            <!-- PAYMONGO FLOW: Instant Hold & Pay (GCash, Maya, Cards, QRPH) -->
                            <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-emerald-800 dark:text-emerald-300 text-xs font-bold">
                                        <i class="fa-solid fa-bolt text-emerald-600 text-sm"></i>
                                        <span>{{ $holdMins }}-Minute Real-Time Slot Hold</span>
                                    </div>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-700 font-extrabold uppercase">PayMongo</span>
                                </div>
                                <p class="text-[11px] text-theme-body leading-relaxed">
                                    Click below to lock these slots for <strong>{{ $holdSecs }} seconds</strong> while you complete checkout via PayMongo (GCash, Maya, QRPH, Card). Once paid, confirmation is 100% instantaneous!
                                </p>
                                <button type="button" id="startHoldAndPayBtn" data-gateway="paymongo"
                                    class="w-full py-3.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white font-black text-sm shadow-xl shadow-emerald-500/20 flex items-center justify-center gap-2 transition-all hover:scale-[1.02] cursor-pointer">
                                    <i class="fa-solid fa-lock"></i>
                                    <span>Hold {{ $holdMins }} Mins & Pay with PayMongo</span>
                                </button>
                            </div>
                        @elseif($settings->payment_mode === 'xendit')
                            <!-- XENDIT FLOW: Instant Hold & Pay -->
                            <div class="p-4 rounded-2xl bg-cyan-500/10 border border-cyan-500/30 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-cyan-700 dark:text-cyan-300 text-xs font-bold">
                                        <i class="fa-solid fa-stopwatch text-sm"></i>
                                        <span>{{ $holdMins }}-Minute Real-Time Slot Hold</span>
                                    </div>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-cyan-500/20 text-cyan-700 font-extrabold uppercase">Xendit</span>
                                </div>
                                <p class="text-[11px] text-theme-body leading-relaxed">
                                    Click below to hold this slot for <strong>{{ $holdSecs }} seconds</strong> while you complete payment via Xendit (GCash, Maya, QRPH, Card). Once paid, confirmation is 100% instantaneous!
                                </p>
                                <button type="button" id="startHoldAndPayBtn" data-gateway="xendit"
                                    class="w-full py-3.5 rounded-xl bg-gradient-to-r from-cyan-500 to-cyan-600 hover:from-cyan-400 hover:to-cyan-500 text-slate-950 font-black text-sm shadow-xl shadow-cyan-500/20 flex items-center justify-center gap-2 transition-all hover:scale-[1.02] cursor-pointer">
                                    <i class="fa-solid fa-lock"></i>
                                    <span>Hold {{ $holdMins }} Mins & Pay with Xendit</span>
                                </button>
                            </div>
                        @else
                            <!-- MANUAL RECEIPT FLOW: GCash QR & Upload Proof -->
                            <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 space-y-3.5">
                                <div class="flex items-center justify-between text-amber-800 dark:text-amber-400 text-xs font-bold">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-qrcode text-base"></i>
                                        <span>Manual Payment & Receipt Upload</span>
                                    </div>
                                    <span class="text-[10px] text-amber-700 dark:text-amber-300 bg-amber-500/20 px-2 py-0.5 rounded-full">Owner Approves</span>
                                </div>

                                <!-- Bank / GCash Details -->
                                <div class="p-3 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-800 text-xs space-y-1.5">
                                    <div class="flex justify-between">
                                        <span class="text-theme-muted">Account Name:</span>
                                        <span class="font-bold text-theme-heading">{{ $settings->manual_account_name }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-theme-muted">Account / GCash:</span>
                                        <span class="font-mono font-bold text-cyan-600 dark:text-cyan-400">{{ $settings->manual_account_number }}</span>
                                    </div>
                                    @if($settings->manual_payment_qr)
                                        <div class="pt-2 text-center">
                                            <a href="{{ asset('storage/' . $settings->manual_payment_qr) }}" target="_blank" class="inline-flex items-center gap-1.5 text-[11px] text-cyan-600 dark:text-cyan-400 hover:underline">
                                                <i class="fa-solid fa-image"></i> View GCash / Maya QR Standee
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                <!-- Form for Manual Receipt Submission -->
                                <form id="manualReceiptForm" action="{{ route('booking.manual_receipt') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                                    @csrf
                                    <input type="hidden" name="court_id" id="mrCourtId">
                                    <input type="hidden" name="date" id="mrDate">
                                    <input type="hidden" name="slots" id="mrSlots">
                                    <input type="hidden" name="customer_name" id="mrCustName">
                                    <input type="hidden" name="customer_phone" id="mrCustPhone">
                                    <input type="hidden" name="customer_email" id="mrCustEmail">
                                    <input type="hidden" name="players_count" id="mrPlayersCount">
                                    <input type="hidden" name="notes" id="mrNotes">

                                    <div>
                                        <label class="block text-[11px] font-bold text-theme-heading mb-1">
                                            Upload Payment Receipt Screenshot <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="file" name="receipt" id="receiptFileInput" required accept="image/*"
                                            class="w-full text-xs text-theme-muted file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-cyan-500 file:text-slate-950 hover:file:bg-cyan-400 cursor-pointer">
                                        <div id="receiptPreviewBox" class="mt-2 hidden">
                                            <img id="receiptPreviewImg" src="" alt="Receipt Preview" class="w-full max-h-36 object-contain rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-900 p-1">
                                        </div>
                                    </div>

                                    <button type="submit" id="submitManualBookingBtn"
                                        class="w-full py-3.5 rounded-xl bg-gradient-to-r from-amber-600 to-cyan-500 hover:from-amber-500 hover:to-cyan-400 text-slate-950 font-black text-sm shadow-xl shadow-cyan-500/25 flex items-center justify-center gap-2 transition-all hover:scale-[1.02] cursor-pointer">
                                        <i class="fa-solid fa-paper-plane"></i>
                                        <span>Submit Reservation for Approval</span>
                                    </button>
                                </form>
                            </div>
                        @endif

                        <!-- Clear selection button -->
                        <button type="button" id="clearSelectionBtn" class="w-full py-2 text-xs text-theme-muted hover:text-rose-500 transition-colors cursor-pointer">
                            <i class="fa-solid fa-rotate-left mr-1"></i> Clear All Selected Slots
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- COURTS SHOWCASE SECTION -->
<section id="courts-section" class="py-20 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
            <span class="px-3 py-1 rounded-full bg-cyan-500/10 text-cyan-700 dark:text-cyan-300 border border-cyan-500/20 text-xs font-bold uppercase tracking-wider">
                Tournament Standards
            </span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-theme-heading tracking-tight">Our Pickleball Courts</h2>
            <p class="text-sm text-theme-body">
                Engineered with high-traction cushioned acrylic surfaces to protect knees and joints while delivering true tournament ball bounce.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($courts as $court)
                <div class="glass-panel rounded-3xl overflow-hidden shadow-xl flex flex-col group hover:border-cyan-500/50 transition-all duration-300">
                    <div class="relative h-48 overflow-hidden">
                        <img src="{{ $court->display_image }}" alt="{{ $court->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-cyan-500 text-slate-950 uppercase">
                            {{ ucfirst($court->type) }}
                        </div>
                        <div class="absolute top-3 right-3 px-2.5 py-1 rounded-xl glass-dropdown text-xs font-bold text-theme-heading">
                            Court #{{ $court->court_number }}
                        </div>
                    </div>

                    <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                        <div>
                            <h3 class="text-base font-bold text-theme-heading group-hover:text-cyan-600 transition-colors">{{ $court->name }}</h3>
                            <p class="text-xs text-theme-muted mt-1 line-clamp-2">{{ $court->description }}</p>

                            <div class="mt-4 pt-3 border-t border-stone-200 space-y-1.5 text-xs text-theme-body">
                                <div class="flex items-center justify-between">
                                    <span class="text-theme-muted">Surface:</span>
                                    <span class="font-semibold text-theme-heading">{{ $court->surface_type }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-theme-muted">Capacity:</span>
                                    <span class="font-semibold text-theme-heading">{{ $court->max_players }} Players Max</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-stone-200 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] text-theme-muted block">Rate per hour</span>
                                <span class="text-lg font-black text-cyan-600">{{ $court->formatted_price }}</span>
                            </div>
                            <button type="button" onclick="scrollToCourtInEngine({{ $court->id }})" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-cyan-600 hover:from-cyan-400 hover:to-cyan-500 text-slate-950 text-xs font-black shadow-md shadow-cyan-500/20 transition-all hover:scale-105 cursor-pointer flex items-center gap-1.5">
                                <i class="fa-solid fa-calendar-check"></i>
                                <span>Select Slot</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- MAIN PAGE FACILITY PHOTO GALLERY (Req #8) -->
<section id="gallery-section" class="py-20 bg-stone-100/60 dark:bg-stone-900/50 border-t border-stone-200 dark:border-stone-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
            <div>
                <span class="px-3 py-1 rounded-full bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/20 text-xs font-bold uppercase tracking-wider mb-2 inline-block">
                    Center Atmosphere
                </span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-theme-heading tracking-tight">Facility & Clubhouse Gallery</h2>
                <p class="text-sm text-theme-body mt-1">
                    Take a look inside Paddle Field Sports Center. Updated live with photos uploaded by our owner.
                </p>
            </div>
            @auth
                @if(Auth::user()->isStaffOrAdmin())
                    <a href="{{ route('owner.photos.index') }}" class="px-4 py-2 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-stone-300 dark:hover:bg-stone-700 text-xs font-bold text-cyan-700 dark:text-cyan-400 border border-cyan-500/30 flex items-center gap-2">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span>Upload More Photos</span>
                    </a>
                @endif
            @endauth
        </div>

        <!-- Gallery Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($photos as $photo)
                <div class="glass-panel rounded-3xl overflow-hidden group shadow-xl">
                    <div class="relative h-64 overflow-hidden">
                        <img src="{{ $photo->url }}" alt="{{ $photo->title }}"
                            onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1554068865-24cecd4e34b8?auto=format&fit=crop&w=800&q=80';"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                        <div class="absolute inset-0 bg-gradient-to-t from-stone-950 via-stone-950/20 to-transparent opacity-80 group-hover:opacity-60 transition-opacity"></div>
                        <div class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-[10px] font-bold bg-stone-900/80 backdrop-blur-md text-cyan-400 border border-white/10 uppercase">
                            {{ ucfirst($photo->category) }}
                        </div>
                        <div class="absolute bottom-4 left-4 right-4">
                            <h4 class="text-sm font-bold text-cascading-white drop-shadow-md">{{ $photo->title }}</h4>
                            @if($photo->caption)
                                <p class="text-xs text-chinese-silver drop-shadow-sm mt-0.5 line-clamp-1">{{ $photo->caption }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 py-12 text-center text-theme-muted text-sm">
                    No gallery photos uploaded yet.
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- CENTER AMENITIES -->
<section class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16 space-y-2">
            <h2 class="text-3xl font-extrabold text-theme-heading">World-Class Amenities</h2>
            <p class="text-xs sm:text-sm text-theme-muted">Everything you need for an unforgettable match day experience.</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="p-5 rounded-2xl glass-card text-center space-y-2.5 hover:border-cyan-500/40 transition-all">
                <div class="w-12 h-12 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 flex items-center justify-center mx-auto text-xl">
                    <i class="fa-solid fa-lightbulb"></i>
                </div>
                <h4 class="text-xs font-bold text-theme-heading">500-Lux LED</h4>
                <p class="text-[11px] text-theme-muted">Anti-glare lights</p>
            </div>
            <div class="p-5 rounded-2xl glass-card text-center space-y-2.5 hover:border-cyan-500/40 transition-all">
                <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-700 dark:text-amber-400 flex items-center justify-center mx-auto text-xl">
                    <i class="fa-solid fa-mug-saucer"></i>
                </div>
                <h4 class="text-xs font-bold text-theme-heading">Ace Cafe Lounge</h4>
                <p class="text-[11px] text-theme-muted">Specialty coffee & bar</p>
            </div>
            <div class="p-5 rounded-2xl glass-card text-center space-y-2.5 hover:border-cyan-500/40 transition-all">
                <div class="w-12 h-12 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 flex items-center justify-center mx-auto text-xl">
                    <i class="fa-solid fa-table-tennis-paddle-ball"></i>
                </div>
                <h4 class="text-xs font-bold text-theme-heading">Paddle Rental</h4>
                <p class="text-[11px] text-theme-muted">Joola & Selkirk gear</p>
            </div>
            <div class="p-5 rounded-2xl glass-card text-center space-y-2.5 hover:border-cyan-500/40 transition-all">
                <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center mx-auto text-xl">
                    <i class="fa-solid fa-shower"></i>
                </div>
                <h4 class="text-xs font-bold text-theme-heading">Hot Showers</h4>
                <p class="text-[11px] text-theme-muted">Clean private lockers</p>
            </div>
            <div class="p-5 rounded-2xl glass-card text-center space-y-2.5 hover:border-cyan-500/40 transition-all">
                <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-700 dark:text-amber-400 flex items-center justify-center mx-auto text-xl">
                    <i class="fa-solid fa-square-parking"></i>
                </div>
                <h4 class="text-xs font-bold text-theme-heading">Free Parking</h4>
                <p class="text-[11px] text-theme-muted">24/7 guarded slots</p>
            </div>
            <div class="p-5 rounded-2xl glass-card text-center space-y-2.5 hover:border-cyan-500/40 transition-all">
                <div class="w-12 h-12 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 flex items-center justify-center mx-auto text-xl">
                    <i class="fa-solid fa-wifi"></i>
                </div>
                <h4 class="text-xs font-bold text-theme-heading">High-Speed WiFi</h4>
                <p class="text-[11px] text-theme-muted">Complimentary 1Gbps</p>
            </div>
        </div>
    </div>
</section>

<!-- 2-MINUTE REAL-TIME HOLD CHECKOUT MODAL (PayMongo & Xendit) -->
<div id="xenditHoldModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md opacity-0 pointer-events-none transition-all duration-300">
    <div class="glass-dropdown p-6 sm:p-8 rounded-3xl max-w-lg w-full border border-cyan-500/30 shadow-2xl relative">
        <button type="button" id="closeXenditModalBtn" class="absolute top-4 right-4 sm:top-5 sm:right-5 z-20 text-theme-muted hover:text-theme-heading p-2 rounded-xl hover:bg-stone-200/50 dark:hover:bg-stone-800/50 transition-colors cursor-pointer" title="Close modal">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>

        <!-- Live Holding Timer Display -->
        <div class="text-center space-y-2 mb-6">
            <div id="modalGatewayIcon" class="w-16 h-16 rounded-full bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-2xl mx-auto border border-cyan-500/40 animate-pulse">
                <i class="fa-solid fa-stopwatch"></i>
            </div>
            @php
                $holdingSecs = (int) ($settings->holding_duration_seconds ?: 120);
                $holdingMins = max(1, (int) round($holdingSecs / 60));
                $holdingDefaultDisplay = sprintf('%02d:%02d', floor($holdingSecs / 60), $holdingSecs % 60);
            @endphp
            <span id="modalGatewayBadge" class="px-3 py-1 rounded-full text-xs font-extrabold bg-cyan-500/10 text-cyan-700 dark:text-cyan-300 border border-cyan-500/30 uppercase tracking-widest">
                {{ $holdingMins }}-Minute Slot Hold Active
            </span>
            <div class="text-4xl sm:text-5xl font-black text-theme-heading font-mono tracking-tight" id="modalHoldCountdown">
                {{ $holdingDefaultDisplay }}
            </div>
            <p class="text-xs text-theme-muted max-w-xs mx-auto">
                These slots are locked exclusively for you. Other players cannot select them. Please complete payment before time expires.
            </p>
        </div>

        <!-- Booking Quick Info -->
        <div class="p-4 rounded-2xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 text-xs space-y-2 mb-6">
            <div class="flex justify-between">
                <span class="text-theme-muted">Reference:</span>
                <span class="font-mono font-bold text-cyan-600 dark:text-cyan-400" id="modalRefCode">-</span>
            </div>
            <div class="flex justify-between">
                <span class="text-theme-muted">Court & Time:</span>
                <span class="font-semibold text-theme-heading" id="modalCourtTime">-</span>
            </div>
            <div class="flex justify-between text-sm pt-2 border-t border-stone-200 dark:border-stone-800">
                <span class="font-bold text-theme-heading">Amount Due:</span>
                <span class="font-black text-cyan-600 dark:text-cyan-400" id="modalTotalDue">₱0.00</span>
            </div>
        </div>

        <!-- Actions -->
        <div class="space-y-3">
            <a id="modalXenditPayLink" href="#" target="_blank"
                class="w-full py-4 rounded-xl bg-gradient-to-r from-cyan-500 to-cyan-600 hover:from-cyan-400 hover:to-cyan-500 text-slate-950 font-black text-sm shadow-xl shadow-cyan-500/25 flex items-center justify-center gap-2 transition-all">
                <i class="fa-solid fa-credit-card"></i>
                <span id="modalPayButtonText">Proceed to Pay (GCash / Maya / Card)</span>
            </a>

            <!-- Quick instant simulation button for demonstration -->
            <button type="button" id="modalSimulatePayBtn"
                class="w-full py-3 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-stone-300 dark:hover:bg-stone-700 text-cyan-700 dark:text-cyan-300 text-xs font-bold border border-cyan-500/30 flex items-center justify-center gap-2 transition-colors cursor-pointer">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                <span>Simulate Instant Successful Payment (Demo Test)</span>
            </button>

            <button type="button" id="modalCancelHoldBtn" class="w-full py-2 text-xs text-theme-muted hover:text-rose-500 transition-colors cursor-pointer">
                Cancel Hold & Release Slots
            </button>
        </div>
    </div>
</div>

<!-- INTERACTIVE CALENDAR VIEW MODAL (Option to View Calendar on Client/Player side) -->
<div id="playerCalendarModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md opacity-0 pointer-events-none transition-all duration-300">
    <div class="glass-dropdown p-6 sm:p-7 rounded-3xl max-w-md w-full border border-cyan-500/30 shadow-2xl relative space-y-5">
        <button type="button" id="closeCalendarModalBtn" class="absolute top-4 right-4 sm:top-5 sm:right-5 z-20 text-theme-muted hover:text-theme-heading p-2 rounded-xl hover:bg-stone-200/50 dark:hover:bg-stone-800/50 transition-colors cursor-pointer" title="Close calendar">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>

        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-2xl bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-xl border border-cyan-500/30 shadow-sm">
                <i class="fa-solid fa-calendar-days"></i>
            </div>
            <div>
                <h3 class="text-lg font-black text-theme-heading">Court Reservation Calendar</h3>
                <p class="text-xs text-theme-muted">Select any date to view court timeslot availability</p>
            </div>
        </div>

        <!-- Month Navigation Bar -->
        <div class="flex items-center justify-between p-2 rounded-2xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800">
            <button type="button" id="calPrevMonthBtn" class="w-8 h-8 rounded-xl hover:bg-stone-200 dark:hover:bg-stone-800 text-theme-heading flex items-center justify-center transition-colors cursor-pointer" title="Previous Month">
                <i class="fa-solid fa-chevron-left text-xs"></i>
            </button>
            <div class="font-extrabold text-sm text-theme-heading tracking-wide" id="calMonthYearLabel">
                {{ date('F Y', strtotime($selectedDate)) }}
            </div>
            <button type="button" id="calNextMonthBtn" class="w-8 h-8 rounded-xl hover:bg-stone-200 dark:hover:bg-stone-800 text-theme-heading flex items-center justify-center transition-colors cursor-pointer" title="Next Month">
                <i class="fa-solid fa-chevron-right text-xs"></i>
            </button>
        </div>

        <!-- Calendar Days Header (Sun - Sat) -->
        <div class="grid grid-cols-7 gap-1 text-center text-[11px] font-bold text-theme-muted uppercase tracking-wider">
            <div>Su</div>
            <div>Mo</div>
            <div>Tu</div>
            <div>We</div>
            <div>Th</div>
            <div>Fr</div>
            <div>Sa</div>
        </div>

        <!-- Calendar Days Grid Container -->
        <div id="calDaysGrid" class="grid grid-cols-7 gap-1.5 text-center text-xs">
            <!-- Dynamically populated via JS -->
        </div>

        <!-- Calendar Footer / Legend -->
        <div class="pt-3 border-t border-stone-200 dark:border-stone-800 flex items-center justify-between text-[11px] text-theme-muted">
            <div class="flex items-center gap-3">
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span> Selected</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full border border-cyan-500"></span> Today</span>
            </div>
            <button type="button" id="calJumpTodayBtn" class="text-cyan-600 dark:text-cyan-400 font-bold hover:underline cursor-pointer">
                Jump to Today
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // State management for court booking with drag/click to select and remove
    const bookingState = {
        selectedCourtId: null,
        selectedCourtName: '',
        selectedRate: 0,
        selectedMaxPlayers: 4,
        selectedSlots: [], // sorted array of "HH:00" e.g. ['08:00', '09:00']
        isDragging: false,
        dragMode: null, // 'SELECT' or 'REMOVE'
        dragAnchorTime: null,
        paymentMode: "{{ $settings->payment_mode }}",
        activeHold: null,
        activeHoldRef: null,
        holdTimerInterval: null,
        holdRemainingSeconds: {{ (int) ($settings->holding_duration_seconds ?: 120) }},
    };

    // Helper: Normalize slot time strings (e.g., '06:00:00' -> '06:00')
    function normalizeSlotTime(t) {
        return (t || '').toString().trim().substring(0, 5);
    }

    // Helper: Retrieve and validate active hold for current client session (In-memory or LocalStorage)
    function getActiveHold() {
        if (bookingState.activeHold && bookingState.activeHold.reference) {
            if (Date.now() < bookingState.activeHold.expiresAt) {
                return bookingState.activeHold;
            } else {
                clearActiveHold();
                return null;
            }
        }
        try {
            const raw = localStorage.getItem('paddle_active_hold');
            if (raw) {
                const parsed = JSON.parse(raw);
                if (parsed && parsed.expires_at && Date.now() < parsed.expires_at) {
                    bookingState.activeHold = {
                        reference: parsed.reference,
                        courtId: parseInt(parsed.court_id),
                        courtName: parsed.court_name,
                        date: parsed.date,
                        slots: (parsed.slots || []).map(s => normalizeSlotTime(s)),
                        expiresAt: parsed.expires_at,
                        formattedAmount: parsed.formatted_amount,
                        invoiceUrl: parsed.invoice_url,
                        gateway: parsed.gateway,
                        gatewayName: parsed.gateway_name
                    };
                    bookingState.activeHoldRef = parsed.reference;
                    bookingState.holdRemainingSeconds = Math.max(0, Math.floor((parsed.expires_at - Date.now()) / 1000));
                    return bookingState.activeHold;
                } else {
                    localStorage.removeItem('paddle_active_hold');
                }
            }
        } catch (e) {}
        return null;
    }

    // Helper: Check whether a specific court slot is held by the CURRENT client
    function isSlotHeldByMe(courtId, time) {
        const hold = getActiveHold();
        if (!hold) return false;
        const currentDate = document.getElementById('selectedDateInput')?.value;
        if (hold.date && currentDate && hold.date.trim() !== currentDate.trim()) return false;
        if (parseInt(hold.courtId) !== parseInt(courtId)) return false;
        const targetTime = normalizeSlotTime(time);
        return Array.isArray(hold.slots) && hold.slots.some(s => normalizeSlotTime(s) === targetTime);
    }

    // Helper: Show the quick-resume banner in sidebar
    function showActiveHoldBanner() {
        const hold = getActiveHold();
        const banner = document.getElementById('activeHoldBanner');
        if (!banner || !hold) return;

        const timerEl = document.getElementById('activeHoldBannerTimer');
        const courtEl = document.getElementById('activeHoldBannerCourt');
        const refEl = document.getElementById('activeHoldBannerRef');

        if (courtEl) {
            const slotsStr = (hold.slots || []).join(', ');
            courtEl.textContent = `${hold.courtName || 'Court'} (${slotsStr})`;
        }
        if (refEl) {
            refEl.textContent = hold.reference;
        }
        const sec = Math.max(0, Math.floor((hold.expiresAt - Date.now()) / 1000));
        const m = Math.floor(sec / 60);
        const s = sec % 60;
        if (timerEl) {
            timerEl.textContent = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
        }
        banner.classList.remove('hidden');
    }

    // Helper: Hide the quick-resume banner
    function hideActiveHoldBanner() {
        const banner = document.getElementById('activeHoldBanner');
        if (banner) banner.classList.add('hidden');
    }

    // Helper: Clear active hold locally
    function clearActiveHold() {
        try {
            localStorage.removeItem('paddle_active_hold');
        } catch (e) {}
        bookingState.activeHold = null;
        bookingState.activeHoldRef = null;
        hideActiveHoldBanner();
        document.querySelectorAll('.slot-my-hold').forEach(el => {
            el.classList.remove('slot-my-hold', 'pointer-events-none', 'cursor-not-allowed');
            el.disabled = false; // Re-enable so other clients can click
            const status = el.getAttribute('data-status');
            if (status === 'held') {
                el.classList.remove('pointer-events-none');
                el.classList.add('slot-held', 'cursor-pointer', 'select-none', 'opacity-80');
                el.title = 'Temporarily held by another customer';
                const labelEl = el.querySelector('.slot-status-label');
                const sec = parseInt(el.getAttribute('data-remaining-seconds')) || 0;
                if (labelEl) {
                    labelEl.innerHTML = `<span class="text-amber-700 dark:text-amber-300 flex items-center justify-center gap-1"><i class="fa-regular fa-hourglass-half text-[9px] animate-spin"></i> <span class="slot-timer" data-seconds="${sec}">${Math.floor(sec / 60)}:${String(sec % 60).padStart(2, '0')}</span></span>`;
                }
            } else if (status === 'available') {
                el.classList.add('slot-available', 'cursor-pointer');
                el.title = '';
                const labelEl = el.querySelector('.slot-status-label');
                if (labelEl) {
                    labelEl.innerHTML = '<span class="text-cyan-600 dark:text-cyan-400">Open</span>';
                }
            }
        });
    }

    // Helper: Visually style the client's own held slots in the DOM (disabled for holder)
    function updateMyHeldSlotsInDOM() {
        const hold = getActiveHold();
        if (!hold) return;

        const currentDate = document.getElementById('selectedDateInput')?.value;
        if (hold.date && currentDate && hold.date.trim() !== currentDate.trim()) return;

        const courtId = parseInt(hold.courtId);
        const slots = hold.slots || [];
        const remSec = hold.expiresAt 
            ? Math.max(0, Math.floor((hold.expiresAt - Date.now()) / 1000))
            : (bookingState.holdRemainingSeconds || 120);

        document.querySelectorAll('.court-slot').forEach(slotBtn => {
            const btnCourtId = parseInt(slotBtn.getAttribute('data-court-id'));
            const btnTime = normalizeSlotTime(slotBtn.getAttribute('data-slot-time'));
            if (btnCourtId === courtId && slots.some(s => normalizeSlotTime(s) === btnTime)) {
                slotBtn.setAttribute('data-status', 'held');
                slotBtn.setAttribute('data-remaining-seconds', remSec);
                slotBtn.classList.remove('slot-available', 'slot-pending', 'slot-booked', 'slot-selected', 'cursor-pointer');
                slotBtn.classList.add('slot-held', 'slot-my-hold', 'cursor-not-allowed', 'pointer-events-none');
                slotBtn.disabled = true; // DISABLED for the client who reserved it
                slotBtn.title = 'Your held reservation';
                const labelEl = slotBtn.querySelector('.slot-status-label');
                if (labelEl) {
                    const m = Math.floor(remSec / 60);
                    const s = remSec % 60;
                    labelEl.innerHTML = `<span class="text-emerald-700 dark:text-emerald-300 font-bold flex items-center justify-center gap-1"><i class="fa-solid fa-lock text-[9px]"></i> Your Hold (<span class="slot-timer" data-seconds="${remSec}">${m}:${String(s).padStart(2, '0')}</span>)</span>`;
                }
            }
        });
    }

    // Re-open checkout summary modal for the client's active hold so they can trigger pay again
    function openHoldCheckoutModal() {
        const hold = getActiveHold();
        if (!hold || !hold.reference) {
            if (!bookingState.activeHoldRef) {
                Swal.fire({
                    icon: 'info',
                    title: 'No Active Hold',
                    text: 'You do not have an active timeslot reservation hold.',
                    confirmButtonColor: '#0891b2'
                });
                return;
            }
        }

        const modal = document.getElementById('xenditHoldModal');
        if (!modal) return;

        const refCodeEl = document.getElementById('modalRefCode');
        const courtTimeEl = document.getElementById('modalCourtTime');
        const totalDueEl = document.getElementById('modalTotalDue');
        const payLinkEl = document.getElementById('modalXenditPayLink');
        const payTextEl = document.getElementById('modalPayButtonText');
        const modalGatewayIcon = document.getElementById('modalGatewayIcon');
        const modalGatewayBadge = document.getElementById('modalGatewayBadge');

        const holdData = hold || bookingState;
        const courtName = holdData.courtName || holdData.court_name || bookingState.selectedCourtName;
        const slots = holdData.slots || bookingState.selectedSlots || [];
        const formattedAmount = holdData.formattedAmount || holdData.formatted_amount || document.getElementById('summaryTotalAmount')?.textContent || '₱0.00';
        const invoiceUrl = holdData.invoiceUrl || holdData.invoice_url || '#';
        const reference = holdData.reference || bookingState.activeHoldRef;
        const gateway = holdData.gateway || (bookingState.paymentMode === 'paymongo' ? 'paymongo' : 'xendit');
        const isPayMongo = gateway === 'paymongo';

        if (refCodeEl) refCodeEl.textContent = reference;
        if (courtTimeEl) {
            const slotsCount = slots.length;
            courtTimeEl.textContent = `${courtName} (${slotsCount} ${slotsCount > 1 ? 'hrs' : 'hr'}: ${slots.join(', ')})`;
        }
        if (totalDueEl) totalDueEl.textContent = formattedAmount;
        if (payLinkEl) payLinkEl.href = invoiceUrl;

        const remainingSec = holdData.expiresAt 
            ? Math.max(0, Math.floor((holdData.expiresAt - Date.now()) / 1000))
            : (bookingState.holdRemainingSeconds || 120);
        bookingState.holdRemainingSeconds = remainingSec;
        const holdMins = Math.max(1, Math.round(remainingSec / 60));

        if (isPayMongo) {
            if (modalGatewayIcon) {
                modalGatewayIcon.className = 'w-16 h-16 rounded-full bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl mx-auto border border-emerald-500/40 animate-pulse';
                modalGatewayIcon.innerHTML = '<i class="fa-solid fa-bolt"></i>';
            }
            if (modalGatewayBadge) {
                modalGatewayBadge.className = 'px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30 uppercase tracking-widest';
                modalGatewayBadge.textContent = `PayMongo ${holdMins}-Minute Checkout Active`;
            }
            if (payLinkEl) {
                payLinkEl.className = 'w-full py-4 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white font-black text-sm shadow-xl shadow-emerald-500/25 flex items-center justify-center gap-2 transition-all';
            }
            if (payTextEl) {
                payTextEl.textContent = 'Proceed to Pay with PayMongo (GCash / Maya / Card)';
            }
        } else {
            if (modalGatewayIcon) {
                modalGatewayIcon.className = 'w-16 h-16 rounded-full bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-2xl mx-auto border border-cyan-500/40 animate-pulse';
                modalGatewayIcon.innerHTML = '<i class="fa-solid fa-stopwatch"></i>';
            }
            if (modalGatewayBadge) {
                modalGatewayBadge.className = 'px-3 py-1 rounded-full text-xs font-extrabold bg-cyan-500/10 text-cyan-700 dark:text-cyan-300 border border-cyan-500/30 uppercase tracking-widest';
                modalGatewayBadge.textContent = `Xendit ${holdMins}-Minute Slot Hold Active`;
            }
            if (payLinkEl) {
                payLinkEl.className = 'w-full py-4 rounded-xl bg-gradient-to-r from-cyan-500 to-cyan-600 hover:from-cyan-400 hover:to-cyan-500 text-slate-950 font-black text-sm shadow-xl shadow-cyan-500/25 flex items-center justify-center gap-2 transition-all';
            }
            if (payTextEl) {
                payTextEl.textContent = 'Proceed to Pay with Xendit (GCash / Maya / Card)';
            }
        }

        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100', 'pointer-events-auto');

        startModalCountdown();
    }
    window.openHoldCheckoutModal = openHoldCheckoutModal;

    document.addEventListener('DOMContentLoaded', () => {
        setupDateControls();
        setupInteractiveCalendar();
        setupSlotInteractivity();
        setupManualReceiptPreview();
        setupXenditCheckout();
        listenToReverbUpdates();
        startSlotCountdownTimers();

        // Restore active hold from localStorage if active and unexpired
        const activeHold = getActiveHold();
        if (activeHold) {
            showActiveHoldBanner();
            updateMyHeldSlotsInDOM();
        }
    });

    // Auto-scroll directly to targeted court card in booking engine with pulse animation
    window.scrollToCourtInEngine = function(courtId) {
        const target = document.getElementById('booking-court-' + courtId);
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            target.classList.remove('court-card-highlight');
            void target.offsetWidth; // Force CSS repaint
            target.classList.add('court-card-highlight');
            setTimeout(() => {
                target.classList.remove('court-card-highlight');
            }, 3600);
        } else {
            const engine = document.getElementById('booking-engine');
            if (engine) engine.scrollIntoView({ behavior: 'smooth' });
        }
    };

    // 1. Date Controls
    function setupDateControls() {
        const dateInput = document.getElementById('selectedDateInput');
        const prevBtn = document.getElementById('prevDayBtn');
        const nextBtn = document.getElementById('nextDayBtn');

        dateInput.addEventListener('change', () => {
            window.location.href = `/?date=${dateInput.value}#booking-engine`;
        });

        prevBtn.addEventListener('click', () => {
            const current = new Date(dateInput.value);
            current.setDate(current.getDate() - 1);
            const today = new Date();
            today.setHours(0,0,0,0);
            if (current >= today) {
                const yyyy = current.getFullYear();
                const mm = String(current.getMonth() + 1).padStart(2, '0');
                const dd = String(current.getDate()).padStart(2, '0');
                window.location.href = `/?date=${yyyy}-${mm}-${dd}#booking-engine`;
            }
        });

        nextBtn.addEventListener('click', () => {
            const current = new Date(dateInput.value);
            current.setDate(current.getDate() + 1);
            const yyyy = current.getFullYear();
            const mm = String(current.getMonth() + 1).padStart(2, '0');
            const dd = String(current.getDate()).padStart(2, '0');
            window.location.href = `/?date=${yyyy}-${mm}-${dd}#booking-engine`;
        });

        document.querySelectorAll('.quick-date-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const targetDate = btn.getAttribute('data-date');
                window.location.href = `/?date=${targetDate}#booking-engine`;
            });
        });
    }

    // Helper: get contiguous array of slots between two times on a court grid
    function getSlotsRange(courtId, startSlotTime, endSlotTime) {
        const grid = document.querySelector(`.court-slots-grid[data-court-id="${courtId}"]`);
        if (!grid) return [];
        const allSlots = Array.from(grid.querySelectorAll('.court-slot')).map(s => s.getAttribute('data-slot-time'));
        const startIdx = allSlots.indexOf(startSlotTime);
        const endIdx = allSlots.indexOf(endSlotTime);
        if (startIdx === -1 || endIdx === -1) return [];
        const minIdx = Math.min(startIdx, endIdx);
        const maxIdx = Math.max(startIdx, endIdx);
        return allSlots.slice(minIdx, maxIdx + 1);
    }

    // Helper: check if all slots in a range are bookable (STRICT: all must be 'available')
    function isRangeAvailable(courtId, range) {
        const grid = document.querySelector(`.court-slots-grid[data-court-id="${courtId}"]`);
        if (!grid) return false;
        return range.every(time => {
            const slotEl = grid.querySelector(`.court-slot[data-slot-time="${time}"]`);
            return slotEl && slotEl.getAttribute('data-status') === 'available';
        });
    }

    // Unified Slot Interaction Handler (Click to select/resize/trim, and Drag to expand/shrink)
    function handleSlotClickOrDrag(slotBtn, isDragMove = false) {
        if (!slotBtn || slotBtn.disabled || slotBtn.classList.contains('slot-my-hold')) {
            return;
        }

        const courtId = parseInt(slotBtn.getAttribute('data-court-id'));
        const courtGrid = slotBtn.closest('.court-slots-grid');
        const courtName = courtGrid.getAttribute('data-court-name');
        const rate = parseFloat(courtGrid.getAttribute('data-rate'));
        const maxPlayers = parseInt(courtGrid.getAttribute('data-max-players'));
        const time = slotBtn.getAttribute('data-slot-time');
        const status = slotBtn.getAttribute('data-status');

        // Check if slot is unavailable (Held by another player, Confirmed, or In Review)
        if (status !== 'available') {
            if (!isDragMove) {
                const isHeld = status === 'held';
                if (isHeld && (slotBtn.classList.contains('slot-my-hold') || isSlotHeldByMe(courtId, time))) {
                    // Disabled for the client who reserved it - do not show warning modal
                    return;
                }
                resetSelection();
                Swal.fire({
                    icon: 'warning',
                    title: isHeld ? 'Slot Held by Another Customer' : 'Timeslot Unavailable',
                    text: isHeld 
                        ? `Timeslot ${time} is temporarily held by another customer completing checkout.` 
                        : `Timeslot ${time} has already been reserved and cannot be selected.`,
                    confirmButtonColor: '#0891b2',
                    confirmButtonText: 'Understood'
                });
            }
            return;
        }

        // If clicking/dragging on a different court, switch selection to this court
        if (bookingState.selectedCourtId && bookingState.selectedCourtId !== courtId) {
            resetSelection();
            bookingState.selectedCourtId = courtId;
            bookingState.selectedCourtName = courtName;
            bookingState.selectedRate = rate;
            bookingState.selectedMaxPlayers = maxPlayers;
            bookingState.selectedSlots = [time];
            bookingState.dragAnchorTime = time;
            renderSelectionUI();
            return;
        }

        bookingState.selectedCourtId = courtId;
        bookingState.selectedCourtName = courtName;
        bookingState.selectedRate = rate;
        bookingState.selectedMaxPlayers = maxPlayers;

        // If no slots currently selected
        if (bookingState.selectedSlots.length === 0) {
            bookingState.selectedSlots = [time];
            bookingState.dragAnchorTime = time;
            renderSelectionUI();
            return;
        }

        const sorted = [...bookingState.selectedSlots].sort();
        const earliest = sorted[0];
        const latest = sorted[sorted.length - 1];

        // Dragging over slots:
        if (isDragMove) {
            const anchor = bookingState.dragAnchorTime || earliest;
            const newRange = getSlotsRange(courtId, anchor, time);
            if (newRange.length > 0) {
                if (isRangeAvailable(courtId, newRange)) {
                    bookingState.selectedSlots = newRange;
                    renderSelectionUI();
                } else {
                    const heldSlot = newRange.find(t => {
                        const btn = document.querySelector(`.court-slot[data-court-id="${courtId}"][data-slot-time="${t}"]`);
                        return btn && btn.getAttribute('data-status') === 'held';
                    });
                    bookingState.isDragging = false;
                    resetSelection();
                    if (heldSlot && isSlotHeldByMe(courtId, heldSlot)) {
                        return;
                    }
                    Swal.fire({
                        icon: 'warning',
                        title: heldSlot ? 'Slot Held by Another Customer' : 'Range Unavailable',
                        text: heldSlot
                            ? `Timeslot ${heldSlot} in this range is temporarily held by another customer completing checkout.`
                            : 'Cannot select this range because one or more timeslots are already reserved.',
                        confirmButtonColor: '#0891b2',
                        confirmButtonText: 'Understood'
                    });
                }
            }
            return;
        }

        // Direct Click on slot:
        // Scenario 1: User clicks the exact latest slot
        if (time === latest) {
            if (bookingState.selectedSlots.length <= 1) {
                // Clicking the only selected slot clears the selection
                resetSelection();
            } else {
                // Clicking the latest slot removes that latest slot
                bookingState.selectedSlots.pop();
                renderSelectionUI();
            }
            return;
        }

        // Scenario 2: User clicks a slot between earliest and latest (or earliest itself)
        if (time >= earliest && time < latest) {
            const newRange = getSlotsRange(courtId, earliest, time);
            if (newRange.length > 0 && isRangeAvailable(courtId, newRange)) {
                bookingState.selectedSlots = newRange;
                bookingState.dragAnchorTime = earliest;
                renderSelectionUI();
            } else {
                const heldSlot = newRange.find(t => {
                    const btn = document.querySelector(`.court-slot[data-court-id="${courtId}"][data-slot-time="${t}"]`);
                    return btn && btn.getAttribute('data-status') === 'held';
                });
                resetSelection();
                if (heldSlot && isSlotHeldByMe(courtId, heldSlot)) {
                    return;
                }
                Swal.fire({
                    icon: 'warning',
                    title: heldSlot ? 'Slot Held by Another Customer' : 'Range Unavailable',
                    text: heldSlot
                        ? `Timeslot ${heldSlot} in this range is temporarily held by another customer completing checkout.`
                        : 'Cannot select this range because one or more intermediate timeslots are already reserved.',
                    confirmButtonColor: '#0891b2',
                    confirmButtonText: 'Understood'
                });
            }
            return;
        }

        // Scenario 3: User clicks a slot after latest (extends range forward)
        if (time > latest) {
            const newRange = getSlotsRange(courtId, earliest, time);
            if (newRange.length > 0 && isRangeAvailable(courtId, newRange)) {
                bookingState.selectedSlots = newRange;
                bookingState.dragAnchorTime = earliest;
                renderSelectionUI();
            } else {
                const heldSlot = newRange.find(t => {
                    const btn = document.querySelector(`.court-slot[data-court-id="${courtId}"][data-slot-time="${t}"]`);
                    return btn && btn.getAttribute('data-status') === 'held';
                });
                resetSelection();
                if (heldSlot && isSlotHeldByMe(courtId, heldSlot)) {
                    return;
                }
                Swal.fire({
                    icon: 'warning',
                    title: heldSlot ? 'Slot Held by Another Customer' : 'Range Unavailable',
                    text: heldSlot
                        ? `Timeslot ${heldSlot} in this range is temporarily held by another customer completing checkout.`
                        : 'Cannot extend selection because one or more intermediate slots are already reserved.',
                    confirmButtonColor: '#0891b2',
                    confirmButtonText: 'Understood'
                });
            }
            return;
        }

        // Scenario 4: User clicks a slot before earliest (extends range backward)
        if (time < earliest) {
            const newRange = getSlotsRange(courtId, time, latest);
            if (newRange.length > 0 && isRangeAvailable(courtId, newRange)) {
                bookingState.selectedSlots = newRange;
                bookingState.dragAnchorTime = time;
                renderSelectionUI();
            } else {
                const heldSlot = newRange.find(t => {
                    const btn = document.querySelector(`.court-slot[data-court-id="${courtId}"][data-slot-time="${t}"]`);
                    return btn && btn.getAttribute('data-status') === 'held';
                });
                resetSelection();
                if (heldSlot && isSlotHeldByMe(courtId, heldSlot)) {
                    return;
                }
                Swal.fire({
                    icon: 'warning',
                    title: heldSlot ? 'Slot Held by Another Customer' : 'Range Unavailable',
                    text: heldSlot
                        ? `Timeslot ${heldSlot} in this range is temporarily held by another customer completing checkout.`
                        : 'Cannot extend selection because one or more intermediate slots are already reserved.',
                    confirmButtonColor: '#0891b2',
                    confirmButtonText: 'Understood'
                });
            }
            return;
        }
    }

    // 2. Slot Interactivity (Drag & Click to SELECT, RESIZE, and REMOVE)
    function setupSlotInteractivity() {
        const container = document.getElementById('courtsScheduleContainer');
        if (!container) return;

        container.addEventListener('dragstart', (e) => e.preventDefault());

        // Mouse Down
        container.addEventListener('mousedown', (e) => {
            const slotBtn = e.target.closest('.court-slot');
            if (!slotBtn || slotBtn.disabled || slotBtn.classList.contains('slot-my-hold')) return;

            bookingState.isDragging = true;
            handleSlotClickOrDrag(slotBtn, false);
        });

        // Mouse Over while dragging
        container.addEventListener('mouseover', (e) => {
            if (!bookingState.isDragging) return;
            const slotBtn = e.target.closest('.court-slot');
            if (!slotBtn || slotBtn.disabled || slotBtn.classList.contains('slot-my-hold')) return;

            const courtId = parseInt(slotBtn.getAttribute('data-court-id'));
            if (bookingState.selectedCourtId && bookingState.selectedCourtId !== courtId) return;

            handleSlotClickOrDrag(slotBtn, true);
        });

        // Mouse Up
        window.addEventListener('mouseup', () => {
            bookingState.isDragging = false;
        });

        // Keyboard navigation support (Enter / Space clicks)
        container.addEventListener('click', (e) => {
            if (e.detail === 0) {
                const slotBtn = e.target.closest('.court-slot');
                if (slotBtn && !slotBtn.disabled && !slotBtn.classList.contains('slot-my-hold')) {
                    handleSlotClickOrDrag(slotBtn, false);
                }
            }
        });

        // Touch support for mobile devices
        container.addEventListener('touchstart', (e) => {
            const touch = e.touches[0];
            const target = document.elementFromPoint(touch.clientX, touch.clientY);
            const slotBtn = target?.closest('.court-slot');
            if (!slotBtn || slotBtn.disabled || slotBtn.classList.contains('slot-my-hold')) return;
            handleSlotClickOrDrag(slotBtn, false);
        }, { passive: true });

        // Clear Selection
        document.getElementById('clearSelectionBtn')?.addEventListener('click', resetSelection);
    }

    function renderSelectionUI() {
        // Automatically eject any slot that is no longer 'available' (unless currently held by this user's active checkout)
        if (bookingState.selectedCourtId && bookingState.selectedSlots.length > 0 && !bookingState.activeHoldRef) {
            bookingState.selectedSlots = bookingState.selectedSlots.filter(time => {
                const btn = document.querySelector(`.court-slot[data-court-id="${bookingState.selectedCourtId}"][data-slot-time="${time}"]`);
                return btn && btn.getAttribute('data-status') === 'available';
            });
            if (bookingState.selectedSlots.length === 0) {
                bookingState.selectedCourtId = null;
                bookingState.selectedCourtName = '';
            }
        }

        document.querySelectorAll('.court-slot').forEach(btn => {
            const courtId = parseInt(btn.getAttribute('data-court-id'));
            const time = btn.getAttribute('data-slot-time');
            const statusLabel = btn.querySelector('.slot-status-label');
            const status = btn.getAttribute('data-status');

            if (bookingState.selectedCourtId === courtId && bookingState.selectedSlots.includes(time) && (status === 'available' || bookingState.activeHoldRef)) {
                btn.classList.add('slot-selected');
                btn.classList.remove('slot-available', 'pointer-events-none', 'cursor-not-allowed', 'select-none');
                btn.disabled = false;
                if (statusLabel) {
                    statusLabel.innerHTML = '<span class="text-slate-950 font-extrabold">Selected</span>';
                }
            } else if (status === 'available') {
                btn.classList.remove('slot-selected', 'pointer-events-none', 'cursor-not-allowed', 'select-none', 'slot-my-hold');
                btn.classList.add('slot-available', 'cursor-pointer');
                btn.disabled = false;
                if (statusLabel) {
                    statusLabel.innerHTML = '<span class="text-cyan-600 dark:text-cyan-400">Open</span>';
                }
            } else if (status === 'held') {
                const isMine = isSlotHeldByMe(courtId, time);
                if (isMine) {
                    // DISABLED FOR THE CLIENT WHO SUCCESSFULLY RESERVED IT
                    btn.disabled = true;
                    btn.classList.remove('slot-selected', 'slot-available', 'cursor-pointer');
                    btn.classList.add('slot-held', 'slot-my-hold', 'cursor-not-allowed', 'pointer-events-none');
                    btn.title = 'Your held reservation';
                } else {
                    // CLICKABLE FOR OTHER CLIENTS
                    btn.disabled = false;
                    btn.classList.remove('slot-selected', 'slot-available', 'pointer-events-none', 'cursor-not-allowed', 'slot-my-hold');
                    btn.classList.add('slot-held', 'cursor-pointer', 'select-none', 'opacity-80');
                    btn.title = 'Temporarily held by another customer';
                }
            } else {
                // Confirmed or in-review: allow clicking to trigger error warning & unselection
                btn.disabled = false;
                btn.classList.remove('slot-selected', 'slot-available', 'pointer-events-none', 'slot-my-hold');
                btn.classList.add('cursor-not-allowed', 'select-none', 'opacity-80');
            }
        });

        const noNotice = document.getElementById('noSelectionNotice');
        const activeBox = document.getElementById('activeSelectionBox');

        if (bookingState.selectedSlots.length === 0) {
            noNotice.classList.remove('hidden');
            activeBox.classList.add('hidden');
            return;
        }

        noNotice.classList.add('hidden');
        activeBox.classList.remove('hidden');

        // Calculate hours and amount
        const totalHours = bookingState.selectedSlots.length;
        const totalAmount = totalHours * bookingState.selectedRate;

        // Times string format
        const sorted = [...bookingState.selectedSlots].sort();
        const startHour = sorted[0];
        const lastSlot = sorted[sorted.length - 1];
        const endHourNum = parseInt(lastSlot.split(':')[0]) + 1;
        const endTime = `${String(endHourNum).padStart(2, '0')}:00`;

        const format12 = (t) => {
            const [h, m] = t.split(':');
            let hour = parseInt(h);
            if (hour === 24 || hour === 0) return '12:00 AM';
            const ampm = hour >= 12 ? 'PM' : 'AM';
            hour = hour % 12 || 12;
            return `${hour}:00 ${ampm}`;
        };

        const rangeStr = `${format12(startHour)} – ${format12(endTime)}`;

        // Update Summary elements
        document.getElementById('summaryCourtName').textContent = bookingState.selectedCourtName;
        document.getElementById('summaryDate').textContent = document.getElementById('selectedDateInput').value;
        document.getElementById('summaryDuration').textContent = `${totalHours} ${totalHours > 1 ? 'Hours' : 'Hour'} (${rangeStr})`;
        document.getElementById('summarySlotsList').textContent = sorted.map(format12).join(', ');
        document.getElementById('summaryTotalAmount').textContent = `₱${totalAmount.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
        document.getElementById('summaryRateSnapshot').textContent = `₱${bookingState.selectedRate.toFixed(2)}`;

        // Update hidden inputs for manual receipt form
        if (document.getElementById('mrCourtId')) {
            document.getElementById('mrCourtId').value = bookingState.selectedCourtId;
            document.getElementById('mrDate').value = document.getElementById('selectedDateInput').value;
            document.getElementById('mrSlots').value = JSON.stringify(bookingState.selectedSlots);
        }
    }

    function resetSelection() {
        bookingState.selectedCourtId = null;
        bookingState.selectedCourtName = '';
        bookingState.selectedSlots = [];
        renderSelectionUI();
    }

    // 3. Manual Receipt File Preview & Form sync
    function setupManualReceiptPreview() {
        const fileInput = document.getElementById('receiptFileInput');
        const previewBox = document.getElementById('receiptPreviewBox');
        const previewImg = document.getElementById('receiptPreviewImg');
        const form = document.getElementById('manualReceiptForm');

        if (fileInput) {
            fileInput.addEventListener('change', () => {
                const file = fileInput.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        previewImg.src = e.target.result;
                        previewBox.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewBox.classList.add('hidden');
                }
            });
        }

        if (form) {
            form.addEventListener('submit', (e) => {
                const name = document.getElementById('custName').value.trim();
                const phone = document.getElementById('custPhone').value.trim();
                const email = document.getElementById('custEmail').value.trim();
                const players = document.getElementById('custPlayersCount').value;
                const notes = document.getElementById('custNotes').value.trim();

                if (!name || !phone) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Contact Details',
                        text: 'Please provide your Full Name and Mobile Phone Number.',
                        confirmButtonColor: '#0891b2'
                    });
                    return;
                }

                if (bookingState.selectedSlots.length === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'info',
                        title: 'No Timeslots Selected',
                        text: 'Please select at least one timeslot on the court schedule before proceeding.',
                        confirmButtonColor: '#0891b2'
                    });
                    return;
                }

                document.getElementById('mrCourtId').value = bookingState.selectedCourtId;
                document.getElementById('mrDate').value = document.getElementById('selectedDateInput').value;
                document.getElementById('mrSlots').value = JSON.stringify(bookingState.selectedSlots);
                document.getElementById('mrCustName').value = name;
                document.getElementById('mrCustPhone').value = phone;
                document.getElementById('mrCustEmail').value = email;
                document.getElementById('mrPlayersCount').value = players;
                document.getElementById('mrNotes').value = notes;
            });
        }
    }

    // 4. Xendit & PayMongo Hold Checkout Flow
    function setupXenditCheckout() {
        const holdBtn = document.getElementById('startHoldAndPayBtn');
        const modal = document.getElementById('xenditHoldModal');
        const closeBtn = document.getElementById('closeXenditModalBtn');
        const cancelBtn = document.getElementById('modalCancelHoldBtn');
        const simulateBtn = document.getElementById('modalSimulatePayBtn');

        if (holdBtn) {
            const gateway = holdBtn.getAttribute('data-gateway') || 'xendit';
            const isPayMongo = gateway === 'paymongo';
            const holdMinsLabel = Math.max(1, Math.round({{ (int) ($settings->holding_duration_seconds ?: 120) }} / 60));
            const defaultBtnText = isPayMongo 
                ? `<i class="fa-solid fa-lock"></i> Hold ${holdMinsLabel} Mins & Pay with PayMongo` 
                : `<i class="fa-solid fa-lock"></i> Hold ${holdMinsLabel} Mins & Pay with Xendit`;

            holdBtn.addEventListener('click', async () => {
                // If current selection is already held by this client, re-open checkout summary modal directly!
                const existingHold = getActiveHold();
                if (existingHold && parseInt(existingHold.courtId) === parseInt(bookingState.selectedCourtId) && existingHold.slots.length > 0) {
                    const isAllHeld = bookingState.selectedSlots.length === 0 || bookingState.selectedSlots.every(s => existingHold.slots.includes(s));
                    if (isAllHeld) {
                        openHoldCheckoutModal();
                        return;
                    }
                }

                const name = document.getElementById('custName').value.trim();
                const phone = document.getElementById('custPhone').value.trim();
                const email = document.getElementById('custEmail').value.trim();
                const players = document.getElementById('custPlayersCount').value;
                const notes = document.getElementById('custNotes').value.trim();

                if (!name || !phone) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Customer Details Required',
                        text: 'Please enter your Full Name and Mobile Phone Number to hold the slot.',
                        confirmButtonColor: '#0891b2'
                    });
                    return;
                }

                if (bookingState.selectedSlots.length === 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Select Timeslots',
                        text: 'Please select at least one court timeslot first.',
                        confirmButtonColor: '#0891b2'
                    });
                    return;
                }

                holdBtn.disabled = true;
                holdBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Locking Slots with ${isPayMongo ? 'PayMongo' : 'Xendit'}...`;

                try {
                    const response = await fetch('/api/hold-slots', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            court_id: bookingState.selectedCourtId,
                            date: document.getElementById('selectedDateInput').value,
                            slots: bookingState.selectedSlots,
                            customer_name: name,
                            customer_phone: phone,
                            customer_email: email,
                            players_count: players,
                            notes: notes
                        })
                    });

                    const data = await response.json();

                    if (!data.success) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Cannot Reserve Slot',
                            text: data.message || 'Unable to hold slots. They may have just been reserved or held by another player.',
                            confirmButtonColor: '#0891b2'
                        });
                        holdBtn.disabled = false;
                        holdBtn.innerHTML = defaultBtnText;
                        return;
                    }

                    // Store active hold in local storage & bookingState
                    const remainingSec = data.remaining_seconds || {{ (int) ($settings->holding_duration_seconds ?: 120) }};
                    const holdData = {
                        reference: data.reference,
                        court_id: bookingState.selectedCourtId,
                        court_name: bookingState.selectedCourtName,
                        date: document.getElementById('selectedDateInput').value,
                        slots: [...bookingState.selectedSlots],
                        expires_at: Date.now() + remainingSec * 1000,
                        remaining_seconds: remainingSec,
                        formatted_amount: data.formatted_amount,
                        invoice_url: data.invoice_url,
                        gateway: data.gateway || (isPayMongo ? 'paymongo' : 'xendit'),
                        gateway_name: data.gateway_name || (isPayMongo ? 'PayMongo' : 'Xendit')
                    };

                    bookingState.activeHold = {
                        reference: holdData.reference,
                        courtId: parseInt(holdData.court_id),
                        courtName: holdData.court_name,
                        date: holdData.date,
                        slots: (holdData.slots || []).map(s => normalizeSlotTime(s)),
                        expiresAt: holdData.expires_at,
                        formattedAmount: holdData.formatted_amount,
                        invoiceUrl: holdData.invoice_url,
                        gateway: holdData.gateway,
                        gatewayName: holdData.gateway_name
                    };
                    bookingState.activeHoldRef = data.reference;
                    bookingState.holdRemainingSeconds = remainingSec;

                    try {
                        localStorage.setItem('paddle_active_hold', JSON.stringify(holdData));
                    } catch (e) {}

                    bookingState.selectedSlots = [];
                    bookingState.selectedCourtId = null;
                    bookingState.selectedCourtName = '';
                    renderSelectionUI();

                    updateMyHeldSlotsInDOM();
                    showActiveHoldBanner();
                    openHoldCheckoutModal();

                } catch (err) {
                    console.error('Hold error:', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Connection Error',
                        text: 'Failed to connect to reservation service. Please try again.',
                        confirmButtonColor: '#0891b2'
                    });
                } finally {
                    holdBtn.disabled = false;
                    holdBtn.innerHTML = defaultBtnText;
                }
            });
        }

        // Close modal (accidentally or deliberately) - keeps hold active and shows resume banner
        function closeModal() {
            modal.classList.add('opacity-0', 'pointer-events-none');
            modal.classList.remove('opacity-100', 'pointer-events-auto');
            showActiveHoldBanner();
            updateMyHeldSlotsInDOM();
        }

        closeBtn?.addEventListener('click', closeModal);

        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('opacity-100')) {
                closeModal();
            }
        });

        // Cancel Hold
        cancelBtn?.addEventListener('click', async () => {
            const hold = getActiveHold();
            const ref = hold ? hold.reference : bookingState.activeHoldRef;
            if (!ref) return;

            const confirmResult = await Swal.fire({
                title: 'Cancel Reservation Hold?',
                text: 'Are you sure you want to release these held timeslots? Other players will be able to book them.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, Release Slots',
                cancelButtonText: 'Keep My Hold'
            });

            if (!confirmResult.isConfirmed) return;

            try {
                await fetch(`/api/cancel-hold/${ref}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
            } catch (e) {}

            clearInterval(bookingState.holdTimerInterval);
            modal.classList.add('opacity-0', 'pointer-events-none');
            modal.classList.remove('opacity-100', 'pointer-events-auto');
            clearActiveHold();
            resetSelection();
            location.reload();
        });

        // Simulate Instant Payment (For demo & testing)
        simulateBtn?.addEventListener('click', async () => {
            const hold = getActiveHold();
            const ref = hold ? hold.reference : bookingState.activeHoldRef;
            if (!ref) return;
            simulateBtn.disabled = true;
            simulateBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing Payment...';

            try {
                const res = await fetch(`/simulate-payment/${ref}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ payment_channel: 'GCash' })
                });
                const resData = await res.json();
                clearActiveHold();
                if (resData.success) {
                    window.location.href = resData.track_url;
                } else {
                    window.location.href = `/track/${ref}`;
                }
            } catch (e) {
                clearActiveHold();
                Swal.fire({
                    icon: 'success',
                    title: 'Payment Simulated',
                    text: 'Payment simulation complete.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = `/track/${ref}`;
                });
            }
        });
    }

    function startModalCountdown() {
        clearInterval(bookingState.holdTimerInterval);
        const timerEl = document.getElementById('modalHoldCountdown');
        const bannerTimerEl = document.getElementById('activeHoldBannerTimer');

        const updateTimer = () => {
            const hold = getActiveHold();
            if (hold && hold.expiresAt) {
                bookingState.holdRemainingSeconds = Math.max(0, Math.floor((hold.expiresAt - Date.now()) / 1000));
            }

            if (bookingState.holdRemainingSeconds <= 0) {
                clearInterval(bookingState.holdTimerInterval);
                if (timerEl) timerEl.textContent = '00:00';
                if (bannerTimerEl) bannerTimerEl.textContent = '00:00';

                const refToCancel = bookingState.activeHoldRef;
                clearActiveHold();

                // Instantly notify server to release hold so Reverb broadcasts 'released' to all players
                if (refToCancel) {
                    fetch(`/api/cancel-hold/${refToCancel}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            'Accept': 'application/json'
                        }
                    }).catch(() => {});
                }

                const holdDurationMinutes = Math.max(1, Math.round({{ (int) ($settings->holding_duration_seconds ?: 120) }} / 60));
                Swal.fire({
                    icon: 'warning',
                    title: 'Reservation Hold Expired',
                    text: `Your ${holdDurationMinutes}-minute reservation hold has expired. Slots have been released back to the schedule.`,
                    confirmButtonColor: '#0891b2'
                }).then(() => {
                    document.getElementById('xenditHoldModal').classList.add('opacity-0', 'pointer-events-none');
                    resetSelection();
                    location.reload();
                });
                return;
            }

            const m = Math.floor(bookingState.holdRemainingSeconds / 60);
            const s = bookingState.holdRemainingSeconds % 60;
            const timeStr = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
            if (timerEl) timerEl.textContent = timeStr;
            if (bannerTimerEl) bannerTimerEl.textContent = timeStr;
            bookingState.holdRemainingSeconds--;
        };

        updateTimer();
        bookingState.holdTimerInterval = setInterval(updateTimer, 1000);
    }

    // 5. Live Real-Time Reverb WebSocket Synchronization (No HTTP Polling)
    function listenToReverbUpdates() {
        // Handle incoming court update payload
        function applyCourtSlotUpdate(courtId, slotTime, status, remainingSeconds = {{ (int) ($settings->holding_duration_seconds ?: 120) }}) {
            const slotBtn = document.querySelector(`.court-slot[data-court-id="${courtId}"][data-slot-time="${slotTime}"]`);
            if (!slotBtn) return;

            const normalizedStatus = (status === 'released') ? 'available' : status;
            slotBtn.setAttribute('data-status', normalizedStatus);

            // Check if THIS slot is currently selected by the active user in the booking engine
            const isCurrentlySelectedByMe = (
                bookingState.selectedCourtId === courtId &&
                bookingState.selectedSlots.includes(slotTime)
            );

            slotBtn.classList.remove('slot-available', 'slot-held', 'slot-pending', 'slot-booked', 'slot-selected', 'slot-my-hold');

            if (normalizedStatus === 'available') {
                slotBtn.classList.remove('pointer-events-none', 'cursor-not-allowed', 'select-none', 'opacity-80', 'slot-held', 'slot-pending', 'slot-booked', 'slot-my-hold');
                slotBtn.disabled = false;
                slotBtn.title = '';

                if (isCurrentlySelectedByMe) {
                    // PRESERVE SELECTION! Do not unselect user's chosen timeslots during updates
                    slotBtn.classList.add('slot-selected');
                    const label = slotBtn.querySelector('.slot-status-label');
                    if (label) label.innerHTML = '<span class="text-slate-950 font-extrabold">Selected</span>';
                } else {
                    slotBtn.classList.add('slot-available', 'cursor-pointer');
                    const label = slotBtn.querySelector('.slot-status-label');
                    if (label) label.innerHTML = '<span class="text-cyan-600 dark:text-cyan-400">Open</span>';
                }

                // If my hold was released, clear local state
                if (isSlotHeldByMe(courtId, slotTime)) {
                    clearActiveHold();
                }
            } else if (normalizedStatus === 'held') {
                const isMine = isSlotHeldByMe(courtId, slotTime);
                const rem = Math.max(0, parseInt(remainingSeconds) || 0);
                slotBtn.setAttribute('data-remaining-seconds', rem);

                if (isMine) {
                    // DISABLED FOR THE CLIENT WHO SUCCESSFULLY RESERVED IT
                    slotBtn.disabled = true;
                    slotBtn.classList.remove('slot-available', 'slot-pending', 'slot-booked', 'slot-selected', 'cursor-pointer');
                    slotBtn.classList.add('slot-held', 'slot-my-hold', 'cursor-not-allowed', 'pointer-events-none');
                    slotBtn.title = 'Your held reservation';
                    const m = Math.floor(rem / 60);
                    const s = rem % 60;
                    slotBtn.querySelector('.slot-status-label').innerHTML = `<span class="text-emerald-700 dark:text-emerald-300 font-bold flex items-center justify-center gap-1"><i class="fa-solid fa-lock text-[9px]"></i> Your Hold (<span class="slot-timer" data-seconds="${rem}">${m}:${String(s).padStart(2, '0')}</span>)</span>`;
                } else {
                    // CLICKABLE FOR OTHER CLIENTS
                    slotBtn.disabled = false;
                    slotBtn.classList.remove('slot-available', 'slot-pending', 'slot-booked', 'slot-selected', 'pointer-events-none', 'cursor-not-allowed', 'slot-my-hold');
                    slotBtn.classList.add('slot-held', 'cursor-pointer', 'select-none', 'opacity-80');
                    slotBtn.title = 'Temporarily held by another customer';
                    const m = Math.floor(rem / 60);
                    const s = rem % 60;
                    slotBtn.querySelector('.slot-status-label').innerHTML = `<span class="text-amber-700 dark:text-amber-300 flex items-center justify-center gap-1"><i class="fa-regular fa-hourglass-half text-[9px] animate-spin"></i> <span class="slot-timer" data-seconds="${rem}">${m}:${String(s).padStart(2, '0')}</span></span>`;

                    // Only if ANOTHER customer held this slot while we had it selected, eject it from selection
                    if (isCurrentlySelectedByMe && !bookingState.activeHoldRef) {
                        bookingState.selectedSlots = bookingState.selectedSlots.filter(t => t !== slotTime);
                        renderSelectionUI();
                    }
                }
            } else if (normalizedStatus === 'pending_approval') {
                slotBtn.classList.remove('cursor-pointer', 'slot-available', 'pointer-events-none', 'slot-my-hold');
                slotBtn.classList.add('slot-pending', 'cursor-not-allowed', 'select-none', 'opacity-80');
                slotBtn.disabled = false;
                slotBtn.title = 'Pending approval';
                slotBtn.querySelector('.slot-status-label').innerHTML = '<span class="text-indigo-700 dark:text-indigo-300">In Review</span>';

                if (isCurrentlySelectedByMe && !bookingState.activeHoldRef) {
                    bookingState.selectedSlots = bookingState.selectedSlots.filter(t => t !== slotTime);
                    renderSelectionUI();
                }
            } else if (normalizedStatus === 'confirmed') {
                slotBtn.classList.remove('cursor-pointer', 'slot-available', 'pointer-events-none', 'slot-my-hold');
                slotBtn.classList.add('slot-booked', 'cursor-not-allowed', 'select-none', 'opacity-80');
                slotBtn.disabled = false;
                slotBtn.title = 'Reserved';
                slotBtn.querySelector('.slot-status-label').innerHTML = '<span class="text-rose-700 dark:text-rose-400">Booked</span>';

                if (isSlotHeldByMe(courtId, slotTime)) {
                    clearActiveHold();
                }

                if (isCurrentlySelectedByMe && !bookingState.activeHoldRef) {
                    bookingState.selectedSlots = bookingState.selectedSlots.filter(t => t !== slotTime);
                    renderSelectionUI();
                }
            }
        }

        // WebSockets Reverb listener: pure real-time push events without HTTP polling
        if (typeof window.subscribeCourtUpdates === 'function') {
            window.subscribeCourtUpdates((data) => {
                const selectedDate = document.getElementById('selectedDateInput')?.value;
                if (data.date === selectedDate) {
                    let remSec = {{ (int) ($settings->holding_duration_seconds ?: 120) }};
                    if (data.remaining_seconds != null && data.remaining_seconds !== undefined) {
                        remSec = Math.max(0, parseInt(data.remaining_seconds) || 0);
                    } else if (data.held_until_timestamp) {
                        remSec = Math.max(0, data.held_until_timestamp - Math.floor(Date.now() / 1000));
                    }

                    (data.slots || []).forEach(slotTime => {
                        applyCourtSlotUpdate(data.court_id, slotTime, data.status, remSec);
                    });
                }
            });
        }
    }

    // 6. Timers for held slots on grid & banner synchronization
    function startSlotCountdownTimers() {
        setInterval(() => {
            // Keep banner & active hold in sync every second
            const hold = getActiveHold();
            if (hold && hold.expiresAt) {
                const sec = Math.max(0, Math.floor((hold.expiresAt - Date.now()) / 1000));
                const m = Math.floor(sec / 60);
                const s = sec % 60;
                const timeStr = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
                const bannerTimer = document.getElementById('activeHoldBannerTimer');
                if (bannerTimer) bannerTimer.textContent = timeStr;
                if (sec <= 0) {
                    clearActiveHold();
                }
            }

            document.querySelectorAll('.slot-timer').forEach(el => {
                let sec = parseInt(el.getAttribute('data-seconds'));
                if (sec > 0) {
                    sec--;
                    el.setAttribute('data-seconds', sec);
                    const m = Math.floor(sec / 60);
                    const s = sec % 60;
                    el.textContent = `${m}:${String(s).padStart(2, '0')}`;
                } else {
                    const slotBtn = el.closest('.court-slot');
                    if (slotBtn) {
                        const courtId = parseInt(slotBtn.getAttribute('data-court-id'));
                        const slotTime = slotBtn.getAttribute('data-slot-time');
                        const isCurrentlySelected = (
                            bookingState.selectedCourtId === courtId &&
                            bookingState.selectedSlots.includes(slotTime)
                        );
                        slotBtn.setAttribute('data-status', 'available');
                        slotBtn.classList.remove('slot-held', 'slot-my-hold', 'pointer-events-none', 'cursor-not-allowed', 'select-none', 'opacity-80');
                        slotBtn.disabled = false;
                        slotBtn.title = '';
                        const labelEl = slotBtn.querySelector('.slot-status-label');
                        if (isCurrentlySelected) {
                            slotBtn.classList.add('slot-selected');
                            if (labelEl) labelEl.innerHTML = '<span class="text-slate-950 font-extrabold">Selected</span>';
                        } else {
                            slotBtn.classList.add('slot-available', 'cursor-pointer');
                            if (labelEl) labelEl.innerHTML = '<span class="text-cyan-600 dark:text-cyan-400">Open</span>';
                        }
                    }
                }
            });
        }, 1000);
    }

    // 7. Interactive Calendar View Modal (Player option to view calendar)
    function setupInteractiveCalendar() {
        const modal = document.getElementById('playerCalendarModal');
        const openBtn = document.getElementById('openCalendarModalBtn');
        const closeBtn = document.getElementById('closeCalendarModalBtn');
        const prevBtn = document.getElementById('calPrevMonthBtn');
        const nextBtn = document.getElementById('calNextMonthBtn');
        const jumpTodayBtn = document.getElementById('calJumpTodayBtn');
        const labelEl = document.getElementById('calMonthYearLabel');
        const gridEl = document.getElementById('calDaysGrid');
        const dateInput = document.getElementById('selectedDateInput');

        if (!modal || !openBtn || !gridEl) return;

        let curDate = dateInput?.value ? new Date(dateInput.value + 'T00:00:00') : new Date();
        if (isNaN(curDate.getTime())) curDate = new Date();

        let calYear = curDate.getFullYear();
        let calMonth = curDate.getMonth();

        function renderCalendar() {
            gridEl.innerHTML = '';

            const monthNames = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];
            labelEl.textContent = `${monthNames[calMonth]} ${calYear}`;

            const firstDayOfWeek = new Date(calYear, calMonth, 1).getDay(); // 0 is Sunday
            const totalDaysInMonth = new Date(calYear, calMonth + 1, 0).getDate();

            const now = new Date();
            const todayStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
            const activeSelected = dateInput?.value || todayStr;

            // Empty cells for padding days before the 1st
            for (let i = 0; i < firstDayOfWeek; i++) {
                const blank = document.createElement('div');
                blank.className = 'p-2.5';
                gridEl.appendChild(blank);
            }

            // Days 1 through totalDaysInMonth
            for (let day = 1; day <= totalDaysInMonth; day++) {
                const dayStr = String(day).padStart(2, '0');
                const monthStr = String(calMonth + 1).padStart(2, '0');
                const dateStr = `${calYear}-${monthStr}-${dayStr}`;

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = day;

                const isPast = dateStr < todayStr;
                const isToday = dateStr === todayStr;
                const isSelected = dateStr === activeSelected;

                if (isPast) {
                    btn.className = 'p-2.5 rounded-xl text-center font-medium text-xs text-theme-muted opacity-25 cursor-not-allowed select-none';
                    btn.disabled = true;
                } else if (isSelected) {
                    btn.className = 'p-2.5 rounded-xl text-center font-black text-xs bg-gradient-to-tr from-cyan-500 to-cyan-600 text-slate-950 shadow-md shadow-cyan-500/30 cursor-pointer transition-transform hover:scale-105';
                } else if (isToday) {
                    btn.className = 'p-2.5 rounded-xl text-center font-bold text-xs border border-cyan-500/60 text-cyan-600 dark:text-cyan-400 hover:bg-cyan-500/15 transition-all cursor-pointer';
                } else {
                    btn.className = 'p-2.5 rounded-xl text-center font-semibold text-xs text-theme-heading hover:bg-stone-200 dark:hover:bg-stone-800 hover:text-cyan-600 dark:hover:text-cyan-400 transition-all cursor-pointer';
                }

                if (!isPast) {
                    btn.addEventListener('click', () => {
                        dateInput.value = dateStr;
                        closeCalendar();
                        window.location.href = `/?date=${dateStr}#booking-engine`;
                    });
                }

                gridEl.appendChild(btn);
            }
        }

        function openCalendar() {
            if (dateInput?.value) {
                const d = new Date(dateInput.value + 'T00:00:00');
                if (!isNaN(d.getTime())) {
                    calYear = d.getFullYear();
                    calMonth = d.getMonth();
                }
            }
            renderCalendar();
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.classList.add('opacity-100', 'pointer-events-auto');
        }

        function closeCalendar() {
            modal.classList.remove('opacity-100', 'pointer-events-auto');
            modal.classList.add('opacity-0', 'pointer-events-none');
        }

        openBtn.addEventListener('click', openCalendar);
        closeBtn?.addEventListener('click', closeCalendar);

        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeCalendar();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('opacity-100')) {
                closeCalendar();
            }
        });

        prevBtn?.addEventListener('click', () => {
            calMonth--;
            if (calMonth < 0) {
                calMonth = 11;
                calYear--;
            }
            renderCalendar();
        });

        nextBtn?.addEventListener('click', () => {
            calMonth++;
            if (calMonth > 11) {
                calMonth = 0;
                calYear++;
            }
            renderCalendar();
        });

        jumpTodayBtn?.addEventListener('click', () => {
            const now = new Date();
            calYear = now.getFullYear();
            calMonth = now.getMonth();
            renderCalendar();
        });
    }
</script>
@endpush
