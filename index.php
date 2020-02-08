<?php

session_start();

require_once('Class/Database.php');
require_once('Class/IndexManagment.php');
require_once('Class/FrontManagment.php');
require_once('Class/Users.php');
require_once('config/database.php');
require_once('config/checkValid.php');

if (isset($_GET['logout']) AND isset($_SESSION['id'])) {
    session_destroy();
    header('Location: index.php');
}

$section = 'index';

$User = new App\Users($db, null, $global);

if (isset($_GET['verification']) AND $_GET['verification'] != '0') {
    $key = htmlentities($_GET['verification']);
    if ($User->validateUser($key) == 1)
        header('Location: index.php');
}


?>

<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?= $global['sitename'] ?> | Index</title>

    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/fonts/ionicons.min.css">
    <link rel="stylesheet" href="assets/css/partners.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/features.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/light.css">
    <link rel="stylesheet" href="assets/css/styles.css">

</head>

<body class="body_index">

    <?= $FrontManagment->navbar($userid, $section); ?>

    <header class="header">
        <p class="d-none d-print-flex d-sm-none d-md-none d-lg-none d-xl-flex subtitle_header"><?= $global['subtitle_index'] ?></p>
        <h1 class="display-3 d-none d-print-none d-sm-none d-md-none d-lg-none d-xl-flex title_header"><?= $global['sitename'] ?></h1>
    </header>

    <div class="photo-gallery background_index_1">
        <div class="container text-primary d-table first_part_index">
            <div class="intro">

                <h2 class="text-center" style="color: rgb(243,24,37);">Publications populaires
                    <img src="assets/img/ui/misc/chibi01.png" class="mascot_1">
                </h2>

                <p class="text-center">Les publications les plus populaires du site !
                    <br>Inscrivez vous pour liker ce contenu !
                </p>

            </div>

            <div class="row justify-content-center photos photos_index">


                <?php

                $indexManagment = new App\IndexManagment();
                $topUser_query = $db->query('
                    SELECT      username, wibuu_users.id AS userid,
                                title, img, wibuu_posts.description, wibuu_posts.id AS post_id, nb_likes, nb_comments

                    FROM        wibuu_users

                    INNER JOIN  wibuu_posts ON wibuu_users.id = author

                    WHERE       wibuu_users.private = 0 AND wibuu_posts.private = 0 AND wibuu_posts.active = 1

                    ORDER BY    (nb_likes + nb_comments) DESC
                    LIMIT       3 OFFSET 0');
                    $indexManagment->getTop($db, $topUser_query, $userid);

                ?>

            </div>
        </div>
    </div>
    <?= $indexManagment->features($db, $global); ?>
    <?= $FrontManagment->partners(); ?>
    <?= $FrontManagment->footer(); ?>

    <?= $User->validatedAccount; ?>



</body>

<script src="/assets/js/dropdown.js"></script>

</html>