<?php
$host = 'gateway01.ap-southeast-1.prod.aws.tidbcloud.com';
$user = 'i6DvFHPQtMZfBFD.root';
$pass = 'hvDxHF05UC0fVYAF';
$db   = 'test';
$port = 4000; // TiDB biasanya menggunakan port 4000

$conn = mysqli_init();
// Mengaktifkan SSL (TiDB Cloud mewajibkan ini)
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL); 

$success = mysqli_real_connect($conn, $host, $user, $pass, $db, $port, NULL, MYSQLI_CLIENT_SSL);

if (!$success) {
    error_log("Koneksi TiDB gagal: " . mysqli_connect_error());
    die("Koneksi Database Bermasalah: " . mysqli_connect_error());
}
?>