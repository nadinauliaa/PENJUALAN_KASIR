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

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id_user, username, nama, level, status
     FROM users
     WHERE id_user = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    header("Location: index.php");
    exit;
}

$pesan = '';

if (isset($_POST['simpan'])) {

    $username = trim($_POST['username'] ?? '');
    $nama     = trim($_POST['nama'] ?? '');
    $password = $_POST['password'] ?? '';
    $level    = $_POST['level'] ?? 'kasir';
    $status   = $_POST['status'] ?? 'aktif';

    if ($username === '' || $nama === '') {

        $pesan = "Nama dan username wajib diisi.";

    } elseif (strlen($username) > 50) {

        $pesan = "Username maksimal 50 karakter.";

    } elseif ($password !== '' && strlen($password) < 6) {

        $pesan = "Password baru minimal 6 karakter.";

    } elseif (!in_array($level, ['admin', 'kasir'], true)) {

        $pesan = "Level pengguna tidak valid.";

    } elseif (!in_array($status, ['aktif', 'nonaktif'], true)) {

        $pesan = "Status pengguna tidak valid.";

    } else {

        // Cek username agar tidak sama dengan user lain
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id_user
             FROM users
             WHERE username = ?
             AND id_user != ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "si", $username, $id);
        mysqli_stmt_execute($stmt);

        $cek = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($cek) > 0) {

            $pesan = "Username sudah digunakan oleh pengguna lain.";

        } else {

            if ($password !== '') {

                $password_hash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $stmt = mysqli_prepare(
                    $koneksi,
                    "UPDATE users
                     SET username = ?,
                         password = ?,
                         nama = ?,
                         level = ?,
                         status = ?
                     WHERE id_user = ?"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "sssssi",
                    $username,
                    $password_hash,
                    $nama,
                    $level,
                    $status,
                    $id
                );

            } else {

                $stmt = mysqli_prepare(
                    $koneksi,
                    "UPDATE users
                     SET username = ?,
                         nama = ?,
                         level = ?,
                         status = ?
                     WHERE id_user = ?"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "ssssi",
                    $username,
                    $nama,
                    $level,
                    $status,
                    $id
                );
            }

            if (mysqli_stmt_execute($stmt)) {

                // Perbarui session jika admin sedang mengedit akun sendiri
                if (
                    isset($_SESSION['id_user']) &&
                    (int) $_SESSION['id_user'] === $id
                ) {
                    $_SESSION['username'] = $username;
                    $_SESSION['nama'] = $nama;
                    $_SESSION['level'] = $level;
                }

                header("Location: index.php?pesan=edit");
                exit;

            } else {

                $pesan = "Gagal memperbarui data pengguna.";
            }
        }
    }

    // Supaya input tetap menampilkan data terakhir
    $user['username'] = $username;
    $user['nama'] = $nama;
    $user['level'] = $level;
    $user['status'] = $status;
}

