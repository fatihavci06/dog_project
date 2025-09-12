<!DOCTYPE html>
<html lang="tr">
<head>
    @include('layouts.partials.header')

</head>
<body id="page-top" >

    <!-- Sayfa Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        @include('layouts.partials.sidebar')

        <!-- İçerik Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                @include('layouts.partials.navbar')

                <!-- Sayfa İçeriği -->
                <div class="container-fluid">
                    @yield('content')
                </div>

            </div>

            <!-- Footer -->
            @include('layouts.partials.footer')

        </div>
    </div>

    <!-- Scriptler -->
    <script src="{{ asset('tema/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('tema/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('temavendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('tema/js/sb-admin-2.min.js') }}"></script>
    @yield('scripts')
</body>
</html>
