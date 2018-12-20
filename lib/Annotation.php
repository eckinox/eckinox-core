<?php

namespace Eckinox;

class Annotation extends Component {
    use singleton, config;

    protected $persistent;
    protected $annotations = [];
    protected $updated_data = false;

    protected function __construct() {
        $this->persistent = new Persistent(static::class);
        $this->annotations = Eckinox::debug() ? [] : $this->persistent->load();

        if ( ! $this->annotations() ) {
            $this->autoload();
        }
    }

    public function __destruct() {
        $this->update_data() && $this->persistent->save($this->annotations());
    }

    public function update_data() {
        return $this->updated_data || Eckinox::debug();
    }

    public function get_class_methods($class) {
        return $this->annotations[$class] ?? false;
    }

    public function get_methods_list() {
        return $this->annotations;
    }

    public function annotations($set = null) {
        return $set === null ? $this->annotations : $this->annotations = $set;
    }

    public function get_from_object($obj) {
        return $this->get_class_methods(get_class($obj));
    }

    public function get_from_method_name($name) {
        foreach($this->get_methods_list() as $class => $annotation) {
            foreach($annotation['methods'] ?? [] as $method => $item) {
                if ( ( $item['name'] ?? false ) === $name ) {
                    return array_merge([ 'object' => $class, 'method' => $method ], $item);
                }
            }
        }
    }

    public function autoload() {
        $stack = [];
        $autoload = $this->config('Eckinox.annotation.autoload');

        foreach( autoload::instance()->ns_stack as $key => $value ) {
            foreach($autoload as $item) {
                foreach((array) $item['file_extension'] as $ext) {
                    foreach(iterate::files($value['dir'].$item['search'], $ext) as $file) {
                        $classname = str_replace('/', '\\', substr($file, strpos($file, $item['search']) + strlen($item['search']), -4));

                        if ( $class = $this->load_class($key, $classname, $item['namespace']) ) {
                            $stack = array_merge($stack, $class);
                            $this->updated_data = true;
                        }
                    }
                }
            }
        }

        return $this->annotations( $stack );
    }

    public function load_class($ns, $name, $prefix) {
        $classname = implode('\\', [ $ns, $prefix, $name ]);
        $methods = Reflection::instance()->reflect($classname)['methods'];

        foreach($methods as $item) {
            if ( $comment = (new \ReflectionMethod($classname, $item))->getDocComment()) {
                $annotations['methods'][$item] = $this->parse($comment, $classname);
            }
        }

        $annotations['type'] = strtolower($prefix);
        $annotations['class'] = $this->parse( (new \ReflectionClass($classname))->getDocComment(), $classname);

        return [ $classname => $annotations ?? [] ];
    }

    protected function parse($doccomment, $classname = null) {
        $ignore = $this->config('Eckinox.annotation.ignore');
        $stack  = [];
        $status = null;
        $json_opened = false;

        $decode_json = function($function) use (&$json_opened, &$stack, $classname) {
            if ( $json_opened && $function ) {
                $stack[$function] = json_decode($json_opened, true);

                if (json_last_error()) {
                     trigger_error("An error occured parsing [$classname]: $json_opened");
                }

                $json_opened = false;
            }
        };

        foreach(preg_split("/\r\n|\n|\r/", $doccomment) as $item) {
            $line = ltrim($item, "* \t\/");

            if ( ! $line ) continue;

            if ( substr($line, 0, 1) === '@' ) {
                $decode_json($function ?? false);

                list($function, $arguments) = array_map('trim', array_pad(explode(' ', substr($line, 1), 2), 2, null));

                if ( in_array($function, $ignore ) ) {
                    continue;
                }

                if ( $arguments === '{' ) {
                    $json_opened = '{';
                    continue;
                }

                if ( strpos($arguments, ':') !== false && strpos($arguments, '"') !== false ) {
                    $arguments = "{ $arguments }";
                }

                $stack[$function] = json_decode($arguments, true);

                if (json_last_error()) {
                     trigger_error("An error occured parsing: $doccomment");
                }
            }
            elseif ( $json_opened ) {
                $json_opened .= trim($line);
            }
            elseif ( substr($line, 0, 1) === '#' ) {
                # we're in a comment
                continue;
            }
        }

        $decode_json($function ?? false);

        return $stack;
    }
}
