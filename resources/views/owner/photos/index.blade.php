@extends('layouts.owner')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-stone-200 dark:border-stone-800 gap-4">
        <div>
            <h2 class="text-xl font-bold text-theme-heading">Website Main Page Photos</h2>
            <p class="text-xs text-theme-muted">Upload facility photos, court shots, and lounge pictures to showcase on the main landing page.</p>
        </div>
        <button type="button" onclick="openUploadPhotoModal()"
            class="px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-black text-xs shadow-lg shadow-cyan-500/20 flex items-center gap-2 transition-all cursor-pointer">
            <i class="fa-solid fa-cloud-arrow-up"></i> Upload New Photo
        </button>
    </div>

    <!-- Photos Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($photos as $photo)
            <div class="rounded-3xl glass-panel border border-stone-200 dark:border-stone-800 overflow-hidden shadow-xl flex flex-col justify-between group hover:border-cyan-500/40 transition-all">
                <div>
                    <div class="relative h-56 overflow-hidden bg-stone-900">
                        <img src="{{ $photo->url }}" alt="{{ $photo->title }}"
                            onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1554068865-24cecd4e34b8?auto=format&fit=crop&w=800&q=80';"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        
                        <!-- Category Badge -->
                        @php
                            $catColors = [
                                'court' => 'bg-cyan-500/90 text-slate-950 border-cyan-400',
                                'lounge' => 'bg-amber-500/90 text-slate-950 border-amber-400',
                                'amenity' => 'bg-emerald-500/90 text-slate-950 border-emerald-400',
                                'event' => 'bg-purple-500/90 text-white border-purple-400',
                                'general' => 'bg-stone-800/90 text-stone-200 border-stone-600',
                            ];
                            $catColor = $catColors[$photo->category] ?? 'bg-stone-800/90 text-stone-200 border-stone-600';
                        @endphp
                        <div class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-[10px] font-bold backdrop-blur-md border uppercase {{ $catColor }}">
                            {{ ucfirst($photo->category) }}
                        </div>

                        <!-- Featured Pill -->
                        @if($photo->is_featured)
                            <div class="absolute top-3 right-3 px-2 py-0.5 rounded-full text-[10px] font-black bg-cyan-500 text-slate-950 shadow-md">
                                <i class="fa-solid fa-star text-[9px] mr-1"></i> Featured
                            </div>
                        @else
                            <div class="absolute top-3 right-3 px-2 py-0.5 rounded-full text-[10px] font-bold bg-stone-900/80 backdrop-blur-md text-stone-400 border border-stone-700">
                                Hidden
                            </div>
                        @endif
                    </div>

                    <div class="p-5 space-y-2">
                        <h3 class="text-sm font-bold text-theme-heading">{{ $photo->title }}</h3>
                        @if($photo->caption)
                            <p class="text-xs text-theme-muted line-clamp-2">{{ $photo->caption }}</p>
                        @endif
                    </div>
                </div>

                <div class="p-5 pt-0 flex items-center justify-between text-xs border-t border-stone-200 dark:border-stone-800 mt-2">
                    <span class="text-theme-muted font-medium">Order: #{{ $photo->sort_order }}</span>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="openEditPhotoModal({{ json_encode($photo) }})" class="text-theme-muted hover:text-cyan-600 dark:hover:text-cyan-400 p-1.5 rounded-lg hover:bg-stone-200/50 dark:hover:bg-stone-800/50 transition-colors flex items-center gap-1 cursor-pointer" title="Edit Photo Details">
                            <i class="fa-solid fa-pen-to-square text-xs"></i> Edit
                        </button>
                        <form id="delete-photo-form-{{ $photo->id }}" method="POST" action="{{ route('owner.photos.destroy', $photo->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" onclick="confirmDeletePhoto({{ $photo->id }}, '{{ addslashes($photo->title) }}')" class="text-theme-muted hover:text-rose-500 p-1.5 rounded-lg hover:bg-rose-500/10 transition-colors flex items-center gap-1 cursor-pointer" title="Delete Photo">
                                <i class="fa-solid fa-trash-can text-xs"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-3 py-16 text-center text-theme-muted">
                <i class="fa-solid fa-images text-4xl mb-3 text-stone-400 dark:text-stone-600"></i>
                <h4 class="text-sm font-bold text-theme-heading">No Photos Uploaded Yet</h4>
                <p class="text-xs text-theme-muted mt-1 max-w-sm mx-auto">Upload court snapshots, lounge images, and tournament moments to showcase Paddle Field on the landing page.</p>
                <button type="button" onclick="openUploadPhotoModal()" class="mt-4 px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs inline-flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-plus"></i> Upload First Photo
                </button>
            </div>
        @endforelse
    </div>
