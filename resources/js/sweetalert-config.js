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