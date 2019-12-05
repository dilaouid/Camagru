<?php

namespace App;

use \PDO;

class Gallery {

    private $db;
    private $global;
    private $userid;
    private $admin = 0;
    private $alert;

    public  $nbPages;

    public function __construct($db, $global = null) {
        $this->db       = $db;
        $this->global   = $global;
        if (isset($_SESSION['id'])) {
            $this->userid = $_SESSION['id'];
            $this->admin = $this->isAdmin($this->userid);
        }
        else
            $this->userid = -1;
    }

    private function isAdmin($id) {
        $query = $this->db->query("SELECT id FROM wibuu_users WHERE admin = 1 AND id = $id");
        return ($query->rowCount());
    }

    private function humanTiming($time) {
        $time = strtotime($time);
        $time = strtotime(date('Y-m-d h:i:s', time())) - $time;
        $tokens = array (
            31536000 => 'an',
            2592000 => 'mois',
            604800 => 'semaine',
            86400 => 'jour',
            3600 => 'heure',
            60 => 'minute',
            1 => 'seconde'
        );
        foreach ($tokens as $unit => $text) {
            if ($time < $unit AND $time != 0) continue;
                $numberOfUnits = floor($time / $unit);
            if ($time == 0)
                return 'UN INSTANT';
            return strtoupper($numberOfUnits.' '.$text.(($numberOfUnits > 1 AND $text != 'mois')?'s':''));
        }

    }

    private function getFilter($idFilter) {
        $query = $this->db->query("SELECT name FROM wibuu_filters WHERE id = $idFilter");
        $filter = $query->fetch()[0];
        return $filter;
    }

    private function isFollowing($user, $follows) {
        $query = $this->db->query("SELECT id FROM wibuu_follows WHERE user = $user AND follows = $follows AND accepted = 1");
        return ($query->rowCount());
    }

    private function showPost($private, $author) {
        if ($private == 1 AND $this->userid != $author AND $this->admin != 1) {
            $query = $this->db->query("SELECT id FROM wibuu_follows WHERE follows = $author AND user = $this->userid AND accepted = 1");
            if ($query->rowCount() == 0)
                return 0;
        }
        return 1;
    }

    private function userLikes($postid) {
        $query = $this->db->query("SELECT id FROM wibuu_likes WHERE user = $this->userid AND likes = $postid");
        if ($query->rowCount() > 0)
            return ;
        return '-o';
    }

    private function userCommented($postid) {
        $query = $this->db->query("SELECT id FROM wibuu_comments WHERE author = $this->userid AND post = $postid AND active = 1");
        if ($query->rowCount() > 0)
            return ;
        return '-o';
    }

    private function getAuthorInfo($id) {
        $query = $this->db->query("SELECT id, avatar, username, private, admin FROM wibuu_users WHERE id = $id");
        $data = $query->fetch();
        return $data;
    }

    public function uploadFile($file, $newName, $path, $legalExtensions, $legalSize, $outputExt) {

        $actualName = $file['tmp_name'];
        $actualSize = $file['size'];
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);

        if ($file['tmp_name'] === 0 OR $actualSize == 0) {
            $this->alert = '<div class="col"><div class="alert alert-danger" role="alert" style="width: 100%;margin-top: 24px;"><span>L\'avatar est invalide ! Taille max: '.($legalSize / 1000).' ko / Formats : '.implode(' ', $legalExtensions).'</span></div></div>';
            return 0;
        }

        if (file_exists($path.'/'.$newName.'.'.$extension))
            unlink($path.'/'.$newName.'.'.$extension);

