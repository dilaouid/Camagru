function deleteNotif(id) {

    let xhr = new XMLHttpRequest();
    let url = window.location.href + '?delete=' + id;
    xhr.open('GET', url);
    xhr.send();

    console.log(xhr);

    let line = document.querySelector('#line_' + id);
    line.parentNode.removeChild(line);

}