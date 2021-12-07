delimiter ;

CREATE DATABASE IF NOT EXISTS procedures;

USE procedures;

DROP PROCEDURE IF EXISTS execute_command;
DROP PROCEDURE IF EXISTS log_to_table;
DROP PROCEDURE IF EXISTS get_columns;
DROP PROCEDURE IF EXISTS column_exists;
DROP PROCEDURE IF EXISTS create_or_update;
DROP PROCEDURE IF EXISTS value_exists;
DROP PROCEDURE IF EXISTS insert_if_not_exists;

delimiter //

-- execute_command
CREATE PROCEDURE execute_command(query TEXT)
BEGIN
	SET @query = query;

	PREPARE stmt FROM @query;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
END//

-- log_to_table
-- Gonderilen `txt` icerisinde `'` sembolu bulunmamali
CREATE PROCEDURE log_to_table(db_name TEXT, txt TEXT)
BEGIN
	SET @query = "CREATE TABLE IF NOT EXISTS `log` (`db` VARCHAR(128), `text` TEXT, `log_date` DATETIME);";
	CALL execute_command(@query);
	
	SET @query = CONCAT("INSERT INTO `log` SET `db` = '", db_name, "', `text` = '", txt, "', log_date = NOW();");
	CALL execute_command(@query);
END//

-- get_columns
CREATE PROCEDURE get_columns(db_name TEXT, table_name TEXT, OUT columns TEXT)
BEGIN
	SET @temp := 0;
	SET @query = CONCAT("SELECT GROUP_CONCAT(COLUMN_NAME) INTO @temp FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '", db_name, "' AND TABLE_NAME = '", table_name, "'");

	PREPARE stmt FROM @query;
	EXECUTE stmt;
	SET columns = (@temp);
	DEALLOCATE PREPARE stmt;
END//

-- column_exists
CREATE PROCEDURE column_exists(db_name TEXT, table_name TEXT, column_name TEXT, OUT ex BOOLEAN)
BEGIN
	SET @temp := 0;
	SET @query = CONCAT("SELECT COUNT(*) INTO @temp FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '", db_name, "' AND TABLE_NAME = '", table_name, "' AND COLUMN_NAME = '", column_name, "';");

	PREPARE stmt FROM @query; 
	EXECUTE stmt;
	SET ex = (@temp > 0);
	DEALLOCATE PREPARE stmt;
END//

-- create_or_update
CREATE PROCEDURE create_or_update(db_name TEXT, tablename TEXT, columns TEXT)
BEGIN
	DECLARE str_length INT DEFAULT 0;
	DECLARE sub_str_length INT DEFAULT 0;
	DECLARE column_name VARCHAR(128);
	DECLARE exsts BOOLEAN;
	DECLARE columns_set TEXT DEFAULT '';
	DECLARE current_columns TEXT;

	SET @tablename = TRIM(tablename);
	SET @columns = TRIM(columns);
	
	-- create table if not exists
	SET @query = CONCAT('CREATE TABLE IF NOT EXISTS `', db_name,'`.`', @tablename, '` (placeholder1289313 INT);');
	CALL execute_command(@query);

	-- add missing columns and build columns_set
	add_fields:
		LOOP
			SET str_length = LENGTH(@columns);
			SET column_name = TRIM(REPLACE(SUBSTRING_INDEX(@columns, ' ', 1), '`', ''));

			CALL log_to_table(db_name, CONCAT("column_name: ", column_name));

			-- add column_name to columns_set
			IF (LENGTH(TRIM(column_name)) > 0) THEN
				IF LENGTH(TRIM(columns_set)) = 0 THEN
					SET columns_set = column_name;
				ELSE
					SET columns_set = CONCAT(columns_set, ',', column_name);
				END IF;
			END IF;

			CALL column_exists(db_name, @tablename, column_name, exsts);

			IF !exsts THEN
				CALL log_to_table(db_name, CONCAT("Adding column `", column_name, "` to table `", @tablename, "`"));

				SET @query = CONCAT('ALTER TABLE `', db_name, '`.`', @tablename, '` ADD COLUMN ', SUBSTRING_INDEX(@columns, ',', 1), ';');
				CALL execute_command(@query);
			END IF;

			SET sub_str_length = LENGTH(SUBSTRING_INDEX(@columns, ',', 1));
			SET @columns = TRIM(MID(@columns, sub_str_length + 2, str_length));

			IF ((@columns = NULL) OR (LENGTH(@columns) = 0)) THEN
				LEAVE add_fields;
			END IF;
		END LOOP add_fields;

	-- remove placeholder column if exists
	CALL column_exists(db_name, @tablename, 'placeholder1289313', exsts);

	IF exsts THEN
		SET @query = CONCAT('ALTER TABLE `', db_name, '`.`', @tablename, '` DROP COLUMN `placeholder1289313`;');
		CALL execute_command(@query);
	END IF;

	-- reset environment
	SET @columns = TRIM(columns);

	CALL get_columns(db_name, @tablename, current_columns);

	-- remove extra columns
	remove_fields:
		LOOP
			SET str_length = LENGTH(current_columns);
			SET column_name = TRIM(REPLACE(SUBSTRING_INDEX(current_columns, ',', 1), '`', ''));

			IF FIND_IN_SET(column_name, columns_set) = 0 THEN
				CALL log_to_table(db_name, CONCAT("Removing column `", column_name, "` from table `", @tablename, "`"));

				SET @query = CONCAT("ALTER TABLE `", db_name, "`.`", @tablename, '` DROP COLUMN `', column_name, '`');
				CALL execute_command(@query);
			END IF;

			SET sub_str_length = LENGTH(SUBSTRING_INDEX(current_columns, ',', 1));
			SET current_columns = TRIM(MID(current_columns, sub_str_length + 2, str_length));

			IF((current_columns = NULL) OR (LENGTH(current_columns) = 0)) THEN
				LEAVE remove_fields;
			END IF;
		END LOOP remove_fields;
END//

-- value_exists

CREATE PROCEDURE value_exists(db_name TEXT, table_name TEXT, value_ TEXT, column_ TEXT, OUT ex TEXT)
BEGIN
	SET @temp := 0;
	SET @query = CONCAT("SELECT COUNT(*) INTO @temp FROM `", db_name, "`.`", table_name, "` WHERE `", column_, "` = '", value_, "'");

	PREPARE stmt FROM @query;
	EXECUTE stmt;
	SET ex = (@temp > 0);
	DEALLOCATE PREPARE stmt;
END//

