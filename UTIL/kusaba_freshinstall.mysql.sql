-- --------------------------------------------------------

-- #snivystuff : Banners
CREATE TABLE `PREFIX_banners` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `link` varchar(255) NOT NULL,
  `path` varchar(127) NOT NULL,
  `custom` tinyint(1) NOT NULL DEFAULT '0',
  `version` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- #snivystuff : Custom styles
CREATE TABLE `PREFIX_customstyles` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `version` smallint(11) NOT NULL DEFAULT '0',
  `temporary` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


--
-- Table structure for table `ads`
--

CREATE TABLE `PREFIX_ads` (
  `id` smallint(1) unsigned NOT NULL,
  `position` varchar(3) NOT NULL,
  `disp` tinyint(1) NOT NULL,
  `boards` varchar(255) NOT NULL,
  `code` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `PREFIX_announcements` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `parentid` int(10) unsigned NOT NULL default '0',
  `subject` varchar(255) NOT NULL,
  `postedat` int(20) NOT NULL,
  `postedby` varchar(75) NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `banlist`
--

CREATE TABLE `PREFIX_banlist` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `type` tinyint(1) NOT NULL default '0',
  `expired` tinyint(1) NOT NULL default '0',
  `allowread` tinyint(1) NOT NULL default '1',
  `ip` varchar(50) NOT NULL,
  `ipmd5` char(32) NOT NULL,
  `globalban` tinyint(1) NOT NULL default '0',
  `boards` varchar(255) NOT NULL,
  `by` varchar(75) NOT NULL,
  `at` int(20) NOT NULL,
  `until` int(20) NOT NULL,
  `reason` text NOT NULL,
  `staffnote` text NOT NULL,
  `appeal` text,
  `appealat` int(20) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bannedhashes`
--

CREATE TABLE `PREFIX_bannedhashes` (
  `id` int(10) NOT NULL auto_increment,
  `md5` varchar(255) NOT NULL,
  `bantime` int(10) NOT NULL default '0',
  `description` text NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `blotter`
--

CREATE TABLE `PREFIX_blotter` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `important` tinyint(1) NOT NULL,
  `at` int(20) NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `boards`
--

CREATE TABLE `PREFIX_boards` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `order` tinyint(5) NOT NULL DEFAULT '0',
  `name` varchar(75) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `start` int(10) unsigned NOT NULL DEFAULT '1',
  `maxfiles` TINYINT(3) UNSIGNED NOT NULL DEFAULT '4',
  `desc` varchar(75) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `section` tinyint(2) NOT NULL DEFAULT '0',
  `maximagesize` int(20) NOT NULL DEFAULT '4096000',
  `maxpages` int(20) NOT NULL DEFAULT '11',
  `maxage` int(20) NOT NULL DEFAULT '0',
  `markpage` tinyint(4) NOT NULL DEFAULT '0',
  `maxreplies` int(5) NOT NULL DEFAULT '500',
  `messagelength` int(10) NOT NULL DEFAULT '8192',
  `createdon` int(20) NOT NULL,
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  `includeheader` text COLLATE utf8_unicode_ci NOT NULL,
  `redirecttothread` tinyint(1) NOT NULL DEFAULT '0',
  `anonymous` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Аноним',
  `forcedanon` tinyint(1) NOT NULL DEFAULT '0',
  `embeds_allowed` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'you,vim,cob',
  `trial` tinyint(1) NOT NULL DEFAULT '0',
  `popular` tinyint(1) NOT NULL DEFAULT '0',
  `defaultstyle` varchar(50) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `locale` varchar(30) CHARACTER SET latin1 NOT NULL DEFAULT 'ru',
  `showid` tinyint(1) NOT NULL DEFAULT '0',
  `compactlist` tinyint(1) NOT NULL DEFAULT '0',
  `enablereporting` tinyint(1) NOT NULL DEFAULT '1',
  `enablecaptcha` tinyint(1) NOT NULL DEFAULT '1',
  `enablenofile` tinyint(1) NOT NULL DEFAULT '0',
  `enablearchiving` tinyint(1) NOT NULL DEFAULT '0',
  `enablecatalog` tinyint(1) NOT NULL DEFAULT '1',
  `loadbalanceurl` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `loadbalancepassword` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `balls` tinyint(1) NOT NULL DEFAULT '0',
  `dice` tinyint(1) NOT NULL DEFAULT '0',
  `useragent` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `board_filetypes`
--

CREATE TABLE `PREFIX_board_filetypes` (
  `boardid` tinyint(5) NOT NULL default '0',
  `typeid` mediumint(5) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `embeds`
--

CREATE TABLE `PREFIX_embeds` (
  `id` tinyint(5) unsigned NOT NULL auto_increment,
  `filetype` varchar(3) NOT NULL,
  `name` varchar(255) NOT NULL,
  `videourl` varchar(510) NOT NULL,
  `width` tinyint(3) unsigned NOT NULL,
  `height` tinyint(3) unsigned NOT NULL,
  `code` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `PREFIX_events` (
  `name` varchar(255) NOT NULL,
  `at` int(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `filetypes`
--

CREATE TABLE `PREFIX_filetypes` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `filetype` varchar(255) NOT NULL,
  `mime` varchar(255) NOT NULL default '',
  `image` varchar(255) NOT NULL default '',
  `image_w` int(7) NOT NULL default '0',
  `image_h` int(7) NOT NULL default '0',
  `force_thumb` int(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `front`
--

CREATE TABLE `PREFIX_front` (
	`id` smallint(5) unsigned NOT NULL auto_increment,
	`page` smallint(1) unsigned NOT NULL default '0',
	`order` smallint(5) unsigned NOT NULL default '0',
	`subject` varchar(255) NOT NULL,
	`message` text NOT NULL,
	`timestamp` int(20) NOT NULL default '0',
	`poster` varchar(75) NOT NULL,
	`email` varchar(255) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `loginattempts`
--

CREATE TABLE `PREFIX_loginattempts` (
  `username` varchar(255) NOT NULL,
  `ip` varchar(20) NOT NULL,
  `timestamp` int(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `modlog`
--

CREATE TABLE `PREFIX_modlog` (
  `entry` text NOT NULL,
  `user` varchar(255) NOT NULL,
  `category` tinyint(2) NOT NULL default '0',
  `timestamp` int(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `module_settings`
--

CREATE TABLE `PREFIX_module_settings` (
  `module` varchar(255) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `type` varchar(255) NOT NULL default 'string'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `PREFIX_posts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `boardid` smallint(5) unsigned NOT NULL,
  `parentid` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `tripcode` varchar(30) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `password` varchar(255) NOT NULL,
  `ip` varchar(75) NOT NULL,
  `ipmd5` char(32) NOT NULL,
  `tag` varchar(5) NOT NULL,
  `timestamp` int(20) unsigned NOT NULL,
  `stickied` tinyint(1) NOT NULL DEFAULT '0',
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  `posterauthority` tinyint(1) NOT NULL DEFAULT '0',
  `reviewed` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `deleted_timestamp` int(20) NOT NULL DEFAULT '0',
  `IS_DELETED` tinyint(1) NOT NULL DEFAULT '0',
  `bumped` int(20) unsigned NOT NULL DEFAULT '0',
  `country` varchar(10) NOT NULL DEFAULT 'xx',
  PRIMARY KEY (`boardid`,`id`),
  KEY `parentid` (`parentid`),
  KEY `bumped` (`bumped`),
  KEY `stickied` (`stickied`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `PREFIX_files` (
  `file_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_id` INT(10) UNSIGNED NOT NULL,
  `boardid` SMALLINT(5) UNSIGNED NOT NULL,
  `file` VARCHAR(50) NOT NULL,
  `file_md5` CHAR(32) NOT NULL,
  `file_type` VARCHAR(20) NOT NULL,
  `file_original` VARCHAR(255) NOT NULL,
  `file_size` INT(20) NOT NULL DEFAULT '0',
  `file_size_formatted` VARCHAR(75) NOT NULL,
  `image_w` SMALLINT(5) NOT NULL DEFAULT '0',
  `image_h` SMALLINT(5) NOT NULL DEFAULT '0',
  `thumb_w` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
  `thumb_h` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`file_id`),
  INDEX `file_md5` (`file_md5`),
  INDEX `file_id` (`file_id`),
  INDEX `post_id` (`post_id`)
)
ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- View `postembeds`
--

CREATE VIEW `PREFIX_postembeds`  
AS SELECT 
  `posts`.`id` AS `id`,
  `posts`.`boardid` AS `boardid`,
  `posts`.`parentid` AS `parentid`,
  `posts`.`name` AS `name`,
  `posts`.`tripcode` AS `tripcode`,
  `posts`.`email` AS `email`,
  `posts`.`subject` AS `subject`,
  `posts`.`message` AS `message`,
  `posts`.`password` AS `password`,
  `posts`.`ip` AS `ip`,
  `posts`.`ipmd5` AS `ipmd5`,
  `posts`.`tag` AS `tag`,
  `posts`.`timestamp` AS `timestamp`,
  `posts`.`stickied` AS `stickied`,
  `posts`.`locked` AS `locked`,
  `posts`.`posterauthority` AS `posterauthority`,
  `posts`.`reviewed` AS `reviewed`,
  `posts`.`deleted_timestamp` AS `deleted_timestamp`,
  `posts`.`IS_DELETED` AS `IS_DELETED`,
  `posts`.`bumped` AS `bumped`,
  `posts`.`country` AS `country`,
  `files`.`file` AS `file`,
  `files`.`file_md5` AS `file_md5`,
  `files`.`file_type` AS `file_type`,
  `files`.`file_original` AS `file_original`,
  `files`.`file_size` AS `file_size`,
  `files`.`file_size_formatted` AS `file_size_formatted`,
  `files`.`image_w` AS `image_w`,
  `files`.`image_h` AS `image_h`,
  `files`.`thumb_w` AS `thumb_w`,
  `files`.`thumb_h` AS `thumb_h` 
FROM (
  `posts` LEFT JOIN `files` ON (
    (
      (`files`.`post_id` = `posts`.`id`) 
      and 
      (`files`.`boardid` = `posts`.`boardid`)
    )
  )
);

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `PREFIX_reports` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `cleared` tinyint(1) NOT NULL default '0',
  `board` varchar(255) NOT NULL,
  `postid` int(20) NOT NULL,
  `when` int(20) NOT NULL,
  `ip` varchar(75) NOT NULL,
  `reason` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `PREFIX_sections` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `order` tinyint(3) NOT NULL default '0',
  `hidden` tinyint(1) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '0',
  `abbreviation` varchar(10) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `PREFIX_staff` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `salt` varchar(3) NOT NULL,
  `type` tinyint(1) NOT NULL default '0',
  `boards` text,
  `addedon` int(20) NOT NULL,
  `lastactive` int(20) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `watchedthreads`
--

CREATE TABLE `PREFIX_watchedthreads` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `threadid` int(20) NOT NULL,
  `board` varchar(255) NOT NULL,
  `ip` char(15) NOT NULL,
  `lastsawreplyid` int(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wordfilter`
--

CREATE TABLE `PREFIX_wordfilter` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `word` varchar(75) NOT NULL,
  `replacedby` varchar(75) NOT NULL,
  `boards` text NOT NULL,
  `time` int(20) NOT NULL,
  `regex` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `PREFIX_sections` (`id`, `order`, `hidden`, `name`, `abbreviation`) VALUES
(1, 1, 0, 'all', 'all'),
(2, 2, 0, 'geek', 'geek'),
(3, 3, 0, 'other', 'other'),
(4, 4, 0, 'adult', 'adult'),
(5, 0, 1, '2.0', '20');

INSERT INTO `PREFIX_ads` (`id`, `position`, `disp`, `boards`, `code`) VALUES (1, 'top', 0, '', 'Right Frame Top'), (2, 'bot', 0, '', 'Right Frame Bottom');

INSERT INTO `PREFIX_filetypes` 
(`filetype`,               `mime`,                `image`,    `image_w`, `image_h`, `force_thumb`) VALUES
(   'jpg',                   '',                     '',          0,         0,           0),
(   'gif',                   '',                     '',          0,         0,           0),
(   'png',                   '',                     '',          0,         0,           0),
(   'mp3',                   '',                  'mp3.png',      36,        48,          1),
(   'ogg',                   '',                  'ogg.png',      36,        48,          1),
(   'webm',              'video/webm',               '',          0,         0,           0),
(   'css',                   '',                  'css.png',      36,        48,          1),
(   'swf',     'application/x-shockwave-flash', 'flash.png',      36,        48,          1);

INSERT INTO `PREFIX_events` (`name`, `at`) VALUES ('pingback', 0), ('sitemap', 0);

INSERT INTO `PREFIX_embeds` (`filetype`, `name`, `videourl`, `width`, `height`, `code`) VALUES
('you', 'Youtube', 'http://www.youtube.com/watch?v=', 255, 255, '<div class=\"youtube embed wrapper\" style=\"background-image:url(http://i.ytimg.com/vi/EMBED_ID/0.jpg)\" data-id=\"EMBED_ID\" data-site=\"youtube\"></div>'),
('vim', 'Vimeo', 'http://vimeo.com/', 200, 164, '<div class=\"vimeo embed wrapper\" data-id=\"EMBED_ID\" data-site=\"vimeo\"></div>'),
('cob', 'Coub', 'http://coub.com/view/', 200, 164, '<div class=\"coub embed wrapper\" data-id=\"EMBED_ID\" data-site=\"coub\"></div>');

INSERT INTO `PREFIX_boards` 
(`section`, `order`, `name`, `desc`,                    `maximagesize`, `maxpages`, `createdon`,           `anonymous`, `forcedanon`, `enablenofile`, `dice`, `useragent` ) VALUES
('1',       '1',     'b',    'Бред',                       '10240000',      '11',    UNIX_TIMESTAMP(NOW()), '',              '1',           '0',        '0',      '0' ),
('1',       '2',     'd',    'Рисунки',                    '10240000',      '5',     UNIX_TIMESTAMP(NOW()), 'Аноним',        '0',           '0',        '0',      '0' ),
('1',       '3',     'r',    'Реквесты',                   '10240000',      '11',    UNIX_TIMESTAMP(NOW()), 'Аноним',        '0',           '1',        '0',      '0' ),
('1',       '4',     '0',    'Øчан',                       '10240000',      '11',    UNIX_TIMESTAMP(NOW()), 'Аноним',        '0',           '1',        '0',      '1' ),
('2',       '1',     'e',    'Радиоэлектроника',           '10240000',      '11',    UNIX_TIMESTAMP(NOW()), 'Аноним',        '0',           '0',        '0',      '0' ),
('2',       '2',     't',    'Технологии',                 '10240000',      '11',    UNIX_TIMESTAMP(NOW()), 'Аноним',        '0',           '0',        '0',      '0' ),
('2',       '3',     'hw',   'Железо',                     '10240000',      '11',    UNIX_TIMESTAMP(NOW()), 'Аноним',        '0',           '0',        '0',      '0' ),
('2',       '4',     's',    'Софт',                       '10240000',      '11',    UNIX_TIMESTAMP(NOW()), 'Аноним',        '0',           '0',        '0',      '1' ),
('2',       '5',     'c',    'Быдлокодинг',                '10240000',      '11',    UNIX_TIMESTAMP(NOW()), 'Аноним',        '0',           '0',        '0',      '0' ),
('2',       '6',     'vg',   'Видеоигры',                  '10240000',      '11',    UNIX_TIMESTAMP(NOW()), 'Аноним',        '0',           '0',        '0',      '0' ),
('2',       '7',     '8',    '8-bit и pixel art',          '10240000',      '11',    UNIX_TIMESTAMP(NOW()), 'Аноним',        '0',           '0',        '0',      '0' ),
('2',       '8',     'bg',   'Настольные игры',            '10240000',      '11',    UNIX_TIMESTAMP(NOW()), 'Аноним',        '0',           '0',        '1',      '0' ),
('2',       '9',     'wh',   'Warhammer',                  '10240000',      '11',    UNIX_TIMESTAMP(NOW()), 'Аноним',        '0',           '0',        '1',      '0' ),
('3',       '1',     'a',    'Аниме',                      '10240000',      '11',    UNIX_TIMESTAMP(NOW()), 'Аноним',        '0',           '0',        '0',      '0' ),
('3',       '2',     'au',   'Автомобили',                 '10240000',      '11',    UNIX_TIMESTAMP(NOW()), 'Аноним',        '0',           '0',        '0',      '0' ),
('3',       '3',     'bo',   'Книги',                      '10240000',      '11',    UNIX_TIMESTAMP(NOW()), 'Аноним',        '0',           '0',        '0',      '0' ),
('3',       '4',     'co',   'Комиксы',                    '10240000',      '11',    UNIX_TIMESTAMP(NOW()), 'Аноним',        '0',           '0',        '0',      '0' ),
('3',       '5',     'cook', 'Лепка супов',                '10240000',      '11',    UNIX_TIMESTAMP(NOW()), 'Аноним',        '0',           '0',        '0',      '0' ),
('3',       '6',     'f',    'Flash',                      '10240000',      '11',    UNIX_TIMESTAMP(NOW()), 'Аноним',        '0',           '0',        '0',      '0' ),
('3',       '7',     'fa',   'Мода и стиль',               '10240000',      '11',    UNIX_TIMESTAMP(NOW()), 'Аноним',        '0',           '0',        '0',      '0' ),
('3',       '8',     'fl',   'Иностранные языки',          '10240000',      '11',    UNIX_TIMESTAMP(NOW()), 'Аноним',        '0',           '0',        '0',      '0' ),
('3',       '9',     'm',    'Музыка',                     '10240000',      '11',    UNIX_TIMESTAMP(NOW()), 'Аноним',        '0',           '0',        '0',      '0' ),
('3',       '10',    'med',  'Медицина',                   '10240000',      '11',    UNIX_TIMESTAMP(NOW()), 'Аноним',        '0',           '0',        '0',      '0' ),
('3',       '11',    'ph',   'Фотографии',                 '10240000',      '11',    UNIX_TIMESTAMP(NOW()), 'Аноним',        '0',           '0',        '0',      '0' ),
('3',       '12',    'tv',   'Кино и сериалы',             '10240000',      '11',    UNIX_TIMESTAMP(NOW()), 'Аноним',        '0',           '0',        '0',      '0' ),
('3',       '13',    'war',  'Вооружение',                 '10240000',      '11',    UNIX_TIMESTAMP(NOW()), 'Аноним',        '0',           '0',        '0',      '0' ),
('3',       '14',    'wp',   'Обои',                       '10240000',      '11',    UNIX_TIMESTAMP(NOW()), 'Аноним',        '0',           '0',        '0',      '0' ),
('4',       '1',     'h',    'Хентай',                     '10240000',      '11',    UNIX_TIMESTAMP(NOW()), 'Аноним',        '0',           '0',        '0',      '0' ),
('4',       '2',     'g',    'Девушки',                    '10240000',      '11',    UNIX_TIMESTAMP(NOW()), 'Аноним',        '0',           '0',        '0',      '0' ),
('4',       '3',     'fur',  'Фурри',                      '10240000',      '11',    UNIX_TIMESTAMP(NOW()), 'Аноним',        '0',           '0',        '0',      '0' ),
('',        '1',     'nhk',  'Nullchan Hikikomori Kyokai', '10240000',      '11',    UNIX_TIMESTAMP(NOW()), 'Аноним',        '0',           '0',        '0',      '0' ),
('',        '1',     'test', 'Тестирования движка',        '10240000',      '1',     UNIX_TIMESTAMP(NOW()), 'Аноним',        '0',           '0',        '0',      '0' ),
('',        '1',     'i',    'Invasion',                   '10240000',      '11',    UNIX_TIMESTAMP(NOW()), 'Аноним',        '0',           '1',        '1',      '1' );