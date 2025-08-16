<div x-data="qrCodeManager()" x-init="init()">
    <x-flash-session />
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('QR Code Generator') }}</h1>
                <p class="mt-2 text-gray-600">{{ __('Generate and manage standalone QR codes') }}</p>
            </div>
            <div class="flex space-x-4">
                <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3">
                    <div class="text-2xl font-bold text-gray-600">{{ $totalAll }}</div>
                    <div class="text-sm text-gray-800">{{ __('Total') }}</div>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3">
                    <div class="text-2xl font-bold text-blue-600">{{ $totalUnassigned }}</div>
                    <div class="text-sm text-blue-800">{{ __('Unassigned') }}</div>
                </div>
                <div class="bg-green-50 border border-green-200 rounded-lg px-4 py-3">
                    <div class="text-2xl font-bold text-green-600">{{ $totalAssigned }}</div>
                    <div class="text-sm text-green-800">{{ __('Assigned') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    @include('contents.qr.action-buttons')

    <!-- Filters and Search -->
    @include('contents.qr.filters')

    <!-- QR Codes Grid -->
    @if ($hasQrCodes)
        <div class="mb-6">
            <div class="mb-4 flex items-center justify-between bg-gray-50 rounded-lg p-4">
                <div class="flex items-center space-x-4">
                    <label class="flex items-center">
                        <input type="checkbox" wire:model.live="selectAll" wire:change="toggleSelectAll"
                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">{{ __('Select All') }}</span>
                    </label>
                    @if (count($selectedQrCodes) > 0)
                        <span class="text-sm text-gray-600">{{ count($selectedQrCodes) }} {{ __('selected') }}</span>
                    @endif
                </div>
                <div class="text-sm text-gray-600">
                    {{ __('Showing :from to :to of :total QR codes', ['from' => $from, 'to' => $to, 'total' => $total]) }}
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($qrCodes as $qrCode)
                    <div
                        class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200">
                        <div class="p-3 border-b border-gray-100">
                            <div class="flex items-center justify-between">
                                <label class="flex items-center">
                                    <input type="checkbox" value="{{ $qrCode['reference_code'] }}"
                                        wire:model.live="selectedQrCodes"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span
                                        class="ml-2 text-sm font-medium text-gray-700">{{ $qrCode['reference_code'] }}</span>
                                </label>
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $qrCode['is_assigned'] ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $qrCode['is_assigned'] ? __('Assigned') : __('Available') }}
                                </span>
                            </div>
                        </div>
                        <div class="p-4 text-center">
                            <div class="inline-block p-2 bg-gray-50 rounded-lg">
                                <img src="{{ 'storage/' . $qrCode['qr_code_path'] }}"
                                    alt="{{ $qrCode['reference_code'] }}" class="w-24 h-24 object-contain">
                            </div>
                        </div>
                        @if ($qrCode['is_assigned'])
                            @if (isset($qrCode['assigned_info']) && $qrCode['assigned_info'])
                                <div class="px-4 pb-2">
                                    <p class="text-green-600 text-sm">
                                        {{ __('Assigned to Carpet #:id', ['id' => $qrCode['assigned_info']['carpet_id']]) }}
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $qrCode['assigned_info']['client_name'] }}</p>
                                </div>
                            @endif
                        @endif
                        <div class="px-4 py-3 bg-gray-50 flex justify-between">
                            <button
                                @click="printSingle('{{ $qrCode['reference_code'] }}', '{{ $qrCode['qr_code_url'] }}')"
                                class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">{{ __('Print') }}</button>
                            @if (!$qrCode['is_assigned'])
                                <button
                                    wire:click="$dispatch('openAssignModal', { qrCodeReference: '{{ $qrCode['reference_code'] }}' })"
                                    class="text-sm text-green-600 hover:text-green-800 font-medium">{{ __('Assign') }}</button>
                                <button wire:click="deleteQrCode('{{ $qrCode['reference_code'] }}')"
                                    wire:confirm="{{ __('Are you sure you want to delete this QR code?') }}"
                                    class="text-sm text-red-600 hover:text-red-800 font-medium">{{ __('Delete') }}</button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($lastPage > 1)
                @include('contents.paginations.qr-code')
            @endif
        </div>
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                    d="M9 12h6M9 16h6M4 6v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('No QR codes') }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ __('Get started by generating your first QR code.') }}</p>
        </div>
    @endif

    <!-- Print Modal -->
    @include('contents.qr.print-modal')

    <!-- Assign Modal -->
    @livewire('qr.assign-modal')
</div>

<script>
    function qrCodeManager() {
        return {
            showPrintModal: false,
            printCopies: 1,

            init() {
                const handlePrint = ({
                    content
                }) => {
                    if (content) {
                        this.printHtml(content);
                    }
                };

                this.$wire.on('print-qr-codes', handlePrint);


                return () => {
                    console.log('Alpine component is being cleaned up.');
                };
            },

            openPrintModal() {
                if (this.$wire.selectedQrCodes.length === 0) {
                    return;
                }
                this.showPrintModal = true;
                this.printCopies = 1;
            },

            incrementCopies() {
                if (this.printCopies < 50) {
                    this.printCopies++;
                }
            },

            decrementCopies() {
                if (this.printCopies > 1) {
                    this.printCopies--;
                }
            },

            printSingle(reference, qrCodeUrl) {
                const singleItemHtml = `
                    <div class="qr-code-item">
                        <img src="${qrCodeUrl}" alt="${reference}" />
                        <p>${reference}</p>
                    </div>`;
                this.printHtml(singleItemHtml);
            },

            printSelected() {
                this.showPrintModal = false;
                this.$wire.printSelected(this.printCopies);
            },

            printHtml(content) {
                const frameId = 'print-frame-' + new Date().getTime();
                const printFrame = document.createElement('iframe');
                printFrame.setAttribute('id', frameId);
                printFrame.setAttribute('style', 'position: absolute; width: 0; height: 0; border: 0;');
                document.body.appendChild(printFrame);

                const frameDoc = printFrame.contentWindow.document;
                frameDoc.open();
                frameDoc.write(`
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Drukuj kody QR</title>
                        <style>
                            @media print {
                                @page { size: A4; margin: 20mm; }
                                body { font-family: sans-serif; display: grid; grid-template-columns: repeat(3, 1fr); gap: 25mm 15mm; align-items: center; justify-items: center; }
                                .qr-code-item { text-align: center; page-break-inside: avoid; }
                                .qr-code-item img { width: 50mm; height: 50mm; display: block; }
                                .qr-code-item p { font-size: 10pt; margin-top: 2mm; font-weight: bold; }
                            }
                        </style>
                    </head>
                    <body>
                        ${content}
                    </body>
                    </html>
                `);
                frameDoc.close();

                const frameWindow = printFrame.contentWindow;

                setTimeout(() => {
                    frameWindow.focus();
                    frameWindow.print();
                    document.body.removeChild(printFrame);
                }, 250);
            }
        };
    }
</script>
