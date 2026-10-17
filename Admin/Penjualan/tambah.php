<?php

/* =========================================================
   TIMEZONE
   ========================================================= */

date_default_timezone_set('Asia/Jakarta');

session_start();


/* =========================================================
   CEK LOGIN ADMIN
   ========================================================= */

if (
    !isset($_SESSION['level']) ||
    $_SESSION['level'] !== 'admin'
) {
    header("Location: ../../login.php");
    exit;
}


/* =========================================================
   KONEKSI DATABASE
   ========================================================= */

include "../../koneksi.php";

mysqli_report(
    MYSQLI_REPORT_ERROR |
    MYSQLI_REPORT_STRICT
);


/* =========================================================
   SET TIMEZONE MYSQL
   Supaya NOW() menggunakan WIB / UTC+7
   ========================================================= */

mysqli_query(
    $koneksi,
    "SET time_zone = '+07:00'"
);


$error = "";


/* =========================================================
   DATA PELANGGAN
   ========================================================= */

$pelanggan_list = mysqli_query(
    $koneksi,
    "SELECT
        id_pelanggan,
        nama_pelanggan,
        no_telp
     FROM pelanggan
     ORDER BY nama_pelanggan ASC"
);


/* =========================================================
   DATA PRODUK
   ========================================================= */

$produk_list = mysqli_query(
    $koneksi,
    "SELECT
        id_produk,
        nama_produk,
        harga,
        stok
     FROM produk
     WHERE stok > 0
     ORDER BY nama_produk ASC"
);


/* =========================================================
   SIMPAN TRANSAKSI
   ========================================================= */

