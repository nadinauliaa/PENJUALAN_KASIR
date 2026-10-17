<?php

session_start();

if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

include "../../koneksi.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$nama = "";
$alamat = "";
$no_telp = "";
$error = "";

if (isset($_POST['simpan'])) {

    $nama = trim($_POST['nama_pelanggan'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $no_telp = trim($_POST['no_telp'] ?? '');

    if ($nama === '') {

        $error = "Nama pelanggan wajib diisi.";

    } else {

        $namaDB = mysqli_real_escape_string($koneksi, $nama);
        $alamatDB = mysqli_real_escape_string($koneksi, $alamat);
        $telpDB = mysqli_real_escape_string($koneksi, $no_telp);

        mysqli_query(
            $koneksi,
            "INSERT INTO pelanggan
            (nama_pelanggan, alamat, no_telp)
            VALUES
            ('$namaDB', '$alamatDB', '$telpDB')"
        );

        header("Location: index.php");
        exit;
    }
}

$namaAdmin = $_SESSION['nama'] ?? 'Administrator';

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Tambah Pelanggan | Bottle.Co</title>

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
    font-family: Arial, Helvetica, sans-serif;
    background: #F7F9F5;
    color: #26332A;
}


/* =====================================================
   PAGE
===================================================== */

.customer-page {

    min-height: 100vh;

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 45px 24px;

}


/* =====================================================
   CARD
===================================================== */

.customer-card {

    width: 100%;

    max-width: 850px;

    background: #ffffff;

    border: 1px solid #E1E8E2;

    border-radius: 16px;

    box-shadow:
        0 12px 35px rgba(38, 51, 42, .07);

    overflow: hidden;

}


/* =====================================================
   CARD HEADER
===================================================== */

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


/* =====================================================
   ERROR
===================================================== */

.customer-error {

    margin: 24px 36px 0;

    padding: 13px 16px;

    background: #fff5f5;

    border: 1px solid #fecaca;

    border-radius: 9px;

    color: #b91c1c;

    font-size: 14px;

    line-height: 1.5;

}


/* =====================================================
   FORM
===================================================== */

.customer-form {

    padding: 32px 36px 0;

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

    color: #dc2626;

}


/* =====================================================
   INPUT & TEXTAREA
===================================================== */

.form-control {

    width: 100%;

    border: 1px solid #D5DED7;

    border-radius: 9px;

    background: #ffffff;

    color: #26332A;

    padding: 0 14px;

    font-family: Arial, Helvetica, sans-serif;

    font-size: 14px;

    outline: none;

    transition: .2s ease;

}

input.form-control {

    height: 47px;

}

textarea.form-control {

    min-height: 125px;

    padding: 13px 14px;

    resize: vertical;

    line-height: 1.5;

}

.form-control::placeholder {

    color: #98a49D;

}

.form-control:focus {

    border-color: #7FA88C;

    box-shadow:
        0 0 0 3px rgba(127, 168, 140, .10);

}


/* =====================================================
   ACTIONS
===================================================== */

.customer-actions {

    margin-top: 8px;

    padding: 25px 36px 30px;

    border-top: 1px solid #E1E8E2;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

}

.btn {

    min-height: 45px;

    padding: 0 20px;

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


/* KEMBALI */

.btn-back {

    background: #ffffff;

    border: 1px solid #D5DED7;

    color: #78837B;

}

.btn-back:hover {

    background: #F7F9F5;

    border-color: #BFD0C3;

    color: #66846F;

}


/* SIMPAN */

.btn-save {

    background: #7FA88C;

    border: 1px solid #7FA88C;

    color: #ffffff;

    box-shadow:
        0 4px 10px rgba(127, 168, 140, .16);

}

.btn-save:hover {

    background: #66846F;

    border-color: #66846F;

    transform: translateY(-1px);

}


/* =====================================================
   RESPONSIVE
===================================================== */

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

        padding: 25px 22px 0;

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


            <!-- =================================================
                 JUDUL
            ================================================== -->

            <div class="customer-card-header">

                <span class="customer-label">
                    PELANGGAN
                </span>

                <h1>
                    Tambah Pelanggan
                </h1>

                <p>
                    Tambahkan data pelanggan baru ke dalam sistem Bottle.Co
                </p>

            </div>


            <!-- =================================================
                 ERROR
            ================================================== -->

            <?php if ($error !== "") : ?>

                <div class="customer-error">

                    <?= htmlspecialchars($error); ?>

                </div>

            <?php endif; ?>


            <!-- =================================================
                 FORM
            ================================================== -->

            <form method="POST">


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


                <!-- =================================================
                     BUTTON
                ================================================== -->

                <div class="customer-actions">

                    <a
                        href="index.php"
                        class="btn btn-back"
                    >
                        ← Kembali
                    </a>


                    <button
                        type="submit"
                        name="simpan"
                        class="btn btn-save"
                    >
                        ✓ Simpan Pelanggan
                    </button>

                </div>


            </form>


        </section>

    </main>


</body>

</html>