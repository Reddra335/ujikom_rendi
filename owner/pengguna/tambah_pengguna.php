<?php
// tambah_pengguna.php

// Konfigurasi koneksi database
$host     = "localhost";
$username = "root";
$password = "";
$dbname   = "kasir_reddra";

$mysqli = new mysqli($host, $username, $password, $dbname);
if ($mysqli->connect_errno) {
    die("Gagal koneksi ke database: " . $mysqli->connect_error);
}

// Inisialisasi variabel untuk output pesan dan status
$message   = "";
$isSuccess = false;

// Proses form jika disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {

    // Tangkap dan sanitasi input pengguna
    $nama_user = isset($_POST['nama_user']) ? trim($_POST['nama_user']) : "";
    $username_input = isset($_POST['username']) ? trim($_POST['username']) : "";
    $password_input = isset($_POST['password']) ? trim($_POST['password']) : "";
    $role = isset($_POST['role']) ? trim($_POST['role']) : "";
    
    // Validasi input wajib
    if (empty($nama_user) || empty($username_input) || empty($password_input) || empty($role)) {
        $message = "Semua field wajib diisi.";
    } elseif (!in_array($role, ['admin', 'kasir', 'owner'])) {
        $message = "Role tidak valid.";
    }
    
    // Proses upload foto (jika dipilih)
    $fotoPath = "";
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] != 4) {
        if ($_FILES['foto']['error'] === 0) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType = mime_content_type($_FILES['foto']['tmp_name']);
            if (!in_array($fileType, $allowedTypes)) {
                $message = "Error: File yang diupload harus berupa gambar (JPG, PNG, atau GIF).";
            } else {
                $uploadDir = "uploads/";
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                // Sanitasi nama file
                $fileName = time() . "_" . preg_replace("/[^a-zA-Z0-9\._-]/", "", basename($_FILES['foto']['name']));
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
    
    // Cek apakah username sudah ada (cek uniqueness)
    if ($message == "") {
        $stmtCheck = $mysqli->prepare("SELECT COUNT(*) FROM user WHERE username = ?");
        if($stmtCheck){
            $stmtCheck->bind_param("s", $username_input);
            $stmtCheck->execute();
            $stmtCheck->bind_result($count);
            $stmtCheck->fetch();
            $stmtCheck->close();

            if ($count > 0) {
                $message = "Error: Username sudah digunakan, tidak boleh sama.";
            }
        } else {
            $message = "Error: Gagal menyiapkan pengecekan username. " . $mysqli->error;
        }
    }
    
    // Jika tidak ada error validasi, lakukan INSERT data pengguna
    if ($message == "") {
        // Hash password menggunakan algoritma default (biasanya bcrypt)
        $hashedPassword = password_hash($password_input, PASSWORD_DEFAULT);
        // Karena kolom remember_token tidak digunakan di sini, kita set default sebagai string kosong
        $remember_token = "";
        
        $stmt = $mysqli->prepare("INSERT INTO user (nama_user, remember_token, username, password, role, foto) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssssss", $nama_user, $remember_token, $username_input, $hashedPassword, $role, $fotoPath);
            if ($stmt->execute()) {
                $message = "Pengguna berhasil disimpan!";
                $isSuccess = true;
            } else {
                $message = "Error: Gagal menyimpan data pengguna. " . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = "Error: Gagal menyiapkan statement. " . $mysqli->error;
        }
    }
    
    // Tampilkan notifikasi menggunakan SweetAlert2 dan redireksi ke pengguna.php secara otomatis
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
  text: "Pengguna berhasil disimpan!",
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
    <title>Tambah Pengguna</title>
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
            <h2>Form Input Pengguna</h2>
        </div>
        <!-- Form Input Pengguna -->
        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label class="form-label" for="nama_user">Nama Pengguna:</label>
                <input type="text" name="nama_user" id="nama_user" class="form-control"
                    placeholder="Masukkan nama lengkap pengguna" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="username">Username:</label>
                <input type="text" name="username" id="username" class="form-control" placeholder="Masukkan username"
                    required>
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password:</label>
                <input type="password" name="password" id="password" class="form-control"
                    placeholder="Masukkan password" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="role">Role Pengguna:</label>
                <select name="role" id="role" class="form-control" required>
                    <option value="" disabled selected>Pilih role</option>
                    <option value="admin">Admin</option>
                    <option value="kasir">Kasir</option>
                    <option value="owner">Owner</option>
                </select>
            </div>
            <!-- Custom File Upload untuk foto -->
            <div class="form-group">
                <label class="form-label" for="foto">Foto Pengguna (Opsional):</label>
                <div class="custom-file">
                    <input type="file" name="foto" id="foto" class="custom-file-input" accept="image/*">
                    <label for="foto" class="custom-file-label">Pilih Foto</label>
                </div>
            </div>
            <button type="submit" class="btn-primary" name="submit">Simpan Pengguna</button>
        </form>
        <a href="kelola_pengguna.php" class="back-link">Kembali</a>
    </div>

    <!-- Script untuk meng-update label file dengan nama file yang diupload -->
    <script>
    document.getElementById("foto").addEventListener("change", function() {
        var fileName = this.files[0] ? this.files[0].name : "Pilih Foto";
        // Mengupdate label yang berada di samping input file
        this.nextElementSibling.textContent = fileName;
    });
    </script>
    <?php $mysqli->close(); ?>
</body>

</html>