if (isset($_POST['simpan'])) {


    /* =====================================================
       PELANGGAN
       ===================================================== */

    $id_pelanggan = !empty($_POST['id_pelanggan'])
        ? (int) $_POST['id_pelanggan']
        : null;


    /* =====================================================
       PRODUK
       ===================================================== */

    $produk_ids =
        $_POST['id_produk'] ?? [];

    $jumlahs =
        $_POST['jumlah_produk'] ?? [];


    /* =====================================================
       VALIDASI PRODUK
       ===================================================== */

    if (empty($produk_ids)) {

        $error =
            "Silakan pilih minimal satu produk.";

    } else {

        try {


            /* =================================================
               MULAI TRANSAKSI DATABASE
               ================================================= */

            mysqli_begin_transaction($koneksi);


            $items = [];

            $total_harga = 0;


            /* =================================================
               CEK SEMUA PRODUK
               ================================================= */

            foreach (
                $produk_ids as $index => $id_produk
            ) {


                $id_produk =
                    (int) $id_produk;


                $jumlah =
                    (int) (
                        $jumlahs[$index] ?? 0
                    );


                /* ---------------------------------------------
                   SKIP BARIS INVALID
                   --------------------------------------------- */

                if (
                    $id_produk <= 0 ||
                    $jumlah <= 0
                ) {
                    continue;
                }


                /* =================================================
                   LOCK PRODUK
                   ================================================= */

                $stmt = mysqli_prepare(
                    $koneksi,
                    "SELECT
                        id_produk,
                        nama_produk,
                        harga,
                        stok
                     FROM produk
                     WHERE id_produk = ?
                     FOR UPDATE"
                );


                mysqli_stmt_bind_param(
                    $stmt,
                    "i",
                    $id_produk
                );


                mysqli_stmt_execute($stmt);


                $result_produk =
                    mysqli_stmt_get_result(
                        $stmt
                    );


                $produk =
                    mysqli_fetch_assoc(
                        $result_produk
                    );


                mysqli_stmt_close($stmt);


                /* =================================================
                   CEK PRODUK
                   ================================================= */

                if (!$produk) {

                    throw new Exception(
                        "Produk tidak ditemukan."
                    );
                }


                /* =================================================
                   CEK STOK
                   ================================================= */

                if (
                    $jumlah >
                    $produk['stok']
                ) {

                    throw new Exception(
                        "Stok produk \"" .
                        $produk['nama_produk'] .
                        "\" tidak mencukupi. " .
                        "Stok tersedia: " .
                        $produk['stok']
                    );
                }


                /* =================================================
                   HITUNG SUBTOTAL
                   ================================================= */

                $subtotal =
                    (float) $produk['harga']
                    * $jumlah;


                /* =================================================
                   SIMPAN ITEM
                   ================================================= */

                $items[] = [

                    'id_produk' =>
                        $id_produk,

                    'jumlah' =>
                        $jumlah,

                    'harga' =>
                        (float) $produk['harga'],

                    'subtotal' =>
                        $subtotal

                ];


                $total_harga +=
                    $subtotal;
            }


            /* =================================================
               CEK ITEM
               ================================================= */

            if (empty($items)) {

                throw new Exception(
                    "Produk yang dipilih tidak valid."
                );
            }


            /* =================================================
               SIMPAN HEADER PENJUALAN
               
               PENTING:
               NOW() = waktu transaksi sebenarnya
               menggunakan timezone MySQL +07:00
               ================================================= */

            if (
                $id_pelanggan === null
            ) {


                $stmt = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO penjualan
                    (
                        tanggal_penjualan,
                        total_harga,
                        id_pelanggan
                    )
                    VALUES
                    (
                        NOW(),
                        ?,
                        NULL
                    )"
                );


                mysqli_stmt_bind_param(
                    $stmt,
                    "d",
                    $total_harga
                );


            } else {


                $stmt = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO penjualan
                    (
                        tanggal_penjualan,
                        total_harga,
                        id_pelanggan
                    )
                    VALUES
                    (
                        NOW(),
                        ?,
                        ?
                    )"
                );


                mysqli_stmt_bind_param(
                    $stmt,
                    "di",
                    $total_harga,
                    $id_pelanggan
                );
            }


            /* =================================================
               EXECUTE HEADER
               ================================================= */

            mysqli_stmt_execute($stmt);


            /* =================================================
               AMBIL ID TRANSAKSI
               ================================================= */

            $id_penjualan =
                mysqli_insert_id(
                    $koneksi
                );


            mysqli_stmt_close($stmt);


            /* =================================================
               SIMPAN DETAIL PRODUK
               ================================================= */

            foreach (
                $items as $item
            ) {


                /* ---------------------------------------------
                   INSERT DETAIL
                   --------------------------------------------- */

                $stmt = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO detailpenjualan
                    (
                        id_penjualan,
                        id_produk,
                        jumlah_produk,
                        subtotal
                    )
                    VALUES
                    (
                        ?,
                        ?,
                        ?,
                        ?
                    )"
                );


                mysqli_stmt_bind_param(
                    $stmt,
                    "iiid",
                    $id_penjualan,
                    $item['id_produk'],
                    $item['jumlah'],
                    $item['subtotal']
                );


                mysqli_stmt_execute($stmt);


                mysqli_stmt_close($stmt);


                /* ---------------------------------------------
                   KURANGI STOK
                   --------------------------------------------- */

                $stmt = mysqli_prepare(
                    $koneksi,
                    "UPDATE produk
                     SET stok = stok - ?
                     WHERE id_produk = ?"
                );


                mysqli_stmt_bind_param(
                    $stmt,
                    "ii",
                    $item['jumlah'],
                    $item['id_produk']
                );


                mysqli_stmt_execute($stmt);


                mysqli_stmt_close($stmt);
            }


            /* =================================================
               COMMIT
               ================================================= */

            mysqli_commit($koneksi);


            /* =================================================
               REDIRECT KE DETAIL TRANSAKSI
               ================================================= */

            header(
                "Location: ../detail-penjualan/detail.php?id=" .
                $id_penjualan
            );

            exit;


        } catch (Exception $e) {


            /* =================================================
               ROLLBACK JIKA ERROR
               ================================================= */

            mysqli_rollback($koneksi);


            $error =
                $e->getMessage();
        }
    }
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
    Transaksi Baru | Bottle.Co
