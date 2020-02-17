ALTER TABLE `posts`
	ADD COLUMN `attempts` TINYINT(3) UNSIGNED NULL DEFAULT '0' AFTER `by_new_user`;

UPDATE `posts` SET `attempts`=0 WHERE 1;