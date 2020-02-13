<?php

session_start();

require_once('Class/Database.php');
require_once('Class/Checkdatas.php');
require_once('Class/IndexManagment.php');
require_once('Class/FrontManagment.php');
require_once('Class/Users.php');
require_once('config/database.php');
require_once('config/checkValid.php');
require_once('config/func.php');

if ($userid == -1){
    header('Location: login.php');
    exit();
}

$FrontManagment = new App\FrontManagment($db, $global);
$section = 'profile';

$Users = new App\Users($db, null, $global);

if (isset($_POST['submit'])) {
    if (!isset($_POST['private']))
        $_POST['private'] = 'off';
    if (!isset($_POST['notifications']))
        $_POST['notifications'] = 'off';
    if (isset($_FILES['avatar'])) 
        $Users->newAvatar($_FILES, $userid);
    $Users->changeInfos($_POST);
}

$userInfos = $Users->infoUsers($userid);

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?= $global['sitename'] ?> | Profil</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/fonts/ionicons.min.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    
    <?= $FrontManagment->navbar($userid, $section); ?>
    
    <div>
        <div class="row bg_edit_profile">

            <div class="col-6 col-sm-12 col-md-12 col-lg-12 col-xl-6 offset-xl-0 text-right text-sm-center text-md-center text-lg-center text-xl-right edit_profile_avatar">
                <div class="row justify-content-end">
                    <div class="col-1 col-sm-9 col-md-10 col-lg-11 col-xl-auto edit_profile_avatarbloc">
                        <img class="rounded-circle avatar_edit" src="assets/img/profil_pictures/<?= $userInfos['avatar']; ?>"
                        style="width: 175px;height: 175px">
                    </div>
                </div>

                <form method="post" action="" enctype="multipart/form-data">

                <div class="row text-right text-lg-center text-xl-right justify-content-end justify-content-sm-center justify-content-md-center justify-content-lg-center justify-content-xl-end">

                    <div class="col-auto">
                        <div class="custom-file" style="width: 300px;">
                            <input type="file" class="custom-file-input" accept="image/*" name="avatar">
                            <label class="text-left custom-file-label">Nom du fichier</label>
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-auto col-sm-12 col-md-12 col-lg-12 col-xl-auto editbloc">
                <h1 class="display-4 text-center title_editprofile">Éditer le profil</h1>

                
                    <div class="form-row justify-content-sm-center justify-content-lg-center justify-content-xl-start profile-row fieldedit">
                        <div class="col-auto col-sm-12 col-md-12 col-lg-7 col-xl-12">

                            <div class="form-group">
                                <label>Nom d'utilisateur</label>
                                <input class="form-control" type="username" name="username" placeholder="<?= $userInfos['username'] ?>">
                            </div>

                            <div class="form-group">
                                <label>Email</label>
                                <input class="form-control" type="email" name="email" placeholder="<?= $userInfos['email'] ?>">
                            </div>

                            <div class="form-row">
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label>Mot de passe</label>
                                        <input class="form-control" type="password" name="password">
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label>Confirmer mot de passe</label>
                                        <input class="form-control" type="password" name="confirm_password">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Facebook</label>
                                <input class="form-control" type="facebook" name="facebook" placeholder="https://www.facebook.com/<?= $userInfos['facebook'] ?>">
                            </div>

                            <div class="form-group">
                                <label>Instagram</label>
                                <input class="form-control" type="instagram" name="instagram" placeholder="https://www.instagram.com/<?= $userInfos['instagram'] ?>">
                            </div>

                            <div class="form-group">
                                <label>Twitter</label>
                                <input class="form-control" type="twitter" name="twitter" placeholder="https://www.twitter.com/<?= $userInfos['twitter'] ?>">
                            </div>

                            <?php

                            if ($userInfos['private'] == 1)
                                $checkedPriv = 'checked';
                            else
                                $checkedPriv = null;

                            if ($userInfos['notifications'] == 1)
                                $checkedNotif = 'checked';
                            else
                                $checkedNotif = null;

                            ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="formCheck-1" name="private" <?= $checkedPriv ?>>
                                <label class="form-check-label" for="formCheck-1" >Compte privé</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="formCheck-2" name="notifications" <?= $checkedNotif ?>>
                                <label class="form-check-label" for="formCheck-2" >Recevoir les notifications par mail</label>
                            </div>

                            <hr>

                            <div class="form-row">

                                <div class="col-md-12 content-right">
                                    <button class="btn btn-primary form-btn" name="submit" type="submit">ENREGISTRER</button>
                                    <button class="btn btn-danger form-btn" type="reset">ANNULER</button>
                                </div>

                                <?php if (isset($Users->alert)) echo $Users->alert; ?>
<!-- 
                                 <div class="col">
                                    <div class="alert alert-success" role="alert" style="width: 100%;margin-top: 24px;">
                                        <span><strong>Alert</strong> text.</span>
                                    </div>
                                </div>     -->       

                            </div>

                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?= $FrontManagment->footer(); ?>

</body>

<script src="/assets/js/dropdown.js"></script>

</html>