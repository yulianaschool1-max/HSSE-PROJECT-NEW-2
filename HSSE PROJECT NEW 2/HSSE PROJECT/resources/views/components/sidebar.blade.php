@props(['active' => ''])

<aside class="sidebar" id="sidebar">
    <!-- Brand -->
    <div class="brand">
        <div class="brand-logo">
            <img src="{{ asset('images/logo/geo-dipa.png') }}" alt="Logo Geo Dipa Energi" class="brand-logo-img">
        </div>
        <div>
            <div class="brand-title">{{ config('branding.app_name', 'Hsse Manajemen') }}</div>
            <div class="brand-subtitle">Project Manajemen Geodipa<br>Unit Dieng 2</div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="nav-menu">
        <a class="nav-link-custom {{ $active === 'dashboard' ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <a class="nav-link-custom {{ $active === 'daily-work' ? 'active' : '' }}" href="{{ route('daily-work.index') }}">
            <i class="bi bi-calendar2-week-fill"></i> Daily Work
        </a>

        <a class="nav-link-custom {{ $active === 'hsse-plan' ? 'active' : '' }}" href="{{ route('hsse-plan.index') }}">
            <i class="bi bi-calendar-check-fill"></i> HSSE Plan
        </a>

        <a class="nav-link-custom {{ $active === 'events' ? 'active' : '' }}" href="{{ route('events.index') }}">
            <i class="bi bi-calendar-event-fill"></i> Events
        </a>

        <a class="nav-link-custom {{ $active === 'inventory' ? 'active' : '' }}" href="{{ url('/inventory') }}">
            <i class="bi bi-box-seam-fill"></i> Inventory
        </a>

        <a class="nav-link-custom" data-bs-toggle="collapse" href="#dataSubmenu" role="button" aria-expanded="{{ in_array($active, ['man-hours', 'incident', 'issue-follow-up', 'gas-detector']) ? 'true' : 'false' }}">
            <i class="bi bi-folder2-open"></i> Data
            <i class="bi bi-chevron-down nav-chevron"></i>
        </a>

        <div class="collapse {{ in_array($active, ['man-hours', 'incident', 'issue-follow-up', 'gas-detector']) ? 'show' : '' }} submenu" id="dataSubmenu">
            <a class="nav-link-custom {{ in_array($active, ['man-hours', 'workforce']) ? 'active' : '' }}" href="{{ route('man-hours.index') }}"><i class="bi bi-clock-history"></i> Manhour</a>
            <a class="nav-link-custom {{ $active === 'incident' ? 'active' : '' }}" href="{{ url('/incident') }}"><i class="bi bi-exclamation-triangle"></i> Incident</a>
            <a class="nav-link-custom {{ $active === 'issue-follow-up' ? 'active' : '' }}" href="{{ url('/issue-follow-up') }}"><i class="bi bi-clipboard-check"></i> Issue &amp; Follow Up</a>
            <a class="nav-link-custom {{ $active === 'gas-detector' ? 'active' : '' }}" href="{{ route('gas-detector.index') }}"><i class="bi bi-broadcast-pin"></i> Gas Monitoring</a>
        </div>

        @adminManagement
        <a class="nav-link-custom {{ $active === 'users' ? 'active' : '' }}" href="{{ route('users.index') }}">
            <i class="bi bi-people-fill"></i> Manajemen Pengguna
        </a>
        @endadminManagement
    </nav>

    <!-- Sidebar Footer Dieng -->
    <div class="sidebar-footer">
        <div class="sidebar-footer-text">
            Safety Today<br>Sustainability Tomorrow
        </div>
    </div>
</aside>

