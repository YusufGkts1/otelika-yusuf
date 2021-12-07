<?php
// Site
$_['site_ssl']				= true;

// Database
$_['db_autostart']			= false;

$_['db_system_type']		= 'pdo';
$_['db_system_hostname']	= '127.0.0.1';
$_['db_system_username']	= 'appuser';
$_['db_system_password']	= '123123';
$_['db_system_database']	= 'system';
$_['db_system_port']		= '33060';

$_['db_event_type']			= 'pdo';
$_['db_event_hostname']		= '127.0.0.1';
$_['db_event_username']		= 'appuser';
$_['db_event_password']		= '123123';
$_['db_event_database']		= 'event_store';
$_['db_event_port']			= '33060';

$_['db_auth_type']			= 'pdo';
$_['db_auth_hostname']		= '127.0.0.1';
$_['db_auth_username']		= 'appuser';
$_['db_auth_password']		= '123123';
$_['db_auth_database']		= 'auth';
$_['db_auth_port']			= '33060';

$_['db_notification_type']  = 'pdo';
$_['db_notification_hostname'] = '127.0.0.1';
$_['db_notification_username'] = 'appuser';
$_['db_notification_password'] = '123123';
$_['db_notification_database'] = 'notification';
$_['db_notification_port']  = '33060';

$_['db_common_type']  = 'pdo';
$_['db_common_hostname'] = '127.0.0.1';
$_['db_common_username'] = 'appuser';
$_['db_common_password'] = '123123';
$_['db_common_database'] = 'common';
$_['db_common_port']  = '33060';

$_['db_iaa_type']   = 'pdo';
$_['db_iaa_hostname']  = '127.0.0.1';
$_['db_iaa_username']  = 'appuser';
$_['db_iaa_password']  = '123123';
$_['db_iaa_database']  = 'identity_and_access';
$_['db_iaa_port']   = '33060';

$_['db_task_type']   = 'pdo';
$_['db_task_hostname']  = '127.0.0.1';
$_['db_task_username']  = 'appuser';
$_['db_task_password']  = '123123';
$_['db_task_database']  = 'task_management';
$_['db_task_port']   = '33060';

$_['db_file_type']   = 'pdo';
$_['db_file_hostname']  = '127.0.0.1';
$_['db_file_username']  = 'appuser';
$_['db_file_password']  = '123123';
$_['db_file_database']  = 'file_management';
$_['db_file_port']   = '33060';

$_['db_department_type']		= 'pdo';
$_['db_department_hostname']	= '127.0.0.1';
$_['db_department_username']	= 'appuser';
$_['db_department_password']	= '123123';
$_['db_department_database']	= 'department_management';
$_['db_department_port']		= '33060';

$_['db_procedure_management_type']		= 'pdo';
$_['db_procedure_management_hostname']	= '127.0.0.1';
$_['db_procedure_management_username']	= 'appuser';
$_['db_procedure_management_password']	= '123123';
$_['db_procedure_management_database']	= 'procedure_management';
$_['db_procedure_management_port']		= '33060';

$_['db_gis_type']						= 'pdo_postgre';
$_['db_gis_hostname']					= '127.0.0.1';
$_['db_gis_username']					= 'appuser';
$_['db_gis_password']					= '123123';
$_['db_gis_database']					= 'gis';
$_['db_gis_port']						= '5432';

$_['db_structure_type']			= 'pdo';
$_['db_structure_hostname']		= '127.0.0.1';
$_['db_structure_username']		= 'appuser';
$_['db_structure_password']		= '123123';
$_['db_structure_database']		= 'structure_profile';
$_['db_structure_port']			= '33060';

$_['db_citizen_registry_type']		= 'pdo';
$_['db_citizen_registry_hostname']	= '127.0.0.1';
$_['db_citizen_registry_username']	= 'appuser';
$_['db_citizen_registry_password']	= '123123';
$_['db_citizen_registry_database']	= 'citizen_registry';
$_['db_citizen_registry_port']		= '33060';

$_['db_corporation_registry_type']		= 'pdo';
$_['db_corporation_registry_hostname']	= '127.0.0.1';
$_['db_corporation_registry_username']	= 'appuser';
$_['db_corporation_registry_password']	= '123123';
$_['db_corporation_registry_database']	= 'corporation_registry';
$_['db_corporation_registry_port']		= '33060';

$_['db_polling_type']			= 'pdo';
$_['db_polling_hostname']		= '127.0.0.1';
$_['db_polling_username']		= 'appuser';
$_['db_polling_password']		= '123123';
$_['db_polling_database']		= 'polling';
$_['db_polling_port']			= '33061';

// Sms
$_['sms_autostart']			= false;
$_['sms_type']				= '';
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

// Reponse
$_['response_header']      = array('Content-Type: application/json');

// Session
$_['session_autostart']		= false;
$_['session_duration']		= 7200;  // seconds

// Password Reset
$_['password_reset_duration'] = 7200;  //seconds

// Cache
$_['cache_autostart']		= false;
$_['cache_type']			= 'file';
$_['cache_expire']			= 2 * 24 * 60 * 60;

// Sentry
$_['sentry_autostart']		= true;
$_['sentry_dsn']			= 'https://3de5fb47060c46f3af4b576494b8f34f@o527778.ingest.sentry.io/5644460';

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
	'auth/password_reset',
	'file_management/image'
);

// Actions
$_['action_router']        = 'startup/test_router';

# polling
$_['polling_allowed_personnels'] = [];