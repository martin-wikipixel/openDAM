
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

#-----------------------------------------------------------------------------
#-- customer
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `customer`;


CREATE TABLE `customer`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`company` VARCHAR(255),
	`name` VARCHAR(255),
	`first_name` VARCHAR(255),
	`email` VARCHAR(255),
	`address` VARCHAR(255),
	`address_bis` VARCHAR(255),
	`zip` VARCHAR(255),
	`city` VARCHAR(255),
	`country_id` INTEGER  NOT NULL,
	`phone` VARCHAR(255),
	`mobile` VARCHAR(255),
	`fax` VARCHAR(255),
	`siret` VARCHAR(255),
	`ape` VARCHAR(255),
	`tva` VARCHAR(255),
	`created_at` DATETIME  NOT NULL,
	`state` INTEGER  NOT NULL,
	`activated_at` DATETIME,
	PRIMARY KEY (`id`),
	KEY `country_id`(`country_id`),
	CONSTRAINT `customer_FK_1`
		FOREIGN KEY (`country_id`)
		REFERENCES `country` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- comment
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `comment`;


CREATE TABLE `comment`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`file_id` INTEGER  NOT NULL,
	`user_id` INTEGER  NOT NULL,
	`title` VARCHAR(255)  NOT NULL,
	`content` TEXT  NOT NULL,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `user_id`(`user_id`),
	KEY `file_id`(`file_id`),
	CONSTRAINT `comment_FK_1`
		FOREIGN KEY (`file_id`)
		REFERENCES `file` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `comment_FK_2`
		FOREIGN KEY (`user_id`)
		REFERENCES `user` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- configuration
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `configuration`;


CREATE TABLE `configuration`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`type` VARCHAR(255)  NOT NULL,
	`value` VARCHAR(255)  NOT NULL COMMENT '0-illimité',
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- consumer_log
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `consumer_log`;


