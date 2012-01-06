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