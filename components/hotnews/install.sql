DROP TABLE IF EXISTS `#__otvety_cats`;

CREATE TABLE IF NOT EXISTS `#__otvety_cats` (
  `id` int(11) NOT NULL auto_increment,
  `parent_id` int(11) NOT NULL,
  `title` varchar(250) NOT NULL,
  `description` text NOT NULL,
  `file` varchar(200) NOT NULL,
  `seolink` text NOT NULL,
  `moderator_id` int(11) NOT NULL,
  `published` int(3) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251;

DROP TABLE IF EXISTS `#__otvety_quests`;

CREATE TABLE IF NOT EXISTS `#__otvety_quests` (
  `id` int(11) NOT NULL auto_increment,
  `category_id` int(11) NOT NULL,
  `pubdate` datetime NOT NULL,
  `user_id` int(11) default NULL,
  `title` varchar(140) NOT NULL,
  `question` text,
  `question_html` text NOT NULL,
  `answer` text NOT NULL,
  `moderator_id` int(11) NOT NULL,
  `hits` int(11) default NULL,
  `answers` int(11) NOT NULL default '0',
  `last_answer` datetime NOT NULL,
  `anonimname` varchar(50) NOT NULL,
  `published` int(11) default NULL,
  PRIMARY KEY  (`id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251;

INSERT INTO `#__comment_targets` (target, component, title)
VALUES ('otvety', 'otvety', 'Ответы');

INSERT INTO `#__actions` (`component`, `name`, `title`, `message`, `is_tracked`, `is_visible`) 
VALUES ('otvety', 'add_question', 'Вопрос', 'спрашивает %s| в разделе %s', 1, 1);