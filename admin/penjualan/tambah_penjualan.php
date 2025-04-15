<?php  
session_start();
// Pastikan sesi sudah dimulai dan user login.
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php"); // Sesuaikan path login
    exit;
}

// Koneksi ke database
$host     = "localhost";
$username = "root";
$password = "";
$dbname   = "kasir_reddra";
$mysqli = new mysqli($host, $username, $password, $dbname);
if ($mysqli->connect_errno) {
    die("Gagal koneksi ke database: " . $mysqli->connect_error);
}

// Ambil data produk untuk dropdown (ProdukID, NamaProduk, Harga, Stok)
$productList = [];
$sqlProducts = "SELECT ProdukID, NamaProduk, Harga, Stok FROM produk";
$resultProducts = $mysqli->query($sqlProducts);
if ($resultProducts) {
    while ($row = $resultProducts->fetch_assoc()) {
        $productList[] = $row;
    }
}

// Ambil data pelanggan untuk memilih pembeli
$pelangganList = [];
$sqlPelanggan = "SELECT PelangganID, NamaPelanggan FROM pelanggan";
$resultPelanggan = $mysqli->query($sqlPelanggan);
if ($resultPelanggan) {
    while ($row = $resultPelanggan->fetch_assoc()) {
        $pelangganList[] = $row;
    }
}

