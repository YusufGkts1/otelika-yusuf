<?php
// BASES
define('DOMAIN', explode('.', $_SERVER['HTTP_HOST'])[0]); // sub
define('MAIN_DOMAIN', str_replace(DOMAIN . '.', '', $_SERVER['HTTP_HOST'])); // domain.com
define('DOCUMENT_ROOT', realpath(dirname(__FILE__))); // ../../sub.domain.com
$temp = explode('/', DOCUMENT_ROOT); // supress warning
define('DOCUMENT_PATH', end($temp)); //

$is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
$is_https = $is_https || $_SERVER['SERVER_PORT'] == 443;
$is_https = $is_https || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https');

define('HTTP_SERVER', ($is_https ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/' . DOCUMENT_PATH . '/'); // https://sub.domain.com/v1/

// COMMON DIR
define('DIR_APPLICATION', DOCUMENT_ROOT . '/');
define('DIR_SYSTEM', DIR_APPLICATION . 'system/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_STORAGE', DIR_SYSTEM . 'storage/');
define('DIR_LANGUAGE', DIR_SYSTEM . 'language/');
define('DIR_REPOSITORY', DIR_APPLICATION . 'repository/');
define('DIR_IMAGE', DIR_REPOSITORY . 'repo/image/');
define('DIR_FILE', DIR_REPOSITORY . 'repo/file/');
define('DIR_FILE_BIN', DIR_REPOSITORY . 'repo/file_bin/');
define('DIR_CACHE', DIR_REPOSITORY . 'repo/cache/');
define('DIR_LOGS', DIR_REPOSITORY . 'repo/logs/');
define('DIR_TEMP', DIR_REPOSITORY . 'repo/temp/');
define('DIR_DATA', DIR_APPLICATION . 'data/');
define('DIR_RESOURCE', DIR_APPLICATION . 'resource/');

// OTHER DEFINES
define('COOKIE_DOMAIN', '.' . MAIN_DOMAIN);
define('SESSION', '.' . strtoupper(MAIN_DOMAIN));
define('SESSION_EXPIRE', 2 * 24 * 60 * 60);

// Debug
if (isset($_GET['debug']) || false == getenv('PRODUCTION')) {
	define('ENVIRONMENT', 'development');
} else {
	// Default
	define('ENVIRONMENT', 'production');
}

// Set Error Display
if (ENVIRONMENT == 'development') {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
}
else {
	ini_set('display_errors', 0);
	ini_set('display_startup_errors', 0);
	error_reporting(0);
}

// Startup
require_once(DIR_SYSTEM . 'startup.php');

start('api');
