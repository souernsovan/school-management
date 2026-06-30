@if (session('success') || session('error') || $errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: @json(session('success')),
                    confirmButtonColor: '#2563eb',
                    timer: 2500,
                    timerProgressBar: true,
                });
            @elseif (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: @json(session('error')),
                    confirmButtonColor: '#2563eb',
                });
            @elseif ($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Please fix the form',
                    html: @json(collect($errors->all())->implode('<br>')),
                    confirmButtonColor: '#2563eb',
                });
            @endif
        });
    </script>
@endif

<script>
    document.addEventListener('submit', function (event) {
        const form = event.target.closest('form[data-swal-confirm]');

        if (!form || form.dataset.swalConfirmed === '1' || typeof Swal === 'undefined') {
            return;
        }

        event.preventDefault();

        const title = form.dataset.swalTitle || 'Are you sure?';
        const text = form.dataset.swalText || 'This action cannot be undone.';
        const confirmText = form.dataset.swalConfirmText || 'Yes, continue';

        Swal.fire({
            icon: 'warning',
            title: title,
            text: text,
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
        }).then((result) => {
            if (result.isConfirmed) {
                form.dataset.swalConfirmed = '1';
                form.submit();
            }
        });
    }, true);
</script>
