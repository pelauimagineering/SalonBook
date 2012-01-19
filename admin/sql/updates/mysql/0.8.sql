ALTER TABLE `#__salonbook_appointments` CHANGE  `paypal_id`  `payment_id` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE `#__salonbook_appointments` ADD `status` INT NULL COMMENT 'foreign key' AFTER `payment_id`;

CREATE TABLE `#__salonbook_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` varchar(25) NOT NULL,
   PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

INSERT INTO `#__salonbook_status` (`status`) VALUES
	('In Progress'),
	('Completed'),
	('Refunded'),
	('Cancelled');