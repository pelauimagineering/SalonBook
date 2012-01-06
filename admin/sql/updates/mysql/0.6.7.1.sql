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
