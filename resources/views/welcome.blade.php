<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>POS &amp; Order</title>
        <meta http-equiv="refresh" content="0;url={{ url('/') }}">
    </head>
    <body class="bg-[var(--pos-bg)]">
        <div class="min-vh-100 d-flex align-items-center justify-content-center p-3">
            <div class="container text-center">
                <div class="spinner-border text-brand" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted-pos mt-3">Mengarahkan ke marketplace...</p>
                <a href="{{ url('/') }}" class="btn btn-brand mt-2">Ke Marketplace</a>
            </div>
        </div>
    </body>
</html>
