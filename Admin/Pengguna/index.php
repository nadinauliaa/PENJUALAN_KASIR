<?php

session_start();

if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

include "../../koneksi.php";

/* =========================
   DATA ADMIN YANG LOGIN
========================= */

 $namaAdmin = $_SESSION['nama']
    ?? $_SESSION['username']
    ?? 'Administrator';


/* =========================
   SEARCH
========================= */

 $keyword = trim($_GET['keyword'] ?? '');

if ($keyword !== '') {

    $search = "%" . $keyword . "%";

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT *
         FROM users
         WHERE username LIKE ?
            OR nama LIKE ?
            OR level LIKE ?
            OR status LIKE ?
         ORDER BY id_user DESC"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ssss",
        $search,
        $search,
        $search,
        $search
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

} else {

    $result = mysqli_query(
        $koneksi,
        "SELECT *
         FROM users
         ORDER BY id_user DESC"
    );
}


/* =========================
   STATISTIK PENGGUNA
========================= */

 $queryTotal = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM users"
);

 $totalUser = mysqli_fetch_assoc($queryTotal)['total'] ?? 0;


 $queryAdmin = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM users
     WHERE level = 'admin'"
);

 $totalAdmin = mysqli_fetch_assoc($queryAdmin)['total'] ?? 0;


 $queryKasir = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM users
     WHERE level = 'kasir'"
);

 $totalKasir = mysqli_fetch_assoc($queryKasir)['total'] ?? 0;


 $queryAktif = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM users
     WHERE status = 'aktif'"
);

 $totalAktif = mysqli_fetch_assoc($queryAktif)['total'] ?? 0;

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Pengguna | KasirPro</title>

    <!-- BOOTSTRAP -->
    <link
        rel="stylesheet"
        href="../../assets/css/bootstrap.min.css"
    >

    <!-- ADMIN CSS -->
    <link
        rel="stylesheet"
        href="../../assets/css/admin.css?v=11"
    >

    <!-- PENGGUNA CSS -->
    <link
        rel="stylesheet"
        href="../../assets/css/pengguna.css?v=11"
    >

</head>

<body>


<!-- =====================================================
     SIDEBAR
===================================================== -->

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


    <!-- SIDEBAR CONTENT -->
    <div class="sidebar-content">

        <!-- MENU UTAMA -->
        <div class="menu-label">
            MENU UTAMA
        </div>

        <nav class="admin-menu">

            <a href="../index.php" class="admin-menu-item">

                <span class="menu-icon">
                    ▦
                </span>

                <span>
                    Dashboard
                </span>

            </a>


            <a href="../produk/index.php" class="admin-menu-item">

                <span class="menu-icon">
                    ▣
                </span>

                <span>
                    Produk
                </span>

            </a>


            <a href="../pelanggan/index.php" class="admin-menu-item">

                <span class="menu-icon">
                    ♟
                </span>

                <span>
                    Pelanggan
                </span>

            </a>


            <a href="../penjualan/index.php" class="admin-menu-item">

                <span class="menu-icon">
                    🛒
                </span>

                <span>
                    Penjualan
                </span>

            </a>


            <a href="../detail-penjualan/index.php" class="admin-menu-item">

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

            <!-- PENGGUNA AKTIF -->
            <a href="index.php" class="admin-menu-item active">

                <span class="menu-icon">
                    ♙
                </span>

                <span>
                    Pengguna
                </span>

            </a>


            <a href="../laporan/index.php" class="admin-menu-item">

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

        <a href="../../logout.php" class="logout-link">

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
     WAJIB ADA: menahan konten agar tidak tertutup
     sidebar yang position:fixed (lebar 198px)
===================================================== -->

