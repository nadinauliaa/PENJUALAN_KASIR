<?php
session_start();

if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

include "../../koneksi.php";

date_default_timezone_set('Asia/Jakarta');

$pesan = '';
$tipe_pesan = '';

if (isset($_POST['simpan'])) {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $nama     = trim($_POST['nama'] ?? '');
    $level    = $_POST['level'] ?? 'kasir';
    $status   = $_POST['status'] ?? 'aktif';

    if ($username === '' || $password === '' || $nama === '') {
        $pesan = "Semua data wajib diisi.";
        $tipe_pesan = "error";
    } elseif (strlen($username) > 50) {
        $pesan = "Username maksimal 50 karakter.";
        $tipe_pesan = "error";
    } elseif (strlen($password) < 6) {
        $pesan = "Password minimal 6 karakter.";
        $tipe_pesan = "error";
    } elseif (!in_array($level, ['admin', 'kasir'], true)) {
        $pesan = "Level pengguna tidak valid.";
        $tipe_pesan = "error";
    } elseif (!in_array($status, ['aktif', 'nonaktif'], true)) {
        $pesan = "Status pengguna tidak valid.";
        $tipe_pesan = "error";
    } else {

        // Cek username
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id_user FROM users WHERE username = ? LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $hasil = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($hasil) > 0) {

            $pesan = "Username sudah digunakan.";
            $tipe_pesan = "error";

        } else {

            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO users (username, password, nama, level, status)
                 VALUES (?, ?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sssss",
                $username,
                $password_hash,
                $nama,
                $level,
                $status
            );

            if (mysqli_stmt_execute($stmt)) {

                header("Location: index.php?pesan=tambah");
                exit;

            } else {

                $pesan = "Gagal menambahkan pengguna.";
                $tipe_pesan = "error";
            }
        }
    }
}

$namaAdmin = $_SESSION['nama'] ?? $_SESSION['username'] ?? 'Administrator';
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Tambah Pengguna | Bottle.Co</title>

    <style>

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            min-height: 100%;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #F7F9F5;
            color: #26332A;
        }


        /* =========================================
           HALAMAN
        ========================================= */

        .user-form-page {

            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 45px 25px;

        }


        /* =========================================
           CARD
        ========================================= */

        .user-form-card {

            width: 100%;

            max-width: 900px;

            background: #fff;

            border: 1px solid #E1E8E2;

            border-radius: 16px;

            box-shadow:
                0 12px 35px rgba(38, 51, 42, .07);

            overflow: hidden;

        }


        /* =========================================
           CARD HEADER
        ========================================= */

        .user-form-header {

            padding: 32px 36px 28px;

            border-bottom: 1px solid #E1E8E2;

        }

        .user-form-header small {

            display: block;

            margin-bottom: 8px;

            color: #7FA88C;

            font-size: 12px;

            font-weight: 700;

            letter-spacing: .8px;

        }

        .user-form-header h1 {

            margin: 0 0 8px;

            color: #26332A;

            font-size: 28px;

            font-weight: 700;

            line-height: 1.25;

        }

        .user-form-header p {

            margin: 0;

            color: #78837B;

            font-size: 14px;

            line-height: 1.6;

        }


        /* =========================================
           BODY
        ========================================= */

        .user-form-body {

            padding: 32px 36px 34px;

        }


        /* =========================================
           ALERT
        ========================================= */

        .alert {

            margin-bottom: 24px;

            padding: 13px 16px;

            border-radius: 9px;

            font-size: 14px;

        }

        .alert-error {

            background: #fff5f5;

            border: 1px solid #fecaca;

            color: #b91c1c;

        }


        /* =========================================
           FORM
        ========================================= */

        .form-row {

            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 22px;

        }

        .form-group {

            margin-bottom: 23px;

        }

        .form-group label {

            display: block;

            margin-bottom: 9px;

            color: #3F4A43;

            font-size: 14px;

            font-weight: 600;

        }

        .form-group label span {

            color: #dc2626;

        }


        /* =========================================
           INPUT
        ========================================= */

        .form-control {

            width: 100%;

            height: 47px;

            padding: 0 14px;

            border: 1px solid #D5DED7;

            border-radius: 9px;

            background: #fff;

            color: #26332A;

            font-family: Arial, Helvetica, sans-serif;

            font-size: 14px;

            outline: none;

            transition: .2s ease;

        }

        .form-control::placeholder {

            color: #98A49D;

        }

        .form-control:focus {

            border-color: #7FA88C;

            box-shadow:
                0 0 0 3px rgba(127, 168, 140, .10);

        }

        select.form-control {

            cursor: pointer;

        }


        /* =========================================
           INFO PASSWORD
        ========================================= */

        .password-info {

            display: block;

            margin-top: 7px;

            color: #98A49D;

            font-size: 12px;

        }


        /* =========================================
           BUTTON
        ========================================= */

        .form-actions {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

            margin-top: 5px;

            padding-top: 25px;

            border-top: 1px solid #E1E8E2;

        }

        .btn-form {

            min-height: 45px;

            padding: 0 21px;

            border-radius: 9px;

            font-family: Arial, Helvetica, sans-serif;

            font-size: 14px;

            font-weight: 600;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            text-decoration: none;

            cursor: pointer;

            transition: .2s ease;

        }


        /* KEMBALI */

        .btn-back {

            border: 1px solid #D5DED7;

            background: #fff;

            color: #52665B;

        }

        .btn-back:hover {

            background: #F7F9F5;

        }


        /* SIMPAN */

        .btn-save {

            border: none;

            background: #7FA88C;

            color: #fff;

        }

        .btn-save:hover {

            background: #66846F;

            transform: translateY(-1px);

        }


        /* =========================================
           RESPONSIVE
        ========================================= */

        @media (max-width: 700px) {

            .user-form-page {

                padding: 25px 15px;

                align-items: flex-start;

            }

            .form-row {

                grid-template-columns: 1fr;

                gap: 0;

            }

            .user-form-header {

                padding: 26px 24px;

            }

            .user-form-body {

                padding: 26px 24px;

            }

        }


        @media (max-width: 500px) {

            .user-form-page {

                padding: 15px;

            }

            .user-form-header h1 {

                font-size: 24px;

            }

            .user-form-header p {

                font-size: 13px;

            }

            .user-form-body {

                padding: 22px 20px;

            }

            .form-actions {

                flex-direction: column-reverse;

                align-items: stretch;

            }

            .btn-form {

                width: 100%;

            }

        }

    </style>

