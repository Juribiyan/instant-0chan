ALTER TABLE `embeds`
  ADD COLUMN `timeprefix` VARCHAR(10) NULL;

UPDATE `embeds` SET `timeprefix`='&t=' WHERE  `filetype`='you';
UPDATE `embeds` SET `timeprefix`='#t=' WHERE  `filetype`='vim';