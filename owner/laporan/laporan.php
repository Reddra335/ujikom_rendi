<?php
// print_report.php

$koneksi = new mysqli("localhost", "root", "", "kasir_reddra");
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Ambil parameter filter dari GET
$tahun         = isset($_GET['tahun']) ? $_GET['tahun'] : '';
$bulan         = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$tanggal       = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';
$status_filter = isset($_GET['status_bayar']) ? $_GET['status_bayar'] : '';
$produk_filter = isset($_GET['produk']) ? $_GET['produk'] : '';

// Bangun klausa WHERE (untuk kondisi non-agregat)
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
if ($status_filter !== "") {
    $where_conditions[] = "p.status_bayar = ?";
    $params[] = $status_filter;
    $param_types .= "s";
}
$where_clause = count($where_conditions) > 0 ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Pagination: 5 data per halaman
$results_per_page = 5;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$page = ($page < 1) ? 1 : $page;
$start_from = ($page - 1) * $results_per_page;

// Bangun HAVING clause untuk filter produk (dari GROUP_CONCAT)
$having_clause = "";
if ($produk_filter !== "") {
    $having_clause = " HAVING Produk_Dibeli LIKE ?";
    $produk_filter_param = "%" . $produk_filter . "%";
}

// Query laporan dengan GROUP_CONCAT untuk menggabungkan produk yang dibeli
$query = "SELECT 
            p.PenjualanID, 
            p.invoice, 
            p.TanggalPenjualan, 
            COALESCE(pel.NamaPelanggan, 'Umum') AS NamaPelanggan,
            p.status_bayar,
            IFNULL(SUM(dp.total_harga), 0) AS Total_Harga,
            GROUP_CONCAT(dp.barang_dibeli SEPARATOR ', ') AS Produk_Dibeli
          FROM penjualan p
          LEFT JOIN pelanggan pel ON p.PelangganID = pel.PelangganID
          LEFT JOIN detailpenjualan dp ON p.PenjualanID = dp.PenjualanID
          $where_clause
          GROUP BY p.PenjualanID" 
          . $having_clause .
          " ORDER BY p.TanggalPenjualan DESC
          LIMIT ?, ?";

// Gabungkan parameter: pertama kondisi WHERE, jika ada produk, lalu parameter LIMIT
$all_params = $params;
$all_types = $param_types;
if ($produk_filter !== "") {
    $all_params[] = $produk_filter_param;
    $all_types .= "s";
}
$all_params[] = $start_from;
$all_params[] = $results_per_page;
$all_types .= "ii";

