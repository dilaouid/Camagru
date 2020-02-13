<?php

namespace App;

use App\Database;
use \PDO;

class Users {

    public $alert;
    public $validatedAccount;

    private $userid;

    private $db;

    private $username = null;
    private $email = null;
    private $password = null;
    private $confirm = null;
    private $cgu = null;
    private $global;
    private $admin = 0;

    public function __construct($db, $post = null, $global = null) {
        $this->db       = $db;
        if (isset($post)) {
            $this->username = htmlentities($post['username']);
            $this->password = htmlentities($post['password']);

            if (isset($post['confirm_password']))
                $this->confirm  = htmlentities($post['confirm_password']);

            if (isset($post['email']))
                $this->email    = htmlentities($post['email']);

            if (isset($post['cgu']))
                $this->cgu      = htmlentities($post['cgu']);
        }

        if (isset($_SESSION['id'])) {
            $this->userid = $_SESSION['id'];
            $this->admin = $this->isAdmin($this->userid);
        }

        if (isset($global))
            $this->global = $global;

    }

    public function isAdmin($id) {
        $query = $this->db->query("SELECT id FROM wibuu_users WHERE admin = 1 AND id = $id");
        return ($query->rowCount());
    }

    public function createAdmin() {

        if ($this->password === $this->confirm) {
            if (filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                $uppercase = preg_match('@[A-Z]@', $this->password);
                $lowercase = preg_match('@[a-z]@', $this->password);
                $number    = preg_match('@[0-9]@', $this->password);
                $specialChars = preg_match('@[^\w]@', $this->password);

                if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($this->password) < 8)
                    return -2;

                $query = $this->db->prepare("INSERT INTO Wibuu_users (username, password, email, admin, description) VALUES (:username, :password, :email, 1, 'Administrateur du site')");
                $query->execute(array(
                    "username" => $this->username,
                    "password" => password_hash($this->password, PASSWORD_DEFAULT),
                    "email"    => $this->email));

                return 1;
            }
            else
                return 0;
        }
        else
            return -1;
    }

    private function checkEmail_exists() {
        $checkEmail = $this->db->prepare('SELECT email FROM wibuu_users WHERE email = ?');
        $checkEmail->execute(array($this->email));
        return $checkEmail->rowCount();
    }

    private function checkUser_exists() {
        $checkUser = $this->db->prepare('SELECT username FROM wibuu_users WHERE username = ?');
        $checkUser->execute(array($this->username));
        return $checkUser->rowCount();
    }

    private function verificationMail($key) {
        $verification_link  = $_SERVER['SERVER_NAME'].'?verification='.$key;
        $subject            = 'Bienvenue sur '.$this->global['sitename'].' ! Votre inscription est presque terminée !';
        $message            = '<html><body><p>Bonjour '.$this->username.' !<br>
    Bienvenue sur '.$this->global['sitename'].' ! Votre inscription est presque terminée. Il ne vous reste qu\'à valider 
    votre compte en cliquant sur le lien suivant: <a href="http://'.$verification_link.'">Valider son inscription</a>.<br>
    Si vous n\'avez pas essayé de vous inscrire à '.$this->global['sitename'].', merci d\'ignorer ce mail.<br><br>

    Cordialement,<br>
    L\'équipe '.$this->global['sitename'].'<br><br>

    Note: Ce mail est automatique, merci de ne pas y répondre.</p></body></html>';
        $headers            = 'MIME-Version: 1.0' . "\r\n";
        $headers            .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
        $headers            .= 'From: noreply@'.$this->global['sitename'].'.com';

        mail($this->email, $subject, $message, $headers);
    }

    private function createKey() {
        $key = bin2hex(random_bytes(23));
        while ($this->db->prepare('SELECT COUNT(registration_key) FROM wibuu_users WHERE registration_key = '.$key)->fetch()[0] > 0)
            $key = bin2hex(random_bytes(23));
        $this->verificationMail($key);
        return $key;
    }

    public function createUser() {

        if ($this->global['enable_registration'] == 0) {
            $this->alert = '<div class="alertmsg"><div class="alert alert-danger alertbox" role="alert"><span>Les inscriptions sont temporairement suspendus</span></div></div>';
            return null;
        }

        if ($this->password != $this->confirm) {
            $this->alert = '<div class="alertmsg"><div class="alert alert-danger alertbox" role="alert"><span>Les mots de passes saisis sont différents !</span></div></div>';
            return null;
        }
        $uppercase = preg_match('@[A-Z]@', $this->password);
        $lowercase = preg_match('@[a-z]@', $this->password);
        $number    = preg_match('@[0-9]@', $this->password);
        $specialChars = preg_match('@[^\w]@', $this->password);

        if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($this->password) < 8) {
            $this->alert = '<div class="alertmsg"><div class="alert alert-danger"  role="alert">
                    <span>Votre mot de passe doit contenir au moins une majuscule, une minuscule, <br/>un chiffre, un caractère special (autre qu\'une lettre ou un chiffre) <br/>et doit faire au moins 8 caractères de longueur.</span>
                    </div></div>';
            return null;
        }


        if (filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            if ($this->checkEmail_exists() == 0) {
                if ($this->checkUser_exists() == 0) {
                    $createKey = $this->createKey();
                    $create = $this->db->prepare('INSERT INTO wibuu_users (username, password, email, description, registration_key) VALUES (:username, :password, :email, "Membre", :createKey)');
                    $create->execute(array(
                        'username' => $this->username,
                        'password' => password_hash($this->password, PASSWORD_DEFAULT),
                        'email'    => $this->email,
                        'createKey'=> $createKey));
                    if ($create)
                        $this->alert = '<div class="alertmsg"><div class="alert alert-success alertbox" role="alert"><span><strong>Votre compte a bien été crée !</strong><br />Veuillez le confirmer depuis votre boîte mail.</span></div></div>';
                    else
                        $this->alert = '<div class="alertmsg"><div class="alert alert-danger alertbox" role="alert"><span><Une erreur PHP est survenue.</span></div></div>';
                    return null;
                }
                $this->alert = '<div class="alertmsg"><div class="alert alert-danger alertbox" role="alert"><span>Un utilisateur porte déjà ce <strong>nom</strong>.</span></div></div>';
                return null;
            }
            $this->alert = '<div class="alertmsg"><div class="alert alert-danger alertbox" role="alert"><span>Cette <strong>email</strong> est déjà utilisée.</span></div></div>';
            return null;
        }
        $this->alert = '<div class="alertmsg"><div class="alert alert-danger alertbox" role="alert"><span>Le format de l\'email est invalide='.$this->email.'</span></div></div>';
        return null;
    }

    public function validateUser($key) {
        $checkKey = $this->db->prepare('SELECT registration_key FROM wibuu_users WHERE registration_key = :key');
        $checkKey->execute(array('key' => $key));
        if ($checkKey->rowCount() == 1) {
            $checkKey->closeCursor();
            $validateUser = $this->db->query('UPDATE wibuu_users SET registration_key = 0 WHERE registration_key = "'.$key.'"');
            $this->validatedAccount = '<script>alert("Votre compte est validée ! Vous pouvez à présent vous connecter !")</script>';
        }
    }

    public function login() {
        $checklog = $this->db->prepare('SELECT password, id, registration_key, banned, admin FROM wibuu_users WHERE username = ?');
        $checklog->execute(array($this->username));
        if ($checklog->rowCount() == 1) {
            $result = $checklog->fetchAll(PDO::FETCH_ASSOC);
            $banned = $result[0]['banned'];
            $validated = $result[0]['registration_key'];
            if (password_verify($this->password, $result[0]['password']) AND $banned == 0 AND (strlen($validated) == 1 OR $validated == null)) {
                $checklog->closeCursor();
                $_SESSION['id'] = $result[0]['id'];
                return (1);
            }
            else if (strlen($validated) > 1)
                $this->alert = '<div class="alert alert-danger alertbloc" role="alert"><span>Compte non validé</span></div>';
            else if ($banned == 1)
                $this->alert = '<div class="alert alert-danger alertbloc" role="alert"><span>Compte fermé</span></div>';
            else
                $this->alert = '<div class="alert alert-danger alertbloc" role="alert"><span>Identifiants incorrects</span></div>';
            return (0);
        }
        $checklog->closeCursor();
        $this->alert = '<div class="alert alert-danger alertbloc" role="alert"><span>Identifiants incorrects</span></div>';
    }

    public function infoUsers($id) {
        $datas = $this->db->prepare('SELECT username, email, avatar, twitter, instagram, facebook, description, private, notifications
                                    FROM wibuu_users WHERE id = ?');
        $datas->execute(array($id));
        return $datas->fetch(PDO::FETCH_ASSOC);
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
            $this->alert = '<div class="col"><div class="alert alert-danger" role="alert" style="width: 100%;margin-top: 24px;"><span>Le fichier est trop lourd ! Taille max: '.($legalSize / 1000).' ko</span></div></div>';
            return 0;
        }
        return 1;
    }

    public function newAvatar($avatarFile, $userid) {

        if ($this->uploadFile($avatarFile['avatar'], 'user_'.$userid, 'assets/img/profil_pictures', array("jpg", "png", "JPG", "PNG"), 700000, 'jpg') == 0)
            return ;
        $new_avatar = 'user_'.$userid.'.jpg';
        $updateAvatar = $this->db->query("UPDATE wibuu_users SET avatar = '$new_avatar' WHERE id = $userid");
        if (!$updateAvatar) {
           echo "\nPDO::errorInfo():\n";
           print_r($dbh->errorInfo());
           exit();
        }
        return 1;
    }

    public function changeInfos($post) {

        $userid = $_SESSION['id'];
        $nbofchange = 0;    
        $this->email = $post['email'];
        $this->username = $post['username'];

        if ( !empty($this->username) && (strlen($this->username) > 15 OR (strlen($this->username) < 4) || (preg_match('/\s/', $this->username) OR preg_match('/[^a-zA-Z\d]/', $this->username) ) )) {
            $this->alert = '<div class="col"><div class="alert alert-danger" role="alert" style="width: 100%;margin-top: 24px;"><span>Le nom d\'utilisateur saisi est invalide !</span></div></div>';
            return 0;
        }

        if (!empty($this->email) && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->alert = '<div class="col"><div class="alert alert-danger" role="alert" style="width: 100%;margin-top: 24px;"><span>L\'adresse mail saisie est invalide !</span></div></div>';
            return 0;
        }

        foreach ($post as $key => $value) {
            if ($key != 'submit' && $key != 'confirm_password' && $key != 'private' && $key != 'notifications') {
                $entry = htmlentities($value);
                if (!empty($value)) {
                    if ($key == 'password') {
                        if ($post['password'] == $post['confirm_password']) {

                            $uppercase = preg_match('@[A-Z]@', $post['password']);
                            $lowercase = preg_match('@[a-z]@', $post['password']);
                            $number    = preg_match('@[0-9]@', $post['password']);
                            $specialChars = preg_match('@[^\w]@', $post['password']);

                            if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($post['password']) < 8) {
                                $this->alert = '<div class="col"><div class="alert alert-danger"  role="alert">
                                <span>Votre mot de passe doit contenir au moins une majuscule, une minuscule, <br/>un chiffre, un caractère special (autre qu\'une lettre ou un chiffre) <br/>et doit faire au moins 8 caractères de longueur.</span>
                                </div></div>';
                                return ;
                            }
                            $entry = password_hash($entry, PASSWORD_DEFAULT);
                        }
                        else {
                            $this->alert = '<div class="col"><div class="alert alert-danger" role="alert" style="width: 100%;margin-top: 24px;">
                                            <span>Les mots de passes saisis sont différents !</span></div></div>';
                            return 0;
                        }

                    }
                    if (($key == 'username' && $this->checkUser_exists($this->username) == 0) || ($key == 'email' && $this->checkEmail_exists($this->email) == 0) || ($key != 'username' && $key != 'email')) {
                        $update = $this->db->prepare("UPDATE wibuu_users SET $key = ? WHERE id = $userid");
                        $update->execute(array($entry));
                        $update->closeCursor();
                        $nbofchange++;
                    }
                    else if ($key == 'username') {
                        $this->alert = '<div class="col"><div class="alert alert-danger" role="alert" style="width: 100%;margin-top: 24px;"><span>Ce nom d\'utilisateur existe déjà.</span></div></div>';
                        return 0;
                    }
                    else if ($key == 'email') {
                        $this->alert = '<div class="col"><div class="alert alert-danger" role="alert" style="width: 100%;margin-top: 24px;"><span>Cette email est déjà utilisée par un autre utilisateur.</span></div></div>';
                        return 0;
                    }
                }
            }
            else if ($key == 'private') {
                if ($value == 'on')
                    $this->private = 1;
                else
                    $this->private = 0;
                $update = $this->db->query("UPDATE wibuu_users SET $key = $this->private WHERE id = $userid");
                $update->closeCursor();

                $acceptFollows = $this->db->query("UPDATE wibuu_follows SET accepted = 1 WHERE follows = $userid");
                $removNotif    = $this->db->query("DELETE FROM wibuu_notifications WHERE type = 'follow' AND dest = $userid");

                $nbofchange++;
            }
            else if ($key == 'notifications') {
                if ($value == 'on')
                    $this->notifications = 1;
                else
                    $this->notifications = 0;
                $update = $this->db->query("UPDATE wibuu_users SET $key = $this->notifications WHERE id = $userid");
                $update->closeCursor();
                $nbofchange++;
            }
        }
        if ($nbofchange > 0)
            $this->alert = '<div class="col"><div class="alert alert-success" role="alert" style="max-width: 100%;margin-top: 24px;"><span>Les informations du compte ont été correctement mis à jours !</span></div></div>';
        return 1;

    }

    private function isFollowing($user, $follows) {
        $query = $this->db->query("SELECT id FROM wibuu_follows WHERE user = $user AND follows = $follows AND accepted = 1");
        return ($query->rowCount());
    }

    private function awaitFollowing($user, $follows) {
        $query = $this->db->query("SELECT id FROM wibuu_follows WHERE user = $user AND follows = $follows AND accepted = 0");
        return ($query->rowCount());

    }

    private function notifManagment($action, $author, $dest, $type, $id_link, $object = null) {
        if ($object == null)
            $object = 0;
        if ($action == "delete")
            $query = $this->db->query("DELETE FROM wibuu_notifications WHERE author = $author AND dest = $dest AND type = '$type' AND id_link = $id_link AND object = $object");
        if ($action == "insert")
            $query = $this->db->query("INSERT INTO wibuu_notifications (author, dest, type, id_link, object) VALUES ($author, $dest, '$type', $id_link, $object)");
    }

    public function askFollow($userid, $id) {
        $private = 1;
        if ($this->admin == 0) {
            $checkPrivate = $this->db->query("SELECT private FROM wibuu_users WHERE id = $id");
            $private = $checkPrivate->fetch()[0];
            if ($private == 1)
                $private = 0;
            else
                $private = 1;
        }
        if ($this->isFollowing($userid, $id) == 0 AND $this->awaitFollowing($userid, $id) == 0)  {
            $query = $this->db->query("INSERT INTO wibuu_follows (user, follows, accepted) VALUES ($userid, $id, $private)");
            $this->notifManagment("insert", $userid, $id, 'follow', $userid);
        }
    }

    public function acceptFollow($userid, $id) {
        if ($this->awaitFollowing($id, $userid) == 1) {
            $query = $this->db->query("UPDATE wibuu_follows SET accepted = 1 WHERE user = $id AND follows = $userid");
            $this->notifManagment("delete", $id, $userid, 'follow', $id);
        }
    }

    public function unFollow($userid, $id) {
        if ($this->isFollowing($userid, $id) == 1 AND $this->awaitFollowing($userid, $id) == 0) {
            $query = $this->db->query("DELETE FROM wibuu_follows WHERE user = $userid AND follows = $id AND accepted = 1");
            $this->notifManagment("delete", $userid, $id, 'follow', $userid);

        }
    }

    public function stopFollow($userid, $id) {
        if ($this->awaitFollowing($userid, $id) == 1) {
            $query = $this->db->query("DELETE FROM wibuu_follows WHERE user = $userid AND follows = $id AND accepted = 0");
            $this->notifManagment("delete", $userid, $id, 'follow', $userid);
        }

    }

    private function sendMail($key, $email) {

        $recoverlink        =       'http://'.$_SERVER['SERVER_NAME'].'/forgot_password.php?key='.$key;
        $subject            = 'Réinitialiser votre mot de passe';
        $message            = '<html><body><p>Bonjour !<br>';

        $message .= 'On dirait que vous avez oublié votre mot de passe ? Pas de panique ! Vous pouvez le récupérer en cliquant sur le lien ci-dessous :<br><br><a href="'.$recoverlink.'">Réinitialiser mon mot de passe</a><br><br>Ce lien sera valide pour une durée de 24 heures à partir de l\'envoi de ce mail.';

        $message .= 'Cordialement,<br>
    L\'équipe '.$this->global['sitename'].'<br><br>

    Note: Ce mail est automatique, merci de ne pas y répondre.</p></body></html>';
        $headers            = 'MIME-Version: 1.0' . "\r\n";
        $headers            .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
        $headers            .= 'From: noreply@'.$this->global['sitename'].'.com';

        mail($email, $subject, $message, $headers);

    }

    public function recoverPassword($email) {

        $safeMail = htmlentities($email);

        if (!filter_var($safeMail, FILTER_VALIDATE_EMAIL)) {
            $this->alert = '<div class="alert alert-danger"  role="alert" style="width: 250px;margin-top: 16px;">
                        <span>Le format du mail saisi est invalide.</span>
                    </div>';
            return ;
        }

        $checkEmail = $this->db->prepare("SELECT id FROM wibuu_users WHERE email = ?");
        $checkEmail->execute(array($email));
        if ($checkEmail->rowCount() == 1) {
            $userid = $checkEmail->fetch()[0];
            $key = bin2hex(random_bytes(23));
            while ($this->db->prepare('SELECT COUNT(keylock) FROM wibuu_password WHERE keylock = '.$key)->fetch()[0] > 0)
                $key = bin2hex(random_bytes(23));
            $this->db->query("DELETE FROM wibuu_password WHERE user = $userid");
            $query = $this->db->query("INSERT INTO wibuu_password (user, keylock) VALUES ($userid, '$key')");
            $this->sendMail($key, $safeMail);
        }
        $this->alert = '<div class="alert alert-success"  role="alert" style="width: 250px;margin-top: 16px;">
                    <span>Un email a été envoyé pour réinitialiser votre mot de passe.</span>
                    </div>';

    }

    public function validKey($key) {
        $safeKey = htmlentities($key);

        $query = $this->db->prepare("SELECT id, date FROM wibuu_password WHERE keylock = ?");
        $query->execute(array($safeKey));
        if (!$query->rowCount())
            return 0;
        $date = strtotime($query->fetch()[1]);
        $time = strtotime(date('Y-m-d h:i:s', time())) - $date;
        if ($time > 86400)
            return 0;
        return 1;
    }

    public function newPassword($password, $confirm_password, $keylock) {

        if ($password != $confirm_password) {
            $this->alert = '<div class="alert alert-danger"  role="alert" style="width: 250px;margin-top: 16px;">
                    <span>Les mots de passes saisis sont différents.</span>
                    </div>';
            return ;
        }

        $uppercase = preg_match('@[A-Z]@', $password);
        $lowercase = preg_match('@[a-z]@', $password);
        $number    = preg_match('@[0-9]@', $password);
        $specialChars = preg_match('@[^\w]@', $password);

        if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
            $this->alert = '<div class="alert alert-danger"  role="alert" style="width: 250px;margin-top: 16px;">
                    <span>Votre mot de passe n\'est pas assez sécurisé.</span>
                    </div>';
            return ;
        }

        $safePass = htmlentities($password);

        $safeKey = htmlentities($keylock);

        $query = $this->db->prepare("SELECT user FROM wibuu_password WHERE keylock = ?");
        $query->execute(array($safeKey));
        if (!$query->rowCount())
            return ;
        $userid = $query->fetch()[0];
        $updatePassword = $this->db->prepare("UPDATE wibuu_users SET password = ? WHERE id = $userid");
        $updatePassword->execute(array(password_hash($safePass, PASSWORD_DEFAULT)));
        $this->db->query("DELETE FROM wibuu_password WHERE user = $userid");

    }


}

?>