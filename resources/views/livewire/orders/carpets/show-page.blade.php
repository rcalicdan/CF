<div x-data="printManager()" class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        @include('contents.order-carpets.header')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                @include('contents.order-carpets.photos-section')
                @include('contents.order-carpets.services-section')
                @include('contents.order-carpets.remarks-section')
                @include('contents.order-carpets.history-section') 
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                @include('contents.order-carpets.qr-code-section')
                @include('contents.order-carpets.order-info-section')
                @include('contents.order-carpets.measurements-section')
                @include('contents.order-carpets.actions-section')
            </div>
        </div>
    </div>

    {{-- modals --}}
    @include('contents.order-carpets.photos-upload-modal')
    @include('contents.order-carpets.photo-viewer-modal')
    @include('contents.order-carpets.print-modal')
    @include('contents.order-carpets.print-template')
</div>


<script>
    function printManager() {
        return {
            isPrinting: false,
            showPhotoUploadModal: false,
            printCopies: 1,

            isPhotoViewerOpen: false,
            viewingPhotoUrl: '',

            init() {
                window.addEventListener('photos-uploaded', () => {
                    this.showPhotoUploadModal = false;
                });
            },

            openPhotoViewer(url) {
                this.viewingPhotoUrl = url;
                this.isPhotoViewerOpen = true;
            },

            closePhotoViewer() {
                this.isPhotoViewerOpen = false;
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

            printQrCode() {
                const copies = parseInt(this.printCopies) || 1;
                const template = document.getElementById('qr-code-print-template').innerHTML;
                let printContent = '';

                for (let i = 0; i < copies; i++) {
                    printContent += template;
                }

                const printWindow = window.open('', '_blank');

                if (!printWindow) {
                    alert('Please allow popups to print the QR code.');
                    return;
                }

                printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Print QR Code - Carpet #{{ $orderCarpet->id }}</title>
                    <style>
                        * { margin: 0; padding: 0; box-sizing: border-box; }
                        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: white; padding: 20px; }
                        @media print {
                            body { padding: 0; -webkit-print-color-adjust: exact; color-adjust: exact; }
                            @page { margin: 0.5in; size: auto; }
                        }
                        .print-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; max-width: 100%; }
                        .print-item { border: 2px dashed #e5e7eb; border-radius: 12px; padding: 20px; text-align: center; background: #fafafa; page-break-inside: avoid; min-height: 300px; display: flex; flex-direction: column; justify-content: center; align-items: center; }
                        .qr-container { background: white; padding: 16px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 16px; }
                        .qr-container img { width: 150px; height: 150px; display: block; max-width: 100%; }
                        .carpet-info h3 { font-size: 18px; font-weight: bold; color: #1f2937; margin-bottom: 8px; }
                        .carpet-info p { font-size: 14px; color: #6b7280; margin-bottom: 4px; }
                        .carpet-info p:first-of-type { font-weight: 600; color: #4f46e5; }
                        @media print {
                            .print-item { background: white !important; border: 2px solid #d1d5db !important; break-inside: avoid; }
                            .qr-container { box-shadow: none; border: 1px solid #e5e7eb; }
                        }
                    </style>
                </head>
                <body>
                    <div class='print-container'>
                        ${printContent}
                    </div>
                </body>
                </html>
            `);

                printWindow.document.close();

                printWindow.addEventListener('load', function() {
                    setTimeout(() => {
                        printWindow.focus();
                        printWindow.print();
                        setTimeout(() => {
                            printWindow.close();
                        }, 1000);
                    }, 500);
                });

                this.isPrinting = false;
            }
        }
    }
</script>
