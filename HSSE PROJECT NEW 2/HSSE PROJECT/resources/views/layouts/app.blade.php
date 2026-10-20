<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="application-name" content="{{ config('branding.app_name', 'Hsse Manajemen') }} (HSSE PROJECT)">
    <title>{{ $title ?? config('branding.app_name', 'Hsse Manajemen') }} | {{ config('branding.project_title', 'Project Manajemen Geodipa Unit Dieng 2') }}</title>

    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Flatpickr for Date Range -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- Shared HSSE Theme CSS -->
    <link rel="stylesheet" href="/css/hsse-theme.css">

    @stack('styles')
</head>
<body>
    <!-- Shared Sidebar -->
    <x-sidebar :active="$activeModule ?? ''" />

    <!-- Main Wrapper -->
    <div class="main-wrapper" id="mainWrapper">
        <!-- Shared Navbar -->
        <x-navbar />

        <!-- Main Page Content -->
        <main class="page-content">
            @yield('content')
        </main>
    </div>

    <!-- Core Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js"></script>

    <script>
        // Shared Sidebar Toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                document.body.classList.toggle('sidebar-collapsed');
                document.body.classList.toggle('sidebar-open');
            });
        }
    </script>

    @stack('scripts')
</body>
</html>

