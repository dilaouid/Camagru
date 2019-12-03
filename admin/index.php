<?php

session_start();

require_once($_SERVER['DOCUMENT_ROOT'].'/Class/Database.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/Class/AdminManagment.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/Class/FrontManagment.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/Class/Users.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/config/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/config/checkValid.php');

if (!isset($userid))
    header('Location: /index.php');

$AdminManagment = new App\AdminManagment($db, $global);
if ($AdminManagment->admin == 0)
    header('Location: /index.php');

$section = 'Panneau de contrôle';

$User = new App\Users($db, null, $global);

if (isset($_POST['submit'])) {
        switch($_POST['submit']) {
        case '0': 
            $AdminManagment->updateGlobal($_POST);
        break;

        case '1':
            if ($_FILES['header']['error'] == 0)
                $AdminManagment->newHeader($_FILES['header']);
        break;

        case '2':
                $AdminManagment->uploadPDF($_FILES['whoarewe'], $_FILES['cgu'], $_FILES['mentions']);
        break;
    }
    if (empty($AdminManagment->alert) AND empty($Admin->alert))
        echo "<meta http-equiv='refresh' content='0'>";
}

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?= $global['sitename'] ?> | Panneau de contrôle</title>
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="/assets/fonts/ionicons.min.css">
    <link rel="stylesheet" href="/assets/css/navbar.css">
    <link rel="stylesheet" href="/assets/css/footer.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>

<body class="bg_edit_profile">

    <?= $FrontManagment->navbar($userid, $section); ?>

    <?= $AdminManagment->navbar($section); ?>

    <div>
        <div class="container" style="margin-top: 11px;margin-bottom: 20px;">
            <div class="row">

                <div class="col-md-6">                
                
                    <?= $AdminManagment->alert ?>

                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Paramètres globaux</h4>
                            <h6 class="text-muted card-subtitle mb-2">Mooon précieeeeux siiiiiite ~~</h6>

                            <p class="card-text">Configurez les paramètres globaux de votre site ! Courte description, réseaux sociaux, nom, autorisation des inscriptions ou même le logo !</p>

                            <?= $AdminManagment->globalParam(); ?>

                        </div>
                    </div>
                </div>


                <div class="col-md-6">

                    <div class="card-group">
                        <div class="card">
                            <form action="" method="post" enctype="multipart/form-data">
                            <img class="card-img-top w-100 d-block" src="/assets/img/ui/index/banner.jpg">
                            <div class="card-body">
                                <h4 class="card-title">Bannière</h4>

                                <p class="card-text">
                                    <em>Taille recommandée: 1920x400</em>
                                </p>

                                <input type="file" name="header">

                                <div class="row">
                                    <div class="col" style="margin-top: 21px;">
                                        <button class="btn btn-danger btn-block" type="submit" name="submit" value="1">Changer la bannière</button>
                                    </div>
                                </div>
                            </div>
                        
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <h1 class="display-4 text-center" style="font-size: 46px;">Panneau de contrôle</h1>
                        </div>
                    </div>


                    <div class="row justify-content-center align-items-center">
                        <div class="col-auto"><img src="/assets/img/ui/misc/chibi03.png" style="width: 242px;"></div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Fichiers PDF</h4>
                            <h6 class="text-muted card-subtitle mb-2">Qui servent à rien</h6>
                            <p class="card-text">Définissez les différents fichiers PDF du footer.</p>

                            <label>Qui sommes nous</label>
                            <div class="form-row">
                                <div class="col" style="margin-bottom: 17px;">
                                    <input type="file" name="whoarewe">
                                </div>
                            </div>

                            <label>CGU</label>
                            <div class="form-row">
                                <div class="col" style="margin-bottom: 17px;">
                                    <input type="file" name="cgu">
                                </div>
                            </div>

                            <label>Mentions légales</label>
                            <div class="form-row">
                                <div class="col" style="margin-bottom: 17px;">
                                    <input type="file" name="mentions">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col" style="margin-top: 21px;">
                                    <button class="btn btn-danger btn-block" type="submit" name="submit" value="2">Mettre les PDF à jour !</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?= $FrontManagment->footer(); ?>

</body>
<script src="/assets/js/dropdown.js"></script>
</html>