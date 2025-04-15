<!-- <?php
// session_start();
// require_once 'dist/koneksi.php';

// // Jika session sudah ada, langsung alihkan
// if (isset($_SESSION['user_id'])) {
//     header("Location: dashboard.php");
//     exit;
// }

// // Cek cookie "remember me" jika tidak ada session
// if (isset($_COOKIE['rememberme'])) {
//     $token = $_COOKIE['rememberme'];
//     $stmt = $conn->prepare("SELECT * FROM user WHERE remember_token = ?");
//     $stmt->bind_param("s", $token);
//     $stmt->execute();
//     $result = $stmt->get_result();
//     if ($result->num_rows == 1) {
//         $user = $result->fetch_assoc();
//         // Set session dan alihkan
//         $_SESSION['user_id'] = $user['UserID'];
//         $_SESSION['role'] = $user['role'];
//         header("Location: admin/index.php");
//         exit;
//     }
// }
?> -->
<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
</head>

<body>
    <h2>Login Multi User</h2>
    <form action="proses_login.php" method="post">
        <label>Username:</label>
        <input type="text" name="username" required><br><br>

        <label>Password:</label>
        <input type="password" name="password" required><br><br>

        <label>Remember Me</label>
        <input type="checkbox" name="remember" value="1"><br><br>

        <button type="submit" name="login">Login</button>
    </form>
</body>

</html>