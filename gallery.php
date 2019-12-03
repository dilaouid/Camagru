<?php

session_start();

require_once('Class/Database.php');
require_once('Class/Checkdatas.php');
require_once('Class/FrontManagment.php');
require_once('Class/Users.php');
require_once('Class/Gallery.php');
require_once('config/config.php');
require_once('config/checkValid.php');


$profilePage = new App\Checkdatas($db);

$section = 'gallery';

if (!isset($_GET['page']) OR !is_numeric($_GET['page']))
    $page = 1;

else if (isset($_GET['page']) AND is_numeric($_GET['page']))
    $page = $_GET['page'];

$Gallery = new App\Gallery($db, $global);
$User = new App\Users($db, null, $global);

if (isset($_GET['like']) AND is_numeric($_GET['like']))
    $Gallery->like($_GET['like']);

if (isset($_GET['unLike']) AND is_numeric($_GET['unLike']))
    $Gallery->unLike($_GET['unLike']);

if (isset($_POST['submit'])) {

    switch($_POST['submit']) {

        case '0': 
            $Gallery->newComment($_POST['comment_0'], $_POST['postid']);
        break;

        case '1':
            $Gallery->newComment($_POST['comment_1'], $_POST['postid']);
        break;

        case '2':
            $Gallery->newComment($_POST['comment_2'], $_POST['postid']);
        break;

        case '3':
            $Gallery->newComment($_POST['comment_3'], $_POST['postid']);
        break;

        case '4':
            $Gallery->newComment($_POST['comment_4'], $_POST['postid']);
        break;

    }
    echo "<meta http-equiv='refresh' content='0'>";
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?= $global['sitename'] ?> | Page <?= $page ?></title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/fonts/ionicons.min.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body class="bg_gallery">

    <?= $FrontManagment->navbar($userid, $section); ?>

    <?= $Gallery->printPosts($page, 5); ?>

    <?= $Gallery->pagination($Gallery->nbPages, $page); ?>

        </div>

        <div class="col-md-4 col-lg-6 col-xl-4 d-inline d-sm-none d-md-inline-block d-lg-inline-block d-xl-inline-block" style="background-color: #f31825;padding-right: 0px;padding-left: 0px;">

            <?php if ($userid != -1)  { ?>
            <div class="row">
                <div class="col">
                    <div class="btn-group btn-group-sm d-flex align-content-center align-self-center" role="group" style="margin-top: 15px;">
                        
                        <button class="btn btn-danger active panelChoose" type="button" onclick="printFollowing()">Abonnements</button>

                        <button class="btn btn-danger panelChoose" type="button" onclick="printPopular()">Populaires</button>
                    </div>
                </div>
            </div>
            <?php } ?>

            <!--     FOLLOWING    -->

            <?php if ($userid != -1) { echo $Gallery->printFollowingPanel(); } ?>

            <?= $Gallery->printPopularPanel(); ?>

        </div>
    </div>
    <?= $FrontManagment->footer(); ?>
</body>

<script src="/assets/js/likes.js"></script>
<script src="/assets/js/dropdown.js"></script>
<script>

<?php if ($userid != -1) { ?>

function printFollowing() {
    document.getElementById("popular").style.display = "none";
    document.getElementById("following").style.display = "initial";
    document.getElementsByClassName("panelChoose")[1].className = document.getElementsByClassName("panelChoose")[1].className.replace(" active", "");
    document.getElementsByClassName("panelChoose")[0].className = "btn btn-danger active panelChoose";

    document.getElementById

}

function printPopular() {
    document.getElementById("following").style.display = "none";
    document.getElementById("popular").style.display = "initial";
    document.getElementsByClassName("panelChoose")[0].className = document.getElementsByClassName("panelChoose")[1].className.replace(" active", "");
    document.getElementsByClassName("panelChoose")[1].className = "btn btn-danger active panelChoose";
}

<?php } ?>

</script>

</html>