$stmt = $koneksi->prepare($query);
if ($stmt) {
    if (!empty($all_params)) {
        $stmt->bind_param($all_types, ...$all_params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    die("Query error: " . $koneksi->error);
}

// Hitung total data untuk pagination (tanpa LIMIT)
$count_query = "SELECT COUNT(*) AS total FROM (
                    SELECT p.PenjualanID
                    FROM penjualan p
                    LEFT JOIN pelanggan pel ON p.PelangganID = pel.PelangganID
                    LEFT JOIN detailpenjualan dp ON p.PenjualanID = dp.PenjualanID
                    $where_clause
                    GROUP BY p.PenjualanID" . $having_clause . "
                ) AS sub";
$count_stmt = $koneksi->prepare($count_query);
if ($count_stmt) {
    $all_params_count = $params;
    $all_types_count = $param_types;
    if ($produk_filter !== "") {
        $all_params_count[] = $produk_filter_param;
        $all_types_count .= "s";
    }
    if (!empty($all_params_count)) {
        $count_stmt->bind_param($all_types_count, ...$all_params_count);
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
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi - Kasir RedDra</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Google Fonts & Font Awesome -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    :root {
        --primary: #5D4037;
        --secondary: #8D6E63;
        --accent: #E0AA6E;
        --background: #FFFCF5;
        --text: #4E342E;
        --success: #4CAF50;
        --danger: #F44336;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }

    body {
        background: var(--background);
        color: var(--text);
        line-height: 1.6;
    }

    .container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .header {
        text-align: center;
        margin-bottom: 3rem;
    }

    .title {
        color: var(--primary);
        font-size: 2.5rem;
        position: relative;
        display: inline-block;
        padding-bottom: 0.5rem;
    }

    .title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 60%;
        height: 4px;
        background: var(--accent);
        border-radius: 2px;
    }

    .filter-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
        align-items: end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .filter-label {
        font-size: 0.9rem;
        color: var(--primary);
        font-weight: 500;
    }

    .filter-input {
        padding: 0.8rem;
        border: 2px solid #EEE;
        border-radius: 8px;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .filter-input:focus {
        border-color: var(--accent);
        outline: none;
        box-shadow: 0 0 0 3px rgba(224, 170, 110, 0.2);
    }

    .btn-filter {
        padding: 0.8rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        background: var(--secondary);
        color: white;
    }

    .btn-filter:hover {
        background: var(--primary);
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .search-box {
        position: relative;
        margin-bottom: 1.5rem;
    }

    .search-input {
        width: 100%;
        padding: 0.8rem 2.5rem 0.8rem 1rem;
        border: 2px solid #EEE;
        border-radius: 8px;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .search-input:focus {
        border-color: var(--accent);
        outline: none;
        box-shadow: 0 0 0 3px rgba(224, 170, 110, 0.2);
    }

    .search-icon {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #666;
    }

    .report-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
    }

    .report-table thead {
        background: var(--primary);
        color: white;
    }

    .report-table th {
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        white-space: nowrap;
    }

    .report-table td {
        padding: 1rem;
        border-bottom: 1px solid #F0F0F0;
        vertical-align: top;
    }

    .report-table tbody tr:last-child td {
        border-bottom: none;
    }

    .report-table tbody tr:hover {
        background: #FFF9F2;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
        text-transform: capitalize;
    }

    .status-paid {
        background: #E8F5E9;
        color: #2E7D32;
    }

    .status-unpaid {
        background: #FFEBEE;
        color: #C62828;
    }

    .pagination {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
        flex-wrap: wrap;
        margin: 2rem 0;
    }

    .page-link {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        background: white;
        color: var(--text);
        text-decoration: none;
        border: 2px solid #EEE;
        transition: all 0.3s ease;
    }

    .page-link:hover,
    .page-link.active {
        border-color: var(--accent);
        color: var(--primary);
    }

    .page-link.active {
        background: var(--accent);
        color: white;
    }

    .btn-print {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.8rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        background: var(--secondary);
        color: white;
    }

    .btn-print:hover {
        background: var(--primary);
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    /* Responsive Table Helper */
    @media (max-width: 768px) {
        .report-table {
            display: block;
            overflow-x: auto;
        }

        .report-table td {
            min-width: 150px;
        }

        .filter-grid {
            grid-template-columns: 1fr;
        }

        .title {
            font-size: 2rem;
        }

        .btn-print {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .report-table td {
            display: block;
            width: 100%;
            text-align: right;
            padding-left: 50%;
            position: relative;
        }

        .report-table td::before {
            content: attr(data-label);
            position: absolute;
            left: 0;
            width: 50%;
            padding-left: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--primary);
        }

        .report-table thead {
            display: none;
        }

        .report-table tbody tr {
            margin-bottom: 1rem;
            display: block;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    }

    @media print {

        .filter-card,
        .search-box,
        .pagination,
        .btn-print {
            display: none;
        }

        body {
            background: #fff;
            color: #000;
        }

        table,
        th,
        td {
            border: 1px solid #000;
        }
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1 class="title">Laporan Transaksi</h1>
        </div>

        <div class="filter-card">
            <form class="filter-grid" method="get" action="">
                <div class="filter-group">
                    <label for="tahun" class="filter-label">Tahun</label>
                    <select name="tahun" id="tahun" class="filter-input">
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
                    <label for="bulan" class="filter-label">Bulan</label>
                    <select name="bulan" id="bulan" class="filter-input">
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
                    <label for="tanggal" class="filter-label">Tanggal</label>
                    <select name="tanggal" id="tanggal" class="filter-input">
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
                    <label for="status_bayar" class="filter-label">Status Bayar</label>
                    <select name="status_bayar" id="status_bayar" class="filter-input">
                        <option value="">Semua</option>
                        <option value="belum dibayar" <?= ($status_filter == "belum dibayar") ? 'selected' : '' ?>>Belum
                            Dibayar</option>
                        <option value="dibayar" <?= ($status_filter == "dibayar") ? 'selected' : '' ?>>Dibayar</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="produk" class="filter-label">Barang yang Dibeli</label>
                    <input type="text" name="produk" id="produk" class="filter-input" placeholder="Filter produk..."
                        value="<?= htmlspecialchars($produk_filter) ?>">
                </div>
                <div class="filter-group">
                    <button type="submit" class="btn-filter">Terapkan Filter</button>
                </div>
            </form>
        </div>

        <div class="search-box">
            <input type="text" id="jsSearch" class="search-input"
                placeholder="Cari berdasarkan Invoice, Pelanggan, atau Produk...">
            <i class="fas fa-search search-icon"></i>
        </div>

        <table class="report-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Invoice</th>
                    <th>Tanggal</th>
                    <th>Pelanggan</th>
                    <th>Produk</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <?php 
                if ($result && $result->num_rows > 0):
                    $no = 1;
                    while ($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td data-label="No"><?= $no++ ?></td>
                    <td data-label="Invoice"><?= htmlspecialchars($row['invoice']) ?></td>
                    <td data-label="Tanggal"><?= date('d/m/Y H:i', strtotime($row['TanggalPenjualan'])) ?></td>
                    <td data-label="Pelanggan"><?= htmlspecialchars($row['NamaPelanggan']) ?></td>
                    <td data-label="Produk"><?= htmlspecialchars($row['Produk_Dibeli']) ?></td>
                    <td data-label="Total">Rp<?= number_format($row['Total_Harga'], 0, ',', '.') ?></td>
                    <td data-label="Status">
                        <span
                            class="status-badge <?= ($row['status_bayar'] == 'dibayar') ? 'status-paid' : 'status-unpaid' ?>">
                            <?= htmlspecialchars($row['status_bayar']) ?>
                        </span>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem;">
                        <i class="fas fa-info-circle"></i> Tidak ada data transaksi
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if($total_pages > 1): ?>
        <div class="pagination">
            <?php
            $query_str = http_build_query([
                'tahun' => $tahun,
                'bulan' => $bulan,
                'tanggal' => $tanggal,
                'status_bayar' => $status_filter,
                'produk' => $produk_filter
            ]);
            for ($i = 1; $i <= $total_pages; $i++):
                $active = ($i == $page) ? 'active' : '';
            ?>
            <a href="?page=<?= $i ?>&<?= $query_str ?>" class="page-link <?= $active ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

        <button class="btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> Cetak Laporan
        </button>
    </div>

    <script>
    // Enhanced client-side search: Invoice, Pelanggan, dan Produk
    const searchTable = () => {
        const input = document.getElementById('jsSearch');
        const filter = input.value.toLowerCase().trim();
        const rows = document.querySelectorAll('#tableBody tr');
        rows.forEach(row => {
            const cells = Array.from(row.querySelectorAll('td'));
            // Gabungkan teks dari kolom Invoice (index 1), Pelanggan (index 3), Produk (index 4)
            const text = cells[1].textContent.toLowerCase() + " " + cells[3].textContent.toLowerCase() +
                " " + cells[4].textContent.toLowerCase();
            row.style.display = text.includes(filter) ? "" : "none";
        });
    };
    document.getElementById('jsSearch').addEventListener('input', searchTable);

    // Responsive table helper untuk atribut data-label
    const initResponsiveTable = () => {
        const headers = Array.from(document.querySelectorAll('.report-table th')).map(th => th.textContent);
        document.querySelectorAll('.report-table td').forEach((td, index) => {
            const headerIndex = index % headers.length;
            td.setAttribute('data-label', headers[headerIndex]);
        });
    };
    window.addEventListener('resize', initResponsiveTable);
    initResponsiveTable();
    </script>
</body>

</html>
<?php 
$stmt->close();
$koneksi->close();
?>