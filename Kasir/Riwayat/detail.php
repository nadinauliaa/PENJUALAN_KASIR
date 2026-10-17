<?php
session_start();

if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'kasir') {
    header("Location: ../../login.php");
    exit;
}

include "../../koneksi.php";

date_default_timezone_set('Asia/Jakarta');

$namaKasir = $_SESSION['nama'] ?? 'Kasir';

/* =========================
   CEK ID TRANSAKSI
========================= */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_penjualan = (int) $_GET['id'];


/* =========================
   DATA TRANSAKSI
========================= */

$queryTransaksi = mysqli_query(
    $koneksi,
    "
    SELECT
        p.id_penjualan,
        p.tanggal_penjualan,
        p.total_harga,
        p.id_pelanggan,
        COALESCE(pl.nama_pelanggan, 'Umum') AS nama_pelanggan,
        pl.alamat,
        pl.no_telp
    FROM penjualan p
    LEFT JOIN pelanggan pl
        ON p.id_pelanggan = pl.id_pelanggan
    WHERE p.id_penjualan = $id_penjualan
    LIMIT 1
    "
);

if (!$queryTransaksi || mysqli_num_rows($queryTransaksi) === 0) {
    header("Location: index.php");
    exit;
}

$transaksi = mysqli_fetch_assoc($queryTransaksi);


/* =========================
   DETAIL PRODUK
========================= */

$queryDetail = mysqli_query(
    $koneksi,
    "
    SELECT
        d.id_detail,
        d.id_produk,
        d.jumlah_produk,
        d.subtotal,
        pr.nama_produk,
        pr.harga
    FROM detailpenjualan d
    INNER JOIN produk pr
        ON d.id_produk = pr.id_produk
    WHERE d.id_penjualan = $id_penjualan
    ORDER BY d.id_detail ASC
    "
);


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

    <title>
        Detail Transaksi #<?= $id_penjualan; ?> - KasirPro
    </title>

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
        href="/PENJUALAN/assets/css/kasir.css?v=3"
    >

    <link
        rel="stylesheet"
        href="/PENJUALAN/assets/css/detail_riwayat.css?v=1"
    >

</head>


<body class="kasir-dashboard">



<!-- =====================================================
     CONTENT
===================================================== -->

