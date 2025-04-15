<?php
// kelola_pengguna.php

$koneksi = new mysqli("localhost", "root", "", "kasir_reddra");

if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Konfigurasi pagination
$results_per_page = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$start_from = ($page - 1) * $results_per_page;

// Query utama untuk menampilkan data pengguna
$sql = "SELECT * FROM user ORDER BY UserID DESC LIMIT $start_from, $results_per_page";
$result = $koneksi->query($sql);

// Hitung total halaman
$sql_total = "SELECT COUNT(UserID) AS total FROM user";
$result_total = $koneksi->query($sql_total);
$row_total = $result_total->fetch_assoc();
$total_pages = ceil($row_total["total"] / $results_per_page);

// Data untuk pencarian client-side
$allData = [];
$sql_all = "SELECT * FROM user ORDER BY UserID DESC";
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
    <title>Kelola Pengguna</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Include SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
    /* Style dasar halaman Kelola Pengguna */
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
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 2rem;
    }

    .title-wrapper {
        text-align: center;
        flex-grow: 1;
        order: 2;
        width: 100%;
        margin-bottom: 1rem;
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
        order: 1;
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

    .btn-secondary {
        background: #f0f0f0;
        color: #3D2B1F;
        border: 2px solid #ddd;
    }

    .search-box {
        width: 100%;
        margin-bottom: 1.5rem;
        order: 3;
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
        flex-wrap: wrap;
    }

    .page-item {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        background: rgba(224, 170, 110, 0.1);
        color: #3D2B1F;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .page-item.active {
        background: #E0AA6E;
        color: white;
    }

    /* Modal Gambar */
    #imagePopup {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    #popupImage {
        max-width: 80%;
        max-height: 80%;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
    }

    #imagePopup span {
        position: absolute;
        top: 30px;
        right: 40px;
        font-size: 35px;
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    </style>
</head>

<body>
    <div class="glass-panel">
        <div class="header">
            <div class="btn-group">
                <a href="tambah_pengguna.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Pengguna
                </a>

            </div>

            <div class="title-wrapper">
                <h2 class="page-title">Kelola Pengguna</h2>
            </div>

            <div class="search-box">
                <input type="text" class="search-input" placeholder="Cari pengguna..." id="searchInput">
            </div>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama User</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Foto</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if ($result->num_rows > 0): ?>
                    <?php $no = 1 + (($page - 1) * $results_per_page); ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['nama_user']) ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['role']) ?></td>
                        <td>
                            <?php if (!empty($row['foto'])): 
                                        $imagePath = $row['foto'];
                                        if (strpos($imagePath, 'uploads/') !== 0) {
                                            $imagePath = 'uploads/' . $imagePath;
                                        }
                                    ?>
                            <a href="#" class="action-link" onclick="showPopup('<?= $imagePath ?>'); return false;">
                                <i class="fas fa-image"></i> Lihat
                            </a>
                            <?php else: ?>
                            <span style="color: #999;">Tidak ada</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-links">
                                <a href="edit_pengguna.php?id=<?= $row['UserID'] ?>" class="action-link">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="hapus_pengguna.php?id=<?= $row['UserID'] ?>" class="action-link delete-btn"
                                    data-id="<?= $row['UserID'] ?>">
                                    <i class="fas fa-trash-alt"></i> Hapus
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem;">Tidak ada data pengguna</td>
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

    <!-- Modal Gambar -->
    <div id="imagePopup">
        <span onclick="closePopup()">&times;</span>
        <img id="popupImage" src="" alt="Foto Pengguna">
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    // Client-side data untuk pencarian
    var fullData = <?php echo json_encode($allData); ?>;
    var tableBody = document.getElementById('tableBody');
    var searchInput = document.getElementById('searchInput');

    function renderTable(data) {
        var html = '';
        if (data.length === 0) {
            html =
                '<tr><td colspan="6" style="text-align: center; padding: 2rem;">Tidak ada data yang ditemukan</td></tr>';
        } else {
            data.forEach(function(row, index) {
                var imagePath = row.foto;
                if (imagePath && !imagePath.startsWith("uploads/")) {
                    imagePath = "uploads/" + imagePath;
                }
                html += `<tr>
                        <td>${index + 1}</td>
                        <td>${escapeHtml(row.nama_user)}</td>
                        <td>${escapeHtml(row.username)}</td>
                        <td>${escapeHtml(row.role)}</td>
                        <td>
                            ${ row.foto ? `<a href="#" class="action-link" onclick="showPopup('${imagePath}'); return false;"><i class="fas fa-image"></i> Lihat</a>` : `<span style="color: #999;">Tidak ada</span>` }
                        </td>
                        <td>
                            <div class="action-links">
                                <a href="edit_pengguna.php?id=${row.UserID}" class="action-link"><i class="fas fa-edit"></i> Edit</a>
                                <a href="hapus_pengguna.php?id=${row.UserID}" class="action-link delete-btn" data-id="${row.UserID}"><i class="fas fa-trash-alt"></i> Hapus</a>
                            </div>
                        </td>
                    </tr>`;
            });
        }
        tableBody.innerHTML = html;
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

    searchInput.addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        const filtered = fullData.filter(item =>
            Object.values(item).some(value =>
                String(value).toLowerCase().includes(term)
            )
        );
        renderTable(filtered);
    });

    // Modal Gambar
    function showPopup(imageUrl) {
        document.getElementById('popupImage').src = imageUrl;
        document.getElementById('imagePopup').style.display = 'flex';
    }

    function closePopup() {
        document.getElementById('imagePopup').style.display = 'none';
    }
    window.onclick = function(event) {
        const modal = document.getElementById('imagePopup');
        if (event.target === modal) {
            closePopup();
        }
    };

    // Konfirmasi hapus menggunakan SweetAlert2
    document.querySelectorAll('.delete-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var deleteUrl = this.getAttribute('href');
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Pengguna akan dihapus secara permanen.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#E0AA6E',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = deleteUrl;
                }
            });
        });
    });
    </script>
    <?php $koneksi->close(); ?>
</body>

</html>