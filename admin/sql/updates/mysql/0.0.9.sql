DROP TABLE IF EXISTS `#__salonbook_appointments`;

CREATE TABLE  `#__salonbook_appointments` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`date` DATE NOT NULL ,
	`startTime` TIME NULL ,
	`duration` INT NULL COMMENT  'foreign key',
	`user` INT NOT NULL COMMENT  'foreign key',
	`deposit_paid` BINARY NOT NULL DEFAULT  '0',
	`balance_due` FLOAT NULL ,
	`stylist` INT NULL COMMENT  'foreign key'
) ENGINE=MYISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

-- INSERT INTO  `cus_salonbook_appointments` (
-- `date` ,`startTime` ,`duration` ,`user` ,`deposit_paid` ,`balance_due` ,`stylist`)
-- VALUES 
-- ( '2011-09-16', `18:30:00` ,  '3',  '100',  '1',  '56.75',  '1');
