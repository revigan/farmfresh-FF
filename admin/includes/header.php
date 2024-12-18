<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FarmFresh</title>
    <!-- Bootstrap 5.3.2 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- BoxIcons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
    :root {
        --primary-color: #3498db;
        --primary-dark: #2980b9;
        --secondary-color: #2c3e50;
        --light-blue: #ebf5fb;
        --sidebar-width: 280px;
        --header-height: 70px;
    }

    body {
        min-height: 100vh;
        background: #f8f9fa;
    }

    /* Mobile Header */
    .mobile-header {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: var(--header-height);
        background: white;
        z-index: 999;
        box-shadow: 0 2px 15px rgba(52, 152, 219, 0.1);
        padding: 0 1.5rem;
    }

    /* Sidebar Styles */
    .sidebar {
        width: var(--sidebar-width);
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        background: white;
        color: var(--secondary-color);
        transition: all 0.3s ease;
        z-index: 1000;
        overflow-y: auto;
        box-shadow: 0 0 20px rgba(52, 152, 219, 0.1);
    }

    .sidebar-brand {
        height: var(--header-height);
        display: flex;
        align-items: center;
        padding: 0 1.5rem;
        background: var(--light-blue);
        border-bottom: 1px solid rgba(52, 152, 219, 0.1);
    }

    .brand-icon {
        width: 45px;
        height: 45px;
        background: var(--primary-color);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        margin-right: 1rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
    }

    .brand-text {
        display: flex;
        flex-direction: column;
    }

    .brand-title {
        font-weight: 700;
        font-size: 1.2rem;
        color: var(--primary-color);
    }

    .brand-subtitle {
        font-size: 0.8rem;
        color: #6c757d;
    }

    .nav-item {
        margin: 5px 15px;
    }

    .nav-link {
        color: var(--secondary-color) !important;
        padding: 12px 20px !important;
        border-radius: 10px;
        transition: all 0.3s ease;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .nav-link i {
        font-size: 1.2rem;
        color: var(--primary-color);
    }

    .nav-link:hover {
        background: var(--light-blue);
        color: var(--primary-color) !important;
        transform: translateX(5px);
    }

    .nav-link.active {
        background: var(--primary-color) !important;
        color: white !important;
        box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
    }

    .nav-link.active i {
        color: white;
    }

    /* Main Content */
    .main-content {
        margin-left: var(--sidebar-width);
        padding: 2rem;
        min-height: 100vh;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }

    /* Logout Button */
    .nav-link.text-danger {
        color: #dc3545 !important;
    }

    .nav-link.text-danger:hover {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545 !important;
    }

    .nav-link.text-danger i {
        color: #dc3545;
    }

    /* Scrollbar */
    .sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar::-webkit-scrollbar-track {
        background: var(--light-blue);
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: var(--primary-color);
        border-radius: 3px;
    }
    /* Mobile Sidebar Transition */
.sidebar.show {
    transform: translateX(0);
}

/* Animation for Sidebar on Mobile */
@media (max-width: 992px) {
    .sidebar {
        transform: translateX(-100%);
    }
    
    .sidebar.show {
        transform: translateX(0);
    }
}


    /* Responsive */
    @media (max-width: 992px) {
        .mobile-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar {
            transform: translateX(-100%);
        }
        
        .sidebar.show {
            transform: translateX(0);
        }

        .main-content {
            margin-left: 0;
            padding-top: calc(var(--header-height) + 1rem);
        }
    }

    /* Animations */
    @keyframes slideIn {
        from { transform: translateX(-100%); }
        to { transform: translateX(0); }
    }

    .slide-in {
        animation: slideIn 0.3s ease forwards;
    }

    /* Hover Effects */
    .brand-icon:hover {
        transform: scale(1.1);
        background: var(--primary-dark);
    }

    .nav-link:hover i {
        transform: translateX(5px);
    }

    /* Card Styles */
    .card {
        border: none;
        box-shadow: 0 0 15px rgba(52, 152, 219, 0.1);
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(52, 152, 219, 0.2);
    }

    /* Button Styles */
    .btn-primary {
        background: var(--primary-color);
        border-color: var(--primary-color);
    }

    .btn-primary:hover {
        background: var(--primary-dark);
        border-color: var(--primary-dark);
    }
    </style>
</head>
<body>
    <!-- Mobile Header -->
    <div class="mobile-header">
        <button class="btn btn-light navbar-toggler border-0">
            <i class='bx bx-menu fs-4'></i>
        </button>
        <div class="d-flex align-items-center">
            <div class="brand-icon">
                <i class='bx bx-leaf'></i>
            </div>
            <div class="brand-text">
                <span class="brand-title">FarmFresh</span>
            </div>
        </div>
        <div class="dropdown">
            <button class="btn btn-light border-0" type="button" data-bs-toggle="dropdown">
                <i class='bx bx-user-circle fs-4'></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="../logout.php">Keluar</a></li>
            </ul>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Brand -->
        <div class="sidebar-brand">
            <div class="brand-icon">
                <i class='bx bx-leaf'></i>
            </div>
            <div class="brand-text">
                <span class="brand-title">FarmFresh</span>
                <span class="brand-subtitle">Admin Panel</span>
            </div>
        </div>

        <!-- Navigation -->
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" 
                   href="dashboard.php">
                    <i class='bx bxs-dashboard'></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>" 
                   href="products.php">
                    <i class='bx bxs-package'></i> Produk
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>" 
                   href="orders.php">
                    <i class='bx bxs-shopping-bag'></i> Pesanan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" 
                   href="users.php">
                    <i class='bx bxs-user'></i> Pengguna
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'reviews.php' ? 'active' : ''; ?>" 
                   href="reviews.php">
                    <i class='bx bxs-star'></i> Ulasan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'report.php' ? 'active' : ''; ?>" 
                   href="report.php">
                    <i class='bx bxs-report'></i> Laporan
                </a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link text-danger" href="../logout.php">
                    <i class='bx bxs-log-out'></i> Keluar
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebarToggle = document.querySelector('.navbar-toggler');
        const sidebar = document.querySelector('.sidebar');
        const mobileHeader = document.querySelector('.mobile-header');

        // Toggle Sidebar on Hamburger Click
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('show');
            mobileHeader.classList.toggle('show');
        });
    });
</script>

</body>
</html>
