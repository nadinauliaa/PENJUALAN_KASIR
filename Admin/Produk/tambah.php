<?php
session_start();

if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

include "../../koneksi.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$error = "";

if (isset($_POST['simpan'])) {

    $nama_produk = trim($_POST['nama_produk'] ?? '');
    $harga       = $_POST['harga'] ?? '';
    $stok        = $_POST['stok'] ?? '';

    if ($nama_produk === '') {
        $error = "Nama produk wajib diisi.";
    } elseif ($harga === '' || !is_numeric($harga) || $harga < 0) {
        $error = "Harga produk tidak valid.";
    } elseif ($stok === '' || !is_numeric($stok) || $stok < 0) {
        $error = "Stok produk tidak valid.";
    } else {

        $harga = (float) $harga;
        $stok  = (int) $stok;

        $stmt = mysqli_prepare(
            $koneksi,
            "INSERT INTO produk (nama_produk, harga, stok)
             VALUES (?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sdi",
            $nama_produk,
            $harga,
            $stok
        );

        mysqli_stmt_execute($stmt);
        $id_baru = mysqli_insert_id($koneksi);
        mysqli_stmt_close($stmt);

        // Proses Upload Gambar Produk
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['gambar']['tmp_name'];
            $fileName = $_FILES['gambar']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($fileExtension, $allowedExtensions)) {
                $uploadFileDir = __DIR__ . '/../../assets/image/produk/';

                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }

                $newFileName = $id_baru . '.' . $fileExtension;
                $dest_path = $uploadFileDir . $newFileName;

                move_uploaded_file($fileTmpPath, $dest_path);
            }
        }

        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Produk | Bottle.Co</title>

    <style>
        * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    min-height: 100%;
}

body.product-form-page {
    min-height: 100vh;
    background: #F7F9F5;
    color: #26332A;
    font-family: "Segoe UI", Arial, Helvetica, sans-serif;
}

.product-form-wrapper {
    width: 100%;
    max-width: 850px;
    margin: 0 auto;
    padding: 42px 25px 50px;
}

.form-back {
    display: inline-flex;
    align-items: center;
    margin-bottom: 22px;
    color: #78837B;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: color .2s ease;
}

.form-back:hover {
    color: #7FA88C;
}

.product-form-card {
    width: 100%;
    background: #fff;
    border: 1px solid #E1E8E2;
    border-radius: 14px;
    padding: 34px 38px 38px;
    box-shadow: 0 8px 28px rgba(38, 51, 42, .06);
}

.product-form-heading {
    display: flex;
    align-items: flex-start;
    gap: 17px;
    padding-bottom: 28px;
    margin-bottom: 27px;
    border-bottom: 1px solid #E1E8E2;
}

.form-icon {
    width: 50px;
    height: 50px;
    flex: 0 0 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: #EAF2EC;
    color: #7FA88C;
    font-size: 25px;
    font-weight: 500;
}

.form-eyebrow {
    display: block;
    margin-bottom: 6px;
    color: #7FA88C;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .9px;
}

.product-form-heading h1 {
    margin: 0;
    color: #26332A;
    font-size: 28px;
    font-weight: 700;
    line-height: 1.25;
}

.product-form-heading p {
    margin-top: 7px;
    color: #78837B;
    font-size: 14px;
    line-height: 1.5;
}

.form-alert {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 23px;
    padding: 13px 15px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    color: #b91c1c;
    font-size: 14px;
    line-height: 1.5;
}

.form-alert span {
    width: 22px;
    height: 22px;
    flex: 0 0 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #dc2626;
    color: #fff;
    font-size: 13px;
    font-weight: 700;
}

.form-group {
    width: 100%;
    margin-bottom: 21px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #3F4A43;
    font-size: 14px;
    font-weight: 600;
}

.form-group input {
    width: 100%;
    height: 46px;
    padding: 0 14px;
    background: #fff;
    border: 1px solid #D5DED7;
    border-radius: 8px;
    outline: none;
    color: #26332A;
    font-family: inherit;
    font-size: 14px;
    transition: border-color .2s ease, box-shadow .2s ease;
}

.form-group input[type="file"] {
    padding: 10px 14px;
    height: auto;
    line-height: normal;
}

.form-group input::placeholder {
    color: #98A49D;
}

