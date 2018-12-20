<?php

namespace Eckinox;

class Reflection {
    use singleton;
    
    protected $persistent;
    protected $data = [];
    protected $updated_data = false;
    
    protected function __construct() {
        $this->persistent = new Persistent(static::class);
        $this->data = $this->persistent->load();   
    }
    
    public function __destruct() {
        $this->update_data() && $this->persistent->save($this->data);
    }
    
    public function reflect($name, $obj = null) {
        if ( empty($this->data[$name]) || Eckinox::debug() ) {
            $this->updated_data = true;
            $this->data[$name] = $this->analyze($obj ?: $name);
        }
        
        return $this->data[$name];
    }
    
    public function update_data() {
        return $this->updated_data || Eckinox::debug();
    }
    
    public function analyze($obj) {
        $ref = new \ReflectionClass($obj);
        
        return [
            'name'       => $n = is_string($obj) ? $obj : \get_class($obj),
            'classname'  => substr($n, strrpos($n, "\\") + 1),
            'namespace'  => $ns = $this->_get_real_namespace($obj),
            'ns_root'    => ($root = strpos($ns, "\\")) > 0 ? substr($ns, 0, $root) : $ns,
            'traits'     => array_map(function($item) { return substr($item, strrpos($item, "\\") + 1); }, $this->_class_uses_deep($obj)),
            'properties' => array_map(function($item) { return $item->name; }, $ref->getProperties()),
            'methods'    => array_map(function($item) { return $item->name; }, $ref->getMethods()),
            'doccomment' => $ref->getDocComment(),
            'dir'        => dirname($ref->getFileName()),
            'component'  => apps::component_of($obj)
        ];
    }

    public function functions($obj) {
        return get_class_methods($obj);
    }

    function _class_uses_deep($class, $autoload = true) {
        $traits = [];

        // Get traits of all parent classes
        do {
            $traits = array_merge(class_uses($class, $autoload), $traits);
        } while ($class = get_parent_class($class));

        // Get traits of all parent traits
        $traitsToSearch = $traits;

        while (!empty($traitsToSearch)) {
            $newTraits = class_uses(array_pop($traitsToSearch), $autoload);
            $traits = array_merge($newTraits, $traits);
            $traitsToSearch = array_merge($newTraits, $traitsToSearch);
        }

        foreach ($traits as $trait => $same) {
            $traits = array_merge(class_uses($trait, $autoload), $traits);
        }

        return array_unique($traits);
    }

    protected function _get_real_namespace($class, $separator = '\\') {
        $classname = is_string($class) ? $class : get_class($class);
        $ns = explode('\\', $classname);
        array_pop($ns);

        # Will return the whole array if $separator = false
        return (is_bool($separator) && !$separator) ? $ns : implode($separator, $ns);
    }
}
