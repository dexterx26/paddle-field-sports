@extends('layouts.app')

@section('content')
<div class="py-12 sm:py-16 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
    <!-- Header Banner -->
    <div class="text-center mb-12 space-y-3">
        <div class="w-14 h-14 rounded-2xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-2xl mx-auto border border-cyan-500/20 shadow-md">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <h1 class="text-3xl sm:text-4xl font-black text-theme-heading tracking-tight">Privacy Policy</h1>
        <p class="text-xs sm:text-sm text-theme-muted max-w-xl mx-auto">
            Last Updated: {{ date('F d, Y') }} • Paddle Field Sports Center
        </p>
    </div>

    <!-- Main Content Container -->
    <div class="glass-panel rounded-3xl p-6 sm:p-10 shadow-2xl space-y-8 text-xs sm:text-sm text-theme-body leading-relaxed">
        <!-- 1. Introduction -->
        <section class="space-y-3">
            <h2 class="text-base sm:text-lg font-bold text-theme-heading flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 font-bold text-xs flex items-center justify-center border border-cyan-500/20">1</span>
                <span>Introduction</span>
            </h2>
            <p>
                Welcome to <strong>Paddle Field Sports Center</strong> ("we", "our", or "us"). We operate premier indoor pickleball courts and sports facility booking services located at Bacal 3, Talavera, Nueva Ecija, Philippines.
            </p>
            <p>
                We are committed to protecting the privacy and personal information of our clients, guests, and registered users. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website (<a href="{{ url('/') }}" class="text-cyan-600 dark:text-cyan-400 hover:underline font-semibold">{{ config('app.url') }}</a>) and utilize our online reservation engine, including social authentication via Google.
            </p>
        </section>

        <!-- 2. Information We Collect -->
        <section class="space-y-3">
            <h2 class="text-base sm:text-lg font-bold text-theme-heading flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 font-bold text-xs flex items-center justify-center border border-cyan-500/20">2</span>
                <span>Information We Collect</span>
            </h2>
            <p>We may collect information directly from you when you register an account, make a court reservation, or contact customer support:</p>
            <ul class="list-disc pl-5 space-y-1.5 text-theme-muted">
                <li><strong class="text-theme-heading">Personal Identification:</strong> Full name, email address, and mobile phone number.</li>
                <li><strong class="text-theme-heading">Reservation Details:</strong> Court selections, reservation dates, timeslots, and reference codes.</li>
                <li><strong class="text-theme-heading">Payment Information:</strong> Payment reference numbers, proof-of-payment receipts (for manual GCash/bank transfers), and transaction statuses via authorized gateways (PayMongo / Xendit). We do not store sensitive credit card or e-wallet PINs.</li>
                <li><strong class="text-theme-heading">Google Account Data (OAuth 2.0):</strong> When you choose "Continue with Google", we receive your basic public profile information: name, verified email address, Google unique account ID, and profile picture avatar.</li>
            </ul>
        </section>

        <!-- 3. Google API Services User Data Policy Compliance -->
        <section class="space-y-3 p-5 rounded-2xl bg-stone-100/80 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-800">
            <h2 class="text-base sm:text-lg font-bold text-theme-heading flex items-center gap-2.5 text-cyan-700 dark:text-cyan-400">
                <i class="fa-brands fa-google text-sm"></i>
                <span>Use of Google User Data</span>
            </h2>
            <p>
                Paddle Field Sports Center's use and transfer of information received from Google APIs adheres to the 
                <a href="https://developers.google.com/terms/api-services-user-data-policy" target="_blank" rel="noopener noreferrer" class="text-cyan-600 dark:text-cyan-400 hover:underline font-semibold">Google API Services User Data Policy</a>, including the Limited Use requirements.
            </p>
            <p>
                Specifically:
            </p>
            <ul class="list-disc pl-5 space-y-1.5 text-theme-muted">
                <li>We only request minimal read-only scopes (<code class="px-1.5 py-0.5 rounded bg-stone-200 dark:bg-stone-800 font-mono text-[11px]">openid</code>, <code class="px-1.5 py-0.5 rounded bg-stone-200 dark:bg-stone-800 font-mono text-[11px]">email</code>, <code class="px-1.5 py-0.5 rounded bg-stone-200 dark:bg-stone-800 font-mono text-[11px]">profile</code>) necessary to create your player account and sign you in securely.</li>
                <li>We do <strong>not</strong> sell, transfer, or distribute your Google user data to any third parties or advertisers.</li>
                <li>We do <strong>not</strong> use your Google user data for serving personalized advertising or machine learning model training.</li>
                <li>Your Google profile avatar and name are exclusively used to personalize your sports club dashboard and identify your reservations.</li>
            </ul>
        </section>

        <!-- 4. How We Use Your Information -->
        <section class="space-y-3">
            <h2 class="text-base sm:text-lg font-bold text-theme-heading flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 font-bold text-xs flex items-center justify-center border border-cyan-500/20">4</span>
                <span>How We Use Your Information</span>
            </h2>
            <p>We use the collected information for the following specific purposes:</p>
            <ul class="list-disc pl-5 space-y-1.5 text-theme-muted">
                <li>To facilitate instant court reservations, manage real-time court availability, and prevent double booking.</li>
                <li>To authenticate user sessions securely across devices.</li>
                <li>To send reservation confirmations, game access passes, receipts, and important facility updates via email.</li>
                <li>To provide customer assistance and handle reservation change or cancellation requests.</li>
                <li>To ensure facility safety and enforce club rules.</li>
            </ul>
        </section>

        <!-- 5. Data Storage and Security -->
        <section class="space-y-3">
            <h2 class="text-base sm:text-lg font-bold text-theme-heading flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 font-bold text-xs flex items-center justify-center border border-cyan-500/20">5</span>
                <span>Data Storage and Security</span>
            </h2>
            <p>
                We implement industry-standard technical and organizational security measures to protect your personal data, including:
            </p>
            <ul class="list-disc pl-5 space-y-1.5 text-theme-muted">
                <li>256-bit SSL/TLS encryption for all website communications and API transactions.</li>
                <li>Bcrypt hashing for user account passwords.</li>
                <li>Secure database isolation and role-based permissions preventing unauthorized staff access.</li>
                <li>Automated session expiration and CSRF security verification on all interactive forms.</li>
            </ul>
        </section>

        <!-- 6. User Rights and Data Deletion -->
        <section class="space-y-3">
            <h2 class="text-base sm:text-lg font-bold text-theme-heading flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 font-bold text-xs flex items-center justify-center border border-cyan-500/20">6</span>
                <span>Your Rights & Account Deletion</span>
            </h2>
            <p>
                Under the Data Privacy Act of 2012 (Republic Act No. 10173 of the Philippines) and applicable international standards, you have the right to access, update, correct, or request the deletion of your personal data.
            </p>
            <p>
                You may update your profile details at any time through your <a href="{{ route('profile.edit') }}" class="text-cyan-600 dark:text-cyan-400 hover:underline font-semibold">User Profile</a> settings. To request full deletion of your account and associated records, please email us directly at 
                <a href="mailto:paddlefieldsports@gmail.com" class="text-cyan-600 dark:text-cyan-400 hover:underline font-bold">paddlefieldsports@gmail.com</a>. Requests are processed within 7 business days.
            </p>
        </section>

        <!-- 7. Contact Us -->
        <section class="space-y-3 pt-4 border-t border-stone-200 dark:border-stone-800">
            <h2 class="text-base sm:text-lg font-bold text-theme-heading flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 font-bold text-xs flex items-center justify-center border border-cyan-500/20">7</span>
                <span>Contact Information</span>
            </h2>
            <p>If you have any questions, inquiries, or concerns regarding this Privacy Policy or our data practices, please contact us at:</p>
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
