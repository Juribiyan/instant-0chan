ALTER TABLE `posts` ADD `sound` VARCHAR(50) NULL AFTER `by_new_user`;

SELECT 
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
  `posts`.`sound` AS `sound`,
  `files`.`file` AS `file`,
  `files`.`file_id` AS `file_id`,
  `files`.`file_md5` AS `file_md5`,
  `files`.`file_type` AS `file_type`,
  `files`.`file_original` AS `file_original`,
  `files`.`file_size` AS `file_size`,
  `files`.`file_size_formatted` AS `file_size_formatted`,
  `files`.`image_w` AS `image_w`,
  `files`.`image_h` AS `image_h`,
  `files`.`thumb_w` AS `thumb_w`,
  `files`.`thumb_h` AS `thumb_h`,
  `files`.`spoiler` AS `spoiler`
FROM (
  `posts` LEFT JOIN `files` ON (
    (
      (`files`.`post_id` = `posts`.`id`) 
      and 
      (`files`.`boardid` = `posts`.`boardid`)
    )
  )
);