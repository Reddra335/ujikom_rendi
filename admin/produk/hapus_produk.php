<?php
// hapus_produk.php

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

// Validasi parameter ID produk dari URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID produk tidak valid.");
}
$produkId = (int)$_GET['id'];

// Ambil data produk untuk mendapatkan informasi file gambar (jika ada)
$stmt = $mysqli->prepare("SELECT gambar FROM produk WHERE ProdukID = ?");
$stmt->bind_param("i", $produkId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Produk tidak ditemukan.");
}
$produk = $result->fetch_assoc();
$stmt->close();

// Jika ada file gambar dan file tersebut ada di server, hapus file gambar tersebut
if (!empty($produk['gambar']) && file_exists($produk['gambar'])) {
    unlink($produk['gambar']);
}

// Hapus data produk dari database
$stmtDelete = $mysqli->prepare("DELETE FROM produk WHERE ProdukID = ?");
$stmtDelete->bind_param("i", $produkId);
if ($stmtDelete->execute()) {
    if ($stmtDelete->affected_rows > 0) {
        // Notifikasi jika berhasil dihapus
        $icon  = "success";
        $title = "Sukses";
        $msg   = "Produk berhasil dihapus!";
    } else {
        // Kondisi tidak ada baris yang dihapus
        $icon  = "error";
        $title = "Gagal";
        $msg   = "Produk tidak ditemukan atau sudah dihapus.";
    }
} else {
    // Notifikasi jika terjadi error saat menghapus
    $icon  = "error";
    $title = "Gagal";
    $msg   = "Error: Gagal menghapus produk. " . $stmtDelete->error;
}
$stmtDelete->close();
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Hapus Produk</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
    body {
        margin: 0;
        padding: 0;
    }
    </style>
</head>

<body>
    <script>
    // Tampilkan popup notifikasi menggunakan SweetAlert2 berdasarkan hasil hapus data
    Swal.fire({
        icon: "<?php echo $icon; ?>",
        title: "<?php echo $title; ?>",
        text: "<?php echo htmlspecialchars($msg, ENT_QUOTES); ?>",
        timer: 1500,
        showConfirmButton: false,
        timerProgressBar: true
    }).then(function() {
        window.location.href = "produk.php";
    });
    </script>
</body>

</html>