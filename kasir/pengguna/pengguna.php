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

// Query utama
$sql = "SELECT * FROM user ORDER BY UserID DESC LIMIT $start_from, $results_per_page";
$result = $koneksi->query($sql);

// Hitung total halaman
$sql_total = "SELECT COUNT(UserID) AS total FROM user";
$result_total = $koneksi->query($sql_total);
$row_total = $result_total->fetch_assoc();
$total_pages = ceil($row_total["total"] / $results_per_page);

// Data untuk pencarian
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
        transition: color 0.3s ease;
    }

    .page-title:hover {
        color: #d4af37;
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
    }

    .btn-primary {
        background: #E0AA6E;
        color: white;
        border: 2px solid #E0AA6E;
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
        background: #E0AA6E;
        color: white;
        position: sticky;
        top: 0;
    }

    .data-table tr {
        transition: all 0.3s ease;
    }

    .data-table tr:hover {
        background: rgba(224, 170, 110, 0.05);
        transform: translateX(5px);
    }

    .user-thumbnail {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border: 2px solid #E0AA6E;
    }

    .user-thumbnail:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .role-badge {
        display: inline-block;
        padding: 0.3rem 0.8rem;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 500;
        text-transform: uppercase;
        background: rgba(224, 170, 110, 0.2);
        color: #3D2B1F;
        border: 1px solid rgba(224, 170, 110, 0.3);
    }

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

    #imagePopup {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        justify-content: center;
        align-items: center;
        z-index: 1000;
        animation: fadeIn 0.3s;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    .popup-content {
        position: relative;
        max-width: 90%;
        max-height: 90vh;
        text-align: center;
    }

    #popupImage {
        max-width: 100%;
        max-height: 80vh;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
    }

    .close-btn {
        position: absolute;
        top: 20px;
        right: 30px;
        font-size: 40px;
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .close-btn:hover {
        color: #E0AA6E;
        transform: rotate(90deg);
    }

    @media (max-width: 768px) {

        .data-table th:nth-child(4),
        .data-table td:nth-child(4) {
            display: none;
        }

        .user-thumbnail {
            width: 40px;
            height: 40px;
        }

        .btn-group {
            width: 100%;
            justify-content: center;
        }

        .action-btn {
            width: 30px;
            height: 30px;
            font-size: 0.9rem;
        }
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
                        <td><span class="role-badge"><?= htmlspecialchars($row['role']) ?></span></td>
                        <td>
                            <?php if (!empty($row['foto'])): 
                                $imagePath = $row['foto'];
                                if (strpos($imagePath, 'uploads/') !== 0) {
                                    $imagePath = 'uploads/' . $imagePath;
                                }
                            ?>
                            <img src="<?= $imagePath ?>" class="user-thumbnail"
                                onclick="showImage('<?= $imagePath ?>', '<?= htmlspecialchars($row['nama_user']) ?>')">
                            <?php else: ?>
                            <span style="color: #999;">Tidak ada</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-links">
                                <a href="edit_pengguna.php?id=<?= $row['UserID'] ?>" class="action-btn btn-edit"
                                    title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="hapus_pengguna.php?id=<?= $row['UserID'] ?>"
                                    class="action-btn btn-delete delete-btn" title="Hapus">
                                    <i class="fas fa-trash-alt"></i>
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

    <div id="imagePopup">
        <span class="close-btn" onclick="closeImage()">&times;</span>
        <div class="popup-content">
            <img id="popupImage" src="" alt="Foto Pengguna">
            <div id="imageCaption" style="color: white; margin-top: 10px;"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    const fullData = <?php echo json_encode($allData); ?>;
    const tableBody = document.getElementById('tableBody');
    const searchInput = document.getElementById('searchInput');

    function renderTable(data) {
        let html = '';
        if (data.length === 0) {
            html =
                `<tr><td colspan="6" style="text-align: center; padding: 2rem;">Tidak ada data yang ditemukan</td></tr>`;
        } else {
            data.forEach((row, index) => {
                const imagePath = row.foto ?
                    (row.foto.startsWith("uploads/") ? row.foto : `uploads/${row.foto}`) :
                    null;

                html += `<tr>
                    <td>${index + 1}</td>
                    <td>${escapeHtml(row.nama_user)}</td>
                    <td>${escapeHtml(row.username)}</td>
                    <td><span class="role-badge">${escapeHtml(row.role)}</span></td>
                    <td>
                        ${imagePath ? 
                            `<img src="${imagePath}" class="user-thumbnail" 
                                 onclick="showImage('${imagePath}', '${escapeHtml(row.nama_user)}')">` : 
                            `<span style="color: #999;">Tidak ada</span>`}
                    </td>
                    <td>
                        <div class="action-links">
                            <a href="edit_pengguna.php?id=${row.UserID}" 
                               class="action-btn btn-edit"
                               title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="hapus_pengguna.php?id=${row.UserID}" 
                               class="action-btn btn-delete delete-btn"
                               title="Hapus">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </div>
                    </td>
                </tr>`;
            });
        }
        tableBody.innerHTML = html;
        attachDeleteListeners();
    }

    function escapeHtml(text) {
        return text.replace(/[&<>"']/g, m => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        } [m]));
    }

    searchInput.addEventListener('input', (e) => {
        const term = e.target.value.toLowerCase();
        const filtered = fullData.filter(item =>
            Object.values(item).some(value =>
                String(value).toLowerCase().includes(term)
            )
        );
        renderTable(filtered);
    });

    // Fitur tampilan gambar
    function showImage(imageUrl, username) {
        const popup = document.getElementById('imagePopup');
        const img = document.getElementById('popupImage');
        const caption = document.getElementById('imageCaption');

        img.src = imageUrl;
        caption.textContent = `Foto Profil: ${username}`;
        popup.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeImage() {
        document.getElementById('imagePopup').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    window.onclick = (event) => {
        if (event.target === document.getElementById('imagePopup')) {
            closeImage();
        }
    };

    // Konfirmasi hapus
    function attachDeleteListeners() {
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const deleteUrl = this.href;

                Swal.fire({
                    title: 'Hapus Pengguna?',
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#E0AA6E',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = deleteUrl;
                    }
                });
            });
        });
    }

    attachDeleteListeners();
    </script>
    <?php $koneksi->close(); ?>
</body>

</html>