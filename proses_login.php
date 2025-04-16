<?php
session_start();
require_once 'dist/koneksi.php';

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Ambil data pengguna berdasarkan username
    $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Asumsikan password tersimpan dalam bentuk hash
        if(password_verify($password, $user['password'])) {
            // Set session untuk login
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['role'] = $user['role'];

            // Jika checkbox "Remember Me" dicentang
            if (isset($_POST['remember'])) {
                // Jika token belum ada, buat token baru
                if (empty($user['remember_token'])) {
                    $token = bin2hex(random_bytes(16));
                    $update_stmt = $conn->prepare("UPDATE user SET remember_token = ? WHERE UserID = ?");
                    $update_stmt->bind_param("si", $token, $user['UserID']);
                    $update_stmt->execute();
                } else {
                    $token = $user['remember_token'];
                }
                // Set cookie untuk 30 hari
                setcookie("rememberme", $token, time() + (86400 * 30), "/");
            }

            // Redirect berdasarkan peran pengguna
            switch ($user['role']) {
                case 'admin':
                    header("Location: admin/index.php");
                    break;
                case 'kasir':
                    header("Location: kasir/index.php");
                    break;
                case 'owner':
                    header("Location: owner/index.php");
                    break;
                default:
                    header("Location: index.php");
            }
            exit;
        } else {
            // Jika password salah
            $_SESSION['error'] = "Username atau Password salah.";
            header("Location: login.php");
            exit;
        }
    } else {
        // Jika username tidak ditemukan
        $_SESSION['error'] = "Username atau Password salah.";
        header("Location: login.php");
        exit;
    }
}
?>