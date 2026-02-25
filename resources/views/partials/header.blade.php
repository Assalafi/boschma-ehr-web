<header class="header-area bg-white mb-4 rounded-bottom-15" id="header-area">
    <div class="row align-items-center">
        <div class="col-lg-5 col-sm-6">
            <div class="left-header-content">
                <ul class="d-flex align-items-center ps-0 mb-0 list-unstyled justify-content-center justify-content-sm-start">
                    <li>
                        <button class="header-burger-menu bg-transparent p-0 border-0" id="header-burger-menu">
                            <span class="material-symbols-outlined">menu</span>
                        </button>
                    </li>
                    <li class="ms-3">
                        <div class="d-flex align-items-center">
                            <span class="material-symbols-outlined text-primary me-2">local_hospital</span>
                            <div>
                                <h6 class="mb-0 fw-semibold text-dark">
                                    @auth
                                        {{ auth()->user()->facility->name ?? 'BOSCHMA EHR' }}
                                    @else
                                        BOSCHMA EHR
                                    @endauth
                                </h6>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <div class="col-lg-7 col-sm-6">
            <div class="right-header-content mt-2 mt-sm-0">
                <ul class="d-flex align-items-center justify-content-center justify-content-sm-end ps-0 mb-0 list-unstyled">
                    @auth
                    <li class="header-right-item me-3">
                        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill">
                            <span class="material-symbols-outlined fs-14 me-1 align-middle">badge</span>
                            {{ auth()->user()->roles->first()->name ?? 'User' }}
                        </span>
                    </li>
                    @endauth
                    
                    <li class="header-right-item">
                        <div class="light-dark">
                            <button class="switch-toggle settings-btn dark-btn p-0 bg-transparent" id="switch-toggle">
                                <span class="dark"><i class="material-symbols-outlined">light_mode</i></span>
                                <span class="light"><i class="material-symbols-outlined">dark_mode</i></span>
                            </button>
                        </div>
                    </li>

                    <li class="header-right-item">
                        <div class="dropdown notifications">
                            <button class="btn btn-secondary dropdown-toggle border-0 p-0 position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="material-symbols-outlined">notifications</span>
                                <span class="badge bg-danger rounded-circle position-absolute notification-badge">0</span>
                            </button>
                            <div class="dropdown-menu dropdown-lg p-0 border-0 dropdown-menu-end">
                                <span class="fw-semibold fs-15 text-secondary title d-flex justify-content-between align-items-center">
                                    Notifications
                                    <span class="badge bg-primary-subtle text-primary rounded-pill">0 New</span>
                                </span>
                                <div class="max-h-275" data-simplebar>
                                    <div class="notification-menu">
                                        <p class="text-center text-muted py-4">No new notifications</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>

                    <li class="header-right-item">
                        <div class="dropdown admin-profile">
                            <button class="btn btn-secondary dropdown-toggle border-0 bg-transparent p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="d-flex align-items-center">
                                    <div class="position-relative">
                                        <div class="wh-40 bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center">
                                            <span class="material-symbols-outlined text-primary">person</span>
                                        </div>
                                        <span class="active-status position-absolute bg-success rounded-circle"></span>
                                    </div>
                                    <div class="ms-2 text-start d-none d-lg-block">
                                        <span class="d-block fs-14 fw-medium text-dark">
                                            @auth
                                                {{ auth()->user()->name ?? 'User' }}
                                            @else
                                                Guest
                                            @endauth
                                        </span>
                                        <span class="fs-12 text-muted">
                                            @auth
                                                {{ auth()->user()->email ?? '' }}
                                            @endauth
                                        </span>
                                    </div>
                                </div>
                            </button>
                            <div class="dropdown-menu dropdown-lg border-0 dropdown-menu-end">
                                <div class="d-flex align-items-center info border-bottom pb-3">
                                    <div class="flex-shrink-0">
                                        <div class="wh-54 bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center">
                                            <span class="material-symbols-outlined text-primary fs-2">person</span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="fw-semibold mb-0">
                                            @auth
                                                {{ auth()->user()->name ?? 'User' }}
                                            @else
                                                Guest
                                            @endauth
                                        </h6>
                                        <span class="fs-13 text-muted">
                                            @auth
                                                {{ auth()->user()->roles->first()->name ?? 'User' }}
                                            @endauth
                                        </span>
                                    </div>
                                </div>
                                <ul class="admin-link ps-0 mb-0 list-unstyled mt-3">
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center text-body" href="{{ route('dashboard') }}">
                                            <i class="material-symbols-outlined">dashboard</i>
                                            <span class="ms-2">Dashboard</span>
                                        </a>
                                    </li>
                                    <li>
                                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="dropdown-item d-flex align-items-center text-body w-100 border-0 bg-transparent">
                                                <i class="material-symbols-outlined text-danger">logout</i>
                                                <span class="ms-2 text-danger">Logout</span>
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
