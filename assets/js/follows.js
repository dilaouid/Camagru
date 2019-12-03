
function askFollow(admin) {

    let xhr = new XMLHttpRequest();
    let url = window.location.href + '&askFollow=1';
    xhr.open('GET', url);
    xhr.send();

    if (document.getElementById("private") && admin == 0) {
        let button = document.getElementById("buttonFollow");
        button.setAttribute("class", "btn btn-outline-primary");
        button.setAttribute("onclick", "stopFollow("+admin+")");
        button.innerHTML = "Annuler la demande";
    }

    else {
        let button = document.getElementById("buttonFollow");
        button.setAttribute("class", "btn btn-outline-primary");
        button.setAttribute("onclick", "unFollow()");
        button.innerHTML = "Ne plus suivre";
        
        if (document.getElementById("private") && admin == 1)
            document.location.reload(true);
        else if (document.getElementById("private") && admin == 0) {
            let followers = document.getElementById("followers").innerHTML;
            followers = parseInt(followers);
            followers = followers + 1;
            document.getElementById("followers").innerHTML = followers;
        }
        else {
            let followers = document.getElementById("followers").innerHTML;
            followers = parseInt(followers);
            followers = followers + 1;
            document.getElementById("followers").innerHTML = followers;            
        }
    }
}

function stopFollow(admin) {

    let xhr = new XMLHttpRequest();
    let url = window.location.href + '&stopFollow';
    xhr.open('GET', url);
    xhr.send();

    let button = document.getElementById("buttonFollow");
    button.setAttribute("class", "btn btn-primary");
    button.setAttribute("onclick", "askFollow("+admin+")");
    button.innerHTML = "Suivre";

}


function acceptFollow() {

    let xhr = new XMLHttpRequest();
    let url = window.location.href + '&acceptFollow';
    xhr.open('GET', url);
    xhr.send();

    let button = document.querySelector('#acceptFollow');
    button.parentNode.removeChild(button);

}

function unFollow() {

    let xhr = new XMLHttpRequest();
    let url = window.location.href + '&unFollow';
    xhr.open('GET', url);
    xhr.send();

    document.location.reload(true);

}
