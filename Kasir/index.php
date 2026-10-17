<?php

session_start();

if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'kasir') {
    header("Location: ../login.php");
    exit;
}

include "../koneksi.php";

date_default_timezone_set('Asia/Jakarta');

$namaKasir = $_SESSION['nama'] ?? 'Kasir';


/* =========================
   HALAMAN AKTIF
========================= */

$halamanAktif = 'dashboard';


/* =========================
   TRANSAKSI HARI INI
========================= */

$qTransaksiHariIni = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total
    FROM penjualan
    WHERE DATE(tanggal_penjualan) = CURDATE()
");

$dataTransaksiHariIni = mysqli_fetch_assoc($qTransaksiHariIni);
$transaksiHariIni = (int) $dataTransaksiHariIni['total'];


/* =========================
   PENJUALAN HARI INI
========================= */

$qPenjualanHariIni = mysqli_query($koneksi, "
    SELECT COALESCE(SUM(total_harga), 0) AS total
    FROM penjualan
    WHERE DATE(tanggal_penjualan) = CURDATE()
");

$dataPenjualanHariIni = mysqli_fetch_assoc($qPenjualanHariIni);
$penjualanHariIni = (float) $dataPenjualanHariIni['total'];


/* =========================
   PRODUK
========================= */

$qProduk = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total
    FROM produk
");

$dataProduk = mysqli_fetch_assoc($qProduk);
$totalProduk = (int) $dataProduk['total'];


/* =========================
   PELANGGAN
========================= */

$qPelanggan = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total
    FROM pelanggan
");

$dataPelanggan = mysqli_fetch_assoc($qPelanggan);
$totalPelanggan = (int) $dataPelanggan['total'];


/* =========================
   TRANSAKSI TERBARU
========================= */

$qTransaksiTerbaru = mysqli_query($koneksi, "
    SELECT
        p.id_penjualan,
        p.tanggal_penjualan,
        p.total_harga,
        COALESCE(pl.nama_pelanggan, 'Umum') AS nama_pelanggan
    FROM penjualan p
    LEFT JOIN pelanggan pl
        ON p.id_pelanggan = pl.id_pelanggan
    ORDER BY p.tanggal_penjualan DESC
    LIMIT 5
");


/* =========================
   FORMAT RUPIAH
========================= */

function rupiah($angka)
{
    return 'Rp ' . number_format(
        (float) $angka,
        0,
        ',',
        '.'
    );
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

    <title>Kasir | Bottle.Co</title>

    <link
        rel="stylesheet"
        href="/PENJUALAN/assets/css/bootstrap.min.css"
    >

    <link
        rel="stylesheet"
        href="/PENJUALAN/assets/css/style.css?v=20"
    >

    <link
        rel="stylesheet"
        href="/PENJUALAN/assets/css/kasir.css?v=13"
    >

</head>


<body class="kasir-dashboard">


<!-- =====================================================
     SIDEBAR
====================================================== -->

<aside class="kasir-sidebar">

    <!-- BRAND -->

    <div class="sidebar-brand">

        <div class="sidebar-logo">
            K
        </div>

        <div>
            <strong>Bottle.Co</strong>
            <span>SISTEM KASIR</span>
        </div>

    </div>


    <!-- MENU -->

    <div class="sidebar-label">
        MENU UTAMA
    </div>


    <nav class="sidebar-menu">

        <!-- DASHBOARD -->

        <a
            href="index.php"
            class="sidebar-link <?= $halamanAktif === 'dashboard' ? 'active' : ''; ?>"
        >

            <span class="sidebar-icon">
                ⌂
            </span>

            <span>
                Dashboard
            </span>

        </a>


        <!-- TRANSAKSI -->

        <a
            href="transaksi/index.php"
            class="sidebar-link transaction-link <?= $halamanAktif === 'transaksi' ? 'active' : ''; ?>"
        >

            <span class="sidebar-icon">
                🛒
            </span>

            <span>
                Transaksi
            </span>

        </a>


        <!-- PRODUK -->

        <a
            href="produk/index.php"
            class="sidebar-link <?= $halamanAktif === 'produk' ? 'active' : ''; ?>"
        >

            <span class="sidebar-icon">
                ▣
            </span>

            <span>
                Produk
            </span>

        </a>


        <!-- PELANGGAN -->

        <a
            href="pelanggan/index.php"
            class="sidebar-link <?= $halamanAktif === 'pelanggan' ? 'active' : ''; ?>"
        >

            <span class="sidebar-icon">
                ♙
            </span>

            <span>
                Pelanggan
            </span>

        </a>


        <!-- RIWAYAT -->

        <a
            href="riwayat/index.php"
            class="sidebar-link <?= $halamanAktif === 'riwayat' ? 'active' : ''; ?>"
        >

            <span class="sidebar-icon">
                ▤
            </span>

            <span>
                Riwayat Transaksi
            </span>

        </a>

    </nav>


    <!-- LOGOUT -->

    <div class="sidebar-bottom">

        <a
            href="../logout.php"
            class="sidebar-logout"
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
     MAIN
====================================================== -->

<main class="kasir-main">


    <!-- =================================================
         HEADER
    ================================================== -->

    <header class="kasir-topbar">

        <!-- LEFT -->

        <div class="topbar-heading">

            <span class="topbar-label">
                OPERASIONAL
            </span>

            <h1>
                Dashboard Kasir
            </h1>

        </div>


        <!-- RIGHT -->

        <div class="topbar-right">

            <!-- DATE -->

            <div class="topbar-date">

                <span>
                    <?= date('d F Y'); ?>
                </span>

                <strong>
                    <?= date('H:i'); ?> WIB
                </strong>

            </div>


            <!-- PROFILE -->

            <div class="topbar-profile">

                <div class="topbar-avatar">

                    <?= strtoupper(
                        substr($namaKasir, 0, 1)
                    ); ?>

                </div>


                <div class="topbar-profile-info">

                    <strong>
                        <?= htmlspecialchars($namaKasir); ?>
                    </strong>

                    <span>
                        Kasir
                    </span>

                </div>

            </div>

        </div>

    </header>



    <!-- =================================================
         CONTENT
    ================================================== -->

    <div class="kasir-content">


        <!-- =================================================
             WELCOME
        ================================================== -->

        <section class="kasir-welcome">

            <div>

                <span class="welcome-label">
                    SELAMAT DATANG
                </span>

                <h2>
                    Halo, <?= htmlspecialchars($namaKasir); ?> 👋
                </h2>

                <p>
                    Siap melayani transaksi hari ini?
                </p>

            </div>


            <a
                href="transaksi/index.php"
                class="main-transaction-button"
            >

                <span class="button-icon">
                    +
                </span>


                <span>

                    <small>
                        MULAI
                    </small>

                    Transaksi Penjualan

                </span>


                <strong>
                    →
                </strong>

            </a>

        </section>



        <!-- =================================================
             STATISTIK
        ================================================== -->

        <section class="kasir-stats">


            <!-- TRANSAKSI -->

            <div class="stat-card">

                <div class="stat-icon blue">
                    🧾
                </div>

                <div class="stat-content">

                    <span>
                        TRANSAKSI HARI INI
                    </span>

                    <strong>
                        <?= number_format($transaksiHariIni); ?>
                    </strong>

                </div>

            </div>


            <!-- PENJUALAN -->

            <div class="stat-card">

                <div class="stat-icon green">
                    Rp
                </div>

                <div class="stat-content">

                    <span>
                        PENJUALAN HARI INI
                    </span>

                    <strong>
                        <?= rupiah($penjualanHariIni); ?>
                    </strong>

                </div>

            </div>


            <!-- PRODUK -->

            <div class="stat-card">

                <div class="stat-icon orange">
                    📦
                </div>

                <div class="stat-content">

                    <span>
                        PRODUK
                    </span>

                    <strong>
                        <?= number_format($totalProduk); ?>
                    </strong>

                </div>

            </div>


            <!-- PELANGGAN -->

            <div class="stat-card">

                <div class="stat-icon purple">
                    👤
                </div>

                <div class="stat-content">

                    <span>
                        PELANGGAN
                    </span>

                    <strong>
                        <?= number_format($totalPelanggan); ?>
                    </strong>

                </div>

            </div>

        </section>



        <!-- =================================================
             BODY
        ================================================== -->

        <div class="kasir-body-grid">


            <!-- =================================================
                 TRANSAKSI TERBARU
            ================================================== -->

            <section class="recent-section">

                <div class="section-header">

                    <div>

                        <span>
                            AKTIVITAS
                        </span>

                        <h2>
                            Transaksi Terbaru
                        </h2>

                    </div>


                    <a href="riwayat/index.php">
                        Lihat semua →
                    </a>

                </div>


                <div class="transaction-table">


                    <!-- HEADER TABLE -->

                    <div class="transaction-table-head">

                        <span>
                            TRANSAKSI
                        </span>

                        <span>
                            PELANGGAN
                        </span>

                        <span>
                            WAKTU
                        </span>

                        <span>
                            TOTAL
                        </span>

                    </div>


                    <?php if (mysqli_num_rows($qTransaksiTerbaru) > 0): ?>


                        <?php while ($transaksi = mysqli_fetch_assoc($qTransaksiTerbaru)): ?>

                            <div class="transaction-row">


                                <div class="transaction-number">

                                    <div class="transaction-mini-icon">
                                        Rp
                                    </div>

                                    <strong>
                                        #<?= (int) $transaksi['id_penjualan']; ?>
                                    </strong>

                                </div>


                                <div class="customer-name">

                                    <?= htmlspecialchars(
                                        $transaksi['nama_pelanggan']
                                    ); ?>

                                </div>


                                <div class="transaction-time">

                                    <?= date(
                                        'd/m/Y H:i',
                                        strtotime(
                                            $transaksi['tanggal_penjualan']
                                        )
                                    ); ?>

                                </div>


                                <div class="transaction-price">

                                    <?= rupiah(
                                        $transaksi['total_harga']
                                    ); ?>

                                </div>

                            </div>

                        <?php endwhile; ?>


                    <?php else: ?>


                        <div class="transaction-empty">

                            <div>
                                🧾
                            </div>

                            <strong>
                                Belum ada transaksi
                            </strong>

                            <span>
                                Transaksi yang dilakukan akan muncul di sini.
                            </span>

                        </div>


                    <?php endif; ?>


                </div>

            </section>



            <!-- =================================================
                 MENU KASIR
            ================================================== -->

            <section class="quick-section">

                <div class="section-header">

                    <div>

                        <span>
                            AKSES CEPAT
                        </span>

                        <h2>
                            Menu Kasir
                        </h2>

                    </div>

                </div>


                <div class="quick-menu">


                    <!-- PRODUK -->

                    <a
                        href="produk/index.php"
                        class="quick-card"
                    >

                        <div class="quick-icon">
                            📦
                        </div>

                        <div>

                            <strong>
                                Produk
                            </strong>

                            <span>
                                Lihat data & stok
                            </span>

                        </div>

                        <b>
                            →
                        </b>

                    </a>


                    <!-- PELANGGAN -->

                    <a
                        href="pelanggan/index.php"
                        class="quick-card"
                    >

                        <div class="quick-icon">
                            👤
                        </div>

                        <div>

                            <strong>
                                Pelanggan
                            </strong>

                            <span>
                                Data pelanggan
                            </span>

                        </div>

                        <b>
                            →
                        </b>

                    </a>


                    <!-- RIWAYAT -->

                    <a
                        href="riwayat/index.php"
                        class="quick-card"
                    >

                        <div class="quick-icon">
                            🧾
                        </div>

                        <div>

                            <strong>
                                Riwayat
                            </strong>

                            <span>
                                Transaksi sebelumnya
                            </span>

                        </div>

                        <b>
                            →
                        </b>

                    </a>


                </div>


                <!-- CTA KASIR -->

                <a
                    href="transaksi/index.php"
                    class="quick-transaction"
                >

                    <div class="quick-transaction-icon">
                        🛒
                    </div>

                    <div class="quick-transaction-text">

                        <span>
                            SIAP MELAYANI?
                        </span>

                        <strong>
                            Buka Kasir
                        </strong>

                    </div>

                    <b>
                        →
                    </b>

                </a>

            </section>


        </div>


    </div>

</main>



<!-- =====================================================
     FOOTER
====================================================== -->

<footer class="kasir-footer">

    Bottle.Co &copy; <?= date('Y'); ?>

</footer>



<script src="/PENJUALAN/assets/js/bootstrap.bundle.min.js"></script>

</body>

</html>