-- insert_if_not_exists
-- unique_combination
CREATE PROCEDURE insert_if_not_exists(db_name TEXT, table_name TEXT, columns_ TEXT, values_ TEXT, unique_combination TEXT)
BEGIN
	DECLARE str_length INT DEFAULT 0;
	DECLARE sub_str_length INT DEFAULT 0;
	DECLARE unique_column_name TEXT;
	DECLARE str_length_col INT DEFAULT 0;
	DECLARE sub_str_length_col INT DEFAULT 0;
	DECLARE col_name_ TEXT;
	DECLARE str_length_val INT DEFAULT 0;
	DECLARE sub_str_length_val INT DEFAULT 0;
	DECLARE val TEXT;

	SET @tablename = TRIM(table_name);

	-- a pair of unique_combination values already exists
	SET @exsts = 1;

	-- loop through unique_combination entries
	unique_combination_loop:
		LOOP
			SET str_length = LENGTH(unique_combination);
			SET unique_column_name = TRIM(REPLACE(SUBSTRING_INDEX(unique_combination, ',', 1), '`', ''));

			SET @columns = columns_;
			SET @values = values_;

			-- if (unique_column_name, value) pair is not found set @exsts to false
			col_value_check_loop:
				LOOP
					-- get column name
					SET str_length_col = LENGTH(@columns);
					SET col_name_ = TRIM(REPLACE(SUBSTRING_INDEX(@columns, ',', 1), '`', ''));

					-- get value
					SET str_length_val = LENGTH(@values);
					SET val = TRIM(REPLACE(SUBSTRING_INDEX(@values, ',', 1), "'", ''));

					-- check if this value already exists in table
					SET @retval = 0;

					CALL value_exists(db_name, table_name, val, col_name_, @retval);

					IF ! @retval THEN
						SET @exsts = 0;
					END IF;

					-- remove left-most column from @columns set
					SET sub_str_length_col = LENGTH(SUBSTRING_INDEX(@columns, ',', 1));
					SET @columns = TRIM(MID(@columns, sub_str_length_col + 2, str_length_col));

					-- remove left-most value from @values set
					SET sub_str_length_val = LENGTH(SUBSTRING_INDEX(@values, ',', 1));
					SET @values = TRIM(MID(@values, sub_str_length_val + 2, str_length_val));

					IF ((@columns = NULL) OR (LENGTH(@columns) = 0) OR (@values = NULL) OR (LENGTH(@values) = 0)) THEN
						LEAVE col_value_check_loop;
					END IF;
				END LOOP col_value_check_loop;

			SET sub_str_length = LENGTH(SUBSTRING_INDEX(unique_combination, ',', 1));
			SET unique_combination = TRIM(MID(unique_combination, sub_str_length + 2, str_length));

			IF ((unique_combination = NULL) OR (LENGTH(unique_combination) = 0)) THEN
				LEAVE unique_combination_loop;
			END IF;
			
		END LOOP unique_combination_loop;

	IF ! @exsts THEN
		CALL log_to_table(db_name, CONCAT("Inserting values `", REPLACE(values_, "'", ''), "` into table `", table_name, "`"));

		SET @query = CONCAT("INSERT IGNORE INTO `", db_name, "`.`", table_name, "` (", columns_, ") VALUES (", values_, ")");
		CALL execute_command(@query);
	END IF;
END//

delimiter ;

-- auth

CREATE DATABASE IF NOT EXISTS auth;
ALTER DATABASE auth CHARACTER SET utf8 COLLATE utf8_general_ci;

USE auth;

CALL procedures.create_or_update('auth', 'login', '`id` INT AUTO_INCREMENT PRIMARY KEY, personnel_id INT NOT NULL UNIQUE, email VARCHAR(64) NOT NULL, password VARCHAR(128) NOT NULL, salt VARCHAR(9) NOT NULL, is_active TINYINT, date_added DATETIME NOT NULL, last_modification DATETIME NOT NULL');

CALL procedures.insert_if_not_exists('auth', 'login', 'personnel_id, email, password, salt, is_active, date_added, last_modification', "1, 'info@kant.ist', '6500523e66577e8ec2d85ae8298eecde0f5250ae', 'lAW1o59lg', '1', '1971-01-01', '1970-01-01'", 'personnel_id,');

CALL procedures.create_or_update('auth', 'login_bin', '`id` INT AUTO_INCREMENT PRIMARY KEY, personnel_id INT NOT NULL, email VARCHAR(64) NOT NULL, password VARCHAR(128) NOT NULL, salt VARCHAR(9) NOT NULL, is_active TINYINT, date_added DATETIME NOT NULL, last_modification DATETIME NOT NULL, removal_date DATETIME NOT NULL');

CALL procedures.create_or_update('auth', 'session', 'personnel_id INT NOT NULL UNIQUE, token VARCHAR(64) NOT NULL UNIQUE, ip VARCHAR(40) NOT NULL, expires_in INT NOT NULL, last_operation TIMESTAMP NOT NULL');

CALL procedures.create_or_update('auth', 'password_reset', 'personnel_id INT NOT NULL UNIQUE, token VARCHAR(64) NOT NULL UNIQUE, expires_in INT, request_time TIMESTAMP NOT NULL');

-- event_store

CREATE DATABASE IF NOT EXISTS event_store;
ALTER DATABASE event_store CHARACTER SET utf8 COLLATE utf8_general_ci;

USE event_store;

CALL procedures.create_or_update('event_store', 'event', '`id` INT AUTO_INCREMENT PRIMARY KEY, `type` VARCHAR(128) NOT NULL, `action_module` VARCHAR(128) NOT NULL, `action_service` VARCHAR(128) NOT NULL, `action_method` VARCHAR(128) NOT NULL, `data` TEXT, `version` TINYINT NOT NULL, trigger_type VARCHAR(64) NULL, trigger_id VARCHAR(32) NULL, `occurred_on` DATETIME NOT NULL, `is_processed` TINYINT NOT NULL, `has_failed` TINYINT NOT NULL, `error` VARCHAR(2048) NULL');

CALL procedures.create_or_update('event_store', 'subscription', '`type` VARCHAR(128) NOT NULL, `action_module` VARCHAR(128) NOT NULL, action_service VARCHAR(128) NOT NULL, action_method VARCHAR(128) NOT NULL');

CALL procedures.insert_if_not_exists('event_store', 'subscription', '`type`, action_module, action_service, action_method', "'model\/IdentityAndAccess\/domain\/model\/PersonnelCreated', 'auth', 'EventProcessor', 'onPersonnelCreation'", '`type`, action_module, action_service, action_method');
CALL procedures.insert_if_not_exists('event_store', 'subscription', '`type`, action_module, action_service, action_method', "'model\/IdentityAndAccess\/domain\/model\/PersonnelActivated', 'auth', 'EventProcessor', 'onPersonnelActivation'", '`type`, action_module, action_service, action_method');
CALL procedures.insert_if_not_exists('event_store', 'subscription', '`type`, action_module, action_service, action_method', "'model\/IdentityAndAccess\/domain\/model\/PersonnelDeactivated', 'auth', 'EventProcessor', 'onPersonnelDeactivation'", '`type`, action_module, action_service, action_method');
CALL procedures.insert_if_not_exists('event_store', 'subscription', '`type`, action_module, action_service, action_method', "'model\/IdentityAndAccess\/domain\/model\/PersonnelRemoved', 'auth', 'EventProcessor', 'onPersonnelDeletion'", '`type`, action_module, action_service, action_method');
CALL procedures.insert_if_not_exists('event_store', 'subscription', '`type`, action_module, action_service, action_method', "'model\/IdentityAndAccess\/domain\/model\/PersonnelEmailChanged', 'auth', 'EventProcessor', 'onPersonnelEmailUpdate'", '`type`, action_module, action_service, action_method');
CALL procedures.insert_if_not_exists('event_store', 'subscription', '`type`, action_module, action_service, action_method', "'model\/IdentityAndAccess\/domain\/model\/PersonnelPhoneChangedByAnotherPersonnel', 'auth', 'EventProcessor', 'onPersonnelPhoneUpdateByAnotherPersonnel'", '`type`, action_module, action_service, action_method');
CALL procedures.insert_if_not_exists('event_store', 'subscription', '`type`, action_module, action_service, action_method', "'model\/IdentityAndAccess\/domain\/model\/DirectorAssigned', 'TaskManagement', 'TaskGenerator', 'onDepartmentDirectorAssignment'", '`type`, action_module, action_service, action_method');

