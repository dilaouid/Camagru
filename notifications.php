<?php

session_start();

require_once('Class/Database.php');
require_once('Class/FrontManagment.php');
require_once('Class/Checkdatas.php');
require_once('config/config.php');
require_once('config/checkValid.php');

if ($userid == -1)
    header('Location: /');

$notif = new App\Checkdatas($db);

$section = 'notifications';

if (isset($_GET['delete']) AND is_numeric($_GET['delete'])) {
    $query = $db->prepare("SELECT id FROM wibuu_notifications WHERE dest = $userid AND id = ?");
    $query->execute(array($_GET['delete']));
    if ($query->rowCount() != 0) {
        $delete = $db->prepare("UPDATE wibuu_notifications SET active = 0 WHERE dest = $userid AND id = ?");
        $delete->execute(array($_GET['delete']));
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Wibuu</title>
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

    <?= $notif->getNotifList(); ?>

    <?= $FrontManagment->footer(); ?>

</body>

<script src="/assets/js/notifs.js"></script>
<script src="/assets/js/dropdown.js"></script>

</html>