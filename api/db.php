<?php
// 1. Inisialisasi objek mysqli
$conn = mysqli_init();

// 2. Wajib set SSL sebelum melakukan koneksi (ini kunci utamanya)
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);

// 3. Ambil data dari Environment Variables
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');
$db   = getenv('DB_NAME');
$port = 4000;

// 4. Lakukan koneksi dengan error suppression (@) untuk handling manual
$success = mysqli_real_connect($conn, $host, $user, $pass, $db, $port);

if (!$success) {
    die("Koneksi gagal ke TiDB Cloud: " . mysqli_connect_error());
}
?>