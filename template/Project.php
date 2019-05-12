<?php

namespace %NAMESPACE%;

use Eckinox\{
    autoload,
    singleton,
    Cron,
    Event,
    Language,
    Bootstrap,
    Annotation,
    Configuration
};

use Eckinox\Nex\{
    url_function,
    Nex,
    Application
};

class %PROJECTNAME% extends Application {
    use singleton, url_function;

    public function initialize() {
        Bootstrap::initialize();

        Language::current_language( $this->config('Eckinox.locale.lang') );

        Event::make()->trigger('eckinox.bootstrap.completed');
    }

    public function index($uri) {
        ( new Controller\Dashboard() )->index($uri);
    }

    public function migrate() {
        Event::make()->on('eckinox.migration.migrate', function() {
            Migrate\Migrate::instance()->autoload()->migrate();
        })->trigger('eckinox.migration.migrate');
    }

    public function launch(...$uri) {
        $callback_value = null;

        if ($uri) {
            $controller = $this->reflection['namespace']."\\Controller\\".implode('\\', array_map('ucfirst', explode('-', $uri[0])) );
            $controller = new $controller();
        }
        else {
            $controller = $this;
        }

        Event::instance()->on('%PROJECTNAME%.application.launch', function($e, &$frontend, &$uri) use (&$callback_value, &$controller) {

            if ( ! $e['return'] ) {
                $callback_value = ( $controller ?? new Controller\Dashboard() )->url_route(...$uri);
            }
            else {
                $callback_value = $e['return'];
            }

        })->trigger('%PROJECTNAME%.application.launch', $this, [ &$frontend, &$uri ] );

        return $callback_value;
    }

    public function cron() {
        $stack = [];
        $this->_load_frontend();

        foreach(Annotation::instance()->get_methods_list() as $class => $annotation) {
            foreach($annotation['methods'] ?? [] as $method => $content) {
                if ( $content['cron'] ?? false ) {
                    $cron = new Cron();

                    if ( is_string($content['cron']) ) {
                        $cron->setTab($content['cron']);
                    }
                    elseif ( is_array($content['cron']) ) {
                        if ($content['cron']['function'] ?? false) {
                            $cron->{$content['cron']['function']}();
                        }
                    }

                    $cron->run(function() use ( $class, $method ){
                        ( new $class() )->$method();
                    });
                }
            }
        }
    }
}
