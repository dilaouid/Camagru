<?php

require_once('../Class/Config/Form.php');
require_once('../Class/Users.php');
require_once('../Class/Database.php');
require_once('config.php');

$form = new App\Form();

if (isset($_POST['submit'])) {

    try {
        $db = new PDO($DB_DSN, $USER_DB, $PASSWORD_DB);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $ex){
        die(header('Location: /config/setup.php'));
    }
    $user = new App\Users($db, $_POST);
    $create = $user->createAdmin();
    if ($create == 1) {
        $_SESSION['id'] = 1;
        header('Location: ../index.php');
    }
    else if ($create == 0)
        $error = $form->errorMessage('Le format de l\'email entré est incorrect');
    else if ($create == -2)
        $error = $form->errorMessage('Le mot de passe n\'est pas assez securisé');
    else
        $error = $form->errorMessage('Les mots de passes saisis sont différents');

}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Création du compte administrateur</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>


<body>
<div class="container">

    <div class="mx-auto text-center">
        <img class ="mx-auto text-center img-fluid" src="../assets/img/wibuu_name.png" style="max-width: 200px; margin-top:10vh; margin-bottom:5vh" oncontextmenu="return false;">
    </div>

    <div class="row">
        <?= $form->leftSide(
            'Compte administrateur <img class ="mx-auto text-center" src="../assets/img/nyan_face.png" style="max-width:10%;" oncontextmenu="return false;">',
            'Maintenant que le site est déployé, il vous faut un compte administrateur ! Ce compte vous permettra de paramétrer votre site. <small>Pour des raisons de sécurité, pensez à choisir un mot de passe bien difficile à trouver !</small>'
        ); ?>

        <div class="col-md-4 mx-auto">

            <?php
            if ( isset($error) )
                echo $error;
            ?>

            <?= $form->CreateForm('createAdmin'); ?>

        </div>
    </div>
</div>
</body>
<script>
    function check_pass() {
        if (document.getElementsByName('password')[0].value ==
            document.getElementsByName('confirm_password')[0].value) {
            document.getElementsByName('submit')[0].disabled = false;
        } else {
            document.getElementsByName('submit')[0].disabled = true;
        }
    }
</script>
</html>
