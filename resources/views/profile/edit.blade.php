@extends('layouts.app')

@section('content')
<div class="py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
    
    <!-- Top Breadcrumb & Actions Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 mb-8 border-b border-stone-200 dark:border-stone-800">
        <div>
            <div class="flex items-center gap-2 text-xs text-theme-muted mb-1">
                <a href="{{ route('home') }}" class="hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors">Home</a>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                <span class="text-theme-heading font-semibold">User Profile</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-theme-heading tracking-tight flex items-center gap-3">
                <span>Account & Profile Settings</span>
            </h1>
            <p class="text-xs sm:text-sm text-theme-muted mt-0.5">
                Manage your personal information, contact credentials, profile picture, and account security.
            </p>
        </div>

        <div class="flex items-center gap-3">
            @if(Auth::user()->isStaffOrAdmin())
                <a href="{{ route(Auth::user()->getFirstAllowedRoute()) }}" class="px-4 py-2 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-stone-300 dark:hover:bg-stone-700 text-xs font-bold text-theme-heading border border-stone-300 dark:border-stone-700 flex items-center gap-2 transition-all">
                    <i class="fa-solid fa-gauge-high text-cyan-600 dark:text-cyan-400"></i>
                    <span>{{ Auth::user()->isAdminAssistant() ? 'Staff Portal' : 'Owner Portal' }}</span>
                </a>
            @endif
            <a href="{{ route('home') }}#booking-engine" class="px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 text-xs font-extrabold shadow-md shadow-cyan-500/20 flex items-center gap-2 transition-all">
                <i class="fa-solid fa-calendar-plus"></i>
                <span>Reserve Court</span>
            </a>
        </div>
    </div>

    <!-- User Hero Overview Card -->
    <div class="glass-panel rounded-3xl p-6 sm:p-8 shadow-xl border border-stone-200 dark:border-stone-800 mb-8 relative overflow-hidden">
        <div class="absolute -right-12 -top-12 w-48 h-48 bg-gradient-to-br from-cyan-500/10 to-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
            <!-- Left: Avatar & Primary Info -->
            <div class="flex items-center gap-5">
                <div class="relative group">
                    <img id="header-avatar-preview" src="{{ $user->avatar_url }}" alt="{{ $user->name }}" 
                        class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl sm:rounded-3xl object-cover border-2 border-cyan-500/40 shadow-lg shadow-cyan-500/15">
                    <label for="avatar-input" class="absolute inset-0 bg-stone-950/60 rounded-2xl sm:rounded-3xl flex flex-col items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer text-xs font-bold gap-1 backdrop-blur-xs">
                        <i class="fa-solid fa-camera text-sm"></i>
                        <span>Change</span>
                    </label>
                </div>

                <div class="space-y-1.5">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h2 class="text-xl sm:text-2xl font-black text-theme-heading">{{ $user->name }}</h2>
                        
                        @if($user->isAdmin())
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold uppercase bg-amber-500/15 text-amber-800 dark:text-amber-300 border border-amber-500/30">
                                System Administrator
                            </span>
                        @elseif($user->isOwner())
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold uppercase bg-emerald-500/15 text-emerald-800 dark:text-emerald-300 border border-emerald-500/30">
                                Court Owner
                            </span>
                        @elseif($user->isAdminAssistant())
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold uppercase bg-teal-500/15 text-teal-800 dark:text-teal-300 border border-teal-500/30">
                                Admin Assistant
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold uppercase bg-cyan-500/15 text-cyan-800 dark:text-cyan-300 border border-cyan-500/30">
                                Registered Player
                            </span>
                        @endif

                        @if($user->google_id)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-700 text-theme-muted shadow-xs">
                                <i class="fa-brands fa-google text-rose-500 text-[10px]"></i> Google Linked
                            </span>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-4 text-xs text-theme-muted">
                        <div class="flex items-center gap-1.5">
                            <i class="fa-solid fa-envelope text-cyan-600 dark:text-cyan-400"></i>
                            <span>{{ $user->email }}</span>
                        </div>
                        @if($user->phone)
                            <div class="flex items-center gap-1.5">
                                <i class="fa-solid fa-phone text-amber-600 dark:text-amber-400"></i>
                                <span>{{ $user->phone }}</span>
                            </div>
                        @endif
                        <div class="flex items-center gap-1.5">
                            <i class="fa-regular fa-calendar-check text-theme-muted"></i>
                            <span>Member since {{ $user->created_at ? $user->created_at->format('M Y') : 'Recently' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Quick Stat Badges -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 border-t md:border-t-0 md:border-l border-stone-200 dark:border-stone-800 pt-4 md:pt-0 md:pl-6">
                <div class="p-3 rounded-2xl bg-stone-100/80 dark:bg-stone-900/60 border border-stone-200/80 dark:border-stone-800 text-center">
                    <div class="text-xs text-theme-muted font-semibold">Total Bookings</div>
                    <div class="text-xl font-black text-cyan-600 dark:text-cyan-400 mt-0.5">{{ $totalBookings }}</div>
                </div>
                <div class="p-3 rounded-2xl bg-stone-100/80 dark:bg-stone-900/60 border border-stone-200/80 dark:border-stone-800 text-center">
                    <div class="text-xs text-theme-muted font-semibold">Active / Upcoming</div>
                    <div class="text-xl font-black text-amber-600 dark:text-amber-400 mt-0.5">{{ $activeBookings }}</div>
                </div>
                <div class="p-3 rounded-2xl bg-stone-100/80 dark:bg-stone-900/60 border border-stone-200/80 dark:border-stone-800 text-center col-span-2 sm:col-span-1">
                    <div class="text-xs text-theme-muted font-semibold">Account Status</div>
                    <div class="text-sm font-extrabold text-emerald-600 dark:text-emerald-400 mt-1 flex items-center justify-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Active</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Grid: Profile Form & Password Security -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Left 7 Columns: Personal Details & Password Change -->
        <div class="lg:col-span-7 space-y-8">
            
            <!-- SECTION 1: Personal Details Form -->
            <div class="glass-panel rounded-3xl p-6 sm:p-8 shadow-xl border border-stone-200 dark:border-stone-800">
                <div class="flex items-center gap-3 pb-5 mb-6 border-b border-stone-200 dark:border-stone-800">
                    <div class="w-10 h-10 rounded-xl bg-cyan-500/15 text-cyan-700 dark:text-cyan-400 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-user-pen"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-theme-heading">Personal Information</h3>
                        <p class="text-xs text-theme-muted">Update your display name, contact phone, and profile photo.</p>
                    </div>
                </div>

                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5 text-xs">
                    @csrf
                    @method('PUT')

                    <!-- Avatar Upload Picker -->
                    <div>
                        <label class="block font-bold text-theme-body mb-2">Profile Avatar</label>
                        <div class="flex items-center gap-4">
                            <img id="form-avatar-preview" src="{{ $user->avatar_url }}" alt="{{ $user->name }}" 
                                class="w-16 h-16 rounded-2xl object-cover border border-stone-300 dark:border-stone-700 shadow-sm">
                            
                            <div class="space-y-1.5 flex-1">
                                <input type="file" name="avatar" id="avatar-input" accept="image/jpeg,image/png,image/webp,image/jpg"
                                    onchange="previewAvatar(this)"
                                    class="block w-full text-xs text-theme-muted file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-cyan-500/15 file:text-cyan-700 dark:file:text-cyan-300 hover:file:bg-cyan-500/25 file:cursor-pointer cursor-pointer">
                                <p class="text-[11px] text-theme-muted">Supported formats: JPG, PNG, WEBP. Max size: 2MB.</p>
                                
                                @if($user->avatar)
                                    <label class="inline-flex items-center gap-2 mt-1 cursor-pointer">
                                        <input type="checkbox" name="remove_avatar" value="1" class="rounded text-rose-500 focus:ring-rose-400">
                                        <span class="text-[11px] font-semibold text-rose-600 dark:text-rose-400">Remove uploaded avatar (reset to default initials)</span>
                                    </label>
                                @endif
                            </div>
                        </div>
                        @error('avatar')
                            <span class="text-[11px] text-rose-500 mt-1.5 block font-semibold">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Full Name -->
                    <div>
                        <label for="name" class="block font-bold text-theme-body mb-1">Full Name <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-theme-muted">
                                <i class="fa-solid fa-user text-xs"></i>
                            </span>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                                class="w-full pl-10 pr-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 focus:outline-none focus:border-cyan-500 transition-colors">
                        </div>
                        @error('name')
                            <span class="text-[11px] text-rose-500 mt-1 block font-semibold">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block font-bold text-theme-body mb-1">Email Address <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-theme-muted">
                                <i class="fa-solid fa-envelope text-xs"></i>
                            </span>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                                class="w-full pl-10 pr-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 focus:outline-none focus:border-cyan-500 transition-colors">
                        </div>
                        <p class="text-[11px] text-theme-muted mt-1">Booking receipts and notification emails are sent to this address.</p>
                        @error('email')
                            <span class="text-[11px] text-rose-500 mt-1 block font-semibold">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Contact Phone -->
                    <div>
                        <label for="phone" class="block font-bold text-theme-body mb-1">Contact Phone Number</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-theme-muted">
                                <i class="fa-solid fa-phone text-xs"></i>
                            </span>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" placeholder="e.g. 0917-555-7233"
                                class="w-full pl-10 pr-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 focus:outline-none focus:border-cyan-500 transition-colors">
                        </div>
                        <p class="text-[11px] text-theme-muted mt-1">Used by staff to coordinate court reservation confirmations.</p>
                        @error('phone')
                            <span class="text-[11px] text-rose-500 mt-1 block font-semibold">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-3 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs shadow-md shadow-cyan-500/20 flex items-center gap-2 transition-all hover:scale-102 cursor-pointer">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>Save Profile Changes</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- SECTION 2: Security & Password Form -->
            <div class="glass-panel rounded-3xl p-6 sm:p-8 shadow-xl border border-stone-200 dark:border-stone-800">
                <div class="flex items-center gap-3 pb-5 mb-6 border-b border-stone-200 dark:border-stone-800">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/15 text-amber-700 dark:text-amber-400 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-theme-heading">Password & Security</h3>
                        <p class="text-xs text-theme-muted">Ensure your account is using a secure, long password.</p>
                    </div>
                </div>

                @if(!$user->hasPassword())
                    <div class="p-3.5 rounded-2xl bg-cyan-500/10 border border-cyan-500/30 text-xs text-cyan-900 dark:text-cyan-200 flex items-start gap-3 mb-5">
                        <i class="fa-solid fa-circle-info text-cyan-600 dark:text-cyan-400 text-sm mt-0.5"></i>
                        <div>
                            <strong class="font-bold">Signed in via Google OAuth:</strong>
                            <p class="text-[11px] mt-0.5">You currently do not have a password configured. Setting a new password below will allow you to sign in with both Google and your email/password.</p>
                        </div>
                    </div>
                @endif

                <form action="{{ route('profile.password.update') }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    @method('PUT')

                    @if($user->hasPassword())
                        <!-- Current Password -->
                        <div>
                            <label for="current_password" class="block font-bold text-theme-body mb-1">Current Password <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-theme-muted">
                                    <i class="fa-solid fa-lock text-xs"></i>
                                </span>
                                <input type="password" name="current_password" id="current_password" required placeholder="Enter current password"
                                    class="w-full pl-10 pr-10 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 focus:outline-none focus:border-cyan-500 transition-colors">
                                <button type="button" onclick="togglePasswordVisibility('current_password', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-theme-muted hover:text-theme-heading cursor-pointer">
                                    <i class="fa-regular fa-eye text-xs"></i>
                                </button>
                            </div>
                            @error('current_password')
                                <span class="text-[11px] text-rose-500 mt-1 block font-semibold">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif

                    <!-- New Password -->
                    <div>
                        <label for="new_password" class="block font-bold text-theme-body mb-1">New Password <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-theme-muted">
                                <i class="fa-solid fa-key text-xs"></i>
                            </span>
                            <input type="password" name="password" id="new_password" required minlength="8" placeholder="Minimum 8 characters"
                                class="w-full pl-10 pr-10 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 focus:outline-none focus:border-cyan-500 transition-colors">
                            <button type="button" onclick="togglePasswordVisibility('new_password', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-theme-muted hover:text-theme-heading cursor-pointer">
                                <i class="fa-regular fa-eye text-xs"></i>
                            </button>
                        </div>
                        @error('password')
                            <span class="text-[11px] text-rose-500 mt-1 block font-semibold">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Confirm New Password -->
                    <div>
                        <label for="password_confirmation" class="block font-bold text-theme-body mb-1">Confirm New Password <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-theme-muted">
                                <i class="fa-solid fa-check-double text-xs"></i>
                            </span>
                            <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8" placeholder="Repeat new password"
                                class="w-full pl-10 pr-10 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 focus:outline-none focus:border-cyan-500 transition-colors">
                            <button type="button" onclick="togglePasswordVisibility('password_confirmation', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-theme-muted hover:text-theme-heading cursor-pointer">
                                <i class="fa-regular fa-eye text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Password Button -->
                    <div class="pt-3 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow-md shadow-amber-600/20 flex items-center gap-2 transition-all hover:scale-102 cursor-pointer">
                            <i class="fa-solid fa-key"></i>
                            <span>Update Password</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <!-- Right 5 Columns: Connected Accounts, Recent Bookings & Venue Info -->
        <div class="lg:col-span-5 space-y-8">
            
            <!-- Connected Accounts Card -->
            <div class="glass-panel rounded-3xl p-6 shadow-xl border border-stone-200 dark:border-stone-800">
                <h3 class="text-sm font-extrabold text-theme-heading mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-link text-cyan-600 dark:text-cyan-400"></i>
                    <span>Connected Sign-in Methods</span>
                </h3>

                <div class="space-y-3 text-xs">
                    <!-- Google Account Status -->
                    <div class="p-3.5 rounded-2xl bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-stone-100 dark:bg-stone-800 flex items-center justify-center text-base">
                                <i class="fa-brands fa-google text-rose-500"></i>
                            </div>
                            <div>
                                <div class="font-bold text-theme-heading">Google / Gmail</div>
                                <div class="text-[10px] text-theme-muted">
                                    {{ $user->google_id ? 'Connected to Google Account' : 'Not linked to Google' }}
                                </div>
                            </div>
                        </div>

                        @if($user->google_id)
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30 flex items-center gap-1">
                                <i class="fa-solid fa-check text-[9px]"></i> Connected
                            </span>
                        @else
                            <a href="{{ route('auth.social.redirect', 'google') }}" class="px-3 py-1.5 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-stone-300 dark:hover:bg-stone-700 text-[10px] font-bold text-theme-heading transition-colors">
                                Link Account
                            </a>
                        @endif
                    </div>

                    <!-- Email & Password Status -->
                    <div class="p-3.5 rounded-2xl bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-stone-100 dark:bg-stone-800 flex items-center justify-center text-base">
                                <i class="fa-solid fa-envelope text-cyan-600"></i>
                            </div>
                            <div>
                                <div class="font-bold text-theme-heading">Email & Password</div>
                                <div class="text-[10px] text-theme-muted">
                                    {{ $user->hasPassword() ? 'Direct email credentials active' : 'Password not set yet' }}
                                </div>
                            </div>
                        </div>

                        @if($user->hasPassword())
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30 flex items-center gap-1">
                                <i class="fa-solid fa-check text-[9px]"></i> Enabled
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-800 dark:text-amber-400 border border-amber-500/30">
                                Optional
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Court Reservations Card -->
            <div class="glass-panel rounded-3xl p-6 shadow-xl border border-stone-200 dark:border-stone-800">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-extrabold text-theme-heading flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-amber-500"></i>
                        <span>Recent Reservations</span>
                    </h3>
                    <button type="button" onclick="openLookupModal()" class="text-[11px] font-bold text-cyan-600 dark:text-cyan-400 hover:underline cursor-pointer">
                        Lookup Reference
                    </button>
                </div>

                @if($recentBookings->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentBookings as $booking)
                            <div class="p-3 rounded-2xl bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 hover:border-cyan-500/40 transition-colors text-xs">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="font-mono font-bold text-cyan-700 dark:text-cyan-400">
                                        {{ $booking->booking_reference }}
                                    </span>
                                    
                                    @php
                                        $badgeClasses = match($booking->booking_status) {
                                            'confirmed' => 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border-emerald-500/30',
                                            'pending_approval' => 'bg-amber-500/15 text-amber-800 dark:text-amber-400 border-amber-500/30',
                                            'held' => 'bg-cyan-500/15 text-cyan-700 dark:text-cyan-400 border-cyan-500/30',
                                            'cancelled', 'rejected' => 'bg-rose-500/15 text-rose-700 dark:text-rose-400 border-rose-500/30',
                                            default => 'bg-stone-200 text-stone-600 border-stone-300'
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase border {{ $badgeClasses }}">
                                        {{ str_replace('_', ' ', $booking->booking_status) }}
                                    </span>
                                </div>

                                <div class="text-[11px] text-theme-heading font-bold">
                                    {{ $booking->court->name ?? 'Court' }}
                                </div>

                                <div class="text-[11px] text-theme-muted mt-0.5 flex items-center justify-between">
                                    <span>
                                        {{ \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') }} 
                                        ({{ $booking->slots->pluck('time_slot')->join(', ') }})
                                    </span>
                                    <span class="font-bold text-theme-heading">
                                        ₱{{ number_format($booking->total_amount, 2) }}
                                    </span>
                                </div>

                                <div class="mt-2 pt-2 border-t border-stone-100 dark:border-stone-800/60 flex justify-end">
                                    <a href="{{ route('booking.track', $booking->booking_reference) }}" class="text-[10px] font-extrabold text-cyan-600 dark:text-cyan-400 hover:underline flex items-center gap-1">
                                        <span>View Tracking Status</span>
                                        <i class="fa-solid fa-arrow-right text-[8px]"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center text-theme-muted text-xs space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-stone-100 dark:bg-stone-800 flex items-center justify-center mx-auto text-xl text-theme-muted">
                            <i class="fa-solid fa-table-tennis-paddle-ball"></i>
                        </div>
                        <p>No court reservations yet. Ready for your first pickleball match?</p>
                        <a href="{{ route('home') }}#booking-engine" class="inline-block px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs shadow-md shadow-cyan-500/20 transition-all">
                            Book a Court Now
                        </a>
                    </div>
                @endif
            </div>

            <!-- Venue Support & Hours Card -->
            <div class="p-6 rounded-3xl bg-lead text-cascading-white shadow-xl relative overflow-hidden">
                <div class="relative z-10 space-y-3 text-xs">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center">
                            <i class="fa-solid fa-headset"></i>
                        </div>
                        <h4 class="font-bold text-sm text-cascading-white">Center Support & Inquiries</h4>
                    </div>

                    <p class="text-chinese-silver text-[11px] leading-relaxed">
                        Need to reschedule your reserved court time or inquire regarding corporate tournaments? Contact our clubhouse desk:
                    </p>

                    <div class="space-y-1.5 text-[11px] pt-1 border-t border-stone-800">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-phone text-cyan-400 w-4"></i>
                            <span>{{ $settings->phone ?? '+63 917 555 7233' }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-envelope text-amber-400 w-4"></i>
                            <span>{{ $settings->email ?? 'contact@paddlefield.com' }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-clock text-cyan-400 w-4"></i>
                            <span>Daily: {{ $settings->opening_time ?? '06:00' }} - {{ $settings->closing_time ?? '00:00' }} Midnight</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

@push('styles')
<script>
    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const headerImg = document.getElementById('header-avatar-preview');
                const formImg = document.getElementById('form-avatar-preview');
                if (headerImg) headerImg.src = e.target.result;
                if (formImg) formImg.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function togglePasswordVisibility(inputId, button) {
        const input = document.getElementById(inputId);
        const icon = button.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endpush
@endsection
