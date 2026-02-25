<div class="sidebar-area" id="sidebar-area">
    <div class="logo position-relative">
        <center>
        <a href="{{ route('dashboard') }}" class="d-block text-decoration-none position-relative">
            <img style="width: 80px" src="/assets/images/logo.png" alt="logo-icon">
        </a>
        <button
            class="sidebar-burger-menu bg-transparent p-0 border-0 opacity-0 z-n1 position-absolute top-50 end-0 translate-middle-y"
            id="sidebar-burger-menu">
            <i data-feather="x"></i>
        </button>

        </center>
    </div>

    <aside id="layout-menu" class="layout-menu menu-vertical menu active" data-simplebar>
        <ul class="menu-inner">
            <!-- Dashboard -->
            <li class="menu-item">
                <a href="{{ route('dashboard') }}"
                    class="menu-link {{ Request::is('dashboard') || Request::is('/') ? 'active' : '' }}">
                    <span class="material-symbols-outlined menu-icon">dashboard</span>
                    <span class="title">Dashboard</span>
                </a>
            </li>

            <!-- Receptionist Module -->
            @auth
                @if (auth()->user()->isReceptionist() || auth()->user()->isAdmin())
                    <li class="menu-item">
                        <a href="{{ route('receptionist.beneficiaries.search') }}"
                            class="menu-link {{ Request::is('receptionist/beneficiaries*') ? 'active' : '' }}">
                            <span class="material-symbols-outlined menu-icon">person_search</span>
                            <span class="title">Create Encounter</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('receptionist.encounters.index') }}"
                            class="menu-link {{ Request::is('receptionist/encounters*') ? 'active' : '' }}">
                            <span class="material-symbols-outlined menu-icon">list_alt</span>
                            <span class="title">Today's Queue</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('receptionist.referrals') }}"
                            class="menu-link {{ Request::is('receptionist/referrals*') ? 'active' : '' }}">
                            <span class="material-symbols-outlined menu-icon">swap_horiz</span>
                            <span class="title">Referrals</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('receptionist.history') }}"
                            class="menu-link {{ Request::is('receptionist/history*') ? 'active' : '' }}">
                            <span class="material-symbols-outlined menu-icon">history</span>
                            <span class="title">Encounter History</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('receptionist.reports') }}"
                            class="menu-link {{ Request::is('receptionist/reports*') ? 'active' : '' }}">
                            <span class="material-symbols-outlined menu-icon">analytics</span>
                            <span class="title">Reports</span>
                        </a>
                    </li>
                @endif
            @endauth

            <!-- Nurse Module -->
            @auth
                @if (auth()->user()->isNurse() || auth()->user()->isAdmin())
                    <li class="menu-item">
                        <a href="{{ route('nurse.triage.index') }}"
                            class="menu-link {{ Request::is('nurse/triage') ? 'active' : '' }}">
                            <span class="material-symbols-outlined menu-icon">monitor_heart</span>
                            <span class="title">Triage Queue</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('nurse.triage.history') }}"
                            class="menu-link {{ Request::is('nurse/triage/history*') ? 'active' : '' }}">
                            <span class="material-symbols-outlined menu-icon">history</span>
                            <span class="title">Triage History</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('nurse.drug-administration.index') }}"
                            class="menu-link {{ Request::is('nurse/drug-administration*') ? 'active' : '' }}">
                            <span class="material-symbols-outlined menu-icon">medication</span>
                            <span class="title">Drug Administration</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('nurse.triage.report') }}"
                            class="menu-link {{ Request::is('nurse/triage/report*') ? 'active' : '' }}">
                            <span class="material-symbols-outlined menu-icon">summarize</span>
                            <span class="title">Reports</span>
                        </a>
                    </li>
                @endif
            @endauth

            <!-- Doctor Module -->
            @auth
                @if (auth()->user()->isDoctor() || auth()->user()->isAdmin())
                    <li class="menu-item">
                        <a href="{{ route('doctor.queue') }}"
                            class="menu-link {{ Request::is('doctor/queue*') ? 'active' : '' }}">
                            <span class="material-symbols-outlined menu-icon">queue</span>
                            <span class="title">Patient Queue</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('doctor.patients') }}"
                            class="menu-link {{ Request::is('doctor/patient*') || Request::is('doctor/patients*') ? 'active' : '' }}">
                            <span class="material-symbols-outlined menu-icon">manage_search</span>
                            <span class="title">Patient Records</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('doctor.consultation.history') }}"
                            class="menu-link {{ Request::is('doctor/consultation-history*') ? 'active' : '' }}">
                            <span class="material-symbols-outlined menu-icon">history</span>
                            <span class="title">Consultation History</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('doctor.reports') }}"
                            class="menu-link {{ Request::is('doctor/reports*') ? 'active' : '' }}">
                            <span class="material-symbols-outlined menu-icon">summarize</span>
                            <span class="title">Reports</span>
                        </a>
                    </li>
                @endif
            @endauth

            <!-- Pharmacy Module -->
            @auth
                @if (auth()->user()->isPharmacist() || auth()->user()->isAdmin())
                    <li class="menu-item">
                        <a href="{{ route('pharmacy.queue') }}"
                            class="menu-link {{ Request::is('pharmacy/queue*') || Request::is('pharmacy/prescription*') ? 'active' : '' }}">
                            <span class="material-symbols-outlined menu-icon">queue</span>
                            <span class="title">Dispensation Queue</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('pharmacy.history') }}"
                            class="menu-link {{ Request::is('pharmacy/history*') ? 'active' : '' }}">
                            <span class="material-symbols-outlined menu-icon">history</span>
                            <span class="title">Dispensation History</span>
                        </a>
                    </li>
                @endif
            @endauth

            <!-- Laboratory Module -->
            @auth
                @if (auth()->user()->isLabTechnician() || auth()->user()->isAdmin())
                    <li class="menu-item">
                        <a href="{{ route('laboratory.queue') }}"
                            class="menu-link {{ Request::is('laboratory/queue*') || Request::is('laboratory/order*') ? 'active' : '' }}">
                            <span class="material-symbols-outlined menu-icon">assignment_late</span>
                            <span class="title">Lab Queue</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('laboratory.history') }}"
                            class="menu-link {{ Request::is('laboratory/history*') ? 'active' : '' }}">
                            <span class="material-symbols-outlined menu-icon">history</span>
                            <span class="title">Results History</span>
                        </a>
                    </li>
                @endif
            @endauth

            <!-- Admin Module -->
            @auth
                @if (auth()->user()->isAdmin())
                    <li class="menu-title small text-uppercase">
                        <span class="menu-title-text">ADMINISTRATION</span>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('admin.dashboard') }}"
                            class="menu-link {{ Request::is('admin*') ? 'active' : '' }}">
                            <span class="material-symbols-outlined menu-icon">admin_panel_settings</span>
                            <span class="title">Admin Dashboard</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('admin.beneficiaries.index') }}"
                            class="menu-link {{ Request::is('admin/beneficiaries*') ? 'active' : '' }}">
                            <span class="material-symbols-outlined menu-icon">people</span>
                            <span class="title">Beneficiaries</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('users.index') }}"
                            class="menu-link {{ Request::is('users*') ? 'active' : '' }}">
                            <span class="material-symbols-outlined menu-icon">manage_accounts</span>
                            <span class="title">User Management</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('facilities.index') }}"
                            class="menu-link {{ Request::is('facilities*') ? 'active' : '' }}">
                            <span class="material-symbols-outlined menu-icon">location_city</span>
                            <span class="title">Facilities</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('reports.index') }}"
                            class="menu-link {{ Request::is('reports*') ? 'active' : '' }}">
                            <span class="material-symbols-outlined menu-icon">assessment</span>
                            <span class="title">Reports</span>
                        </a>
                    </li>
                @endif
            @endauth

        </ul>
    </aside>
</div>
