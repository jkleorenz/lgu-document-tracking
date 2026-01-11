import './bootstrap';
import 'bootstrap';

// Auto-dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});

// Notification auto-refresh
if (document.querySelector('.notification-badge')) {
    setInterval(function() {
        fetch('/api/notifications/unread-count')
            .then(response => response.json())
            .then(data => {
                const badge = document.querySelector('.notification-badge');
                if (badge) {
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = 'inline';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            })
            .catch(error => console.error('Error fetching notifications:', error));
    }, 30000); // Every 30 seconds
}

// Confirm delete actions
document.querySelectorAll('form[method="POST"]').forEach(form => {
    if (form.querySelector('input[name="_method"][value="DELETE"]')) {
        form.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to delete this item?')) {
                e.preventDefault();
            }
        });
    }
});

// Add active class to current navigation item
const currentPath = window.location.pathname;
document.querySelectorAll('.sidebar .nav-link').forEach(link => {
    if (link.getAttribute('href') === currentPath) {
        link.classList.add('active');
    }
});

// Print functionality for QR codes
if (window.location.pathname.includes('print-qr')) {
    window.onload = function() {
        // Auto-print is optional, commented out by default
        // window.print();
    };
}

// Initialize tooltips
const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

