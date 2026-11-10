<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../images/favicon.ico">

    <title>CampusBallot Core - Verification Processing</title>

    <!-- Bootstrap 4.0-->
    <link rel="stylesheet" href="../../assets/vendor_components/bootstrap/dist/css/bootstrap.min.css">

    <!-- theme style -->
    <link rel="stylesheet" href="../../css/horizontal-menu.css">
    <link rel="stylesheet" href="../../css/style.css">

    <!-- Admin skins -->
    <link rel="stylesheet" href="../../css/skin_color.css">

</head>
<body class="layout-top-nav light-skin theme-classic">
<!-- Site wrapper -->
<div class="wrapper">

    <header class="main-header">
        <div class="inside-header">
            <div class="d-flex align-items-center logo-box justify-content-between">
                <a href="../../index.php" class="logo">
                    <div class="logo-mini">
                        <span class="light-logo"><img src="../../images/logo.svg" style="height: 32px;" alt="logo"></span>
                        <span class="dark-logo"><img src="../../images/logo.svg" style="height: 32px;" alt="logo"></span>
                    </div>
                    <div class="logo-lg">
                        <span class="light-logo"><img src="../../images/logo-text.svg" style="height: 24px;" alt="CampusBallot"></span>
                        <span class="dark-logo"><img src="../../images/logo-text.svg" style="height: 24px;" alt="CampusBallot"></span>
                    </div>
                </a>
            </div>
            <!-- Header Navbar -->
            <nav class="navbar navbar-static-top">

                <div class="navbar-custom-menu r-side">
                    <ul class="nav navbar-nav">

                    </ul>
                </div>
            </nav>
        </div>
    </header>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <div class="container-full">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <h3>
                    Verifikasi
                </h3>
            </div>

            <!-- Main content -->
            <section class="content">
                <div class="row">
                    <div class="col-12">
                        <div class="box">
                            <div class="box-header with-border">
                                <h4 class="box-title"><b>VOTING INSTRUCTIONS & SSO AUTHORIZATION</b></h4>
                            </div>
                            <div class="box-body text-dark">
                                <ul>
                                    <li style="font-weight: bold; color: #dc2626; font-size: 1.25rem">IMPORTANT: READ CAREFULLY BEFORE PROCEEDING</li><br>
                                    <li style="font-weight: bold; color: #dc2626;">DO NOT REFRESH, LEAVE THE TAB, OR SWITCH WINDOWS DURING VOTING.</li><br>
                                    <li>Ensure your central campus SSO credentials are active and accessible.</li><br>
                                    <li>Enter your official Student ID (NIM) below to initiate cryptographic token generation.</li><br>
                                    <li>You will be directed to the secure single-page multi-tier voting booth.</li><br>
                                </ul>
                                <div class="row">
                                    <div class="col-12 text-center">
                                        <form class="modal-content animate" action="index.php" method="get">
                                            <label><b>Student ID Number (NIM)</b></label>
                                            <div class="form-group">
                                                <div class="controls">
                                                    <input type="text" autocomplete="off" minlength="8" maxlength="15" placeholder="Enter your Student ID (NIM)" name="nim_mhs"
                                                           class="form-control" required
                                                           data-validation-containsnumber-regex="(\d)+"
                                                           data-validation-containsnumber-message="Only numeric digits allowed">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div hidden class="controls">
                                                    <input type="text" placeholder="Masukkan nomor HP Anda"
                                                           name="nomor_hp_mhs" class="form-control"
                                                           data-validation-containsnumber-regex="(\d)+"
                                                           data-validation-containsnumber-message="Nomor HP, Hanya Angka yang Di izinkan">
                                                </div>
                                            </div>
                                            <br>
                                            <button type="submit" class="btn btn-default" title="Verifikasi">
                                                <i class="fa fa-refresh"></i>&nbsp;Login SSO
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
        </section>
        <!-- /.content -->

    </div>
</div>
<!-- /.content-wrapper -->

</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>

<!-- js -->
<script src="../../js/vendors.min.js"></script>

<!-- Form validator JavaScript -->
<script src="../../js/pages/validation.js"></script>
<script src="../../js/pages/form-validation.js"></script>

</body>
</html>
