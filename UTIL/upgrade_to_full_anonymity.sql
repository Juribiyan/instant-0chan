CREATE TABLE `user_activity` (
  `idmd5` VARCHAR(50) NOT NULL,
  `latest_post` INT UNSIGNED NULL DEFAULT '0',
  `latest_thread` INT UNSIGNED NULL DEFAULT '0',
  `post_count` INT UNSIGNED NULL DEFAULT '0',
  PRIMARY KEY (`idmd5`)
)
CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Caution! This is irreversable.
UPDATE `posts` SET `ip`='', `ipmd5`='' WHERE 1;