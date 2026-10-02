import Swal from 'sweetalert2';

window.Swal = Swal;

Swal.mixin({
    customClass: {
        popup: 'rounded-4',
        confirmButton: 'btn btn-primary',
        cancelButton: 'btn btn-outline-secondary',
    },
    buttonsStyling: false,
});

document.addEventListener('submit', (event) => {
    const form = event.target.closest('form[data-confirm]');

    if (! form || event.defaultPrevented) {
        return;
    }

    event.preventDefault();

    Swal.fire({
        title: 'Konfirmasi',
        text: form.getAttribute('data-confirm'),
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            HTMLFormElement.prototype.submit.call(form);
        }
    });
});