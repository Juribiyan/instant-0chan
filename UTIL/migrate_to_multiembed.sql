CREATE TABLE `files` (
  `file_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_id` INT(10) UNSIGNED NOT NULL,
  `boardid` SMALLINT(5) UNSIGNED NOT NULL,
  `file` VARCHAR(50) NOT NULL,
  `file_md5` CHAR(32) NOT NULL,
  `file_type` VARCHAR(20) NOT NULL,
  `file_original` VARCHAR(255) NOT NULL,
  `file_size` INT(20) NOT NULL DEFAULT '0',
  `file_size_formatted` VARCHAR(75) NOT NULL,
  `image_w` SMALLINT(5) NOT NULL DEFAULT '0',
  `image_h` SMALLINT(5) NOT NULL DEFAULT '0',
  `thumb_w` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
  `thumb_h` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`file_id`),
  INDEX `file_md5` (`file_md5`),
  INDEX `file_id` (`file_id`),
  INDEX `post_id` (`post_id`)
)
ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

INSERT INTO `files` (
  `post_id`,
  `boardid`,
  `file`,
  `file_md5`,
  `file_type`,
  `file_original`,
  `file_size`,
  `file_size_formatted`,
  `image_w`,
  `image_h`,
  `thumb_w`,
  `thumb_h`
) SELECT
  `id`,
  `boardid`,
  `file`,
  `file_md5`,
  `file_type`,
  `file_original`,
  `file_size`,
  `file_size_formatted`,
  `image_w`,
  `image_h`,
  `thumb_w`,
  `thumb_h`
FROM `posts`
WHERE `file` != '';

ALTER TABLE `posts`
  DROP COLUMN `file`,
  DROP COLUMN `file_md5`,
  DROP COLUMN `file_type`,
  DROP COLUMN `file_original`,
  DROP COLUMN `file_size`,
  DROP COLUMN `file_size_formatted`,
  DROP COLUMN `image_w`,
  DROP COLUMN `image_h`,
  DROP COLUMN `thumb_w`,
  DROP COLUMN `thumb_h`;
  
CREATE VIEW `postembeds`  
AS SELECT 
  `posts`.`id` AS `id`,
  `posts`.`boardid` AS `boardid`,
  `posts`.`parentid` AS `parentid`,
  `posts`.`name` AS `name`,
  `posts`.`tripcode` AS `tripcode`,
  `posts`.`email` AS `email`,
  `posts`.`subject` AS `subject`,
  `posts`.`message` AS `message`,
  `posts`.`password` AS `password`,
  `posts`.`ip` AS `ip`,
  `posts`.`ipmd5` AS `ipmd5`,
  `posts`.`tag` AS `tag`,
  `posts`.`timestamp` AS `timestamp`,
  `posts`.`stickied` AS `stickied`,
  `posts`.`locked` AS `locked`,
  `posts`.`posterauthority` AS `posterauthority`,
  `posts`.`reviewed` AS `reviewed`,
  `posts`.`deleted_timestamp` AS `deleted_timestamp`,
  `posts`.`IS_DELETED` AS `IS_DELETED`,
  `posts`.`bumped` AS `bumped`,
  `posts`.`country` AS `country`,
  `files`.`file` AS `file`,
  `files`.`file_md5` AS `file_md5`,
  `files`.`file_type` AS `file_type`,
  `files`.`file_original` AS `file_original`,
  `files`.`file_size` AS `file_size`,
  `files`.`file_size_formatted` AS `file_size_formatted`,
  `files`.`image_w` AS `image_w`,
  `files`.`image_h` AS `image_h`,
  `files`.`thumb_w` AS `thumb_w`,
  `files`.`thumb_h` AS `thumb_h` 
FROM (
  `posts` LEFT JOIN `files` ON (
    (
      (`files`.`post_id` = `posts`.`id`) 
      and 
      (`files`.`boardid` = `posts`.`boardid`)
    )
  )
);