<?php

session_start();

if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

include "../../koneksi.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_produk = (int) $_GET['id'];

/* =========================================
   CEK APAKAH PRODUK SUDAH PERNAH DIGUNAKAN
   DALAM TRANSAKSI
========================================= */

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM detailpenjualan
     WHERE id_produk = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id_produk);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* =========================================
   JIKA SUDAH PERNAH TERJUAL
========================================= */

if ($data['total'] > 0) {

    echo "
    <script>
        alert('Produk tidak dapat dihapus karena sudah digunakan dalam transaksi.');
        window.location.href = 'index.php';
    </script>
    ";

    exit;
}


/* =========================================
   JIKA BELUM PERNAH TERJUAL
========================================= */

$stmt = mysqli_prepare(
    $koneksi,
    "DELETE FROM produk WHERE id_produk = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_produk
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

header("Location: index.php");
exit;