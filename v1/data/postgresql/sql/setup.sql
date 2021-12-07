-- CREATE OR REPLACE PROCEDURE create_database_if_not_exists(db_name TEXT) LANGUAGE plpgsql AS 
-- $$
-- 	EXECUTE "SELECT CONCAT('CREATE DATABASE ', $db_name) 
-- 	WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = $db_name)\gexec"
-- $$

-- CALL create_database_if_not_exists('gis');

-- CREATE TABLE IF NOT EXISTS 'location' (
-- 	'id' INT AUTO_INCREMENT PRIMARY KEY,
-- 	'geometry' GEOMETRY NOT NULL
-- );





-- CREATE TABLE IF NOT EXISTS zoning_status (
-- 	id SERIAL PRIMARY KEY,
-- 	name VARCHAR(128) NULL,
-- 	type VARCHAR(64) NOT NULL,
-- 	"geometry" GEOMETRY NOT NULL,
-- 	layer VARCHAR(64) NOT NULL
-- );