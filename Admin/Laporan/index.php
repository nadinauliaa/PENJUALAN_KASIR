<?php

session_start();

if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

include "../../koneksi.php";

date_default_timezone_set('Asia/Jakarta');

$namaAdmin = $_SESSION['nama']
    ?? $_SESSION['username']
    ?? 'Administrator';


/* =====================================================
   FILTER TANGGAL
===================================================== */

$tanggal_awal = $_GET['tanggal_awal'] ?? date('Y-m-01');
$tanggal_akhir = $_GET['tanggal_akhir'] ?? date('Y-m-d');


/* =====================================================
   VALIDASI TANGGAL
===================================================== */

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_awal)) {
    $tanggal_awal = date('Y-m-01');
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_akhir)) {
    $tanggal_akhir = date('Y-m-d');
}


/* =====================================================
   RINGKASAN LAPORAN
===================================================== */

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT
        COUNT(*) AS total_transaksi,
        COALESCE(SUM(total_harga), 0) AS total_omzet
     FROM penjualan
     WHERE tanggal_penjualan >= ?
       AND tanggal_penjualan < DATE_ADD(?, INTERVAL 1 DAY)"
);

mysqli_stmt_bind_param($stmt, "ss", $tanggal_awal, $tanggal_akhir);
mysqli_stmt_execute($stmt);
$summary = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$totalTransaksi = (int) ($summary['total_transaksi'] ?? 0);
$totalOmzet = (float) ($summary['total_omzet'] ?? 0);


/* =====================================================
   TOTAL PRODUK TERJUAL
===================================================== */

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT
        COALESCE(SUM(dp.jumlah_produk), 0) AS total_produk
     FROM penjualan p
     INNER JOIN detailpenjualan dp
        ON p.id_penjualan = dp.id_penjualan
     WHERE p.tanggal_penjualan >= ?
       AND p.tanggal_penjualan < DATE_ADD(?, INTERVAL 1 DAY)"
);

mysqli_stmt_bind_param($stmt, "ss", $tanggal_awal, $tanggal_akhir);
mysqli_stmt_execute($stmt);
$totalProduk = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total_produk'] ?? 0);


/* =====================================================
   DATA PENJUALAN
===================================================== */

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT
        p.id_penjualan,
        p.tanggal_penjualan,
        p.total_harga,
        pl.nama_pelanggan
     FROM penjualan p
     LEFT JOIN pelanggan pl
        ON p.id_pelanggan = pl.id_pelanggan
     WHERE p.tanggal_penjualan >= ?
       AND p.tanggal_penjualan < DATE_ADD(?, INTERVAL 1 DAY)
     ORDER BY p.tanggal_penjualan DESC"
);

mysqli_stmt_bind_param($stmt, "ss", $tanggal_awal, $tanggal_akhir);
mysqli_stmt_execute($stmt);
$penjualanResult = mysqli_stmt_get_result($stmt);


/* =====================================================
   JUMLAH ITEM PER TRANSAKSI
===================================================== */

$itemPerTransaksi = [];
$stmtItem = mysqli_prepare($koneksi, "SELECT id_penjualan, SUM(jumlah_produk) AS total_item FROM detailpenjualan GROUP BY id_penjualan");
mysqli_stmt_execute($stmtItem);
$itemResult = mysqli_stmt_get_result($stmtItem);

