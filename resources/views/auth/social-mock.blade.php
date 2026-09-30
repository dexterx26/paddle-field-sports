@extends('layouts.app')

@section('content')
<div class="py-16 px-4 sm:px-6 lg:px-8 max-w-xl mx-auto">
    <div class="text-center mb-8 space-y-2">
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-stone-800 to-stone-900 border border-stone-700 text-white flex items-center justify-center text-3xl mx-auto shadow-xl">
            <i class="{{ $providerIcon }}"></i>
        </div>
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-500 text-xs font-semibold">
            <i class="fa-solid fa-flask"></i> Sandbox / Dev Simulation Mode
        </div>
        <h2 class="text-2xl font-black text-theme-heading">{{ $providerName }} Sign-In</h2>
        <p class="text-xs text-theme-muted">
            Test account registration & login locally before adding production API keys.
        </p>
    </div>

    <div class="space-y-6">
        <!-- Fast 1-Click Simulated Profiles -->
        <div class="glass-panel rounded-3xl p-6 shadow-2xl space-y-4">
            <h3 class="text-sm font-bold text-theme-heading flex items-center gap-2">
                <i class="fa-solid fa-bolt text-cyan-400"></i> Instant 1-Click Test Personas
            </h3>
            <p class="text-xs text-theme-muted">
                Click a test persona below to simulate a real {{ $providerName }} profile returning to Paddle Field Sports Center:
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                <form action="{{ route('auth.social.mock.process', $provider) }}" method="POST">
                    @csrf
                    <input type="hidden" name="name" value="Marcus Player (Google Test)">
                    <input type="hidden" name="email" value="marcus.google@example.com">
                    <button type="submit" class="w-full p-3 rounded-2xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 hover:border-cyan-500 text-left transition-all group cursor-pointer">
                        <div class="flex items-center gap-3">
                            <img src="https://ui-avatars.com/api/?name=Marcus+Player&background=0284c7&color=fff" class="w-9 h-9 rounded-xl">
                            <div>
                                <div class="text-xs font-bold text-theme-heading group-hover:text-cyan-400">Marcus Player</div>
                                <div class="text-[10px] text-theme-muted">marcus.google@example.com</div>
                            </div>
                        </div>
                    </button>
                </form>

                <form action="{{ route('auth.social.mock.process', $provider) }}" method="POST">
                    @csrf
                    <input type="hidden" name="name" value="Elena Santos (New Player)">
                    <input type="hidden" name="email" value="elena.santos@gmail.com">
                    <button type="submit" class="w-full p-3 rounded-2xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 hover:border-cyan-500 text-left transition-all group cursor-pointer">
                        <div class="flex items-center gap-3">
                            <img src="https://ui-avatars.com/api/?name=Elena+Santos&background=ec4899&color=fff" class="w-9 h-9 rounded-xl">
                            <div>
                                <div class="text-xs font-bold text-theme-heading group-hover:text-cyan-400">Elena Santos</div>
                                <div class="text-[10px] text-theme-muted">elena.santos@gmail.com</div>
                            </div>
                        </div>
                    </button>
                </form>
            </div>
        </div>

        <!-- Custom Simulated Profile Form -->
        <div class="glass-panel rounded-3xl p-6 shadow-2xl space-y-4">
            <h3 class="text-sm font-bold text-theme-heading flex items-center gap-2">
                <i class="fa-solid fa-user-pen text-amber-500"></i> Or Enter Custom Test Info
            </h3>

            <form action="{{ route('auth.social.mock.process', $provider) }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Simulated Full Name</label>
                    <input type="text" name="name" value="Alex Rivera" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 focus:outline-none focus:border-cyan-500">
                </div>
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Simulated Email</label>
                    <input type="email" name="email" value="alex.rivera@example.com" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 focus:outline-none focus:border-cyan-500">
                </div>
                <button type="submit"
                    class="w-full py-3 rounded-xl bg-gradient-to-r from-cyan-500 to-cyan-600 hover:from-cyan-400 hover:to-cyan-500 text-slate-950 font-black text-xs shadow-lg shadow-cyan-500/20 transition-all cursor-pointer">
                    Simulate Registration & Login
                </button>
            </form>
        </div>

        <!-- Setup Instructions Card -->
        <div class="p-5 rounded-3xl bg-stone-100/80 dark:bg-stone-900/80 border border-stone-200 dark:border-stone-800 space-y-3 text-xs">
            <div class="flex items-center gap-2 font-bold text-theme-heading">
                <i class="fa-solid fa-key text-cyan-400"></i>
                <span>How to switch from Simulation to Real {{ $providerName }} Keys</span>
            </div>
            <p class="text-theme-muted text-[11px] leading-relaxed">
                To connect real {{ $providerName }} accounts, add your developer credentials in <code class="px-1.5 py-0.5 rounded bg-stone-200 dark:bg-stone-800 font-mono text-[10px]">.env</code>:
            </p>
            @if($provider === 'google')
                <div class="p-3 rounded-xl bg-stone-950 font-mono text-[11px] text-cyan-400 overflow-x-auto select-all">
                    GOOGLE_CLIENT_ID=your_google_client_id_here<br>
                    GOOGLE_CLIENT_SECRET=your_google_client_secret_here<br>
                    GOOGLE_REDIRECT_URI="{{ rtrim($appUrl, '/') }}/auth/google/callback"
                </div>
            @else
                <div class="p-3 rounded-xl bg-stone-950 font-mono text-[11px] text-cyan-400 overflow-x-auto select-all">
                    FACEBOOK_CLIENT_ID=your_facebook_app_id_here<br>
                    FACEBOOK_CLIENT_SECRET=your_facebook_app_secret_here<br>
                    FACEBOOK_REDIRECT_URI="{{ rtrim($appUrl, '/') }}/auth/facebook/callback"
                </div>
            @endif
            <p class="text-[11px] text-theme-muted">
                Authorized Redirect URI to whitelist in your developer console:
                <strong class="text-theme-heading font-mono">{{ rtrim($appUrl, '/') }}/auth/{{ $provider }}/callback</strong>
            </p>
        </div>

        <div class="text-center text-xs">
            <a href="{{ route('login') }}" class="text-cyan-600 dark:text-cyan-400 hover:underline">
                <i class="fa-solid fa-arrow-left"></i> Return to Sign In
            </a>
        </div>
    </div>
</div>
@endsection
