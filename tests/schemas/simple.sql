CREATE TABLE `parents` (
  `parent_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `children` (
  `child_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) unsigned NOT NULL,
  `child_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`child_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `friends` (
  `friend_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `friend_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`friend_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `parent_friends` (
  `parent_friend_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) unsigned NOT NULL,
  `friend_id` int(11) unsigned NOT NULL,
  `parent_friend_relation` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`parent_friend_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;