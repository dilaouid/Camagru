<?php


namespace App;
use \PDO;


require_once($_SERVER['DOCUMENT_ROOT'].'/Class/Users.php');

class AdminManagment extends Users {

	private $db;
	private $global;
    private $userid = -1;
    private $pagename;
    public  $admin;
    public  $alert = null;

    public function __construct($db, $global) {
        parent::__construct($db, null, $global);
        $this->db 		= $db;
        $this->global 	= $global;
        if (isset($_SESSION['id']))
            $this->userid = $_SESSION['id'];
        $this->admin = $this->isAdmin($this->userid);
    }

    private function navElem($url, $page, $icon) {
        $active = null;
        if ($page === $this->pagename)
            $active = 'active';
        return '<li class="nav-item" role="presentation"><a class="nav-link '.$active.'" href="'.$url.'">'.$page.' <i class="fa fa-'.$icon.'" style="margin-left: 4px;"></i></a></li>';
    }

    public function navbar($section) {
        $this->pagename = $section;

        $Content = '<nav class="navbar navbar-dark navbar-expand-md fixed-bottom border-dark text-uppercase shadow navigation-clean-search" style="width: 100%;background-color: rgb(145,15,23);height: 47px;"><div class="container-fluid"><div class="navbar-collapse text-white" id="navcol-1"><ul class="nav navbar-nav">';

        $Content .= $this->navElem('.', 'Panneau de contrôle', 'gears');
        $Content .= $this->navElem('features.php?option=create', 'Features', 'trophy');
        $Content .= $this->navElem('filter.php', 'Nouveau filtre', 'camera');

        $Content .= '</ul></div></div></nav>';

        return $Content;
    }

    private function formGroup($label, $name, $value, $placeholder = null, $textarea = 0) {
        $Content = '<div class="form-group"><label>'.$label.'</label>';
        if ($name == 'short_about_us') {
            $value = str_replace('<br />', "", $value);
        }
        if ($textarea == 1)
            $Content .= '<textarea class="form-control" name="'.$name.'" rows=4>'.$value.'</textarea>';
        else
            $Content .= '<input class="form-control" type="text" name="'.$name.'" placeholder="'.$placeholder.'" value="'.$value.'">';
        return $Content.'</div>';
    }

    private function formCheck($id, $name, $label, $checked) {
        $check = null;
        if ($checked == 1)
            $check = 'checked';
        return '<div class="col"><div class="form-check"><input class="form-check-input" type="checkbox" id="'.$id.'" name="'.$name.'" '.$check.'><label class="form-check-label" for="'.$id.'">'.$label.'</label></div></div>';
    }

    public function globalParam() {
        $Content = '<form action="" method="post" enctype="multipart/form-data">';
        $Content .= $this->formGroup('Nom du site', 'sitename', $this->global['sitename']);
        $Content .= $this->formGroup('Sous-titre (dans le header de la page d\'accueil)', 'subtitle_index', $this->global['subtitle_index']);
        $Content .= $this->formGroup('Court résumé (footer, en bas à droite)', 'short_about_us', $this->global['short_about_us'], null, 1);
        $Content .= '<label>Logo</label><div class="form-row"><div class="col" style="margin-bottom: 17px;"><input type="file" name="logo"></div></div>';
        $Content .= '<div class="form-row" style="margin-bottom: 20px;">';

        $Content .= $this->formCheck('registrationOK', 'enable_registration', 'Autoriser les inscriptions', $this->global['enable_registration']);
        $Content .= $this->formCheck('siteEnMaintenance', 'maintenance', 'Site en maintenance', $this->global['maintenance']);

        $Content .= '</div>';

        $Content .= $this->formGroup('Facebook', 'facebook', $this->global['facebook'], 'https://www.facebook.com/[VALEUR A SAISIR]');
        $Content .= $this->formGroup('Twitter', 'twitter', $this->global['twitter'], 'https://www.twitter.com/[VALEUR A SAISIR]');
        $Content .= $this->formGroup('Instagram', 'instagram', $this->global['instagram'], 'https://www.instagram.com/[VALEUR A SAISIR]');
        $Content .= '<button class="btn btn-danger btn-block" type="submit" name="submit" value="0">Appliquer les modifications</button></form>';

        return $Content;
    }

