<div>
    <x-flash-session />
    <x-partials.dashboard.content-header :title="__('Users Management')" />

    <!-- Status Filter - Minimalist -->
    <div class="mb-4">
        <div class="inline-flex items-center gap-2 bg-white dark:bg-gray-800 rounded-lg shadow px-2 py-2">
            <button wire:click="$set('statusFilter', 'active')"
                class="px-3 py-1.5 text-sm font-medium rounded transition-colors
                {{ $statusFilter === 'active'
                    ? 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300'
                    : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                {{ __('Active Users') }}
            </button>
            <button wire:click="$set('statusFilter', 'inactive')"
                class="px-3 py-1.5 text-sm font-medium rounded transition-colors
                {{ $statusFilter === 'inactive'
                    ? 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300'
                    : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                {{ __('Inactive Users') }}
            </button>
            <button wire:click="$set('statusFilter', 'all')"
                class="px-3 py-1.5 text-sm font-medium rounded transition-colors
                {{ $statusFilter === 'all'
                    ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300'
                    : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                {{ __('All Users') }}
            </button>
        </div>
    </div>

    <x-data-table :data="$this->rows" :headers="$dataTable['headers']" :showActions="$dataTable['showActions']" :showSearch="$dataTable['showSearch']" :showCreate="$dataTable['showCreate']"
        :createRoute="$dataTable['createRoute']" :createButtonName="$dataTable['createButtonName']" :editRoute="$dataTable['editRoute']" :viewRoute="$dataTable['viewRoute']" :deleteAction="$dataTable['deleteAction']" :searchPlaceholder="$dataTable['searchPlaceholder']"
        :emptyMessage="$dataTable['emptyMessage']" :searchQuery="$search" :sortColumn="$sortColumn" :sortDirection="$sortDirection" :showBulkActions="$dataTable['showBulkActions']"
        :bulkDeleteAction="$dataTable['bulkDeleteAction']" :selectedRowsCount="$selectedRowsCount" :selectAll="$selectAll" :selectPage="$selectPage" :selectedRows="$selectedRows" />
</div>
