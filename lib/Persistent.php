<?php

namespace Eckinox;

class Persistent {
    /**
     *
     * @var mixed  Can be a real file path OR false to autogenerate
     */
    protected $path = false;
    protected $from = null;
    
    protected $uid = null;
    
    protected $base_dir = "persistent";
    
    public function __construct($from, $uid = null) {
        $this->from = implode('_', array_filter([ $from, $uid ]));
    }
    
    public function load() {
        return file_exists( $path = $this->path() ) ? include( $path ) : [];
    }
    
    public function save($array) {
        if ( file_put_contents($path = $this->path(), $this->generate_code($array)) ) {
            chmod($path, 0775);
        }
        else {
            $this->debug»error('Error trying to save to given path', [ $path ], __FUNCTION__, __LINE__);
        }
    }
    
    public function path($set = null, $root_dir = null) {
        if ( $set || $this->path ) {
            return $set === null ? $this->path : $this->path = $set;
        }
        
        $fallback = ( $root_dir ?: Eckinox::path_var() ).$this->base_dir.DIRECTORY_SEPARATOR;
        
        file_exists($fallback) || mkdir($fallback, 0777, true);
        
        # generate otherwise
        return $fallback.str_replace('\\', '_', $this->from).".php";
    }
    
    protected function generate_code($data) {
        return "<?php return ".var_export($data, true).";";
    }
}
