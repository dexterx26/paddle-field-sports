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
        @if(!app()->isProduction())
            <!-- 1-Click Fast Demo Login Switcher -->
            <div class="p-4 rounded-2xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-theme-muted uppercase tracking-wider">Fast Demo Accounts (1-Click)</span>
                    <i class="fa-solid fa-bolt text-amber-500 text-xs"></i>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-2">
                    <a href="{{ route('quick.login', 'owner') }}" class="p-2 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-cyan-500 hover:text-slate-950 text-center font-bold text-xs text-cyan-700 dark:text-cyan-400 border border-stone-300 dark:border-stone-700 transition-all cursor-pointer">
                        Owner
                    </a>
                    <a href="{{ route('quick.login', 'assistant') }}" class="p-2 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-teal-500 hover:text-slate-950 text-center font-bold text-xs text-teal-700 dark:text-teal-400 border border-stone-300 dark:border-stone-700 transition-all cursor-pointer" title="Maria Santos (Admin Assistant)">
                        Assistant
                    </a>
                    <a href="{{ route('quick.login', 'admin') }}" class="p-2 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-amber-500 hover:text-slate-950 text-center font-bold text-xs text-amber-700 dark:text-amber-400 border border-stone-300 dark:border-stone-700 transition-all cursor-pointer">
                        Admin
                    </a>
                    <a href="{{ route('quick.login', 'player') }}" class="p-2 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-cyan-500 hover:text-slate-950 text-center font-bold text-xs text-cyan-700 dark:text-cyan-400 border border-stone-300 dark:border-stone-700 transition-all cursor-pointer" title="Marcus Vance">
                        Player 1
                    </a>
                    <a href="{{ route('quick.login', 'player2') }}" class="p-2 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-emerald-500 hover:text-slate-950 text-center font-bold text-xs text-emerald-700 dark:text-emerald-400 border border-stone-300 dark:border-stone-700 transition-all cursor-pointer" title="Elena Cruz">
                        Player 2
                    </a>
                    <a href="{{ route('quick.login', 'paolo') }}" class="p-2 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-violet-500 hover:text-slate-950 text-center font-bold text-xs text-violet-700 dark:text-violet-400 border border-stone-300 dark:border-stone-700 transition-all cursor-pointer" title="Paolo Soriano">
                        Paolo (P3)
                    </a>
                </div>
                <div class="text-[10px] text-theme-muted text-center">Click any role to log in instantly without typing credentials!</div>
            </div>
        @endif

        @if(request('auth_error') || session('error'))
            <div class="p-3.5 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-600 dark:text-rose-400 text-xs font-medium flex items-start gap-2.5">
                <i class="fa-solid fa-circle-exclamation text-rose-500 mt-0.5 text-sm shrink-0"></i>
                <div class="space-y-0.5">
                    <div class="font-bold">Authentication Issue</div>
                    <div class="text-[11px] leading-relaxed">{{ request('auth_error') ?: session('error') }}</div>
                </div>
            </div>
        @endif

        <!-- 1-Click Social Sign-In Options -->
        <div class="space-y-2.5">
            <a href="{{ route('auth.social.redirect', 'google') }}"
                class="w-full py-2.5 px-4 rounded-xl bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-700 hover:border-rose-500/50 hover:bg-stone-50 dark:hover:bg-stone-800 text-theme-heading font-bold text-xs flex items-center justify-center gap-3 shadow-sm hover:shadow transition-all group cursor-pointer">
                <svg class="w-4 h-4" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                </svg>
                <span>Continue with Google</span>
            </a>
        </div>

        <!-- Divider -->
        <div class="relative flex items-center justify-center">
            <div class="border-t border-stone-200 dark:border-stone-800 w-full"></div>
            <span class="bg-white dark:bg-stone-900 px-3 text-[10px] uppercase tracking-wider text-theme-muted font-bold relative">or sign in with credentials</span>
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