</title>


<style>

/* =========================================================
   RESET
========================================================= */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    font-family: "Segoe UI", Arial, sans-serif;
    background: #F7F9F5;
    color: #26332A;
    font-size: 14px;
}


/* =========================================================
   PAGE
========================================================= */

.sale-page {
    width: 100%;
    min-height: 100vh;
    padding: 38px 32px 50px;
}

.sale-container {
    width: 100%;
    max-width: 1120px;
    margin: 0 auto;
}


/* =========================================================
   BACK
========================================================= */

.sale-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 18px;

    color: #7FA88C;
    font-size: 14px;
    font-weight: 600;

    text-decoration: none;
}

.sale-back:hover {
    color: #66846F;
}


/* =========================================================
   MAIN CARD
========================================================= */

.sale-card {
    width: 100%;

    background: #ffffff;

    border: 1px solid #E1E8E2;

    border-radius: 16px;

    overflow: hidden;

    box-shadow:
        0 8px 28px rgba(38, 51, 42, .055);
}


/* =========================================================
   PAGE TITLE
========================================================= */

.sale-header {
    min-height: 118px;

    padding: 28px 32px;

    display: flex;
    align-items: center;

    gap: 16px;

    border-bottom: 1px solid #E1E8E2;
}

.sale-header-icon {
    width: 50px;
    height: 50px;

    flex: 0 0 50px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 12px;

    background: #EAF2EC;

    color: #7FA88C;

    font-size: 23px;
    font-weight: 600;
}

.sale-header-text {
    min-width: 0;
}

.sale-header-label {
    display: block;

    margin-bottom: 5px;

    color: #7FA88C;

    font-size: 11px;
    font-weight: 700;

    letter-spacing: .8px;
}

.sale-header h1 {
    margin: 0;

    color: #26332A;

    font-size: 26px;
    font-weight: 700;

    line-height: 1.25;
}

.sale-header p {
    margin-top: 5px;

    color: #78837B;

    font-size: 14px;

    line-height: 1.5;
}


/* =========================================================
   ERROR
========================================================= */

.sale-error {
    margin: 22px 32px 0;

    padding: 13px 16px;

    background: #fff5f5;

    border: 1px solid #fecaca;

    border-radius: 9px;

    color: #b42318;

    font-size: 14px;
}


/* =========================================================
   FORM
========================================================= */

.sale-form {
    padding: 30px 32px 32px;
}


/* =========================================================
   INFO TRANSAKSI
========================================================= */

.sale-info-grid {
    width: 100%;

    display: grid;

    grid-template-columns:
        minmax(0, 1fr)
        minmax(0, 1fr);

    gap: 20px;

    margin-bottom: 32px;
}

.form-group {
    width: 100%;
}

.form-group label {
    display: block;

    margin-bottom: 8px;

    color: #3F4A43;

    font-size: 14px;
    font-weight: 600;
}

.form-group input,
.form-group select {

    width: 100%;
    height: 46px;

    padding: 0 14px;

    border: 1px solid #D5DED7;

    border-radius: 9px;

    background: #ffffff;

    color: #26332A;

    font-family: inherit;

    font-size: 14px;

    outline: none;

    transition: .2s ease;
}

.form-group input:focus,
.form-group select:focus {

    border-color: #7FA88C;

    box-shadow:
        0 0 0 3px rgba(127, 168, 140, .09);
}


/* =========================================================
   PRODUCT SECTION HEADER
========================================================= */

.product-heading {

    width: 100%;

    min-height: 62px;

    padding-bottom: 16px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    border-bottom: 1px solid #E1E8E2;
}

.product-heading-label {
    display: block;

    margin-bottom: 4px;

    color: #7FA88C;

    font-size: 11px;

    font-weight: 700;

    letter-spacing: .8px;
}

