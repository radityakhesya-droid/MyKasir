<?php
$conn = mysqli_init();

// Baris ini SANGAT PENTING: Mengaktifkan mode SSL sebelum koneksi dibuat
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);

// Melakukan koneksi menggunakan data dari Environment Variables Vercel
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