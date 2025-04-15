<?php
session_start();
require_once 'dist/koneksi.php';

// Jika session sudah ada, langsung alihkan
if (isset($_SESSION['user_id'])) {
    header("Location: admin/index.php");
    exit;
}

// Cek cookie "remember me" jika tidak ada session
if (isset($_COOKIE['rememberme'])) {
    $token = $_COOKIE['rememberme'];
    $stmt = $conn->prepare("SELECT * FROM user WHERE remember_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        // Set session dan alihkan
        $_SESSION['user_id'] = $user['UserID'];
        $_SESSION['role'] = $user['role'];
        header("Location: admin/index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Royal Dumpling - Login</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500&family=Poppins:wght@300;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">

</head>

<body>
    <div class="login-container">
        <div class="brand-logo">Dapurkkbus.</div>
        <div class="login-form">
            <h2>Selamat Datang</h2>
            <form action="proses_login.php" method="post">
                <div class="form-group">
                    <input type="text" autocomplete="off" placeholder="Username" name="username" required>
                </div>
                <div class="form-group">
                    <input type="password" autocomplete="off" placeholder="Password" name="password" required>
                </div>
                <div class="remember-forgot">
                    <label class="remember-me">
                        <input type="checkbox" name="remember">
                        Remember Me
                    </label>
                    <a href="#" style="color: #E0AA6E; text-decoration: none;">Forgot Password?</a>
                </div>
                <button type="submit" class="login-btn" name="login">SIGN IN</button>
            </form>
        </div>
    </div>

    <div class="image-container">
        <div class="image-text">
            <h3>Laras Pangsit Otentik</h3>
            <p>Di sini, kehangatan sederhana mengalun singkat namun penuh makna—mengisi hari dengan keaslian yang tak
                lekang oleh waktu.</p>
        </div>
    </div>
</body>

</html>