.product-heading h2 {

    margin: 0;

    color: #26332A;

    font-size: 18px;

    font-weight: 700;
}


/* =========================================================
   ADD PRODUCT
========================================================= */

.btn-add {

    height: 40px;

    padding: 0 17px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    flex-shrink: 0;

    border: 0;

    border-radius: 8px;

    background: #7FA88C;

    color: #ffffff;

    font-family: inherit;

    font-size: 13px;

    font-weight: 600;

    cursor: pointer;

    transition: .2s ease;
}

.btn-add:hover {
    background: #66846F;
}


/* =========================================================
   PRODUCT LIST
========================================================= */

.product-list {

    width: 100%;

    padding-top: 18px;

    display: flex;

    flex-direction: column;

    gap: 12px;
}


/* =========================================================
   PRODUCT ROW
========================================================= */

.product-row {

    width: 100%;

    display: grid;

    grid-template-columns:
        minmax(250px, 1fr)
        145px
        105px
        155px
        40px;

    gap: 12px;

    align-items: end;

    padding: 17px;

    background: #FAFCF9;

    border: 1px solid #E1E8E2;

    border-radius: 11px;

    transition: .2s ease;
}

.product-row:hover {
    border-color: #D5DED7;

    background: #FCFDFC;
}

.product-field {
    width: 100%;

    min-width: 0;
}

.product-field label {

    display: block;

    margin-bottom: 7px;

    color: #78837B;

    font-size: 12px;

    font-weight: 600;
}


/* =========================================================
   PRODUCT SELECT
========================================================= */

.product-select {

    width: 100%;
    height: 43px;

    padding: 0 12px;

    border: 1px solid #D5DED7;

    border-radius: 8px;

    background: #ffffff;

    color: #26332A;

    font-family: inherit;

    font-size: 13px;

    outline: none;
}

.product-select:focus {

    border-color: #7FA88C;

    box-shadow:
        0 0 0 3px rgba(127, 168, 140, .08);
}


/* =========================================================
   PRICE
========================================================= */

.price-box {

    width: 100%;
    height: 43px;

    padding: 0 12px;

    display: flex;

    align-items: center;

    border: 1px solid #D5DED7;

    border-radius: 8px;

    background: #F7F9F5;

    color: #52665B;

    font-size: 13px;

    white-space: nowrap;

    overflow: hidden;
}


/* =========================================================
   QUANTITY
========================================================= */

.quantity-input {

    width: 100%;
    height: 43px;

    padding: 0 10px;

    border: 1px solid #D5DED7;

    border-radius: 8px;

    background: #ffffff;

    color: #26332A;

    font-family: inherit;

    font-size: 14px;

    text-align: center;

    outline: none;
}

.quantity-input:focus {

    border-color: #7FA88C;

    box-shadow:
        0 0 0 3px rgba(127, 168, 140, .08);
}


/* =========================================================
   SUBTOTAL
========================================================= */

.subtotal-box {

    width: 100%;
    height: 43px;

    padding: 0 12px;

    display: flex;

    align-items: center;

    border: 1px solid #D5DED7;

    border-radius: 8px;

    background: #EAF2EC;

    color: #66846F;

    font-size: 13px;

    font-weight: 700;

    white-space: nowrap;

    overflow: hidden;
}


/* =========================================================
   REMOVE
========================================================= */

.btn-remove {

    width: 40px;
    height: 43px;

    padding: 0;

    display: flex;

    align-items: center;
    justify-content: center;

    border: 1px solid #fecaca;

    border-radius: 8px;

    background: #fff5f5;

    color: #dc2626;

    font-size: 19px;

    cursor: pointer;

    transition: .2s ease;
}

.btn-remove:hover {

    background: #fee2e2;

    border-color: #fca5a5;
}


/* =========================================================
   TOTAL
========================================================= */

