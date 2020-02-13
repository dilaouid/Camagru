<?php

session_start();

require_once('Class/Database.php');
require_once('Class/FrontManagment.php');
require_once('Class/Gallery.php');
require_once('config/database.php');
require_once('config/checkValid.php');

$section = 'upload';

if (!isset($_GET['type']) AND $_GET['type'] != 'webcam' AND $_GET['type'] != 'upload' AND $_GET['type'] != 'choice'){
    header('Location: /index.php');
    exit();
}


$type = htmlentities($_GET['type']);

if ($userid == -1){
    header('Location: /index.php');
    exit();
}


$uploadPicture = new App\Gallery($db, $global);

if (isset($_POST['x_pos'])) {
    if ($type == 'webcam')
        $uploadPicture->submitPicture($_POST);
    else if ($type == 'upload')
        $uploadPicture->submitFile($_POST, $_FILES['imgUpload']);
}

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?= $global['sitename'] ?> | Capture</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/fonts/ionicons.min.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/scrollbar.css">
</head>

<body>
    <?= $FrontManagment->navbar($userid, $section); ?>
    <div class="row justify-content-center">

    <?php if ($type != 'choice') echo $uploadPicture->capturePhoto($type); else { ?>

        <div class="container" style="height: 169%;max-height: 100%;">
            <div class="row text-center">
                <div class="col-md-12" style="padding-top: 75px;padding-bottom: 75px;">
                    <a href="?type=upload">
                        <i class="fa fa-upload" style="font-size: 62px;"></i><p>Uploader une photo</p>
                    </a>
                </div>
            </div>

            <hr>

            <div class="row text-center">
                <div class="col-md-12" style="padding-top: 75px;padding-bottom: 75px;">
                    <a href="?type=webcam">
                        <i class="fa fa-camera" style="font-size: 62px;"></i><p>Prendre une photo</p>
                    </a>
                </div>
            </div>
        </div>
</div>
    <?php } ?>

    <?= $FrontManagment->footer(); ?>

</body>
<script src="/assets/js/dropdown.js"></script>
<script src="/assets/js/montage.js"></script>
<script>

</script>

<script type="text/javascript">

</script>


</html>