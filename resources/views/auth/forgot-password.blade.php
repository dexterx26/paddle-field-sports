@extends('layouts.app')

@section('content')
<div class="py-16 px-4 sm:px-6 lg:px-8 max-w-md mx-auto">
    <div class="text-center mb-8 space-y-2">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-cyan-600 via-cyan-500 to-amber-500 text-white flex items-center justify-center text-2xl mx-auto shadow-lg shadow-cyan-500/20">
            <i class="fa-solid fa-key"></i>
        </div>
        <h2 class="text-2xl font-black text-theme-heading">Forgot Password</h2>
        <p class="text-xs text-theme-muted">Enter your account email to receive a password reset link</p>
    </div>

    <!-- Main Card -->
    <div class="glass-panel rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">

        @if(session('status'))
            <div class="p-3.5 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-400 text-xs font-medium flex items-start gap-2.5">
                <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5 text-sm shrink-0"></i>
                <div class="space-y-0.5">
                    <div class="font-bold">Reset Link Sent</div>
                    <div class="text-[11px] leading-relaxed">{{ session('status') }}</div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="p-3.5 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-600 dark:text-rose-400 text-xs font-medium flex items-start gap-2.5">
                <i class="fa-solid fa-circle-exclamation text-rose-500 mt-0.5 text-sm shrink-0"></i>
                <div class="space-y-0.5">
                    <div class="font-bold">Error</div>
                    <div class="text-[11px] leading-relaxed">{{ session('error') }}</div>
                </div>
            </div>
        @endif

        @if(app()->isLocal() && session('dev_reset_url'))
            <div class="p-3.5 rounded-xl bg-cyan-500/10 border border-cyan-500/30 text-cyan-700 dark:text-cyan-400 text-xs space-y-1">
                <div class="font-bold flex items-center gap-1.5">
                    <i class="fa-solid fa-code text-xs"></i> Local Dev Quick Reset:
                </div>
                <div class="text-[11px] text-theme-muted">Email sending is disabled or simulated. Click below to reset directly:</div>
                <a href="{{ session('dev_reset_url') }}" class="underline font-bold inline-block text-cyan-600 dark:text-cyan-400 mt-1">
                    Click here to open Reset Password page &rarr;
                </a>
            </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST" class="space-y-4 text-xs">
            @csrf

            <div>
                <label class="block font-semibold text-theme-body mb-1">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    placeholder="Enter your registered email"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 focus:outline-none focus:border-cyan-500">
                @error('email')
                    <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit"
                class="w-full py-3.5 rounded-xl bg-gradient-to-r from-cyan-500 to-cyan-600 hover:from-cyan-400 hover:to-cyan-500 text-slate-950 font-black text-sm shadow-xl shadow-cyan-500/25 transition-transform hover:scale-[1.02] cursor-pointer">
                Send Reset Link
            </button>
        </form>

        <div class="text-center pt-2 border-t border-stone-200 dark:border-stone-800 space-y-2 text-xs text-theme-muted">
            <p>
                Remembered your password?
                <a href="{{ route('login') }}" class="text-cyan-600 dark:text-cyan-400 hover:underline font-bold ml-1 inline-flex items-center gap-1">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i> Back to Sign In
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
