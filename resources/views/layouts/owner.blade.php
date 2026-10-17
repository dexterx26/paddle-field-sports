<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light">

    @php
        $globalSettings = $globalSettings ?? \App\Models\VenueSetting::getSettings();
        $venueTabIcon = ($globalSettings->tab_icon_url ?? null) ?: asset('images/default-logo.svg');
        $venueDisplayName = $globalSettings->venue_name ?? 'Paddle Field Sports Center';
    @endphp
    <title>{{ $title ?? 'Owner Portal' }} - {{ $venueDisplayName }}</title>

    <!-- Venue Favicon & Browser Tab Icon -->
    <link rel="icon" type="image/svg+xml" href="{{ $venueTabIcon }}">
    <link rel="alternate icon" href="{{ $venueTabIcon }}">
    <link rel="apple-touch-icon" href="{{ $venueTabIcon }}">

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
<body class="min-h-screen flex selection:bg-cyan-400 selection:text-slate-950 antialiased transition-colors duration-200 overflow-x-hidden w-full max-w-full">
    <!-- Mobile Sidebar Backdrop Overlay -->
    <div id="sidebarBackdrop" onclick="toggleOwnerSidebar(false)" class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-40 transition-opacity duration-300 opacity-0 pointer-events-none md:hidden"></div>

    <!-- Sidebar (Collapsible / Responsive) -->
    <aside id="ownerSidebar" class="w-64 bg-stone-100 border-r border-stone-200 flex flex-col fixed inset-y-0 left-0 z-50 transition-all duration-300 ease-in-out -translate-x-full md:translate-x-0 shadow-2xl md:shadow-none">
        <!-- Brand Header with Minimize / Close Button -->
        <div class="h-20 px-5 flex items-center justify-between border-b border-stone-200">
            <div class="flex items-center gap-3 min-w-0">
                @if(!empty($globalSettings->logo_path))
                    <div class="w-10 h-10 rounded-2xl bg-white border border-stone-200 dark:border-stone-700 p-1 flex items-center justify-center shadow-md shadow-cyan-500/10 overflow-hidden shrink-0">
                        <img src="{{ $globalSettings->logo_url }}" alt="{{ $venueDisplayName }}" class="w-full h-full object-contain">
                    </div>
                @else
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-amber-600 via-amber-700 to-cyan-500 flex items-center justify-center shadow-lg shadow-cyan-500/20 text-white shrink-0">
                        <i class="fa-solid fa-table-tennis-paddle-ball text-lg"></i>
                    </div>
                @endif
                <div class="truncate">
                    <h1 class="text-sm font-extrabold text-theme-heading tracking-tight leading-tight truncate">{{ $venueDisplayName }}</h1>
                    <p class="text-[11px] text-cyan-700 font-bold tracking-wider uppercase truncate">Owner & Admin Hub</p>
                </div>
            </div>

            <!-- Close Button (Mobile Only) -->
            <button type="button" onclick="toggleOwnerSidebar(false)" class="p-2 rounded-xl text-theme-muted hover:text-theme-heading hover:bg-stone-200 transition-colors cursor-pointer shrink-0 md:hidden" title="Close Sidebar">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        <!-- Current Payment Mode Badge in Sidebar -->
        @php
            $globalSettings = \App\Models\VenueSetting::getSettings();
            $pendingBookingsCount = \App\Models\Booking::where('booking_status', 'pending_approval')->count();
            $pendingTopupsCount = \App\Models\CreditTopup::where('status', 'pending')->count();
            $pendingCount = $pendingBookingsCount + $pendingTopupsCount;
        @endphp
        <div class="px-5 py-3 border-b border-stone-200 bg-stone-200/50">
            <div class="flex items-center justify-between text-[11px] mb-1">
                <span class="text-theme-muted">Payment Engine:</span>
                @if($globalSettings->payment_mode === 'xendit')
                    <span class="px-2 py-0.5 rounded-md bg-cyan-500/20 text-cyan-700 font-bold border border-cyan-500/30">Xendit (Auto)</span>
                @elseif($globalSettings->payment_mode === 'paymongo')
                    <span class="px-2 py-0.5 rounded-md bg-green-500/20 text-green-700 font-bold border border-green-500/30">Paymongo (Auto)</span>
                @else
                    <span class="px-2 py-0.5 rounded-md bg-amber-500/20 text-amber-800 font-bold border border-amber-500/30">Manual Receipt</span>
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
                <a href="{{ route('owner.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('owner.dashboard') ? 'bg-cyan-500 text-slate-950 font-bold shadow-md shadow-cyan-500/20' : 'text-theme-body hover:bg-stone-200' }} transition-colors">
                    <i class="fa-solid fa-chart-pie w-4 text-center"></i>
                    <span>Overview & Today</span>
                </a>
            @endif

            <!-- Approvals with Badge -->
            @if(Auth::user()->hasModuleAccess('approvals'))
                <a href="{{ route('owner.approvals') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('owner.approvals') ? 'bg-cyan-500 text-slate-950 font-bold shadow-md shadow-cyan-500/20' : 'text-theme-body hover:bg-stone-200' }} transition-colors">
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
                <a href="{{ route('owner.courts.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('owner.courts.*') ? 'bg-cyan-500 text-slate-950 font-bold shadow-md shadow-cyan-500/20' : 'text-theme-body hover:bg-stone-200' }} transition-colors">
                    <i class="fa-solid fa-table-tennis-paddle-ball w-4 text-center"></i>
                    <span>Courts & Pricing</span>
                </a>
            @endif

            <!-- Photo Uploads for Website -->
            @if(Auth::user()->hasModuleAccess('photos'))
                <a href="{{ route('owner.photos.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('owner.photos.*') ? 'bg-cyan-500 text-slate-950 font-bold shadow-md shadow-cyan-500/20' : 'text-theme-body hover:bg-stone-200' }} transition-colors">
                    <i class="fa-solid fa-images w-4 text-center"></i>
                    <span>Website Photos</span>
                </a>
            @endif

            <!-- All Bookings -->
            @if(Auth::user()->hasModuleAccess('schedule'))
                <a href="{{ route('owner.bookings.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('owner.bookings.*') ? 'bg-cyan-500 text-slate-950 font-bold shadow-md shadow-cyan-500/20' : 'text-theme-body hover:bg-stone-200' }} transition-colors">
                    <i class="fa-solid fa-list-check w-4 text-center"></i>
                    <span>All Reservations</span>
                </a>
            @endif

            <!-- Settings -->
            @if(Auth::user()->hasModuleAccess('settings'))
                <a href="{{ route('owner.settings') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('owner.settings') ? 'bg-cyan-500 text-slate-950 font-bold shadow-md shadow-cyan-500/20' : 'text-theme-body hover:bg-stone-200' }} transition-colors">
                    <i class="fa-solid fa-sliders w-4 text-center"></i>
                    <span>Payment & Center Config</span>
                </a>
            @endif

            <!-- User Management -->
            @if(Auth::user()->hasModuleAccess('users'))
                <a href="{{ route('owner.users.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('owner.users.*') ? 'bg-cyan-500 text-slate-950 font-bold shadow-md shadow-cyan-500/20' : 'text-theme-body hover:bg-stone-200' }} transition-colors">
                    <i class="fa-solid fa-users w-4 text-center"></i>
                    <span>User Management</span>
                </a>
            @endif

            <!-- Admin Assistants Management (Only Court Owner & System Admin) -->
            @if(Auth::user()->canManageStaff())
                <a href="{{ route('owner.assistants.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('owner.assistants.*') ? 'bg-cyan-500 text-slate-950 font-bold shadow-md shadow-cyan-500/20' : 'text-theme-body hover:bg-stone-200' }} transition-colors">
                    <i class="fa-solid fa-users-gear w-4 text-center"></i>
                    <span>Admin Assistants</span>
                </a>
            @endif

            <!-- Profile & Security Link -->
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('profile.*') ? 'bg-cyan-500 text-slate-950 font-bold shadow-md shadow-cyan-500/20' : 'text-theme-body hover:bg-stone-200' }} transition-colors">
                <i class="fa-solid fa-user-gear w-4 text-center"></i>
                <span>My Profile & Password</span>
            </a>

            <div class="pt-4 border-t border-stone-200">
                <a href="{{ route('home') }}" target="_blank" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-medium text-theme-muted hover:text-cyan-600 transition-colors">
                    <i class="fa-solid fa-arrow-up-right-from-square w-4 text-center"></i>
                    <span>View Public Website</span>
                </a>
            </div>
        </nav>

        <!-- User Profile Bar -->
        <div class="p-4 border-t border-stone-200 bg-stone-200/40">
            <div class="flex items-center justify-between">
                <a href="{{ route('profile.edit') }}" title="Edit Profile & Security" class="flex items-center gap-3 group flex-1 min-w-0 pr-2">
                    <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-8 h-8 rounded-full object-cover border border-cyan-500/40 shadow-xs group-hover:scale-105 transition-transform">
                    <div class="text-xs truncate">
                        <div class="font-bold text-theme-heading leading-tight truncate group-hover:text-cyan-600 transition-colors">{{ Auth::user()->name ?? 'User' }}</div>
                        <div class="text-[10px] text-cyan-600 font-bold capitalize flex items-center gap-1">
                            <span>{{ str_replace('_', ' ', Auth::user()->role ?? 'Staff') }}</span>
                            <i class="fa-solid fa-pen text-[9px] opacity-60"></i>
                        </div>
                    </div>
                </a>
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
    <div id="ownerMainContent" class="flex-1 min-w-0 ml-0 md:ml-64 flex flex-col min-h-screen transition-all duration-300 ease-in-out">
        <!-- Topbar -->
        <header class="h-20 border-b border-stone-200 glass-panel px-3.5 sm:px-6 md:px-8 flex items-center justify-between sticky top-0 z-30 min-w-0 w-full max-w-full">
            <div class="flex items-center gap-3 min-w-0">
                <!-- Sidebar Toggle Button (Mobile Drawer) -->
                <button type="button" id="ownerSidebarToggle" onclick="toggleOwnerSidebar()" class="p-2 sm:p-2.5 rounded-xl bg-stone-200 hover:bg-stone-300 text-theme-heading flex items-center justify-center transition-all cursor-pointer shadow-xs shrink-0 md:hidden" title="Toggle Sidebar Menu">
                    <i class="fa-solid fa-bars-staggered text-sm"></i>
                </button>
                <div class="min-w-0">
                    <h2 class="text-sm sm:text-base font-bold text-theme-heading truncate">{{ $headerTitle ?? 'Owner Portal' }}</h2>
                    <p class="text-[11px] sm:text-xs text-theme-muted truncate hidden sm:block">Manage Paddle Field Sports Center court operations</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3.5">
                <!-- Environment & DB Indicator -->
                <div class="hidden sm:block">
                    @if(app()->isProduction())
                        <!-- <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-700 border border-emerald-500/30" title="Environment: Production | Database: MySQL">
                            <i class="fa-solid fa-cloud text-[10px]"></i> PROD (MySQL)
                        </span> -->
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-700 border border-amber-500/30" title="Environment: Local | Database: SQLite">
                            <i class="fa-solid fa-laptop-code text-[10px]"></i> DEV (SQLite)
                        </span>
                    @endif
                </div>

                <!-- Reverb Status Indicator -->
                <div class="reverb-status-indicator hidden sm:block">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-cyan-500/10 text-cyan-700 border border-cyan-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-ping"></span> Reverb Live
                    </span>
                </div>

                <!-- Fast Demo Role Switcher -->
                @if(!app()->isProduction())
                    <div class="relative group">
                        <button type="button" class="px-2.5 sm:px-3 py-1.5 rounded-xl bg-stone-200 hover:bg-stone-300 text-xs font-semibold text-theme-heading border border-stone-300 flex items-center gap-1.5 transition-all cursor-pointer">
                            <i class="fa-solid fa-bolt text-amber-500"></i>
                            <span class="hidden sm:inline">Switch Role</span>
                            <i class="fa-solid fa-chevron-down text-[10px] text-theme-muted"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 py-2 glass-dropdown rounded-2xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
                            <a href="{{ route('quick.login', 'owner') }}" class="block px-3 py-1.5 text-xs text-theme-heading hover:bg-cyan-500/10 hover:text-cyan-600">Court Owner</a>
                            <a href="{{ route('quick.login', 'assistant') }}" class="block px-3 py-1.5 text-xs text-theme-heading hover:bg-emerald-500/10 hover:text-emerald-700">Admin Assistant</a>
                            <a href="{{ route('quick.login', 'admin') }}" class="block px-3 py-1.5 text-xs text-theme-heading hover:bg-amber-500/10 hover:text-amber-700">System Admin</a>
                            <a href="{{ route('quick.login', 'player') }}" class="block px-3 py-1.5 text-xs text-theme-heading hover:bg-cyan-500/10 hover:text-cyan-600">Client / Player</a>
                        </div>
                    </div>
                @endif

                <!-- Add Court Shortcut -->
                @if(Auth::user()->hasModuleAccess('courts'))
                    <a href="{{ route('owner.courts.index') }}" class="px-3 sm:px-3.5 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 text-xs font-bold shadow-md shadow-cyan-500/20 flex items-center gap-1.5 sm:gap-2 transition-all cursor-pointer">
                        <i class="fa-solid fa-plus"></i>
                        <span class="hidden sm:inline">Add Court</span>
                    </a>
                @endif

                <!-- My Profile Shortcut -->
                <a href="{{ route('profile.edit') }}" title="My Profile & Security" class="p-2 sm:px-3 sm:py-2 rounded-xl bg-stone-200 hover:bg-stone-300 text-theme-heading text-xs font-bold border border-stone-300 flex items-center gap-2 transition-all cursor-pointer">
                    <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-5 h-5 sm:w-4 sm:h-4 rounded-full object-cover border border-cyan-500/40">
                    <span class="hidden md:inline">Profile</span>
                </a>
            </div>
        </header>

        <!-- Flash messages -->
        <div class="px-3.5 sm:px-6 md:px-8 pt-4 sm:pt-6 min-w-0 w-full max-w-full">
            @if(session('success'))
                <div class="p-4 mb-4 rounded-2xl bg-cyan-500/10 border border-cyan-500/30 text-cyan-900 text-sm flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check text-cyan-600 text-lg"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-theme-muted hover:text-theme-heading cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 mb-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-900 text-sm flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-rose-600 text-lg"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-theme-muted hover:text-theme-heading cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
                </div>
            @endif

            @if(session('info'))
                <div class="p-4 mb-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-900 text-sm flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-info text-amber-600 text-lg"></i>
                        <span>{{ session('info') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-theme-muted hover:text-theme-heading cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 mb-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-900 text-sm flex items-start justify-between">
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg mt-0.5 shrink-0"></i>
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
        <main class="flex-1 min-w-0 w-full max-w-full px-3.5 sm:px-6 md:px-8 py-4 sm:py-6 overflow-x-hidden">
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

    <!-- Owner Sidebar Toggle Script (Mobile Drawer & Desktop Minimize) -->
    <script>
        function toggleOwnerSidebar(forceState) {
            const sidebar = document.getElementById('ownerSidebar');
            const mainContent = document.getElementById('ownerMainContent');
            const backdrop = document.getElementById('sidebarBackdrop');
            if (!sidebar) return;

            const isMobile = window.innerWidth < 768;

            if (isMobile) {
                // Mobile behavior: Drawer overlay
                const isOpen = sidebar.classList.contains('translate-x-0');
                const shouldOpen = forceState !== undefined ? forceState : !isOpen;

                if (shouldOpen) {
                    sidebar.classList.remove('-translate-x-full');
                    sidebar.classList.add('translate-x-0');
                    if (backdrop) {
                        backdrop.classList.remove('opacity-0', 'pointer-events-none');
                        backdrop.classList.add('opacity-100', 'pointer-events-auto');
                    }
                    document.body.classList.add('overflow-hidden');
                } else {
                    sidebar.classList.add('-translate-x-full');
                    sidebar.classList.remove('translate-x-0');
                    if (backdrop) {
                        backdrop.classList.add('opacity-0', 'pointer-events-none');
                        backdrop.classList.remove('opacity-100', 'pointer-events-auto');
                    }
                    document.body.classList.remove('overflow-hidden');
                }
            } else {
                // Desktop behavior: Sidebar is persistent. If explicitly toggled, minimize / expand cleanly without overlap.
                if (forceState === false) {
                    return;
                }

                const isMinimized = sidebar.classList.contains('md:-translate-x-full');
                const shouldMinimize = forceState !== undefined ? !forceState : !isMinimized;

                if (shouldMinimize) {
                    sidebar.classList.remove('md:translate-x-0');
                    sidebar.classList.add('md:-translate-x-full');
                    if (mainContent) {
                        mainContent.classList.remove('md:ml-64');
                        mainContent.classList.add('md:ml-0');
                    }
                } else {
                    sidebar.classList.remove('md:-translate-x-full');
                    sidebar.classList.add('md:translate-x-0');
                    if (mainContent) {
                        mainContent.classList.remove('md:ml-0');
                        mainContent.classList.add('md:ml-64');
                    }
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Remove any obsolete localStorage item that was causing desktop overlap
            try {
                localStorage.removeItem('owner_sidebar_minimized');
            } catch (e) {}

            // Ensure desktop sidebar is properly positioned and never overlapping
            if (window.innerWidth >= 768) {
                const sidebar = document.getElementById('ownerSidebar');
                const mainContent = document.getElementById('ownerMainContent');
                if (sidebar) {
                    sidebar.classList.remove('md:-translate-x-full', 'translate-x-0');
                    sidebar.classList.add('md:translate-x-0');
                }
                if (mainContent) {
                    mainContent.classList.remove('md:ml-0');
                    mainContent.classList.add('md:ml-64');
                }
            }

            // Close mobile sidebar when a navigation link is clicked
            const navLinks = document.querySelectorAll('#ownerSidebar nav a');
            navLinks.forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth < 768) {
                        toggleOwnerSidebar(false);
                    }
                });
            });

            // Close on Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && window.innerWidth < 768) {
                    toggleOwnerSidebar(false);
                }
            });

            // Ensure mobile backdrop, drawer and overflow are cleaned up if window resizes to desktop
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 768) {
                    const backdrop = document.getElementById('sidebarBackdrop');
                    if (backdrop) {
                        backdrop.classList.add('opacity-0', 'pointer-events-none');
                        backdrop.classList.remove('opacity-100', 'pointer-events-auto');
                    }
                    document.body.classList.remove('overflow-hidden');

                    const sidebar = document.getElementById('ownerSidebar');
                    const mainContent = document.getElementById('ownerMainContent');
                    if (sidebar) {
                        sidebar.classList.remove('translate-x-0', 'md:-translate-x-full');
                        sidebar.classList.add('md:translate-x-0');
                    }
                    if (mainContent) {
                        mainContent.classList.remove('md:ml-0');
                        mainContent.classList.add('md:ml-64');
                    }
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
