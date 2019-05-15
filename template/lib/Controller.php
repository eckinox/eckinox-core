<?php

namespace %NAMESPACE%;

class Controller extends \Eckinox\Nex\Basic_controller {

    protected $title = "";

    protected $page_limit = 50;

    protected function _search_fields($model) {
        return array_combine($model::SEARCH_FIELDS, array_map(function($item) {
            return $this->_("search.$item");
        }, $model::SEARCH_FIELDS));
    }
}
