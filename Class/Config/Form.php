<?php

namespace App;

class Form
{

    public function leftSide($title, $text) {
        return '<div class="col-md-4 mx-auto"><h3>'. $title .'</h3><p>'. $text .'</p> </div>';
    }

    public function information($information_text) {
        return '<em>Informations</em><p class="small mt-3">'.$information_text.'<br></p>';
    }

    public function input($placeholder, $name, $required = NULL, $type = NULL, $onchange = NULL) {
        return '<div class="form-group"><input type="'. $type .'" name="'. $name .'" class="form-control" placeholder="'. $placeholder .'" '.$required . ' ' . $onchange . '></div>';
    }

    public function submit($text, $typeButton = 'success', $disabled = NULL) {
        return '<div class="form-group text-center"><button type="submit" class="btn btn-'.$typeButton.'" name="submit" '. $disabled .'>'.$text.'</button></div>';
    }

    public static function hr() {
        return '<div class="col-md-12 "><div class="login-or"><hr class="hr-or"></div></div>';
    }

    public function fieldSetup($type) {

        if ($type === 'setup')
            return $this->input('Hôte', 'hostname', 'required', 'text') . $this->input('Identifiant', 'user', 'required', 'text') . $this->input('Mot de passe', 'password', '', 'password') . $this->input('Nom de la base de données', 'db', '', 'text') . $this->submit('Connexion à la base de données');
        if ($type === 'createAdmin')
            return $this->input('Nom d\'utilisateur', 'username', 'required') . $this->input('Adresse e-mail', 'email', 'required') . $this->input('Mot de passe', 'password', 'required', 'password', 'onchange=\'check_pass();\'') . $this->input('Confirmez le mot de passe', 'confirm_password', 'required', 'password', 'onchange=\'check_pass();\'') . $this->submit('Création du compte administrateur', 'success', 'disabled');

    }

    public function errorMessage($msg) {
        return '<div class="alert alert-danger" role="alert">'. $msg .'</div>';
    }



    public function createForm($type) {

        $baliseForm = '<form method="post" action="">';

        if ($type === 'setup') {
            $title = '<h5>Informations de connexion</h5>';
            return $title . $baliseForm . $this->fieldSetup($type) . self::hr() . $this->information('Vous trouverez ces informations dans un email que vous a envoyé votre hébergeur internet. <strong>La table sera crée automatiquement si elle n\'existe pas dans votre base de données.</strong>'). '</form></div>';
        }
        if ($type === 'createAdmin') {
            $title = '<h5>Inscription admin</h5>';
            return $title . $baliseForm . $this->fieldSetup($type) . self::hr() . $this->information('Votre mot de passe doit avoir au moins une majuscule, une minuscule, et être composé d\'au moins 8 caractères. <strong>Ce compte disposera de tout les droits sur votre site.</strong>'). '</form></div>';
        }

     }

}