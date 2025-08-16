 <div class="mb-6 flex flex-wrap gap-4 items-center justify-between">
     <div class="flex flex-wrap gap-4 items-center">
         <div class="flex space-x-1 bg-gray-100 p-1 rounded-lg">
             <button wire:click="$set('filter', 'unassigned')"
                 class="px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ $filter === 'unassigned' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">{{ __('Unassigned') }}</button>
             <button wire:click="$set('filter', 'assigned')"
                 class="px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ $filter === 'assigned' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">{{ __('Assigned') }}</button>
             <button wire:click="$set('filter', 'all')"
                 class="px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ $filter === 'all' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">{{ __('All') }}</button>
         </div>
         @if ($hasQrCodes || $search)
             <div class="relative flex-1 max-w-md">
                 <input type="text" wire:model.live.debounce.300ms="search"
                     placeholder="{{ __('Search QR codes...') }}"
                     class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                 <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                     <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                             d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                     </svg>
                 </div>
             </div>
         @endif
     </div>
     @if ($hasQrCodes)
         <div class="flex items-center space-x-2">
             <label class="text-sm text-gray-600">{{ __('Per page:') }}</label>
             <select wire:model.live="perPage"
                 class="border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                 <option value="12">12</option>
                 <option value="24">24</option>
                 <option value="48">48</option>
                 <option value="96">96</option>
             </select>
         </div>
     @endif
 </div>
