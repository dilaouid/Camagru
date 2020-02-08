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
  img.src = canvas.toDataURL('image/png');
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
  