<div class="admin-wrapper">


    <!-- TOPBAR -->

    <header class="admin-topbar">

        <div class="topbar-left">

            <!-- Tombol menu (hanya muncul di layar kecil) -->

            <button
                type="button"
                class="sidebar-toggle"
                id="btnSidebar"
                aria-label="Buka menu"
            >
                ☰
            </button>

            <div class="topbar-heading">

                <span class="topbar-small">
                    MANAJEMEN SISTEM
                </span>

                <h2>
                    Pengguna
                </h2>

            </div>

        </div>


        <!-- PROFILE -->

        <div class="admin-profile">

            <div class="profile-avatar">

                <?= htmlspecialchars(
                    strtoupper(
                        substr($namaAdmin, 0, 1)
                    )
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


        <!-- CONTENT -->

        <section class="user-content">


            <!-- =============================================
                 NOTIFICATION
            ============================================= -->

            <?php if (isset($_GET['pesan'])): ?>

                <?php if ($_GET['pesan'] === 'tambah'): ?>

                    <div class="user-alert success">
                        Pengguna berhasil ditambahkan.
                    </div>

                <?php elseif ($_GET['pesan'] === 'edit'): ?>

                    <div class="user-alert success">
                        Data pengguna berhasil diperbarui.
                    </div>

                <?php elseif ($_GET['pesan'] === 'hapus'): ?>

                    <div class="user-alert success">
                        Pengguna berhasil dihapus.
                    </div>

                <?php elseif ($_GET['pesan'] === 'self'): ?>

                    <div class="user-alert error">
                        Akun yang sedang digunakan tidak dapat dihapus.
                    </div>

                <?php elseif ($_GET['pesan'] === 'notfound'): ?>

                    <div class="user-alert error">
                        Data pengguna tidak ditemukan.
                    </div>

                <?php elseif ($_GET['pesan'] === 'gagal'): ?>

                    <div class="user-alert error">
                        Gagal menghapus pengguna.
                    </div>

                <?php endif; ?>

            <?php endif; ?>



            <!-- =============================================
                 INTRO
            ============================================= -->

            <div class="user-intro">


                <div class="user-intro-left">

                    <div class="user-intro-icon">
                        ♙
                    </div>

                    <div>

                        <span class="user-intro-label">
                            MANAJEMEN PENGGUNA
                        </span>

                        <h1>
                            Pengguna
                        </h1>

                        <p>
                            Kelola akun admin dan kasir yang memiliki
                            akses ke sistem Bottle.Co
                        </p>

                    </div>

                </div>


                <!-- TAMBAH PENGGUNA -->

                <a
                    href="tambah.php"
                    class="btn-add-user"
                >
                    + Tambah Pengguna
                </a>

            </div>



            <!-- =============================================
                 SUMMARY
            ============================================= -->

            <div class="user-summary">


                <!-- TOTAL -->

                <div class="user-summary-card">

                    <div class="user-summary-icon">
                        ♙
                    </div>

                    <div class="user-summary-info">

                        <span>
                            TOTAL PENGGUNA
                        </span>

                        <strong>
                            <?= $totalUser; ?>
                        </strong>

                    </div>

                </div>


                <!-- ADMIN -->

                <div class="user-summary-card">

                    <div class="user-summary-icon">
                        ★
                    </div>

                    <div class="user-summary-info">

                        <span>
                            ADMIN
                        </span>

                        <strong>
                            <?= $totalAdmin; ?>
                        </strong>

                    </div>

                </div>


                <!-- KASIR -->

                <div class="user-summary-card">

                    <div class="user-summary-icon">
                        $                     </div>

                    <div class="user-summary-info">

                        <span>
                            KASIR
                        </span>

                        <strong>
                            <?= $totalKasir; ?>
                        </strong>

                    </div>

                </div>


                <!-- AKTIF -->

                <div class="user-summary-card">

                    <div class="user-summary-icon">
                        ✓
                    </div>

                    <div class="user-summary-info">

                        <span>
                            AKUN AKTIF
                        </span>

                        <strong>
                            <?= $totalAktif; ?>
                        </strong>

                    </div>

                </div>

            </div>



            <!-- =============================================
                 TABLE BOX
            ============================================= -->

            <div class="user-box">


                <!-- TABLE HEADER -->

                <div class="user-box-header">


                    <div class="user-box-title">

                        <span>
                            DATA AKUN
                        </span>

                        <h2>
                            Daftar Pengguna
                        </h2>

                    </div>


                    <!-- SEARCH -->

                    <form
                        method="GET"
                        class="user-search"
                    >

                        <input
                            type="text"
                            name="keyword"
                            placeholder="Cari pengguna..."
                            value="<?= htmlspecialchars($keyword); ?>"
                        >

                        <button
                            type="submit"
                            class="btn-search"
                        >
                            Cari
                        </button>
                        <a href="index.php" style="height: 42px; padding: 0 16px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; border: 1px solid #e7ebf0; background: #f8fafc; color: #64748b; font-size: 14px; font-weight: 600; text-decoration: none;">
                        Reset
                    </a>

                    </form>

                </div>



                <!-- TABLE -->

                <div class="user-table-wrapper">

                    <table class="user-table">

                        <thead>

                            <tr>

                                <th>
                                    #
                                </th>

                                <th>
                                    PENGGUNA
                                </th>

                                <th>
                                    USERNAME
                                </th>

                                <th>
                                    LEVEL
                                </th>

                                <th>
                                    STATUS
                                </th>

                                <th>
                                    AKSI
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php if ($result && mysqli_num_rows($result) > 0): ?>

                                <?php
                                $no = 1;
                                ?>

                                <?php while ($user = mysqli_fetch_assoc($result)): ?>

                                    <?php

                                    $namaUser = $user['nama'] ?? '';

                                    $inisial = strtoupper(
                                        substr(
                                            trim($namaUser),
                                            0,
                                            1
                                        )
                                    );

                                    if ($inisial === '') {
                                        $inisial = 'U';
                                    }

                                    ?>


                                    <tr>


                                        <!-- NOMOR -->

                                        <td class="user-number">
                                            <?= $no++; ?>
                                        </td>


                                        <!-- NAMA -->

                                        <td>

                                            <div class="user-profile">

                                                <div class="user-avatar">
                                                    <?= htmlspecialchars($inisial); ?>
                                                </div>

                                                <div>

                                                    <div class="user-profile-name">
                                                        <?= htmlspecialchars($namaUser); ?>
                                                    </div>

                                                </div>

                                            </div>

                                        </td>


                                        <!-- USERNAME -->

                                        <td>

                                            <span class="username">
                                                @<?= htmlspecialchars($user['username']); ?>
                                            </span>

                                        </td>


                                        <!-- LEVEL -->

                                        <td>

                                            <?php if ($user['level'] === 'admin'): ?>

                                                <span class="user-badge badge-admin">
                                                    Admin
                                                </span>

                                            <?php else: ?>

                                                <span class="user-badge badge-kasir">
                                                    Kasir
                                                </span>

                                            <?php endif; ?>

                                        </td>


                                        <!-- STATUS -->

                                        <td>

                                            <?php if ($user['status'] === 'aktif'): ?>

                                                <span class="user-badge badge-aktif">
                                                    Aktif
                                                </span>

                                            <?php else: ?>

                                                <span class="user-badge badge-nonaktif">
                                                    Nonaktif
                                                </span>

                                            <?php endif; ?>

                                        </td>


                                        <!-- AKSI -->

                                        <td>

                                            <div class="user-actions">


                                                <!-- EDIT -->

                                                <a
                                                    href="edit.php?id=<?= (int) $user['id_user']; ?>"
                                                    class="user-action"
                                                    title="Edit pengguna"
                                                >
                                                    ✎
                                                </a>


                                                <!-- HAPUS -->

                                                <?php
                                                $isCurrentUser =
                                                    isset($_SESSION['id_user']) &&
                                                    (int) $_SESSION['id_user'] === (int) $user['id_user'];
                                                ?>

                                                <?php if (!$isCurrentUser): ?>

                                                    <a
                                                        href="hapus.php?id=<?= (int) $user['id_user']; ?>"
                                                        class="user-action delete"
                                                        title="Hapus pengguna"
                                                        onclick="return confirm('Yakin ingin menghapus pengguna ini?');"
                                                    >
                                                        ×
                                                    </a>

                                                <?php else: ?>

                                                    <span
                                                        class="user-action delete disabled"
                                                        title="Akun yang sedang login tidak dapat dihapus"
                                                    >
                                                        ×
                                                    </span>

                                                <?php endif; ?>


                                            </div>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>


                            <?php else: ?>


                                <!-- DATA KOSONG -->

                                <tr>

                                    <td
                                        colspan="6"
                                        style="padding:0;"
                                    >

                                        <div class="user-empty">

                                            <div class="user-empty-icon">
                                                ♙
                                            </div>

                                            <h3>
                                                <?= $keyword !== ''
                                                    ? 'Pengguna tidak ditemukan'
                                                    : 'Belum ada pengguna'; ?>
                                            </h3>

                                            <p>

                                                <?php if ($keyword !== ''): ?>

                                                    Tidak ada pengguna yang
                                                    sesuai dengan pencarian
                                                    "<?= htmlspecialchars($keyword); ?>".

                                                <?php else: ?>

                                                    Belum ada data pengguna
                                                    yang tersimpan.

                                                <?php endif; ?>

                                            </p>

                                        </div>

                                    </td>

                                </tr>


                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>


            <!-- FOOTER -->

            <footer class="footer">
                <strong>© <?= date('Y'); ?> Bottle.Co</strong>
                <span>Manajemen Pengguna</span>
            </footer>


        </section>

    </main>

</div>


<!-- =====================================================
     TOGGLE SIDEBAR (MOBILE)
===================================================== -->

<script>

    var btnSidebar = document.getElementById('btnSidebar');
    var sidebar    = document.querySelector('.admin-sidebar');

    if (btnSidebar && sidebar) {
        btnSidebar.addEventListener('click', function () {
            sidebar.classList.toggle('show-mobile');
        });
    }

</script>


</body>
</html>