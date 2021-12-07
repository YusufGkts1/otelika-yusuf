CREATE DATABASE IF NOT EXISTS `polling`;
ALTER DATABASE `polling` CHARACTER SET utf8 COLLATE utf8_general_ci;

USE `polling`;

CREATE TABLE IF NOT EXISTS `flags` (
	`key` VARCHAR(64) PRIMARY KEY,
	`value` TEXT NOT NULL
);
INSERT IGNORE INTO `flags`
	(`key`, `value`)
VALUES
	('halt_message_delivery', 0);

CREATE TABLE IF NOT EXISTS `queue` (
	`id` VARCHAR(36) PRIMARY KEY,
	`is_paused` TINYINT(1) NOT NULL,
	`created_on` DATETIME NOT NULL,
	`updated_on` DATETIME NULL
);
ALTER TABLE `queue` ADD COLUMN IF NOT EXISTS `name` VARCHAR(256) NOT NULL DEFAULT 'Kuyruk' AFTER `id`;
ALTER TABLE `queue` ADD COLUMN IF NOT EXISTS is_complete tinyint(1) NOT NULL COMMENT '0 = Not Complete, 1 = Complete' DEFAULT 0 AFTER `is_paused`;
ALTER TABLE `queue` ADD COLUMN IF NOT EXISTS survey_id VARCHAR(36) NULL COMMENT 'null if no survey is attached' DEFAULT NULL AFTER `is_complete`;

CREATE TABLE IF NOT EXISTS `queue_bin` (
	`id` VARCHAR(36),
	`name` VARCHAR(256) NOT NULL DEFAULT 'Kuyruk',
	`is_paused` TINYINT(1) NOT NULL,
	`created_on` DATETIME NOT NULL,
	`updated_on` DATETIME NULL,
	`removed_on` DATETIME NOT NULL
);
ALTER TABLE `queue_bin` ADD COLUMN IF NOT EXISTS is_complete tinyint(1) NOT NULL COMMENT '0 = Not Complete, 1 = Complete' DEFAULT 0 AFTER `is_paused`;
ALTER TABLE `queue_bin` ADD COLUMN IF NOT EXISTS survey_id VARCHAR(36) NULL COMMENT 'null if no survey is attached' DEFAULT NULL AFTER `is_complete`;

CREATE TABLE IF NOT EXISTS `message` (
	`id` VARCHAR(36) PRIMARY KEY,
	`citizen_id` VARCHAR(36) NOT NULL,
	`queue_id` VARCHAR(36) NULL,
	`content` TEXT NOT NULL,
	`status` TINYINT NOT NULL COMMENT '1 = Pending, 2 = Successful, 3 = Failed',
	`processed` TINYINT NOT NULL,
	`processed_on` DATETIME NULL
);
ALTER TABLE `message` ADD COLUMN IF NOT EXISTS `token` VARCHAR(36) NULL COMMENT 'null if no token is attached' DEFAULT NULL AFTER `processed_on`;
ALTER TABLE `message` MODIFY COLUMN `token` VARCHAR(128) NULL COMMENT 'null if no token is attached' DEFAULT NULL;

CREATE TABLE IF NOT EXISTS `message_bin` (
	`id` VARCHAR(36),
	`citizen_id` VARCHAR(36) NOT NULL,
	`queue_id` VARCHAR(36) NULL,
	`content` TEXT NOT NULL,
	`status` TINYINT NOT NULL COMMENT '1 = Pending, 2 = Successful, 3 = Failed',
	`processed` TINYINT NOT NULL,
	`processed_on` DATETIME NULL,
	`removed_on` DATETIME NOT NULL
);
ALTER TABLE `message_bin` ADD COLUMN IF NOT EXISTS `token` VARCHAR(36) NULL COMMENT 'null if no token is attached' DEFAULT NULL AFTER `processed_on`;
ALTER TABLE `message_bin` MODIFY COLUMN `token` VARCHAR(128) NULL COMMENT 'null if no token is attached' DEFAULT NULL;

