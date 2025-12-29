<?php
session_start();
require 'db.php';
require 'functions.php';

// Inisialisasi keranjang belanja jika belum ada
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

// 1. LOGIKA: Tambah ke Keranjang
if (isset($_POST['tambah'])) {
    $id = $_POST['id'];
    $jumlah = (int)$_POST['jumlah'];
    
    // Ambil detail produk dari database
    $stmt = $conn->prepare("SELECT * FROM produk WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $p = $stmt->get_result()->fetch_assoc();

    if ($p && $jumlah > 0 && $p['stok'] >= $jumlah) {
        // Jika produk sudah ada di keranjang, tambahkan jumlahnya
        if (isset($_SESSION['keranjang'][$id])) {
            $_SESSION['keranjang'][$id]['jumlah'] += $jumlah;
        } else {
            // Jika belum ada, buat entri baru
            $_SESSION['keranjang'][$id] = [
                'nama' => $p['nama'],
                'harga' => $p['harga'],
                'jumlah' => $jumlah
            ];
        }
    }
}

// 2. LOGIKA: Hapus Item dari Keranjang
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    unset($_SESSION['keranjang'][$id_hapus]);
    header("Location: index.php");
    exit;
}

// 3. LOGIKA: Pencarian Produk
$search = $_GET['cari'] ?? '';
$query = "SELECT * FROM produk WHERE nama LIKE ? AND stok > 0";

$stmt = $conn->prepare($query);

// Tambahkan pengecekan ini:
if ($stmt === false) {
    // Ini akan memberi tahu kita jika tabel 'produk' benar-benar belum terbaca
    die("Gagal menyiapkan query: " . $conn->error); 
}

$search_param = "%$search%";
$stmt->bind_param("s", $search_param);
$stmt->execute();
$produk_db = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyKasir - Dashboard Kasir</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <nav class="navbar">
        <div class="navbar-brand">☕🍽️ MyKasir.</div>
        <div class="navbar-nav">
            <a href="index.php" class="active">Dashboard Kasir</a>
            <a href="/api/manage_products.php" class="btn">Kelola Produk</a>
        </div>
    </nav>

    <div class="container">
        
        <div class="card">
            <form method="GET" style="display: flex; gap: 10px;">
                <input type="text" name="cari" placeholder="Cari nama produk..." value="<?= htmlspecialchars($search) ?>" style="flex: 1; margin-bottom: 0;">
                <button type="submit" class="btn btn-primary">Cari Produk</button>
            </form>
        </div>

        <div class="pos-grid">
            
            <div class="card">
                <h3 style="margin-bottom: 1.5rem;">Daftar Produk</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th style="width: 80px;">Qty</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($produk_db->num_rows > 0): ?>
                            <?php while($p = $produk_db->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($p['nama']) ?></strong></td>
                                <td><?= formatRupiah($p['harga']) ?></td>
                                <td><span class="stok-badge"><?= $p['stok'] ?></span></td>
                                <td>
                                    <form method="POST" id="form-add-<?= $p['id'] ?>">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <input type="number" name="jumlah" value="1" min="1" max="<?= $p['stok'] ?>" style="margin-bottom: 0; padding: 5px;">
                                    </form>
                                </td>
                                <td style="text-align: center;">
                                    <button type="submit" name="tambah" form="form-add-<?= $p['id'] ?>" class="btn btn-primary">Tambah</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 20px; color: #999;">Produk tidak ditemukan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h3 style="margin-bottom: 1.5rem;">Keranjang Belanja</h3>
                
                <?php if (empty($_SESSION['keranjang'])): ?>
                    <div style="text-align: center; padding: 30px 0; color: #999;">
                        <p>Keranjang masih kosong</p>
                    </div>
                <?php else: ?>
                    <div style="max-height: 300px; overflow-y: auto; margin-bottom: 20px;">
                        <?php foreach ($_SESSION['keranjang'] as $id => $item): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px dashed var(--border);">
                            <div>
                                <div style="font-weight: 600;"><?= htmlspecialchars($item['nama']) ?></div>
                                <div style="font-size: 0.85rem; color: #666;"><?= $item['jumlah'] ?> x <?= formatRupiah($item['harga']) ?></div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="font-weight: 700;"><?= formatRupiah($item['harga'] * $item['jumlah']) ?></span>
                                <a href="?hapus=<?= $id ?>" style="color: var(--destructive); text-decoration: none; font-weight: bold;">✕</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="border-top: 2px solid var(--primary); padding-top: 15px;">
                        <?php $total_tagihan = hitungTotal($_SESSION['keranjang']); ?>
                        <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.3rem; margin-bottom: 20px;">
                            <span>Total</span>
                            <span style="color: var(--primary);"><?= formatRupiah($total_tagihan) ?></span>
                        </div>

                        <form action="proses_pembayaran.php" method="POST">
                            <div class="form-group">
                                <label>Uang Tunai (Rp)</label>
                                <input type="number" name="nominal_bayar" placeholder="Masukkan jumlah uang..." required min="<?= $total_tagihan ?>" style="font-size: 1.1rem; padding: 12px;">
                            </div>
                            <button type="submit" class="btn btn-primary btn-full" style="padding: 15px; font-size: 1.1rem;">
                                Konfirmasi & Bayar
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

        </div> </div> </body>
</html>