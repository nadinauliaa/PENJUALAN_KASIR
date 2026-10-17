<?php

session_start();

if (!isset($_SESSION['id_user'])) {
    header("Location: ../login.php");
    exit;
}

include "../koneksi.php";

$namaAdmin = $_SESSION['nama'] ?? $_SESSION['username'];
/* =========================
   TOTAL PRODUK
========================= */

$queryProduk = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM produk"
);

$dataProduk = mysqli_fetch_assoc($queryProduk);

$totalProduk = $dataProduk['total'] ?? 0;


/* =========================
   TOTAL PELANGGAN
========================= */

$queryPelanggan = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM pelanggan"
);

$dataPelanggan = mysqli_fetch_assoc($queryPelanggan);

$totalPelanggan = $dataPelanggan['total'] ?? 0;


/* =========================
   TOTAL TRANSAKSI
========================= */

$queryTransaksi = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM penjualan"
);

$dataTransaksi = mysqli_fetch_assoc($queryTransaksi);

$totalTransaksi = $dataTransaksi['total'] ?? 0;


/* =========================
   TOTAL PENDAPATAN
========================= */

$queryPendapatan = mysqli_query(
    $koneksi,
    "SELECT COALESCE(SUM(total_harga), 0) AS total
     FROM penjualan"
);

$dataPendapatan = mysqli_fetch_assoc($queryPendapatan);

$totalPendapatan = $dataPendapatan['total'] ?? 0;


/* =========================
   TRANSAKSI TERBARU
========================= */
$queryTerbaru = mysqli_query(
    $koneksi,
    "SELECT
        p.id_penjualan,
        p.tanggal_penjualan,
        p.total_harga,
        pl.nama_pelanggan
     FROM penjualan p
     LEFT JOIN pelanggan pl
        ON p.id_pelanggan = pl.id_pelanggan
     ORDER BY p.id_penjualan DESC
     LIMIT 10"
);

/* =========================
   FORMAT RUPIAH
========================= */

