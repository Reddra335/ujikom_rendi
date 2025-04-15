<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Konfigurasi koneksi ke database
$host     = "localhost";
$username = "root";
$password = "";
$dbname   = "kasir_reddra";
$mysqli   = new mysqli($host, $username, $password, $dbname);
if ($mysqli->connect_errno) {
    die("Gagal koneksi ke database: " . $mysqli->connect_error);
}

// Ambil data order dari POST
$customer   = isset($_POST['customer']) ? intval($_POST['customer']) : NULL;
$totalOrder = isset($_POST['total_order']) ? floatval($_POST['total_order']) : 0;
$diskon     = isset($_POST['diskon']) ? floatval($_POST['diskon']) : 0;
$pajak      = isset($_POST['pajak']) ? floatval($_POST['pajak']) : 0;

// Data detail order (array)
$produkArr   = isset($_POST['produk']) ? $_POST['produk'] : [];
$jumlahArr   = isset($_POST['jumlah']) ? $_POST['jumlah'] : [];
$subtotalArr = isset($_POST['subtotal']) ? $_POST['subtotal'] : [];
$user_id     = $_SESSION['user_id']; // Kasir yang sedang login

// Buat invoice unik
$invoice = "INV" . time();

// Simpan header order ke tabel "penjualan"
$stmt = $mysqli->prepare("INSERT INTO penjualan (TanggalPenjualan, tgl_bayar, PelangganID, status_bayar, invoice, diskon, pajak) VALUES (NOW(), NULL, ?, 'belum dibayar', ?, ?, ?)");
if (!$stmt) {
    die("Gagal menyiapkan statement: " . $mysqli->error);
}
$stmt->bind_param("isdd", $customer, $invoice, $diskon, $pajak);
if (!$stmt->execute()) {
    die("Gagal menyimpan header order: " . $stmt->error);
}
$penjualanID = $mysqli->insert_id;
$stmt->close();

// Agregasi detail order berdasarkan ProdukID agar produk yang sama tidak duplikat
$aggregatedDetails = [];
for ($i = 0; $i < count($produkArr); $i++) {
    $prodID      = intval($produkArr[$i]);
    $jumlahItem  = intval($jumlahArr[$i]);
    $subTotalItem = floatval($subtotalArr[$i]);
    
    if (isset($aggregatedDetails[$prodID])) {
        // Tambahkan jumlah dan subtotal jika produk sudah ada di aggregator
        $aggregatedDetails[$prodID]['jumlah'] += $jumlahItem;
        $aggregatedDetails[$prodID]['subtotal'] += $subTotalItem;
    } else {
        $aggregatedDetails[$prodID] = [
            'user_id'  => $user_id,
            'jumlah'   => $jumlahItem,
            'subtotal' => $subTotalItem
        ];
    }
}

// Simpan detail order ke tabel "detailpenjualan"
// Misalnya, kita set kode_pembayaran = 0, total_harga = total order akhir, dan kembalian = 0 (default)
foreach ($aggregatedDetails as $prodID => $data) {
    $dummyKembalian = 0;
    $finalTotal = $totalOrder; // Atau Anda bisa menghitung ulang jika perlu
    $stmtDet = $mysqli->prepare("INSERT INTO detailpenjualan (PenjualanID, ProdukID, user_id, JumlahProduk, Subtotal, kode_pembayaran, total_harga, kembalian) VALUES (?, ?, ?, ?, ?, 0, ?, ?)");
    if (!$stmtDet) {
        die("Gagal menyiapkan statement detail: " . $mysqli->error);
    }
    $stmtDet->bind_param("iiiiddi", $penjualanID, $prodID, $data['user_id'], $data['jumlah'], $data['subtotal'], $finalTotal, $dummyKembalian);
    if (!$stmtDet->execute()) {
        die("Gagal menyimpan detail order: " . $stmtDet->error);
    }
    $stmtDet->close();

    // Update stok produk: kurangi stok sesuai jumlah yang terjual
    $stmtStock = $mysqli->prepare("UPDATE produk SET Stok = Stok - ? WHERE ProdukID = ?");
    if ($stmtStock) {
        $stmtStock->bind_param("ii", $data['jumlah'], $prodID);
        $stmtStock->execute();
        $stmtStock->close();
    }
}

$mysqli->close();

// Setelah berhasil memproses order, redirect ke halaman konfirmasi atau tampilkan pesan sukses.
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Order Berhasil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
    body {
        font-family: Arial, sans-serif;
        background: #f2f2f2;
        text-align: center;
        padding: 50px;
    }

    .message {
        background: #fff;
        display: inline-block;
        padding: 20px;
        border-radius: 4px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    a {
        display: inline-block;
        margin-top: 20px;
        padding: 10px 20px;
        background: #E0AA6E;
        color: #fff;
        text-decoration: none;
        border-radius: 4px;
    }

    a:hover {
        background: #d4a373;
    }
    </style>
</head>

<body>
    <div class="message">
        <h2>Order Berhasil</h2>
        <p>Terima kasih, order Anda telah diproses.</p>
        <a href="penjualan.php">Kembali ke halaman pembelian</a>
    </div>
</body>

</html>