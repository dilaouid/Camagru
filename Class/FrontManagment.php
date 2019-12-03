<?php

namespace App;
use \PDO;

class FrontManagment {

	private $db;
	private $global;
    private $userid = -1;
	static $dbs;

    public function __construct($db, $global) {
    	self::$dbs 		= $db;
        $this->db 		= $db;
        $this->global 	= $global;
        if (isset($_SESSION['id']))
            $this->userid = $_SESSION['id'];
    }

    public function partners() {
    	$nbPartners_query = $this->db->query('SELECT * FROM wibuu_partner');
    	if ($nbPartners_query->rowCount() > 0) {
    		$Content = '<div class="brands"><div class="text-center brand_bloc"><h1 class="display-4 text-uppercase text-white-50 title_brands">Nos partenaires</h1>';
    		while ($data = $nbPartners_query->fetch()) {
    			$Content .= '<a href="'. $data['url'] .'" target="_blank" style="display: inline;"><img src="assets/img/brands/brand_'. $data['id'] .'.png" class="brand"></a>';
    		}
    		return $Content .'</div></div>';
    	}
    }

    private function lastpublication() {
    	$lastpublication = $this->db->query('SELECT id, author FROM wibuu_posts WHERE private = 0 AND active = 1 ORDER BY date DESC LIMIT 1 OFFSET 0');
        $return = $lastpublication->fetch(PDO::FETCH_ASSOC);
        $user = $return['author'];
        $id = $return['id'];
        $i = 0;
        while ($this->isPrivate($user) AND $this->isFollowing($this->userid, $user) == 0) {
            $i++;
            $lastpublication->closeCursor();
            $lastpublication = $this->db->query("SELECT id, author FROM wibuu_posts WHERE private = 0 AND active = 1 ORDER BY date DESC LIMIT 1 OFFSET $i");
            $return = $lastpublication->fetch(PDO::FETCH_ASSOC);
            $id = $return['id'];
            $user = $return['author'];
            if ($lastpublication->rowCount() == 0 OR $i > 100)
                return ;
        }
    	return '<li><a href="/post.php?id='. $id .'">dernière publication</a></li>';

    }

    private function isFollowing($user, $follows) {
        if ($this->userid == $user)
            return 1;
        if ($this->userid == -1)
            return 0;
        $query = $this->db->query("SELECT id FROM wibuu_follows WHERE user = $user AND follows = $follows AND accepted = 1");
        return $query->rowCount();
    }

    private function isPrivate($user) {
        if ($this->userid == $user)
            return 0;
        $query = $this->db->query("SELECT id FROM wibuu_users WHERE id = $user AND private = 1");
        return $query->rowCount();
    }

    public function isAdmin($userid) {
        $query = $this->db->query("SELECT id FROM wibuu_users WHERE id = $userid AND admin = 1");
        return $query->rowCount();
    }

    private function rdmpublication() {
    	$rdmpublication = $this->db->query('SELECT id, author FROM wibuu_posts WHERE private = 0 AND active = 1 ORDER BY RAND() LIMIT 1');
        $return = $rdmpublication->fetch(PDO::FETCH_ASSOC);
        $user = $return['author'];
        $id = $return['id'];
        $i = 0;
    	while ($this->isPrivate($user) AND $this->isFollowing($this->userid, $user) == 1) {
            $rdmpublication->closeCursor();
            $rdmpublication = $this->db->query('SELECT id, author FROM wibuu_posts WHERE private = 0 AND active = 1 ORDER BY RAND() LIMIT 1');
            $return = $rdmpublication->fetch(PDO::FETCH_ASSOC);
            $user = $return['author'];
            $id = $return['id'];
            $i++;
            if ($rdmpublication->rowCount() == 0 OR $i > 100)
                return ;
        }
    	return '<li><a href="/post.php?id='. $id .'">publication aléatoire</a></li>';
    }

    private function bestpublication() {
    	$bestpublication = $this->db->query('SELECT id, author FROM wibuu_posts WHERE private = 0 AND active = 1 ORDER BY (nb_likes + nb_comments) DESC LIMIT 1 OFFSET 0');
    	$return = $bestpublication->fetch(PDO::FETCH_ASSOC);
        $user = $return['author'];
        $id = $return['id'];
        $i = 1;
        while ($this->isPrivate($user) AND $this->isFollowing($this->userid, $user) == 1) {
            $bestpublication->closeCursor();
            $bestpublication = $this->db->query("SELECT id, author FROM wibuu_posts WHERE private = 0 AND active = 1 ORDER BY (nb_likes + nb_comments) DESC LIMIT 1 OFFSET $i");
            $return = $bestpublication->fetch(PDO::FETCH_ASSOC);
            $user = $return['author'];
            $id = $return['id'];
            $i++;
            if ($bestpublication->rowCount() == 0 OR $i > 100)
                return ;
        }
    	return '<li><a href="/post.php?id='. $id .'">publication la plus populaire</a></li>';
    }

