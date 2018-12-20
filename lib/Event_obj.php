<?php

namespace Eckinox;

class Event_obj extends Arrayobj {
 
    private $args;
    
    private $event_type;
    
    private $obj;
    
    private $callback_return;
    
    protected $propagate = true;
    
    protected $item_index = null;

    public function __construct($event_type, $args = [], & $obj = null) {
        parent::__construct();
        
        $this->event_type = $event_type;
        $this->args = $args;
        $this->obj  = & $obj;
    }
    
    public function call($callback, $arguments, $index) {
        $this->item_index = $index;
        return $this->callback_return[] = call_user_func_array($callback, array_merge([ $this ], $arguments, $this->args));
    }

    public function get_type() {
        return $this->event_type;
    }
    
    public function off() {
        Event::instance()->off($this->event_type, $this->item_index);
        return $this;
    }
    
    public function & arguments($key = null) {
        return $key ? $this->args[$key] : $this->args;   
    }
    
    public function & caller() {
        return $this->obj;
    }
    
    public function propagation($set = null) {
        return $set === null ? $this->propagate : $this->propagate = $set;
    }
    
    public function stop_propagation() {
        return $this->propagation(false);
    }
}