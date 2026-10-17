<?php

session_start();

include "koneksi.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';


// ===============================
// VALIDASI
// ===============================

if ($username === '' || $password === '') {
    header("Location: login.php?error=salah");
    exit;
}


// ===============================
// CARI USER
// ===============================

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id_user, username, password, nama, level, status
     FROM users
     WHERE username = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


// ===============================
// USER TIDAK DITEMUKAN
// ===============================

if (!$user) {
    header("Location: login.php?error=salah");
    exit;
}


// ===============================
// CEK STATUS AKUN
// ===============================

if ($user['status'] !== 'aktif') {
    header("Location: login.php?error=nonaktif");
    exit;
}


// ===============================
// CEK PASSWORD
// ===============================

$passwordValid = false;


// Password baru yang sudah menggunakan password_hash()
if (password_verify($password, $user['password'])) {

    $passwordValid = true;

}


// Password lama yang masih plaintext
elseif (hash_equals($user['password'], $password)) {

    $passwordValid = true;

    // ===============================
    // OTOMATIS UBAH KE PASSWORD HASH
    // ===============================

    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $update = mysqli_prepare(
        $koneksi,
        "UPDATE users
         SET password = ?
         WHERE id_user = ?"
    );

    mysqli_stmt_bind_param(
        $update,
        "si",
        $passwordHash,
        $user['id_user']
    );

    mysqli_stmt_execute($update);

    mysqli_stmt_close($update);
}


// ===============================
// PASSWORD SALAH
// ===============================

if (!$passwordValid) {
    header("Location: login.php?error=salah");
    exit;
}


// ===============================
// CEK LEVEL
// ===============================

if ($user['level'] !== 'admin' && $user['level'] !== 'kasir') {
    header("Location: login.php?error=level");
    exit;
}


// ===============================
// SIMPAN SESSION
// ===============================

session_regenerate_id(true);

$_SESSION['id_user'] = $user['id_user'];
$_SESSION['username'] = $user['username'];
$_SESSION['nama'] = $user['nama'];
$_SESSION['level'] = $user['level'];


// ===============================
// REDIRECT
// ===============================

if ($user['level'] === 'admin') {

    header("Location: admin/index.php");
    exit;

}

if ($user['level'] === 'kasir') {

    header("Location: kasir/index.php");
    exit;

}

?>