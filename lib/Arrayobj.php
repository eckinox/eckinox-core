<?php

namespace Eckinox;

class Arrayobj implements \ArrayAccess, \Iterator {
    private $container = [];

    public static function make(...$args) {
        return new static(...$args);
    }

    public function __construct($default = null) {
        if (is_array($default)) {
            $this->container = array_merge($this->container, $default);
        }
    }

    public function replace($array, $recursive = false) {
        $this->container = $recursive ? array_replace_recursive($this->container, $array) : array_replace($this->container, $array);
        return $this;

    }
    public function merge($array, $recursive = false)   {
        $this->container = $recursive ? array_merge_recursive($this->container, $array) : array_merge($this->container, $array);
        return $this;
    }

    public function push(...$values) {
        array_push($this->container, ...$values);
        return $this;
    }

    public function pop($return_value = true) {
        $return_value ? ( $item = array_pop($this->container) ) : array_pop($this->container);
        return $return_value ? $item : $this;
    }

    public function unshift(...$values) {
        array_unshift($this->container, ...$values);
        return $this;
    }

    public function shift($return_value = true) {
        $return_value ? ( $item = array_shift($this->container) ) : array_shift($this->container);
        return $return_value ? $item : $this;
    }

    public function map($callback, ...$args) {
        $this->container = call_user_func_array('array_map', array_merge( [ $callback , $this->container ], $args) );
        return $this;
    }

    public function filter(callable $callback = null, int $flag = 0, $recursive = false) {

        $filtering = function()use ($callback, $flag) {
            return $callback ? array_filter($this->container, $callback, $flag) : array_filter($this->container);
        };

        if (!$recursive) {
            $this->container = $filtering($this->container);
        }
        else {
            $recursivity = function(&$array) use ($callback, $flag, $filtering, &$recursivity) {

                /* TODO ! Fix that func! */
                foreach($array as &$item) {
                    if ( is_array($item) ) {
                        $item = $recursivity($item);
                    }
                    else {
                        $item = $filtering($item);
                    }
                }

                $array = $filtering($array);
            };

            $recursivity($this->container);

            return $callback ? $this->filter($callback, $flag, false) : $this->filter();
        }

        return $this;
    }
    public function contains($term) {
        return (array_search($term, $this->container) !== false) ;
    }

    public function toArray() {
        return $this->container;
    }

