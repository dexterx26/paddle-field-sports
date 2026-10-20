@extends('layouts.owner')

@section('content')
<div class="space-y-6 w-full max-w-full min-w-0">
    <!-- Top Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-stone-200 dark:border-stone-800 gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h2 class="text-xl font-bold text-theme-heading">User Management</h2>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-cyan-500/15 text-cyan-700 dark:text-cyan-300 border border-cyan-500/30 uppercase tracking-wider">
                    {{ ucwords(str_replace('_', ' ', $roleFilter ?: 'All Roles')) }}
                </span>
            </div>
            <p class="text-xs text-theme-muted mt-1">Manage court owners, staff assistants, and registered player accounts.</p>
        </div>
        <div class="flex flex-col sm:flex-row items-center gap-2.5 w-full sm:w-auto">
            @if(Auth::user()->canManageStaff())
                <button type="button" onclick="openAddAssistantModal()"
                    class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2 transition-all cursor-pointer">
                    <i class="fa-solid fa-user-tie"></i> Add Admin Assistant
                </button>
            @endif
            <button type="button" onclick="openAddUserModal()"
                class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-black text-xs shadow-lg shadow-cyan-500/20 flex items-center justify-center gap-2 transition-all cursor-pointer">
                <i class="fa-solid fa-user-plus"></i> Add New User
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
        <div class="p-4 rounded-2xl glass-panel border border-stone-200 dark:border-stone-800 flex items-center gap-3 sm:gap-4">
            <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-theme-muted font-medium truncate">Total Registered</p>
                <h3 class="text-xl font-extrabold text-theme-heading">{{ $counts['all'] }}</h3>
            </div>
        </div>

        <div class="p-4 rounded-2xl glass-panel border border-stone-200 dark:border-stone-800 flex items-center gap-3 sm:gap-4">
            <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-theme-muted font-medium truncate">Players / Clients</p>
                <h3 class="text-xl font-extrabold text-theme-heading">{{ $counts['client'] }}</h3>
            </div>
        </div>

        <div class="p-4 rounded-2xl glass-panel border border-stone-200 dark:border-stone-800 flex items-center gap-3 sm:gap-4">
            <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-user-tie"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-theme-muted font-medium truncate">Admin Assistants</p>
                <h3 class="text-xl font-extrabold text-theme-heading">{{ $counts['admin_assistant'] }}</h3>
            </div>
        </div>

        <div class="p-4 rounded-2xl glass-panel border border-stone-200 dark:border-stone-800 flex items-center gap-3 sm:gap-4">
            <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-crown"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-theme-muted font-medium truncate">Court Owners</p>
                <h3 class="text-xl font-extrabold text-theme-heading">{{ $counts['court_owner'] }}</h3>
            </div>
        </div>

        <a href="{{ route('owner.users.index', array_merge(request()->except('status', 'page'), ['status' => $statusFilter === 'has_held' ? null : 'has_held'])) }}"
            class="p-4 rounded-2xl glass-panel border {{ $statusFilter === 'has_held' ? 'border-amber-500/60 bg-amber-500/10 shadow-lg shadow-amber-500/10' : 'border-stone-200 dark:border-stone-800 hover:border-amber-500/40' }} flex items-center gap-3 sm:gap-4 transition-all group cursor-pointer"
            title="Click to filter users with held timeslots">
            <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-amber-500/15 text-amber-700 dark:text-amber-400 flex items-center justify-center text-lg shrink-0 group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-theme-muted font-medium flex items-center gap-1.5 truncate">
                    <span>Held Slots</span>
                    @if($counts['has_held'] > 0)
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    @endif
                </p>
                <h3 class="text-xl font-extrabold text-theme-heading">{{ $counts['total_held_slots'] }}</h3>
                <p class="text-[10px] text-amber-800 dark:text-amber-300 font-semibold truncate">{{ $counts['has_held'] }} {{ Str::plural('user', $counts['has_held']) }}</p>
            </div>
        </a>

        <!-- Total Wallet Credits Card -->
        <a href="{{ route('owner.users.index', array_merge(request()->except('status', 'sort', 'page'), ['status' => $statusFilter === 'has_credits' ? null : 'has_credits', 'sort' => 'credits_desc'])) }}"
            class="p-4 rounded-2xl glass-panel border {{ $statusFilter === 'has_credits' ? 'border-cyan-500/60 bg-cyan-500/10 shadow-lg shadow-cyan-500/10' : 'border-stone-200 dark:border-stone-800 hover:border-cyan-500/40' }} flex items-center gap-3 sm:gap-4 transition-all group cursor-pointer"
            title="Click to filter and sort users by wallet credit balance">
            <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-cyan-500/15 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-lg shrink-0 group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-coins text-amber-500"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-theme-muted font-medium flex items-center gap-1.5 truncate">
                    <span>User Credits</span>
                    @if(($counts['has_credits'] ?? 0) > 0)
                        <span class="w-2 h-2 rounded-full bg-cyan-500 animate-pulse"></span>
                    @endif
                </p>
                <h3 class="text-xl font-extrabold text-cyan-600 dark:text-cyan-400">{{ number_format($counts['total_credits'] ?? 0, 1) }}</h3>
                <p class="text-[10px] text-theme-muted font-semibold truncate">{{ $counts['has_credits'] ?? 0 }} {{ Str::plural('player', $counts['has_credits'] ?? 0) }} with balance</p>
            </div>
        </a>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="p-4 sm:p-5 rounded-3xl glass-card border border-stone-200 dark:border-stone-800 space-y-4 w-full max-w-full overflow-hidden">
        <!-- Role Pills (Horizontally scrollable without stretching parent) -->
        <div class="overflow-x-auto pb-1 scrollbar-none w-full max-w-full -mx-1 px-1">
            <div class="flex items-center gap-2 min-w-max">
                <span class="text-xs font-bold text-theme-muted mr-1 shrink-0">Role:</span>
                <a href="{{ route('owner.users.index', array_merge(request()->except('role', 'page'), ['role' => null])) }}"
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5 shrink-0 {{ empty($roleFilter) ? 'bg-cyan-500 text-slate-950 shadow-md shadow-cyan-500/20' : 'bg-stone-200 dark:bg-stone-800 text-theme-body hover:bg-stone-300 dark:hover:bg-stone-700' }}">
                    <span>All Users</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ empty($roleFilter) ? 'bg-slate-950 text-cyan-400' : 'bg-stone-300 dark:bg-stone-700 text-theme-muted' }}">{{ $counts['all'] }}</span>
                </a>

                <a href="{{ route('owner.users.index', array_merge(request()->except('role', 'page'), ['role' => 'client'])) }}"
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5 shrink-0 {{ $roleFilter === 'client' ? 'bg-cyan-500 text-slate-950 shadow-md shadow-cyan-500/20' : 'bg-stone-200 dark:bg-stone-800 text-theme-body hover:bg-stone-300 dark:hover:bg-stone-700' }}">
                    <span>Players / Clients</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $roleFilter === 'client' ? 'bg-slate-950 text-cyan-400' : 'bg-stone-300 dark:bg-stone-700 text-theme-muted' }}">{{ $counts['client'] }}</span>
                </a>

                <a href="{{ route('owner.users.index', array_merge(request()->except('role', 'page'), ['role' => 'admin_assistant'])) }}"
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5 shrink-0 {{ $roleFilter === 'admin_assistant' ? 'bg-emerald-500 text-slate-950 shadow-md shadow-emerald-500/20' : 'bg-stone-200 dark:bg-stone-800 text-theme-body hover:bg-stone-300 dark:hover:bg-stone-700' }}">
                    <span>Admin Assistants</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $roleFilter === 'admin_assistant' ? 'bg-slate-950 text-emerald-400' : 'bg-stone-300 dark:bg-stone-700 text-theme-muted' }}">{{ $counts['admin_assistant'] }}</span>
                </a>

                <a href="{{ route('owner.users.index', array_merge(request()->except('role', 'page'), ['role' => 'court_owner'])) }}"
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5 shrink-0 {{ $roleFilter === 'court_owner' ? 'bg-cyan-500 text-slate-950 shadow-md shadow-cyan-500/20' : 'bg-stone-200 dark:bg-stone-800 text-theme-body hover:bg-stone-300 dark:hover:bg-stone-700' }}">
                    <span>Court Owners</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $roleFilter === 'court_owner' ? 'bg-slate-950 text-cyan-400' : 'bg-stone-300 dark:bg-stone-700 text-theme-muted' }}">{{ $counts['court_owner'] }}</span>
                </a>
            </div>
        </div>

        <!-- Search & Status Row -->
        <form method="GET" action="{{ route('owner.users.index') }}" class="flex flex-col lg:flex-row items-stretch lg:items-center gap-3 text-xs w-full max-w-full">
            @if($roleFilter)
                <input type="hidden" name="role" value="{{ $roleFilter }}">
            @endif

            <div class="flex-1 w-full relative min-w-0">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-xs"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search name, email, or phone..."
                    class="w-full min-w-0 pl-9 pr-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 focus:border-cyan-500 focus:outline-none text-xs">
            </div>

            <div class="flex flex-col sm:flex-row sm:flex-wrap lg:flex-nowrap items-stretch sm:items-center gap-2 w-full lg:w-auto min-w-0">
                <select name="sort" onchange="this.form.submit()"
                    class="w-full sm:w-auto min-w-0 px-3 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-medium focus:border-cyan-500 focus:outline-none truncate text-xs">
                    <option value="" {{ empty($sort) ? 'selected' : '' }}>Role Order</option>
                    <option value="held_desc" {{ ($sort ?? '') === 'held_desc' ? 'selected' : '' }}>Most Held Timeslots</option>
                </select>

                <select name="status" onchange="this.form.submit()"
                    class="w-full sm:w-auto min-w-0 px-3 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-medium focus:border-cyan-500 focus:outline-none truncate text-xs">
                    <option value="" {{ empty($statusFilter) ? 'selected' : '' }}>All Statuses</option>
                    <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Active Only ({{ $counts['active'] }})</option>
                    <option value="inactive" {{ $statusFilter === 'inactive' ? 'selected' : '' }}>Deactivated Only ({{ $counts['inactive'] }})</option>
                    <option value="has_held" {{ $statusFilter === 'has_held' ? 'selected' : '' }}>⚠️ With Held Slots ({{ $counts['has_held'] }})</option>
                </select>

                <div class="flex items-center gap-2 w-full sm:w-auto shrink-0">
                    <button type="submit"
                        class="flex-1 sm:flex-initial px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold transition-all cursor-pointer shrink-0 text-center">
                        Filter
                    </button>

                    @if($search || $statusFilter || $roleFilter || !empty($sort))
                        <a href="{{ route('owner.users.index') }}"
                            class="flex-1 sm:flex-initial px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 text-theme-muted hover:text-theme-heading transition-all whitespace-nowrap text-center">
                            Clear
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="glass-panel border border-stone-200 dark:border-stone-800 rounded-3xl overflow-hidden shadow-xl w-full max-w-full">
        <div class="p-4 sm:p-5 border-b border-stone-200 dark:border-stone-800 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2 min-w-0">
                <h3 class="font-bold text-sm text-theme-heading truncate">Registered Accounts</h3>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-stone-200 dark:bg-stone-800 text-theme-muted shrink-0">
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
            <!-- Desktop Table View (Hidden on mobile) -->
            <div class="hidden md:block overflow-x-auto w-full max-w-full">
                <table class="w-full text-left text-xs text-theme-body">
                    <thead class="bg-stone-100 dark:bg-stone-900/50 text-theme-muted uppercase tracking-wider text-[11px] border-b border-stone-200 dark:border-stone-800">
                        <tr>
                            <th class="py-3.5 px-6">User</th>
                            <th class="py-3.5 px-6">Role</th>
                            <th class="py-3.5 px-6">Phone</th>
                            <th class="py-3.5 px-6">Permissions / Activity</th>
                            <th class="py-3.5 px-6 text-center">Wallet Credits</th>
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

                                <!-- Wallet Credits -->
                                <td class="py-4 px-6 text-center whitespace-nowrap" id="user-credits-cell-{{ $user->id }}">
                                    @php
                                        $userCredits = (float) ($user->credits ?? 0);
                                    @endphp
                                    <button type="button"
                                        onclick="openCreditAdjustmentModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', {{ $userCredits }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer shadow-sm group
                                            {{ $userCredits > 0 
                                                ? 'bg-cyan-500/15 text-cyan-700 dark:text-cyan-300 border border-cyan-500/30 hover:bg-cyan-500/25' 
                                                : 'bg-stone-100 dark:bg-stone-800 text-stone-400 dark:text-stone-500 border border-stone-200 dark:border-stone-700 hover:border-cyan-500/40 hover:text-cyan-600' }}"
                                        title="Click to view ledger or adjust credits">
                                        <i class="fa-solid fa-coins text-[11px] text-amber-500 group-hover:scale-110 transition-transform"></i>
                                        <span id="user-credits-val-{{ $user->id }}"><strong>{{ number_format($userCredits, 1) }}</strong></span>
                                        <span class="text-[10px] opacity-75">Credits</span>
                                        <i class="fa-solid fa-pen text-[8px] opacity-0 group-hover:opacity-100 transition-opacity ml-0.5"></i>
                                    </button>
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
                                            @if(Auth::user()->canManageStaff())
                                                @if($user->role === 'client')
                                                    <!-- Tag as Admin Assistant Button -->
                                                    <button type="button"
                                                        onclick="openTagAssistantModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', '{{ addslashes($user->phone ?? '') }}', [], false, {{ $user->court_owner_id ? $user->court_owner_id : 'null' }})"
                                                        class="p-2 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30 transition-all cursor-pointer shadow-sm group"
                                                        title="Tag as Admin Assistant">
                                                        <i class="fa-solid fa-user-tie group-hover:scale-110 transition-transform"></i>
                                                    </button>
                                                @elseif($user->role === 'admin_assistant')
                                                    <!-- Manage Assistant Access Button -->
                                                    <button type="button"
                                                        onclick="openTagAssistantModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', '{{ addslashes($user->phone ?? '') }}', @json($perms), true, {{ $user->court_owner_id ? $user->court_owner_id : 'null' }})"
                                                        class="p-2 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30 transition-all cursor-pointer shadow-sm group"
                                                        title="Manage Assistant Modules & Access">
                                                        <i class="fa-solid fa-user-shield group-hover:scale-110 transition-transform"></i>
                                                    </button>
                                                    <!-- Remove Admin Assistant Access Button -->
                                                    <button type="button"
                                                        onclick="confirmRemoveAssistantAccess({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                                        class="p-2 rounded-xl bg-rose-500/15 hover:bg-rose-500/25 text-rose-700 dark:text-rose-400 border border-rose-500/30 transition-all cursor-pointer shadow-sm group"
                                                        title="Remove Admin Assistant Access">
                                                        <i class="fa-solid fa-user-xmark group-hover:scale-110 transition-transform"></i>
                                                    </button>
                                                @endif
                                            @endif

                                            <!-- Manage Credits Button -->
                                            <button type="button"
                                                onclick="openCreditAdjustmentModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', {{ (float) ($user->credits ?? 0) }})"
                                                class="p-2 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-cyan-500/10 hover:text-cyan-600 dark:hover:text-cyan-400 text-theme-muted transition-colors cursor-pointer"
                                                title="Manage Wallet Credits (Add / Deduct)">
                                                <i class="fa-solid fa-coins text-amber-500"></i>
                                            </button>

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

            <!-- Mobile Cards View (Shown on screens < md, eliminates horizontal scrolling) -->
            <div class="block md:hidden p-3 sm:p-4 space-y-3 w-full max-w-full">
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
                        $canEdit = true;
                        if (Auth::user()->isAdminAssistant() && ($user->isAdmin() || $user->isOwner())) {
                            $canEdit = false;
                        }
                        if (Auth::user()->isOwner() && $user->isAdmin()) {
                            $canEdit = false;
                        }
                    @endphp
                    <div class="p-3.5 sm:p-4 rounded-2xl bg-white/80 dark:bg-stone-900/80 border border-stone-200 dark:border-stone-800 shadow-sm space-y-3 w-full max-w-full overflow-hidden">
                        <!-- Top Bar: Avatar, Name, Email, Role, Joined, Status -->
                        <div class="flex items-start justify-between gap-2 pb-3 border-b border-stone-200/70 dark:border-stone-800/70">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-2xl {{ $roleBadge['bg'] }} {{ $roleBadge['text'] }} border {{ $roleBadge['border'] }} flex items-center justify-center font-bold text-sm shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-theme-heading text-sm flex items-center gap-1.5 truncate">
                                        <span class="truncate">{{ $user->name }}</span>
                                        @if($user->id === Auth::id())
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-extrabold bg-cyan-500 text-slate-950 uppercase shrink-0">You</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-theme-muted font-mono truncate">{{ $user->email }}</div>
                                    <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold {{ $roleBadge['bg'] }} {{ $roleBadge['text'] }} border {{ $roleBadge['border'] }}">
                                            <i class="fa-solid {{ $roleBadge['icon'] }} text-[9px]"></i>
                                            {{ $roleBadge['label'] }}
                                        </span>
                                        <span class="text-[10px] text-theme-muted flex items-center gap-1">
                                            <i class="fa-regular fa-calendar text-[9px]"></i>
                                            <span>Joined {{ $user->created_at->format('M d, Y') }}</span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Pill (Top Right, matching approvals top-right placement) -->
                            <div class="shrink-0 text-right" id="user-status-cell-mobile-{{ $user->id }}">
                                @if($user->id === Auth::id() || (Auth::user()->isAdminAssistant() && ($user->isAdmin() || $user->isOwner())) || (Auth::user()->isOwner() && $user->isAdmin()))
                                    @if($user->is_active)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30 opacity-70 cursor-not-allowed">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-500/30 opacity-70 cursor-not-allowed">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Deactivated
                                        </span>
                                    @endif
                                @else
                                    <button type="button"
                                        id="status-btn-mobile-{{ $user->id }}"
                                        onclick="handleStatusClick({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', '{{ $user->role }}', {{ $user->is_active ? 1 : 0 }}, '{{ addslashes($user->deactivation_reason ?? '') }}')"
                                        title="Click to edit status"
                                        class="inline-flex flex-col items-center gap-1 cursor-pointer group focus:outline-none transition-transform active:scale-95">
                                        @if($user->is_active)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30 group-hover:bg-emerald-500/20 shadow-sm transition-all">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                <span>Active</span>
                                                <i class="fa-solid fa-pen text-[8px] opacity-40 group-hover:opacity-100 transition-opacity"></i>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-500/30 group-hover:bg-rose-500/20 shadow-sm transition-all">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                <span>Deactivated</span>
                                                <i class="fa-solid fa-pen text-[8px] opacity-40 group-hover:opacity-100 transition-opacity"></i>
                                            </span>
                                        @endif
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Middle Content: Details -->
                        <div class="space-y-2 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-theme-muted flex items-center gap-1.5">
                                    <i class="fa-solid fa-phone text-[10px] text-cyan-600 dark:text-cyan-400"></i> Phone
                                </span>
                                @if($user->phone)
                                    <a href="tel:{{ $user->phone }}" class="font-mono text-cyan-600 dark:text-cyan-400 hover:underline">{{ $user->phone }}</a>
                                @else
                                    <span class="text-theme-muted">—</span>
                                @endif
                            </div>

                            <!-- Wallet Credits Row -->
                            <div class="flex items-center justify-between">
                                <span class="text-theme-muted flex items-center gap-1.5">
                                    <i class="fa-solid fa-coins text-[10px] text-amber-500"></i> Wallet Credits
                                </span>
                                @php
                                    $userCreditsMobile = (float) ($user->credits ?? 0);
                                @endphp
                                <button type="button"
                                    onclick="openCreditAdjustmentModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', {{ $userCreditsMobile }})"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold transition-all cursor-pointer shadow-sm group
                                        {{ $userCreditsMobile > 0 ? 'bg-cyan-500/15 text-cyan-700 dark:text-cyan-300 border border-cyan-500/30' : 'bg-stone-100 dark:bg-stone-800 text-stone-400 dark:text-stone-500 border border-stone-200 dark:border-stone-700' }}">
                                    <i class="fa-solid fa-coins text-[9px] text-amber-500"></i>
                                    <span id="user-credits-val-mobile-{{ $user->id }}"><strong>{{ number_format($userCreditsMobile, 1) }}</strong></span>
                                    <span>Credits</span>
                                    <i class="fa-solid fa-pen text-[8px] opacity-40 group-hover:opacity-100 transition-opacity ml-0.5"></i>
                                </button>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-theme-muted flex items-center gap-1.5">
                                    <i class="fa-regular fa-clock text-[10px] text-cyan-600 dark:text-cyan-400"></i> Held Timeslots
                                </span>
                                @if($heldCount > 0)
                                    <button type="button"
                                        onclick="openHeldSlotsModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', {{ $heldCount }}, {{ $activeHeldCount }})"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold transition-all cursor-pointer shadow-sm
                                            {{ $heldCount >= 3 ? 'bg-rose-500/15 text-rose-700 dark:text-rose-400 border border-rose-500/30' : 'bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-500/30' }}">
                                        <i class="fa-solid fa-clock-rotate-left text-[9px]"></i>
                                        <span>{{ $heldCount }} held {{ Str::plural('slot', $heldCount) }}</span>
                                        @if($activeHeldCount > 0)
                                            <span class="px-1 py-0.2 rounded-full text-[8px] font-extrabold bg-cyan-500 text-slate-950 uppercase animate-pulse">live</span>
                                        @endif
                                    </button>
                                @else
                                    <span class="text-[11px] text-theme-muted">0 held</span>
                                @endif
                            </div>

                            @if($user->role === 'admin_assistant')
                                <div class="pt-2 border-t border-stone-200/50 dark:border-stone-800/50 space-y-1">
                                    <span class="text-[10px] text-theme-muted block font-semibold">Assigned Modules:</span>
                                    <div class="flex flex-wrap gap-1">
                                        @if(in_array('schedule', $perms))<span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-cyan-500/10 text-cyan-700 dark:text-cyan-300 border border-cyan-500/30">Schedule</span>@endif
                                        @if(in_array('approvals', $perms))<span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30">Approvals</span>@endif
                                        @if(in_array('courts', $perms))<span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border border-indigo-500/30">Courts</span>@endif
                                        @if(in_array('photos', $perms))<span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-purple-500/10 text-purple-700 dark:text-purple-300 border border-purple-500/30">Photos</span>@endif
                                        @if(in_array('users', $perms))<span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-500/10 text-amber-700 dark:text-amber-300 border border-amber-500/30">Users</span>@endif
                                        @if(empty($perms))<span class="text-[9px] text-theme-muted">None</span>@endif
                                    </div>
                                    @if($user->courtOwner)
                                        <div class="text-[10px] text-theme-muted mt-1">Supervisor: <strong class="text-theme-heading">{{ $user->courtOwner->name }}</strong></div>
                                    @endif
                                </div>
                            @elseif($user->role === 'client')
                                <div class="pt-2 border-t border-stone-200/50 dark:border-stone-800/50 space-y-1 text-[11px]">
                                    <div class="flex items-center justify-between">
                                        <span class="text-theme-muted">Bookings:</span>
                                        <span class="font-bold text-theme-heading">{{ $user->bookings_count }} total</span>
                                    </div>
                                    @if($heldCount > 0)
                                        <div class="flex items-center gap-1.5 {{ $heldCount >= 3 ? 'text-rose-600 dark:text-rose-400 font-bold' : 'text-amber-600 dark:text-amber-400 font-medium' }}">
                                            <i class="fa-solid fa-triangle-exclamation text-[10px]"></i>
                                            <span>{{ $heldCount }} held {{ Str::plural('slot', $heldCount) }} without payment</span>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Actions Buttons -->
                        @if($canEdit)
                            @if(Auth::user()->canManageStaff())
                                <div class="pt-2 border-t border-stone-200/60 dark:border-stone-800/60">
                                    @if($user->role === 'client')
                                        <button type="button"
                                            onclick="openTagAssistantModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', '{{ addslashes($user->phone ?? '') }}', [], false, {{ $user->court_owner_id ? $user->court_owner_id : 'null' }})"
                                            class="w-full py-2 px-3 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30 font-bold text-[11px] transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-sm">
                                            <i class="fa-solid fa-user-tie text-[11px]"></i> Tag as Admin Assistant
                                        </button>
                                    @elseif($user->role === 'admin_assistant')
                                        <div class="grid grid-cols-2 gap-1.5">
                                            <button type="button"
                                                onclick="openTagAssistantModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', '{{ addslashes($user->phone ?? '') }}', @json($perms), true, {{ $user->court_owner_id ? $user->court_owner_id : 'null' }})"
                                                class="w-full py-2 px-2 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30 font-bold text-[11px] transition-colors flex items-center justify-center gap-1 cursor-pointer shadow-sm truncate">
                                                <i class="fa-solid fa-user-shield text-[10px]"></i> Staff Modules
                                            </button>
                                            <button type="button"
                                                onclick="confirmRemoveAssistantAccess({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                                class="w-full py-2 px-2 rounded-xl bg-rose-500/15 hover:bg-rose-500/25 text-rose-700 dark:text-rose-400 border border-rose-500/30 font-bold text-[11px] transition-colors flex items-center justify-center gap-1 cursor-pointer shadow-sm truncate">
                                                <i class="fa-solid fa-user-xmark text-[10px]"></i> Remove Access
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            @endif
                            <div class="grid grid-cols-3 gap-1.5 pt-2 {{ Auth::user()->canManageStaff() ? '' : 'border-t border-stone-200/60 dark:border-stone-800/60' }}">
                                <button type="button"
                                    onclick="openCreditAdjustmentModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', {{ (float) ($user->credits ?? 0) }})"
                                    class="w-full py-2 px-1.5 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-cyan-500/20 text-stone-700 dark:text-stone-300 hover:text-cyan-600 font-bold text-[11px] border border-stone-300 dark:border-stone-700 transition-colors flex items-center justify-center gap-1 cursor-pointer"
                                    title="Manage Wallet Credits">
                                    <i class="fa-solid fa-coins text-amber-500 text-[10px]"></i> Credits
                                </button>

                                <form method="POST" action="{{ route('owner.users.reset_password', $user->id) }}"
                                    id="reset-password-form-mobile-{{ $user->id }}" class="w-full">
                                    @csrf
                                    <button type="button"
                                        onclick="confirmResetPassword({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}')"
                                        class="w-full py-2 px-1.5 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-amber-500/20 text-stone-700 dark:text-stone-300 hover:text-amber-500 font-bold text-[11px] border border-stone-300 dark:border-stone-700 transition-colors flex items-center justify-center gap-1 cursor-pointer"
                                        title="Reset Password to PaddleField2026!">
                                        <i class="fa-solid fa-key text-[10px]"></i> Key
                                    </button>
                                </form>

                                <button type="button"
                                    onclick='openEditUserModal(@json($user), @json($perms))'
                                    class="w-full py-2 px-1.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-extrabold text-[11px] shadow-md shadow-cyan-500/20 flex items-center justify-center gap-1 transition-all cursor-pointer">
                                    <i class="fa-solid fa-pen-to-square text-[10px]"></i> Edit
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
                <div class="p-4 sm:p-5 border-t border-stone-200 dark:border-stone-800 overflow-x-auto w-full max-w-full">
                    {{ $users->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

<!-- ========================================== -->
<!-- ADD USER MODAL                             -->
<!-- ========================================== -->
<div id="addUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm hidden transition-all p-3 sm:p-4">
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

                    <div class="space-y-2.5">
                        @foreach($availableModules as $key => $mod)
                            <label class="module-permission-card flex items-start gap-3 p-3.5 rounded-2xl border border-stone-200 dark:border-stone-800 bg-stone-100/60 dark:bg-stone-900/40 hover:border-cyan-500/50 cursor-pointer select-none transition-all duration-200">
                                <input type="checkbox" name="modules[]" value="{{ $key }}"
                                    {{ $mod['default'] ? 'checked' : '' }}
                                    onchange="syncParentModuleCard(this)"
                                    class="sr-only module-checkbox">
                                <div class="module-icon-box w-9 h-9 rounded-xl bg-stone-200 dark:bg-stone-800 text-stone-500 flex items-center justify-center shrink-0 transition-all duration-200">
                                    <i class="fa-solid {{ $mod['icon'] }} text-sm"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2 truncate">
                                            <span class="text-xs font-black text-theme-heading module-title">{{ $mod['name'] }}</span>
                                            @if($mod['default'])
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-500/30 uppercase">Default</span>
                                            @endif
                                        </div>
                                        <div class="shrink-0 flex items-center gap-2">
                                            <span class="module-badge-granted hidden items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-cyan-500 text-slate-950 shadow-sm shadow-cyan-500/30">
                                                <i class="fa-solid fa-check text-[10px]"></i> Active
                                            </span>
                                            <span class="module-badge-disabled inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-stone-200 dark:bg-stone-800 text-theme-muted">
                                                <i class="fa-solid fa-xmark text-[10px]"></i> Off
                                            </span>
                                            <div class="module-indicator-check w-5 h-5 rounded-lg border flex items-center justify-center transition-all duration-200">
                                                <i class="fa-solid fa-check text-[10px]"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-theme-muted mt-1 leading-snug">{{ $mod['description'] }}</p>
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
<div id="editUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm hidden transition-all p-3 sm:p-4">
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

                    <div class="space-y-2.5">
                        @foreach($availableModules as $key => $mod)
                            <label class="module-permission-card flex items-start gap-3 p-3.5 rounded-2xl border border-stone-200 dark:border-stone-800 bg-stone-100/60 dark:bg-stone-900/40 hover:border-cyan-500/50 cursor-pointer select-none transition-all duration-200">
                                <input type="checkbox" name="modules[]" value="{{ $key }}" id="edit_user_module_{{ $key }}"
                                    onchange="syncParentModuleCard(this)"
                                    class="sr-only module-checkbox">
                                <div class="module-icon-box w-9 h-9 rounded-xl bg-stone-200 dark:bg-stone-800 text-stone-500 flex items-center justify-center shrink-0 transition-all duration-200">
                                    <i class="fa-solid {{ $mod['icon'] }} text-sm"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2 truncate">
                                            <span class="text-xs font-black text-theme-heading module-title">{{ $mod['name'] }}</span>
                                            @if($mod['default'])
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-500/30 uppercase">Default</span>
                                            @endif
                                        </div>
                                        <div class="shrink-0 flex items-center gap-2">
                                            <span class="module-badge-granted hidden items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-cyan-500 text-slate-950 shadow-sm shadow-cyan-500/30">
                                                <i class="fa-solid fa-check text-[10px]"></i> Active
                                            </span>
                                            <span class="module-badge-disabled inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-stone-200 dark:bg-stone-800 text-theme-muted">
                                                <i class="fa-solid fa-xmark text-[10px]"></i> Off
                                            </span>
                                            <div class="module-indicator-check w-5 h-5 rounded-lg border flex items-center justify-center transition-all duration-200">
                                                <i class="fa-solid fa-check text-[10px]"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-theme-muted mt-1 leading-snug">{{ $mod['description'] }}</p>
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
<!-- TAG AS ADMIN ASSISTANT MODAL               -->
<!-- ========================================== -->
<div id="tagAssistantModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm hidden transition-all p-3 sm:p-4">
    <div class="w-full max-w-xl max-h-[90vh] overflow-y-auto rounded-3xl glass-dropdown border border-stone-200 dark:border-stone-800 p-6 md:p-8 shadow-2xl relative bg-white/95 dark:bg-stone-900/95">
        <button type="button" onclick="closeTagAssistantModal()" class="absolute top-5 right-5 w-8 h-8 rounded-full bg-stone-200 dark:bg-stone-800 flex items-center justify-center text-theme-muted hover:text-theme-heading cursor-pointer">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="flex items-center gap-3.5 mb-5">
            <div class="w-11 h-11 rounded-2xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-user-tie"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-theme-heading" id="tag_modal_title">Tag User as Admin Assistant</h3>
                <p class="text-xs text-theme-muted" id="tag_modal_subtitle">Authorize this registered user with admin assistant staff privileges and module permissions.</p>
            </div>
        </div>

        <!-- Target User Preview Summary Card -->
        <div class="p-4 rounded-2xl bg-stone-100 dark:bg-stone-800/60 border border-stone-200 dark:border-stone-700/60 flex items-center justify-between gap-3 mb-5">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-11 h-11 rounded-2xl bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30 font-bold flex items-center justify-center text-base shrink-0 uppercase" id="tag_modal_avatar">
                    U
                </div>
                <div class="min-w-0">
                    <div class="text-sm font-bold text-theme-heading truncate" id="tag_modal_name">User Name</div>
                    <div class="text-xs text-theme-muted truncate font-mono" id="tag_modal_email">user@email.com</div>
                    <div class="text-[11px] text-theme-muted font-mono mt-0.5" id="tag_modal_phone">+63 900 000 0000</div>
                </div>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold shrink-0 uppercase tracking-wider" id="tag_modal_badge">
                Client
            </span>
        </div>

        <form id="tagAssistantForm" method="POST" class="space-y-4">
            @csrf

            <!-- Supervisor Selector (For Admins or to reassign owner) -->
            @if(Auth::user()->isAdmin())
                <div>
                    <label class="block text-xs font-bold text-theme-heading mb-1.5">Supervisor (Court Owner)</label>
                    <select name="court_owner_id" id="tag_modal_supervisor"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="">-- No Direct Supervisor --</option>
                        @foreach($courtOwners as $owner)
                            <option value="{{ $owner->id }}">{{ $owner->name }} ({{ $owner->email }})</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Module Permissions Section -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-extrabold text-theme-heading uppercase tracking-wider">Module Permissions</h4>
                    <span class="text-[11px] text-theme-muted">Schedule & Approvals are default</span>
                </div>
                <p class="text-[11px] text-theme-muted mb-2">Check the operations this assistant is permitted to access and manage.</p>

                <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                    @foreach($availableModules as $key => $mod)
                        <label class="module-permission-card flex items-start gap-3 p-3 rounded-2xl border border-stone-200 dark:border-stone-800 bg-stone-100/60 dark:bg-stone-900/40 hover:border-emerald-500/50 cursor-pointer select-none transition-all duration-200">
                            <input type="checkbox" name="modules[]" value="{{ $key }}" id="tag_modal_module_{{ $key }}"
                                {{ $mod['default'] ? 'checked' : '' }}
                                onchange="syncParentModuleCard(this)"
                                class="sr-only module-checkbox">
                            <div class="module-icon-box w-8 h-8 rounded-xl bg-stone-200 dark:bg-stone-800 text-stone-500 flex items-center justify-center shrink-0 transition-all duration-200">
                                <i class="fa-solid {{ $mod['icon'] }} text-xs"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-1.5 truncate">
                                        <span class="text-xs font-black text-theme-heading module-title">{{ $mod['name'] }}</span>
                                        @if($mod['default'])
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-extrabold bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-500/30 uppercase">Default</span>
                                        @endif
                                    </div>
                                    <div class="shrink-0 flex items-center gap-1.5">
                                        <span class="module-badge-granted hidden items-center gap-1 px-2 py-0.2 rounded-full text-[9px] font-black bg-emerald-500 text-slate-950 shadow-sm shadow-emerald-500/30">
                                            <i class="fa-solid fa-check text-[9px]"></i> Active
                                        </span>
                                        <span class="module-badge-disabled inline-flex items-center gap-1 px-2 py-0.2 rounded-full text-[9px] font-bold bg-stone-200 dark:bg-stone-800 text-theme-muted">
                                            <i class="fa-solid fa-xmark text-[9px]"></i> Off
                                        </span>
                                        <div class="module-indicator-check w-4 h-4 rounded-lg border flex items-center justify-center transition-all duration-200">
                                            <i class="fa-solid fa-check text-[9px]"></i>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-[10px] text-theme-muted mt-0.5 leading-tight">{{ $mod['description'] }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-stone-200 dark:border-stone-800">
                <div id="tag_modal_untag_wrapper" class="hidden w-full sm:w-auto">
                    <button type="button" onclick="executeUntagAssistant()"
                        class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-rose-300 dark:border-rose-900/60 bg-rose-500/10 hover:bg-rose-500/20 text-rose-700 dark:text-rose-400 text-xs font-bold transition-all cursor-pointer flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-user-xmark"></i> Remove Admin Assistant Access
                    </button>
                </div>

                <div class="flex items-center justify-end gap-2.5 w-full sm:w-auto ml-auto">
                    <button type="button" onclick="closeTagAssistantModal()"
                        class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 text-xs font-semibold text-theme-muted hover:text-theme-heading cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" id="tag_modal_submit_btn"
                        class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-extrabold shadow-lg shadow-emerald-500/20 cursor-pointer transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-check"></i> <span id="tag_modal_submit_text">Tag as Admin Assistant</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- ADD ADMIN ASSISTANT MODAL (TAG OR CREATE)  -->
<!-- ========================================== -->
<div id="addAssistantModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm hidden transition-all p-3 sm:p-4">
    <div class="w-full max-w-xl max-h-[90vh] overflow-y-auto rounded-3xl glass-dropdown border border-stone-200 dark:border-stone-800 p-6 md:p-8 shadow-2xl relative bg-white/95 dark:bg-stone-900/95">
        <button type="button" onclick="closeAddAssistantModal()" class="absolute top-5 right-5 w-8 h-8 rounded-full bg-stone-200 dark:bg-stone-800 flex items-center justify-center text-theme-muted hover:text-theme-heading cursor-pointer">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="flex items-center gap-3.5 mb-5">
            <div class="w-11 h-11 rounded-2xl bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-theme-heading">Add Admin Assistant</h3>
                <p class="text-xs text-theme-muted">Tag an existing registered user or register a new assistant account.</p>
            </div>
        </div>

        <!-- Mode Toggle Tabs -->
        <div class="flex items-center p-1 rounded-2xl bg-stone-100 dark:bg-stone-800/70 border border-stone-200 dark:border-stone-700/60 mb-5">
            <button type="button" onclick="switchAddAssistantTab('tag')" id="btn_tab_assistant_tag"
                class="flex-1 py-2 px-3 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center justify-center gap-1.5 bg-emerald-500 text-slate-950 shadow-sm shadow-emerald-500/20">
                <i class="fa-solid fa-tag"></i> Tag Registered User
            </button>
            <button type="button" onclick="switchAddAssistantTab('new')" id="btn_tab_assistant_new"
                class="flex-1 py-2 px-3 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center justify-center gap-1.5 text-theme-muted hover:text-theme-heading">
                <i class="fa-solid fa-user-plus"></i> Create New Account
            </button>
        </div>

        <!-- TAB 1: TAG REGISTERED USER -->
        <div id="pane_assistant_tag" class="space-y-4">
            <form action="{{ route('owner.users.tag_assistant') }}" method="POST" class="space-y-4">
                @csrf

                <!-- User Selection Dropdown -->
                <div>
                    <label class="block text-xs font-bold text-theme-heading mb-1.5">Select Registered Player / Client *</label>
                    <select name="user_id" id="add_assistant_client_select" onchange="updateTagUserSelection(this)" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading font-medium focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="" disabled selected>-- Choose a registered player to tag --</option>
                        @foreach($registeredClients as $rc)
                            <option value="{{ $rc->id }}"
                                data-name="{{ $rc->name }}"
                                data-email="{{ $rc->email }}"
                                data-phone="{{ $rc->phone ?? '—' }}">
                                {{ $rc->name }} ({{ $rc->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Selected User Preview Box -->
                <div id="tag_user_preview_box" class="p-3.5 rounded-2xl bg-stone-100/70 dark:bg-stone-800/40 border border-stone-200 dark:border-stone-700/60 hidden items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30 font-bold flex items-center justify-center text-sm shrink-0 uppercase" id="tag_user_preview_avatar">
                            U
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-theme-heading truncate" id="tag_user_preview_name">Name</div>
                            <div class="text-[11px] text-theme-muted truncate font-mono" id="tag_user_preview_email">Email</div>
                            <div class="text-[10px] text-theme-muted font-mono" id="tag_user_preview_phone">Phone</div>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-stone-200 dark:bg-stone-800 text-theme-body border border-stone-300 dark:border-stone-700">
                        Player Account
                    </span>
                </div>

                @if(Auth::user()->isAdmin())
                    <div>
                        <label class="block text-xs font-bold text-theme-heading mb-1.5">Supervisor (Court Owner)</label>
                        <select name="court_owner_id"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="">-- No Direct Supervisor --</option>
                            @foreach($courtOwners as $owner)
                                <option value="{{ $owner->id }}">{{ $owner->name }} ({{ $owner->email }})</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- Module Permissions Section -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-extrabold text-theme-heading uppercase tracking-wider">Module Permissions</h4>
                        <span class="text-[11px] text-theme-muted">Schedule & Approvals are default</span>
                    </div>
                    <p class="text-[11px] text-theme-muted mb-2">Configure operational module access granted to this assistant.</p>

                    <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                        @foreach($availableModules as $key => $mod)
                            <label class="module-permission-card flex items-start gap-3 p-3 rounded-2xl border border-stone-200 dark:border-stone-800 bg-stone-100/60 dark:bg-stone-900/40 hover:border-emerald-500/50 cursor-pointer select-none transition-all duration-200">
                                <input type="checkbox" name="modules[]" value="{{ $key }}"
                                    {{ $mod['default'] ? 'checked' : '' }}
                                    onchange="syncParentModuleCard(this)"
                                    class="sr-only module-checkbox">
                                <div class="module-icon-box w-8 h-8 rounded-xl bg-stone-200 dark:bg-stone-800 text-stone-500 flex items-center justify-center shrink-0 transition-all duration-200">
                                    <i class="fa-solid {{ $mod['icon'] }} text-xs"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-1.5 truncate">
                                            <span class="text-xs font-black text-theme-heading module-title">{{ $mod['name'] }}</span>
                                            @if($mod['default'])
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-extrabold bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-500/30 uppercase">Default</span>
                                            @endif
                                        </div>
                                        <div class="shrink-0 flex items-center gap-1.5">
                                            <span class="module-badge-granted hidden items-center gap-1 px-2 py-0.2 rounded-full text-[9px] font-black bg-emerald-500 text-slate-950 shadow-sm shadow-emerald-500/30">
                                                <i class="fa-solid fa-check text-[9px]"></i> Active
                                            </span>
                                            <span class="module-badge-disabled inline-flex items-center gap-1 px-2 py-0.2 rounded-full text-[9px] font-bold bg-stone-200 dark:bg-stone-800 text-theme-muted">
                                                <i class="fa-solid fa-xmark text-[9px]"></i> Off
                                            </span>
                                            <div class="module-indicator-check w-4 h-4 rounded-lg border flex items-center justify-center transition-all duration-200">
                                                <i class="fa-solid fa-check text-[9px]"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-theme-muted mt-0.5 leading-tight">{{ $mod['description'] }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-stone-200 dark:border-stone-800">
                    <button type="button" onclick="closeAddAssistantModal()"
                        class="px-4 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 text-xs font-semibold text-theme-muted hover:text-theme-heading cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-extrabold shadow-lg shadow-emerald-500/20 cursor-pointer transition-all flex items-center gap-2">
                        <i class="fa-solid fa-user-check"></i> Tag as Admin Assistant
                    </button>
                </div>
            </form>
        </div>

        <!-- TAB 2: CREATE NEW ASSISTANT ACCOUNT -->
        <div id="pane_assistant_new" class="space-y-4 hidden">
            <form action="{{ route('owner.users.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="role" value="admin_assistant">

                <div>
                    <label class="block text-xs font-bold text-theme-heading mb-1.5">Full Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Maria Santos"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-theme-heading mb-1.5">Email Address *</label>
                        <input type="email" name="email" required placeholder="assistant@paddlefield.com"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-theme-heading mb-1.5">Phone Number</label>
                        <input type="text" name="phone" placeholder="+63 918 000 0000"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>

                @if(Auth::user()->isAdmin())
                    <div>
                        <label class="block text-xs font-bold text-theme-heading mb-1.5">Supervisor (Court Owner)</label>
                        <select name="court_owner_id"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="">-- No Direct Supervisor --</option>
                            @foreach($courtOwners as $owner)
                                <option value="{{ $owner->id }}">{{ $owner->name }} ({{ $owner->email }})</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-theme-heading mb-1.5">Password *</label>
                        <input type="password" name="password" required minlength="6" placeholder="••••••••"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-theme-heading mb-1.5">Confirm Password *</label>
                        <input type="password" name="password_confirmation" required minlength="6" placeholder="••••••••"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>

                <!-- Module Permissions Section -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-extrabold text-theme-heading uppercase tracking-wider">Module Permissions</h4>
                        <span class="text-[11px] text-theme-muted">Schedule & Approvals are default</span>
                    </div>

                    <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                        @foreach($availableModules as $key => $mod)
                            <label class="module-permission-card flex items-start gap-3 p-3 rounded-2xl border border-stone-200 dark:border-stone-800 bg-stone-100/60 dark:bg-stone-900/40 hover:border-emerald-500/50 cursor-pointer select-none transition-all duration-200">
                                <input type="checkbox" name="modules[]" value="{{ $key }}"
                                    {{ $mod['default'] ? 'checked' : '' }}
                                    onchange="syncParentModuleCard(this)"
                                    class="sr-only module-checkbox">
                                <div class="module-icon-box w-8 h-8 rounded-xl bg-stone-200 dark:bg-stone-800 text-stone-500 flex items-center justify-center shrink-0 transition-all duration-200">
                                    <i class="fa-solid {{ $mod['icon'] }} text-xs"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-1.5 truncate">
                                            <span class="text-xs font-black text-theme-heading module-title">{{ $mod['name'] }}</span>
                                            @if($mod['default'])
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-extrabold bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-500/30 uppercase">Default</span>
                                            @endif
                                        </div>
                                        <div class="shrink-0 flex items-center gap-1.5">
                                            <span class="module-badge-granted hidden items-center gap-1 px-2 py-0.2 rounded-full text-[9px] font-black bg-emerald-500 text-slate-950 shadow-sm shadow-emerald-500/30">
                                                <i class="fa-solid fa-check text-[9px]"></i> Active
                                            </span>
                                            <span class="module-badge-disabled inline-flex items-center gap-1 px-2 py-0.2 rounded-full text-[9px] font-bold bg-stone-200 dark:bg-stone-800 text-theme-muted">
                                                <i class="fa-solid fa-xmark text-[9px]"></i> Off
                                            </span>
                                            <div class="module-indicator-check w-4 h-4 rounded-lg border flex items-center justify-center transition-all duration-200">
                                                <i class="fa-solid fa-check text-[9px]"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-theme-muted mt-0.5 leading-tight">{{ $mod['description'] }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-stone-200 dark:border-stone-800">
                    <button type="button" onclick="closeAddAssistantModal()"
                        class="px-4 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 text-xs font-semibold text-theme-muted hover:text-theme-heading cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-extrabold shadow-lg shadow-emerald-500/20 cursor-pointer transition-all flex items-center gap-2">
                        <i class="fa-solid fa-user-plus"></i> Create Admin Assistant
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- HELD TIMESLOTS DETAILS MODAL               -->
<!-- ========================================== -->
<div id="heldSlotsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm hidden transition-all p-3 sm:p-4">
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
<div id="deactivateUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm hidden transition-all p-3 sm:p-4">
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

<!-- ========================================== -->
<!-- CREDIT ADJUSTMENT & LEDGER MODAL           -->
<!-- ========================================== -->
<div id="creditAdjustmentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm hidden transition-all p-3 sm:p-4">
    <div class="w-full max-w-xl max-h-[92vh] flex flex-col rounded-3xl glass-dropdown border border-stone-200 dark:border-stone-800 shadow-2xl overflow-hidden relative">
        <!-- Modal Header -->
        <div class="p-5 md:p-6 pb-4 border-b border-stone-200 dark:border-stone-800 flex items-center justify-between shrink-0 bg-stone-50 dark:bg-stone-900/40">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-cyan-500/15 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-coins text-amber-500"></i>
                </div>
                <div>
                    <h3 class="text-base md:text-lg font-bold text-theme-heading flex items-center gap-2">
                        <span>Manage Wallet Credits</span>
                    </h3>
                    <p class="text-xs text-theme-muted" id="credit_modal_user_subtitle">Add or deduct player court credits</p>
                </div>
            </div>
            <button type="button" onclick="closeCreditAdjustmentModal()" class="w-8 h-8 rounded-full bg-stone-200 dark:bg-stone-800 flex items-center justify-center text-theme-muted hover:text-theme-heading cursor-pointer transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Scrollable Content -->
        <div class="p-5 md:p-6 overflow-y-auto space-y-4 flex-1">
            <!-- Target User & Balance Info Card -->
            <div class="p-4 rounded-2xl bg-stone-100 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-800 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-cyan-500/15 text-cyan-600 dark:text-cyan-400 font-bold flex items-center justify-center text-sm shrink-0 uppercase" id="credit_modal_avatar">
                        U
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-theme-heading truncate" id="credit_modal_user_name">User Name</div>
                        <div class="text-[11px] text-theme-muted truncate font-mono" id="credit_modal_user_email">user@email.com</div>
                    </div>
                </div>
                <!-- Current Balance Display -->
                <div class="text-right shrink-0">
                    <span class="text-[10px] uppercase font-bold text-theme-muted block">Current Balance</span>
                    <div class="text-base font-extrabold text-cyan-600 dark:text-cyan-400 flex items-center justify-end gap-1">
                        <i class="fa-solid fa-wallet text-[11px] text-amber-500"></i>
                        <span id="credit_modal_current_balance_display">0.0</span>
                        <span class="text-xs font-semibold">Credits</span>
                    </div>
                </div>
            </div>

            <!-- Tab Switcher (Adjust Balance vs Transaction Ledger) -->
            <div class="flex items-center gap-2 border-b border-stone-200 dark:border-stone-800 pb-2">
                <button type="button" onclick="switchCreditModalTab('adjust')" id="tab_btn_credit_adjust"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer bg-cyan-500 text-slate-950 shadow-md shadow-cyan-500/20">
                    <i class="fa-solid fa-sliders"></i>
                    <span>Adjust Balance</span>
                </button>
                <button type="button" onclick="switchCreditModalTab('ledger')" id="tab_btn_credit_ledger"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer bg-stone-200 dark:bg-stone-800 text-theme-muted hover:text-theme-heading">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Transaction Ledger</span>
                    <span id="credit_modal_ledger_count" class="px-1.5 py-0.2 rounded-full text-[9px] bg-stone-300 dark:bg-stone-700 text-theme-muted">...</span>
                </button>
            </div>

            <!-- Tab Pane 1: Adjust Form -->
            <div id="pane_credit_adjust" class="space-y-4">
                <form id="creditAdjustmentForm" onsubmit="submitCreditAdjustment(event)" class="space-y-4">
                    <input type="hidden" id="credit_modal_user_id" value="">
                    <input type="hidden" id="credit_modal_action" value="add">

                    <!-- Action Selector (Add vs Deduct) -->
                    <div>
                        <label class="block text-xs font-bold text-theme-heading mb-1.5">Action Type</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" onclick="setCreditAction('add')" id="action_btn_add"
                                class="py-2.5 px-3 rounded-xl font-bold text-xs flex items-center justify-center gap-2 transition-all cursor-pointer border border-emerald-500/50 bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 shadow-sm">
                                <i class="fa-solid fa-circle-plus text-emerald-500"></i>
                                <span>Add Credits (+)</span>
                            </button>
                            <button type="button" onclick="setCreditAction('deduct')" id="action_btn_deduct"
                                class="py-2.5 px-3 rounded-xl font-bold text-xs flex items-center justify-center gap-2 transition-all cursor-pointer border border-stone-300 dark:border-stone-700 bg-stone-200/60 dark:bg-stone-800/60 text-theme-muted hover:bg-stone-200 dark:hover:bg-stone-800">
                                <i class="fa-solid fa-circle-minus text-rose-500"></i>
                                <span>Deduct Credits (-)</span>
                            </button>
                        </div>
                    </div>

                    <!-- Amount Input & Quick Chips -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="credit_modal_amount" class="block text-xs font-bold text-theme-heading">
                                Amount of Credits <span class="text-cyan-500">*</span>
                            </label>
                            <span class="text-[10px] text-theme-muted">1 credit = 1 court hr</span>
                        </div>

                        <!-- Quick Chips -->
                        <div class="mb-2 flex flex-wrap gap-1.5" id="credit_amount_chips">
                            <!-- Populated dynamically based on add vs deduct -->
                        </div>

                        <div class="relative">
                            <input type="number" id="credit_modal_amount" step="0.5" min="0.1" max="50000" required
                                placeholder="e.g. 5.0" oninput="recalcCreditBalance()"
                                class="w-full pl-3.5 pr-20 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-sm font-bold text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none transition-all">
                            <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-theme-muted pointer-events-none">Credits</span>
                        </div>
                    </div>

                    <!-- Dynamic Balance Calculation Preview Card -->
                    <div id="credit_calc_preview_card" class="p-3.5 rounded-2xl bg-cyan-500/10 border border-cyan-500/30 text-xs transition-all">
                        <div class="flex items-center justify-between font-mono text-[11px] text-theme-muted mb-1">
                            <span>Calculation Preview:</span>
                            <span id="calc_formula_text">0.0 + 0.0</span>
                        </div>
                        <div class="flex items-center justify-between pt-1 border-t border-cyan-500/20">
                            <span class="font-bold text-theme-heading text-xs">Resulting Wallet Balance:</span>
                            <span id="calc_new_balance_display" class="font-extrabold text-sm text-cyan-600 dark:text-cyan-400">0.0 Credits</span>
                        </div>
                        <p id="calc_warning_text" class="hidden text-[11px] text-rose-500 font-bold mt-1.5 flex items-center gap-1">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <span id="calc_warning_msg">Cannot deduct more credits than current balance!</span>
                        </p>
                    </div>

                    <!-- Reason Input & Preset Chips -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="credit_modal_reason" class="block text-xs font-bold text-theme-heading">
                                Reason / Reference Note <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[10px] text-theme-muted">Recorded in transaction audit ledger</span>
                        </div>

                        <!-- Reason Preset Chips -->
                        <div class="mb-2 flex flex-wrap gap-1.5" id="credit_reason_chips">
                            <!-- Populated dynamically -->
                        </div>

                        <textarea id="credit_modal_reason" rows="2" required
                            placeholder="Provide reason for this manual adjustment..."
                            class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none transition-all resize-none"></textarea>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-stone-200 dark:border-stone-800">
                        <button type="button" onclick="closeCreditAdjustmentModal()"
                            class="px-4 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 text-xs font-semibold text-theme-muted hover:text-theme-heading cursor-pointer transition-colors">
                            Cancel
                        </button>
                        <button type="submit" id="credit_submit_btn"
                            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-md shadow-emerald-600/20 cursor-pointer transition-all flex items-center gap-2">
                            <span id="credit_submit_spinner" class="hidden"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                            <span id="credit_submit_icon"><i class="fa-solid fa-circle-plus"></i></span>
                            <span id="credit_submit_btn_text">Confirm Add Credits</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tab Pane 2: Ledger History -->
            <div id="pane_credit_ledger" class="space-y-3 hidden">
                <div id="credit_ledger_loading" class="py-10 text-center text-theme-muted space-y-2">
                    <i class="fa-solid fa-circle-notch fa-spin text-xl text-cyan-500"></i>
                    <p class="text-xs">Fetching credit transaction ledger...</p>
                </div>
                <div id="credit_ledger_empty" class="py-10 text-center text-theme-muted space-y-2 hidden">
                    <div class="w-12 h-12 rounded-xl bg-stone-200 dark:bg-stone-800 flex items-center justify-center mx-auto text-xl">
                        <i class="fa-solid fa-receipt text-stone-400"></i>
                    </div>
                    <h4 class="text-xs font-bold text-theme-heading">No Transactions Yet</h4>
                    <p class="text-[11px] max-w-xs mx-auto">This player has no recorded credit top-ups, booking deductions, or adjustments.</p>
                </div>
                <div id="credit_ledger_list" class="space-y-2 hidden max-h-80 overflow-y-auto pr-1">
                    <!-- Populated dynamically via AJAX -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const availableUserModules = ['schedule', 'approvals', 'courts', 'photos', 'users', 'settings'];

    function syncParentModuleCard(checkbox) {
        if (!checkbox) return;
        const card = checkbox.closest('.module-permission-card');
        if (!card) return;
        if (checkbox.checked) {
            card.classList.add('is-active');
        } else {
            card.classList.remove('is-active');
        }
    }

    function syncAllModuleCards(containerId = null) {
        const root = containerId ? document.getElementById(containerId) : document;
        if (!root) return;
        root.querySelectorAll('.module-permission-card input[type="checkbox"]').forEach(cb => {
            syncParentModuleCard(cb);
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        syncAllModuleCards();
    });

    function openAddUserModal() {
        document.getElementById('addUserModal').classList.remove('hidden');
        syncAllModuleCards('addUserModal');
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
        syncAllModuleCards('editUserModal');

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
            const checkedBoxes = section.querySelectorAll('input[name="modules[]"]:checked');
            if (checkedBoxes.length === 0) {
                const defaultMods = ['schedule', 'approvals'];
                defaultMods.forEach(mod => {
                    const cb = document.getElementById(`edit_user_module_${mod}`);
                    if (cb) cb.checked = true;
                });
                syncAllModuleCards('edit_assistant_section');
            }
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
        const desktopCell = document.getElementById(`user-status-cell-${userId}`);
        const mobileCell = document.getElementById(`user-status-cell-mobile-${userId}`);
        if (!desktopCell && !mobileCell) return;

        const safeName = escapeHtml(userName);
        const safeEmail = escapeHtml(userEmail);
        const safeRole = escapeHtml(userRole);
        const safeReason = reason ? escapeHtml(reason) : '';
        const jsName = escapeJsString(userName);
        const jsEmail = escapeJsString(userEmail);
        const jsRole = escapeJsString(userRole);
        const jsReason = reason ? escapeJsString(reason) : '';

        const renderHtml = (btnId) => {
            if (isActive) {
                return `
                    <button type="button"
                        id="${btnId}"
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

                return `
                    <button type="button"
                        id="${btnId}"
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
        };

        if (desktopCell) desktopCell.innerHTML = renderHtml(`status-btn-${userId}`);
        if (mobileCell) mobileCell.innerHTML = renderHtml(`status-btn-mobile-${userId}`);
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

    // ==========================================
    // CREDIT ADJUSTMENT MODAL LOGIC
    // ==========================================
    let currentCreditUserId = null;
    let currentCreditUserBalance = 0;
    let currentCreditAction = 'add';

    function openCreditAdjustmentModal(userId, name, email, credits) {
        currentCreditUserId = userId;
        currentCreditUserBalance = parseFloat(credits) || 0;
        currentCreditAction = 'add';

        document.getElementById('credit_modal_user_id').value = userId;
        document.getElementById('credit_modal_user_name').textContent = name;
        document.getElementById('credit_modal_user_email').textContent = email;
        document.getElementById('credit_modal_avatar').textContent = name.charAt(0).toUpperCase();
        document.getElementById('credit_modal_current_balance_display').textContent = currentCreditUserBalance.toFixed(1);
        document.getElementById('credit_modal_user_subtitle').textContent = `Manage wallet credits for ${name}`;

        // Reset inputs
        document.getElementById('credit_modal_amount').value = '';
        document.getElementById('credit_modal_reason').value = '';

        setCreditAction('add');
        switchCreditModalTab('adjust');

        // Fetch transaction ledger in background
        loadUserCreditHistory(userId);

        const modal = document.getElementById('creditAdjustmentModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeCreditAdjustmentModal() {
        const modal = document.getElementById('creditAdjustmentModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    function switchCreditModalTab(tab) {
        const paneAdjust = document.getElementById('pane_credit_adjust');
        const paneLedger = document.getElementById('pane_credit_ledger');
        const btnAdjust = document.getElementById('tab_btn_credit_adjust');
        const btnLedger = document.getElementById('tab_btn_credit_ledger');

        if (tab === 'adjust') {
            paneAdjust.classList.remove('hidden');
            paneLedger.classList.add('hidden');
            btnAdjust.className = 'px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer bg-cyan-500 text-slate-950 shadow-md shadow-cyan-500/20';
            btnLedger.className = 'px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer bg-stone-200 dark:bg-stone-800 text-theme-muted hover:text-theme-heading';
        } else {
            paneAdjust.classList.add('hidden');
            paneLedger.classList.remove('hidden');
            btnLedger.className = 'px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer bg-cyan-500 text-slate-950 shadow-md shadow-cyan-500/20';
            btnAdjust.className = 'px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer bg-stone-200 dark:bg-stone-800 text-theme-muted hover:text-theme-heading';
        }
    }

    function setCreditAction(action) {
        currentCreditAction = action;
        document.getElementById('credit_modal_action').value = action;

        const btnAdd = document.getElementById('action_btn_add');
        const btnDeduct = document.getElementById('action_btn_deduct');
        const submitBtn = document.getElementById('credit_submit_btn');
        const submitText = document.getElementById('credit_submit_btn_text');
        const submitIcon = document.getElementById('credit_submit_icon');
        const chipsContainer = document.getElementById('credit_amount_chips');
        const reasonChipsContainer = document.getElementById('credit_reason_chips');

        if (action === 'add') {
            btnAdd.className = 'py-2.5 px-3 rounded-xl font-bold text-xs flex items-center justify-center gap-2 transition-all cursor-pointer border border-emerald-500/50 bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 shadow-sm';
            btnDeduct.className = 'py-2.5 px-3 rounded-xl font-bold text-xs flex items-center justify-center gap-2 transition-all cursor-pointer border border-stone-300 dark:border-stone-700 bg-stone-200/60 dark:bg-stone-800/60 text-theme-muted hover:bg-stone-200 dark:hover:bg-stone-800';

            submitBtn.className = 'px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-md shadow-emerald-600/20 cursor-pointer transition-all flex items-center gap-2';
            submitText.textContent = 'Confirm Add Credits';
            submitIcon.innerHTML = '<i class="fa-solid fa-circle-plus"></i>';

            chipsContainer.innerHTML = `
                <button type="button" onclick="applyQuickCredit(1)" class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-stone-200/80 dark:bg-stone-800 text-theme-body hover:bg-emerald-500/15 hover:text-emerald-600 border border-stone-300/60 dark:border-stone-700 transition-colors cursor-pointer">+1 Credit</button>
                <button type="button" onclick="applyQuickCredit(2)" class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-stone-200/80 dark:bg-stone-800 text-theme-body hover:bg-emerald-500/15 hover:text-emerald-600 border border-stone-300/60 dark:border-stone-700 transition-colors cursor-pointer">+2 Credits</button>
                <button type="button" onclick="applyQuickCredit(5)" class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-stone-200/80 dark:bg-stone-800 text-theme-body hover:bg-emerald-500/15 hover:text-emerald-600 border border-stone-300/60 dark:border-stone-700 transition-colors cursor-pointer">+5 Credits</button>
                <button type="button" onclick="applyQuickCredit(10)" class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-stone-200/80 dark:bg-stone-800 text-theme-body hover:bg-emerald-500/15 hover:text-emerald-600 border border-stone-300/60 dark:border-stone-700 transition-colors cursor-pointer">+10 Credits</button>
                <button type="button" onclick="applyQuickCredit(20)" class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-stone-200/80 dark:bg-stone-800 text-theme-body hover:bg-emerald-500/15 hover:text-emerald-600 border border-stone-300/60 dark:border-stone-700 transition-colors cursor-pointer">+20 Credits</button>
            `;

            reasonChipsContainer.innerHTML = `
                <button type="button" onclick="setCreditReason('In-person cash payment at front desk')" class="px-2.5 py-1 rounded-lg text-[10px] font-semibold bg-stone-200/80 dark:bg-stone-800 text-theme-body hover:bg-cyan-500/15 hover:text-cyan-600 border border-stone-300/60 dark:border-stone-700 transition-colors cursor-pointer">💵 Cash at Front Desk</button>
                <button type="button" onclick="setCreditReason('Complimentary tournament / promo bonus')" class="px-2.5 py-1 rounded-lg text-[10px] font-semibold bg-stone-200/80 dark:bg-stone-800 text-theme-body hover:bg-cyan-500/15 hover:text-cyan-600 border border-stone-300/60 dark:border-stone-700 transition-colors cursor-pointer">🎁 Promo / Bonus</button>
                <button type="button" onclick="setCreditReason('Refund for cancelled court reservation')" class="px-2.5 py-1 rounded-lg text-[10px] font-semibold bg-stone-200/80 dark:bg-stone-800 text-theme-body hover:bg-cyan-500/15 hover:text-cyan-600 border border-stone-300/60 dark:border-stone-700 transition-colors cursor-pointer">🔄 Booking Refund</button>
            `;
        } else {
            btnDeduct.className = 'py-2.5 px-3 rounded-xl font-bold text-xs flex items-center justify-center gap-2 transition-all cursor-pointer border border-rose-500/50 bg-rose-500/15 text-rose-700 dark:text-rose-300 shadow-sm';
            btnAdd.className = 'py-2.5 px-3 rounded-xl font-bold text-xs flex items-center justify-center gap-2 transition-all cursor-pointer border border-stone-300 dark:border-stone-700 bg-stone-200/60 dark:bg-stone-800/60 text-theme-muted hover:bg-stone-200 dark:hover:bg-stone-800';

            submitBtn.className = 'px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold shadow-md shadow-rose-600/20 cursor-pointer transition-all flex items-center gap-2';
            submitText.textContent = 'Confirm Deduct Credits';
            submitIcon.innerHTML = '<i class="fa-solid fa-circle-minus"></i>';

            chipsContainer.innerHTML = `
                <button type="button" onclick="applyQuickCredit(1)" class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-stone-200/80 dark:bg-stone-800 text-theme-body hover:bg-rose-500/15 hover:text-rose-600 border border-stone-300/60 dark:border-stone-700 transition-colors cursor-pointer">-1 Credit</button>
                <button type="button" onclick="applyQuickCredit(2)" class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-stone-200/80 dark:bg-stone-800 text-theme-body hover:bg-rose-500/15 hover:text-rose-600 border border-stone-300/60 dark:border-stone-700 transition-colors cursor-pointer">-2 Credits</button>
                <button type="button" onclick="applyQuickCredit(5)" class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-stone-200/80 dark:bg-stone-800 text-theme-body hover:bg-rose-500/15 hover:text-rose-600 border border-stone-300/60 dark:border-stone-700 transition-colors cursor-pointer">-5 Credits</button>
                <button type="button" onclick="applyQuickCredit(${currentCreditUserBalance})" class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-rose-500/15 text-rose-700 dark:text-rose-300 hover:bg-rose-500/25 border border-rose-500/30 transition-colors cursor-pointer">Clear All (${currentCreditUserBalance.toFixed(1)})</button>
            `;

            reasonChipsContainer.innerHTML = `
                <button type="button" onclick="setCreditReason('Administrative balance correction')" class="px-2.5 py-1 rounded-lg text-[10px] font-semibold bg-stone-200/80 dark:bg-stone-800 text-theme-body hover:bg-rose-500/15 hover:text-rose-600 border border-stone-300/60 dark:border-stone-700 transition-colors cursor-pointer">⚖️ Balance Correction</button>
                <button type="button" onclick="setCreditReason('In-person court reservation charge')" class="px-2.5 py-1 rounded-lg text-[10px] font-semibold bg-stone-200/80 dark:bg-stone-800 text-theme-body hover:bg-rose-500/15 hover:text-rose-600 border border-stone-300/60 dark:border-stone-700 transition-colors cursor-pointer">🎾 In-person Court Fee</button>
                <button type="button" onclick="setCreditReason('Expired / voided promotional credits')" class="px-2.5 py-1 rounded-lg text-[10px] font-semibold bg-stone-200/80 dark:bg-stone-800 text-theme-body hover:bg-rose-500/15 hover:text-rose-600 border border-stone-300/60 dark:border-stone-700 transition-colors cursor-pointer">⌛ Expired Promo</button>
            `;
        }

        recalcCreditBalance();
    }

    function applyQuickCredit(amount) {
        document.getElementById('credit_modal_amount').value = amount;
        recalcCreditBalance();
    }

    function setCreditReason(text) {
        document.getElementById('credit_modal_reason').value = text;
    }

    function recalcCreditBalance() {
        const amountInput = document.getElementById('credit_modal_amount');
        const amount = parseFloat(amountInput.value) || 0;
        const formulaEl = document.getElementById('calc_formula_text');
        const newBalanceEl = document.getElementById('calc_new_balance_display');
        const warningEl = document.getElementById('calc_warning_text');
        const warningMsg = document.getElementById('calc_warning_msg');
        const submitBtn = document.getElementById('credit_submit_btn');

        let newBalance = currentCreditUserBalance;

        if (currentCreditAction === 'add') {
            newBalance = currentCreditUserBalance + amount;
            formulaEl.textContent = `${currentCreditUserBalance.toFixed(1)} + ${amount.toFixed(1)}`;
            newBalanceEl.textContent = `${newBalance.toFixed(1)} Credits`;
            newBalanceEl.className = 'font-extrabold text-sm text-emerald-600 dark:text-emerald-400';
            warningEl.classList.add('hidden');
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            newBalance = currentCreditUserBalance - amount;
            formulaEl.textContent = `${currentCreditUserBalance.toFixed(1)} - ${amount.toFixed(1)}`;
            newBalanceEl.textContent = `${newBalance.toFixed(1)} Credits`;

            if (newBalance < 0) {
                newBalanceEl.className = 'font-extrabold text-sm text-rose-600 dark:text-rose-400';
                warningMsg.textContent = `Deduction exceeds user balance by ${Math.abs(newBalance).toFixed(1)} credits!`;
                warningEl.classList.remove('hidden');
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                newBalanceEl.className = 'font-extrabold text-sm text-cyan-600 dark:text-cyan-400';
                warningEl.classList.add('hidden');
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }
    }

    async function loadUserCreditHistory(userId) {
        const loadingEl = document.getElementById('credit_ledger_loading');
        const emptyEl = document.getElementById('credit_ledger_empty');
        const listEl = document.getElementById('credit_ledger_list');
        const countBadge = document.getElementById('credit_modal_ledger_count');

        loadingEl.classList.remove('hidden');
        emptyEl.classList.add('hidden');
        listEl.classList.add('hidden');
        listEl.innerHTML = '';

        try {
            const res = await fetch(`/owner/users/${userId}/credits`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await res.json();

            loadingEl.classList.add('hidden');

            if (data.success && data.transactions && data.transactions.length > 0) {
                countBadge.textContent = data.transactions.length;
                renderCreditLedger(data.transactions);
                listEl.classList.remove('hidden');
            } else {
                countBadge.textContent = '0';
                emptyEl.classList.remove('hidden');
            }
        } catch (err) {
            loadingEl.classList.add('hidden');
            emptyEl.classList.remove('hidden');
            countBadge.textContent = '0';
        }
    }

    function renderCreditLedger(transactions) {
        const listEl = document.getElementById('credit_ledger_list');
        listEl.innerHTML = '';

        transactions.forEach(tx => {
            const isPositive = parseFloat(tx.amount_credits) > 0;
            const badgeBg = isPositive ? 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border-emerald-500/30' : 'bg-rose-500/15 text-rose-700 dark:text-rose-400 border-rose-500/30';
            const icon = isPositive ? 'fa-plus' : 'fa-minus';

            const card = document.createElement('div');
            card.className = 'p-3 rounded-2xl bg-stone-100 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-800 text-xs flex items-center justify-between gap-3';
            card.innerHTML = `
                <div class="min-w-0">
                    <div class="flex items-center gap-2 mb-0.5">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border ${badgeBg}">
                            <i class="fa-solid ${icon} text-[8px]"></i>
                            <span>${escapeHtml(tx.type.replace('_', ' ').toUpperCase())}</span>
                        </span>
                        <span class="text-[10px] text-theme-muted">${escapeHtml(tx.created_at)}</span>
                    </div>
                    <p class="text-[11px] text-theme-heading font-medium truncate" title="${escapeHtml(tx.description)}">
                        ${escapeHtml(tx.description)}
                    </p>
                </div>
                <div class="text-right shrink-0">
                    <span class="font-extrabold text-xs ${isPositive ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'}">
                        ${tx.formatted_credits}
                    </span>
                    <span class="text-[10px] text-theme-muted block font-mono">Bal: ${parseFloat(tx.balance_after).toFixed(1)}</span>
                </div>
            `;
            listEl.appendChild(card);
        });
    }

    async function submitCreditAdjustment(event) {
        event.preventDefault();

        const userId = currentCreditUserId;
        const action = document.getElementById('credit_modal_action').value;
        const amount = parseFloat(document.getElementById('credit_modal_amount').value);
        const reason = document.getElementById('credit_modal_reason').value.trim();

        if (!amount || amount <= 0) {
            Swal.fire({ icon: 'warning', title: 'Invalid Amount', text: 'Please enter a valid credit amount greater than 0.' });
            return;
        }

        if (!reason) {
            Swal.fire({ icon: 'warning', title: 'Reason Required', text: 'Please provide a reference note or reason for this credit adjustment.' });
            return;
        }

        const submitBtn = document.getElementById('credit_submit_btn');
        const spinner = document.getElementById('credit_submit_spinner');
        submitBtn.disabled = true;
        spinner.classList.remove('hidden');

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            const response = await fetch(`/owner/users/${userId}/credits`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: action,
                    amount: amount,
                    reason: reason
                })
            });

            const data = await response.json();

            submitBtn.disabled = false;
            spinner.classList.add('hidden');

            if (data.success) {
                // Update local tracking
                currentCreditUserBalance = parseFloat(data.credits);
                document.getElementById('credit_modal_current_balance_display').textContent = currentCreditUserBalance.toFixed(1);

                // Update desktop table value
                const desktopVal = document.getElementById(`user-credits-val-${userId}`);
                if (desktopVal) {
                    desktopVal.innerHTML = `<strong>${currentCreditUserBalance.toFixed(1)}</strong>`;
                }

                // Update mobile view value
                const mobileVal = document.getElementById(`user-credits-val-mobile-${userId}`);
                if (mobileVal) {
                    mobileVal.innerHTML = `<strong>${currentCreditUserBalance.toFixed(1)}</strong>`;
                }

                // Show success toast
                Swal.fire({
                    icon: 'success',
                    title: action === 'add' ? 'Credits Added' : 'Credits Deducted',
                    text: data.message,
                    timer: 3500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });

                // Reset form inputs & reload history
                document.getElementById('credit_modal_amount').value = '';
                document.getElementById('credit_modal_reason').value = '';
                recalcCreditBalance();
                loadUserCreditHistory(userId);
                switchCreditModalTab('ledger');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Adjustment Failed',
                    text: data.message || 'Unable to complete credit adjustment.'
                });
            }
        } catch (err) {
            submitBtn.disabled = false;
            spinner.classList.add('hidden');
            Swal.fire({
                icon: 'error',
                title: 'Network Error',
                text: 'An error occurred while communicating with the server. Please check your connection and try again.'
            });
        }
    }

    // ==========================================
    // TAG AS ADMIN ASSISTANT & ADD ASSISTANT
    // ==========================================
    let currentTagUserId = null;
    let currentTagUserName = '';

    function openTagAssistantModal(userId, name, email, phone, permissions = [], isAlreadyAssistant = false, courtOwnerId = null) {
        currentTagUserId = userId;
        currentTagUserName = name;

        const form = document.getElementById('tagAssistantForm');
        form.action = "{{ url('owner/users') }}/" + userId + "/tag-assistant";

        document.getElementById('tag_modal_name').textContent = name;
        document.getElementById('tag_modal_email').textContent = email;
        document.getElementById('tag_modal_phone').textContent = phone || 'No phone recorded';
        document.getElementById('tag_modal_avatar').textContent = (name || 'U').charAt(0).toUpperCase();

        const badge = document.getElementById('tag_modal_badge');
        const title = document.getElementById('tag_modal_title');
        const subtitle = document.getElementById('tag_modal_subtitle');
        const submitText = document.getElementById('tag_modal_submit_text');
        const untagWrapper = document.getElementById('tag_modal_untag_wrapper');

        if (isAlreadyAssistant) {
            badge.textContent = 'Admin Assistant';
            badge.className = 'px-2.5 py-1 rounded-full text-[10px] font-extrabold shrink-0 uppercase tracking-wider bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30';
            title.textContent = 'Manage Admin Assistant Access';
            subtitle.textContent = `Update assigned module permissions for staff assistant ${name}.`;
            submitText.textContent = 'Update Permissions';
            untagWrapper.classList.remove('hidden');
        } else {
            badge.textContent = 'Player / Client';
            badge.className = 'px-2.5 py-1 rounded-full text-[10px] font-extrabold shrink-0 uppercase tracking-wider bg-stone-200 dark:bg-stone-800 text-theme-body border border-stone-300 dark:border-stone-700';
            title.textContent = 'Tag User as Admin Assistant';
            subtitle.textContent = `Promote registered user ${name} to an Admin Assistant staff member.`;
            submitText.textContent = 'Tag as Admin Assistant';
            untagWrapper.classList.add('hidden');
        }

        const supervisorSelect = document.getElementById('tag_modal_supervisor');
        if (supervisorSelect && courtOwnerId) {
            supervisorSelect.value = courtOwnerId;
        }

        // Set module checkboxes
        const defaultMods = ['schedule', 'approvals'];
        availableUserModules.forEach(mod => {
            const cb = document.getElementById(`tag_modal_module_${mod}`);
            if (cb) {
                if (isAlreadyAssistant) {
                    cb.checked = Array.isArray(permissions) && permissions.includes(mod);
                } else {
                    // Default permissions for new promotion
                    cb.checked = defaultMods.includes(mod);
                }
            }
        });
        syncAllModuleCards('tagAssistantModal');

        document.getElementById('tagAssistantModal').classList.remove('hidden');
    }

    function closeTagAssistantModal() {
        document.getElementById('tagAssistantModal').classList.add('hidden');
    }

    function confirmRemoveAssistantAccess(userId, name) {
        Swal.fire({
            title: 'Remove Admin Assistant Access?',
            html: `Are you sure you want to remove admin assistant privileges for <strong>${name}</strong>?<br><span class="text-xs text-stone-500">This will revoke their staff access and revert their account back to a regular Player/Client.</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#78716c',
            confirmButtonText: 'Yes, Remove Access',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const untagForm = document.createElement('form');
                untagForm.method = 'POST';
                untagForm.action = "{{ url('owner/users') }}/" + userId + "/untag-assistant";

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = "{{ csrf_token() }}";
                untagForm.appendChild(csrf);

                document.body.appendChild(untagForm);
                untagForm.submit();
            }
        });
    }

    function executeUntagAssistant() {
        if (!currentTagUserId) return;
        confirmRemoveAssistantAccess(currentTagUserId, currentTagUserName);
    }

    function openAddAssistantModal() {
        document.getElementById('addAssistantModal').classList.remove('hidden');
        switchAddAssistantTab('tag');
        syncAllModuleCards('addAssistantModal');
    }

    function closeAddAssistantModal() {
        document.getElementById('addAssistantModal').classList.add('hidden');
    }

    function switchAddAssistantTab(tab) {
        const paneTag = document.getElementById('pane_assistant_tag');
        const paneNew = document.getElementById('pane_assistant_new');
        const btnTag = document.getElementById('btn_tab_assistant_tag');
        const btnNew = document.getElementById('btn_tab_assistant_new');

        if (tab === 'tag') {
            paneTag.classList.remove('hidden');
            paneNew.classList.add('hidden');
            btnTag.className = 'flex-1 py-2 px-3 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center justify-center gap-1.5 bg-emerald-500 text-slate-950 shadow-sm shadow-emerald-500/20';
            btnNew.className = 'flex-1 py-2 px-3 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center justify-center gap-1.5 text-theme-muted hover:text-theme-heading';
        } else {
            paneTag.classList.add('hidden');
            paneNew.classList.remove('hidden');
            btnNew.className = 'flex-1 py-2 px-3 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center justify-center gap-1.5 bg-emerald-500 text-slate-950 shadow-sm shadow-emerald-500/20';
            btnTag.className = 'flex-1 py-2 px-3 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center justify-center gap-1.5 text-theme-muted hover:text-theme-heading';
        }
        syncAllModuleCards('addAssistantModal');
    }

    function updateTagUserSelection(selectEl) {
        const selected = selectEl.options[selectEl.selectedIndex];
        const previewBox = document.getElementById('tag_user_preview_box');
        if (!selected || !selected.value) {
            previewBox.classList.add('hidden');
            previewBox.classList.remove('flex');
            return;
        }

        const name = selected.dataset.name || 'User';
        const email = selected.dataset.email || '';
        const phone = selected.dataset.phone || 'No phone recorded';

        document.getElementById('tag_user_preview_name').textContent = name;
        document.getElementById('tag_user_preview_email').textContent = email;
        document.getElementById('tag_user_preview_phone').textContent = phone;
        document.getElementById('tag_user_preview_avatar').textContent = name.charAt(0).toUpperCase();

        previewBox.classList.remove('hidden');
        previewBox.classList.add('flex');
    }

    // Close modals on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeAddUserModal();
            closeEditUserModal();
            closeHeldSlotsModal();
            closeDeactivateModal();
            closeCreditAdjustmentModal();
            closeTagAssistantModal();
            closeAddAssistantModal();
        }
    });
</script>
@endpush
