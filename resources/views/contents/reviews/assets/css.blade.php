<style>
    :root {
        --color-primary-accent: #6d28d9;
        --color-primary-text: #4c1d95;
        --color-bg-main: #f5f3ff;
        --color-bg-active: #ede9fe;
        --color-secondary-accent: #3b82f6;
        --color-border-soft: #ddd6fe;
    }

    .bg-main {
        background-color: var(--color-bg-main);
    }

    .bg-active {
        background-color: var(--color-bg-active);
    }

    .text-primary-accent {
        color: var(--color-primary-accent);
    }

    .text-primary-text {
        color: var(--color-primary-text);
    }

    .border-soft {
        border-color: var(--color-border-soft);
    }

    .btn-primary {
        background-color: var(--color-primary-accent);
        color: white;
        transition: background-color 0.3s ease-in-out;
    }

    .btn-primary:hover {
        background-color: var(--color-primary-text);
    }

    .btn-secondary {
        background-color: var(--color-secondary-accent);
        color: white;
        transition: background-color 0.3s ease-in-out;
    }

    .btn-secondary:hover {
        opacity: 0.9;
    }

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

    @keyframes bounce {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.2);
        }
    }

    .fade-in {
        animation: fadeIn 0.5s ease-out forwards;
    }

    .bounce-on-click {
        animation: bounce 0.3s ease-in-out;
    }
</style>
