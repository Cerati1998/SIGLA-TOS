import Swal from 'sweetalert2';

const toastStyles = {
    success: {
        iconColor: 'var(--color-status-green)',
        popup: 'bg-badge-success! ring-status-green/40!',
        title: 'text-badge-success-ink!',
        timerProgressBar: 'bg-status-green!',
    },
    warning: {
        iconColor: 'var(--color-highlight)',
        popup: 'bg-badge-warning! ring-highlight/30!',
        title: 'text-badge-warning-ink!',
        timerProgressBar: 'bg-highlight!',
    },
    error: {
        iconColor: 'var(--color-danger)',
        popup: 'bg-badge-danger! ring-danger/30!',
        title: 'text-badge-danger-ink!',
        timerProgressBar: 'bg-danger!',
    },
};

/**
 * Show the toasts that the Livewire components dispatch with SweetAlert2.
 */
window.addEventListener('toast', ({ detail }) =>
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: detail.icon,
        iconColor: toastStyles[detail.icon]?.iconColor,
        titleText: detail.text,
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
        returnFocus: false,
        customClass: {
            container: 'w-100! max-w-full!',
            popup: `w-full! rounded-control! px-5! py-4! shadow-xl! ring-1! ${toastStyles[detail.icon]?.popup ?? 'ring-line!'}`,
            title: `text-base! font-medium! ${toastStyles[detail.icon]?.title ?? 'text-ink!'}`,
            timerProgressBar: toastStyles[detail.icon]?.timerProgressBar ?? 'bg-brand!',
        },
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        },
    }),
);
