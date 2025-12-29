<?php
/**
 * Konfigurasi Database mykasir
 */

$host = "localhost";
$user = "root";
$pass = ""; // Kosongkan jika menggunakan XAMPP default
$db   = "mykasir123";

// Membuat koneksi ke database
$conn = mysqli_connect($host, $user, $pass, $db);

// Cek Koneksi
if (!$conn) {
    // Menghentikan eksekusi jika gagal dan menampilkan pesan error
    die("Gagal terhubung ke database: " . mysqli_connect_error());
}

// Set timezone agar waktu transaksi akurat (WIB)
date_default_timezone_set('Asia/Jakarta');
?>