</div>

<!-- UPLOAD PHOTO MODAL (Req #8) -->
<div id="uploadPhotoModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md opacity-0 pointer-events-none transition-all duration-300">
    <div class="glass-dropdown p-6 sm:p-8 rounded-3xl max-w-lg w-full border border-stone-300 dark:border-stone-700 shadow-2xl relative space-y-4 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="flex items-start justify-between pb-3 border-b border-stone-200 dark:border-stone-800 gap-3">
            <div class="min-w-0 pr-2">
                <h3 class="text-lg font-bold text-theme-heading">Upload Website Facility Photo</h3>
                <p class="text-xs text-theme-muted">This photo will appear in the public landing page gallery.</p>
            </div>
            <button type="button" onclick="closeUploadPhotoModal()" class="shrink-0 text-theme-muted hover:text-theme-heading p-2 rounded-xl hover:bg-stone-200/50 dark:hover:bg-stone-800/50 transition-colors cursor-pointer -mt-1 -mr-1" title="Close modal">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form action="{{ route('owner.photos.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
            @csrf

            <!-- Photo Title -->
            <div>
                <label class="block font-semibold text-theme-body mb-1">Photo Title <span class="text-rose-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. Ace Cafe Lounge & Rest Area"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500">
                @error('title')
                    <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Category & Sort Order -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Category <span class="text-rose-500">*</span></label>
                    <select name="category" required class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500">
                        <option value="court" {{ old('category') === 'court' ? 'selected' : '' }}>Court View</option>
                        <option value="lounge" {{ old('category') === 'lounge' ? 'selected' : '' }}>Lounge & Cafe</option>
                        <option value="amenity" {{ old('category') === 'amenity' ? 'selected' : '' }}>Amenity</option>
                        <option value="event" {{ old('category') === 'event' ? 'selected' : '' }}>Tournament / Event</option>
                        <option value="general" {{ old('category') === 'general' ? 'selected' : '' }}>General Facility</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Display Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500">
                </div>
            </div>

            <!-- Caption -->
            <div>
                <label class="block font-semibold text-theme-body mb-1">Caption / Description</label>
                <textarea name="caption" rows="2" placeholder="e.g. Modern airconditioned clubhouse with court viewing windows."
                    class="w-full px-3.5 py-2 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500">{{ old('caption') }}</textarea>
                @error('caption')
                    <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Upload Method Toggle (File Upload vs URL) -->
            <div class="space-y-3 p-3.5 rounded-2xl bg-stone-100/80 dark:bg-stone-900/80 border border-stone-200 dark:border-stone-800">
                <div class="flex items-center justify-between">
                    <span class="font-semibold text-theme-heading">Image Source <span class="text-rose-500">*</span></span>
                    <div class="flex gap-1 p-0.5 bg-stone-200 dark:bg-stone-800 rounded-lg text-[11px]">
                        <button type="button" id="tabUploadBtn" onclick="switchUploadTab('file')" class="px-2.5 py-1 rounded-md font-bold transition-all bg-white dark:bg-stone-700 text-cyan-600 dark:text-cyan-400 shadow-sm cursor-pointer">
                            <i class="fa-solid fa-file-arrow-up"></i> Upload File
                        </button>
                        <button type="button" id="tabUrlBtn" onclick="switchUploadTab('url')" class="px-2.5 py-1 rounded-md font-semibold text-theme-muted hover:text-theme-heading transition-all cursor-pointer">
                            <i class="fa-solid fa-link"></i> Web URL
                        </button>
                    </div>
                </div>

                <!-- 1. File Upload Section -->
                <div id="uploadFileSection" class="space-y-2">
                    <div class="border-2 border-dashed border-stone-300 dark:border-stone-700 hover:border-cyan-500/50 rounded-2xl p-4 text-center transition-colors relative cursor-pointer group bg-white/40 dark:bg-stone-950/40">
                        <input type="file" name="photo" id="photoInput" accept="image/*" onchange="previewUploadImage(event)"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                        <div id="uploadPlaceholder" class="space-y-1.5 py-2">
                            <div class="w-10 h-10 mx-auto rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-lg">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                            </div>
                            <div class="font-semibold text-theme-heading">Click or drag image file here</div>
                            <div class="text-[11px] text-theme-muted">PNG, JPG, JPEG, WEBP, GIF (Max 10MB)</div>
                        </div>
                        <div id="uploadPreviewContainer" class="hidden relative">
                            <img id="uploadPreviewImg" src="" alt="Preview" class="h-40 w-full object-cover rounded-xl border border-stone-200 dark:border-stone-700">
                            <div id="fileSizeBadge" class="absolute bottom-2 right-2 px-2 py-0.5 rounded-md bg-slate-950/80 backdrop-blur-md text-[10px] text-cyan-400 font-bold"></div>
                        </div>
                    </div>
                    @error('photo')
                        <p class="text-rose-500 text-[11px]">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 2. Web URL Section -->
                <div id="uploadUrlSection" class="hidden space-y-2">
                    <input type="url" name="image_url" id="photoUrlInput" placeholder="https://images.unsplash.com/..." oninput="previewUrlImage(this.value)"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500">
                    <p class="text-[10px] text-theme-muted">Direct image URL (e.g. Unsplash, CDN, or cloud hosted link)</p>
                    <div id="urlPreviewContainer" class="hidden">
                        <img id="urlPreviewImg" src="" alt="URL Preview" class="h-36 w-full object-cover rounded-xl border border-stone-200 dark:border-stone-700" onerror="this.parentElement.classList.add('hidden')">
                    </div>
                    @error('image_url')
                        <p class="text-rose-500 text-[11px]">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Featured Toggle -->
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="is_featured" id="photoFeatured" value="1" checked class="w-4 h-4 rounded text-cyan-500 bg-stone-100 dark:bg-stone-900 border-stone-300 dark:border-stone-700">
                <label for="photoFeatured" class="text-xs text-theme-body font-medium">Display on Public Website Landing Page</label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-stone-200 dark:border-stone-800">
                <button type="button" onclick="closeUploadPhotoModal()" class="px-4 py-2.5 rounded-xl bg-stone-200 dark:bg-stone-800 text-xs text-theme-body hover:bg-stone-300 dark:hover:bg-stone-700 cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-black text-xs shadow-lg shadow-cyan-500/20 cursor-pointer flex items-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Upload Photo
                </button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT PHOTO MODAL -->
<div id="editPhotoModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md opacity-0 pointer-events-none transition-all duration-300">
    <div class="glass-dropdown p-6 sm:p-8 rounded-3xl max-w-lg w-full border border-stone-300 dark:border-stone-700 shadow-2xl relative space-y-4 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="flex items-start justify-between pb-3 border-b border-stone-200 dark:border-stone-800 gap-3">
            <div class="min-w-0 pr-2">
                <h3 class="text-lg font-bold text-theme-heading">Edit Facility Photo</h3>
                <p class="text-xs text-theme-muted">Update title, category, order, or replace image.</p>
            </div>
            <button type="button" onclick="closeEditPhotoModal()" class="shrink-0 text-theme-muted hover:text-theme-heading p-2 rounded-xl hover:bg-stone-200/50 dark:hover:bg-stone-800/50 transition-colors cursor-pointer -mt-1 -mr-1" title="Close modal">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="editPhotoForm" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
            @csrf
            @method('PUT')

            <!-- Title -->
            <div>
                <label class="block font-semibold text-theme-body mb-1">Photo Title <span class="text-rose-500">*</span></label>
                <input type="text" name="title" id="editPhotoTitle" required
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500">
            </div>

            <!-- Category & Sort Order -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Category <span class="text-rose-500">*</span></label>
                    <select name="category" id="editPhotoCategory" required class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500">
                        <option value="court">Court View</option>
                        <option value="lounge">Lounge & Cafe</option>
                        <option value="amenity">Amenity</option>
                        <option value="event">Tournament / Event</option>
                        <option value="general">General Facility</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Display Sort Order</label>
                    <input type="number" name="sort_order" id="editPhotoSortOrder" min="0"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500">
                </div>
            </div>

            <!-- Caption -->
            <div>
                <label class="block font-semibold text-theme-body mb-1">Caption / Description</label>
                <textarea name="caption" id="editPhotoCaption" rows="2"
                    class="w-full px-3.5 py-2 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500"></textarea>
            </div>

            <!-- Current Image Preview & Replace Option -->
            <div class="space-y-2 p-3.5 rounded-2xl bg-stone-100/80 dark:bg-stone-900/80 border border-stone-200 dark:border-stone-800">
                <span class="block font-semibold text-theme-heading mb-1">Replace Image (Optional)</span>
                <div class="relative mb-2">
                    <img id="editCurrentImg" src="" alt="Current Photo" class="h-32 w-full object-cover rounded-xl border border-stone-200 dark:border-stone-700">
                    <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-slate-950/80 text-[10px] text-white">Current</span>
                </div>

                <div class="space-y-2">
                    <input type="file" name="photo" accept="image/*" onchange="previewEditImage(event)"
                        class="w-full text-xs text-theme-muted file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-cyan-500 file:text-slate-950 hover:file:bg-cyan-400 cursor-pointer">
                    <input type="url" name="image_url" id="editPhotoUrl" placeholder="Or enter new image URL..."
                        class="w-full px-3.5 py-2 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500 text-xs">
                </div>
            </div>

            <!-- Featured Toggle -->
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="is_featured" id="editPhotoFeatured" value="1" class="w-4 h-4 rounded text-cyan-500 bg-stone-100 dark:bg-stone-900 border-stone-300 dark:border-stone-700">
                <label for="editPhotoFeatured" class="text-xs text-theme-body font-medium">Display on Public Website Landing Page</label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-stone-200 dark:border-stone-800">
                <button type="button" onclick="closeEditPhotoModal()" class="px-4 py-2.5 rounded-xl bg-stone-200 dark:bg-stone-800 text-xs text-theme-body hover:bg-stone-300 dark:hover:bg-stone-700 cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-black text-xs shadow-lg shadow-cyan-500/20 cursor-pointer">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openUploadPhotoModal() {
        const modal = document.getElementById('uploadPhotoModal');
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100', 'pointer-events-auto');
    }

    function closeUploadPhotoModal() {
        const modal = document.getElementById('uploadPhotoModal');
        modal.classList.add('opacity-0', 'pointer-events-none');
        modal.classList.remove('opacity-100', 'pointer-events-auto');
    }

    // Close on backdrop click
    document.getElementById('uploadPhotoModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeUploadPhotoModal();
    });
    document.getElementById('editPhotoModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeEditPhotoModal();
    });

    function switchUploadTab(type) {
        const fileSec = document.getElementById('uploadFileSection');
        const urlSec = document.getElementById('uploadUrlSection');
        const fileBtn = document.getElementById('tabUploadBtn');
        const urlBtn = document.getElementById('tabUrlBtn');
        const photoInput = document.getElementById('photoInput');
        const photoUrlInput = document.getElementById('photoUrlInput');

        if (type === 'file') {
            fileSec.classList.remove('hidden');
            urlSec.classList.add('hidden');
            fileBtn.className = "px-2.5 py-1 rounded-md font-bold transition-all bg-white dark:bg-stone-700 text-cyan-600 dark:text-cyan-400 shadow-sm cursor-pointer";
            urlBtn.className = "px-2.5 py-1 rounded-md font-semibold text-theme-muted hover:text-theme-heading transition-all cursor-pointer";
            photoUrlInput.value = '';
        } else {
            fileSec.classList.add('hidden');
            urlSec.classList.remove('hidden');
            urlBtn.className = "px-2.5 py-1 rounded-md font-bold transition-all bg-white dark:bg-stone-700 text-cyan-600 dark:text-cyan-400 shadow-sm cursor-pointer";
            fileBtn.className = "px-2.5 py-1 rounded-md font-semibold text-theme-muted hover:text-theme-heading transition-all cursor-pointer";
            photoInput.value = '';
            document.getElementById('uploadPreviewContainer')?.classList.add('hidden');
            document.getElementById('uploadPlaceholder')?.classList.remove('hidden');
        }
    }

    function previewUploadImage(event) {
        const file = event.target.files[0];
        if (!file) return;

        // Size check: 10MB limit, warning if > 5MB
        const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
        const badge = document.getElementById('fileSizeBadge');
        if (badge) {
            badge.textContent = `${sizeMB} MB`;
            if (file.size > 10 * 1024 * 1024) {
                Swal.fire({
                    icon: 'warning',
                    title: 'File Too Large',
                    text: `This photo is ${sizeMB}MB. Please select an image under 10MB to avoid upload failure.`,
                    confirmButtonColor: '#0891b2'
                });
                event.target.value = '';
                return;
            }
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('uploadPreviewImg');
            const container = document.getElementById('uploadPreviewContainer');
            const placeholder = document.getElementById('uploadPlaceholder');
            preview.src = e.target.result;
            container.classList.remove('hidden');
            placeholder.classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }

    function previewUrlImage(url) {
        const preview = document.getElementById('urlPreviewImg');
        const container = document.getElementById('urlPreviewContainer');
        if (url && url.trim().length > 10) {
            preview.src = url.trim();
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
    }

    function openEditPhotoModal(photo) {
        const modal = document.getElementById('editPhotoModal');
        const form = document.getElementById('editPhotoForm');
        form.action = "{{ url('owner/photos') }}/" + photo.id;

        document.getElementById('editPhotoTitle').value = photo.title || '';
        document.getElementById('editPhotoCategory').value = photo.category || 'court';
        document.getElementById('editPhotoSortOrder').value = photo.sort_order || 0;
        document.getElementById('editPhotoCaption').value = photo.caption || '';
        document.getElementById('editPhotoFeatured').checked = Boolean(photo.is_featured);

        const currentImg = document.getElementById('editCurrentImg');
        const photoUrl = photo.image_path.startsWith('http') ? photo.image_path : `/storage/${photo.image_path.replace(/^storage\//, '')}`;
        currentImg.src = photoUrl;
        currentImg.onerror = function() {
            this.src = 'https://images.unsplash.com/photo-1554068865-24cecd4e34b8?auto=format&fit=crop&w=800&q=80';
        };

        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100', 'pointer-events-auto');
    }

    function closeEditPhotoModal() {
        const modal = document.getElementById('editPhotoModal');
        modal.classList.add('opacity-0', 'pointer-events-none');
        modal.classList.remove('opacity-100', 'pointer-events-auto');
    }

    function previewEditImage(event) {
        const file = event.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('editCurrentImg').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    function confirmDeletePhoto(photoId, photoTitle) {
        Swal.fire({
            title: 'Remove Photo?',
            text: `Are you sure you want to remove "${photoTitle}" from the facility gallery?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-photo-form-' + photoId).submit();
            }
        });
    }

    // Auto reopen upload modal if validation errors occurred on submit
    @if($errors->any())
        document.addEventListener('DOMContentLoaded', () => {
            openUploadPhotoModal();
        });
    @endif
</script>
@endpush
