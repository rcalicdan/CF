<x-layouts.app>
    <div x-data="routeOptimizer()" x-cloak class="min-h-screen">
        <div class="w-full max-w-full">
            @include('contents.routes.partials.reoptimization-banner')
            
            <div class="grid grid-cols-1 xl:grid-cols-4 gap-4 lg:gap-6">
                <!-- Left Column -->
                <div class="xl:col-span-1 space-y-4 lg:space-y-6">
                    @include('contents.routes.partials.date-selection')
                    @include('contents.routes.partials.driver-selection')
                    @include('contents.routes.partials.delivery-orders')
                    @include('contents.routes.partials.optimization-controls')
                </div>          

                <!-- Right Column -->
                <div class="xl:col-span-3 space-y-4 lg:space-y-6">
                    @include('contents.routes.partials.map-section')
                    @include('contents.routes.partials.manual-edit-controls')
                    
                    @include('contents.routes.partials.route-summary')
                    @include('contents.routes.partials.route-details')
                </div>
            </div>
        </div>

        @include('contents.routes.partials.loading-overlay')
    </div>
</x-layouts.app>