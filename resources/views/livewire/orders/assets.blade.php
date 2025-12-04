@push('styles')
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.3s ease-out;
        }

        input[type="date"] {
            position: relative;
            cursor: pointer;
        }

        input[type="date"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
            opacity: 0.6;
            transition: opacity 0.2s;
        }

        input[type="date"]:hover::-webkit-calendar-picker-indicator {
            opacity: 1;
        }

        input[type="date"]::-moz-calendar-picker-indicator {
            cursor: pointer;
            opacity: 0.6;
            transition: opacity 0.2s;
        }

        input[type="date"]:hover::-moz-calendar-picker-indicator {
            opacity: 1;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('livewire:navigated', function() {
            const startDateInput = document.getElementById('customStartDate');
            const endDateInput = document.getElementById('customEndDate');

            if (startDateInput && endDateInput) {
                startDateInput.addEventListener('change', function() {
                    if (this.value && endDateInput.value && this.value > endDateInput.value) {
                        endDateInput.value = this.value;
                        @this.set('customEndDate', this.value);
                    }
                    endDateInput.min = this.value;
                });

                endDateInput.addEventListener('change', function() {
                    if (this.value && startDateInput.value && this.value < startDateInput.value) {
                        startDateInput.value = this.value;
                        @this.set('customStartDate', this.value);
                    }
                });
            }
        });
    </script>
@endpush