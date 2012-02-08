DROP TABLE IF EXISTS `#__salonbook_services`;
 
CREATE TABLE `#__salonbook_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sku` varchar(25),
  `name` varchar(100) NOT NULL,
  `durationInMinutes` int(11) ,
  `duration` int(11) COMMENT  'foreign key' ,
   PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
 
INSERT INTO `#__salonbook_services` (`name`, `durationInMinutes`, `duration`) VALUES
	('Shampoo',30, 0 ),
	('Twist', 60, 2),
	('Re-tightening', 60, 2),
	('Braid', 120, 4),
	('Wash and Style', 60, 2),
	('Wash and Roller-set', 90, 3);
	
DROP TABLE IF EXISTS `#__salonbook_durations`;

CREATE TABLE `#__salonbook_durations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `displayName` varchar(25) NOT NULL,
  `durationInMinutes` int(11) ,
   PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

INSERT INTO `#__salonbook_durations` (`displayName`, `durationInMinutes`) VALUES
	('1/2 hour',30 ),
	('45 minutes', 45),
	('1 hour', 60),
	('1-1/2 hours', 90),
	('2 hours', 120),
	('3 hours', 180),
	('1 day', 1440);
	
DROP TABLE IF EXISTS `#__salonbook_appointments`;

CREATE TABLE  `#__salonbook_appointments` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`created_when` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
	`appointmentDate` DATE NULL ,
	`startTime` TIME NULL ,
	`durationInMinutes` INT NULL ,
	`user` INT NOT NULL COMMENT  'foreign key',
	`deposit_paid` BINARY NOT NULL DEFAULT  '0',
	`balance_due` FLOAT NULL ,
	`stylist` INT NULL COMMENT  'foreign key',
	`service` INT NULL COMMENT  'foreign key',
	`payment_id` varchar(50) NULL, 
	`status` INT NULL DEFAULT '0' COMMENT 'foreign key',
	`calendarEventURL` VARCHAR( 255 ) NULL,
) ENGINE=MYISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__salonbook_users`;

CREATE TABLE `#__salonbook_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_when` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL,
  `firstName` varchar(50) DEFAULT NULL,
  `lastName` varchar(50) DEFAULT NULL,
  `userName` varchar(50) DEFAULT NULL,
  `calendarLogin` varchar(100) DEFAULT NULL,
  `calendarPassword` varchar(100) DEFAULT NULL,
  `hairstyle` varchar(100) DEFAULT NULL,
  `notes` varchar(500) DEFAULT NULL,
  `completed_parsing` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;

DROP TABLE IF EXISTS `#__salonbook_status`;

CREATE TABLE `#__salonbook_status` (
  `id` int(11) NOT NULL ,
  `status` varchar(25) NOT NULL,
   PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `#__salonbook_status` (`id`,`status`) VALUES
	('0','Waiting for Deposit'),
	('1','In Progress'),
	('2','Completed'),
	('3','Refunded'),
	('4','Cancelled');
	


	
	
	