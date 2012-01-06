<?php

namespace Siphon;

class Autoloader {

    static private $registered = false;

    private final function __construct(){}
    private final function __clone(){}

    static public function register(){

        if(false === self::$registered){
            spl_autoload_register(function($class) {
                if (0 === strpos($class, 'Siphon\\')) {
                    $dir = realpath(dirname(__FILE__));
                    $class = substr($class, strlen('Siphon\\'));
                    if ('\\' != DIRECTORY_SEPARATOR) {
                        $class = $dir . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
                    } else {
                        $class = $dir . DIRECTORY_SEPARATOR . $class . '.php';
                    }
                    if (file_exists($class)) {
                        require $class;
                        return true;
                    }
                }
                return false;
            });
            self::$registered = true;
        }
    }

}