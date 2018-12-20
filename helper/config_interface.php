<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Eckinox;

/**
 *
 * @author mcnd
 */
abstract class config_interface {
    protected $name   = "";
    protected $accept = [];
    
    public function translate($filepath) {}
    
    public function name() {
        return $this->name;
    }
    
    public function accept($file) {
        return in_array(strtolower( pathinfo($file, PATHINFO_EXTENSION) ), $this->accept);
    }
}
