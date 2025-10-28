<div x-data="assignModalManager()" x-init="init()"
    class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/30 backdrop-blur-sm transition-all duration-300"
    x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" @keydown.escape.window="show = false" x-cloak>

    <div class="relative w-full max-w-md mx-4 my-8 bg-white rounded-xl shadow-2xl transform transition-all duration-300 sm:mx-auto"
        x-show="show" x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" @click.away="show = false">

        <!-- Nagłówek -->
        <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-t-xl">
            <h2 class="text-xl font-bold text-white">Przypisz Kod QR</h2>
        </div>

        <!-- Zawartość -->
        <div class="p-6">
            <!-- Odwołanie do Kodu QR -->
            <div class="mb-5 p-3 bg-blue-50 rounded-lg border border-blue-100">
                <p class="text-sm font-medium text-gray-700">
                    Kod QR:
                    <span class="font-semibold text-blue-700 break-words">{{ $qrCodeReference }}</span>
                </p>
            </div>

            <!-- Wyszukiwanie -->
            <div class="mb-5">
                <label for="search" class="block mb-2 text-sm font-medium text-gray-700">
                    Wyszukaj i Wybierz Dywan
                </label>
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <input id="search" type="text" wire:model.live.debounce.300ms="searchTerm"
                        placeholder="Wyszukaj po ID dywanu lub nazwie klienta..."
                        class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                        autocomplete="off" @focus="open = true" @input.debounce.300ms="open = true" />

                    <!-- Wyniki Listy Rozwijanej -->
                    <div x-show="open && ($wire.carpets.length > 0 || $wire.searchTerm.length > 2)"
                        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1"
                        class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto"
                        x-cloak>
                        @if (!empty($carpets))
                            <ul class="divide-y divide-gray-100">
                                @foreach ($carpets as $carpet)
                                    <li class="px-4 py-3 text-sm text-gray-700 cursor-pointer hover:bg-blue-50 transition-colors duration-150"
                                        wire:click="selectCarpet({{ $carpet['id'] }})" @click="open = false">
                                        <p class="font-semibold text-gray-900 truncate">
                                            ID dywanu: {{ $carpet['id'] }}
                                        </p>
                                        <p class="truncate text-gray-600">
                                            {{ $carpet['order']['client']['full_name'] ?? 'N/A' }} -
                                            Zamówienie #{{ $carpet['order_id'] }}
                                        </p>
                                    </li>
                                @endforeach
                            </ul>
                        @elseif(strlen($searchTerm) > 2)
                            <p class="px-4 py-3 text-sm text-gray-500">Nie znaleziono dywanów.</p>
                        @endif
                    </div>
                </div>
                @error('assignment')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Wyświetlenie Wybranego Dywanu -->
            @if ($selectedCarpet)
                <div class="mb-5 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <h3 class="mb-2 text-base font-semibold text-green-800">Wybrany Dywan:</h3>
                    <div class="space-y-1 text-sm text-gray-600">
                        <p><span class="font-medium">ID Dywanu:</span> {{ $selectedCarpet['id'] }}</p>
                        <p><span class="font-medium">Klient:</span>
                            {{ $selectedCarpet['order']['client']['full_name'] ?? 'N/A' }}</p>
                        <p><span class="font-medium">Zamówienie #:</span> {{ $selectedCarpet['order_id'] }}</p>
                    </div>
                </div>
            @endif

            <!-- Przyciski Akcji -->
            <div class="flex justify-end space-x-3">
                <button type="button" @click="show = false"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                    Anuluj
                </button>
                <button type="button" wire:click="assignQrCode" :disabled="!$wire.selectedCarpet"
                    class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
                    Przypisz
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function assignModalManager() {
        return {
            show: @entangle('showModal'),
            init() {
                this.$wire.on('close-assign-modal', () => {
                    this.show = false;
                });
            }
        };
    }
</script>
