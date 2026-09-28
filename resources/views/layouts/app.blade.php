<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Paddle Field Sports Center' }} - Pickleball Court Reservation</title>
    <meta name="description" content="Premier indoor and outdoor pickleball court reservation for Paddle Field Sports Center. Real-time availability, instant slot holding, and seamless booking.">

    <!-- Light Mode Initialization (Ensures clean Light theme across all browsers) -->
    <script>
        document.documentElement.classList.remove('dark');
        localStorage.removeItem('theme');
    </script>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Vite Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="min-h-screen flex flex-col selection:bg-cyan-400 selection:text-slate-950 antialiased transition-colors duration-200">
    <!-- Top Announcement Bar (Lead, Crunch & Dreamland Accents) -->
    <div class="bg-lead text-cascading-white py-1.5 px-4 text-xs font-semibold text-center tracking-wide flex items-center justify-center gap-3 border-b border-stone-800">
        <span>⚡ Open Daily 6:00 AM – 12:00 AM Midnight • Fast Real-Time Court Reservations</span>
        <span class="hidden md:inline-block w-1.5 h-1.5 rounded-full bg-cyan-400"></span>
        <span class="hidden md:inline text-amber-300">Instant 2-Minute Slot Hold with Xendit or Manual GCash Receipt</span>
    </div>

    <!-- Main Navigation Header -->
    <header class="sticky top-0 z-50 glass-panel border-b backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Brand / Logo -->
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-amber-600 via-amber-700 to-cyan-500 flex items-center justify-center shadow-lg shadow-cyan-500/20 group-hover:scale-105 transition-transform text-white">
                        <i class="fa-solid fa-table-tennis-paddle-ball text-xl"></i>
                    </div>
                    <div>
                        <div class="text-lg font-extrabold tracking-tight flex items-center gap-2">
                            <span class="text-theme-heading">Paddle Field</span>
                            <span class="text-xs px-2.5 py-0.5 rounded-full bg-amber-500/15 text-amber-800 dark:text-amber-300 border border-amber-500/30 uppercase tracking-widest font-bold">Sports Center</span>
                        </div>
                        <p class="text-xs text-theme-muted font-medium">Pickleball Arena & Club</p>
                    </div>
                </a>

                <!-- Desktop Nav Links -->
                <nav class="hidden md:flex items-center gap-7 text-sm font-semibold text-theme-body">
                    <a href="{{ route('home') }}#booking-engine" class="hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-calendar-check text-xs text-cyan-600 dark:text-cyan-400"></i> Reserve Court
                    </a>
                    <a href="{{ route('home') }}#courts-section" class="hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors">
                        Courts & Rates
                    </a>
                    <a href="{{ route('home') }}#gallery-section" class="hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors">
                        Photo Gallery
                    </a>
                    <button type="button" onclick="openLookupModal()" class="hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i> Check Booking
                    </button>
                </nav>

                <!-- Actions: Theme Toggle, Sync Badge, Role Switcher, Auth -->
                <div class="flex items-center gap-3">
                    <!-- Real-Time Sync Indicator -->
                    <div class="reverb-status-indicator hidden sm:block">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-cyan-500/10 text-cyan-700 border border-cyan-500/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-ping"></span> Live Sync
                        </span>
                    </div>

                    <!-- Quick Switcher Demo Dropdown -->
                    <div class="relative group">
                        <button type="button" class="px-2.5 py-1.5 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-stone-300 dark:hover:bg-stone-700 text-xs font-semibold text-theme-heading border border-stone-300 dark:border-stone-700 flex items-center gap-1.5 transition-all cursor-pointer">
                            <i class="fa-solid fa-bolt text-amber-500"></i>
                            <span class="hidden sm:inline">Role Switcher</span>
                            <i class="fa-solid fa-chevron-down text-[10px] text-theme-muted"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-56 py-2 glass-dropdown rounded-2xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
                            <div class="px-3 py-1 text-[11px] font-bold text-theme-muted uppercase tracking-wider">Quick Switch Demo</div>
                            <a href="{{ route('quick.login', 'owner') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs text-theme-heading hover:bg-cyan-500/10 hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors">
                                <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                                <div>
                                    <div class="font-bold">Court Owner</div>
                                    <div class="text-[10px] text-theme-muted">Manage courts, prices, approvals</div>
                                </div>
                            </a>
                            <a href="{{ route('quick.login', 'assistant') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs text-theme-heading hover:bg-teal-500/10 hover:text-teal-600 dark:hover:text-teal-400 transition-colors">
                                <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                                <div>
                                    <div class="font-bold">Admin Assistant</div>
                                    <div class="text-[10px] text-theme-muted">Schedule & reservation approvals</div>
                                </div>
                            </a>
                            <a href="{{ route('quick.login', 'admin') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs text-theme-heading hover:bg-amber-500/10 hover:text-amber-700 dark:hover:text-amber-400 transition-colors">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                <div>
                                    <div class="font-bold">System Admin</div>
                                    <div class="text-[10px] text-theme-muted">Full platform management</div>
                                </div>
                            </a>
                            <a href="{{ route('quick.login', 'player') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs text-theme-heading hover:bg-cyan-500/10 hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors">
                                <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                                <div>
                                    <div class="font-bold">Registered Player</div>
                                    <div class="text-[10px] text-theme-muted">Marcus Vance account</div>
                                </div>
                            </a>
                        </div>
                    </div>

                    @auth
                        @if(Auth::user()->isStaffOrAdmin())
                            <a href="{{ route(Auth::user()->getFirstAllowedRoute()) }}" class="px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 text-xs font-bold shadow-md shadow-cyan-500/20 flex items-center gap-2 transition-all">
                                <i class="fa-solid fa-gauge-high"></i>
                                <span>{{ Auth::user()->isAdminAssistant() ? 'Staff Hub' : 'Owner Hub' }}</span>
                            </a>
                        @else
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-theme-heading font-semibold hidden sm:inline">{{ Auth::user()->name }}</span>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-stone-300 dark:hover:bg-stone-700 text-xs text-theme-heading transition-colors cursor-pointer">
                                        Logout
                                    </button>
                                </form>
                            </div>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="px-3.5 py-2 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-stone-300 dark:hover:bg-stone-700 text-xs font-semibold text-theme-heading transition-colors">
                            Sign In
                        </a>
                        <a href="{{ route('home') }}#booking-engine" class="px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 text-xs font-extrabold shadow-lg shadow-cyan-500/25 transition-transform hover:scale-105">
                            Book Court
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Flash Notifications -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-cyan-500/10 border border-cyan-500/30 text-cyan-900 dark:text-cyan-200 text-sm flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-check text-cyan-600 dark:text-cyan-400 text-lg"></i>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-theme-muted hover:text-theme-heading cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-900 dark:text-rose-200 text-sm flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 dark:text-rose-400 text-lg"></i>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-theme-muted hover:text-theme-heading cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif

        @if(session('info'))
            <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-900 dark:text-amber-200 text-sm flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-info text-amber-600 dark:text-amber-400 text-lg"></i>
                    <span class="font-medium">{{ session('info') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-theme-muted hover:text-theme-heading cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif
    </div>

    <!-- Main Content Area -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="mt-20 border-t border-stone-200 dark:border-stone-800 bg-stone-100/90 dark:bg-stone-900/60 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Col 1: Venue Info -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-cyan-500 text-slate-950 flex items-center justify-center font-bold shadow-md">
                            <i class="fa-solid fa-table-tennis-paddle-ball"></i>
                        </div>
                        <span class="text-lg font-extrabold text-theme-heading">Paddle Field</span>
                    </div>
                    <p class="text-xs text-theme-muted leading-relaxed">
                        Paddle Field Sports Center is Bacal 3, Talavera's premier pickleball facility featuring tournament-grade cushion acrylic courts, anti-glare floodlights, and a social clubhouse lounge.
                    </p>
                    <div class="flex items-center gap-3 text-theme-muted text-sm pt-2">
                        <a href="#" class="w-8 h-8 rounded-lg bg-stone-200 flex items-center justify-center text-theme-muted hover:text-cyan-600 transition-colors"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" class="w-8 h-8 rounded-lg bg-stone-200 flex items-center justify-center text-theme-muted hover:text-cyan-600 transition-colors"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="w-8 h-8 rounded-lg bg-stone-200 flex items-center justify-center text-theme-muted hover:text-cyan-600 transition-colors"><i class="fa-brands fa-tiktok"></i></a>
                    </div>
                </div>

                <!-- Col 2: Quick Links -->
                <div>
                    <h4 class="text-sm font-bold text-theme-heading mb-4 uppercase tracking-wider">Quick Navigation</h4>
                    <ul class="space-y-2 text-xs text-theme-muted">
                        <li><a href="{{ route('home') }}#booking-engine" class="hover:text-cyan-600 transition-colors">Book a Court</a></li>
                        <li><a href="{{ route('home') }}#courts-section" class="hover:text-cyan-600 transition-colors">Courts & Pricing</a></li>
                        <li><a href="{{ route('home') }}#gallery-section" class="hover:text-cyan-600 transition-colors">Facility Photos</a></li>
                        <li><button type="button" onclick="openLookupModal()" class="hover:text-cyan-600 transition-colors cursor-pointer">Search My Booking</button></li>
                        <li><a href="{{ route('owner.dashboard') }}" class="hover:text-cyan-600 transition-colors">Owner & Admin Portal</a></li>
                    </ul>
                </div>

                <!-- Col 3: Hours & Location -->
                <div>
                    <h4 class="text-sm font-bold text-theme-heading mb-4 uppercase tracking-wider">Center Schedule</h4>
                    <ul class="space-y-2 text-xs text-theme-muted">
                        <li class="flex items-center gap-2"><i class="fa-regular fa-clock text-amber-600"></i> Monday - Sunday: 6:00 AM – 12:00 AM (Midnight)</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-map-pin text-rose-500"></i> Bacal 3, Talavera, Nueva Ecija</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-phone text-cyan-600"></i> +63 917 555 7233</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-envelope text-amber-600"></i> contact@paddlefield.com</li>
                    </ul>
                </div>

                <!-- Col 4: Payment Acceptance -->
                <div>
                    <h4 class="text-sm font-bold text-theme-heading mb-4 uppercase tracking-wider">Payment Options</h4>
                    <p class="text-xs text-theme-muted mb-3">
                        We support automated checkout via Xendit API (Instant confirmation) and manual receipt upload (GCash, Maya, Bank Transfer).
                    </p>
                    <div class="flex flex-wrap gap-2 text-xs">
                        <span class="px-2.5 py-1 rounded-lg bg-stone-200 dark:bg-stone-800 text-theme-heading font-semibold border border-stone-300 dark:border-stone-700">GCash</span>
                        <span class="px-2.5 py-1 rounded-lg bg-stone-200 dark:bg-stone-800 text-theme-heading font-semibold border border-stone-300 dark:border-stone-700">Maya</span>
                        <span class="px-2.5 py-1 rounded-lg bg-stone-200 dark:bg-stone-800 text-theme-heading font-semibold border border-stone-300 dark:border-stone-700">Xendit</span>
                        <span class="px-2.5 py-1 rounded-lg bg-stone-200 dark:bg-stone-800 text-theme-heading font-semibold border border-stone-300 dark:border-stone-700">QRPH</span>
                        <span class="px-2.5 py-1 rounded-lg bg-stone-200 dark:bg-stone-800 text-theme-heading font-semibold border border-stone-300 dark:border-stone-700">BDO</span>
                    </div>
                </div>
            </div>

            <div class="mt-12 pt-8 border-t border-stone-200 dark:border-stone-800 flex flex-col md:flex-row items-center justify-between text-xs text-theme-muted gap-4">
                <p>&copy; {{ date('Y') }} Paddle Field Sports Center. All rights reserved.</p>
                <p class="flex items-center gap-1 font-medium">Powered by Laravel & Reverb Real-Time Engine</p>
            </div>
        </div>
    </footer>

    <!-- Booking Lookup Modal -->
    <div id="lookupModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md opacity-0 pointer-events-none transition-all duration-300">
        <div class="glass-dropdown p-6 rounded-3xl max-w-md w-full border border-stone-300 dark:border-stone-700 shadow-2xl relative">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-2xl bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-xl border border-cyan-500/30 shrink-0">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <button type="button" onclick="closeLookupModal()" class="shrink-0 text-theme-muted hover:text-theme-heading p-2 rounded-xl hover:bg-stone-200/50 dark:hover:bg-stone-800/50 transition-colors cursor-pointer -mt-1 -mr-1" title="Close modal">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <h3 class="text-lg font-bold text-theme-heading mb-1">Check Your Reservation</h3>
            <p class="text-xs text-theme-muted mb-5">Enter your Booking Reference (e.g. PF-20260926-XXXX) or your mobile phone number.</p>

            <form action="{{ route('booking.lookup') }}" method="GET" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-theme-body mb-1.5">Booking Reference or Phone</label>
                    <input type="text" name="query" required placeholder="e.g. PF-20260926-A101 or 0917-555-7233"
                        class="w-full px-4 py-3 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 focus:outline-none focus:border-cyan-500 text-sm font-medium">
                </div>
                <button type="submit" class="w-full py-3 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-sm shadow-lg shadow-cyan-500/20 transition-all cursor-pointer">
                    Find Reservation
                </button>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        function openLookupModal() {
            const modal = document.getElementById('lookupModal');
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.classList.add('opacity-100', 'pointer-events-auto');
        }
        function closeLookupModal() {
            const modal = document.getElementById('lookupModal');
            modal.classList.add('opacity-0', 'pointer-events-none');
            modal.classList.remove('opacity-100', 'pointer-events-auto');
        }
    </script>

    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: @json(session('success')),
                confirmButtonColor: '#0891b2'
            });
        });
    </script>
    @endif

    @if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Swal.fire({
                icon: 'error',
                title: 'Notice',
                text: @json(session('error')),
                confirmButtonColor: '#0891b2'
            });
        });
    </script>
    @endif

    @if(session('info'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Swal.fire({
                icon: 'info',
                title: 'Information',
                text: @json(session('info')),
                confirmButtonColor: '#0891b2'
            });
        });
    </script>
    @endif

    @stack('scripts')
</body>
</html>
