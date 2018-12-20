<?php namespace Eckinox;

trait config {
    public static function config(...$args) {
        switch ( count($args) ) {
            case 2:
                return Configuration::set($args[0], $args[1]);
                
            case 1:
                return Configuration::get($args[0]);
                
            case 0:
            default:
                return Configuration::instance();
        }
    }   
}