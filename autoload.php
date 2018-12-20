<?php namespace Eckinox;

const CONFIG_DIR    = 'config/' ;
const LANG_DIR      = 'lang/';
const BOOT_DIR      = 'boot/' ;
const LIB_DIR       = 'lib/' ;
const BLOCK_DIR     = 'block/' ;
const CTRL_DIR      = 'controller/' ;
const API_DIR       = 'api/' ;
const MIGRATE_DIR   = 'migrate/' ;
const MODEL_DIR     = 'model/' ;
const HELPER_DIR    = 'helper/' ;
const TRAIT_DIR     = 'trait/' ;
const TESTING_DIR   = 'testing/';

const VAR_DIR       = 'var/';
const MIGRATION_DIR = 'migration/';
const TMP_DIR       = 'tmp/';
const CACHE_DIR     = 'cache/' ;

const COMPONENT_DIR = 'component/' ;
const MODULE_DIR    = 'module/' ;
const PHP_EXT       = '.php';

final class autoload {
    const COMPONENT = 1;
    const MODULE    = 2;

    public $ns_stack  = [];
    public $app_stack = [];

    final public static function instance(...$arguments) {
        static $self = null;

        if ( !isset($self) ) {
            $self = new static(...$arguments);
        }

        return $self;
    }

    public function register_namespace($name, $dir, $boot = true) {
        $this->ns_stack[$name] = [
            'dir'  => $dir
        ];

        if ( $boot ) {
            $this->_require_file($dir.$name.PHP_EXT);
            $this->_boot_component($dir);
        }

        $this->app_stack[$name] = [];
   }

    public function register_application($namespace, $application, $from = self::COMPONENT) {
        # Registering an application twice is not
        if ( $this->app_stack[$namespace][$application] ?? false ) return;

        if ($from === self::COMPONENT) {
            $dir = $this->ns_stack[$namespace]['dir'].($from === static::COMPONENT ? COMPONENT_DIR : MODULE_DIR).$application.DIRECTORY_SEPARATOR;
        }
        else {
            $dir = $from;
        }

        $this->app_stack[$namespace][$application] = [
            'dir' => $dir
        ];

        $this->register_namespace($namespace."\\".$application, $dir, false);
        $this->_require_file($dir.$application.PHP_EXT);
        $this->_boot_component($dir);

        return true;
    }

    public function load($class) {
         $dir_segment = [];
         $class_split = explode('\\', $class);
         $namespace = array_shift($class_split);
         $classname = array_pop($class_split);

         array_unshift($class_split, $namespace);

        while ($class_split) {
            # Trying to find a matching component / module based on namespace
            if ( isset( $this->ns_stack[ $ns = implode("\\", $class_split) ]) ) {
               $component = $this->ns_stack[$ns];

               $this->_load_from($component['dir'], $classname, $dir_segment, $class);
               return true;
            }

            array_unshift($dir_segment, array_pop($class_split));
        }
    }

    protected function __construct() {
        spl_autoload_register([$this, 'load']);
    }

    protected function _load_from($dir, $class, $subfolder = [], $fullname = null) {
        $segment = "";

        # Uppecase -> Lib / Model / Controllers
        if ( ctype_upper($class[0]) ) {
            if ( ( $type = $this->_get_class_type_dir($subfolder) ) !== LIB_DIR ) {
                $segment = array_shift($subfolder);
            }

            if ( ! $this->_require_file( $ex = $this->_build_path($dir, $class, $subfolder, $type) ) ) {
                array_unshift($subfolder, $segment);

                if ( ! $this->_require_file( $ex = $this->_build_path($dir, $class, $subfolder, LIB_DIR) ) ) {
                    trigger_error("Library '$class' not found ", \E_USER_ERROR);
                }
            }
        }
        else {
            # Lowercase -> helper or trait
            if ( !($this->_require_file( $this->_build_path($dir, $class, $subfolder, HELPER_DIR)) || $this->_require_file($this->_build_path($dir, $class, $subfolder, TRAIT_DIR))) ) {
                trigger_error("Helper or Trait '$class' not found", \E_USER_ERROR);
            }

        }
    }

    protected function _get_class_type_dir($subfolder) {

        if ( $subfolder ) {
            switch( strtolower( $subfolder[0] ) ) {
                case 'model':
                    return MODEL_DIR;

                case 'controller':
                    return CTRL_DIR;

                case 'api':
                    return API_DIR;

                case 'migrate':
                    return MIGRATE_DIR;

                case 'block':
                    return BLOCK_DIR;
            }
        }

        return LIB_DIR;
    }

    protected function _build_path($dir, $class, $subfolder, $type) {
        $subfolder = is_array($subfolder) && $subfolder ? implode(DIRECTORY_SEPARATOR, $subfolder) . DIRECTORY_SEPARATOR : "";
        $output = $dir.$type.$subfolder.$class.PHP_EXT;
        return $output;
    }

    protected function _require_file($file) {
        if ( $retval = file_exists($file) ) {
            require $file;
        }

        return $retval;
    }

    protected function _boot_component($dir) {
        foreach(iterate::files($dir.BOOT_DIR, "php") as $item) {
           $this->_require_file( $item );
        }
    }
}

return autoload::instance();
