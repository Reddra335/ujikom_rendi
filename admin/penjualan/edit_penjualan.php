<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

// Koneksi ke database
$mysqli = new mysqli("localhost", "root", "", "kasir_reddra");
if ($mysqli->connect_errno) {
    die("Gagal koneksi ke database: " . $mysqli->connect_error);
}

// Ambil PenjualanID dari URL
$penjualanID = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$penjualanID) {
    die("Penjualan ID tidak valid!");
}

// Ambil data header penjualan
$stmt = $mysqli->prepare("SELECT * FROM penjualan WHERE PenjualanID = ?");
$stmt->bind_param("i", $penjualanID);
$stmt->execute();
$result = $stmt->get_result();
$penjualan = $result->fetch_assoc();
$stmt->close();
if (!$penjualan) {
    die("Data penjualan tidak ditemukan!");
}

// Ambil data detail penjualan (diasumsikan tersimpan dalam satu baris)
$stmt = $mysqli->prepare("SELECT * FROM detailpenjualan WHERE PenjualanID = ?");
$stmt->bind_param("i", $penjualanID);
$stmt->execute();
$result = $stmt->get_result();
$detailPenjualan = $result->fetch_assoc();  // hanya ada 1 baris (data aggregate)
$stmt->close();

// Ambil data produk untuk dropdown
$productList = [];
$resultProducts = $mysqli->query("SELECT ProdukID, NamaProduk, Harga, Stok FROM produk");
if ($resultProducts) {
    while ($row = $resultProducts->fetch_assoc()) {
        $productList[] = $row;
    }
}

// Ambil data pelanggan
$pelangganList = [];
$resultPelanggan = $mysqli->query("SELECT PelangganID, NamaPelanggan FROM pelanggan");
if ($resultPelanggan) {
    while ($row = $resultPelanggan->fetch_assoc()) {
        $pelangganList[] = $row;
    }
}

// Proses update saat form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $pelangganIDPost = isset($_POST['pelanggan']) && $_POST['pelanggan'] !== "" ? intval($_POST['pelanggan']) : NULL;
    $diskon = isset($_POST['diskon']) ? floatval($_POST['diskon']) : 0;
    $pajak  = isset($_POST['pajak']) ? floatval($_POST['pajak']) : 0;
    
    // Ambil data detail dari form yang dikirim melalui input hidden
    $produkIDs = isset($_POST['produk']) ? $_POST['produk'] : [];   // Array product ID
    $jumlahs   = isset($_POST['jumlah']) ? $_POST['jumlah'] : [];       // Array jumlah per produk
    $subtotals = isset($_POST['subtotal']) ? $_POST['subtotal'] : [];   // Array subtotal per produk
    
    // Agregasikan detail: hitung total kuantitas & subtotal dan gabungkan product id serta nama produk
    $totalQuantity = 0;
    $totalSubtotal = 0;
    $listProductIDs = [];
    $listProductNames = [];
    
    $countDetail = count($produkIDs);
    for ($i = 0; $i < $countDetail; $i++) {
        $prodID = intval($produkIDs[$i]);
        $jumlahItem = isset($jumlahs[$i]) ? intval($jumlahs[$i]) : 0;
        $subTotalItem = isset($subtotals[$i]) ? floatval($subtotals[$i]) : 0;
        
        $totalQuantity += $jumlahItem;
        $totalSubtotal += $subTotalItem;
        $listProductIDs[] = $prodID;
        
        // Cari nama produk dari productList
        foreach ($productList as $prod) {
            if ($prod['ProdukID'] == $prodID) {
                $listProductNames[] = $prod['NamaProduk'];
                break;
            }
        }
    }
    
    // Hitung total akhir dengan diskon dan pajak
    $discountAmount = ($diskon / 100) * $totalSubtotal;
    $subAfterDiscount = $totalSubtotal - $discountAmount;
    $taxAmount = ($pajak / 100) * $subAfterDiscount;
    $finalTotal = $subAfterDiscount + $taxAmount;
    
    // Gabungkan product IDs dan nama produk menjadi string (gunakan array_unique untuk produk id)
    $aggregatedProductIDs = implode(",", array_unique($listProductIDs));
    $aggregatedProductNames = implode(",", $listProductNames);
    
    // Update header penjualan
    $stmt = $mysqli->prepare("UPDATE penjualan SET PelangganID = ?, diskon = ?, pajak = ? WHERE PenjualanID = ?");
    $stmt->bind_param("iddi", $pelangganIDPost, $diskon, $pajak, $penjualanID);
    $stmt->execute();
    $stmt->close();
    
    // Hapus detail penjualan lama
    $stmt = $mysqli->prepare("DELETE FROM detailpenjualan WHERE PenjualanID = ?");
    $stmt->bind_param("i", $penjualanID);
    $stmt->execute();
    $stmt->close();
    
    // Insert detail penjualan baru dalam satu baris (data aggregate)
    $stmtDet = $mysqli->prepare("INSERT INTO detailpenjualan (PenjualanID, ProdukID, user_id, JumlahProduk, Subtotal, kode_pembayaran, total_harga, kembalian, barang_dibeli) VALUES (?, ?, ?, ?, ?, 0, ?, 0, ?)");
    $userID = $_SESSION['user_id'];
    $stmtDet->bind_param("isiidds", $penjualanID, $aggregatedProductIDs, $userID, $totalQuantity, $totalSubtotal, $finalTotal, $aggregatedProductNames);
    $stmtDet->execute();
    $stmtDet->close();
    
    echo '<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Sukses</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>body { margin:0; padding:0; }</style>
