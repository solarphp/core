# this is for MySQL -- other users will need to translate.
ALTER TABLE `nodes` ADD COLUMN `publish` DATETIME;
CREATE INDEX `publish` ON `nodes` (`publish`);
