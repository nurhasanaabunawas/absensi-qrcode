<?php
SESSION_START();
include "../../database.php";

$db = new Database();

$nip = (isset($_SESSION['nim_nip'])) ? $_SESSION['nim_nip'] : "";
$token = (isset($_SESSION['token'])) ? $_SESSION['token'] : "";

// Get form id
$form_id = $_GET['form_id'];

if($token && $nip){
    // Query dosen
    $result = $db->execute("SELECT * FROM dosen_tbl
WHERE nip = '".$nip."' AND token = '".$token."'");

    // If not dosen, ...
    if(!$result){
        // Redirect to login
        header("Location: ../../login.php");
    }

    // Get user data
    $userdata = $db->get("SELECT nip, nama_lengkap
    FROM dosen_tbl
    WHERE nip = '".$nip."'");

    $userdata = mysqli_fetch_assoc($userdata);

    if (isset($form_id)) {

        // Get absen data
        $absen_data = $db->get("SELECT form_id, nama_matkul, kelas, pertemuan, tanggal, program_studi, qrcode
    FROM absen_form_tbl
    WHERE nip = '" . $nip . "' AND form_id = " . $form_id);

        if($absen_data) {
            $absen_data = mysqli_fetch_assoc($absen_data);

            // Get attendance data
            $attendance_data = $db->get("SELECT mahasiswa_tbl.nim as nim,
mahasiswa_tbl.nama_lengkap as nama_lengkap,
kehadiran_tbl.tanggal_absen as tanggal_absen
FROM mahasiswa_tbl, kehadiran_tbl,dosen_tbl
WHERE kehadiran_tbl.form_id = " . $form_id . " AND 
kehadiran_tbl.nim = mahasiswa_tbl.nim AND
dosen_tbl.nip = '" . $nip . "'
ORDER BY kehadiran_tbl.tanggal_absen ASC");
        } else{
            $_SESSION['notification'] = "Absen tidak ditemukan";
            header("Location: dashboarddosen.php");
        }
    }else{
        header("Location: dashboarddosen.php");
    }

} else{
    header("Location: ../../login.php");
}

$notification = (isset($_SESSION['notification'])) ? $_SESSION['notification'] : "";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Dashboard">
    <meta name="keyword" content="Dashboard, Bootstrap, Admin, Template, Theme, Responsive, Fluid, Retina">
    <title>DASHBOARD | ABSENSI CODE QR</title>

    <!-- Favicons -->
    <link href="../../img/favicon.png" rel="icon">
    <link href="../../img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Bootstrap core CSS -->
    <link href="../../lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!--external css-->
    <link href="../../lib/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="../../css/zabuto_calendar.css">
    <link rel="stylesheet" type="text/css" href="../../lib/gritter/css/jquery.gritter.css" />
    <!-- Custom styles for this template -->
    <link href="../../css/style.css" rel="stylesheet">
    <link href="../../css/style2.css" rel="stylesheet">
    <link href="../../css/style-responsive.css" rel="stylesheet">
    <script src="../../lib/chart-master/Chart.js"></script>

    <!-- =======================================================
    Template Name: Dashio
    Template URL: https://templatemag.com/dashio-bootstrap-admin-template/
    Author: TemplateMag.com
    License: https://templatemag.com/license/
  ======================================================= -->
</head>

<body>
<section id="container">
    <!-- **********************************************************************************************************************************************************
    TOP BAR CONTENT & NOTIFICATIONS
    *********************************************************************************************************************************************************** -->
    <!--header start-->
    <header class="header black-bg">
        <div class="sidebar-toggle-box">
            <div class="fa fa-bars tooltips" data-placement="right" data-original-title="Toggle Navigation"></div>
        </div>
        <!--logo start-->
        <a href="dashboarddosen.php" class="logo"><b>DASHBOARD <span>DOSEN</span></b></a>
        <!--logo end-->
        <div class="top-menu">
            <ul class="nav pull-right top-menu">
                <li><a class="logout" href="add_absen.php">+</a></li>
                <li><a class="logout" href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </header>
    <!--header end-->
    <!-- **********************************************************************************************************************************************************
    MAIN SIDEBAR MENU
    *********************************************************************************************************************************************************** -->
    <!--sidebar start-->
    <aside>
        <div id="sidebar" class="nav-collapse ">
            <!-- sidebar menu start-->
            <ul class="sidebar-menu" id="nav-accordion">
                <p class="centered">
                    <img src="../../assets/img/profil.jpg" class="img-circle" width="80">
                </p><br>
                <h5 class="centered"><?php echo $userdata['nama_lengkap']?></h5>
                <h5 class="centered"><?php echo $userdata['nip']?></h5><br><br>

            </ul>
            <!-- sidebar menu end-->
        </div>
    </aside>
    <!--sidebar end-->
    <!-- **********************************************************************************************************************************************************
    MAIN CONTENT
    *********************************************************************************************************************************************************** -->
    <!--main content start-->
    <section id="main-content">
        <section class="wrapper">
            <div class="row">
                <div class="col-lg-12 main-chart">
                    <!--CUSTOM CHART START -->
                    <div class="row">
                        <!-- ABSEN PANEL -->
                        <div class="col-md-12 col-sm-4 mb">
                            <div class="green-panel">
                                <div class="green-header">
                                    <h3>
                                        <strong>
                                            <?php echo $absen_data['nama_matkul']." Kelas ".$absen_data['kelas']?>
                                        </strong>
                                    </h3>
                                </div>
                                <p class="fadilah"><img src="<?php echo "process/make_qrcode.php?id=".$absen_data['qrcode']?>" width="80"></p>

                                <div class="text-center">
                                    <p class="fadilah">Pertemuan Ke : <?php echo $absen_data['pertemuan']?></p>
                                    <p class="fadilah">Tanggal : <?php echo $absen_data['tanggal']?></p>
                                    <p class="fadilah">Program Studi : <?php echo $absen_data['program_studi']?></p>
                                </div>

                                <form action="update_absen.php" method="post">
                                    <input type="hidden" name="form_id" value="<?php echo $absen_data['form_id']?>">
                                    <button class="button btn-small btn-theme02" name="edit">Update absen</button>
                                </form>

                                <form action="process/export_absen.php" method="get">
                                    <input type="hidden" name="form_id" value="<?php echo $absen_data['form_id']?>">
                                    <button class="button btn-small btn-theme02" name="export">Export absen</button>
                                </form>

                                <button class="button btn-small btn-theme04" onclick="OnOneClick()">
                                    Delete absen
                                </button>
                            </div>
                        </div>
                    </div>

                            <!-- </div> -->
                        </div>
                    </div>
                </div>
            </div>
            <!-- /row -->

            <!--main content end-->

            <!--main content start-->
            <!-- row -->
            <div class="row mt">
                <div class="col-md-12">
                    <div class="content-panel">
                        <table class="table table-striped table-advance table-hover">
                            <h4><i class="fa fa-angle-right"></i> Detail Absen </h4>
                            <hr>
                            <thead>
                            <tr>
                                <th> NO </th>
                                <th><i class=""></i>NIM</th>
                                <th><i class=""></i>Nama Mahasiswa</th>
                                <th><i class=""></i>Tanggal Absen</th>
                            </tr>
                            </thead>

                            <tbody>
                            <?php
                            if($attendance_data) {
                                $i = 0;
                                while ($row = mysqli_fetch_assoc($attendance_data)) {
                                    $i++;
                                    ?>
                                    <tr>
                                        <td><?php echo $i?></td>
                                        <td><?php echo $row['nim']?></td>
                                        <td><?php echo $row['nama_lengkap']?></td>
                                        <td><?php echo $row['tanggal_absen']?></td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- /content-panel -->
                </div>
                <!-- /col-md-12 -->
            </div>
            <!-- /row -->
        </section>
    </section>
    <!-- /MAIN CONTENT -->

    <!--footer start-->
    <footer class="site-footer">
        <div class="text-center">
            <p>
                &copy; Copyrights <strong>Absensi QR</strong>. By Kelompok 6
            </p>
            <div class="credits">
                <!--
            You are NOT allowed to delete the credit link to TemplateMag with free version.
            You can delete the credit link only if you bought the pro version.
            Buy the pro version with working PHP/AJAX contact form: https://templatemag.com/dashio-bootstrap-admin-template/
            Licensing information: https://templatemag.com/license/
          -->
            </div>
            <a href="index.html#" class="go-top">
                <i class="fa fa-angle-up"></i>
            </a>
        </div>
    </footer>
    <!--footer end-->
</section>
<!-- js placed at the end of the document so the pages load faster -->
<script src="../../lib/jquery/jquery.min.js"></script>

<script src="../../lib/bootstrap/js/bootstrap.min.js"></script>
<script class="include" type="text/javascript" src="../../lib/jquery.dcjqaccordion.2.7.js"></script>
<script src="../../lib/jquery.scrollTo.min.js"></script>
<script src="../../lib/jquery.nicescroll.js" type="text/javascript"></script>
<script src="../../lib/jquery.sparkline.js"></script>
<!--common script for all pages-->
<script src="../../lib/common-scripts.js"></script>
<script type="text/javascript" src="../../lib/gritter/js/jquery.gritter.js"></script>
<script type="text/javascript" src="../../lib/gritter-conf.js"></script>
<!--script for this page-->
<script src="../../lib/sparkline-chart.js"></script>
<script src="../../lib/zabuto_calendar.js"></script>
<script type="application/javascript">
    $(document).ready(function() {
        $("#date-popover").popover({
            html: true,
            trigger: "manual"
        });
        $("#date-popover").hide();
        $("#date-popover").click(function(e) {
            $(this).hide();
        });

        $("#my-calendar").zabuto_calendar({
            action: function() {
                return myDateFunction(this.id, false);
            },
            action_nav: function() {
                return myNavFunction(this.id);
            },
            ajax: {
                url: "show_data.php?action=1",
                modal: true
            },
            legend: [{
                type: "text",
                label: "Special event",
                badge: "00"
            }, {
                type: "block",
                label: "Regular event",
            }]
        });
    });

    function myNavFunction(id) {
        $("#date-popover").hide();
        var nav = $("#" + id).data("navigation");
        var to = $("#" + id).data("to");
        console.log('nav ' + nav + ' to: ' + to.month + '/' + to.year);
    }
</script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script>
    var OnOneClick = function() {
        swal({
            title: "Apakah anda yakin ingin menghapus absen ini?",
            text: "Ketika sudah dihapus, anda tidak dapat memulihkan absen ini lagi!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
            .then((willDelete) => {
                if (willDelete) {
                    window.location.href = 'process/delete_absen_process.php?form_id=' + <?php echo $form_id?>
                }
            });
    };
</script>
</body>

</html>

<?php
$db->setNotification($notification);
?>