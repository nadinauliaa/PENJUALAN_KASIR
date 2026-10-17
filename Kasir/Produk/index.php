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
   PENCARIAN PRODUK
========================= */

$cari = isset($_GET['cari'])
    ? trim($_GET['cari'])
    : '';

$where = '';

if ($cari !== '') {

    $cariAman = mysqli_real_escape_string(
        $koneksi,
        $cari
    );

    $where = "
        WHERE nama_produk LIKE '%$cariAman%'
    ";
}


/* =========================
   DATA PRODUK
========================= */

$queryProduk = mysqli_query(
    $koneksi,
    "
    SELECT
        id_produk,
        nama_produk,
        harga,
        stok
    FROM produk
    $where
    ORDER BY nama_produk ASC
    "
);


/* =========================
   TOTAL PRODUK
========================= */

$queryTotal = mysqli_query(
    $koneksi,
    "
    SELECT COUNT(*) AS total
    FROM produk
    $where
    "
);

$dataTotal = mysqli_fetch_assoc($queryTotal);

$totalProduk = (int) $dataTotal['total'];


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


/* =========================
   CARI GAMBAR PRODUK
========================= */

function gambarProduk($idProduk)
{
    $folder = "../../assets/img/produk/";

    $extensions = [
        'jpg',
        'jpeg',
        'png',
        'webp'
    ];

    foreach ($extensions as $extension) {

        $file = $folder . $idProduk . "." . $extension;

        if (file_exists($file)) {

            return "/PENJUALAN/assets/img/produk/"
                . $idProduk
                . "."
                . $extension;
        }
    }

    return null;
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

    <title>Data Produk - Bottle.Co</title>

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
        href="/PENJUALAN/assets/css/produk_kasir.css?v=1"
    >

</head>




<!-- =====================================================
     CONTENT
===================================================== -->

<main>

    <div class="produk-kasir-container">


        <!-- HEADER -->

        <section class="produk-kasir-header">

            <a
                href="../index.php"
                class="produk-kasir-back"
            >
                ← Kembali ke Dashboard
            </a>


            <span class="produk-kasir-label">
                DATA PRODUK
            </span>


            <div class="produk-kasir-title-row">

                <div>

                    <h1>
                        Produk
                    </h1>

                    <p>
                        Daftar produk, harga, dan ketersediaan
                        stok dalam sistem.
                    </p>

                </div>


                <div class="produk-total">

                    <span>
                        Total Produk
                    </span>

                    <strong>
                        <?= number_format($totalProduk); ?>
                    </strong>

                </div>

            </div>

        </section>



        <!-- =================================================
             CARD PRODUK
        ================================================= -->

        <section class="produk-kasir-card">


            <!-- SEARCH -->

            <div class="produk-search">

                <form
                    method="GET"
                    action=""
                >

                    <div class="produk-search-wrapper">

                        <span class="produk-search-icon">
                            🔎
                        </span>

                        <input
                            type="text"
                            name="cari"
                            value="<?= htmlspecialchars($cari); ?>"
                            placeholder="Cari nama produk..."
                        >

                    </div>


                    <button
                        type="submit"
                        class="btn btn-primary btn-produk-cari"
                    >
                        Cari
                    </button>


                    <?php if ($cari !== ''): ?>

                        <a
                            href="index.php"
                            class="btn-produk-reset"
                        >
                            Reset
                        </a>

                    <?php endif; ?>

                </form>

            </div>



            <!-- PRODUCT GRID -->

            <div class="produk-grid">

                <?php if (
                    mysqli_num_rows($queryProduk) > 0
                ): ?>

                    <?php while (
                        $produk =
                        mysqli_fetch_assoc($queryProduk)
                    ): ?>

                        <?php

                        $idProduk = (int) $produk['id_produk'];

                        $gambar = gambarProduk(
                            $idProduk
                        );

                        $stok = (int) $produk['stok'];

                        ?>

                        <article class="produk-card">


                            <!-- GAMBAR -->

                            <div class="produk-image">

                                <?php if ($gambar !== null): ?>

                                    <img
                                        src="<?= htmlspecialchars($gambar); ?>"
                                        alt="<?= htmlspecialchars($produk['nama_produk']); ?>"
                                    >

                                <?php else: ?>

                                    <div class="produk-image-placeholder">

                                        <span>
                                            📦
                                        </span>

                                        <small>
                                            Tidak ada gambar
                                        </small>

                                    </div>

                                <?php endif; ?>

                            </div>



                            <!-- INFORMASI -->

                            <div class="produk-card-body">


                                <span class="produk-kode">
                                    PRODUK #<?= $idProduk; ?>
                                </span>


                                <h2>
                                    <?= htmlspecialchars(
                                        $produk['nama_produk']
                                    ); ?>
                                </h2>


                                <strong class="produk-harga">
                                    <?= rupiah(
                                        $produk['harga']
                                    ); ?>
                                </strong>


                                <div class="produk-stock-row">

                                    <span>
                                        Stok
                                    </span>


                                    <?php if ($stok <= 0): ?>

                                        <strong class="stok-habis">
                                            Habis
                                        </strong>

                                    <?php elseif ($stok <= 5): ?>

                                        <strong class="stok-sedikit">
                                            <?= $stok; ?> tersedia
                                        </strong>

                                    <?php else: ?>

                                        <strong class="stok-tersedia">
                                            <?= $stok; ?> tersedia
                                        </strong>

                                    <?php endif; ?>

                                </div>

                            </div>

                        </article>

                    <?php endwhile; ?>

                <?php else: ?>


                    <!-- EMPTY -->

                    <div class="produk-empty">

                        <div class="produk-empty-icon">
                            📦
                        </div>

                        <strong>

                            <?php if ($cari !== ''): ?>

                                Produk tidak ditemukan

                            <?php else: ?>

                                Belum ada produk

                            <?php endif; ?>

                        </strong>

                        <span>

                            <?php if ($cari !== ''): ?>

                                Coba gunakan nama produk
                                yang berbeda.

                            <?php else: ?>

                                Belum ada data produk
                                yang tersimpan dalam sistem.

                            <?php endif; ?>

                        </span>

                    </div>


                <?php endif; ?>

            </div>


        </section>


    </div>

</main>


<script src="/PENJUALAN/assets/js/bootstrap.bundle.min.js"></script>

</body>

</html>