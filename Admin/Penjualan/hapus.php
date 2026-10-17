<?php
session_start();

if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

include "../../koneksi.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$id_penjualan = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($id_penjualan <= 0) {
    header("Location: index.php");
    exit;
}


try {

    mysqli_begin_transaction($koneksi);


    /*
     * Ambil semua detail transaksi
     */

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id_produk, jumlah_produk
         FROM detailpenjualan
         WHERE id_penjualan = ?
         FOR UPDATE"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $id_penjualan
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $items = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }

    mysqli_stmt_close($stmt);


    /*
     * Kembalikan stok
     */

    foreach ($items as $item) {

        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE produk
             SET stok = stok + ?
             WHERE id_produk = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ii",
            $item['jumlah_produk'],
            $item['id_produk']
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);
    }


    /*
     * Hapus detail
     */

    $stmt = mysqli_prepare(
        $koneksi,
        "DELETE FROM detailpenjualan
         WHERE id_penjualan = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $id_penjualan
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);


    /*
     * Hapus transaksi utama
     */

    $stmt = mysqli_prepare(
        $koneksi,
        "DELETE FROM penjualan
         WHERE id_penjualan = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $id_penjualan
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);


    mysqli_commit($koneksi);

    header("Location: index.php");
    exit;

} catch (Exception $e) {

    mysqli_rollback($koneksi);

    die(
        "Gagal menghapus transaksi: " .
        htmlspecialchars($e->getMessage())
    );
}