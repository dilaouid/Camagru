<?php


namespace App;
use \PDO;

class Database {

    private $dsn;
    private $dbuser;
    private $dbpass;
    private $bdd;

    public function __construct($dsn, $dbuser, $dbpass) {
        $this->dsn =    $dsn;
        $this->dbuser = $dbuser;
        $this->dbpass = $dbpass;
    }

    private function getPDO() {

        if ($this->bdd === NULL) {
            try {
                $bdd = new PDO($this->dsn, $this->dbuser, $this->dbpass);
            } catch (PDOException $e) {
                echo 'Connexion échouée : ' . $e->getMessage();
            }
            $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->bdd = $bdd;
        }
        return $this->bdd;

    }

    public function query($statement, $classname, $single = false, $fetchDatas = false) {

        $req 	= $this->getPDO()->query($statement);
        if ($fetchDatas === TRUE) {
            $req->setFetchMode(PDO::FETCH_CLASS, $classname);
            if ($single) {
                $data = $req->fetch();
            } else {
                $data = $req->fetchAll();
            }
            return $data;
        }

    }

    public function query_new($statement) {
        $req = $this->getPDO()->query($statement);
        $datas = $req->fetchAll(PDO::FETCH_ASSOC);
        return $datas;
    }

    public function prepare($statement, $attributes, $classname, $single = false) {
        $req = $this->getPDO()->prepare($statement);
        $req->execute($attributes);
        $req->setFetchMode(PDO::FETCH_CLASS, $classname);
        if ($single) {
            $data = $req->fetch();
        } else {
            $data = $req->fetchAll();
        }
        return $data;
    }

    public static function createTables($database) {

        $database->query("DROP TABLE IF EXISTS `wibuu_global`;
        CREATE TABLE IF NOT EXISTS `wibuu_global` (
          `sitename` varchar(15) DEFAULT 'Wibuu',
          `subtitle_index` varchar(40) DEFAULT '',
          `short_about_us` text,
          `facebook` varchar(45) DEFAULT '',
          `twitter` varchar(30) DEFAULT '',
          `instagram` varchar(45) DEFAULT '',
          `maintenance` int(6) DEFAULT '0',
          `enable_registration` int(6) DEFAULT '1',
          UNIQUE KEY `sitename` (`sitename`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;", 'App\Database', true);

        $database->query('INSERT INTO wibuu_global (short_about_us, subtitle_index) VALUES ("Praesent sed lobortis mi. Suspendisse vel placerat ligula. Vivamus ac sem lacus. Ut vehicula rhoncus elementum. Etiam quis tristique lectus. Aliquam in arcu eget velit pulvinar dictum vel in justo.", "Weeb comme tu es !")', 'App\Database', true);

        $database->query("DROP TABLE IF EXISTS `wibuu_users`;
        CREATE TABLE IF NOT EXISTS `wibuu_users` (
          `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT,
          `username` varchar(15) DEFAULT NULL,
          `password` varchar(255) DEFAULT NULL,
          `email` varchar(30) DEFAULT NULL,
          `avatar` varchar(60) DEFAULT 'default.jpg',
          `twitter` varchar(30) DEFAULT '',
          `instagram` varchar(50) DEFAULT '',
          `facebook` varchar(50) DEFAULT '',
          `description` text,
          `admin` int(1) DEFAULT '0',
          `logged` int(1) DEFAULT '0',
          `banned` int(1) DEFAULT '0',
          `private` int(1) DEFAULT '0',
          `notifications` tinyint(1) NOT NULL DEFAULT '1',
          `registration_key` varchar(255) DEFAULT '',
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        COMMIT;", 'App\Database', true);

        $database->query("DROP TABLE IF EXISTS `wibuu_posts`;
        CREATE TABLE IF NOT EXISTS `wibuu_posts` (
          `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT,
          `title` varchar(20) DEFAULT NULL,
          `description` varchar(255) DEFAULT NULL,
          `img` varchar(120) DEFAULT NULL,
          `date` datetime DEFAULT CURRENT_TIMESTAMP,
          `author` int(6) DEFAULT NULL,
          `filter` int(6) DEFAULT NULL,
          `nb_likes` int(11) DEFAULT '0',
          `nb_comments` int(11) DEFAULT '0',
          `active` int(1) DEFAULT '1',
          `private` int(1) DEFAULT '0',
          `keylock` varchar(255) DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        COMMIT;", 'App\Database', true);

        $database->query("DROP TABLE IF EXISTS `wibuu_notifications`;
        CREATE TABLE IF NOT EXISTS `wibuu_notifications` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `author` int(6) NOT NULL,
          `dest` int(6) NOT NULL,
          `type` varchar(20) NOT NULL,
          `id_link` int(11) NOT NULL,
          `object` int(11) DEFAULT NULL,
          `active` tinyint(1) NOT NULL DEFAULT '1',
          `date` datetime DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        COMMIT;", 'App\Database', true);

        $database->query("DROP TABLE IF EXISTS `wibuu_partner`;
        CREATE TABLE IF NOT EXISTS `wibuu_partner` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `url` varchar(160) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        COMMIT;", 'App\Database', true);

        $database->query("DROP TABLE IF EXISTS `wibuu_features`;
        CREATE TABLE IF NOT EXISTS `wibuu_features` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `title` varchar(60) NOT NULL,
          `description` text NOT NULL,
          `icon` varchar(100) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        COMMIT;", 'App\Database', true);

        $database->query("DROP TABLE IF EXISTS `wibuu_comments`;
        CREATE TABLE IF NOT EXISTS `wibuu_comments` (
          `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT,
          `author` int(6) DEFAULT NULL,
          `post` int(11) NOT NULL,
          `date` datetime DEFAULT NULL,
          `comment` text,
          `active` int(1) DEFAULT '1',
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        COMMIT;", 'App\Database', true);

        $database->query("DROP TABLE IF EXISTS `wibuu_follows`;
        CREATE TABLE IF NOT EXISTS `wibuu_follows` (
          `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT,
          `user` int(6) DEFAULT NULL,
          `follows` int(6) DEFAULT NULL,
          `accepted` tinyint(1) NOT NULL DEFAULT '0',
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        COMMIT;", 'App\Database', true);

        $database->query("DROP TABLE IF EXISTS `wibuu_likes`;
        CREATE TABLE IF NOT EXISTS `wibuu_likes` (
          `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT,
          `user` int(6) DEFAULT NULL,
          `likes` int(6) DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;", 'App\Database', true);

        $database->query("DROP TABLE IF EXISTS `wibuu_filters`;
        CREATE TABLE IF NOT EXISTS `wibuu_filters` (
          `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT,
          `name` varchar(15) DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        COMMIT;", 'App\Database', true);
        
        $database->query("DROP TABLE IF EXISTS `wibuu_password`;
        CREATE TABLE IF NOT EXISTS `wibuu_password` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user` int(11) NOT NULL,
          `keylock` varchar(255) NOT NULL,
          `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        COMMIT;", 'App\Database', true);

    }

}