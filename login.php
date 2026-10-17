<?php

session_start();

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | Bottle.Co</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;

            min-height: 100vh;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #F7F9F5;

            color: #26332A;
        }


        /* =========================
           LOGIN CARD
        ========================= */

        .login-container {
            width: 100%;
            max-width: 390px;

            padding: 20px;
        }


        .login-card {
            background: #FFFFFF;

            padding: 38px 36px 30px;

            border-radius: 16px;

            border: 1px solid #E1E8E2;

            box-shadow:
                0 12px 35px rgba(38, 51, 42, 0.08);
        }


        /* =========================
           LOGO
        ========================= */

        .logo {
            width: 56px;
            height: 56px;

            margin: 0 auto 16px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 14px;

            background: #7FA88C;

            color: #FFFFFF;

            font-size: 24px;
            font-weight: 700;
        }


        /* =========================
           TITLE
        ========================= */

        .login-title {
            text-align: center;

            color: #26332A;

            font-size: 24px;

            font-weight: 700;

            margin-bottom: 6px;
        }


        .login-subtitle {
            text-align: center;

            color: #78837B;

            font-size: 12px;

            margin-bottom: 28px;
        }


        /* =========================
           FORM
        ========================= */

        .form-group {
            margin-bottom: 18px;
        }


        .form-group label {
            display: block;

            margin-bottom: 7px;

            color: #3F4A43;

            font-size: 12px;

            font-weight: 600;
        }


        .form-group input {
            width: 100%;

            height: 44px;

            padding: 0 13px;

            border: 1px solid #D5DED7;

            border-radius: 9px;

            outline: none;

            background: #FFFFFF;

            color: #3F4A43;

            font-size: 12px;

            transition: 0.2s;
        }


        .form-group input::placeholder {
            color: #9AA49D;
        }


        .form-group input:hover {
            border-color: #C2D0C6;
        }


        .form-group input:focus {
            border-color: #7FA88C;

            box-shadow:
                0 0 0 3px rgba(127, 168, 140, 0.12);
        }


        /* =========================
           LOGIN BUTTON
        ========================= */

        .btn-login {
            width: 100%;

            height: 44px;

            margin-top: 2px;

            border: none;

            border-radius: 9px;

            background: #7FA88C;

            color: #FFFFFF;

            font-size: 12px;

            font-weight: 700;

            cursor: pointer;

            transition: 0.2s;
        }


        .btn-login:hover {
            background: #66846F;
        }


        /* =========================
           BACK BUTTON
        ========================= */

        .btn-back-dashboard {
            display: flex;

            align-items: center;
            justify-content: center;

            width: 100%;

            height: 38px;

            margin-top: 10px;

            border: 1px solid #D5DED7;

            border-radius: 8px;

            background: #FFFFFF;

            color: #78837B;

            font-size: 10px;

            font-weight: 600;

            text-decoration: none;

            transition: 0.2s;
        }


        .btn-back-dashboard:hover {
            background: #F7F9F5;

            border-color: #BFD0C3;

            color: #66846F;
        }


        /* =========================
           ERROR
        ========================= */

        .error {
            margin-bottom: 18px;

            padding: 10px 12px;

            border-radius: 8px;

            background: #FFF4F4;

            border: 1px solid #F1D1D1;

            color: #C62828;

            font-size: 11px;
        }


        /* =========================
           FOOTER
        ========================= */

        .footer-text {
            text-align: center;

            margin-top: 22px;

            color: #9AA49D;

            font-size: 10px;
        }


        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 480px) {

            .login-container {
                padding: 15px;
            }

            .login-card {
                padding: 32px 25px 27px;
            }

        }

    </style>

</head>


<body>


<div class="login-container">

    <div class="login-card">

        <!-- TITLE -->

        <h1 class="login-title">
            Bottle.Co
        </h1>


        <p class="login-subtitle">
            Sistem Penjualan
        </p>



        <!-- ERROR -->

        <?php if (isset($_GET['error'])): ?>

            <?php if ($_GET['error'] === 'salah'): ?>

                <div class="error">
                    Username atau password salah.
                </div>

            <?php elseif ($_GET['error'] === 'nonaktif'): ?>

                <div class="error">
                    Akun kamu tidak aktif.
                </div>

            <?php elseif ($_GET['error'] === 'level'): ?>

                <div class="error">
                    Level pengguna tidak dikenali.
                </div>

            <?php endif; ?>

        <?php endif; ?>



        <!-- FORM -->

        <form
            action="proses_login.php"
            method="POST"
        >


            <div class="form-group">

                <label>
                    Username
                </label>

                <input
                    type="text"
                    name="username"
                    placeholder="Masukkan username"
                    autocomplete="username"
                    required
                >

            </div>



            <div class="form-group">

                <label>
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    placeholder="Masukkan password"
                    autocomplete="current-password"
                    required
                >

            </div>



            <button
                type="submit"
                name="login"
                class="btn-login"
            >
                Masuk
            </button>



            <a
                href="index.php"
                class="btn-back-dashboard"
            >
                ← Kembali ke Dashboard Utama
            </a>


        </form>



        <div class="footer-text">
            © 2026 Bottle.Co
        </div>


    </div>

</div>


</body>

</html>