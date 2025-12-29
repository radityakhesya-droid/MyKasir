<?php
// Mengambil data dari Environment Variables Vercel
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');
$db   = getenv('DB_NAME');
$port = getenv('DB_PORT') ?: 4000;

// Inisialisasi koneksi MySQL
$conn = mysqli_init();

// Wajib menggunakan SSL untuk koneksi ke TiDB Cloud
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);

// Melakukan koneksi
$success = mysqli_real_connect($conn, $host, $user, $pass, $db, $port);

if (!$success) {
    die("Koneksi Database Gagal: " . mysqli_connect_error());
}
?>