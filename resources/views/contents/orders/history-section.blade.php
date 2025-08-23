<div
    class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-100 hover:shadow-xl transition-all duration-300 mb-8">
    <div class="bg-gradient-to-r from-purple-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg leading-6 font-semibold text-gray-900 flex items-center">
            <div
                class="w-8 h-8 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center mr-3">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                    </path>
                </svg>
            </div>
            {{ __('Order History & Status Changes') }}
        </h3>
    </div>
    <div class="px-6 py-6">
        @if ($order->orderHistories && $order->orderHistories->count() > 0)
        <div class="flow-root">
            <ul role="list" class="-mb-8">
                @foreach ($order->orderHistories as $history)
                <li>
                    <div class="relative pb-8">
                        @if (!$loop->last)
                        <span
                            class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gradient-to-b from-gray-200 to-gray-100"
                            aria-hidden="true"></span>
                        @endif
                        <div class="relative flex space-x-3">
                            <div>
                                @php
                                $statusColor = $history->getStatusBadgeColor();
                                $iconPath = $history->getStatusChangeIcon();
                                @endphp
                                <span
                                    class="h-8 w-8 rounded-full bg-gradient-to-br from-{{ $statusColor }}-400 to-{{ $statusColor }}-500 flex items-center justify-center ring-8 ring-white shadow-sm">
                                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="{{ $iconPath }}" />
                                    </svg>
                                </span>
                            </div>
                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <p class="text-sm font-semibold text-gray-900">
                                            {{ $history->getActionTypeLabel() }}
                                        </p>
                                        @if ($history->action_type === 'status_change' && $history->old_status && $history->new_status)
                                        <div class="flex items-center space-x-2">
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ $history->old_status_label }}
                                            </span>
                                            <svg class="w-3 h-3 text-gray-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                            </svg>
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
                                                {{ $history->new_status_label }}
                                            </span>
                                        </div>
                                        @elseif($history->new_status)
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
                                            {{ $history->new_status_label }}
                                        </span>
                                        @endif
                                    </div>

                                    @if ($history->notes)
                                    <div class="mt-2 bg-gray-50 rounded-lg p-3 border">
                                        <p class="text-sm text-gray-700">{{ $history->notes }}</p>
                                    </div>
                                    @endif

                                    @php
                                    $formattedChanges = $history->formatted_changes;
                                    @endphp
                                    @if ($formattedChanges && count($formattedChanges) > 0)
                                    <div class="mt-2">
                                        <details class="group">
                                            <summary
                                                class="flex cursor-pointer items-center text-sm text-gray-500 hover:text-gray-700 transition-colors">
                                                <svg class="mr-1 h-3 w-3 transform transition-transform group-open:rotate-90"
                                                    fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                                {{ __('View Changes') }} ({{ count($formattedChanges) }})
                                            </summary>
                                            <div class="mt-2 ml-4 bg-gray-50 rounded-lg p-3 border">
                                                @foreach ($formattedChanges as $field => $change)
                                                <div class="text-xs text-gray-600 mb-2 last:mb-0">
                                                    <span
                                                        class="font-medium text-gray-800">{{ $field }}:</span>
                                                    @if (is_array($change) && isset($change['old'], $change['new']))
                                                    <div class="mt-1 flex items-center space-x-2">
                                                        <span
                                                            class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs">{{ $change['old'] }}</span>
                                                        <svg class="w-3 h-3 text-gray-400"
                                                            fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                                        </svg>
                                                        <span
                                                            class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">{{ $change['new'] }}</span>
                                                    </div>
                                                    @else
                                                    <span class="ml-1">{{ $change }}</span>
                                                    @endif
                                                </div>
                                                @endforeach
                                            </div>
                                        </details>
                                    </div>
                                    @endif

                                    <div class="mt-2 flex items-center space-x-2 text-xs text-gray-500">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span>{{ $history->user->full_name ?? __('System') }}</span>
                                    </div>
                                </div>
                                <div class="whitespace-nowrap text-right text-xs text-gray-500">
                                    <time datetime="{{ $history->created_at->toISOString() }}">
                                        {{ $history->created_at->format('d.m.Y') }}<br>
                                        <span
                                            class="text-gray-400">{{ $history->created_at->format('H:i') }}</span>
                                    </time>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
        @else
        <div class="text-center py-12">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                    </path>
                </svg>
            </div>
            <p class="text-lg font-medium text-gray-500">{{ __('No History Available') }}</p>
            <p class="text-sm text-gray-400 mt-1">{{ __('Order history and status changes will appear here') }}</p>
        </div>
        @endif
    </div>
</div>