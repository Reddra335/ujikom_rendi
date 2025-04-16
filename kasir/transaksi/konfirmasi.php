<?php
// Koneksi database
$host     = "localhost";
$username = "root";
$password = "";
$dbname   = "kasir_reddra";

$mysqli = new mysqli($host, $username, $password, $dbname);
if ($mysqli->connect_errno) {
    die("Gagal koneksi ke database: " . $mysqli->connect_error);
}

// Ambil ID penjualan dari GET
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("ID transaksi tidak valid.");
}

// Ambil data transaksi dari tabel penjualan
$stmt = $mysqli->prepare("SELECT * FROM penjualan WHERE PenjualanID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    die("Transaksi tidak ditemukan.");
}
$penjualan = $result->fetch_assoc();

// Jika transaksi sudah dibayar, tampilkan popup SweetAlert2 dan redirect
if ($penjualan['status_bayar'] === 'dibayar') {
    echo '<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Transaksi Sudah Dibayar</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>body { margin: 0; padding: 0; }</style>
</head>
<body>
<script>
Swal.fire({
  icon: "info",
  title: "Transaksi Sudah Dibayar",
  text: "Transaksi ini sudah dikonfirmasi.",
  timer: 1500,
  showConfirmButton: false,
  timerProgressBar: true
}).then(function() {
  window.location.href = "transaksi.php";
});
</script>
</body>
</html>';
    exit;
}

// Hitung total pembayaran dengan menjumlahkan total_harga dari detailpenjualan
$totalPembayaran = 0;
$qTotal = $mysqli->prepare("SELECT SUM(total_harga) as total FROM detailpenjualan WHERE PenjualanID = ?");
$qTotal->bind_param("i", $id);
$qTotal->execute();
$resTotal = $qTotal->get_result();
if ($rowTotal = $resTotal->fetch_assoc()){
    $totalPembayaran = $rowTotal['total'];
}

// Inisialisasi variabel pesan dan status
$message   = "";
$isSuccess = false;

