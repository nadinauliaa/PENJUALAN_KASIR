<?php
session_start();

if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'kasir') {
    header("Location: ../../login.php");
    exit;
}

include "../../koneksi.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID transaksi tidak valid.");
}

$id_penjualan = (int) $_GET['id'];

/* =========================
   DATA TRANSAKSI
========================= */
$queryTransaksi = mysqli_query($koneksi, "
    SELECT
        p.id_penjualan,
        p.tanggal_penjualan,
        p.total_harga,
        COALESCE(pl.nama_pelanggan, 'Umum') AS nama_pelanggan
    FROM penjualan p
    LEFT JOIN pelanggan pl
        ON p.id_pelanggan = pl.id_pelanggan
    WHERE p.id_penjualan = $id_penjualan
");

if (!$queryTransaksi || mysqli_num_rows($queryTransaksi) === 0) {
    die("Transaksi tidak ditemukan.");
}

$transaksi = mysqli_fetch_assoc($queryTransaksi);

/* =========================
   DETAIL TRANSAKSI
========================= */
$queryDetail = mysqli_query($koneksi, "
    SELECT
        dp.jumlah_produk,
        dp.subtotal,
        pr.nama_produk,
        pr.harga
    FROM detailpenjualan dp
    INNER JOIN produk pr
        ON dp.id_produk = pr.id_produk
    WHERE dp.id_penjualan = $id_penjualan
    ORDER BY dp.id_detail ASC
");

function rupiah($angka)
{
    return 'Rp ' . number_format(
        (float) $angka,
        0,
        ',',
        '.'
    );
}

$namaKasir = $_SESSION['nama'] ?? 'Kasir';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Struk #<?= $id_penjualan; ?> - Bottle.Co
    </title>

    <link
        rel="stylesheet"
        href="/PENJUALAN/assets/css/cetak.css?v=1"
    >
</head>

<body>

<div class="print-wrapper">

    <!-- STRUK -->
    <div class="struk">

        <div class="struk-header">
            <h1>Bottle.Co</h1>
            <p>Sistem Informasi Penjualan</p>
        </div>

        <div class="struk-line"></div>

        <div class="struk-info">

            <div>
                <span>No. Transaksi</span>
                <strong>
                    #<?= $id_penjualan; ?>
                </strong>
            </div>

            <div>
                <span>Tanggal</span>
                <strong>
                    <?= date(
                        'd/m/Y H:i',
                        strtotime($transaksi['tanggal_penjualan'])
                    ); ?>
                </strong>
            </div>

            <div>
                <span>Pelanggan</span>
                <strong>
                    <?= htmlspecialchars(
                        $transaksi['nama_pelanggan']
                    ); ?>
                </strong>
            </div>

            <div>
                <span>Kasir</span>
                <strong>
                    <?= htmlspecialchars($namaKasir); ?>
                </strong>
            </div>

        </div>

        <div class="struk-line"></div>

        <!-- PRODUK -->
        <div class="produk-list">

            <?php while ($detail = mysqli_fetch_assoc($queryDetail)): ?>

                <div class="produk">

                    <div class="produk-nama">
                        <?= htmlspecialchars(
                            $detail['nama_produk']
                        ); ?>
                    </div>

                    <div class="produk-detail">

                        <span>
                            <?= (int) $detail['jumlah_produk']; ?>
                            ×
                            <?= rupiah($detail['harga']); ?>
                        </span>

                        <strong>
                            <?= rupiah($detail['subtotal']); ?>
                        </strong>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

        <div class="struk-line"></div>

        <!-- TOTAL -->
        <div class="total-row">

            <span>Total</span>

            <strong>
                <?= rupiah($transaksi['total_harga']); ?>
            </strong>

        </div>

        <div class="struk-line"></div>

        <div class="struk-footer">
            <p>Terima kasih</p>
            <span>Struk ini merupakan bukti transaksi penjualan.</span>
        </div>

    </div>

    <!-- TOMBOL -->
    <div class="print-actions">

        <button
            type="button"
            onclick="window.print()"
            class="btn-print"
        >
            🖨 Cetak Struk
        </button>

        <a
            href="index.php"
            class="btn-back"
        >
            Kembali
        </a>

    </div>

</div>

</body>
</html>