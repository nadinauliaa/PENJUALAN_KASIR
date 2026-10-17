<?php
session_start();

if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

include "../../koneksi.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


/* =========================================================
   DATA ADMIN
   ========================================================= */

$namaAdmin = $_SESSION['nama']
    ?? $_SESSION['username']
    ?? 'Administrator';


/* =========================================================
   DATA PELANGGAN
   ========================================================= */

$queryPelanggan = mysqli_query(
    $koneksi,
    "SELECT
        id_pelanggan,
        nama_pelanggan,
        alamat,
        no_telp
     FROM pelanggan
     ORDER BY id_pelanggan DESC"
);


/* =========================================================
   STATISTIK
   ========================================================= */

$queryTotal = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM pelanggan"
);

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
        href="../../assets/css/bootstrap.min.css"
    >


    <!-- ADMIN CSS -->
    <link
        rel="stylesheet"
        href="../../assets/css/admin.css"
    >


    <!-- PELANGGAN CSS -->
    <link
        rel="stylesheet"
        href="../../assets/css/pelanggan.css"
    >

</head>


<body>


<!-- =====================================================
     SIDEBAR
     ===================================================== -->

<aside class="admin-sidebar">

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


    <div class="sidebar-content">

        <div class="menu-label">
            MENU UTAMA
        </div>


        <nav class="admin-menu">

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


            <a
                href="index.php"
                class="admin-menu-item active"
            >

                <span class="menu-icon">
                    ♟
                </span>

                <span>
                    Pelanggan
                </span>

            </a>


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


        <div class="menu-label menu-label-system">
            SISTEM
        </div>


        <nav class="admin-menu">

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
         ================================================= -->

    <header class="admin-topbar">

        <div class="topbar-left">

            <div class="breadcrumb-small">
                DATA MASTER
            </div>

            <h1>
                Pelanggan
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
         MAIN
         ================================================= -->

    <main class="admin-main">


        <div class="pelanggan-content">


            <!-- =============================================
                 INTRO
                 ============================================= -->

            <section class="pelanggan-intro">

                <div class="intro-left">

                    <div class="intro-icon">
                        👥
                    </div>


                    <div>

                        <span class="intro-label">
                            DATA PELANGGAN
                        </span>

                        <h2>
                            Kelola Pelanggan
                        </h2>

                        <p>
                            Kelola data pelanggan toko dengan mudah.
                        </p>

                    </div>

                </div>


                <a
                    href="tambah.php"
                    class="btn-add-pelanggan"
                >

                    <span>
                        +
                    </span>

                    Tambah Pelanggan

                </a>

            </section>



            <!-- =============================================
                 SUMMARY
                 ============================================= -->

            <section class="pelanggan-summary">


                <div class="summary-card">

                    <div class="summary-icon">
                        👥
                    </div>

                    <div class="summary-info">

                        <span>
                            TOTAL PELANGGAN
                        </span>

                        <strong>
                            <?= $totalPelanggan; ?>
                        </strong>

                    </div>

                </div>



                <div class="summary-card">

                    <div class="summary-icon">
                        ✓
                    </div>

                    <div class="summary-info">

                        <span>
                            STATUS DATA
                        </span>

                        <strong>
                            Aktif
                        </strong>

                    </div>

                </div>


            </section>



            <!-- =============================================
                 TABLE
                 ============================================= -->

            <section class="pelanggan-table-card">


                <div class="table-header">

                    <div>

                        <span>
                            DATABASE PELANGGAN
                        </span>

                        <h3>
                            Daftar Pelanggan
                        </h3>

                    </div>


                    <div class="data-count">
                        <?= $totalPelanggan; ?> Data
                    </div>

                </div>



                <div class="table-responsive">


                    <table class="pelanggan-table">

                        <thead>

                            <tr>

                                <th width="55">
                                    NO
                                </th>

                                <th>
                                    PELANGGAN
                                </th>

                                <th>
                                    ALAMAT
                                </th>

                                <th>
                                    NO. TELEPON
                                </th>

                                <th width="105">
                                    AKSI
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php if (mysqli_num_rows($queryPelanggan) > 0): ?>

                            <?php
                            $no = 1;
                            ?>

                            <?php while ($row = mysqli_fetch_assoc($queryPelanggan)): ?>

                                <?php
                                $namaPelanggan =
                                    $row['nama_pelanggan'] ?? '';

                                $inisial =
                                    strtoupper(
                                        substr(
                                            trim($namaPelanggan),
                                            0,
                                            1
                                        )
                                    );
                                ?>

                                <tr>


                                    <td class="number-cell">
                                        <?= $no++; ?>
                                    </td>


                                    <td>

                                        <div class="customer-cell">

                                            <div class="customer-avatar">
                                                <?= htmlspecialchars($inisial); ?>
                                            </div>


                                            <div class="customer-info">

                                                <strong>
                                                    <?= htmlspecialchars(
                                                        $namaPelanggan
                                                    ); ?>
                                                </strong>

                                                <span>
                                                    ID #<?= htmlspecialchars(
                                                        $row['id_pelanggan']
                                                    ); ?>
                                                </span>

                                            </div>

                                        </div>

                                    </td>


                                    <td>

                                        <div class="address-cell">

                                            <span class="address-icon">
                                                ⌖
                                            </span>

                                            <span>
                                                <?= htmlspecialchars(
                                                    $row['alamat']
                                                        ?? '-'
                                                ); ?>
                                            </span>

                                        </div>

                                    </td>


                                    <td>

                                        <div class="phone-cell">

                                            <span>
                                                ☎
                                            </span>

                                            <?= htmlspecialchars(
                                                $row['no_telp']
                                                    ?? '-'
                                            ); ?>

                                        </div>

                                    </td>


                                    <td>

                                        <div class="action-buttons">

                                            <a
                                                href="edit.php?id=<?= $row['id_pelanggan']; ?>"
                                                class="btn-edit"
                                                title="Edit"
                                            >
                                                ✎
                                            </a>


                                            <a
                                                href="hapus.php?id=<?= $row['id_pelanggan']; ?>"
                                                class="btn-delete"
                                                title="Hapus"
                                                onclick="return confirm('Yakin ingin menghapus pelanggan ini?');"
                                            >
                                                🗑
                                            </a>

                                        </div>

                                    </td>

                                </tr>

                            <?php endwhile; ?>


                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="5"
                                    class="empty-data"
                                >

                                    <div class="empty-icon">
                                        👥
                                    </div>

                                    <strong>
                                        Belum ada data pelanggan
                                    </strong>

                                    <span>
                                        Tambahkan pelanggan baru untuk mulai mengelola data.
                                    </span>

                                </td>

                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </section>


        </div>

    </main>

</div>



<script src="../../assets/js/bootstrap.bundle.min.js"></script>

</body>
</html>