@extends('layouts.owner')

@section('content')
<div class="space-y-6">
    <!-- Top Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-stone-200 dark:border-stone-800 gap-4">
        <div>
            <h2 class="text-xl font-bold text-theme-heading">Courts & Pricing Management</h2>
            <p class="text-xs text-theme-muted">Configure Court 1, Court 2, Court 3, set hourly rates, and manage player limits.</p>
        </div>
        <button type="button" onclick="openAddCourtModal()"
            class="px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-black text-xs shadow-lg shadow-cyan-500/20 flex items-center gap-2 transition-all cursor-pointer">
            <i class="fa-solid fa-plus"></i> Add New Court
        </button>
    </div>

    <!-- Pricing Policy Notice (Req #7) -->
    <div class="p-4 rounded-2xl bg-cyan-500/10 border border-cyan-500/30 flex items-start gap-3.5 text-xs text-theme-body">
        <div class="w-8 h-8 rounded-xl bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 flex items-center justify-center shrink-0 text-sm">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div class="space-y-1">
            <h4 class="font-bold text-theme-heading">Snapshot Pricing Guarantee</h4>
            <p class="leading-relaxed">
                When you edit a court's hourly price or player limit, the new rate applies <strong>strictly to new bookings made after your change</strong>. All existing and past reservations will preserve their original booked rates.
            </p>
        </div>
    </div>

    <!-- Courts Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($courts as $court)
            <div class="rounded-3xl glass-panel border border-stone-200 dark:border-stone-800 overflow-hidden shadow-xl flex flex-col justify-between group hover:border-cyan-500/40 transition-all">
                <div>
                    <div class="relative h-48 overflow-hidden bg-stone-900">
                        <img src="{{ $court->display_image }}" alt="{{ $court->name }}"
                            onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1554068865-24cecd4e34b8?auto=format&fit=crop&w=1000&q=80';"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-[10px] font-extrabold {{ $court->type === 'indoor' ? 'bg-cyan-500 text-slate-950' : 'bg-amber-600 text-white' }} uppercase">
                            {{ ucfirst($court->type) }}
                        </div>
                        <div class="absolute top-3 right-3 px-2.5 py-1 rounded-xl glass-dropdown text-xs font-bold text-theme-heading border border-stone-200 dark:border-stone-800">
                            Court #{{ $court->court_number }}
                        </div>

                        @if($court->trashed())
                            <div class="absolute inset-0 bg-slate-950/75 flex items-center justify-center">
                                <span class="px-3.5 py-1.5 rounded-xl bg-amber-500 text-slate-950 font-black text-xs shadow-lg uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="fa-solid fa-box-archive"></i> Archived / Hidden
                                </span>
                            </div>
                        @elseif(!$court->is_active)
                            <div class="absolute inset-0 bg-slate-950/80 flex items-center justify-center">
                                <span class="px-3 py-1 rounded-full bg-rose-500 text-white font-bold text-xs">Inactive</span>
                            </div>
                        @endif
                    </div>

                    <!-- Court Body Details -->
                    <div class="p-5 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-theme-heading">{{ $court->name }}</h3>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-stone-200 dark:bg-stone-800 text-theme-muted border border-stone-300 dark:border-stone-700">
                                {{ $court->total_bookings }} Bookings
                            </span>
                        </div>
                        <p class="text-xs text-theme-muted line-clamp-2">{{ $court->description }}</p>

                        <!-- Key Specs -->
                        <div class="p-3.5 rounded-2xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 space-y-2 text-xs">
                            <div class="flex justify-between items-center">
                                <span class="text-theme-muted">Rate per Hour:</span>
                                <span class="font-black text-cyan-600 dark:text-cyan-400 text-base">{{ $court->formatted_price }}<span class="text-xs text-theme-muted font-normal">/hr</span></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-theme-muted">Available Hours:</span>
                                <span class="font-bold text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                                    <i class="fa-regular fa-clock text-[10px]"></i>
                                    {{ $court->operating_hours_label }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-theme-muted">Max Players:</span>
                                <span class="font-bold text-theme-heading">{{ $court->max_players }} Players</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-theme-muted">Surface:</span>
                                <span class="font-medium text-theme-body">{{ $court->surface_type }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="p-5 pt-0 flex items-center justify-between gap-2">
                    @if($court->trashed())
                        <form method="POST" action="{{ route('owner.courts.restore', $court->id) }}" class="w-full">
                            @csrf
                            <button type="submit" class="w-full py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs flex items-center justify-center gap-1.5 shadow-md shadow-amber-500/20 transition-all cursor-pointer">
                                <i class="fa-solid fa-rotate-left"></i> Restore / Un-hide Court
                            </button>
                        </form>
                    @else
                        <button type="button"
                            onclick="openEditCourtModal({{ json_encode($court) }})"
                            class="flex-1 py-2.5 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-stone-300 dark:hover:bg-stone-700 text-xs font-bold text-cyan-700 dark:text-cyan-400 border border-stone-300 dark:border-stone-700 flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                            <i class="fa-solid fa-pen-to-square"></i> Edit Pricing & Info
                        </button>

                        <form id="archive-court-form-{{ $court->id }}" method="POST" action="{{ route('owner.courts.destroy', $court->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" onclick="confirmArchiveCourt({{ $court->id }}, '{{ addslashes($court->name) }}')" title="Archive / Hide Court" class="p-2.5 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-rose-500/20 text-stone-500 hover:text-rose-500 border border-stone-300 dark:border-stone-700 transition-colors cursor-pointer">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- ADD COURT MODAL -->
<div id="addCourtModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md opacity-0 pointer-events-none transition-all duration-300">
    <div class="glass-dropdown p-6 sm:p-8 rounded-3xl max-w-lg w-full border border-stone-300 dark:border-stone-700 shadow-2xl relative space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-start justify-between pb-3 border-b border-stone-200 dark:border-stone-800 gap-3">
            <div class="min-w-0 pr-2">
                <h3 class="text-lg font-bold text-theme-heading">Add New Pickleball Court</h3>
                <p class="text-xs text-theme-muted">Add Court 1, Court 2, Court 3... and configure booking pricing.</p>
            </div>
            <button type="button" onclick="closeAddCourtModal()" class="shrink-0 text-theme-muted hover:text-theme-heading p-2 rounded-xl hover:bg-stone-200/50 dark:hover:bg-stone-800/50 transition-colors cursor-pointer -mt-1 -mr-1" title="Close modal">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form action="{{ route('owner.courts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Court Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Court 5 - South Center"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500">
                </div>
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Court Number <span class="text-rose-500">*</span></label>
                    <input type="number" name="court_number" min="1" required placeholder="e.g. 5"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Type <span class="text-rose-500">*</span></label>
                    <select name="type" required class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500">
                        <option value="indoor">Indoor</option>
                        <option value="outdoor">Outdoor</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Surface Type <span class="text-rose-500">*</span></label>
                    <input type="text" name="surface_type" required value="Pro Cushion Acrylic"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500">
                </div>
            </div>

            <!-- PRICING AND CAPACITY (Req #7 - Crunch Accent) -->
            <div class="grid grid-cols-2 gap-3 p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/30">
                <div>
                    <label class="block font-bold text-amber-800 dark:text-amber-400 mb-1">Hourly Rate (₱) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0" name="price_per_hour" required value="150.00" placeholder="150.00"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-bold focus:outline-none focus:border-cyan-500">
                    <span class="text-[10px] text-theme-muted">Charge per 1 hour slot</span>
                </div>
                <div>
                    <label class="block font-bold text-amber-800 dark:text-amber-400 mb-1">Max Players <span class="text-rose-500">*</span></label>
                    <input type="number" min="1" max="20" name="max_players" required value="4"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-bold focus:outline-none focus:border-cyan-500">
                    <span class="text-[10px] text-theme-muted">Players allowed per court</span>
                </div>
            </div>

            <!-- AVAILABLE TIME SLOTS / OPERATING HOURS (Req #1) -->
            <div class="p-3.5 rounded-2xl bg-cyan-500/10 border border-cyan-500/30 space-y-2.5">
                <div class="flex items-center justify-between">
                    <label class="block font-bold text-cyan-800 dark:text-cyan-400 text-xs">
                        <i class="fa-regular fa-clock"></i> Available Operating Time Slots <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-[10px] text-theme-muted font-medium">Daily bookable range</span>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-theme-body mb-1">Opens At (Start Time)</label>
                        <select name="opening_time" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading text-xs focus:outline-none focus:border-cyan-500">
                            @for($i = 0; $i < 24; $i++)
                                @php $val = sprintf('%02d:00', $i); $lbl = \Carbon\Carbon::createFromFormat('H:i', $val)->format('g:i A'); @endphp
                                <option value="{{ $val }}" {{ $val === '06:00' ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-theme-body mb-1">Closes At (End Time)</label>
                        <select name="closing_time" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading text-xs focus:outline-none focus:border-cyan-500">
                            @for($i = 1; $i <= 24; $i++)
                                @php 
                                    $val = ($i === 24) ? '00:00' : sprintf('%02d:00', $i); 
                                    $lbl = ($i === 24) ? '12:00 AM Midnight' : \Carbon\Carbon::createFromFormat('H:i', $val)->format('g:i A');
                                @endphp
                                <option value="{{ $val }}" {{ ($val === '00:00') ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <p class="text-[10px] text-theme-muted">
                    Example: Select <strong>6:00 AM to 5:00 PM</strong> for daytime hours, or <strong>6:00 AM to 12:00 AM Midnight</strong> for all-day.
                </p>
            </div>

            <div>
                <label class="block font-semibold text-theme-body mb-1">Court Description</label>
                <textarea name="description" rows="2" placeholder="e.g. Cushioned acrylic court with tournament markings and dedicated rest bench."
                    class="w-full px-3.5 py-2 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500"></textarea>
            </div>

            <!-- Court Photo Upload & URL -->
            <div class="space-y-2 p-3.5 rounded-2xl bg-stone-100/80 dark:bg-stone-900/80 border border-stone-200 dark:border-stone-800">
                <label class="block font-semibold text-theme-heading mb-1">Court Photo</label>
                <input type="file" name="image" id="addCourtPhotoInput" accept="image/*" onchange="previewAddCourtImage(event)"
                    class="w-full text-xs text-theme-muted file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-cyan-500 file:text-slate-950 hover:file:bg-cyan-400 cursor-pointer">
                
                <div id="addCourtPreviewContainer" class="hidden relative mt-2">
                    <img id="addCourtPreviewImg" src="" alt="Preview" class="h-32 w-full object-cover rounded-xl border border-stone-200 dark:border-stone-700">
                    <span id="addCourtSizeBadge" class="absolute bottom-2 right-2 px-2 py-0.5 rounded-md bg-slate-950/80 text-[10px] text-cyan-400 font-bold"></span>
                </div>

                <div class="pt-1">
                    <label class="block text-[11px] text-theme-muted mb-0.5">Or provide direct image URL:</label>
                    <input type="url" name="image_url" id="addCourtImageUrl" placeholder="https://images.unsplash.com/..." oninput="previewAddCourtUrl(this.value)"
                        class="w-full px-3 py-2 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500 text-xs">
                </div>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="is_active" id="addCourtActive" value="1" checked class="w-4 h-4 rounded text-cyan-500 bg-stone-100 dark:bg-stone-900 border-stone-300 dark:border-stone-700">
                <label for="addCourtActive" class="text-xs text-theme-body font-medium">Activate Court for Public Reservations</label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-stone-200 dark:border-stone-800">
                <button type="button" onclick="closeAddCourtModal()" class="px-4 py-2.5 rounded-xl bg-stone-200 dark:bg-stone-800 text-xs text-theme-body hover:bg-stone-300 dark:hover:bg-stone-700 cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-black text-xs shadow-lg shadow-cyan-500/20 cursor-pointer">
                    Create Court
                </button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT COURT MODAL -->
<div id="editCourtModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md opacity-0 pointer-events-none transition-all duration-300" onclick="if(event.target === this) closeEditCourtModal()">
    <div class="glass-dropdown p-6 sm:p-8 rounded-3xl max-w-lg w-full border border-stone-300 dark:border-stone-700 shadow-2xl relative space-y-4 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="flex items-start justify-between pb-3 border-b border-stone-200 dark:border-stone-800 gap-3">
            <div class="min-w-0 pr-2">
                <h3 class="text-lg font-bold text-theme-heading">Edit Court Details & Pricing</h3>
                <p class="text-xs text-theme-muted">Updates to pricing/capacity apply only to future bookings.</p>
            </div>
            <button type="button" onclick="closeEditCourtModal()" class="shrink-0 text-theme-muted hover:text-theme-heading p-2 rounded-xl hover:bg-stone-200/50 dark:hover:bg-stone-800/50 transition-colors cursor-pointer -mt-1 -mr-1" title="Close modal">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="editCourtForm" action="" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Court Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" id="editCourtName" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500">
                </div>
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Court Number <span class="text-rose-500">*</span></label>
                    <input type="number" name="court_number" id="editCourtNumber" min="1" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Type <span class="text-rose-500">*</span></label>
                    <select name="type" id="editCourtType" required class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500">
                        <option value="indoor">Indoor</option>
                        <option value="outdoor">Outdoor</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Surface Type <span class="text-rose-500">*</span></label>
                    <input type="text" name="surface_type" id="editCourtSurface" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500">
                </div>
            </div>

            <!-- PRICING & MAX PLAYERS EDIT (Req #7) -->
            <div class="grid grid-cols-2 gap-3 p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/30">
                <div>
                    <label class="block font-bold text-amber-800 dark:text-amber-400 mb-1">Hourly Rate (₱) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0" name="price_per_hour" id="editCourtPrice" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-bold focus:outline-none focus:border-cyan-500">
                    <span class="text-[10px] text-theme-muted">Applies to new bookings</span>
                </div>
                <div>
                    <label class="block font-bold text-amber-800 dark:text-amber-400 mb-1">Max Players <span class="text-rose-500">*</span></label>
                    <input type="number" min="1" max="20" name="max_players" id="editCourtMaxPlayers" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-bold focus:outline-none focus:border-cyan-500">
                    <span class="text-[10px] text-theme-muted">Players per booking</span>
                </div>
            </div>

            <!-- AVAILABLE TIME SLOTS / OPERATING HOURS (Req #1) -->
            <div class="p-3.5 rounded-2xl bg-cyan-500/10 border border-cyan-500/30 space-y-2.5">
                <div class="flex items-center justify-between">
                    <label class="block font-bold text-cyan-800 dark:text-cyan-400 text-xs">
                        <i class="fa-regular fa-clock"></i> Available Operating Time Slots <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-[10px] text-theme-muted font-medium">Daily bookable range</span>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-theme-body mb-1">Opens At (Start Time)</label>
                        <select name="opening_time" id="editCourtOpeningTime" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading text-xs focus:outline-none focus:border-cyan-500">
                            @for($i = 0; $i < 24; $i++)
                                @php $val = sprintf('%02d:00', $i); $lbl = \Carbon\Carbon::createFromFormat('H:i', $val)->format('g:i A'); @endphp
                                <option value="{{ $val }}">{{ $lbl }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-theme-body mb-1">Closes At (End Time)</label>
                        <select name="closing_time" id="editCourtClosingTime" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading text-xs focus:outline-none focus:border-cyan-500">
                            @for($i = 1; $i <= 24; $i++)
                                @php 
                                    $val = ($i === 24) ? '00:00' : sprintf('%02d:00', $i); 
                                    $lbl = ($i === 24) ? '12:00 AM Midnight' : \Carbon\Carbon::createFromFormat('H:i', $val)->format('g:i A');
                                @endphp
                                <option value="{{ $val }}">{{ $lbl }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <p class="text-[10px] text-theme-muted">
                    Example: Select <strong>6:00 AM to 5:00 PM</strong> for daytime hours, or <strong>6:00 AM to 12:00 AM Midnight</strong> for all-day.
                </p>
            </div>

            <div>
                <label class="block font-semibold text-theme-body mb-1">Court Description</label>
                <textarea name="description" id="editCourtDesc" rows="2"
                    class="w-full px-3.5 py-2 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500"></textarea>
            </div>

            <!-- Current Image & Replace Photo -->
            <div class="space-y-2 p-3.5 rounded-2xl bg-stone-100/80 dark:bg-stone-900/80 border border-stone-200 dark:border-stone-800">
                <span class="block font-semibold text-theme-heading mb-1">Court Image</span>
                
                <div class="relative mb-2">
                    <img id="editCourtCurrentImg" src="" alt="Current Court Image" class="h-32 w-full object-cover rounded-xl border border-stone-200 dark:border-stone-700">
                    <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-slate-950/80 text-[10px] text-white">Current</span>
                </div>

                <div class="space-y-2">
                    <label class="block text-[11px] text-theme-muted">Upload new photo file:</label>
                    <input type="file" name="image" id="editCourtPhotoInput" accept="image/*" onchange="previewEditCourtImage(event)"
                        class="w-full text-xs text-theme-muted file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-cyan-500 file:text-slate-950 hover:file:bg-cyan-400 cursor-pointer">
                    
                    <div id="editCourtNewPreviewContainer" class="hidden relative mt-2">
                        <img id="editCourtNewPreviewImg" src="" alt="New Image Preview" class="h-32 w-full object-cover rounded-xl border border-cyan-500/50">
                        <span id="editCourtSizeBadge" class="absolute bottom-2 right-2 px-2 py-0.5 rounded-md bg-slate-950/80 text-[10px] text-cyan-400 font-bold"></span>
                    </div>

                    <div class="pt-1">
                        <label class="block text-[11px] text-theme-muted mb-0.5">Or enter image URL:</label>
                        <input type="url" name="image_url" id="editCourtImageUrl" placeholder="Or enter image URL (https://...)" oninput="previewEditCourtUrl(this.value)"
                            class="w-full px-3.5 py-2 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500 text-xs">
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="is_active" id="editCourtActive" value="1" class="w-4 h-4 rounded text-cyan-500 bg-stone-100 dark:bg-stone-900 border-stone-300 dark:border-stone-700">
                <label for="editCourtActive" class="text-xs text-theme-body font-medium">Court is Active and Bookable</label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-stone-200 dark:border-stone-800">
                <button type="button" onclick="closeEditCourtModal()" class="px-4 py-2.5 rounded-xl bg-stone-200 dark:bg-stone-800 text-xs text-theme-body hover:bg-stone-300 dark:hover:bg-stone-700 cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-black text-xs shadow-lg shadow-cyan-500/20 cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-floppy-disk"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openAddCourtModal() {
        const modal = document.getElementById('addCourtModal');
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100', 'pointer-events-auto');
    }
    function closeAddCourtModal() {
        const modal = document.getElementById('addCourtModal');
        modal.classList.add('opacity-0', 'pointer-events-none');
        modal.classList.remove('opacity-100', 'pointer-events-auto');
    }

    function previewAddCourtImage(event) {
        const file = event.target.files[0];
        if (!file) return;

        const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
        const badge = document.getElementById('addCourtSizeBadge');
        if (badge) {
            badge.textContent = `${sizeMB} MB`;
            if (file.size > 10 * 1024 * 1024) {
                Swal.fire({
                    icon: 'warning',
                    title: 'File Too Large',
                    text: `Court photo is ${sizeMB}MB. Please select an image under 10MB to avoid upload failure.`,
                    confirmButtonColor: '#0891b2'
                });
                event.target.value = '';
                return;
            }
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('addCourtPreviewImg').src = e.target.result;
            document.getElementById('addCourtPreviewContainer').classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }

    function previewAddCourtUrl(url) {
        if (url && url.trim().length > 10) {
            document.getElementById('addCourtPreviewImg').src = url.trim();
            document.getElementById('addCourtPreviewContainer').classList.remove('hidden');
        }
    }

    function openEditCourtModal(court) {
        // Correctly route to full application URL (compatible with subfolder and root domain)
        const courtBaseUrl = "{{ url('owner/courts') }}";
        document.getElementById('editCourtForm').action = `${courtBaseUrl}/${court.id}`;

        document.getElementById('editCourtName').value = court.name || '';
        document.getElementById('editCourtNumber').value = court.court_number || 1;
        document.getElementById('editCourtType').value = court.type || 'indoor';
        document.getElementById('editCourtSurface').value = court.surface_type || 'Pro Cushion Acrylic';
        document.getElementById('editCourtPrice').value = court.price_per_hour || '150.00';
        document.getElementById('editCourtMaxPlayers').value = court.max_players || 4;
        document.getElementById('editCourtDesc').value = court.description || '';
        document.getElementById('editCourtOpeningTime').value = court.opening_time || '06:00';
        document.getElementById('editCourtClosingTime').value = court.closing_time || '00:00';
        document.getElementById('editCourtActive').checked = Boolean(court.is_active);
        document.getElementById('editCourtImageUrl').value = (court.image_path && court.image_path.startsWith('http')) ? court.image_path : '';

        // Reset new photo preview
        document.getElementById('editCourtPhotoInput').value = '';
        document.getElementById('editCourtNewPreviewContainer').classList.add('hidden');

        // Set current photo
        const currentImg = document.getElementById('editCourtCurrentImg');
        let imgUrl = court.display_image;
        if (!imgUrl) {
            if (court.image_path && court.image_path.startsWith('http')) {
                imgUrl = court.image_path;
            } else if (court.image_path) {
                imgUrl = "{{ asset('storage') }}/" + court.image_path.replace(/^storage\//, '');
            } else {
                imgUrl = 'https://images.unsplash.com/photo-1554068865-24cecd4e34b8?auto=format&fit=crop&w=1000&q=80';
            }
        }
        currentImg.src = imgUrl;
        currentImg.onerror = function() {
            this.src = 'https://images.unsplash.com/photo-1554068865-24cecd4e34b8?auto=format&fit=crop&w=1000&q=80';
        };

        const modal = document.getElementById('editCourtModal');
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100', 'pointer-events-auto');
    }

    function closeEditCourtModal() {
        const modal = document.getElementById('editCourtModal');
        modal.classList.add('opacity-0', 'pointer-events-none');
        modal.classList.remove('opacity-100', 'pointer-events-auto');
    }

    function previewEditCourtImage(event) {
        const file = event.target.files[0];
        if (!file) return;

        const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
        const badge = document.getElementById('editCourtSizeBadge');
        if (badge) {
            badge.textContent = `${sizeMB} MB`;
            if (file.size > 10 * 1024 * 1024) {
                Swal.fire({
                    icon: 'warning',
                    title: 'File Too Large',
                    text: `Court photo is ${sizeMB}MB. Please select an image under 10MB to avoid upload failure.`,
                    confirmButtonColor: '#0891b2'
                });
                event.target.value = '';
                return;
            }
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('editCourtNewPreviewImg').src = e.target.result;
            document.getElementById('editCourtNewPreviewContainer').classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }

    function previewEditCourtUrl(url) {
        if (url && url.trim().length > 10) {
            document.getElementById('editCourtNewPreviewImg').src = url.trim();
            document.getElementById('editCourtNewPreviewContainer').classList.remove('hidden');
        }
    }

    // Backdrop click dismiss for modals
    document.getElementById('addCourtModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeAddCourtModal();
    });

    function confirmArchiveCourt(courtId, courtName) {
        Swal.fire({
            title: 'Archive ' + courtName + '?',
            text: 'This court will be hidden from the public schedule. Past reservations and total earnings are safely preserved.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Yes, Archive / Hide',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('archive-court-form-' + courtId).submit();
            }
        });
    }

    // Auto-reopen edit modal if validation errors exist
    @if($errors->any())
        document.addEventListener('DOMContentLoaded', () => {
            openAddCourtModal();
        });
    @endif
</script>
@endpush
