<?php

date_default_timezone_set('Asia/Jakarta');

session_start();

if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

include "../../koneksi.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


/* =========================================================
   ID TRANSAKSI
========================================================= */

$id_penjualan = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($id_penjualan <= 0) {
    header("Location: index.php");
    exit;
}


/* =========================================================
   DATA TRANSAKSI
========================================================= */

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

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_penjualan
);

mysqli_stmt_execute($stmt);

$result_penjualan = mysqli_stmt_get_result($stmt);

$penjualan = mysqli_fetch_assoc($result_penjualan);

mysqli_stmt_close($stmt);


/* =========================================================
   CEK TRANSAKSI
========================================================= */

if (!$penjualan) {
    header("Location: index.php");
    exit;
}


/* =========================================================
   DETAIL PRODUK
========================================================= */

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

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_penjualan
);

mysqli_stmt_execute($stmt);

$detail_result = mysqli_stmt_get_result($stmt);

$detail_items = [];

while ($item = mysqli_fetch_assoc($detail_result)) {
    $detail_items[] = $item;
}

mysqli_stmt_close($stmt);


/* =========================================================
   FORMAT WAKTU TRANSAKSI
========================================================= */

$tanggalTransaksi = date(
    'd/m/Y',
    strtotime($penjualan['tanggal_penjualan'])
);

$waktuTransaksi = date(
    'H:i:s',
    strtotime($penjualan['tanggal_penjualan'])
);


/* =========================================================
   NAMA PELANGGAN
========================================================= */

$namaPelanggan = !empty($penjualan['nama_pelanggan'])
    ? $penjualan['nama_pelanggan']
    : 'Pelanggan Umum';

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
        Struk #<?= str_pad(
            $penjualan['id_penjualan'],
            4,
            '0',
            STR_PAD_LEFT
        ); ?>
    </title>


<style>

/* =========================================================
   CETAK STRUK KASIRPRO
========================================================= */

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    background: #F7F9F5;
    color: #26332A;
    font-family:
        Arial,
        Helvetica,
        sans-serif;
}

.print-wrapper {
    width: 100%;
    min-height: 100vh;
    padding: 40px 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
}


/* =========================
   STRUK
========================= */

.struk {
    width: 380px;
    padding: 28px;
    background: #FFFFFF;
    border: 1px solid #E1E8E2;
    box-shadow: 0 8px 30px rgba(38, 51, 42, 0.08);
}

.struk-header {
    text-align: center;
}

.struk-header h1 {
    margin: 0;
    color: #7FA88C;
    font-size: 24px;
    font-weight: 800;
}

.struk-header p {
    margin: 6px 0 0;
    color: #78837B;
    font-size: 11px;
}

.struk-header .alamat {
    margin: 4px 0 0;
    color: #98A49D;
    font-size: 10px;
}


/* =========================
   LOGO
========================= */

.struk-logo {
    width: 38px;
    height: 38px;

    margin: 0 auto 10px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #7FA88C;
    color: #FFFFFF;

    border-radius: 7px;

    font-size: 19px;
}


/* =========================
   GARIS
========================= */

.struk-line {
    margin: 18px 0;
    border-top: 1px dashed #98A49D;
}


/* =========================
   INFORMASI
========================= */

.struk-info {
    display: flex;
    flex-direction: column;
    gap: 9px;
}

.struk-info > div {
    display: flex;
    justify-content: space-between;
    gap: 20px;
}

.struk-info span {
    color: #78837B;
    font-size: 11px;
}

.struk-info strong {
    color: #26332A;
    font-size: 11px;
    font-weight: 700;
    text-align: right;
}


/* =========================
   PRODUK
========================= */

.produk-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.produk-item {
    display: block;
}

.produk-nama {
    margin-bottom: 5px;
    color: #26332A;
    font-size: 12px;
    font-weight: 700;
}

.produk-detail {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
}

.produk-detail span {
    color: #78837B;
    font-size: 11px;
}

.produk-detail strong {
    color: #26332A;
    font-size: 11px;
    font-weight: 700;
}


/* =========================
   TOTAL
========================= */

.total-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.total-row span {
    color: #3F4A43;
    font-size: 13px;
    font-weight: 700;
}

.total-row strong {
    color: #7FA88C;
    font-size: 18px;
    font-weight: 800;
}


/* =========================
   FOOTER
========================= */

.struk-footer {
    text-align: center;
}

.struk-footer p {
    margin: 0 0 5px;
    color: #3F4A43;
    font-size: 12px;
    font-weight: 700;
}

.struk-footer span {
    color: #98A49D;
    font-size: 9px;
}


/* =========================
   BUTTON
========================= */

