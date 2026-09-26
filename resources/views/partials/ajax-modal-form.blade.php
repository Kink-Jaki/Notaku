@push('scripts')
<script>
    (function () {
        function clearErrors(form) {
            form.querySelectorAll('.is-invalid').forEach(function (el) {
                el.classList.remove('is-invalid');
            });
            form.querySelectorAll('[data-error-for]').forEach(function (el) {
                el.textContent = '';
            });
        }

        function showErrors(form, errors) {
            var unmatched = [];

            Object.keys(errors || {}).forEach(function (name) {
                var input = form.querySelector('[name="' + name + '"]:not([type="hidden"])') || form.querySelector('[name="' + name + '"]');
                var feedback = form.querySelector('[data-error-for="' + name + '"]');
                var message = Array.isArray(errors[name]) ? errors[name][0] : errors[name];

                if (input) {
                    input.classList.add('is-invalid');
                }

                if (feedback) {
                    feedback.textContent = message;
                } else {
                    unmatched.push(message);
                }
            });

            if (unmatched.length) {
                Swal.fire({ icon: 'error', title: 'Validasi Gagal', text: unmatched.join(' ') });
            }

            var firstInvalid = form.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.focus();
            }
        }

        function fill(form, payload) {
            Object.keys(payload || {}).forEach(function (name) {
                var input = form.querySelector('[name="' + name + '"]');

                if (!input || input.type === 'file') {
                    return;
                }

                var value = payload[name];

                if (input.type === 'checkbox') {
                    input.checked = value === true || value === 1 || value === '1';
                } else {
                    input.value = value === null || value === undefined ? '' : value;
                }
            });
        }

        function open(form, trigger, onSetup) {
            var mode = trigger.getAttribute('data-mode') || 'create';
            var rawPayload = trigger.getAttribute('data-payload');
            var payload = null;

            if (rawPayload) {
                try {
                    payload = JSON.parse(rawPayload);
                } catch (e) {
                    payload = null;
                }
            }

            clearErrors(form);
            form.reset();

            var targetUrl = trigger.getAttribute('data-url');
            if (targetUrl) {
                form.setAttribute('action', targetUrl);
            }

            var methodInput = form.querySelector('input[name="_method"]');

            if (mode === 'edit') {
                if (!methodInput) {
                    methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    form.appendChild(methodInput);
                }
                methodInput.value = 'PUT';
            } else if (methodInput) {
                methodInput.remove();
            }

            fill(form, payload);

            form.dataset.mode = mode;

            if (typeof onSetup === 'function') {
                onSetup({ mode: mode, payload: payload });
            }
        }

        function submit(form, options) {
            options = options || {};

            if (form.dataset.submitting === '1') {
                return;
            }

            if (typeof options.beforeSubmit === 'function' && options.beforeSubmit() === false) {
                return;
            }

            clearErrors(form);
            form.dataset.submitting = '1';

            var button = form.querySelector('[data-submit]');
            var originalHtml = button ? button.innerHTML : '';

            if (button) {
                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
            }

            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then(function (response) {
                    return response.json()
                        .catch(function () {
                            return null;
                        })
                        .then(function (body) {
                            if (response.ok) {
                                return body || {};
                            }

                            var error = new Error((body && body.message) || 'Permintaan gagal diproses.');
                            error.status = response.status;
                            error.errors = (body && body.errors) || null;

                            throw error;
                        });
                })
                .then(function (body) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: body.message || 'Data tersimpan.',
                        timer: 1500,
                        showConfirmButton: false,
                    });

                    var modalElement = form.closest('.modal');
                    if (modalElement && window.bootstrap) {
                        bootstrap.Modal.getOrCreateInstance(modalElement).hide();
                    }

                    setTimeout(function () {
                        window.location.reload();
                    }, 1200);
                })
                .catch(function (error) {
                    if (error.status === 422 && error.errors) {
                        showErrors(form, error.errors);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: error.message || 'Terjadi kesalahan.',
                        });
                    }
                })
                .finally(function () {
                    form.dataset.submitting = '0';

                    if (button) {
                        button.disabled = false;
                        button.innerHTML = originalHtml;
                    }
                });
        }

        window.POSModalForm = {
            open: open,
            submit: submit,
        };
    })();
</script>
@endpush