</head>
<body>
<script>
Swal.fire({
  icon: "success",
  title: "Sukses",
  text: "Penjualan berhasil diperbarui!",
  timer: 1500,
  showConfirmButton: false
}).then(function(){
  window.location.href = "penjualan.php";
});
</script>
</body>
</html>';
    exit;
}

// Untuk menampilkan form, pecah nilai detail yang tersimpan menggunakan explode dengan aman
$detailProdukIDs = !empty($detailPenjualan['ProdukID']) ? explode(",", $detailPenjualan['ProdukID']) : [];
$detailJumlah = !empty($detailPenjualan['JumlahProduk']) ? explode(",", $detailPenjualan['JumlahProduk']) : [];
$detailSubtotal = !empty($detailPenjualan['Subtotal']) ? explode(",", $detailPenjualan['Subtotal']) : [];

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Penjualan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600;700&display=swap" rel="stylesheet">
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
        padding: 20px;
        box-sizing: border-box;
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
        margin-top: 10px;
    }

    button.btn-primary:hover {
        background-color: #d4a373;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    table,
    th,
    td {
        border: 1px solid #ddd;
    }

    th,
    td {
        padding: 10px;
        text-align: center;
    }

    .btn-add {
        margin-top: 10px;
    }
    </style>
</head>

<body>
    <div class="content-card">
        <h2>Edit Penjualan</h2>
        <form action="" method="post" id="penjualanForm">
            <!-- Pilih Pelanggan -->
            <div class="form-group">
                <label class="form-label" for="pelanggan">Pembeli:</label>
                <select name="pelanggan" id="pelanggan" class="form-control">
                    <option value="">-- Pilih Pelanggan --</option>
                    <?php foreach($pelangganList as $p): ?>
                    <option value="<?= $p['PelangganID'] ?>"
                        <?= ($penjualan['PelangganID'] == $p['PelangganID'] ? 'selected' : '') ?>>
                        <?= htmlspecialchars($p['NamaPelanggan']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Input Diskon & Pajak -->
            <div class="form-group">
                <label class="form-label">Diskon (%)</label>
                <input type="number" id="diskonInput" class="form-control"
                    value="<?= htmlspecialchars($penjualan['diskon']) ?>" step="0.01">
            </div>
            <div class="form-group">
                <label class="form-label">Pajak (%)</label>
                <input type="number" id="pajakInput" class="form-control"
                    value="<?= htmlspecialchars($penjualan['pajak']) ?>" step="0.01">
            </div>

            <!-- Tabel Detail Penjualan -->
            <table id="detailTable">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Harga</th>
                        <th>Stok Tersedia</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($detailProdukIDs[0])): ?>
                    <?php foreach ($detailProdukIDs as $index => $prodID): 
                            // Gunakan isset untuk menghindari undefined array key
                            $jumlahVal = isset($detailJumlah[$index]) ? $detailJumlah[$index] : "0";
                            $subtotalVal = isset($detailSubtotal[$index]) ? $detailSubtotal[$index] : "0.00";
                            // Cari info produk dari productList
                            $prodInfo = array_filter($productList, function($prod) use ($prodID) {
                                return $prod['ProdukID'] == $prodID;
                            });
                            $prodInfo = !empty($prodInfo) ? array_shift($prodInfo) : [];
                        ?>
                    <tr>
                        <td>
                            <select class="form-control" onchange="updateRow(this)">
                                <option value="">Pilih Produk</option>
                                <?php foreach ($productList as $prod): ?>
                                <option value="<?= $prod['ProdukID'] ?>" data-harga="<?= $prod['Harga'] ?>"
                                    data-stok="<?= $prod['Stok'] ?>"
                                    <?= ($prodID == $prod['ProdukID'] ? 'selected' : '') ?>>
                                    <?= htmlspecialchars($prod['NamaProduk']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control" readonly
                                value="<?= isset($prodInfo['Harga']) ? number_format($prodInfo['Harga'], 2) : '0.00' ?>">
                        </td>
                        <td>
                            <input type="text" class="form-control" readonly
                                value="<?= isset($prodInfo['Stok']) ? $prodInfo['Stok'] : '0' ?>">
                        </td>
                        <td>
                            <input type="number" class="form-control" value="<?= htmlspecialchars($jumlahVal) ?>"
                                min="0" onchange="updateRow(this)">
                        </td>
                        <td>
                            <input type="text" class="form-control" readonly
                                value="<?= number_format($subtotalVal, 2) ?>">
                        </td>
                        <td>
                            <button type="button" onclick="removeRow(this)">Hapus</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="6">Tidak ada detail penjualan</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <button type="button" class="btn-primary btn-add" onclick="addRow()">Tambah Barang</button>

            <!-- Total Penjualan -->
            <div class="form-group" style="margin-top:20px;">
                <label class="form-label">Total Penjualan</label>
                <input type="text" id="totalPenjualan" class="form-control" readonly value="0">
            </div>

            <!-- Hidden Fields untuk Diskon, Pajak, dan Total -->
            <input type="hidden" name="diskon" id="hiddenDiskon">
            <input type="hidden" name="pajak" id="hiddenPajak">
            <input type="hidden" name="total_penjualan" id="hiddenTotal">

            <!-- Tempat untuk menyimpan input detail secara hidden -->
            <div id="detailInputs"></div>

            <button type="submit" name="submit" class="btn-primary">Simpan Perubahan</button>
            <button type="button" onclick="window.location.href='penjualan.php';" class="btn-primary">Kembali</button>
        </form>
    </div>

    <script>
    const products = <?php echo json_encode($productList); ?>;

    // Fungsi untuk menambahkan baris detail baru
    function addRow() {
        const tbody = document.querySelector("#detailTable tbody");
        const row = document.createElement("tr");

        // Cell: Dropdown Produk
        const cellProduk = document.createElement("td");
        let selectHtml = `<select class="form-control" onchange="updateRow(this)">
                                <option value="">Pilih Produk</option>`;
        products.forEach(prod => {
            selectHtml +=
                `<option value="${prod.ProdukID}" data-harga="${prod.Harga}" data-stok="${prod.Stok}">${prod.NamaProduk}</option>`;
        });
        selectHtml += `</select>`;
        cellProduk.innerHTML = selectHtml;
        row.appendChild(cellProduk);

        // Cell: Harga
        const cellHarga = document.createElement("td");
        cellHarga.innerHTML = `<input type="text" class="form-control" readonly value="0">`;
        row.appendChild(cellHarga);

        // Cell: Stok Tersedia
        const cellStok = document.createElement("td");
        cellStok.innerHTML = `<input type="text" class="form-control" readonly value="0">`;
        row.appendChild(cellStok);

        // Cell: Jumlah
        const cellJumlah = document.createElement("td");
        cellJumlah.innerHTML =
        `<input type="number" class="form-control" value="0" min="0" onchange="updateRow(this)">`;
        row.appendChild(cellJumlah);

        // Cell: Subtotal
        const cellSubtotal = document.createElement("td");
        cellSubtotal.innerHTML = `<input type="text" class="form-control" readonly value="0">`;
        row.appendChild(cellSubtotal);

        // Cell: Aksi
        const cellAksi = document.createElement("td");
        cellAksi.innerHTML = `<button type="button" onclick="removeRow(this)">Hapus</button>`;
        row.appendChild(cellAksi);

        tbody.appendChild(row);
        calculateTotal();
    }

    // Fungsi untuk mengupdate baris detail saat produk dipilih atau jumlah diubah
    function updateRow(el) {
        const row = el.closest("tr");
        const select = row.querySelector("select");
        const hargaInput = row.children[1].querySelector("input");
        const stokInput = row.children[2].querySelector("input");
        const jumlahInput = row.children[3].querySelector("input");
        const subtotalInput = row.children[4].querySelector("input");

        if (select.value !== "") {
            const selectedOption = select.options[select.selectedIndex];
            const harga = parseFloat(selectedOption.getAttribute("data-harga"));
            const stok = parseInt(selectedOption.getAttribute("data-stok"));
            hargaInput.value = harga.toFixed(2);
            stokInput.value = stok;

            let jumlah = parseInt(jumlahInput.value);
            if (jumlah > stok) {
                Swal.fire({
                    icon: "warning",
                    title: "Stok tidak cukup",
                    text: "Jumlah yang dimasukkan melebihi stok tersedia.",
                    timer: 2000,
                    showConfirmButton: false
                });
                jumlahInput.value = stok;
                jumlah = stok;
            }
            subtotalInput.value = (harga * jumlah).toFixed(2);
        } else {
            hargaInput.value = "0";
            stokInput.value = "0";
            subtotalInput.value = "0";
        }
        calculateTotal();
    }

    // Fungsi untuk menghapus baris detail
    function removeRow(el) {
        const row = el.closest("tr");
        row.parentNode.removeChild(row);
        calculateTotal();
    }

    // Fungsi untuk menghitung total penjualan berdasarkan detail, diskon, dan pajak
    function calculateTotal() {
        let total = 0;
        document.querySelectorAll("#detailTable tbody tr").forEach(row => {
            const subtotal = parseFloat(row.children[4].querySelector("input").value) || 0;
            total += subtotal;
        });

        const diskonPercent = parseFloat(document.getElementById("diskonInput").value) || 0;
        const pajakPercent = parseFloat(document.getElementById("pajakInput").value) || 0;
        const nilaiDiskon = total * diskonPercent / 100;
        const nilaiPajak = (total - nilaiDiskon) * pajakPercent / 100;
        const totalAkhir = total - nilaiDiskon + nilaiPajak;

        document.getElementById("totalPenjualan").value = totalAkhir.toFixed(2);
        document.getElementById("hiddenDiskon").value = nilaiDiskon.toFixed(2);
        document.getElementById("hiddenPajak").value = nilaiPajak.toFixed(2);
        document.getElementById("hiddenTotal").value = totalAkhir.toFixed(2);
    }

    // Update kalkulasi saat diskon atau pajak berubah
    document.getElementById("diskonInput").addEventListener("input", calculateTotal);
    document.getElementById("pajakInput").addEventListener("input", calculateTotal);

    // Sebelum submit, kumpulkan detail item ke dalam input hidden
    document.getElementById("penjualanForm").addEventListener("submit", function(e) {
        const detailDiv = document.getElementById("detailInputs");
        detailDiv.innerHTML = "";
        document.querySelectorAll("#detailTable tbody tr").forEach(row => {
            const select = row.querySelector("select");
            const jumlah = row.children[3].querySelector("input").value;
            const subtotal = row.children[4].querySelector("input").value;
            if (select.value !== "" && parseInt(jumlah) > 0) {
                detailDiv.innerHTML += `<input type="hidden" name="produk[]" value="${select.value}">`;
                detailDiv.innerHTML += `<input type="hidden" name="jumlah[]" value="${jumlah}">`;
                detailDiv.innerHTML += `<input type="hidden" name="subtotal[]" value="${subtotal}">`;
            }
        });
    });

    window.onload = calculateTotal;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>
<?php $mysqli->close(); ?>