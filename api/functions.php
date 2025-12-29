<?php
function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

function hitungTotal($keranjang) {
    $total = 0;
    foreach ($keranjang as $item) {
        $total += $item['harga'] * $item['jumlah'];
    }
    return $total;
}
?>