<style>
    :root {
        --color-primary-accent: #6d28d9;
        --color-primary-text: #4c1d95;
        --color-bg-main: #f5f3ff;
        --color-bg-active: #ede9fe;
        --color-secondary-accent: #3b82f6;
        --color-border-soft: #ddd6fe;
        --color-success: #10b981;
        --color-warning: #f59e0b;
    }

    .bg-main {
        background-color: var(--color-bg-main);
        background-image:
            radial-gradient(at 40% 20%, hsla(280, 100%, 70%, 0.05) 0px, transparent 50%),
            radial-gradient(at 80% 0%, hsla(260, 100%, 70%, 0.05) 0px, transparent 50%),
            radial-gradient(at 0% 50%, hsla(240, 100%, 70%, 0.05) 0px, transparent 50%);
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
        background: linear-gradient(135deg, var(--color-primary-accent), var(--color-primary-text));
        color: white;
        transition: all 0.3s ease-in-out;
        box-shadow: 0 4px 15px rgba(109, 40, 217, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(109, 40, 217, 0.4);
    }

    .btn-primary:active {
        transform: translateY(0);
    }

    .btn-secondary {
        background-color: var(--color-secondary-accent);
        color: white;
        transition: background-color 0.3s ease-in-out;
    }

    .btn-secondary:hover {
        opacity: 0.9;
    }

    .input-field {
        transition: all 0.3s ease-in-out;
        border: 2px solid var(--color-border-soft);
    }

    .input-field:focus {
        outline: none;
        border-color: var(--color-primary-accent);
        box-shadow: 0 0 0 3px rgba(109, 40, 217, 0.1);
        transform: scale(1.01);
    }

    .input-field:hover {
        border-color: var(--color-primary-accent);
    }

    .card-container {
        background: linear-gradient(145deg, #ffffff, #f8fafc);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .star-rating {
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-15px);
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

    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in {
        animation: fadeIn 0.5s ease-out forwards;
    }

    .bounce-on-click {
        animation: bounce 0.3s ease-in-out;
    }

    .slide-in-up {
        animation: slideInUp 0.4s ease-out forwards;
    }

    .rating-text {
        transition: all 0.3s ease-in-out;
        opacity: 0;
        transform: translateY(8px);
    }

    .rating-text.show {
        opacity: 1;
        transform: translateY(0);
    }

    .form-group {
        position: relative;
        margin-bottom: 1.25rem;
    }

    .floating-label {
        position: absolute;
        left: 12px;
        top: 12px;
        background: white;
        padding: 0 4px;
        color: #9ca3af;
        transition: all 0.3s ease;
        pointer-events: none;
        font-size: 14px;
    }

    .input-field:focus+.floating-label,
    .input-field:not(:placeholder-shown)+.floating-label {
        top: -8px;
        font-size: 12px;
        color: var(--color-primary-accent);
        font-weight: 500;
    }

    .success-icon {
        animation: bounce 0.6s ease-in-out;
    }
</style>
