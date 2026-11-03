import Swal from 'sweetalert2';

const iconColors = {
    success: 'var(--color-status-green)',
    warning: 'var(--color-highlight)',
    error: 'var(--color-danger)',
};

/**
 * Show the toasts that the Livewire components dispatch with SweetAlert2.
 */
window.addEventListener('toast', ({ detail }) =>
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: detail.icon,
        iconColor: iconColors[detail.icon],
        titleText: detail.text,
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
        returnFocus: false,
        customClass: {
            popup: 'rounded-control! shadow-xl! ring-1! ring-line!',
            title: 'text-sm! font-medium! text-ink!',
            timerProgressBar: 'bg-brand!',
        },
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        },
    }),
);
