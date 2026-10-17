<?php

session_start();

if (!isset($_SESSION['id_user'])) {
    header("Location: ../../login.php");
    exit;
}

include "../../koneksi.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


/* =========================================================
   DATA PRODUK
========================================================= */

$query = mysqli_query(
    $koneksi,
    "SELECT id_produk, nama_produk, harga, stok
     FROM produk
     ORDER BY id_produk DESC"
);

$totalProduk = mysqli_num_rows($query);


/* TOTAL STOK */

$stokQuery = mysqli_query(
    $koneksi,
    "SELECT COALESCE(SUM(stok), 0) AS total_stok
     FROM produk"
);

$stokData = mysqli_fetch_assoc($stokQuery);

$totalStok = (int) $stokData['total_stok'];


/* PRODUK TERSEDIA */

$tersediaQuery = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS jumlah
     FROM produk
     WHERE stok > 0"
);

$tersediaData = mysqli_fetch_assoc($tersediaQuery);

$totalTersedia = (int) $tersediaData['jumlah'];


/* NAMA ADMIN */

$namaAdmin = $_SESSION['nama'] ?? $_SESSION['username'] ?? 'Administrator';


/* =========================================================
   FUNGSI CARI GAMBAR PRODUK
========================================================= */

function getProductImage($id_produk)
{
    // Sesuaikan path folder relatif terhadap file index.php di admin/produk/
    // Jika folder assets ada di root utama (PENJUALAN/assets/), gunakan "../../assets/img/produk/"
    $folder = "../../assets/img/produk/";

    $extensions = [
        'jpg',
        'jpeg',
        'png',
        'webp'
    ];

    foreach ($extensions as $extension) {
        $file = $folder . $id_produk . "." . $extension;

        // Cek menggunakan file_exists dengan path fisik server (__DIR__) agar lebih akurat
        $physicalPath = __DIR__ . "/../../assets/img/produk/" . $id_produk . "." . $extension;

        if (file_exists($physicalPath)) {
            return $file;
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

    <title>Produk | Bottle.Co</title>


    <!-- BOOTSTRAP -->

    <link
        rel="stylesheet"
        href="../../assets/css/bootstrap.min.css"
    >


    <link
        rel="stylesheet"
        href="../../assets/css/admin.css"
    >

    <link
        rel="stylesheet"
        href="../../assets/css/produk.css"
    >

</head>


<body>


<!-- =====================================================
     SIDEBAR
     SAMA PERSIS DENGAN KODE KAMU (TIDAK DIUBAH)
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


    <!-- CONTENT SIDEBAR -->

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
                href="index.php"
                class="admin-menu-item active"
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
     WRAPPER
===================================================== -->

<div class="admin-wrapper">


    <!-- =================================================
         TOPBAR
    ================================================== -->

    <header class="admin-topbar">


        <div class="topbar-left">

            <div class="breadcrumb-small">
                DATA MASTER
            </div>

            <h1>
                Produk
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


        <div class="product-content">


            <!-- INTRO -->

            <section class="product-intro">


                <div class="intro-left">


                    <div class="intro-icon">
                        📦
                    </div>


                    <div>

                        <span class="intro-label">
                            DATA PRODUK
                        </span>

                        <h2>
                            Kelola Produk
                        </h2>

                        <p>
                            Kelola data produk toko dengan mudah.
                        </p>

                    </div>


                </div>


                <a
                    href="tambah.php"
                    class="btn-add-product"
                >

                    <span>
                        ＋
                    </span>

                    Tambah Produk

                </a>


            </section>



            <!-- STATISTIK -->

            <section class="product-summary">


                <div class="summary-card">

                    <div class="summary-icon">
                        📦
                    </div>

                    <div>

                        <span>
                            TOTAL PRODUK
                        </span>

                        <strong>
                            <?= $totalProduk; ?>
                        </strong>

                    </div>

                </div>


                <div class="summary-card">

                    <div class="summary-icon">
                        📊
                    </div>

                    <div>

                        <span>
                            TOTAL STOK
                        </span>

                        <strong>
                            <?= number_format(
                                $totalStok,
                                0,
                                ',',
                                '.'
                            ); ?>
                        </strong>

                    </div>

                </div>


                <div class="summary-card">

                    <div class="summary-icon">
                        ✓
                    </div>

                    <div>

                        <span>
                            PRODUK TERSEDIA
                        </span>

                        <strong>
                            <?= $totalTersedia; ?>
                        </strong>

                    </div>

                </div>


            </section>



            <!-- TABLE -->

            <section class="product-section">


                <div class="product-box">


                    <div class="product-box-header">


                        <div>

                            <span class="table-label">
                                DATABASE PRODUK
                            </span>

                            <h2>
                                Daftar Produk
                            </h2>

                        </div>


                        <span class="table-count">

                            <?= $totalProduk; ?>

                            Data

                        </span>


                    </div>



                    <div class="table-responsive">


                        <table class="product-table">


                            <thead>

                                <tr>

                                    <th class="col-no">
                                        NO
                                    </th>

                                    <th>
                                        PRODUK
                                    </th>

                                    <th>
                                        HARGA
                                    </th>

                                    <th>
                                        STOK
                                    </th>

                                    <th class="col-action">
                                        AKSI
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                            <?php if ($totalProduk > 0): ?>


                                <?php

                                $no = 1;

                                while (
                                    $row =
                                    mysqli_fetch_assoc($query)
                                ):

                                    $gambarProduk =
                                        getProductImage(
                                            $row['id_produk']
                                        );

                                ?>


                                <tr>


                                    <!-- NOMOR -->

                                    <td class="number-cell">

                                        <?= $no++; ?>

                                    </td>



                                    <!-- PRODUK -->

                                    <td>

                                        <div class="product-name-cell">


                                            <div class="product-avatar">

                                                <?php if ($gambarProduk): ?>

                                                    <img
                                                        src="<?= htmlspecialchars($gambarProduk); ?>"
                                                        alt="<?= htmlspecialchars($row['nama_produk']); ?>"
                                                    >

                                                <?php else: ?>

                                                    📦

                                                <?php endif; ?>

                                            </div>


                                            <div class="product-info">

                                                <strong>

                                                    <?= htmlspecialchars(
                                                        $row['nama_produk']
                                                    ); ?>

                                                </strong>

                                                <span>

                                                    ID #<?= $row['id_produk']; ?>

                                                </span>

                                            </div>


                                        </div>

                                    </td>



                                    <!-- HARGA -->

                                    <td class="price-cell">

                                        Rp

                                        <?= number_format(
                                            (float)$row['harga'],
                                            0,
                                            ',',
                                            '.'
                                        ); ?>

                                    </td>



                                    <!-- STOK -->

                                    <td>


                                        <?php

                                        $stok =
                                            (int)$row['stok'];


                                        if ($stok <= 0) {

                                            $stockClass =
                                                'stock-empty';

                                            $stockText =
                                                'Habis';

                                        } elseif ($stok <= 5) {

                                            $stockClass =
                                                'stock-low';

                                            $stockText =
                                                'Stok rendah';

                                        } else {

                                            $stockClass =
                                                'stock-safe';

                                            $stockText =
                                                'Tersedia';

                                        }

                                        ?>


                                        <div class="stock-wrapper">


                                            <strong>
                                                <?= $stok; ?>
                                            </strong>


                                            <span
                                                class="
                                                    stock-badge
                                                    <?= $stockClass; ?>
                                                "
                                            >

                                                <?= $stockText; ?>

                                            </span>


                                        </div>


                                    </td>



                                    <!-- AKSI -->

                                    <td>


                                        <div class="action-buttons">


                                            <a
                                                href="edit.php?id=<?= $row['id_produk']; ?>"
                                                class="
                                                    btn-action
                                                    btn-edit
                                                "
                                            >

                                                ✎ Edit

                                            </a>


                                            <a
                                                href="hapus.php?id=<?= $row['id_produk']; ?>"
                                                class="
                                                    btn-action
                                                    btn-delete
                                                "
                                                onclick="
                                                    return confirm(
                                                        'Yakin ingin menghapus produk ini?'
                                                    );
                                                "
                                            >

                                                🗑 Hapus

                                            </a>


                                        </div>


                                    </td>


                                </tr>


                                <?php endwhile; ?>


                            <?php else: ?>


                                <tr>

                                    <td colspan="5">


                                        <div class="empty-data">


                                            <div class="empty-data-icon">
                                                📦
                                            </div>


                                            <h3>
                                                Belum Ada Produk
                                            </h3>


                                            <p>
                                                Belum ada produk yang tersimpan.
                                            </p>


                                            <a
                                                href="tambah.php"
                                                class="btn-empty-add"
                                            >

                                                ＋ Tambah Produk

                                            </a>


                                        </div>


                                    </td>

                                </tr>


                            <?php endif; ?>


                            </tbody>


                        </table>


                    </div>


                </div>


            </section>


        </div>


    </main>


</div>



<!-- =====================================================
     CSS GAMBAR PRODUK
     HANYA UNTUK TABEL PRODUK
===================================================== -->

<style>

.product-table .product-name-cell {
    display: flex;
    align-items: center;
    gap: 13px;
}

.product-table .product-avatar {
    width: 52px;
    height: 52px;
    min-width: 52px;
    border-radius: 10px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f1f5f9;
    font-size: 22px;
}

.product-table .product-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

</style>


</body>
</html>