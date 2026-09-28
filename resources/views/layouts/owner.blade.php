<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Owner Portal' }} - Paddle Field Sports Center</title>

    <!-- Light Mode Initialization -->
    <script>
        document.documentElement.classList.remove('dark');
        localStorage.removeItem('theme');
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Vite Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="min-h-screen flex selection:bg-cyan-400 selection:text-slate-950 antialiased transition-colors duration-200">
    <!-- Sidebar -->
    <aside class="w-64 bg-stone-100 dark:bg-stone-900 border-r border-stone-200 dark:border-stone-800 flex flex-col fixed inset-y-0 z-40 transition-colors">
        <!-- Brand Header -->
        <div class="h-20 px-6 flex items-center gap-3 border-b border-stone-200 dark:border-stone-800">
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-amber-600 via-amber-700 to-cyan-500 flex items-center justify-center shadow-lg shadow-cyan-500/20 text-white">
                <i class="fa-solid fa-table-tennis-paddle-ball text-lg"></i>
            </div>
            <div>
                <h1 class="text-sm font-extrabold text-theme-heading tracking-tight leading-tight">Paddle Field</h1>
                <p class="text-[11px] text-cyan-700 dark:text-cyan-400 font-bold tracking-wider uppercase">Owner & Admin Hub</p>
            </div>
        </div>

        <!-- Current Payment Mode Badge in Sidebar -->
        @php
            $globalSettings = \App\Models\VenueSetting::getSettings();
            $pendingCount = \App\Models\Booking::where('booking_status', 'pending_approval')->count();
        @endphp
        <div class="px-5 py-3 border-b border-stone-200 dark:border-stone-800/80 bg-stone-200/50 dark:bg-stone-950/40">
            <div class="flex items-center justify-between text-[11px] mb-1">
                <span class="text-theme-muted">Payment Engine:</span>
                @if($globalSettings->payment_mode === 'xendit')
                    <span class="px-2 py-0.5 rounded-md bg-cyan-500/20 text-cyan-700 dark:text-cyan-400 font-bold border border-cyan-500/30">Xendit (Auto)</span>
                @elseif($globalSettings->payment_mode === 'paymongo')
                    <span class="px-2 py-0.5 rounded-md bg-green-500/20 text-green-700 dark:text-green-400 font-bold border border-green-500/30">Paymongo (Auto)</span>
                @else
                    <span class="px-2 py-0.5 rounded-md bg-amber-500/20 text-amber-800 dark:text-amber-400 font-bold border border-amber-500/30">Manual Receipt</span>
                @endif
            </div>
            <div class="text-[10px] text-theme-muted">
                @if($globalSettings->payment_mode === 'xendit' || $globalSettings->payment_mode === 'paymongo')
                    Holds for 2 minutes, auto-confirms on payment completion
                @else
                    Client uploads proof, owner approves
                @endif
            </div>
        </div>

        <!-- Nav Items -->
        <nav class="flex-1 px-4 py-4 space-y-1.5 overflow-y-auto">
            @if(Auth::user()->hasModuleAccess('schedule'))
                <a href="{{ route('owner.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('owner.dashboard') ? 'bg-cyan-500 text-slate-950 font-bold shadow-md shadow-cyan-500/20' : 'text-theme-body hover:bg-stone-200 dark:hover:bg-stone-800' }} transition-colors">
                    <i class="fa-solid fa-chart-pie w-4 text-center"></i>
                    <span>Overview & Today</span>
                </a>
            @endif

            <!-- Approvals with Badge -->
            @if(Auth::user()->hasModuleAccess('approvals'))
                <a href="{{ route('owner.approvals') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('owner.approvals') ? 'bg-cyan-500 text-slate-950 font-bold shadow-md shadow-cyan-500/20' : 'text-theme-body hover:bg-stone-200 dark:hover:bg-stone-800' }} transition-colors">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-file-invoice-dollar w-4 text-center"></i>
                        <span>Receipt Approvals</span>
                    </div>
                    @if($pendingCount > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ request()->routeIs('owner.approvals') ? 'bg-slate-950 text-cyan-400' : 'bg-amber-500 text-slate-950 animate-pulse' }}">
                            {{ $pendingCount }}
                        </span>
                    @endif
                </a>
            @endif

            <!-- Court Management -->
            @if(Auth::user()->hasModuleAccess('courts'))
                <a href="{{ route('owner.courts.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('owner.courts.*') ? 'bg-cyan-500 text-slate-950 font-bold shadow-md shadow-cyan-500/20' : 'text-theme-body hover:bg-stone-200 dark:hover:bg-stone-800' }} transition-colors">
                    <i class="fa-solid fa-table-tennis-paddle-ball w-4 text-center"></i>
                    <span>Courts & Pricing</span>
                </a>
            @endif

            <!-- Photo Uploads for Website -->
            @if(Auth::user()->hasModuleAccess('photos'))
                <a href="{{ route('owner.photos.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('owner.photos.*') ? 'bg-cyan-500 text-slate-950 font-bold shadow-md shadow-cyan-500/20' : 'text-theme-body hover:bg-stone-200 dark:hover:bg-stone-800' }} transition-colors">
                    <i class="fa-solid fa-images w-4 text-center"></i>
                    <span>Website Photos</span>
                </a>
            @endif

            <!-- All Bookings -->
            @if(Auth::user()->hasModuleAccess('schedule'))
                <a href="{{ route('owner.bookings.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('owner.bookings.*') ? 'bg-cyan-500 text-slate-950 font-bold shadow-md shadow-cyan-500/20' : 'text-theme-body hover:bg-stone-200 dark:hover:bg-stone-800' }} transition-colors">
                    <i class="fa-solid fa-list-check w-4 text-center"></i>
                    <span>All Reservations</span>
                </a>
            @endif

            <!-- Settings -->
            @if(Auth::user()->hasModuleAccess('settings'))
                <a href="{{ route('owner.settings') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('owner.settings') ? 'bg-cyan-500 text-slate-950 font-bold shadow-md shadow-cyan-500/20' : 'text-theme-body hover:bg-stone-200 dark:hover:bg-stone-800' }} transition-colors">
                    <i class="fa-solid fa-sliders w-4 text-center"></i>
                    <span>Payment & Center Config</span>
                </a>
            @endif

            <!-- Admin Assistants Management (Only Court Owner & System Admin) -->
            @if(Auth::user()->canManageStaff())
                <a href="{{ route('owner.assistants.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('owner.assistants.*') ? 'bg-cyan-500 text-slate-950 font-bold shadow-md shadow-cyan-500/20' : 'text-theme-body hover:bg-stone-200 dark:hover:bg-stone-800' }} transition-colors">
                    <i class="fa-solid fa-users-gear w-4 text-center"></i>
                    <span>Admin Assistants</span>
                </a>
            @endif

            <div class="pt-4 border-t border-stone-200 dark:border-stone-800">
                <a href="{{ route('home') }}" target="_blank" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-medium text-theme-muted hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors">
                    <i class="fa-solid fa-arrow-up-right-from-square w-4 text-center"></i>
                    <span>View Public Website</span>
                </a>
            </div>
        </nav>

        <!-- User Profile Bar -->
        <div class="p-4 border-t border-stone-200 dark:border-stone-800 bg-stone-200/40 dark:bg-stone-950/60">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-cyan-500/20 border border-cyan-500/40 flex items-center justify-center text-xs font-bold text-cyan-700 dark:text-cyan-400">
                        {{ strtoupper(substr(Auth::user()->name ?? 'O', 0, 1)) }}
                    </div>
                    <div class="text-xs">
                        <div class="font-bold text-theme-heading leading-tight truncate w-28">{{ Auth::user()->name ?? 'Owner' }}</div>
                        <div class="text-[10px] text-cyan-600 dark:text-cyan-400 font-bold capitalize">{{ str_replace('_', ' ', Auth::user()->role ?? 'Court Owner') }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Logout" class="p-2 text-theme-muted hover:text-rose-500 transition-colors cursor-pointer">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Container -->
    <div class="flex-1 ml-64 flex flex-col min-h-screen">
        <!-- Topbar -->
        <header class="h-20 border-b border-stone-200 dark:border-stone-800 glass-panel px-8 flex items-center justify-between sticky top-0 z-30">
            <div>
                <h2 class="text-base font-bold text-theme-heading">{{ $headerTitle ?? 'Owner Portal' }}</h2>
                <p class="text-xs text-theme-muted">Manage Paddle Field Sports Center court operations</p>
            </div>

            <div class="flex items-center gap-3.5">
                <!-- Reverb Status Indicator -->
                <div class="reverb-status-indicator">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-cyan-500/10 text-cyan-700 border border-cyan-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-ping"></span> Reverb Live
                    </span>
                </div>

                <!-- Fast Demo Role Switcher -->
                <div class="relative group">
                    <button type="button" class="px-3 py-1.5 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-stone-300 dark:hover:bg-stone-700 text-xs font-semibold text-theme-heading border border-stone-300 dark:border-stone-700 flex items-center gap-1.5 transition-all cursor-pointer">
                        <i class="fa-solid fa-bolt text-amber-500"></i>
                        <span>Switch Role</span>
                        <i class="fa-solid fa-chevron-down text-[10px] text-theme-muted"></i>
                    </button>
                    <div class="absolute right-0 mt-2 w-48 py-2 glass-dropdown rounded-2xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
                        <a href="{{ route('quick.login', 'owner') }}" class="block px-3 py-1.5 text-xs text-theme-heading hover:bg-cyan-500/10 hover:text-cyan-600 dark:hover:text-cyan-400">Court Owner</a>
                        <a href="{{ route('quick.login', 'assistant') }}" class="block px-3 py-1.5 text-xs text-theme-heading hover:bg-emerald-500/10 hover:text-emerald-700 dark:hover:text-emerald-400">Admin Assistant</a>
                        <a href="{{ route('quick.login', 'admin') }}" class="block px-3 py-1.5 text-xs text-theme-heading hover:bg-amber-500/10 hover:text-amber-700 dark:hover:text-amber-400">System Admin</a>
                        <a href="{{ route('quick.login', 'player') }}" class="block px-3 py-1.5 text-xs text-theme-heading hover:bg-cyan-500/10 hover:text-cyan-600 dark:hover:text-cyan-400">Client / Player</a>
                    </div>
                </div>

                <!-- Add Court Shortcut -->
                @if(Auth::user()->hasModuleAccess('courts'))
                    <a href="{{ route('owner.courts.index') }}" class="px-3.5 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 text-xs font-bold shadow-md shadow-cyan-500/20 flex items-center gap-2 transition-all cursor-pointer">
                        <i class="fa-solid fa-plus"></i>
                        <span>Add Court</span>
                    </a>
                @endif
            </div>
        </header>

        <!-- Flash messages -->
        <div class="px-8 pt-6">
            @if(session('success'))
                <div class="p-4 mb-4 rounded-2xl bg-cyan-500/10 border border-cyan-500/30 text-cyan-900 dark:text-cyan-200 text-sm flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check text-cyan-600 dark:text-cyan-400 text-lg"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-theme-muted hover:text-theme-heading cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 mb-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-900 dark:text-rose-200 text-sm flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-rose-600 dark:text-rose-400 text-lg"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-theme-muted hover:text-theme-heading cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
                </div>
            @endif

            @if(session('info'))
                <div class="p-4 mb-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-900 dark:text-amber-200 text-sm flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-info text-amber-600 dark:text-amber-400 text-lg"></i>
                        <span>{{ session('info') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-theme-muted hover:text-theme-heading cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 mb-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-900 dark:text-rose-200 text-sm flex items-start justify-between">
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-circle-exclamation text-rose-600 dark:text-rose-400 text-lg mt-0.5 shrink-0"></i>
                        <div>
                            <span class="font-bold">Please check the following error(s):</span>
                            <ul class="list-disc list-inside text-xs mt-1 space-y-0.5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-theme-muted hover:text-theme-heading cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
                </div>
            @endif
        </div>

        <!-- Main Body -->
        <main class="flex-1 px-8 py-6">
            @yield('content')
        </main>
    </div>


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

    @if($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Swal.fire({
                icon: 'error',
                title: 'Action Failed',
                html: `<div class="text-left text-xs space-y-1.5 mt-2">@foreach($errors->all() as $error)<div class="text-rose-600 font-semibold flex items-start gap-1.5"><span class="shrink-0">•</span><span>{{ addslashes($error) }}</span></div>@endforeach</div>`,
                confirmButtonColor: '#e11d48'
            });
        });
    </script>
    @endif

    @stack('scripts')
</body>
</html>