.print-actions {
    margin-top: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.btn-print,
.btn-back {
    min-height: 40px;
    padding: 10px 17px;
    border-radius: 7px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
}

.btn-print {
    border: 1px solid #7FA88C;
    background: #7FA88C;
    color: #FFFFFF;
}

.btn-print:hover {
    background: #66846F;
}

.btn-back {
    border: 1px solid #D5DED7;
    background: #FFFFFF;
    color: #52665B;
}

.btn-back:hover {
    background: #F7F9F5;
}


/* =========================
   RESPONSIVE
========================= */

@media (max-width: 500px) {

    body {
        padding: 0;
    }

    .print-wrapper {
        width: 100%;
        min-height: 100vh;
        padding: 25px 15px;
    }

    .struk {
        width: 100%;
        max-width: 380px;
    }

    .print-actions {
        width: 100%;
        max-width: 380px;
    }
}


/* =========================
   MODE CETAK
========================= */

@media print {

    @page {
        size: 80mm auto;
        margin: 0;
    }

    html,
    body {
        width: 80mm;
        margin: 0;
        padding: 0;
        background: #FFFFFF;
    }

    .print-wrapper {
        width: 80mm;
        min-height: auto;
        padding: 0;
    }

    .struk {
        width: 80mm;
        padding: 8mm 5mm;
        border: none;
        box-shadow: none;
    }

    .print-actions {
        display: none;
    }

    .struk-header h1 {
        font-size: 20px;
    }

    .struk-header p {
        font-size: 9px;
    }

    .struk-header .alamat {
        font-size: 9px;
    }

    .struk-logo {
        background: #7FA88C !important;
        color: #FFFFFF !important;

        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}

</style>

</head>


<body>


<div class="print-wrapper">


    <!-- =====================================================
         STRUK
    ===================================================== -->

    <div class="struk">


        <!-- HEADER -->

        <div class="struk-header">

            <h1>
                Bottle.Co
            </h1>

            <p>
                Toko Tumbler
            </p>

            <div class="alamat">
                Sistem Penjualan &amp; Kasir
            </div>

        </div>


        <div class="struk-line"></div>


        <!-- INFORMASI TRANSAKSI -->

        <div class="struk-info">

            <div>

                <span>
                    No. Transaksi
                </span>

                <strong>

                    #<?= str_pad(
                        $penjualan['id_penjualan'],
                        4,
                        '0',
                        STR_PAD_LEFT
                    ); ?>

                </strong>

            </div>


            <div>

                <span>
                    Tanggal
                </span>

                <strong>

                    <?= htmlspecialchars(
                        $tanggalTransaksi
                    ); ?>

                </strong>

            </div>


            <div>

                <span>
                    Waktu
                </span>

                <strong>

                    <?= htmlspecialchars(
                        $waktuTransaksi
                    ); ?>

                    WIB

                </strong>

            </div>


            <div>

                <span>
                    Pelanggan
                </span>

                <strong>

                    <?= htmlspecialchars(
                        $namaPelanggan
                    ); ?>

                </strong>

            </div>

        </div>


        <div class="struk-line"></div>


        <!-- PRODUK -->

        <div class="produk-list">


            <?php if (!empty($detail_items)): ?>


                <?php foreach ($detail_items as $item): ?>


                    <div class="produk-item">


                        <div class="produk-nama">

                            <?= htmlspecialchars(
                                $item['nama_produk']
                            ); ?>

                        </div>


                        <div class="produk-detail">


                            <span>

                                <?= number_format(
                                    $item['jumlah_produk'],
                                    0,
                                    ',',
                                    '.'
                                ); ?>

                                x

                                Rp <?= number_format(
                                    $item['harga'],
                                    0,
                                    ',',
                                    '.'
                                ); ?>

                            </span>


                            <strong>

                                Rp <?= number_format(
                                    $item['subtotal'],
                                    0,
                                    ',',
                                    '.'
                                ); ?>

                            </strong>


                        </div>


                    </div>


                <?php endforeach; ?>


            <?php else: ?>


                <div
                    style="
                        text-align:center;
                        font-size:9px;
                        color:#98A49D;
                    "
                >
                    Tidak ada produk.
                </div>


            <?php endif; ?>


        </div>


        <div class="struk-line"></div>


        <!-- TOTAL -->

        <div class="total-row">

            <span>
                TOTAL
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


        <div class="struk-line"></div>


        <!-- FOOTER -->

        <div class="struk-footer">

            <p>
                Terima Kasih
            </p>

            <span>
                Terima kasih telah berbelanja.
            </span>

            <span>
                Barang yang sudah dibeli tidak dapat
                dikembalikan.
            </span>

        </div>


    </div>


    <!-- =====================================================
         BUTTON
    ===================================================== -->

    <div class="print-actions">


        <a
            href="detail.php?id=<?= $penjualan['id_penjualan']; ?>"
            class="btn-back"
        >
            ← Kembali
        </a>


        <button
            type="button"
            class="btn-print"
            onclick="window.print()"
        >
            🖨 Cetak Struk
        </button>


    </div>


</div>


</body>

</html>