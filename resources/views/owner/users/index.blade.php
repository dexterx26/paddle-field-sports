@extends('layouts.owner')

@section('content')
<div class="space-y-6">
    <!-- Top Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-stone-200 dark:border-stone-800 gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h2 class="text-xl font-bold text-theme-heading">User Management</h2>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-cyan-500/15 text-cyan-700 dark:text-cyan-300 border border-cyan-500/30 uppercase tracking-wider">
                    {{ ucwords(str_replace('_', ' ', $roleFilter ?: 'All Roles')) }}
                </span>
            </div>
            <p class="text-xs text-theme-muted mt-1">Manage system administrators, court owners, staff assistants, and registered player accounts.</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" onclick="openAddUserModal()"
                class="px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-black text-xs shadow-lg shadow-cyan-500/20 flex items-center gap-2 transition-all cursor-pointer">
                <i class="fa-solid fa-user-plus"></i> Add New User
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="p-4 rounded-2xl glass-panel border border-stone-200 dark:border-stone-800 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-lg">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <p class="text-xs text-theme-muted font-medium">Total Registered</p>
                <h3 class="text-xl font-extrabold text-theme-heading">{{ $counts['all'] }}</h3>
            </div>
        </div>

        <div class="p-4 rounded-2xl glass-panel border border-stone-200 dark:border-stone-800 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg">
                <i class="fa-solid fa-user"></i>
            </div>
            <div>
                <p class="text-xs text-theme-muted font-medium">Clients / Players</p>
                <h3 class="text-xl font-extrabold text-theme-heading">{{ $counts['client'] }}</h3>
            </div>
        </div>

        <div class="p-4 rounded-2xl glass-panel border border-stone-200 dark:border-stone-800 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                <i class="fa-solid fa-user-tie"></i>
            </div>
            <div>
                <p class="text-xs text-theme-muted font-medium">Admin Assistants</p>
                <h3 class="text-xl font-extrabold text-theme-heading">{{ $counts['admin_assistant'] }}</h3>
            </div>
        </div>

        <div class="p-4 rounded-2xl glass-panel border border-stone-200 dark:border-stone-800 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <p class="text-xs text-theme-muted font-medium">Owners & Admins</p>
                <h3 class="text-xl font-extrabold text-theme-heading">{{ $counts['court_owner'] + $counts['admin'] }}</h3>
            </div>
        </div>

        <a href="{{ route('owner.users.index', array_merge(request()->except('status', 'page'), ['status' => $statusFilter === 'has_held' ? null : 'has_held'])) }}"
            class="p-4 rounded-2xl glass-panel border {{ $statusFilter === 'has_held' ? 'border-amber-500/60 bg-amber-500/10 shadow-lg shadow-amber-500/10' : 'border-stone-200 dark:border-stone-800 hover:border-amber-500/40' }} flex items-center gap-4 transition-all group cursor-pointer"
            title="Click to filter users with held timeslots">
            <div class="w-12 h-12 rounded-xl bg-amber-500/15 text-amber-700 dark:text-amber-400 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div>
                <p class="text-xs text-theme-muted font-medium flex items-center gap-1.5">
                    <span>Held Slots</span>
                    @if($counts['has_held'] > 0)
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    @endif
                </p>
                <h3 class="text-xl font-extrabold text-theme-heading">{{ $counts['total_held_slots'] }}</h3>
                <p class="text-[10px] text-amber-800 dark:text-amber-300 font-semibold">{{ $counts['has_held'] }} {{ Str::plural('user', $counts['has_held']) }} flagged</p>
            </div>
        </a>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="p-5 rounded-3xl glass-card border border-stone-200 dark:border-stone-800 space-y-4">
        <!-- Role Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
            <span class="text-xs font-bold text-theme-muted mr-1">Role:</span>
            <a href="{{ route('owner.users.index', array_merge(request()->except('role', 'page'), ['role' => null])) }}"
                class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5 {{ empty($roleFilter) ? 'bg-cyan-500 text-slate-950 shadow-md shadow-cyan-500/20' : 'bg-stone-200 dark:bg-stone-800 text-theme-body hover:bg-stone-300 dark:hover:bg-stone-700' }}">
                <span>All Users</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ empty($roleFilter) ? 'bg-slate-950 text-cyan-400' : 'bg-stone-300 dark:bg-stone-700 text-theme-muted' }}">{{ $counts['all'] }}</span>
            </a>

            <a href="{{ route('owner.users.index', array_merge(request()->except('role', 'page'), ['role' => 'client'])) }}"
                class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5 {{ $roleFilter === 'client' ? 'bg-cyan-500 text-slate-950 shadow-md shadow-cyan-500/20' : 'bg-stone-200 dark:bg-stone-800 text-theme-body hover:bg-stone-300 dark:hover:bg-stone-700' }}">
                <span>Players / Clients</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $roleFilter === 'client' ? 'bg-slate-950 text-cyan-400' : 'bg-stone-300 dark:bg-stone-700 text-theme-muted' }}">{{ $counts['client'] }}</span>
            </a>

            <a href="{{ route('owner.users.index', array_merge(request()->except('role', 'page'), ['role' => 'admin_assistant'])) }}"
                class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5 {{ $roleFilter === 'admin_assistant' ? 'bg-emerald-500 text-slate-950 shadow-md shadow-emerald-500/20' : 'bg-stone-200 dark:bg-stone-800 text-theme-body hover:bg-stone-300 dark:hover:bg-stone-700' }}">
                <span>Admin Assistants</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $roleFilter === 'admin_assistant' ? 'bg-slate-950 text-emerald-400' : 'bg-stone-300 dark:bg-stone-700 text-theme-muted' }}">{{ $counts['admin_assistant'] }}</span>
            </a>

            <a href="{{ route('owner.users.index', array_merge(request()->except('role', 'page'), ['role' => 'court_owner'])) }}"
                class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5 {{ $roleFilter === 'court_owner' ? 'bg-cyan-500 text-slate-950 shadow-md shadow-cyan-500/20' : 'bg-stone-200 dark:bg-stone-800 text-theme-body hover:bg-stone-300 dark:hover:bg-stone-700' }}">
                <span>Court Owners</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $roleFilter === 'court_owner' ? 'bg-slate-950 text-cyan-400' : 'bg-stone-300 dark:bg-stone-700 text-theme-muted' }}">{{ $counts['court_owner'] }}</span>
            </a>

            <a href="{{ route('owner.users.index', array_merge(request()->except('role', 'page'), ['role' => 'admin'])) }}"
                class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5 {{ $roleFilter === 'admin' ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/20' : 'bg-stone-200 dark:bg-stone-800 text-theme-body hover:bg-stone-300 dark:hover:bg-stone-700' }}">
                <span>System Admins</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $roleFilter === 'admin' ? 'bg-slate-950 text-amber-400' : 'bg-stone-300 dark:bg-stone-700 text-theme-muted' }}">{{ $counts['admin'] }}</span>
            </a>
        </div>

        <!-- Search & Status Row -->
        <form method="GET" action="{{ route('owner.users.index') }}" class="flex flex-col md:flex-row items-center gap-3 text-xs">
            @if($roleFilter)
                <input type="hidden" name="role" value="{{ $roleFilter }}">
            @endif

            <div class="flex-1 w-full relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-xs"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by user name, email, or phone number..."
                    class="w-full pl-9 pr-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 focus:border-cyan-500 focus:outline-none">
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
                <select name="sort" onchange="this.form.submit()"
                    class="px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-medium focus:border-cyan-500 focus:outline-none">
                    <option value="" {{ empty($sort) ? 'selected' : '' }}>Role Order</option>
                    <option value="held_desc" {{ ($sort ?? '') === 'held_desc' ? 'selected' : '' }}>Most Held Timeslots</option>
                </select>

                <select name="status" onchange="this.form.submit()"
                    class="px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-medium focus:border-cyan-500 focus:outline-none">
                    <option value="" {{ empty($statusFilter) ? 'selected' : '' }}>All Statuses</option>
                    <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Active Only ({{ $counts['active'] }})</option>
                    <option value="inactive" {{ $statusFilter === 'inactive' ? 'selected' : '' }}>Deactivated Only ({{ $counts['inactive'] }})</option>
                    <option value="has_held" {{ $statusFilter === 'has_held' ? 'selected' : '' }}>⚠️ With Held Slots ({{ $counts['has_held'] }})</option>
                </select>

                <button type="submit"
                    class="px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold transition-all cursor-pointer shrink-0">
                    Filter
                </button>

                @if($search || $statusFilter || $roleFilter || !empty($sort))
                    <a href="{{ route('owner.users.index') }}"
                        class="px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 text-theme-muted hover:text-theme-heading transition-all whitespace-nowrap text-center">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="glass-panel border border-stone-200 dark:border-stone-800 rounded-3xl overflow-hidden shadow-xl">
        <div class="p-5 border-b border-stone-200 dark:border-stone-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <h3 class="font-bold text-sm text-theme-heading">Registered Accounts</h3>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-stone-200 dark:bg-stone-800 text-theme-muted">
                    {{ $users->total() }} total users
                </span>
            </div>
        </div>

        @if($users->isEmpty())
            <div class="p-12 text-center space-y-3">
                <div class="w-16 h-16 rounded-2xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 flex items-center justify-center mx-auto text-2xl">
                    <i class="fa-solid fa-user-xmark"></i>
                </div>
                <h4 class="text-base font-bold text-theme-heading">No Users Found</h4>
                <p class="text-xs text-theme-muted max-w-sm mx-auto">
                    No registered user accounts matched your search or filter criteria.
                </p>
                <button type="button" onclick="openAddUserModal()"
                    class="px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs inline-flex items-center gap-2 transition-all cursor-pointer">
                    <i class="fa-solid fa-plus"></i> Add New User
                </button>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-theme-body">
                    <thead class="bg-stone-100 dark:bg-stone-900/50 text-theme-muted uppercase tracking-wider text-[11px] border-b border-stone-200 dark:border-stone-800">
                        <tr>
                            <th class="py-3.5 px-6">User</th>
                            <th class="py-3.5 px-6">Role</th>
                            <th class="py-3.5 px-6">Phone</th>
                            <th class="py-3.5 px-6">Permissions / Activity</th>
                            <th class="py-3.5 px-6 text-center">Held Timeslots</th>
                            <th class="py-3.5 px-6 text-center">Status</th>
                            <th class="py-3.5 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-200 dark:divide-stone-800">
                        @foreach($users as $user)
                            @php
                                $roleBadge = match($user->role) {
                                    'admin' => ['label' => 'System Admin', 'bg' => 'bg-amber-500/15', 'text' => 'text-amber-800 dark:text-amber-300', 'border' => 'border-amber-500/30', 'icon' => 'fa-shield-halved'],
                                    'court_owner' => ['label' => 'Court Owner', 'bg' => 'bg-cyan-500/15', 'text' => 'text-cyan-700 dark:text-cyan-300', 'border' => 'border-cyan-500/30', 'icon' => 'fa-crown'],
                                    'admin_assistant' => ['label' => 'Admin Assistant', 'bg' => 'bg-emerald-500/15', 'text' => 'text-emerald-800 dark:text-emerald-300', 'border' => 'border-emerald-500/30', 'icon' => 'fa-user-tie'],
                                    default => ['label' => 'Player / Client', 'bg' => 'bg-stone-200 dark:bg-stone-800', 'text' => 'text-theme-body', 'border' => 'border-stone-300 dark:border-stone-700', 'icon' => 'fa-user'],
                                };
                                $perms = $user->permissions ?? [];
                                if (!is_array($perms)) {
                                    $perms = json_decode($perms, true) ?? [];
                                }
                                $heldCount = (int) ($user->held_timeslots_count ?? 0);
                                $activeHeldCount = (int) ($user->active_held_slots_count ?? 0);
                            @endphp
                            <tr class="hover:bg-stone-100/50 dark:hover:bg-stone-900/50 transition-colors">
                                <!-- User Info -->
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-2xl {{ $roleBadge['bg'] }} {{ $roleBadge['text'] }} border {{ $roleBadge['border'] }} flex items-center justify-center font-bold text-sm shrink-0">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-theme-heading flex items-center gap-1.5">
                                                <span class="truncate">{{ $user->name }}</span>
                                                @if($user->id === Auth::id())
                                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-extrabold bg-cyan-500 text-slate-950 uppercase">You</span>
                                                @endif
                                            </div>
                                            <div class="text-[11px] text-theme-muted font-mono truncate">{{ $user->email }}</div>
                                            <div class="text-[10px] text-stone-400 mt-0.5">Joined {{ $user->created_at->format('M d, Y') }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Role Badge -->
                                <td class="py-4 px-6 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold {{ $roleBadge['bg'] }} {{ $roleBadge['text'] }} border {{ $roleBadge['border'] }}">
                                        <i class="fa-solid {{ $roleBadge['icon'] }} text-[10px]"></i>
                                        {{ $roleBadge['label'] }}
                                    </span>
                                </td>

                                <!-- Phone -->
                                <td class="py-4 px-6 font-mono text-[11px] whitespace-nowrap">
                                    {{ $user->phone ?: '—' }}
                                </td>

                                <!-- Details / Permissions -->
                                <td class="py-4 px-6">
                                    @if($user->role === 'admin_assistant')
                                        <div class="space-y-1">
                                            <div class="flex flex-wrap gap-1">
                                                @if(in_array('schedule', $perms))
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-cyan-500/10 text-cyan-700 dark:text-cyan-300 border border-cyan-500/30">
                                                        Schedule
                                                    </span>
                                                @endif
                                                @if(in_array('approvals', $perms))
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30">
                                                        Approvals
                                                    </span>
                                                @endif
                                                @if(in_array('courts', $perms))
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border border-indigo-500/30">
                                                        Courts
                                                    </span>
                                                @endif
                                                @if(in_array('photos', $perms))
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-500/10 text-purple-700 dark:text-purple-300 border border-purple-500/30">
                                                        Photos
                                                    </span>
                                                @endif
                                                @if(in_array('users', $perms))
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-700 dark:text-amber-300 border border-amber-500/30">
                                                        Users
                                                    </span>
                                                @endif
                                                @if(in_array('settings', $perms))
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-stone-500/10 text-stone-700 dark:text-stone-300 border border-stone-500/30">
                                                        Settings
                                                    </span>
                                                @endif
                                                @if(empty($perms))
                                                    <span class="px-2 py-0.5 rounded text-[10px] bg-stone-200 dark:bg-stone-800 text-theme-muted">None</span>
                                                @endif
                                            </div>
                                            @if($user->courtOwner)
                                                <div class="text-[10px] text-theme-muted">
                                                    Supervisor: <strong class="text-theme-heading">{{ $user->courtOwner->name }}</strong>
                                                </div>
                                            @endif
                                        </div>
                                    @elseif($user->role === 'client')
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-1.5 text-theme-muted">
                                                <i class="fa-solid fa-calendar-check text-[11px] text-cyan-600 dark:text-cyan-400"></i>
                                                <span><strong>{{ $user->bookings_count }}</strong> total bookings</span>
                                            </div>
                                            @if($heldCount > 0)
                                                <div class="flex items-center gap-1.5 text-[11px] {{ $heldCount >= 3 ? 'text-rose-600 dark:text-rose-400 font-bold' : 'text-amber-600 dark:text-amber-400 font-medium' }}">
                                                    <i class="fa-solid fa-triangle-exclamation text-[10px]"></i>
                                                    <span>{{ $heldCount }} held {{ Str::plural('slot', $heldCount) }} without payment</span>
                                                </div>
                                            @endif
                                        </div>
                                    @elseif($user->role === 'court_owner')
                                        <div class="text-[11px] text-theme-muted">
                                            <span>Full venue management</span>
                                        </div>
                                    @else
                                        <div class="text-[11px] text-amber-600 dark:text-amber-400 font-semibold">
                                            <i class="fa-solid fa-lock-open text-[10px] mr-1"></i> Full System Access
                                        </div>
                                    @endif
                                </td>

                                <!-- Held Timeslots Counter -->
                                <td class="py-4 px-6 text-center whitespace-nowrap">
                                    @if($heldCount > 0)
                                        <button type="button"
                                            onclick="openHeldSlotsModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', {{ $heldCount }}, {{ $activeHeldCount }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer shadow-sm group
                                                {{ $heldCount >= 3 
                                                    ? 'bg-rose-500/15 text-rose-700 dark:text-rose-400 border border-rose-500/30 hover:bg-rose-500/25' 
                                                    : 'bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-500/30 hover:bg-amber-500/25' }}"
                                            title="Click to inspect held timeslots and abandoned checkouts">
                                            <i class="fa-solid fa-clock-rotate-left text-[11px] group-hover:rotate-[-45deg] transition-transform"></i>
                                            <span><strong>{{ $heldCount }}</strong> held {{ Str::plural('slot', $heldCount) }}</span>
                                            @if($activeHeldCount > 0)
                                                <span class="px-1.5 py-0.2 rounded-full text-[9px] font-extrabold bg-cyan-500 text-slate-950 uppercase animate-pulse" title="{{ $activeHeldCount }} currently active hold in checkout">
                                                    {{ $activeHeldCount }} live
                                                </span>
                                            @endif
                                        </button>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-medium bg-stone-100 dark:bg-stone-800 text-stone-400 dark:text-stone-500 border border-stone-200 dark:border-stone-700">
                                            <i class="fa-solid fa-check text-[10px] text-emerald-500/70"></i>
                                            0 held
                                        </span>
                                    @endif
                                </td>

                                <!-- Status -->
                                <td class="py-4 px-6 text-center whitespace-nowrap" id="user-status-cell-{{ $user->id }}">
                                    @if($user->id === Auth::id() || (Auth::user()->isAdminAssistant() && ($user->isAdmin() || $user->isOwner())) || (Auth::user()->isOwner() && $user->isAdmin()))
                                        <!-- Protected toggle button -->
                                        @if($user->is_active)
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30 opacity-70 cursor-not-allowed" title="Protected account - cannot modify status">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-500/30 opacity-70 cursor-not-allowed" title="Protected account - cannot modify status">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                Deactivated
                                            </span>
                                        @endif
                                    @else
                                        <button type="button"
                                            id="status-btn-{{ $user->id }}"
                                            onclick="handleStatusClick({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', '{{ addslashes($roleBadge['label']) }}', {{ $user->is_active ? 1 : 0 }}, '{{ addslashes($user->deactivation_reason ?? '') }}')"
                                            title="{{ $user->is_active ? 'Click to edit status / deactivate account' : 'Click to edit status / reactivate account' }}"
                                            class="inline-flex flex-col items-center gap-1 cursor-pointer group focus:outline-none transition-transform active:scale-95">
                                            @if($user->is_active)
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30 group-hover:bg-emerald-500/20 group-hover:border-emerald-500/50 shadow-sm transition-all">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                    <span>Active</span>
                                                    <i class="fa-solid fa-pen text-[8px] opacity-40 group-hover:opacity-100 transition-opacity"></i>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-500/30 group-hover:bg-rose-500/20 group-hover:border-rose-500/50 shadow-sm transition-all">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                    <span>Deactivated</span>
                                                    <i class="fa-solid fa-pen text-[8px] opacity-40 group-hover:opacity-100 transition-opacity"></i>
                                                </span>
                                                @if(!empty($user->deactivation_reason))
                                                    <span class="text-[9px] text-theme-muted max-w-[140px] truncate block opacity-75 group-hover:opacity-100 transition-opacity" title="Reason: {{ $user->deactivation_reason }}">
                                                        <i class="fa-solid fa-circle-info text-[8px] mr-0.5 text-rose-400"></i>{{ $user->deactivation_reason }}
                                                    </span>
                                                @endif
                                            @endif
                                        </button>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="py-4 px-6 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        @php
                                            $canEdit = true;
                                            if (Auth::user()->isAdminAssistant() && ($user->isAdmin() || $user->isOwner())) {
                                                $canEdit = false;
                                            }
                                            if (Auth::user()->isOwner() && $user->isAdmin()) {
                                                $canEdit = false;
                                            }
                                        @endphp

                                        @if($canEdit)
                                            <!-- Reset Password Button -->
                                            <form method="POST" action="{{ route('owner.users.reset_password', $user->id) }}"
                                                id="reset-password-form-{{ $user->id }}" class="inline-block">
                                                @csrf
                                                <button type="button"
                                                    onclick="confirmResetPassword({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}')"
                                                    class="p-2 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-amber-500/10 hover:text-amber-500 text-theme-muted transition-colors cursor-pointer"
                                                    title="Reset Password to PaddleField2026!">
                                                    <i class="fa-solid fa-key"></i>
                                                </button>
                                            </form>

                                            <button type="button"
                                                onclick='openEditUserModal(@json($user), @json($perms))'
                                                class="p-2 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-cyan-500/10 hover:text-cyan-600 dark:hover:text-cyan-400 text-theme-muted transition-colors cursor-pointer"
                                                title="Edit User">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
                <div class="p-5 border-t border-stone-200 dark:border-stone-800">
                    {{ $users->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

<!-- ========================================== -->
<!-- ADD USER MODAL                             -->
<!-- ========================================== -->
<div id="addUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm hidden transition-all">
    <div class="w-full max-w-xl max-h-[90vh] overflow-y-auto rounded-3xl glass-dropdown border border-stone-200 dark:border-stone-800 p-6 md:p-8 shadow-2xl relative">
        <button type="button" onclick="closeAddUserModal()" class="absolute top-5 right-5 w-8 h-8 rounded-full bg-stone-200 dark:bg-stone-800 flex items-center justify-center text-theme-muted hover:text-theme-heading cursor-pointer">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-2xl bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-lg">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-theme-heading">Add New User</h3>
                <p class="text-xs text-theme-muted">Create a new user account and configure role and permissions.</p>
            </div>
        </div>

        <form action="{{ route('owner.users.store') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Full Name -->
            <div>
                <label class="block text-xs font-bold text-theme-heading mb-1.5">Full Name *</label>
                <input type="text" name="name" required placeholder="e.g. Carlos Ramos"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none">
            </div>

            <!-- Email & Phone -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-theme-heading mb-1.5">Email Address *</label>
                    <input type="email" name="email" required placeholder="user@paddlefield.com"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-theme-heading mb-1.5">Phone Number</label>
                    <input type="text" name="phone" placeholder="+63 918 000 0000"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                </div>
            </div>

            <!-- Role Selection -->
            <div>
                <label class="block text-xs font-bold text-theme-heading mb-1.5">Assigned Role *</label>
                <select name="role" id="add_role_select" onchange="toggleAddAssistantSection(this.value)" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading font-medium focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                    <option value="client" selected>Player / Client (Standard public reservation user)</option>
                    <option value="admin_assistant">Admin Assistant (Staff member with assigned module access)</option>
                    @if(Auth::user()->canManageStaff())
                        <option value="court_owner">Court Owner (Full venue control & staff management)</option>
                    @endif
                    @if(Auth::user()->isAdmin())
                        <option value="admin">System Administrator (Root unrestricted platform access)</option>
                    @endif
                </select>
            </div>

            <!-- Admin Assistant Specific Configuration (Shown only when role is admin_assistant) -->
            <div id="add_assistant_section" class="hidden space-y-4 pt-3 border-t border-stone-200 dark:border-stone-800">
                @if(Auth::user()->isAdmin())
                    <div>
                        <label class="block text-xs font-bold text-theme-heading mb-1.5">Supervisor (Court Owner)</label>
                        <select name="court_owner_id"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                            <option value="">-- No Direct Supervisor --</option>
                            @foreach($courtOwners as $owner)
                                <option value="{{ $owner->id }}">{{ $owner->name }} ({{ $owner->email }})</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <h4 class="text-xs font-extrabold text-theme-heading uppercase tracking-wider mb-1">Module Permissions</h4>
                    <p class="text-[11px] text-theme-muted mb-3">Select which modules this assistant is authorized to access.</p>

                    <div class="space-y-2">
                        @foreach($availableModules as $key => $mod)
                            <label class="flex items-start gap-3 p-3 rounded-2xl border border-stone-200 dark:border-stone-800 hover:border-cyan-500/40 bg-stone-100/70 dark:bg-stone-900/40 cursor-pointer transition-all">
                                <input type="checkbox" name="modules[]" value="{{ $key }}"
                                    {{ $mod['default'] ? 'checked' : '' }}
                                    class="mt-0.5 rounded text-cyan-500 focus:ring-cyan-500 focus:ring-offset-0 w-4 h-4 cursor-pointer">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid {{ $mod['icon'] }} text-xs text-cyan-600 dark:text-cyan-400"></i>
                                        <span class="text-xs font-bold text-theme-heading">{{ $mod['name'] }}</span>
                                        @if($mod['default'])
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-extrabold bg-cyan-500 text-slate-950 uppercase">Default</span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] text-theme-muted mt-0.5 leading-snug">{{ $mod['description'] }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Password Fields -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-theme-heading mb-1.5">Password *</label>
                    <input type="password" name="password" required minlength="6" placeholder="••••••••"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-theme-heading mb-1.5">Confirm Password *</label>
                    <input type="password" name="password_confirmation" required minlength="6" placeholder="••••••••"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                </div>
            </div>

            <!-- Active Checkbox -->
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="is_active" id="add_user_is_active" value="1" checked
                    class="rounded text-cyan-500 focus:ring-cyan-500 focus:ring-offset-0 w-4 h-4 cursor-pointer">
                <label for="add_user_is_active" class="text-xs font-semibold text-theme-heading cursor-pointer">
                    Account is active immediately
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-stone-200 dark:border-stone-800">
                <button type="button" onclick="closeAddUserModal()"
                    class="px-4 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 text-xs font-semibold text-theme-muted hover:text-theme-heading cursor-pointer">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 text-xs font-bold shadow-md shadow-cyan-500/20 cursor-pointer transition-all">
                    Create User
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- EDIT USER MODAL                            -->
<!-- ========================================== -->
<div id="editUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm hidden transition-all">
    <div class="w-full max-w-xl max-h-[90vh] overflow-y-auto rounded-3xl glass-dropdown border border-stone-200 dark:border-stone-800 p-6 md:p-8 shadow-2xl relative">
        <button type="button" onclick="closeEditUserModal()" class="absolute top-5 right-5 w-8 h-8 rounded-full bg-stone-200 dark:bg-stone-800 flex items-center justify-center text-theme-muted hover:text-theme-heading cursor-pointer">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-2xl bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-lg">
                <i class="fa-solid fa-user-pen"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-theme-heading">Edit User Account</h3>
                <p class="text-xs text-theme-muted">Update user credentials, role assignment, and access controls.</p>
            </div>
        </div>

        <form id="editUserForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Full Name -->
            <div>
                <label class="block text-xs font-bold text-theme-heading mb-1.5">Full Name *</label>
                <input type="text" id="edit_user_name" name="name" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none">
            </div>

            <!-- Email & Phone -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-theme-heading mb-1.5">Email Address *</label>
                    <input type="email" id="edit_user_email" name="email" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-theme-heading mb-1.5">Phone Number</label>
                    <input type="text" id="edit_user_phone" name="phone"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                </div>
            </div>

            <!-- Role Selection -->
            <div>
                <label class="block text-xs font-bold text-theme-heading mb-1.5">Assigned Role *</label>
                <select name="role" id="edit_user_role" onchange="toggleEditAssistantSection(this.value)" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading font-medium focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                    <option value="client">Player / Client (Standard public reservation user)</option>
                    <option value="admin_assistant">Admin Assistant (Staff member with assigned module access)</option>
                    @if(Auth::user()->canManageStaff())
                        <option value="court_owner">Court Owner (Full venue control & staff management)</option>
                    @endif
                    @if(Auth::user()->isAdmin())
                        <option value="admin">System Administrator (Root unrestricted platform access)</option>
                    @endif
                </select>
            </div>

            <!-- Admin Assistant Specific Configuration -->
            <div id="edit_assistant_section" class="hidden space-y-4 pt-3 border-t border-stone-200 dark:border-stone-800">
                @if(Auth::user()->isAdmin())
                    <div>
                        <label class="block text-xs font-bold text-theme-heading mb-1.5">Supervisor (Court Owner)</label>
                        <select name="court_owner_id" id="edit_user_court_owner_id"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                            <option value="">-- No Direct Supervisor --</option>
                            @foreach($courtOwners as $owner)
                                <option value="{{ $owner->id }}">{{ $owner->name }} ({{ $owner->email }})</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <h4 class="text-xs font-extrabold text-theme-heading uppercase tracking-wider mb-1">Module Permissions</h4>
                    <p class="text-[11px] text-theme-muted mb-3">Configure which modules this assistant is authorized to access.</p>

                    <div class="space-y-2">
                        @foreach($availableModules as $key => $mod)
                            <label class="flex items-start gap-3 p-3 rounded-2xl border border-stone-200 dark:border-stone-800 hover:border-cyan-500/40 bg-stone-100/70 dark:bg-stone-900/40 cursor-pointer transition-all">
                                <input type="checkbox" name="modules[]" value="{{ $key }}" id="edit_user_module_{{ $key }}"
                                    class="mt-0.5 rounded text-cyan-500 focus:ring-cyan-500 focus:ring-offset-0 w-4 h-4 cursor-pointer">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid {{ $mod['icon'] }} text-xs text-cyan-600 dark:text-cyan-400"></i>
                                        <span class="text-xs font-bold text-theme-heading">{{ $mod['name'] }}</span>
                                        @if($mod['default'])
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-extrabold bg-cyan-500 text-slate-950 uppercase">Default</span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] text-theme-muted mt-0.5 leading-snug">{{ $mod['description'] }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Optional Password Change -->
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-theme-heading">New Password (Blank to keep)</label>
                    <button type="button"
                        onclick="document.getElementById('edit_user_password').value='PaddleField2026!'; document.getElementById('edit_user_password_confirmation').value='PaddleField2026!';"
                        class="text-[11px] font-semibold text-cyan-600 dark:text-cyan-400 hover:underline cursor-pointer">
                        <i class="fa-solid fa-key text-[10px] mr-1"></i>Fill with PaddleField2026!
                    </button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <input type="password" id="edit_user_password" name="password" minlength="6" placeholder="••••••••"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                    </div>
                    <div>
                        <input type="password" id="edit_user_password_confirmation" name="password_confirmation" minlength="6" placeholder="Confirm new password"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Active Checkbox & Deactivation Reason -->
            <div class="space-y-2 pt-1">
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="edit_user_is_active" value="1"
                        onchange="toggleEditDeactivationReason(this.checked)"
                        class="rounded text-cyan-500 focus:ring-cyan-500 focus:ring-offset-0 w-4 h-4 cursor-pointer">
                    <label for="edit_user_is_active" class="text-xs font-semibold text-theme-heading cursor-pointer">
                        Account is active and able to sign in
                    </label>
                </div>
                <div id="edit_deactivation_reason_container" class="hidden space-y-1.5 pl-6">
                    <label for="edit_user_deactivation_reason" class="block text-[11px] font-bold text-rose-500">
                        Deactivation Reason
                    </label>
                    <textarea name="deactivation_reason" id="edit_user_deactivation_reason" rows="2"
                        placeholder="State reason for deactivating this user..."
                        class="w-full px-3 py-2 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-rose-500 focus:outline-none resize-none"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-stone-200 dark:border-stone-800">
                <button type="button" onclick="closeEditUserModal()"
                    class="px-4 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 text-xs font-semibold text-theme-muted hover:text-theme-heading cursor-pointer">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 text-xs font-bold shadow-md shadow-cyan-500/20 cursor-pointer transition-all">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
<!-- ========================================== -->
<!-- HELD TIMESLOTS DETAILS MODAL               -->
<!-- ========================================== -->
<div id="heldSlotsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm hidden transition-all">
    <div class="w-full max-w-2xl max-h-[90vh] flex flex-col rounded-3xl glass-dropdown border border-stone-200 dark:border-stone-800 shadow-2xl relative overflow-hidden bg-white/95 dark:bg-stone-900/95">
        <!-- Header -->
        <div class="p-6 border-b border-stone-200 dark:border-stone-800 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-theme-heading flex items-center gap-2">
                        <span>Held Timeslots History</span>
                    </h3>
                    <p class="text-xs text-theme-muted" id="modalUserSubtitle">Loading user details...</p>
                </div>
            </div>
            <button type="button" onclick="closeHeldSlotsModal()" class="w-8 h-8 rounded-full bg-stone-200 dark:bg-stone-800 flex items-center justify-center text-theme-muted hover:text-theme-heading cursor-pointer transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Scrollable Content Body -->
        <div class="p-6 overflow-y-auto space-y-4 flex-1">
            <!-- Summary KPI Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="p-3 rounded-2xl bg-stone-100 dark:bg-stone-800/60 border border-stone-200 dark:border-stone-700/60">
                    <div class="text-[10px] uppercase font-bold text-theme-muted">Total Held Slots</div>
                    <div class="text-xl font-extrabold text-amber-600 dark:text-amber-400 mt-0.5" id="modalTotalHeldSlots">0</div>
                    <div class="text-[10px] text-theme-muted" id="modalHeldSessions">0 sessions</div>
                </div>
                <div class="p-3 rounded-2xl bg-stone-100 dark:bg-stone-800/60 border border-stone-200 dark:border-stone-700/60">
                    <div class="text-[10px] uppercase font-bold text-theme-muted">Currently Active</div>
                    <div class="text-xl font-extrabold text-cyan-600 dark:text-cyan-400 mt-0.5" id="modalActiveHeldSlots">0</div>
                    <div class="text-[10px] text-theme-muted">In checkout</div>
                </div>
                <div class="p-3 rounded-2xl bg-stone-100 dark:bg-stone-800/60 border border-stone-200 dark:border-stone-700/60">
                    <div class="text-[10px] uppercase font-bold text-theme-muted">Expired (Unpaid)</div>
                    <div class="text-xl font-extrabold text-stone-600 dark:text-stone-400 mt-0.5" id="modalExpiredHeldSlots">0</div>
                    <div class="text-[10px] text-theme-muted">Timed out</div>
                </div>
                <div class="p-3 rounded-2xl bg-stone-100 dark:bg-stone-800/60 border border-stone-200 dark:border-stone-700/60">
                    <div class="text-[10px] uppercase font-bold text-theme-muted">Cancelled</div>
                    <div class="text-xl font-extrabold text-rose-600 dark:text-rose-400 mt-0.5" id="modalCancelledHeldSlots">0</div>
                    <div class="text-[10px] text-theme-muted">Abandoned</div>
                </div>
            </div>

            <!-- Context Alert / Warning -->
            <div class="p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-800 dark:text-amber-300 text-xs flex items-start gap-2.5">
                <i class="fa-solid fa-circle-exclamation text-amber-500 mt-0.5 shrink-0 text-sm"></i>
                <div class="leading-relaxed">
                    <strong class="font-bold">Reservation Hold Monitoring:</strong>
                    These timeslots were temporarily locked during online checkout but were never completed with payment.
                    Use this ledger to detect customers who repeatedly hold slots without paying.
                </div>
            </div>

            <!-- Loading State -->
            <div id="modalLoadingState" class="py-12 text-center text-theme-muted space-y-2">
                <i class="fa-solid fa-circle-notch fa-spin text-2xl text-cyan-500"></i>
                <p class="text-xs">Fetching held timeslot records...</p>
            </div>

            <!-- Empty State -->
            <div id="modalEmptyState" class="py-10 text-center text-theme-muted space-y-2 hidden">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center mx-auto text-xl">
                    <i class="fa-solid fa-check"></i>
                </div>
                <h4 class="text-xs font-bold text-theme-heading">No Held Timeslot Incidents</h4>
                <p class="text-[11px] max-w-xs mx-auto">This customer has never abandoned or timed out on a held timeslot reservation.</p>
            </div>

            <!-- Timeslot List -->
            <div id="modalSlotsList" class="space-y-2 hidden">
                <!-- Dynamically generated rows -->
            </div>
        </div>

        <!-- Footer -->
        <div class="p-4 border-t border-stone-200 dark:border-stone-800 flex items-center justify-between shrink-0 bg-stone-50 dark:bg-stone-900/60">
            <span class="text-[11px] text-theme-muted font-mono" id="modalUserContactInfo"></span>
            <button type="button" onclick="closeHeldSlotsModal()"
                class="px-4 py-2 rounded-xl border border-stone-300 dark:border-stone-700 text-xs font-semibold text-theme-muted hover:text-theme-heading cursor-pointer transition-colors">
                Close
            </button>
        </div>
    </div>
</div>
<!-- ========================================== -->
<!-- DEACTIVATE USER MODAL                      -->
<!-- ========================================== -->
<div id="deactivateUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm hidden transition-all">
    <div class="w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-3xl glass-dropdown border border-stone-200 dark:border-stone-800 p-6 md:p-8 shadow-2xl relative">
        <button type="button" onclick="closeDeactivateModal()" class="absolute top-5 right-5 w-8 h-8 rounded-full bg-stone-200 dark:bg-stone-800 flex items-center justify-center text-theme-muted hover:text-theme-heading cursor-pointer transition-colors">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="flex items-center gap-3.5 mb-5">
            <div class="w-11 h-11 rounded-2xl bg-rose-500/15 text-rose-500 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-user-slash"></i>
            </div>
            <div>
                <h3 class="text-base md:text-lg font-bold text-theme-heading">Deactivate Account</h3>
                <p class="text-xs text-theme-muted">Suspend this account and indicate the reason for deactivation.</p>
            </div>
        </div>

        <form id="deactivateUserForm" onsubmit="submitDeactivateUser(event)" class="space-y-4">
            <input type="hidden" id="deactivate_user_id" value="">

            <!-- Target User Info Card -->
            <div class="p-3.5 rounded-2xl bg-stone-100 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-800 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 font-bold flex items-center justify-center text-sm shrink-0 uppercase" id="deactivate_user_avatar">
                        U
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-theme-heading truncate" id="deactivate_user_name">User Name</div>
                        <div class="text-[11px] text-theme-muted truncate font-mono" id="deactivate_user_email">user@email.com</div>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold shrink-0 bg-stone-200 dark:bg-stone-800 text-theme-body border border-stone-300 dark:border-stone-700" id="deactivate_user_role">
                    Client
                </span>
            </div>

            <!-- Reason Input -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="deactivate_reason" class="block text-xs font-bold text-theme-heading">
                        Reason for Deactivation <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-[10px] text-theme-muted">Required</span>
                </div>

                <!-- Reason Preset Chips -->
                <div class="mb-2 flex flex-wrap gap-1.5">
                    <button type="button" onclick="setDeactivateReason('Repeated unpaid / expired reservation holds')"
                        class="px-2.5 py-1 rounded-lg text-[10px] font-semibold bg-stone-200/80 dark:bg-stone-800 text-theme-body hover:bg-rose-500/15 hover:text-rose-600 dark:hover:text-rose-400 border border-stone-300/60 dark:border-stone-700 transition-colors cursor-pointer">
                        ⏱️ Unpaid / Expired Holds
                    </button>
                    <button type="button" onclick="setDeactivateReason('Violation of facility court rules / terms')"
                        class="px-2.5 py-1 rounded-lg text-[10px] font-semibold bg-stone-200/80 dark:bg-stone-800 text-theme-body hover:bg-rose-500/15 hover:text-rose-600 dark:hover:text-rose-400 border border-stone-300/60 dark:border-stone-700 transition-colors cursor-pointer">
                        🚫 Rule Violation
                    </button>
                    <button type="button" onclick="setDeactivateReason('Requested by account owner / user')"
                        class="px-2.5 py-1 rounded-lg text-[10px] font-semibold bg-stone-200/80 dark:bg-stone-800 text-theme-body hover:bg-rose-500/15 hover:text-rose-600 dark:hover:text-rose-400 border border-stone-300/60 dark:border-stone-700 transition-colors cursor-pointer">
                        🙋 Requested by User
                    </button>
                    <button type="button" onclick="setDeactivateReason('Suspicious / fraudulent booking activity')"
                        class="px-2.5 py-1 rounded-lg text-[10px] font-semibold bg-stone-200/80 dark:bg-stone-800 text-theme-body hover:bg-rose-500/15 hover:text-rose-600 dark:hover:text-rose-400 border border-stone-300/60 dark:border-stone-700 transition-colors cursor-pointer">
                        ⚠️ Suspicious Activity
                    </button>
                </div>

                <textarea id="deactivate_reason" rows="3" required
                    placeholder="Indicate the reason why you are deactivating this account..."
                    class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-rose-500 focus:outline-none transition-all resize-none"></textarea>
                <p id="deactivate_reason_error" class="hidden text-[11px] text-rose-500 mt-1 font-semibold">Please provide a reason for deactivation.</p>
            </div>

            <!-- Context Alert -->
            <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-800 dark:text-amber-300 text-xs flex items-start gap-2.5">
                <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5 shrink-0"></i>
                <div class="leading-relaxed text-[11px]">
                    While deactivated, this user will be blocked from logging in, booking courts, or holding slots. You can reactivate this account at any time without data loss.
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-stone-200 dark:border-stone-800">
                <button type="button" onclick="closeDeactivateModal()"
                    class="px-4 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 text-xs font-semibold text-theme-muted hover:text-theme-heading cursor-pointer transition-colors">
                    Cancel
                </button>
                <button type="submit" id="deactivate_submit_btn"
                    class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold shadow-md shadow-rose-600/20 cursor-pointer transition-all flex items-center gap-2">
                    <span id="deactivate_btn_spinner" class="hidden"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                    <span id="deactivate_btn_text">Confirm Deactivation</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const availableUserModules = ['schedule', 'approvals', 'courts', 'photos', 'users', 'settings'];

    function openAddUserModal() {
        document.getElementById('addUserModal').classList.remove('hidden');
    }

    function closeAddUserModal() {
        document.getElementById('addUserModal').classList.add('hidden');
    }

    function toggleAddAssistantSection(role) {
        const section = document.getElementById('add_assistant_section');
        if (role === 'admin_assistant') {
            section.classList.remove('hidden');
        } else {
            section.classList.add('hidden');
        }
    }

    function openEditUserModal(user, permissions) {
        document.getElementById('editUserForm').action = "{{ url('owner/users') }}/" + user.id;
        document.getElementById('edit_user_name').value = user.name || '';
        document.getElementById('edit_user_email').value = user.email || '';
        document.getElementById('edit_user_phone').value = user.phone || '';

        const isActive = !!user.is_active;
        const activeCheckbox = document.getElementById('edit_user_is_active');
        if (activeCheckbox) {
            activeCheckbox.checked = isActive;
        }

        const reasonBox = document.getElementById('edit_user_deactivation_reason');
        if (reasonBox) {
            reasonBox.value = user.deactivation_reason || '';
        }
        toggleEditDeactivationReason(isActive);

        const passField = document.getElementById('edit_user_password');
        const passConfField = document.getElementById('edit_user_password_confirmation');
        if (passField) passField.value = '';
        if (passConfField) passConfField.value = '';

        const roleSelect = document.getElementById('edit_user_role');
        if (roleSelect) {
            roleSelect.value = user.role || 'client';
            toggleEditAssistantSection(user.role);
        }

        const supervisorSelect = document.getElementById('edit_user_court_owner_id');
        if (supervisorSelect) {
            supervisorSelect.value = user.court_owner_id || '';
        }

        availableUserModules.forEach(mod => {
            const cb = document.getElementById(`edit_user_module_${mod}`);
            if (cb) {
                cb.checked = Array.isArray(permissions) && permissions.includes(mod);
            }
        });

        document.getElementById('editUserModal').classList.remove('hidden');
    }

    function toggleEditDeactivationReason(isActive) {
        const container = document.getElementById('edit_deactivation_reason_container');
        if (!container) return;
        if (isActive) {
            container.classList.add('hidden');
        } else {
            container.classList.remove('hidden');
        }
    }

    function closeEditUserModal() {
        document.getElementById('editUserModal').classList.add('hidden');
    }

    function toggleEditAssistantSection(role) {
        const section = document.getElementById('edit_assistant_section');
        if (role === 'admin_assistant') {
            section.classList.remove('hidden');
        } else {
            section.classList.add('hidden');
        }
    }

    function openHeldSlotsModal(userId, userName, userEmail, heldCount, activeCount) {
        const modal = document.getElementById('heldSlotsModal');
        modal.classList.remove('hidden');

        document.getElementById('modalUserSubtitle').innerText = `${userName} (${userEmail})`;
        document.getElementById('modalUserContactInfo').innerText = `User ID: #${userId} • ${userEmail}`;
        document.getElementById('modalTotalHeldSlots').innerText = heldCount;
        document.getElementById('modalActiveHeldSlots').innerText = activeCount;

        const loading = document.getElementById('modalLoadingState');
        const emptyState = document.getElementById('modalEmptyState');
        const list = document.getElementById('modalSlotsList');

        loading.classList.remove('hidden');
        emptyState.classList.add('hidden');
        list.classList.add('hidden');
        list.innerHTML = '';

        fetch(`{{ url('owner/users') }}/${userId}/held-slots`)
            .then(res => res.json())
            .then(data => {
                loading.classList.add('hidden');
                if (!data.success || !data.bookings || data.bookings.length === 0) {
                    emptyState.classList.remove('hidden');
                    return;
                }

                document.getElementById('modalTotalHeldSlots').innerText = data.total_held_slots || 0;
                document.getElementById('modalActiveHeldSlots').innerText = data.active_held_slots || 0;
                document.getElementById('modalHeldSessions').innerText = `${data.held_bookings_count || data.bookings.length} reservations`;

                let expiredCount = 0;
                let cancelledCount = 0;

                let html = '';
                data.bookings.forEach(b => {
                    if (b.booking_status === 'cancelled') {
                        cancelledCount += b.total_hours;
                    } else if (b.booking_status === 'expired') {
                        expiredCount += b.total_hours;
                    }

                    let statusBadge = '';
                    if (b.is_held) {
                        statusBadge = `<span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 border border-cyan-500/30 uppercase animate-pulse">Live Hold (${Math.max(0, b.remaining_seconds)}s left)</span>`;
                    } else if (b.booking_status === 'expired') {
                        statusBadge = `<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-stone-500/15 text-stone-600 dark:text-stone-400 border border-stone-500/30 uppercase">Expired Hold</span>`;
                    } else if (b.booking_status === 'cancelled') {
                        statusBadge = `<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/15 text-rose-600 dark:text-rose-400 border border-rose-500/30 uppercase">Cancelled</span>`;
                    } else {
                        statusBadge = `<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-700 dark:text-amber-400 uppercase">${b.booking_status}</span>`;
                    }

                    const slotsDisplay = (b.slots && b.slots.length > 0)
                        ? b.slots.map(s => `<span class="px-1.5 py-0.5 rounded bg-stone-200 dark:bg-stone-800 text-[10px] font-mono">${s}</span>`).join(' ')
                        : `${b.start_time} - ${b.end_time}`;

                    html += `
                        <div class="p-3.5 rounded-2xl border border-stone-200 dark:border-stone-800 bg-stone-50/70 dark:bg-stone-900/40 hover:border-amber-500/30 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-mono font-bold text-theme-heading">${b.reference}</span>
                                    ${statusBadge}
                                    <span class="text-[11px] text-theme-muted capitalize">• ${b.payment_method} checkout</span>
                                </div>
                                <div class="text-[11px] text-theme-muted flex items-center gap-2 flex-wrap">
                                    <span class="font-semibold text-theme-heading"><i class="fa-solid fa-table-tennis-paddle-ball text-[10px] mr-1 text-cyan-600 dark:text-cyan-400"></i>${b.court_name}</span>
                                    <span>•</span>
                                    <span>${b.booking_date}</span>
                                    <span>•</span>
                                    <span>${b.total_hours} hour(s)</span>
                                </div>
                                <div class="pt-0.5 flex items-center gap-1 flex-wrap">
                                    <span class="text-[10px] text-theme-muted">Slots held:</span>
                                    ${slotsDisplay}
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="font-extrabold text-theme-heading text-sm">${b.formatted_amount}</div>
                                <div class="text-[10px] text-theme-muted mt-0.5">Held on ${b.created_at}</div>
                            </div>
                        </div>
                    `;
                });

                document.getElementById('modalExpiredHeldSlots').innerText = expiredCount;
                document.getElementById('modalCancelledHeldSlots').innerText = cancelledCount;

                list.innerHTML = html;
                list.classList.remove('hidden');
            })
            .catch(err => {
                loading.classList.add('hidden');
                emptyState.classList.remove('hidden');
            });
    }

    function closeHeldSlotsModal() {
        document.getElementById('heldSlotsModal').classList.add('hidden');
    }

    // ==========================================
    // STATUS MODAL & ASYNC TOGGLE (NO REFRESH)
    // ==========================================
    let currentDeactivateUser = null;

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function escapeJsString(str) {
        if (!str) return '';
        return String(str)
            .replace(/\\/g, '\\\\')
            .replace(/'/g, "\\'")
            .replace(/"/g, '\\"')
            .replace(/\n/g, '\\n')
            .replace(/\r/g, '');
    }

    function setDeactivateReason(text) {
        const textarea = document.getElementById('deactivate_reason');
        textarea.value = text;
        textarea.focus();
        document.getElementById('deactivate_reason_error').classList.add('hidden');
    }

    function handleStatusClick(userId, userName, userEmail, userRole, isActive, currentReason) {
        if (isActive) {
            // Deactivating: show pop up modal to indicate reason
            openDeactivateModal(userId, userName, userEmail, userRole, currentReason);
        } else {
            // Reactivating: show confirm dialog
            const prevReasonHtml = currentReason 
                ? `<div class="p-3 my-3 rounded-xl bg-stone-100 dark:bg-stone-800/80 text-xs text-theme-muted text-left border border-stone-200 dark:border-stone-700"><span class="font-bold text-theme-heading">Previous deactivation reason:</span><br>${escapeHtml(currentReason)}</div>` 
                : '';

            Swal.fire({
                title: 'Reactivate Account?',
                html: `Are you sure you want to activate the account for <strong>${escapeHtml(userName)}</strong> (${escapeHtml(userEmail)})?<br>${prevReasonHtml}<p class="text-xs text-theme-muted mt-2">The user will immediately be able to sign in and book courts.</p>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="fa-solid fa-check mr-1.5"></i>Yes, Activate Account',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    performStatusUpdate(userId, true, null, userName, userEmail, userRole);
                }
            });
        }
    }

    function openDeactivateModal(userId, userName, userEmail, userRole, currentReason) {
        currentDeactivateUser = { id: userId, name: userName, email: userEmail, role: userRole };
        document.getElementById('deactivate_user_id').value = userId;
        document.getElementById('deactivate_user_name').textContent = userName;
        document.getElementById('deactivate_user_email').textContent = userEmail;
        document.getElementById('deactivate_user_role').textContent = userRole;
        document.getElementById('deactivate_user_avatar').textContent = (userName || 'U').charAt(0).toUpperCase();
        document.getElementById('deactivate_reason').value = '';
        document.getElementById('deactivate_reason_error').classList.add('hidden');
        document.getElementById('deactivateUserModal').classList.remove('hidden');
        setTimeout(() => {
            document.getElementById('deactivate_reason').focus();
        }, 100);
    }

    function closeDeactivateModal() {
        document.getElementById('deactivateUserModal').classList.add('hidden');
        document.getElementById('deactivate_reason_error').classList.add('hidden');
        currentDeactivateUser = null;
    }

    function submitDeactivateUser(e) {
        e.preventDefault();
        if (!currentDeactivateUser) return;

        const reason = document.getElementById('deactivate_reason').value.trim();
        if (!reason) {
            document.getElementById('deactivate_reason_error').classList.remove('hidden');
            document.getElementById('deactivate_reason').focus();
            return;
        }

        const submitBtn = document.getElementById('deactivate_submit_btn');
        const spinner = document.getElementById('deactivate_btn_spinner');
        const btnText = document.getElementById('deactivate_btn_text');

        submitBtn.disabled = true;
        spinner.classList.remove('hidden');
        btnText.textContent = 'Deactivating...';

        performStatusUpdate(
            currentDeactivateUser.id,
            false,
            reason,
            currentDeactivateUser.name,
            currentDeactivateUser.email,
            currentDeactivateUser.role,
            () => {
                closeDeactivateModal();
                submitBtn.disabled = false;
                spinner.classList.add('hidden');
                btnText.textContent = 'Confirm Deactivation';
            },
            () => {
                submitBtn.disabled = false;
                spinner.classList.add('hidden');
                btnText.textContent = 'Confirm Deactivation';
            }
        );
    }

    function performStatusUpdate(userId, newActive, reason, userName, userEmail, userRole, onComplete, onError) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        fetch(`{{ url('owner/users') }}/${userId}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                is_active: newActive ? 1 : 0,
                reason: reason || ''
            })
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Failed to update account status.');
            }
            return data;
        })
        .then(data => {
            if (typeof onComplete === 'function') onComplete();

            // Update the table cell without page refresh!
            updateUserStatusCell(userId, data.is_active, data.deactivation_reason, userName, userEmail, userRole);

            // SweetAlert toast notification
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true
            });

            Toast.fire({
                icon: data.is_active ? 'success' : 'info',
                title: data.message || `User account status updated.`
            });
        })
        .catch(err => {
            if (typeof onError === 'function') onError();
            Swal.fire({
                title: 'Action Failed',
                text: err.message || 'An error occurred while updating the account status.',
                icon: 'error',
                confirmButtonColor: '#06b6d4'
            });
        });
    }

    function updateUserStatusCell(userId, isActive, reason, userName, userEmail, userRole) {
        const cell = document.getElementById(`user-status-cell-${userId}`);
        if (!cell) return;

        const safeName = escapeHtml(userName);
        const safeEmail = escapeHtml(userEmail);
        const safeRole = escapeHtml(userRole);
        const safeReason = reason ? escapeHtml(reason) : '';
        const jsName = escapeJsString(userName);
        const jsEmail = escapeJsString(userEmail);
        const jsRole = escapeJsString(userRole);
        const jsReason = reason ? escapeJsString(reason) : '';

        if (isActive) {
            cell.innerHTML = `
                <button type="button"
                    id="status-btn-${userId}"
                    onclick="handleStatusClick(${userId}, '${jsName}', '${jsEmail}', '${jsRole}', 1, '')"
                    title="Click to edit status / deactivate account"
                    class="inline-flex flex-col items-center gap-1 cursor-pointer group focus:outline-none transition-transform active:scale-95">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30 group-hover:bg-emerald-500/20 group-hover:border-emerald-500/50 shadow-sm transition-all">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Active</span>
                        <i class="fa-solid fa-pen text-[8px] opacity-40 group-hover:opacity-100 transition-opacity"></i>
                    </span>
                </button>
            `;
        } else {
            const reasonHtml = safeReason ? `
                <span class="text-[9px] text-theme-muted max-w-[140px] truncate block opacity-75 group-hover:opacity-100 transition-opacity" title="Reason: ${safeReason}">
                    <i class="fa-solid fa-circle-info text-[8px] mr-0.5 text-rose-400"></i>${safeReason}
                </span>
            ` : '';

            cell.innerHTML = `
                <button type="button"
                    id="status-btn-${userId}"
                    onclick="handleStatusClick(${userId}, '${jsName}', '${jsEmail}', '${jsRole}', 0, '${jsReason}')"
                    title="Click to edit status / reactivate account"
                    class="inline-flex flex-col items-center gap-1 cursor-pointer group focus:outline-none transition-transform active:scale-95">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-500/30 group-hover:bg-rose-500/20 group-hover:border-rose-500/50 shadow-sm transition-all">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                        <span>Deactivated</span>
                        <i class="fa-solid fa-pen text-[8px] opacity-40 group-hover:opacity-100 transition-opacity"></i>
                    </span>
                    ${reasonHtml}
                </button>
            `;
        }
    }

    function confirmResetPassword(id, name, email) {
        Swal.fire({
            title: 'Reset Password?',
            html: `Are you sure you want to reset the password for <strong>${escapeHtml(name)}</strong> (${escapeHtml(email)}) to <code class="px-2 py-0.5 rounded bg-amber-500/20 text-amber-600 dark:text-amber-400 font-mono font-bold text-xs">PaddleField2026!</code>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Reset Password',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(`reset-password-form-${id}`).submit();
            }
        });
    }

    // Close modals on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeAddUserModal();
            closeEditUserModal();
            closeHeldSlotsModal();
            closeDeactivateModal();
        }
    });
</script>
@endpush
