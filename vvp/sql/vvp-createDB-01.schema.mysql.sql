/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

/**
 * For VVP database:
 * 1) Drop FUNCTIONS/PROCEDURES/TABLES if they exist
 * 2) Create FUNCTIONS/PROCEDURES/TABLES and access definitions
 *
 * NOTE: You cannot have TWO TIMESTAMP columns in the same table with defaults 
 * that reference CURRENT_TIMESTAMP.
 *
 * LOG:
 * 2015-01-05: changed mysql engine to [InnoDB]
 * 2014-12-29: Change 'moddte TIMESTAMP' to comply with WampServer / MySQL 5.6.17.
 */

-- =================================
-- DROP DATABASE IF EXISTS dbname;
-- CREATE DATABASE dbname;

-- USE dbname;
-- =================================

SET foreign_key_checks=0;

-- -------------------

DROP FUNCTION IF EXISTS date_time_display_01;
DELIMITER $$
CREATE FUNCTION date_time_display_01(p_date TIMESTAMP)  -- TEXT)
	RETURNS TEXT
	LANGUAGE SQL
BEGIN
	RETURN DATE_FORMAT(p_date, '%W, %M %D, %Y, %h:%i:%s %p');
END; $$
DELIMITER ;

-- -------------------

DROP PROCEDURE IF EXISTS add_next_video_to_vidpage;
DELIMITER $$
/**
 * INSERT -or- UPDATE procedure for table 'vidpage_videos':
 */
CREATE PROCEDURE add_next_video_to_vidpage
	(IN p_vidpage_id VARCHAR(10), IN p_vid_flnam VARCHAR(60), IN p_alt_text VARCHAR(30),
	 IN p_alt_txt2 VARCHAR(30), IN p_vid_type VARCHAR(4), IN p_aspect CHAR(2),
	 IN p_time_created DECIMAL(13,3) UNSIGNED)
	-- DISALLOW DUPE FILENAMES - Only INSERT if video filename does not already exist
	BEGIN
		DECLARE checkIfExists INT;
		SET checkIfExists = 0;
		SELECT count(*) INTO checkIfExists 
					 FROM vidpage_videos 
					WHERE vidpage_id = p_vidpage_id 
					  AND vid_flnam = p_vid_flnam;
		IF (p_alt_text = '') THEN 
			SET p_alt_text = p_vid_flnam;
		END IF;
		IF (checkIfExists = 0) THEN
			INSERT INTO vidpage_videos (
							  vidpage_id, vid_flnam, alt_text, alt_txt2, vid_type, aspect,
								time_created) 
				 VALUES (p_vidpage_id, p_vid_flnam, p_alt_text, p_alt_txt2, p_vid_type, 
						 p_aspect, p_time_created);
		ELSE
			UPDATE vidpage_videos 
			   SET alt_text = p_alt_text, alt_txt2 = p_alt_txt2, vid_type = p_vid_type, 
				   aspect = p_aspect 
			 WHERE vidpage_id = p_vidpage_id 
			   AND vid_flnam = p_vid_flnam;
		END IF;
	END; $$
DELIMITER ;

-- -------------------

DROP PROCEDURE IF EXISTS delete_user;
DELIMITER $$
/**
 * DELETE user from database:
 */
CREATE PROCEDURE delete_user
	(IN p_user_name VARCHAR(130))
	BEGIN
		DECLARE custNo INT(5);
		SET custNo = 0;
		SELECT cust_no INTO custNo
					   FROM users
					  WHERE user_name = p_user_name;
		DELETE FROM vidpage_videos WHERE vidpage_id = custNo;
		DELETE FROM user_activity  WHERE user_name  = p_user_name;
		DELETE FROM customers      WHERE cust_no    = custNo;
		DELETE FROM users          WHERE user_name  = p_user_name;
		DELETE FROM login_failures WHERE user_name  = p_user_name;
	END; $$
DELIMITER ;

-- -------------------

