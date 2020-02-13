<?php

session_start();

require_once($_SERVER['DOCUMENT_ROOT'].'/Class/Database.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/Class/AdminManagment.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/Class/FrontManagment.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/Class/Users.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/config/database.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/config/checkValid.php');

if (!isset($userid)){
    header('Location: /index.php');
    exit();
}


$AdminManagment = new App\AdminManagment($db, $global);
if ($AdminManagment->admin == 0) {
    header('Location: /index.php');
    exit();
}

$section = 'Nouveau filtre';

$User = new App\Users($db, null, $global);

$alert = "";

if (isset($_POST['submit'])) {
    if ($_FILES['filtre']['size'] == 0) {
        $AdminManagment->alert = '<div class="col"><div class="alert alert-danger" role="alert" style="width: 100%;margin-top: 24px;"><span>Le fichier envoyé est invalide</span></div></div>';
    }
    else if (strlen($_POST['name']) > 50) {
        $AdminManagment->alert = '<div class="col"><div class="alert alert-danger" role="alert" style="width: 100%;margin-top: 24px;"><span>Le nom ne doit pas depasser 50 caracteres</span></div></div>';
    }
    else if (preg_match('@[^\w]@', $_POST['name']) == 1) {
        $AdminManagment->alert = '<div class="col"><div class="alert alert-danger" role="alert" style="width: 100%;margin-top: 24px;"><span>Le nom du filtre ne peut contenir que des lettres et chiffres</span></div></div>';
    }
    else if ($AdminManagment->newFilter($_POST, $_FILES['filtre']))
        $AdminManagment->alert = '<div class="col"><div class="alert alert-success" role="alert" style="width: 100%;margin-top: 24px;"><span>Filtre crée avec succès</span></div></div>';
}

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?= $global['sitename'] ?> | Filtres</title>
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
        <div class="container" style="margin-top: 11px;margin-bottom: 2vh;">
            <div class="row">
                <div class="col">
                    <div class="row justify-content-center align-items-center">
                        <div class="col-auto"><img src="/assets/img/ui/camera.png" style="width: 157px;"></div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <h1 class="display-4 text-center" style="font-size: 46px;">Créer un nouveau filtre</h1>
                        </div>
                        <?= $AdminManagment->alert ?>
                    </div>
                    <div class="card-group">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Nouveau filtre</h4>
                                <p class="card-text"><em>Taille </em><strong><em>OBLIGATOIRE</em></strong><em>: 455x286</em></p>
                                <form action="" method="post" enctype="multipart/form-data">

                                    <div class="form-group">
                                        <input type="file" name="filtre">
                                    </div>

                                    <div class="form-group">
                                        <label>Nom du filtre</label>
                                        <input class="form-control" type="text" name="name">
                                    </div>

                                <div class="row">
                                    <div class="col" style="margin-top: 21px;">
                                        <button class="btn btn-danger btn-block" type="submit" name="submit">Oui, ce filtre sera éternellement chez nous !</button>
                                    </div>
                                </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?= $FrontManagment->footer(); ?>

</body>
<script src="/assets/js/dropdown.js"></script>
</html>