<?php

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
   NAMA ADMIN
========================================================= */

$namaAdmin = $_SESSION['nama']
    ?? $_SESSION['username']
    ?? 'Administrator';

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
        Detail Transaksi |
        Bottle.Co
    </title>


   <style>
    {
    box-sizing: border-box;
}

html,
body {
    margin: 0;
    padding: 0;
}

body {
    min-height: 100vh;
    background: #F7F9F5;
    color: #26332A;
    font-family:
        "Segoe UI",
        Arial,
        Helvetica,
        sans-serif;
}


/* =====================================================
   MAIN
===================================================== */

.detail-page {
    width: 100%;
    min-height: 100vh;
    padding: 40px 25px 55px;
}

.detail-container {
    width: 100%;
    max-width: 1050px;
    margin: 0 auto;
}


/* =====================================================
   PAGE TITLE
===================================================== */

.page-heading {
    margin-bottom: 22px;
}

.page-heading-label {
    display: block;
    margin-bottom: 6px;
    color: #7FA88C;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .9px;
}

.page-heading h1 {
    margin: 0;
    color: #26332A;
    font-size: 28px;
    font-weight: 700;
    line-height: 1.2;
}

.page-heading p {
    margin: 7px 0 0;
    color: #78837B;
    font-size: 14px;
    line-height: 1.5;
}


/* =====================================================
   ACTION BUTTON
===================================================== */

.detail-actions {
    width: 100%;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.detail-actions-right {
    display: flex;
    align-items: center;
    gap: 9px;
}

.btn-back,
.btn-print,
.btn-delete {
    min-height: 42px;
    padding: 0 17px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition:
        background .2s ease,
        border-color .2s ease,
        color .2s ease,
        transform .2s ease;
}

.btn-back {
    background: #ffffff;
    border: 1px solid #E1E8E2;
    color: #78837B;
}

.btn-back:hover {
    background: #F7F9F5;
    border-color: #BFD0C3;
    color: #66846F;
}

.btn-print {
    background: #7FA88C;
    border: 1px solid #7FA88C;
    color: #ffffff;
}

.btn-print:hover {
    background: #66846F;
    border-color: #66846F;
    color: #ffffff;
    transform: translateY(-1px);
}

.btn-delete {
    background: #ffffff;
    border: 1px solid #fecaca;
    color: #dc2626;
}

.btn-delete:hover {
    background: #fef2f2;
    border-color: #fca5a5;
    color: #b91c1c;
}


/* =====================================================
   DETAIL CARD
===================================================== */

.sale-detail-card {
    width: 100%;
    overflow: hidden;
    background: #ffffff;
    border: 1px solid #E1E8E2;
    border-radius: 13px;
    box-shadow:
        0 8px 28px rgba(38, 51, 42, .055);
}


/* =====================================================
   TRANSACTION HEADER
===================================================== */

.sale-detail-header {
    min-height: 115px;
    padding: 27px 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 25px;
    border-bottom: 1px solid #E1E8E2;
}

.sale-detail-label {
    display: block;
    margin-bottom: 7px;
    color: #7FA88C;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .9px;
}

.sale-detail-header h2 {
    margin: 0;
    color: #26332A;
    font-size: 29px;
    font-weight: 700;
    line-height: 1.2;
}

.sale-detail-date {
    text-align: right;
}

.sale-detail-date span {
    display: block;
    margin-bottom: 5px;
    color: #98a49D;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .7px;
}

.sale-detail-date strong {
    color: #52665B;
    font-size: 14px;
    font-weight: 600;
}


/* =====================================================
   CUSTOMER
===================================================== */

.sale-customer-info {
    min-height: 90px;
    padding: 20px 30px;
    display: flex;
    align-items: center;
    gap: 15px;
    background: #F7F9F5;
    border-bottom: 1px solid #E1E8E2;
}

.sale-customer-info-icon {
    width: 48px;
    height: 48px;
    flex: 0 0 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: #EAF2EC;
    color: #7FA88C;
    font-size: 21px;
}

.sale-customer-info span {
    display: block;
    margin-bottom: 4px;
    color: #98a49D;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .8px;
}

.sale-customer-info strong {
    display: block;
    color: #26332A;
    font-size: 16px;
    font-weight: 600;
}

.sale-customer-info small {
    display: block;
    margin-top: 4px;
    color: #78837B;
    font-size: 13px;
}


/* =====================================================
   TABLE
===================================================== */

.sale-detail-table-wrapper {
    width: 100%;
    padding: 0 30px;
    overflow-x: auto;
}

.sale-detail-table {
    width: 100%;
    min-width: 700px;
    margin: 20px 0;
    border-collapse: collapse;
}

.sale-detail-table th {
    height: 49px;
    padding: 0 15px;
    background: #F7F9F5;
    border-top: 1px solid #E1E8E2;
    border-bottom: 1px solid #E1E8E2;
    color: #78837B;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .5px;
    text-align: left;
    white-space: nowrap;
}

.sale-detail-table td {
    height: 67px;
    padding: 10px 15px;
    border-bottom: 1px solid #E1E8E2;
    color: #52665B;
    font-size: 14px;
    vertical-align: middle;
}

.sale-detail-table tbody tr {
    transition: background .15s ease;
}

.sale-detail-table tbody tr:hover {
    background: #FAFCF9;
}

.sale-detail-table tbody tr:last-child td {
    border-bottom: none;
}

.sale-detail-table td strong {
    color: #26332A;
    font-size: 14px;
    font-weight: 600;
}

.sale-detail-table th:first-child,
.sale-detail-table td:first-child {
    width: 60px;
    color: #98a49D;
}


/* =====================================================
   TOTAL
===================================================== */

.sale-detail-total {
    min-height: 90px;
    padding: 22px 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    background: #F7F9F5;
    border-top: 1px solid #E1E8E2;
}

.sale-detail-total span {
    color: #52665B;
    font-size: 16px;
    font-weight: 600;
}

.sale-detail-total strong {
    color: #7FA88C;
    font-size: 25px;
    font-weight: 700;
    white-space: nowrap;
}


/* =====================================================
   FOOTNOTE
===================================================== */

.detail-note {
    margin-top: 17px;
    text-align: center;
    color: #98a49D;
    font-size: 12px;
}


/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 800px) {

    .detail-page {
        padding: 28px 18px 40px;
    }

    .detail-actions {
        align-items: stretch;
        flex-direction: column;
    }

    .detail-actions-right {
        width: 100%;
    }

    .btn-back {
        width: 100%;
    }

    .btn-print,
    .btn-delete {
        flex: 1;
    }

    .sale-detail-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .sale-detail-date {
        text-align: left;
    }
}


