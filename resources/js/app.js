import './bootstrap';
import 'bootstrap';
import Swal from 'sweetalert2';

const SWAL_TIMER_MS = 5000;

const swalTheme = Swal.mixin({
    confirmButtonColor: '#2563eb',
    cancelButtonColor: '#64748b',
    reverseButtons: true,
    buttonsStyling: true,
    showClass: {
        popup: 'animate__animated animate__fadeInDown'
    },
    hideClass: {
        popup: 'animate__animated animate__fadeOutUp'
    }
});

window.SwalHelper = {
    timer: SWAL_TIMER_MS,

    success({ title = 'Success', text = '' } = {}) {
        return swalTheme.fire({
            icon: 'success',
            title,
            text,
            timer: SWAL_TIMER_MS,
            timerProgressBar: true,
            showConfirmButton: true,
            confirmButtonText: 'OK',
            position: 'center'
        });
    },

    error({ title = 'Error', text = '' } = {}) {
        return swalTheme.fire({
            icon: 'error',
            title,
            text,
            confirmButtonText: 'OK',
            position: 'center'
        });
    },

    warning({ title = 'Warning', text = '' } = {}) {
        return swalTheme.fire({
            icon: 'warning',
            title,
            text,
            confirmButtonText: 'OK',
            position: 'center'
        });
    },

    confirm({
        title = 'Are you sure?',
        text = 'Please confirm to continue.',
        confirmButtonText = 'Yes, continue',
        cancelButtonText = 'Cancel',
        icon = 'question'
    } = {}) {
        return swalTheme.fire({
            icon,
            title,
            text,
            showCancelButton: true,
            confirmButtonText,
            cancelButtonText,
            focusCancel: true,
            position: 'center'
        });
    },

    validationError(errors = []) {
        const items = errors.map((error) => `<li>${error}</li>`).join('');
        return swalTheme.fire({
            icon: 'error',
            title: 'Validation Error',
            html: items ? `<ul class="text-start mb-0">${items}</ul>` : 'Please review your input and try again.',
            confirmButtonText: 'OK',
            position: 'center'
        });
    }
};

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

// Session flash to SweetAlert2
document.addEventListener('DOMContentLoaded', function() {
    const flashDataElement = document.getElementById('swal-flash-data');
    if (!flashDataElement) {
        return;
    }

    let payload;
    try {
        payload = JSON.parse(flashDataElement.textContent || '{}');
    } catch (error) {
        console.error('Failed to parse swal flash payload:', error);
        return;
    }

    if (Array.isArray(payload.validationErrors) && payload.validationErrors.length > 0) {
        window.SwalHelper.validationError(payload.validationErrors);
        return;
    }

    if (payload.error) {
        window.SwalHelper.error({
            title: 'Action Failed',
            text: payload.error
        });
        return;
    }

    if (payload.success) {
        window.SwalHelper.success({
            title: 'Success',
            text: payload.success
        });
    }
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
    const deleteMethod = form.querySelector('input[name="_method"][value="DELETE"]');
    const requiresConfirmation = Boolean(deleteMethod || form.dataset.swalConfirm === 'true');
    if (!requiresConfirmation) {
        return;
    }

    form.addEventListener('submit', async function(e) {
        if (form.dataset.swalConfirmed === 'true') {
            return;
        }

        e.preventDefault();

        const result = await window.SwalHelper.confirm({
            title: form.dataset.swalTitle || 'Are you sure?',
            text: form.dataset.swalText || 'Please confirm to continue.',
            confirmButtonText: form.dataset.swalConfirmText || 'Yes, continue',
            cancelButtonText: form.dataset.swalCancelText || 'Cancel',
            icon: form.dataset.swalIcon || (deleteMethod ? 'warning' : 'question')
        });

        if (result.isConfirmed) {
            form.dataset.swalConfirmed = 'true';
            form.submit();
            return;
        }

        if (form.dataset.swalShowCancelMessage === 'true') {
            window.SwalHelper.warning({
                title: form.dataset.swalCancelTitle || 'Cancelled',
                text: form.dataset.swalCancelText || 'No changes were made.'
            });
        }
    });
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

