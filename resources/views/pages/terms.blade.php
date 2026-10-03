@extends('layouts.app')

@section('content')
<div class="py-12 sm:py-16 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
    <!-- Header Banner -->
    <div class="text-center mb-12 space-y-3">
        <div class="w-14 h-14 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-2xl mx-auto border border-amber-500/20 shadow-md">
            <i class="fa-solid fa-file-contract"></i>
        </div>
        <h1 class="text-3xl sm:text-4xl font-black text-theme-heading tracking-tight">Terms of Service</h1>
        <p class="text-xs sm:text-sm text-theme-muted max-w-xl mx-auto">
            Last Updated: {{ date('F d, Y') }} • Paddle Field Sports Center
        </p>
    </div>

    <!-- Main Content Container -->
    <div class="glass-panel rounded-3xl p-6 sm:p-10 shadow-2xl space-y-8 text-xs sm:text-sm text-theme-body leading-relaxed">
        <!-- 1. Agreement to Terms -->
        <section class="space-y-3">
            <h2 class="text-base sm:text-lg font-bold text-theme-heading flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold text-xs flex items-center justify-center border border-amber-500/20">1</span>
                <span>Agreement to Terms</span>
            </h2>
            <p>
                These Terms of Service ("Terms") constitute a legally binding agreement between you ("User", "Player", or "Guest") and <strong>Paddle Field Sports Center</strong> ("we", "us", or "our"), governing your access to and use of our facilities, website (<a href="{{ url('/') }}" class="text-cyan-600 dark:text-cyan-400 hover:underline font-semibold">{{ config('app.url') }}</a>), and online reservation system.
            </p>
            <p>
                By accessing our website, creating an account, or reserving a court, you agree that you have read, understood, and accept to be bound by all of these Terms. If you do not agree with any part of these Terms, you must discontinue using our services immediately.
            </p>
        </section>

        <!-- 2. Court Reservations & Temporary Slot Holding -->
        <section class="space-y-3">
            <h2 class="text-base sm:text-lg font-bold text-theme-heading flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold text-xs flex items-center justify-center border border-amber-500/20">2</span>
                <span>Reservations & Real-Time Slot Holds</span>
            </h2>
            <p>
                Our facility offers certified indoor pickleball courts available for reservation through our real-time booking engine:
            </p>
            <ul class="list-disc pl-5 space-y-1.5 text-theme-muted">
                <li><strong class="text-theme-heading">Temporary Hold:</strong> When you select available timeslots and proceed to checkout, those slots are temporarily held in real time to prevent double-booking. The holding timer is displayed on the screen.</li>
                <li><strong class="text-theme-heading">Auto-Release:</strong> If payment is not completed before the holding duration expires, the reserved slots are automatically released back to public availability for other players.</li>
                <li><strong class="text-theme-heading">Confirmed Bookings:</strong> A reservation is only confirmed once successful payment verification is recorded (via automated gateway confirmation or staff approval of manual receipt upload).</li>
                <li><strong class="text-theme-heading">Operating Hours:</strong> Reservations must adhere to individual court operating hours as configured in our schedule. Courts are reserved for the exact timeslots booked.</li>
            </ul>
        </section>

        <!-- 3. Payments, Pricing & Fees -->
        <section class="space-y-3">
            <h2 class="text-base sm:text-lg font-bold text-theme-heading flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold text-xs flex items-center justify-center border border-amber-500/20">3</span>
                <span>Payments & Pricing</span>
            </h2>
            <ul class="list-disc pl-5 space-y-1.5 text-theme-muted">
                <li>Court rental rates are calculated per hour per court and displayed clearly prior to checkout.</li>
                <li>We accept payments via integrated digital payment gateways (GCash, Maya, QRPH, Cards via PayMongo / Xendit) and manual bank/e-wallet transfer with receipt submission.</li>
                <li>All payments must be made in Philippine Peso (PHP). Prices are inclusive of applicable facility utility charges.</li>
            </ul>
        </section>

        <!-- 4. Cancellations, Rescheduling & Refund Policy -->
        <section class="space-y-3">
            <h2 class="text-base sm:text-lg font-bold text-theme-heading flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold text-xs flex items-center justify-center border border-amber-500/20">4</span>
                <span>Cancellations & Refunds</span>
            </h2>
            <ul class="list-disc pl-5 space-y-1.5 text-theme-muted">
                <li><strong class="text-theme-heading">Customer Rescheduling:</strong> Players wishing to reschedule their booking must notify facility management at least 24 hours prior to the reserved slot.</li>
                <li><strong class="text-theme-heading">Facility Maintenance or Unforeseen Closures:</strong> If court availability is interrupted due to unexpected power outages, extreme weather conditions, or facility maintenance, management will offer full rescheduling credit or refund.</li>
                <li><strong class="text-theme-heading">No-Show Policy:</strong> Failure to arrive for a confirmed reservation without prior notice forfeits the booking fee without refund.</li>
            </ul>
        </section>

        <!-- 5. Facility Rules & Code of Conduct -->
        <section class="space-y-3">
            <h2 class="text-base sm:text-lg font-bold text-theme-heading flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold text-xs flex items-center justify-center border border-amber-500/20">5</span>
                <span>Facility Rules & Player Safety</span>
            </h2>
            <p>To ensure a safe and enjoyable environment for all sports enthusiasts:</p>
            <ul class="list-disc pl-5 space-y-1.5 text-theme-muted">
                <li><strong class="text-theme-heading">Footwear:</strong> Proper non-marking athletic or court shoes are required on all cushioned acrylic courts at all times. Black-soled street shoes are prohibited.</li>
                <li><strong class="text-theme-heading">Capacity:</strong> The maximum number of active players per court must strictly adhere to facility guidelines (maximum 4 active players for standard doubles play).</li>
                <li><strong class="text-theme-heading">Sportsmanship:</strong> Harassment, abusive language, or deliberate property damage will result in immediate expulsion from the premises without refund.</li>
                <li><strong class="text-theme-heading">Cleanliness:</strong> Food and colored drinks are not permitted inside the court playing surface. Only bottled water and sports hydration drinks with secure caps are allowed in designated bench areas.</li>
            </ul>
        </section>

        <!-- 6. User Accounts & Third-Party Sign-In (Google OAuth) -->
        <section class="space-y-3">
            <h2 class="text-base sm:text-lg font-bold text-theme-heading flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold text-xs flex items-center justify-center border border-amber-500/20">6</span>
                <span>User Accounts & Google Authentication</span>
            </h2>
            <p>
                When creating an account or using Google sign-in:
            </p>
            <ul class="list-disc pl-5 space-y-1.5 text-theme-muted">
                <li>You are responsible for maintaining the confidentiality of your account credentials.</li>
                <li>You agree to provide accurate and current contact details to ensure booking confirmations reach you.</li>
                <li>We reserve the right to suspend or deactivate accounts that engage in fraudulent holds, unauthorized timeslot reselling, or abuse of the booking platform.</li>
            </ul>
        </section>

        <!-- 7. Limitation of Liability -->
        <section class="space-y-3">
            <h2 class="text-base sm:text-lg font-bold text-theme-heading flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold text-xs flex items-center justify-center border border-amber-500/20">7</span>
                <span>Limitation of Liability</span>
            </h2>
            <p>
                Participation in sports activities carries inherent risks of physical exertion and injury. Users and guests voluntarily assume all risks associated with playing pickleball or utilizing sports center amenities. Paddle Field Sports Center, its owners, and employees shall not be liable for any personal injury, loss of personal belongings, or property damage sustained on the premises, except where caused directly by gross negligence.
            </p>
        </section>

        <!-- 8. Contact Information -->
        <section class="space-y-3 pt-4 border-t border-stone-200 dark:border-stone-800">
            <h2 class="text-base sm:text-lg font-bold text-theme-heading flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold text-xs flex items-center justify-center border border-amber-500/20">8</span>
                <span>Contact Us</span>
            </h2>
            <p>For questions or assistance regarding these Terms of Service, please reach out to us:</p>
            <div class="p-4 rounded-2xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 space-y-1.5 text-xs text-theme-muted">
                <div><strong class="text-theme-heading">Facility Name:</strong> Paddle Field Sports Center</div>
                <div><strong class="text-theme-heading">Location:</strong> Bacal 3, Talavera, Nueva Ecija, 3114, Philippines</div>
                <div><strong class="text-theme-heading">Email:</strong> <a href="mailto:paddlefieldsports@gmail.com" class="text-cyan-600 dark:text-cyan-400 hover:underline">paddlefieldsports@gmail.com</a></div>
                <div><strong class="text-theme-heading">Website:</strong> <a href="{{ url('/') }}" class="text-cyan-600 dark:text-cyan-400 hover:underline">{{ config('app.url') }}</a></div>
            </div>
        </section>
    </div>

    <!-- Back to Home -->
    <div class="text-center mt-8">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-stone-200 dark:bg-stone-800 text-xs font-bold text-theme-heading hover:bg-cyan-500 hover:text-slate-950 transition-all">
            <i class="fa-solid fa-arrow-left text-[11px]"></i> Return to Home
        </a>
    </div>
</div>
@endsection
