<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">

    <meta name="viewport" id="viewport" content="width=device-width, initial-scale=1">

    <script>
        (function() {
            const desktopWidth = 1200;
            const viewport = document.getElementById('viewport');

            if (window.screen.width < 1024) {
                // Calculate the ratio to fit the screen
                const scale = window.screen.width / desktopWidth;
                viewport.setAttribute('content',
                    'width=' + desktopWidth +
                    ', initial-scale=' + scale +
                    ', maximum-scale=1.0, user-scalable=yes'
                );
            }
        })();
    </script>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Lottery Game') }}</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('build/assets/images/logo.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('build/assets/images/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('build/assets/images/logo.png') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('build/assets/css/style.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* 2. Force the CSS to match the forced desktop width */
        @media (max-width: 1024px) {
            body {
                min-width: 1200px !important;
                overflow-x: auto !important;
                -webkit-text-size-adjust: 100%;
            }

            /* This prevents Bootstrap's navbar and containers from collapsing */
            main,
            .container,
            .container-fluid,
            .navbar {
                min-width: 1200px !important;
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body style="background: var(--bg-main); color: var(--text-light); font-family: Poppins, sans-serif;">

    @include('layouts.navigation')

    <main class="">
        @yield('content')
    </main>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if (session('success'))
        <script>
            Swal.fire({
                title: "Success!",
                text: "{{ session('success') }}",
                icon: "success",
                confirmButtonColor: "#3085d6",
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                title: "Error!",
                text: "{{ session('error') }}",
                icon: "error",
                confirmButtonColor: "#d33",
            });
        </script>
    @endif


    @if (session('status') === 'password-updated')
        <script>
            const modal = bootstrap.Modal.getInstance(document.getElementById('passwordModal'));
            if (modal) modal.hide();

            Swal.fire({
                icon: "success",
                title: "Password Updated",
                text: "Your password has been successfully changed!",
                timer: 2000,
                showConfirmButton: false,
                position: "top-end",
                toast: true
            });
        </script>
    @endif

</body>

</html>
