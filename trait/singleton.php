<?php

namespace Eckinox;

trait singleton {

    public static function instance(...$arguments) {
        static $self = null;

        if ( !isset($self) ) {
            $self = new static(...$arguments);
        }

        return $self;
    }

    public static function make(...$arguments) {
        return static::instance(...$arguments);
    }

}
