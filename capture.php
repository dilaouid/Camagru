<?php

session_start();

require_once('Class/Database.php');
require_once('Class/FrontManagment.php');
require_once('Class/Gallery.php');
require_once('config/config.php');
require_once('config/checkValid.php');

$section = 'upload';

if (!isset($_GET['type']) AND $_GET['type'] != 'webcam' AND $_GET['type'] != 'upload' AND $_GET['type'] != 'choice')
    header('Location: index.php');

$type = htmlentities($_GET['type']);

if ($userid == -1)
    header('Location: index.php');

$uploadPicture = new App\Gallery($db, $global);

if (isset($_POST['x_pos'])) {
    if ($type == 'webcam')
        $uploadPicture->submitPicture($_POST);
    else if ($type == 'upload')
        $uploadPicture->submitFile($_POST, $_FILES['imgUpload']);
}

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?= $global['sitename'] ?> | Capture</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/fonts/ionicons.min.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/scrollbar.css">
</head>

<body>
    <?= $FrontManagment->navbar($userid, $section); ?>
    <div class="row justify-content-center">

    <?php if ($type != 'choice') echo $uploadPicture->capturePhoto($type); else { ?>

        <div class="container" style="height: 169%;max-height: 100%;">
            <div class="row text-center">
                <div class="col-md-12" style="padding-top: 75px;padding-bottom: 75px;">
                    <a href="?type=upload">
                        <i class="fa fa-upload" style="font-size: 62px;"></i><p>Uploader une photo</p>
                    </a>
                </div>
            </div>

            <hr>

            <div class="row text-center">
                <div class="col-md-12" style="padding-top: 75px;padding-bottom: 75px;">
                    <a href="?type=webcam">
                        <i class="fa fa-camera" style="font-size: 62px;"></i><p>Prendre une photo</p>
                    </a>
                </div>
            </div>
        </div>
</div>
    <?php } ?>

    <?= $FrontManagment->footer(); ?>

</body>
<script src="/assets/js/dropdown.js"></script>
<script>
const constraints = {
  video: true
};
    const video = document.querySelector('video');
    const screenshotButton = document.querySelector('#screenshot-button');
    const canvas = document.createElement('canvas');
    var submitButton = document.getElementById('submit');
    if (screenshotButton) {
  screenshotButton.onclick = video.onclick = function() {
  canvas.width = video.videoWidth;
  canvas.height = video.videoHeight;
  canvas.getContext('2d').drawImage(video, 0, 0);
  img.src = canvas.toDataURL('image/webp');
  img.style = "";
  screenshotButton.disabled = true;
  submitButton.disabled = false;
  let inputFile = document.getElementsByName('image')[0];
  inputFile.value = img.src;
  video.style = "display:none";

  let checkFilterSelected = document.getElementsByClassName('border rounded border-warning')[0];
  if (checkFilterSelected)
    submitButton.setAttribute("style", "margin-bottom: 36px;");

};
navigator.mediaDevices.getUserMedia(constraints).
  then((stream) => {
    video.srcObject = stream
});}
</script>

<script type="text/javascript">
    function changeFilter(id) {
        var allFilters = document.getElementsByName('filterList');
        i = 0;
        while (allFilters[i]) {
            allFilters[i].setAttribute("style", "width: 130px;filter: grayscale(100%);opacity: 0.30;");
            allFilters[i].setAttribute("class", "");
            i++;
        }
        var selectedFilter = document.getElementById('filter_' + id);
        selectedFilter.setAttribute("style", "width: 130px;background-color: #d7d7d7;");
        selectedFilter.setAttribute("class", "border rounded border-warning");
        var actualFilter = document.getElementById('filter');
        actualFilter.src = 'assets/img/filters/filter_' + id + '.png';
        var inputFilter = document.getElementsByName('filterPost')[0];
        inputFilter.value = id;
        if (inputFile.value != "" || img.src.startsWith('blob'))
            submitButton.setAttribute("style", "margin-bottom: 36px;");
    }

    function moveFilter() {
        var filter = document.getElementById('filter');
        var posX = document.getElementsByName('x_pos')[0].value;
        var posY = document.getElementsByName('y_pos')[0].value;
        var size = document.getElementsByName('size')[0].value;
        filter.setAttribute("style", "margin-top: " + posY + "px; margin-left: " + posX + "px;width: " + size + "px;position: absolute;");

        document.getElementsByName('height')[0].value = filter.clientHeight;
    }

  var loadFile = function(event) {
    var output = document.getElementById('img');
    output.src = URL.createObjectURL(event.target.files[0]);
    img.style = "";
    let checkFilterSelected = document.getElementsByClassName('border rounded border-warning')[0];
    if (checkFilterSelected && img.src.startsWith('blob'))
        submitButton.setAttribute("style", "margin-bottom: 36px;");
  };


  var inputFile = document.getElementsByName('image')[0];

</script>


</html>