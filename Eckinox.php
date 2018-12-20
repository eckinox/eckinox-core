<?php

namespace Eckinox;

class Eckinox extends Bootstrap {
    use singleton; 
    
    public static $lang;
    
    protected static $debug = false;
    protected static $error_reporting = 0;
    
    protected $current_application = "Eckinox";
    
    public function initialize() {
        /* Default JSON parser engine */
        $engine = new class extends config_interface {
            protected $name = "json";
            protected $accept = [ "json" ];
            
            public function translate($filepath) {
                if ( null === $content = json_decode(file_get_contents($filepath), true) ) {
                    throw( new \Exception("An error occured trying to parse configuration file: given json file [ $filepath ] seems to be invalid") );
                }
                
                return $content;
            }
            
            public function save($filepath, $content) {
                return json_encode($content, \JSON_PRETTY_PRINT);
            }
        };
        
        $this->config()->add_engine($engine);
        $this->lang()->add_engine($engine);
        
        parent::initialize();
        
        Event::make()->on('eckinox.bootstrap.completed', function() {
            \mb_internal_encoding( $this->config('Eckinox.encoding.default') ?: 'UTF-8');
            \date_default_timezone_set($t = $this->config('Eckinox.locale.timezone'));
            
            $this->_load_configs( static::path_system_config() );
        });
        
    }
    
    public function current_application($set = null) {
        return $set === null ? $this->current_application : $this->current_application = $set;
    }
    
    public function create_cms_folders() {
        foreach([ static::path_var(), static::path_tmp(), static::path_cache(), static::path_migration(), static::path_system_config() ] as $item) {
            if ( ! file_exists($item) ) {
                mkdir($item);
                chmod($item, 0775);
            }
        }
    }   
    
    public static function header_vars() {
        $headers = array_change_key_case( getallheaders() );

        if ( isset($headers['eckinox']) ) {
            if ( $retval = json_decode($headers['eckinox'], true) ) {
                return $retval;
            }
            else {
                if ( json_last_error() ) {
                    trigger_error('Your application\'s header has an invalid json syntax...', E_USER_WARNING);
                }
            }
        }
        
        return [];
    }
    
    public static function debug($set = null) {
        return $set === null ? static::$debug : static::$debug = $set;
    }
    
    public static function error_reporting($set = null, $force = false) {
        if ( ( ($set !== null) && ( !static::$error_reporting || $force) )) {
            static::$error_reporting = $set;
            error_reporting($set);
        }
        
        return static::$error_reporting;
    }
    
    public static function path_var() {
        return SRC_DIR.VAR_DIR;
    }
    
    public static function path_migration() {
        return static::path_var().MIGRATION_DIR;
    }
    
    public static function path_tmp() {
        return static::path_var().TMP_DIR;
    }
    
    public static function path_cache() {
        return static::path_var().CACHE_DIR;
    }
    
    public static function path_system_config() {
        return static::path_var().CONFIG_DIR;
    }
}