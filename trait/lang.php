<?php namespace Eckinox;

trait lang {
    
    public function lang(...$args) {
        static $vars_pattern = null;
        
        $vars_pattern || ( $vars_pattern = $this->config('Eckinox.language.vars.pattern') );
        
        switch ( count($args) ) {
            case 1:
                return ( $value = Language::get( $args[0] ) ) !== null ? $value : ( Eckinox::debug() ? "'{$args[0]}'" : "" );
                
            case 0:
                return Language::instance();

            case 2:
                if ( empty($args[1]) ) {
                    return $this->lang($args[0]);
                }
                
                /* Handling arrays with keys */
                if ( Arrayobj::array_is_associative($args[1]) ) {
                    $content = $this->lang($args[0]);
                    
                    if ( preg_match_all($vars_pattern, $this->lang($args[0]), $matches, PREG_SET_ORDER) ) {
                        $search = [];
    
                        foreach($matches as $item) { 
                            $search[ $item[0] ] = $a = iterate::array_get($args[1], $item[1]);
                        }
                        
                        $content = str_replace(array_keys($search), array_values($search), $content);
                    }
                    
                    return $content;
                }
            
            default:
                /* Handling arrays with indexes */
                return call_user_func_array('sprintf', array_merge([ $this->lang( array_shift($args) ) ], $args));
        }
    }
    
}