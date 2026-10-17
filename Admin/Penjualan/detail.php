<?php
session_start();

if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

include "../../koneksi.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$id_penjualan = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id_penjualan <= 0) {
    header("Location: index.php");
    exit;
}


/* =========================
   DATA TRANSAKSI
========================= */

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT
        p.id_penjualan,
        p.tanggal_penjualan,
        p.total_harga,
        pl.nama_pelanggan,
        pl.alamat,
        pl.no_telp
     FROM penjualan p
     LEFT JOIN pelanggan pl
        ON p.id_pelanggan = pl.id_pelanggan
     WHERE p.id_penjualan = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id_penjualan);
mysqli_stmt_execute($stmt);

$result_penjualan = mysqli_stmt_get_result($stmt);
$penjualan = mysqli_fetch_assoc($result_penjualan);

mysqli_stmt_close($stmt);


if (!$penjualan) {
    header("Location: index.php");
    exit;
}


/* =========================
   DETAIL PRODUK
========================= */

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT
        d.id_detail,
        d.id_produk,
        d.jumlah_produk,
        d.subtotal,
        pr.nama_produk,
        pr.harga
     FROM detailpenjualan d
     INNER JOIN produk pr
        ON d.id_produk = pr.id_produk
     WHERE d.id_penjualan = ?
     ORDER BY d.id_detail ASC"
);

mysqli_stmt_bind_param($stmt, "i", $id_penjualan);
mysqli_stmt_execute($stmt);

$detail_result = mysqli_stmt_get_result($stmt);

$detail_items = [];

while ($item = mysqli_fetch_assoc($detail_result)) {
    $detail_items[] = $item;
}

mysqli_stmt_close($stmt);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Detail Penjualan | KasirPro</title>

    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="../../assets/css/penjualan.css">

</head>

<body>

<aside class="admin-sidebar">

    <div class="admin-brand">

        <div class="brand-logo">🛒</div>

        <div class="brand-info">
            <strong>KasirPro</strong>
            <small>SISTEM KASIR</small>
        </div>

    </div>


    <div class="sidebar-title">
        MENU UTAMA
    </div>


    <nav class="admin-menu">

        <a href="../index.php" class="admin-menu-item">
            <span class="menu-icon">▦</span>
            <span class="menu-text">Dashboard</span>
        </a>

        <a href="../produk/index.php" class="admin-menu-item">
            <span class="menu-icon">▣</span>
            <span class="menu-text">Produk</span>
        </a>

        <a href="../pelanggan/index.php" class="admin-menu-item">
            <span class="menu-icon">♙</span>
            <span class="menu-text">Pelanggan</span>
        </a>

        <a href="index.php" class="admin-menu-item active">
            <span class="menu-icon">▤</span>
            <span class="menu-text">Penjualan</span>
        </a>

    </nav>


    <div class="sidebar-title system-title">
        SISTEM
    </div>


    <div class="sidebar-bottom">

        <a href="../logout.php" class="admin-menu-item logout-item">
            <span class="menu-icon">↪</span>
            <span class="menu-text">Keluar</span>
        </a>

    </div>

</aside>


<main class="admin-main">

    <header class="admin-topbar">

        <div>

            <span class="topbar-small">
                TRANSAKSI
            </span>

            <h2>
                Detail Penjualan
            </h2>

        </div>


        <div class="admin-profile">

            <div class="profile-avatar">
                <?= strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)); ?>
            </div>

            <div class="profile-text">
                <strong>
                    <?= htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>
                </strong>

                <span>
                    Administrator
                </span>
            </div>

        </div>

    </header>


    <section class="sale-detail-content">

        <div class="sale-detail-top">

            <a href="index.php" class="sale-detail-back">
                ← Kembali
            </a>

            <div class="sale-detail-actions">

                <a
                    href="hapus.php?id=<?= $penjualan['id_penjualan']; ?>"
                    class="sale-detail-delete"
                    onclick="return confirm('Yakin ingin menghapus transaksi ini? Stok produk akan dikembalikan.')"
                >
                    Hapus Transaksi
                </a>

            </div>

        </div>


        <div class="sale-detail-card">

            <div class="sale-detail-header">

                <div>

                    <span class="sale-detail-label">
                        DETAIL TRANSAKSI
                    </span>

                    <h1>
                        #<?= str_pad($penjualan['id_penjualan'], 4, '0', STR_PAD_LEFT); ?>
                    </h1>

                </div>


                <div class="sale-detail-date">

                    <span>
                        TANGGAL
                    </span>

                    <strong>
                        <?= date(
                            'd F Y',
                            strtotime($penjualan['tanggal_penjualan'])
                        ); ?>
                    </strong>

                </div>

            </div>


            <div class="sale-customer-info">

                <div class="sale-customer-info-icon">
                    ♙
                </div>

                <div>

                    <span>
                        PELANGGAN
                    </span>

                    <strong>
                        <?= htmlspecialchars(
                            $penjualan['nama_pelanggan'] ?? 'Pelanggan Umum'
                        ); ?>
                    </strong>

                    <?php if (!empty($penjualan['no_telp'])): ?>

                        <small>
                            <?= htmlspecialchars($penjualan['no_telp']); ?>
                        </small>

                    <?php endif; ?>

                </div>

            </div>


            <div class="sale-detail-table-wrapper">

                <table class="sale-detail-table">

                    <thead>

                        <tr>

                            <th>No</th>

                            <th>Produk</th>

                            <th>Harga</th>

                            <th>Jumlah</th>

                            <th>Subtotal</th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php
                    $no = 1;

                    foreach ($detail_items as $item):
                    ?>

                        <tr>

                            <td>
                                <?= $no++; ?>
                            </td>

                            <td>

                                <strong>
                                    <?= htmlspecialchars($item['nama_produk']); ?>
                                </strong>

                            </td>

                            <td>
                                Rp <?= number_format(
                                    $item['harga'],
                                    0,
                                    ',',
                                    '.'
                                ); ?>
                            </td>

                            <td>
                                <?= $item['jumlah_produk']; ?>
                            </td>

                            <td>

                                <strong>
                                    Rp <?= number_format(
                                        $item['subtotal'],
                                        0,
                                        ',',
                                        '.'
                                    ); ?>
                                </strong>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>


            <div class="sale-detail-total">

                <span>
                    Total Pembayaran
                </span>

                <strong>
                    Rp <?= number_format(
                        $penjualan['total_harga'],
                        0,
                        ',',
                        '.'
                    ); ?>
                </strong>

            </div>

        </div>

    </section>

</main>

</body>
</html>