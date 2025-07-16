<?php
/**
 * Shared Dashboard Styles
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function careers_get_dashboard_styles() {
    return '
    <style>
    .careers-dashboard-container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 2rem 0;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        color: #333;
    }
    .careers-dashboard-container * {
        box-sizing: border-box;
    }
    .careers-dashboard-container a {
        text-decoration: none;
    }
    .careers-dashboard-container a:hover {
        text-decoration: none;
    }
    .careers-dashboard-container .dashboard-header {
        margin-bottom: 3rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid #eee;
    }
    .careers-dashboard-container .dashboard-title {
        font-size: 2.5rem;
        font-weight: 500;
        margin: 0 0 0.5rem 0;
        line-height: 1.2;
        color: #111;
    }
    .careers-dashboard-container .dashboard-subtitle {
        color: #666;
        margin: 0;
        font-size: 1rem;
    }
    .careers-dashboard-container .dashboard-tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 2rem;
        background: #f5f5f5;
        padding: 0.25rem;
        border-radius: 4px;
        width: fit-content;
    }
    .careers-dashboard-container .dashboard-tab {
        padding: 0.75rem 1.5rem;
        background: transparent;
        border: none;
        border-radius: 4px;
        font-size: 0.875rem;
        font-weight: 500;
        color: #666;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .careers-dashboard-container .dashboard-tab.active {
        background: #fff;
        color: #111;
    }
    .careers-dashboard-container .tab-content {
        display: none;
    }
    .careers-dashboard-container .tab-content.active {
        display: block;
    }
    .careers-dashboard-container .action-btn {
        padding: 0.5rem 1rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        display: inline-block;
        transition: all 0.2s ease;
        background: #fff;
        color: #333;
        cursor: pointer;
    }
    .careers-dashboard-container .action-btn:hover {
        background: #f5f5f5;
        color: #333;
        text-decoration: none;
    }
    .careers-dashboard-container .action-btn.primary {
        background: #000;
        color: white;
        border-color: #000;
    }
    .careers-dashboard-container .action-btn.primary:hover {
        background: #333;
        color: white;
    }
    .careers-dashboard-container .action-btn.danger {
        background: #dc2626;
        color: white;
        border-color: #dc2626;
    }
    .careers-dashboard-container .action-btn.danger:hover {
        background: #b91c1c;
        color: white;
    }
    .careers-dashboard-container .dashboard-actions {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }
    .careers-dashboard-container .dashboard-action-btn {
        background: #000;
        color: white;
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 4px;
        font-size: 1rem;
        font-weight: 500;
        text-decoration: none;
        display: inline-block;
        transition: background 0.2s ease;
    }
    .careers-dashboard-container .dashboard-action-btn:hover {
        background: #333;
        color: white;
        text-decoration: none;
    }
    .careers-dashboard-container .dashboard-action-btn.secondary {
        background: #f5f5f5;
        color: #333;
        border: 1px solid #ddd;
    }
    .careers-dashboard-container .dashboard-action-btn.secondary:hover {
        background: #e8e8e8;
        color: #333;
    }
    .careers-dashboard-container .section-title {
        font-size: 1.25rem;
        font-weight: 500;
        color: #111;
        margin: 0 0 1.5rem 0;
    }
    .careers-dashboard-container .empty-state {
        text-align: center;
        padding: 3rem;
        color: #666;
    }
    .careers-dashboard-container .empty-state h3 {
        font-size: 1.125rem;
        font-weight: 500;
        color: #111;
        margin: 0 0 0.5rem 0;
    }
    .careers-dashboard-container .filter-button {
        padding: 0.5rem 1rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        display: inline-block;
        transition: all 0.2s ease;
        background: #fff;
        color: #333;
        cursor: pointer;
    }
    .careers-dashboard-container .filter-button:hover {
        background: #f5f5f5;
        color: #333;
        text-decoration: none;
    }
    .careers-dashboard-container .filter-button.active {
        background: #000;
        color: white;
        border-color: #000;
    }
    .careers-dashboard-container .create-button {
        background: #000;
        color: white;
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 4px;
        font-size: 1rem;
        font-weight: 500;
        text-decoration: none;
        display: inline-block;
        transition: background 0.2s ease;
    }
    .careers-dashboard-container .create-button:hover {
        background: #333;
        color: white;
        text-decoration: none;
    }
    .careers-dashboard-container .create-button.secondary {
        background: #f5f5f5;
        color: #333;
        border: 1px solid #ddd;
    }
    .careers-dashboard-container .create-button.secondary:hover {
        background: #e8e8e8;
        color: #333;
    }
    
    /* Form styles */
    .careers-dashboard-container .form-group {
        margin-bottom: 1.5rem;
    }
    .careers-dashboard-container .form-label {
        display: block;
        font-weight: 500;
        margin-bottom: 0.5rem;
        color: #111;
    }
    .careers-dashboard-container .form-input {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 1rem;
        font-family: inherit;
    }
    .careers-dashboard-container .form-input:focus {
        outline: none;
        border-color: #000;
        box-shadow: 0 0 0 2px rgba(0,0,0,0.1);
    }
    .careers-dashboard-container .form-textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 1rem;
        font-family: inherit;
        min-height: 120px;
        resize: vertical;
    }
    .careers-dashboard-container .form-textarea:focus {
        outline: none;
        border-color: #000;
        box-shadow: 0 0 0 2px rgba(0,0,0,0.1);
    }
    .careers-dashboard-container .form-select {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 1rem;
        font-family: inherit;
        background: white;
    }
    .careers-dashboard-container .form-select:focus {
        outline: none;
        border-color: #000;
        box-shadow: 0 0 0 2px rgba(0,0,0,0.1);
    }
    
    /* Mobile responsive */
    @media (max-width: 768px) {
        .careers-dashboard-container {
            padding: 1rem;
        }
        .careers-dashboard-container .dashboard-title {
            font-size: 2rem;
        }
        .careers-dashboard-container .dashboard-actions {
            flex-direction: column;
            gap: 0.5rem;
        }
        .careers-dashboard-container .dashboard-action-btn {
            text-align: center;
        }
        .careers-dashboard-container .action-btn {
            flex: 1;
            text-align: center;
            padding: 0.75rem 0.5rem;
            font-size: 0.875rem;
        }
    }
    </style>';
}
?>