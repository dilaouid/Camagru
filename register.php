<?php

session_start();

require_once('Class/Database.php');
require_once('Class/FrontManagment.php');
require_once('Class/Users.php');
require_once('config/database.php');
require_once('config/checkValid.php');

if ($userid > 0)
    header('Location: index.php');

if (isset($_POST['submit'])) {
    $newUser = new App\Users($db, $_POST, $global);
    if (!isset($_POST['username']) || !isset($_POST['confirm_password'])
        || !isset($_POST['password']) || !isset($_POST['email'])
        || !isset($_POST['cgu']))
    $newUser->alert = '<div class="alertmsg"><div class="alert alert-danger alertbox" role="alert"><span><strong>Tout les champs sont obligatoires</strong></span></div></div>';
    else
        $newUser->createUser();
}


?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?= $global['sitename'] ?> | Inscription</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/registration.css">
    <link rel="stylesheet" href="assets/fonts/typicons.min.css">
</head>

<body>
    <div class="d-flex d-xl-flex align-items-xl-center register-photo">
        <img src="assets/img/ui/register/nezuko.png" class="nezuko">

        <?php
            if (isset($newUser->alert))
                echo $newUser->alert;
        ?>

        <div class="align-items-center order-2 form-container" style="">
            <div class="image-holder bloc"></div>
            <form class="text-white-50" method="post" action="" style="background-color: rgb(51,51,51);opacity: 0.95;color: rgb(200,200,200);">
                <h2 class="text-center">Nous <strong>rejoindre</strong></h2>

                <div class="form-group">
                    <input class="form-control fieldinp" type="username" name="username" placeholder="Nom d'utilisateur" style="color: rgb(51,51,51);" required>
                </div>

                <div class="form-group">
                    <input class="form-control" type="email" name="email" placeholder="Email" style="color: rgb(51,51,51);" required>
                </div>

                <div class="form-group">
                    <input class="form-control" type="password" name="password" placeholder="Mot de passe" style="color: rgb(51,51,51);" required>
                </div>

                <div class="form-group text-primary">
                    <input class="form-control" type="password" name="confirm_password" placeholder="Confirmer le mot de passe" style="color: rgb(51,51,51);" required>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <label class="form-check-label">
                            <input class="form-check-input" name="cgu" type="checkbox">J'acceptes les <a href="#" target="_blank" required>conditions générales d'utilisations</a>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <button class="btn btn-primary btn-block" name="submit" type="submit">Nous rejoindre</button>
                </div>

                <a class="already" href="login.php">Vous avez déjà un compte ?</a></form>

        </div>
    </div>
</body>

</html>