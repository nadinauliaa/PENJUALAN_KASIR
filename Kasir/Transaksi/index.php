<?php
session_start();

if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'kasir') {
    header("Location: ../../login.php");
    exit;
}

include "../../koneksi.php";

date_default_timezone_set('Asia/Jakarta');

$namaKasir = $_SESSION['nama'] ?? 'Kasir';

/* =========================
   PROSES SIMPAN TRANSAKSI
========================= */

if (isset($_POST['simpan_transaksi'])) {

    $id_pelanggan = !empty($_POST['id_pelanggan'])
        ? (int) $_POST['id_pelanggan']
        : null;

    $id_produk = $_POST['id_produk'] ?? [];
    $jumlah_produk = $_POST['jumlah_produk'] ?? [];

    if (empty($id_produk)) {
        $_SESSION['pesan_error'] = "Produk belum dipilih.";
        header("Location: index.php");
        exit;
    }

    mysqli_begin_transaction($koneksi);

    try {

        $total_harga = 0;
        $detail_transaksi = [];

        /* CEK PRODUK DAN HITUNG TOTAL */

        for ($i = 0; $i < count($id_produk); $i++) {

            $produk_id = (int) $id_produk[$i];
            $jumlah = (int) $jumlah_produk[$i];

            if ($produk_id <= 0 || $jumlah <= 0) {
                continue;
            }

            $queryProduk = mysqli_query(
                $koneksi,
                "SELECT id_produk, nama_produk, harga, stok
                 FROM produk
                 WHERE id_produk = $produk_id
                 FOR UPDATE"
            );

            if (!$queryProduk || mysqli_num_rows($queryProduk) === 0) {
                throw new Exception("Produk tidak ditemukan.");
            }

            $produk = mysqli_fetch_assoc($queryProduk);

            if ($jumlah > (int) $produk['stok']) {
                throw new Exception(
                    "Stok produk \"" .
                    $produk['nama_produk'] .
                    "\" tidak mencukupi."
                );
            }

            $subtotal = (float) $produk['harga'] * $jumlah;

            $total_harga += $subtotal;

            $detail_transaksi[] = [
                'id_produk' => $produk_id,
                'jumlah' => $jumlah,
                'subtotal' => $subtotal
            ];
        }

        if (empty($detail_transaksi)) {
            throw new Exception("Produk belum dipilih.");
        }

        /* SIMPAN PENJUALAN */

        if ($id_pelanggan === null) {

            $queryPenjualan = mysqli_query(
                $koneksi,
                "INSERT INTO penjualan
                (tanggal_penjualan, total_harga, id_pelanggan)
                VALUES
                (NOW(), $total_harga, NULL)"
            );

        } else {

            $queryPenjualan = mysqli_query(
                $koneksi,
                "INSERT INTO penjualan
                (tanggal_penjualan, total_harga, id_pelanggan)
                VALUES
                (NOW(), $total_harga, $id_pelanggan)"
            );
        }

        if (!$queryPenjualan) {
            throw new Exception("Transaksi gagal disimpan.");
        }

        $id_penjualan = mysqli_insert_id($koneksi);

        /* SIMPAN DETAIL DAN KURANGI STOK */

        foreach ($detail_transaksi as $detail) {

            $produk_id = $detail['id_produk'];
            $jumlah = $detail['jumlah'];
            $subtotal = $detail['subtotal'];

            $queryDetail = mysqli_query(
                $koneksi,
                "INSERT INTO detailpenjualan
                (id_penjualan, id_produk, jumlah_produk, subtotal)
                VALUES
                ($id_penjualan, $produk_id, $jumlah, $subtotal)"
            );

            if (!$queryDetail) {
                throw new Exception("Detail transaksi gagal disimpan.");
            }

            $queryStok = mysqli_query(
                $koneksi,
                "UPDATE produk
                 SET stok = stok - $jumlah
                 WHERE id_produk = $produk_id"
            );

            if (!$queryStok) {
                throw new Exception("Stok produk gagal diperbarui.");
            }
        }

       mysqli_commit($koneksi);

        header("Location: ../Riwayat/cetak.php?id=" . $id_penjualan);
        exit;

        } catch (Exception $e) {

            mysqli_rollback($koneksi);

            $_SESSION['pesan_error'] = $e->getMessage();

            header("Location: index.php");
            exit;
        }

    }

/* =========================
   AMBIL DATA PRODUK
========================= */