    public function newHeader($file) {
        if ($this->uploadFile($file, 'banner', '../assets/img/ui/index', array("jpg", "png", "JPG", "PNG"), 700000, 'jpg') == 0)
            return ;
    }

    public function updateGlobal($post) {
        $availableKeys = array("maintenance", "enable_registration", "sitename", "subtitle_index", "short_about_us", "facebook", "twitter", "instagram");
        foreach ($post as $key => $value) {
            if (($key == 'maintenance' || $key == 'enable_registration') && $value == 'on')
                $value = 1;
            if (in_array($key, $availableKeys)) {
                if ($key == 'short_about_us')
                    $value = nl2br($value);
                $query = $this->db->prepare("UPDATE wibuu_global SET $key = ?");
                $query->execute(array($value));
            }
        }
        if (!isset($post['enable_registration']))
            $this->db->query("UPDATE wibuu_global SET enable_registration = 0");
        if (!isset($post['maintenance']))
            $this->db->query("UPDATE wibuu_global SET maintenance = 0");
        if (isset($_FILES['logo']) AND $_FILES['logo']['error'] == 0) {
            $this->uploadFile($_FILES['logo'], 'logo', '../assets/img/ui', array("jpg", "png", "JPG", "PNG"), 700000, 'png');
            copy('../assets/img/ui/logo.png', '../favicon.ico');
        }
    }

    public function newFilter($post, $file) {

        if (strlen($post['name']) < 3) {
            $this->alert = $this->alert = '<div class="col"><div class="alert alert-danger" role="alert" style="width: 100%;margin-top: 24px;"><span>Le nom du filtre est trop court !</span></div></div>';
            return ;
        }
        $query = $this->db->query("SELECT id FROM wibuu_filters");

        $newId = $query->rowCount() + 1;
        if ($this->uploadFile($file, 'filter_'.$newId, '../assets/img/filters', array('png'), 700000, 'png') == 0) {
            $this->alert = '<div class="col"><div class="alert alert-danger" role="alert" style="width: 100%;margin-top: 24px;"><span>Une erreur est survenue lors de l\'envoi du fichier.</span></div></div>';
            return ;
        }

        $name = htmlentities($post['name']);

        $insert = $this->db->prepare("INSERT INTO wibuu_filters (name) VALUES (?)");
        $insert->execute(array($name));

    }


    public function features($page, $option, $id) {

        $icon = null;
        $description = null;
        $title = null;
        $btn = 'Oui, on fait ca nous ! BAH OUI !';
        if (isset($id) AND $id != 0 AND $page == 'edit') {
            $query = $this->db->query("SELECT title, description, icon FROM wibuu_features WHERE id = $id");
            if ($query->rowCount() == 0 AND $page == 'edit')
                header('Location: features.php?option=create');
            $data = $query->fetch(PDO::FETCH_ASSOC);
            $icon = $data['icon'];
            $description = $data['description'];
            $title = $data['title'];
            $btn = 'Je change d\'avis comme de Pipimi';
        }


        $Content = '<h4 class="card-title">'.$option.'</h4><form method="post" action=""><div class="form-row justify-content-center"><div class="col-5"><div class="form-group"><label>Icone font awesome</label><input class="form-control" type="text" placeholder="Exemple : fa-video-camera" name="icon" required value="'.$icon.'"></div></div><div class="col-5"><div class="form-group"><label>Titre de votre feature</label><input class="form-control" type="text" name="title" required value="'.$title.'"></div></div></div><div class="form-row justify-content-center"><div class="col-10"><div class="form-group"><label>Description de la feature</label><textarea class="form-control form-control-lg" name="description" required="">'.$description.'</textarea></div></div><div class="row">';

        $Content .= '<div class="col" style="margin-top: 21px;"><button class="btn btn-danger btn-block" name="submit" type="submit">'.$btn.'</button></div></div>';

        $Content .= '</div></form></div></div></div>';

        if ($page == 'create') {
            $Content .= '<div class="card-group"><div class="card"><div class="card-body"><h4 class="card-title">Features existantes</h4><div class="row"><div class="col"><div class="table-responsive"><table class="table"><thead><tr><th>Titre de la feature</th><th>Actions</th></tr></thead><tbody>';
            $query = $this->db->query("SELECT id, title FROM wibuu_features");
            if ($query->rowCount() == 0)
                $Content .= '<tr>Aucune feature</tr>';
            while ($data = $query->fetch(PDO::FETCH_ASSOC)) {
                $Content .= '<tr id="feat_'.$data['id'].'"><td><strong>'.$data['title'].'</strong></td><td><a href="?option=edit&id='.$data['id'].'"><i class="fa fa-pencil" style="color: rgb(28,125,222);"></i></a><a onclick="delete('.$data['id'].')"><i class="fa fa-times" style="margin-left: 4px;color: rgb(213,33,44);"></i></a></td></tr>';
            }
            $Content .= '</tbody></table></div></div></div></div></div></div>';
        }
        return $Content;

    }

