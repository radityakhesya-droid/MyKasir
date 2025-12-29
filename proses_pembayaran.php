<?php
session_start();
require 'db.php';
require 'functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_SESSION['keranjang'])) {
    $total_tagihan = hitungTotal($_SESSION['keranjang']);
    $nominal_bayar = (int)$_POST['nominal_bayar'];
    $kembalian = $nominal_bayar - $total_tagihan;

    // Proses Pengurangan Stok
    $conn->begin_transaction();
    try {
        foreach ($_SESSION['keranjang'] as $id => $item) {
            $stmt = $conn->prepare("UPDATE produk SET stok = stok - ? WHERE id = ?");
            $stmt->bind_param("ii", $item['jumlah'], $id);
            $stmt->execute();
        }
        $conn->commit();
        
        // Simpan data untuk ditampilkan sebelum dihapus
        $ringkasan = [
            'total' => $total_tagihan,
            'bayar' => $nominal_bayar,
            'kembali' => $kembalian
        ];
        
        $_SESSION['keranjang'] = []; // Kosongkan keranjang
    } catch (Exception $e) {
        $conn->rollback();
        die("Gagal memproses transaksi: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transaksi Berhasil - MyKasir</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .success-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: var(--background);
        }
        .success-card {
            width: 100%;
            max-width: 400px;
            text-align: center;
            padding: 3rem 2rem;
            border-top: 8px solid #4caf50; /* Garis hijau sukses di atas */
        }
        .icon-box {
            width: 80px;
            height: 80px;
            background: #e8f5e9;
            color: #4caf50;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin: 0 auto 1.5rem;
        }
        .data-box {
            background: rgba(141, 110, 99, 0.05);
            padding: 1.5rem;
            border-radius: 12px;
            margin: 2rem 0;
        }
        .row-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 0.95rem;
        }
        .row-item.highlight {
            border-top: 1px solid var(--border);
            padding-top: 12px;
            margin-top: 12px;
            font-weight: bold;
            font-size: 1.2rem;
            color: var(--destructive);
        }
    </style>
</head>
<body>

    <div class="success-wrapper">
        <div class="card success-card">
            <div class="icon-box">✓</div>
            <h2 style="color: var(--primary);">Berhasil!</h2>
            <p style="color: #888;">Stok produk telah diperbarui</p>

            <div class="data-box">
                <div class="row-item">
                    <span>Total Tagihan</span>
                    <span><?= formatRupiah($ringkasan['total']) ?></span>
                </div>
                <div class="row-item">
                    <span>Tunai</span>
                    <span><?= formatRupiah($ringkasan['bayar']) ?></span>
                </div>
                <div class="row-item highlight">
                    <span>Kembalian</span>
                    <span><?= formatRupiah($ringkasan['kembali']) ?></span>
                </div>
            </div>

            <a href="index.php" class="btn btn-primary btn-full" style="padding: 12px;">Kembali ke Kasir</a>
        </div>
    </div>

</body>
</html>