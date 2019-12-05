<?php

session_start();

require_once($_SERVER['DOCUMENT_ROOT'].'/Class/Database.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/Class/AdminManagment.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/Class/FrontManagment.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/Class/Users.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/config/database.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/config/checkValid.php');

if (!isset($userid))
    header('Location: /index.php');
$AdminManagment = new App\AdminManagment($db, $global);
if ($AdminManagment->admin == 0)
    header('Location: /index.php');

$section = 'Features';

$User = new App\Users($db, null, $global);

if (!isset($_GET['option']) OR ($_GET['option'] != 'edit' AND $_GET['option'] != 'create'))
    header('Location : index.php');

if ($_GET['option'] == 'edit')
    $option = "Éditer une feature";
else
    $option = "Gérer les features";

if ($_GET['option'] == 'edit' AND (!isset($_GET['id']) OR !is_numeric($_GET['id'])))
    header('Location: features.php?option=create');

$id = 0;
if (isset($_GET['id']))
    $id = $_GET['id'];

$page = htmlentities($_GET['option']);

if (isset($_POST['submit'])) {

    if ($page == "edit") 
        $AdminManagment->editFeature($_POST, $id);
    else
        $AdminManagment->newFeature($_POST);

    if (empty($AdminManagment->alert) AND empty($Admin->alert))
            echo "<meta http-equiv='refresh' content='0'>";
}

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?= $global['sitename'] ?> | Features</title>
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="/assets/fonts/ionicons.min.css">
    <link rel="stylesheet" href="/assets/css/navbar.css">
    <link rel="stylesheet" href="/assets/css/footer.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>

<body style="background-color: #e0e0e0;">

    <?= $FrontManagment->navbar($userid, $section); ?>

    <?= $AdminManagment->navbar($section); ?>

    <div>

        <div class="container" style="margin-top: 11px;margin-bottom: 20px;">
            <div class="row">
                <div class="col">
                    <div class="row justify-content-center align-items-center">
                        <div class="col-auto"><img src="../assets/img/ui/features.png" style="width: 157px;"></div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <h1 class="display-4 text-center" style="font-size: 46px;"><?= $option ?></h1>
                        </div>
                    </div>
                    <?= $AdminManagment->alert;  ?>
                    <div class="card-group" style="margin-bottom: 15px;">
                        <div class="card">
                            <div class="card-body">
                                <?= $AdminManagment->features($page, $option, $id); ?>
                </div>
            </div>
        </div>
    </div>
    <?= $FrontManagment->footer(); ?>
</body>

</html>