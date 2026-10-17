<?php

session_start();

if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

include "../../koneksi.php";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// Jangan izinkan menghapus akun yang sedang login
if (
    isset($_SESSION['id_user']) &&
    (int) $_SESSION['id_user'] === $id
) {
    header("Location: index.php?pesan=self");
    exit;
}

// Cek apakah user ada
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id_user FROM users WHERE id_user = ? LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    header("Location: index.php?pesan=notfound");
    exit;
}

// Hapus user
$stmt = mysqli_prepare(
    $koneksi,
    "DELETE FROM users WHERE id_user = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {

    header("Location: index.php?pesan=hapus");
    exit;

} else {

    header("Location: index.php?pesan=gagal");
    exit;
}