CALL procedures.insert_if_not_exists('event_store', 'subscription', '`type`, action_module, action_service, action_method', "'model\/ProcedureManagement\/domain\/model\/IndividualInitiatedProcedure', 'CitizenRegistry', 'EventProcessor', 'onIndividualProcedureInitiation'", '`type`, action_module, action_service, action_method');
CALL procedures.insert_if_not_exists('event_store', 'subscription', '`type`, action_module, action_service, action_method', "'model\/ProcedureManagement\/domain\/model\/CorporateEntityInitiatedProcedure', 'CorporationRegistry', 'EventProcessor', 'onCorporateEntityProcedureInitiation'", '`type`, action_module, action_service, action_method');
CALL procedures.insert_if_not_exists('event_store', 'subscription', '`type`, action_module, action_service, action_method', "'model\/Proceduremanagement\/domain\/model\/UAVTGenerationTriggered', 'ProcedureManagement', 'DocumentDistributor', 'onUAVTGenerationTrigger'", '`type`, action_module, action_service, action_method');
CALL procedures.insert_if_not_exists('event_store', 'subscription', '`type`, action_module, action_service, action_method', "'model\/Proceduremanagement\/domain\/model\/RiskliYapiRaporEksiklikTespit', 'TaskManagement', 'TaskGenerator', 'onRiskliYapiRaporEksiklikTespit'", '`type`, action_module, action_service, action_method');

CALL procedures.insert_if_not_exists('event_store', 'subscription', '`type`, action_module, action_service, action_method', "'model\/Polling\/domain\/model\/tag\/TagUsed', 'Polling', 'EventProcessor', 'tagUsed'", '`type`, action_module, action_service, action_method');
CALL procedures.insert_if_not_exists('event_store', 'subscription', '`type`, action_module, action_service, action_method', "'model\/Polling\/domain\/model\/tag\/TagRemoved', 'Polling', 'EventProcessor', 'tagRemoved'", '`type`, action_module, action_service, action_method');
CALL procedures.insert_if_not_exists('event_store', 'subscription', '`type`, action_module, action_service, action_method', "'model\/Polling\/domain\/model\/tag\/SurveyResponseSubmitted', 'Polling', 'EventProcessor', 'surveyResponseSubmitted'", '`type`, action_module, action_service, action_method');
CALL procedures.insert_if_not_exists('event_store', 'subscription', '`type`, action_module, action_service, action_method', "'model\/Polling\/domain\/model\/survey\/TagRuleCreated', 'Polling', 'EventProcessor', 'tagRuleCreated'", '`type`, action_module, action_service, action_method');
CALL procedures.insert_if_not_exists('event_store', 'subscription', '`type`, action_module, action_service, action_method', "'model\/Polling\/domain\/model\/survey\/SubmissionCreated', 'Polling', 'EventProcessor', 'surveyResponseSubmitted'", '`type`, action_module, action_service, action_method');
CALL procedures.insert_if_not_exists('event_store', 'subscription', '`type`, action_module, action_service, action_method', "'model\/Polling\/domain\/model\/tag\/CategoryRemoved', 'Polling', 'EventProcessor', 'categoryRemoved'", '`type`, action_module, action_service, action_method');
-- notification

CREATE DATABASE IF NOT EXISTS `notification`;
ALTER DATABASE `notification` CHARACTER SET utf8 COLLATE utf8_general_ci;

USE `notification`;

CALL procedures.create_or_update('notification', 'sms', "`id` INT AUTO_INCREMENT PRIMARY KEY, `message` TEXT NOT NULL, phone VARCHAR(32) NOT NULL, `is_processed` TINYINT NOT NULL, queued_on DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, sent_on DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");

CALL procedures.create_or_update('notification', 'mail', "`id` INT AUTO_INCREMENT PRIMARY KEY, `subject` VARCHAR(512) NULL, `message` MEDIUMTEXT NOT NULL, `recipient` VARCHAR(512) NOT NULL, is_processed TINYINT NOT NULL, queued_on DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, sent_on DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");

-- common

CREATE DATABASE IF NOT EXISTS `common`;
ALTER DATABASE `common` CHARACTER SET utf8 COLLATE utf8_general_ci;

USE `common`;

CALL procedures.create_or_update('common', 'module', '`id` INT PRIMARY KEY, `name` VARCHAR(50) NOT NULL UNIQUE');

CALL procedures.insert_if_not_exists('common', 'module', '`id`, `name`', "1, 'identity_and_access'", '`id`');
CALL procedures.insert_if_not_exists('common', 'module', '`id`, `name`', "2, 'system'", '`id`');
CALL procedures.insert_if_not_exists('common', 'module', '`id`, `name`', "3, 'structure_registry_and_procedure_management'", '`id`');

CALL procedures.create_or_update('common', 'submodule', '`id` INT AUTO_INCREMENT PRIMARY KEY, module_id INT NOT NULL, `name` VARCHAR(50) NOT NULL UNIQUE');

CALL procedures.insert_if_not_exists('common', 'submodule', '`id`, module_id, `name`', "1, 1, 'personnel'", '`id`');
CALL procedures.insert_if_not_exists('common', 'submodule', '`id`, module_id, `name`', "2, 1, 'role'", '`id`');
CALL procedures.insert_if_not_exists('common', 'submodule', '`id`, module_id, `name`', "5, 1, 'department'", '`id`');
CALL procedures.insert_if_not_exists('common', 'submodule', '`id`, module_id, `name`', "3, 2, 'setting'", '`id`');
CALL procedures.insert_if_not_exists('common', 'submodule', '`id`, module_id, `name`', "4, 2, 'log'", '`id`');
CALL procedures.insert_if_not_exists('common', 'submodule', '`id`, module_id, `name`', "7, 3, 'structure_profile'", '`id`');
CALL procedures.insert_if_not_exists('common', 'submodule', '`id`, module_id, `name`', "6, 3, 'procedure_management'", '`id`');

-- Communication

CREATE DATABASE IF NOT EXISTS `communication`;
ALTER DATABASE `communication` CHARACTER SET utf8 COLLATE utf8_general_ci;

USE `communication`;

CALL procedures.create_or_update('communication', 'message', 'id INT NOT NULL PRIMARY KEY, sender_id INT NOT NULL, receiver_id INT NOT NULL, message VARCHAR(500) NOT NULL, msg_time VARCHAR(250) NOT NULL ');


-- identity_and_access

CREATE DATABASE IF NOT EXISTS `identity_and_access`;
ALTER DATABASE `identity_and_access` CHARACTER SET utf8 COLLATE utf8_general_ci;

USE `identity_and_access`;

CALL procedures.create_or_update('identity_and_access', 'personnel', 'id INT AUTO_INCREMENT PRIMARY KEY, role_id INT NULL, department_id INT NULL, image_id VARCHAR(32) NULL, firstname VARCHAR(64) NOT NULL, lastname VARCHAR(64) NOT NULL, tcno VARCHAR(11) NOT NULL UNIQUE, gender VARCHAR(16) NOT NULL, phone VARCHAR(24) NOT NULL UNIQUE, email VARCHAR(64) NOT NULL UNIQUE, is_active TINYINT(1) NOT NULL, date_added DATETIME NOT NULL, last_modification DATETIME NULL');

