<?php

namespace App;

class IndexManagment {

    public static function noTop() {
        return '<p class="text-center"><em>Aucune publication pour le moment...</em></p>';
    }

    public function features($db, $global) {
        $features_query = $db->query('SELECT title, description, icon FROM wibuu_features');
        if ($features_query->rowCount() == 0)
            return null;
        $Content = '<div class="shadow-lg features-clean features_index"><div class="container d-table features_container">
                <div class="intro">
                <h2 class="text-center" style="color: rgb(243,24,37);"><img class="mascot_2" src="assets/img/ui/misc/chibi02.png">Pourquoi '. $global['sitename'] .' ?</h2>
                <p class="text-center">Nunc luctus in metus eget fringilla. Aliquam sed justo ligula. Vestibulum nibh erat, pellentesque ut laoreet vitae. </p>
                </div><div class="row features">';
        while ($data = $features_query->fetch())
            $Content .= '<div class="col-sm-6 col-lg-4 item"><i class="fa '. $data['icon'] .' icon" style="color: #afbab2;"></i>
            <h3 class="text-white-50 name">'. $data['title'] .'</h3>
            <p class="description">'. $data['description'] .'</p></div>';
        $Content .= '</div></div></div>';
        return $Content;
    }

    public function getTop($db, $query, $userid) {
        while ($getContent = $query->fetch()) {
            $checklike = $db->prepare('SELECT COUNT(user) FROM wibuu_likes WHERE user = ? AND likes = ?');
            $checklike->execute(array($userid, $getContent['post_id']));
            $userLiked = $checklike->fetch()[0];
            if ($userLiked == 0)
                $like = '<i class="fa fa-heart-o"></i>';
            else
                $like = '<i class="fa fa-heart"></i>';
            $checklike->closeCursor();

            $checkcomment = $db->prepare('SELECT COUNT(id) FROM wibuu_comments WHERE author = ? AND post = ? AND active = 1');
            $checkcomment->execute(array($userid, $getContent['post_id']));
            $userComment = $checkcomment->fetch()[0];
            if ($userComment == 0)
                $comment = '<i class="fa fa-comment-o"></i>';
            else
                $comment = '<i class="fa fa-comment"></i>';
            $checkcomment->closeCursor();

            $Content = '<div class="col-sm-6 col-md-4 col-lg-3 text-center d-inline item top_post_index_base">
            <h6 class="display-3 text-center title_post_index">'. $getContent['title'] .'</h6>
            <small><a href="profile.php?id='. $getContent['userid'] .'" class="username_top_post_index">'. $getContent['username'] .'</a></small>
            <figure class="figure figure_top_post">
            <img class="rounded-circle img-fluid figure-img profile_picture_top_index" src="assets/img/posts/'. $getContent['img'] .'">
                <p class="figure-caption text-white-50 description_top_index">'. $getContent['description'] .'<br></p>
                <figcaption class="figure-caption d-inline-block icon_top_index">'. $like .'&nbsp'. $getContent['nb_likes'] .'<br></figcaption>
                <figcaption class="figure-caption d-inline-block icon_top_index">'. $comment .'&nbsp'. $getContent['nb_comments'] .'<br></figcaption>
            </figure>
            <a href="post.php?id='. $getContent['post_id'] .'" class="username_top_post_index">Voir la publication</a>
            </div>';

            echo $Content;
        }
    }

}