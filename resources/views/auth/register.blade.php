@extends('layouts.app')

@section('content')
<div class="py-16 px-4 sm:px-6 lg:px-8 max-w-md mx-auto">
    <div class="text-center mb-8 space-y-2">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-amber-600 via-amber-700 to-cyan-500 text-white flex items-center justify-center text-2xl mx-auto shadow-lg shadow-cyan-500/20">
            <i class="fa-solid fa-user-plus"></i>
        </div>
        <h2 class="text-2xl font-black text-theme-heading">Create Player Account</h2>
        <p class="text-xs text-theme-muted">Join Paddle Field Sports Center Community</p>
    </div>

    <div class="glass-panel rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">
        <form action="{{ route('register') }}" method="POST" class="space-y-4 text-xs">
            @csrf

            <div>
                <label class="block font-semibold text-theme-body mb-1">Full Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="e.g. Maria Santos"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 focus:outline-none focus:border-cyan-500">
                @error('name')
                    <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block font-semibold text-theme-body mb-1">Mobile Phone Number <span class="text-rose-500">*</span></label>
                <input type="tel" name="phone" value="{{ old('phone') }}" required placeholder="e.g. 0917-123-4567"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 focus:outline-none focus:border-cyan-500">
                @error('phone')
                    <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block font-semibold text-theme-body mb-1">Email Address <span class="text-rose-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="maria@example.com"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 focus:outline-none focus:border-cyan-500">
                @error('email')
                    <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block font-semibold text-theme-body mb-1">Password <span class="text-rose-500">*</span></label>
                <input type="password" name="password" required placeholder="Minimum 8 characters"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 focus:outline-none focus:border-cyan-500">
                @error('password')
                    <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block font-semibold text-theme-body mb-1">Confirm Password <span class="text-rose-500">*</span></label>
                <input type="password" name="password_confirmation" required placeholder="Repeat password"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 focus:outline-none focus:border-cyan-500">
            </div>

            <button type="submit"
                class="w-full py-3.5 rounded-xl bg-gradient-to-r from-cyan-500 to-cyan-600 hover:from-cyan-400 hover:to-cyan-500 text-slate-950 font-black text-sm shadow-xl shadow-cyan-500/25 transition-transform hover:scale-[1.02] cursor-pointer">
                Complete Registration
            </button>
        </form>

        <div class="text-center pt-2 border-t border-stone-200 dark:border-stone-800 space-y-1 text-xs text-theme-muted">
            <p>Already have an account? <a href="{{ route('login') }}" class="text-cyan-600 dark:text-cyan-400 hover:underline font-bold">Sign In</a></p>
            <p class="text-[11px]">Remember: Guest booking is always available without an account.</p>
        </div>
    </div>
</div>
@endsection