$queryProduk = mysqli_query(
    $koneksi,
    "SELECT id_produk, nama_produk, harga, stok
     FROM produk
     WHERE stok > 0
     ORDER BY nama_produk ASC"
);


/* =========================
   AMBIL DATA PELANGGAN
========================= */

$queryPelanggan = mysqli_query(
    $koneksi,
    "SELECT id_pelanggan, nama_pelanggan
     FROM pelanggan
     ORDER BY nama_pelanggan ASC"
);


/* =========================
   PESAN
========================= */

$pesanSukses = $_SESSION['pesan_sukses'] ?? '';
$pesanError = $_SESSION['pesan_error'] ?? '';

unset($_SESSION['pesan_sukses']);
unset($_SESSION['pesan_error']);

?>
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Transaksi Penjualan - Bottle.Co</title>

    <link
        rel="stylesheet"
        href="/PENJUALAN/assets/css/bootstrap.min.css"
    >

    <link
        rel="stylesheet"
        href="/PENJUALAN/assets/css/style.css?v=20"
    >

    <link
        rel="stylesheet"
        href="/PENJUALAN/assets/css/kasir.css?v=2"
    >

    <link
        rel="stylesheet"
        href="/PENJUALAN/assets/css/transaksi.css?v=1"
    >

</head>

    <!-- CONTENT -->

    <main>

        <div class="transaksi-container">


            <!-- HEADER -->

            <div class="transaksi-header">

                <div>

                    <a
                        href="../index.php"
                        class="transaksi-back"
                    >
                        ← Kembali ke Dashboard
                    </a>

                    <span class="transaksi-label">
                        TRANSAKSI KASIR
                    </span>

                    <h1>
                        Transaksi Penjualan
                    </h1>

                    <p>
                        Masukkan produk yang dibeli pelanggan
                        untuk mencatat transaksi penjualan.
                    </p>

                </div>

            </div>


            <!-- PESAN SUKSES -->

            <?php if ($pesanSukses): ?>

                <div class="alert alert-success transaksi-alert">

                    <?= htmlspecialchars($pesanSukses); ?>

                </div>

            <?php endif; ?>


            <!-- PESAN ERROR -->

            <?php if ($pesanError): ?>

                <div class="alert alert-danger transaksi-alert">

                    <?= htmlspecialchars($pesanError); ?>

                </div>

            <?php endif; ?>


            <form
                method="POST"
                id="formTransaksi"
            >

                <div class="transaksi-layout">


                    <!-- BAGIAN KIRI -->

                    <div class="transaksi-main-card">


                        <!-- PELANGGAN -->

                        <div class="transaksi-card-header">

                            <div>

                                <span>
                                    DATA TRANSAKSI
                                </span>

                                <h2>
                                    Informasi Pelanggan
                                </h2>

                            </div>

                        </div>


                        <div class="transaksi-form-group">

                            <label for="id_pelanggan">
                                Pelanggan
                            </label>

                            <select
                                name="id_pelanggan"
                                id="id_pelanggan"
                                class="form-select"
                            >

                                <option value="">
                                    Pelanggan Umum
                                </option>

                                <?php while (
                                    $pelanggan =
                                    mysqli_fetch_assoc(
                                        $queryPelanggan
                                    )
                                ): ?>

                                    <option
                                        value="<?= $pelanggan['id_pelanggan']; ?>"
                                    >

                                        <?= htmlspecialchars(
                                            $pelanggan['nama_pelanggan']
                                        ); ?>

                                    </option>

                                <?php endwhile; ?>

                            </select>

                            <small>
                                Pilih pelanggan jika transaksi
                                memiliki data pelanggan.
                            </small>

                        </div>


                        <!-- PRODUK -->

                        <div class="transaksi-card-header produk-header">

                            <div>

                                <span>
                                    PRODUK
                                </span>

                                <h2>
                                    Daftar Produk
                                </h2>

                            </div>

                            <button
                                type="button"
                                class="btn btn-primary btn-tambah-produk"
                                id="tambahProduk"
                            >
                                + Tambah Produk
                            </button>

                        </div>


                        <div class="produk-table-wrapper">

                            <table class="table transaksi-table">

                                <thead>

                                    <tr>

                                        <th>
                                            Produk
                                        </th>

                                        <th width="120">
                                            Harga
                                        </th>

                                        <th width="110">
                                            Jumlah
                                        </th>

                                        <th width="140">
                                            Subtotal
                                        </th>

                                        <th width="55">
                                        </th>

                                    </tr>

                                </thead>

                                <tbody id="produkContainer">

                                    <tr class="produk-row">

                                        <td>

                                            <select
                                                name="id_produk[]"
                                                class="form-select produk-select"
                                                required
                                            >

                                                <option
                                                    value=""
                                                    data-harga="0"
                                                    data-stok="0"
                                                >
                                                    Pilih Produk
                                                </option>

                                                <?php

                                                mysqli_data_seek(
                                                    $queryProduk,
                                                    0
                                                );

                                                while (
                                                    $produk =
                                                    mysqli_fetch_assoc(
                                                        $queryProduk
                                                    )
                                                ):

                                                ?>

                                                    <option
                                                        value="<?= $produk['id_produk']; ?>"
                                                        data-harga="<?= $produk['harga']; ?>"
                                                        data-stok="<?= $produk['stok']; ?>"
                                                    >

                                                        <?= htmlspecialchars(
                                                            $produk['nama_produk']
                                                        ); ?>

                                                        —
                                                        Stok:
                                                        <?= $produk['stok']; ?>

                                                    </option>

                                                <?php endwhile; ?>

                                            </select>

                                        </td>


                                        <td>

                                            <div class="harga-produk">
                                                Rp 0
                                            </div>

                                        </td>


                                        <td>

                                            <input
                                                type="number"
                                                name="jumlah_produk[]"
                                                class="form-control jumlah-produk"
                                                min="1"
                                                value="1"
                                                disabled
                                                required
                                            >

                                            <small class="stok-info">
                                                Pilih produk
                                            </small>

                                        </td>


                                        <td>

                                            <strong class="subtotal-produk">
                                                Rp 0
                                            </strong>

                                        </td>


                                        <td>

                                            <button
                                                type="button"
                                                class="hapus-produk"
                                                title="Hapus produk"
                                            >
                                                ×
                                            </button>

                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>


                        <div class="produk-info">

                            <span>
                                Produk yang tersedia:
                                <strong>
                                    <?= mysqli_num_rows($queryProduk); ?>
                                </strong>
                            </span>

                            <span>
                                Pastikan jumlah tidak melebihi stok.
                            </span>

                        </div>

                    </div>


                    <!-- BAGIAN KANAN -->

                    <aside class="transaksi-summary-card">

                        <div class="summary-label">
                            RINGKASAN
                        </div>

                        <h2>
                            Total Transaksi
                        </h2>


                        <div class="summary-detail">

                            <span>
                                Jumlah Produk
                            </span>

                            <strong id="jumlahItem">
                                0
                            </strong>

                        </div>


                        <div class="summary-detail">

                            <span>
                                Total Item
                            </span>

                            <strong id="totalItem">
                                0
                            </strong>

                        </div>


                        <div class="summary-total">

                            <span>
                                Total Pembayaran
                            </span>

                            <strong id="totalHarga">
                                Rp 0
                            </strong>

                        </div>


                        <button
                            type="submit"
                            name="simpan_transaksi"
                            class="btn btn-primary btn-simpan-transaksi"
                        >

                            Simpan Transaksi

                        </button>


                        <a
                            href="../index.php"
                            class="btn btn-light btn-batal-transaksi"
                        >

                            Batal

                        </a>

                    </aside>

                </div>

            </form>

        </div>

    </main>


    <script>

        const produkContainer =
            document.getElementById('produkContainer');

        const tambahProduk =
            document.getElementById('tambahProduk');

        const jumlahItem =
            document.getElementById('jumlahItem');

        const totalItem =
            document.getElementById('totalItem');

        const totalHarga =
            document.getElementById('totalHarga');


        function formatRupiah(angka) {

            return 'Rp ' +
                new Intl.NumberFormat(
                    'id-ID'
                ).format(angka);

        }


        function updateRow(row) {

            const select =
                row.querySelector('.produk-select');

            const jumlah =
                row.querySelector('.jumlah-produk');

            const harga =
                row.querySelector('.harga-produk');

            const subtotal =
                row.querySelector('.subtotal-produk');

            const stokInfo =
                row.querySelector('.stok-info');


            const option =
                select.options[
                    select.selectedIndex
                ];


            const hargaProduk =
                Number(
                    option.dataset.harga || 0
                );

            const stok =
                Number(
                    option.dataset.stok || 0
                );


            if (!select.value) {

                harga.textContent = 'Rp 0';

                subtotal.textContent = 'Rp 0';

                jumlah.disabled = true;

                jumlah.value = 1;

                stokInfo.textContent =
                    'Pilih produk';

                return;
            }


            jumlah.disabled = false;

            jumlah.max = stok;

            if (Number(jumlah.value) > stok) {
                jumlah.value = stok;
            }

            if (Number(jumlah.value) < 1) {
                jumlah.value = 1;
            }


            harga.textContent =
                formatRupiah(hargaProduk);


            const nilaiSubtotal =
                hargaProduk *
                Number(jumlah.value);


            subtotal.textContent =
                formatRupiah(nilaiSubtotal);


            stokInfo.textContent =
                'Stok tersedia: ' + stok;

        }


        function updateSummary() {

            const rows =
                document.querySelectorAll(
                    '.produk-row'
                );


            let jumlahJenis = 0;

            let totalJumlah = 0;

            let total = 0;


            rows.forEach(function(row) {

                const select =
                    row.querySelector(
                        '.produk-select'
                    );

                const jumlah =
                    row.querySelector(
                        '.jumlah-produk'
                    );


                if (select.value) {

                    const option =
                        select.options[
                            select.selectedIndex
                        ];

                    const harga =
                        Number(
                            option.dataset.harga || 0
                        );

                    const qty =
                        Number(
                            jumlah.value || 0
                        );


                    jumlahJenis++;

                    totalJumlah += qty;

                    total +=
                        harga * qty;

                }

            });


            jumlahItem.textContent =
                jumlahJenis;

            totalItem.textContent =
                totalJumlah;

            totalHarga.textContent =
                formatRupiah(total);

        }


        function updateAll() {

            const rows =
                document.querySelectorAll(
                    '.produk-row'
                );

            rows.forEach(function(row) {

                updateRow(row);

            });

            updateSummary();

        }


        function cekProdukDuplikat(selectYangDipilih) {

            const semuaSelect =
                document.querySelectorAll(
                    '.produk-select'
                );

            let jumlahSama = 0;


            semuaSelect.forEach(function(select) {

                if (
                    select.value &&
                    select.value ===
                    selectYangDipilih.value
                ) {

                    jumlahSama++;

                }

            });


            return jumlahSama > 1;

        }


        produkContainer.addEventListener(
            'change',
            function(e) {

                if (
                    e.target.classList.contains(
                        'produk-select'
                    )
                ) {

                    if (
                        cekProdukDuplikat(
                            e.target
                        )
                    ) {

                        alert(
                            'Produk tersebut sudah dipilih.'
                        );

                        e.target.value = '';

                    }

                    updateAll();

                }

            }
        );


        produkContainer.addEventListener(
            'input',
            function(e) {

                if (
                    e.target.classList.contains(
                        'jumlah-produk'
                    )
                ) {

                    const row =
                        e.target.closest(
                            '.produk-row'
                        );

                    updateRow(row);

                    updateSummary();

                }

            }
        );


        produkContainer.addEventListener(
            'click',
            function(e) {

                if (
                    e.target.classList.contains(
                        'hapus-produk'
                    )
                ) {

                    const rows =
                        document.querySelectorAll(
                            '.produk-row'
                        );


                    if (rows.length === 1) {

                        const row =
                            e.target.closest(
                                '.produk-row'
                            );

                        const select =
                            row.querySelector(
                                '.produk-select'
                            );

                        select.value = '';

                        updateAll();

                        return;

                    }


                    e.target
                        .closest('.produk-row')
                        .remove();


                    updateAll();

                }

            }
        );


        tambahProduk.addEventListener(
            'click',
            function() {

                const firstRow =
                    document.querySelector(
                        '.produk-row'
                    );

                const newRow =
                    firstRow.cloneNode(true);


                newRow
                    .querySelector(
                        '.produk-select'
                    )
                    .value = '';


                newRow
                    .querySelector(
                        '.jumlah-produk'
                    )
                    .value = 1;


                produkContainer.appendChild(
                    newRow
                );


                updateAll();

            }
        );


        document
            .getElementById('formTransaksi')
            .addEventListener(
                'submit',
                function(e) {

                    const rows =
                        document.querySelectorAll(
                            '.produk-row'
                        );

                    let adaProduk = false;


                    rows.forEach(function(row) {

                        const select =
                            row.querySelector(
                                '.produk-select'
                            );

                        if (select.value) {
                            adaProduk = true;
                        }

                    });


                    if (!adaProduk) {

                        e.preventDefault();

                        alert(
                            'Silakan pilih minimal satu produk.'
                        );

                    }

                }
            );


        updateAll();

    </script>

    <script src="/PENJUALAN/assets/js/bootstrap.bundle.min.js"></script>

</body>

</html>