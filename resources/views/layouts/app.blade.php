<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'POS System')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --sidebar-width: 250px;
            --header-height: 60px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: #2c3e50;
            color: white;
            overflow-y: auto;
            transition: all 0.3s;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 20px;
            background: #1a252f;
            text-align: center;
        }
        
        .sidebar-header h3 {
            margin: 0;
            font-size: 20px;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li a {
            display: block;
            padding: 15px 20px;
            color: #ecf0f1;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background: #34495e;
            border-left: 4px solid #3498db;
        }
        
        .sidebar-menu li a i {
            margin-right: 10px;
            width: 20px;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            background: #f8f9fa;
        }
        
        .top-navbar {
            height: var(--header-height);
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        .content-wrapper {
            padding: 30px;
        }
        
        .card {
            border: none;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .card-header {
            background: white;
            border-bottom: 2px solid #f0f0f0;
            padding: 15px 20px;
        }
        
        .btn-primary {
            background: #3498db;
            border-color: #3498db;
        }
        
        .btn-primary:hover {
            background: #2980b9;
            border-color: #2980b9;
        }
        
        .badge {
            padding: 5px 10px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body class="@yield('body-class')">
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-cash-register"></i> POS System</h3>
            <small>{{ auth()->user()->full_name }}</small>
            <br>
            <small class="text-muted">{{ ucfirst(auth()->user()->role) }}</small>
        </div>
        
        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            
            <li>
                <a href="{{ route('pos.index') }}" class="{{ request()->routeIs('pos.*') ? 'active' : '' }}">
                    <i class="fas fa-shopping-cart"></i> POS / Kasir
                </a>
            </li>
            
            <li>
                <a href="{{ route('transactions.index') }}" class="{{ request()->routeIs('transactions.*') ? 'active' : '' }}">
                    <i class="fas fa-receipt"></i> Transaksi
                </a>
            </li>
            
            @if(auth()->user()->hasRole(['owner','supervisor']))
            <li>
                <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <i class="fas fa-box"></i> Produk
                </a>
            </li>
            
            <li>
                <a href="{{ route('purchases.index') }}" class="{{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                    <i class="fas fa-truck"></i> Pembelian
                </a>
            </li>
            @endif
            
            <li>
                <a href="{{ route('returns.index') }}" class="{{ request()->routeIs('returns.*') ? 'active' : '' }}">
                    <i class="fas fa-undo"></i> Return Barang
                </a>
            </li>
            
            <li>
                <a href="{{ route('cash-sessions.index') }}" class="{{ request()->routeIs('cash-sessions.*') ? 'active' : '' }}">
                    <i class="fas fa-wallet"></i> Sesi Kas
                </a>
            </li>
            
            @if(auth()->user()->hasRole(['owner','supervisor']))
            <li>
                <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <i class="fas fa-tags"></i> Kategori
                </a>
            </li>
            
            <li>
                <a href="{{ route('price-tiers.index') }}" class="{{ request()->routeIs('price-tiers.*') ? 'active' : '' }}">
                    <i class="fas fa-dollar-sign"></i> Harga Bertingkat
                </a>
            </li>
            
            <li>
                <a href="{{ route('warehouses.index') }}" class="{{ request()->routeIs('warehouses.*') ? 'active' : '' }}">
                    <i class="fas fa-warehouse"></i> Gudang
                </a>
            </li>
            
            <li>
                <a href="{{ route('reports.sales') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i> Laporan
                </a>
            </li>
            @endif
            
            @if(auth()->user()->hasRole(['owner','supervisor']))
            <li>
                <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> User Management
                </a>
            </li>
            @endif
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div>
                <button class="btn btn-link d-md-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h5 class="mb-0 d-inline">@yield('page-title', 'Dashboard')</h5>
            </div>
            
            <div class="dropdown">
                <a href="#" class="text-decoration-none text-dark dropdown-toggle" 
                   data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle"></i> {{ auth()->user()->full_name }}
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#">
                        <i class="fas fa-user"></i> Profile
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Content Wrapper -->
        <div class="content-wrapper">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            
            @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            
            @yield('content')
        </div>
    </div>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Sidebar toggle for mobile
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });
        
        // Auto hide alerts
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
    
    @stack('scripts')
</body>
</html>