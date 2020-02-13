<?php

$config_file = file("database.php");
if (count($config_file) === 5) {
    header('Location: create_admin.php');
    exit();
}

require '../Class/Config/Form.php';
require '../Class/Database.php';

$form = new App\Form();

if (isset($_POST['submit'])){

    $host   = htmlentities($_POST['hostname']);
    $user   = htmlentities($_POST['user']);
    $pass   = htmlentities($_POST['password']);
    $db     = htmlentities($_POST['db']);

    try {
        $dbs = new PDO("mysql:host={$host}", $user, $pass);
        $count = $dbs->exec("CREATE DATABASE IF NOT EXISTS `$db` 
                DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
                CREATE USER '$user'@'$db' IDENTIFIED BY '$pass';
                GRANT ALL ON `$db`.* TO '$user'@'$db';
                FLUSH PRIVILEGES;");
    } catch (PDOException $e) {
        $error = TRUE;
    }

    $dsn = "mysql:dbname={$db};host={$host}";
    $database = new App\Database($dsn, $user, $pass);

    $createTable = $database::createTables($database);

    if (isset($count)) {
        $file_config = fopen('database.php', 'w+');

        fputs($file_config, "<?php
        \$DB_DSN = \"mysql:dbname=" . $db . ";host=" . $host . "\";
        \$USER_DB = \"" . $user . "\";
        \$PASSWORD_DB = \"" . $pass . "\";
?>");
        fclose($file_config);
        header('Location: create_admin.php');
        exit();
    }
    unset($_POST);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Installation de Wibuu</title>
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
                'Installation de Wibuu <img class ="mx-auto text-center" src="../assets/img/nyan_face.png" style="max-width:10%;" oncontextmenu="return false;">',
                'Merci d\'installer <strong>Wibuu</strong> ! Le logiciel de réseau social numéro 1 pour ce qui est du partage de photos dans le thème de la japanimation. <small>Saisissez vos informations de connexions pour continuer l\'installation du logiciel.</small>'
        ); ?>

        <div class="col-md-4 mx-auto">

            <?php
            if ( isset($error) )
                echo $form->errorMessage('Les informations entrées sont incorrectes. Merci de revérifier.');
            ?>

            <?= $form->CreateForm('setup'); ?>

        </div>
    </div>
</div>
</body>
</html>