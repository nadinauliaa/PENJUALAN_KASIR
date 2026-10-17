<?php
include "koneksi.php";

date_default_timezone_set('Asia/Jakarta');

/* =========================
   FUNGSI FORMAT RUPIAH
========================= */
function rupiah($angka)
{
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

/* =========================
   DATA RINGKASAN
========================= */

// Jumlah produk
$qProduk = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total
    FROM produk
");
$totalProduk = (int) mysqli_fetch_assoc($qProduk)['total'];

// Jumlah pelanggan
$qPelanggan = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total
    FROM pelanggan
");
$totalPelanggan = (int) mysqli_fetch_assoc($qPelanggan)['total'];

// Jumlah transaksi
$qTransaksi = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total
    FROM penjualan
");
$totalTransaksi = (int) mysqli_fetch_assoc($qTransaksi)['total'];

// Total pendapatan
$qPendapatan = mysqli_query($koneksi, "
    SELECT COALESCE(SUM(total_harga), 0) AS total
    FROM penjualan
");
$totalPendapatan = (float) mysqli_fetch_assoc($qPendapatan)['total'];

// Total produk terjual
$qTerjual = mysqli_query($koneksi, "
    SELECT COALESCE(SUM(jumlah_produk), 0) AS total
    FROM detailpenjualan
");
$totalTerjual = (int) mysqli_fetch_assoc($qTerjual)['total'];

/* =========================
   PRODUK TERLARIS
========================= */

$qProdukTerlaris = mysqli_query($koneksi, "
    SELECT
        p.id_produk,
        p.nama_produk,
        p.harga,
        p.stok,
        COALESCE(SUM(dp.jumlah_produk), 0) AS jumlah_terjual
    FROM produk p
    LEFT JOIN detailpenjualan dp
        ON p.id_produk = dp.id_produk
    GROUP BY
        p.id_produk,
        p.nama_produk,
        p.harga,
        p.stok
    HAVING jumlah_terjual > 0
    ORDER BY jumlah_terjual DESC, p.nama_produk ASC
    LIMIT 4
");

$produkTerlaris = [];

while ($row = mysqli_fetch_assoc($qProdukTerlaris)) {
    $produkTerlaris[] = $row;
}

$maxTerjual = 0;

foreach ($produkTerlaris as $produk) {
    if ((int)$produk['jumlah_terjual'] > $maxTerjual) {
        $maxTerjual = (int)$produk['jumlah_terjual'];
    }
}

/* =========================
   TRANSAKSI TERBARU
========================= */

$qTransaksiTerbaru = mysqli_query($koneksi, "
    SELECT
        pj.id_penjualan,
        pj.tanggal_penjualan,
        pj.total_harga,
        COALESCE(pl.nama_pelanggan, 'Umum') AS nama_pelanggan
    FROM penjualan pj
    LEFT JOIN pelanggan pl
        ON pj.id_pelanggan = pl.id_pelanggan
    ORDER BY pj.tanggal_penjualan DESC
    LIMIT 4
");

$transaksiTerbaru = [];

while ($row = mysqli_fetch_assoc($qTransaksiTerbaru)) {
    $transaksiTerbaru[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Bottle.Co | Sistem Kasir</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">

    <!-- CSS utama -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- CSS halaman utama -->
    <link rel="stylesheet" href="assets/css/dashboard.css?v=10">
</head>

<body class="home-page">

    <!-- =========================
         NAVBAR
    ========================== -->

    <header class="home-navbar">

        <div class="home-nav-container">

            <a href="index.php" class="home-brand">
                <div class="home-brand-icon">
                    B
                </div>

                <div>
                    <strong>Bottle.Co</strong>
                    <span>Smart Cashier System</span>
                </div>
            </a>


            <nav class="home-nav-menu">

                <a href="#beranda" class="active">
                    Beranda
                </a>

                <a href="#produk">
                    Produk
                </a>

                <a href="#aktivitas">
                    Aktivitas
                </a>

                <a href="#tentang">
                    Tentang
                </a>

            </nav>


            <a href="login.php" class="home-login-btn">
                Login
                <span>→</span>
            </a>

        </div>

    </header>


    <main>

        <!-- =========================
             HERO
        ========================== -->

        <section class="home-hero" id="beranda">

            <div class="home-hero-container">

                <div class="home-hero-content">

                    <div class="home-eyebrow">
                        <span></span>
                        SISTEM KASIR DIGITAL
                    </div>

                    <h1>
                        Sistem Informasi
                        <strong>Penjualan</strong>
                    </h1>

                    <p>
                        Sistem kasir untuk mengelola data produk,
                        pelanggan, dan transaksi penjualan.
                    </p>

                    <div class="home-hero-actions">

                        <a href="login.php" class="home-primary-btn">
                            Login
                            <span>→</span>
                        </a>

                        <a href="#produk" class="home-secondary-btn">
                            Lihat Data Produk
                        </a>

                    </div>

                </div>


                <!-- HERO SUMMARY -->

                <div class="home-hero-panel">

                    <div class="hero-panel-top">

                        <div>
                            <span class="panel-label">
                                Ringkasan Sistem
                            </span>

                            <h3>
                                <?= rupiah($totalPendapatan); ?>
                            </h3>
                        </div>

                        <div class="hero-live">
                            <i></i>
                            Database Terhubung
                        </div>

                    </div>


                    <div class="hero-panel-divider"></div>


                    <div class="hero-panel-stats">

                        <div>
                            <strong>
                                <?= number_format($totalTransaksi); ?>
                            </strong>

                            <span>Transaksi</span>
                        </div>

                        <div>
                            <strong>
                                <?= number_format($totalTerjual); ?>
                            </strong>

                            <span>Produk Terjual</span>
                        </div>

                        <div>
                            <strong>
                                <?= number_format($totalProduk); ?>
                            </strong>

                            <span>Produk</span>
                        </div>

                    </div>


                    <div class="hero-panel-footer">

                        <span>
                            Data tersimpan di sistem
                        </span>

                        <span class="hero-arrow">
                            ↗
                        </span>

                    </div>

                </div>

            </div>

        </section>


        <!-- =========================
             MINI STATS
        ========================== -->

        <section class="home-mini-section">

            <div class="home-container">

                <div class="home-mini-grid">

                    <div class="home-mini-item">

                        <div class="mini-icon">
                            ₱
                        </div>

                        <div>
                            <strong>
                                <?= rupiah($totalPendapatan); ?>
                            </strong>

                            <span>Total pendapatan</span>
                        </div>

                    </div>


                    <div class="home-mini-item">

                        <div class="mini-icon">
                            #
                        </div>

                        <div>
                            <strong>
                                <?= number_format($totalTransaksi); ?>
                            </strong>

                            <span>Total transaksi</span>
                        </div>

                    </div>


                    <div class="home-mini-item">

                        <div class="mini-icon">
                            P
                        </div>

                        <div>
                            <strong>
                                <?= number_format($totalProduk); ?>
                            </strong>

                            <span>Produk tersedia</span>
                        </div>

                    </div>


                    <div class="home-mini-item">

                        <div class="mini-icon">
                            U
                        </div>

                        <div>
                            <strong>
                                <?= number_format($totalPelanggan); ?>
                            </strong>

                            <span>Pelanggan</span>
                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!-- =========================
             PRODUK TERLARIS
        ========================== -->

        <section class="home-section" id="produk">

            <div class="home-container">

                <div class="section-heading">

                    <div>

                        <span class="section-overline">
                            PRODUK
                        </span>

                        <h2>
                            Data Produk Terjual
                        </h2>

                    </div>

                    <p>
                        Menampilkan produk berdasarkan jumlah 
                        penjualan yang tercatat dalam sistem.
                    </p>

                </div>


                <?php if (count($produkTerlaris) > 0): ?>

                    <div class="home-product-grid">

                        <?php foreach ($produkTerlaris as $index => $produk): ?>

                            <?php
                            $jumlahTerjual = (int)$produk['jumlah_terjual'];

                            $persentase = $maxTerjual > 0
                                ? ($jumlahTerjual / $maxTerjual) * 100
                                : 0;
                            ?>

                            <article class="home-product-card">

                                <div class="product-card-top">

                                    <span class="product-number">
                                        <?= str_pad($index + 1, 2, '0', STR_PAD_LEFT); ?>
                                    </span>

                                    <span class="product-status">
                                        Terjual
                                    </span>

                                </div>


                                <div class="product-card-body">

                                    <h3>
                                        <?= htmlspecialchars($produk['nama_produk']); ?>
                                    </h3>

                                    <span class="product-price">
                                        <?= rupiah($produk['harga']); ?>
                                    </span>

                                </div>


                                <div class="product-card-bottom">

                                    <div class="product-sold-info">

                                        <span>
                                            <?= number_format($jumlahTerjual); ?>
                                            unit terjual
                                        </span>

                                        <strong>
                                            <?= number_format($produk['stok']); ?>
                                            stok
                                        </strong>

                                    </div>


                                    <div class="product-progress">

                                        <span
                                            style="width: <?= $persentase; ?>%;"
                                        ></span>

                                    </div>

                                </div>

                            </article>

                        <?php endforeach; ?>

                    </div>

                <?php else: ?>

                    <div class="home-empty">

                        <div class="empty-icon">
                            —
                        </div>

                        <h3>
                            Belum ada data penjualan
                        </h3>

                        <p>
                            Produk yang sudah terjual akan tampil
                            di bagian ini.
                        </p>

                    </div>

                <?php endif; ?>

            </div>

        </section>


        <!-- =========================
             AKTIVITAS
        ========================== -->

        <section class="home-section home-section-soft" id="aktivitas">

            <div class="home-container">

                <div class="section-heading">

                    <div>

                        <span class="section-overline">
                            AKTIVITAS
                        </span>

                        <h2>
                            Transaksi terbaru
                        </h2>

                    </div>

                    <p>
                        Daftar transaksi penjualan 
                        yang terakhir tercatat.
                    </p>

                </div>


                <?php if (count($transaksiTerbaru) > 0): ?>

                    <div class="home-activity-list">

                        <?php foreach ($transaksiTerbaru as $transaksi): ?>

                            <?php
                            $tanggal = strtotime(
                                $transaksi['tanggal_penjualan']
                            );
                            ?>

                            <div class="activity-item">

                                <div class="activity-icon">
                                    Rp
                                </div>


                                <div class="activity-main">

                                    <div class="activity-title">

                                        <strong>
                                            Transaksi #<?= (int)$transaksi['id_penjualan']; ?>
                                        </strong>

                                        <span>
                                            <?= htmlspecialchars($transaksi['nama_pelanggan']); ?>
                                        </span>

                                    </div>

                                    <small>
                                        <?= date('d M Y, H:i', $tanggal); ?> WIB
                                    </small>

                                </div>


                                <strong class="activity-price">
                                    <?= rupiah($transaksi['total_harga']); ?>
                                </strong>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php else: ?>

                    <div class="home-empty">

                        <div class="empty-icon">
                            —
                        </div>

                        <h3>
                            Belum ada transaksi
                        </h3>

                        <p>
                            Transaksi terbaru akan tampil
                            setelah penjualan dilakukan.
                        </p>

                    </div>

                <?php endif; ?>

            </div>

        </section>


        <!-- =========================
             TENTANG
        ========================== -->

        <section class="home-about" id="tentang">

            <div class="home-container">

                <div class="home-about-grid">

                    <div>

                        <span class="section-overline">
                            TENTANG SISTEM
                        </span>

                        <h2>
                            Sistem untuk pengelolaan 
                            toko yang lebih teratur.
                        </h2>

                    </div>


                    <div>

                        <p>
                            Sistem kasir ini digunakan untuk mengelola 
                            data penjualan pada toko, meliputi data produk, 
                            pelanggan, transaksi penjualan, dan detail transaksi.
                        </p>

                        <p>
                            Seluruh data tersimpan dalam database sehingga informasi
                            penjualan dapat dikelola melalui sistem secara terstruktur.
                        </p>

                    </div>

                </div>


                <div class="about-line"></div>


                <div class="about-bottom">

                    <div>
                        <strong>
                            <?= number_format($totalProduk); ?>
                        </strong>

                        <span>
                            Produk
                        </span>
                    </div>

                    <div>
                        <strong>
                            <?= number_format($totalPelanggan); ?>
                        </strong>

                        <span>
                            Pelanggan
                        </span>
                    </div>

                    <div>
                        <strong>
                            <?= number_format($totalTransaksi); ?>
                        </strong>

                        <span>
                            Transaksi
                        </span>
                    </div>

                    <a href="login.php" class="about-login">
                        Buka sistem admin
                        <span>→</span>
                    </a>

                </div>

            </div>

        </section>


        <!-- =========================
             CTA
        ========================== -->

        <section class="home-cta">

            <div class="home-container">

                <div class="cta-inner">

                    <div>

                        <span>
                            AKSES SISTEM KASIR
                        </span>

                        <h2>
                            Masuk sebagai administrator untuk mengelola data dan transaksi penjualan.
                        </h2>

                    </div>


                    <a href="login.php" class="cta-button">
                        Login
                        <span>→</span>
                    </a>

                </div>

            </div>

        </section>

    </main>


    <!-- =========================
         FOOTER
    ========================== -->

    <footer class="home-footer">

        <div class="home-container">

            <div class="footer-inner">

                <div class="footer-brand">

                    <strong>
                        Bottle.Co
                    </strong>

                    <span>
                        Smart Cashier System
                    </span>

                </div>


                <div class="footer-copy">

                    © <?= date('Y'); ?> Bottle.Co
                    Sistem pengelolaan toko.

                </div>

            </div>

        </div>

    </footer>


    <script src="assets/js/bootstrap.bundle.min.js"></script>

</body>

</html>