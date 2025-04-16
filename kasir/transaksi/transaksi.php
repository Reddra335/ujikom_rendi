<?php
// Koneksi ke database
$koneksi = new mysqli("localhost", "root", "", "kasir_reddra");
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Ambil data untuk dropdown filter pelanggan
$pelangganFilterList = [];
$sql_pelanggan = "SELECT PelangganID, NamaPelanggan FROM pelanggan";
$result_pelanggan = $koneksi->query($sql_pelanggan);
if ($result_pelanggan) {
    while ($row = $result_pelanggan->fetch_assoc()) {
        $pelangganFilterList[] = $row;
    }
}

// Ambil parameter filter dari GET: tahun, bulan, tanggal, status bayar, dsb.
$tahun            = isset($_GET['tahun']) ? $_GET['tahun'] : '';
$bulan            = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$tanggal          = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';
$pelanggan_filter = isset($_GET['pelanggan']) ? $_GET['pelanggan'] : '';
$search           = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter    = isset($_GET['status_bayar']) ? $_GET['status_bayar'] : '';

// Bangun klausa WHERE berdasarkan filter
$where_conditions = [];
$params = [];
$param_types = "";

if ($tahun !== "") {
    $where_conditions[] = "YEAR(p.TanggalPenjualan) = ?";
    $params[] = $tahun;
    $param_types .= "i";
}
if ($bulan !== "") {
    $where_conditions[] = "MONTH(p.TanggalPenjualan) = ?";
    $params[] = $bulan;
    $param_types .= "i";
}
if ($tanggal !== "") {
    $where_conditions[] = "DAY(p.TanggalPenjualan) = ?";
    $params[] = $tanggal;
    $param_types .= "i";
}
if ($pelanggan_filter !== "") {
    $where_conditions[] = "pel.PelangganID = ?";
    $params[] = $pelanggan_filter;
    $param_types .= "i";
}
if ($search !== "") {
    $where_conditions[] = "p.invoice LIKE ?";
    $params[] = "%" . $search . "%";
    $param_types .= "s";
}
if ($status_filter !== "") {
    $where_conditions[] = "p.status_bayar = ?";
    $params[] = $status_filter;
    $param_types .= "s";
}

$where_clause = (count($where_conditions) > 0) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Setup Pagination
$results_per_page = 5;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$page = ($page < 1) ? 1 : $page;
$start_from = ($page - 1) * $results_per_page;

// Query utama: Mengambil data penjualan beserta produk yang dibeli (di-group agar jika ada lebih dari satu detail, nilai barang_dibeli digabung)
$query = "SELECT 
            p.PenjualanID, 
            p.invoice, 
            p.TanggalPenjualan, 
            pel.NamaPelanggan,
            p.status_bayar,
            IFNULL(SUM(dp.total_harga), 0) AS Total_Harga,
            GROUP_CONCAT(dp.barang_dibeli SEPARATOR ', ') AS Produk_Dibeli
          FROM penjualan p
          LEFT JOIN pelanggan pel ON p.PelangganID = pel.PelangganID
          LEFT JOIN detailpenjualan dp ON p.PenjualanID = dp.PenjualanID
          $where_clause
          GROUP BY p.PenjualanID
          ORDER BY p.PenjualanID DESC
          LIMIT ?, ?";

$params_for_query = $params;
$params_for_query[] = $start_from;
$params_for_query[] = $results_per_page;
$param_types_for_query = $param_types . "ii";

$stmt = $koneksi->prepare($query);
if ($stmt) {
    if (!empty($params_for_query)) {
        $stmt->bind_param($param_types_for_query, ...$params_for_query);
    }
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    die("Query error: " . $koneksi->error);
}

// Hitung total halaman untuk pagination
$count_query = "SELECT COUNT(*) AS total FROM (
                    SELECT p.PenjualanID
                    FROM penjualan p
                    LEFT JOIN pelanggan pel ON p.PelangganID = pel.PelangganID
                    $where_clause
                    GROUP BY p.PenjualanID
                ) AS subquery";

$count_stmt = $koneksi->prepare($count_query);
if ($count_stmt) {
    if (!empty($params)) {
        $count_stmt->bind_param($param_types, ...$params);
    }
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $row_total = $count_result->fetch_assoc();
    $total_data = $row_total["total"] ?? 0;
    $total_pages = ceil($total_data / $results_per_page);
    $count_stmt->close();
} else {
    $total_pages = 1;
}

// Ambil seluruh data untuk pencarian client-side (opsional)
$allData = [];
$sql_all = "SELECT 
              p.PenjualanID, 
              p.invoice, 
              p.TanggalPenjualan, 
              pel.NamaPelanggan,
              p.status_bayar,
              IFNULL(SUM(dp.total_harga), 0) AS Total_Harga,
              GROUP_CONCAT(dp.barang_dibeli SEPARATOR ', ') AS Produk_Dibeli
            FROM penjualan p
            LEFT JOIN pelanggan pel ON p.PelangganID = pel.PelangganID
            LEFT JOIN detailpenjualan dp ON p.PenjualanID = dp.PenjualanID
            GROUP BY p.PenjualanID
            ORDER BY p.PenjualanID DESC";