$namaAdmin = $_SESSION['nama'] ?? $_SESSION['username'] ?? 'Administrator';
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Pengguna | Bottle.Co</title>

    <style>

        /* =====================================================
           RESET
        ===================================================== */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            background: #F7F9F5;
            color: #26332A;
            font-family: Arial, Helvetica, sans-serif;
        }


        /* =====================================================
           PAGE
        ===================================================== */

        .user-edit-page {
            width: 100%;
            min-height: 100vh;

            padding: 40px 20px 50px;
        }

        .user-edit-wrapper {
            width: 100%;
            max-width: 850px;

            margin: 0 auto;
        }


        /* =====================================================
           BACK
        ===================================================== */

        .edit-back {
            display: inline-flex;
            align-items: center;

            margin-bottom: 20px;

            color: #78837B;

            font-size: 14px;
            font-weight: 400;

            text-decoration: none;

            transition: color .2s ease;
        }

        .edit-back:hover {
            color: #7FA88C;
        }


        /* =====================================================
           CARD
        ===================================================== */

        .user-edit-card {
            width: 100%;

            background: #ffffff;

            border: 1px solid #E1E8E2;
            border-radius: 12px;

            box-shadow: 0 6px 24px rgba(38, 51, 42, .06);

            overflow: hidden;
        }


        /* =====================================================
           TITLE
        ===================================================== */

        .user-edit-heading {
            padding: 28px 32px 25px;

            border-bottom: 1px solid #E1E8E2;
        }

        .heading-label {
            display: block;

            margin-bottom: 7px;

            color: #7FA88C;

            font-size: 12px;
            font-weight: 700;

            letter-spacing: .6px;
        }

        .user-edit-heading h1 {
            margin: 0;

            color: #26332A;

            font-size: 27px;
            font-weight: 600;

            line-height: 1.3;
        }

        .user-edit-heading p {
            margin-top: 7px;

            color: #78837B;

            font-size: 14px;
            font-weight: 400;

            line-height: 1.5;
        }


        /* =====================================================
           FORM BODY
        ===================================================== */

        .user-edit-body {
            padding: 30px 32px 32px;
        }


        /* =====================================================
           ALERT
        ===================================================== */

        .edit-alert {
            display: flex;
            align-items: center;

            margin-bottom: 24px;
            padding: 12px 14px;

            background: #fef2f2;

            border: 1px solid #fecaca;
            border-radius: 7px;

            color: #b91c1c;

            font-size: 14px;
            line-height: 1.5;
        }


        /* =====================================================
           FORM
        ===================================================== */

        .edit-form-group {
            margin-bottom: 21px;
        }

        .edit-form-group label {
            display: block;

            margin-bottom: 8px;

            color: #3F4A43;

            font-size: 14px;
            font-weight: 600;
        }

        .required {
            color: #dc2626;
        }

        .edit-form-control {
            width: 100%;
            height: 44px;

            padding: 0 13px;

            background: #ffffff;

            border: 1px solid #D5DED7;
            border-radius: 7px;

            outline: none;

            color: #26332A;

            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;

            transition:
                border-color .2s ease,
                box-shadow .2s ease;
        }

        .edit-form-control::placeholder {
            color: #98A49D;
        }

        .edit-form-control:hover {
            border-color: #BFD0C3;
        }

        .edit-form-control:focus {
            border-color: #7FA88C;

            box-shadow: 0 0 0 3px rgba(127, 168, 140, .10);
        }

        select.edit-form-control {
            cursor: pointer;
        }


        /* =====================================================
           TWO COLUMNS
        ===================================================== */

        .edit-form-row {
            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 20px;
        }


        /* =====================================================
           PASSWORD INFO
        ===================================================== */

        .password-info {
            display: block;

            margin-top: 7px;

            color: #98A49D;

            font-size: 12px;
            line-height: 1.5;
        }


        /* =====================================================
           ACTIONS
        ===================================================== */

        .edit-form-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 12px;

            margin-top: 28px;
            padding-top: 23px;

            border-top: 1px solid #E1E8E2;
        }

        .edit-btn {
            height: 43px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            padding: 0 18px;

            border-radius: 7px;

            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            font-weight: 600;

            text-decoration: none;

            cursor: pointer;

            transition:
                background .2s ease,
                border-color .2s ease,
                color .2s ease,
                transform .2s ease;
        }

        .edit-btn-back {
            background: #ffffff;

            border: 1px solid #D5DED7;

            color: #52665B;
        }

        .edit-btn-back:hover {
            background: #F7F9F5;

            border-color: #BFD0C3;

            color: #66846F;
        }

        .edit-btn-save {
            background: #7FA88C;

            border: 1px solid #7FA88C;

            color: #ffffff;
        }

        .edit-btn-save:hover {
            background: #66846F;

            border-color: #66846F;

            transform: translateY(-1px);
        }


        /* =====================================================
           FOOTER
        ===================================================== */

        .edit-footer {
            margin-top: 18px;

            text-align: center;

            color: #98A49D;

            font-size: 12px;
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 700px) {

            .user-edit-page {
                padding: 28px 15px 40px;
            }

            .user-edit-heading {
                padding: 24px 22px 21px;
            }

            .user-edit-body {
                padding: 24px 22px 27px;
            }

            .edit-form-row {
                grid-template-columns: 1fr;

                gap: 0;
            }

        }


        @media (max-width: 500px) {

            .user-edit-page {
                padding: 20px 12px 30px;
            }

            .user-edit-heading h1 {
                font-size: 24px;
            }

            .user-edit-heading p {
                font-size: 13px;
            }

            .user-edit-body {
                padding: 21px 18px 23px;
            }

            .edit-form-actions {
                flex-direction: column-reverse;

                align-items: stretch;
            }

            .edit-btn {
                width: 100%;
            }

        }

    </style>

