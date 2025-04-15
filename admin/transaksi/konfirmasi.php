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
    <title>Konfirmasi Pembayaran</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #fafafa;
        margin: 0;
        padding: 0;
    }

    .content-card {
        width: 100%;
        max-width: 600px;
        margin: 40px auto;
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        box-sizing: border-box;
    }

    .card-header h2 {
        text-align: center;
        margin-bottom: 20px;
        font-size: 1.8rem;
        color: #3D2B1F;
        transition: color 0.3s ease, text-shadow 0.3s ease;
    }

    .card-header h2:hover {
        color: #d4af37;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }

    .detail-transaksi {
        margin-bottom: 20px;
        font-size: 1rem;
        line-height: 1.5;
        color: #3D2B1F;
    }

    .detail-transaksi span {
        font-weight: 600;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #3D2B1F;
    }

    .form-control {
        width: 100%;
        padding: 0.8rem;
        border-radius: 8px;
        border: 1px solid #ddd;
        font-size: 1rem;
        box-sizing: border-box;
    }

    button.btn-primary {
        width: 100%;
        padding: 1rem;
        border: none;
        border-radius: 8px;
        background-color: #E0AA6E;
        color: #fff;
        font-size: 1rem;
        cursor: pointer;
        margin-top: 10px;
        transition: background 0.3s ease;
    }

    button.btn-primary:hover {
        background-color: #d4a373;
    }

    .back-link {
        display: block;
        text-align: center;
        margin-top: 1rem;
        color: #E0AA6E;
        text-decoration: none;
        font-weight: 600;
    }

    .back-link:hover {
        text-decoration: underline;
    }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="content-card">
        <div class="card-header">
            <h2>Konfirmasi Pembayaran</h2>
        </div>
        <div class="detail-transaksi">
            <p><span>Invoice:</span> <?= htmlspecialchars($penjualan['invoice']) ?></p>
            <p><span>Tanggal Penjualan:</span> <?= date("d/m/Y H:i", strtotime($penjualan['TanggalPenjualan'])) ?></p>
            <p><span>Status Bayar:</span> <?= htmlspecialchars($penjualan['status_bayar']) ?></p>
            <p><span>Total Pembayaran:</span> Rp<?= number_format($totalPembayaran, 0, ',', '.') ?></p>
        </div>
        <!-- Form Input Konfirmasi Pembayaran -->
        <form action="konfirmasi.php?id=<?= $id ?>" method="post">
            <div class="form-group">
                <label class="form-label" for="jumlah_uang">Masukkan Nominal Pembayaran (Rp):</label>
                <input type="number" name="jumlah_uang" id="jumlah_uang" class="form-control" min="0" step="any"
                    placeholder="Contoh: 150000" required>
            </div>
            <button type="submit" class="btn-primary" name="submit">Konfirmasi Pembayaran</button>
        </form>
        <a href="transaksi.php" class="back-link">Kembali ke Transaksi</a>
    </div>
    <?php $mysqli->close(); ?>
</body>

</html>