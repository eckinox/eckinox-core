<?php

namespace Eckinox;

abstract class Bootstrap {
    use config, lang;

    const IS_NAMESPACE = 00;
    const IS_COMPONENT = 10;
    const IS_MODULE    = 20;

    public static $apps = [];
    public static $directory = [];
    public static $keyname   = [];

    protected $type = null;

    protected $reflection = null;

    protected $component_of = "";
    protected $module_of    = "";

    public $object_path = "";

    protected function __construct() {}

    public function initialize() {
        Event::make()->trigger( $this->_event_key('initialize.begin') );

        $this->reflection = Reflection::instance()->reflect(static::class, $this);
        $this->object_path = implode('.',apps::get_object_path($this, true));

        static::$directory[ $this->object_path ] = $this->reflection['dir'] . "/";
        static::$keyname[ strtolower($this->reflection['classname']) ] = $this->reflection['classname'];

        $this->_load_configs(static::$directory[  $this->object_path ].CONFIG_DIR);
        $this->_load_langs(static::$directory[  $this->object_path ].LANG_DIR);
        $this->_load_component();
        $this->_load_module();
        $this->_internal_classification();

        Event::make()->trigger( $this->_event_key('initialize.done') );
        return $this;
    }

    public function get_type() {
        if ( $this->type !== null ) return $this->type;

        # Order is important here!
        switch(true) {
            /*case $this instanceof Module:
                return static::IS_MODULE;*/

            case $this instanceof Component:
                return static::IS_COMPONENT;
        }

        return static::IS_NAMESPACE;
    }

    public function component_of() {
        return $this->component_of ?: $this->component_of = apps::component_of($this);
    }

    public function module_of() {
        return $this->module_of ?: $this->module_of = apps::module_of($this);
    }

    public function path() {
        return static::$directory[  $this->object_path ];
    }

    protected function _load_configs($path = null) {
        #Event::make()->on( $this->_event_key('load_config'), function($e) {
            /*$e['stop'] ??*/ Configuration::instance()->load($path);
        #})->trigger('load_config');
    }

    protected function _load_langs($path = null) {
        #Event::make()->on( $this->_event_key('load_config'), function($e) {
            /*$e['stop'] ??*/ Language::instance()->load($path, true);
        #})->trigger('load_config');
    }

    protected function _load_component() {
        Event::make()->trigger($this->_event_key('load_component.begin'));

        foreach($this->_list_of('component') as $key => $value) {
            if ( $value['autoload'] ?? false ) {
                autoload::instance()->register_application($this->reflection['namespace'], $key);
            }
        }

        Event::make()->trigger($this->_event_key('load_component.done'));
    }

    protected function _load_module() {
        Event::make()->trigger($this->_event_key('load_module.begin'));

        foreach($this->_list_of('module') as $key => $value) {
            if ( $value['autoload'] ?? false ) {
                autoload::instance()->register_application($this->reflection['namespace'], $key, MODULE_DIR);
            }
        }

        Event::make()->trigger($this->_event_key('load_module.done'));

    }

    protected function _list_of($key) {
        $list = (array) $this->config("{$this->reflection['classname']}.$key");
        uasort($list, function($a, $b) { return $a['priority'] > $b['priority']; });
        return $list;
    }

    protected function _internal_classification() {
        $this->type = $this->get_type();

        if ( $parent = $this->component_of($this) ) {
            switch( $this->get_type() ) {
                case static::IS_COMPONENT:
                    static::$apps[ array_shift($parent) ]['component'][$this->reflection['classname']] = [
                        'object'    => $this,
                        'component' => [],
                        'module'    => []
                    ];
            }

        }
        else {
            static::$apps[ strtolower( $this->reflection['classname'] ) ] = [
                'object'    => $this,
                'component' => [],
                'module'    => []
            ];
        }

    }

    protected function _event_key($name) {
        return 'application.'.strtolower($this->reflection['classname']).".$name";
    }
}
