<?php

session_start();

if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'kasir') {
    header("Location: ../../login.php");
    exit;
}

include "../../koneksi.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


/* =========================================================
   AMBIL ID
========================================================= */

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}


/* =========================================================
   AMBIL DATA PELANGGAN
========================================================= */

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT
        id_pelanggan,
        nama_pelanggan,
        alamat,
        no_telp
     FROM pelanggan
     WHERE id_pelanggan = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {

    mysqli_stmt_close($stmt);

    header("Location: index.php");
    exit;
}

$data = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


$nama   = $data['nama_pelanggan'];
$alamat = $data['alamat'];
$no_telp = $data['no_telp'];

$error = "";


/* =========================================================
   UPDATE
========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama    = trim($_POST['nama_pelanggan'] ?? '');
    $alamat  = trim($_POST['alamat'] ?? '');
    $no_telp = trim($_POST['no_telp'] ?? '');


    if ($nama === '') {

        $error = "Nama pelanggan wajib diisi.";

    } else {

        $stmtUpdate = mysqli_prepare(
            $koneksi,
            "UPDATE pelanggan
             SET
                nama_pelanggan = ?,
                alamat = ?,
                no_telp = ?
             WHERE id_pelanggan = ?"
        );

        mysqli_stmt_bind_param(
            $stmtUpdate,
            "sssi",
            $nama,
            $alamat,
            $no_telp,
            $id
        );

        mysqli_stmt_execute($stmtUpdate);

        mysqli_stmt_close($stmtUpdate);


        /* =============================================
           BERHASIL UPDATE
           LANGSUNG KEMBALI KE DATA PELANGGAN KASIR
        ============================================= */

        header("Location: index.php");
        exit;
    }
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

    <title>Edit Pelanggan | Bottle.Co</title>

    <style>

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
            font-family: Arial, Helvetica, sans-serif;
            background: #F7F9F5;
            color: #26332A;
        }


        /* =========================================
           PAGE
        ========================================= */

        .customer-page {
            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 40px 24px;
        }


        /* =========================================
           CARD
        ========================================= */

        .customer-card {
            width: 100%;

            max-width: 850px;

            background: #FFFFFF;

            border: 1px solid #E1E8E2;

            border-radius: 16px;

            box-shadow:
                0 12px 35px rgba(38, 51, 42, .07);

            overflow: hidden;
        }


        /* =========================================
           HEADER
        ========================================= */

        .customer-card-header {
            padding: 32px 36px 28px;

            border-bottom: 1px solid #E1E8E2;
        }

        .customer-label {
            display: block;

            margin-bottom: 8px;

            color: #7FA88C;

            font-size: 12px;

            font-weight: 700;

            letter-spacing: .8px;
        }

        .customer-card-header h1 {
            margin: 0 0 8px;

            color: #26332A;

            font-size: 28px;

            font-weight: 700;

            line-height: 1.25;
        }

        .customer-card-header p {
            margin: 0;

            color: #78837B;

            font-size: 14px;

            line-height: 1.6;
        }


        /* =========================================
           ERROR
        ========================================= */

        .customer-error {
            margin: 24px 36px 0;

            padding: 13px 16px;

            background: #FFF5F5;

            border: 1px solid #F1B8B4;

            border-radius: 9px;

            color: #D92D20;

            font-size: 14px;
        }


        /* =========================================
           FORM
        ========================================= */

        .customer-form {
            padding: 32px 36px 8px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;

            margin-bottom: 9px;

            color: #52665B;

            font-size: 14px;

            font-weight: 600;
        }

        .form-group label span {
            color: #D92D20;
        }


        /* =========================================
           INPUT
        ========================================= */

        .form-control {
            width: 100%;

            border: 1px solid #D5DED7;

            border-radius: 9px;

            background: #FFFFFF;

            color: #26332A;

            font-family: Arial, Helvetica, sans-serif;

            font-size: 14px;

            outline: none;

            transition: .2s ease;
        }

        input.form-control {
            height: 47px;

            padding: 0 14px;
        }

        textarea.form-control {
            min-height: 125px;

            padding: 13px 14px;

            resize: vertical;

            line-height: 1.5;
        }

        .form-control::placeholder {
            color: #98A49D;
        }

        .form-control:focus {
            border-color: #7FA88C;

            box-shadow:
                0 0 0 3px rgba(127, 168, 140, .10);
        }


        /* =========================================
           ACTION
        ========================================= */

        .customer-actions {
            padding: 25px 36px 30px;

            border-top: 1px solid #E1E8E2;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;
        }

        .btn {
            min-height: 45px;

            padding: 0 21px;

            border-radius: 9px;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            font-family: Arial, Helvetica, sans-serif;

            font-size: 14px;

            font-weight: 600;

            text-decoration: none;

            cursor: pointer;

            transition: .2s ease;
        }


        /* =========================================
           KEMBALI
        ========================================= */

        .btn-back {
            background: #FFFFFF;

            border: 1px solid #D5DED7;

            color: #78837B;
        }

        .btn-back:hover {
            background: #F7F9F5;

            border-color: #BFD0C3;

            color: #66846F;
        }


        /* =========================================
           SIMPAN
        ========================================= */

        .btn-save {
            background: #7FA88C;

            border: 1px solid #7FA88C;

            color: #FFFFFF;

            box-shadow:
                0 4px 10px rgba(127, 168, 140, .16);
        }

        .btn-save:hover {
            background: #66846F;

            border-color: #66846F;

            transform: translateY(-1px);
        }


        /* =========================================
           RESPONSIVE
        ========================================= */

        @media (max-width: 600px) {

            .customer-page {
                padding: 20px 14px;

                align-items: flex-start;
            }

            .customer-card-header {
                padding: 25px 22px;
            }

            .customer-card-header h1 {
                font-size: 24px;
            }

            .customer-card-header p {
                font-size: 13px;
            }

            .customer-form {
                padding: 25px 22px 5px;
            }

            .customer-error {
                margin-left: 22px;

                margin-right: 22px;
            }

            .customer-actions {
                padding: 22px;

                flex-direction: column-reverse;

                align-items: stretch;
            }

            .btn {
                width: 100%;
            }

        }

    </style>

