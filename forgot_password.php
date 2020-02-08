<?php
session_start();

require_once('Class/Database.php');
require_once('Class/Users.php');
require_once('Class/FrontManagment.php');
require_once('config/database.php');
require_once('config/checkValid.php');

$validkey = null;

if ($userid > 0)
    header('Location: index.php');
$User = new App\Users($db, null, $global);

if (isset($_POST['askPass']) AND isset($_POST['email']))
    $User->recoverPassword($_POST['email']);

if (isset($_GET['key'])) {
    $validkey = $User->validKey($_GET['key']);
    if ($validkey == 0)
        header('Location: index.php');
}

if (isset($_POST['new_pass']) AND isset($_POST['password']) AND isset($_POST['confirm_password'])) {
    $User->newPassword($_POST['password'], $_POST['confirm_password'], $_GET['key']);
    if (!isset($User->alert))
        header('Location: login.php');
}

$stylebtn = 'background-color: rgb(74,74,74);margin: 92px;margin-top: -17px;margin-bottom: -28px;width: 49px;';

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?= $global['sitename'] ?> | Connexion</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fonts/line-awesome.min.css">
    <link rel="stylesheet" href="assets/fonts/typicons.min.css">
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <div class="login-dark">
        <div>


            <?php if ($validkey == null) { ?>

            <form class="mt-auto" action="" method="post" style="opacity: 0.92;">
                <a class="btn btn-primary btn-block btn-sm homebtn" role="button" href="index.php" style="<?= $stylebtn; ?>">
                    <i class="typcn typcn-home"></i>
                </a>

                <h2 class="sr-only">Connexion</h2>
                    <div class="illustration">
                        <i class="la la-question" style="color: rgb(239,41,41);"></i>
                    </div>

                    <div class="form-group">
                        <input class="form-control" type="email" name="email" placeholder="Adresse e-mail">
                    </div>

                    <div class="form-group">
                        <button class="btn btn-primary btn-block" name="askPass" type="submit" style="background-color: rgb(128,33,33);">
                        Récupérer mot de passe</button>
                    </div>
                    <a class="forgot" href="login.php">Se connecter</a>
                    <?php if (isset($User->alert)) echo $User->alert; ?>
            </form>

        <?php } else { ?>

            <form class="mt-auto" action="" method="post" style="opacity: 0.92;">
                <a class="btn btn-primary btn-block btn-sm homebtn" role="button" href="index.php" style="<?= $stylebtn; ?>">
                    <i class="typcn typcn-home"></i>
                </a>

                <h2 class="sr-only">Changer le mot de passe</h2>
                    <div class="illustration">
                        <i class="la la-question" style="color: rgb(239,41,41);"></i>
                    </div>

                    <div class="form-group">
                        <input class="form-control" type="password" name="password" placeholder="Mot de passe">
                    </div>

                    <div class="form-group">
                        <input class="form-control" type="password" name="confirm_password" placeholder="Confirmez le mot de passe">
                    </div>

                    <div class="form-group">
                        <button class="btn btn-primary btn-block" name="new_pass" type="submit" style="background-color: rgb(128,33,33);">
                        Changer le mot de passe</button>
                    </div>
                    <?php if (isset($User->alert)) echo $User->alert; ?>
            </form>            

        <?php } ?>


        </div>
    </div>
</body>

</html>