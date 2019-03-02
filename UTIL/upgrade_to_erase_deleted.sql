-- This will irreversibly erase the information amout deleted posts and files (duh)

UPDATE `posts` SET
  `name` = '',
  `tripcode` = '',
  `email` = '',
  `subject` = '',
  `message` = '',
  `country` = '',
  `password` = ''
WHERE `IS_DELETED` = 1;

UPDATE `files` SET
  `file_md5` = '',
  `file_original` = '',
  `file_size` = 0,
  `file_size_formatted` = '',
  `image_w` = 0,
  `image_h` = 0,
  `thumb_w` = 0,
  `thumb_h` = 0,
  `spoiler` = 0
WHERE `file` = 'removed';