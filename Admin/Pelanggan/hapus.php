<?php

include "../../koneksi.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

try {

    $query = mysqli_query(
        $koneksi,
        "DELETE FROM pelanggan WHERE id_pelanggan = $id"
    );

    echo "<script>
            alert('Data pelanggan berhasil dihapus!');
            window.location='index.php';
          </script>";

} catch (mysqli_sql_exception $e) {

    if ($e->getCode() == 1451) {

        echo "<script>
                alert('Pelanggan tidak dapat dihapus karena masih memiliki riwayat transaksi!');
                window.location='index.php';
              </script>";

    } else {

        echo "<script>
                alert('Data pelanggan gagal dihapus!');
                window.location='index.php';
              </script>";
    }
}
?>