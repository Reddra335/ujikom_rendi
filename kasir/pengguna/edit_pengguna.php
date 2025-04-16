<?php
// edit_pengguna.php

// Konfigurasi koneksi database
$mysqli = new mysqli("localhost", "root", "", "kasir_reddra");
if ($mysqli->connect_errno) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}

// Validasi parameter GET 'id'
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID pengguna tidak valid.");
}
$penggunaID = (int) $_GET['id'];

// Ambil data pengguna berdasarkan ID
$stmt = $mysqli->prepare("SELECT * FROM user WHERE UserID = ?");
$stmt->bind_param("i", $penggunaID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    die("Pengguna tidak ditemukan.");
}

// Inisialisasi variabel untuk output pesan dan status
$message   = "";
$isSuccess = false;

// Proses update data pengguna jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {

    // Tangkap dan sanitasi input pengguna
    $nama_user = isset($_POST['nama_user']) ? trim($_POST['nama_user']) : "";
    $username_input = isset($_POST['username']) ? trim($_POST['username']) : "";
    $role = isset($_POST['role']) ? trim($_POST['role']) : "";
    // Password bersifat opsional, jika tidak diisi maka tidak diupdate
    $password_input = isset($_POST['password']) ? trim($_POST['password']) : "";

    // Validasi input wajib (kecuali password)
    if (empty($nama_user) || empty($username_input) || empty($role)) {
        $message = "Semua field wajib diisi.";
    } elseif (!in_array($role, ['admin', 'kasir', 'owner'])) {
        $message = "Role tidak valid.";
    }

    // Proses upload foto (jika ada file yang dipilih)
    // Default: tetap gunakan foto yang sudah ada
    $fotoPath = $user['foto'];
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] != 4) {
        if ($_FILES['foto']['error'] === 0) {
            // Tipe file yang diperbolehkan
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType     = mime_content_type($_FILES['foto']['tmp_name']);
            if (!in_array($fileType, $allowedTypes)) {
                $message = "Error: File yang diupload harus berupa gambar (JPG, PNG, atau GIF).";
            } else {
                $uploadDir = "uploads/";
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                // Sanitasi nama file
                $fileName   = time() . "_" . preg_replace("/[^a-zA-Z0-9\._-]/", "", basename($_FILES['foto']['name']));
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

    // Cek uniqueness username (selain data sendiri)
    if ($message == "") {
        $stmtUnique = $mysqli->prepare("SELECT COUNT(*) FROM user WHERE username = ? AND UserID <> ?");
        if ($stmtUnique) {
            $stmtUnique->bind_param("si", $username_input, $penggunaID);
            $stmtUnique->execute();
            $stmtUnique->bind_result($count);
            $stmtUnique->fetch();
            $stmtUnique->close();
            if ($count > 0) {
                $message = "Error: Username sudah digunakan, tidak boleh sama.";
            }
        } else {
            $message = "Error: Gagal menyiapkan pengecekan username. " . $mysqli->error;
        }
    }
    
    // Jika tidak ada error validasi, lanjutkan update data
    if ($message == "") {
        // Jika password diisi, update dengan hash baru. Jika tidak, abaikan update password.
        if (!empty($password_input)) {
            $hashedPassword = password_hash($password_input, PASSWORD_DEFAULT);
            $queryUpdate = "UPDATE user SET nama_user = ?, username = ?, password = ?, role = ?, foto = ? WHERE UserID = ?";
            $stmt = $mysqli->prepare($queryUpdate);
            $stmt->bind_param("sssssi", $nama_user, $username_input, $hashedPassword, $role, $fotoPath, $penggunaID);
        } else {
            $queryUpdate = "UPDATE user SET nama_user = ?, username = ?, role = ?, foto = ? WHERE UserID = ?";
            $stmt = $mysqli->prepare($queryUpdate);
            $stmt->bind_param("ssssi", $nama_user, $username_input, $role, $fotoPath, $penggunaID);
        }
        if ($stmt) {
            if ($stmt->execute()) {
                $message = "Pengguna berhasil diupdate!";
                $isSuccess = true;
            } else {
                $message = "Gagal mengupdate data pengguna. " . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = "Gagal menyiapkan statement. " . $mysqli->error;
        }
    }

    // Notifikasi menggunakan SweetAlert2, redirect ke halaman kelola_pengguna.php
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
  text: "Pengguna berhasil diupdate!",
  timer: 1500,
  showConfirmButton: false,
  timerProgressBar: true
}).then(function() {
  window.location.href = "pengguna.php";
});
</script>
</body>
</html>';
        exit;
    } else {
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
  text: "' . htmlspecialchars($message, ENT_QUOTES) . '",
  timer: 2000,
  showConfirmButton: false,
  timerProgressBar: true
}).then(function() {
  window.location.href = "pengguna.php";
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
    <title>Edit Pengguna</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #fafafa;
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
        font-size: 1.8rem;
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="content-card">
        <div class="card-header">
            <h2>Form Edit Pengguna</h2>
        </div>
        <!-- Form Edit Pengguna -->
        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label class="form-label" for="nama_user">Nama Pengguna:</label>
                <input type="text" name="nama_user" id="nama_user" class="form-control"
                    value="<?= htmlspecialchars($user['nama_user']) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="username">Username:</label>
                <input type="text" name="username" id="username" class="form-control"
                    value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <!-- Password kosong jika tidak ingin diupdate -->
            <div class="form-group">
                <label class="form-label" for="password">Password (kosongkan jika tidak diubah):</label>
                <input type="password" name="password" id="password" class="form-control"
                    placeholder="Masukkan password baru jika ingin mengubah">
            </div>
            <div class="form-group">
                <label class="form-label" for="role">Role Pengguna:</label>
                <select name="role" id="role" class="form-control" required>
                    <option value="" disabled>Pilih role</option>
                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="kasir" <?= $user['role'] == 'kasir' ? 'selected' : '' ?>>Kasir</option>
                    <option value="owner" <?= $user['role'] == 'owner' ? 'selected' : '' ?>>Owner</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="foto">Foto Pengguna (Opsional):</label>
                <div class="custom-file">
                    <input type="file" name="foto" id="foto" class="custom-file-input" accept="image/*">
                    <label for="foto" class="custom-file-label">
                        <?= ($user['foto'] != "") ? basename($user['foto']) : "Pilih Foto" ?>
                    </label>
                </div>
            </div>
            <button type="submit" class="btn-primary" name="submit">Update Pengguna</button>
        </form>
        <a href="pengguna.php" class="back-link">Kembali</a>
    </div>
    <!-- Script untuk meng-update label file dengan nama file yang diupload -->
    <script>
    document.getElementById("foto").addEventListener("change", function() {
        var fileName = this.files[0] ? this.files[0].name : "Pilih Foto";
        this.nextElementSibling.textContent = fileName;
    });
    </script>
    <?php $mysqli->close(); ?>
</body>

</html>