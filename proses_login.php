<?php
session_start();
require_once 'dist/koneksi.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Mencari user berdasarkan username
    $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['role'] = $user['role'];
            
            if (isset($_POST['remember'])) {
                $token = bin2hex(random_bytes(16));
                $updateStmt = $conn->prepare("UPDATE user SET remember_token = ? WHERE UserID = ?");
                $updateStmt->bind_param("si", $token, $user['UserID']);
                $updateStmt->execute();
                setcookie("rememberme", $token, time() + (7 * 24 * 60 * 60), "/");
            }
            
            header("Location: admin/index.php");
            exit;
        } else {
            $error = "Username atau password tidak valid.";
        }
    } else {
        $error = "Username atau password tidak valid.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Login Error</title>
    <!-- Sertakan Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <!-- Sertakan Animate.css untuk animasi -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <!-- Sertakan Google Font (misalnya 'Playfair Display' untuk nuansa mewah) -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <!-- Sertakan jQuery, Popper.js, dan Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

    <style>
    /* Custom CSS untuk tema coklat mewah dengan gradien dan tekstur ringan */
    .modal-content {
        background: linear-gradient(135deg, #6d4c41, #8d6e63);
        /* gradien coklat */
        color: #f5f5f5;
        border: 2px solid #d7ccc8;
        border-radius: 12px;
        font-family: 'Playfair Display', serif;
        box-shadow: 0 0 25px rgba(0, 0, 0, 0.5);
        padding: 20px;
    }

    .modal-header,
    .modal-footer {
        border-color: #d7ccc8;
    }

    .modal-title {
        font-size: 1.75rem;
    }

    .btn-custom {
        background-color: #8d6e63;
        border: none;
        color: #fff;
        padding: 10px 20px;
        font-size: 1rem;
        border-radius: 5px;
        transition: background-color 0.3s;
    }

    .btn-custom:hover {
        background-color: #795548;
    }
    </style>

    <script type="text/javascript">
    $(document).ready(function() {
        <?php if (isset($error)) { ?>
        // Tambahkan kelas animasi saat modal tampil
        $('#errorModal .modal-content').addClass('animate__animated animate__zoomIn');
        $('#errorModal').modal('show');
        <?php } ?>
    });
    </script>
</head>

<body>
    <!-- Halaman ini hanya menampilkan pop-up modal error -->
    <div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalLabel">Login Error</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                        onclick="window.location.href='login.php';">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php echo isset($error) ? $error : 'Terjadi kesalahan'; ?>
                </div>
                <div class="modal-footer">
                    <a href="login.php" class="btn btn-custom">Kembali ke halaman login</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>