<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Laravel</title>
    <!-- Custom fonts for this template-->
    <link href="{{ asset('tema/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

<link rel="icon" href="{{ asset('tema/tr_goc_logo_65.png')  }}" type="image/x-icon">
    <!-- Custom styles for this template-->
    <link href="{{ asset('tema/css/sb-admin-2.min.css') }}" rel="stylesheet">
    <script src="{{ asset('tema/js/bootstrap.bundle.min.js') }}"></script>

    <style>/* Sadece rapor dropdown menüsüne özel stil */
.rapor-dropdown .dropdown-menu {
    min-width: 350px;
    box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.15);
    animation: none; /* SB Admin 2 animasyonu kaldır */
    border-radius: 0.35rem;
}

/* Çakışmaları önlemek için kullanıcı dropdown'a dokunma */
.dropdown-menu-right {
    right: 0;
    left: auto;
}
</style>
    @yield('styles')
