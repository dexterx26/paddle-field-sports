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
                        <img src="{{ $photo->url }}" alt="{{ $photo->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-[10px] font-bold bg-stone-900/80 backdrop-blur-md text-cyan-400 border border-stone-700 uppercase">
                            {{ ucfirst($photo->category) }}
                        </div>
                        @if($photo->is_featured)
                            <div class="absolute top-3 right-3 px-2 py-0.5 rounded-full text-[10px] font-bold bg-cyan-500 text-slate-950">
                                Featured
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
                    <span class="text-theme-muted">Order: #{{ $photo->sort_order }}</span>
                    <form id="delete-photo-form-{{ $photo->id }}" method="POST" action="{{ route('owner.photos.destroy', $photo->id) }}">
                        @csrf
                        @method('DELETE')
                        <button type="button" onclick="confirmDeletePhoto({{ $photo->id }})" class="text-theme-muted hover:text-rose-500 p-1 transition-colors flex items-center gap-1 cursor-pointer">
                            <i class="fa-solid fa-trash-can text-xs"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-3 py-16 text-center text-theme-muted">
                <i class="fa-solid fa-images text-3xl mb-2 text-stone-400 dark:text-stone-600"></i>
                <h4 class="text-sm font-bold text-theme-heading">No Photos Uploaded Yet</h4>
                <p class="text-xs text-theme-muted mt-1">Upload photos to show off Paddle Field Sports Center to visitors.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- UPLOAD PHOTO MODAL (Req #8) -->
<div id="uploadPhotoModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md opacity-0 pointer-events-none transition-all duration-300">
    <div class="glass-dropdown p-6 sm:p-8 rounded-3xl max-w-lg w-full border border-stone-300 dark:border-stone-700 shadow-2xl relative space-y-4">
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
            <div>
                <label class="block font-semibold text-theme-body mb-1">Photo Title <span class="text-rose-500">*</span></label>
                <input type="text" name="title" required placeholder="e.g. Ace Cafe Lounge & Rest Area"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Category <span class="text-rose-500">*</span></label>
                    <select name="category" required class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500">
                        <option value="court">Court View</option>
                        <option value="lounge">Lounge & Cafe</option>
                        <option value="amenity">Amenity</option>
                        <option value="event">Tournament / Event</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-theme-body mb-1">Display Sort Order</label>
                    <input type="number" name="sort_order" value="0" min="0"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-theme-body mb-1">Caption / Description</label>
                <textarea name="caption" rows="2" placeholder="e.g. Modern airconditioned clubhouse with viewing windows."
                    class="w-full px-3.5 py-2 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading focus:outline-none focus:border-cyan-500"></textarea>
            </div>

            <div>
                <label class="block font-semibold text-theme-body mb-1">Image File <span class="text-rose-500">*</span></label>
                <input type="file" name="photo" required accept="image/*"
                    class="w-full text-xs text-theme-muted file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-cyan-500 file:text-slate-950 hover:file:bg-cyan-400 cursor-pointer">
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="is_featured" id="photoFeatured" value="1" checked class="w-4 h-4 rounded text-cyan-500 bg-stone-100 dark:bg-stone-900 border-stone-300 dark:border-stone-700">
                <label for="photoFeatured" class="text-xs text-theme-body font-medium">Display on Public Website</label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-stone-200 dark:border-stone-800">
                <button type="button" onclick="closeUploadPhotoModal()" class="px-4 py-2.5 rounded-xl bg-stone-200 dark:bg-stone-800 text-xs text-theme-body hover:bg-stone-300 dark:hover:bg-stone-700 cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-black text-xs shadow-lg shadow-cyan-500/20 cursor-pointer">
                    Upload Photo
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

    function confirmDeletePhoto(photoId) {
        Swal.fire({
            title: 'Remove Photo?',
            text: 'Are you sure you want to remove this photo from the facility gallery?',
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
</script>
@endpush
