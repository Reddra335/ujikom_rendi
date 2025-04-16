<?php
// hapus_pelanggan.php

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

// Pastikan parameter GET 'id' ada dan valid
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID pelanggan tidak valid.");
}

$pelangganID = (int)$_GET['id'];

// Siapkan statement untuk menghapus data pelanggan
$stmt = $mysqli->prepare("DELETE FROM pelanggan WHERE PelangganID = ?");
if ($stmt) {
    $stmt->bind_param("i", $pelangganID);
    if ($stmt->execute()) {
        // Jika sukses, tampilkan notifikasi dan redirect ke halaman daftar pelanggan
        echo '<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Hapus Pelanggan</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body { margin: 0; padding: 0; }
  </style>
</head>
<body>
<script>
Swal.fire({
  icon: "success",
  title: "Sukses",
  text: "Pelanggan berhasil dihapus!",
  timer: 1500,
  showConfirmButton: false,
  timerProgressBar: true
}).then(function() {
  window.location.href = "pelanggan.php";
});
</script>
</body>
</html>';
    } else {
        // Jika terjadi error saat menghapus data
        echo '<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Hapus Pelanggan</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body { margin: 0; padding: 0; }
  </style>
</head>
<body>
<script>
Swal.fire({
  icon: "error",
  title: "Gagal",
  text: "Gagal menghapus pelanggan. ' . htmlspecialchars($stmt->error, ENT_QUOTES) . '",
  timer: 2000,
  showConfirmButton: false,
  timerProgressBar: true
}).then(function() {
  window.location.href = "pelanggan.php";
});
</script>
</body>
</html>';
    }
    $stmt->close();
} else {
    echo "Gagal menyiapkan statement: " . $mysqli->error;
}

$mysqli->close();
?>