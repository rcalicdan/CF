<div class="xl:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-4 sm:p-8 border-b border-gray-100">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-lg sm:text-xl font-bold text-gray-900">Najnowsze skargi</h3>
                <p class="text-sm text-gray-500 mt-1">Ostatnie zamówienia oznaczone jako skargi</p>
            </div>
            <button class="text-sm font-medium text-red-600 hover:text-red-700 px-4 py-2 rounded-lg hover:bg-red-50 transition-colors self-start">
                Zobacz wszystkie
            </button>
        </div>
    </div>
    <div class="divide-y divide-gray-50">
        @forelse($recentComplaints as $complaint)
            @php $statusInfo = $this->getStatusColor($complaint['status']); @endphp
            <div class="p-4 sm:p-6">
                <div class="flex items-start space-x-3 sm:space-x-4">
                    <div class="bg-gradient-to-br from-red-500 to-red-600 p-2 sm:p-3 rounded-xl shadow-lg flex-shrink-0">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="space-y-2 flex-1 min-w-0">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <p class="text-sm sm:text-base font-semibold text-gray-900 truncate">
                                Zamówienie #{{ $complaint['order_id'] }}
                            </p>
                            <div class="flex items-center space-x-2">
                                <span class="text-xs font-medium text-green-600">
                                    {{ number_format($complaint['total_amount'], 2) }} PLN
                                </span>
                                <span class="text-xs text-gray-400 flex-shrink-0">
                                    {{ $complaint['created_at']->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                        <p class="text-xs sm:text-sm text-gray-600">
                            {{ $complaint['client_name'] }} • {{ $complaint['carpet_qr'] }}
                        </p>
                        <p class="text-xs sm:text-sm text-gray-500 line-clamp-2">
                            {{ $complaint['details'] }}
                        </p>
                        <div class="flex flex-wrap items-center gap-2 sm:gap-3 mt-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusInfo['bg'] }} {{ $statusInfo['text'] }}">
                                {{ $statusInfo['label'] }}
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $complaint['priority']['level'] === 'high' ? 'bg-red-100 text-red-700' : 
                                   ($complaint['priority']['level'] === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
                                {{ $complaint['priority']['label'] }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-8 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-gray-500">Brak skarg w wybranym okresie</p>
            </div>
        @endforelse
    </div>
</div>