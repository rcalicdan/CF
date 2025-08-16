<script>
    function qrCodeManager() {
        return {
            showPrintModal: false,
            printCopies: 1,
            currentPrintQrCode: null,
            selectedPrintQrCodes: [],

            init() {
                Livewire.on('qr-generated', (event) => {
                    this.showAlert(event.message, 'success');
                });

                Livewire.on('qr-deleted', (event) => {
                    this.showAlert(event.message, 'success');
                });

                Livewire.on('qr-error', (event) => {
                    this.showAlert(event.message, 'error');
                });
            },

            showAlert(message, type = 'success') {
                if (type === 'error') {
                    alert('Error: ' + message);
                } else {
                    alert(message);
                }
            },

            openPrintModal() {
                const selectedCodes = @this.selectedQrCodes || [];
                const allCodes = @this.qrCodes || [];

                if (selectedCodes.length === 0) {
                    this.showAlert('No QR codes selected for printing.', 'error');
                    return;
                }

                this.selectedPrintQrCodes = allCodes.filter(qr =>
                    selectedCodes.includes(qr.reference_code)
                );

                if (this.selectedPrintQrCodes.length === 0) {
                    this.showAlert('No valid QR codes found for printing.', 'error');
                    console.error('Selected codes:', selectedCodes);
                    console.error('Available codes:', allCodes.map(qr => qr.reference_code));
                    return;
                }

                this.currentPrintQrCode = null;
                this.showPrintModal = true;
            }

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

            printSingle(referenceCode, qrCodeUrl) {
                this.currentPrintQrCode = {
                    reference_code: referenceCode,
                    qr_code_url: qrCodeUrl
                };
                this.selectedPrintQrCodes = [];
                this.showPrintModal = true;
            },

            executeQrCodePrint() {
                const copies = parseInt(this.printCopies) || 1;
                let printContent = '';

                if (this.currentPrintQrCode) {
                    // Single QR code
                    for (let i = 0; i < copies; i++) {
                        printContent += this.generateQrCodeTemplate(this.currentPrintQrCode);
                    }
                } else if (this.selectedPrintQrCodes.length > 0) {
                    // Multiple QR codes
                    this.selectedPrintQrCodes.forEach(qrCode => {
                        for (let i = 0; i < copies; i++) {
                            printContent += this.generateQrCodeTemplate(qrCode);
                        }
                    });
                } else {
                    this.showAlert('No QR codes to print.', 'error');
                    return;
                }

                const printWindow = window.open('', '_blank');

                if (!printWindow) {
                    this.showAlert('Please allow popups to print the QR codes.', 'error');
                    return;
                }

                printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Print QR Codes</title>
                    <style>
                        * { margin: 0; padding: 0; box-sizing: border-box; }
                        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: white; padding: 20px; }
                        @media print {
                            body { padding: 0; -webkit-print-color-adjust: exact; color-adjust: exact; }
                            @page { margin: 0.5in; size: auto; }
                        }
                        .print-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px; max-width: 100%; }
                        .print-item { border: 2px dashed #e5e7eb; border-radius: 12px; padding: 20px; text-align: center; background: #fafafa; page-break-inside: avoid; min-height: 320px; display: flex; flex-direction: column; justify-content: center; align-items: center; }
                        .qr-container { background: white; padding: 16px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 16px; }
                        .qr-container img { width: 150px; height: 150px; display: block; max-width: 100%; }
                        .qr-info h3 { font-size: 18px; font-weight: bold; color: #1f2937; margin-bottom: 8px; }
                        .qr-info p { font-size: 14px; color: #6b7280; margin-bottom: 4px; }
                        .qr-info p.reference { font-weight: 600; color: #4f46e5; font-size: 16px; }
                        .qr-info p.type { color: #059669; font-weight: 500; }
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

                this.showPrintModal = false;
            },

            printSelected() {
                this.openPrintModal();
            },

            generateQrCodeTemplate(qrCode) {
                return `
                <div class='print-item'>
                    <div class='qr-container'>
                        <img src='${qrCode.qr_code_url}' alt='${qrCode.reference_code}' />
                    </div>
                    <div class='qr-info'>
                        <h3>QR Code</h3>
                        <p class='reference'>${qrCode.reference_code}</p>
                        <p class='type'>Standalone QR Code</p>
                        <p>Generated: ${new Date().toLocaleDateString()}</p>
                    </div>
                </div>
            `;
            }
        }
    }

    document.addEventListener('livewire:init', () => {
        Livewire.on('qr-generated', (event) => {
            console.log('QR Generated:', event[0].message);
        });

        Livewire.on('qr-deleted', (event) => {
            console.log('QR Deleted:', event[0].message);
        });

        Livewire.on('qr-error', (event) => {
            console.error('QR Error:', event[0].message);
        });
    });
</script>