</head>


<body>


<main class="customer-page">


    <section class="customer-card">


        <!-- HEADER -->

        <div class="customer-card-header">

            <span class="customer-label">
                KASIR / PELANGGAN
            </span>

            <h1>
                Edit Pelanggan
            </h1>

            <p>
                Perbarui data pelanggan yang tersimpan dalam sistem Bottle.Co.
            </p>

        </div>


        <!-- ERROR -->

        <?php if ($error !== "") : ?>

            <div class="customer-error">
                <?= htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>


        <!-- FORM -->

        <form method="POST" autocomplete="off">


            <div class="customer-form">


                <!-- NAMA -->

                <div class="form-group">

                    <label for="nama_pelanggan">

                        Nama Pelanggan

                        <span>*</span>

                    </label>

                    <input
                        type="text"
                        id="nama_pelanggan"
                        name="nama_pelanggan"
                        class="form-control"
                        placeholder="Masukkan nama pelanggan"
                        maxlength="255"
                        value="<?= htmlspecialchars($nama); ?>"
                        required
                    >

                </div>


                <!-- ALAMAT -->

                <div class="form-group">

                    <label for="alamat">
                        Alamat
                    </label>

                    <textarea
                        id="alamat"
                        name="alamat"
                        class="form-control"
                        placeholder="Masukkan alamat pelanggan"
                    ><?= htmlspecialchars($alamat); ?></textarea>

                </div>


                <!-- TELEPON -->

                <div class="form-group">

                    <label for="no_telp">
                        No. Telepon
                    </label>

                    <input
                        type="text"
                        id="no_telp"
                        name="no_telp"
                        class="form-control"
                        placeholder="Contoh: 081234567890"
                        maxlength="15"
                        value="<?= htmlspecialchars($no_telp); ?>"
                    >

                </div>


            </div>


            <!-- BUTTON -->

            <div class="customer-actions">

                <a
                    href="index.php"
                    class="btn btn-back"
                >
                    ← Kembali
                </a>


                <button
                    type="submit"
                    class="btn btn-save"
                >
                    ✓ Simpan Perubahan
                </button>

            </div>


        </form>


    </section>

</main>


</body>

</html>