CALL procedures.insert_if_not_exists('identity_and_access', 'personnel', 'id, role_id, department_id, image_id, firstname, lastname, tcno, gender, phone, email, is_active, date_added, last_modification', "1, 1, 1, '', 'Sistem', 'Yöneticisi', '00000000000', 'female', '00000000000', 'info@kant.ist', 1, '1971-01-01', '1971-01-01'", 'id');

CALL procedures.create_or_update('identity_and_access', 'personnel_bin', 'id INT PRIMARY KEY, role_id INT NULL, department_id INT NULL, image_id VARCHAR(32) NULL, firstname VARCHAR(64) NOT NULL, lastname VARCHAR(64) NOT NULL, tcno VARCHAR(11) NOT NULL, gender VARCHAR(16) NOT NULL, phone VARCHAR(24) NOT NULL, email VARCHAR(64) NOT NULL, is_active TINYINT(1) NOT NULL, date_added DATETIME NOT NULL, last_modification DATETIME NULL');

CALL procedures.create_or_update('identity_and_access', 'role', 'id INT AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(50) NOT NULL UNIQUE');

CALL procedures.insert_if_not_exists('identity_and_access', 'role', '`id`, `name`', "1, 'superuser'", '`id`');

CALL procedures.create_or_update('identity_and_access', 'role_bin', 'id int NOT NULL, `name` VARCHAR(50) NOT NULL');

CALL procedures.create_or_update('identity_and_access', 'privilege', 'role_id INT NOT NULL, submodule_id INT NOT NULL, create_privileges TINYINT(1) NOT NULL, update_privileges TINYINT(1) NOT NULL, delete_privileges TINYINT(1) NOT NULL');

CALL procedures.insert_if_not_exists('identity_and_access', 'privilege', 'role_id, submodule_id, create_privileges, update_privileges, delete_privileges', '1, 1, 1, 1, 1', 'role_id, submodule_id');
CALL procedures.insert_if_not_exists('identity_and_access', 'privilege', 'role_id, submodule_id, create_privileges, update_privileges, delete_privileges', '1, 2, 1, 1, 1', 'role_id, submodule_id');
CALL procedures.insert_if_not_exists('identity_and_access', 'privilege', 'role_id, submodule_id, create_privileges, update_privileges, delete_privileges', '1, 3, 1, 1, 1', 'role_id, submodule_id');
CALL procedures.insert_if_not_exists('identity_and_access', 'privilege', 'role_id, submodule_id, create_privileges, update_privileges, delete_privileges', '1, 4, 1, 1, 1', 'role_id, submodule_id');
CALL procedures.insert_if_not_exists('identity_and_access', 'privilege', 'role_id, submodule_id, create_privileges, update_privileges, delete_privileges', '1, 5, 1, 1, 1', 'role_id, submodule_id');
CALL procedures.insert_if_not_exists('identity_and_access', 'privilege', 'role_id, submodule_id, create_privileges, update_privileges, delete_privileges', '1, 6, 1, 1, 1', 'role_id, submodule_id');
CALL procedures.insert_if_not_exists('identity_and_access', 'privilege', 'role_id, submodule_id, create_privileges, update_privileges, delete_privileges', '1, 7, 1, 1, 1', 'role_id, submodule_id');

CALL procedures.create_or_update('identity_and_access', 'privilege_bin', 'role_id INT NOT NULL, submodule_id INT NOT NULL, create_privileges TINYINT(1) NOT NULL, update_privileges TINYINT(1) NOT NULL, delete_privileges TINYINT(1) NOT NULL');

CALL procedures.create_or_update('identity_and_access', 'department', '`id` INT NOT NULL PRIMARY KEY, `name` VARCHAR(128) NOT NULL, parent_id INT NULL, director INT NULL, director_allowed_parent_depth TINYINT NOT NULL, director_allowed_child_depth TINYINT NOT NULL, member_allowed_parent_depth TINYINT NOT NULL, member_allowed_child_depth TINYINT NOT NULL, `order` TINYINT NOT NULL');

-- baskan
CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "1, 'Başkan', null, 1, 2, 2, 2, 2, 1", '`id`');

-- baskan yardimcilari
CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "2, 'Başkan Yardımcısı 1', 1, null, 2, 2, 2, 2, 2", '`id`');
CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "3, 'Başkan Yardımcısı 2', 1, null, 2, 2, 2, 2, 3", '`id`');
CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "4, 'Başkan Yardımcısı 3', 1, null, 2, 2, 2, 2, 4", '`id`');

-- baskana bagli departmanlar
CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "5, 'Özel Kalem Müdürlüğü', 1, null, 2, 2, 2, 2, 5", '`id`');

CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "6, 'Teftiş Kurulu Müdürlüğü', 1, null, 2, 2, 2, 2, 6", '`id`');

CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "7, 'Mali Hizmetler Müdürlüğü', 1, null, 2, 2, 2, 2, 7", '`id`');
CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "8, 'Tahsilat Şefliği', 7, null, 2, 2, 2, 2, 8", '`id`');
CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "9, 'Tahakkuk Şefliği', 7, null, 2, 2, 2, 2, 9", '`id`');
CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "39, 'Satınalma Şefliği', 7, null, 2, 2, 2, 2, 10", '`id`');

-- baskan yardimcisi 1
CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "10, 'Yazı İşleri Müdürlüğü', 2, null, 2, 2, 2, 2, 11", '`id`');

CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "11, 'İnsan Kaynakları ve Eğitim Müdürlüğü', 2, null, 2, 2, 2, 2, 12", '`id`');
CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "12, 'Personel İşleri Şefliği', 11, null, 2, 2, 2, 2, 13", '`id`');
CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "13, 'Eğitim Şefliği', 11, null, 2, 2, 2, 2, 14", '`id`');

CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "14, 'Halkla İlişkiler Müdürlüğü', 2, null, 2, 2, 2, 2, 15", '`id`');
CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "15, 'Halkla İlişkiler Şefliği', 14, null, 2, 2, 2, 2, 16", '`id`');

CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "16, 'Kültür ve Sosyal İşler Müdürlüğü', 2, null, 2, 2, 2, 2, 17", '`id`');
CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "17, 'Kültür ve Sosyal İşler Şefliği', 16, null, 2, 2, 2, 2, 18", '`id`');
CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "18, 'Bilgi İşlem Şefliği', 16, null, 2, 2, 2, 2, 19", '`id`');

CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "19, 'Çevre Koruma ve Kontrol Müdürlüğü', 2, null, 2, 2, 2, 2, 20", '`id`');

-- baskan yardimcisi 2
CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "20, 'Fen İşleri Müdürlüğü', 3, null, 2, 2, 2, 2, 21", '`id`');
CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "21, 'Yol Bakım ve Onarım Şefliği', 20, null, 2, 2, 2, 2, 22", '`id`');
CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "22, 'Park Bakım Onarım Şefliği', 20, null, 2, 2, 2, 2, 23", '`id`');
CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "23, 'Büro Şefliği', 20, null, 2, 2, 2, 2, 24", '`id`');

CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "24, 'Veteriner İşleri Müdürlüğü', 3, null, 2, 2, 2, 2, 25", '`id`');

-- baskan yardimcisi 3
CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "25, 'Emlak ve İstimlak Müdürlüğü', 4, null, 2, 2, 2, 2, 26", '`id`');
CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "26, 'Emlak Şefliği', 25, null, 2, 2, 2, 2, 27", '`id`');
CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "27, 'Harita Şefliği', 25, null, 2, 2, 2, 2, 28", '`id`');

CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "28, 'İmar ve Şehircilik Müdürlüğü', 4, null, 2, 2, 2, 2, 29", '`id`');
CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "29, 'İmar Şefliği', 28, null, 2, 2, 2, 2, 29", '`id`');
CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "30, 'Kentsel Dönüşüm Şefliği', 28, null, 2, 2, 2, 2, 30", '`id`');

CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "31, 'Zabıta Müdürlüğü', 4, null, 2, 2, 2, 2, 31", '`id`');
CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "32, 'Zabıta Amiri 1', 31, null, 2, 2, 2, 2, 32", '`id`');
CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "33, 'Zabıta Amiri 2', 31, null, 2, 2, 2, 2, 33", '`id`');
CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "34, 'Ruhsat Denetim Şefliği', 31, null, 2, 2, 2, 2, 34", '`id`');

CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "35, 'Hukuk İşleri Müdürlüğü', 4, null, 2, 2, 2, 2, 35", '`id`');

CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "36, 'Destek Hizmetleri Müdürlüğü', 4, null, 2, 2, 2, 2, 36", '`id`');
CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "37, 'Sivil Savunma ve İş Güvenliği Şefliği', 36, null, 2, 2, 2, 2, 37", '`id`');
CALL procedures.insert_if_not_exists('identity_and_access', 'department', '`id`, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth, `order`', "38, 'Büro Şefliği', 36, null, 2, 2, 2, 2, 38", '`id`');

-- citizen_registry

CREATE DATABASE IF NOT EXISTS `citizen_registry`;
ALTER DATABASE `citizen_registry` CHARACTER SET utf8 COLLATE utf8_general_ci;

CALL procedures.create_or_update('citizen_registry', 'citizen', 'id VARCHAR(32) PRIMARY KEY, tcno VARCHAR(11) NOT NULL UNIQUE, firstname VARCHAR(64) NULL, lastname VARCHAR(64) NULL, gender TINYINT(1) NULL, address VARCHAR(512) NULL, phone VARCHAR(24) NULL');

CALL procedures.create_or_update('citizen_registry', 'citizen_variation', "id VARCHAR(32) NOT NULL, varied_field VARCHAR(32) NOT NULL, varied_value VARCHAR(512) NOT NULL, triggering_event VARCHAR(64) NOT NULL DEFAULT('unknown'), date_submitted DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");

-- corporation_registry

CREATE DATABASE IF NOT EXISTS `corporation_registry`;
ALTER DATABASE `corporation_registry` CHARACTER SET utf8 COLLATE utf8_general_ci;

CALL procedures.create_or_update('corporation_registry', 'corporation', 'id VARCHAR(32) PRIMARY KEY, tax_number VARCHAR(11) NOT NULL UNIQUE, tax_office VARCHAR(128) NULL, title VARCHAR(128) NULL, address VARCHAR(512) NULL, phone VARCHAR(24) NULL');

CALL procedures.create_or_update('corporation_registry', 'corporation_variation', "id VARCHAR(32) NOT NULL, varied_field VARCHAR(32) NOT NULL, varied_value VARCHAR(512) NOT NULL, triggering_event VARCHAR(64) NOT NULL DEFAULT('unknown'), date_submitted DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");

-- task_management

CREATE DATABASE IF NOT EXISTS `task_management`;
ALTER DATABASE `task_management` CHARACTER SET utf8 COLLATE utf8_general_ci;

USE `task_management`;

CALL procedures.create_or_update('task_management', 'task', 'id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(256) NOT NULL, assigner INT NULL, description VARCHAR(2048) NULL, start_date DATETIME NULL, due_date DATETIME NULL, priority TINYINT NOT NULL, `status` TINYINT NOT NULL, created_on DATETIME NOT NULL, edited_on DATETIME NULL');

CALL procedures.create_or_update('task_management', 'task_on_hold', 'department_id INT NOT NULL, title VARCHAR(256) NOT NULL, assigner INT NULL, description VARCHAR(2048) NULL, start_date DATETIME NULL, due_date DATETIME NULL, priority TINYINT NOT NULL, `status` TINYINT NOT NULL, created_on DATETIME NOT NULL, edited_on DATETIME NULL');

CALL procedures.create_or_update('task_management', 'task_assignee', 'id INT AUTO_INCREMENT PRIMARY KEY, task_id INT NOT NULL, assignee INT NOT NULL');

CALL procedures.create_or_update('task_management', 'task_location', 'id INT AUTO_INCREMENT PRIMARY KEY, task_id INT NOT NULL, latitude VARCHAR(16) NOT NULL, longitude VARCHAR(16) NOT NULL');

CALL procedures.create_or_update('task_management', 'task_trigger', 'id INT AUTO_INCREMENT PRIMARY KEY, task_id INT NOT NULL, type VARCHAR(256) NOT NULL, data MEDIUMTEXT NULL');

CALL procedures.create_or_update('task_management', 'task_comment', 'id INT AUTO_INCREMENT PRIMARY KEY, task_id INT NOT NULL, commentator INT NOT NULL, message VARCHAR(2048) NOT NULL, commented_on DATETIME NOT NULL, edited_on DATETIME NULL');

CALL procedures.create_or_update('task_management', 'task_event', 'id INT AUTO_INCREMENT PRIMARY KEY, task_id INT NOT NULL, enabler INT NULL, `type` TINYINT NOT NULL, `data` TEXT NULL, occurred_on DATETIME NOT NULL');

CALL procedures.create_or_update('task_management', 'task_attachment', 'id INT AUTO_INCREMENT PRIMARY KEY, task_id INT NOT NULL, uploader INT NULL, name VARCHAR(64) NOT NULL, prefix VARCHAR(256), extension VARCHAR(16), date_added DATETIME NOT NULL');


CALL procedures.create_or_update('task_management', 'subtask', 'id INT AUTO_INCREMENT PRIMARY KEY, task_id INT NOT NULL, title VARCHAR(256) NOT NULL, assigner INT NULL, description VARCHAR(2048) NULL, start_date DATETIME NULL, due_date DATETIME NULL, priority TINYINT NOT NULL, status TINYINT NOT NULL, created_on DATETIME NOT NULL');

CALL procedures.create_or_update('task_management', 'subtask_assignee', 'id INT AUTO_INCREMENT PRIMARY KEY, subtask_id INT NOT NULL, assignee INT NOT NULL');

CALL procedures.create_or_update('task_management', 'subtask_location', 'id INT AUTO_INCREMENT PRIMARY KEY, subtask_id INT NOT NULL, latitude VARCHAR(16) NOT NULL, longitude VARCHAR(16) NOT NULL');

CALL procedures.create_or_update('task_management', 'subtask_comment', 'id INT AUTO_INCREMENT PRIMARY KEY, subtask_id INT NOT NULL, commentator INT NOT NULL, message VARCHAR(2048) NOT NULL, commented_on DATETIME NOT NULL, edited_on DATETIME NULL');

CALL procedures.create_or_update('task_management', 'subtask_event', 'id INT AUTO_INCREMENT PRIMARY KEY, subtask_id INT NOT NULL, enabler INT NULL, `type` TINYINT NOT NULL, `data` TEXT NULL, occurred_on DATETIME NOT NULL');

CALL procedures.create_or_update('task_management', 'subtask_attachment', 'id INT AUTO_INCREMENT PRIMARY KEY, subtask_id INT NOT NULL, uploader INT NULL, name VARCHAR(64) NOT NULL, prefix VARCHAR(256), extension VARCHAR(16), date_added DATETIME NOT NULL');


