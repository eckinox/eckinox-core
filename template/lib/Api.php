<?php

namespace %NAMESPACE%;

class Api extends Controller {

    public function __construct($locked = null) {
        parent::__construct($locked);

        if ( !$this->post() && ( $rawData = file_get_contents("php://input") ) ) {
            $_POST = json_decode($rawData, true);
        }
    }

}
