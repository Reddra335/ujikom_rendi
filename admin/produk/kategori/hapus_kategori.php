<?php
// Konfigurasi database
$host     = 'localhost';
$username = 'root';
$password = '';
$dbname   = 'kasir_reddra';

// Membuat koneksi ke database
$koneksi = new mysqli($host, $username, $password, $dbname);

// Cek koneksi
if ($koneksi->connect_error) {
    showErrorModal("Koneksi gagal: " . $koneksi->connect_error);
}

// Validasi parameter ID
if (!isset($_GET['id'])) {
    showErrorModal("ID kategori tidak ditemukan.");
}

$kategori_id = intval($_GET['id']);

// Cek penggunaan kategori
$stmt_check = $koneksi->prepare("SELECT COUNT(*) FROM produk WHERE kategori_id = ?");
if (!$stmt_check) {
    showErrorModal("Terjadi kesalahan dalam query: " . $koneksi->error);
}
$stmt_check->bind_param("i", $kategori_id);
$stmt_check->execute();
$stmt_check->bind_result($jumlah_produk);
$stmt_check->fetch();
$stmt_check->close();

if ($jumlah_produk > 0) {
    showErrorModal("Tidak bisa menghapus kategori karena masih digunakan oleh $jumlah_produk produk.");
}

// Proses penghapusan
$stmt_delete = $koneksi->prepare("DELETE FROM kategori WHERE kategori_id = ?");
if (!$stmt_delete) {
    showErrorModal("Terjadi kesalahan dalam query: " . $koneksi->error);
}
$stmt_delete->bind_param("i", $kategori_id);

if ($stmt_delete->execute()) {
    showSuccessModal("Kategori berhasil dihapus.");
} else {
    showErrorModal("Gagal menghapus kategori: " . $stmt_delete->error);
}

$stmt_delete->close();
$koneksi->close();

/**
 * Tampilkan modal error
 */
function showErrorModal($error) {
    ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Error - Hapus Kategori</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
    :root {
        --primary-color: #E0AA6E;
        --error-color: #e74c3c;
        --success-color: #2ecc71;
    }

    body {
        font-family: 'Poppins', sans-serif;
        margin: 0;
        background: rgba(0, 0, 0, 0.4);
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .modal-container {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(15px);
        border-radius: 20px;
        padding: 2rem;
        width: 90%;
        max-width: 400px;
        text-align: center;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        animation: modalSlide 0.4s ease-out;
    }

    @keyframes modalSlide {
        0% {
            transform: translateY(-50px);
            opacity: 0;
        }

        100% {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .modal-icon {
        font-size: 3.5rem;
        color: var(--error-color);
        margin-bottom: 1rem;
        animation: iconBounce 0.6s;
    }

    @keyframes iconBounce {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-10px);
        }
    }

    h3.modal-title {
        color: var(--error-color);
        margin-bottom: 1rem;
        font-size: 1.5rem;
    }

    .modal-message {
        color: #3D2B1F;
        margin-bottom: 1.5rem;
        line-height: 1.5;
    }

    .modal-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
    }

    .modal-btn {
        padding: 0.8rem 1.5rem;
        border: none;
        border-radius: 30px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-back {
        background: var(--primary-color);
        color: white;
    }

    .btn-back:hover {
        background: #c9955d;
        transform: translateY(-2px);
    }
    </style>
</head>

<body>
    <div class="modal-container">
        <div class="modal-icon">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <h3 class="modal-title">Gagal Menghapus</h3>
        <p class="modal-message"><?= htmlspecialchars($error) ?></p>
        <div class="modal-actions">
            <button class="modal-btn btn-back" onclick="window.history.back()">
                <i class="fas fa-arrow-left"></i> Kembali
            </button>
        </div>
    </div>
</body>

</html>
<?php
    exit;
}

/**
 * Tampilkan modal sukses
 */
function showSuccessModal($message) {
    ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sukses - Hapus Kategori</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
    :root {
        --primary-color: #E0AA6E;
        --success-color: #2ecc71;
    }

    body {
        font-family: 'Poppins', sans-serif;
        margin: 0;
        background: rgba(0, 0, 0, 0.4);
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .modal-container {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(15px);
        border-radius: 20px;
        padding: 2rem;
        width: 90%;
        max-width: 400px;
        text-align: center;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        animation: modalSlide 0.4s ease-out;
    }

    .modal-icon {
        font-size: 3.5rem;
        color: var(--success-color);
        margin-bottom: 1rem;
        animation: iconFloat 1.5s ease-in-out infinite;
    }

    @keyframes iconFloat {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-10px);
        }
    }

    h3.modal-title {
        color: var(--success-color);
        margin-bottom: 1rem;
        font-size: 1.5rem;
    }

    .modal-message {
        color: #3D2B1F;
        margin-bottom: 1.5rem;
        line-height: 1.5;
    }

    .modal-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
    }

    .modal-btn {
        padding: 0.8rem 1.5rem;
        border: none;
        border-radius: 30px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-ok {
        background: var(--success-color);
        color: white;
    }

    .btn-ok:hover {
        background: #27ae60;
        transform: translateY(-2px);
    }

    .countdown {
        margin-top: 1rem;
        color: #3D2B1F;
        font-size: 0.9rem;
    }
    </style>
    <script>
    let seconds = 3;
    const countdownElement = document.createElement('div');

    function updateCountdown() {
        countdownElement.innerHTML = `Mengarahkan ke halaman kategori dalam ${seconds} detik...`;
        seconds--;

        if (seconds < 0) {
            window.location.href = 'kategori.php';
        } else {
            setTimeout(updateCountdown, 1000);
        }
    }
    </script>
</head>

<body>
    <div class="modal-container">
        <div class="modal-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h3 class="modal-title">Sukses!</h3>
        <p class="modal-message"><?= htmlspecialchars($message) ?></p>
        <div class="modal-actions">
            <button class="modal-btn btn-ok" onclick="window.location.href='kategori.php'">
                <i class="fas fa-check"></i> OK
            </button>
        </div>
        <div class="countdown"></div>
    </div>

    <script>
    // Inisialisasi countdown
    const countdown = document.querySelector('.countdown');
    countdown.innerHTML = `Mengarahkan ke halaman kategori dalam ${seconds} detik...`;

    const countdownInterval = setInterval(() => {
        seconds--;
        countdown.innerHTML = `Mengarahkan ke halaman kategori dalam ${seconds} detik...`;

        if (seconds <= 0) {
            clearInterval(countdownInterval);
            window.location.href = 'kategori.php';
        }
    }, 1000);
    </script>
</body>

</html>
<?php
    exit;
}
?>