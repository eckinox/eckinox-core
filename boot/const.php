<?php namespace Eckinox;

const NAMESPACE_SEPARATOR = "\\";

// Directories
define('APP_PATH'       , 'app/'); // Applications, this is where you code
define('CONF_PATH'      , 'etc/'); // Main system configuration, this is where you declare your apps and base functionnalities
define('VAR_PATH'       , 'var/'); // System files like logs, error templates, etc
define('BOOT_PATH'      , 'boot/'); // System boot sector, no touch
define('PUB_PATH'       , 'skin/'); // Public files related to your apps like images, css, scripts. Accessible by http
define('EXT_PATH'       , 'ext/'); // Packages, class, anything that has nothing to do with this framework
define('MEDIA_PATH'     , 'media/'); // Files uploaded by users. Accessible by http
define('TMP_PATH'       , 'tmp/'); // System temporary files like cache
define('I18N_PATH'      , 'i18n/'); // System internationnalization

// Early timezone to avoid strict error in system setup
// Can be redefined later by config file
define('NEX_TZ', 'America/Montreal');

// Define the front index name and docroot
define('DOC_ROOT', dirname(realpath(__FILE__)).DIRECTORY_SEPARATOR);    // will output something like : /home/sas/    or C:\wamp\www\sas\
define('SCRIPT_ROOT', getcwd().DIRECTORY_SEPARATOR); // Same as DOC_ROOT but path is defined depending of where the include/require came from. Will be the same as DOC_ROOT when index.php is called directly
define('NEX',  basename(__FILE__));               // will output something like : index.php


// change to the real docroot if external script allows it or if front controller is a symlink
if( is_link(NEX) or SCRIPT_ROOT != DOC_ROOT and ( !defined("NEX_REAL_SCRIPT_ROOT") or NEX_REAL_SCRIPT_ROOT == false ) ) chdir(DOC_ROOT);

// Config Translator
define('NEX_CONFIG_TRANSLATOR', DOC_ROOT.BOOT_PATH.'translator/xml_translator'.PHP_EXT);
define('NEX_LANG_TRANSLATOR', DOC_ROOT.BOOT_PATH.'translator/csv_translator'.PHP_EXT);

// Nex Internal cache
define('NEX_INTERNAL_CACHE', '_NEX_cache');

// Char definitions
define('NEX_COMPAT', "'");
define('NEX_QUOTES', '"');
define('NEX_NO_QUOTES', '');
define('NEX_BACKTICK', '`');
define('NEX_EOL', PHP_EOL);
define('NEX_MAIL_EOL', NEX_EOL);