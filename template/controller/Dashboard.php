<?php

namespace %NAMESPACE%\Controller;

class Dashboard extends \%NAMESPACE%\Controller {
    /**
     *   @route "/"
     *   @name "home"
     *   @breadcrumb "parent": false, "icon" : "home", "lang" : "%namespace%.dashboard.breadcrumb.index"
     */
    public function index($uri = []) {
        return $this->render('page/dashboard/index');
    }
}
