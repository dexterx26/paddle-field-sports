@extends('layouts.owner')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div class="pb-4 border-b border-stone-200 dark:border-stone-800">
        <h2 class="text-xl font-bold text-theme-heading">Payment & Center Settings</h2>
        <p class="text-xs text-theme-muted">Configure your payment gateway (Xendit vs Manual Receipt) and center operating hours.</p>
    </div>

    <form id="venueSettingsForm" action="{{ route('owner.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8 text-xs">
        @csrf
        @method('PUT')

        @if(isset($errors) && $errors->any())
            <div class="p-5 rounded-2xl bg-rose-500/15 border-2 border-rose-500/30 text-rose-950 dark:text-rose-200 shadow-lg space-y-2">
                <div class="flex items-center gap-2.5 font-black text-sm text-rose-800 dark:text-rose-300">
                    <i class="fa-solid fa-triangle-exclamation text-base"></i>
                    <span>Some settings could not be saved. Please correct the highlighted issues:</span>
                </div>
                <ul class="list-disc list-inside text-xs space-y-1 text-rose-800 dark:text-rose-300 pl-1 font-medium">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $currentPaymentMode = old('payment_mode', $settings->payment_mode);
            $currentHoldingSeconds = (int) old('holding_duration_seconds', $settings->holding_duration_seconds ?: 120);
            $currentHoldMins = max(1, (int) round($currentHoldingSeconds / 60));
            $currentCreditsEnabled = old('credits_enabled', $settings->credits_enabled ? '1' : '0');
        @endphp

        <!-- PAYMENT MODE TOGGLE (Req #3 & #5) -->
        <div class="rounded-3xl glass-panel border border-cyan-500/30 p-6 sm:p-8 shadow-xl space-y-6">
            <div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-widest bg-cyan-500/15 text-cyan-700 dark:text-cyan-300 border border-cyan-500/30">
                    Payment Gateway Selection
                </span>
                <h3 class="text-lg font-extrabold text-theme-heading mt-2">Pickleball Reservation Payment Mode</h3>
                <p class="text-theme-muted text-xs mt-1">
                    Choose how clients pay for court bookings. You can switch modes at any time.
                </p>
            </div>

            <!-- Radio Selection Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Option 1: Manual Receipt Mode (Crunch Tan Tone) -->
                <label id="paymentCard-manual_receipt" class="payment-mode-card payment-mode-amber p-5 rounded-2xl cursor-pointer flex flex-col justify-between space-y-4 {{ $currentPaymentMode === 'manual_receipt' ? 'is-active' : '' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-1">
                            <div class="mode-icon-box w-11 h-11 rounded-xl bg-amber-500/20 text-amber-700 dark:text-amber-400 flex items-center justify-center text-lg mb-2 transition-all">
                                <i class="fa-solid fa-file-invoice"></i>
                            </div>
                            <h4 class="font-extrabold text-theme-heading text-sm">Manual Receipt Upload</h4>
                            <p class="text-theme-muted text-[11px] leading-relaxed">
                                Client scans your GCash / Maya QR Standee, then uploads payment screenshot. Owner reviews & approves manually.
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-1 shrink-0">
                            <div class="mode-indicator w-6 h-6 rounded-full border-2 border-stone-300 dark:border-stone-600 flex items-center justify-center transition-all">
                                <i class="fa-solid fa-check text-[10px] mode-check-icon"></i>
                            </div>
                            <input type="radio" name="payment_mode" value="manual_receipt" {{ $currentPaymentMode === 'manual_receipt' ? 'checked' : '' }} onchange="selectPaymentMode('manual_receipt')" class="sr-only">
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-1">
                        <span class="text-[10px] font-bold text-amber-800 dark:text-amber-300 bg-amber-500/15 px-2.5 py-1 rounded-lg border border-amber-500/25">
                            Manual Verification
                        </span>
                        <span class="mode-active-pill text-[10px] font-black tracking-wider uppercase bg-amber-500 text-slate-950 px-2 py-0.5 rounded-md shadow-xs">
                            Active Engine
                        </span>
                        <span class="mode-select-pill text-[10px] font-semibold text-stone-500 dark:text-stone-400 bg-stone-200/50 dark:bg-stone-800/50 px-2 py-0.5 rounded-md">
                            Select
                        </span>
                    </div>
                </label>

                <!-- Option 2: Xendit API Mode (Dreamland Cyan Tone) -->
                <label id="paymentCard-xendit" class="payment-mode-card payment-mode-cyan p-5 rounded-2xl cursor-pointer flex flex-col justify-between space-y-4 {{ $currentPaymentMode === 'xendit' ? 'is-active' : '' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-1">
                            <div class="mode-icon-box w-11 h-11 rounded-xl bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-lg mb-2 transition-all">
                                <i class="fa-solid fa-bolt"></i>
                            </div>
                            <h4 class="font-extrabold text-theme-heading text-sm">Xendit API (Auto-Reserve)</h4>
                            <p class="text-theme-muted text-[11px] leading-relaxed">
                                Automatically confirms bookings when paid. Slots held for <strong>{{ $currentHoldMins }} minute{{ $currentHoldMins > 1 ? 's' : '' }}</strong> during checkout.
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-1 shrink-0">
                            <div class="mode-indicator w-6 h-6 rounded-full border-2 border-stone-300 dark:border-stone-600 flex items-center justify-center transition-all">
                                <i class="fa-solid fa-check text-[10px] mode-check-icon"></i>
                            </div>
                            <input type="radio" name="payment_mode" value="xendit" {{ $currentPaymentMode === 'xendit' ? 'checked' : '' }} onchange="selectPaymentMode('xendit')" class="sr-only">
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-1">
                        <span class="text-[10px] font-bold text-cyan-700 dark:text-cyan-300 bg-cyan-500/15 px-2.5 py-1 rounded-lg border border-cyan-500/25">
                            {{ $currentHoldMins }}-Min Hold & Instant Confirm
                        </span>
                        <span class="mode-active-pill text-[10px] font-black tracking-wider uppercase bg-cyan-400 text-slate-950 px-2 py-0.5 rounded-md shadow-xs">
                            Active Engine
                        </span>
                        <span class="mode-select-pill text-[10px] font-semibold text-stone-500 dark:text-stone-400 bg-stone-200/50 dark:bg-stone-800/50 px-2 py-0.5 rounded-md">
                            Select
                        </span>
                    </div>
                </label>

                <!-- Option 3: PayMongo API Mode (Teal / Emerald Tone) -->
                <label id="paymentCard-paymongo" class="payment-mode-card payment-mode-emerald p-5 rounded-2xl cursor-pointer flex flex-col justify-between space-y-4 {{ $currentPaymentMode === 'paymongo' ? 'is-active' : '' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-1">
                            <div class="mode-icon-box w-11 h-11 rounded-xl bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg mb-2 transition-all">
                                <i class="fa-solid fa-money-bill-wave"></i>
                            </div>
                            <h4 class="font-extrabold text-theme-heading text-sm">PayMongo API (Auto-Reserve)</h4>
                            <p class="text-theme-muted text-[11px] leading-relaxed">
                                Auto-confirms bookings via PayMongo GCash, Maya, QRPH, and Cards. Slots held for <strong>{{ $currentHoldMins }} minute{{ $currentHoldMins > 1 ? 's' : '' }}</strong>.
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-1 shrink-0">
                            <div class="mode-indicator w-6 h-6 rounded-full border-2 border-stone-300 dark:border-stone-600 flex items-center justify-center transition-all">
                                <i class="fa-solid fa-check text-[10px] mode-check-icon"></i>
                            </div>
                            <input type="radio" name="payment_mode" value="paymongo" {{ $currentPaymentMode === 'paymongo' ? 'checked' : '' }} onchange="selectPaymentMode('paymongo')" class="sr-only">
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-1">
                        <span class="text-[10px] font-bold text-emerald-800 dark:text-emerald-300 bg-emerald-500/15 px-2.5 py-1 rounded-lg border border-emerald-500/25">
                            {{ $currentHoldMins }}-Min Checkout
                        </span>
                        <span class="mode-active-pill text-[10px] font-black tracking-wider uppercase bg-emerald-500 text-slate-950 px-2 py-0.5 rounded-md shadow-xs">
                            Active Engine
                        </span>
                        <span class="mode-select-pill text-[10px] font-semibold text-stone-500 dark:text-stone-400 bg-stone-200/50 dark:bg-stone-800/50 px-2 py-0.5 rounded-md">
                            Select
                        </span>
                    </div>
                </label>
            </div>
            @error('payment_mode')
                <p class="text-rose-600 dark:text-rose-400 text-xs font-semibold mt-1 flex items-center gap-1">
                    <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                </p>
            @enderror

            <!-- Holding Time Setting (Default 2 mins, fully editable by Court Owner) -->
            <div class="pt-4 border-t border-stone-200 dark:border-stone-800 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <label class="block font-bold text-theme-heading text-sm">Checkout Slot Holding Time Window</label>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-700 dark:text-amber-400 border border-amber-500/30 uppercase">
                                Default: 2 Mins
                            </span>
                        </div>
                        <p class="text-[11px] text-theme-muted mt-0.5">
                            Duration the timeslot is locked exclusively for the player during checkout (PayMongo & Xendit) before auto-releasing back to schedule.
                        </p>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <div class="relative">
                            <input type="number" id="holdingDurationInput" name="holding_duration_seconds" value="{{ $currentHoldingSeconds }}" min="30" max="600" step="10" required
                                class="w-28 px-3 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-mono font-bold text-center focus:border-cyan-500 text-sm">
                        </div>
                        <span class="text-xs text-theme-body font-semibold">seconds</span>
                        <span id="holdingMinutesBadge" class="px-2.5 py-1 rounded-lg bg-stone-200 dark:bg-stone-800 text-xs font-mono font-bold text-cyan-700 dark:text-cyan-400 border border-stone-300 dark:border-stone-700">
                            {{ round($currentHoldingSeconds / 60, 1) }} mins
                        </span>
                    </div>
                </div>
                @error('holding_duration_seconds')
                    <p class="text-rose-600 dark:text-rose-400 text-xs font-semibold mt-1">
                        <i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}
                    </p>
                @enderror

                <!-- Quick Presets for Owner Convenience -->
                <div class="flex items-center gap-2 pt-1 flex-wrap">
                    <span class="text-[11px] text-theme-muted font-medium">Quick Presets:</span>
                    <button type="button" onclick="setHoldingTime(60)" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-stone-200 dark:bg-stone-800 hover:bg-cyan-500 hover:text-slate-950 text-theme-heading border border-stone-300 dark:border-stone-700 transition-colors cursor-pointer">
                        1 Min (60s)
                    </button>
                    <button type="button" onclick="setHoldingTime(120)" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-stone-200 dark:bg-stone-800 hover:bg-cyan-500 hover:text-slate-950 text-theme-heading border border-stone-300 dark:border-stone-700 transition-colors cursor-pointer">
                        2 Mins (120s) - Default
                    </button>
                    <button type="button" onclick="setHoldingTime(180)" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-stone-200 dark:bg-stone-800 hover:bg-cyan-500 hover:text-slate-950 text-theme-heading border border-stone-300 dark:border-stone-700 transition-colors cursor-pointer">
                        3 Mins (180s)
                    </button>
                    <button type="button" onclick="setHoldingTime(300)" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-stone-200 dark:bg-stone-800 hover:bg-cyan-500 hover:text-slate-950 text-theme-heading border border-stone-300 dark:border-stone-700 transition-colors cursor-pointer">
                        5 Mins (300s)
                    </button>
                </div>
            </div>

            <!-- Court Credits for Booking Payment Config (Enable / Disable) -->
            <div class="pt-5 border-t border-stone-200 dark:border-stone-800">
                <div class="p-5 rounded-2xl bg-amber-500/10 border-2 border-amber-500/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm font-bold">
                                <i class="fa-solid fa-coins"></i>
                            </div>
                            <h4 class="font-extrabold text-theme-heading text-sm">Court Credits Booking Option</h4>
                            <span id="creditsConfigBadge" class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $currentCreditsEnabled == '1' ? 'bg-emerald-500/20 text-emerald-800 dark:text-emerald-300 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-800 dark:text-rose-300 border border-rose-500/30' }}">
                                {{ $currentCreditsEnabled == '1' ? 'Active / Enabled' : 'Disabled (Gateway Only)' }}
                            </span>
                        </div>
                        <p class="text-theme-muted text-[11px] leading-relaxed max-w-xl">
                            Choose whether registered players can use their pre-paid court credits to book courts at checkout. When disabled, bookings can only be paid via Gateway / Cash (PayMongo, Xendit, or Manual GCash).
                        </p>
                    </div>

                    <!-- Explicit Segmented Switch for Credits Booking (Yes / No) -->
                    <div class="flex items-center gap-2 bg-white dark:bg-stone-900 p-1.5 rounded-2xl border border-stone-300 dark:border-stone-700 shadow-sm shrink-0">
                        <label id="creditsBtnYes" class="px-3.5 py-2 rounded-xl text-xs font-black cursor-pointer transition-all flex items-center gap-1.5 {{ $currentCreditsEnabled == '1' ? 'bg-amber-500 text-slate-950 shadow-md' : 'text-theme-muted hover:text-theme-heading' }}">
                            <input type="radio" name="credits_enabled" value="1" {{ $currentCreditsEnabled == '1' ? 'checked' : '' }} class="sr-only" onchange="updateCreditsToggle(true)">
                            <i class="fa-solid fa-check"></i>
                            <span>Allow Credits</span>
                        </label>
                        <label id="creditsBtnNo" class="px-3.5 py-2 rounded-xl text-xs font-black cursor-pointer transition-all flex items-center gap-1.5 {{ $currentCreditsEnabled == '0' ? 'bg-rose-500 text-white shadow-md' : 'text-theme-muted hover:text-theme-heading' }}" >
                            <input type="radio" name="credits_enabled" value="0" {{ $currentCreditsEnabled == '0' ? 'checked' : '' }} class="sr-only" onchange="updateCreditsToggle(false)">
                            <i class="fa-solid fa-ban"></i>
                            <span>Disallow Credits</span>
                        </label>
                    </div>
                </div>
                @error('credits_enabled')
                    <p class="text-rose-600 dark:text-rose-400 text-xs font-semibold mt-1">
                        <i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}
                    </p>
                @enderror
            </div>
        </div>

        <!-- MANUAL RECEIPT SETTINGS -->
        <div class="rounded-3xl glass-panel border border-stone-200 dark:border-stone-800 p-6 sm:p-8 shadow-xl space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-stone-200 dark:border-stone-800">
                <div>
                    <h3 class="text-base font-bold text-theme-heading flex items-center gap-2">
                        <i class="fa-solid fa-qrcode text-amber-600 dark:text-amber-400"></i> Manual Receipt / GCash Details
                    </h3>
                    <p class="text-theme-muted text-xs mt-0.5">Details presented to the client when booking in Manual Receipt mode.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Bank / E-Wallet Name</label>
                    <input type="text" name="manual_bank_name" value="{{ old('manual_bank_name', $settings->manual_bank_name) }}" placeholder="GCash / Maya / BDO"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:border-cyan-500">
                    @error('manual_bank_name')
                        <p class="text-rose-600 dark:text-rose-400 text-[11px] font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Account Holder Name</label>
                    <input type="text" name="manual_account_name" value="{{ old('manual_account_name', $settings->manual_account_name) }}" placeholder="Paddle Field Sports Center"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:border-cyan-500">
                    @error('manual_account_name')
                        <p class="text-rose-600 dark:text-rose-400 text-[11px] font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Account / Mobile Number</label>
                    <input type="text" name="manual_account_number" value="{{ old('manual_account_number', $settings->manual_account_number) }}" placeholder="0917-555-7233"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-mono focus:border-cyan-500">
                    @error('manual_account_number')
                        <p class="text-rose-600 dark:text-rose-400 text-[11px] font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block font-semibold text-theme-body mb-1">Payment Instructions for Client</label>
                <textarea name="manual_payment_instructions" rows="3"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:border-cyan-500">{{ old('manual_payment_instructions', $settings->manual_payment_instructions) }}</textarea>
                @error('manual_payment_instructions')
                    <p class="text-rose-600 dark:text-rose-400 text-[11px] font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-semibold text-theme-body mb-1">Payment QR Code Standee Image</label>
                <div class="flex items-center gap-4">
                    @if($settings->manual_payment_qr)
                        <img src="{{ asset('storage/' . $settings->manual_payment_qr) }}" alt="QR Code" class="w-16 h-16 rounded-xl object-contain border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 p-1">
                    @endif
                    <input type="file" name="manual_payment_qr" accept="image/*"
                        class="w-full text-xs text-theme-muted file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-cyan-500 file:text-slate-950 hover:file:bg-cyan-400 cursor-pointer">
                </div>
                @error('manual_payment_qr')
                    <p class="text-rose-600 dark:text-rose-400 text-[11px] font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- XENDIT API CONFIGURATION -->
        <div class="rounded-3xl glass-panel border border-stone-200 dark:border-stone-800 p-6 sm:p-8 shadow-xl space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-stone-200 dark:border-stone-800">
                <div>
                    <h3 class="text-base font-bold text-theme-heading flex items-center gap-2">
                        <i class="fa-solid fa-key text-cyan-600 dark:text-cyan-400"></i> Xendit API Credentials
                    </h3>
                    <p class="text-theme-muted text-xs mt-0.5">Used when Xendit API mode is enabled for automatic reservation checkout.</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Xendit Secret API Key</label>
                    <input type="password" name="xendit_secret_key" value="{{ old('xendit_secret_key', $settings->xendit_secret_key) }}" placeholder="xnd_development_... or xnd_production_..."
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-mono focus:border-cyan-500">
                    @error('xendit_secret_key')
                        <p class="text-rose-600 dark:text-rose-400 text-[11px] font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-theme-body mb-1">Xendit Public Key</label>
                        <input type="text" name="xendit_public_key" value="{{ old('xendit_public_key', $settings->xendit_public_key) }}" placeholder="xnd_public_..."
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-mono focus:border-cyan-500">
                        @error('xendit_public_key')
                            <p class="text-rose-600 dark:text-rose-400 text-[11px] font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block font-semibold text-theme-body mb-1">Webhook Verification Token</label>
                        <input type="password" name="xendit_webhook_token" value="{{ old('xendit_webhook_token', $settings->xendit_webhook_token) }}" placeholder="xendit_webhook_token"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-mono focus:border-cyan-500">
                        @error('xendit_webhook_token')
                            <p class="text-rose-600 dark:text-rose-400 text-[11px] font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="xendit_simulation_mode" id="simMode" value="1" {{ old('xendit_simulation_mode', $settings->xendit_simulation_mode) ? 'checked' : '' }} class="w-4 h-4 rounded text-cyan-500 bg-stone-100 dark:bg-stone-900 border-stone-300 dark:border-stone-700">
                    <label for="simMode" class="text-xs text-theme-body font-medium">
                        Enable Interactive Test Simulation Mode (Allows instant checkout testing without live API keys)
                    </label>
                </div>
                @error('xendit_simulation_mode')
                    <p class="text-rose-600 dark:text-rose-400 text-[11px] font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- PAYMONGO API CONFIGURATION -->
        <div class="rounded-3xl glass-panel border border-emerald-500/30 p-6 sm:p-8 shadow-xl space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-stone-200 dark:border-stone-800">
                <div>
                    <h3 class="text-base font-bold text-theme-heading flex items-center gap-2">
                        <i class="fa-solid fa-money-bill-wave text-emerald-600 dark:text-emerald-400"></i> PayMongo API Credentials
                    </h3>
                    <p class="text-theme-muted text-xs mt-0.5">Used when PayMongo API mode is enabled for automatic reservation checkout with GCash, Maya, and Cards.</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block font-semibold text-theme-body mb-1">PayMongo Secret Key</label>
                    <input type="password" name="paymongo_secret_key" value="{{ old('paymongo_secret_key', $settings->paymongo_secret_key) }}" placeholder="sk_test_... or sk_live_..."
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-mono focus:border-emerald-500">
                    @error('paymongo_secret_key')
                        <p class="text-rose-600 dark:text-rose-400 text-[11px] font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-theme-body mb-1">PayMongo Public Key</label>
                        <input type="text" name="paymongo_public_key" value="{{ old('paymongo_public_key', $settings->paymongo_public_key) }}" placeholder="pk_test_... or pk_live_..."
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-mono focus:border-emerald-500">
                        @error('paymongo_public_key')
                            <p class="text-rose-600 dark:text-rose-400 text-[11px] font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block font-semibold text-theme-body mb-1">Webhook Secret Key</label>
                        <input type="password" name="paymongo_webhook_token" value="{{ old('paymongo_webhook_token', $settings->paymongo_webhook_token) }}" placeholder="whsk_..."
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-mono focus:border-emerald-500">
                        @error('paymongo_webhook_token')
                            <p class="text-rose-600 dark:text-rose-400 text-[11px] font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="paymongo_simulation_mode" id="pmSimMode" value="1" {{ old('paymongo_simulation_mode', $settings->paymongo_simulation_mode) ? 'checked' : '' }} class="w-4 h-4 rounded text-emerald-500 bg-stone-100 dark:bg-stone-900 border-stone-300 dark:border-stone-700">
                    <label for="pmSimMode" class="text-xs text-theme-body font-medium">
                        Enable Interactive Test Simulation Mode (Allows instant PayMongo checkout testing without live API keys)
                    </label>
                </div>
                @error('paymongo_simulation_mode')
                    <p class="text-rose-600 dark:text-rose-400 text-[11px] font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- COURT CREDITS & WALLET SETTINGS -->
        <div class="rounded-3xl glass-panel border border-amber-500/30 p-6 sm:p-8 shadow-xl space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-stone-200 dark:border-stone-800 gap-2">
                <div>
                    <h3 class="text-base font-bold text-theme-heading flex items-center gap-2">
                        <i class="fa-solid fa-coins text-amber-500"></i> Court Credits & Wallet System
                    </h3>
                    <p class="text-theme-muted text-xs mt-0.5">Configure player pre-paid credits conversion rate and top-up rules.</p>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-amber-500/15 text-amber-800 dark:text-amber-300 border border-amber-500/30 self-start sm:self-auto">
                    Pre-paid Court Credits
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="space-y-2">
                    <label class="block font-bold text-theme-heading text-xs">
                        Credit Conversion Rate (₱ PHP per 1 Credit) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 font-bold text-theme-muted text-xs">₱</span>
                        <input type="number" step="any" min="1" max="10000" name="credit_conversion_rate" required value="{{ old('credit_conversion_rate', $settings->credit_conversion_rate ?: 150) }}"
                            class="w-full pl-8 pr-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-black text-sm focus:border-amber-500 focus:outline-none">
                    </div>
                    @error('credit_conversion_rate')
                        <p class="text-rose-600 dark:text-rose-400 text-[11px] font-semibold mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-[11px] text-theme-muted leading-relaxed">
                        Setting this to <strong>150</strong> means <strong>₱150.00 = 1 Court Credit</strong>. When a player purchases 2 credits, they will pay ₱300.00.
                    </p>
                </div>

                <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-xs text-theme-body flex flex-col justify-center space-y-1.5">
                    <div class="font-bold text-amber-800 dark:text-amber-300 flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-check"></i> How Credits Work
                    </div>
                    <p class="text-[11px] leading-relaxed text-theme-muted">
                        Players upload payment receipts (GCash/Maya/Bank) to purchase credits. Once you confirm and approve in the <strong>Approvals</strong> queue, credits are credited directly to their account, allowing instant court bookings without waiting for receipt checks!
                    </p>
                </div>
            </div>

            <div class="pt-3 border-t border-stone-200 dark:border-stone-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-theme-heading">Checkout Booking Status:</span>
                    <span id="creditsSystemPill" class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $currentCreditsEnabled == '1' ? 'bg-emerald-500/20 text-emerald-800 dark:text-emerald-300 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-800 dark:text-rose-300 border border-rose-500/30' }}">
                        {{ $currentCreditsEnabled == '1' ? 'Allowed for Court Booking' : 'Disallowed for Booking' }}
                    </span>
                </div>
                <span class="text-[11px] text-theme-muted">Configured in Payment Settings above</span>
            </div>
        </div>

        <!-- PICKLEBALL VENUE & COURT LOGO (Branding & Browser Tab Icon) -->
        <div class="rounded-3xl glass-panel border border-cyan-500/30 p-6 sm:p-8 shadow-xl space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-stone-200 dark:border-stone-800 gap-2">
                <div>
                    <h3 class="text-base font-bold text-theme-heading flex items-center gap-2">
                        <i class="fa-solid fa-image text-cyan-600 dark:text-cyan-400"></i> Pickleball Court & Venue Logo
                    </h3>
                    <p class="text-theme-muted text-xs mt-0.5">
                        Upload your official pickleball sports center logo. It will be displayed on the public navigation header, owner portal, printable booking passes, emails, and the browser tab icon (favicon).
                    </p>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-cyan-500/15 text-cyan-700 dark:text-cyan-300 border border-cyan-500/30 self-start sm:self-auto">
                    Venue Branding & Favicon
                </span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <!-- Preview Column (Left) -->
                <div class="lg:col-span-5 space-y-4">
                    <div class="p-5 rounded-2xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 space-y-4">
                        <div class="flex items-center justify-between text-xs font-bold text-theme-heading">
                            <span>Live Brand Previews</span>
                            <span class="text-[10px] font-semibold text-theme-muted">Real-time simulation</span>
                        </div>

                        <!-- 1. Simulated Browser Tab (Icon on top of tab) -->
                        <div class="space-y-1.5">
                            <span class="text-[11px] font-semibold text-theme-muted flex items-center gap-1.5">
                                <i class="fa-solid fa-window-maximize text-cyan-500"></i> Browser Tab Icon (Favicon):
                            </span>
                            <div class="w-full bg-stone-200 dark:bg-stone-950 p-2 rounded-xl flex items-center gap-2 border border-stone-300 dark:border-stone-800">
                                <div class="bg-white dark:bg-stone-800 px-3 py-1.5 rounded-lg flex items-center gap-2 border border-stone-300 dark:border-stone-700 shadow-xs max-w-full">
                                    <img id="simulatedTabIcon" src="{{ $settings->tab_icon_url }}" alt="Tab Icon" class="w-4 h-4 object-contain rounded-xs shrink-0">
                                    <span class="text-[11px] font-bold text-theme-heading truncate">{{ $settings->venue_name ?? 'Paddle Field' }} - Court...</span>
                                    <i class="fa-solid fa-xmark text-[9px] text-stone-400 ml-1"></i>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Header Brand Simulation -->
                        <div class="space-y-1.5">
                            <span class="text-[11px] font-semibold text-theme-muted flex items-center gap-1.5">
                                <i class="fa-solid fa-bars text-amber-500"></i> Navigation Header Logo:
                            </span>
                            <div class="p-3 bg-white dark:bg-stone-800/80 rounded-xl border border-stone-200 dark:border-stone-700 flex items-center gap-3">
                                <div id="logoPreviewContainer" class="w-12 h-12 rounded-2xl border border-stone-200 dark:border-stone-700 bg-stone-50 dark:bg-stone-900 flex items-center justify-center p-1.5 overflow-hidden shadow-xs shrink-0">
                                    @if($settings->has_custom_logo)
                                        <img id="logoPreviewImg" src="{{ $settings->logo_url }}" alt="Venue Logo" class="w-full h-full object-contain">
                                    @else
                                        <img id="logoPreviewImg" src="{{ asset('images/default-logo.svg') }}" alt="Default Logo" class="w-full h-full object-contain">
                                    @endif
                                </div>
                                <div class="truncate">
                                    <div class="text-xs font-black text-theme-heading leading-tight truncate">{{ $settings->venue_name ?? 'Paddle Field Sports Center' }}</div>
                                    <div class="text-[10px] text-theme-muted truncate">{{ $settings->tagline ?? 'Pickleball Arena & Club' }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Current status indicator -->
                        <div class="pt-2 border-t border-stone-200 dark:border-stone-800 flex items-center justify-between text-[11px]">
                            <span class="text-theme-muted">Logo Status:</span>
                            @if($settings->has_custom_logo)
                                <span class="px-2 py-0.5 rounded-full font-bold bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30">
                                    <i class="fa-solid fa-check text-[10px] mr-1"></i> Custom Logo Active
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full font-bold bg-stone-200 dark:bg-stone-800 text-stone-600 dark:text-stone-400">
                                    Default Pickleball Icon
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Upload Controls Column (Right) -->
                <div class="lg:col-span-7 space-y-4">
                    <div>
                        <label class="block font-bold text-theme-heading mb-1.5">
                            Upload Logo Image (PNG, JPG, WEBP, SVG, or ICO)
                        </label>
                        <p class="text-[11px] text-theme-muted mb-3 leading-relaxed">
                            Recommended: High-resolution PNG or SVG with transparent background, square or rectangular (minimum 256×256 pixels). Max file size: 4MB.
                        </p>

                        <!-- Custom File Input with Drag & Drop styling -->
                        <div class="relative">
                            <label for="venueLogoInput" class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-stone-300 dark:border-stone-700 hover:border-cyan-500 dark:hover:border-cyan-400 rounded-2xl bg-white dark:bg-stone-900 cursor-pointer transition-all group">
                                <div class="w-12 h-12 rounded-2xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-xl mb-2 group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                </div>
                                <span class="text-xs font-bold text-theme-heading group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition-colors">
                                    Click to select image or drag & drop here
                                </span>
                                <span id="venueLogoFileName" class="text-[11px] text-theme-muted mt-1">
                                    PNG, JPG, WEBP, SVG, or ICO up to 4MB
                                </span>
                            </label>
                            <input type="file" id="venueLogoInput" name="logo" accept="image/png,image/jpeg,image/webp,image/svg+xml,image/x-icon" onchange="previewVenueLogo(this)" class="sr-only">
                        </div>
                    </div>

                    <!-- Remove Logo Option (if custom logo already exists) -->
                    @if($settings->has_custom_logo)
                        <div class="p-3.5 rounded-2xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <i class="fa-solid fa-trash-can text-rose-500 text-sm shrink-0"></i>
                                <div class="text-[11px] text-rose-700 dark:text-rose-300">
                                    <span class="font-bold">Remove Custom Logo:</span> Reset back to default Paddle Field emblem.
                                </div>
                            </div>
                            <label class="inline-flex items-center gap-2 cursor-pointer shrink-0">
                                <input type="checkbox" name="remove_logo" value="1" id="removeLogoCheckbox" onchange="handleRemoveLogoToggle(this)" class="w-4 h-4 rounded text-rose-600 bg-white dark:bg-stone-900 border-stone-300 dark:border-stone-700 focus:ring-rose-500">
                                <span class="text-xs font-bold text-rose-600 dark:text-rose-400">Remove</span>
                            </label>
                        </div>
                    @endif

                    <!-- Where this logo is shown Info Checklist -->
                    <div class="p-4 rounded-2xl bg-cyan-500/5 border border-cyan-500/15 space-y-2">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-cyan-800 dark:text-cyan-300 flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-info"></i> Automatically Displayed On:
                        </span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px] text-theme-muted">
                            <div class="flex items-center gap-1.5"><i class="fa-solid fa-check text-cyan-600 text-[10px]"></i> Browser Tab Icon (Favicon)</div>
                            <div class="flex items-center gap-1.5"><i class="fa-solid fa-check text-cyan-600 text-[10px]"></i> Main Public Website Header</div>
                            <div class="flex items-center gap-1.5"><i class="fa-solid fa-check text-cyan-600 text-[10px]"></i> Owner & Staff Admin Hub</div>
                            <div class="flex items-center gap-1.5"><i class="fa-solid fa-check text-cyan-600 text-[10px]"></i> Printable Pass & Receipts</div>
                            <div class="flex items-center gap-1.5"><i class="fa-solid fa-check text-cyan-600 text-[10px]"></i> Sign-in & Authentication Screens</div>
                            <div class="flex items-center gap-1.5"><i class="fa-solid fa-check text-cyan-600 text-[10px]"></i> Booking & Email Confirmations</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- VENUE GENERAL INFO -->
        <div class="rounded-3xl glass-panel border border-stone-200 dark:border-stone-800 p-6 sm:p-8 shadow-xl space-y-5">
            <h3 class="text-base font-bold text-theme-heading flex items-center gap-2">
                <i class="fa-solid fa-building text-cyan-600 dark:text-cyan-400"></i> Center Information & Operating Hours
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Venue Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="venue_name" required value="{{ old('venue_name', $settings->venue_name) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:border-cyan-500">
                    @error('venue_name')
                        <p class="text-rose-600 dark:text-rose-400 text-[11px] font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Tagline</label>
                    <input type="text" name="tagline" value="{{ old('tagline', $settings->tagline) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:border-cyan-500">
                    @error('tagline')
                        <p class="text-rose-600 dark:text-rose-400 text-[11px] font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block font-semibold text-theme-body mb-1">About / Facility Description</label>
                <textarea name="description" rows="3" placeholder="Describe your facility amenities and pickleball courts..."
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:border-cyan-500">{{ old('description', $settings->description) }}</textarea>
                @error('description')
                    <p class="text-rose-600 dark:text-rose-400 text-[11px] font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Opening Time <span class="text-rose-500">*</span></label>
                    <input type="time" name="opening_time" value="{{ old('opening_time', $settings->opening_time) }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:border-cyan-500">
                    @error('opening_time')
                        <p class="text-rose-600 dark:text-rose-400 text-[11px] font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Closing Time <span class="text-rose-500">*</span></label>
                    <input type="time" name="closing_time" value="{{ old('closing_time', $settings->closing_time) }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:border-cyan-500">
                    @error('closing_time')
                        <p class="text-rose-600 dark:text-rose-400 text-[11px] font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $settings->phone) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:border-cyan-500">
                    @error('phone')
                        <p class="text-rose-600 dark:text-rose-400 text-[11px] font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $settings->email) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:border-cyan-500">
                    @error('email')
                        <p class="text-rose-600 dark:text-rose-400 text-[11px] font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block font-semibold text-theme-body mb-1">Physical Address</label>
                <input type="text" name="address" value="{{ old('address', $settings->address) }}"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:border-cyan-500">
                @error('address')
                    <p class="text-rose-600 dark:text-rose-400 text-[11px] font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Currency Code <span class="text-rose-500">*</span></label>
                    <input type="text" name="currency" value="{{ old('currency', $settings->currency) }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:border-cyan-500">
                    @error('currency')
                        <p class="text-rose-600 dark:text-rose-400 text-[11px] font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Currency Symbol <span class="text-rose-500">*</span></label>
                    <input type="text" name="currency_symbol" value="{{ old('currency_symbol', $settings->currency_symbol) }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:border-cyan-500">
                    @error('currency_symbol')
                        <p class="text-rose-600 dark:text-rose-400 text-[11px] font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-4 pt-4">
            <button type="submit" id="saveSettingsBtn" class="px-8 py-3.5 rounded-2xl bg-gradient-to-r from-cyan-500 to-cyan-600 hover:from-cyan-400 hover:to-cyan-500 text-slate-950 font-black text-sm shadow-xl shadow-cyan-500/25 transition-transform hover:scale-105 cursor-pointer flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Save All Settings</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function selectPaymentMode(mode) {
        const modes = ['manual_receipt', 'xendit', 'paymongo'];
        modes.forEach(m => {
            const card = document.getElementById(`paymentCard-${m}`);
            const radio = document.querySelector(`input[name="payment_mode"][value="${m}"]`);
            if (card) {
                if (m === mode) {
                    card.classList.add('is-active');
                    if (radio) radio.checked = true;
                } else {
                    card.classList.remove('is-active');
                }
            }
        });
    }

    function setHoldingTime(seconds) {
        const input = document.getElementById('holdingDurationInput');
        const badge = document.getElementById('holdingMinutesBadge');
        if (input) {
            input.value = seconds;
            if (badge) {
                badge.textContent = `${(seconds / 60).toFixed(1)} mins`;
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('holdingDurationInput');
        const badge = document.getElementById('holdingMinutesBadge');
        if (input && badge) {
            input.addEventListener('input', function() {
                const val = parseInt(this.value, 10);
                if (!isNaN(val) && val > 0) {
                    badge.textContent = `${(val / 60).toFixed(1)} mins`;
                }
            });
        }
    });

    function updateCreditsToggle(enabled) {
        const btnYes = document.getElementById('creditsBtnYes');
        const btnNo = document.getElementById('creditsBtnNo');
        const badge = document.getElementById('creditsConfigBadge');
        const pill = document.getElementById('creditsSystemPill');

        if (enabled) {
            btnYes.className = 'px-3.5 py-2 rounded-xl text-xs font-black cursor-pointer transition-all flex items-center gap-1.5 bg-amber-500 text-slate-950 shadow-md';
            btnNo.className = 'px-3.5 py-2 rounded-xl text-xs font-black cursor-pointer transition-all flex items-center gap-1.5 text-theme-muted hover:text-theme-heading';
            if (badge) {
                badge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-800 dark:text-emerald-300 border border-emerald-500/30';
                badge.textContent = 'Active / Enabled';
            }
            if (pill) {
                pill.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-800 dark:text-emerald-300 border border-emerald-500/30';
                pill.textContent = 'Allowed for Court Booking';
            }
        } else {
            btnNo.className = 'px-3.5 py-2 rounded-xl text-xs font-black cursor-pointer transition-all flex items-center gap-1.5 bg-rose-500 text-white shadow-md';
            btnYes.className = 'px-3.5 py-2 rounded-xl text-xs font-black cursor-pointer transition-all flex items-center gap-1.5 text-theme-muted hover:text-theme-heading';
            if (badge) {
                badge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-500/20 text-rose-800 dark:text-rose-300 border border-rose-500/30';
                badge.textContent = 'Disabled (Gateway Only)';
            }
            if (pill) {
                pill.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-500/20 text-rose-800 dark:text-rose-300 border border-rose-500/30';
                pill.textContent = 'Disallowed for Booking';
            }
        }
    }

    function previewVenueLogo(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const fileNameDisplay = document.getElementById('venueLogoFileName');
            if (fileNameDisplay) {
                fileNameDisplay.textContent = `Selected: ${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
                fileNameDisplay.classList.add('text-cyan-600', 'font-bold');
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const previewImg = document.getElementById('logoPreviewImg');
                const tabIcon = document.getElementById('simulatedTabIcon');
                if (previewImg) previewImg.src = e.target.result;
                if (tabIcon) tabIcon.src = e.target.result;
            };
            reader.readAsDataURL(file);

            // If user selects a new file, uncheck the remove checkbox
            const removeCheckbox = document.getElementById('removeLogoCheckbox');
            if (removeCheckbox) removeCheckbox.checked = false;
        }
    }

    function handleRemoveLogoToggle(checkbox) {
        const previewImg = document.getElementById('logoPreviewImg');
        const tabIcon = document.getElementById('simulatedTabIcon');
        const defaultLogoUrl = "{{ asset('images/default-logo.svg') }}";
        const currentLogoUrl = "{{ $settings->logo_url }}";

        if (checkbox.checked) {
            if (previewImg) previewImg.src = defaultLogoUrl;
            if (tabIcon) tabIcon.src = defaultLogoUrl;
        } else {
            if (previewImg) previewImg.src = currentLogoUrl || defaultLogoUrl;
            if (tabIcon) tabIcon.src = currentLogoUrl || defaultLogoUrl;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('venueSettingsForm');
        const submitBtn = document.getElementById('saveSettingsBtn');

        if (form && submitBtn) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                // Native constraint check
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                const originalBtnHtml = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Saving Settings...</span>';

                try {
                    const formData = new FormData(form);
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || form.querySelector('input[name="_token"]')?.value;

                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: formData
                    });

                    const data = await response.json().catch(() => null);

                    if (response.ok && data && data.success) {
                        if (typeof Swal !== 'undefined') {
                            await Swal.fire({
                                icon: 'success',
                                title: 'Settings Saved!',
                                text: data.message || 'Settings updated successfully.',
                                confirmButtonColor: '#06b6d4',
                                confirmButtonText: 'Great'
                            });
                        }
                        // Update live previews
                        if (data.settings) {
                            const tabIcon = document.getElementById('simulatedTabIcon');
                            const logoPreview = document.getElementById('logoPreviewImg');
                            if (tabIcon && data.settings.tab_icon_url) tabIcon.src = data.settings.tab_icon_url;
                            if (logoPreview && data.settings.logo_url) logoPreview.src = data.settings.logo_url;
                        }
                        // Refresh to sync layout topbar & sidebar
                        window.location.reload();
                    } else if (response.status === 422 && data && data.errors) {
                        const errorList = Object.values(data.errors).flat();
                        let errorHtml = '<div style="text-align: left; font-size: 13px; line-height: 1.6;">' +
                            '<p style="margin-bottom: 8px; font-weight: 600;">Please correct the highlighted issues:</p>' +
                            '<ul style="padding-left: 20px; margin: 0;">';
                        errorList.forEach(err => {
                            errorHtml += `<li>${err}</li>`;
                        });
                        errorHtml += '</ul></div>';

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Settings Not Saved',
                                html: errorHtml,
                                confirmButtonColor: '#e11d48',
                                confirmButtonText: 'Review Form'
                            });
                        } else {
                            alert('Settings not saved:\n' + errorList.join('\n'));
                        }
                    } else {
                        const errorMsg = (data && data.message) ? data.message : `Server responded with status ${response.status}`;
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error Saving Settings',
                                text: errorMsg,
                                confirmButtonColor: '#e11d48',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert('Error: ' + errorMsg);
                        }
                    }
                } catch (err) {
                    console.error('Settings submit error:', err);
                    // Fall back to native submission
                    form.submit();
                    return;
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                    submitBtn.innerHTML = originalBtnHtml;
                }
            });
        }
    });
</script>

@if(isset($errors) && $errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Settings Not Saved',
                html: '<div style="text-align: left; font-size: 13px; line-height: 1.6;">' +
                      '<p style="margin-bottom: 8px; font-weight: 600;">Please fix the following validation issue(s):</p>' +
                      '<ul style="padding-left: 20px; margin: 0;">' +
                      @foreach($errors->all() as $error)
                          '<li>{{ addslashes($error) }}</li>' +
                      @endforeach
                      '</ul></div>',
                confirmButtonColor: '#06b6d4',
                confirmButtonText: 'Review Form'
            });
        }
    });
</script>
@endif
@endpush