    private static function socialnetwork_footer($global) {
    	if (is_null($global['facebook']) AND is_null($global['twitter']) AND is_null($global['instagram']))
    		return ;
    	$Content = '<div class="col item social">';
    	if (!empty($global['facebook']))
    		$Content .= '<a href="https://www.facebook.com/'. $global['facebook'] .'"><i class="icon ion-social-facebook"></i></a>';
    	if (!empty($global['twitter']))
    		$Content .= '<a href="https://www.twitter.com/'. $global['facebook'] .'"><i class="icon ion-social-twitter"></i></a>';
    	if (!empty($global['instagram']))
    		$Content .= '<a href="https://www.instagram.com/'. $global['instagram'] .'"><i class="icon ion-social-instagram"></i></a>';
    	$Content .= '</div>';
    	return $Content;
    }

    public function footer() {
    	$Content = '<div class="text-uppercase footer-dark footer_wibuu"><footer><div class="container footer_container"><div class="row">';
    	$nbArticles = $this->db->query('SELECT id FROM wibuu_posts WHERE private = 0 AND active = 1');
    	if ($nbArticles->rowCount() > 0) {
    		$Content .= '<div class="col-sm-6 col-md-3 item"><h3>LIENS</h3><ul>';
    		$Content .= $this->lastpublication();
    		$Content .= $this->rdmpublication();
    		$Content .= $this->bestpublication();
    		$Content .= '</ul></div>';
    	}
    	$nbArticles->closeCursor();

    	$Content .= '<div class="col-sm-6 col-md-3 item">
    	<h3>A propos de nous</h3>
    		<ul>
                <li><a href="/aboutus.pdf" target="_blank">qui sommes nous</a></li>
                <li><a href="/cgu.pdf" target="_blank">cgu</a></li>
                <li><a href="/legal.pdf" target="_blank">mentions légales</a></li>
            </ul>
         </div>

        <div class="col-md-6 item text">
            <h3>'. $this->global['sitename'] .'</h3>
            <p>'. $this->global['short_about_us'] .'</p>
        </div>';

        $Content .= self::socialnetwork_footer($this->global).'</div>';
        if ($this->isAdmin($this->userid))
            $Content .= '<div class="col text-center"><a href="/admin" class="text-white">administration</a></div>';
        $Content .= '<p class="copyright">'.$this->global['sitename'] .' © 2019</p></div></footer></div>';
    	return $Content;
    }

    private static function getactive($section, $page) {
    	if ($section == $page)
    		return 'active';
    	return null;
    }

    private static function getnotifications($userid) {
    	$nbNotifications = self::$dbs->query('SELECT id AS nb FROM wibuu_notifications WHERE active = 1 AND dest = '.$userid);
    	if ($nbNotifications->rowCount() > 0) {
	    	return '<strong>('.$nbNotifications->rowCount().')</strong>';
    	}
    }

    public function navbar($userid, $section) {
    	$Content = '
        <nav class="navbar navbar-dark navbar-expand-md sticky-top border-dark shadow navigation-clean-search" class="navbar">
        <div class="container-fluid">

        <img src="/assets/img/ui/logo.png" class="logo_navbar">
        <a class="navbar-brand text-light" href="/index.php">'.$this->global['sitename'].'</a>

        <ul class="nav navbar-nav">';

        $page = 'index';
        $Content .= '<li class="nav-item" role="presentation"><a class="nav-link '.self::getactive($section, $page).'" href="/index.php">Accueil<i class="fa fa-home icon_navbar"></i></a></li>';
        $page = 'gallery';
        $Content .= '<li class="nav-item" role="presentation"><a class="nav-link '.self::getactive($section, $page).'" href="/gallery.php">Galerie<i class="fa fa-picture-o icon_navbar"></i></a></li>';
        if ($userid != -1) {
        	$page = 'upload';
        	$Content .= '<li class="nav-item" role="presentation"><a class="nav-link '.self::getactive($section, $page).'" href="/capture.php?type=choice">Envoyer une photo<i class="fa fa-upload icon_navbar"></i></a></li>';

        	$page = 'notifications';
        	$Content .= '<li class="nav-item" role="presentation"><a class="nav-link '.self::getactive($section, $page).'" href="/notifications.php">notifications '. self::getnotifications($userid) .'<i class="fa fa-bell icon_navbar"></i></a></li>';
        }
        $Content .= '</ul><form class="form-inline mr-auto" action="search.php">
                	</form>';
        if ($userid == -1)
            $Content .= '<a class="text-light" href="/login.php">Connexion</a>
                <a class="btn btn-primary action-button" role="button" href="/register.php" style="margin-left: 12px;background-color: rgb(170,58,58);">Inscription</a>';
        else
            $Content .= '<div class="dropdown">
                    <button onclick="myFunction()" class="dropdown-toggle" style="background-color:transparent;color:white;border: none;
  cursor: pointer;">MON COMPTE</button>
                    <div id="dropdown" class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" role="presentation" href="/edit_profile.php">MODIFIER MON PROFIL</a>
                        <a class="dropdown-item" role="presentation" href="/profile.php?id='.$userid.'">VOIR MON PROFIL</a>
                        <a class="dropdown-item" role="presentation" href="/index.php?logout">Déconnexion</a>
                    </div>
                </div>';
        return $Content.'</div></nav>';
    }


}