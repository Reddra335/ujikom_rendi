<?php
session_start();

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

// Koneksi ke database
$koneksi = new mysqli("localhost", "root", "", "kasir_reddra");
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Ambil PenjualanID dari parameter GET
$penjualanID = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$penjualanID) {
    echo "<script>alert('Penjualan ID tidak valid'); window.location.href = 'daftar_penjualan.php';</script>";
    exit;
}

// Hapus data penjualan (detail penjualan akan terhapus secara otomatis jika foreign key sudah disetting dengan ON DELETE CASCADE)
$stmt = $koneksi->prepare("DELETE FROM penjualan WHERE PenjualanID = ?");
$stmt->bind_param("i", $penjualanID);

if ($stmt->execute()) {
    $stmt->close();
    echo '<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Sukses Hapus</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>body { margin:0; padding:0; }</style>
</head>
<body>
<script>
Swal.fire({
  icon: "success",
  title: "Terhapus",
  text: "Penjualan berhasil dihapus!",
  timer: 1500,
  showConfirmButton: false
}).then(function(){
  window.location.href = "penjualan.php";
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
  <title>Gagal Hapus</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>body { margin:0; padding:0; }</style>
</head>
<body>
<script>
Swal.fire({
  icon: "error",
  title: "Gagal",
  text: "Gagal menghapus penjualan: ' . htmlspecialchars($stmt->error, ENT_QUOTES) . '",
  timer: 2000,
  showConfirmButton: false
}).then(function(){
  window.location.href = "penjualan.php";
});
</script>
</body>
</html>';
    $stmt->close();
    exit;
}


?>