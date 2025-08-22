
@push('styles')
<style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in {
        animation: fadeIn 0.3s ease-out;
    }

    [x-show] {
        transition: all 0.3s ease-in-out;
    }

    .custom-scrollbar {
        scrollbar-width: thin;
        scrollbar-color: #cbd5e0 #f7fafc;
    }

    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f7fafc;
        border-radius: 3px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 3px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #a0aec0;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('livewire:load', function() {
        window.addEventListener('livewire:update', function() {
            document.querySelector('[x-show]')?.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Tab' && e.ctrlKey) {
                e.preventDefault();
                const tabs = ['overview', 'orders', 'carpets'];
                const currentIndex = tabs.indexOf(@this.activeTab);
                const nextIndex = (currentIndex + 1) % tabs.length;
                @this.setActiveTab(tabs[nextIndex]);
            }
        });
    });
</script>
@endpush
