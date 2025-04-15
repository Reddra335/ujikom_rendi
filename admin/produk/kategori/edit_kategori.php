<?php 
// Pastikan koneksi ada; jika tidak, buat koneksi
if (!isset($conn)) {
    $conn = mysqli_connect("localhost", "root", "", "kasir_reddra");
    if (!$conn) {
        die("Koneksi gagal: " . mysqli_connect_error());
    }
}

$error_message = "";

if (!isset($_GET['id'])) {
    die("ID kategori tidak ditemukan.");
}

$kategori_id = intval($_GET['id']);

// Ambil data kategori berdasarkan ID
$stmt = $conn->prepare("SELECT nama_kategori, deskripsi FROM kategori WHERE kategori_id = ?");
$stmt->bind_param("i", $kategori_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 0) {
    die("Kategori dengan ID " . htmlspecialchars($kategori_id) . " tidak ditemukan.");
}

$stmt->bind_result($nama_kategori_old, $deskripsi_old);
$stmt->fetch();
$stmt->close();

// Variabel untuk menampung error dan pesan sukses
$errors = [];
$success_message = '';

// Proses update jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form dan sanitasi input
    $nama_kategori = mysqli_real_escape_string($conn, stripslashes($_POST['nama_kategori']));
    $deskripsi     = mysqli_real_escape_string($conn, stripslashes($_POST['deskripsi']));

    // Validasi: pastikan nama kategori tidak kosong
    if (empty($nama_kategori)) {
        $errors[] = "Nama kategori harus diisi.";
    }

    // Jika validasi berhasil, lakukan update data kategori
    if (empty($errors)) {
        $stmt_update = $conn->prepare("UPDATE kategori SET nama_kategori = ?, deskripsi = ? WHERE kategori_id = ?");
        $stmt_update->bind_param("ssi", $nama_kategori, $deskripsi, $kategori_id);

        if ($stmt_update->execute()) {
            $success_message = "Kategori berhasil diperbarui.";
            // Update nilai lama agar form selalu menunjukkan data terbaru
            $nama_kategori_old = $nama_kategori;
            $deskripsi_old = $deskripsi;
        } else {
            $errors[] = "Gagal memperbarui kategori: " . $stmt_update->error;
        }
        $stmt_update->close();
    }
}
$conn->close();
?>

<!-- Tampilan Form -->
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Kategori</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Google Fonts untuk Playfair Display dan Poppins -->
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Poppins:wght@300;500;600;700&display=swap"
        rel="stylesheet">
    <!-- SweetAlert2 (jika diperlukan untuk notifikasi) -->
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #fff;
        /* Latar belakang putih */
        margin: 0;
        padding: 0;
    }

    /* Container penuh, sehingga form memanjang dari ujung ke ujung */
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
        /* Warna emas mewah */
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
    </style>
</head>

<body>
    <div class="content-card">
        <div class="card-header">
            <h2>Edit Kategori</h2>
        </div>

        <!-- Tampilkan pesan error jika ada -->
        <?php if (!empty($errors)): ?>
        <div
            style="text-align: center; padding: 1rem; border-radius: 8px; background: #f8d7da; color: #721c24; margin-bottom: 1rem;">
            <ul>
                <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Tampilkan pesan sukses jika ada -->
        <?php if ($success_message): ?>
        <div
            style="text-align: center; padding: 1rem; border-radius: 8px; background: #d4edda; color: #155724; margin-bottom: 1rem;">
            <?= htmlspecialchars($success_message) ?>
        </div>
        <?php endif; ?>

        <form action="edit_kategori.php?id=<?= htmlspecialchars($kategori_id) ?>" method="post">
            <div class="form-group">
                <label for="nama_kategori" class="form-label">Nama Kategori</label>
                <input type="text" id="nama_kategori" name="nama_kategori" class="form-control"
                    value="<?= htmlspecialchars($nama_kategori_old) ?>" required>
            </div>
            <div class="form-group">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" class="form-control"
                    rows="4"><?= htmlspecialchars($deskripsi_old) ?></textarea>
            </div>
            <button type="submit" class="btn-primary">Update Kategori</button>
        </form>
        <a class="back-link" href="kategori.php">Kembali ke Daftar Kategori</a>
    </div>
</body>

</html>