CALL procedures.create_or_update('task_management', 'task_bin', 'id INT PRIMARY KEY, title VARCHAR(256) NOT NULL, assigner INT NULL, description VARCHAR(2048) NULL, start_date DATETIME NULL, due_date DATETIME NULL, priority TINYINT NOT NULL, `status` TINYINT NOT NULL, created_on DATETIME NOT NULL, edited_on DATETIME NULL, removed_on DATETIME NOT NULL');

CALL procedures.create_or_update('task_management', 'task_assignee_bin', 'id INT PRIMARY KEY, task_id INT NOT NULL, assignee INT NOT NULL, removed_on DATETIME NOT NULL');

CALL procedures.create_or_update('task_management', 'task_location_bin', 'id INT PRIMARY KEY, task_id INT NOT NULL, latitude VARCHAR(16) NOT NULL, longitude VARCHAR(16) NOT NULL, removed_on DATETIME NOT NULL');

CALL procedures.create_or_update('task_management', 'task_trigger_bin', 'id INT AUTO_INCREMENT PRIMARY KEY, task_id INT NOT NULL, type VARCHAR(256) NOT NULL, data MEDIUMTEXT NULL, removed_on DATETIME NOT NULL');

CALL procedures.create_or_update('task_management', 'task_comment_bin', 'id INT PRIMARY KEY, task_id INT NOT NULL, commentator INT NOT NULL, message VARCHAR(2048) NOT NULL, commented_on DATETIME NOT NULL, edited_on DATETIME NULL, removed_on DATETIME NOT NULL');

CALL procedures.create_or_update('task_management', 'task_event_bin', 'id INT PRIMARY KEY, task_id INT NOT NULL, enabler INT NULL, `type` TINYINT NOT NULL, `data` TEXT NULL, occurred_on DATETIME NOT NULL, removed_on DATETIME NOT NULL');

CALL procedures.create_or_update('task_management', 'task_attachment_bin', 'id INT PRIMARY KEY, task_id INT NOT NULL, uploader INT NULL, name VARCHAR(64) NOT NULL, prefix VARCHAR(256), extension VARCHAR(16), date_added DATETIME NOT NULL, removed_on DATETIME NOT NULL');


CALL procedures.create_or_update('task_management', 'subtask_bin', 'id INT PRIMARY KEY, task_id INT NOT NULL, title VARCHAR(256) NOT NULL, assigner INT NULL, description VARCHAR(2048) NULL, start_date DATETIME NULL, due_date DATETIME NULL, priority TINYINT NOT NULL, status TINYINT NOT NULL, created_on DATETIME NOT NULL, removed_on DATETIME NOT NULL');

CALL procedures.create_or_update('task_management', 'subtask_assignee_bin', 'id INT PRIMARY KEY, subtask_id INT NOT NULL, assignee INT NOT NULL, removed_on DATETIME NOT NULL');

CALL procedures.create_or_update('task_management', 'subtask_location_bin', 'id INT PRIMARY KEY, subtask_id INT NOT NULL, latitude VARCHAR(16) NOT NULL, longitude VARCHAR(16) NOT NULL, removed_on DATETIME NOT NULL');

CALL procedures.create_or_update('task_management', 'subtask_comment_bin', 'id INT PRIMARY KEY, subtask_id INT NOT NULL, commentator INT NOT NULL, message VARCHAR(2048) NOT NULL, commented_on DATETIME NOT NULL, edited_on DATETIME NULL, removed_on DATETIME NOT NULL');

CALL procedures.create_or_update('task_management', 'subtask_event_bin', 'id INT PRIMARY KEY, subtask_id INT NOT NULL, enabler INT NULL, `type` TINYINT NOT NULL, `data` TEXT NULL, occurred_on DATETIME NOT NULL, removed_on DATETIME NOT NULL');

CALL procedures.create_or_update('task_management', 'subtask_attachment_bin', 'id INT PRIMARY KEY, subtask_id INT NOT NULL, uploader INT NULL, name VARCHAR(64) NOT NULL, prefix VARCHAR(256), extension VARCHAR(16), date_added DATETIME NOT NULL, removed_on DATETIME NOT NULL');

-- procedure_management

CREATE DATABASE IF NOT EXISTS procedure_management;
ALTER DATABASE procedure_management CHARACTER SET utf8 COLLATE utf8_general_ci;

CALL procedures.create_or_update('procedure_management', 'procedure', "id VARCHAR(32) PRIMARY KEY, container_id VARCHAR(32) NOT NULL, container_type TINYINT NOT NULL, initiator_id VARCHAR(32) NULL, title VARCHAR(128) NOT NULL, description VARCHAR(256) NOT NULL DEFAULT '', `department` INT NOT NULL, started_by INT NULL, current_step VARCHAR(32) NULL, due_date DATETIME NULL, `type` TINYINT NOT NULL, date_created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, is_complete TINYINT NOT NULL DEFAULT 0, completed_on DATETIME NULL");

CALL procedures.create_or_update('procedure_management', 'procedure_bin', "id VARCHAR(32), container_type TINYINT NOT NULL, container_id VARCHAR(32) NOT NULL, initiator_id VARCHAR(32) NULL, title VARCHAR(128) NOT NULL, description VARCHAR(256) NOT NULL DEFAULT '', `department` INT NOT NULL, started_by INT NULL, current_step VARCHAR(32) NULL, due_date DATETIME NULL, `type` TINYINT NOT NULL, date_created DATETIME NOT NULL, removed_on DATETIME NOT NULL, is_complete TINYINT NOT NULL DEFAULT 0, completed_on DATETIME NULL");

CALL procedures.create_or_update('procedure_management', 'procedure_event', 'id VARCHAR(32) PRIMARY KEY, procedure_id VARCHAR(32) NOT NULL, enabler INT NULL, `type` TINYINT NOT NULL, `data` TEXT NULL, occurred_on DATETIME NOT NULL');

CALL procedures.create_or_update('procedure_management', 'procedure_event_bin', 'id VARCHAR(32), procedure_id VARCHAR(32) NOT NULL, enabler INT NULL, `type` TINYINT NOT NULL, `data` TEXT NULL, occurred_on DATETIME NOT NULL');

CALL procedures.create_or_update('procedure_management', 'subprocedure', 'id VARCHAR(32) PRIMARY KEY, parent_id VARCHAR(32) NOT NULL, title VARCHAR(128) NOT NULL, current_step VARCHAR(32) NULL, is_active TINYINT(1) NOT NULL');

CALL procedures.create_or_update('procedure_management', 'subprocedure_bin', 'id VARCHAR(32), parent_id VARCHAR(32) NOT NULL, title VARCHAR(128) NOT NULL, current_step VARCHAR(32) NULL, is_active TINYINT(1) NOT NULL');

CALL procedures.create_or_update('procedure_management', 'step', 'id VARCHAR(32) PRIMARY KEY, procedure_id VARCHAR(32) NOT NULL, title VARCHAR(512) NOT NULL, out_of_scope TINYINT(1) NOT NULL, is_complete TINYINT(1) NOT NULL, completed_by INT NULL, completed_on DATETIME NULL, `order` TINYINT NOT NULL, activated_on DATETIME NULL, due_date DATETIME NULL');

