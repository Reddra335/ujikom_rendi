<?php
session_start();

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

// Koneksi ke database
$mysqli = new mysqli("localhost", "root", "", "kasir_reddra");
if ($mysqli->connect_errno) {
    die("Gagal koneksi ke database: " . $mysqli->connect_error);
}

// Pastikan ada parameter id penjualan
if (!isset($_GET['id'])) {
    die("ID penjualan tidak ditemukan.");
}
$penjualanID = intval($_GET['id']);

// Ambil data header penjualan
$stmt = $mysqli->prepare("SELECT * FROM penjualan WHERE PenjualanID = ?");
$stmt->bind_param("i", $penjualanID);
$stmt->execute();
$resultHeader = $stmt->get_result();
$headerData = $resultHeader->fetch_assoc();
if (!$headerData) {
    die("Data penjualan tidak ditemukan.");
}
$stmt->close();

// Ambil data detail penjualan (khususnya kolom barang_dibeli)
$stmt = $mysqli->prepare("SELECT * FROM detailpenjualan WHERE PenjualanID = ?");
$stmt->bind_param("i", $penjualanID);
$stmt->execute();
$resultDetail = $stmt->get_result();
$detailData = $resultDetail->fetch_assoc();
$stmt->close();

// Ambil data pelanggan untuk dropdown
$pelangganList = [];
$sqlPelanggan = "SELECT PelangganID, NamaPelanggan FROM pelanggan";
$resultPelanggan = $mysqli->query($sqlPelanggan);
if ($resultPelanggan) {
    while ($row = $resultPelanggan->fetch_assoc()) {
        $pelangganList[] = $row;
    }
}

// Pisahkan daftar barang yang dibeli (disimpan sebagai string terpisah koma) menjadi array
$purchasedItems = [];
if (!empty($detailData['barang_dibeli'])) {
    $purchasedItems = explode(",", $detailData['barang_dibeli']);
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    // Ambil pelanggan baru dari form
    $newPelangganID = (isset($_POST['pelanggan']) && $_POST['pelanggan'] !== "") ? intval($_POST['pelanggan']) : NULL;

    // Update hanya kolom pelanggan pada tabel penjualan
    $stmt = $mysqli->prepare("UPDATE penjualan SET PelangganID = ?, updated_at = NOW() WHERE PenjualanID = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $newPelangganID, $penjualanID);
        if ($stmt->execute()) {
            echo '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sukses Update</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>body { margin:0; padding:0; }</style>
</head>
<body>
<script>
Swal.fire({
  icon: "success",
  title: "Sukses",
  text: "Pelanggan berhasil diperbarui!",
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
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Error: Gagal menyiapkan statement. " . $mysqli->error;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Pelanggan Penjualan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Google Fonts & SweetAlert2 CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600;700&display=swap" rel="stylesheet">
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
        max-width: 100%;
        margin: 0;
        padding: 20px;
    }

    h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #3D2B1F;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-label {
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #3D2B1F;
    }

    .form-control,
    select {
        width: 100%;
        padding: 0.8rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
    }

    button.btn-primary {
        width: 100%;
        padding: 1rem;
        border: none;
        border-radius: 4px;
        background-color: #E0AA6E;
        color: #fff;
        font-size: 1rem;
        cursor: pointer;
    }

    button.btn-primary:hover {
        background-color: #d4a373;
    }

    .readonly-field {
        background-color: #eee;
        padding: 0.5rem;
    }

    /* Style untuk tabel daftar barang, 100% lebar */
    .full-width-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    .full-width-table th,
    .full-width-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    .full-width-table th {
        background-color: #f2f2f2;
    }
    </style>
</head>

<body>
    <div class="content-card">
        <h2>Edit Pelanggan Penjualan</h2>

        <!-- Tampilkan informasi transaksi untuk referensi -->
        <div class="form-group">
            <label class="form-label">Invoice:</label>
            <div class="readonly-field"><?= htmlspecialchars($headerData['invoice']) ?></div>
        </div>
        <div class="form-group">
            <label class="form-label">Tanggal Penjualan:</label>
            <div class="readonly-field"><?= date('d/m/Y H:i', strtotime($headerData['TanggalPenjualan'])) ?></div>
        </div>

        <!-- Tampilkan daftar barang yang dibeli (penuh lebar) -->
        <div class="form-group">
            <label class="form-label">Barang yang Dibeli:</label>
            <?php if (count($purchasedItems) > 0): ?>
            <table class="full-width-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Barang</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($purchasedItems as $index => $barang): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($barang) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="readonly-field">Tidak ada data barang yang dibeli.</div>
            <?php endif; ?>
        </div>

        <!-- Form edit untuk mengganti pelanggan -->
        <form action="" method="post" id="editForm">
            <div class="form-group">
                <label class="form-label" for="pelanggan">Pilih Pelanggan:</label>
                <select name="pelanggan" id="pelanggan" class="form-control">
                    <option value="">-- Pilih Pelanggan --</option>
                    <?php foreach($pelangganList as $p): ?>
                    <option value="<?= $p['PelangganID'] ?>"
                        <?= ($p['PelangganID'] == $headerData['PelangganID']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['NamaPelanggan']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" name="submit" class="btn-primary">Update Pelanggan</button>
            <button type="button" onclick="window.location.href='penjualan.php';" class="btn-primary"
                style="margin-top:10px;">Kembali</button>
        </form>
    </div>
</body>

</html>
<?php $mysqli->close(); ?>