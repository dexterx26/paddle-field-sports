@extends('layouts.app')

@section('content')
<div class="py-16 px-4 sm:px-6 lg:px-8 max-w-md mx-auto">
    <div class="text-center mb-8 space-y-2">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-amber-600 via-amber-700 to-cyan-500 text-white flex items-center justify-center text-2xl mx-auto shadow-lg shadow-cyan-500/20">
            <i class="fa-solid fa-table-tennis-paddle-ball"></i>
        </div>
        <h2 class="text-2xl font-black text-theme-heading">Sign In to Paddle Field</h2>
        <p class="text-xs text-theme-muted">Owner, Admin & Player Authentication</p>
    </div>

    <!-- Main Login Card -->
    <div class="glass-panel rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">
        <!-- 1-Click Fast Demo Login Switcher -->
        <div class="p-4 rounded-2xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 space-y-2.5">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-theme-muted uppercase tracking-wider">Fast Demo Accounts (1-Click)</span>
                <i class="fa-solid fa-bolt text-amber-500 text-xs"></i>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <a href="{{ route('quick.login', 'owner') }}" class="p-2 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-cyan-500 hover:text-slate-950 text-center font-bold text-xs text-cyan-700 dark:text-cyan-400 border border-stone-300 dark:border-stone-700 transition-all cursor-pointer">
                    Owner
                </a>
                <a href="{{ route('quick.login', 'admin') }}" class="p-2 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-amber-500 hover:text-slate-950 text-center font-bold text-xs text-amber-700 dark:text-amber-400 border border-stone-300 dark:border-stone-700 transition-all cursor-pointer">
                    Admin
                </a>
                <a href="{{ route('quick.login', 'player') }}" class="p-2 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-cyan-500 hover:text-slate-950 text-center font-bold text-xs text-cyan-700 dark:text-cyan-400 border border-stone-300 dark:border-stone-700 transition-all cursor-pointer">
                    Player
                </a>
            </div>
            <div class="text-[10px] text-theme-muted text-center">Click any role to log in instantly without typing credentials!</div>
        </div>

        <form action="{{ route('login') }}" method="POST" class="space-y-4 text-xs">
            @csrf

            <div>
                <label class="block font-semibold text-theme-body mb-1">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 focus:outline-none focus:border-cyan-500">
                @error('email')
                    <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block font-semibold text-theme-body mb-1">Password</label>
                <input type="password" name="password" required
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 focus:outline-none focus:border-cyan-500">
                @error('password')
                    <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer text-theme-body">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded text-cyan-500 bg-stone-100 dark:bg-stone-900 border-stone-300 dark:border-stone-700">
                    <span>Remember me</span>
                </label>
            </div>

            <button type="submit"
                class="w-full py-3.5 rounded-xl bg-gradient-to-r from-cyan-500 to-cyan-600 hover:from-cyan-400 hover:to-cyan-500 text-slate-950 font-black text-sm shadow-xl shadow-cyan-500/25 transition-transform hover:scale-[1.02] cursor-pointer">
                Sign In
            </button>
        </form>

        <div class="text-center pt-2 border-t border-stone-200 dark:border-stone-800 space-y-2 text-xs text-theme-muted">
            <p>Don't have an account? <a href="{{ route('register') }}" class="text-cyan-600 dark:text-cyan-400 hover:underline font-bold">Register as a Player</a></p>
            <p class="text-[11px]">Note: Guests can book courts directly without creating an account!</p>
        </div>
    </div>
</div>
@endsection