CALL procedures.create_or_update('procedure_management', 'step_bin', 'id VARCHAR(32), procedure_id VARCHAR(32) NOT NULL, title VARCHAR(128) NOT NULL, out_of_scope TINYINT(1) NOT NULL, is_complete TINYINT(1) NOT NULL, completed_by INT NULL, completed_on DATETIME NULL, `order` TINYINT NOT NULL, activated_on DATETIME NULL, due_date DATETIME NULL');

CALL procedures.create_or_update('procedure_management', 'step_trigger', 'step_id VARCHAR(32) NOT NULL, outcome TINYINT NOT NULL, type VARCHAR(256) NOT NULL, data MEDIUMTEXT NULL, description VARCHAR(512) NULL');

CALL procedures.create_or_update('procedure_management', 'step_trigger_bin', 'step_id VARCHAR(32) NOT NULL, outcome TINYINT NOT NULL, type VARCHAR(256) NOT NULL, data MEDIUMTEXT NULL, description VARCHAR(512) NULL');

CALL procedures.create_or_update('procedure_management', 'step_choice', 'step_id VARCHAR(32) NOT NULL, message VARCHAR(64) NOT NULL, `next_step_id` VARCHAR(32) NULL, subprocedure_id VARCHAR(32) NULL, `type` TINYINT NOT NULL, `number` TINYINT NOT NULL, `wait_for_days` INT NULL, is_selected TINYINT NOT NULL DEFAULT 0');

CALL procedures.create_or_update('procedure_management', 'step_choice_bin', 'step_id VARCHAR(32) NOT NULL, message VARCHAR(64) NOT NULL, `next_step_id` VARCHAR(32) NULL, subprocedure_id VARCHAR(32) NULL, `type` TINYINT NOT NULL, `number` TINYINT NOT NULL, `wait_for_days` INT NULL, is_selected TINYINT NOT NULL DEFAULT 0');

CALL procedures.create_or_update('procedure_management', 'step_comment', 'id VARCHAR(32) PRIMARY KEY, step_id VARCHAR(32) NOT NULL, commentator INT NULL, message VARCHAR(512), edited_on DATETIME NULL, commented_on DATETIME NOT NULL');

CALL procedures.create_or_update('procedure_management', 'step_comment_bin', 'id VARCHAR(32), step_id VARCHAR(32) NOT NULL, commentator INT NULL, message VARCHAR(512), edited_on DATETIME NULL, commented_on DATETIME NOT NULL, removed_on DATETIME NOT NULL');

CALL procedures.create_or_update('procedure_management', 'step_attachment', 'id VARCHAR(32) PRIMARY KEY, step_id VARCHAR(32) NOT NULL, uploader INT NOT NULL, `name` VARCHAR(64) NOT NULL, `prefix` VARCHAR(256) NULL, `extension` VARCHAR(16) NULL, `date_added` DATETIME NOT NULL');

CALL procedures.create_or_update('procedure_management', 'step_attachment_bin', 'id VARCHAR(32), step_id VARCHAR(32) NOT NULL, uploader INT NOT NULL, `name` VARCHAR(64) NOT NULL, `prefix` VARCHAR(256) NULL, `extension` VARCHAR(16) NULL, `date_added` DATETIME NOT NULL, removed_on DATETIME NOT NULL');

CALL procedures.create_or_update('procedure_management', 'application', 'id VARCHAR(32) NOT NULL PRIMARY KEY, procedure_id VARCHAR(32) NOT NULL, form_data MEDIUMTEXT NOT NULL, initiator_identifier VARCHAR(16) NOT NULL, initiator_type TINYINT NOT NULL, applied_on DATETIME NOT NULL');

CALL procedures.create_or_update('procedure_management', 'application_bin', 'id VARCHAR(32) NOT NULL, procedure_id VARCHAR(32) NOT NULL, form_data MEDIUMTEXT NOT NULL, initiator_identifier VARCHAR(16) NOT NULL, initiator_type TINYINT NOT NULL, applied_on DATETIME NOT NULL, removed_on DATETIME NOT NULL');

CALL procedures.create_or_update('procedure_management', 'application_file', 'id VARCHAR(32) NOT NULL PRIMARY KEY, application_id VARCHAR(32) NOT NULL, field_id VARCHAR(32) NOT NULL, `name` VARCHAR(64) NOT NULL, `extension` VARCHAR(16) NULL');

CALL procedures.create_or_update('procedure_management', 'application_file_bin', 'id VARCHAR(32) NOT NULL, application_id VARCHAR(32) NOT NULL, field_id VARCHAR(32) NOT NULL, `name` VARCHAR(64) NOT NULL, `extension` VARCHAR(16) NULL, removed_on DATETIME NOT NULL');

-- structure_profile

CREATE DATABASE IF NOT EXISTS structure_profile;
ALTER DATABASE structure_profile CHARACTER SET utf8 COLLATE utf8_general_ci;

CALL procedures.create_or_update('structure_profile', 'comment', 'id VARCHAR(32) PRIMARY KEY, structure_id INT NOT NULL, commentator INT NULL, message VARCHAR(512), edited_on DATETIME NULL, commented_on DATETIME NOT NULL');

CALL procedures.create_or_update('structure_profile', 'comment_bin', 'id VARCHAR(32), structure_id INT NOT NULL, commentator INT NULL, message VARCHAR(512), edited_on DATETIME NULL, commented_on DATETIME NOT NULL, removed_on DATETIME NOT NULL');

CALL procedures.create_or_update('structure_profile', 'attachment', 'id VARCHAR(32) PRIMARY KEY, structure_id INT NOT NULL, uploader INT NOT NULL, `name` VARCHAR(64) NOT NULL, `prefix` VARCHAR(256) NULL, `extension` VARCHAR(16) NULL, `date_added` DATETIME NOT NULL');

CALL procedures.create_or_update('structure_profile', 'attachment_bin', 'id VARCHAR(32), structure_id INT NOT NULL, uploader INT NOT NULL, `name` VARCHAR(64) NOT NULL, `prefix` VARCHAR(256) NULL, `extension` VARCHAR(16) NULL, `date_added` DATETIME NOT NULL, removed_on DATETIME NOT NULL');

CALL procedures.create_or_update('structure_profile', 'parcel_comment', 'id VARCHAR(32) PRIMARY KEY, ada VARCHAR(8) NOT NULL, no VARCHAR(8) NOT NULL, commentator INT NULL, message VARCHAR(512), edited_on DATETIME NULL, commented_on DATETIME NOT NULL');

CALL procedures.create_or_update('structure_profile', 'parcel_comment_bin', 'id VARCHAR(32), ada VARCHAR(8) NOT NULL, no VARCHAR(8) NOT NULL, commentator INT NULL, message VARCHAR(512), edited_on DATETIME NULL, commented_on DATETIME NOT NULL, removed_on DATETIME NOT NULL');

CALL procedures.create_or_update('structure_profile', 'custom_feature_attachment', 'id VARCHAR(32) PRIMARY KEY, custom_feature_id VARCHAR(16) NOT NULL, uploader INT NOT NULL, `name` VARCHAR(64) NOT NULL, `prefix` VARCHAR(256) NULL, `extension` VARCHAR(16) NULL, `date_added` DATETIME NOT NULL');

CALL procedures.create_or_update('structure_profile', 'custom_feature_attachment_bin', 'id VARCHAR(32), custom_feature_id VARCHAR(16) NOT NULL, uploader INT NOT NULL, `name` VARCHAR(64) NOT NULL, `prefix` VARCHAR(256) NULL, `extension` VARCHAR(16) NULL, `date_added` DATETIME NOT NULL, removed_on DATETIME NOT NULL');

