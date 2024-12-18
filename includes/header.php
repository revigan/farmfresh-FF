<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FarmFresh - Belanja Sayur & Buah Segar</title>
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- BoxIcons -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #2ecc71;
            --primary-dark: #27ae60;
            --primary-light: #a8e6cf;
            --secondary-color: #f1f5f9;
            --text-color: #2c3e50;
            --gradient: linear-gradient(120deg, #2ecc71, #27ae60);
        }

        /* Navbar Styles */
        .navbar-custom {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.03);
            border-bottom: 1px solid rgba(46, 204, 113, 0.1);
            padding: 0.8rem 0;
        }

        /* Brand Styles */
        .navbar-brand {
            font-size: 1.6rem;
            font-weight: 800;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            gap: 0.7rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .navbar-brand i {
            font-size: 2rem;
            color: var(--primary-color);
            background: var(--primary-light);
            padding: 0.5rem;
            border-radius: 12px;
            transition: all 0.4s ease;
        }

        .navbar-brand:hover {
            transform: translateY(-2px) scale(1.02);
        }

        .navbar-brand:hover i {
            transform: rotate(15deg);
            background: var(--primary-color);
            color: white;
        }

        /* Nav Links */
        .nav-link {
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--text-color) !important;
            padding: 0.6rem 1.2rem !important;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin: 0 0.6rem; /* Menambahkan jarak antar navigasi */
            position: relative;
        }

        .nav-link i {
            font-size: 1.3rem;
        }

        .nav-link:before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background: var(--gradient);
            transition: all 0.3s ease;
        }

        .nav-link:hover:before {
            width: 80%;
        }

        .nav-link:hover {
            background: rgba(46, 204, 113, 0.08);
            color: var(--primary-color) !important;
            transform: translateY(-2px);
        }

        .nav-link.active {
            color: var(--primary-color) !important;
            font-weight: 600;
            position: relative;
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--primary-color);
            border-radius: 2px;
        }

        .nav-link.active i {
            transform: scale(1.1);
        }

        .nav-link.active .cart-badge {
            background: var(--primary-color);
            transform: scale(1.1);
        }

        /* Cart Badge */
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #e74c3c;
            color: white;
            font-size: 12px;
            font-weight: 600;
            height: 20px;
            width: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
            padding: 0.2rem;
        }

        .cart-badge:empty {
            display: none;
        }

        /* Animasi ketika jumlah berubah */
        @keyframes cartBadgeUpdate {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        .cart-badge.updating {
            animation: cartBadgeUpdate 0.3s ease;
        }

        /* Auth Buttons */
        .auth-buttons .nav-link {
            padding: 0.7rem 1.5rem !important;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .nav-link.login-btn {
            background: rgba(46, 204, 113, 0.1);
            color: var(--primary-color) !important;
            border: 2px solid transparent;
        }

        .nav-link.login-btn:hover {
            border-color: var(--primary-color);
            background: transparent;
        }

        .nav-link.register-btn {
            background: var(--gradient);
            color: white !important;
            box-shadow: 0 4px 15px rgba(46, 204, 113, 0.2);
        }

        .nav-link.register-btn:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 6px 20px rgba(46, 204, 113, 0.25);
        }

        .nav-link.logout-btn {
            color: #dc3545 !important;
            border: 2px solid transparent;
        }

        .nav-link.logout-btn:hover {
            background: rgba(220, 53, 69, 0.1);
            border-color: #dc3545;
        }

        /* Animations */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.15); }
            100% { transform: scale(1); }
        }

        /* Mobile Styles */
        @media (max-width: 991.98px) {
            .navbar-collapse {
                background: white;
                padding: 1.5rem;
                border-radius: 16px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.08);
                margin-top: 1rem;
                border: 1px solid rgba(46, 204, 113, 0.1);
            }

            .nav-link {
                padding: 1rem 1.5rem !important;
                margin: 0.3rem 0;
            }

            .auth-buttons {
                margin-top: 1rem;
                padding-top: 1rem;
                border-top: 1px solid rgba(0,0,0,0.05);
            }

            .navbar-toggler {
                border: none;
                padding: 0.6rem 1rem;
                border-radius: 10px;
                background: rgba(46, 204, 113, 0.1);
            }

            .navbar-toggler:focus {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <div class="brand-icon">
                <i class='bx bx-leaf'></i>
            </div>
            <h4 class="ms-3 mb-0 fw-bold">FarmFresh</h4>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" 
                               href="dashboard.php">
                                <i class='bx bxs-dashboard'></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>" 
                               href="products.php">
                                <i class='bx bxs-store'></i> Produk
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['cart.php', 'checkout.php', 'payment.php']) ? 'active' : ''; ?>" 
                               href="cart.php">
                                <i class='bx bxs-cart'></i> 
                                <span>Keranjang</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>" 
                               href="orders.php">
                                <i class='bx bxs-package'></i> Riwayat Pesanan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>" 
                               href="profile.php">
                                <i class='bx bxs-user'></i> Profil
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="products.php">
                                <i class='bx bxs-store'></i> Produk
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="logout.php">
                                <i class='bx bx-log-out'></i> Keluar
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class='bx bx-log-in'></i> Masuk
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">
                                <i class='bx bx-user-plus'></i> Daftar
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</body>
</html>
