<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3"
    id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
            aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href="{{ route('dashboard') }}">
            <div class="logo-text">
                <span class="neko-logo">Neko</span>
                <span class="ps-logo">PlayStation</span>
                <span class="version-badge">v2</span>
            </div>
        </a>
    </div>
    <hr class="horizontal dark mt-0">
    <div class="collapse navbar-collapse w-auto max-height-vh-100 h-100" id="sidenav-collapse-main">
        <!-- Tambahkan id untuk scrollbar -->
        <div id="sidenav-scrollbar">
            <ul class="navbar-nav">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                        href="{{ route('dashboard') }}">
                        <div
                            class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-tv-2 text-primary text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Dashboard</span>
                    </a>
                </li>

                <li class="nav-item mt-3">
                    <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">PlayStation Management
                    </h6>
                </li>

                <!-- PlayStation -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.playstation.*') ? 'active' : '' }}"
                        href="{{ route('admin.playstation.index') }}">
                        <div
                            class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-controller text-success text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">PlayStation</span>
                    </a>
                </li>

                <!-- Availability - NEW MENU ITEM -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.playstation.availability') ? 'active' : '' }}"
                        href="{{ route('admin.playstation.availability') }}">
                        <div
                            class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-time-alarm text-warning text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Availability</span>
                    </a>
                </li>

                <!-- Games -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.game.*') ? 'active' : '' }}"
                        href="{{ route('admin.game.index') }}">
                        <div
                            class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-app text-info text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Games</span>
                    </a>
                </li>

                <li class="nav-item mt-3">
                    <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Booking & Finance</h6>
                </li>

                <!-- Reservations -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reservation.*') ? 'active' : '' }}"
                        data-bs-toggle="collapse" href="#reservationsCollapse" role="button"
                        aria-expanded="{{ request()->routeIs('admin.reservation.*') ? 'true' : 'false' }}"
                        aria-controls="reservationsCollapse">
                        <div
                            class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-calendar-grid-58 text-warning text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Reservations</span>
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.reservation.*') ? 'show' : '' }}"
                        id="reservationsCollapse">
                        <ul class="nav ms-4 ps-3">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.reservation.index') ? 'active' : '' }}"
                                    href="{{ route('admin.reservation.index') }}">
                                    <span class="sidenav-mini-icon"> A </span>
                                    <span class="sidenav-normal"> All Reservations </span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.reservation.active') ? 'active' : '' }}"
                                    href="{{ route('admin.reservation.active') }}">
                                    <span class="sidenav-mini-icon"> C </span>
                                    <span class="sidenav-normal"> Active & Upcoming </span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.reservation.history') ? 'active' : '' }}"
                                    href="{{ route('admin.reservation.history') }}">
                                    <span class="sidenav-mini-icon"> H </span>
                                    <span class="sidenav-normal"> History </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Payments -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.payment.*') ? 'active' : '' }}"
                        href="{{ route('admin.payment.index') }}">
                        <div
                            class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-credit-card text-danger text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Payments</span>
                    </a>
                </li>

                <!-- Refunds -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.refund.*') ? 'active' : '' }}"
                        href="{{ route('admin.refund.index') }}">
                        <div
                            class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-money-coins text-dark text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Refunds</span>
                    </a>
                </li>

                <li class="nav-item mt-3">
                    <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Account</h6>
                </li>

                <!-- User Profile -->
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <div
                            class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-single-02 text-primary text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Profile</span>
                    </a>
                </li>

                <!-- Logout -->
                <li class="nav-item">
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                    <a class="nav-link" href="#"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <div
                            class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-button-power text-danger text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</aside>
