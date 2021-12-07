<?php

// Registry
$registry = new Registry();

// Loader
$loader = new Loader($registry);
$registry->set('load', $loader);

// Config
$config = new Config();
$config->load('default');
$config->load($application_config);

if(ENVIRONMENT != 'test') {
	$files = scandir(DIR_CONFIG . 'conf.d');
	foreach($files as $file) {
		if(strpos($file, '.php') !== false)
			$config->load('conf.d/' . substr($file, 0, strpos($file, '.php')));
	}
}

$registry->set('config', $config);

// Sentry
if($config->get('sentry_autostart')) {
	$sentry = new Sentry($config->get('sentry_dsn'));
	$registry->set('sentry', $sentry);
}

// Request
$registry->set('request', new Request());

if ($config->get('error_log')) {
	$registry->set('log', new Log($config->get('error_filename')));	
}

date_default_timezone_set($config->get('timezone_default'));
setlocale(LC_ALL, $config->get('locale_default'));

// Response
$response = new Response();
$response->addHeader($config->get('response_header')[0]);
$response->setCompression($config->get('response_compression'));
$registry->set('response', $response);

// UOW
// TODO: Burda sentrynin kullanilmasi yanlis. Dinamik bir yapi lazim
$uow = new UOW($config->get('uow_rollback_on_objection'), new Logger\SentryLogger($sentry));
$uow->begin();
$registry->set('uow', $uow);

// Database
if ($config->get('db_autostart')) {
	$registry->set('db', new DB($config->get('db_type'), $config->get('db_hostname'), $config->get('db_username'), $config->get('db_password'), $config->get('db_database'), $config->get('db_port')));
}

// Session
$session = new Session();
$registry->set('session', $session);

// Operator
// $operator = new Operator();

// Cache
if ($config->get('cache_autostart')) {
	$registry->set('cache', new Cache($config->get('cache_type'), $config->get('cache_expire')));
}

// Event
$event = new Event($registry);
$registry->set('event', $event);

// Language
$language = new Language($config->get('language_default'));
$language->load($config->get('language_default'));
$registry->set('language', $language);

// SMS
if ($config->get('sms_autostart')) {
	$sms = new SMS($config->get('sms_type'), $config->get('sms_params'));
	$registry->set('sms', $sms);
}

// Mail
if($config->get('email_autostart')) {
	$mail = new Mailer($registry);
	$registry->set('mail', $mail);
}

// JWT
$jwt = new JWToken($config->get('jwt_password'));
$registry->set('jwt', $jwt);

// API Params
if ($config->get('param_autostart')) {
	$registry->set('param', new Param($registry));
}

// Event Register
if ($config->has('action_event')) {
	foreach ($config->get('action_event') as $key => $value) {
		$event->register($key, new Action($value));
	}
}

// Config Autoload
if ($config->has('config_autoload')) {
	foreach ($config->get('config_autoload') as $value) {
		$loader->config($value);
	}
}

// Library Autoload
if ($config->has('library_autoload')) {
	foreach ($config->get('library_autoload') as $value) {
		$loader->library($value);
	}
}

// Model Autoload
if ($config->has('model_autoload')) {
	foreach ($config->get('model_autoload') as $value) {
		$loader->model($value);
	}
}

// Helper Autoload
if ($config->has('helper_autoload')) {
	foreach ($config->get('helper_autoload') as $value) {
		$loader->model($value);
	}
}

// Front Controller
$controller = new Front($registry);

// Pre Actions
if ($config->has('action_pre_action')) {
	foreach ($config->get('action_pre_action') as $value) {
		$controller->addPreAction(new Action($value));
	}
}

$framework = $registry;
$GLOBALS['framework'] = $framework;

// Dispatch
$controller->dispatch(new Action($config->get('action_router')), new Action($config->get('action_error')));

/**
 * ?*TEST:
 * 		uow_auto_rollback confige bakilarak
 * 		otomatik olarak rollback veya commit
 * 		yapmali
 */
if($uow->inProgress()) {
	// echo PHP_EOL . 'transaction began but did not commit';

	if($config->get('uow_auto_rollback')) {
		// echo '. rolling back';
		$uow->rollback();
	}
	else {
		// echo '. committing';
		$uow->commit();
	}
}

// Output
$response->output();