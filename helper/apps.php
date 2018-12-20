<?php

namespace Eckinox;

abstract class apps {
    
    public static function get_object_path($object, $skip_class = true) {
        $class = explode("\\", is_string($object) ? $object : get_class($object) ) ;
        $skip_class && array_pop($class);
        
        return array_diff( array_map('strtolower', $class), [ 'model', 'controller' ]);
    }
    
    public static function keyname($fullkey) {
        return isset(Bootstrap::$keyname[$fullkey]) ? Bootstrap::$keyname[$fullkey] : "";
    }
    
    public static function component_of($object) {
        $retval = [];
        
        foreach(array_reverse(apps::get_object_path($object)) as $item) {
            if ( isset(Bootstrap::$apps[$item]) && ( Bootstrap::$apps[$item]['object']->get_type() <= Bootstrap::IS_COMPONENT ) ) {
                $retval[] = $item;
            }
        }
        
        return $retval ?: false;
    }
    
    public static function module_of($object) {
        $retval = [];
        
        foreach(array_reverse(apps::get_object_path($object)) as $item) {
            if ( isset(Bootstrap::$apps[$item]) && ( Bootstrap::$apps[$item]['object']->get_type() === Bootstrap::IS_MODULE ) ) {
                $retval[] = $item;
            }
        }
        
        return $retval ?: false;
    }
    
    public static function iterable_apps_key($object) {
        return implode('.', static::get_object_path($object, false));
    }
    
    public static function find_from($object, $base_dir, $file, $module = null) {
        $path = static::get_object_path($object, true);
        
        $compo = static::component_of($object);
        
        # var_dump( $compo );
        
        #var_dump( $compo /* static::get_object_path($object) */ );
        # var_dump(get_class($object));
        
        while ( $path && empty(Bootstrap::$directory[ implode('.', $path) ]) ) {
            array_pop($path);
        }
        
        $absolute_path = Bootstrap::$directory[ implode('.', $path) ].$base_dir.$file;
        
        return is_file($absolute_path) ? $absolute_path : false;
    }
    
}