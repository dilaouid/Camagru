<?php
session_start();
setlocale(LC_TIME, 'fr_FR.utf8','fra');

require_once('Class/Database.php');
require_once('Class/Checkdatas.php');
require_once('Class/FrontManagment.php');
require_once('Class/Users.php');
require_once('Class/Gallery.php');
require_once('config/database.php');
require_once('config/checkValid.php');

$profilePage = new App\Checkdatas($db);

$section = 'gallery';

if (isset($_GET['id']) AND is_numeric($_GET['id']))
    $postid = $_GET['id'];
else
    header('Location: gallery.php');

$key = null;
if (isset($_GET['key']))
    $key = htmlentities($_GET['key']);

$Gallery = new App\Gallery($db, $global);
$User = new App\Users($db, null, $global);

if (isset($_GET['like']) AND is_numeric($_GET['like']))
    $Gallery->like($_GET['like']);

if (isset($_GET['unLike']) AND is_numeric($_GET['unLike']))
    $Gallery->unLike($_GET['unLike']);

if (isset($_POST['submit']) AND strlen($_POST['comment']) > 3) {
    if (!ctype_space($_POST['comment'])) {
        $Gallery->newComment($_POST['comment'], $postid);
        if (!empty($Gallery->alert))
            echo "<meta http-equiv='refresh' content='0'>";
    }
}

if (isset($_POST['submit_sm']))
    $Gallery->newComment($_POST['comment'], $postid);

if (isset($_GET['delete_comment']) AND is_numeric($_GET['delete_comment']))
    $Gallery->deleteComment($_GET['delete_comment'], $postid);

if (isset($_GET['action']) AND $_GET['action'] === 'delete')
    $Gallery->deletePost($postid);

$postPage = $db->query("SELECT title FROM wibuu_posts WHERE id = $postid");
$titlePage = $postPage->fetch()[0];


?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?= $global['sitename'] ?> | <?= $titlePage ?></title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/fonts/ionicons.min.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/scrollbar.css">
</head>

<body class="bg_gallery">

    <?= $FrontManagment->navbar($userid, $section); ?>

    <?= $Gallery->alert ?>

   <?= $Gallery->pagePost($postid, $key) ?>



                    </section>
                </div>
            </div>
        </div>

    </div>

    <?= $FrontManagment->footer(); ?>

</body>

<script src="/assets/js/likes.js"></script>
<script src="/assets/js/dropdown.js"></script>

</html>