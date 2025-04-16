<?php
// hapus_pengguna.php

// Konfigurasi koneksi database
$mysqli = new mysqli("localhost", "root", "", "kasir_reddra");
if ($mysqli->connect_errno) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}

// Validasi parameter GET 'id'
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID pengguna tidak valid.");
}

$penggunaID = (int)$_GET['id'];

// Siapkan statement untuk menghapus data pengguna
$stmt = $mysqli->prepare("DELETE FROM user WHERE UserID = ?");
if ($stmt) {
    $stmt->bind_param("i", $penggunaID);
    if ($stmt->execute()) {
        // Jika sukses, tampilkan notifikasi sukses dan redirect ke halaman kelola_pengguna.php
        echo '<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Hapus Pengguna</title>
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
  text: "Pengguna berhasil dihapus!",
  timer: 1500,
  showConfirmButton: false,
  timerProgressBar: true
}).then(function() {
  window.location.href = "pengguna.php";
});
</script>
</body>
</html>';
    } else {
        // Jika terjadi error saat penghapusan, tampilkan notifikasi error
        echo '<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Hapus Pengguna</title>
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
  text: "Gagal menghapus pengguna. ' . htmlspecialchars($stmt->error, ENT_QUOTES) . '",
  timer: 2000,
  showConfirmButton: false,
  timerProgressBar: true
}).then(function() {
  window.location.href = "pengguna.php";
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