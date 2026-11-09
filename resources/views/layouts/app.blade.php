<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'LAKONI') — Lembar Konfirmasi Anomali</title>
    
    <!-- Google Fonts: Plus Jakarta Sans & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    
    <!-- Pastel, Clean, Airy UI Stylesheet -->
    <style>
        :root {
            /* Palette Pastel & Kalem */
            --lk-bg: #f8fafc;
            --lk-card-bg: #ffffff;
            --lk-text-main: #334155;
            --lk-text-muted: #64748b;
            --lk-text-heading: #1e293b;
            
            /* Pastel Accents */
            --lk-primary: #4f6ff6;
            --lk-primary-soft: #edf2fe;
            --lk-primary-hover: #3b5bdb;
            
            --lk-teal: #0ea5e9;
            --lk-teal-soft: #f0f9ff;
            
            --lk-success: #10b981;
            --lk-success-soft: #ecfdf5;
            
            --lk-warning: #f59e0b;
            --lk-warning-soft: #fefbeb;
            
            --lk-danger: #ef4444;
            --lk-danger-soft: #fef2f2;
            
            --lk-purple: #8b5cf6;
            --lk-purple-soft: #f5f3ff;

            /* Soft Border & Shadows */
            --lk-border: #f1f5f9;
            --lk-border-card: rgba(226, 232, 240, 0.7);
            --lk-shadow-sm: 0 2px 8px -2px rgba(15, 23, 42, 0.04), 0 4px 16px -4px rgba(15, 23, 42, 0.03);
            --lk-shadow-md: 0 10px 30px -5px rgba(15, 23, 42, 0.04), 0 4px 12px -2px rgba(15, 23, 42, 0.02);
            --lk-shadow-lg: 0 20px 40px -10px rgba(15, 23, 42, 0.05);

            --lk-radius: 16px;
            --lk-radius-sm: 10px;
        }

        body {
            background-color: var(--lk-bg);
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            color: var(--lk-text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            letter-spacing: -0.01em;
            line-height: 1.6;
        }

        h1, h2, h3, h4, h5, h6, .brand-font {
            font-family: 'Outfit', sans-serif;
            color: var(--lk-text-heading);
            letter-spacing: -0.02em;
        }

        /* Top Header */
        .lakoni-header {
            height: 72px;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-bottom: 1px solid var(--lk-border-card);
            z-index: 1030;
        }

        /* Airy & Modern Sidebar */
        .lakoni-sidebar {
            width: 270px;
            min-height: calc(100vh - 72px);
            background: #ffffff;
            border-right: 1px solid var(--lk-border-card);
            padding: 1.5rem 1rem;
        }

        .sidebar-section-title {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 700;
            color: #94a3b8;
            padding: 0.75rem 1rem 0.4rem;
        }

        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 0 0 1.25rem 0;
        }

        .sidebar-nav li a {
            display: flex;
            align-items: center;
            padding: 0.7rem 1rem;
            color: #64748b;
            text-decoration: none;
            font-size: 0.92rem;
            font-weight: 500;
            border-radius: var(--lk-radius-sm);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 2px;
        }

        .sidebar-nav li a i {
            width: 26px;
            font-size: 1.05rem;
            margin-right: 0.5rem;
            color: #94a3b8;
            transition: color 0.2s;
        }

        .sidebar-nav li a:hover {
            color: var(--lk-primary);
            background: var(--lk-primary-soft);
        }

        .sidebar-nav li a:hover i {
            color: var(--lk-primary);
        }

        .sidebar-nav li a.active {
            color: var(--lk-primary);
            background: var(--lk-primary-soft);
            font-weight: 600;
        }

        .sidebar-nav li a.active i {
            color: var(--lk-primary);
        }

        /* Pastel Cards */
        .card-clean {
            background: var(--lk-card-bg);
            border: 1px solid var(--lk-border-card);
            border-radius: var(--lk-radius);
            box-shadow: var(--lk-shadow-sm);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card-clean-hover:hover {
            box-shadow: var(--lk-shadow-md);
        }

        .card-clean .card-header {
            background-color: transparent;
            border-bottom: 1px solid var(--lk-border);
            padding: 1.25rem 1.75rem;
            font-weight: 700;
        }

        /* Pastel Badges */
        .badge-pastel-primary {
            background-color: var(--lk-primary-soft);
            color: var(--lk-primary);
            border: 1px solid rgba(79, 111, 246, 0.15);
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .badge-pastel-success {
            background-color: var(--lk-success-soft);
            color: #047857;
            border: 1px solid rgba(16, 185, 129, 0.2);
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .badge-pastel-warning {
            background-color: var(--lk-warning-soft);
            color: #b45309;
            border: 1px solid rgba(245, 158, 11, 0.25);
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .badge-pastel-danger {
            background-color: var(--lk-danger-soft);
            color: #b91c1c;
            border: 1px solid rgba(239, 68, 68, 0.2);
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .badge-pastel-purple {
            background-color: var(--lk-purple-soft);
            color: #6d28d9;
            border: 1px solid rgba(139, 92, 246, 0.2);
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .badge-pastel-neutral {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        /* Buttons Kalem & Menarik */
        .btn-pastel-primary {
            background-color: var(--lk-primary);
            border-color: var(--lk-primary);
            color: #ffffff;
            font-weight: 600;
            padding: 0.65rem 1.4rem;
            border-radius: var(--lk-radius-sm);
            box-shadow: 0 4px 14px rgba(79, 111, 246, 0.22);
            transition: all 0.2s;
        }

        .btn-pastel-primary:hover {
            background-color: var(--lk-primary-hover);
            border-color: var(--lk-primary-hover);
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(79, 111, 246, 0.32);
        }

        .btn-pastel-soft {
            background-color: var(--lk-primary-soft);
            border: 1px solid rgba(79, 111, 246, 0.15);
            color: var(--lk-primary);
            font-weight: 600;
            padding: 0.55rem 1.1rem;
            border-radius: var(--lk-radius-sm);
            transition: all 0.2s;
        }

        .btn-pastel-soft:hover {
            background-color: #e0e7fe;
            color: var(--lk-primary-hover);
        }

        .btn-pastel-success {
            background-color: var(--lk-success);
            border-color: var(--lk-success);
            color: #ffffff;
            font-weight: 600;
            border-radius: var(--lk-radius-sm);
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.22);
        }

        .btn-pastel-success:hover {
            background-color: #059669;
            border-color: #059669;
            color: #ffffff;
            transform: translateY(-1px);
        }

        /* ======================================================== */
        /* CLEAN & SPACIOUS INPUT GROUP (Fix Mepet Kiri) */
        /* ======================================================== */
        .input-clean-group {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .input-clean-group .icon-slot {
            position: absolute;
            left: 1.15rem;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--lk-primary);
            font-size: 1.15rem;
            pointer-events: none;
            z-index: 5;
        }

        .input-clean-group .form-control-clean {
            padding-left: 3.25rem !important;
            padding-right: 1.25rem !important;
            padding-top: 0.85rem !important;
            padding-bottom: 0.85rem !important;
            border-radius: var(--lk-radius-sm) !important;
            border: 1.5px solid #cbd5e1;
            font-size: 1rem;
            font-weight: 500;
            background-color: #ffffff;
            transition: all 0.2s ease;
            width: 100%;
        }

        .input-clean-group .form-control-clean:focus {
            border-color: var(--lk-primary);
            box-shadow: 0 0 0 4px rgba(79, 111, 246, 0.12);
        }

        .form-control, .form-select {
            border: 1px solid #cbd5e1;
            border-radius: var(--lk-radius-sm);
            padding: 0.7rem 1.1rem;
            font-size: 0.95rem;
            color: var(--lk-text-heading);
            background-color: #ffffff;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--lk-primary);
            box-shadow: 0 0 0 3px rgba(79, 111, 246, 0.12);
            background-color: #ffffff;
        }

        /* ======================================================== */
        /* MODERN, LEGA, PASTEL & KALEM TABLE SYSTEM */
        /* ======================================================== */
        .table-responsive-modern {
            background: #ffffff;
            border-radius: var(--lk-radius);
            box-shadow: var(--lk-shadow-sm);
            border: 1px solid var(--lk-border-card);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .table-clean {
            margin-bottom: 0;
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-clean thead {
            background-color: #f8fafc;
        }

        .table-clean thead th {
            background: #f8fafc;
            color: #64748b;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 700;
            padding: 1.15rem 1.5rem;
            border-bottom: 1.5px solid #e2e8f0;
            white-space: nowrap;
        }

        .table-clean tbody tr {
            transition: background-color 0.18s ease;
        }

        .table-clean tbody tr:hover {
            background-color: #f8faff;
        }

        .table-clean tbody td {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            color: #334155;
            font-size: 0.92rem;
            line-height: 1.5;
        }

        .table-clean tbody tr:last-child td {
            border-bottom: none;
        }

        /* Table Action Buttons Clean */
        .btn-table-action {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #64748b;
            font-size: 0.85rem;
            transition: all 0.2s;
        }

        .btn-table-action:hover {
            background: var(--lk-primary-soft);
            color: var(--lk-primary);
            border-color: var(--lk-primary-soft);
        }

        /* ======================================================== */
        /* MODERN PASTEL MOTION GRAPHIC & PAGE TRANSITIONS         */
        /* ======================================================== */
        @keyframes pageFadeInUp {
            0% {
                opacity: 0;
                transform: translateY(14px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes subtleFloat {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-4px);
            }
        }

        @keyframes softPulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.03);
            }
        }

        @keyframes shimmerWave {
            0% {
                background-position: -200% 0;
            }
            100% {
                background-position: 200% 0;
            }
        }

        /* Modal Backdrop & Z-Index Safety */
        .modal {
            z-index: 1065 !important;
        }
        .modal-dialog {
            z-index: 1070 !important;
        }
        #modalPastelConfirm {
            z-index: 1075 !important;
        }
        #modalPastelConfirm .modal-dialog {
            z-index: 1080 !important;
        }
        .modal-backdrop {
            z-index: 1050 !important;
        }

        /* Page Content Entry Motion */
        .page-motion-wrapper {
            animation: pageFadeInUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* Staggered card animations */
        .stagger-motion-1 { animation: pageFadeInUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) both; }
        .stagger-motion-2 { animation: pageFadeInUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) 0.08s both; }
        .stagger-motion-3 { animation: pageFadeInUp 0.55s cubic-bezier(0.16, 1, 0.3, 1) 0.16s both; }

        /* Card interactive lift & glow */
        .card-clean {
            background: var(--lk-card-bg);
            border: 1px solid var(--lk-border-card);
            border-radius: var(--lk-radius);
            box-shadow: var(--lk-shadow-sm);
            transition: transform 0.28s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.28s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.2s ease;
        }

        .card-clean:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px -8px rgba(79, 111, 246, 0.12), 0 4px 12px -2px rgba(15, 23, 42, 0.04);
            border-color: rgba(79, 111, 246, 0.25);
        }

        /* Subtle float for brand icons & action badges */
        .brand-icon-motion {
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .brand-icon-motion:hover {
            transform: rotate(-6deg) scale(1.08);
        }

        /* Interactive buttons with spring micro-interaction */
        .btn-pastel-primary, .btn-pastel-soft, .btn-pastel-success, .btn-pastel-warning, .btn-pastel-danger {
            transition: all 0.24s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .btn-pastel-primary:active, .btn-pastel-soft:active {
            transform: scale(0.96);
        }

        /* Table row hover smooth transition */
        .table-clean tbody tr {
            transition: background-color 0.2s ease, transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .table-clean tbody tr:hover {
            background-color: #f8faff;
        }

        /* Public Header */
        .public-topbar {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-bottom: 1px solid var(--lk-border-card);
            height: 72px;
            transition: all 0.3s ease;
        }
    </style>
    @stack('styles')
</head>
<body>

    @if(auth()->check() && auth()->user()->role === 'admin')
        <!-- ============================================== -->
        <!-- ADMIN DASHBOARD LAYOUT (LEGA, BERSIH, KALEM) -->
        <!-- ============================================== -->
        <header class="lakoni-header d-flex align-items-center justify-content-between px-3 px-lg-4 sticky-top">
            <div class="d-flex align-items-center">
                <a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none me-4">
                    <div class="d-inline-flex align-items-center justify-content-center text-white rounded-3 me-2 shadow-sm brand-icon-motion" style="width: 40px; height: 40px; background: linear-gradient(135deg, #4f6ff6 0%, #0ea5e9 100%);">
                        <i class="fa-solid fa-clipboard-check fs-5"></i>
                    </div>
                    <div>
                        <span class="fs-4 brand-font text-dark fw-bold">LAKONI</span>
                        <span class="badge-pastel-primary ms-1" style="font-size: 0.65rem;">Anomali</span>
                    </div>
                </a>
            </div>

            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('home') }}" class="btn btn-pastel-soft btn-sm" target="_blank" title="Buka Halaman Petugas">
                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Area Petugas
                </a>

                <div class="dropdown">
                    <button class="btn btn-light d-flex align-items-center gap-2 border-0 bg-transparent py-1 px-2 rounded-pill" type="button" data-bs-toggle="dropdown">
                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 36px; height: 36px; background: linear-gradient(135deg, #4f6ff6 0%, #8b5cf6 100%); font-size: 0.9rem;">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="d-none d-md-block text-start me-1">
                            <div class="fw-semibold text-dark small leading-tight">{{ auth()->user()->name }}</div>
                            <span class="badge-pastel-danger" style="font-size: 0.65rem;">Admin</span>
                        </div>
                        <i class="fa-solid fa-angle-down text-muted small"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-2 p-2" style="border-radius: var(--lk-radius-sm);">
                        <li><span class="dropdown-item-text text-muted small py-2 px-3">{{ auth()->user()->email }}</span></li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger py-2 px-3 rounded">
                                    <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Keluar (Logout)
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <div class="d-flex flex-grow-1">
            <!-- Sidebar -->
            <aside class="lakoni-sidebar d-none d-lg-block flex-shrink-0">
                <div class="sidebar-section-title">Overview</div>
                <ul class="sidebar-nav">
                    <li>
                        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="fa-solid fa-chart-pie"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                </ul>

                <div class="sidebar-section-title">Konfirmasi Anomali</div>
                <ul class="sidebar-nav">
                    <li>
                        <a href="{{ route('kegiatan.index') }}" class="{{ request()->routeIs('kegiatan.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-calendar-check"></i>
                            <span>Manajemen Kegiatan</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('konfirmasi.index') }}" class="{{ request()->routeIs('konfirmasi.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-circle-question"></i>
                            <span>Template Konfirmasi</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('alokasi.index') }}" class="{{ request()->routeIs('alokasi.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-user-group"></i>
                            <span>Alokasi Petugas</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('anomali.index') }}" class="{{ request()->routeIs('anomali.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <span>Manajemen Anomali</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('monitoring.index') }}" class="{{ request()->routeIs('monitoring.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-chart-line"></i>
                            <span>Monitoring</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('panduan.infografis') }}" class="{{ request()->routeIs('panduan.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-compass"></i>
                            <span>Panduan Alur Kerja</span>
                        </a>
                    </li>
                </ul>

                <div class="sidebar-section-title">Konfigurasi</div>
                <ul class="sidebar-nav">
                    <li>
                        <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-users-gear"></i>
                            <span>Manajemen User</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('wilayah.index') }}" class="{{ request()->routeIs('wilayah.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-map-location-dot"></i>
                            <span>Master Wilayah</span>
                        </a>
                    </li>
                </ul>
            </aside>

            <!-- Main Body -->
            <main class="flex-grow-1 p-4 p-xl-5 page-motion-wrapper" style="min-width: 0;">
                <div class="container-fluid px-0">
                    <!-- Flash Messages Kalem -->
                    @if(session('success'))
                        <div class="alert alert-success d-flex align-items-center shadow-sm border-0 mb-4 stagger-motion-1" role="alert" style="background-color: var(--lk-success-soft); color: #065f46; border-radius: var(--lk-radius-sm);">
                            <i class="fa-solid fa-circle-check fs-5 me-2 text-success"></i>
                            <div>{{ session('success') }}</div>
                            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger d-flex align-items-center shadow-sm border-0 mb-4 stagger-motion-1" role="alert" style="background-color: var(--lk-danger-soft); color: #991b1b; border-radius: var(--lk-radius-sm);">
                            <i class="fa-solid fa-triangle-exclamation fs-5 me-2 text-danger"></i>
                            <div>{{ session('error') }}</div>
                            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>

    @else
        <!-- ============================================== -->
        <!-- PUBLIC / PETUGAS TOPBAR (LEGA, BERSIH, TENANG) -->
        <!-- ============================================== -->
        <header class="public-topbar sticky-top d-flex align-items-center">
            <div class="container">
                <div class="d-flex align-items-center justify-content-between">
                    <a class="d-flex align-items-center text-decoration-none" href="{{ route('home') }}">
                        <div class="d-inline-flex align-items-center justify-content-center text-white rounded-3 me-2 shadow-sm brand-icon-motion" style="width: 40px; height: 40px; background: linear-gradient(135deg, #4f6ff6 0%, #0ea5e9 100%);">
                            <i class="fa-solid fa-clipboard-check fs-5"></i>
                        </div>
                        <div>
                            <span class="fs-4 brand-font text-dark fw-bold">LAKONI</span>
                            <span class="badge-pastel-primary ms-1" style="font-size: 0.65rem;">Konfirmasi Anomali</span>
                        </div>
                    </a>

                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('home') }}" class="btn btn-light btn-sm px-3 text-secondary border">
                            <i class="fa-solid fa-barcode me-1"></i> Input Kode
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-pastel-primary btn-sm px-3">
                            <i class="fa-solid fa-user-shield me-1"></i> Login Admin
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <main class="py-5 flex-grow-1 page-motion-wrapper">
            <div class="container">
                @if(session('success'))
                    <div class="alert alert-success d-flex align-items-center shadow-sm border-0 mb-4 stagger-motion-1" role="alert" style="background-color: var(--lk-success-soft); color: #065f46; border-radius: var(--lk-radius-sm);">
                        <i class="fa-solid fa-circle-check fs-5 me-2 text-success"></i>
                        <div>{{ session('success') }}</div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger d-flex align-items-center shadow-sm border-0 mb-4 stagger-motion-1" role="alert" style="background-color: var(--lk-danger-soft); color: #991b1b; border-radius: var(--lk-radius-sm);">
                        <i class="fa-solid fa-triangle-exclamation fs-5 me-2 text-danger"></i>
                        <div>{{ session('error') }}</div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    @endif

    <!-- Modal Dialog Konfirmasi Elegan & Pastel Kotak Global -->
    <div class="modal fade" id="modalPastelConfirm" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 4px; overflow: hidden; animation: popUpModal 0.28s cubic-bezier(0.34, 1.56, 0.64, 1); background: #ffffff;">
                <div class="p-4 text-center">
                    <div id="modalConfirmIconWrapper" class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; border-radius: 4px; background: rgba(239, 68, 68, 0.1); color: #ef4444; font-size: 1.35rem;">
                        <i id="modalConfirmIcon" class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <h5 id="modalConfirmTitle" class="fw-bold brand-font text-dark mb-2">Konfirmasi Tindakan</h5>
                    <p id="modalConfirmMessage" class="text-muted small mb-4" style="line-height: 1.5;">Apakah Anda yakin ingin melanjutkan tindakan ini?</p>
                    
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-light border px-4 py-2 text-secondary fw-semibold" style="border-radius: 4px;" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="button" id="btnModalConfirmAction" class="btn btn-danger px-4 py-2 fw-semibold shadow-sm" style="border-radius: 4px;">
                            <i class="fa-solid fa-trash-can me-1"></i> Ya, Lanjutkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes popUpModal {
            0% { transform: scale(0.85); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>

    <footer class="py-4 text-center text-muted small mt-auto border-top bg-white">
        <div class="container">
            <span class="fw-semibold text-dark">LAKONI</span> &bull; Lembar Konfirmasi Anomali &copy; {{ date('Y') }}. Desain Bersih & Kalem.
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global Pastel Confirmation Dialog
        let targetFormToSubmit = null;
        let targetConfirmCallback = null;

        function showPastelDialog(options = {}) {
            targetFormToSubmit = null;
            targetConfirmCallback = typeof options.onConfirm === 'function' ? options.onConfirm : null;

            const modalEl = document.getElementById('modalPastelConfirm');
            if (!modalEl) {
                if (confirm(options.message || 'Apakah Anda yakin ingin melanjutkan?')) {
                    if (targetConfirmCallback) targetConfirmCallback();
                }
                return;
            }

            const title = options.title || 'Konfirmasi Tindakan';
            const message = options.message || 'Data yang dihapus atau diproses tidak dapat dikembalikan.';
            const btnText = options.confirmText || options.btnText || 'Ya, Lanjutkan';
            const btnClass = options.confirmClass || options.btnClass || 'btn-danger';
            const iconClass = options.iconClass || 'fa-solid fa-triangle-exclamation';
            const iconBg = options.iconBg || 'rgba(239, 68, 68, 0.1)';
            const iconColor = options.iconColor || '#ef4444';

            document.getElementById('modalConfirmTitle').textContent = title;
            document.getElementById('modalConfirmMessage').innerHTML = message;

            const iconWrapper = document.getElementById('modalConfirmIconWrapper');
            iconWrapper.style.background = iconBg;
            iconWrapper.style.color = iconColor;
            document.getElementById('modalConfirmIcon').className = iconClass;

            const confirmBtn = document.getElementById('btnModalConfirmAction');
            confirmBtn.className = `btn ${btnClass} px-4 py-2 fw-semibold shadow-sm`;
            confirmBtn.style.borderRadius = '4px';
            confirmBtn.innerHTML = `<i class="${iconClass} me-1"></i> ${btnText}`;

            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }

        function confirmPastelAction(form, options = {}) {
            targetFormToSubmit = form;
            targetConfirmCallback = null;
            const modalEl = document.getElementById('modalPastelConfirm');
            if (!modalEl) {
                if (confirm(options.message || 'Apakah Anda yakin ingin melanjutkan?')) {
                    form.submit();
                }
                return false;
            }

            const title = options.title || 'Konfirmasi Tindakan';
            const message = options.message || 'Data yang dihapus atau diproses tidak dapat dikembalikan.';
            const btnText = options.btnText || 'Ya, Hapus Sekarang';
            const btnClass = options.btnClass || 'btn-danger';
            const iconClass = options.iconClass || 'fa-solid fa-trash-can';
            const iconBg = options.iconBg || 'rgba(239, 68, 68, 0.1)';
            const iconColor = options.iconColor || '#ef4444';

            document.getElementById('modalConfirmTitle').textContent = title;
            document.getElementById('modalConfirmMessage').innerHTML = message;

            const iconWrapper = document.getElementById('modalConfirmIconWrapper');
            iconWrapper.style.background = iconBg;
            iconWrapper.style.color = iconColor;
            document.getElementById('modalConfirmIcon').className = iconClass;

            const confirmBtn = document.getElementById('btnModalConfirmAction');
            confirmBtn.className = `btn ${btnClass} px-4 py-2 fw-semibold shadow-sm`;
            confirmBtn.style.borderRadius = '4px';
            confirmBtn.innerHTML = `<i class="${iconClass} me-1"></i> ${btnText}`;

            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
            return false;
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Pindahkan semua elemen modal ke direct child <body> agar tidak terpengaruh CSS stacking context (page-motion-wrapper)
            document.querySelectorAll('.modal').forEach(function(modalEl) {
                if (modalEl.parentNode !== document.body) {
                    document.body.appendChild(modalEl);
                }
            });

            const confirmBtn = document.getElementById('btnModalConfirmAction');
            if (confirmBtn) {
                confirmBtn.addEventListener('click', function() {
                    const modalEl = document.getElementById('modalPastelConfirm');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();

                    if (targetConfirmCallback) {
                        targetConfirmCallback();
                        targetConfirmCallback = null;
                    } else if (targetFormToSubmit) {
                        targetFormToSubmit.submit();
                    }
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
