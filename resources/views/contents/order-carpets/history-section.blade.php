<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
            <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ __('Carpet History') }}
        </h2>
    </div>

    <div class="p-6">
        @if ($orderCarpet->histories->count() > 0)
            <div class="flow-root">
                <ul role="list" class="-mb-8">
                    @foreach ($orderCarpet->histories as $history)
                        <li>
                            <div class="relative pb-8">
                                @if (!$loop->last)
                                    <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200"
                                        aria-hidden="true"></span>
                                @endif
                                <div class="relative flex items-start space-x-3">
                                    {{-- Icon --}}
                                    <div class="relative">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full 
                                            @if ($history->getStatusBadgeColor() === 'red') bg-red-100
                                            @elseif($history->getStatusBadgeColor() === 'green') bg-green-100
                                            @elseif($history->getStatusBadgeColor() === 'blue') bg-blue-100
                                            @elseif($history->getStatusBadgeColor() === 'yellow') bg-yellow-100
                                            @elseif($history->getStatusBadgeColor() === 'indigo') bg-indigo-100
                                            @elseif($history->getStatusBadgeColor() === 'purple') bg-purple-100
                                            @elseif($history->getStatusBadgeColor() === 'orange') bg-orange-100
                                            @elseif($history->getStatusBadgeColor() === 'emerald') bg-emerald-100
                                            @elseif($history->getStatusBadgeColor() === 'amber') bg-amber-100
                                            @else bg-gray-100
                                            @endif">
                                           <svg class="h-5 w-5 
                                               @if ($history->getStatusBadgeColor() === 'red') text-red-600
                                               @elseif($history->getStatusBadgeColor() === 'green') text-green-600
                                               @elseif($history->getStatusBadgeColor() === 'blue') text-blue-600
                                               @elseif($history->getStatusBadgeColor() === 'yellow') text-yellow-600
                                               @elseif($history->getStatusBadgeColor() === 'indigo') text-indigo-600
                                               @elseif($history->getStatusBadgeColor() === 'purple') text-purple-600
                                               @elseif($history->getStatusBadgeColor() === 'orange') text-orange-600
                                               @elseif($history->getStatusBadgeColor() === 'emerald') text-emerald-600
                                               @elseif($history->getStatusBadgeColor() === 'amber') text-amber-600
                                               @else text-gray-600 
                                               @endif"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="{{ $history->getStatusChangeIcon() }}"></path>
                                            </svg>
                                        </div>
                                    </div>

                                    {{-- Content --}}
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <div class="text-sm">
                                                    <span class="font-medium text-gray-900">
                                                        {{ $history->getActionTypeLabel() }}
                                                    </span>
                                                    @if ($history->action_type === 'status_change')
                                                        <span
                                                            class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                           @if ($history->getStatusBadgeColor() === 'red') bg-red-100 text-red-800
                                                           @elseif($history->getStatusBadgeColor() === 'green') bg-green-100 text-green-800
                                                           @elseif($history->getStatusBadgeColor() === 'blue') bg-blue-100 text-blue-800
                                                           @elseif($history->getStatusBadgeColor() === 'yellow') bg-yellow-100 text-yellow-800
                                                           @elseif($history->getStatusBadgeColor() === 'indigo') bg-indigo-100 text-indigo-800
                                                           @elseif($history->getStatusBadgeColor() === 'purple') bg-purple-100 text-purple-800
                                                           @elseif($history->getStatusBadgeColor() === 'orange') bg-orange-100 text-orange-800
                                                           @elseif($history->getStatusBadgeColor() === 'emerald') bg-emerald-100 text-emerald-800
                                                           @elseif($history->getStatusBadgeColor() === 'amber') bg-amber-100 text-amber-800
                                                           @else bg-gray-100 text-gray-800 
                                                           @endif">
                                                            {{ $history->new_status_label }}
                                                        </span>
                                                    @endif
                                                </div>
                                                @if ($history->notes)
                                                    <p class="mt-0.5 text-sm text-gray-600">{{ $history->notes }}</p>
                                                @endif
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                <div>{{ $history->created_at->format('H:i') }}</div>
                                                <div>{{ $history->created_at->format('d.m.Y') }}</div>
                                            </div>
                                        </div>

                                        {{-- Changes Details --}}
                                        @if ($history->formatted_changes && count($history->formatted_changes) > 0)
                                            <div class="mt-2">
                                                <div class="bg-gray-50 rounded-lg p-3">
                                                    <h4
                                                        class="text-xs font-medium text-gray-900 uppercase tracking-wide mb-2">
                                                        Zmiany:</h4>
                                                    <div class="space-y-1">
                                                        @foreach ($history->formatted_changes as $field => $change)
                                                            <div class="text-xs text-gray-600">
                                                                <span class="font-medium">{{ $field }}:</span>
                                                                @if (is_array($change) && isset($change['old'], $change['new']))
                                                                    <span
                                                                        class="text-red-600">{{ $change['old'] }}</span>
                                                                    <span class="mx-1">→</span>
                                                                    <span
                                                                        class="text-green-600">{{ $change['new'] }}</span>
                                                                @else
                                                                    <span
                                                                        class="text-blue-600">{{ $change }}</span>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- User Info --}}
                                        <div class="mt-2 flex items-center text-xs text-gray-500">
                                            <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $history->user->full_name ?? 'System' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('No history') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('No activity has been recorded for this carpet yet.') }}
                </p>
            </div>
        @endif
    </div>
</div>