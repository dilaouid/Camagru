function like(postId, page) {

    let xhr = new XMLHttpRequest();
    let url = window.location.href + '?id=' + postId + '&like=' + postId;
    xhr.open('GET', url);
    xhr.send();

    let heart = document.getElementById("like_" + postId);
    heart.setAttribute("class", "fa fa-heart");

    let click = document.getElementById("trigger_like_" + postId);
    click.setAttribute("onclick", "unLike("+postId+")");


    let nb_likes = document.getElementById("nb_likes_" + postId).innerHTML;
    nb_likes = parseInt(nb_likes);
    nb_likes = nb_likes + 1;
    document.getElementById("nb_likes_" + postId).innerHTML = nb_likes;

}

function unLike(postId, page) {

    let xhr = new XMLHttpRequest();
    let url = window.location.href + '?id=' + postId + '&unLike=' + postId;
    xhr.open('GET', url);
    xhr.send();

    let heart = document.getElementById("like_" + postId);
    heart.setAttribute("class", "fa fa-heart-o");
    let click = document.getElementById("trigger_like_" + postId);
    click.setAttribute("onclick", "like("+postId+")");

    let nb_likes = document.getElementById("nb_likes_" + postId).innerHTML;
    nb_likes = parseInt(nb_likes);
    nb_likes = nb_likes - 1;
    document.getElementById("nb_likes_" + postId).innerHTML = nb_likes;

}