</head>

<body>

    <main class="user-edit-page">

        <div class="user-edit-wrapper">


            <section class="user-edit-card">


                <!-- HEADER FORM -->

                <div class="user-edit-heading">

                    <span class="heading-label">
                        DATA PENGGUNA
                    </span>

                    <h1>
                        Edit Pengguna
                    </h1>

                    <p>
                        Perbarui informasi akun pengguna Bottle.Co.
                    </p>

                </div>


                <!-- FORM -->

                <div class="user-edit-body">

                    <?php if ($pesan !== ''): ?>

                        <div class="edit-alert">
                            <?= htmlspecialchars($pesan); ?>
                        </div>

                    <?php endif; ?>


                    <form method="POST">


                        <!-- NAMA + USERNAME -->

                        <div class="edit-form-row">

                            <div class="edit-form-group">

                                <label for="nama">
                                    Nama Lengkap
                                    <span class="required">*</span>
                                </label>

                                <input
                                    type="text"
                                    id="nama"
                                    name="nama"
                                    class="edit-form-control"
                                    maxlength="100"
                                    value="<?= htmlspecialchars($user['nama']); ?>"
                                    required
                                >

                            </div>


                            <div class="edit-form-group">

                                <label for="username">
                                    Username
                                    <span class="required">*</span>
                                </label>

                                <input
                                    type="text"
                                    id="username"
                                    name="username"
                                    class="edit-form-control"
                                    maxlength="50"
                                    value="<?= htmlspecialchars($user['username']); ?>"
                                    required
                                >

                            </div>

                        </div>


                        <!-- PASSWORD -->

                        <div class="edit-form-group">

                            <label for="password">
                                Password Baru
                            </label>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="edit-form-control"
                                placeholder="Kosongkan jika tidak ingin mengganti password"
                            >

                            <span class="password-info">
                                Kosongkan apabila password tidak ingin diubah.
                                Jika diisi, minimal 6 karakter.
                            </span>

                        </div>


                        <!-- LEVEL + STATUS -->

                        <div class="edit-form-row">

                            <div class="edit-form-group">

                                <label for="level">
                                    Level
                                </label>

                                <select
                                    id="level"
                                    name="level"
                                    class="edit-form-control"
                                >

                                    <option
                                        value="kasir"
                                        <?= $user['level'] === 'kasir' ? 'selected' : ''; ?>
                                    >
                                        Kasir
                                    </option>

                                    <option
                                        value="admin"
                                        <?= $user['level'] === 'admin' ? 'selected' : ''; ?>
                                    >
                                        Admin
                                    </option>

                                </select>

                            </div>


                            <div class="edit-form-group">

                                <label for="status">
                                    Status
                                </label>

                                <select
                                    id="status"
                                    name="status"
                                    class="edit-form-control"
                                >

                                    <option
                                        value="aktif"
                                        <?= $user['status'] === 'aktif' ? 'selected' : ''; ?>
                                    >
                                        Aktif
                                    </option>

                                    <option
                                        value="nonaktif"
                                        <?= $user['status'] === 'nonaktif' ? 'selected' : ''; ?>
                                    >
                                        Nonaktif
                                    </option>

                                </select>

                            </div>

                        </div>


                        <!-- BUTTON -->

                        <div class="edit-form-actions">

                            <a
                                href="index.php"
                                class="edit-btn edit-btn-back"
                            >
                                ← Kembali
                            </a>

                            <button
                                type="submit"
                                name="simpan"
                                class="edit-btn edit-btn-save"
                            >
                                Simpan Perubahan
                            </button>

                        </div>

                    </form>

                </div>

            </section>


            <p class="edit-footer">
                Bottle.Co &nbsp;•&nbsp; Sistem Informasi Penjualan
            </p>

        </div>

    </main>

</body>

</html>