DROP TABLE IF EXISTS vidpage_videos;
CREATE TABLE vidpage_videos (
	vidpage_id	  VARCHAR(10) NOT NULL,
	vid_flnam	  VARCHAR(60) NOT NULL,
	alt_text	  VARCHAR(30) NOT NULL,
	alt_txt2	  VARCHAR(30) NOT NULL,
	vid_type	  VARCHAR(4)  NOT NULL,
	aspect		  ENUM('HD', 'SD') NOT NULL,
	time_created  DECIMAL(13,3) UNSIGNED NOT NULL, -- PHP sys datetime in seconds.microseconds
	moddte		  TIMESTAMP   DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (vidpage_id, vid_flnam),  -- Prevent dupes
  INDEX (vid_flnam),
  INDEX (vidpage_id)
) ENGINE = InnoDB;


DROP TABLE IF EXISTS users;
CREATE TABLE users (
	user_name		VARCHAR(130) NOT NULL,
	cust_no			INT(5)		 UNSIGNED NOT NULL AUTO_INCREMENT,
	admin_user_flag	TINYINT(1)   UNSIGNED NOT NULL,
	password		VARCHAR(70)  NOT NULL,
	user_dirname    CHAR(32)     NOT NULL,
	pw_reset_token  CHAR(32)     NOT NULL,
	pw_reset_pin    CHAR(10)     NOT NULL,
	pw_reset_time   DECIMAL(13,3) UNSIGNED NOT NULL, -- PHP sys datetime in seconds.microseconds
	moddte			TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (user_name),
  INDEX password (password),
  INDEX user_dirname (user_dirname),
  INDEX pw_reset_token (pw_reset_token),
  UNIQUE INDEX cust_no (cust_no)
) ENGINE = InnoDB;


DROP TABLE IF EXISTS login_failures;
CREATE TABLE login_failures (
	user_name		VARCHAR(130)  NOT NULL,
	fail_count		SMALLINT	  UNSIGNED NOT NULL,
	fail_time		DECIMAL(13,3) UNSIGNED NOT NULL, -- PHP sys datetime in seconds.microseconds
	moddte			TIMESTAMP	  DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (user_name)
) ENGINE = InnoDB;


DROP TABLE IF EXISTS user_activity;
CREATE TABLE user_activity (
	seq_num			INT(11)		 UNSIGNED NOT NULL AUTO_INCREMENT,
	user_name		VARCHAR(130) NOT NULL,
	activity_code	CHAR(4)      NOT NULL,
	session_id		VARCHAR(26)  NOT NULL,
	time_created    DECIMAL(13,3) UNSIGNED NOT NULL, -- PHP sys datetime in seconds.microseconds
	moddte			TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (seq_num),
  INDEX user_name (user_name),
  INDEX activity_code (activity_code)
) ENGINE = InnoDB;


DROP TABLE IF EXISTS activity_codes;
CREATE TABLE activity_codes (
	activity_code	CHAR(4)      NOT NULL,
	activity_descr	VARCHAR(130) NOT NULL,
	moddte			TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (activity_code)
) ENGINE = InnoDB;


DROP TABLE IF EXISTS customers;
CREATE TABLE customers (
	cust_no		 INT(5)		  UNSIGNED NOT NULL,
	firstname	 VARCHAR(50)  NOT NULL,
	initial		 CHAR(1)	  NOT NULL,
	lastname	 VARCHAR(50)  NOT NULL,
	title		 VARCHAR(5)	  NOT NULL,
	address		 VARCHAR(100) NOT NULL,
	city		 VARCHAR(40)  NOT NULL,
	statecode	 CHAR(2)	  NOT NULL,
	zipcode		 VARCHAR(10)  NOT NULL,
	phone		 VARCHAR(15)  NOT NULL,
	textmsg_addr VARCHAR(50)  NOT NULL,
	testimonial	 VARCHAR(300) NOT NULL,
	moddte		 TIMESTAMP	  DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (cust_no)
) ENGINE = InnoDB;


SET foreign_key_checks=1;

-- ===============================================================