-- file_management

CREATE DATABASE IF NOT EXISTS file_management;
ALTER DATABASE file_management CHARACTER SET utf8 COLLATE utf8_general_ci;

USE file_management;

CALL procedures.create_or_update('file_management', 'directory', 'id VARCHAR(13) UNIQUE NOT NULL PRIMARY KEY, submodule_id INT NOT NULL, parent_id VARCHAR(13) NULL, name VARCHAR(64) NOT NULL, date_added DATETIME NOT NULL');

CALL procedures.create_or_update('file_management', 'directory_bin', 'id VARCHAR(13) NOT NULL, submodule_id INT NOT NULL, parent_id VARCHAR(13) NULL, name VARCHAR(64) NOT NULL, date_added DATETIME NOT NULL, removed_on DATETIME NOT NULL');

CALL procedures.create_or_update('file_management', 'file', 'id VARCHAR(13) UNIQUE NOT NULL PRIMARY KEY, submodule_id INT NOT NULL, directory_id VARCHAR(13) NULL, name VARCHAR(64) NOT NULL, prefix VARCHAR(256), mime_type VARCHAR(128), extension VARCHAR(16), date_added DATETIME NOT NULL');

CALL procedures.create_or_update('file_management', 'file_bin', 'id VARCHAR(13) NOT NULL, submodule_id INT NOT NULL, directory_id VARCHAR(13) NULL, name VARCHAR(64) NOT NULL, prefix VARCHAR(256), mime_type VARCHAR(128), extension VARCHAR(16), date_added DATETIME NOT NULL, removed_on DATETIME NOT NULL');

-- system

CREATE DATABASE IF NOT EXISTS `system`;
ALTER DATABASE `system` CHARACTER SET utf8 COLLATE utf8_general_ci;

USE `system`;

-- CALL procedures.create_or_update('system', 'setting', 'key VARCHAR(64) NOT NULL UNIQUE PRIMARY KEY, value VARCHAR(256) NULL, category VARCHAR(64) NOT NULL');

CREATE TABLE IF NOT EXISTS `setting` (
	`key` VARCHAR(64) NOT NULL UNIQUE PRIMARY KEY,
	`value` VARCHAR(256) NULL,
	`category` VARCHAR(64) NOT NULL
);

INSERT IGNORE INTO `setting` (`key`, `value`, `category`)
VALUES
	('logo_id', '', 'general'),
	('icon_id', '', 'general'),
	('title', '', 'general'),
	('tax_office', '', 'general'),
	('tax_number', '', 'general'),

	('protocol', '', 'email'),
	('hostname', '', 'email'),
	('email', '', 'email'),
	('password', '', 'email'),
	('port', '', 'email'),
	('timeout', '', 'email'),

	('google_analytics_key', '', 'other');

CALL procedures.create_or_update('system', 'log_operation', '`id` INT AUTO_INCREMENT PRIMARY KEY, source INT NULL, result INT NOT NULL, operation VARCHAR(64) NOT NULL, operator INT NULL, module_id INT NULL, operation_date DATETIME NOT NULL');

CALL procedures.insert_if_not_exists('system', 'log_operation', 'id, source, result, operation, operator, module_id', '1, null, 1, "", 1, 1', 'operation_id');
CALL procedures.insert_if_not_exists('system', 'log_operation', 'id, source, result, operation, operator, module_id', '2, null, 2, "", 1, 2', 'operation_id');
CALL procedures.insert_if_not_exists('system', 'log_operation', 'id, source, result, operation, operator, module_id', '3, null, 2, "", 1, 2', 'operation_id');

CALL procedures.create_or_update('system', 'log_node', 'node_id INT AUTO_INCREMENT PRIMARY KEY, `type` VARCHAR(32) NOT NULL, `id` VARCHAR(16) NOT NULL, `data` TEXT NULL, `version` SMALLINT NOT NULL');

INSERT IGNORE INTO `system`.`log_node` (node_id, type, id, data, version)
VALUES
	('1', 'personnel', '1', '{"firstname":"Sistem", "lastname":"Yöneticisi"}', '1'),
	('2', 'role', '1', '{"name": "superuser"}', '1');

-- CALL procedures.insert_if_not_exists('system', 'log_node', 'node_id, type, id, data, version', '1, personnel, 1, { firstname: root lastname: root image_id: null }, 1', 'node_id');


-- Sdm Databas
CREATE DATABASE `sdm`;

USE `sdm`;

CREATE TABLE IF NOT EXISTS `grocer` (
	`id` VARCHAR(24) PRIMARY KEY NOT NULL,
	`first_name` VARCHAR(64) NOT NULL,
	`last_name` VARCHAR(64) NOT NULL,
	`email` VARCHAR(64) NOT NULL,
	`phone` VARCHAR(24) NOT  NULL,
	`tc_kimlik` VARCHAR(11) NOT NULL,
	`balance` VARCHAR(24) NOT NULL,
	`company` VARCHAR(64) NOT NULL,
   	`category` VARCHAR(64)  NOT NULL,
  	`tax_office` VARCHAR(64)  NOT NULL,
    `tax_number`  VARCHAR(10) NOT NULL,
  	`city` VARCHAR(64)  NOT NULL,
  	`town` VARCHAR(64) NOT NULL,
  	`district`VARCHAR(64)  NOT NULL,
  	`street`VARCHAR(64)  NOT NULL,
  	`building_no`INT  NOT NULL,
  	`door_no` INT NOT NULL,
	`created_on` DATETIME NOT NULL,
	`last_used_on` DATETIME NULL,
	`updated_on` DATETIME NULL
);

USE `sdm`;

CREATE TABLE IF NOT EXISTS `transaction` (
	`id` VARCHAR(24) PRIMARY KEY NOT NULL,
	`deptor_id` VARCHAR(64) NOT NULL,
	`payee_id` VARCHAR(64) NOT NULL,
	`amount` VARCHAR(64) NOT NULL,
	`status` INT NOT NULL,
	`transaction_date` DATETIME NOT NULL,
	`verification_code` VARCHAR(6)	
);

CREATE TABLE IF NOT EXISTS `login` (
	`id` INT AUTO_INCREMENT PRIMARY KEY,
	`grocer_id` VARCHAR(24) NOT NULL,
	`phone` VARCHAR(64) NOT NULL,
	`password` VARCHAR(128) NOT NULL,
	`salt` VARCHAR(9) NOT NULL,
	`is_active` TINYINT,
	`date_added` DATETIME NOT NULL,
	`last_modification` DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS `auth_session` (
	`grocer_id` VARCHAR(24) PRIMARY KEY NOT NULL,
	`token` VARCHAR(64) NOT NULL,
	`ip` VARCHAR(40) NOT NULL,
	`expires_in` VARCHAR(64) NOT NULL,
	`last_operation` TIMESTAMP NOT NULL
);

CREATE TABLE IF NOT EXISTS `password_reset` ( 
	`grocer_id` VARCHAR(24) PRIMARY KEY NOT NULL,
	`token` VARCHAR(64) NOT NULL 
);

CREATE TABLE IF NOT EXISTS `creating_password_token` ( 
	`grocer_id` VARCHAR(24) PRIMARY KEY NOT NULL,
	`token` VARCHAR(64) NOT NULL 
);




