<header class="topbar">
    <button class="sidebar-toggle-btn" id="sidebarToggle" aria-label="Toggle Navigation">
        <i class="bi bi-list"></i>
    </button>

    <div class="topbar-right">
        <div class="user-profile dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark" id="userProfileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="user-avatar me-2">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div>
                    <div class="user-name">{{ Auth::user()->name ?? 'Administrator Dieng' }}</div>
                    <div class="user-role">{{ Auth::user()->role_label ?? 'Admin Management' }}</div>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userProfileDropdown" style="font-size: 13px;">
                <li class="px-3 py-2 border-bottom">
                    <div class="fw-bold text-dark">{{ Auth::user()->name ?? 'Administrator Dieng' }}</div>
                    <div class="text-muted" style="font-size: 11.5px;">{{ Auth::user()->email ?? 'admin@geodipa.co.id' }}</div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle mt-1" style="font-size: 10.5px;">
                        {{ Auth::user()->role_label ?? 'Admin Management' }}
                    </span>
                </li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2 py-2">
                            <i class="bi bi-box-arrow-right"></i> Keluar (Logout)
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>

