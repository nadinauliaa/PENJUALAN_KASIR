<?php

session_start();

if (
    !isset($_SESSION['level']) ||
    $_SESSION['level'] !== 'admin'
) {
    header("Location: ../../login.php");
    exit;
}

include "../../koneksi.php";


/* =========================================================
   NAMA ADMIN
========================================================= */

$namaAdmin = $_SESSION['nama']
    ?? $_SESSION['username']
    ?? 'Administrator';


/* =========================================================
   DATA DETAIL PENJUALAN
========================================================= */

$query = mysqli_query($koneksi, "

    SELECT

        dp.id_detail,
        dp.id_penjualan,
        dp.id_produk,
        dp.jumlah_produk,

        pr.harga,

        dp.subtotal,

        p.tanggal_penjualan,

        pr.nama_produk,

        pl.nama_pelanggan

    FROM detailpenjualan dp

    INNER JOIN penjualan p
        ON dp.id_penjualan = p.id_penjualan

    INNER JOIN produk pr
        ON dp.id_produk = pr.id_produk

    LEFT JOIN pelanggan pl
        ON p.id_pelanggan = pl.id_pelanggan

    ORDER BY dp.id_detail DESC

");


/* =========================================================
   SUMMARY
========================================================= */

$qTotalDetail = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM detailpenjualan"
);

$dataTotalDetail = mysqli_fetch_assoc($qTotalDetail);

$totalDetail = $dataTotalDetail['total'] ?? 0;


$qProdukTerjual = mysqli_query(
    $koneksi,
    "SELECT SUM(jumlah_produk) AS total FROM detailpenjualan"
);

$dataProdukTerjual = mysqli_fetch_assoc($qProdukTerjual);

$totalProdukTerjual = $dataProdukTerjual['total'] ?? 0;


$qTotalPenjualan = mysqli_query(
    $koneksi,
    "SELECT SUM(subtotal) AS total FROM detailpenjualan"
);

$dataTotalPenjualan = mysqli_fetch_assoc($qTotalPenjualan);

$totalPenjualan = $dataTotalPenjualan['total'] ?? 0;


$totalProdukTerjual = (int) $totalProdukTerjual;
$totalPenjualan = (float) $totalPenjualan;


/* =========================================================
   FORMAT RUPIAH
========================================================= */

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

    <title>Detail Penjualan | Bottle.Co</title>


    <!-- GLOBAL ADMIN CSS -->
    <link
        rel="stylesheet"
        href="../../assets/css/admin.css?v=<?= time(); ?>"
    >


    <!-- DETAIL PENJUALAN CSS -->
    <link
        rel="stylesheet"
        href="../../assets/css/detail_penjualan.css?v=<?= time(); ?>"
    >

</head>


<body>


<!-- =====================================================
     SIDEBAR
====================================================== -->

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


            <!-- DASHBOARD -->

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


            <!-- PRODUK -->

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


            <!-- PELANGGAN -->

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


            <!-- PENJUALAN -->

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


            <!-- DETAIL PENJUALAN -->

            <a
                href="index.php"
                class="admin-menu-item active"
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


            <!-- LAPORAN -->

            <a
                href="../laporan/index.php"
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
     MAIN
====================================================== -->

