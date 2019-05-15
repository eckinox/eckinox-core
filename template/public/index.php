<?php

namespace %NAMESPACE%;

use Eckinox\Eckinox,
    Eckinox\Nex,
    Eckinox\User,
    Eckinox\Nex_model_tools,
    Eckinox\Configuration,
    Eckinox\Annotation;

define("PUBLIC_DIR", __DIR__."/");
define("SRC_DIR", dirname(PUBLIC_DIR)."/");
define("VENDOR_SRC_DIR", SRC_DIR."vendor/");
define("FRAMEWORK_SRC_DIR", VENDOR_SRC_DIR."eckinox/");

$autoload = require_once(FRAMEWORK_SRC_DIR."core/autoload.php");

$autoload->register_namespace('Eckinox', FRAMEWORK_SRC_DIR."core/");
Eckinox::instance()->initialize();

$autoload->register_application('Eckinox', 'Nex', FRAMEWORK_SRC_DIR."nex/");
Nex\Nex::instance()->initialize();

try {
    $autoload->register_application('Eckinox', 'User', FRAMEWORK_SRC_DIR."user/");
    User\User::instance()->initialize();

    $autoload->register_application('Eckinox', 'Nex_model_tools', FRAMEWORK_SRC_DIR."nex-model-tools/");
    Nex_model_tools\Nex_model_tools::instance()->initialize();

    Configuration::instance()->load(SRC_DIR."env.json");

    $autoload->register_namespace('%NAMESPACE%', SRC_DIR);
    %PROJECTNAME%::instance()->initialize();
}
catch (\Throwable $e) {
    $html = new \Eckinox\Nex\Driver\ErrorHandler\Html(Configuration::get('Nex.errorhandler.html'));
    $html->error($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace());
}
