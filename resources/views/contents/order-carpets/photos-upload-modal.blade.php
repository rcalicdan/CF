<div x-show="showPhotoUploadModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
    @keydown.escape.window="showPhotoUploadModal = false" x-data="photoUpload()">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">

        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50 transition-opacity" aria-hidden="true"
            @click="showPhotoUploadModal = false"></div>

        <!-- Spacer for centering -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div @click.stop
            class="relative inline-block align-bottom bg-white rounded-2xl overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <!-- Icon -->
                    <div
                        class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>

                    <!-- Content -->
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg font-medium text-gray-900">
                            {{ __('Upload Photos') }}
                        </h3>
                        <div class="mt-4">
                            <!-- Invisible file inputs -->
                            <input type="file" multiple accept="image/*" class="hidden" x-ref="photoInput"
                                @change="handleFileValidation($event)">
                            <input type="file" wire:model="newPhotos" multiple accept="image/*" class="hidden"
                                x-ref="livewireInput">

                            <!-- Drop zone -->
                            <div @click="$refs.photoInput.click()"
                                class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md cursor-pointer hover:border-indigo-500 transition-colors">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                        viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28
                              M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <p class="text-sm text-gray-600">
                                        {{ __('Click to upload or drag and drop') }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ __('PNG, JPG, WebP up to 5MB each (max 10 files)') }}
                                    </p>
                                </div>
                            </div>

                            <!-- Client error -->
                            <div x-show="clientError" class="mt-4 bg-red-50 border border-red-200 rounded-md p-3">
                                <div class="flex">
                                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293... (icon path truncated)"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800">{{ __('Upload Error') }}</h3>
                                        <p class="mt-2 text-sm text-red-700" x-text="clientError"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Upload progress -->
                            <div x-show="isUploading" class="w-full bg-gray-200 rounded-full h-2.5 mt-4">
                                <div class="h-2.5 rounded-full transition-all duration-300"
                                    :style="`width: ${progress}%`"></div>
                            </div>

                            <!-- Server errors -->
                            @if ($errors->any())
                                <div class="mt-4 bg-red-50 border border-red-200 rounded-md p-3">
                                    <div class="flex">
                                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293... (icon path truncated)"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-red-800">{{ __('Upload Error') }}</h3>
                                            <ul class="mt-2 list-disc list-inside text-sm text-red-700 space-y-1">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Preview grid -->
                            <div x-show="previewFiles.length > 0" class="mt-4">
                                <div class="flex justify-between items-center mb-2">
                                    <h4 class="text-sm font-medium text-gray-900">
                                        {{ __('Selected Files') }} (<span x-text="previewFiles.length"></span>)
                                    </h4>
                                    <button type="button" @click="clearAll()"
                                        class="text-xs text-red-600 hover:text-red-800">
                                        {{ __('Clear All') }}
                                    </button>
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <template x-for="(item, index) in previewFiles" :key="index">
                                        <div class="relative group">
                                            <img :src="item.url"
                                                class="rounded-lg object-cover h-24 w-full shadow-sm" loading="lazy"
                                                :alt="item.name">
                                            <p class="mt-1 text-xs text-gray-500 truncate" x-text="item.name"></p>
                                            <button type="button" @click="removeImage(index)"
                                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1.5
                                     opacity-0 group-hover:opacity-100 transition-opacity duration-200
                                     hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500
                                     focus:ring-offset-1">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer buttons -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="saveNewPhotos" wire:loading.attr="disabled" type="button"
                    :disabled="previewFiles.length === 0"
                    class="w-full inline-flex justify-center rounded-md border border-transparent
                       shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white
                       hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2
                       focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                    <span wire:loading.remove wire:target="saveNewPhotos">{{ __('Save Photos') }}</span>
                    <span wire:loading wire:target="saveNewPhotos">{{ __('Saving...') }}</span>
                </button>
                <button @click="showPhotoUploadModal = false" type="button"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300
                       shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700
                       hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2
                       focus:ring-gray-500 sm:mt-0 sm:w-auto sm:text-sm">
                    {{ __('Cancel') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function photoUpload() {
        return {
            isUploading: false,
            progress: 0,
            imageUrls: {},
            clientError: '',
            validFiles: [],
            previewFiles: [],

            handleFileValidation(event) {
                this.clientError = '';
                const files = Array.from(event.target.files);
                const maxSize = 5 * 1024 * 1024; 
                const maxFiles = 10;

                if (files.length > maxFiles) {
                    this.clientError = '{{ __('You can upload a maximum of 10 photos.') }}';
                    this._resetInput(event);
                    return false;
                }

                const invalidFiles = [];
                const oversizedFiles = [];
                this.validFiles = [];
                this.previewFiles = [];
                this.imageUrls = {};

                files.forEach((file, idx) => {
                    if (!file.type.startsWith('image/')) {
                        invalidFiles.push(file.name);
                    } else if (file.size > maxSize) {
                        oversizedFiles.push(file.name);
                    } else {
                        this.validFiles.push(file);
                        this.previewFiles.push({
                            file,
                            name: file.name,
                            url: URL.createObjectURL(file)
                        });
                        this.imageUrls[idx] = URL.createObjectURL(file);
                    }
                });

                if (invalidFiles.length) {
                    this.clientError = '{{ __('Please select only image files (JPEG, PNG, GIF, etc.).') }}';
                    this._resetInput(event);
                    return false;
                }

                if (oversizedFiles.length) {
                    this.clientError =
                        '{{ __('The following files are too large (max 5MB each):') }} ' +
                        oversizedFiles.join(', ');
                    this._resetInput(event);
                    return false;
                }

                this._syncToLivewire();
                return true;
            },

            removeImage(index) {
                URL.revokeObjectURL(this.previewFiles[index].url);
                this.previewFiles.splice(index, 1);
                this.validFiles.splice(index, 1);
                this._syncToLivewire();
                this.clientError = '';
            },

            clearAll() {
                this.previewFiles.forEach(item => URL.revokeObjectURL(item.url));
                this.validFiles = [];
                this.previewFiles = [];
                this.imageUrls = {};
                this.clientError = '';
                this.$refs.photoInput.value = '';
                this._syncToLivewire();
            },

            _resetInput(event) {
                event.target.value = '';
                this.validFiles = [];
                this.previewFiles = [];
                this.imageUrls = {};
            },

            _syncToLivewire() {
                const dt = new DataTransfer();
                this.validFiles.forEach(f => dt.items.add(f));
                this.$refs.livewireInput.files = dt.files;
                this.$refs.livewireInput.dispatchEvent(new Event('change', {
                    bubbles: true
                }));
            }
        };
    }
</script>
