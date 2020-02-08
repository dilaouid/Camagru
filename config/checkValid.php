<?php
if (!isset($DB_DSN) || !isset($USER_DB) || !isset($PASSWORD_DB))
    header('Location: /config/setup.php');

try {
    $db = new PDO($DB_DSN, $USER_DB, $PASSWORD_DB);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $ex){
    die(header('Location: /config/setup.php'));
}

$checkDatabase = $db->query('SELECT COUNT(id) FROM wibuu_users WHERE admin = 1');
$data = $checkDatabase->fetch();
if ($data[0] == 0)
    header('Location: /config/create_admin.php');

$global_query = $db->query('SELECT * FROM wibuu_global');
$global = $global_query->fetch(PDO::FETCH_ASSOC);

if (isset($_SESSION['id']))
    $userid = $_SESSION['id'];
else
	$userid = -1;

$FrontManagment = new App\FrontManagment($db, $global);

if (!isset($loginpage) && $global['maintenance'] == 1)
	$forbiddenpage = true;


if (isset($forbiddenpage) AND (!$FrontManagment->isAdmin($userid))) {
	header('Location: maintenance.php');
}

?>