while ($item = mysqli_fetch_assoc($itemResult)) {
    $itemPerTransaksi[(int) $item['id_penjualan']] = (int) $item['total_item'];
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Laporan | Bottle.Co</title>


    <!-- BOOTSTRAP -->

    <link
        rel="stylesheet"
        href="../../assets/css/bootstrap.min.css"
    >


    <!-- ADMIN CSS -->

    <link
        rel="stylesheet"
        href="../../assets/css/admin.css?v=11"
    >


    <!-- LAPORAN CSS -->

    <link
        rel="stylesheet"
        href="../../assets/css/laporan.css?v=11"
    >

</head>


<body>


<!-- =====================================================
     SIDEBAR
===================================================== -->

<aside class="admin-sidebar">

    <!-- BRAND -->

    <div class="admin-brand">

        <div class="brand-logo">
            🛒
        </div>

        <div class="brand-text">

            <strong>
                Bottle.Co
            </strong>

            <span>
                ADMINISTRATOR
            </span>

        </div>

    </div>


    <!-- SIDEBAR CONTENT -->

    <div class="sidebar-content">

        <!-- MENU UTAMA -->

        <div class="menu-label">
            MENU UTAMA
        </div>

        <nav class="admin-menu">

            <a
                href="../index.php"
                class="admin-menu-item"
            >
                <span class="menu-icon">
                    ▦
                </span>
                <span>
                    Dashboard
                </span>
            </a>

            <a
                href="../produk/index.php"
                class="admin-menu-item"
            >
                <span class="menu-icon">
                    ▣
                </span>
                <span>
                    Produk
                </span>
            </a>

            <a
                href="../pelanggan/index.php"
                class="admin-menu-item"
            >
                <span class="menu-icon">
                    ♟
                </span>
                <span>
                    Pelanggan
                </span>
            </a>

            <a
                href="../penjualan/index.php"
                class="admin-menu-item"
            >
                <span class="menu-icon">
                    🛒
                </span>
                <span>
                    Penjualan
                </span>
            </a>

            <a
                href="../detail-penjualan/index.php"
                class="admin-menu-item"
            >
                <span class="menu-icon">
                    ▤
                </span>
                <span>
                    Detail Penjualan
                </span>
            </a>

        </nav>


        <!-- SISTEM -->

        <div class="menu-label menu-label-system">
            SISTEM
        </div>

        <nav class="admin-menu">

            <a
                href="../pengguna/index.php"
                class="admin-menu-item"
            >
                <span class="menu-icon">
                    ♙
                </span>
                <span>
                    Pengguna
                </span>
            </a>

            <a
                href="index.php"
                class="admin-menu-item active"
            >
                <span class="menu-icon">
                    ▥
                </span>
                <span>
                    Laporan
                </span>
            </a>

        </nav>

    </div>


    <!-- LOGOUT -->

    <div class="sidebar-footer">

        <a
            href="../../logout.php"
            class="logout-link"
        >
            <span>
                ↪
            </span>
            <span>
                Logout
            </span>
        </a>

    </div>

</aside>



<!-- =====================================================
     WRAPPER (Sama persis dengan halaman penjualan)
===================================================== -->

<div class="admin-wrapper">


    <!-- =================================================
         TOPBAR
    ================================================== -->

    <header class="admin-topbar">

        <div class="topbar-left">

            <span class="breadcrumb-small">
                SISTEM INFORMASI PENJUALAN
            </span>

            <h1>
                Laporan
            </h1>

        </div>


        <!-- PROFILE -->

        <div class="admin-profile">

            <div class="profile-avatar">

                <?= strtoupper(
                    substr($namaAdmin, 0, 1)
                ); ?>

            </div>

            <div class="profile-text">

                <strong>
                    <?= htmlspecialchars($namaAdmin); ?>
                </strong>

                <span>
                    Administrator
                </span>

            </div>

        </div>

    </header>



    <!-- =================================================
         MAIN
    ================================================== -->

    <main class="admin-main">

        <div class="sales-content">


            <!-- =================================================
                 INTRO
            ================================================= -->

            <section class="sales-intro">

                <div class="sales-intro-left">

                    <div class="sales-intro-icon">
                        ▥
                    </div>

                    <div>

                        <span class="sales-intro-label">
                            LAPORAN PENJUALAN
                        </span>

                        <h2>
                            Laporan Penjualan
                        </h2>

                        <p>
                            Lihat dan cetak ringkasan transaksi berdasarkan periode tertentu.
                        </p>

                    </div>

                </div>

                <button
                    type="button"
                    class="btn-add-sale"
                    onclick="window.print();"
                    style="border: none; cursor: pointer;"
                >
                    <span>🖨</span>
                    Cetak Laporan
                </button>

            </section>



            <!-- =================================================
                 FILTER LAPORAN
            ================================================= -->

            <section class="sales-intro" style="margin-top: 20px; align-items: center;">

                <div class="sales-intro-left">
                    <div>
                        <span class="sales-intro-label">
                            FILTER
                        </span>
                        <h2 style="font-size: 16px;">
                            Pilih Periode
                        </h2>
                    </div>
                </div>

                <form method="GET" style="display: flex; align-items: flex-end; gap: 12px; margin: 0;">

                    <div>
                        <label style="display: block; margin-bottom: 4px; color: #64748b; font-size: 12px; font-weight: 600;">Tanggal Awal</label>
                        <input type="date" name="tanggal_awal" value="<?= htmlspecialchars($tanggal_awal); ?>" style="height: 42px; padding: 0 12px; border: 1px solid #e7ebf0; border-radius: 8px; font-size: 14px; outline: none; color: #172033;">
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 4px; color: #64748b; font-size: 12px; font-weight: 600;">Tanggal Akhir</label>
                        <input type="date" name="tanggal_akhir" value="<?= htmlspecialchars($tanggal_akhir); ?>" style="height: 42px; padding: 0 12px; border: 1px solid #e7ebf0; border-radius: 8px; font-size: 14px; outline: none; color: #172033;">
                    </div>

                    <button type="submit" class="btn-add-sale" style="border: none; cursor: pointer;">
                        Tampilkan
                    </button>

                    <a href="index.php" style="height: 42px; padding: 0 16px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; border: 1px solid #e7ebf0; background: #f8fafc; color: #64748b; font-size: 14px; font-weight: 600; text-decoration: none;">
                        Reset
                    </a>

                </form>

            </section>



            <!-- =================================================
                 SUMMARY
            ================================================= -->

            <section class="sales-summary">

                <!-- TOTAL TRANSAKSI -->
                <div class="sales-summary-card">
                    <div class="sales-summary-icon">
                        ▤
                    </div>
                    <div>
                        <span>
                            TOTAL TRANSAKSI
                        </span>
                        <strong>
                            <?= number_format($totalTransaksi, 0, ',', '.'); ?>
                        </strong>
                    </div>
                </div>

                <!-- TOTAL OMZET -->
                <div class="sales-summary-card">
                    <div class="sales-summary-icon">
                        Rp
                    </div>
                    <div>
                        <span>
                            TOTAL OMZET
                        </span>
                        <strong class="income-value">
                            Rp <?= number_format($totalOmzet, 0, ',', '.'); ?>
                        </strong>
                    </div>
                </div>

                <!-- PRODUK TERJUAL -->
                <div class="sales-summary-card">
                    <div class="sales-summary-icon">
                        ▣
                    </div>
                    <div>
                        <span>
                            PRODUK TERJUAL
                        </span>
                        <strong>
                            <?= number_format($totalProduk, 0, ',', '.'); ?>
                        </strong>
                    </div>
                </div>

            </section>



            <!-- =================================================
                 DATA TRANSAKSI (TABEL)
            ================================================= -->

            <section class="sales-section">

                <div class="sales-box">

                    <!-- HEADER -->
                    <div class="sales-box-header">
                        <div>
                            <span class="sales-table-label">
                                DATA TRANSAKSI
                            </span>
                            <h2>
                                Riwayat Penjualan
                            </h2>
                        </div>

                        <span class="sales-table-count">
                            <?= date('d M Y', strtotime($tanggal_awal)); ?> — <?= date('d M Y', strtotime($tanggal_akhir)); ?>
                        </span>
                    </div>

                    <!-- TABLE -->
                    <div class="sales-table-responsive">

                        <?php if (mysqli_num_rows($penjualanResult) > 0): ?>

                            <table class="sales-table">

                                <thead>
                                    <tr>
                                        <th class="sales-col-no">#</th>
                                        <th>ID TRANSAKSI</th>
                                        <th>TANGGAL</th>
                                        <th>PELANGGAN</th>
                                        <th>ITEM</th>
                                        <th>TOTAL</th>
                                        <th class="sales-col-action">DETAIL</th>
                                    </tr>
                                </thead>

                                <tbody>

                                <?php
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($penjualanResult)):
                                    $idPenjualan = (int) $row['id_penjualan'];
                                    $namaPelanggan = $row['nama_pelanggan'] ?? '';
                                    $totalItem = $itemPerTransaksi[$idPenjualan] ?? 0;
                                ?>

                                    <tr>
                                        <!-- NO -->
                                        <td class="sales-number-cell">
                                            <?= $no++; ?>
                                        </td>

                                        <!-- ID TRANSAKSI -->
                                        <td>
                                            <div class="transaction-cell">
                                                <div class="transaction-icon">
                                                    🧾
                                                </div>
                                                <div>
                                                    <strong>
                                                        #TRX<?= str_pad($idPenjualan, 4, '0', STR_PAD_LEFT); ?>
                                                    </strong>
                                                    <span>
                                                        Penjualan
                                                    </span>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- TANGGAL -->
                                        <td>
                                            <span class="date-cell">
                                                <strong><?= date('d/m/Y', strtotime($row['tanggal_penjualan'])); ?></strong><br>
                                                <span style="color: #64748b; font-size: 12px;"><?= date('H:i', strtotime($row['tanggal_penjualan'])); ?></span>
                                            </span>
                                        </td>

                                        <!-- PELANGGAN -->
                                        <td>
                                            <div class="sales-customer">
                                                <div class="sales-customer-avatar">
                                                    <?= !empty($namaPelanggan) ? strtoupper(substr($namaPelanggan, 0, 1)) : 'U'; ?>
                                                </div>
                                                <span>
                                                    <?= !empty($namaPelanggan) ? htmlspecialchars($namaPelanggan) : 'Umum'; ?>
                                                </span>
                                            </div>
                                        </td>

                                        <!-- ITEM -->
                                        <td>
                                            <span style="color: #64748b; font-weight: 600;">
                                                <?= number_format($totalItem, 0, ',', '.'); ?> item
                                            </span>
                                        </td>

                                        <!-- TOTAL -->
                                        <td>
                                            <strong class="sale-total">
                                                Rp <?= number_format((float)$row['total_harga'], 0, ',', '.'); ?>
                                            </strong>
                                        </td>

                                        <!-- DETAIL -->
                                        <td>
                                            <div class="sales-action-buttons">
                                                <a
                                                    href="../detail-penjualan/detail.php?id=<?= $idPenjualan; ?>"
                                                    class="sales-btn-action sales-btn-view"
                                                    title="Lihat Detail"
                                                    style="width: auto; padding: 0 12px; border-radius: 6px;"
                                                >
                                                    Lihat
                                                </a>
                                            </div>
                                        </td>
                                    </tr>

                                <?php endwhile; ?>

                                </tbody>

                            </table>

                        <?php else: ?>

                            <!-- EMPTY -->
                            <div class="sales-empty">
                                <div class="sales-empty-icon">
                                    🧾
                                </div>
                                <h3>
                                    Tidak ada transaksi
                                </h3>
                                <p>
                                    Tidak ditemukan transaksi pada periode yang dipilih.
                                </p>
                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            </section>

        </div>

    </main>

</div>


</body>
</html>