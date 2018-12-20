<?php

namespace Eckinox;

class Exception extends \Exception {

    /**
     * Make a new Core Exception with the given result.
     * @param array $result
     */
    public function __construct($msg, $code = 0) {
        parent::__construct($msg, $code);
    }

    /**
     * To make debugging easier.
     * @returns string
     */
    public function __toString() {
        $str = '';
        if ($this->code != 0) {
            $str .= $this->code . ': ';
        }
        return $str . $this->message;
    }

}