.total-box {

    width: 100%;

    margin-top: 24px;

    padding: 20px 22px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    border: 1px solid #D5DED7;

    border-radius: 11px;

    background: #F7F9F5;
}

.total-label {

    color: #52665B;

    font-size: 15px;

    font-weight: 600;
}

.total-value {

    color: #7FA88C;

    font-size: 25px;

    font-weight: 700;

    white-space: nowrap;
}


/* =========================================================
   ACTION
========================================================= */

.form-actions {

    width: 100%;

    margin-top: 25px;

    padding-top: 23px;

    display: flex;

    align-items: center;

    justify-content: flex-end;

    gap: 10px;

    border-top: 1px solid #E1E8E2;
}


/* =========================================================
   CANCEL
========================================================= */

.btn-cancel {

    min-height: 43px;

    padding: 0 20px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    border: 1px solid #D5DED7;

    border-radius: 8px;

    background: #ffffff;

    color: #52665B;

    font-family: inherit;

    font-size: 13px;

    font-weight: 600;

    text-decoration: none;

    transition: .2s ease;
}

.btn-cancel:hover {

    background: #F7F9F5;

    border-color: #BFD0C3;
}


/* =========================================================
   SAVE
========================================================= */

.btn-save {

    min-height: 43px;

    padding: 0 22px;

    border: 0;

    border-radius: 8px;

    background: #7FA88C;

    color: #ffffff;

    font-family: inherit;

    font-size: 13px;

    font-weight: 600;

    cursor: pointer;

    box-shadow:
        0 4px 10px rgba(127, 168, 140, .15);

    transition: .2s ease;
}

.btn-save:hover {

    background: #66846F;

    transform: translateY(-1px);
}


/* =========================================================
   FOOTER DIHILANGKAN
========================================================= */

.sale-footer {
    display: none;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 950px) {

    .sale-page {
        padding: 28px 20px 40px;
    }

    .product-row {

        grid-template-columns:
            minmax(220px, 1fr)
            130px
            95px
            140px
            40px;

    }

}


@media (max-width: 800px) {

    .sale-info-grid {
        grid-template-columns: 1fr;
    }

    .product-row {

        grid-template-columns:
            1fr 1fr;

        gap: 14px;
    }

    .product-field:first-child {
        grid-column: 1 / -1;
    }

    .btn-remove {
        width: 100%;
    }

}


@media (max-width: 600px) {

    .sale-page {
        padding: 18px 12px 30px;
    }

    .sale-header {
        padding: 24px 20px;

        align-items: flex-start;
    }

    .sale-header-icon {
        width: 44px;
        height: 44px;
        flex-basis: 44px;
    }

    .sale-header h1 {
        font-size: 22px;
    }

    .sale-header p {
        font-size: 13px;
    }

    .sale-form {
        padding: 24px 20px;
    }

    .sale-error {
        margin-left: 20px;
        margin-right: 20px;
    }

    .product-heading {
        align-items: flex-start;
        flex-direction: column;
    }

    .btn-add {
        width: 100%;
    }

    .product-row {
        grid-template-columns: 1fr;
    }

    .product-field:first-child {
        grid-column: auto;
    }

    .total-box {
        align-items: flex-start;
        flex-direction: column;
    }

    .total-value {
        font-size: 22px;
    }

    .form-actions {
        flex-direction: column-reverse;
    }

    .btn-cancel,
    .btn-save {
        width: 100%;
    }

}

</style>

</head>


<body>


