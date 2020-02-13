<?php

session_start();

require_once('Class/Database.php');
require_once('Class/Checkdatas.php');
require_once('Class/IndexManagment.php');
require_once('Class/FrontManagment.php');
require_once('Class/Users.php');
require_once('config/database.php');
require_once('config/checkValid.php');

if (!isset($_GET['id'])){
    header('Location: index.php');
    exit();
}

$profilePage = new App\Checkdatas($db);

$id = htmlentities($_GET['id']);

if ($profilePage->check_qs_exists($id, 'wibuu_users') == 0){
    header('Location: index.php');
    exit();
}

$section = 'profile';

$User = new App\Users($db, null, $global);

if (isset($_GET['askFollow']) AND $userid != $id AND $userid != -1) {
    $User->askFollow($userid, $id);
    exit();
}

if (isset($_GET['acceptFollow']) AND $userid != $id AND $userid != -1) {
    $User->acceptFollow($userid, $id);
    exit();
}

if (isset($_GET['unFollow']) AND $userid != $id AND $userid != -1) {
    $User->unFollow($userid, $id);
    exit();
}

if (isset($_GET['stopFollow']) AND $userid != $id AND $userid != -1) {
    $User->stopFollow($userid, $id);
    exit();
}

?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?= $global['sitename'] ?> | <?= $global['sitename'] ?></title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/fonts/ionicons.min.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/light.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body style="background-color: #e0e0e0;">

    <?= $FrontManagment->navbar($userid, $section); ?>

    <div>
        <div class="row" style="background-color: #e0e0e0;">
            <?= $profilePage->getInfos(); ?>
        </div>

        <?= $profilePage->privateProfile(); ?>

        <?= $profilePage->allPosts($id); ?> 

        <?= $FrontManagment->footer(); ?>

</body>

<script src="/assets/js/follows.js"></script>
<script src="/assets/js/dropdown.js"></script>

</html>