// Proses form jika disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    // Ambil nominal pembayaran
    $jumlahUang = isset($_POST['jumlah_uang']) ? trim($_POST['jumlah_uang']) : "";
    
    // Validasi input: harus diisi dan berupa angka
    if ($jumlahUang === "" || !is_numeric($jumlahUang)) {
        $message = "Nominal pembayaran harus diisi dengan angka.";
    } elseif ($jumlahUang < $totalPembayaran) {
        $message = "Nominal pembayaran kurang. Total pembayaran adalah Rp" . number_format($totalPembayaran, 0, ',', '.');
    } else {
        // Hitung kembalian
        $kembalian = $jumlahUang - $totalPembayaran;
        // Generate kode pembayaran misalnya dengan menggunakan timestamp
        $kodePembayaran = time();
        
        // Update transaksi pada tabel penjualan
        $updateStmt = $mysqli->prepare("UPDATE penjualan SET tgl_bayar = NOW(), status_bayar = 'dibayar', updated_at = NOW() WHERE PenjualanID = ?");
        $updateStmt->bind_param("i", $id);
        if ($updateStmt->execute()) {
            // Update record di tabel detailpenjualan: set kode_pembayaran dan kembalian
            $updDetail = $mysqli->prepare("UPDATE detailpenjualan SET kode_pembayaran = ?, kembalian = ? WHERE PenjualanID = ?");
            $updDetail->bind_param("idi", $kodePembayaran, $kembalian, $id);
            if ($updDetail->execute()) {
                $isSuccess = true;
                $message = "Pembayaran berhasil dikonfirmasi! Kembalian: Rp" . number_format($kembalian, 0, ',', '.');
            } else {
                $message = "Gagal mengupdate detail transaksi: " . $mysqli->error;
            }
        } else {
            $message = "Gagal melakukan konfirmasi pembayaran: " . $mysqli->error;
        }
    }
    
    // Tampilkan notifikasi menggunakan SweetAlert2 dan redirect ke transaksi.php
    if ($isSuccess) {
        echo '<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Sukses</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>body { margin: 0; padding: 0; }</style>
</head>
<body>
<script>
Swal.fire({
  icon: "success",
  title: "Sukses",
  text: "'.$message.'",
  timer: 1500,
  showConfirmButton: false,
  timerProgressBar: true
}).then(function() {
  window.location.href = "transaksi.php";
});
</script>
</body>
</html>';
        exit;
    } else {
        echo '<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Gagal</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>body { margin: 0; padding: 0; }</style>
</head>
<body>
<script>
Swal.fire({
  icon: "error",
  title: "Gagal",
  text: "'.htmlspecialchars($message, ENT_QUOTES).'",
  timer: 2000,
  showConfirmButton: false,
  timerProgressBar: true
}).then(function() {
  window.location.href = "transaksi.php";
});
</script>
</body>
</html>';
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Pembayaran Premium</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Google Fonts: Poppins & Material Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
    :root {
        --primary-color: #E0AA6E;
        --secondary-color: #3D2B1F;
        --accent-gradient: linear-gradient(135deg, #E0AA6E 0%, #D4AF37 100%);
    }

    body {
        font-family: 'Poppins', sans-serif;
        background: url('data:image/svg+xml,<svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg"><path fill="%23E0AA6E20" d="M49,-38.7C61.3,-22.3,67,-0.3,62.2,17.7C57.4,35.7,42.1,49.7,23.9,58.7C5.7,67.7,-15.4,71.7,-29.8,63.7C-44.2,55.7,-51.9,35.7,-55.5,15.3C-59.1,-5.1,-58.7,-25.9,-49.4,-41.9C-40.1,-58,-22,-69.3,-1.9,-68.3C18.2,-67.2,36.7,-53.9,49,-38.7Z" transform="translate(100 100)"/></svg>'),
            linear-gradient(135deg, #fafafa 0%, #fff5eb 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0;
        padding: 20px;
    }

    .content-card {
        width: 100%;
        max-width: 680px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 24px;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        padding: 40px;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .content-card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: var(--accent-gradient);
        opacity: 0.1;
        transform: rotate(15deg);
        z-index: -1;
    }

    .card-header {
        text-align: center;
        margin-bottom: 32px;
        position: relative;
    }

    .card-header h2 {
        font-size: 2.2rem;
        color: var(--secondary-color);
        margin: 0 0 12px;
        position: relative;
        display: inline-block;
    }

    .card-header h2::after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 3px;
        background: var(--accent-gradient);
        border-radius: 2px;
    }

    .detail-transaksi {
        background: rgba(224, 170, 110, 0.08);
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 32px;
        position: relative;
    }

    .detail-transaksi p {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 0 0 16px;
        padding: 12px 0;
        border-bottom: 1px solid rgba(61, 43, 31, 0.1);
    }

    .detail-transaksi p:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    .detail-transaksi span:first-child {
        font-weight: 500;
        color: var(--secondary-color);
        opacity: 0.8;
        min-width: 160px;
    }

    .detail-transaksi span:last-child {
        font-weight: 600;
        color: var(--secondary-color);
        text-align: right;
    }

    .form-group {
        margin-bottom: 24px;
        position: relative;
    }

    .form-label {
        display: block;
        margin-bottom: 12px;
        font-weight: 600;
        color: var(--secondary-color);
        padding-left: 8px;
    }

    .input-container {
        position: relative;
        background: rgba(224, 170, 110, 0.05);
        border-radius: 12px;
        border: 1px solid rgba(61, 43, 31, 0.15);
        transition: all 0.3s ease;
    }

    .input-container:hover {
        border-color: var(--primary-color);
        box-shadow: 0 4px 12px rgba(224, 170, 110, 0.1);
    }

    .input-container:focus-within {
        border-color: var(--primary-color);
        box-shadow: 0 4px 16px rgba(224, 170, 110, 0.2);
    }

    .currency-prefix {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        font-weight: 500;
        color: var(--secondary-color);
        opacity: 0.6;
    }

    .form-control {
        width: 100%;
        padding: 16px 16px 16px 48px;
        border: none;
        background: transparent;
        font-size: 1.1rem;
        font-weight: 500;
        color: var(--secondary-color);
        border-radius: 12px;
    }

    .form-control:focus {
        outline: none;
        box-shadow: none;
    }

    button.btn-primary {
        width: 100%;
        padding: 18px;
        border: none;
        border-radius: 12px;
        background: var(--accent-gradient);
        color: white;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    button.btn-primary::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        transition: 0.5s;
    }

    button.btn-primary:hover::before {
        left: 100%;
    }

    button.btn-primary:hover {
        box-shadow: 0 8px 24px rgba(224, 170, 110, 0.4);
        transform: translateY(-2px);
    }

    .back-link {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 24px;
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .back-link:hover {
        color: var(--secondary-color);
        transform: translateX(5px);
    }

    .material-icons {
        margin-right: 8px;
        font-size: 1.2rem;
        transition: all 0.3s ease;
    }
    </style>
</head>

<body>
    <div class="content-card">
        <div class="card-header">
            <h2>Konfirmasi Pembayaran</h2>
        </div>

        <div class="detail-transaksi">
            <p>
                <span>Nomor Invoice</span>
                <span><?= htmlspecialchars($penjualan['invoice']) ?></span>
            </p>
            <p>
                <span>Tanggal Transaksi</span>
                <span><?= date("d/m/Y H:i", strtotime($penjualan['TanggalPenjualan'])) ?></span>
            </p>
            <p>
                <span>Status Pembayaran</span>
                <span style="color: <?= $penjualan['status_bayar'] === 'dibayar' ? '#2ecc71' : '#e74c3c' ?>;">
                    <?= ucfirst($penjualan['status_bayar']) ?>
                </span>
            </p>
            <p>
                <span>Total Pembayaran</span>
                <span style="font-size: 1.2rem; color: var(--secondary-color);">
                    Rp<?= number_format($totalPembayaran, 0, ',', '.') ?>
                </span>
            </p>
        </div>

        <form action="konfirmasi.php?id=<?= $id ?>" method="post">
            <div class="form-group">
                <label class="form-label">Masukkan Nominal Pembayaran</label>
                <div class="input-container">
                    <span class="currency-prefix">Rp</span>
                    <input type="number" name="jumlah_uang" id="jumlah_uang" class="form-control" min="0" step="1000"
                        placeholder="Contoh: 150.000" required>
                </div>
            </div>

            <button type="submit" class="btn-primary" name="submit">
                Konfirmasi Pembayaran
            </button>
        </form>

        <a href="transaksi.php" class="back-link">
            <span class="material-icons">arrow_back</span>
            Kembali ke Daftar Transaksi
        </a>
    </div>

    <!-- SweetAlert2 dengan Custom Animation -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    // Custom Success Alert
    const showSuccessAlert = (message) => {
        Swal.fire({
            title: 'Pembayaran Berhasil!',
            html: `<div style="position: relative;">
                <svg viewBox="0 0 100 100" style="width: 120px; margin: -20px auto 10px;">
                    <circle cx="50" cy="50" r="45" fill="none" stroke="#2ecc71" stroke-width="5" stroke-dasharray="283" stroke-dashoffset="283" style="animation: circle 1s ease-out forwards"/>
                    <path d="M30,55 L45,70 L70,35" fill="none" stroke="#fff" stroke-width="8" stroke-linecap="round" stroke-dasharray="60" stroke-dashoffset="60" style="animation: check 0.5s 0.5s ease-out forwards"/>
                </svg>
                <div style="font-size: 1.2em; margin-top: 20px;">${message}</div>
            </div>`,
            background: 'linear-gradient(135deg, #E0AA6E 0%, #D4AF37 100%)',
            color: '#fff',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            willOpen: () => {
                document.querySelector('body').style.overflow = 'hidden';
            },
            willClose: () => {
                document.querySelector('body').style.overflow = 'auto';
            }
        }).then(() => {
            window.location.href = "transaksi.php";
        });
    }

    // CSS Animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes circle {
            from { stroke-dashoffset: 283; }
            to { stroke-dashoffset: 0; }
        }
        @keyframes check {
            from { stroke-dashoffset: 60; }
            to { stroke-dashoffset: 0; }
        }
    `;
    document.head.appendChild(style);
    </script>
</body>

<?php $mysqli->close(); ?>

</html>