<div class="sale-page">

    <div class="sale-container">


        <!-- =================================================
             BACK
             ================================================= -->

        <a
            href="index.php"
            class="sale-back"
        >
            ← Kembali ke Penjualan
        </a>


        <!-- =================================================
             CARD
             ================================================= -->

        <section class="sale-card">


            <!-- HEADER -->

            <div class="sale-header">

                <div class="sale-header-icon">
                    ＋
                </div>

                <div class="sale-header-text">

                    <span class="sale-header-label">
                        TRANSAKSI PENJUALAN
                    </span>

                    <h1>
                        Transaksi Baru
                    </h1>

                    <p>
                        Buat transaksi penjualan baru.
                    </p>

                </div>

            </div>


            <!-- ERROR -->

            <?php if ($error !== ""): ?>

                <div class="sale-error">

                    <?= htmlspecialchars($error); ?>

                </div>

            <?php endif; ?>


            <!-- FORM -->

            <form
                method="POST"
                id="saleForm"
                class="sale-form"
                autocomplete="off"
            >


                <!-- =================================================
                     TANGGAL + PELANGGAN
                     ================================================= -->

                <div class="sale-info-grid">


                    <div class="form-group">

                        <label for="tanggal_penjualan">
                            Tanggal Penjualan
                        </label>

                        <input
                            type="date"
                            id="tanggal_penjualan"
                            name="tanggal_penjualan"
                            value="<?= date('Y-m-d'); ?>"
                            readonly
                        >

                    </div>


                    <div class="form-group">

                        <label for="id_pelanggan">
                            Pelanggan
                        </label>

                        <select
                            name="id_pelanggan"
                            id="id_pelanggan"
                        >

                            <option value="">
                                Pelanggan Umum
                            </option>


                            <?php while (
                                $pelanggan =
                                mysqli_fetch_assoc(
                                    $pelanggan_list
                                )
                            ): ?>

                                <option
                                    value="<?= $pelanggan['id_pelanggan']; ?>"
                                    <?= (
                                        (
                                            $_POST['id_pelanggan']
                                            ?? ''
                                        ) ==
                                        $pelanggan['id_pelanggan']
                                    )
                                        ? 'selected'
                                        : ''; ?>
                                >

                                    <?= htmlspecialchars(
                                        $pelanggan[
                                            'nama_pelanggan'
                                        ]
                                    ); ?>

                                    <?php if (
                                        !empty(
                                            $pelanggan['no_telp']
                                        )
                                    ): ?>

                                        —
                                        <?= htmlspecialchars(
                                            $pelanggan['no_telp']
                                        ); ?>

                                    <?php endif; ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>

                </div>


                <!-- =================================================
                     PRODUCT HEADER
                     ================================================= -->

                <div class="product-heading">

                    <div>

                        <span class="product-heading-label">
                            DAFTAR PRODUK
                        </span>

                        <h2>
                            Produk yang Dibeli
                        </h2>

                    </div>


                    <button
                        type="button"
                        class="btn-add"
                        onclick="addProduct()"
                    >
                        ＋ Tambah Produk
                    </button>

                </div>


                <!-- =================================================
                     PRODUCT LIST
                     ================================================= -->

                <div
                    id="productContainer"
                    class="product-list"
                >


                    <div class="product-row">


                        <!-- PRODUK -->

                        <div class="product-field">

                            <label>
                                Produk
                            </label>

                            <select
                                name="id_produk[]"
                                class="product-select"
                                onchange="updatePrice(this)"
                                required
                            >

                                <option value="">
                                    Pilih produk...
                                </option>


                                <?php

                                mysqli_data_seek(
                                    $produk_list,
                                    0
                                );

                                while (
                                    $produk =
                                    mysqli_fetch_assoc(
                                        $produk_list
                                    )
                                ):

                                ?>

                                    <option
                                        value="<?= $produk['id_produk']; ?>"
                                        data-price="<?= $produk['harga']; ?>"
                                        data-stock="<?= $produk['stok']; ?>"
                                    >

                                        <?= htmlspecialchars(
                                            $produk['nama_produk']
                                        ); ?>

                                        —
                                        Rp
                                        <?= number_format(
                                            $produk['harga'],
                                            0,
                                            ',',
                                            '.'
                                        ); ?>

                                        (Stok
                                        <?= $produk['stok']; ?>)

                                    </option>

                                <?php endwhile; ?>

                            </select>

                        </div>


                        <!-- HARGA -->

                        <div class="product-field">

                            <label>
                                Harga
                            </label>

                            <div class="price-box">
                                Rp 0
                            </div>

                        </div>


                        <!-- JUMLAH -->

                        <div class="product-field">

                            <label>
                                Jumlah
                            </label>

                            <input
                                type="number"
                                name="jumlah_produk[]"
                                class="quantity-input"
                                min="1"
                                value="1"
                                onchange="calculateRow(this)"
                                oninput="calculateRow(this)"
                                required
                            >

                        </div>


                        <!-- SUBTOTAL -->

                        <div class="product-field">

                            <label>
                                Subtotal
                            </label>

                            <div class="subtotal-box">
                                Rp 0
                            </div>

                        </div>


                        <!-- HAPUS -->

                        <button
                            type="button"
                            class="btn-remove"
                            onclick="removeProduct(this)"
                            title="Hapus produk"
                            style="visibility:hidden;"
                        >
                            ×
                        </button>


                    </div>

                </div>


                <!-- =================================================
                     TOTAL
                     ================================================= -->

                <div class="total-box">

                    <span class="total-label">
                        Total Pembayaran
                    </span>


                    <strong
                        id="grandTotal"
                        class="total-value"
                    >
                        Rp 0
                    </strong>

                </div>


                <!-- =================================================
                     ACTION
                     ================================================= -->

                <div class="form-actions">

                    <a
                        href="index.php"
                        class="btn-cancel"
                    >
                        Batal
                    </a>


                    <button
                        type="submit"
                        name="simpan"
                        class="btn-save"
                    >
                        ✓ Simpan Transaksi
                    </button>

                </div>


            </form>

        </section>


        <div class="sale-footer">
            Bottle.Co &nbsp;•&nbsp; Sistem Informasi Penjualan
        </div>


    </div>

