<?php
session_start();
session_destroy();

// Hapus cookie "remember me"
setcookie("rememberme", "", time() - 3600, "/");

header("Location: ../login.php");
exit;