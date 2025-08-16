<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
            <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                </path>
            </svg>
            {{ __('Photos') }}
        </h2>
        @if ($orderCarpet->orderCarpetPhotos->isNotEmpty())
            <button @click="showPhotoUploadModal = true"
                class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                {{ __('Add') }}
            </button>
        @endif
    </div>
    <div class="relative h-80 bg-gray-100">
        @if ($orderCarpet->orderCarpetPhotos->isNotEmpty())
            <div class="splide h-full" x-data x-init="new Splide($el, { type: 'loop', autoplay: false, pauseOnHover: true }).mount()">
                <div class="splide__track h-full">
                    <ul class="splide__list">
                        @foreach ($orderCarpet->orderCarpetPhotos as $photo)
                            <li class="splide__slide">
                                <img src="{{ asset('storage/' . $photo->photo_path) }}"
                                    alt="{{ __('Carpet Photo') }} {{ $loop->iteration }}"
                                    @click="openPhotoViewer('{{ asset('storage/' . $photo->photo_path) }}')"
                                    class="object-cover w-full h-80 cursor-pointer hover:scale-105 transition-transform duration-300">
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @else
            <div class="flex items-center justify-center h-full">
                <div class="text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('No photos available') }}</h3>
                    <p class="mt-2 text-sm text-gray-500">{{ __('Upload photos for this carpet.') }}</p>
                    <div class="mt-4">
                        <button @click="showPhotoUploadModal = true"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            {{ __('Add Photos') }}
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
