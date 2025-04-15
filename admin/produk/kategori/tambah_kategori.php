<?php 
// Pastikan koneksi ada; jika tidak, buat koneksi
if (!isset($conn)) {
    // Ganti "reddrakasir" dengan "kasir_reddra" sesuai nama database Anda
    $conn = mysqli_connect("localhost", "root", "", "kasir_reddra");
    if (!$conn) {
        die("Koneksi gagal: " . mysqli_connect_error());
    }
}

$error_message = "";

if (isset($_POST['kirim'])) {
    // Ambil dan sanitasi data
    $nama_kategori = mysqli_real_escape_string($conn, stripslashes($_POST['nama_kategori']));
    $deskripsi     = mysqli_real_escape_string($conn, stripslashes($_POST['deskripsi']));

    // Validasi: pastikan nama kategori tidak kosong
    if (empty($nama_kategori)) {
        $error_message = "Nama kategori harus diisi.";
    }

    // Jika tidak ada error, masukkan data ke database
    if (empty($error_message)) {
        $query_insert = "INSERT INTO kategori (nama_kategori, deskripsi) VALUES ('$nama_kategori', '$deskripsi')";
        $execute = mysqli_query($conn, $query_insert);

        if ($execute) {
            // Jika berhasil, tampilkan SweetAlert dan redirect dengan JavaScript
            echo '<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Sukses</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body { margin: 0; padding: 0; }
  </style>
</head>
<body>
<script>
Swal.fire({
  icon: "success",
  title: "Kategori",
  text: "Kategori Berhasil Ditambahkan!",
  timer: 1000,
  showConfirmButton: false,
  timerProgressBar: true
}).then(function() {
  window.location.href = "kategori.php";
});
</script>
</body>
</html>';
            exit;
        } else {
            $error_message = "Gagal Tambah Data: " . mysqli_error($conn);
        }
    }
}
?>

<!-- Tampilan Form -->
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tambah Kategori</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Google Fonts untuk Playfair Display dan Poppins -->
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
            <h2>Tambah Kategori</h2>
        </div>

        <!-- Tampilkan pesan error jika ada -->
        <?php if ($error_message != "") { ?>
        <div
            style="text-align: center; padding: 1rem; border-radius: 8px; background: #f8d7da; color: #721c24; margin-bottom: 1rem;">
            <?php echo $error_message; ?>
        </div>
        <?php } ?>

        <form method="post" action="">
            <div class="form-group">
                <label class="form-label">Nama Kategori</label>
                <input type="text" name="nama_kategori" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea name="deskripsi" class="form-control" rows="4"></textarea>
            </div>
            <button type="submit" class="btn-primary" name="kirim">Simpan Kategori</button>
        </form>
        <a href="kategori.php" class="back-link">Kembali</a>
    </div>
</body>

</html>