DROP TABLE IF EXISTS `#__salonbook_services`;
 
CREATE TABLE `#__salonbook_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sku` varchar(25),
  `name` varchar(100) NOT NULL,
  `durationInMinutes` int(11) ,
   PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
 
INSERT INTO `#__salonbook_services` (`name`, `durationInMinutes`) VALUES
	(`Shampoo`,30 ),
	(`Twist`, 60),
	(`Re-tightening`, 60),
	(`Braid`, 120),
	(`Wash and Style`, 60),
	(`Wash and Roller-set`, 90);