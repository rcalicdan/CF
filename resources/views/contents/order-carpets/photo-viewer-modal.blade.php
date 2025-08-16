<div x-show="isPhotoViewerOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" class="fixed inset-0 z-[99] flex items-center justify-center p-4"
    style="display: none;" @keydown.escape.window="closePhotoViewer()">

    <div @click="closePhotoViewer()" class="fixed inset-0 bg-black/70 backdrop-blur-sm" aria-hidden="true"></div>

    <div class="relative w-full h-full flex items-center justify-center pointer-events-none">
        <img x-show="isPhotoViewerOpen" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
            :src="viewingPhotoUrl" alt="{{ __('Full screen carpet photo') }}"
            class="object-contain max-w-[90vw] max-h-[90vh] rounded-lg shadow-2xl pointer-events-auto" @click.stop>
    </div>

    <button @click="closePhotoViewer()"
        class="absolute top-4 right-4 text-white/70 hover:text-white transition-colors z-10">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>
</div>
