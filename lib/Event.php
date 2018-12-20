<?php

namespace Eckinox;

class Event {
    use singleton;
    
    protected $list = [];
    
    protected $ran  = [];
    
    protected $latest_key;
    
    protected $latest_event = null;
    
    public function on($name, $callback, $arguments = [], $index = null) {
        isset($this->list[$name]) || ( $this->list[$name] = [] );
        
        /* if ( in_array(array($callback, $callback_args), $this->list[$name], true) ) {
            return false;
        } */
        
        $this->latest_key = $name;
        
        $obj = [ 
            'callback'  => $callback, 
            'arguments' => (array)$arguments 
        ];
        
        if ($index !== null) {
            $this->list[$name][$index] = $obj;
        }
        else {
            $this->list[$name][] = $obj;
        }

        
        return $this;
    }
    
    public function off($name, $index = null) {
/*      $remove = [];
                     
        foreach ($this->list[$name] ?? [] as $key => $value) {                
            if ( $callback === $value['callback'] && $arguments === $value['arguments']) {
                $remove[] = $key;
            }
        } */
        if ( $index !== null ) {
            unset($this->list[$name][$index]);
        }
        else {        
            $this->list[$name] = [];
        }
        
        return $this;
    }
    
    public function purge($key) {
        unset($this->list[$key]);
        return $this;
    }

    
    public function trigger($key = "", & $caller = null, $arguments = [], $reverse = false) {
        $key || ( $key = $this->latest_key );
        
        if ( $e = $this->event($key) ) {
            $event_obj = new Event_obj($key, $arguments, $caller);

            if ($reverse) {
                $e = array_reverse($e, true);
            }
            
            foreach($e as $index => $item) {
                $event_obj->call( $item['callback'], array_merge($arguments, $item['arguments']), $index );
            
                if ( ! $event_obj->propagation() ) {
                    break;
                }
            }
            
            $this->latest_event = $this->ran[$key] = [
                'obj' => $caller,
                'event_obj' => $event_obj,
                'arguments' => $arguments
            ];
        }
        else {
            $this->latest_event = null;
        }
        
        return $this;
    }
    
    public function & event($key = null) {
        if ( $key === null ) {
            return $this->list;
        }
        elseif (isset( $this->list[$key] )) {
            return $this->list[$key];
        }
        
        $retval = false;
        return $retval;
    }

    public function triggered($key) {
        return !empty($this->ran[$key]);
    }
    
    public function event_object() {
        return $this->latest_event['event_obj'];
    }
}