$message   = "";
$isSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {

    // Header penjualan
    $pelangganID = (isset($_POST['pelanggan']) && $_POST['pelanggan'] !== "") ? intval($_POST['pelanggan']) : NULL;
    $invoice = "INV" . time();
    
    // Nilai diskon dan pajak dikirim dari form (dalam persen)
    $diskon = isset($_POST['diskon']) ? floatval($_POST['diskon']) : 0;
    $pajak  = isset($_POST['pajak']) ? floatval($_POST['pajak']) : 0;
    
    // Hitung total subtotal dari masing-masing detail (nilai subtotal dikirim oleh client)
    $sumSub = 0;
    if (isset($_POST['subtotal']) && is_array($_POST['subtotal'])) {
        foreach ($_POST['subtotal'] as $sub) {
            $sumSub += floatval($sub);
        }
    }
    
    // Hitung final total setelah diskon dan pajak
    $discountAmount   = ($diskon / 100) * $sumSub;
    $subAfterDiscount = $sumSub - $discountAmount;
    $taxAmount        = ($pajak / 100) * $subAfterDiscount;
    $finalTotal       = $subAfterDiscount + $taxAmount;
    // Jika perlu, Anda dapat melakukan pembulatan atau casting ke integer
    // $finalTotal = (int) round($finalTotal);

    // Simpan header penjualan ke tabel penjualan
    $stmt = $mysqli->prepare("INSERT INTO penjualan (TanggalPenjualan, tgl_bayar, PelangganID, status_bayar, invoice, diskon, pajak) VALUES (NOW(), NULL, ?, 'belum dibayar', ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("isdd", $pelangganID, $invoice, $diskon, $pajak);
        if ($stmt->execute()) {
            $penjualanID = $mysqli->insert_id;
            
            // Ambil data produk, jumlah, subtotal yang dikirim dari form.
            $produkIDs = isset($_POST['produk']) ? $_POST['produk'] : [];
            $jumlahs   = isset($_POST['jumlah']) ? $_POST['jumlah'] : [];
            $subtotals = isset($_POST['subtotal']) ? $_POST['subtotal'] : [];
            
            // Agregasi data detail berdasarkan ProdukID.
            $aggregatedDetails = [];
            $userID = $_SESSION['user_id'];
            for ($i = 0; $i < count($produkIDs); $i++) {
                $prodID = intval($produkIDs[$i]);
                $jumlahItem   = intval($jumlahs[$i]);
                $subTotalItem = floatval($subtotals[$i]);
                
                if (isset($aggregatedDetails[$prodID])) {
                    $aggregatedDetails[$prodID]['jumlah'] += $jumlahItem;
                    $aggregatedDetails[$prodID]['subtotal'] += $subTotalItem;
                } else {
                    $aggregatedDetails[$prodID] = [
                        'user_id'  => $userID,
                        'jumlah'   => $jumlahItem,
                        'subtotal' => $subTotalItem
                    ];
                }
            }
            
            /* 
            // Debug aggregator (jika perlu cek hasilnya)
            echo "<pre>";
            print_r($aggregatedDetails);
            echo "</pre>";
            die();
            */
            
            // Lakukan insert ke tabel detailpenjualan satu kali per ProdukID.
            foreach ($aggregatedDetails as $prodID => $data) {
                // INSERT statement disesuaikan dengan field tabel detailpenjualan.
                // Field kode_pembayaran diset 0, total_harga diisi dengan finalTotal,
                // dan kembalian diset 0 (default) jika belum ada perhitungan.
                $stmtDet = $mysqli->prepare("INSERT INTO detailpenjualan (PenjualanID, ProdukID, user_id, JumlahProduk, Subtotal, kode_pembayaran, total_harga, kembalian) VALUES (?, ?, ?, ?, ?, 0, ?, ?)");
                if ($stmtDet) {
                    // Menggunakan tipe binding "iiiiddi": 4 integer, 1 integer, 2 double
                    $stmtDet->bind_param("iiiiddi", $penjualanID, $prodID, $data['user_id'], $data['jumlah'], $data['subtotal'], $finalTotal, $dummyKembalian);
                    // Kita set kembalian = 0 saat insert
                    $dummyKembalian = 0;
                    $stmtDet->execute();
                    $stmtDet->close();
                    
                    // Update stok produk: kurangi stok sesuai total jumlah yang terjual
                    $updateStock = $mysqli->prepare("UPDATE produk SET Stok = Stok - ? WHERE ProdukID = ?");
                    if ($updateStock) {
                        $updateStock->bind_param("ii", $data['jumlah'], $prodID);
                        $updateStock->execute();
                        $updateStock->close();
                    }
                }
            }
            $stmt->close();
            $isSuccess = true;
        } else {
            $message = "Error: Gagal menyimpan header penjualan. " . $stmt->error;
        }
    } else {
        $message = "Error: Gagal menyiapkan statement header. " . $mysqli->error;
    }
    
    if ($isSuccess) {
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
  text: "Penjualan berhasil disimpan!",
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
    <title>Gagal</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>body { margin:0; padding:0; }</style>
</head>
<body>
<script>
Swal.fire({
  icon: "error",
  title: "Gagal",
  text: "' . htmlspecialchars($message, ENT_QUOTES) . '",
  timer: 2000,
  showConfirmButton: false
}).then(function(){
  window.location.href = "penjualan.php";
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
    <title>Tambah Penjualan</title>
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
    </style>
</head>

<body>
    <div class="content-card">
        <h2>Form Penjualan</h2>
        <form action="" method="post" id="penjualanForm">
            <!-- Pilih Pembeli -->
            <div class="form-group">
                <label class="form-label" for="pelanggan">Pembeli:</label>
                <select name="pelanggan" id="pelanggan" class="form-control">
                    <option value="">-- Pilih Pembeli --</option>
                    <?php foreach($pelangganList as $p): ?>
                    <option value="<?= $p['PelangganID'] ?>"><?= htmlspecialchars($p['NamaPelanggan']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Input Diskon & Pajak (dihitung otomatis oleh JavaScript) -->
            <div class="form-group">
                <label class="form-label">Diskon (%)</label>
                <input type="number" id="diskonInput" class="form-control" value="0" step="0.01">
            </div>
            <div class="form-group">
                <label class="form-label">Pajak (%)</label>
                <input type="number" id="pajakInput" class="form-control" value="0" step="0.01">
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
                    <!-- Baris detail akan ditambahkan secara dinamis -->
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
            <!-- Tempat untuk menyimpan detail item secara hidden -->
            <div id="detailInputs"></div>
            <button type="submit" name="submit" class="btn-primary" style="margin-top:20px;">Simpan Penjualan</button>
            <!-- Tombol Kembali -->
            <button type="button" onclick="window.location.href='penjualan.php';" class="btn-primary"
                style="margin-top:20px;">Kembali</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    const products = <?php echo json_encode($productList); ?>;

    // Fungsi menambahkan baris detail baru
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

        // Cell: Aksi (hapus baris)
        const cellAksi = document.createElement("td");
        cellAksi.innerHTML = `<button type="button" onclick="removeRow(this)">Hapus</button>`;
        row.appendChild(cellAksi);

        tbody.appendChild(row);
        calculateTotal();
    }

    // Fungsi update baris detail ketika produk dipilih atau jumlah diubah
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
                    text: "Jumlah melebihi stok tersedia.",
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

    // Fungsi menghapus baris detail dengan konfirmasi
    function removeRow(el) {
        const row = el.closest("tr");
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: "Yakin ingin menghapus baris ini?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#dc3545',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                row.remove();
                calculateTotal();
            }
        });
    }

    // Fungsi menghitung total penjualan dengan diskon dan pajak
    function calculateTotal() {
        let subTotal = 0;
        document.querySelectorAll("#detailTable tbody tr").forEach(row => {
            const rowSubtotal = parseFloat(row.children[4].querySelector("input").value) || 0;
            subTotal += rowSubtotal;
        });
        let discountPercent = parseFloat(document.getElementById("diskonInput").value) || 0;
        let taxPercent = parseFloat(document.getElementById("pajakInput").value) || 0;
        let discountAmount = (discountPercent / 100) * subTotal;
        let subTotalAfterDiscount = subTotal - discountAmount;
        let taxAmount = (taxPercent / 100) * subTotalAfterDiscount;
        let finalTotal = subTotalAfterDiscount + taxAmount;

        document.getElementById("totalPenjualan").value = finalTotal.toFixed(2);
        document.getElementById("hiddenTotal").value = finalTotal.toFixed(2);
        document.getElementById("hiddenDiskon").value = discountPercent;
        document.getElementById("hiddenPajak").value = taxPercent;
    }

    // Update perhitungan saat diskon atau pajak diubah
    document.getElementById("diskonInput").addEventListener("input", calculateTotal);
    document.getElementById("pajakInput").addEventListener("input", calculateTotal);

    // Validasi form dan kumpulkan data detail sebelum submit
    document.getElementById("penjualanForm").addEventListener("submit", function(e) {
        if (document.getElementById("pelanggan").value === "") {
            e.preventDefault();
            Swal.fire({
                icon: "error",
                title: "Data tidak lengkap",
                text: "Pelanggan harus dipilih.",
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }
        const rows = document.querySelectorAll("#detailTable tbody tr");
        if (rows.length === 0) {
            e.preventDefault();
            Swal.fire({
                icon: "error",
                title: "Data tidak lengkap",
                text: "Setidaknya satu barang harus ditambahkan.",
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }
        let valid = true;
        rows.forEach(row => {
            const select = row.querySelector("select");
            const jumlah = parseInt(row.children[3].querySelector("input").value);
            if (select.value === "" || isNaN(jumlah) || jumlah <= 0) {
                valid = false;
            }
        });
        if (!valid) {
            e.preventDefault();
            Swal.fire({
                icon: "error",
                title: "Data tidak lengkap",
                text: "Setiap baris detail harus memiliki produk yang dipilih dan jumlah minimal 1.",
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }
        // Kumpulkan data detail ke input hidden sebelum submit
        const detailDiv = document.getElementById("detailInputs");
        detailDiv.innerHTML = "";
        rows.forEach(row => {
            if (row.style.display !== "none") {
                const select = row.querySelector("select");
                const jumlah = row.children[3].querySelector("input").value;
                const subtotal = row.children[4].querySelector("input").value;
                if (select.value !== "" && parseInt(jumlah) > 0) {
                    detailDiv.innerHTML +=
                        `<input type="hidden" name="produk[]" value="${select.value}">`;
                    detailDiv.innerHTML += `<input type="hidden" name="jumlah[]" value="${jumlah}">`;
                    detailDiv.innerHTML +=
                        `<input type="hidden" name="subtotal[]" value="${subtotal}">`;
                }
            }
        });
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>
<?php $mysqli->close(); ?>