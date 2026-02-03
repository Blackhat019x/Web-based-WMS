// Main JavaScript for Warehouse Management System

$(document).ready(function() {
    // Auto-focus on scan inputs
    $('.scan-input').first().focus();
    
    // Handle Enter key for quick navigation
    $('.scan-input').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            const form = $(this).closest('form');
            if (form.length) {
                form.submit();
            }
        }
    });
    
    // Toast notifications
    window.showToast = function(message, type = 'success') {
        const toastHtml = `
            <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        let toastContainer = $('.toast-container');
        if (toastContainer.length === 0) {
            toastContainer = $('<div class="toast-container position-fixed top-0 end-0 p-3"></div>');
            $('body').append(toastContainer);
        }
        
        const $toast = $(toastHtml);
        toastContainer.append($toast);
        
        const toast = new bootstrap.Toast($toast[0]);
        toast.show();
        
        $toast.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    };
    
    // Confirm dialogs
    $('[data-confirm]').on('click', function(e) {
        const message = $(this).data('confirm');
        if (!confirm(message)) {
            e.preventDefault();
            return false;
        }
    });
    
    // Auto-refresh for dashboard
    if ($('.content-area').hasClass('dashboard')) {
        setInterval(function() {
            location.reload();
        }, 300000); // Refresh every 5 minutes
    }
    
    // Table search functionality
    $('.table-search').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        const table = $(this).data('target');
        
        $(table + ' tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
    
    // Print functionality
    $('.btn-print').on('click', function(e) {
        e.preventDefault();
        window.print();
    });
    
    // Barcode scanner simulation
    let barcodeBuffer = '';
    let barcodeTimeout = null;
    
    $(document).on('keypress', function(e) {
        // Check if user is typing in an input field
        if ($(e.target).is('input, textarea')) {
            return;
        }
        
        // Build barcode buffer
        clearTimeout(barcodeTimeout);
        barcodeBuffer += String.fromCharCode(e.which);
        
        barcodeTimeout = setTimeout(function() {
            if (barcodeBuffer.length > 3) {
                // Assume it's a barcode scan
                const $scanInput = $('.scan-input:visible').first();
                if ($scanInput.length) {
                    $scanInput.val(barcodeBuffer).focus();
                }
            }
            barcodeBuffer = '';
        }, 100);
    });
});

// Format numbers
function formatNumber(number) {
    return new Intl.NumberFormat().format(number);
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

// Validate barcode format
function validateBarcode(barcode) {
    return barcode && barcode.length >= 3;
}

// Loading indicator
function showLoading() {
    const html = `
        <div class="loading-overlay">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    $('body').append(html);
}

function hideLoading() {
    $('.loading-overlay').remove();
}
