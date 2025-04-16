<?php
$conn = new mysqli("localhost", "root", "", "kasir_reddra");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Handle filter
$period = $_GET['period'] ?? 'all';
$specific_date = $_GET['specific_date'] ?? '';
$specific_month = $_GET['specific_month'] ?? '';
$specific_year = $_GET['specific_year'] ?? '';

$whereClause = "";

switch($period) {
    case 'yearly':
        $whereClause = "WHERE YEAR(p.TanggalPenjualan) = YEAR(CURDATE())";
        break;
    case 'monthly':
        $whereClause = "WHERE MONTH(p.TanggalPenjualan) = MONTH(CURDATE()) 
                      AND YEAR(p.TanggalPenjualan) = YEAR(CURDATE())";
        break;
    case 'daily':
        $whereClause = "WHERE DATE(p.TanggalPenjualan) = CURDATE()";
        break;
    case 'specific_date':
        if (!empty($specific_date)) {
            $whereClause = "WHERE DATE(p.TanggalPenjualan) = '$specific_date'";
        }
        break;
    case 'specific_month':
        if (!empty($specific_month)) {
            $whereClause = "WHERE DATE_FORMAT(p.TanggalPenjualan, '%Y-%m') = '$specific_month'";
        }
        break;
    case 'specific_year':
        if (!empty($specific_year)) {
            $whereClause = "WHERE YEAR(p.TanggalPenjualan) = '$specific_year'";
        }
        break;
    default:
        $whereClause = "";
}

// Data untuk card
$total_produk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM produk"))['total'];
$total_pelanggan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pelanggan"))['total'];
$total_transaksi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM penjualan p $whereClause"))['total'];
$total_penghasilan = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT SUM(dp.total_harga) as total 
     FROM detailpenjualan dp
     JOIN penjualan p ON dp.PenjualanID = p.PenjualanID
     $whereClause"))['total'] ?? 0;

// Data grafik
$chartQuery = "SELECT 
    DATE_FORMAT(p.TanggalPenjualan, '%Y-%m-%d') as label, 
    SUM(dp.total_harga) as total
FROM penjualan p
JOIN detailpenjualan dp ON p.PenjualanID = dp.PenjualanID
$whereClause
GROUP BY label
ORDER BY label";

$chartData = mysqli_query($conn, $chartQuery);

$labels = [];
$data = [];
while($row = mysqli_fetch_assoc($chartData)) {
    $labels[] = date('d M', strtotime($row['label']));
    $data[] = $row['total'];
}

