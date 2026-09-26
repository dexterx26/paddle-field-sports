@extends('layouts.app')

@section('content')
<div class="py-12 px-4 sm:px-6 lg:px-8 max-w-xl mx-auto">
    <!-- Header -->
    <div class="text-center mb-8 space-y-2">
        <div class="w-12 h-12 rounded-2xl bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-xl mx-auto border border-cyan-500/30">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <span class="text-[11px] font-extrabold uppercase tracking-widest text-cyan-700 dark:text-cyan-300 bg-cyan-500/10 px-3 py-1 rounded-full border border-cyan-500/20 inline-block">
            Xendit Secure Checkout Simulation
        </span>
        <h2 class="text-2xl font-black text-theme-heading">Payment Gateway</h2>
        <p class="text-xs text-theme-muted">Paddle Field Sports Center Official Checkout</p>
    </div>

    <!-- Main Checkout Container -->
    <div class="glass-panel rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6 relative overflow-hidden">
        <!-- 2-Minute Timer Pill -->
        <div class="p-3.5 rounded-2xl bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-stopwatch text-cyan-600 dark:text-cyan-400 animate-spin text-sm"></i>
                <span class="text-xs text-theme-body font-medium">Holding Time Remaining:</span>
            </div>
            <div class="font-mono font-black text-theme-heading text-base" id="xenditSimTimer" data-remaining="{{ $booking->remaining_hold_seconds }}">
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
                <span class="font-mono font-bold text-cyan-600 dark:text-cyan-400">{{ $booking->booking_reference }}</span>
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
                <span class="font-black text-cyan-600 dark:text-cyan-400 text-lg">{{ $booking->formatted_amount }}</span>
            </div>
        </div>

        <!-- Payment Channel Tabs -->
        <div>
            <label class="block text-xs font-bold text-theme-body mb-2">Select Payment Method</label>
            <div class="grid grid-cols-3 gap-2 text-xs">
                <label class="p-3 rounded-xl border-2 border-cyan-500 bg-cyan-500/10 flex flex-col items-center justify-center gap-1.5 cursor-pointer text-center font-bold text-cyan-700 dark:text-cyan-300">
                    <input type="radio" name="payment_channel" value="GCash" checked class="hidden">
                    <i class="fa-solid fa-mobile-screen text-lg"></i>
                    <span>GCash</span>
                </label>
                <label class="p-3 rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 hover:border-cyan-500 flex flex-col items-center justify-center gap-1.5 cursor-pointer text-center font-bold text-theme-body">
                    <input type="radio" name="payment_channel" value="Maya" class="hidden">
                    <i class="fa-solid fa-wallet text-lg"></i>
                    <span>Maya</span>
                </label>
                <label class="p-3 rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 hover:border-cyan-500 flex flex-col items-center justify-center gap-1.5 cursor-pointer text-center font-bold text-theme-body">
                    <input type="radio" name="payment_channel" value="Credit/Debit Card" class="hidden">
                    <i class="fa-solid fa-credit-card text-lg"></i>
                    <span>Card / QRPH</span>
                </label>
            </div>
        </div>

        <!-- Simulated Action Button -->
        <form action="{{ route('booking.simulate_payment', $booking->booking_reference) }}" method="POST" class="space-y-3">
            @csrf
            <input type="hidden" name="payment_channel" id="selectedChannelInput" value="GCash">

            <button type="submit"
                class="w-full py-4 rounded-xl bg-gradient-to-r from-cyan-500 to-cyan-600 hover:from-cyan-400 hover:to-cyan-500 text-slate-950 font-black text-sm shadow-xl shadow-cyan-500/25 flex items-center justify-center gap-2 transition-all hover:scale-[1.02] cursor-pointer">
                <i class="fa-solid fa-circle-check"></i>
                <span>Complete Simulated Payment (Test Mode)</span>
            </button>

            <a href="{{ route('booking.track', $booking->booking_reference) }}" class="block text-center text-xs text-theme-muted hover:text-theme-heading py-1">
                Return to Reservation Tracker
            </a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const timerEl = document.getElementById('xenditSimTimer');
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
                r.parentElement.classList.remove('border-2', 'border-cyan-500', 'bg-cyan-500/10', 'text-cyan-700', 'dark:text-cyan-300');
                r.parentElement.classList.add('border', 'border-stone-300', 'dark:border-stone-700', 'bg-white', 'dark:bg-stone-900', 'text-theme-body');
            });
            radio.parentElement.classList.add('border-2', 'border-cyan-500', 'bg-cyan-500/10', 'text-cyan-700', 'dark:text-cyan-300');
            radio.parentElement.classList.remove('border', 'border-stone-300', 'dark:border-stone-700', 'bg-white', 'dark:bg-stone-900', 'text-theme-body');
            document.getElementById('selectedChannelInput').value = radio.value;
        });
    });
</script>
@endpush
