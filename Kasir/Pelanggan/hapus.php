<?php

session_start();

if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'kasir') {
    header("Location: ../../login.php");
    exit;
}

include "../../koneksi.php";

$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}


/* =========================================================
   HAPUS PELANGGAN
========================================================= */

$stmt = mysqli_prepare(
    $koneksi,
    "DELETE FROM pelanggan
     WHERE id_pelanggan = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);


/* =========================================================
   KEMBALI
========================================================= */

header("Location: index.php");
exit;