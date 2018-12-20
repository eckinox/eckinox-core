<?php

namespace Eckinox;

class Language {
    use singleton;

    const DELIMITER = '.';

    static $data   = [];
    static $cache  = [];
    static $engine = [];

    static $current_language = "";

    protected $updated_data = false;
    protected $filestack = [];

    protected function __construct() {
        $this->persistent = new Persistent(static::class);

        if ( ! Eckinox::debug() ) {
            $data = $this->persistent->load();
            static::$data = $data['data'];
            $this->filestack = $data['filestack'];
        }
    }

    public function __destruct() {
        $this->update_data() && $this->persistent->save([
            'filestack' => $this->filestack,
            'data' => static::$data
        ]);
    }

    public function update_data() {
        return $this->updated_data || Eckinox::debug();
    }

    public static function get($key = null) {
        if ( $key === null ) {
            return static::$data;
        }

        return isset( static::$cache[$key] ) ? static::$cache[$key] : static::$cache[$key] = iterate::array_get(static::$data, ( static::$current_language ? static::$current_language."." : "").$key, static::DELIMITER);
    }

    public static function set($key, $value) {
        $cache = explode(static::DELIMITER, $key);

        while ( !empty($cache) ) {
            $imp = implode(static::DELIMITER, $cache);
            array_pop($cache);
            unset( static::$cache[$imp] );
        }

        static::$cache[$key] = $value ;

        return iterate::array_set(static::$data, $key, $value, static::DELIMITER);
    }

    public function add_engine($engine) {
        static::$engine[] = $engine;
    }

    public function load($path, $filename_as_key = false) {
        if ( !in_array($path, $this->filestack) ) {
            $this->updated_data = true;
            $this->filestack[] = $path;

            foreach(iterate::files($path) as $item) {
                 foreach(static::$engine as $engine) {
                     if ( $engine->accept($item )) {

                         if ( $array = $engine->translate( $item ) ){
                             if ($filename_as_key ) {
                                 $base = basename($item);
                                 
                                 $key = substr($base,0, strrpos($base, '.'));
                                 $keysplit = explode('.', $key);
                                 $key = array_pop($keysplit);
                                 
                                 $this->_merge_data([ $keysplit ? "{".implode('.', array_merge([ $key ], $keysplit))."}" : $key => $array ]);
                             }
                             else {
                                 $this->_merge_data($array);
                             }
                         }

                         continue 2;
                     }
                 }
            }
        }
    }

    public static function current_language($set = null) {
        return $set === null ? static::$current_language : static::$current_language = $set;
    }

    protected function _merge_data($data) {
        $data = iterate::split_keys($data);
        static::$data = array_replace_recursive(static::$data, $data);
    }
}
