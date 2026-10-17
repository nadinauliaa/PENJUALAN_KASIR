<?php

session_start();

if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'kasir') {
    header("Location: ../../login.php");
    exit;
}

include "../../koneksi.php";

date_default_timezone_set('Asia/Jakarta');

$namaKasir = $_SESSION['nama'] ?? 'Kasir';


/* =========================================================
   HALAMAN AKTIF
========================================================= */

$halamanAktif = 'pelanggan';


/* =========================================================
   PENCARIAN
========================================================= */

$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';


/* =========================================================
   DATA PELANGGAN
========================================================= */

if ($cari !== '') {

    $cariEsc = mysqli_real_escape_string($koneksi, $cari);

    $queryPelanggan = mysqli_query($koneksi, "
        SELECT
            id_pelanggan,
            nama_pelanggan,
            alamat,
            no_telp
        FROM pelanggan
        WHERE
            nama_pelanggan LIKE '%$cariEsc%'
            OR alamat LIKE '%$cariEsc%'
            OR no_telp LIKE '%$cariEsc%'
        ORDER BY id_pelanggan DESC
    ");

} else {

    $queryPelanggan = mysqli_query($koneksi, "
        SELECT
            id_pelanggan,
            nama_pelanggan,
            alamat,
            no_telp
        FROM pelanggan
        ORDER BY id_pelanggan DESC
    ");

}


/* =========================================================
   TOTAL PELANGGAN
========================================================= */

$queryTotal = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total
    FROM pelanggan
");

$dataTotal = mysqli_fetch_assoc($queryTotal);

$totalPelanggan = (int) ($dataTotal['total'] ?? 0);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Pelanggan | Bottle.Co</title>


    <!-- BOOTSTRAP -->

    <link
        rel="stylesheet"
        href="/PENJUALAN/assets/css/bootstrap.min.css"
    >


    <!-- STYLE UTAMA -->

    <link
        rel="stylesheet"
        href="/PENJUALAN/assets/css/style.css?v=20"
    >


    <!-- CSS PELANGGAN -->

    <link
        rel="stylesheet"
        href="/PENJUALAN/assets/css/pelanggan_kasir.css?v=30"
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

            <strong>
                Bottle.Co
            </strong>

            <span>
                SISTEM KASIR
            </span>

        </div>

    </div>


    <!-- LABEL -->

    <div class="sidebar-label">
        MENU UTAMA
    </div>


    <!-- MENU -->

    <nav class="sidebar-menu">


        <!-- DASHBOARD -->

        <a
            href="../index.php"
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
            href="../transaksi/index.php"
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
            href="../produk/index.php"
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
            href="index.php"
            class="sidebar-link active"
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
            href="../riwayat/index.php"
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
            href="../../logout.php"
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
                KASIR / PELANGGAN
            </span>

            <h1>
                Data Pelanggan
            </h1>

        </div>


        <!-- RIGHT -->

        <div class="topbar-right">


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

    <div class="customer-content">

        <!-- =================================================
             TOOLBAR
        ================================================== -->

        <div class="content-toolbar">


            <!-- SEARCH -->

            <!-- SEARCH -->

            <form
                method="GET"
                action=""
                class="search-form"
            >

                <div class="search-box">

                    <span class="search-icon">
                        🔍
                    </span>

                    <input
                        type="text"
                        name="cari"
                        value="<?= htmlspecialchars($cari); ?>"
                        placeholder="Cari pelanggan..."
                        autocomplete="off"
                    >

                </div>

                <button
                    type="submit"
                    class="btn-search"
                >
                    Cari
                </button>

                <a
                    href="index.php"
                    class="btn-reset"
                >
                    Reset
                </a>
            </form>


            <!-- TAMBAH -->

            <a
                href="tambah.php"
                class="btn-primary"
            >
                + Tambah Pelanggan
            </a>

        </div>



        <!-- =================================================
             CUSTOMER CARD
        ================================================== -->

        <section class="customer-card">


            <!-- CARD HEADER -->

            <div class="card-header">

                <div>

                    <span class="card-label">
                        TOTAL PELANGGAN
                    </span>

                    <h2>
                        <?= $totalPelanggan; ?>
                    </h2>

                </div>

                <div>

                    <h3>
                        Daftar Pelanggan
                    </h3>

                    <p>
                        Data pelanggan yang tersimpan dalam sistem.
                    </p>

                </div>

            </div>



            <!-- TABLE -->

            <div class="table-wrapper">

                <table class="customer-table">

                    <thead>

                        <tr>

                            <th width="7%">
                                No
                            </th>

                            <th width="23%">
                                Nama Pelanggan
                            </th>

                            <th width="25%">
                                Alamat
                            </th>

                            <th width="22%">
                                No. Telepon
                            </th>

                            <th width="23%">
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php if (mysqli_num_rows($queryPelanggan) > 0): ?>

                        <?php $no = 1; ?>

                        <?php while ($pelanggan = mysqli_fetch_assoc($queryPelanggan)): ?>

                            <tr>

                                <td>
                                    <?= $no++; ?>
                                </td>


                                <td class="customer-name">
                                    <?= htmlspecialchars(
                                        $pelanggan['nama_pelanggan']
                                    ); ?>
                                </td>


                                <td>
                                    <?= htmlspecialchars(
                                        $pelanggan['alamat'] ?? '-'
                                    ); ?>
                                </td>


                                <td>
                                    <?= htmlspecialchars(
                                        $pelanggan['no_telp'] ?? '-'
                                    ); ?>
                                </td>


                                <td>

                                    <div class="action-buttons">

                                        <a
                                            href="edit.php?id=<?= (int) $pelanggan['id_pelanggan']; ?>"
                                            class="btn-edit"
                                        >
                                            Edit
                                        </a>


                                        <a
                                            href="hapus.php?id=<?= (int) $pelanggan['id_pelanggan']; ?>"
                                            class="btn-delete"
                                            onclick="return confirm('Yakin ingin menghapus pelanggan ini?');"
                                        >
                                            Hapus
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        <?php endwhile; ?>


                    <?php else: ?>

                        <tr>

                            <td
                                colspan="5"
                                class="empty-row"
                            >

                                <?php if ($cari !== ''): ?>

                                    Tidak ada pelanggan yang sesuai dengan pencarian.

                                <?php else: ?>

                                    Belum ada data pelanggan.

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

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