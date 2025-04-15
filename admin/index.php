<?php
require '../dist/koneksi.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
$current_page = $_GET['page'] ?? 'dasboard'; // Ambil parameter halaman saat ini

// Tentukan path file child page berdasarkan parameter
switch ($current_page) {
    case 'dasboard':
        $childFile = 'dasboard/dasboard.php';
        break;
    case 'produk':
        $childFile = 'produk/produk.php';
        break;
    case 'pelanggan':
        $childFile = 'pelanggan/pelanggan.php';
        break;
    case 'pengguna':
        $childFile = 'pengguna/pengguna.php';
        break;
    case 'penjualan':
        $childFile = 'penjualan/penjualan.php';
        break;
    case 'transaksi':
        $childFile = 'transaksi/transaksi.php';
        break;
    case 'laporan':
        $childFile = 'laporan/laporan.php';
        break;
    case 'tambah_produk':
        $childFile = 'produk/tambah_produk.php';
        break;
    default:
        $childFile = 'dasboard/dasboard.php';
        break;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Royal Dumpling - Admin</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Poppins:wght@300;500;600;700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
    /* Reset dan style global layout master */
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

    /* Glassmorphism untuk panel */
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

    .menu-link:hover:not(.active) {
        background: rgba(224, 170, 110, 0.1);
        transform: translateX(5px);
    }

    .menu-link.active {
        background: rgba(224, 170, 110, 0.2) !important;
        font-weight: 600;
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
        padding: 20px 20px 30px;
        background: linear-gradient(135deg, rgba(61, 43, 31, 0.95) 0%, rgba(224, 170, 110, 0.95) 100%);
        backdrop-filter: blur(15px);
        position: sticky;
        top: 0;
        z-index: 999;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom-left-radius: 20px;
        border-bottom-right-radius: 20px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
    }

    .brand-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.8rem;
        color: #ffffff;
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        background: linear-gradient(45deg, #fff, #E0AA6E);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        letter-spacing: 1px;
        text-shadow: 0px 0px 6px rgba(255, 255, 255, 0.5);
    }

    .menu-toggle {
        font-size: 1.5rem;
        color: #ffffff;
        cursor: pointer;
        z-index: 1001;
        transition: transform 0.3s ease;
    }

    /* Main Content */
    .main-content {
        padding: 30px;
        margin-top: 20px;
        transition: transform 0.3s ease;
    }

    /* Container khusus untuk child page: menggunakan iframe untuk isolasi */
    .child-content {
        width: 100%;
        height: calc(100vh - 100px);
        border: none;
    }

    /* Modal Logout */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }

    .modal-content {
        background: white;
        padding: 30px;
        border-radius: 15px;
        width: 90%;
        max-width: 400px;
        text-align: center;
    }

    .modal-actions {
        margin-top: 25px;
        display: flex;
        gap: 15px;
        justify-content: center;
    }

    .btn-confirm,
    .btn-cancel {
        padding: 10px 25px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-confirm {
        background: #E0AA6E;
        color: white;
    }

    .btn-confirm:hover {
        background: #c9955d;
    }

    .btn-cancel {
        background: #f0f0f0;
        color: #666;
    }

    .btn-cancel:hover {
        background: #e0e0e0;
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

        .sidebar.active {
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
        }

        .sidebar.active+.main-content {
            transform: translateX(280px);
        }
    }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div></div>
        <div class="brand-title">Dpprkubus.</div>
        <div class="menu-toggle">
            <i class="fas fa-bars"></i>
        </div>
    </nav>

    <!-- Sidebar -->
    <aside class="sidebar glass-panel">
        <div class="admin-profile">
            <img src="https://via.placeholder.com/60" alt="Admin Profile">
            <div class="admin-info">
                <h3>John Doe</h3>
                <p>Administrator</p>
            </div>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-item">
                <a href="index.php?page=dasboard"
                    class="menu-link <?= ($current_page == 'dasboard') ? 'active' : '' ?>">
                    <i class="fas fa-home menu-icon"></i>
                    Dashboard
                </a>
            </li>
            <li class="menu-item">
                <a href="index.php?page=produk" class="menu-link <?= ($current_page == 'produk') ? 'active' : '' ?>">
                    <i class="fas fa-box menu-icon"></i>
                    Produk
                </a>
            </li>
            <li class="menu-item">
                <a href="index.php?page=pelanggan"
                    class="menu-link <?= ($current_page == 'pelanggan') ? 'active' : '' ?>">
                    <i class="fas fa-users menu-icon"></i>
                    Pelanggan
                </a>
            </li>
            <li class="menu-item">
                <a href="index.php?page=pengguna"
                    class="menu-link <?= ($current_page == 'pengguna') ? 'active' : '' ?>">
                    <i class="fas fa-user-cog menu-icon"></i>
                    Pengguna
                </a>
            </li>
            <li class="menu-item">
                <a href="index.php?page=penjualan"
                    class="menu-link <?= ($current_page == 'penjualan') ? 'active' : '' ?>">
                    <i class="fas fa-chart-line menu-icon"></i>
                    Penjualan
                </a>
            </li>
            <li class="menu-item">
                <a href="index.php?page=transaksi"
                    class="menu-link <?= ($current_page == 'transaksi') ? 'active' : '' ?>">
                    <i class="fas fa-receipt menu-icon"></i>
                    Transaksi
                </a>
            </li>
            <li class="menu-item">
                <a href="index.php?page=laporan" class="menu-link <?= ($current_page == 'laporan') ? 'active' : '' ?>">
                    <i class="fas fa-file-alt menu-icon"></i>
                    Laporan
                </a>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link" id="logoutLink">
                    <i class="fas fa-sign-out-alt menu-icon"></i>
                    Logout
                </a>
            </li>
        </ul>
    </aside>

    <!-- Logout Modal -->
    <div id="logoutModal" class="modal">
        <div class="modal-content glass-panel">
            <h3>Konfirmasi Logout</h3>
            <p>Apakah Anda yakin ingin keluar?</p>
            <div class="modal-actions">
                <button class="btn-cancel">Batal</button>
                <button class="btn-confirm">Logout</button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Memuat child page ke dalam iframe untuk isolasi tampilan -->
        <iframe class="child-content" src="<?= $childFile; ?>" frameborder="0"></iframe>
    </main>

    <script>
    // Toggle sidebar
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    const icon = menuToggle.querySelector('i');

    function toggleSidebar() {
        sidebar.classList.toggle('active');
        icon.classList.toggle('fa-bars');
        icon.classList.toggle('fa-times');
    }
    menuToggle.addEventListener('click', toggleSidebar);

    // Close sidebar pada resolusi kecil
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 992 &&
            !sidebar.contains(e.target) &&
            !menuToggle.contains(e.target)) {
            sidebar.classList.remove('active');
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        }
    });
    window.addEventListener('resize', () => {
        if (window.innerWidth > 992) {
            sidebar.classList.remove('active');
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        }
    });

    // Logout Modal
    const logoutLink = document.getElementById('logoutLink');
    const logoutModal = document.getElementById('logoutModal');
    const btnConfirm = document.querySelector('.btn-confirm');
    const btnCancel = document.querySelector('.btn-cancel');

    logoutLink.addEventListener('click', function(e) {
        e.preventDefault();
        logoutModal.style.display = 'flex';
    });
    btnConfirm.addEventListener('click', () => {
        window.location.href = 'logout.php';
    });
    btnCancel.addEventListener('click', () => {
        logoutModal.style.display = 'none';
    });
    window.onclick = function(e) {
        if (e.target == logoutModal) {
            logoutModal.style.display = 'none';
        }
    }
    </script>
</body>

</html>