.form-group input:focus {
    border-color: #7FA88C;
    box-shadow: 0 0 0 3px rgba(127, 168, 140, .10);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.input-prefix {
    position: relative;
}

.input-prefix > span {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #78837B;
    font-size: 14px;
    font-weight: 600;
    pointer-events: none;
}

.input-prefix input {
    padding-left: 39px;
}

.form-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 29px;
    padding-top: 24px;
    border-top: 1px solid #E1E8E2;
}

.btn-cancel,
.btn-save {
    height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 18px;
    border-radius: 8px;
    font-family: inherit;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: .2s ease;
}

.btn-cancel {
    background: #fff;
    border: 1px solid #D5DED7;
    color: #52665B;
}

.btn-cancel:hover {
    background: #F7F9F5;
    color: #7FA88C;
}

.btn-save {
    gap: 7px;
    background: #7FA88C;
    border: 1px solid #7FA88C;
    color: #fff;
}

.btn-save span {
    font-size: 17px;
}

.btn-save:hover {
    background: #66846F;
    border-color: #66846F;
    transform: translateY(-1px);
}

.form-footer {
    margin-top: 19px;
    text-align: center;
    color: #98A49D;
    font-size: 12px;
}

@media (max-width: 700px) {
    .product-form-wrapper {
        padding: 28px 18px 40px;
    }

    .product-form-card {
        padding: 27px 24px 29px;
    }

    .form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }
}

@media (max-width: 500px) {
    .product-form-wrapper {
        padding: 22px 13px 30px;
    }

    .product-form-card {
        padding: 23px 18px 25px;
    }

    .form-actions {
        flex-direction: column-reverse;
        align-items: stretch;
    }

    .btn-cancel,
    .btn-save {
        width: 100%;
    }
}
    </style>

</head>

<body class="product-form-page">

    <main class="product-form-wrapper">

        <a href="index.php" class="form-back">
            ← Kembali ke Produk
        </a>


        <section class="product-form-card">

            <div class="product-form-heading">

                <div class="form-icon">
                    ＋
                </div>

                <div>
                    <span class="form-eyebrow">
                        DATA PRODUK
                    </span>

                    <h1>
                        Tambah Produk
                    </h1>

                    <p>
                        Tambahkan produk baru beserta foto ke dalam database.
                    </p>
                </div>

            </div>


            <?php if ($error !== ""): ?>

                <div class="form-alert">
                    <span>!</span>

                    <?= htmlspecialchars($error); ?>
                </div>

            <?php endif; ?>


            <form method="POST" enctype="multipart/form-data" autocomplete="off">

                <div class="form-group">

                    <label for="nama_produk">
                        Nama Produk
                    </label>

                    <input
                        type="text"
                        id="nama_produk"
                        name="nama_produk"
                        value="<?= htmlspecialchars($_POST['nama_produk'] ?? ''); ?>"
                        placeholder="Masukkan nama produk"
                        required
                        autofocus
                    >

                </div>


                <div class="form-row">

                    <div class="form-group">

                        <label for="harga">
                            Harga
                        </label>

                        <div class="input-prefix">

                            <span>Rp</span>

                            <input
                                type="number"
                                id="harga"
                                name="harga"
                                value="<?= htmlspecialchars($_POST['harga'] ?? ''); ?>"
                                placeholder="0"
                                min="0"
                                step="0.01"
                                required
                            >

                        </div>

                    </div>


                    <div class="form-group">

                        <label for="stok">
                            Stok
                        </label>

                        <input
                            type="number"
                            id="stok"
                            name="stok"
                            value="<?= htmlspecialchars($_POST['stok'] ?? ''); ?>"
                            placeholder="0"
                            min="0"
                            required
                        >

                    </div>

                </div>


                <div class="form-group">

                    <label for="gambar">
                        Foto Produk (Opsional)
                    </label>

                    <input
                        type="file"
                        id="gambar"
                        name="gambar"
                        accept=".jpg, .jpeg, .png, .webp"
                    >
                    <small style="color: #78837B; font-size: 12px; margin-top: 4px; display: block;">
                        Format yang didukung: JPG, JPEG, PNG, WEBP
                    </small>

                </div>


                <div class="form-actions">

                    <a href="index.php" class="btn-cancel">
                        Batal
                    </a>

                    <button
                        type="submit"
                        name="simpan"
                        class="btn-save"
                    >
                        <span>＋</span>
                        Simpan Produk
                    </button>

                </div>

            </form>

        </section>


        <p class="form-footer">
            Bottle.Co &nbsp;•&nbsp; Sistem Informasi Penjualan
        </p>

    </main>

</body>

</html>