CREATE TABLE `consumer_log`
(
	`year` INTEGER  NOT NULL,
	`month` INTEGER  NOT NULL,
	`active_users` INTEGER  NOT NULL,
	`total_users` INTEGER  NOT NULL,
	`disk_space` BIGINT  NOT NULL,
	PRIMARY KEY (`year`,`month`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- consumer_log_criteria
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `consumer_log_criteria`;


CREATE TABLE `consumer_log_criteria`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`file_upload` TINYINT  NOT NULL,
	`file_print` TINYINT  NOT NULL,
	`file_download` TINYINT  NOT NULL,
	`file_retouch` TINYINT  NOT NULL,
	`create_folder` TINYINT  NOT NULL,
	`create_group` TINYINT  NOT NULL,
	`send_file` TINYINT  NOT NULL,
	`create_permalink` TINYINT  NOT NULL,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- favorites
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `favorites`;


CREATE TABLE `favorites`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`object_id` INTEGER  NOT NULL,
	`object_type` TINYINT  NOT NULL COMMENT '1-folder, 2-file',
	`user_id` INTEGER  NOT NULL,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `media_id`(`object_id`),
	KEY `user_id`(`user_id`),
	CONSTRAINT `favorites_FK_1`
		FOREIGN KEY (`user_id`)
		REFERENCES `user` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- field
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `field`;


CREATE TABLE `field`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`groupe_id` INTEGER  NOT NULL,
	`type` INTEGER  NOT NULL,
	`name` VARCHAR(255)  NOT NULL,
	`values` TEXT,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `groupe_id`(`groupe_id`),
	CONSTRAINT `field_FK_1`
		FOREIGN KEY (`groupe_id`)
		REFERENCES `groupe` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- field_content
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `field_content`;


CREATE TABLE `field_content`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`field_id` INTEGER  NOT NULL,
	`object_id` INTEGER  NOT NULL,
	`object_type` INTEGER  NOT NULL COMMENT '1-folder, 2-file',
	`value` TEXT  NOT NULL,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `field_id`(`field_id`),
	CONSTRAINT `field_content_FK_1`
		FOREIGN KEY (`field_id`)
		REFERENCES `field` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- file
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `file`;


CREATE TABLE `file`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`type` INTEGER  NOT NULL COMMENT '1-photo, 2-audio, 3-video',
	`name` VARCHAR(255)  NOT NULL,
	`description` TEXT  NOT NULL,
	`original` VARCHAR(255)  NOT NULL,
	`web` VARCHAR(255)  NOT NULL,
	`thumb200` VARCHAR(255)  NOT NULL,
	`thumb100` VARCHAR(255)  NOT NULL,
	`extention` VARCHAR(255)  NOT NULL,
	`size` DOUBLE  NOT NULL COMMENT 'KB',
	`folder_cover` TINYINT  NOT NULL,
	`lat` VARCHAR(255)  NOT NULL,
	`lng` VARCHAR(255)  NOT NULL,
	`average_point` DOUBLE  NOT NULL,
	`user_id` INTEGER  NOT NULL,
	`groupe_id` INTEGER  NOT NULL,
	`folder_id` INTEGER  NOT NULL,
	`created_at` DATETIME  NOT NULL,
	`main_color` VARCHAR(255),
	`state` INTEGER  NOT NULL,
	`updated_at` DATETIME  NOT NULL,
	`disk_id` INTEGER  NOT NULL,
	`source` VARCHAR(255),
	`licence_id` INTEGER,
	`usage_distribution_id` INTEGER,
	`usage_constraint_id` INTEGER,
	`usage_use_id` INTEGER,
	`usage_commercial_id` INTEGER,
	`creative_commons_id` INTEGER,
	`width` INTEGER,
	`height` INTEGER,
	`checksum` VARCHAR(255)  NOT NULL,
	`thumb_mob` VARCHAR(255),
	`thumb_mob_w` VARCHAR(255),
	`thumb_tab` VARCHAR(255),
	`thumb_tab_w` VARCHAR(255),
	`groupe_cover` TINYINT  NOT NULL,
	`thumb400` VARCHAR(255),
	`thumb400_w` VARCHAR(255),
	PRIMARY KEY (`id`),
	KEY `user_id`(`user_id`),
	KEY `group_id`(`groupe_id`),
	KEY `folder_id`(`folder_id`),
	KEY `created_at`(`created_at`),
	KEY `disk_id`(`disk_id`),
	KEY `licence_id`(`licence_id`),
	KEY `usage_distribution_id`(`usage_distribution_id`),
	KEY `usage_constraint_id`(`usage_constraint_id`),
	KEY `usage_use_id`(`usage_use_id`),
	KEY `usage_commercial_id`(`usage_commercial_id`),
	KEY `creative_commons_id`(`creative_commons_id`),
	CONSTRAINT `file_FK_1`
		FOREIGN KEY (`user_id`)
		REFERENCES `user` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `file_FK_2`
		FOREIGN KEY (`groupe_id`)
		REFERENCES `groupe` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `file_FK_3`
		FOREIGN KEY (`folder_id`)
		REFERENCES `folder` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `file_FK_4`
		FOREIGN KEY (`disk_id`)
		REFERENCES `disk` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `file_FK_5`
		FOREIGN KEY (`licence_id`)
		REFERENCES `licence` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `file_FK_6`
		FOREIGN KEY (`usage_distribution_id`)
		REFERENCES `usage_distribution` (`id`)
		ON UPDATE SET NULL
		ON DELETE SET NULL,
	CONSTRAINT `file_FK_7`
		FOREIGN KEY (`usage_constraint_id`)
		REFERENCES `usage_constraint` (`id`)
		ON UPDATE SET NULL
		ON DELETE SET NULL,
	CONSTRAINT `file_FK_8`
		FOREIGN KEY (`usage_use_id`)
		REFERENCES `usage_use` (`id`)
		ON UPDATE SET NULL
		ON DELETE SET NULL,
	CONSTRAINT `file_FK_9`
		FOREIGN KEY (`usage_commercial_id`)
		REFERENCES `usage_commercial` (`id`)
		ON UPDATE SET NULL
		ON DELETE SET NULL,
	CONSTRAINT `file_FK_10`
		FOREIGN KEY (`creative_commons_id`)
		REFERENCES `creative_commons` (`id`)
		ON UPDATE SET NULL
		ON DELETE SET NULL
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- file_tag
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `file_tag`;


CREATE TABLE `file_tag`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`file_id` INTEGER  NOT NULL,
	`tag_id` INTEGER  NOT NULL,
	`type` VARCHAR(255)  NOT NULL COMMENT '1-group, 2-folder, 3-file',
	PRIMARY KEY (`id`),
	KEY `tag_id`(`tag_id`),
	CONSTRAINT `file_tag_FK_1`
		FOREIGN KEY (`tag_id`)
		REFERENCES `tag` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- file_tmp
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `file_tmp`;


CREATE TABLE `file_tmp`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`file_id` INTEGER  NOT NULL,
	`folder_id` INTEGER  NOT NULL,
	`user_id` INTEGER  NOT NULL,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `file_id`(`file_id`),
	KEY `user_id`(`user_id`),
	KEY `folder_id`(`folder_id`),
	CONSTRAINT `file_tmp_FK_1`
		FOREIGN KEY (`file_id`)
		REFERENCES `file` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `file_tmp_FK_2`
		FOREIGN KEY (`folder_id`)
		REFERENCES `folder` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `file_tmp_FK_3`
		FOREIGN KEY (`user_id`)
		REFERENCES `user` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- folder
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `folder`;


CREATE TABLE `folder`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255)  NOT NULL,
	`description` TEXT  NOT NULL,
	`lat` VARCHAR(255)  NOT NULL,
	`lng` VARCHAR(255)  NOT NULL,
	`user_id` INTEGER  NOT NULL,
	`groupe_id` INTEGER  NOT NULL,
	`created_at` DATETIME  NOT NULL,
	`subfolder_id` INTEGER,
	`state` INTEGER default 1 NOT NULL,
	`licence_id` INTEGER,
	`usage_distribution_id` INTEGER,
	`usage_constraint_id` INTEGER,
	`usage_use_id` INTEGER,
	`usage_commercial_id` INTEGER,
	`creative_commons_id` INTEGER,
	`free` TINYINT  NOT NULL,
	`thumbnail` VARCHAR(255),
	`disk_id` INTEGER,
	PRIMARY KEY (`id`),
	KEY `group_id`(`groupe_id`),
	KEY `user_id`(`user_id`),
	KEY `created_at`(`created_at`),
	KEY `subfolder_id`(`subfolder_id`),
	KEY `usage_distribution_id`(`usage_distribution_id`),
	KEY `usage_constraint_id`(`usage_constraint_id`),
	KEY `usage_use_id`(`usage_use_id`),
	KEY `licence_id`(`licence_id`),
	KEY `usage_commercial_id`(`usage_commercial_id`),
	KEY `creative_commons_id`(`creative_commons_id`),
	KEY `disk_id`(`disk_id`),
	CONSTRAINT `folder_FK_1`
		FOREIGN KEY (`user_id`)
		REFERENCES `user` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `folder_FK_2`
		FOREIGN KEY (`groupe_id`)
		REFERENCES `groupe` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `folder_FK_3`
		FOREIGN KEY (`subfolder_id`)
		REFERENCES `folder` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `folder_FK_4`
		FOREIGN KEY (`licence_id`)
		REFERENCES `licence` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `folder_FK_5`
		FOREIGN KEY (`usage_distribution_id`)
		REFERENCES `usage_distribution` (`id`)
		ON UPDATE SET NULL
		ON DELETE SET NULL,
	CONSTRAINT `folder_FK_6`
		FOREIGN KEY (`usage_constraint_id`)
		REFERENCES `usage_constraint` (`id`)
		ON UPDATE SET NULL
		ON DELETE SET NULL,
	CONSTRAINT `folder_FK_7`
		FOREIGN KEY (`usage_use_id`)
		REFERENCES `usage_use` (`id`)
		ON UPDATE SET NULL
		ON DELETE SET NULL,
	CONSTRAINT `folder_FK_8`
		FOREIGN KEY (`usage_commercial_id`)
		REFERENCES `usage_commercial` (`id`)
		ON UPDATE SET NULL
		ON DELETE SET NULL,
	CONSTRAINT `folder_FK_9`
		FOREIGN KEY (`creative_commons_id`)
		REFERENCES `creative_commons` (`id`)
		ON UPDATE SET NULL
		ON DELETE SET NULL,
	CONSTRAINT `folder_FK_10`
		FOREIGN KEY (`disk_id`)
		REFERENCES `disk` (`id`)
		ON UPDATE SET NULL
		ON DELETE SET NULL
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- groupe
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `groupe`;


CREATE TABLE `groupe`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255)  NOT NULL,
	`description` TEXT  NOT NULL,
	`thumbnail` VARCHAR(255)  NOT NULL,
	`user_id` INTEGER  NOT NULL,
	`customer_id` INTEGER  NOT NULL,
	`free` TINYINT  NOT NULL,
	`disk_id` INTEGER  NOT NULL,
	`created_at` DATETIME  NOT NULL,
	`state` INTEGER  NOT NULL,
	`type` INTEGER  NOT NULL,
	`free_credential` INTEGER  NOT NULL,
	`url` VARCHAR(255),
	`logo` VARCHAR(255),
	PRIMARY KEY (`id`),
	KEY `user_id`(`user_id`),
	KEY `disk_id`(`disk_id`),
	KEY `customer_id`(`customer_id`),
	KEY `created_at`(`created_at`),
	KEY `free_credential`(`free_credential`),
	KEY `business_unit_id`(`business_unit_id`),
	CONSTRAINT `groupe_FK_1`
		FOREIGN KEY (`user_id`)
		REFERENCES `user` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `groupe_FK_2`
		FOREIGN KEY (`customer_id`)
		REFERENCES `customer` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `groupe_FK_3`
		FOREIGN KEY (`disk_id`)
		REFERENCES `disk` (`id`)
		ON UPDATE SET NULL
		ON DELETE SET NULL,
	CONSTRAINT `groupe_FK_4`
		FOREIGN KEY (`free_credential`)
		REFERENCES `role` (`id`)
		ON UPDATE SET NULL
		ON DELETE SET NULL
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- log
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `log`;


