<?php

namespace Eckinox;

use RecursiveDirectoryIterator,
    RecursiveIteratorIterator,
    RecursiveRegexIterator,
    RegexIterator;

abstract class iterate {

    /**
     * Set value to array with a given path using recursion
     * @param array			$array - array to set passed by reference !
     * @param string		$path - path like 'person.name.first'
     * @param string		$value - value to set
     */
    public static function array_set(& $array, $path, $value = '', $delimiter = '.') {
        $path_arr = explode($delimiter, $path);

        // Go to next node
        if ( isset($path_arr[1])) {
            $arr = array_shift($path_arr);
            static::array_set($array[$arr], implode($delimiter, $path_arr), $value);
        }
        // We are at the end of the path, set value
        else {
            $array[ $path_arr[0] ] = $value;
        }
    }

    public static function array_get($array, $path, $delimiter = '.') {
        $path_arr = explode($delimiter, $path);

        if (isset($array[$path_arr[0]])) {
            if (isset($path_arr[1])) {
                return static::array_get($array[array_shift($path_arr)], implode($delimiter, $path_arr));
            } else {
                return $array[$path_arr[0]];
            }
        }
        else {
            return null;
        }
    }

    public static function split_keys(&$array, $delimiter = ".", $enclosed = '{}') {
        if ( is_array($array) ) {
            $swap = [];

            foreach($array as $key => &$value) {
                $process = strstr($key, $delimiter);

                if ( $enclosed ) {
                    if ( ( strlen($key) - 2 ) !== strlen( $tkey = trim($key, $enclosed) ) ) {
                        $process = false;
                    }
                    else {
                        $oldkey = $key;
                        $key = $tkey;
                    }
                }

                if ( $process ) {
                    $tmp = [];
                    $keylist = explode($delimiter, $key);
                    $final_key = array_shift($keylist);
                    Arrayobj::iterate_set($keylist, $tmp, $value);
                    $swap[$final_key] = static::split_keys($tmp, $delimiter);

                    unset($array[isset($oldkey) ? $oldkey : $key]);
                }
                else if ( is_array($value) ) {
                    $value = static::split_keys($value, $delimiter);
                }
            }

            foreach($swap as $key => $item) {
                if ( $enclosed ) {
                    if ( ( strlen($key) - 2 ) === ( $tkey = trim($key, $enclosed)) ) {
                        $key = $tkey;
                    }
                }

                $array[$key] = $item;
            }
        }

        return $array;
    }

    public static function files($path, $file_extension = "") {
        $retval = [];

        if ( file_exists($path) ) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST,  RecursiveIteratorIterator::CATCH_GET_CHILD);

            if ($file_extension) {
                $iterator = new RegexIterator($iterator, '/^.+\.'.$file_extension.'$/i', RecursiveRegexIterator::GET_MATCH);
            }

            foreach ($iterator as $file) {
                if ($file_extension) {
                    $retval[] = $file[0];
                }
                else {
                    if ( $file->isFile() || $file->isDir() ) {
                        $retval[] = $file->getRealPath();
                    }
                }
            }
        }
        return $retval;
    }

}
