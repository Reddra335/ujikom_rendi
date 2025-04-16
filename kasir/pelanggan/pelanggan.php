<?php
// kelola_pelanggan.php

$koneksi = new mysqli("localhost", "root", "", "kasir_reddra");
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Konfigurasi pagination
$results_per_page = 5;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$page = max($page, 1);
$start_from = ($page - 1) * $results_per_page;

// Query utama untuk menampilkan data pelanggan
$sql = "SELECT * FROM pelanggan LIMIT $start_from, $results_per_page";
$result = $koneksi->query($sql);

// Hitung total halaman
$sql_total = "SELECT COUNT(PelangganID) AS total FROM pelanggan";
$result_total = $koneksi->query($sql_total);
$row_total = $result_total->fetch_assoc();
$total_pages = ceil($row_total["total"] / $results_per_page);

// Data lengkap untuk fungsi pencarian
$allData = [];
$sql_all = "SELECT * FROM pelanggan";
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
    <title>Kelola Pelanggan</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    /* Global Reset & Font */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }

    body {
        background: #fafafa;
        color: #3D2B1F;
    }

    /* Container Utama */
    .glass-panel {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        border-radius: 15px;
        padding: 2rem;
        margin: 2rem auto;
        max-width: 1200px;
    }

    /* Header */
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }

    .title-wrapper {
        text-align: center;
        flex-grow: 1;
    }

    .page-title {
        font-size: 1.8rem;
        color: #3D2B1F;
        position: relative;
        display: inline-block;
        padding: 0 1rem;
        transition: color 0.3s ease, text-shadow 0.3s ease;
    }

    .page-title:hover {
        color: #d4af37;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }

    .page-title::after {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 50%;
        transform: translateX(-50%);
        width: 60%;
        height: 3px;
        background: #E0AA6E;
        border-radius: 2px;
    }

    .btn-group {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .btn {
        padding: 0.6rem 1.5rem;
        border-radius: 30px;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        white-space: nowrap;
    }

    .btn-primary {
        background: #E0AA6E;
        color: white;
        border: 2px solid #E0AA6E;
    }

    /* Kotak Pencarian */
    .search-box {
        width: 100%;
        margin-bottom: 1.5rem;
    }

    .search-input {
        width: 100%;
        padding: 0.8rem 1.2rem;
        border: 2px solid #E0AA6E;
        border-radius: 30px;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    /* Tabel */
    .table-container {
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin: 1rem 0;
    }

    .data-table th,
    .data-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid rgba(61, 43, 31, 0.1);
    }

    .data-table th {
        background: rgba(224, 170, 110, 0.1);
    }

    /* Aksi (tombol edit & delete) */
    .action-links {
        display: flex;
        gap: 0.8rem;
        align-items: center;
    }

    .action-btn {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
        color: white;
    }

    .btn-edit {
        background: #4CAF50;
    }

    .btn-edit:hover {
        background: #45a049;
        transform: rotate(-15deg);
    }

    .btn-delete {
        background: #f44336;
    }

    .btn-delete:hover {
        background: #da190b;
        transform: rotate(15deg);
    }

    /* Pagination */
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

    /* Modal Konfirmasi Hapus */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }

    .modal-content {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        width: 90%;
        max-width: 400px;
        text-align: center;
    }

    .modal-icon {
        font-size: 3rem;
        color: #E0AA6E;
        margin-bottom: 1rem;
    }

    .modal-actions {
        margin-top: 1.5rem;
        display: flex;
        justify-content: space-around;
    }

    .modal-btn {
        padding: 0.8rem 1.5rem;
        border: none;
        border-radius: 30px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .modal-btn.cancel {
        background: #f0f0f0;
        color: #3D2B1F;
    }

    .modal-btn.confirm {
        background: #E0AA6E;
        color: white;
    }

    .modal-btn.confirm:hover {
        background: #cf8b5c;
    }
    </style>
</head>

<body>
    <div class="glass-panel">
        <div class="header">
            <div class="title-wrapper">
                <h2 class="page-title">Kelola Pelanggan</h2>
            </div>
            <div class="btn-group">
                <a href="tambah_pelanggan.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah
                </a>
            </div>
        </div>

        <div class="search-box">
            <input type="text" class="search-input" placeholder="Cari pelanggan..." id="searchInput">
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Alamat</th>
                        <th>Nomor Telepon</th>
                        <th>Jenis Kelamin</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if ($result && $result->num_rows > 0): ?>
                    <?php $no = 1 + (($page - 1) * $results_per_page); ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['NamaPelanggan']) ?></td>
                        <td><?= htmlspecialchars($row['Alamat']) ?></td>
                        <td><?= htmlspecialchars($row['NomorTelepon']) ?></td>
                        <td><?= htmlspecialchars($row['jk']) ?></td>
                        <td>
                            <div class="action-links">
                                <a href="edit_pelanggan.php?id=<?= $row['PelangganID'] ?>" class="action-btn btn-edit"
                                    title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="#" class="action-btn btn-delete delete-btn"
                                    data-id="<?= $row['PelangganID'] ?>" title="Hapus">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem;">Tidak ada data pelanggan</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i ?>" class="page-item <?= $i == $page ? 'active' : '' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3>Konfirmasi Hapus</h3>
            <p>Apakah Anda yakin ingin menghapus pelanggan ini?</p>
            <div class="modal-actions">
                <button class="modal-btn cancel">Batal</button>
                <button class="modal-btn confirm">Hapus</button>
            </div>
        </div>
    </div>

    <script>
    // Data lengkap pelanggan untuk fungsi pencarian
    var fullData = <?php echo json_encode($allData); ?>;

    function renderTable(data) {
        var tableBody = document.getElementById('tableBody');
        if (data.length === 0) {
            tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem;">
                            Tidak ada data yang ditemukan
                        </td>
                    </tr>`;
            return;
        }

        var html = '';
        data.forEach(function(row, index) {
            html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${escapeHtml(row.NamaPelanggan)}</td>
                        <td>${escapeHtml(row.Alamat)}</td>
                        <td>${escapeHtml(row.NomorTelepon)}</td>
                        <td>${escapeHtml(row.jk)}</td>
                        <td>
                            <div class="action-links">
                                <a href="edit_pelanggan.php?id=${row.PelangganID}" class="action-btn btn-edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="#" class="action-btn btn-delete delete-btn" data-id="${row.PelangganID}" title="Hapus">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </td>
                    </tr>`;
        });
        tableBody.innerHTML = html;
        assignDeleteListeners();
    }

    function escapeHtml(text) {
        return text.replace(/[&<>"']/g, function(match) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            } [match];
        });
    }

    // Pencarian data secara real-time
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        const filtered = fullData.filter(item => {
            return Object.values(item).some(value =>
                String(value).toLowerCase().includes(term)
            );
        });
        renderTable(filtered);
        // Sembunyikan pagination saat filter aktif
        document.querySelector('.pagination').style.display = term ? 'none' : 'flex';
    });

    // Penanganan Modal Hapus
    function assignDeleteListeners() {
        const deleteBtns = document.querySelectorAll('.delete-btn');
        const modal = document.getElementById('deleteModal');
        const cancelBtn = modal.querySelector('.cancel');
        const confirmBtn = modal.querySelector('.confirm');
        let deleteUrl = '';

        deleteBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                deleteUrl = `hapus_pelanggan.php?id=${btn.dataset.id}`;
                modal.style.display = 'flex';
            });
        });

        cancelBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        confirmBtn.addEventListener('click', () => {
            window.location.href = deleteUrl;
        });

        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    }

    assignDeleteListeners();
    </script>
</body>

</html>

<?php 
$koneksi->close();
?>