    private function checkEntriesIcon($post) {

        if (strlen($post['title']) == 0 OR strlen($post['description']) == 0 OR strlen($post['icon']) == 0) {
            $this->alert = '<div class="col"><div class="alert alert-danger" role="alert" style="width: 100%;margin-top: 24px;"><span>Tout les champs sont obligatoires</span></div></div>';
            return 0;
        }

        if (!isset($post['title']) OR !isset($post['description']) OR !isset($post['icon'])) {
            $this->alert = '<div class="col"><div class="alert alert-danger" role="alert" style="width: 100%;margin-top: 24px;"><span>Tout les champs sont obligatoires</span></div></div>';
            return 0;
        }

        if (strlen($post['title']) > 60 OR strlen($post['icon']) > 100) {
            $this->alert = '<div class="col"><div class="alert alert-danger" role="alert" style="width: 100%;margin-top: 24px;"><span>Les champs que vous avez saisis sont trop longs.</span></div></div>';
            return 0;
        }

        if (count(explode('fa', $post['icon'])) == 1) {
            $this->alert = '<div class="col"><div class="alert alert-danger" role="alert" style="width: 100%;margin-top: 24px;"><span>Le format de l\'icone est incorrect.</span></div></div>';
            return 0;
        }

        return 1;

    }

    public function newFeature($post) {
        if ($this->checkEntriesIcon($post) == 0)
            return ;
        $title = htmlentities($post['title']);
        $desc = htmlentities($post['description']);
        $icon = htmlentities($post['icon']);

        $query = $this->db->prepare("INSERT INTO wibuu_features (title, description, icon) VALUES (:title, :description, :icon)");
        $query->execute(array(
            "title" => $title,
            "description" => $desc,
            "icon" => $icon));

        $this->alert = '<div class="col"><div class="alert alert-success" role="alert" style="width: 100%;margin-top: 24px;"><span>La feature a été corectement crée !</span></div></div>';
    }

    public function editFeature($post, $id) {
        if ($this->checkEntriesIcon($post) == 0)
            return ;
        $title = htmlentities($post['title']);
        $desc = htmlentities($post['description']);
        $icon = htmlentities($post['icon']);

        $query = $this->db->prepare("UPDATE wibuu_features SET title = :title, description = :description, icon = :icon WHERE id = $id");
        $query->execute(array(
            "title" => $title,
            "description" => $desc,
            "icon" => $icon));

        $this->alert = '<div class="col"><div class="alert alert-success" role="alert" style="width: 100%;margin-top: 24px;"><span>Le changement a été correctement effectué !</span></div></div>';

    }

    public function uploadPDF($whoarewe, $cgu, $mentions) {
        if ($whoarewe['size'] > 0 AND $whoarewe['error'] == 0) {
            if ($this->uploadFile($whoarewe, 'aboutus', '../', array("pdf", "PDF"), 1000000, 'pdf') == 0)
                return ;
        }
        if ($cgu['size'] > 0 AND $cgu['error'] == 0) {
            if ($this->uploadFile($cgu, 'cgu', '../', array("pdf", "PDF"), 1000000, 'pdf') == 0)
                return ;
        }
        if ($mentions['size'] > 0 AND $mentions['error'] == 0) {
            if ($this->uploadFile($mentions, 'legal', '../', array("pdf", "PDF"), 1000000, 'pdf') == 0)
                return ;
        }
    }

}