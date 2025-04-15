<?php
$koneksi = new mysqli("localhost", "root", "", "kasir_reddra");

if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Query untuk paginasi (tampilan awal)
$results_per_page = 5;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start_from = ($page - 1) * $results_per_page;
$sql = "SELECT * FROM kategori LIMIT $start_from, $results_per_page";
$result = $koneksi->query($sql);

// Total pages calculation
$sql_total = "SELECT COUNT(kategori_id) AS total FROM kategori";
$result_total = $koneksi->query($sql_total);
$row_total = $result_total->fetch_assoc();
$total_pages = ceil($row_total["total"] / $results_per_page);

// Query untuk seluruh data kategori (untuk proses search JS)
$allData = [];
$sql_all = "SELECT * FROM kategori";
$result_all = $koneksi->query($sql_all);
if ($result_all) {
    while ($row = $result_all->fetch_assoc()) {
        $allData[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Kelola Kategori</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        gap: 1rem;
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
        /* Warna emas yang mewah */
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

    .btn-back {
        background: #f0f0f0;
        color: #3D2B1F;
        border: 2px solid #ddd;
    }

    .btn-primary {
        background: #E0AA6E;
        color: white;
        border: 2px solid #E0AA6E;
    }

    /* Ubah search-box menjadi full width pada container */
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

    .action-links {
        display: flex;
        gap: 1rem;
    }

    .action-link {
        color: #E0AA6E;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
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

    /* Modal Styles */
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
            <div class="btn-group">
                <a href="../produk.php" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="title-wrapper">
                <h2 class="page-title">Kelola Kategori</h2>
            </div>
            <div class="btn-group">
                <a href="tambah_kategori.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah
                </a>
            </div>
        </div>

        <div class="search-box">
            <input type="text" class="search-input" placeholder="Cari kategori..." id="searchInput">
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Kategori</th>
                        <th>Deskripsi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if ($result && $result->num_rows > 0): ?>
                    <?php $no = 1 + ($page - 1) * $results_per_page; ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                        <td><?= htmlspecialchars($row['deskripsi']) ?></td>
                        <td>
                            <div class="action-links">
                                <a href="edit_kategori.php?id=<?= $row['kategori_id'] ?>" class="action-link">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="#" class="action-link delete-btn" data-id="<?= $row['kategori_id'] ?>">
                                    <i class="fas fa-trash-alt"></i> Hapus
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 2rem;">Tidak ada data kategori</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1 && $results_per_page): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i ?>" class="page-item <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3>Konfirmasi Hapus</h3>
            <p>Apakah Anda yakin ingin menghapus kategori ini?</p>
            <div class="modal-actions">
                <button class="modal-btn cancel">Batal</button>
                <button class="modal-btn confirm">Hapus</button>
            </div>
        </div>
    </div>

    <script>
    // Simpan full data kategori yang diambil dari server (dalam format JSON)
    var fullData = <?php echo json_encode($allData); ?>;

    // Fungsi untuk merender tabel berdasarkan data (array objek)
    function renderTable(data) {
        var tableBody = document.getElementById('tableBody');
        if (data.length === 0) {
            tableBody.innerHTML =
                '<tr><td colspan="4" style="text-align: center; padding: 2rem;">Tidak ada data kategori</td></tr>';
            return;
        }
        var html = '';
        data.forEach(function(row, index) {
            // Menyertakan pencarian berdasarkan nomor urut (index+1) dan juga berdasarkan kategori_id
            html += '<tr>';
            html += '<td>' + (index + 1) + '</td>';
            html += '<td>' + row.nama_kategori + '</td>';
            html += '<td>' + row.deskripsi + '</td>';
            html += '<td><div class="action-links">';
            html += '<a href="edit_kategori.php?id=' + row.kategori_id +
                '" class="action-link"><i class="fas fa-edit"></i> Edit</a>';
            html += '<a href="#" class="action-link delete-btn" data-id="' + row.kategori_id +
                '"><i class="fas fa-trash-alt"></i> Hapus</a>';
            html += '</div></td>';
            html += '</tr>';
        });
        tableBody.innerHTML = html;
        assignDeleteListeners();
    }

    // Event search dengan JS (client-side)
    document.getElementById('searchInput').addEventListener('keyup', function(e) {
        var term = e.target.value.toLowerCase().trim();
        if (term !== '') {
            var filtered = fullData.filter(function(item, index) {
                // Konversi "nomor urut" dan kategori_id ke string
                var noStr = (index + 1).toString();
                var idStr = item.kategori_id.toString();
                return item.nama_kategori.toLowerCase().includes(term) ||
                    item.deskripsi.toLowerCase().includes(term) ||
                    noStr.includes(term) ||
                    idStr.includes(term);
            });
            renderTable(filtered);
            // Sembunyikan pagination jika pencarian aktif
            var pagination = document.querySelector('.pagination');
            if (pagination) {
                pagination.style.display = 'none';
            }
        } else {
            // Jika input kosong, reload halaman untuk mengembalikan tampilan paginasi
            window.location.href = window.location.pathname;
        }
    });

    // Assign delete event listeners menggunakan modal
    function assignDeleteListeners() {
        const deleteBtns = document.querySelectorAll('.delete-btn');
        const modal = document.getElementById('deleteModal');
        const cancelBtn = modal.querySelector('.cancel');
        const confirmBtn = modal.querySelector('.confirm');
        let deleteUrl = '';

        deleteBtns.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                deleteUrl = "hapus_kategori.php?id=" + btn.dataset.id;
                modal.style.display = 'flex';
            });
        });

        cancelBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
        confirmBtn.addEventListener('click', function() {
            window.location.href = deleteUrl;
        });
        window.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    }

    // Assign delete listeners pada initial load
    assignDeleteListeners();
    </script>
</body>

</html>

<?php $koneksi->close(); ?>