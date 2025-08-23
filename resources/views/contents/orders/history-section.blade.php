<div class="bg-white shadow-sm rounded-xl border border-gray-200/60 hover:shadow-md transition-all duration-300 mb-6">
    <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200/50 bg-gray-50/50">
        <div class="flex items-center space-x-2">
            <div class="w-6 h-6 bg-blue-500 rounded-md flex items-center justify-center">
                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h3 class="font-semibold text-gray-900">Historia zamówienia</h3>
        </div>
        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">
            {{ $order->orderHistories->count() }} {{ $order->orderHistories->count() === 1 ? 'wpis' : 'wpisów' }}
        </span>
    </div>

    <div class="max-h-96 overflow-y-auto">
        @if ($order->orderHistories && $order->orderHistories->count() > 0)
            @foreach ($order->orderHistories as $history)
                <div
                    class="relative px-5 py-3 border-b border-gray-100 last:border-b-0 hover:bg-gray-50/70 transition-colors duration-150 group">
                    @php
                        $statusColor = $history->getStatusBadgeColor();
                        $iconPath = $history->getStatusChangeIcon();

                        // Format date in the requested format: Aug 23, 2025 5:50 PM
                        $months = [
                            1 => 'Sty',
                            2 => 'Lut',
                            3 => 'Mar',
                            4 => 'Kwi',
                            5 => 'Maj',
                            6 => 'Cze',
                            7 => 'Lip',
                            8 => 'Sie',
                            9 => 'Wrz',
                            10 => 'Paź',
                            11 => 'Lis',
                            12 => 'Gru',
                        ];

                        $day = $history->created_at->format('j');
                        $month = $months[$history->created_at->format('n')];
                        $year = $history->created_at->format('Y');
                        $time = $history->created_at->format('G:i');

                        $formattedDate = "{$month} {$day}, {$year} {$time}";
                    @endphp

                    <div class="flex items-start space-x-3">
                        <!-- Timeline Icon -->
                        <div class="flex-shrink-0 mt-1">
                            <div
                                class="w-6 h-6 rounded-full bg-{{ $statusColor }}-100 border-2 border-{{ $statusColor }}-300 flex items-center justify-center group-hover:scale-110 transition-transform duration-200">
                                <svg class="h-3 w-3 text-{{ $statusColor }}-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="{{ $iconPath }}" />
                                </svg>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between mb-1">
                                <div class="flex items-center space-x-2 flex-wrap">
                                    <span
                                        class="text-sm font-medium text-gray-900">{{ $history->getActionTypeLabel() }}</span>

                                    @if ($history->action_type === 'status_change' && $history->old_status && $history->new_status)
                                        <div class="flex items-center space-x-1">
                                            <span
                                                class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 border">
                                                {{ $history->old_status_label }}
                                            </span>
                                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                            </svg>
                                            <span
                                                class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800 border border-{{ $statusColor }}-200">
                                                {{ $history->new_status_label }}
                                            </span>
                                        </div>
                                    @elseif($history->new_status)
                                        <span
                                            class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800 border border-{{ $statusColor }}-200">
                                            {{ $history->new_status_label }}
                                        </span>
                                    @endif
                                </div>

                                <span class="text-xs text-gray-500 font-medium whitespace-nowrap ml-2">
                                    {{ $formattedDate }}
                                </span>
                            </div>

                            @if ($history->notes)
                                <p class="text-sm text-gray-600 mb-2 leading-tight">{{ $history->notes }}</p>
                            @endif

                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-1">
                                    <div class="w-4 h-4 bg-gray-100 rounded-full flex items-center justify-center">
                                        <svg class="h-2.5 w-2.5 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <span class="text-xs text-gray-500 truncate">
                                        {{ $history->user->full_name ?? 'System' }}
                                    </span>
                                </div>

                                @php
                                    $formattedChanges = $history->formatted_changes;
                                @endphp
                                @if ($formattedChanges && count($formattedChanges) > 0)
                                    <details class="group/details">
                                        <summary
                                            class="text-xs text-blue-600 hover:text-blue-800 cursor-pointer font-medium flex items-center space-x-1">
                                            <svg class="w-3 h-3 transform transition-transform group-open/details:rotate-90"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                            <span>{{ count($formattedChanges) }}
                                                {{ count($formattedChanges) === 1 ? 'zmiana' : 'zmian' }}</span>
                                        </summary>
                                        <div class="mt-2 text-xs bg-gray-50 rounded-lg p-3 border border-gray-200">
                                            @foreach ($formattedChanges as $field => $change)
                                                <div class="flex flex-col space-y-1 mb-2 last:mb-0">
                                                    <span class="font-medium text-gray-900">{{ $field }}:</span>
                                                    @if (is_array($change) && isset($change['old'], $change['new']))
                                                        <div class="flex items-center space-x-2 text-xs">
                                                            <span
                                                                class="inline-flex items-center px-2 py-1 bg-red-50 text-red-700 rounded border border-red-200 break-all">
                                                                {{ $change['old'] }}
                                                            </span>
                                                            <svg class="w-3 h-3 text-gray-400 flex-shrink-0"
                                                                fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6">
                                                                </path>
                                                            </svg>
                                                            <span
                                                                class="inline-flex items-center px-2 py-1 bg-green-50 text-green-700 rounded border border-green-200 break-all">
                                                                {{ $change['new'] }}
                                                            </span>
                                                        </div>
                                                    @else
                                                        <span class="text-gray-600 ml-2">{{ $change }}</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </details>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="text-center py-8 px-5">
                <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-900 mb-1">Brak historii</p>
                <p class="text-xs text-gray-500">Historia zmian będzie wyświetlana tutaj</p>
            </div>
        @endif
    </div>
</div>
