<?php

namespace Eckinox;

class Configuration {
    use singleton;

    const DELIMITER = '.';
    
    static $data   = [];
    static $cache  = [];
    static $engine = [];
    
    protected $updated_data = false;
    protected $loaded_folder = [];
    
    protected function __construct() {
        $this->persistent = new Persistent(static::class);
        
        if ( ! Eckinox::debug() ) {
            $data = $this->persistent->load();
            static::$data = $data['data'];
            $this->loaded_folder = $data['loaded_folder'];
        }
    }

    public function __destruct() {
        $this->update_data() && $this->persistent->save([
            'loaded_folder' => $this->loaded_folder,
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
        
        return isset( static::$cache[$key] ) ? static::$cache[$key] : static::$cache[$key] = iterate::array_get(static::$data, $key, static::DELIMITER);
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
    
    public function load($path) {
        if ( !in_array($path, $this->loaded_folder) ) {
            $this->updated_data = true;
            $this->loaded_folder[] = $path;
            
            foreach(is_dir($path) ? iterate::files($path) : [$path] as $item) {
                foreach(static::$engine as $engine) {
                    if ( $engine->accept($item )) {
                        if ( $array = $engine->translate( $item ) ) {
                            $this->_merge_data($array);
                        }
                        
                        continue 2;
                    }
                }
            }
        }
    }
    
    protected function _merge_data($data) {
        $data = iterate::split_keys($data);
        static::$data = array_replace_recursive(static::$data, $data);
    }
}