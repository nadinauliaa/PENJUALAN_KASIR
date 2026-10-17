<?php
session_start();

if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once "../../koneksi.php";
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$namaAdmin = $_SESSION['nama'] ?? $_SESSION['username'] ?? 'Administrator';
$cari = trim($_GET['cari'] ?? '');

function rupiah($angka)
{
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

/* QUERY DATA PENJUALAN */
$sql = "
    SELECT
        p.id_penjualan,
        p.tanggal_penjualan,
        p.total_harga,
        p.id_pelanggan,
        pl.nama_pelanggan,
        COALESCE(SUM(dp.jumlah_produk), 0) AS jumlah_item
    FROM penjualan p
    LEFT JOIN pelanggan pl
        ON p.id_pelanggan = pl.id_pelanggan
    LEFT JOIN detailpenjualan dp
        ON p.id_penjualan = dp.id_penjualan
";

if ($cari !== '') {
    $sql .= "
        WHERE CAST(p.id_penjualan AS CHAR) LIKE ?
           OR CAST(p.tanggal_penjualan AS CHAR) LIKE ?
           OR pl.nama_pelanggan LIKE ?
    ";
}

$sql .= "
    GROUP BY
        p.id_penjualan,
        p.tanggal_penjualan,
        p.total_harga,
        p.id_pelanggan,
        pl.nama_pelanggan
    ORDER BY p.id_penjualan DESC
";

$stmt = $koneksi->prepare($sql);

if ($cari !== '') {
    $keyword = '%' . $cari . '%';
    $stmt->bind_param("sss", $keyword, $keyword, $keyword);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penjualan | Bottle.Co</title>

    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="../../assets/css/penjualan.css">
</head>
<body>

<!-- SIDEBAR: dipertahankan -->
<aside class="admin-sidebar">
    <div class="admin-brand">
        <div class="brand-logo">🛒</div>
        <div class="brand-text">
            <strong>Bottle.Co</strong>
            <span>ADMINISTRATOR</span>
        </div>
    </div>

    <div class="sidebar-content">
        <div class="menu-label">MENU UTAMA</div>
        <nav class="admin-menu">
            <a href="../index.php" class="admin-menu-item">
                <span class="menu-icon">▦</span>
                <span>Dashboard</span>
            </a>
            <a href="../produk/index.php" class="admin-menu-item">
                <span class="menu-icon">▣</span>
                <span>Produk</span>
            </a>
            <a href="../pelanggan/index.php" class="admin-menu-item">
                <span class="menu-icon">♟</span>
                <span>Pelanggan</span>
            </a>
            <a href="index.php" class="admin-menu-item active">
                <span class="menu-icon">🛒</span>
                <span>Penjualan</span>
            </a>
            <a href="../detail-penjualan/index.php" class="admin-menu-item">
                <span class="menu-icon">▤</span>
                <span>Detail Penjualan</span>
            </a>
        </nav>

        <div class="menu-label menu-label-system">SISTEM</div>
        <nav class="admin-menu">
            <a href="../pengguna/index.php" class="admin-menu-item">
                <span class="menu-icon">♙</span>
                <span>Pengguna</span>
            </a>
            <a href="../laporan/index.php" class="admin-menu-item">
                <span class="menu-icon">▥</span>
                <span>Laporan</span>
            </a>
        </nav>
    </div>

    <div class="sidebar-footer">
        <a href="../../logout.php" class="logout-link">
            <span>↪</span>
            <span>Logout</span>
        </a>
    </div>
</aside>

<div class="sales-page-wrapper">
    <header class="sales-topbar">
        <div class="sales-topbar-heading">
            <h1>Data Penjualan</h1>
            <p>Area Administrasi Bottle.Co</p>
        </div>

        <div class="sales-admin-profile">
            <div class="sales-admin-avatar">
                <?= htmlspecialchars(strtoupper(substr($namaAdmin, 0, 1))) ?>
            </div>
            <div class="sales-admin-info">
                <strong><?= htmlspecialchars($namaAdmin) ?></strong>
                <span>Administrator</span>
            </div>
        </div>
    </header>

    <main class="sales-main">
        <div class="sales-content">

            <section class="sales-intro">
                <div class="sales-intro-left">
                    <div class="sales-intro-icon">🛒</div>
                    <div>
                        <span class="sales-intro-label">MANAJEMEN PENJUALAN</span>
                        <h2>Daftar Penjualan</h2>
                        <p>Kelola dan pantau transaksi penjualan toko.</p>
                    </div>
                </div>

                <form method="GET" class="sales-search-form">
                    <input
                        type="text"
                        name="cari"
                        class="form-control"
                        placeholder="Cari ID, tanggal, pelanggan..."
                        value="<?= htmlspecialchars($cari) ?>"
                        aria-label="Cari penjualan"
                    >
                    <button type="submit" class="btn-add-sale">Cari</button>

                    <?php if ($cari !== ''): ?>
                        <a href="index.php" class="btn-reset-sale">Reset</a>
                    <?php endif; ?>
                </form>
            </section>

            <section class="sales-section">
                <div class="sales-box">
                    <div class="sales-box-header">
                        <div>
                            <span class="sales-table-label">TABEL DATA</span>
                            <h2>Semua Transaksi</h2>
                        </div>
                        <div class="sales-table-count">
                            Total: <?= number_format($result->num_rows) ?> Transaksi
                        </div>
                    </div>

                    <div class="sales-table-responsive">
                        <table class="sales-table">
                            <thead>
                                <tr>
                                    <th class="sales-col-no">No.</th>
                                    <th>ID Penjualan</th>
                                    <th>Tanggal Penjualan</th>
                                    <th>Pelanggan</th>
                                    <th>Jumlah Item</th>
                                    <th>Total Harga</th>
                                    <th class="sales-col-action">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php $no = 1; ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <?php
                                    $namaPelanggan = $row['nama_pelanggan'] ?: 'Pelanggan umum';
                                    $tanggal = $row['tanggal_penjualan'] ?? '-';
                                    ?>
                                    <tr>
                                        <td class="sales-number-cell"><?= $no++ ?></td>
                                        <td>
                                            <div class="transaction-cell">
                                                <div class="transaction-icon">#</div>
                                                <div>
                                                    <strong><?= (int) $row['id_penjualan'] ?></strong>
                                                    <span>Transaksi Toko</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="date-cell">
                                                <?= htmlspecialchars($tanggal) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="sales-customer">
                                                <div class="sales-customer-avatar">
                                                    <?= htmlspecialchars(strtoupper(substr($namaPelanggan, 0, 1))) ?>
                                                </div>
                                                <span><?= htmlspecialchars($namaPelanggan) ?></span>
                                            </div>
                                        </td>
                                        <td><?= number_format((int) $row['jumlah_item']) ?> item</td>
                                        <td>
                                            <span class="sale-total">
                                                <?= rupiah($row['total_harga']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="sales-action-buttons">
                                                <a
                                                    href="../detail-penjualan/index.php?id_penjualan=<?= (int) $row['id_penjualan'] ?>"
                                                    class="sales-btn-action sales-btn-view"
                                                    title="Lihat detail"
                                                    aria-label="Lihat detail penjualan"
                                                >👁</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7">
                                        <div class="sales-empty">
                                            <div class="sales-empty-icon">🛒</div>
                                            <h3>Tidak ada data</h3>
                                            <p>
                                                <?= $cari !== ''
                                                    ? 'Data penjualan yang Anda cari tidak ditemukan.'
                                                    : 'Belum ada transaksi penjualan yang tercatat.' ?>
                                            </p>
                                            <?php if ($cari !== ''): ?>
                                                <a href="index.php" class="btn-empty-sale">Reset Pencarian</a>
                                            <?php endif; ?>
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

<script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmt->close();
?>
