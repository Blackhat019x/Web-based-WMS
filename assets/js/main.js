// Barcode Scanner Handler
class BarcodeScanner {
    constructor(inputId, onScan) {
        this.input = document.getElementById(inputId);
        this.onScan = onScan;
        this.buffer = '';
        this.timeout = null;
        this.init();
    }

    init() {
        if (this.input) {
            this.input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.processScan();
                } else {
                    this.buffer += e.key;
                    clearTimeout(this.timeout);
                    this.timeout = setTimeout(() => {
                        this.buffer = '';
                    }, 100);
                }
            });

            this.input.focus();
        }
    }

    processScan() {
        const barcode = this.buffer.trim() || this.input.value.trim();
        if (barcode) {
            this.onScan(barcode);
            this.buffer = '';
            this.input.value = '';
        }
    }
}

// Auto-focus scanner input
function focusScannerInput(inputId) {
    const input = document.getElementById(inputId);
    if (input) {
        input.focus();
        setInterval(() => {
            if (document.activeElement !== input) {
                input.focus();
            }
        }, 1000);
    }
}

// Show notification
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'error'}`;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// AJAX Helper
function ajax(url, method, data, callback) {
    const xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                callback(null, response);
            } catch (e) {
                callback(null, { success: true, data: xhr.responseText });
            }
        } else {
            callback(new Error('Request failed'), null);
        }
    };
    
    xhr.onerror = function() {
        callback(new Error('Network error'), null);
    };
    
    if (data) {
        xhr.send(JSON.stringify(data));
    } else {
        xhr.send();
    }
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

// Print Label
function printLabel(content) {
    const printWindow = window.open('', '', 'width=400,height=300');
    printWindow.document.write(`
        <html>
        <head>
            <title>Print Label</title>
            <style>
                body { font-family: monospace; padding: 20px; }
                .label { border: 2px solid black; padding: 10px; }
            </style>
        </head>
        <body>
            <div class="label">
                ${content}
            </div>
            <script>
                window.onload = function() {
                    window.print();
                    window.close();
                }
            </script>
        </body>
        </html>
    `);
    printWindow.document.close();
}