CREATE TABLE IF NOT EXISTS `queue_message` (
	`queue_id` VARCHAR(36) NOT NULL,
	`message` TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS `queue_message_bin` (
	`queue_id` VARCHAR(36),
	`message` TEXT NOT NULL,
	`removed_on` DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS `message_error` (
	`message_id` VARCHAR(36) NOT NULL,
	`error` TEXT
);

CREATE TABLE IF NOT EXISTS `message_error_bin` (
	`message_id` VARCHAR(36),
	`error` TEXT,
	`removed_on` DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS `citizen_queue` (
	`citizen_id` VARCHAR(36) NOT NULL,
	`queue_id` VARCHAR(36) NOT NULL,
	`processed` TINYINT(1) NOT NULL COMMENT '1 = Not Processed, 2 = Processed'
);

CREATE TABLE IF NOT EXISTS `citizen_queue_bin` (
	`citizen_id` VARCHAR(36) NOT NULL,
	`queue_id` VARCHAR(36) NOT NULL,
	`processed` TINYINT(1) NOT NULL COMMENT '1 = Not Processed, 2 = Processed',
	`removed_on` DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS `citizen` (
	`id` VARCHAR(36) PRIMARY KEY,
	`kimlik_no` VARCHAR(32) NOT NULL,

	`dogum_yer_kod` INT NULL,

	`medeni_hal` INT NULL COMMENT 'medeni hal kodu',
	`durum` INT NULL COMMENT 'durum kodu',
	`olum_tarih` DATE NULL,

	`kayit_aile_sira_no` INT NULL,
	`kayit_birey_sira_no` INT NULL,
	`kayit_cilt` INT NULL COMMENT 'cilt kodu',
	`kayit_il` INT NULL COMMENT 'il kodu',
	`kayit_ilce` INT NULL COMMENT 'ilce kodu',

	`ad` TEXT NULL,
	`soyad` TEXT NULL,
	`telefon` TEXT NULL,
	`anne_ad` TEXT NULL,
	`baba_ad` TEXT NULL,
	`cinsiyet` TINYINT NULL COMMENT 'cinsiyet kodu',
	`dogum_tarih` DATE NULL,
	`dogum_yer` TEXT NULL,

	`cuzdan_kayit_no` TEXT NULL,
	`cuzdan_no` TEXT NULL,
	`cuzdan_seri` TEXT NULL,

	`tckk_seri_no` TEXT NULL,

	`adres_no` TEXT NULL,
	`acik_adres` TEXT NULL,
	`mahalle_kodu` VARCHAR(8) NULL,
	`csbm_kodu` VARCHAR(12) NULL,
	`bina_no` VARCHAR(12) NULL,
	`ada` VARCHAR(16) NULL,
	`parsel` VARCHAR(16) NULL,
	`dis_kapi_no` VARCHAR(8) NULL,
	`ic_kapi_no` VARCHAR(8) NULL,

	`yabanci` TINYINT NOT NULL,

	`bitis_tarihi_belirsiz_olma_neden` INT NULL COMMENT 'bitis_tarihi_belirsiz_olma_neden kodu',
	`izin_baslangic_tarih` DATE NULL,
	`izin_bitis_tarih` DATE NULL,
	`izin_duzenlenen_il` INT NULL COMMENT 'il kodu',
	`kaynak_birim` INT NULL COMMENT 'kaynak_birim kodu',
	`kazanilan_tc_kimlik_no` TEXT NULL,
	`uyruk` INT NULL COMMENT 'uyruk kodu'
);

ALTER TABLE `citizen` ADD COLUMN IF NOT EXISTS `verified` TINYINT NOT NULL COMMENT '1 = Not Verified, 2 = Verified' DEFAULT 1 AFTER `uyruk`;

DROP INDEX IF EXISTS idx_citizen_fulltext_name ON citizen;
CREATE FULLTEXT INDEX IF NOT EXISTS idx_citizen_fulltext_name ON citizen(ad);
DROP INDEX IF EXISTS idx_citizen_fulltext_surname ON citizen;
CREATE FULLTEXT INDEX IF NOT EXISTS idx_citizen_fulltext_surname ON citizen(soyad);
DROP INDEX IF EXISTS idx_citizen_fulltext_fullname ON citizen;
CREATE FULLTEXT INDEX IF NOT EXISTS idx_citizen_fulltext_fullname ON citizen(ad, soyad);
DROP INDEX IF EXISTS idx_citizen_fulltext_kimlik_no ON citizen;
CREATE FULLTEXT INDEX IF NOT EXISTS idx_citizen_fulltext_kimlik_no ON citizen(kimlik_no);
DROP INDEX IF EXISTS idx_citizen_fulltext ON citizen;
CREATE FULLTEXT INDEX IF NOT EXISTS idx_citizen_fulltext ON citizen(kimlik_no, ad, soyad, telefon, anne_ad, baba_ad, cuzdan_no, tckk_seri_no);

CREATE TABLE IF NOT EXISTS `message_template` (
	`id` VARCHAR(36) PRIMARY KEY,
	`content` TEXT NOT NULL,
	`created_on` DATETIME NOT NULL,
	`last_used_on` DATETIME NULL,
	`updated_on` DATETIME NULL
);

ALTER TABLE `message_template` ADD COLUMN IF NOT EXISTS `name` VARCHAR(128) NOT NULL DEFAULT 'name' AFTER `id`;

CREATE TABLE IF NOT EXISTS `message_template_bin` (
	`id` VARCHAR(36),
	`content` TEXT NOT NULL,
	`created_on` DATETIME NOT NULL,
	`last_used_on` DATETIME NULL,
	`updated_on` DATETIME NULL,
	`removed_on` DATETIME NOT NULL
);

ALTER TABLE `message_template_bin` ADD COLUMN IF NOT EXISTS `name` VARCHAR(128) NOT NULL DEFAULT 'name' AFTER `id`;

CREATE TABLE IF NOT EXISTS `filter_template` (
	`id` VARCHAR(36) PRIMARY KEY,
	`filter` TEXT NOT NULL,
	`created_on` DATETIME NOT NULL,
	`last_used_on` DATETIME NULL,
	`updated_on` DATETIME NULL
);

ALTER TABLE `filter_template` ADD COLUMN IF NOT EXISTS `name` VARCHAR(128) NOT NULL DEFAULT 'name' AFTER `id`;

CREATE TABLE IF NOT EXISTS `filter_template_bin` (
	`id` VARCHAR(36),
	`filter` TEXT NOT NULL,
	`created_on` DATETIME NOT NULL,
	`last_used_on` DATETIME NULL,
	`updated_on` DATETIME NULL,
	`removed_on` DATETIME NOT NULL
);

ALTER TABLE `filter_template_bin` ADD COLUMN IF NOT EXISTS `name` VARCHAR(128) NOT NULL DEFAULT 'name' AFTER `id`;

CREATE TABLE IF NOT EXISTS `category` (
	`id` VARCHAR(36) PRIMARY KEY,
	`name` VARCHAR(128) NOT NULL,
	`color` VARCHAR(12) NOT NULL DEFAULT '#636363',
	`parent_id` VARCHAR(36) NULL,
	`created_on` DATETIME NOT NULL
);

INSERT IGNORE INTO `category` (`id`, `name`, `color`, `parent_id`, `created_on`) VALUES ('1', 'Kategorilendirilmemiş', '#7a7a7a', null, '0000-00-00');

CREATE TABLE IF NOT EXISTS `category_bin` (
	`id` VARCHAR(36),
	`name` VARCHAR(128) NOT NULL,
	`color` VARCHAR(12) NOT NULL,
	`parent_id` VARCHAR(36) NULL,
	`created_on` DATETIME NOT NULL,
	`removed_on` DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS `tag` (
	`id` VARCHAR(36) PRIMARY KEY,
	`value` VARCHAR(128) NOT NULL,
	`created_on` DATETIME NOT NULL,
	`last_used_on` DATETIME NULL
);

ALTER TABLE `tag` ADD COLUMN IF NOT EXISTS `color` VARCHAR(12) NOT NULL DEFAULT '#B6F9D5' AFTER `id`;
ALTER TABLE `tag` ADD COLUMN IF NOT EXISTS `category_id` VARCHAR(36) NULL DEFAULT NULL AFTER `value`;

CREATE TABLE IF NOT EXISTS `tag_bin` (
	`id` VARCHAR(36),
	`value` VARCHAR(128) NOT NULL,
	`created_on` DATETIME NOT NULL,
	`last_used_on` DATETIME NULL,
	`removed_on` DATETIME NOT NULL
);

ALTER TABLE `tag_bin` ADD COLUMN IF NOT EXISTS `color` VARCHAR(12) NOT NULL AFTER `id`;
ALTER TABLE `tag_bin` ADD COLUMN IF NOT EXISTS `category_id` VARCHAR(36) NULL DEFAULT NULL AFTER `value`;

CREATE TABLE IF NOT EXISTS `tag_citizen` (
	`tag_id` VARCHAR(36) NOT NULL,
	`citizen_id` VARCHAR(36) NOT NULL,
	`tagged_on` DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS `tag_citizen_bin` (
	`tag_id` VARCHAR(36) NOT NULL,
	`citizen_id` VARCHAR(36) NOT NULL,
	`tagged_on` DATETIME NOT NULL,
	`removed_on` DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS `survey` (
	`id` VARCHAR(36) PRIMARY KEY,
	`name` VARCHAR(128) NOT NULL,
	`form` TEXT NOT NULL,
	`created_on` DATETIME NOT NULL,
	`last_used_on` DATETIME NULL,
	`is_hidden` TINYINT(1) NOT NULL COMMENT '1 = Not Hidden, 2 = Hidden' DEFAULT 1
);

CREATE TABLE IF NOT EXISTS `survey_file` (
	`id` VARCHAR(36) PRIMARY KEY,
	`name` VARCHAR(128) NOT NULL,
	`extension` VARCHAR(16) NULL,
	`is_logo` TINYINT(1) NOT NULL,
	`survey_id` VARCHAR(36) NOT NULL
);

CREATE TABLE IF NOT EXISTS `citizen_survey` (
	`citizen_id` VARCHAR(36) NOT NULL,
	`survey_id` VARCHAR(36) NOT NULL
);
ALTER TABLE `citizen_survey` ADD COLUMN IF NOT EXISTS added_on DATETIME NOT NULL;

CREATE TABLE IF NOT EXISTS `token` (
	`token` VARCHAR(128) NOT NULL,
	`citizen_id` VARCHAR(36) NOT NULL,
	`survey_id` VARCHAR(36) NOT NULL,
	`redeemed_on` DATETIME NULL
);

CREATE TABLE IF NOT EXISTS `submission` (
	`id` VARCHAR(36) PRIMARY KEY,
	`data` TEXT NOT NULL,
	`submitted_on` DATETIME NOT NULL,
	`citizen_id` VARCHAR(36) NOT NULL,
	`survey_id` VARCHAR(36) NOT NULL,
	`token` VARCHAR(128) NOT NULL
);

CREATE TABLE IF NOT EXISTS `submission_response` (
	`submission_id` VARCHAR(36) NOT NULL,
	`survey_id` VARCHAR(36) NOT NULL,
	`field_name` VARCHAR(128) NOT NULL,
	`field_type` VARCHAR(32) NOT NULL,
	`value` TEXT NULL
);

CREATE TABLE IF NOT EXISTS `tag_rule` (
	`id` VARCHAR(36) PRIMARY KEY,
	`name` VARCHAR(128) NOT NULL,
	`filter` TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS `tag_rule_bin` (
	`id` VARCHAR(36),
	`name` VARCHAR(128) NOT NULL,
	`filter` TEXT NOT NULL,
	`removed_on` DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS `tag_rule_tag` (
	`tag_rule_id` VARCHAR(36) NOT NULL,
	`tag_id` VARCHAR(36) NOT NULL
);

CREATE TABLE IF NOT EXISTS `tag_rule_tag_bin` (
	`tag_rule_id` VARCHAR(36) NOT NULL,
	`tag_id` VARCHAR(36) NOT NULL,
	`removed_on` DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS `tag_rule_survey` (
	`tag_rule_id` VARCHAR(36) NOT NULL,
	`survey_id` VARCHAR(36) NOT NULL
);

CREATE TABLE IF NOT EXISTS `tag_rule_survey_bin` (
	`tag_rule_id` VARCHAR(36) NOT NULL,
	`survey_id` VARCHAR(36) NOT NULL,
	`removed_on` DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS medeni_hal (
	kod INT PRIMARY KEY,
	aciklama VARCHAR(512) NULL
);

CREATE TABLE IF NOT EXISTS durum (
	kod INT PRIMARY KEY,
	aciklama VARCHAR(512) NULL
);

CREATE TABLE IF NOT EXISTS kayit_cilt (
	kod INT PRIMARY KEY,
	aciklama VARCHAR(512) NULL
);

CREATE TABLE IF NOT EXISTS il (
	kod INT PRIMARY KEY,
	aciklama VARCHAR(512) NULL
);

CREATE TABLE IF NOT EXISTS ilce (
	kod INT PRIMARY KEY,
	aciklama VARCHAR(512) NULL
);

CREATE TABLE IF NOT EXISTS cinsiyet (
	kod INT PRIMARY KEY,
	aciklama VARCHAR(512) NULL
);

CREATE TABLE IF NOT EXISTS cuzdan_verildigi_ilce (
	kod INT PRIMARY KEY,
	aciklama VARCHAR(512) NULL
);

CREATE TABLE IF NOT EXISTS bitis_tarihi_belirsiz_olma_neden (
	kod INT PRIMARY KEY,
	aciklama VARCHAR(512) NULL
);

CREATE TABLE IF NOT EXISTS kaynak_birim (
	kod INT PRIMARY KEY,
	aciklama VARCHAR(512) NULL
);

CREATE TABLE IF NOT EXISTS uyruk (
	kod INT PRIMARY KEY,
	aciklama VARCHAR(512) NULL
);

CREATE TABLE IF NOT EXISTS district (
	kod INT PRIMARY KEY, 
	ad VARCHAR(128) NOT NULL
);

CREATE TABLE IF NOT EXISTS csbm (
	kod INT PRIMARY KEY,
	mahalle_kodu INT NOT NULL,
	ad VARCHAR(100) NOT NULL,
	tip VARCHAR(32) NOT NULL
);

