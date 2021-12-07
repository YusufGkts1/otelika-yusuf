<?php
// Site
$_['site_ssl']				= true;

// Database
$_['db_autostart']			= false;

$_['db_event_type']			= 'pdo';
$_['db_event_hostname']		= '172.111.0.3';
$_['db_event_username']		= getenv('MYSQL_USER');
$_['db_event_password']		= getenv('MYSQL_PASSWORD');
$_['db_event_database']		= 'event_store';
$_['db_event_port']			= '3306';

$_['db_auth_type']			= 'pdo';
$_['db_auth_hostname']		= '172.111.0.3';
$_['db_auth_username']		= getenv('MYSQL_USER');
$_['db_auth_password']		= getenv('MYSQL_PASSWORD');
$_['db_auth_database']		= 'auth';
$_['db_auth_port']			= '3306';

$_['db_notification_type']		= 'pdo';
$_['db_notification_hostname']	= '172.111.0.3';
$_['db_notification_username']	= getenv('MYSQL_USER');
$_['db_notification_password']	= getenv('MYSQL_PASSWORD');
$_['db_notification_database']	= 'notification';
$_['db_notification_port']		= '3306';

$_['db_common_type']		= 'pdo';
$_['db_common_hostname']	= '172.111.0.3';
$_['db_common_username']	= getenv('MYSQL_USER');
$_['db_common_password']	= getenv('MYSQL_PASSWORD');
$_['db_common_database']	= 'common';
$_['db_common_port']		= '3306';

$_['db_iaa_type']			= 'pdo';
$_['db_iaa_hostname']		= '172.111.0.3';
$_['db_iaa_username']		= getenv('MYSQL_USER');
$_['db_iaa_password']		= getenv('MYSQL_PASSWORD');
$_['db_iaa_database']		= 'identity_and_access';
$_['db_iaa_port']			= '3306';

$_['db_task_type']			= 'pdo';
$_['db_task_hostname']		= '172.111.0.3';
$_['db_task_username']		= getenv('MYSQL_USER');;
$_['db_task_password']		= getenv('MYSQL_PASSWORD');
$_['db_task_database']		= 'task_management';
$_['db_task_port']			= '3306';


$_['db_sdm_type']			= 'pdo';
$_['db_sdm_hostname']		= '172.111.0.3';
$_['db_sdm_username']		= getenv('MYSQL_USER');;
$_['db_sdm_password']		= getenv('MYSQL_PASSWORD');
$_['db_sdm_database']		= 'sdm';
$_['db_sdm_port']			= '3306';

// $_['db_task_type']			= 'mongo';
// $_['db_task_hostname']		= '172.111.0.5';
// $_['db_task_username']		= getenv('MYSQL_USER');;
// $_['db_task_password']		= getenv('MYSQL_PASSWORD');
// $_['db_task_database']		= 'task_management';
// $_['db_task_port']			= '27017';

$_['db_system_type']		= 'pdo';
$_['db_system_hostname']	= '172.111.0.3';
$_['db_system_username']	= getenv('MYSQL_USER');
$_['db_system_password']	= getenv('MYSQL_PASSWORD');
$_['db_system_database']	= 'system';
$_['db_system_port']		= '3306';

$_['db_file_type']			= 'pdo';
$_['db_file_hostname']		= '172.111.0.3';
$_['db_file_username']		= getenv('MYSQL_USER');;
$_['db_file_password']		= getenv('MYSQL_PASSWORD');
$_['db_file_database']		= 'file_management';
$_['db_file_port']			= '3306';

$_['db_department_type']		= 'pdo';
$_['db_department_hostname']	= '172.111.0.3';
$_['db_department_username']	= getenv('MYSQL_USER');;
$_['db_department_password']	= getenv('MYSQL_PASSWORD');
$_['db_department_database']	= 'department_management';
$_['db_department_port']		= '3306';

$_['db_procedure_management_type']			= 'pdo';
$_['db_procedure_management_hostname']		= '172.111.0.3';
$_['db_procedure_management_username']		= getenv('MYSQL_USER');;
$_['db_procedure_management_password']		= getenv('MYSQL_PASSWORD');
$_['db_procedure_management_database']		= 'procedure_management';
$_['db_procedure_management_port']			= '3306';

$_['db_citizen_registry_type']			= 'pdo';
$_['db_citizen_registry_hostname']		= '172.111.0.3';
$_['db_citizen_registry_username']		= getenv('MYSQL_USER');;
$_['db_citizen_registry_password']		= getenv('MYSQL_PASSWORD');
$_['db_citizen_registry_database']		= 'citizen_registry';
$_['db_citizen_registry_port']			= '3306';