<main class="admin-main">


    <!-- =================================================
         TOPBAR
    ================================================== -->

    <header class="admin-topbar">

    <div class="topbar-left">

        <span class="topbar-small">
            TRANSAKSI
        </span>

        <h1>
            Detail Penjualan
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
         CONTENT
    ================================================== -->

    <section class="detail-content">


        <!-- PAGE HEADER -->

        <div class="detail-page-header">


            <div class="detail-page-label">
                RIWAYAT TRANSAKSI
            </div>


            <div class="detail-page-title">

                <h1>
                    Detail Penjualan
                </h1>

                <p>
                    Lihat rincian produk dari seluruh transaksi penjualan.
                </p>

            </div>


        </div>



        <!-- =================================================
             SUMMARY
        ================================================== -->

        <div class="detail-summary">


            <!-- TOTAL DETAIL -->

            <div class="detail-summary-card">

                <div class="detail-summary-icon">
                    ▤
                </div>


                <div class="detail-summary-text">

                    <span class="detail-summary-label">
                        TOTAL DETAIL
                    </span>

                    <span class="detail-summary-value">
                        <?= $totalDetail; ?>
                    </span>

                </div>

            </div>



            <!-- PRODUK TERJUAL -->

            <div class="detail-summary-card">

                <div class="detail-summary-icon">
                    📦
                </div>


                <div class="detail-summary-text">

                    <span class="detail-summary-label">
                        PRODUK TERJUAL
                    </span>

                    <span class="detail-summary-value">
                        <?= $totalProdukTerjual; ?>
                    </span>

                </div>

            </div>



            <!-- TOTAL NILAI -->

            <div class="detail-summary-card">

                <div class="detail-summary-icon">
                    Rp
                </div>


                <div class="detail-summary-text">

                    <span class="detail-summary-label">
                        TOTAL NILAI PENJUALAN
                    </span>

                    <span class="detail-summary-value">
                        <?= rupiah($totalPenjualan); ?>
                    </span>

                </div>

            </div>


        </div>



        <!-- =================================================
             TABLE
        ================================================== -->

        <div class="detail-table-card">


            <!-- TABLE HEADER -->

            <div class="detail-table-header">


                <div class="detail-table-title">

                    <span class="detail-table-label">
                        DATA TRANSAKSI
                    </span>

                    <h2>
                        Rincian Penjualan
                    </h2>

                </div>


                <span class="detail-count">
                    <?= $totalDetail; ?> detail
                </span>


            </div>



            <!-- TABLE -->

            <div class="detail-table-wrapper">


                <?php if (mysqli_num_rows($query) > 0): ?>


                    <table class="detail-table">


                        <thead>

                            <tr>

                                <th>
                                    No
                                </th>

                                <th>
                                    ID TRANSAKSI
                                </th>

                                <th>
                                    TANGGAL
                                </th>

                                <th>
                                    PELANGGAN
                                </th>

                                <th>
                                    PRODUK
                                </th>

                                <th>
                                    HARGA
                                </th>

                                <th>
                                    JUMLAH
                                </th>

                                <th>
                                    SUBTOTAL
                                </th>

                                <th>
                                    AKSI
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php

                        $no = 1;

                        while ($row = mysqli_fetch_assoc($query)):

                            $namaPelanggan =
                                $row['nama_pelanggan']
                                ?: 'Umum';

                            $initial =
                                strtoupper(
                                    substr(
                                        $namaPelanggan,
                                        0,
                                        1
                                    )
                                );

                        ?>


                            <tr>


                                <!-- NO -->

                                <td>

                                    <?= $no++; ?>

                                </td>



                                <!-- ID TRANSAKSI -->

                                <td>

                                    <strong>
                                        #TRX<?= str_pad(
                                            $row['id_penjualan'],
                                            4,
                                            '0',
                                            STR_PAD_LEFT
                                        ); ?>
                                    </strong>

                                </td>



                                <!-- TANGGAL -->

                                <td>

                                    <span class="transaction-date">

                                        <?= date(
                                            'd/m/Y',
                                            strtotime(
                                                $row['tanggal_penjualan']
                                            )
                                        ); ?>

                                    </span>

                                </td>



                                <!-- PELANGGAN -->

                                <td>

                                    <div class="customer-cell">


                                        <div class="customer-avatar">

                                            <?= htmlspecialchars(
                                                $initial
                                            ); ?>

                                        </div>


                                        <span class="customer-name">

                                            <?= htmlspecialchars(
                                                $namaPelanggan
                                            ); ?>

                                        </span>


                                    </div>

                                </td>



                                <!-- PRODUK -->

                                <td>

                                    <span class="product-name">

                                        <?= htmlspecialchars(
                                            $row['nama_produk']
                                        ); ?>

                                    </span>

                                </td>



                                <!-- HARGA -->

                                <td>

                                    <span class="price">

                                        <?= rupiah(
                                            $row['harga']
                                        ); ?>

                                    </span>

                                </td>



                                <!-- JUMLAH -->

                                <td>

                                    <span class="quantity-badge">

                                        <?= (int)
                                            $row['jumlah_produk']; ?>

                                    </span>

                                </td>



                                <!-- SUBTOTAL -->

                                <td>

                                    <span class="subtotal">

                                        <?= rupiah(
                                            $row['subtotal']
                                        ); ?>

                                    </span>

                                </td>



                                <!-- AKSI -->

                                <td>

                                    <a
                                        href="detail.php?id=<?= $row['id_penjualan']; ?>"
                                        class="detail-action"
                                        title="Lihat Detail"
                                    >
                                        👁
                                    </a>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                        </tbody>


                    </table>


                <?php else: ?>


                    <div class="detail-empty">


                        <div class="detail-empty-icon">
                            ▤
                        </div>


                        <h3>
                            Belum Ada Detail Penjualan
                        </h3>


                        <p>
                            Data detail transaksi belum tersedia.
                        </p>


                    </div>


                <?php endif; ?>


            </div>


        </div>


    </section>


</main>


</body>

</html>