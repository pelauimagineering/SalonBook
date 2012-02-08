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

ALTER TABLE  `#__salonbook_appointments` CHANGE  `status`  `status` INT( 11 ) NULL DEFAULT  '0' COMMENT  'foreign key';	
