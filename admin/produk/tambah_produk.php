<?php
// tambah_produk.php

// Konfigurasi koneksi database
$host     = "localhost";
$username = "root";
$password = "";
$dbname   = "kasir_reddra";

// Membuat koneksi menggunakan mysqli
$mysqli = new mysqli($host, $username, $password, $dbname);
if ($mysqli->connect_errno) {
    die("Gagal koneksi ke database: " . $mysqli->connect_error);
}

// Ambil data kategori untuk modal (untuk popup)
$categoryList = [];
$query  = "SELECT kategori_id, nama_kategori FROM kategori ORDER BY nama_kategori ASC";
$resultOption = $mysqli->query($query);
while ($row = $resultOption->fetch_assoc()) {
    $categoryList[] = $row;
}

// Inisialisasi variabel untuk output pesan dan status
$message   = "";
$isSuccess = false;

// Proses form jika disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {

    // Tangkap dan sanitasi input produk
    $namaProduk = isset($_POST['namaProduk']) ? trim($_POST['namaProduk']) : "";
    $harga      = isset($_POST['harga']) ? trim($_POST['harga']) : "";
    $stok       = isset($_POST['stok']) ? trim($_POST['stok']) : "";
    // Nilai kategori diambil dari hidden input 'kategori'
    $kategori   = isset($_POST['kategori']) ? trim($_POST['kategori']) : "";
    $deskripsi  = isset($_POST['deskripsi']) ? trim($_POST['deskripsi']) : "";

    // Validasi kategori: Pastikan nilai tidak kosong dan merupakan angka
    if (empty($kategori) || !is_numeric($kategori)) {
        $message = "Error: Kategori harus dipilih dengan benar.";
    } else {
        $kategori = (int)$kategori; // Konversi ke integer
        // Cek apakah kategori ada di tabel
        $stmtCheck = $mysqli->prepare("SELECT kategori_id FROM kategori WHERE kategori_id = ?");
        $stmtCheck->bind_param("i", $kategori);
        $stmtCheck->execute();
        $stmtCheck->store_result();
        if ($stmtCheck->num_rows == 0) {
            $message = "Error: Kategori tidak valid.";
        }
        $stmtCheck->close();
    }

    // Variabel untuk menyimpan path foto (jika diupload)
    $fotoPath = "";

    // Proses upload foto (jika ada file yang dipilih)
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] != 4) {
        if ($_FILES['foto']['error'] === 0) {
            // Tipe file yang diperbolehkan
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType     = mime_content_type($_FILES['foto']['tmp_name']);
            // Validasi tipe file
            if (!in_array($fileType, $allowedTypes)) {
                $message = "Error: File yang diupload harus berupa gambar (JPG, PNG, GIF).";
            } else {
                $uploadDir = "uploads/";
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $fileName   = time() . "_" . basename($_FILES['foto']['name']);
                $targetFile = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetFile)) {
                    $fotoPath = $targetFile;
                } else {
                    $message = "Error: Gagal mengupload file.";
                }
            }
        } else {
            $message = "Terjadi kesalahan saat mengupload foto.";
        }
    }

    // Jika tidak ada error validasi, lakukan INSERT data produk
    if ($message == "") {
        $stmt = $mysqli->prepare("INSERT INTO produk (NamaProduk, Harga, Stok, kategori_id, deskripsi, gambar) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sdiiss", $namaProduk, $harga, $stok, $kategori, $deskripsi, $fotoPath);
            if ($stmt->execute()) {
                $message = "Produk berhasil disimpan!";
                $isSuccess = true;
            } else {
                $message = "Error: Gagal menyimpan data produk. " . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = "Error: Gagal menyiapkan statement. " . $mysqli->error;
        }
    }

    // Jika sudah diproses, tampilkan notifikasi menggunakan SweetAlert2
    if ($isSuccess) {
        echo '<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Sukses</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>body { margin: 0; padding: 0; }</style>
</head>
<body>
<script>
Swal.fire({
  icon: "success",
  title: "Sukses",
  text: "Produk berhasil disimpan!",
  timer: 1500,
  showConfirmButton: false,
  timerProgressBar: true
}).then(function() {
  window.location.href = "produk.php";
});
</script>
</body>
</html>';
        exit;
    } else {
        // Jika terjadi error, tampilkan notifikasi error
        echo '<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Gagal</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>body { margin: 0; padding: 0; }</style>
</head>
<body>
<script>
Swal.fire({
  icon: "error",
  title: "Gagal",
  text: "'.htmlspecialchars($message, ENT_QUOTES).'",
  timer: 2000,
  showConfirmButton: false,
  timerProgressBar: true
}).then(function() {
  window.location.href = "produk.php";
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
    <title>Input Produk</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Google Fonts: Playfair Display dan Poppins -->
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Poppins:wght@300;500;600;700&display=swap"
        rel="stylesheet">
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #fff;
        margin: 0;
        padding: 0;
    }

    .content-card {
        width: 100%;
        padding: 20px;
        box-sizing: border-box;
    }

    .card-header h2 {
        text-align: center;
        margin-bottom: 20px;
        font-family: "Playfair Display", serif;
        color: #3D2B1F;
        transition: color 0.3s ease, text-shadow 0.3s ease;
    }

    .card-header h2:hover {
        color: #d4af37;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #3D2B1F;
    }

    .form-control {
        width: 100%;
        padding: 0.8rem;
        border-radius: 8px;
        border: 1px solid #ddd;
        font-size: 1rem;
        box-sizing: border-box;
        text-align: center;
    }

    /* Tombol kategori untuk membuka modal */
    .btn-category {
        width: 100%;
        padding: 1rem;
        border: none;
        border-radius: 8px;
        background-color: #E0AA6E;
        color: #fff;
        font-size: 1rem;
        cursor: pointer;
        margin-bottom: 1rem;
        transition: background 0.3s ease;
    }

    .btn-category:hover {
        background-color: #d4a373;
    }

    button.btn-primary {
        width: 100%;
        padding: 1rem;
        border: none;
        border-radius: 8px;
        background-color: #E0AA6E;
        color: #fff;
        font-size: 1rem;
        cursor: pointer;
        margin-top: 10px;
        transition: background 0.3s ease;
    }

    button.btn-primary:hover {
        background-color: #d4a373;
    }

    .back-link {
        display: block;
        text-align: center;
        margin-top: 1rem;
        color: #E0AA6E;
        text-decoration: none;
        font-weight: 600;
    }

    .back-link:hover {
        text-decoration: underline;
    }

    .custom-file {
        position: relative;
        display: inline-block;
        width: 100%;
    }

    .custom-file-input {
        opacity: 0;
        position: absolute;
        z-index: 2;
        width: 100%;
        height: 40px;
        cursor: pointer;
    }

    .custom-file-label {
        display: block;
        background-color: #E0AA6E;
        color: #fff;
        text-align: center;
        border-radius: 8px;
        padding: 10px 0;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .custom-file-label:hover {
        background-color: #d4a373;
    }

    /* Modal Styles untuk pemilihan kategori */
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
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .modal-header h3 {
        margin: 0;
        font-family: "Playfair Display", serif;
        color: #3D2B1F;
    }

    .modal-header button {
        background: transparent;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
    }

    .modal-search {
        margin-bottom: 15px;
    }

    .modal-search input {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 8px;
    }

    .modal-category-list {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #ddd;
        border-radius: 8px;
    }

    .modal-category-item {
        padding: 10px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
        text-align: center;
        /* Teks kategori di tengah untuk simetris */
        transition: background 0.3s ease;
    }

    .modal-category-item:hover {
        background: #f0f0f0;
    }

    /* Biar teks kategori di input terpilih berada di kiri */
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="content-card">
        <div class="card-header">
            <h2>Form Input Produk</h2>
        </div>
        <!-- Form Input Produk -->
        <form action="" method="post" enctype="multipart/form-data">
            <!-- Area Pemilihan Kategori via Modal Popup -->
            <div class="form-group">
                <label class="form-label" for="selectedCategory">Kategori Terpilih:</label>
                <!-- Teks di kiri -->
                <input type="text" id="selectedCategory" class="form-control" placeholder="Belum dipilih" readonly>
                <!-- Hidden input untuk menyimpan kategori_id -->
                <input type="hidden" name="kategori" id="kategori">
                <button type="button" id="openCategoryModal" class="btn-category">Pilih Kategori</button>
            </div>
            <div class="form-group">
                <label class="form-label" for="namaProduk">Nama Produk:</label>
                <input type="text" name="namaProduk" id="namaProduk" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="harga">Harga:</label>
                <input type="number" name="harga" id="harga" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="stok">Stok:</label>
                <input type="number" name="stok" id="stok" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="deskripsi">Deskripsi Produk (Opsional):</label>
                <textarea name="deskripsi" id="deskripsi" class="form-control" rows="5"></textarea>
            </div>
            <!-- Custom File Upload untuk foto produk -->
            <div class="form-group">
                <label class="form-label" for="foto">Foto Produk (Opsional):</label>
                <div class="custom-file">
                    <input type="file" name="foto" id="foto" class="custom-file-input" accept="image/*">
                    <label for="foto" class="custom-file-label">Pilih Foto</label>
                </div>
            </div>
            <button type="submit" class="btn-primary" name="submit">Simpan Produk</button>
        </form>
        <a href="produk.php" class="back-link">Kembali</a>
    </div>

    <!-- Modal untuk pemilihan kategori -->
    <div id="categoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Pilih Kategori</h3>
                <button id="closeCategoryModal">&times;</button>
            </div>
            <div class="modal-search">
                <input type="text" id="modalSearch" placeholder="Cari kategori...">
            </div>
            <div class="modal-category-list" id="modalCategoryList">
                <?php foreach ($categoryList as $cat): ?>
                <div class="modal-category-item" data-id="<?= $cat['kategori_id']; ?>">
                    <?= htmlspecialchars($cat['nama_kategori']); ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
    // Referensi elemen modal dan tombol
    const openModalBtn = document.getElementById('openCategoryModal');
    const categoryModal = document.getElementById('categoryModal');
    const closeModalBtn = document.getElementById('closeCategoryModal');
    const modalSearch = document.getElementById('modalSearch');
    const modalCategoryList = document.getElementById('modalCategoryList');
    const hiddenCategoryInput = document.getElementById('kategori');
    const selectedCategoryInput = document.getElementById('selectedCategory');

    // Buka modal & reset pencarian
    openModalBtn.addEventListener('click', () => {
        categoryModal.style.display = 'flex';
        modalSearch.value = '';
        filterCategoryItems('');
    });

    // Tutup modal
    closeModalBtn.addEventListener('click', () => {
        categoryModal.style.display = 'none';
    });

    window.addEventListener('click', (e) => {
        if (e.target === categoryModal) {
            categoryModal.style.display = 'none';
        }
    });

    // Fungsi pencarian dalam modal
    modalSearch.addEventListener('keyup', function() {
        let term = this.value.toLowerCase().trim();
        filterCategoryItems(term);
    });

    function filterCategoryItems(term) {
        const items = document.querySelectorAll('.modal-category-item');
        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(term) ? 'block' : 'none';
        });
    }

    // Saat kategori dipilih
    modalCategoryList.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('modal-category-item')) {
            let catId = e.target.getAttribute('data-id');
            let catName = e.target.textContent;
            hiddenCategoryInput.value = catId; // isikan ID ke hidden input
            selectedCategoryInput.value = catName; // tampilkan nama di field
            categoryModal.style.display = 'none';
        }
    });

    // Update label custom file upload saat file dipilih
    document.getElementById('foto').addEventListener('change', function() {
        var fileName = this.files[0] ? this.files[0].name : "Pilih Foto";
        this.nextElementSibling.innerText = fileName;
    });
    </script>
    <?php $mysqli->close(); ?>
</body>

</html>