<!-- Tab Navigation -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
    <nav class="flex space-x-8 px-6" aria-label="Tabs">
        <button wire:click="setActiveTab('overview')"
            class="py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200
                       {{ $activeTab === 'overview'
                           ? 'border-blue-500 text-blue-600'
                           : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            {{ __('Overview') }}
        </button>
        <button wire:click="setActiveTab('orders')"
            class="py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200
                       {{ $activeTab === 'orders'
                           ? 'border-blue-500 text-blue-600'
                           : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            {{ __('Orders') }} ({{ $stats['total_orders'] }})
        </button>
        <button wire:click="setActiveTab('carpets')"
            class="py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200
                       {{ $activeTab === 'carpets'
                           ? 'border-blue-500 text-blue-600'
                           : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            {{ __('Carpets') }} ({{ $stats['total_carpets'] }})
        </button>
        <button wire:click="setActiveTab('location')"
            class="py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200
                       {{ $activeTab === 'location'
                           ? 'border-blue-500 text-blue-600'
                           : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            {{ __('Location') }}
        </button>
    </nav>
</div>