$_['db_corporation_registry_type']			= 'pdo';
$_['db_corporation_registry_hostname']		= '172.111.0.3';
$_['db_corporation_registry_username']		= getenv('MYSQL_USER');;
$_['db_corporation_registry_password']		= getenv('MYSQL_PASSWORD');
$_['db_corporation_registry_database']		= 'corporation_registry';
$_['db_corporation_registry_port']			= '43060';

$_['db_gis_type']						= 'pdo_postgre';
$_['db_gis_hostname']					= '172.111.0.8';
$_['db_gis_username']					= getenv('MYSQL_USER');
$_['db_gis_password']					= getenv('MYSQL_PASSWORD');
$_['db_gis_database']					= 'gis';
$_['db_gis_port']						= '5432';

$_['db_structure_type']			= 'pdo';
$_['db_structure_hostname']		= '172.111.0.3';
$_['db_structure_username']		= getenv('MYSQL_USER');;
$_['db_structure_password']		= getenv('MYSQL_PASSWORD');
$_['db_structure_database']		= 'structure_profile';
$_['db_structure_port']			= '3306';

// redis
$_['redis_hostname']			= '172.111.0.11';
$_['redis_port']				= '6379';

// Sentry
$_['sentry_autostart']		= true;
$_['sentry_dsn']			= 'https://3de5fb47060c46f3af4b576494b8f34f@o527778.ingest.sentry.io/5644460';

// Language
$_['language_default']     = 'tr';
$_['language_autoload']    = array('tr');

// Sms
$_['sms_autostart']			= true;
$_['sms_type']				= 'asistiletisim';
$_['sms_params']			= array(
	'UserName' => 'otomasyon',
	'Password' => 'xUWQdBmV',
	'UserCode' => '210',
	'ApiKey' => 'abc9d759-f1a7-442c-a53f-32518df0e4d1',
	'AccountID' => '118',
	'Originator' => 'GUNGORENBEL'
);

// JWT
$_['jwt_password']			= '%8MZHCG2k)dh3sPT';
$_['jwt_duration']			= 7200;

// CronJob
$_['cron_key']				= 'y#wdC588ddLwnsQ$oYU0#eHhsq#-VvHI8&aFsoWhmgXxPf$38BNbaqsg+uu&USk&';
$_['cron_endpoints']		= array(
	'event/event',
	'mail/mail',
	'sms/sms'
);

// Event
$_['event_key']				= 'sJ7ztFiRJf?npIMhtAeLQ&4vBYWCq%O%0qmYA39Sxb&NSkUdi7LUnResfVj5!-U1';

// Invoker
$_['invoker_key']           = 'z6*Ea64a5C*yZVd8&w19WRUzpZ&qkQPsG56CPyNwcW!Ni7gV0F2rTIHwhd0dk5+0';

// Reponse
$_['response_header']      = array('Content-Type: application/json');

// UOW
$_['uow_rollback_on_objection']	= false;

// Session
$_['session_autostart']		= false;
$_['session_duration']		= 7200;  // seconds

// Password Reset
$_['password_reset_duration'] = 7200;  //seconds

// Cache
$_['cache_autostart']		= true;
$_['cache_type']			= 'file';
$_['cache_expire']			= 2 * 24 * 60 * 60;

// Param
$_['param_autostart']		= true;

// Error
$_['error_log']				= true;
if (ENVIRONMENT == 'production') {
	$_['error_display']			= false;
}

// Endpoints that do not require authorization/authentication
$_['insecure_endpoints'] = array(
	'auth/authorize',
	'auth/forgotten',
	'auth/reset',
	'file_management/image',
	'file',
	'procedure_management/application',
	'gis/public',
	'gis/proxy',
	'gis/proxy_authorized',
	'polling/survey',
	'sdm/authorize',
	'sdm/new_password',
	'student_station/forgotten',
	'student_station/reset'
);

$_['sdm_endpoints'] = [
	'sdm/app',
	'sdm/verify_transaction',
	'sdm/registration'
];

// Actions
$_['action_pre_action']		= array(
	'startup/cors',
	'startup/filter',
	'auth/authentication',
	'sdm/authentication',
	'startup/startup',
	'startup/error',
	// 'sms/sms',
	// 'mail/mail'
	// 'event/event'
);
