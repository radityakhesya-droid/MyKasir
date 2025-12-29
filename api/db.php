<?php
// Matikan pelaporan error mysqli untuk sementara agar tidak membocorkan password jika gagal
mysqli_report(MYSQLI_REPORT_OFF);

$conn = mysqli_init();

if (!$conn) {
    die("mysqli_init gagal");
}

// 1. Atur SSL (Parameter NULL berarti menggunakan default sistem yang aman)
// Baris ini WAJIB ada di atas mysqli_real_connect
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);

// 2. Lakukan koneksi
$success = mysqli_real_connect(
    $conn, 
    getenv('DB_HOST'), 
    getenv('DB_USER'), 
    getenv('DB_PASSWORD'), 
    getenv('DB_NAME'), 
    4000,
    NULL,
    MYSQLI_CLIENT_SSL // Tambahkan flag SSL eksplisit di sini
);

if (!$success) {
    die("Kesalahan Koneksi: " . mysqli_connect_error());
}
?>