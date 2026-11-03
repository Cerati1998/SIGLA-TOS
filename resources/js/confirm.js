import Swal from 'sweetalert2';

/**
 * Ask for confirmation with SweetAlert2 and run the callback when it is accepted.
 */
window.confirmAction = (title, text, onConfirm) =>
    Swal.fire({
        titleText: title,
        text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        buttonsStyling: false,
        customClass: {
            popup: 'rounded-card!',
            title: 'font-heading! font-normal! text-h3! text-ink!',
            htmlContainer: 'text-base! text-ink-muted!',
            actions: 'gap-3',
            confirmButton: 'h-10.25 cursor-pointer rounded-control bg-brand px-4 text-base text-white max-md:h-11',
            cancelButton: 'h-10.25 cursor-pointer rounded-control border border-line bg-white px-4 text-base text-ink max-md:h-11',
        },
    }).then((result) => {
        if (result.isConfirmed) {
            onConfirm();
        }
    });
