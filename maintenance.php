<?php

session_start();

require_once('Class/Database.php');
require_once('Class/FrontManagment.php');
require_once('Class/Gallery.php');
require_once('config/config.php');

if (!isset($DB_DSN) || !isset($USER_DB) || !isset($PASSWORD_DB))
    header('Location: /config/setup.php');

try {
    $db = new PDO($DB_DSN, $USER_DB, $PASSWORD_DB);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $ex){
    die(header('Location: /config/setup.php'));
}


$global_query = $db->query('SELECT * FROM wibuu_global');
$global = $global_query->fetch(PDO::FETCH_ASSOC);

if ($global['maintenance'] == 0)
    header('Location: index.php');

?>

<!DOCTYPE html>
<html>

<body style="background-image: url('assets/img/ui/bg_maintenance.jpg');">
    <img src="assets/img/ui/OV_maintenance.png">

</body>

</html>