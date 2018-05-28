ALTER TABLE `posts`
  ADD COLUMN `by_new_user` TINYINT(1) NOT NULL DEFAULT '0' AFTER `country`;