<?php


namespace App;


class App {

    private static $_instance;

    public static function getInstance() {
        if (is_null(self::$_instance))
            self::$_instance = new App();
        return self::$_instance;
    }

}