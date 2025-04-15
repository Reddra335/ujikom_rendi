<?php
// tambah_pelanggan.php

// Konfigurasi koneksi database
$host     = "localhost";
$username = "root";
$password = "";
$dbname   = "kasir_reddra";

// Membuat koneksi menggunakan mysqli
$mysqli = new mysqli($host, $username, $password, $dbname);
if ($mysqli->connect_errno) {
    die("Gagal koneksi ke database: " . $mysqli->connect_error);
}

// Inisialisasi variabel untuk output pesan dan status
$message   = "";
$isSuccess = false;

// Proses form jika disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {

    // Tangkap dan sanitasi input pelanggan
    $namaPelanggan = isset($_POST['namaPelanggan']) ? trim($_POST['namaPelanggan']) : "";
    $alamat        = isset($_POST['alamat']) ? trim($_POST['alamat']) : "";
    $nomorTelepon  = isset($_POST['nomorTelepon']) ? trim($_POST['nomorTelepon']) : "";
    $jk            = isset($_POST['jk']) ? trim($_POST['jk']) : "";

    // Validasi input wajib
    if (empty($namaPelanggan) || empty($alamat) || empty($nomorTelepon) || empty($jk)) {
        $message = "Semua field harus diisi.";
    } elseif (!in_array($jk, ['Laki_Laki', 'Perempuan'])) {
        $message = "Jenis kelamin tidak valid.";
    }

    // Jika tidak ada error validasi, lakukan INSERT data pelanggan
    if ($message == "") {
        $stmt = $mysqli->prepare("INSERT INTO pelanggan (NamaPelanggan, Alamat, NomorTelepon, jk) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssss", $namaPelanggan, $alamat, $nomorTelepon, $jk);
            if ($stmt->execute()) {
                $message = "Pelanggan berhasil disimpan!";
                $isSuccess = true;
            } else {
                $message = "Error: Gagal menyimpan data pelanggan. " . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = "Error: Gagal menyiapkan statement. " . $mysqli->error;
        }
    }

    // Tampilkan notifikasi menggunakan SweetAlert2
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
  text: "Pelanggan berhasil disimpan!",
  timer: 1500,
  showConfirmButton: false,
  timerProgressBar: true
}).then(function() {
  window.location.href = "pelanggan.php";
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
  text: "' . htmlspecialchars($message, ENT_QUOTES) . '",
  timer: 2000,
  showConfirmButton: false,
  timerProgressBar: true
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
    <title>Input Pelanggan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Google Fonts: Playfair Display dan Poppins -->
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Poppins:wght@300;500;600;700&display=swap"
        rel="stylesheet">
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #fff;
        margin: 0;
        padding: 0;
    }

    /* Sama dengan tambah_produk.php, gunakan width 100% dan padding 20px */
    .content-card {
        width: 100%;
        padding: 20px;
        box-sizing: border-box;
    }

    .card-header h2 {
        text-align: center;
        margin-bottom: 20px;
        font-family: "Playfair Display", serif;
        color: #3D2B1F;
        transition: color 0.3s ease, text-shadow 0.3s ease;
    }

    .card-header h2:hover {
        color: #d4af37;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
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
            <h2>Form Input Pelanggan</h2>
        </div>
        <!-- Form Input Pelanggan -->
        <form action="" method="post">
            <div class="form-group">
                <label class="form-label" for="namaPelanggan">Nama Pelanggan:</label>
                <input type="text" name="namaPelanggan" id="namaPelanggan" class="form-control"
                    placeholder="Masukkan nama pelanggan" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="alamat">Alamat:</label>
                <textarea name="alamat" id="alamat" class="form-control" rows="4"
                    placeholder="Masukkan alamat pelanggan" required></textarea>
            </div>
            <div class="form-group">
                <label class="form-label" for="nomorTelepon">Nomor Telepon:</label>
                <input type="text" name="nomorTelepon" id="nomorTelepon" class="form-control"
                    placeholder="Masukkan nomor telepon" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="jk">Jenis Kelamin:</label>
                <select name="jk" id="jk" class="form-control" required>
                    <option value="" disabled selected>Pilih jenis kelamin</option>
                    <option value="Laki_Laki">Laki-Laki</option>
                    <option value="Perempuan">Perempuan</option>
                </select>
            </div>
            <button type="submit" class="btn-primary" name="submit">Simpan Pelanggan</button>
        </form>
        <a href="pelanggan.php" class="back-link">Kembali</a>
    </div>
    <?php $mysqli->close(); ?>
</body>

</html>