<?php
require 'db.php';
require 'functions.php';

$id_edit = ""; $nama_edit = ""; $harga_edit = ""; $stok_edit = "";

// 1. LOGIKA: Simpan (Tambah atau Update)
if (isset($_POST['simpan'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];

    if (empty($id)) {
        // Mode Tambah Baru
        $stmt = $conn->prepare("INSERT INTO produk (nama, harga, stok) VALUES (?, ?, ?)");
        $stmt->bind_param("sdi", $nama, $harga, $stok);
    } else {
        // Mode Update/Edit
        $stmt = $conn->prepare("UPDATE produk SET nama=?, harga=?, stok=? WHERE id=?");
        $stmt->bind_param("sdii", $nama, $harga, $stok, $id);
    }
    $stmt->execute();
    header("Location: manage_products.php?pesan=disimpan");
    exit;
}

// 2. LOGIKA: Hapus Produk
if (isset($_GET['hapus'])) {
    $stmt = $conn->prepare("DELETE FROM produk WHERE id = ?");
    $stmt->bind_param("i", $_GET['hapus']);
    $stmt->execute();
    header("Location: manage_products.php?pesan=dihapus");
    exit;
}

// 3. LOGIKA: Ambil Data untuk Edit
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM produk WHERE id = ?");
    $stmt->bind_param("i", $_GET['edit']);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    if ($data) {
        $id_edit = $data['id'];
        $nama_edit = $data['nama'];
        $harga_edit = $data['harga'];
        $stok_edit = $data['stok'];
    }
}

// 4. Ambil Semua Data untuk Tabel
$produk_query = mysqli_query($conn, "SELECT * FROM produk ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyKasir - Kelola Produk</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <nav class="navbar">
        <div class="navbar-brand">☕🍽️ MyKasir.</div>
        <div class="navbar-nav">
            <a href="index.php">Dashboard Kasir</a>
            <a href="manage_products.php" class="active">Kelola Produk</a>
        </div>
    </nav>

    <div class="container">
        
        <div class="card banner-manage">
            <div class="banner-content">
                <span class="banner-icon"></span>
                <div>
                    <h2>🧋 Kelola Produk</h2>
                </div>
            </div>
        </div>

        <div class="manage-grid">
            
            <div class="card">
                <h3 class="card-title"><?= $id_edit ? "✎ Edit Produk" : "Tambah Produk" ?></h3>
                
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $id_edit ?>">
                    
                    <div class="form-group">
                        <label>Nama Produk</label>
                        <input type="text" name="nama" value="<?= htmlspecialchars($nama_edit) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Harga (Rp)</label>
                        <input type="number" name="harga" value="<?= $harga_edit ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Jumlah Stok</label>
                        <input type="number" name="stok" value="<?= $stok_edit ?>" required>
                    </div>

                    <button type="submit" name="simpan" class="btn btn-primary btn-full">
                        <?= $id_edit ? "Simpan Perubahan" : "Simpan Produk" ?>
                    </button>
                    
                    <?php if($id_edit): ?>
                        <a href="manage_products.php" class="btn-cancel">✕ Batal Edit</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="card">
                <h3 class="card-title">Daftar Produk</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 50px;">ID</th>
                                <th>Nama Produk</th>
                                <th>Harga</th>
                                <th style="text-align: center;">Stok</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($p = mysqli_fetch_assoc($produk_query)): ?>
                            <tr>
                                <td class="text-muted"><?= $p['id'] ?></td>
                                <td><strong><?= htmlspecialchars($p['nama']) ?></strong></td>
                                <td><?= formatRupiah($p['harga']) ?></td>
                                <td style="text-align: center;">
                                    <span class="stok-badge"><?= $p['stok'] ?> unit</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?edit=<?= $p['id'] ?>" class="btn-icon btn-edit" title="Edit">✎</a>
                                        <a href="javascript:void(0)" 
                                           onclick="konfirmasiHapus(<?= $p['id'] ?>, '<?= htmlspecialchars($p['nama']) ?>')" 
                                           class="btn-icon btn-delete" title="Hapus">🗑</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div> </div> <script>
    function konfirmasiHapus(id, nama) {
        const overlay = document.createElement('div');
        overlay.className = 'confirm-overlay';
        overlay.innerHTML = `
            <div class="card confirm-modal">
                <div class="confirm-icon">⚠️</div>
                <h3>Hapus Produk?</h3>
                <p>Anda yakin ingin menghapus <strong>${nama}</strong>? Tindakan ini tidak dapat dibatalkan.</p>
                <div class="confirm-actions">
                    <button onclick="closeModal()" class="btn btn-secondary" style="background: #ccc; color: #444;">Batal</button>
                    <a href="?hapus=${id}" class="btn btn-danger">Ya, Hapus</a>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);
    }

    function closeModal() {
        const overlay = document.querySelector('.confirm-overlay');
        if(overlay) overlay.remove();
    }

    // Menutup modal jika user klik di luar area kartu modal
    window.onclick = function(event) {
        const overlay = document.querySelector('.confirm-overlay');
        if (event.target == overlay) {
            closeModal();
        }
    }
    </script>

</body>
</html>