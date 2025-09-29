<style>
    /* Location Marker Styles */
    .location-marker-container {
        background: transparent !important;
        border: none !important;
    }

    .location-marker-wrapper {
        position: relative;
        transform-origin: center bottom;
    }

    .location-pulse {
        position: absolute;
        top: -10px;
        left: -10px;
        width: 60px;
        height: 60px;
        border: 3px solid #ef4444;
        border-radius: 50%;
        opacity: 0.6;
        animation: pulse 2s infinite;
    }

    .location-marker {
        position: relative;
        width: 40px;
        height: 40px;
        background: #ef4444;
        border: 3px solid white;
        border-radius: 50% 50% 50% 0;
        transform: rotate(-45deg);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .location-marker:hover {
        transform: rotate(-45deg) scale(1.1);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.4);
    }

    .location-marker svg {
        transform: rotate(45deg);
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
            opacity: 0.6;
        }

        50% {
            transform: scale(1.2);
            opacity: 0.3;
        }

        100% {
            transform: scale(1);
            opacity: 0.6;
        }
    }

    /* Custom On-Hover Tooltip Styles */
    .custom-address-tooltip {
        background: transparent;
        border: none;
        box-shadow: none;
        padding: 0;
    }

    .custom-address-tooltip .leaflet-tooltip-content {
        padding: 0;
    }

    .custom-address-tooltip .leaflet-tooltip-tip-container {
        display: none;
        /* Hides the default tooltip arrow */
    }

    .address-tooltip-content {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        min-width: 250px;
        max-width: 300px;
    }

    .tooltip-header {
        color: #1f2937;
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 8px;
        border-bottom: 1px solid #f3f4f6;
        padding-bottom: 8px;
    }

    .tooltip-address {
        color: #6b7280;
        font-size: 13px;
        line-height: 1.4;
        margin-bottom: 10px;
        word-break: break-word;
    }

    .tooltip-contact {
        display: flex;
        align-items: center;
        color: #3b82f6;
        font-size: 12px;
        font-weight: 500;
    }

    .tooltip-contact svg {
        margin-right: 6px;
    }

    /* Poland Label Styles */
    .poland-label {
        background: transparent !important;
        border: none !important;
    }

    .poland-label-content {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        padding: 12px 20px;
        border-radius: 16px;
        text-align: center;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        border: 3px solid white;
    }

    .poland-title {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 2px;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }

    .poland-subtitle {
        font-size: 11px;
        opacity: 0.9;
        font-weight: 500;
    }

    /* Custom On-Click Popup Styles */
    .custom-location-popup .leaflet-popup-content-wrapper {
        padding: 0;
        border-radius: 16px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        border: 1px solid #e5e7eb;
    }

    .custom-location-popup .leaflet-popup-tip {
        border-top-color: white;
    }

    .location-popup {
        font-family: inherit;
        max-width: 100%;
    }

    .popup-header {
        background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
        color: white;
        padding: 16px 20px;
        border-radius: 16px 16px 0 0;
    }

    .client-info h3 {
        margin: 0 0 4px 0;
        font-size: 18px;
        font-weight: 700;
    }

    .client-meta {
        display: flex;
        align-items: center;
        font-size: 12px;
        opacity: 0.9;
    }

    .client-meta svg {
        margin-right: 6px;
        width: 14px;
        height: 14px;
    }

    .popup-body {
        padding: 20px;
    }

    .address-section,
    .contact-section {
        margin-bottom: 16px;
    }

    .section-label {
        display: flex;
        align-items: center;
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .section-label svg {
        margin-right: 6px;
        width: 14px;
        height: 14px;
    }

    .section-content {
        font-size: 14px;
        color: #374151;
        line-height: 1.4;
    }

    .phone-link {
        color: #3b82f6;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s ease;
    }

    .phone-link:hover {
        color: #1d4ed8;
    }

    .popup-actions {
        border-top: 1px solid #f3f4f6;
        padding: 16px 20px;
        background: #f9fafb;
        border-radius: 0 0 16px 16px;
    }

    .directions-btn {
        display: inline-flex;
        align-items: center;
        background: #3b82f6;
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .directions-btn:hover {
        background: #1d4ed8;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        color: white;
    }

    .directions-btn svg {
        margin-right: 6px;
        width: 16px;
        height: 16px;
    }

    /* Responsive Design */
    @media (max-width: 640px) {
        .address-tooltip-content {
            min-width: 240px;
            max-width: 280px;
            padding: 12px;
        }

        .location-popup {
            max-width: 280px;
        }

        .popup-header {
            padding: 12px 16px;
        }

        .popup-body {
            padding: 16px;
        }

        .popup-actions {
            padding: 12px 16px;
        }
    }
</style>
