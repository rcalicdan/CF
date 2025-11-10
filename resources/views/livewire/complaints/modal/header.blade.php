<div class="flex justify-between items-start mb-4">
    <div>
        <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">
            {{ $modalTitle }}
        </h3>
        <p class="mt-1 text-sm text-gray-500">
            Łącznie: {{ $this->getTotalOrdersCount() }} zamówień
        </p>
    </div>
    <button @click="showModal = false" type="button"
        class="text-gray-400 hover:text-gray-500 transition-colors">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>