</div>


<script>

/* =========================================================
   FORMAT RUPIAH
   ========================================================= */

function formatRupiah(number) {

    return new Intl.NumberFormat('id-ID')
        .format(number);

}


/* =========================================================
   UPDATE HARGA
   ========================================================= */

function updatePrice(select) {

    const row =
        select.closest('.product-row');

    const option =
        select.options[
            select.selectedIndex
        ];

    const price =
        Number(
            option.dataset.price || 0
        );

    const stock =
        Number(
            option.dataset.stock || 0
        );

    const priceElement =
        row.querySelector(
            '.price-box'
        );

    const quantityInput =
        row.querySelector(
            '.quantity-input'
        );


    priceElement.textContent =
        'Rp ' +
        formatRupiah(price);


    quantityInput.max =
        stock;


    if (
        Number(
            quantityInput.value
        ) > stock
    ) {

        quantityInput.value =
            stock || 1;
    }


    calculateRow(
        quantityInput
    );


    validateDuplicateProducts();

}


/* =========================================================
   HITUNG SUBTOTAL
   ========================================================= */

function calculateRow(input) {

    const row =
        input.closest(
            '.product-row'
        );

    const select =
        row.querySelector(
            '.product-select'
        );

    const option =
        select.options[
            select.selectedIndex
        ];

    const price =
        Number(
            option?.dataset.price || 0
        );

    let quantity =
        Number(
            input.value || 0
        );

    const stock =
        Number(
            option?.dataset.stock || 0
        );


    if (
        stock > 0 &&
        quantity > stock
    ) {

        quantity =
            stock;

        input.value =
            stock;
    }


    const subtotal =
        price * quantity;


    row.querySelector(
        '.subtotal-box'
    ).textContent =
        'Rp ' +
        formatRupiah(
            subtotal
        );


    calculateGrandTotal();

}


/* =========================================================
   TOTAL
========================================================= */

