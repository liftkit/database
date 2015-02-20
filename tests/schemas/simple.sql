CREATE TABLE `parents` (
  `parent_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

CREATE TABLE `children` (
  `child_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) unsigned NOT NULL,
  `child_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`child_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;