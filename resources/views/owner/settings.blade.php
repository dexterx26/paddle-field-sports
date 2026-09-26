@extends('layouts.owner')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div class="pb-4 border-b border-stone-200 dark:border-stone-800">
        <h2 class="text-xl font-bold text-theme-heading">Payment & Center Settings</h2>
        <p class="text-xs text-theme-muted">Configure your payment gateway (Xendit vs Manual Receipt) and center operating hours.</p>
    </div>

    <form action="{{ route('owner.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8 text-xs">
        @csrf
        @method('PUT')

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
                <label class="p-5 rounded-2xl border-2 cursor-pointer transition-all flex flex-col justify-between space-y-4
                    {{ $settings->payment_mode === 'manual_receipt' ? 'border-amber-500 bg-amber-500/10' : 'border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900/60 hover:border-amber-400' }}">
                    <div class="flex items-start justify-between">
                        <div class="space-y-1">
                            <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-700 dark:text-amber-400 flex items-center justify-center text-lg mb-2">
                                <i class="fa-solid fa-file-invoice"></i>
                            </div>
                            <h4 class="font-extrabold text-theme-heading text-sm">Manual Receipt Upload</h4>
                            <p class="text-theme-muted text-[11px] leading-relaxed">
                                Client scans your GCash / Maya QR Standee, then uploads payment screenshot. Owner reviews & approves manually.
                            </p>
                        </div>
                        <input type="radio" name="payment_mode" value="manual_receipt" {{ $settings->payment_mode === 'manual_receipt' ? 'checked' : '' }} class="mt-1 text-cyan-500 focus:ring-0">
                    </div>
                    <span class="text-[10px] font-bold text-amber-800 dark:text-amber-300 bg-amber-500/15 px-2 py-1 rounded-lg border border-amber-500/25 self-start">
                        Manual Verification
                    </span>
                </label>

                <!-- Option 2: Xendit API Mode (Dreamland Cyan Tone) -->
                <label class="p-5 rounded-2xl border-2 cursor-pointer transition-all flex flex-col justify-between space-y-4
                    {{ $settings->payment_mode === 'xendit' ? 'border-cyan-500 bg-cyan-500/10' : 'border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900/60 hover:border-cyan-400' }}">
                    <div class="flex items-start justify-between">
                        <div class="space-y-1">
                            <div class="w-10 h-10 rounded-xl bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-lg mb-2">
                                <i class="fa-solid fa-bolt"></i>
                            </div>
                            <h4 class="font-extrabold text-theme-heading text-sm">Xendit API (Auto-Reserve)</h4>
                            <p class="text-theme-muted text-[11px] leading-relaxed">
                                Automatically confirms bookings when paid. Slots held for <strong>2 minutes</strong> during checkout.
                            </p>
                        </div>
                        <input type="radio" name="payment_mode" value="xendit" {{ $settings->payment_mode === 'xendit' ? 'checked' : '' }} class="mt-1 text-cyan-500 focus:ring-0">
                    </div>
                    <span class="text-[10px] font-bold text-cyan-700 dark:text-cyan-300 bg-cyan-500/15 px-2 py-1 rounded-lg border border-cyan-500/25 self-start">
                        2-Min Hold & Instant Confirm
                    </span>
                </label>

                <!-- Option 3: PayMongo API Mode (Teal / Emerald Tone) -->
                <label class="p-5 rounded-2xl border-2 cursor-pointer transition-all flex flex-col justify-between space-y-4
                    {{ $settings->payment_mode === 'paymongo' ? 'border-emerald-500 bg-emerald-500/10' : 'border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900/60 hover:border-emerald-400' }}">
                    <div class="flex items-start justify-between">
                        <div class="space-y-1">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg mb-2">
                                <i class="fa-solid fa-money-bill-wave"></i>
                            </div>
                            <h4 class="font-extrabold text-theme-heading text-sm">PayMongo API (Auto-Reserve)</h4>
                            <p class="text-theme-muted text-[11px] leading-relaxed">
                                Auto-confirms bookings via PayMongo GCash, Maya, QRPH, and Cards. Slots held for <strong>2 minutes</strong>.
                            </p>
                        </div>
                        <input type="radio" name="payment_mode" value="paymongo" {{ $settings->payment_mode === 'paymongo' ? 'checked' : '' }} class="mt-1 text-emerald-500 focus:ring-0">
                    </div>
                    <span class="text-[10px] font-bold text-emerald-800 dark:text-emerald-300 bg-emerald-500/15 px-2 py-1 rounded-lg border border-emerald-500/25 self-start">
                        PayMongo Checkout
                    </span>
                </label>
            </div>

            <!-- Holding Time Setting (Req #5) -->
            <div class="pt-4 border-t border-stone-200 dark:border-stone-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <label class="block font-bold text-theme-heading text-xs">Slot Holding Time Window (Seconds)</label>
                    <p class="text-[11px] text-theme-muted">Duration the timeslot is held for the client during Xendit checkout (Default: 120 seconds = 2 mins).</p>
                </div>
                <div class="flex items-center gap-2">
                    <input type="number" name="holding_duration_seconds" value="{{ $settings->holding_duration_seconds }}" min="30" max="600"
                        class="w-24 px-3 py-2 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-mono font-bold text-center focus:border-cyan-500">
                    <span class="text-theme-muted text-xs font-semibold">seconds (2 mins)</span>
                </div>
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
                    <input type="text" name="manual_bank_name" value="{{ $settings->manual_bank_name }}" placeholder="GCash / Maya / BDO"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:border-cyan-500">
                </div>
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Account Holder Name</label>
                    <input type="text" name="manual_account_name" value="{{ $settings->manual_account_name }}" placeholder="Paddle Field Sports Center"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:border-cyan-500">
                </div>
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Account / Mobile Number</label>
                    <input type="text" name="manual_account_number" value="{{ $settings->manual_account_number }}" placeholder="0917-555-7233"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-mono focus:border-cyan-500">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-theme-body mb-1">Payment Instructions for Client</label>
                <textarea name="manual_payment_instructions" rows="3"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:border-cyan-500">{{ $settings->manual_payment_instructions }}</textarea>
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
                    <input type="password" name="xendit_secret_key" value="{{ $settings->xendit_secret_key }}" placeholder="xnd_development_... or xnd_production_..."
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-mono focus:border-cyan-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-theme-body mb-1">Xendit Public Key</label>
                        <input type="text" name="xendit_public_key" value="{{ $settings->xendit_public_key }}" placeholder="xnd_public_..."
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-mono focus:border-cyan-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-theme-body mb-1">Webhook Verification Token</label>
                        <input type="password" name="xendit_webhook_token" value="{{ $settings->xendit_webhook_token }}" placeholder="xendit_webhook_token"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-mono focus:border-cyan-500">
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="xendit_simulation_mode" id="simMode" value="1" {{ $settings->xendit_simulation_mode ? 'checked' : '' }} class="w-4 h-4 rounded text-cyan-500 bg-stone-100 dark:bg-stone-900 border-stone-300 dark:border-stone-700">
                    <label for="simMode" class="text-xs text-theme-body font-medium">
                        Enable Interactive Test Simulation Mode (Allows instant checkout testing without live API keys)
                    </label>
                </div>
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
                    <input type="password" name="paymongo_secret_key" value="{{ $settings->paymongo_secret_key }}" placeholder="sk_test_... or sk_live_..."
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-mono focus:border-emerald-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-theme-body mb-1">PayMongo Public Key</label>
                        <input type="text" name="paymongo_public_key" value="{{ $settings->paymongo_public_key }}" placeholder="pk_test_... or pk_live_..."
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-mono focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-theme-body mb-1">Webhook Secret Key</label>
                        <input type="password" name="paymongo_webhook_token" value="{{ $settings->paymongo_webhook_token }}" placeholder="whsk_..."
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-mono focus:border-emerald-500">
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="paymongo_simulation_mode" id="pmSimMode" value="1" {{ $settings->paymongo_simulation_mode ? 'checked' : '' }} class="w-4 h-4 rounded text-emerald-500 bg-stone-100 dark:bg-stone-900 border-stone-300 dark:border-stone-700">
                    <label for="pmSimMode" class="text-xs text-theme-body font-medium">
                        Enable Interactive Test Simulation Mode (Allows instant PayMongo checkout testing without live API keys)
                    </label>
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
                    <input type="text" name="venue_name" required value="{{ $settings->venue_name }}"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:border-cyan-500">
                </div>
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Tagline</label>
                    <input type="text" name="tagline" value="{{ $settings->tagline }}"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:border-cyan-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Opening Time</label>
                    <input type="time" name="opening_time" value="{{ $settings->opening_time }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:border-cyan-500">
                </div>
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Closing Time</label>
                    <input type="time" name="closing_time" value="{{ $settings->closing_time }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:border-cyan-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Phone Number</label>
                    <input type="text" name="phone" value="{{ $settings->phone }}"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:border-cyan-500">
                </div>
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ $settings->email }}"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:border-cyan-500">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-theme-body mb-1">Physical Address</label>
                <input type="text" name="address" value="{{ $settings->address }}"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:border-cyan-500">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Currency Code</label>
                    <input type="text" name="currency" value="{{ $settings->currency }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:border-cyan-500">
                </div>
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Currency Symbol</label>
                    <input type="text" name="currency_symbol" value="{{ $settings->currency_symbol }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:border-cyan-500">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-4 pt-4">
            <button type="submit" class="px-8 py-3.5 rounded-2xl bg-gradient-to-r from-cyan-500 to-cyan-600 hover:from-cyan-400 hover:to-cyan-500 text-slate-950 font-black text-sm shadow-xl shadow-cyan-500/25 transition-transform hover:scale-105 cursor-pointer">
                <i class="fa-solid fa-floppy-disk mr-1.5"></i> Save All Settings
            </button>
        </div>
    </form>
</div>
@endsection