function rupiah($angka)
{
    return 'Rp ' . number_format(
        $angka,
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

    <title>Dashboard | Bottle.Co</title>

    <!-- BOOTSTRAP -->
    <link
        rel="stylesheet"
        href="../assets/css/bootstrap.min.css"
    >

    <!-- ADMIN CSS -->
    <link
        rel="stylesheet"
        href="../assets/css/admin.css?v=<?= time(); ?>"
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


    <!-- MENU -->

    <div class="sidebar-content">


        <div class="menu-label">
            MENU UTAMA
        </div>


        <nav class="admin-menu">


            <!-- DASHBOARD -->

            <a
                href="index.php"
                class="admin-menu-item active"
            >

                <span class="menu-icon">
                    ▦
                </span>

                <span>
                    Dashboard
                </span>

            </a>


            <!-- PRODUK -->

            <a
                href="produk/index.php"
                class="admin-menu-item"
            >

                <span class="menu-icon">
                    ▣
                </span>

                <span>
                    Produk
                </span>

            </a>


            <!-- PELANGGAN -->

            <a
                href="pelanggan/index.php"
                class="admin-menu-item"
            >

                <span class="menu-icon">
                    ♟
                </span>

                <span>
                    Pelanggan
                </span>

            </a>


            <!-- PENJUALAN -->

            <a
                href="penjualan/index.php"
                class="admin-menu-item"
            >

                <span class="menu-icon">
                    🛒
                </span>

                <span>
                    Penjualan
                </span>

            </a>


            <!-- DETAIL PENJUALAN -->

            <a
                href="detail-penjualan/index.php"
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


            <!-- PENGGUNA -->

            <a
                href="pengguna/index.php"
                class="admin-menu-item"
            >

                <span class="menu-icon">
                    ♙
                </span>

                <span>
                    Pengguna
                </span>

            </a>


            <!-- LAPORAN -->

            <a
                href="laporan/index.php"
                class="admin-menu-item"
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
            href="../logout.php"
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
     MAIN AREA
===================================================== -->

<div class="admin-wrapper">


    <!-- =================================================
         TOPBAR
    ================================================== -->

    <header class="admin-topbar">


        <div class="topbar-left">

            <div class="breadcrumb-small">
                HALAMAN ADMIN
            </div>

            <h1>
                Dashboard
            </h1>

        </div>


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
         MAIN CONTENT
    ================================================== -->

    <main class="admin-main">


        <div class="dashboard-content">


            <!-- =========================================
                 WELCOME
            ========================================== -->

            <section class="dashboard-welcome">


                <div>

                    <span class="dashboard-label">
                        DASHBOARD ADMIN
                    </span>


                    <h2>
                        Selamat Datang,
                        <?= htmlspecialchars($namaAdmin); ?>
                    </h2>


                    <p>
                        Kelola aktivitas toko melalui
                        dashboard Bottle.Co
                    </p>

                </div>


            </section>



            <!-- =========================================
                 STATISTIK
            ========================================== -->

            <section class="dashboard-stats">


                <!-- PRODUK -->

                <div class="stat-card">

                    <div class="stat-icon">
                        📦
                    </div>

                    <div class="stat-info">

                        <span>
                            Total Produk
                        </span>

                        <strong>
                            <?= $totalProduk; ?>
                        </strong>

                    </div>

                </div>


                <!-- PELANGGAN -->

                <div class="stat-card">

                    <div class="stat-icon">
                        👥
                    </div>

                    <div class="stat-info">

                        <span>
                            Total Pelanggan
                        </span>

                        <strong>
                            <?= $totalPelanggan; ?>
                        </strong>

                    </div>

                </div>


                <!-- TRANSAKSI -->

                <div class="stat-card">

                    <div class="stat-icon">
                        🧾
                    </div>

                    <div class="stat-info">

                        <span>
                            Total Transaksi
                        </span>

                        <strong>
                            <?= $totalTransaksi; ?>
                        </strong>

                    </div>

                </div>


                <!-- PENDAPATAN -->

                <div class="stat-card">

                    <div class="stat-icon">
                        💰
                    </div>

                    <div class="stat-info">

                        <span>
                            Total Pendapatan
                        </span>

                        <strong class="income-number">
                            <?= rupiah($totalPendapatan); ?>
                        </strong>

                    </div>

                </div>


            </section>



            <!-- =========================================
                 TRANSAKSI TERBARU
            ========================================== -->

            <section class="dashboard-section">


                <div class="section-heading">


                    <div>

                        <span>
                            AKTIVITAS
                        </span>

                        <h3>
                            Transaksi Terbaru
                        </h3>

                    </div>


                    <a
                        href="penjualan/index.php"
                        class="view-all"
                    >
                        Lihat Semua
                    </a>


                </div>



                <div class="dashboard-table">


                    <table>


                        <thead>

                            <tr>

                                <th>
                                    ID
                                </th>

                                <th>
                                    PELANGGAN
                                </th>

                                <th>
                                    TANGGAL
                                </th>

                                <th>
                                    TOTAL
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                            <?php if (
                                mysqli_num_rows($queryTerbaru) > 0
                            ): ?>


                                <?php while (
                                    $transaksi = mysqli_fetch_assoc(
                                        $queryTerbaru
                                    )
                                ): ?>


                                    <tr>


                                        <td>

                                            <span class="transaction-id">
                                                #<?= $transaksi['id_penjualan']; ?>
                                            </span>

                                        </td>


                                        <td>

                                            <?= htmlspecialchars(
                                                $transaksi['nama_pelanggan']
                                                    ?? 'Umum'
                                            ); ?>

                                        </td>


                                        <td>

                                            <?= date(
                                                'd/m/Y',
                                                strtotime(
                                                    $transaksi['tanggal_penjualan']
                                                )
                                            ); ?>

                                        </td>


                                        <td>

                                            <strong class="table-total">

                                                <?= rupiah(
                                                    $transaksi['total_harga']
                                                ); ?>

                                            </strong>

                                        </td>


                                    </tr>


                                <?php endwhile; ?>


                            <?php else: ?>


                                <tr>

                                    <td
                                        colspan="4"
                                        class="empty-table"
                                    >

                                        Belum ada transaksi.

                                    </td>

                                </tr>


                            <?php endif; ?>


                        </tbody>


                    </table>


                </div>


            </section>



            <!-- =========================================
                 MENU CEPAT
            ========================================== -->

            <section class="dashboard-section">


                <div class="section-heading">

                    <div>

                        <span>
                            AKSES CEPAT
                        </span>

                        <h3>
                            Menu Cepat
                        </h3>

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
                                Kelola Produk
                            </strong>

                            <span>
                                Tambah dan edit produk
                            </span>

                        </div>

                    </a>



                    <!-- PELANGGAN -->

                    <a
                        href="pelanggan/index.php"
                        class="quick-card"
                    >

                        <div class="quick-icon">
                            👥
                        </div>

                        <div>

                            <strong>
                                Kelola Pelanggan
                            </strong>

                            <span>
                                Data pelanggan toko
                            </span>

                        </div>

                    </a>



                    <!-- PENJUALAN -->

                    <a
                        href="penjualan/index.php"
                        class="quick-card"
                    >

                        <div class="quick-icon">
                            🧾
                        </div>

                        <div>

                            <strong>
                                Transaksi Penjualan
                            </strong>

                            <span>
                                Kelola transaksi toko
                            </span>

                        </div>

                    </a>


                </div>


            </section>


        </div>


    </main>


</div>



<!-- BOOTSTRAP JS -->

<script src="../assets/js/bootstrap.bundle.min.js"></script>


</body>

</html>