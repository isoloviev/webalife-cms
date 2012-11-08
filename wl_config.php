<?php
/**
 * Configuration settings
 */
define('DEFAULT_LANG', 'ru');
define('ADMIN_PSWRD', md5('121212'));
$SUPPORT_LANG = array('ru'=>'Русский', 'en'=>'English');

/**
 * Database connection settings
 */
define('PREFIX', 'wl_');
define('DB_HOST', 'localhost');
define('DB_NAME', 'webalife_cms');
define('DB_USERNAME', 'root');
define('DB_USERPWRD', '');

/**
 * Paths
 */
define('CMS_ROOT_DIR', $_SERVER['DOCUMENT_ROOT'] . "/");
define('CMS_TEMPLATE_PATH', CMS_ROOT_DIR . 'webalife/templates/');
define('CMS_MODULES', CMS_ROOT_DIR . 'webalife/modules/');
define('CMS_CLASSES', CMS_ROOT_DIR . 'webalife/classes/');
define('CMS_LANGUAGES', CMS_ROOT_DIR . 'webalife/langs/');
define('CMS_RUNTIME_PATH', CMS_ROOT_DIR . 'webalife/runtime/');
define('CMS_RUNTIME_URL', '/webalife/runtime/');
define('SMARTY_DIR', CMS_ROOT_DIR . 'webalife/Smarty/libs/');
