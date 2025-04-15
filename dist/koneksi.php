<?php
// db.php: File koneksi database
$servername = "localhost";
$dbUsername = "root";   // ganti dengan username database Anda
$dbPassword = "";   // ganti dengan password database Anda
$dbname = "kasir_reddra";

// Membuat koneksi
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>