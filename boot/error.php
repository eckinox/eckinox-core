<?php namespace Eckinox;

define('NEX_E_EMPTY_INCLUDE', 1001); // Empty include path
define('NEX_E_TRANSLATOR_PARSER', 1002); // Error in translator parser

define('NEX_E_CLASS_LOAD', 1010); // Could not load Class
define('NEX_E_MODEL_LOAD', 1011); // Could not load Model
define('NEX_E_CONFIG_LOAD', 1012); // Could not load Config
define('NEX_E_LIB_LOAD', 1013); // Could not load librairie
define('NEX_E_HELPER_LOAD', 1014); // Could not load helper
define('NEX_E_LAYOUT_LOAD', 1021); // Could not load Layout
define('NEX_E_VIEW_LOAD', 1022); // Could not load view

define('NEX_E_APP_MAIN_LOAD', 1051); // Could not load application main

define('NEX_E_LAYOUT_ROOT_EXIST', 1101); // Root block doesnt exist in layout
define('NEX_E_LAYOUT_BLOCKNAME_EXIST', 1102); // Block name doesn't exist in layout
define('NEX_E_APP_EXIST', 1111); // Application doesnt exist
define('NEX_E_MODULE_EXIST', 1112); // Application Module doesnt exist

define('NEX_E_DATABASE_CONNECT', 1201); // Error while connecting to database
define('NEX_E_DATABASE_SELECT', 1202); // Error while selecting database
define('NEX_E_DATABASE_QUERY_COMPILE', 1211); // Error while compiling database query
define('NEX_E_DATABASE_QUERY_EXECUTE', 1212); // Error while executing database query

define('NEX_E_METHOD_EXIST', 1300); // Method doesn't exist
