@extends('layouts.owner')

@section('content')
<div class="space-y-6">
    <!-- Top Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-stone-200 dark:border-stone-800 gap-4">
        <div>
            <h2 class="text-xl font-bold text-theme-heading">Admin Assistants Management</h2>
            <p class="text-xs text-theme-muted">Add staff members and configure which operational modules they can access (default: Schedule Viewing & Reservation Approvals).</p>
        </div>
        <button type="button" onclick="openAddAssistantModal()"
            class="px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-black text-xs shadow-lg shadow-cyan-500/20 flex items-center gap-2 transition-all cursor-pointer">
            <i class="fa-solid fa-user-plus"></i> Add Admin Assistant
        </button>
    </div>

    <!-- Permissions Policy Notice Banner -->
    <div class="p-4 rounded-2xl bg-cyan-500/10 border border-cyan-500/30 flex items-start gap-3.5 text-xs text-theme-body">
        <div class="w-8 h-8 rounded-xl bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 flex items-center justify-center shrink-0 text-sm">
            <i class="fa-solid fa-user-shield"></i>
        </div>
        <div class="space-y-1">
            <h4 class="font-bold text-theme-heading">Granular Module Authorization</h4>
            <p class="leading-relaxed">
                As the court owner, you decide which modules each admin assistant can access. By default, assistants are granted access to <strong>Viewing of Schedule</strong> and <strong>Approval of Reservation</strong>. Unassigned modules are automatically locked and hidden from their sidebar navigation.
            </p>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl glass-panel border border-stone-200 dark:border-stone-800 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-lg">
                <i class="fa-solid fa-users-gear"></i>
            </div>
            <div>
                <p class="text-xs text-theme-muted font-medium">Total Assistants</p>
                <h3 class="text-xl font-extrabold text-theme-heading">{{ $assistants->count() }}</h3>
            </div>
        </div>

        <div class="p-4 rounded-2xl glass-panel border border-stone-200 dark:border-stone-800 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                <i class="fa-solid fa-user-check"></i>
            </div>
            <div>
                <p class="text-xs text-theme-muted font-medium">Active Staff</p>
                <h3 class="text-xl font-extrabold text-theme-heading">{{ $assistants->where('is_active', true)->count() }}</h3>
            </div>
        </div>

        <div class="p-4 rounded-2xl glass-panel border border-stone-200 dark:border-stone-800 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg">
                <i class="fa-solid fa-key"></i>
            </div>
            <div>
                <p class="text-xs text-theme-muted font-medium">Default Permissions</p>
                <h3 class="text-xs font-bold text-theme-heading mt-0.5">Schedule & Approvals</h3>
            </div>
        </div>
    </div>

    <!-- Assistants List -->
    <div class="glass-panel border border-stone-200 dark:border-stone-800 rounded-3xl overflow-hidden shadow-xl">
        <div class="p-5 border-b border-stone-200 dark:border-stone-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <h3 class="font-bold text-sm text-theme-heading">Registered Admin Assistants</h3>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-stone-200 dark:bg-stone-800 text-theme-muted">
                    {{ $assistants->count() }} staff members
                </span>
            </div>
        </div>

        @if($assistants->isEmpty())
            <div class="p-12 text-center space-y-3">
                <div class="w-16 h-16 rounded-2xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 flex items-center justify-center mx-auto text-2xl">
                    <i class="fa-solid fa-user-clock"></i>
                </div>
                <h4 class="text-base font-bold text-theme-heading">No Admin Assistants Added Yet</h4>
                <p class="text-xs text-theme-muted max-w-sm mx-auto">
                    Click the button below to add your first assistant and assign which modules they can manage.
                </p>
                <button type="button" onclick="openAddAssistantModal()"
                    class="px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs inline-flex items-center gap-2 transition-all cursor-pointer">
                    <i class="fa-solid fa-plus"></i> Add Admin Assistant
                </button>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-theme-body">
                    <thead class="bg-stone-100 dark:bg-stone-900/50 text-theme-muted uppercase tracking-wider text-[11px] border-b border-stone-200 dark:border-stone-800">
                        <tr>
                            <th class="py-3.5 px-6">Assistant Name & Email</th>
                            <th class="py-3.5 px-6">Phone</th>
                            <th class="py-3.5 px-6">Assigned Module Access</th>
                            <th class="py-3.5 px-6 text-center">Status</th>
                            <th class="py-3.5 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-200 dark:divide-stone-800">
                        @foreach($assistants as $assistant)
                            @php
                                $perms = $assistant->permissions ?? [];
                                if (!is_array($perms)) {
                                    $perms = json_decode($perms, true) ?? [];
                                }
                            @endphp
                            <tr class="hover:bg-stone-50 dark:hover:bg-stone-800/30 transition-colors">
                                <!-- Name and Email -->
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-cyan-500/20 border border-cyan-500/40 text-cyan-600 dark:text-cyan-400 flex items-center justify-center font-bold text-sm shrink-0">
                                            {{ strtoupper(substr($assistant->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-theme-heading text-sm">{{ $assistant->name }}</div>
                                            <div class="text-[11px] text-theme-muted flex items-center gap-1.5 mt-0.5">
                                                <i class="fa-regular fa-envelope text-[10px]"></i>
                                                <span>{{ $assistant->email }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Phone -->
                                <td class="py-4 px-6 text-theme-body">
                                    {{ $assistant->phone ?: 'None' }}
                                </td>

                                <!-- Module Permissions Badges -->
                                <td class="py-4 px-6">
                                    <div class="flex flex-wrap gap-1.5 max-w-md">
                                        @if(in_array('schedule', $perms))
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-cyan-500/10 text-cyan-700 dark:text-cyan-300 border border-cyan-500/30">
                                                <i class="fa-solid fa-calendar-days text-[10px]"></i>
                                                Viewing of Schedule
                                            </span>
                                        @endif

                                        @if(in_array('approvals', $perms))
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30">
                                                <i class="fa-solid fa-file-invoice-dollar text-[10px]"></i>
                                                Approval of Reservation
                                            </span>
                                        @endif

                                        @if(in_array('courts', $perms))
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border border-indigo-500/30">
                                                <i class="fa-solid fa-table-tennis-paddle-ball text-[10px]"></i>
                                                Courts & Pricing
                                            </span>
                                        @endif

                                        @if(in_array('photos', $perms))
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-purple-500/10 text-purple-700 dark:text-purple-300 border border-purple-500/30">
                                                <i class="fa-solid fa-images text-[10px]"></i>
                                                Website Photos
                                            </span>
                                        @endif

                                        @if(in_array('users', $perms))
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-cyan-500/10 text-cyan-700 dark:text-cyan-300 border border-cyan-500/30">
                                                <i class="fa-solid fa-users text-[10px]"></i>
                                                User Management
                                            </span>
                                        @endif

                                        @if(in_array('settings', $perms))
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-amber-500/10 text-amber-700 dark:text-amber-300 border border-amber-500/30">
                                                <i class="fa-solid fa-sliders text-[10px]"></i>
                                                Center Config
                                            </span>
                                        @endif

                                        @if(empty($perms))
                                            <span class="px-2 py-0.5 rounded text-[10px] bg-stone-200 dark:bg-stone-800 text-stone-500">
                                                No modules assigned
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="py-4 px-6 text-center">
                                    <form method="POST" action="{{ route('owner.assistants.toggle', $assistant->id) }}" class="inline-block">
                                        @csrf
                                        <button type="submit" title="Click to toggle status" class="cursor-pointer group">
                                            @if($assistant->is_active)
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
                                </td>

                                <!-- Actions -->
                                <td class="py-4 px-6 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button"
                                            onclick='openEditAssistantModal(@json($assistant), @json($perms))'
                                            class="p-2 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-cyan-500/10 hover:text-cyan-600 dark:hover:text-cyan-400 text-theme-muted transition-colors cursor-pointer"
                                            title="Edit Assistant & Permissions">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>

                                        <form method="POST" action="{{ route('owner.assistants.destroy', $assistant->id) }}"
                                            id="delete-form-{{ $assistant->id }}" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="confirmDeleteAssistant({{ $assistant->id }}, '{{ addslashes($assistant->name) }}')"
                                                class="p-2 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-rose-500/10 hover:text-rose-500 text-theme-muted transition-colors cursor-pointer"
                                                title="Delete Assistant">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- ========================================== -->
<!-- ADD ADMIN ASSISTANT MODAL                  -->
<!-- ========================================== -->
<div id="addAssistantModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm hidden transition-all">
    <div class="w-full max-w-xl max-h-[90vh] overflow-y-auto rounded-3xl glass-dropdown border border-stone-200 dark:border-stone-800 p-6 md:p-8 shadow-2xl relative">
        <button type="button" onclick="closeAddAssistantModal()" class="absolute top-5 right-5 w-8 h-8 rounded-full bg-stone-200 dark:bg-stone-800 flex items-center justify-center text-theme-muted hover:text-theme-heading cursor-pointer">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-2xl bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-lg">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-theme-heading">Add New Admin Assistant</h3>
                <p class="text-xs text-theme-muted">Create staff login credentials and select authorized modules.</p>
            </div>
        </div>

        <form action="{{ route('owner.assistants.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Assistant Basic Details -->
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-theme-heading mb-1.5">Full Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Maria Santos"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-theme-heading mb-1.5">Email Address *</label>
                        <input type="email" name="email" required placeholder="assistant@paddlefield.com"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-theme-heading mb-1.5">Phone Number</label>
                        <input type="text" name="phone" placeholder="0919 123 4567"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-theme-heading mb-1.5">Password * (Min 6 chars)</label>
                        <input type="password" name="password" required minlength="6" placeholder="••••••••"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-theme-heading mb-1.5">Confirm Password *</label>
                        <input type="password" name="password_confirmation" required minlength="6" placeholder="••••••••"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Granular Module Permissions Selection -->
            <div class="pt-3 border-t border-stone-200 dark:border-stone-800">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h4 class="text-xs font-extrabold text-theme-heading uppercase tracking-wider">Module Permissions</h4>
                        <p class="text-[11px] text-theme-muted">Select which modules this admin assistant is authorized to access.</p>
                    </div>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-cyan-500/10 text-cyan-600 border border-cyan-500/30">
                        Default: Schedule & Approvals
                    </span>
                </div>

                <div class="space-y-2.5">
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

            <!-- Active Checkbox -->
            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" id="add_is_active" value="1" checked
                    class="rounded text-cyan-500 focus:ring-cyan-500 focus:ring-offset-0 w-4 h-4 cursor-pointer">
                <label for="add_is_active" class="text-xs font-semibold text-theme-heading cursor-pointer">
                    Account is active immediately upon creation
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-stone-200 dark:border-stone-800">
                <button type="button" onclick="closeAddAssistantModal()"
                    class="px-4 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 text-xs font-semibold text-theme-muted hover:text-theme-heading cursor-pointer">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 text-xs font-bold shadow-md shadow-cyan-500/20 cursor-pointer transition-all">
                    Create Admin Assistant
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- EDIT ADMIN ASSISTANT MODAL                 -->
<!-- ========================================== -->
<div id="editAssistantModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm hidden transition-all">
    <div class="w-full max-w-xl max-h-[90vh] overflow-y-auto rounded-3xl glass-dropdown border border-stone-200 dark:border-stone-800 p-6 md:p-8 shadow-2xl relative">
        <button type="button" onclick="closeEditAssistantModal()" class="absolute top-5 right-5 w-8 h-8 rounded-full bg-stone-200 dark:bg-stone-800 flex items-center justify-center text-theme-muted hover:text-theme-heading cursor-pointer">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-2xl bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-lg">
                <i class="fa-solid fa-user-pen"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-theme-heading">Edit Admin Assistant</h3>
                <p class="text-xs text-theme-muted">Update details and configure accessible operational modules.</p>
            </div>
        </div>

        <form id="editAssistantForm" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Assistant Basic Details -->
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-theme-heading mb-1.5">Full Name *</label>
                    <input type="text" id="edit_name" name="name" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-theme-heading mb-1.5">Email Address *</label>
                        <input type="email" id="edit_email" name="email" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-theme-heading mb-1.5">Phone Number</label>
                        <input type="text" id="edit_phone" name="phone"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-theme-heading mb-1.5">New Password (Leave blank to keep)</label>
                        <input type="password" name="password" minlength="6" placeholder="••••••••"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-theme-heading mb-1.5">Confirm New Password</label>
                        <input type="password" name="password_confirmation" minlength="6" placeholder="••••••••"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 text-xs text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Module Permissions Selection -->
            <div class="pt-3 border-t border-stone-200 dark:border-stone-800">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h4 class="text-xs font-extrabold text-theme-heading uppercase tracking-wider">Module Permissions</h4>
                        <p class="text-[11px] text-theme-muted">Configure modules this assistant is allowed to view and manage.</p>
                    </div>
                </div>

                <div class="space-y-2.5">
                    @foreach($availableModules as $key => $mod)
                        <label class="flex items-start gap-3 p-3 rounded-2xl border border-stone-200 dark:border-stone-800 hover:border-cyan-500/40 bg-stone-100/70 dark:bg-stone-900/40 cursor-pointer transition-all">
                            <input type="checkbox" name="modules[]" value="{{ $key }}" id="edit_module_{{ $key }}"
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

            <!-- Active Checkbox -->
            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" id="edit_is_active" value="1"
                    class="rounded text-cyan-500 focus:ring-cyan-500 focus:ring-offset-0 w-4 h-4 cursor-pointer">
                <label for="edit_is_active" class="text-xs font-semibold text-theme-heading cursor-pointer">
                    Account is active and able to sign in
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-stone-200 dark:border-stone-800">
                <button type="button" onclick="closeEditAssistantModal()"
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
    function openAddAssistantModal() {
        document.getElementById('addAssistantModal').classList.remove('hidden');
    }

    function closeAddAssistantModal() {
        document.getElementById('addAssistantModal').classList.add('hidden');
    }

    function openEditAssistantModal(assistant, permissions) {
        document.getElementById('editAssistantForm').action = "{{ url('owner/assistants') }}/" + assistant.id;
        document.getElementById('edit_name').value = assistant.name || '';
        document.getElementById('edit_email').value = assistant.email || '';
        document.getElementById('edit_phone').value = assistant.phone || '';
        document.getElementById('edit_is_active').checked = !!assistant.is_active;

        const availableModules = ['schedule', 'approvals', 'courts', 'photos', 'users', 'settings'];
        availableModules.forEach(mod => {
            const cb = document.getElementById(`edit_module_${mod}`);
            if (cb) {
                cb.checked = Array.isArray(permissions) && permissions.includes(mod);
            }
        });

        document.getElementById('editAssistantModal').classList.remove('hidden');
    }

    function closeEditAssistantModal() {
        document.getElementById('editAssistantModal').classList.add('hidden');
    }

    function confirmDeleteAssistant(id, name) {
        Swal.fire({
            title: 'Remove Admin Assistant?',
            text: `Are you sure you want to remove ${name}? They will immediately lose access to the portal.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Remove Assistant',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(`delete-form-${id}`).submit();
            }
        });
    }

    // Close modals on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeAddAssistantModal();
            closeEditAssistantModal();
        }
    });
</script>
@endpush
