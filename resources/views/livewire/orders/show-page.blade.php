<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <x-flash-session />

    @include('contents.orders.header')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        @include('contents.orders.stats')

        @include('contents.orders.info-cards')

        @include('contents.orders.payment-delivery-cards')

        <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-100">
            <div class="bg-gradient-to-r from-indigo-50 to-blue-50 px-6 py-5 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl leading-6 font-semibold text-gray-900 flex items-center">
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-xl flex items-center justify-center mr-4">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                </path>
                            </svg>
                        </div>
                        {{ __('Order Carpets') }}
                    </h3>
                    <div class="bg-white px-4 py-2 rounded-full shadow-sm border">
                        <span class="text-sm font-semibold text-gray-600">{{ $order->orderCarpets->count() }}
                            {{ Str::plural(__('carpet'), $order->orderCarpets->count()) }}</span>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="p-6">
                    <x-data-table :data="$this->rows" :headers="$dataTable['headers']" :showActions="true" :showSearch="$dataTable['showSearch']"
                        :showCreate="Auth::user()->can('createCarpet', $order)" :searchPlaceholder="$dataTable['searchPlaceholder']" :emptyMessage="$dataTable['emptyMessage']" :searchQuery="$search" :sortColumn="$sortColumn"
                        :sortDirection="$sortDirection" :createRoute="$dataTable['createRoute']" :createButtonName="$dataTable['createButtonName']" :editRoute="$dataTable['editRoute']" :viewRoute="$dataTable['viewRoute']"
                        :deleteAction="$dataTable['deleteAction']" :showBulkActions="false" class="border-0 shadow-none" />
                </div>
            </div>
        </div>

    </div>
</div>
