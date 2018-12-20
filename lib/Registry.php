<?php

namespace Eckinox;

class Registry {
    use singleton;
    
    protected function __construct() {}

    protected $data = [];

    public function has($key) {
        return isset($this->data[$key]);
    }
    
    public function get($key, $default = null) {
        return $this->has($key) ? $this->data[$key] : $default;
    }

    public function &getByRef($key) {
        return $this->data[$key];
    }

    public function set($key, $value) {
        return $this->data[$key] = $value;
    }

    public function setByRef($key, &$value) {
        return $this->data[$key] = $value;
    }

    public function delete($key) {
        unset($this->data[$key]);
    }
}
