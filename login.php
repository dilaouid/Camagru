<?php
session_start();

$loginpage = true;

require_once('Class/Database.php');
require_once('Class/FrontManagment.php');
require_once('Class/Users.php');
require_once('config/database.php');
require_once('config/checkValid.php');

if ($userid > 0){
    header('Location: index.php');
    exit();
}

if (isset($_POST['submit'])) {
    $User = new App\Users($db, $_POST, $global);
    if ($User->login() == 1){
        header('Location: index.php');
        exit();
    }
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

            <form class="mt-auto" method="post" style="opacity: 0.92;">

                <a class="btn btn-primary btn-block btn-sm homebtn" role="button" href="index.php" style="<?= $stylebtn; ?>">
                    <i class="typcn typcn-home"></i>
                </a>

                <h2 class="sr-only">Connexion</h2>

                    <div class="illustration"><i class="la la-user" style="color: rgb(239,41,41);"></i></div>

                    <?php if (isset($User->alert)) echo $User->alert; ?>

                    <div class="form-group">
                        <input class="form-control" type="username" name="username" placeholder="Nom d'utilisateur" required>
                    </div>

                    <div class="form-group">
                        <input class="form-control" type="password" name="password" placeholder="Mot de passe" required>
                    </div>

                    <div class="form-group">
                        <button class="btn btn-primary btn-block" name="submit" type="submit" style="background-color: rgb(128,33,33);">Se connecter</button>
                    </div>

                    <a class="forgot" href="forgot_password.php">Mot de passe oublié ?</a>
                    <a class="forgot" href="register.php">Pas encore de compte ?</a>

                </form>

        </div>
    </div>
</body>

</html>