<main>

    <div class="detail-container">


        <!-- HEADER -->

        <section class="detail-header">

            <a
                href="index.php"
                class="detail-back"
            >
                ← Kembali ke Riwayat Transaksi
            </a>

            <span class="detail-label">
                DETAIL TRANSAKSI
            </span>

            <div class="detail-title-row">

                <div>

                    <h1>
                        Transaksi
                        #<?= $id_penjualan; ?>
                    </h1>

                    <p>
                        Informasi lengkap transaksi penjualan
                        yang tercatat dalam sistem.
                    </p>

                </div>


                <a
                    href="../transaksi/index.php"
                    class="btn btn-primary btn-transaksi-baru"
                >
                    + Transaksi Baru
                </a>

            </div>

        </section>


        <!-- INFORMASI TRANSAKSI -->

        <section class="detail-info-grid">


            <div class="detail-info-card">

                <span>
                    NOMOR TRANSAKSI
                </span>

                <strong>
                    #<?= $id_penjualan; ?>
                </strong>

            </div>


            <div class="detail-info-card">

                <span>
                    TANGGAL
                </span>

                <strong>

                    <?= date(
                        'd M Y',
                        strtotime(
                            $transaksi['tanggal_penjualan']
                        )
                    ); ?>

                </strong>

                <small>

                    <?= date(
                        'H:i',
                        strtotime(
                            $transaksi['tanggal_penjualan']
                        )
                    ); ?>

                    WIB

                </small>

            </div>


            <div class="detail-info-card">

                <span>
                    PELANGGAN
                </span>

                <strong>
                    <?= htmlspecialchars(
                        $transaksi['nama_pelanggan']
                    ); ?>
                </strong>

            </div>


        </section>


        <!-- DATA PRODUK -->

        <section class="detail-card">

            <div class="detail-card-header">

                <div>

                    <span>
                        RINCIAN PEMBELIAN
                    </span>

                    <h2>
                        Produk
                    </h2>

                </div>

            </div>


            <div class="detail-table-wrapper">

                <table class="table detail-table">

                    <thead>

                        <tr>

                            <th width="60">
                                No
                            </th>

                            <th>
                                Produk
                            </th>

                            <th width="150">
                                Harga
                            </th>

                            <th width="110">
                                Jumlah
                            </th>

                            <th width="160">
                                Subtotal
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php if (
                        mysqli_num_rows($queryDetail) > 0
                    ): ?>

                        <?php
                        $no = 1;
                        $totalItem = 0;
                        ?>

                        <?php while (
                            $detail =
                            mysqli_fetch_assoc($queryDetail)
                        ): ?>

                            <?php
                            $totalItem +=
                                (int) $detail['jumlah_produk'];
                            ?>

                            <tr>

                                <td class="detail-number">

                                    <?= $no++; ?>

                                </td>


                                <td>

                                    <strong class="detail-product-name">

                                        <?= htmlspecialchars(
                                            $detail['nama_produk']
                                        ); ?>

                                    </strong>

                                </td>


                                <td>

                                    <span class="detail-price">

                                        <?= rupiah(
                                            $detail['harga']
                                        ); ?>

                                    </span>

                                </td>


                                <td>

                                    <span class="detail-quantity">

                                        <?= (int) $detail[
                                            'jumlah_produk'
                                        ]; ?>

                                    </span>

                                </td>


                                <td>

                                    <strong class="detail-subtotal">

                                        <?= rupiah(
                                            $detail['subtotal']
                                        ); ?>

                                    </strong>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td
                                colspan="5"
                                class="detail-empty"
                            >
                                Tidak ada detail produk.
                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>


            <!-- TOTAL -->

            <div class="detail-total-area">

                <div class="detail-total-item">

                    <span>
                        Total Item
                    </span>

                    <strong>
                        <?= $totalItem ?? 0; ?>
                    </strong>

                </div>


                <div class="detail-grand-total">

                    <span>
                        Total Pembayaran
                    </span>

                    <strong>
                        <?= rupiah(
                            $transaksi['total_harga']
                        ); ?>
                    </strong>

                </div>

            </div>

        </section>


        <!-- DATA PELANGGAN -->

        <section class="detail-card pelanggan-detail-card">

            <div class="detail-card-header">

                <div>

                    <span>
                        INFORMASI PELANGGAN
                    </span>

                    <h2>
                        Data Pelanggan
                    </h2>

                </div>

            </div>


            <div class="pelanggan-detail">

                <div>

                    <span>
                        Nama
                    </span>

                    <strong>
                        <?= htmlspecialchars(
                            $transaksi['nama_pelanggan']
                        ); ?>
                    </strong>

                </div>


                <div>

                    <span>
                        No. Telepon
                    </span>

                    <strong>

                        <?php if (
                            !empty($transaksi['no_telp'])
                        ): ?>

                            <?= htmlspecialchars(
                                $transaksi['no_telp']
                            ); ?>

                        <?php else: ?>

                            -

                        <?php endif; ?>

                    </strong>

                </div>


                <div>

                    <span>
                        Alamat
                    </span>

                    <strong>

                        <?php if (
                            !empty($transaksi['alamat'])
                        ): ?>

                            <?= htmlspecialchars(
                                $transaksi['alamat']
                            ); ?>

                        <?php else: ?>

                            -

                        <?php endif; ?>

                    </strong>

                </div>

            </div>

        </section>


        <!-- ACTION -->

        <div class="detail-actions">

            <a href="index.php" class="btn btn-light btn-kembali">
                ← Kembali
            </a>

            <a
                href="cetak.php?id=<?= $id_penjualan; ?>"
                target="_blank"
                class="btn btn-primary btn-cetak"
            >
                🖨 Cetak Struk
            </a>

        </div>

    </div>

</main>


<script src="/PENJUALAN/assets/js/bootstrap.bundle.min.js"></script>

</body>

</html>