CREATE TABLE `log`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`user_id` INTEGER,
	`type` TINYINT  NOT NULL,
	`object_id` INTEGER  NOT NULL,
	`log_type` VARCHAR(255)  NOT NULL,
	`customer_id` INTEGER  NOT NULL,
	`created_at` DATETIME  NOT NULL,
	`ids` TEXT  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `user_id`(`user_id`),
	KEY `customer_id`(`customer_id`),
	CONSTRAINT `log_FK_1`
		FOREIGN KEY (`user_id`)
		REFERENCES `user` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `log_FK_2`
		FOREIGN KEY (`customer_id`)
		REFERENCES `customer` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- permalink
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `permalink`;


CREATE TABLE `permalink`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`type` INTEGER default 1 NOT NULL COMMENT '1=web, 2=original, 3=custom',
	`object_type` INTEGER  NOT NULL COMMENT '1=file, 2=folder, 3=group',
	`object_id` INTEGER  NOT NULL,
	`user_id` INTEGER  NOT NULL,
	`created_at` DATETIME,
	`end_at` DATETIME  NOT NULL,
	`link` VARCHAR(255)  NOT NULL,
	`qrcode` VARCHAR(255)  NOT NULL,
	`allow_comments` TINYINT  NOT NULL,
	`format_hd` TINYINT  NOT NULL,
	`state` INTEGER  NOT NULL,
	`password` VARCHAR(255),
	PRIMARY KEY (`id`),
	KEY `object_id`(`file_id`),
	KEY `user_id`(`user_id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- rating
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `rating`;


CREATE TABLE `rating`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`file_id` INTEGER  NOT NULL,
	`nb_rate` INTEGER  NOT NULL,
	`total_rate` INTEGER  NOT NULL,
	`user_ids` TEXT  NOT NULL,
	`created_at` DATETIME  NOT NULL,
	`updated_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `object_id`(`file_id`),
	KEY `user_id`(`nb_rate`),
	CONSTRAINT `rating_FK_1`
		FOREIGN KEY (`file_id`)
		REFERENCES `file` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- request
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `request`;


CREATE TABLE `request`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`type` TINYINT  NOT NULL COMMENT '0-group access request, 1- a particular group, 2 - technical problems, 3 - other',
	`groupe_id` INTEGER  NOT NULL,
	`folder_id` INTEGER  NOT NULL,
	`user_id` INTEGER  NOT NULL,
	`is_request` TINYINT default 0 NOT NULL COMMENT '1-request for access to the group, 0-messages about groups, other and technical problems',
	`message` TEXT  NOT NULL,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `groupe_id`(`groupe_id`),
	KEY `user_id`(`user_id`),
	CONSTRAINT `request_FK_1`
		FOREIGN KEY (`user_id`)
		REFERENCES `user` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- role
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `role`;


CREATE TABLE `role`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`title` VARCHAR(255)  NOT NULL,
	PRIMARY KEY (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- tag
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `tag`;


CREATE TABLE `tag`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`title` VARCHAR(255)  NOT NULL,
	`description` TEXT  NOT NULL,
	`customer_id` INTEGER  NOT NULL,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `customer_id`(`customer_id`),
	CONSTRAINT `tag_FK_1`
		FOREIGN KEY (`customer_id`)
		REFERENCES `customer` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- unit
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `unit`;


CREATE TABLE `unit`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`title` VARCHAR(255)  NOT NULL,
	`customer_id` INTEGER  NOT NULL,
	`created_at` DATETIME  NOT NULL,
	`description` TEXT,
	PRIMARY KEY (`id`),
	KEY `customer_id`(`customer_id`),
	CONSTRAINT `unit_FK_1`
		FOREIGN KEY (`customer_id`)
		REFERENCES `customer` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- user
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `user`;


CREATE TABLE `user`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`username` VARCHAR(255)  NOT NULL,
	`password` VARCHAR(255)  NOT NULL,
	`firstname` VARCHAR(255)  NOT NULL,
	`lastname` VARCHAR(255)  NOT NULL,
	`email` VARCHAR(255)  NOT NULL,
	`position` VARCHAR(255)  NOT NULL,
	`phone` VARCHAR(255)  NOT NULL,
	`role_id` INTEGER  NOT NULL,
	`last_login_at` DATETIME  NOT NULL,
	`created_at` DATETIME  NOT NULL,
	`hash` VARCHAR(255)  NOT NULL,
	`country_id` INTEGER  NOT NULL,
	`culture` VARCHAR(255)  NOT NULL,
	`customer_id` INTEGER  NOT NULL,
	`state` INTEGER  NOT NULL,
	`view_mode` VARCHAR(255),
	`comment` TEXT,
	PRIMARY KEY (`id`),
	KEY `role_id`(`role_id`),
	KEY `customer_id`(`customer_id`),
	KEY `country_id`(`country_id`),
	CONSTRAINT `user_FK_1`
		FOREIGN KEY (`role_id`)
		REFERENCES `role` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `user_FK_2`
		FOREIGN KEY (`country_id`)
		REFERENCES `country` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `user_FK_3`
		FOREIGN KEY (`customer_id`)
		REFERENCES `customer` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- user_folder
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `user_folder`;


CREATE TABLE `user_folder`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`user_id` INTEGER  NOT NULL,
	`folder_id` INTEGER  NOT NULL,
	`role` VARCHAR(255)  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `user_id`(`user_id`),
	KEY `folder_id`(`folder_id`),
	CONSTRAINT `user_folder_FK_1`
		FOREIGN KEY (`user_id`)
		REFERENCES `user` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `user_folder_FK_2`
		FOREIGN KEY (`folder_id`)
		REFERENCES `folder` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- user_group
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `user_group`;


CREATE TABLE `user_group`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`user_id` INTEGER,
	`groupe_id` INTEGER  NOT NULL,
	`role` VARCHAR(255)  NOT NULL,
	`state` INTEGER  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `user_id`(`user_id`),
	KEY `group_id`(`groupe_id`),
	CONSTRAINT `user_group_FK_1`
		FOREIGN KEY (`user_id`)
		REFERENCES `user` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `user_group_FK_2`
		FOREIGN KEY (`groupe_id`)
		REFERENCES `groupe` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- user_unit
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `user_unit`;


CREATE TABLE `user_unit`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`user_id` INTEGER  NOT NULL,
	`unit_id` INTEGER  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `user_id`(`user_id`),
	KEY `unit_id`(`unit_id`),
	CONSTRAINT `user_unit_FK_1`
		FOREIGN KEY (`user_id`)
		REFERENCES `user` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `user_unit_FK_2`
		FOREIGN KEY (`unit_id`)
		REFERENCES `unit` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- exif
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `exif`;


CREATE TABLE `exif`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`title` VARCHAR(255)  NOT NULL,
	`value` TEXT  NOT NULL,
	`file_id` INTEGER  NOT NULL,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `file_id`(`file_id`),
	CONSTRAINT `exif_FK_1`
		FOREIGN KEY (`file_id`)
		REFERENCES `file` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- module_value
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `module_value`;


CREATE TABLE `module_value`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`module_id` INTEGER  NOT NULL,
	`value` TEXT  NOT NULL,
	`ranking` INTEGER  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `module_id`(`module_id`),
	CONSTRAINT `module_value_FK_1`
		FOREIGN KEY (`module_id`)
		REFERENCES `module` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- module_value_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `module_value_i18n`;


CREATE TABLE `module_value_i18n`
(
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(255)  NOT NULL,
	`description` TEXT,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `module_value_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `module_value` (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- module
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `module`;


CREATE TABLE `module`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`deactivated` TINYINT  NOT NULL,
	`default_value` INTEGER,
	PRIMARY KEY (`id`),
	KEY `default_value`(`default_value`),
	CONSTRAINT `module_FK_1`
		FOREIGN KEY (`default_value`)
		REFERENCES `module_value` (`id`)
		ON UPDATE SET NULL
		ON DELETE SET NULL
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- module_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `module_i18n`;


CREATE TABLE `module_i18n`
(
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(255)  NOT NULL,
	`title` VARCHAR(255)  NOT NULL,
	`description` TEXT,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `module_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `module` (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- customer_has_module
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `customer_has_module`;


CREATE TABLE `customer_has_module`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`customer_id` INTEGER  NOT NULL,
	`module_id` INTEGER  NOT NULL,
	`module_value_id` INTEGER,
	`customer_value` TEXT,
	`created_at` DATETIME  NOT NULL,
	`active` TINYINT  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `customer_id`(`customer_id`),
	KEY `module_id`(`module_id`),
	KEY `module_value_id`(`module_value_id`),
	CONSTRAINT `customer_has_module_FK_1`
		FOREIGN KEY (`customer_id`)
		REFERENCES `customer` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `customer_has_module_FK_2`
		FOREIGN KEY (`module_id`)
		REFERENCES `module` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `customer_has_module_FK_3`
		FOREIGN KEY (`module_value_id`)
		REFERENCES `module_value` (`id`)
		ON UPDATE SET NULL
		ON DELETE SET NULL
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- unique_key
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `unique_key`;


CREATE TABLE `unique_key`
(
	`id` VARCHAR(255)  NOT NULL,
	`user_id` INTEGER,
	`created_at` DATETIME  NOT NULL,
	`expired_at` DATETIME,
	`ip` TEXT  NOT NULL,
	`uri` TEXT  NOT NULL,
	`referer` TEXT,
	`session_params` TEXT,
	PRIMARY KEY (`id`),
	KEY `user_id`(`user_id`),
	CONSTRAINT `unique_key_FK_1`
		FOREIGN KEY (`user_id`)
		REFERENCES `user` (`id`)
		ON UPDATE SET NULL
		ON DELETE SET NULL
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- email
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `email`;


CREATE TABLE `email`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255)  NOT NULL,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- email_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `email_i18n`;


CREATE TABLE `email_i18n`
(
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(255)  NOT NULL,
	`subject` VARCHAR(255)  NOT NULL,
	`message` TEXT  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `email_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `email` (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- culture
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `culture`;


CREATE TABLE `culture`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	PRIMARY KEY (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- culture_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `culture_i18n`;


CREATE TABLE `culture_i18n`
(
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(255)  NOT NULL,
	`code` VARCHAR(2)  NOT NULL,
	`title` VARCHAR(255)  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `culture_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `culture` (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- user_preference
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `user_preference`;


CREATE TABLE `user_preference`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`title` VARCHAR(255)  NOT NULL,
	`value` TEXT  NOT NULL,
	`order` INTEGER  NOT NULL,
	`user_id` INTEGER  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `user_id`(`user_id`),
	CONSTRAINT `user_preference_FK_1`
		FOREIGN KEY (`user_id`)
		REFERENCES `user` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- usage_type
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `usage_type`;


CREATE TABLE `usage_type`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	PRIMARY KEY (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- usage_type_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `usage_type_i18n`;


CREATE TABLE `usage_type_i18n`
(
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(255)  NOT NULL,
	`title` VARCHAR(255)  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `usage_type_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `usage_type` (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- file_right
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `file_right`;


CREATE TABLE `file_right`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`object_id` INTEGER  NOT NULL,
	`type` INTEGER  NOT NULL,
	`usage_limitation_id` INTEGER  NOT NULL,
	`value` VARCHAR(255)  NOT NULL,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `usage_limitation_id`(`usage_limitation_id`),
	CONSTRAINT `file_right_FK_1`
		FOREIGN KEY (`usage_limitation_id`)
		REFERENCES `usage_limitation` (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- iptc
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `iptc`;


CREATE TABLE `iptc`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`title` VARCHAR(255)  NOT NULL,
	`value` TEXT  NOT NULL,
	`file_id` INTEGER  NOT NULL,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `file_id`(`file_id`),
	CONSTRAINT `iptc_FK_1`
		FOREIGN KEY (`file_id`)
		REFERENCES `file` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- basket
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `basket`;


CREATE TABLE `basket`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`user_id` INTEGER  NOT NULL,
	`code` VARCHAR(15)  NOT NULL,
	`title` VARCHAR(255)  NOT NULL,
	`description` TEXT,
	`state` INTEGER  NOT NULL,
	`password` VARCHAR(255),
	`allow_comments` TINYINT default 0 NOT NULL,
	`allow_download_hd` TINYINT default 1 NOT NULL,
	`is_valid` TINYINT default 1 NOT NULL,
	`created_at` DATETIME  NOT NULL,
	`is_shared` TINYINT default 0 NOT NULL,
	PRIMARY KEY (`id`),
	KEY `user_id`(`user_id`),
	CONSTRAINT `basket_FK_1`
		FOREIGN KEY (`user_id`)
		REFERENCES `user` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- basket_has_content
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `basket_has_content`;


CREATE TABLE `basket_has_content`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`basket_id` INTEGER  NOT NULL,
	`file_id` INTEGER  NOT NULL,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `basket_id`(`basket_id`),
	KEY `file_id`(`file_id`),
	CONSTRAINT `basket_has_content_FK_1`
		FOREIGN KEY (`basket_id`)
		REFERENCES `basket` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `basket_has_content_FK_2`
		FOREIGN KEY (`file_id`)
		REFERENCES `file` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- basket_has_comment
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `basket_has_comment`;


CREATE TABLE `basket_has_comment`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`basket_id` INTEGER  NOT NULL,
	`email` VARCHAR(255)  NOT NULL,
	`comment` TEXT  NOT NULL,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `basket_id`(`basket_id`),
	CONSTRAINT `basket_has_comment_FK_1`
		FOREIGN KEY (`basket_id`)
		REFERENCES `basket` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- unit_group
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `unit_group`;


CREATE TABLE `unit_group`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`unit_id` INTEGER  NOT NULL,
	`groupe_id` INTEGER  NOT NULL,
	`role` INTEGER  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `unit_id`(`unit_id`),
	KEY `group_id`(`groupe_id`),
	KEY `role`(`role`),
	CONSTRAINT `unit_group_FK_1`
		FOREIGN KEY (`unit_id`)
		REFERENCES `unit` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `unit_group_FK_2`
		FOREIGN KEY (`groupe_id`)
		REFERENCES `groupe` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `unit_group_FK_3`
		FOREIGN KEY (`role`)
		REFERENCES `role` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- file_waiting
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `file_waiting`;


CREATE TABLE `file_waiting`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`file_id` INTEGER  NOT NULL,
	`user_id` INTEGER  NOT NULL,
	`state` INTEGER  NOT NULL,
	`cause` TEXT,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `file_id`(`file_id`),
	KEY `user_id`(`user_id`),
	CONSTRAINT `file_waiting_FK_1`
		FOREIGN KEY (`file_id`)
		REFERENCES `file` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `file_waiting_FK_2`
		FOREIGN KEY (`user_id`)
		REFERENCES `user` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- log_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `log_i18n`;


CREATE TABLE `log_i18n`
(
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(255)  NOT NULL,
	`content` TEXT  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `log_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `log` (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- url
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `url`;


CREATE TABLE `url`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`type` VARCHAR(255)  NOT NULL,
	`path` TEXT  NOT NULL,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- disk
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `disk`;


CREATE TABLE `disk`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255)  NOT NULL,
	`path` VARCHAR(255)  NOT NULL,
	`by_default` TINYINT  NOT NULL,
	`customer_id` INTEGER  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `customer_id`(`customer_id`),
	CONSTRAINT `disk_FK_1`
		FOREIGN KEY (`customer_id`)
		REFERENCES `customer` (`id`)
		ON UPDATE SET NULL
		ON DELETE SET NULL
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- permalink_comment
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `permalink_comment`;


CREATE TABLE `permalink_comment`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`permalink_id` INTEGER  NOT NULL,
	`email` VARCHAR(255)  NOT NULL,
	`comment` TEXT  NOT NULL,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `permalink`(`permalink`),
	INDEX `permalink_comment_FI_1` (`permalink_id`),
	CONSTRAINT `permalink_comment_FK_1`
		FOREIGN KEY (`permalink_id`)
		REFERENCES `permalink` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- constraint
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `constraint`;


CREATE TABLE `constraint`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`role_id` INTEGER,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `constraint_FI_1` (`role_id`),
	CONSTRAINT `constraint_FK_1`
		FOREIGN KEY (`role_id`)
		REFERENCES `role` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- constraint_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `constraint_i18n`;


CREATE TABLE `constraint_i18n`
(
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(255)  NOT NULL,
	`title` VARCHAR(255)  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `constraint_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `constraint` (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- groupe_constraint
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `groupe_constraint`;


CREATE TABLE `groupe_constraint`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`groupe_id` INTEGER  NOT NULL,
	`constraint_id` INTEGER  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `groupe_id`(`groupe_id`),
	KEY `constraint_id`(`constraint_id`),
	CONSTRAINT `groupe_constraint_FK_1`
		FOREIGN KEY (`groupe_id`)
		REFERENCES `groupe` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `groupe_constraint_FK_2`
		FOREIGN KEY (`constraint_id`)
		REFERENCES `constraint` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- file_related
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `file_related`;


CREATE TABLE `file_related`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`file_id_to` INTEGER  NOT NULL,
	`file_id_from` INTEGER  NOT NULL,
	`user_id` INTEGER  NOT NULL,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `file_id_to`(`file_id_to`),
	KEY `file_id_from`(`file_id_from`),
	KEY `user_id`(`user_id`),
	CONSTRAINT `file_related_FK_1`
		FOREIGN KEY (`file_id_to`)
		REFERENCES `file` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `file_related_FK_2`
		FOREIGN KEY (`file_id_from`)
		REFERENCES `file` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `file_related_FK_3`
		FOREIGN KEY (`user_id`)
		REFERENCES `user` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- licence
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `licence`;


CREATE TABLE `licence`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- licence_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `licence_i18n`;


CREATE TABLE `licence_i18n`
(
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(255)  NOT NULL,
	`title` VARCHAR(255)  NOT NULL,
	`description` TEXT,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `licence_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `licence` (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- country
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `country`;


CREATE TABLE `country`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`continent_id` INTEGER  NOT NULL,
	`phone_code` INTEGER  NOT NULL,
	`ue` TINYINT  NOT NULL,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `country_FI_1` (`continent_id`),
	CONSTRAINT `country_FK_1`
		FOREIGN KEY (`continent_id`)
		REFERENCES `continent` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- country_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `country_i18n`;


CREATE TABLE `country_i18n`
(
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(255)  NOT NULL,
	`title` VARCHAR(255)  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `country_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `country` (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- continent
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `continent`;


CREATE TABLE `continent`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- continent_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `continent_i18n`;


CREATE TABLE `continent_i18n`
(
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(255)  NOT NULL,
	`title` VARCHAR(255)  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `continent_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `continent` (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- usage_support
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `usage_support`;


CREATE TABLE `usage_support`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`customer_id` INTEGER,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `customer_id`(`customer_id`),
	CONSTRAINT `usage_support_FK_1`
		FOREIGN KEY (`customer_id`)
		REFERENCES `customer` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- usage_support_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `usage_support_i18n`;


CREATE TABLE `usage_support_i18n`
(
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(255)  NOT NULL,
	`title` VARCHAR(255)  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `usage_support_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `usage_support` (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- usage_constraint
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `usage_constraint`;


CREATE TABLE `usage_constraint`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- usage_constraint_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `usage_constraint_i18n`;


CREATE TABLE `usage_constraint_i18n`
(
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(255)  NOT NULL,
	`title` VARCHAR(255)  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `usage_constraint_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `usage_constraint` (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- usage_distribution
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `usage_distribution`;


CREATE TABLE `usage_distribution`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- usage_distribution_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `usage_distribution_i18n`;


CREATE TABLE `usage_distribution_i18n`
(
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(255)  NOT NULL,
	`title` VARCHAR(255)  NOT NULL,
	`description` TEXT,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `usage_distribution_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `usage_distribution` (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- usage_limitation
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `usage_limitation`;


CREATE TABLE `usage_limitation`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`usage_type_id` INTEGER,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `usage_type_id`(`usage_type_id`),
	CONSTRAINT `usage_limitation_FK_1`
		FOREIGN KEY (`usage_type_id`)
		REFERENCES `usage_type` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- usage_limitation_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `usage_limitation_i18n`;


CREATE TABLE `usage_limitation_i18n`
(
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(255)  NOT NULL,
	`title` VARCHAR(255)  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `usage_limitation_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `usage_limitation` (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- usage_use
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `usage_use`;


CREATE TABLE `usage_use`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- usage_use_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `usage_use_i18n`;


CREATE TABLE `usage_use_i18n`
(
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(255)  NOT NULL,
	`title` VARCHAR(255)  NOT NULL,
	`description` TEXT,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `usage_use_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `usage_use` (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- usage_commercial
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `usage_commercial`;


CREATE TABLE `usage_commercial`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- usage_commercial_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `usage_commercial_i18n`;


CREATE TABLE `usage_commercial_i18n`
(
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(255)  NOT NULL,
	`title` VARCHAR(255)  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `usage_commercial_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `usage_commercial` (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- creative_commons
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `creative_commons`;


CREATE TABLE `creative_commons`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`image_path` TEXT  NOT NULL,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- creative_commons_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `creative_commons_i18n`;


CREATE TABLE `creative_commons_i18n`
(
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(255)  NOT NULL,
	`title` VARCHAR(255)  NOT NULL,
	`description` TEXT,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `creative_commons_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `creative_commons` (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- log_user
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `log_user`;


CREATE TABLE `log_user`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`user_id` INTEGER  NOT NULL,
	`remote_addr` TEXT,
	`user_agent` TEXT,
	`uri` TEXT,
	`referer` TEXT,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `user_id`(`user_id`),
	CONSTRAINT `log_user_FK_1`
		FOREIGN KEY (`user_id`)
		REFERENCES `user` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- log_groupe
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `log_groupe`;


CREATE TABLE `log_groupe`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`groupe_id` INTEGER  NOT NULL,
	`used_space_disk` INTEGER  NOT NULL,
	`folders` INTEGER  NOT NULL,
	`files` INTEGER  NOT NULL,
	`upload_traffic` INTEGER  NOT NULL,
	`download_traffic` INTEGER  NOT NULL,
	`upload_traffic_files` INTEGER  NOT NULL,
	`download_traffic_files` INTEGER  NOT NULL,
	`views` INTEGER  NOT NULL,
	`unique_views` INTEGER  NOT NULL,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `groupe_id`(`groupe_id`),
	CONSTRAINT `log_groupe_FK_1`
		FOREIGN KEY (`groupe_id`)
		REFERENCES `groupe` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- permalink_notification
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `permalink_notification`;


CREATE TABLE `permalink_notification`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`user_id` INTEGER  NOT NULL,
	`permalink_id` INTEGER  NOT NULL,
	`add_comment` TINYINT  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `user_id`(`user_id`),
	KEY `permalink_id`(`permalink_id`),
	CONSTRAINT `permalink_notification_FK_1`
		FOREIGN KEY (`user_id`)
		REFERENCES `user` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `permalink_notification_FK_2`
		FOREIGN KEY (`permalink_id`)
		REFERENCES `user` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- user_has_module
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `user_has_module`;


CREATE TABLE `user_has_module`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`user_id` INTEGER  NOT NULL,
	`module_id` INTEGER  NOT NULL,
	`module_value_id` INTEGER,
	`user_value` TEXT,
	`created_at` DATETIME  NOT NULL,
	`active` TINYINT  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `user_id`(`user_id`),
	KEY `module_id`(`module_id`),
	KEY `module_value_id`(`module_value_id`),
	CONSTRAINT `user_has_module_FK_1`
		FOREIGN KEY (`user_id`)
		REFERENCES `user` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `user_has_module_FK_2`
		FOREIGN KEY (`module_id`)
		REFERENCES `module` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `user_has_module_FK_3`
		FOREIGN KEY (`module_value_id`)
		REFERENCES `module_value` (`id`)
		ON UPDATE SET NULL
		ON DELETE SET NULL
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- module_visibility
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `module_visibility`;


CREATE TABLE `module_visibility`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`title` VARCHAR(255)  NOT NULL,
	PRIMARY KEY (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- module_has_visibility
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `module_has_visibility`;


CREATE TABLE `module_has_visibility`
(
	`module_id` INTEGER  NOT NULL,
	`module_visibility_id` INTEGER  NOT NULL,
	PRIMARY KEY (`module_id`,`module_visibility_id`),
	CONSTRAINT `module_has_visibility_FK_1`
		FOREIGN KEY (`module_id`)
		REFERENCES `module` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	INDEX `module_has_visibility_FI_2` (`module_visibility_id`),
	CONSTRAINT `module_has_visibility_FK_2`
		FOREIGN KEY (`module_visibility_id`)
		REFERENCES `module_visibility` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- lexicon
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `lexicon`;


CREATE TABLE `lexicon`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`culture` VARCHAR(255)  NOT NULL,
	`title` VARCHAR(255)  NOT NULL,
	PRIMARY KEY (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- thesaurus
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `thesaurus`;


CREATE TABLE `thesaurus`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`customer_id` INTEGER  NOT NULL,
	`culture_id` INTEGER  NOT NULL,
	`type` INTEGER  NOT NULL,
	`title` VARCHAR(255)  NOT NULL,
	`parent_id` INTEGER  NOT NULL,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `customer_id`(`customer_id`),
	KEY `culture_id`(`culture_id`),
	KEY `parent_id`(`parent_id`),
	CONSTRAINT `thesaurus_FK_1`
		FOREIGN KEY (`customer_id`)
		REFERENCES `customer` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `thesaurus_FK_2`
		FOREIGN KEY (`culture_id`)
		REFERENCES `culture` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `thesaurus_FK_3`
		FOREIGN KEY (`parent_id`)
		REFERENCES `thesaurus` (`id`)
		ON UPDATE SET NULL
		ON DELETE SET NULL
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- preset
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `preset`;


CREATE TABLE `preset`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`customer_id` INTEGER  NOT NULL,
	`name` VARCHAR(255)  NOT NULL,
	`licence_id` INTEGER,
	`usage_distribution_id` INTEGER,
	`usage_constraint_id` INTEGER,
	`usage_use_id` INTEGER,
	`usage_commercial_id` INTEGER,
	`creative_commons_id` INTEGER,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `licence_id`(`licence_id`),
	KEY `usage_distribution_id`(`usage_distribution_id`),
	KEY `usage_constraint_id`(`usage_constraint_id`),
	KEY `usage_use_id`(`usage_use_id`),
	KEY `usage_commercial_id`(`usage_commercial_id`),
	KEY `creative_commons_id`(`creative_commons_id`),
	INDEX `preset_FI_1` (`customer_id`),
	CONSTRAINT `preset_FK_1`
		FOREIGN KEY (`customer_id`)
		REFERENCES `customer` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `preset_FK_2`
		FOREIGN KEY (`licence_id`)
		REFERENCES `licence` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `preset_FK_3`
		FOREIGN KEY (`usage_distribution_id`)
		REFERENCES `usage_distribution` (`id`)
		ON UPDATE SET NULL
		ON DELETE SET NULL,
	CONSTRAINT `preset_FK_4`
		FOREIGN KEY (`usage_constraint_id`)
		REFERENCES `usage_constraint` (`id`)
		ON UPDATE SET NULL
		ON DELETE SET NULL,
	CONSTRAINT `preset_FK_5`
		FOREIGN KEY (`usage_use_id`)
		REFERENCES `usage_use` (`id`)
		ON UPDATE SET NULL
		ON DELETE SET NULL,
	CONSTRAINT `preset_FK_6`
		FOREIGN KEY (`usage_commercial_id`)
		REFERENCES `usage_commercial` (`id`)
		ON UPDATE SET NULL
		ON DELETE SET NULL,
	CONSTRAINT `preset_FK_7`
		FOREIGN KEY (`creative_commons_id`)
		REFERENCES `creative_commons` (`id`)
		ON UPDATE SET NULL
		ON DELETE SET NULL
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- basket_request
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `basket_request`;


CREATE TABLE `basket_request`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`basket_id` INTEGER  NOT NULL,
	`user_id` INTEGER,
	`accept` TINYINT  NOT NULL,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `basket_id`(`basket_id`),
	KEY `user_id`(`user_id`),
	CONSTRAINT `basket_request_FK_1`
		FOREIGN KEY (`basket_id`)
		REFERENCES `basket` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `basket_request_FK_2`
		FOREIGN KEY (`user_id`)
		REFERENCES `user` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- geolocation_type
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `geolocation_type`;


CREATE TABLE `geolocation_type`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- geolocation_type_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `geolocation_type_i18n`;


CREATE TABLE `geolocation_type_i18n`
(
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(255)  NOT NULL,
	`title` VARCHAR(255)  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `geolocation_type_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `geolocation_type` (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- geolocation
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `geolocation`;


CREATE TABLE `geolocation`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`object_id` INTEGER  NOT NULL,
	`object_type` INTEGER  NOT NULL,
	`geolocation_type_id` INTEGER  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `geolocation_type_id`(`geolocation_type_id`),
	CONSTRAINT `geolocation_FK_1`
		FOREIGN KEY (`geolocation_type_id`)
		REFERENCES `geolocation_type` (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- geolocation_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `geolocation_i18n`;


CREATE TABLE `geolocation_i18n`
(
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(255)  NOT NULL,
	`value` VARCHAR(255)  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `geolocation_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `geolocation` (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- permalink_log
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `permalink_log`;


CREATE TABLE `permalink_log`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`object_id` INTEGER  NOT NULL,
	`object_type` INTEGER  NOT NULL,
	`remote_addr` VARCHAR(255)  NOT NULL,
	`created_at` DATETIME  NOT NULL,
	PRIMARY KEY (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- unit_folder
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `unit_folder`;


CREATE TABLE `unit_folder`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`unit_id` INTEGER  NOT NULL,
	`folder_id` INTEGER  NOT NULL,
	`role` VARCHAR(255)  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `unit_id`(`unit_id`),
	KEY `folder_id`(`folder_id`),
	CONSTRAINT `unit_folder_FK_1`
		FOREIGN KEY (`unit_id`)
		REFERENCES `unit` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	CONSTRAINT `unit_folder_FK_2`
		FOREIGN KEY (`folder_id`)
		REFERENCES `folder` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- reset_password_request
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `reset_password_request`;


CREATE TABLE `reset_password_request`
(
	`token` VARCHAR(100)  NOT NULL,
	`user_id` INTEGER  NOT NULL,
	`created_at` DATETIME,
	PRIMARY KEY (`token`),
	INDEX `reset_password_request_FI_1` (`user_id`),
	CONSTRAINT `reset_password_request_FK_1`
		FOREIGN KEY (`user_id`)
		REFERENCES `user` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)Type=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
