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
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
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
                <select name="status" onchange="this.form.submit()"
                    class="px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-medium focus:border-cyan-500 focus:outline-none">
                    <option value="" {{ empty($statusFilter) ? 'selected' : '' }}>All Statuses</option>
                    <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Active Only ({{ $counts['active'] }})</option>
                    <option value="inactive" {{ $statusFilter === 'inactive' ? 'selected' : '' }}>Deactivated Only ({{ $counts['inactive'] }})</option>
                </select>

                <button type="submit"
                    class="px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold transition-all cursor-pointer shrink-0">
                    Filter
                </button>

                @if($search || $statusFilter || $roleFilter)
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
                                        <div class="flex items-center gap-1.5 text-theme-muted">
                                            <i class="fa-solid fa-calendar-check text-[11px] text-cyan-600 dark:text-cyan-400"></i>
                                            <span><strong>{{ $user->bookings_count }}</strong> total bookings</span>
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

                                <!-- Status -->
                                <td class="py-4 px-6 text-center whitespace-nowrap">
                                    @if($user->id === Auth::id() || (Auth::user()->isAdminAssistant() && ($user->isAdmin() || $user->isOwner())) || (Auth::user()->isOwner() && $user->isAdmin()))
                                        <!-- Protected toggle button -->
                                        @if($user->is_active)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-500/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                Deactivated
                                            </span>
                                        @endif
                                    @else
                                        <form method="POST" action="{{ route('owner.users.toggle', $user->id) }}" class="inline-block">
                                            @csrf
                                            <button type="submit" title="Click to toggle account status" class="cursor-pointer group">
                                                @if($user->is_active)
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30 group-hover:bg-emerald-500/20 transition-all">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                        Active
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-500/30 group-hover:bg-rose-500/20 transition-all">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                        Deactivated
                                                    </span>
                                                @endif
                                            </button>
                                        </form>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="py-4 px-6 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        @php
                                            $canEdit = true;
                                            $canDelete = true;
                                            if (Auth::user()->isAdminAssistant() && ($user->isAdmin() || $user->isOwner())) {
                                                $canEdit = false;
                                                $canDelete = false;
                                            }
                                            if (Auth::user()->isOwner() && $user->isAdmin()) {
                                                $canEdit = false;
                                                $canDelete = false;
                                            }
                                            if ($user->id === Auth::id() || $user->email === 'admin@paddlefield.com') {
                                                $canDelete = false;
                                            }
                                        @endphp

                                        @if($canEdit)
                                            <button type="button"
                                                onclick='openEditUserModal(@json($user), @json($perms))'
                                                class="p-2 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-cyan-500/10 hover:text-cyan-600 dark:hover:text-cyan-400 text-theme-muted transition-colors cursor-pointer"
                                                title="Edit User">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                        @endif

                                        @if($canDelete)
                                            <form method="POST" action="{{ route('owner.users.destroy', $user->id) }}"
                                                id="delete-user-form-{{ $user->id }}" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" onclick="confirmDeleteUser({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ $roleBadge['label'] }}')"
                                                    class="p-2 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-rose-500/10 hover:text-rose-500 text-theme-muted transition-colors cursor-pointer"
                                                    title="Delete User">
                                                    <i class="fa-regular fa-trash-can"></i>
                                                </button>
                                            </form>
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
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-theme-heading mb-1.5">New Password (Blank to keep)</label>
                    <input type="password" name="password" minlength="6" placeholder="••••••••"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-theme-heading mb-1.5">Confirm New Password</label>
                    <input type="password" name="password_confirmation" minlength="6" placeholder="••••••••"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                </div>
            </div>

            <!-- Active Checkbox -->
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="is_active" id="edit_user_is_active" value="1"
                    class="rounded text-cyan-500 focus:ring-cyan-500 focus:ring-offset-0 w-4 h-4 cursor-pointer">
                <label for="edit_user_is_active" class="text-xs font-semibold text-theme-heading cursor-pointer">
                    Account is active and able to sign in
                </label>
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
        document.getElementById('edit_user_is_active').checked = !!user.is_active;

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

    function confirmDeleteUser(id, name, roleLabel) {
        Swal.fire({
            title: `Delete ${roleLabel}?`,
            text: `Are you sure you want to permanently delete "${name}"? This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Delete Account',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(`delete-user-form-${id}`).submit();
            }
        });
    }

    // Close modals on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeAddUserModal();
            closeEditUserModal();
        }
    });
</script>
@endpush
