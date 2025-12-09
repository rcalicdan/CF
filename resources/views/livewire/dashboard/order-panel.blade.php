<div class="max-w-[1920px] mx-auto px-6 sm:px-8 lg:px-10 py-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <a href="/orders/create"
                class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Dodaj zamówienie
            </a>

            @if($statusFilter !== 'all')
                <button wire:click="$set('statusFilter', 'all')"
                    class="inline-flex items-center justify-center px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Wyczyść filtr
                </button>
            @endif
        </div>

        <div class="flex flex-col sm:flex-row gap-4 w-full lg:w-auto">
            <div class="relative">
                <select wire:model.live="dateRange"
                    class="appearance-none w-full sm:w-auto px-4 py-3 pr-10 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    <option value="3">Ostatnie 3 dni</option>
                    <option value="7">Ostatnie 7 dni</option>
                    <option value="30">Ostatnie 30 dni</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </div>

            <div class="relative flex-1 lg:min-w-[400px]">
                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search"
                    class="w-full pl-11 pr-4 py-3 bg-white border border-gray-200 rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                    placeholder="Nr zamówienia, Imię i Nazwisko, Adres lub Miasto">
            </div>
        </div>
    </div>

    <!-- 2. STATUS CARDS SECTION -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Pending Orders Card -->
        <button wire:click="$set('statusFilter', 'pending')"
            class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-2xl shadow-sm border-2 {{ $statusFilter === 'pending' ? 'border-yellow-400' : 'border-yellow-200' }} p-6 hover:shadow-lg transition-all duration-300 text-left relative group">
            @if($statusFilter === 'pending')
                <!-- Tooltip: Bottom Right -->
                <div class="absolute top-full mt-2 right-6 z-10 bg-yellow-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-lg whitespace-nowrap">
                    Filtrowanie aktywne
                    <!-- Arrow pointing UP -->
                    <div class="absolute -top-1 right-3 w-2 h-2 bg-yellow-600 rotate-45"></div>
                </div>
            @endif
            <div class="flex items-start justify-between">
                <div class="space-y-2">
                    <p class="text-sm font-medium text-yellow-700">Oczekujące</p>
                    <p class="text-3xl font-bold text-yellow-900">{{ $statusCounts['pending'] }}</p>
                    <div class="flex items-center space-x-2">
                        <div class="flex items-center bg-yellow-200 px-2 py-1 rounded-full">
                            <svg class="w-3 h-3 text-yellow-700 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-xs font-semibold text-yellow-800">Nowe</span>
                        </div>
                        <span class="text-xs text-yellow-600">zamówienia</span>
                    </div>
                </div>
                <div class="bg-yellow-500 p-3 rounded-xl shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </button>

        <!-- In Progress Orders Card -->
        <button wire:click="$set('statusFilter', 'in_progress')"
            class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl shadow-sm border-2 {{ $statusFilter === 'in_progress' ? 'border-blue-400' : 'border-blue-200' }} p-6 hover:shadow-lg transition-all duration-300 text-left relative group">
            @if($statusFilter === 'in_progress')
                <!-- Tooltip: Bottom Right -->
                <div class="absolute top-full mt-2 right-6 z-10 bg-blue-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-lg whitespace-nowrap">
                    Filtrowanie aktywne
                    <!-- Arrow pointing UP -->
                    <div class="absolute -top-1 right-3 w-2 h-2 bg-blue-600 rotate-45"></div>
                </div>
            @endif
            <div class="flex items-start justify-between">
                <div class="space-y-2">
                    <p class="text-sm font-medium text-blue-700">W realizacji</p>
                    <p class="text-3xl font-bold text-blue-900">{{ $statusCounts['in_progress'] }}</p>
                    <div class="flex items-center space-x-2">
                        <div class="flex items-center bg-blue-200 px-2 py-1 rounded-full">
                            <svg class="w-3 h-3 text-blue-700 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-xs font-semibold text-blue-800">Aktywne</span>
                        </div>
                        <span class="text-xs text-blue-600">w trakcie</span>
                    </div>
                </div>
                <div class="bg-blue-500 p-3 rounded-xl shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
            </div>
        </button>

        <!-- Completed Orders Card -->
        <button wire:click="$set('statusFilter', 'completed')"
            class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl shadow-sm border-2 {{ $statusFilter === 'completed' ? 'border-green-400' : 'border-green-200' }} p-6 hover:shadow-lg transition-all duration-300 text-left relative group">
            @if($statusFilter === 'completed')
                <!-- Tooltip: Bottom Right -->
                <div class="absolute top-full mt-2 right-6 z-10 bg-green-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-lg whitespace-nowrap">
                    Filtrowanie aktywne
                    <!-- Arrow pointing UP -->
                    <div class="absolute -top-1 right-3 w-2 h-2 bg-green-600 rotate-45"></div>
                </div>
            @endif
            <div class="flex items-start justify-between">
                <div class="space-y-2">
                    <p class="text-sm font-medium text-green-700">Zrealizowane</p>
                    <p class="text-3xl font-bold text-green-900">{{ $statusCounts['completed'] }}</p>
                    <div class="flex items-center space-x-2">
                        <div class="flex items-center bg-green-200 px-2 py-1 rounded-full">
                            <svg class="w-3 h-3 text-green-700 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-xs font-semibold text-green-800">Gotowe</span>
                        </div>
                        <span class="text-xs text-green-600">zakończone</span>
                    </div>
                </div>
                <div class="bg-green-500 p-3 rounded-xl shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
        @forelse($orders as $order)
            @php
                $cardType = 'pending';
                if (in_array($order->status, ['accepted', 'processing'])) {
                    $cardType = 'in-progress';
                } elseif (in_array($order->status, ['completed', 'delivered'])) {
                    $cardType = 'completed';
                }
            @endphp
            @include('livewire.dashboard.partials.order-card', ['order' => $order, 'cardType' => $cardType])
        @empty
            <div class="col-span-full bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <p class="text-lg font-medium text-gray-900 mb-1">Brak zamówień</p>
                <p class="text-sm text-gray-500">Nie znaleziono zamówień spełniających kryteria wyszukiwania</p>
            </div>
        @endforelse
    </div>

    @if($orders->hasPages())
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-600">
                    Pokazuję <span class="font-semibold text-gray-900">{{ $orders->firstItem() }}</span> 
                    - <span class="font-semibold text-gray-900">{{ $orders->lastItem() }}</span> 
                    z <span class="font-semibold text-gray-900">{{ $orders->total() }}</span> wyników
                </p>

                <div class="flex items-center space-x-1">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    @endif
</div>