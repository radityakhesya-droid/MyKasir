<?php
$conn = mysqli_init();
// Wajib menggunakan SSL untuk TiDB Cloud
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);

$success = mysqli_real_connect(
    $conn, 
    getenv('DB_HOST'), 
    getenv('DB_USER'), 
    getenv('DB_PASSWORD'), 
    getenv('DB_NAME'), 
    4000
);

if (!$success) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>