<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Royal Dumpling - Admin</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Poppins:wght@300;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background: #fafafa;
        color: #3D2B1F;
        overflow-x: hidden;
    }

    /* Glassmorphism Effect */
    .glass-panel {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }

    /* Sidebar */
    .sidebar {
        position: fixed;
        top: 0;
        left: -280px;
        width: 280px;
        height: 100vh;
        padding: 25px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 1000;
        display: flex;
        flex-direction: column;
    }

    .sidebar.active {
        left: 0;
    }

    .sidebar-header {
        padding: 20px 0;
        margin-bottom: 20px;
        text-align: center;
        border-bottom: 2px solid rgba(61, 43, 31, 0.1);
    }

    .brand-title {
        font-family: 'Playfair Display', serif;
        font-size: 2rem;
        color: #3D2B1F;
        margin: 15px 0;
        letter-spacing: 1px;
        background: linear-gradient(45deg, #3D2B1F, #E0AA6E);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .admin-profile {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 30px;
        padding: 15px;
        background: rgba(224, 170, 110, 0.1);
        border-radius: 12px;
    }

    .admin-profile img {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        border: 2px solid #E0AA6E;
        object-fit: cover;
    }

    .admin-info h3 {
        font-size: 1.1rem;
        color: #3D2B1F;
        margin-bottom: 5px;
    }

    .admin-info p {
        font-size: 0.9rem;
        color: #8C746A;
    }

    .sidebar-menu {
        list-style: none;
        flex: 1;
        overflow-y: auto;
        padding: 0 10px 20px 0;
    }

    .menu-item {
        margin: 8px 0;
    }

    .menu-link {
        display: flex;
        align-items: center;
        padding: 15px 20px;
        color: #3D2B1F;
        text-decoration: none;
        border-radius: 10px;
        transition: all 0.3s ease;
        position: relative;
    }

    .menu-link:hover {
        background: rgba(224, 170, 110, 0.1);
        transform: translateX(5px);
    }

    .menu-link.active {
        background: rgba(224, 170, 110, 0.15);
        font-weight: 500;
    }

    .menu-link.active::before {
        content: '';
        position: absolute;
        left: -25px;
        top: 0;
        height: 100%;
        width: 3px;
        background: #E0AA6E;
        border-radius: 2px;
    }

    .menu-icon {
        font-size: 1.2rem;
        margin-right: 15px;
        color: #E0AA6E;
        width: 25px;
    }

    /* Navbar */
    .navbar {
        padding: 15px 20px;
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(15px);
        position: sticky;
        top: 0;
        z-index: 999;
        display: flex;
        justify-content: center;
        align-items: center;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
    }

    .menu-toggle {
        font-size: 1.5rem;
        color: #3D2B1F;
        cursor: pointer;
        position: absolute;
        left: 20px;
    }

    .brand-title-center {
        font-family: 'Playfair Display', serif;
        font-size: 1.8rem;
        color: #3D2B1F;
        background: linear-gradient(45deg, #3D2B1F, #E0AA6E);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        letter-spacing: 1px;
    }

    /* Main Content */
    .main-content {
        padding: 30px;
        transition: all 0.3s ease;
    }

    /* Scrollbar Styling */
    ::-webkit-scrollbar {
        width: 6px;
    }

    ::-webkit-scrollbar-track {
        background: rgba(224, 170, 110, 0.05);
    }

    ::-webkit-scrollbar-thumb {
        background: rgba(224, 170, 110, 0.2);
        border-radius: 3px;
    }

    /* Responsive Design */
    @media (min-width: 993px) {
        .sidebar {
            left: 0;
        }

        .main-content {
            margin-left: 280px;
        }

        .menu-toggle {
            display: none;
        }

        .brand-title-center {
            display: none;
        }
    }

    @media (max-width: 992px) {
        .admin-profile {
            flex-direction: column;
            text-align: center;
        }

        .admin-profile img {
            width: 80px;
            height: 80px;
        }

        .menu-link {
            padding: 12px 15px;
        }

        .brand-title {
            font-size: 1.8rem;
        }
    }

    @media (max-width: 576px) {
        .admin-profile img {
            width: 70px;
            height: 70px;
        }

        .admin-info h3 {
            font-size: 1rem;
        }

        .main-content {
            padding: 20px;
        }

        .brand-title-center {
            font-size: 1.5rem;
        }
    }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar glass-panel">
        <div class="menu-toggle">
            <i class="fas fa-bars"></i>
        </div>
        <div class="brand-title-center">Royal Dumpling</div>
    </nav>

    <!-- Sidebar -->
    <aside class="sidebar glass-panel">
        <div class="sidebar-header">
            <div class="brand-title">Royal Dumpling</div>
            <div class="admin-profile">
                <img src="https://via.placeholder.com/60" alt="Admin Profile">
                <div class="admin-info">
                    <h3>John Doe</h3>
                    <p>Administrator</p>
                </div>
            </div>
        </div>

        <ul class="sidebar-menu">
            <li class="menu-item">
                <a href="#" class="menu-link active">
                    <i class="fas fa-home menu-icon"></i>
                    Dashboard
                </a>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <i class="fas fa-box menu-icon"></i>
                    Produk
                </a>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <i class="fas fa-users menu-icon"></i>
                    Pelanggan
                </a>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <i class="fas fa-user-cog menu-icon"></i>
                    Pengguna
                </a>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <i class="fas fa-chart-line menu-icon"></i>
                    Penjualan
                </a>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <i class="fas fa-receipt menu-icon"></i>
                    Transaksi
                </a>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <i class="fas fa-file-alt menu-icon"></i>
                    Laporan
                </a>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <i class="fas fa-sign-out-alt menu-icon"></i>
                    Logout
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Konten utama -->
    </main>

    <script>
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');

    // Toggle sidebar
    menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('active');
    });

    // Close sidebar when clicking outside
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 992 &&
            !sidebar.contains(e.target) &&
            !menuToggle.contains(e.target)) {
            sidebar.classList.remove('active');
        }
    });

    // Responsive adjustment
    window.addEventListener('resize', () => {
        if (window.innerWidth > 992) {
            sidebar.classList.remove('active');
        }
    });
    </script>
</body>

</html>