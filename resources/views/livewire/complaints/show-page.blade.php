<div>
    <x-flash-session />

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ __('Complaint Details') }}</h1>
            <p class="text-gray-600">{{ __('Complaint ID: :id', ['id' => $complaint->id]) }}</p>
        </div>
        <a wire:navigate href="{{ route('complaints.index') }}"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
            <i class="fas fa-arrow-left mr-2"></i>
            {{ __('Back to Complaints') }}
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Complaint Information -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">{{ __('Complaint Information') }}</h2>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Complaint Details') }}</label>
                        <div class="mt-1 p-3 bg-gray-50 rounded-md">
                            <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $complaint->complaint_details }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
                            <div class="mt-1">
                                @if ($isEditingStatus)
                                    <div class="flex items-center space-x-2">
                                        <select wire:model="newStatus"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            @foreach ($statusOptions as $option)
                                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                            @endforeach
                                        </select>
                                        <button wire:click="updateStatus"
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                            {{ __('Save') }}
                                        </button>
                                        <button wire:click="toggleStatusEdit"
                                            class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                            {{ __('Cancel') }}
                                        </button>
                                    </div>
                                @else
                                    <div class="flex items-center space-x-2">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            @if ($complaint->status === 'open') bg-yellow-100 text-yellow-800
                                            @elseif($complaint->status === 'in_progress') bg-blue-100 text-blue-800
                                            @elseif($complaint->status === 'resolved') bg-green-100 text-green-800
                                            @elseif($complaint->status === 'rejected') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst(str_replace('_', ' ', $complaint->status)) }}
                                        </span>
                                        @can('update', $complaint)
                                            <button wire:click="toggleStatusEdit"
                                                class="text-indigo-600 hover:text-indigo-900 text-sm">
                                                {{ __('Edit') }}
                                            </button>
                                        @endcan
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Created At') }}</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $complaint->created_at->format('M j, Y g:i A') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Carpet Information -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">{{ __('Related Carpet Information') }}</h2>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Carpet ID') }}</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $complaint->orderCarpet->id }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('QR Code') }}</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $complaint->orderCarpet->qr_code ?? 'N/A' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Dimensions') }}</label>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $complaint->orderCarpet->height }}m × {{ $complaint->orderCarpet->width }}m
                                ({{ $complaint->orderCarpet->total_area }}m²)
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Carpet Status') }}</label>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $complaint->orderCarpet->status_label }}
                            </span>
                        </div>
                    </div>

                    @if ($complaint->orderCarpet->remarks)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Carpet Remarks') }}</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $complaint->orderCarpet->remarks }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Client Information -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Client Information') }}</h3>
                </div>
                <div class="px-6 py-4 space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Name') }}</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $complaint->orderCarpet->order->client->full_name }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Phone') }}</label>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $complaint->orderCarpet->order->client->phone_number }}</p>
                    </div>

                    @if ($complaint->orderCarpet->order->client->full_address)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Address') }}</label>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $complaint->orderCarpet->order->client->full_address }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Order Information -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Order Information') }}</h3>
                </div>
                <div class="px-6 py-4 space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Order ID') }}</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <a href="{{ route('orders.show', $complaint->orderCarpet->order) }}"
                                class="text-indigo-600 hover:text-indigo-900">
                                #{{ $complaint->orderCarpet->order->id }}
                            </a>
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Order Status') }}</label>
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $complaint->orderCarpet->order->status_label }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Schedule Date') }}</label>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $complaint->orderCarpet->order->schedule_date ? $complaint->orderCarpet->order->schedule_date->format('M j, Y') : 'N/A' }}
                        </p>
                    </div>

                    @if ($complaint->orderCarpet->order->driver)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Driver') }}</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $complaint->orderCarpet->order->driver_name }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Services -->
            @if ($complaint->orderCarpet->services->count() > 0)
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Services') }}</h3>
                    </div>
                    <div class="px-6 py-4">
                        <ul class="space-y-2">
                            @foreach ($complaint->orderCarpet->services as $service)
                                <li class="flex justify-between text-sm">
                                    <span>{{ $service->name }}</span>
                                    <span
                                        class="font-medium">${{ number_format($service->pivot->total_price, 2) }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <div class="mt-3 pt-3 border-t border-gray-200">
                            <div class="flex justify-between text-sm font-medium">
                                <span>{{ __('Total') }}</span>
                                <span>${{ number_format($complaint->orderCarpet->total_price, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
