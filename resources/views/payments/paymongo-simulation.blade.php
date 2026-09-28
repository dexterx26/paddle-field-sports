@extends('layouts.app')

@section('content')
<div class="py-12 px-4 sm:px-6 lg:px-8 max-w-xl mx-auto">
    <!-- Header -->
    <div class="text-center mb-8 space-y-2">
        <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl mx-auto border border-emerald-500/30">
            <i class="fa-solid fa-money-bill-wave"></i>
        </div>
        <span class="text-[11px] font-extrabold uppercase tracking-widest text-emerald-700 dark:text-emerald-300 bg-emerald-500/10 px-3 py-1 rounded-full border border-emerald-500/20 inline-block">
            PayMongo Secure Checkout Simulation
        </span>
        <h2 class="text-2xl font-black text-theme-heading">PayMongo Gateway</h2>
        <p class="text-xs text-theme-muted">Paddle Field Sports Center Official Checkout</p>
    </div>

    <!-- Main Checkout Container -->
    <div class="glass-panel rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6 relative overflow-hidden border border-emerald-500/30">
        <!-- Top Green Accent Line -->
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-emerald-500 via-teal-500 to-cyan-500"></div>

        <!-- 2-Minute Timer Pill -->
        <div class="p-3.5 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-stopwatch text-emerald-600 dark:text-emerald-400 animate-spin text-sm"></i>
                <span class="text-xs text-theme-body font-medium">Holding Time Remaining:</span>
            </div>
            <div class="font-mono font-black text-theme-heading text-base" id="paymongoSimTimer" data-remaining="{{ $booking->remaining_hold_seconds }}">
                {{ sprintf('%02d:%02d', floor($booking->remaining_hold_seconds / 60), $booking->remaining_hold_seconds % 60) }}
            </div>
        </div>

        <!-- Order Breakdown -->
        <div class="p-4 rounded-2xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 text-xs space-y-2.5">
            <div class="flex justify-between">
                <span class="text-theme-muted">Merchant:</span>
                <span class="font-bold text-theme-heading">Paddle Field Sports Center</span>
            </div>
            <div class="flex justify-between">
                <span class="text-theme-muted">Reservation Reference:</span>
                <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">{{ $booking->booking_reference }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-theme-muted">Court:</span>
                <span class="font-semibold text-theme-heading">{{ $booking->court->name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-theme-muted">Schedule:</span>
                <span class="font-semibold text-theme-body">{{ $booking->booking_date->format('M d, Y') }} ({{ date('g:i A', strtotime($booking->start_time)) }} - {{ date('g:i A', strtotime($booking->end_time)) }})</span>
            </div>
            <div class="pt-2 border-t border-stone-200 dark:border-stone-800 flex justify-between text-sm">
                <span class="font-bold text-theme-heading">Total Amount Due:</span>
                <span class="font-black text-emerald-600 dark:text-emerald-400 text-lg">{{ $booking->formatted_amount }}</span>
            </div>
        </div>

        <!-- Payment Channel Tabs -->
        <div>
            <label class="block text-xs font-bold text-theme-body mb-2">Select PayMongo Channel</label>
            <div class="grid grid-cols-3 gap-2 text-xs">
                <label class="p-3 rounded-xl border-2 border-emerald-500 bg-emerald-500/10 flex flex-col items-center justify-center gap-1.5 cursor-pointer text-center font-bold text-emerald-700 dark:text-emerald-300">
                    <input type="radio" name="payment_channel" value="GCash PayMongo" checked class="hidden">
                    <i class="fa-solid fa-mobile-screen text-lg"></i>
                    <span>GCash</span>
                </label>
                <label class="p-3 rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 hover:border-emerald-500 flex flex-col items-center justify-center gap-1.5 cursor-pointer text-center font-bold text-theme-body">
                    <input type="radio" name="payment_channel" value="Maya PayMongo" class="hidden">
                    <i class="fa-solid fa-wallet text-lg"></i>
                    <span>Maya</span>
                </label>
                <label class="p-3 rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 hover:border-emerald-500 flex flex-col items-center justify-center gap-1.5 cursor-pointer text-center font-bold text-theme-body">
                    <input type="radio" name="payment_channel" value="Cards QRPH PayMongo" class="hidden">
                    <i class="fa-solid fa-credit-card text-lg"></i>
                    <span>Card / QRPH</span>
                </label>
            </div>
        </div>

        <!-- Checkout Actions -->
        <div class="space-y-3">
            @if(!empty($booking->paymongo_payment_url) && str_starts_with($booking->paymongo_payment_url, 'http') && !str_contains($booking->paymongo_payment_url, 'paymongo-checkout'))
                <a href="{{ $booking->paymongo_payment_url }}"
                    class="w-full py-4 rounded-xl bg-gradient-to-r from-emerald-500 via-teal-500 to-cyan-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-black text-sm shadow-xl shadow-emerald-500/25 flex items-center justify-center gap-2 transition-all hover:scale-[1.02]">
                    <i class="fa-solid fa-lock"></i>
                    <span>Proceed to Official PayMongo Hosted Checkout</span>
                    <i class="fa-solid fa-arrow-up-right-from-square text-xs ml-1"></i>
                </a>
            @endif

            <form action="{{ route('booking.simulate_payment', $booking->booking_reference) }}" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="payment_channel" id="selectedChannelInput" value="GCash PayMongo">

                <button type="submit"
                    class="w-full py-3.5 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-800 dark:text-emerald-300 font-bold text-xs border border-emerald-500/30 flex items-center justify-center gap-2 transition-all cursor-pointer">
                    <i class="fa-solid fa-bolt text-emerald-600 dark:text-emerald-400"></i>
                    <span>Instant Confirm Reservation (Demo / Test Mode)</span>
                </button>

                <a href="{{ route('booking.track', $booking->booking_reference) }}" class="block text-center text-xs text-theme-muted hover:text-theme-heading py-1">
                    Return to Reservation Tracker
                </a>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const timerEl = document.getElementById('paymongoSimTimer');
    let remaining = parseInt(timerEl.getAttribute('data-remaining')) || 120;

    const interval = setInterval(() => {
        if (remaining <= 0) {
            clearInterval(interval);
            timerEl.textContent = '00:00';
            Swal.fire({
                icon: 'warning',
                title: 'Hold Expired',
                text: 'Your 2-minute reservation hold has expired.',
                confirmButtonColor: '#0891b2',
                confirmButtonText: 'Check Status'
            }).then(() => {
                window.location.href = "{{ route('booking.track', $booking->booking_reference) }}";
            });
            return;
        }
        remaining--;
        const m = Math.floor(remaining / 60);
        const s = remaining % 60;
        timerEl.textContent = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    }, 1000);

    document.querySelectorAll('input[name="payment_channel"]').forEach(radio => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('input[name="payment_channel"]').forEach(r => {
                r.parentElement.classList.remove('border-2', 'border-emerald-500', 'bg-emerald-500/10', 'text-emerald-700', 'dark:text-emerald-300');
                r.parentElement.classList.add('border', 'border-stone-300', 'dark:border-stone-700', 'bg-white', 'dark:bg-stone-900', 'text-theme-body');
            });
            radio.parentElement.classList.add('border-2', 'border-emerald-500', 'bg-emerald-500/10', 'text-emerald-700', 'dark:text-emerald-300');
            radio.parentElement.classList.remove('border', 'border-stone-300', 'dark:border-stone-700', 'bg-white', 'dark:bg-stone-900', 'text-theme-body');
            document.getElementById('selectedChannelInput').value = radio.value;
        });
    });
</script>
@endpush
