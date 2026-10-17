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
   PENCARIAN
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
        WHERE
            CAST(p.id_penjualan AS CHAR) LIKE '%$cariAman%'
            OR pl.nama_pelanggan LIKE '%$cariAman%'
    ";
}


/* =========================
   DATA TRANSAKSI
========================= */

$query = mysqli_query(
    $koneksi,
    "
    SELECT
        p.id_penjualan,
        p.tanggal_penjualan,
        p.total_harga,
        COALESCE(pl.nama_pelanggan, 'Umum') AS nama_pelanggan
    FROM penjualan p
    LEFT JOIN pelanggan pl
        ON p.id_pelanggan = pl.id_pelanggan
    $where
    ORDER BY p.tanggal_penjualan DESC
    "
);


/* =========================
   TOTAL TRANSAKSI
========================= */

$queryTotal = mysqli_query(
    $koneksi,
    "
    SELECT COUNT(*) AS total
    FROM penjualan p
    LEFT JOIN pelanggan pl
        ON p.id_pelanggan = pl.id_pelanggan
    $where
    "
);

$dataTotal = mysqli_fetch_assoc($queryTotal);

$totalTransaksi = (int) $dataTotal['total'];


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

    <title>Riwayat Transaksi - Bottle.Co</title>

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
        href="/PENJUALAN/assets/css/riwayat.css?v=1"
    >

</head>


<body class="kasir-dashboard">

<main>

    <div class="riwayat-container">


        <!-- HEADER -->

        <section class="riwayat-header">

            <a
                href="../index.php"
                class="riwayat-back"
            >
                ← Kembali ke Dashboard
            </a>


            <span class="riwayat-label">
                AKTIVITAS KASIR
            </span>


            <div class="riwayat-title-row">

                <div>

                    <h1>
                        Riwayat Transaksi
                    </h1>

                    <p>
                        Daftar transaksi penjualan yang
                        telah tercatat dalam sistem.
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



        <!-- =================================================
             CONTENT CARD
        ================================================= -->

        <section class="riwayat-card">


            <!-- TOP -->

            <div class="riwayat-card-top">

                <div>

                    <span class="riwayat-card-label">
                        DATA TRANSAKSI
                    </span>

                    <h2>
                        Daftar Penjualan
                    </h2>

                </div>


                <div class="riwayat-total">

                    <span>
                        Total Transaksi
                    </span>

                    <strong>
                        <?= number_format($totalTransaksi); ?>
                    </strong>

                </div>

            </div>



            <!-- SEARCH -->

            <div class="riwayat-search">

                <form
                    method="GET"
                    action=""
                >

                    <div class="search-wrapper">

                        <span class="search-icon">
                            🔎
                        </span>

                        <input
                            type="text"
                            name="cari"
                            value="<?= htmlspecialchars($cari); ?>"
                            placeholder="Cari nomor transaksi atau nama pelanggan..."
                        >

                    </div>


                    <button
                        type="submit"
                        class="btn btn-primary btn-cari"
                    >
                        Cari
                    </button>


                    <?php if ($cari !== ''): ?>

                        <a
                            href="index.php"
                            class="btn btn-reset"
                        >
                            Reset
                        </a>

                    <?php endif; ?>

                </form>

            </div>



            <!-- TABLE -->

            <div class="riwayat-table-wrapper">

                <table class="table riwayat-table">

                    <thead>

                        <tr>

                            <th width="70">
                                No
                            </th>

                            <th width="150">
                                Transaksi
                            </th>

                            <th>
                                Tanggal
                            </th>

                            <th>
                                Pelanggan
                            </th>

                            <th>
                                Total
                            </th>

                            <th width="130">
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php if (
                        mysqli_num_rows($query) > 0
                    ): ?>

                        <?php
                        $no = 1;
                        ?>

                        <?php while (
                            $transaksi =
                            mysqli_fetch_assoc($query)
                        ): ?>

                            <tr>

                                <td class="nomor-row">

                                    <?= $no++; ?>

                                </td>


                                <td>

                                    <span class="kode-transaksi">

                                        #<?= (int) $transaksi['id_penjualan']; ?>

                                    </span>

                                </td>


                                <td>

                                    <div class="tanggal-transaksi">

                                        <strong>

                                            <?= date(
                                                'd M Y',
                                                strtotime(
                                                    $transaksi[
                                                        'tanggal_penjualan'
                                                    ]
                                                )
                                            ); ?>

                                        </strong>

                                        <span>

                                            <?= date(
                                                'H:i',
                                                strtotime(
                                                    $transaksi[
                                                        'tanggal_penjualan'
                                                    ]
                                                )
                                            ); ?>

                                            WIB

                                        </span>

                                    </div>

                                </td>


                                <td>

                                    <span class="nama-pelanggan">

                                        <?= htmlspecialchars(
                                            $transaksi[
                                                'nama_pelanggan'
                                            ]
                                        ); ?>

                                    </span>

                                </td>


                                <td>

                                    <strong class="total-transaksi">

                                        <?= rupiah(
                                            $transaksi[
                                                'total_harga'
                                            ]
                                        ); ?>

                                    </strong>

                                </td>


                                <td>

                                    <div class="riwayat-actions">

                                        <a
                                            href="detail.php?id=<?= (int) $transaksi['id_penjualan']; ?>"
                                            class="btn-detail"
                                        >
                                            Detail
                                        </a>

                                        <a
                                            href="cetak.php?id=<?= (int) $transaksi['id_penjualan']; ?>"
                                            target="_blank"
                                            class="btn-cetak-icon"
                                            title="Cetak struk"
                                        >
                                            🖨
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td
                                colspan="6"
                                class="riwayat-empty"
                            >

                                <div class="empty-icon">
                                    🧾
                                </div>

                                <strong>
                                    <?php if ($cari !== ''): ?>

                                        Transaksi tidak ditemukan

                                    <?php else: ?>

                                        Belum ada transaksi

                                    <?php endif; ?>
                                </strong>

                                <span>

                                    <?php if ($cari !== ''): ?>

                                        Coba gunakan kata pencarian
                                        yang berbeda.

                                    <?php else: ?>

                                        Belum ada data transaksi
                                        penjualan yang tercatat.

                                    <?php endif; ?>

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


<script src="/PENJUALAN/assets/js/bootstrap.bundle.min.js"></script>

</body>

</html>