$result_all = $koneksi->query($sql_all);
if ($result_all) {
    while ($row = $result_all->fetch_assoc()) {
        $allData[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Transaksi & Konfirmasi Pembayaran</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
    * {
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }

    body {
        background: #fafafa;
        color: #3D2B1F;
        padding: 20px;
    }

    .glass-panel {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        border-radius: 15px;
        padding: 2rem;
        margin: 0 auto;
        max-width: 1200px;
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .page-title {
        font-size: 1.8rem;
        color: #3D2B1F;
        padding-bottom: 0.5rem;
    }

    .filters {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .filter-group {
        flex: 1;
        min-width: 150px;
    }

    select,
    input {
        width: 100%;
        padding: 0.8rem;
        border: 2px solid #E0AA6E;
        border-radius: 8px;
        margin-top: 0.5rem;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }

    .data-table th,
    .data-table td {
        padding: 1rem;
        border-bottom: 1px solid rgba(61, 43, 31, 0.1);
        text-align: center;
    }

    .badge {
        display: inline-block;
        padding: 0.4em 0.9em;
        font-size: 0.9em;
        border-radius: 12px;
        font-weight: bold;
        margin: 2px;
        text-transform: capitalize;
        box-shadow: 0px 2px 6px rgba(0, 0, 0, 0.1);
    }

    .status-belum {
        background: linear-gradient(to right, #ff7e7e, #ff4c4c);
        color: #fff;
    }

    .status-dibayar {
        background: linear-gradient(to right, #7ed957, #3cba54);
        color: #fff;
    }

    .btn-konfirmasi {
        display: inline-block;
        padding: 10px 20px;
        background: linear-gradient(45deg, #1e90ff, #00bfff);
        color: #fff;
        text-decoration: none;
        border: none;
        border-radius: 50px;
        font-weight: bold;
        transition: background 0.3s ease, box-shadow 0.3s ease;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .btn-konfirmasi:hover {
        background: linear-gradient(45deg, #1c86ee, #009acd);
        box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
    }

    .pagination {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 2rem;
    }

    .page-item {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        background: rgba(224, 170, 110, 0.1);
        color: #3D2B1F;
        text-decoration: none;
    }

    .page-item.active {
        background: #E0AA6E;
        color: white;
    }
    </style>
</head>

<body>
    <div class="glass-panel">
        <div class="header">
            <h2 class="page-title">Transaksi & Konfirmasi Pembayaran</h2>
        </div>
        <!-- Filter Section -->
        <div class="filters">
            <div class="filter-group">
                <label>Tahun:</label>
                <select id="tahun">
                    <option value="">Semua</option>
                    <?php 
                        $startYear = 2000;
                        $endYear = date("Y") + 5;
                        for ($i = $startYear; $i <= $endYear; $i++) {
                            $selected = ($tahun == $i) ? "selected" : "";
                            echo "<option value='$i' $selected>$i</option>";
                        }
                    ?>
                </select>
            </div>
            <div class="filter-group">
                <label>Bulan:</label>
                <select id="bulan">
                    <option value="">Semua</option>
                    <?php 
                        for ($i = 1; $i <= 12; $i++) {
                            $namaBulan = date("F", mktime(0, 0, 0, $i, 10));
                            $selected = ($bulan == $i) ? "selected" : "";
                            echo "<option value='$i' $selected>$namaBulan</option>";
                        }
                    ?>
                </select>
            </div>
            <div class="filter-group">
                <label>Tanggal:</label>
                <select id="tanggal">
                    <option value="">Semua</option>
                    <?php 
                        for ($i = 1; $i <= 31; $i++) {
                            $selected = ($tanggal == $i) ? "selected" : "";
                            echo "<option value='$i' $selected>$i</option>";
                        }
                    ?>
                </select>
            </div>
            <div class="filter-group">
                <label>Pelanggan:</label>
                <select id="pelanggan">
                    <option value="">Semua</option>
                    <?php foreach($pelangganFilterList as $p): ?>
                    <option value="<?= $p['PelangganID'] ?>"
                        <?= ($pelanggan_filter == $p['PelangganID']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['NamaPelanggan']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label>Status Bayar:</label>
                <select id="status_bayar">
                    <option value="">Semua</option>
                    <option value="belum dibayar" <?= ($status_filter == "belum dibayar") ? 'selected' : '' ?>>Belum
                        Dibayar</option>
                    <option value="dibayar" <?= ($status_filter == "dibayar") ? 'selected' : '' ?>>Dibayar</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Pencarian:</label>
                <input type="text" id="search" placeholder="Cari invoice..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="filter-group" style="align-self: flex-end;">
                <button style="padding: 0.8rem 1.5rem; " onclick="applyFilter()">Terapkan
                    Filter</button>
            </div>
        </div>
        <!-- Tabel Transaksi -->
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Invoice</th>
                    <th>Tanggal Penjualan</th>
                    <th>Nama Pelanggan</th>
                    <th>Produk Yang Dibeli</th>
                    <th>Total Harga</th>
                    <th>Status Bayar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <?php if ($result && $result->num_rows > 0): ?>
                <?php $no = 1 + (($page - 1) * $results_per_page); ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['invoice']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($row['TanggalPenjualan'])) ?></td>
                    <td><?= htmlspecialchars($row['NamaPelanggan'] ?? 'Umum') ?></td>
                    <td><?= htmlspecialchars($row['Produk_Dibeli']) ?></td>
                    <td>Rp<?= number_format($row['Total_Harga'], 0, ',', '.') ?></td>
                    <td>
                        <?php if ($row['status_bayar'] == 'belum dibayar'): ?>
                        <span class="badge status-belum"><?= htmlspecialchars($row['status_bayar']) ?></span>
                        <?php else: ?>
                        <span class="badge status-dibayar"><?= htmlspecialchars($row['status_bayar']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="konfirmasi.php?id=<?= $row['PenjualanID'] ?>" class="btn-konfirmasi">Konfirmasi
                            Pembayaran</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align:center; padding:2rem;">Tidak ada data transaksi</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php 
                    $query_str = "tahun=" . urlencode($tahun) . "&bulan=" . urlencode($bulan) . "&tanggal=" . urlencode($tanggal);
                    $query_str .= ($pelanggan_filter !== "") ? "&pelanggan=" . urlencode($pelanggan_filter) : "";
                    $query_str .= ($status_filter !== "") ? "&status_bayar=" . urlencode($status_filter) : "";
                    $query_str .= ($search !== "") ? "&search=" . urlencode($search) : "";
                ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i ?>&<?= $query_str ?>" class="page-item <?= $i == $page ? 'active' : '' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
    <!-- SweetAlert2 & Filter JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function applyFilter() {
        const tahun = document.getElementById('tahun').value;
        const bulan = document.getElementById('bulan').value;
        const tanggal = document.getElementById('tanggal').value;
        const pelanggan = document.getElementById('pelanggan').value;
        const status_bayar = document.getElementById('status_bayar').value;
        const search = document.getElementById('search').value;
        let query = '?';
        if (tahun !== '') query += 'tahun=' + encodeURIComponent(tahun) + '&';
        if (bulan !== '') query += 'bulan=' + encodeURIComponent(bulan) + '&';
        if (tanggal !== '') query += 'tanggal=' + encodeURIComponent(tanggal) + '&';
        if (pelanggan !== '') query += 'pelanggan=' + encodeURIComponent(pelanggan) + '&';
        if (status_bayar !== '') query += 'status_bayar=' + encodeURIComponent(status_bayar) + '&';
        if (search !== '') query += 'search=' + encodeURIComponent(search) + '&';
        query += 'page=1';
        window.location.href = query;
    }

    // Opsional: Pencarian realtime client-side
    var fullData = <?php echo json_encode($allData); ?>;
    var tableBody = document.getElementById('tableBody');
    var searchInput = document.getElementById('search');

    function renderTable(data) {
        var html = '';
        if (data.length === 0) {
            html = '<tr><td colspan="8" style="text-align:center; padding:2rem;">Tidak ada data transaksi</td></tr>';
        } else {
            data.forEach(function(row, index) {
                var no = index + 1;
                html += '<tr>';
                html += '<td>' + no + '</td>';
                html += '<td>' + row.invoice + '</td>';
                var tgl = new Date(row.TanggalPenjualan);
                html += '<td>' + tgl.toLocaleDateString("id-ID") + ' ' + tgl.toLocaleTimeString("id-ID", {
                    hour: "2-digit",
                    minute: "2-digit"
                }) + '</td>';
                html += '<td>' + (row.NamaPelanggan ? row.NamaPelanggan : "Umum") + '</td>';
                html += '<td>' + row.Produk_Dibeli + '</td>';
                html += '<td>Rp' + parseInt(row.Total_Harga).toLocaleString("id-ID") + '</td>';
                if (row.status_bayar === 'belum dibayar') {
                    html += '<td><span class="badge status-belum">' + row.status_bayar + '</span></td>';
                } else {
                    html += '<td><span class="badge status-dibayar">' + row.status_bayar + '</span></td>';
                }
                html += '<td><a href="konfirmasi.php?id=' + row.PenjualanID +
                    '" class="btn-konfirmasi">Konfirmasi Pembayaran</a></td>';
                html += '</tr>';
            });
        }
        tableBody.innerHTML = html;
    }

    searchInput.addEventListener('keyup', function(e) {
        var term = e.target.value.toLowerCase().trim();
        if (term !== '') {
            var filtered = fullData.filter(function(item) {
                return item.invoice.toLowerCase().includes(term) ||
                    (item.NamaPelanggan && item.NamaPelanggan.toLowerCase().includes(term));
            });
            renderTable(filtered);
            var pag = document.querySelector('.pagination');
            if (pag) pag.style.display = 'none';
        } else {
            renderTable(fullData);
            var pag = document.querySelector('.pagination');
            if (pag) pag.style.display = 'flex';
        }
    });
    </script>
</body>

</html>
<?php $koneksi->close(); ?>