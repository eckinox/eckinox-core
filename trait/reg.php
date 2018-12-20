<?php

namespace Eckinox;

trait reg {

    protected static $registry;

    public static function registry(...$args) {
        static::$registry || ( static::$registry = Registry::instance() );

        switch ( count($args) ) {
            case 2:
                return static::$registry->set($args[0], $args[1]);

            case 1:
                return static::$registry->get($args[0]);

            case 0:
            default:
                return static::$registry;
        }
    }

    public static function registry_has($key) {
        return static::registry()->has($key);
    }
}