        if ($actualSize < $legalSize) {
            if (in_array($extension, $legalExtensions))
                move_uploaded_file($actualName, $path.'/'.$newName.'.'.$outputExt);
            else {
                $this->alert = '<div class="col"><div class="alert alert-danger" role="alert" style="width: 100%;margin-top: 24px;"><span>Le fichier est invalide ! Formats acceptés : '.implode(' ', $legalExtensions).'</span></div></div>';
                return 0;
            }
        }
        else {
            $this->alert = '<div class="col"><div class="alert alert-danger" role="alert" style="width: 100%;margin-top: 24px;"><span>L\'avatar est trop lourd ! Taille max: '.($legalSize / 1000).' ko</span></div></div>';
            return 0;
        }
        return 1;
    }

    private function getLastComments($nbcomments, $postid) {
        if ($nbcomments == 0)
            return ;
        $query = $this->db->query("SELECT author, post, date, comment FROM wibuu_comments WHERE post = $postid AND active = 1 ORDER BY date DESC");
        if ($query->rowCount() == 0)
            return ;
        $dataComment = $query->fetch(PDO::FETCH_ASSOC);
        $authorComment = $this->getAuthorInfo($dataComment['author']);
        $lastCommenteur = $authorComment[2];
        $Timestamp = $dataComment['date'];
        $momentComment = '<div class="row"><div class="col"><p style="font-size: 10px;">IL Y A '.$this->humanTiming($Timestamp).'</p></div></div>';
        if ($query->rowCount() == 1)
            return '<a href="profile.php?id='.$authorComment['id'].'" class="keep_proper_link"><strong>'.$lastCommenteur.'</a> </strong>'.$dataComment['comment'].'<br></p>'.$momentComment;
        return '<a href="post.php?id='.$postid.'" class="keep_proper_link"><strong>Afficher les '.$nbcomments.' commentaires</strong></a><br>
        <a href="profile.php?id='.$authorComment['id'].'" class="keep_proper_link"><strong>'.$lastCommenteur.'</a> </strong>'.$dataComment['comment'].'<br></p>'.$momentComment;
    }

    private function allowComment($postid,$counter) {
        if ($this->userid == -1)
            return '<div class="row"><div class="col"><p class="text-center" style="font-size: 12px;"><a href="login.php">Connectez vous</a> pour interagir</p></div></div>';
        else
            return '<form method="post" action="">
        <div class="row">

                            

                            <input type="hidden" name="postid" value="'.$postid.'">

                            <div class="col-9 align-self-center justify-content-xl-center">
                                <input name="comment_'.$counter.'" class="form-control-lg d-flex justify-content-center align-items-center" type="text" style="width: 100%; font-size: 12px;" placeholder="Votre commentaire...">
                            </div>

                            <div class="col-3 d-inline-flex align-self-center justify-content-xl-center align-items-xl-center">
                                <button class="btn btn-danger btn-sm" type="submit" value="'.$counter.'" name="submit">Envoi</button>
                            </div>

                            

                        </div></form>';
    }



    public function printPosts($page, $limit) {

        $Content = '<div class="row">
        <div class="col-sm-auto col-md-8 col-lg-6 col-xl-4 offset-sm-0 offset-md-0 offset-lg-0 offset-xl-2 d-table-cell bg_bloc_gallery">
        <div>';

        $start = ($page - 1) * $limit;
        $query = $this->db->prepare("SELECT wibuu_posts.id, filter, title, wibuu_posts.description, img, date, author, nb_likes, nb_comments

                                    FROM wibuu_posts

                                    INNER JOIN wibuu_users

                                    WHERE active = 1 AND wibuu_posts.private = 0 AND wibuu_posts.author = wibuu_users.id
                                    ORDER BY wibuu_posts.date DESC
                                    LIMIT 100 OFFSET :start");
        $query->bindValue('start', $start, PDO::PARAM_INT);
        $query->execute();

        $counter = 0;

        $totalQuery = $this->db->query("SELECT author FROM wibuu_posts WHERE active = 1");
        $nbEntries = 0;
        while ($dataCheck = $totalQuery->fetch(PDO::FETCH_ASSOC)) {
            $userTruc = $this->getAuthorInfo($dataCheck['author']);
            if ($userTruc['private'] == 0 OR $userTruc['id'] == $this->userid)
                $nbEntries++;
            else if ($userTruc['private'] == 1 AND $this->isFollowing($this->userid, $userTruc['id']))
                $nbEntries++;
            else if ($this->admin == 1)
                $nbEntries++;
        }

        $this->nbPages = ceil($nbEntries / $limit);

        $totalQuery->closeCursor();

        while ($datas = $query->fetch(PDO::FETCH_ASSOC) AND $counter < $limit) {

            $userInfos = $this->getAuthorInfo($datas['author']);
            if ($this->showPost($userInfos['private'], $datas['author']) OR $this->admin == 1) {

                if ($this->userLikes($datas['id']))
                    $function = 'like';
                else
                    $function = 'unLike';


                if ($this->userid != -1)
                    $onclick = 'onclick="'.$function.'('.$datas['id'].', '. $page .')"';
                else
                    $onclick = null;

                $Content .= '<div class="card d-flex d-xl-flex justify-content-center justify-content-xl-center align-items-xl-center" style="margin-top: 34px;">
                <div class="card-body">
                <div class="row justify-content-start align-items-center" style="margin-bottom: 9px;">
                <div class="col-auto offset-0"><a href="profile.php?id='.$userInfos['id'].'"><img class="border rounded-circle" src="assets/img/profil_pictures/'.$userInfos['avatar'].'" style="width: 61px;"></a></div>
                <div class="col">
                    <h4 class="text-left"><a href="post.php?id='.$datas['id'].'"  class="keep_proper_link">'.$datas['title'].'</a></h4>
                    <h6 class="text-left text-muted mb-2" style="font-size: 14px;">Filtre: '.$this->getFilter($datas['filter']).'</h6>
                 </div>
                        </div><div class="row">
                            <div class="col">
                                <img class="img-fluid" src="assets/img/posts/'.$datas['img'].'">
                            </div>
                        </div>


                        <div class="row" style="margin-bottom: 4px;">
                            <div class="col-auto">
                                <span id="trigger_like_'.$datas['id'].'" '.$onclick.'"><i id="like_'.$datas['id'].'" class="fa fa-heart'.$this->userLikes($datas['id']).'" style="font-size: 23px;line-height: 35px;color: rgb(243,24,37);"></i></span>
                            </div>
                            <div class="col-auto">
                                <a href="post.php?id='.$datas['id'].'"><i class="fa fa-comment'.$this->userCommented($datas['id']).'" style="font-size: 23px;line-height: 35px;" href="post.php?id='.$datas['id'].'"></i></a>
                            </div>
                            <div class="col-auto">
                                <i class="fa fa-share-square-o" style="font-size: 23px;line-height: 35px;"></i>
                            </div>
                        </div>



                        <div class="row">
                            <div class="col" style="max-width:450px">
                                <p class="text-left" style="width: auto;font-size: 14px;margin-bottom: 0px;">
                                    <strong><span id="nb_likes_'.$datas['id'].'">'.$datas['nb_likes'].'</span> J\'aime</strong><br>
                                    <a href="profile.php?id='.$userInfos['id'].'" class="keep_proper_link"><strong>'.$userInfos['username'].'</a></strong>
                                    &nbsp;'.$datas['description'].'<br><br>
                                    IL Y A '.$this->humanTiming($datas['date']).'<br>
                                    '.$this->getLastComments($datas['nb_comments'], $datas['id']).'
                            </div>
                        </div>'.$this->allowComment($datas['id'], $counter).'</div></div>';
                        $counter++;
                        
            }
        }
        if ($counter == 0) {
            $Content .= '<div class="card d-flex d-xl-flex justify-content-center justify-content-xl-center align-items-xl-center" style="margin-top: 34px;">
                <div class="card-body">
                <p>Aucun contenu pour le moment</p>
                </div></div>';
        }

        return $Content.'</div>';
    }

    private function fillBlocPanel($authorInfos, $data, $counter) {
        $bg = null;
            if ($counter % 2 == 0)
                $bg = 'background-color: #bf141e;';
            return ('<div class="row justify-content-center align-items-center" style="margin-top: 10px;'.$bg.'">
                <div class="col-auto"><a href="profile.php?id='.$authorInfos['id'].'"><img class="rounded-circle" src="assets/img/profil_pictures/'.$authorInfos['avatar'].'" style="width: 52px;"></a></div>
                <div class="col-9" style="margin-top: 5px;">
                    <div class="row">
                        <div class="col-auto">
                            <p class="text-white-50"><a href="profile.php?id='.$authorInfos['id'].'" class="keep_proper_link"><strong>'.$authorInfos['username'].' </strong></a>a publié&nbsp;<a href="post.php?id='.$data['id'].'" class="keep_proper_link"><strong>'.$data['title'].'</strong></a><br></p>
                        </div>
                    </div>
                    <div class="row justify-content-center align-items-center">
                        <div class="col" style="margin-top: -19px;">
                            <p class="text-uppercase text-white" style="font-size: 12px;">IL Y A '.$this->humanTiming($data['date']).'</p>
                        </div>
                    </div>
                </div>
            </div>');
    }

    public function printFollowingPanel() {
        $counter = 0;
        $Content = '<div id="following">';
        $query = $this->db->query("SELECT wibuu_posts.id, title, date, author 
                                  FROM wibuu_posts
                                  INNER JOIN wibuu_follows ON user = $this->userid AND follows = author AND accepted = 1
                                  WHERE active = 1 AND private = 0 AND author != $this->userid ORDER by date DESC LIMIT 20");
        if ($query->rowCount() == 0)
            $Content .= '<div class="row justify-content-center align-items-center" style="margin-top: 10px;"><p class="text-white-50">Aucune nouveauté chez vos abonnements.</p></div>';
        while ($data = $query->fetch(PDO::FETCH_ASSOC)) {
            $authorInfos = $this->getAuthorInfo($data['author']);
            $Content .= $this->fillBlocPanel($authorInfos, $data, $counter);
            $counter++;
        }
        return $Content.'</div>';
    }

    public function printPopularPanel() {
        $counter = 0;
        $Content = '<div id="popular" style="">';
        if ($this->userid != -1)
            $Content = '<div id="popular" style="display: none;">';
        $query = $this->db->query("SELECT id, title, date, author FROM wibuu_posts WHERE active = 1 AND private = 0 ORDER BY (nb_comments + nb_likes) DESC LIMIT 20");
        if ($query->rowCount() == 0)
            $Content .= '<div class="row justify-content-center align-items-center" style="margin-top: 10px;"><p class="text-white-50">Aucune publication.</p></div>';
        while ($data = $query->fetch(PDO::FETCH_ASSOC)) {
            $authorInfos = $this->getAuthorInfo($data['author']);
            if ($this->admin == 1 OR $authorInfos['private'] == 0 OR $this->userid == $data['author'] OR $this->isFollowing($this->userid, $data['author'])) {
                $Content .= $this->fillBlocPanel($authorInfos, $data, $counter);
                $counter++;
            }
        }
        return $Content.'</div>';
    }


    public function pagination($nbPages, $actualPage) {
        
        $page = 1;
        if ($nbPages <= 1)
            return ;

        $Content = '<div class="row text-center justify-content-center">
                <div class="col-auto text-center">
                    <nav class="d-xl-flex justify-content-xl-center align-items-xl-center" style="margin-top: 16px;filter: hue-rotate(137deg);">
                        <ul class="pagination pagination-sm">';

        if ($actualPage > 1)
            $Content .= '<li class="page-item"><a class="page-link" href="?page='. ($actualPage - 1) .'" aria-label="Previous"><span aria-hidden="true">«</span></a></li>';

        while ($page <= $nbPages) {
            $active = null;
            if ($page == $actualPage)
                $active = 'active';
            $Content .= '<li class="page-item '.$active.'"><a class="page-link" href="?page='. $page .'">'.$page.'</a></li>';
            $page++;
        }

        if ($nbPages > 1 AND $actualPage != $nbPages)
            $Content .= '<li class="page-item"><a class="page-link" href="?page='.($actualPage + 1).'" aria-label="Next"><span aria-hidden="true">»</span></a></li>';

        $Content .= '</ul></nav></div></div>';

        return $Content;
                        
    }

    private function sendMail($to, $type, $id) {


        if ($type == 'comment') {
            $getAuthorInfo = $this->db->query("SELECT username, notifications, email FROM wibuu_users WHERE id = $to");
            $dataAuthor = $getAuthorInfo->fetch(PDO::FETCH_ASSOC);
        }
        if ($dataAuthor['notifications'] == 0) 
            return ;

        $getPostinfo = $this->db->query("SELECT title FROM wibuu_posts WHERE id = $id");
        $dataPost = $getPostinfo->fetch(PDO::FETCH_ASSOC);

        $post_link  = $_SERVER['SERVER_NAME'].'/post.php?id='.$id;
        $subject            = 'Il s\'en passe des chose sur '.$this->global['sitename'].' !';
        $message            = '<html><body><p>Bonjour '.$dataAuthor['username'].' !<br>';

        if ($type == 'comment') {
            $message .= 'Un utilisateur de '.$this->global['sitename'].' vient de commenter votre publication <a href="http://'.$post_link.'">'.$dataPost['title'].'</a><br/>Vous commencez à être populaire on dirait ! ...<br/><br>Si vous ne voulez plus recevoir de notifications par mail, vous pouvez désactiver cette fonctionnalité depuis le <a href="http://'.$_SERVER['SERVER_NAME'].'/edit_profile.php">paramètrage de votre profil </a>!<br><br>';
        }

        $message .= 'Cordialement,<br>
    L\'équipe '.$this->global['sitename'].'<br><br>

    Note: Ce mail est automatique, merci de ne pas y répondre.</p></body></html>';
        $headers            = 'MIME-Version: 1.0' . "\r\n";
        $headers            .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
        $headers            .= 'From: noreply@'.$this->global['sitename'].'.com';

        mail($dataAuthor['email'], $subject, $message, $headers);
        return ;
    }

    public function newComment($comment, $postid) {
        $htmlcomment = htmlentities($comment);
        if (!is_numeric($postid))
            return ;

        $checkQuery = $this->db->prepare("SELECT id FROM wibuu_posts WHERE id = ?");
        $checkQuery->execute(array($postid));

        if ($checkQuery->rowCount() == 0)
            return ;

        $timestamp = date('Y-m-d h:i:s', time());
        $query = $this->db->prepare("INSERT INTO wibuu_comments (author, post, date, comment) VALUES ($this->userid, $postid, '$timestamp', ?)");
        $query->execute(array($htmlcomment));

        $query->closeCursor();

        $getPostAuthor = $this->db->query("SELECT author FROM wibuu_posts WHERE id = $postid");
        if ($checkQuery->rowCount() == 0)
            return ;
        $authorPost = $getPostAuthor->fetch()[0];

        $getObject = $this->db->query("SELECT id FROM wibuu_comments WHERE wibuu_comments.date = '$timestamp'");
        $object = $getObject->fetch()[0];

        if ($authorPost != $this->userid) {
            $notif = $this->db->query("INSERT INTO wibuu_notifications (author, dest, type, date, id_link, object) VALUES ($this->userid, $authorPost, 'comment', '$timestamp', $postid, $object)");
            $this->sendMail($authorPost, 'comment', $postid);
        }

        $newComm = $this->db->query("UPDATE wibuu_posts SET nb_comments = nb_comments + 1 WHERE id = $postid");

        return 1;
    }

    public function pagePost($postid, $key) {

        $page = 0;

        $query = $this->db->query("SELECT * FROM wibuu_posts WHERE id = $postid AND active = 1");

        if ($query->rowCount() == 0)
            header('Location: gallery.php');
        $data = $query->fetch(PDO::FETCH_ASSOC);
        if ($data['private'] == 1 AND $key != $data['keylock']) 
            header('Location: gallery.php');
        $authorInfos = $this->getAuthorInfo($data['author']);

        if ($this->admin != 1 AND $this->userid != $authorInfos['id'] AND $authorInfos['private'] == 1 AND !$this->isFollowing($this->userid, $authorInfos['id']))
            header('Location: gallery.php');

        if ($this->userLikes($data['id']))
            $function = 'like';
        else
            $function = 'unLike';

        if ($this->userid != -1)
            $onclick = 'onclick="'.$function.'('.$data['id'].', '. $page .')"';
        else
            $onclick = null;

        $Content = '<div class="row no-gutters justify-content-center justify-content-md-start justify-content-lg-start justify-content-xl-center">
        <div class="col-sm-auto col-md-6 col-lg-6 col-xl-3" style="background-color: #f2f2f2;">
            <div>
                <div class="card" style="margin-top: 34px;">
                    <div class="card-body">

                        <div class="row justify-content-start align-items-center" style="margin-bottom: 9px;">
                            <div class="col-auto offset-0">

                                <a href="profile.php?id='.$authorInfos['id'].'"><img class="border rounded-circle" src="assets/img/profil_pictures/'.$authorInfos['avatar'].'" style="width: 61px;"></a>

                            </div>
                            <div class="col">
                                <h4 class="text-left" style="margin-bottom: 0px;">'.$data['title'].'</h4>
                                <h6 class="text-left text-muted mb-2" style="font-size: 14px;">Filtre: '.$this->getFilter($data['filter']).'</h6>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col"><img class="img-fluid" src="assets/img/posts/'.$data['img'].'"></div>
                        </div><div class="row" style="margin-bottom: 4px;"><div class="col-auto">
                        <span id="trigger_like_'.$data['id'].'" '.$onclick.'>
                        <i id="like_'.$data['id'].'" class="fa fa-heart'.$this->userLikes($data['id']).'" style="font-size: 23px;line-height: 35px;color: rgb(243,24,37);"></i>
                        </div></span>
                        <div class="col-auto"><i class="fa fa-comment'.$this->userCommented($data['id']).'" style="font-size: 23px;line-height: 35px;"></i></div>
                        <div class="col-auto"><i class="fa fa-share-square-o" style="font-size: 23px;line-height: 35px;"></i></div>';

        if ($this->admin == 1 OR $this->userid == $authorInfos['id'])
            $Content .= '<div class="col-auto"><a href="?id='.$data['id'].'&action=delete" class="keep_proper_link"><i class="fa fa-remove" style="font-size: 23px;line-height: 35px;color: rgb(255,56,56);"></i></a></div>';

        $Content .= '</div><div class="row">
                        <div class="col">

                        <div>
                            <p class="text-left" style="width: auto;font-size: 14px;margin-bottom: 0px;"><strong><span id="nb_likes_'.$data['id'].'"> '.$data['nb_likes'].'</span> J\'aime</strong><br>
                                        <strong>'.$authorInfos['username'].'</strong>&nbsp;
                                        '.$data['description'].'<br></p>
                                </div></div></div>';

        if ($data['nb_comments'] > 0) {
            $getComment_query = $this->db->query("SELECT id, author, date, comment FROM wibuu_comments WHERE active = 1 AND post = $postid");
            while ($getComment = $getComment_query->fetch(PDO::FETCH_ASSOC)) {
                $getCommentateur = $this->getAuthorInfo($getComment['author']);
                $Content .= '<div class="row d-md-none">
                            <div class="col" style="height: 67px;margin-top: -25px;margin-bottom: -5px;">
                                <div style="margin-top: 24px;">

                                    <div style="margin-bottom: -17px;">
                                        <p class="d-print-none d-md-none d-xl-none" style="font-size: 14px;">
                                            <a href="profile.php?id='.$getCommentateur['id'].'" class="keep_proper_link"><strong>'.$getCommentateur['username'].'</strong></a>&nbsp;
                                            '.$getComment['comment'].'&nbsp;
                                        ';
                if ($getCommentateur['id'] == $this->userid OR $this->admin == 1)
                    $Content .= '<a href="post.php?id='.$postid.'&delete_comment='.$getComment['id'].'"><i class="fa fa-remove" style="color: rgb(116,116,116);"></i></a>';
                $Content .= '</p></div><div class="row" style="height: 21px;margin-bottom: 11px;"><div class="col"><p style="font-size: 10px;">IL Y A '.$this->humanTiming($getComment['date']).'</p></div></div></div></div></div>';
            }
        }
            if ($this->userid != -1)
                $Content .= '<form method="post" action="">
                        <div class="row d-print-none d-md-none d-lg-none d-xl-none">
                            <div class="col-9 align-self-center justify-content-xl-center">
                                <input class="form-control-lg d-flex justify-content-center align-items-center" type="text" name="comment" style="width: 100%;font-size: 12px;" placeholder="Votre commentaire...">
                            </div>
                            <div class="col-3 d-inline-flex align-self-center justify-content-xl-center align-items-xl-center">
                                <button class="btn btn-danger btn-sm" name="submit_sm" type="submit">Envoi</button>
                            </div>
                        </div>
                        </form>';
            else
                $Content .= '<div class="row d-print-none d-md-none d-lg-none d-xl-none">
                            <div class="col-9 align-self-center justify-content-xl-center">
                            <p class="text-center" style="font-size: 12px;">
                                <a href="login.php">Connectez vous</a> pour interagir</p>
                            </div>
                        </div>';
            $Content .= '</div></div></div></div><div class="col-3 col-md-6 col-lg-6 col-xl-3 d-none d-print-inline d-sm-none d-md-inline d-lg-inline d-xl-inline"><div style="height: 687px;"><div style="height: 687px;">
                    <section style="color: rgb(113,113,113);background-color: #ffffff;padding: 30px;height: 100%;">
                        <div class="container scstyle-1 sc-overflow" style="margin-bottom: 30px;max-height: 620px;">
                            <article style="background-color: rgba(244,244,244,0);">';
            $getnewComment_query = $this->db->query("SELECT id, author, date, comment FROM wibuu_comments WHERE active = 1 AND post = $postid ORDER BY date ASC");
            while ($scrollComment = $getnewComment_query->fetch(PDO::FETCH_ASSOC)) {
                $getCommentateur = $this->getAuthorInfo($scrollComment['author']);
                $Content .= '<div class="row">
                                    <div class="col">
                                        <div style="margin-bottom: -16px;">
                                            <p style="margin-bottom: 0px;">
                                                <a href="profile.php?id='.$getCommentateur['id'].'" class="keep_proper_link"><strong>'.$getCommentateur['username'].'</strong></a>&nbsp;
                                                '.$scrollComment['comment'];
                if ($getCommentateur['id'] == $this->userid OR $this->admin == 1)
                    $Content .= '<a href="post.php?id='.$postid.'&delete_comment='.$scrollComment['id'].'"<i class="fa fa-remove" style="margin-left: 6px;color: rgb(255,71,71);"></i></a>';
                $Content .= '</p><p><em>IL Y A '.$this->humanTiming($scrollComment['date']).'</em><br></p></div></div></div>';
            }
            $Content .= '</article></div>';
            if ($this->userid != -1)
                $Content .= '<form method="post" action=""><div class="row"><div class="col-9 align-self-center justify-content-xl-center"><input class="form-control-lg d-flex justify-content-center align-items-center" name="comment" type="text" style="width: 100%;font-size: 12px;" placeholder="Votre commentaire..."></div><div class="col-3 d-inline-flex align-self-center justify-content-xl-center align-items-xl-center"><button class="btn btn-danger btn-sm" name="submit" type="submit">Envoi</button></div></div></form>';
            else
                $Content .= '<div class="col"><p class="text-center" style="font-size: 12px;"><a href="login.php">Connectez vous</a> pour interagir</p></div>';

        return $Content.'</section></div></div></div></div>';

    }

    public function deleteComment($comment, $post) {

        if ($this->userid == -1)
            return ;

        if ($this->admin == 1)
            $query = $this->db->prepare("SELECT id FROM wibuu_comments WHERE post = :post AND id = :id");
        else
            $query = $this->db->prepare("SELECT id FROM wibuu_comments WHERE post = :post AND id = :id AND author = $this->userid");
        $query->execute(array(
                        'post'  => $post,
                        'id'    => $comment));
        if ($query->rowCount() == 0)
            return ;
        $disableComment = $this->db->prepare("UPDATE wibuu_comments SET active = 0 WHERE id = ?");
        $disableComment->execute(array($comment));

        if ($disableComment->rowCount() > 0) {
            $removeComment = $this->db->query("UPDATE wibuu_posts SET nb_comments = nb_comments - 1 WHERE id = $post");
            $removeNotif   = $this->db->query("UPDATE wibuu_notifications SET active = 0 WHERE id_link = $post AND object = $comment");
        }

    }

    public function deletePost($postid) {
        if ($this->userid == -1)
            return ;
        $query = $this->db->prepare("SELECT author FROM wibuu_posts WHERE id = ?");
        $query->execute(array($postid));
        if ($query->rowCount() == 0)
            return ;
        $author = $query->fetch()[0];
        if ($this->userid == $author OR $this->admin == 1) {
            $query->closeCursor();
            $query = $this->db->prepare("UPDATE wibuu_posts SET active = 0 WHERE id = ?");
            $query->execute(array($postid));
            $deleteComment = $this->db->query("UPDATE wibuu_comments SET active = 0 WHERE post = $postid");
            $deleteNotif   = $this->db->query("UPDATE wibuu_notifications SET active = 0 WHERE id_link = $postid");
        }
    }

    public function like($postid) {

        if ($this->userid == -1)
            return ;

        $id = htmlentities($postid);
        $query = $this->db->query("SELECT id, author FROM wibuu_posts WHERE id = $id");

        $data = $query->fetch()[1];

        $getPrivate = $this->db->query("SELECT private, id FROM wibuu_users WHERE id = $data");
        $author = $getPrivate->fetch(PDO::FETCH_ASSOC);
        $private = $author['private'];
        $authorID = $author['id'];

        if ($query->rowCount() == 1) {
            if ($this->showPost($private, $this->userid)) {
                $checkLiked = $this->db->query("SELECT id FROM wibuu_likes WHERE user = $this->userid AND likes = $id");
                if ($checkLiked->rowCount() == 0) {
                    $like = $this->db->query("INSERT INTO wibuu_likes (user, likes) VALUES ($this->userid, $id)");
                    $user = $this->db->query("UPDATE wibuu_posts SET nb_likes = nb_likes + 1 WHERE id = $id");
                    if ($this->userid != $authorID) {
                        $notif = $this->db->query("INSERT INTO wibuu_notifications (author, dest, type, id_link) VALUES ($this->userid, $authorID, 'like', $postid)");
                    }
                }
            } 
        }
    }

    public function unLike($postid) {

        if ($this->userid == -1)
            return ;

        $id = htmlentities($postid);

        $query = $this->db->query("SELECT id, author FROM wibuu_posts WHERE id = $id");
        $data = $query->fetch()[1];

        $getPrivate = $this->db->query("SELECT private, id FROM wibuu_users WHERE id = $data");
        $author = $getPrivate->fetch(PDO::FETCH_ASSOC);
        $private = $author['private'];
        $authorID = $author['id'];

        $query = $this->db->query("SELECT id, author FROM wibuu_posts WHERE id = $id");
        if ($query->rowCount() == 1) {
            if ($this->showPost($private, $this->userid)) {
                $checkLiked = $this->db->query("SELECT id FROM wibuu_likes WHERE user = $this->userid AND likes = $id");
                if ($checkLiked->rowCount() == 1) {
                    $like = $this->db->query("DELETE FROM wibuu_likes WHERE user = $this->userid AND likes = $id");
                    $user = $this->db->query("UPDATE wibuu_posts SET nb_likes = nb_likes - 1 WHERE id = $id");
                    if ($this->userid != $authorID) {
                        $notif = $this->db->query("DELETE FROM wibuu_notifications WHERE author = $this->userid AND dest = $authorID AND type = 'like' AND id_link = $postid");
                    }
                }
            }
        }
    }

    private function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){
        $cut = imagecreatetruecolor($src_w, $src_h);

        imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
       
        imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);
       
        imagecopymerge($dst_im, $cut, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $pct);
    }


    private function rangeChoose($label, $name, $min, $max, $style = null, $value = 0) {
        return '<div class="row justify-content-center" style="'.$style.'"><div class="col-auto"><label class="col-form-label">'.$label.'</label></div><div class="col-auto"><input class="custom-range" type="range" name="'.$name.'" step="5" onclick="moveFilter()" value="'.$value.'" min="'.$min.'" max="'.$max.'"></div></div>';
    }

    private function allFilters() {
        $Content = null;
        $filters = $this->db->query("SELECT id FROM wibuu_filters");
        $nbFilters = $filters->rowCount();


        $count = 1;
        while ($count <= $nbFilters) {
            $i = 1;
            $Content .= '<div class="row">';
            while ($i <= 4 AND $count <= $nbFilters) {
                $Content .= '<div class="col-auto"><img name="filterList" class="" id="filter_'.$count.'" src="assets/img/filters/filter_'.$count.'.png" style="width: 130px;filter: grayscale(100%);opacity: 0.30;" onclick="changeFilter('.$count.')"></div>';
                $count++;
                $i++;
            }
            $Content .= '</div>';
        }
        return $Content;
    }

    private function lastPostSubmit() {
        $query = $this->db->query("SELECT img, id, private, keylock FROM wibuu_posts WHERE author = $this->userid AND active = 1 ORDER BY date DESC LIMIT 4 ");
        if ($query->rowCount() == 0)
            return;
        $Content = '<div class="row text-center" style="margin-bottom: 26px;">';
        while ($data = $query->fetch(PDO::FETCH_ASSOC)) {
                $url = null;
                if ($data['private'] == 1)
                    $url = '&key='.$data['keylock'];
                $Content .= '<div class="col"><a href="post.php?id='.$data['id'].$url.'" target="_blank"><img src="assets/img/posts/'.$data['img'].'" style="width: 111px;filter: brightness(40%) grayscale(100%);"></a></div>';
        }
        $Content .= '</div>';
        return $Content;
    }

    public function capturePhoto($type) {

        $Content = '<div class="col" style="background-color: #f2f2f2;" >
        <form method="post" action="" enctype="multipart/form-data"><div class="row justify-content-center" id="filter_bloc">
        <div class="col-auto">
        <div class="d-block" style="background-color: #d33434;margin: 40px;margin-right: 0px;overflow: hidden;position: relative;" id="camera">
        <img class="img-fluid" src="" style="margin-right:0px;height: width: 455px;position: absolute;" id="filter">
        <img id ="img" src="" style="display:none;" name="picture">
        <video autoplay style=""></video></div></div></div><div class="row justify-content-center" style="margin-bottom: 21px;">
                <div class="col-auto">';
        if ($type === 'webcam')
            $Content .= '<button class="btn btn-danger btn-lg" type="button" id="screenshot-button" style="height: 60px;width: 104px;"><i class="fa fa-camera"></i></button>';
        else if ($type === 'upload')
            $Content .= '<input type="file" name="imgUpload" id="screenshotButton" accept="image/*" onchange="loadFile(event)" style="height: 60px;">';
        $Content .= '</div></div></div><div class="col" style="background-color: #f2f2f2;margin-left: -33px;">';
        $Content .= $this->rangeChoose("Position X", "x_pos", -340, 440, "margin-top: 15px;");
        $Content .= $this->rangeChoose("Position Y", "y_pos", -345, 520);
        $Content .= $this->rangeChoose("Taille", "size", 100, 800, "margin-top: 15px;", 455);
        $Content .= '<div class="row"><div class="col-12"><div><div><section style="color: rgb(113,113,113);background-color: #ffffff;padding: 30px;margin-bottom: 20px;"><div class="container scstyle-1 sc-overflow"><article>';
        $Content .= $this->allFilters();
        $Content .= '</article></div></section></div></div></div></div><div class="row"><div class="col"><div class="form-group"><label><strong>Titre</strong></label><input class="form-control" type="text" name="title" required></div><div class="form-group"><label><strong>Description</strong></label><textarea class="form-control" name="description" required></textarea></div><div class="form-group"><div class="form-check"><input class="form-check-input" type="checkbox" id="formCheck-1" name="private"><label class="form-check-label" for="formCheck-1">Privé</label></div></div><input type="hidden" name="image" class="image-tag"><input type="hidden" name="height" value="286"><input type="hidden" name="filterPost" value="1"><button class="btn btn-danger btn-block" type="submit" id="submit" style="display:none">Envoyer</button></div></div>';

        $Content .= $this->alert;
        $Content .= $this->lastPostSubmit();

        $Content .= '</div></form></div></div>';

        return $Content;

    }

    private function applyFilter($post, $filename) {

        $private = 0;
        if (isset($post['private']))
            $private = 1;

        $title = htmlentities($post['title']);
        $description = htmlentities($post['description']);

        $folderPath = "assets/img/posts/";

        $filter = imagecreatefrompng("assets/img/filters/filter_".$post['filterPost'].".png");

        $width = $post['size'];
        $height = $post['height'];

        $trueSize = imagecreatetruecolor($width, $height);
        imagealphablending($trueSize , false);
        imagesavealpha($trueSize , true);

        imagecopyresampled($trueSize, $filter, 0, 0, 0, 0, $width, $height, 455, 286);
        imagealphablending($trueSize , false);
        imagesavealpha($trueSize , true);
        imagepng($trueSize, $folderPath . $filename .'_filter.png', 9);

        $resizedFilter = imagecreatefrompng('assets/img/posts/'.$filename .'_filter.png');
        $webcam = imagecreatefromjpeg($folderPath . $filename .'.jpeg');
        $this->imagecopymerge_alpha($webcam, $resizedFilter, $post['x_pos'], $post['y_pos'], 0, 0, $post['size'], $post['height'], 100);

        imagejpeg($webcam, $folderPath . $filename .'.jpeg', 100);

        unlink($folderPath . $filename .'_filter.png');

        $time = date('Y-m-d h:i:s', time());
        $keylock = uniqid();

        $query = $this->db->prepare("INSERT INTO wibuu_posts (title, description, img, date, author, filter, private, keylock) VALUES (:title, :description, :img, :moment, $this->userid, :filter, :private, :keylock)");
        $query->execute(array(
            "title" => $title,
            "description" => $description,
            "img" => $filename .'.jpeg',
            "moment" => $time,
            "filter" => $post['filterPost'],
            "private" => $private,
            "keylock" => $keylock));

        $getThisID = $this->db->query('SELECT id FROM wibuu_posts WHERE img = "'.$filename.'.jpeg"');
        $correctId = $getThisID->fetch()[0];

        $url = null;
        if ($private == 1)
            $url = '&key='.$keylock;

        header('Location: post.php?id='.$correctId.$url);

    }

    public function submitPicture($post) {

        if (!is_numeric($post['x_pos']) OR !is_numeric($post['y_pos']) OR !is_numeric($post['size']) OR !is_numeric($post['height']) OR !is_numeric($post['filterPost']))
            return ;

        if (strlen($post['title']) > 20 OR strlen($post['description']) > 255)
            return ;

        $checkFilter = $this->db->prepare("SELECT id FROM wibuu_filters WHERE id = ?");
        $checkFilter->execute(array($post['filterPost']));

        if ($checkFilter->rowCount() == 0)
            return ;

        $img = $post['image'];
        $folderPath = "assets/img/posts/";
        $image_parts = explode(";base64,", $img);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);

        $ext = explode('/', $image_parts[0])[1];

        $uniqid = uniqid();

        $fileName = $uniqid . $ext;
        $file = $folderPath . $fileName;
        file_put_contents($file, $image_base64);

        $webcamPhotoWebP = imagecreatefrompng($file);

        imagejpeg($webcamPhotoWebP, $folderPath . $uniqid .'.jpeg', 100);
        $destroy = imagedestroy($webcamPhotoWebP);
        unlink($file);

        $this->applyFilter($post, $uniqid);

    }

    public function submitFile($post, $file) {
        if ($file['size'] == 0 OR $file['error'] > 0)
            return ;

        $uniqid = uniqid();
        $newname = $uniqid.'_tmp';
        $allowedExtensions = array("jpg", "JPEG", "JPG", "jpeg");
        if ($this->uploadFile($file, $newname, 'assets/img/posts', $allowedExtensions, 700000, 'jpeg') == 0)
            return ;
        $this->applyFilter($post, $newname);


    }


}

?>