// Data transaksi terakhir
$transaksi = mysqli_query($conn, 
    "SELECT p.invoice, pl.NamaPelanggan, p.TanggalPenjualan, p.status_bayar, dp.total_harga 
     FROM penjualan p
     JOIN pelanggan pl ON p.PelangganID = pl.PelangganID
     JOIN detailpenjualan dp ON p.PenjualanID = dp.PenjualanID
     ORDER BY p.TanggalPenjualan DESC LIMIT 10");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Kasir Reddra</title>
    <style>
    :root {
        --gold: #E0AA6E;
        --dark: #3D2B1F;
        --cream: #FFF5EB;
    }

    body {
        margin: 0;
        font-family: 'Poppins', sans-serif;
        background: var(--cream);
    }

    .navbar {
        background: var(--dark);
        padding: 1rem 2rem;
        display: flex;
        align-items: center;
    }

    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
        padding: 2rem;
    }

    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--gold);
        color: white;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 0.25rem;
    }

    .stat-title {
        color: #666;
        font-size: 0.9rem;
    }

    .chart-container {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        margin: 0 2rem 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .filter-group {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .filter-input {
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 8px;
        background: white;
    }

    .transaction-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }

    .transaction-table th,
    .transaction-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
    }

    .status-badge.dibayar {
        background: #e8f5e9;
        color: #4CAF50;
    }

    .status-badge.belum {
        background: #ffebee;
        color: #F44336;
    }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

    <main>
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-box"></i></div>
                <div class="stat-value"><?= number_format($total_produk) ?></div>
                <div class="stat-title">Total Produk</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-value"><?= number_format($total_pelanggan) ?></div>
                <div class="stat-title">Total Pelanggan</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                <div class="stat-value"><?= number_format($total_transaksi) ?></div>
                <div class="stat-title">Total Transaksi</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-wallet"></i></div>
                <div class="stat-value">Rp<?= number_format($total_penghasilan) ?></div>
                <div class="stat-title">
                    <?php
                    $title = match($period) {
                        'daily' => 'Hari Ini',
                        'monthly' => 'Bulan Ini',
                        'yearly' => 'Tahun Ini',
                        'specific_date' => date('d M Y', strtotime($specific_date)),
                        'specific_month' => date('M Y', strtotime($specific_month.'-01')),
                        'specific_year' => $specific_year,
                        default => 'Semua Waktu'
                    };
                    echo "Penghasilan $title";
                    ?>
                </div>
            </div>
        </div>

        <div class="chart-container">
            <div class="chart-header">
                <h2>Analisis Penjualan</h2>
                <form method="GET" class="filter-group">
                    <select name="period" class="filter-input" id="periodSelect" onchange="toggleFilters()">
                        <option value="all" <?= $period === 'all' ? 'selected' : '' ?>>Semua</option>
                        <option value="daily" <?= $period === 'daily' ? 'selected' : '' ?>>Hari Ini</option>
                        <option value="monthly" <?= $period === 'monthly' ? 'selected' : '' ?>>Bulan Ini</option>
                        <option value="yearly" <?= $period === 'yearly' ? 'selected' : '' ?>>Tahun Ini</option>
                        <option value="specific_date" <?= $period === 'specific_date' ? 'selected' : '' ?>>Tanggal
                            Tertentu</option>
                        <option value="specific_month" <?= $period === 'specific_month' ? 'selected' : '' ?>>Bulan
                            Tertentu</option>
                        <option value="specific_year" <?= $period === 'specific_year' ? 'selected' : '' ?>>Tahun
                            Tertentu</option>
                    </select>

                    <input type="date" name="specific_date" value="<?= $specific_date ?>" class="filter-input"
                        id="dateFilter" style="display: <?= $period === 'specific_date' ? 'block' : 'none' ?>">

                    <input type="month" name="specific_month" value="<?= $specific_month ?>" class="filter-input"
                        id="monthFilter" style="display: <?= $period === 'specific_month' ? 'block' : 'none' ?>">

                    <select name="specific_year" class="filter-input" id="yearFilter"
                        style="display: <?= $period === 'specific_year' ? 'block' : 'none' ?>">
                        <?php
                        $currentYear = date('Y');
                        for($year = $currentYear; $year >= 2000; $year--) {
                            $selected = ($year == $specific_year) ? 'selected' : '';
                            echo "<option value='$year' $selected>$year</option>";
                        }
                        ?>
                    </select>

                    <button type="submit" class="filter-input"
                        style="background: var(--gold); color: white;">Terapkan</button>
                </form>
            </div>
            <canvas id="salesChart"></canvas>
        </div>

        <div class="chart-container" style="margin-top: -1rem;">
            <h2>Transaksi Terakhir</h2>
            <table class="transaction-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Invoice</th>
                        <th>Pelanggan</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while($t = mysqli_fetch_assoc($transaksi)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= $t['invoice'] ?></td>
                        <td><?= $t['NamaPelanggan'] ?></td>
                        <td><?= date('d M Y H:i', strtotime($t['TanggalPenjualan'])) ?></td>
                        <td>
                            <span class="status-badge <?= $t['status_bayar'] === 'dibayar' ? 'dibayar' : 'belum' ?>">
                                <?= ucfirst($t['status_bayar']) ?>
                            </span>
                        </td>
                        <td>Rp<?= number_format($t['total_harga']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
    function toggleFilters() {
        const period = document.getElementById('periodSelect').value;
        document.getElementById('dateFilter').style.display = 'none';
        document.getElementById('monthFilter').style.display = 'none';
        document.getElementById('yearFilter').style.display = 'none';

        if (period === 'specific_date') {
            document.getElementById('dateFilter').style.display = 'block';
        } else if (period === 'specific_month') {
            document.getElementById('monthFilter').style.display = 'block';
        } else if (period === 'specific_year') {
            document.getElementById('yearFilter').style.display = 'block';
        }
    }

    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: 'Pendapatan',
                data: <?= json_encode($data) ?>,
                borderColor: '#E0AA6E',
                tension: 0.4,
                fill: true,
                backgroundColor: context => {
                    const bg = context.chart.ctx.createLinearGradient(0, 0, 0, 400);
                    bg.addColorStop(0, 'rgba(224, 170, 110, 0.2)');
                    bg.addColorStop(1, 'rgba(224, 170, 110, 0.05)');
                    return bg;
                }
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#3D2B1F',
                    bodyColor: '#fff',
                    callbacks: {
                        label: ctx => ' Rp' + ctx.raw().toLocaleString('id-ID')
                    }
                }
            },
            scales: {
                y: {
                    ticks: {
                        callback: value => 'Rp' + value.toLocaleString('id-ID')
                    }
                }
            }
        }
    });
    </script>
</body>

</html>