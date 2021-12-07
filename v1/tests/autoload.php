<?php

/**
 * ?*TEST
 * 		Eventler test edilmeli, çok sık
 * 		hataya sebep oluyor.
 */

define('ENVIRONMENT', 'test');

define('DIR_ROOT', __DIR__ . '/../');
define('DIR_SYSTEM', DIR_ROOT . 'system/');
define('DIR_MODEL', DIR_ROOT . 'model/');
define('DIR_TEST', DIR_ROOT . 'tests/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_APPLICATION', DIR_ROOT);
define('DIR_LANGUAGE', DIR_SYSTEM . 'language/');
define('DIR_REPOSITORY', DIR_APPLICATION . 'repository/');
define('DIR_IMAGE', DIR_REPOSITORY . 'repo/image/');
define('DIR_FILE', DIR_REPOSITORY . 'repo/file/');
define('DIR_FILE_BIN', DIR_REPOSITORY . 'repo/file_bin/');
define('DIR_CACHE', DIR_REPOSITORY . 'repo/cache/');
define('DIR_LOGS', DIR_REPOSITORY . 'repo/logs/');

/** fake these variables */
define('HTTP_SERVER', 'localhost:8080');
$_SERVER['REMOTE_ADDR'] = 'localhost';

function library($class) {
	$file = DIR_SYSTEM . 'library/' . str_replace('\\', '/', strtolower($class)) . '.php';

	if (is_file($file)) {
		include_once($file);

		return true;
	} else {
		return false;
	}
}

function test_dependency_loader($path) {
	$file = str_replace('\\', '/', DIR_ROOT . $path) . '.php';

	if(false == is_file($file))
		return false;

	require_once($file);
	return true;
}

function data_provider_loader($path) {
	$file = str_replace('\\', '/', DIR_ROOT . $path) . '.php';

	if(false == is_file($file))
		return false;

	require_once($file);

	return true;
}

spl_autoload_register('library');
spl_autoload_register('test_dependency_loader');
spl_autoload_register('data_provider_loader');
spl_autoload_extensions('.php');

// Engine
require_once(DIR_SYSTEM . 'engine/action.php');
require_once(DIR_SYSTEM . 'engine/controller.php');
require_once(DIR_SYSTEM . 'engine/event.php');
require_once(DIR_SYSTEM . 'engine/front.php');
require_once(DIR_SYSTEM . 'engine/loader.php');
require_once(DIR_SYSTEM . 'engine/model.php');
require_once(DIR_SYSTEM . 'engine/module.php');
require_once(DIR_SYSTEM . 'engine/registry.php');
require_once(DIR_SYSTEM . 'engine/proxy.php');

// Helper
require_once(DIR_SYSTEM . 'helper/general.php');
require_once(DIR_SYSTEM . 'helper/token.php');
require_once(DIR_SYSTEM . 'helper/variable.php');
require_once(DIR_SYSTEM . 'helper/utf8.php');
require_once(DIR_SYSTEM . 'helper/json.php');
require_once(DIR_SYSTEM . 'helper/hash_equals.php');

// Error
require_once(DIR_SYSTEM . 'error/AuthorizationException.php');
require_once(DIR_SYSTEM . 'error/NotFoundException.php');

// Framework
$application_config = 'test';

require_once(DIR_SYSTEM . 'framework.php');

$framework->get('session')->set('operator', new \model\system\log\Operator(1, 1));
?>