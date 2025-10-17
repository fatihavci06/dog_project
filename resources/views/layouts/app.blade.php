<!DOCTYPE html>
<html lang="tr">
<head>
       <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Laravel')</title>
    @include('layouts.partials.header')

<link href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap4.min.css" rel="stylesheet">


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
    <script src="{{ asset('tema/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('tema/js/sb-admin-2.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
 <script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap4.min.js"></script>

    @yield('scripts')
</body>
</html>
