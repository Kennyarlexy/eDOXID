<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>@yield('title') - {{ \App\Models\Setting::where('key', 'site_name')->first() ? \App\Models\Setting::where('key', 'site_name')->first()->value : 'Laravel' }}</title>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('/css/bootstrap.css') }}">

    <link rel="stylesheet" href="{{ asset('/plugins/iconly/bold.css') }}">

    <link rel="stylesheet" href="{{ asset('/plugins/perfect-scrollbar/perfect-scrollbar.css') }}">
    <link rel="stylesheet" href="{{ asset('/plugins/bootstrap-icons/bootstrap-icons.css') }}">
    {{-- <link rel="stylesheet" href="{{ asset('/plugins/fontawesome/all.min.css') }}"> --}}
    <script src="https://kit.fontawesome.com/8086b26683.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="{{ asset('/css/app.css') }}">
    <link rel="shortcut icon" href="{{ asset('/images/favicon.svg') }}" type="image/x-icon">

    <!-- Hotjar Tracking Code for Edoxid IF UNTAN -->
    <script>
        (function(h,o,t,j,a,r){
            h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
            h._hjSettings={hjid:3350220,hjsv:6};
            a=o.getElementsByTagName('head')[0];
            r=o.createElement('script');r.async=1;
            r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
            a.appendChild(r);
        })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
    </script>

    <style>
        .required .control-label:after {
            content:"*";
            color:red;
            margin-left:5px;
        }

        @media (max-width: 767.98px) {
            .text-md-nowrap {
                white-space: nowrap;
            }

            .display-md-block {
                display: block;
            }
        }
    </style>
    @stack('styles')
</head>

<body class="hold-transition @yield('bodyClass')">
    @yield('template')

    <script src="{{ asset('/plugins/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('/js/bootstrap.bundle.min.js') }}"></script>

    <script src="{{ asset('/js/main.js') }}"></script>
    <script src="{{ asset('/js/clipboard.js') }}"></script>
    <script src="{{ asset('/plugins/fontawesome/all.min.js') }}"></script>
    

    <script>
        const logoutAction = () => {
            if (confirm('Apakah anda yakin ingin keluar?')) {
                document.getElementById('logout').click();
            } else {
                return;
            }
        }
    </script>
    @stack('scripts')
</body>

</html>
