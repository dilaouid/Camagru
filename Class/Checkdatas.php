<?php

namespace App;
use \PDO;

class Checkdatas {

    private $db;
    public $id = -1;
    public $private;
    private $userid;
    private $admin = 0;

    public function __construct($db) {

        $this->db =    $db;
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

    public function check_qs_exists($id, $table) {

    	if (!is_numeric($id))
    		return 0;

    	if ($table == 'wibuu_posts')
    		return 0;

    	$data = $this->db->prepare("SELECT id FROM $table WHERE id = ?");
    	$data->execute(array($id));
    	if ($data->rowCount() > 0)
    		$this->id = $id;
    	$data->closeCursor();
    	return $data->rowCount();

    }

    public function privateProfile() {

    	$data = $this->db->prepare("SELECT private FROM wibuu_users WHERE id = ?");
    	$data->execute(array($this->id));
    	if ($data->fetch()[0] == 1 AND $this->id != $this->userid AND $this->admin == 0 AND $this->isfollow($this->userid, $this->id, 1) == 0) {
    		$this->private = 1;
    		$data->closeCursor();
    		return '<p class="text-center text-black-50" id="private"><br><i class="fa fa-lock" style="font-size: 60px;"></i><br><em>Ce profil est privé.</em><br></p>';
    	}
    	else
    		$this->private = 0;
    	$data->closeCursor();

    }

    public function isfollow($user, $follows, $await) {

        if ($this->userid == -1)
            return false;

    	$data = $this->db->prepare("SELECT id FROM wibuu_follows WHERE user = $user AND follows = ? AND accepted = $await");
    	$data->execute(array($follows));
    	return $data->rowCount();

    }

    private function getSocialProfile($facebook, $instagram, $twitter) {
    	$this->privateProfile();
    	if ($this->private == 1)
    		return ;
    	if (!empty($facebook) OR !empty($instagram) OR !empty($twitter)) {
    		$Content = '<br />';
    	}
    	else
    		return ;
    	if (!empty($facebook))
    		$Content .= "<a href=\"https://www.facebook.com/$facebook\" target=\"_blank\"><i class=\"fa fa-facebook-square\"></i></a>&nbsp";
    	if (!empty($instagram))
    		$Content .= "<a href=\"https://www.instagram.com/$instagram\" target=\"_blank\"><i class=\"fa fa-instagram\"></i></a>&nbsp";
    	if (!empty($twitter))
    		$Content .= "<a href=\"https://www.twitter.com/$twitter\" target=\"_blank\"><i class=\"fa fa-twitter-square\"></i></a>&nbsp";
    	return $Content;
    }

    private function getFollowing($id) {
        $data = $this->db->query("SELECT COUNT(user) FROM wibuu_follows WHERE user = $id AND accepted = 1");
        return ($data->fetch()[0]);
    }

    private function getFollowers($id) {
        $data = $this->db->query("SELECT COUNT(follows) FROM wibuu_follows WHERE follows = $id AND accepted = 1");
        return ($data->fetch()[0]);
    }

    public function getInfos() {

    	$data = $this->db->prepare("SELECT 		wibuu_users.id, wibuu_users.description, avatar, username, COUNT(author) AS posts, facebook, instagram, twitter
    								FROM 		wibuu_users

    								LEFT JOIN 	wibuu_posts 	ON author = :userid AND active = 1

    								WHERE 		wibuu_users.id = :userid");
    	$data->execute(array(
    		'userid' => $this->id));
    	$rep = $data->fetch(PDO::FETCH_ASSOC);
        $followers = $this->getFollowers($rep['id']);
        $following = $this->getFollowing($rep['id']);
    	$Content = '<div class="col text-right avatar_profilepage_bloc"><img class="rounded-circle avatar_profilepage" src="assets/img/profil_pictures/'. $rep['avatar'] .'" style="height:175px;width:175px"></div><div class="col-7 align-self-center info_profile_page">';

    	$Content .= '<div class="row">
                    <div class="col-auto">
                        <h2 class="profilpage_username">'. $rep['username'] .'</h2>
                    </div>
					<div class="col-auto col-sm-auto col-md-auto col-lg-auto col-xl-auto">';

		if ($this->id == $this->userid)
			$Content .= '<button class="btn btn-primary" type="button" onclick="window.location.href = \'edit_profile.php\';" style="margin-right: 10px;">Modifier le profil</button>';

		elseif ($this->isfollow($this->userid, $this->id, 1) AND $this->userid != -1)
			$Content .= '<button class="btn btn-outline-primary" type="button" style="margin-right: 10px;" onclick="unFollow()" id="buttonFollow">Ne plus suivre</button>';

        elseif ($this->isfollow($this->userid, $this->id, 0) AND $this->userid != -1)
            $Content .= '<button class="btn btn-outline-primary" type="button" style="margin-right: 10px;" onclick="stopFollow('.$this->admin.')" id="buttonFollow">Annuler la demande</button>';

        elseif (!$this->isfollow($this->userid, $this->id, 1) AND $this->userid != -1)
            $Content .= '<button class="btn btn-primary" type="button" style="margin-right: 10px;" onclick="askFollow('.$this->admin.')" id="buttonFollow">Suivre</button>';

        if ($this->isfollow($this->id, $this->userid, 0) AND $this->userid != -1)
            $Content .= '<button class="btn btn-info" type="button" style="margin-right: 10px;" onclick="acceptFollow()" id="acceptFollow">Accepter la demande d\'abonnement</button>';

    	$Content .= '</div></div><div class="row">
                    <div class="col-auto col-sm-auto text-center">
                        <p><strong>'. $rep['posts'] .'</strong> publications</p>
                    </div>
                    <div class="col-auto text-center">
                        <p><strong id="followers">'. $followers .'</strong> abonnés</p>
                    </div>
                    <div class="col-auto text-center">
                        <p><strong>'. $following .'</strong> abonnements</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-5 col-sm-12 col-xl-5 text-center">
                        <p class="text-left">'. $rep['description'] . $this->getSocialProfile($rep['facebook'], $rep['instagram'], $rep['twitter']).'

                        </p>
                    </div>
                </div>';

    	$data->closeCursor();
    	return $Content.'</div>';

    }

    private function interacted($type, $postid) {

    	if ($type == 'like') {
    		$interact = $this->db->prepare("SELECT likes FROM wibuu_likes WHERE user = ? AND likes = $postid");
    		$interact->execute(array($this->userid));
    		if ($interact->rowCount() == 0)
    			return '-o';
    	}

    	if ($type == 'comment') {
    		$interact = $this->db->prepare("SELECT author FROM wibuu_comments WHERE author = ? AND post = $postid AND active = 1");
    		$interact->execute(array($this->userid));
    		if ($interact->rowCount() == 0)
    			return '-o';
    	}



    }

    public function allPosts($userid) {

    	if ($this->private)
    		return ;

    	$Content = '<div class="row d-lg-flex align-items-center justify-content-lg-center align-items-lg-center align-items-xl-center photos bloc_profile_posts">';

    	$data = $this->db->prepare("SELECT id, img, private, nb_likes, nb_comments, keylock FROM wibuu_posts WHERE author = ? AND active = 1 ORDER BY date DESC");
    	$data->execute(array($userid));

    	while ($rep = $data->fetch(PDO::FETCH_ASSOC)) {
    		if ($rep['private'] == 0 OR ($rep['private'] == 1 AND ($this->userid == $userid OR $this->admin == 1))) {
                if ($rep['private'] == 1)
                    $url = '&key='.$rep['keylock'];
                else
                    $url = null;
    			$Content .= '<div class="col-sm-12 col-md-6 col-lg-6 col-xl-auto text-center item">
                <a href="post.php?id='. $rep['id'] . $url .'"><img class="img-fluid img_postprofile" src="assets/img/posts/'. $rep['img'] .'"></a>
                <div class="row justify-content-center infos_post_profile">
                    <div class="col-auto"><i class="fa fa-heart'.$this->interacted('like', $rep['id']).'"></i>
                        <p>'. $rep['nb_likes'] .'</p>
                    </div>
                    <div class="col-auto"><i class="fa fa-comment'.$this->interacted('comment', $rep['id']).'"></i>
                        <p>'. $rep['nb_comments'] .'</p>
                    </div>
                </div>
            </div>';
            }
    	}

    	if ($data->rowCount() == 0) {
    		$Content .= '<p class="text-center text-black-50"><br><em>Aucun contenu pour le moment.</em><br></p>';
    	}

    	return $Content.'</div></div>';

    }

    public function getNotifList() {

        $query = $this->db->query("SELECT * FROM wibuu_notifications WHERE dest = $this->userid AND active = 1");

        $Content = '<div class="row no-gutters justify-content-center justify-content-md-start justify-content-lg-start justify-content-xl-center"><div class="col-lg-10 col-xl-9 offset-lg-1 offset-xl-0" style="background-color: #f2f2f2;">';
        if ($query->rowCount() == 0)
            $Content .= '<p class="text-center" style="margin-top: 10px;color: rgb(129,133,137);"><em>Aucune notification...</em></p>';
        else {
            while ($data = $query->fetch(PDO::FETCH_ASSOC)) {

                $notifUserID = $data['author'];

                $selectUser = $this->db->query("SELECT username, id, avatar FROM wibuu_users WHERE id = $notifUserID");
                $author = $selectUser->fetch(PDO::FETCH_ASSOC);

                $Content .= '<div id="line_'.$data['id'].'">
                <div class="card" style="margin-bottom: 17px;">
                    <div class="card-body">
                        <div class="row justify-content-start align-items-center" style="margin-bottom: 9px;">
                            <div class="col-auto offset-0"><a href="profile.php?id='.$author['id'].'" style="background-image: url("assets/img/profil_pictures/'.$author['avatar'].');"><img class="border rounded-circle" src="assets/img/profil_pictures/'.$author['avatar'].'" style="width: 61px;"></a></div>
                            <div class="col">';

                $idLink = $data['id_link'];

                if ($data['type'] == 'comment' OR $data['type'] == 'like') {
                    if ($data['type'] == 'comment')
                        $txt = 'a commenté';
                    else
                        $txt = 'a aimé';
                    $getArticle_q = $this->db->query("SELECT id, title FROM wibuu_posts WHERE id = $idLink");
                    $getArticle = $getArticle_q->fetch(PDO::FETCH_ASSOC);
                    $action = '<a href="profile.php?id='.$author['id'].'" class="keep_proper_link"><strong>'.$author['username'].'</strong></a> '.$txt.' <a href="post.php?id='.$getArticle['id'].'" class="keep_proper_link"><strong>'.$getArticle['title'].'</strong></a>';
                    $getArticle_q->closeCursor();
                }

                elseif ($data['type'] == 'askfollow' OR $data['type'] == 'follow') {
                    if ($data['type'] == 'askfollow')
                        $txt = 'demande à vous suivre';
                    else
                        $txt = 'vous suit !';
                    $action = '<a href="profile.php?id='.$author['id'].'" class="keep_proper_link"><strong>'.$author['username'].'</strong></a> '.$txt;
                }

                $Content .= '<h6 class="text-left text-muted mb-2" style="font-size: 14px;">'.$action.'</h6></div><div class="col-auto">
                <a onclick="deleteNotif('.$data['id'].')"><i class="fa fa-times" style="color: rgb(197,14,14);"></i></a>
                </div></div></div></div></div>';
            }
        }

        return $Content.'</div></div></div>';

    }


}

?>