@media (max-width: 600px) {

    .detail-page {
        padding: 22px 12px 30px;
    }

    .page-heading h1 {
        font-size: 24px;
    }

    .page-heading p {
        font-size: 13px;
    }

    .detail-actions-right {
        display: grid;
        grid-template-columns: 1fr 1fr;
    }

    .btn-print,
    .btn-delete {
        width: 100%;
        padding: 0 10px;
        font-size: 12px;
    }

    .sale-detail-header {
        padding: 22px 20px;
    }

    .sale-detail-header h2 {
        font-size: 25px;
    }

    .sale-customer-info {
        padding: 18px 20px;
    }

    .sale-detail-table-wrapper {
        padding: 0 15px;
    }

    .sale-detail-total {
        padding: 20px;
    }

    .sale-detail-total span {
        font-size: 14px;
    }

    .sale-detail-total strong {
        font-size: 21px;
    }
}
</style>
</head>


<body>


<div class="detail-page">


    <div class="detail-container">


        <!-- =================================================
             JUDUL HALAMAN
        ================================================= -->

        <div class="page-heading">

            <span class="page-heading-label">
                TRANSAKSI PENJUALAN
            </span>

            <h1>
                Detail Penjualan
            </h1>

            <p>
                Informasi lengkap mengenai transaksi dan produk
                yang dibeli.
            </p>

        </div>



        <!-- =================================================
             ACTION
        ================================================= -->

        <div class="detail-actions">


            <a
                href="index.php"
                class="btn-back"
            >
                ← Kembali
            </a>


            <div class="detail-actions-right">


                <a
                    href="struk.php?id=<?= $penjualan['id_penjualan']; ?>"
                    class="btn-print"
                >
                    🧾 Cetak Struk
                </a>


                <a
                    href="../penjualan/hapus.php?id=<?= $penjualan['id_penjualan']; ?>"
                    class="btn-delete"
                    onclick="return confirm('Yakin ingin menghapus transaksi ini?');"
                >
                    Hapus Transaksi
                </a>


            </div>

        </div>



        <!-- =================================================
             DETAIL CARD
        ================================================= -->

        <div class="sale-detail-card">


            <!-- HEADER TRANSAKSI -->

            <div class="sale-detail-header">


                <div>

                    <span class="sale-detail-label">
                        DETAIL TRANSAKSI
                    </span>

                    <h2>

                        #<?= str_pad(
                            $penjualan['id_penjualan'],
                            4,
                            '0',
                            STR_PAD_LEFT
                        ); ?>

                    </h2>

                </div>


                <div class="sale-detail-date">

                    <span>
                        TANGGAL &amp; WAKTU
                    </span>

                    <strong>

                        <?= date(
                            'd F Y, H:i:s',
                            strtotime(
                                $penjualan['tanggal_penjualan']
                            )
                        ); ?>

                    </strong>

                </div>


            </div>



            <!-- =================================================
                 CUSTOMER
            ================================================= -->

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
                            $penjualan['nama_pelanggan']
                            ?? 'Pelanggan Umum'
                        ); ?>

                    </strong>


                    <?php if (!empty($penjualan['no_telp'])): ?>

                        <small>

                            <?= htmlspecialchars(
                                $penjualan['no_telp']
                            ); ?>

                        </small>

                    <?php endif; ?>


                </div>


            </div>



            <!-- =================================================
                 TABLE PRODUK
            ================================================= -->

            <div class="sale-detail-table-wrapper">


                <table class="sale-detail-table">


                    <thead>

                        <tr>

                            <th>
                                NO
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

                                    <?= htmlspecialchars(
                                        $item['nama_produk']
                                    ); ?>

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

                                <?= number_format(
                                    $item['jumlah_produk'],
                                    0,
                                    ',',
                                    '.'
                                ); ?>

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



            <!-- =================================================
                 TOTAL
            ================================================= -->

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



        <!-- =================================================
             FOOTNOTE
        ================================================= -->

        <div class="detail-note">

            Bottle.Co &nbsp;•&nbsp;
            Detail transaksi penjualan

        </div>


    </div>


</div>


</body>

</html>