</head>


<body>


    <main class="user-form-page">


        <div class="user-form-card">


            <!-- JUDUL FORM -->

            <div class="user-form-header">

                <small>
                    PENGGUNA
                </small>

                <h1>
                    Tambah Pengguna
                </h1>

                <p>
                    Tambahkan akun pengguna baru ke dalam sistem Bottle.Co
                </p>

            </div>


            <!-- FORM -->

            <div class="user-form-body">


                <?php if ($pesan !== ''): ?>

                    <div class="alert alert-error">

                        <?= htmlspecialchars($pesan); ?>

                    </div>

                <?php endif; ?>


                <form method="POST">


                    <!-- NAMA + USERNAME -->

                    <div class="form-row">


                        <div class="form-group">

                            <label>
                                Nama Lengkap <span>*</span>
                            </label>

                            <input
                                type="text"
                                name="nama"
                                class="form-control"
                                maxlength="100"
                                placeholder="Masukkan nama lengkap"
                                value="<?= htmlspecialchars($_POST['nama'] ?? ''); ?>"
                                required
                            >

                        </div>


                        <div class="form-group">

                            <label>
                                Username <span>*</span>
                            </label>

                            <input
                                type="text"
                                name="username"
                                class="form-control"
                                maxlength="50"
                                placeholder="Masukkan username"
                                value="<?= htmlspecialchars($_POST['username'] ?? ''); ?>"
                                required
                            >

                        </div>


                    </div>


                    <!-- PASSWORD -->

                    <div class="form-group">

                        <label>
                            Password <span>*</span>
                        </label>

                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            placeholder="Masukkan password"
                            required
                        >

                        <span class="password-info">
                            Password minimal 6 karakter.
                        </span>

                    </div>


                    <!-- LEVEL + STATUS -->

                    <div class="form-row">


                        <div class="form-group">

                            <label>
                                Level
                            </label>

                            <select
                                name="level"
                                class="form-control"
                            >

                                <option
                                    value="kasir"
                                    <?= (($_POST['level'] ?? 'kasir') === 'kasir') ? 'selected' : ''; ?>
                                >
                                    Kasir
                                </option>

                                <option
                                    value="admin"
                                    <?= (($_POST['level'] ?? '') === 'admin') ? 'selected' : ''; ?>
                                >
                                    Admin
                                </option>

                            </select>

                        </div>


                        <div class="form-group">

                            <label>
                                Status
                            </label>

                            <select
                                name="status"
                                class="form-control"
                            >

                                <option
                                    value="aktif"
                                    <?= (($_POST['status'] ?? 'aktif') === 'aktif') ? 'selected' : ''; ?>
                                >
                                    Aktif
                                </option>

                                <option
                                    value="nonaktif"
                                    <?= (($_POST['status'] ?? '') === 'nonaktif') ? 'selected' : ''; ?>
                                >
                                    Nonaktif
                                </option>

                            </select>

                        </div>


                    </div>


                    <!-- BUTTON -->

                    <div class="form-actions">

                        <a
                            href="index.php"
                            class="btn-form btn-back"
                        >
                            ← Kembali
                        </a>

                        <button
                            type="submit"
                            name="simpan"
                            class="btn-form btn-save"
                        >
                            Simpan Pengguna
                        </button>

                    </div>


                </form>

            </div>

        </div>

    </main>


</body>

</html>