function calculateGrandTotal() {

    let total = 0;


    document
        .querySelectorAll(
            '.product-row'
        )
        .forEach(
            function(row) {

                const select =
                    row.querySelector(
                        '.product-select'
                    );

                const option =
                    select.options[
                        select.selectedIndex
                    ];

                const price =
                    Number(
                        option?.dataset.price ||
                        0
                    );

                const quantity =
                    Number(
                        row.querySelector(
                            '.quantity-input'
                        ).value || 0
                    );


                total +=
                    price *
                    quantity;

            }
        );


    document.getElementById(
        'grandTotal'
    ).textContent =
        'Rp ' +
        formatRupiah(total);

}


/* =========================================================
   TAMBAH PRODUK
   ========================================================= */

function addProduct() {

    const container =
        document.getElementById(
            'productContainer'
        );

    const firstRow =
        container.querySelector(
            '.product-row'
        );


    const newRow =
        firstRow.cloneNode(
            true
        );


    newRow.querySelector(
        '.product-select'
    ).value = '';


    newRow.querySelector(
        '.quantity-input'
    ).value = 1;


    newRow.querySelector(
        '.quantity-input'
    ).removeAttribute(
        'max'
    );


    newRow.querySelector(
        '.price-box'
    ).textContent =
        'Rp 0';


    newRow.querySelector(
        '.subtotal-box'
    ).textContent =
        'Rp 0';


    newRow.querySelector(
        '.btn-remove'
    ).style.visibility =
        'visible';


    container.appendChild(
        newRow
    );


    updateRemoveButtons();

}


/* =========================================================
   HAPUS PRODUK
   ========================================================= */

function removeProduct(button) {

    const rows =
        document.querySelectorAll(
            '.product-row'
        );


    if (
        rows.length <= 1
    ) {

        alert(
            'Minimal harus ada satu produk.'
        );

        return;
    }


    button
        .closest(
            '.product-row'
        )
        .remove();


    updateRemoveButtons();

    calculateGrandTotal();

    validateDuplicateProducts();

}


/* =========================================================
   TOMBOL HAPUS
   ========================================================= */

function updateRemoveButtons() {

    const rows =
        document.querySelectorAll(
            '.product-row'
        );


    rows.forEach(
        function(row) {

            const button =
                row.querySelector(
                    '.btn-remove'
                );


            button.style.visibility =
                rows.length > 1
                    ? 'visible'
                    : 'hidden';

        }
    );

}


/* =========================================================
   CEK DUPLIKAT
   ========================================================= */

function validateDuplicateProducts() {

    const selects =
        document.querySelectorAll(
            '.product-select'
        );

    const selected = [];

    let duplicate = false;


    selects.forEach(
        function(select) {

            if (
                select.value !== ''
            ) {

                if (
                    selected.includes(
                        select.value
                    )
                ) {

                    duplicate =
                        true;
                }


                selected.push(
                    select.value
                );
            }

        }
    );


    return !duplicate;

}


/* =========================================================
   VALIDASI SUBMIT
   ========================================================= */

document
    .getElementById(
        'saleForm'
    )
    .addEventListener(
        'submit',
        function(event) {


            /* ---------------------------------------------
               DUPLIKAT
               --------------------------------------------- */

            if (
                !validateDuplicateProducts()
            ) {

                event.preventDefault();

                alert(
                    'Produk yang sama tidak boleh ' +
                    'ditambahkan dua kali. ' +
                    'Silakan ubah jumlah produk tersebut.'
                );

                return;
            }


            /* ---------------------------------------------
               CEK PRODUK KOSONG
               --------------------------------------------- */

            const selects =
                document.querySelectorAll(
                    '.product-select'
                );

            let valid = true;


            selects.forEach(
                function(select) {

                    if (
                        select.value === ''
                    ) {

                        valid =
                            false;
                    }

                }
            );


            if (!valid) {

                event.preventDefault();

                alert(
                    'Silakan pilih produk pada setiap baris.'
                );

            }

        }
    );


/* =========================================================
   INITIAL
   ========================================================= */

updateRemoveButtons();

calculateGrandTotal();

</script>


</body>

</html>