    public function count() { return count($this->container); }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    public function exist($offset)  { return $this->offsetExists($offset); }
    public function exists($offset) { return $this->offsetExists($offset); }

    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->container[$offset]);
    }

    public function &offsetGet($offset) {
        if (!isset($this->container[$offset])) {
            $this->container[$offset] = null;
        }

        return $this->container[$offset];
    }

    public function flush() {
        unset($this->container);
        $this->container = [];
    }

    public function rewind()  {
        reset($this->container);
    }

    public function current() {
        $var = current($this->container);
        return $var;
    }

    public function key() {
        $var = key($this->container);
        return $var;
    }

    public function next() {
        $var = next($this->container);
        return $var;
    }

    public function implode($glue) {
        return implode($glue, $this->container);
    }

    public function ternary($var, $value) {
        return $this->exist($var) ? $this->offsetGet($var) : ( is_function($value) ? $value() : $value ) ;
    }

    public function if_has($var, $value) {
        return $this->exist($var) && $this->offsetGet($var) ? ( is_function($value) ? $value() : $value ) : "";
    }

    public function mandatory($var) {

        foreach((array)$var as $item) {
            if (!$this->exists($item)) {
                Debug::critical("A mandatory variable ($item) is not available into current array stack", [@func_get_args(), $this->container], __FUNCTION__, __LINE__);
            }
        }
        return $this[$var];
    }


    public static function array_diff_ex( $array, $remove ) {
        foreach ( $remove as $key => $value ) {
            if ( isset($array[$key]) ) {
                unset($array[$key]);
            }
        }

        return $array;
    }
    
    public static function order_by(&$array, $key, $func = "uasort") {
        $func($array, function($v1, $v2) use ($key) {
            return $v1[$key] <=> $v2[$key];
        });

        return $array;
    }

    public static function is_iterable( $obj ) {
        return is_array($obj) || ($obj instanceof \Traversable);
    }

    public static function array_associative_keys($array) {
        return array_filter(array_keys($array), 'is_string');
    }

    public static function array_is_associative($array) {
        return is_array($array) && !empty($array) && ( count( static::array_associative_keys($array) ) === count($array) );
    }

    public static function array_is_partly_associative($array) {
        return is_array($array) && !empty($array) && ( (bool)count(static::array_associative_keys($array) ) );
    }

    public static function array_is_mixed($array) {
        return is_array($array) && !empty($array) && ( count( static::array_associative_keys($array)) !== count($array) );
    }

    public static function array_is_indexed($array) {
        return is_array($array) && !empty($array) && ( count(static::array_associative_keys($array) === 0 ) );
    }

    # http://stackoverflow.com/a/23299766
    public static function array_change_key_case_recursive( $arr, $case = CASE_LOWER ) {
        return array_map(function($item)use($case) {
            if ( is_array($item) ) {
                $item = static::array_change_key_case_recursive($item, $case);
            }
            return $item;
        }, array_change_key_case($arr, $case));
    }

    public static function array_flatten($source) {
        return iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveArrayIterator($source)), false);
    }

    public static function array_object_func_call($array_object, $function_name) {
        $retval = [];

        foreach($array_object as $item) {
            $retval = array_merge($retval, array_filter((array)$item->$function_name()));
        }

        return $retval;
    }

    # http://stackoverflow.com/questions/96759/how-do-i-sort-a-multidimensional-array-in-php/16788610#16788610
    /* exemple :
     * $data = array(
        array('zz', 'name' => 'Jack', 'number' => 22, 'birthday' => '12/03/1980'),
        array('xx', 'name' => 'Adam', 'number' => 16, 'birthday' => '01/12/1979'),
        array('aa', 'name' => 'Paul', 'number' => 16, 'birthday' => '03/11/1987'),
        array('cc', 'name' => 'Helen', 'number' => 44,'birthday' => '24/06/1967'),
      );

        usort($data, make_comparer( ['number', SORT_DESC], ['birthday', SORT_ASC, 'date_create'] ));

    */
    public static function make_comparer() {
        // Normalize criteria up front so that the comparer finds everything tidy
        $criteria = func_get_args();
        foreach ( $criteria as $index => $criterion ) {
            $criteria[$index] = is_array($criterion) ? array_pad($criterion, 3, null) : array($criterion, SORT_ASC, null);
        }

        return function($first, $second) use (&$criteria) {
            foreach ( $criteria as $criterion ) {
                // How will we compare this round?
                list($column, $sortOrder, $projection) = $criterion;
                $sortOrder = $sortOrder === SORT_DESC ? -1 : 1;

                // If a projection was defined project the values now
                if ( $projection ) {
                    $lhs = call_user_func($projection, $first[$column] ?? 0);
                    $rhs = call_user_func($projection, $second[$column] ?? 0);
                }
                else {
                    $lhs = $first[$column] ?? 0;
                    $rhs = $second[$column] ?? 0;
                }

                // Do the actual comparison; do not return if equal
                if ( $lhs < $rhs ) {
                    return -1 * $sortOrder;
                }
                else if ( $lhs > $rhs ) {
                    return 1 * $sortOrder;
                }
            }

            return 0; // tiebreakers exhausted, so $first == $second
        };
    }

    public function valid() {
        $key = key($this->container);
        $var = ($key !== NULL && $key !== FALSE);
        return $var;
    }

    public function each($func) {
        foreach($this->container as &$item) {
            $func($item);
        }
    }

    public static function search_in_array( $needle, $array, $specific_key = null ) {
        if ( is_array($array) ) {
            if ( $specific_key ) {
                return isset($array[$specific_key]) && $array[$specific_key] == $needle ? $array : null;
            }
            else {
                foreach ( $array as $key => $value ) {
                    if ( is_array($value) ) {
                        return search_in_array($needle, $value, $specific_key);
                    }
                    else {
                        if ( is_string($value) && strtolower($needle) == strtolower($value) ) {
                            return $array;
                        }
                        if ( $needle == $value ) {
                            return $array;
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * Remove each occurences of a given value
     *
     * @param type $array
     * @param type $val
     * @param type $strict
     * @return type
     */
    function array_remove_value( &$array, $val, $strict = false ) {
        $remove = [];

        foreach ( $array as $key => $value ) {
            if ( $strict ? $value === $val : $value == $val ) {
                $remove[] = $key;
            }
        }

        foreach($remove as $item) {
            array_splice( $array, $item, 1 );
        }

        return $remove ?: false;
    }

    public static function key_exist($key_list, $array) {
        if ( isset($array[$current_key = array_shift($key_list)]) ) {
            if ($key_list) {
                return static::iterate($key_list, $array[$current_key]) !== null;
            }
            else {
                return isset($array[$current_key]);
            }
        }

        return false;
    }

    public static function iterate($key_list, $array) {

        if ( isset($array[$current_key = array_shift($key_list)]) ) {
            if ($key_list) {
                return static::iterate($key_list, $array[$current_key]);
            }
            else {
                return $array[$current_key];
            }
        }

        return null;
    }

    public static function iterate_set($key_list, &$array, $value, $append = false) {
        $current_key = array_shift($key_list);

        if ($key_list) {
            return static::iterate_set($key_list, $array[$current_key], $value, $append);
        }
        else {
            if ( $append && isset($array[$current_key]) ) {
                if (is_array($array[$current_key])) {
                    return $array[$current_key] = array_merge_recursive($value, $array[$current_key]); #: array_replace_recursive($value, $array[$current_key]);
                }
                else {
                    return $array[$current_key] = $array[$current_key] . $value;
                }
            }
            else {
                return $array[$current_key] = $value;
            }
        }

        return false;
    }

    public static function split_keys(&$array, $delimiter = "»") {
        if (is_array($array)) {
            $swap = [];

            foreach($array as $key => &$value) {
                if ( Stringobj::make($key)->contains($delimiter ?: ALIVE_SEPARATOR) ) {
                    $tmp = [];
                    $keylist = Util::getKeyList($key, true);
                    $final_key = array_shift($keylist);
                    Arrayobj::iterate_set($keylist, $tmp, $value);
                    $swap[$final_key] = static::split_keys($tmp, $delimiter);
                    unset($array[$key]);

                }
                else if ( is_array($value) ) {
                    $value = static::split_keys($value, $delimiter);
                }
            }

            foreach($swap as $key => $item) {
                $array[$key] = $item;
            }
        }

        return $array;
    }

    public static function array_filter_string($array) {
        return array_filter($array, function($item) {
            return !empty($item) || $item !== "";
        });
    }

    public static function array_remove(array $array, $value, $strict = false) {
        return array_diff_key($array, array_flip(array_keys($array, (array)$value, $strict)));
    }
}
