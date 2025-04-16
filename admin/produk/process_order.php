<?php
// process_order.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User belum login']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Permintaan tidak valid']);
    exit;
}

$host = "localhost";
$username = "root";
$password = "";
$dbname = "kasir_reddra";

$mysqli = new mysqli($host, $username, $password, $dbname);
if ($mysqli->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal: ' . $mysqli->connect_error]);
    exit;
}

// Ambil input
$product_id   = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity     = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
$pelanggan_id = isset($_POST['pelanggan_id']) ? intval($_POST['pelanggan_id']) : 0;
$user_id      = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$discount     = isset($_POST['discount']) ? floatval($_POST['discount']) : 0;
$tax          = isset($_POST['tax']) ? floatval($_POST['tax']) : 0;

if ($product_id <= 0 || $quantity <= 0 || $pelanggan_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Input tidak lengkap']);
    exit;
}

// Dapatkan informasi produk
$stmt = $mysqli->prepare("SELECT NamaProduk, Harga, Stok FROM produk WHERE ProdukID = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare error: ' . $mysqli->error]);
    exit;
}
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan']);
    exit;
}
$product = $result->fetch_assoc();
$stmt->close();

if ($quantity > $product['Stok']) {
    echo json_encode(['success' => false, 'message' => 'Stok tidak mencukupi']);
    exit;
}

// Hitung subtotal, diskon, pajak, dan total
$subtotal = round($product['Harga'] * $quantity);  // dibulatkan ke integer
$discount_amount = ($discount / 100) * $subtotal;
$subAfterDiscount = $subtotal - $discount_amount;
$tax_amount = ($tax / 100) * $subAfterDiscount;
$total = round($subAfterDiscount + $tax_amount); // dibulatkan agar sesuai dengan kolom integer

// Buat invoice
$invoice = "INV" . time();

// Simpan ke tabel penjualan (header)
$stmt = $mysqli->prepare("INSERT INTO penjualan (TanggalPenjualan, tgl_bayar, PelangganID, status_bayar, invoice, diskon, pajak) VALUES (NOW(), NULL, ?, 'belum dibayar', ?, ?, ?)");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare penjualan error: ' . $mysqli->error]);
    exit;
}
$stmt->bind_param("isdd", $pelanggan_id, $invoice, $discount, $tax);
if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan header penjualan: ' . $stmt->error]);
    exit;
}
$penjualanID = $mysqli->insert_id;
$stmt->close();

// Update stok produk
$stmt = $mysqli->prepare("UPDATE produk SET Stok = Stok - ? WHERE ProdukID = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare update stok error: ' . $mysqli->error]);
    exit;
}
$stmt->bind_param("ii", $quantity, $product_id);
if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Gagal mengupdate stok: ' . $stmt->error]);
    exit;
}
$stmt->close();

// Simpan ke tabel detailpenjualan
// Kolom: PenjualanID, ProdukID, user_id, JumlahProduk, Subtotal, kode_pembayaran (0), total_harga, kembalian (0), barang_dibeli
// Pastikan urutan dan tipe data sesuai dengan struktur tabel.
// ProdukID akan disimpan sebagai string.
$product_id_str = strval($product_id);
$stmt = $mysqli->prepare("INSERT INTO detailpenjualan (PenjualanID, ProdukID, user_id, JumlahProduk, Subtotal, kode_pembayaran, total_harga, kembalian, barang_dibeli) VALUES (?, ?, ?, ?, ?, 0, ?, 0, ?)");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare detail penjualan error: ' . $mysqli->error]);
    exit;
}
// Gunakan binding "isiiiis" untuk total_harga sebagai integer (i)
$stmt->bind_param("isiiiis", $penjualanID, $product_id_str, $user_id, $quantity, $subtotal, $total, $product['NamaProduk']);
if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan detail penjualan: ' . $stmt->error]);
    exit;
}
$stmt->close();

// Hitung stok baru untuk dikirim ke client
$new_stock = $product['Stok'] - $quantity;

echo json_encode(['success' => true, 'message' => 'Pesanan berhasil diproses', 'new_stock' => $new_stock]);
?>