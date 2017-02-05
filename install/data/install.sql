DROP TABLE IF EXISTS `meu_albummeta`;
CREATE TABLE `meu_albummeta` (
  `ameta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `album_id` bigint(20) unsigned NOT NULL,
  `meta_key` varchar(255) NOT NULL,
  `meta_value` longtext,
  PRIMARY KEY (`ameta_id`),
  KEY `album_id` (`album_id`),
  KEY `meta_key` (`meta_key`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `meu_albums`;
CREATE TABLE `meu_albums` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `cate_id` bigint(4) unsigned NOT NULL DEFAULT '0',
  `cover_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `cover_ext` varchar(20) DEFAULT NULL,
  `comments_num` int(11) unsigned NOT NULL DEFAULT '0',
  `photos_num` int(11) unsigned NOT NULL DEFAULT '0',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0',
  `up_time` int(11) unsigned NOT NULL DEFAULT '0',
  `tags` varchar(255) DEFAULT NULL,
  `priv_type` tinyint(1) NOT NULL DEFAULT '0',
  `priv_pass` varchar(100) DEFAULT NULL,
  `priv_question` varchar(255) DEFAULT NULL,
  `priv_answer` varchar(255) DEFAULT NULL,
  `desc` longtext,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `enable_comment` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `cover_id` (`cover_id`),
  KEY `cate_id` (`cate_id`)
) TYPE=MyISAM ;

DROP TABLE IF EXISTS `meu_commentmeta`;
CREATE TABLE `meu_commentmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comment_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` longtext,
  PRIMARY KEY (`meta_id`),
  KEY `comment_id` (`comment_id`),
  KEY `meta_key` (`meta_key`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `meu_comments`;
CREATE TABLE `meu_comments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `ref_id` bigint(20) unsigned NOT NULL,
  `quote_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `email` varchar(200) NOT NULL,
  `author` varchar(100) NOT NULL,
  `reply_author` varchar(100) DEFAULT NULL,
  `author_ip` varchar(50) NOT NULL,
  `content` text NOT NULL,
  `pid` bigint(20) unsigned NOT NULL DEFAULT '0',
  `post_time` int(11) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `ref_id` (`ref_id`),
  KEY `pid` (`pid`)
) TYPE=MyISAM ;

DROP TABLE IF EXISTS `meu_photometa`;
CREATE TABLE `meu_photometa` (
  `pmeta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `photo_id` bigint(20) unsigned NOT NULL,
  `meta_key` varchar(255) NOT NULL,
  `meta_value` longtext,
  PRIMARY KEY (`pmeta_id`),
  KEY `photo_id` (`photo_id`),
  KEY `meta_key` (`meta_key`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `meu_photos`;
CREATE TABLE `meu_photos` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `album_id` bigint(20) NOT NULL,
  `cate_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `thumb` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `width` int(11) NOT NULL DEFAULT '0',
  `height` int(11) NOT NULL DEFAULT '0',
  `hits` bigint(20) NOT NULL DEFAULT '0',
  `comments_num` int(11) NOT NULL DEFAULT '0',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `taken_time` int(11) NOT NULL DEFAULT '0',
  `taken_y` smallint(4) NOT NULL DEFAULT '0',
  `taken_m` tinyint(2) NOT NULL DEFAULT '0',
  `taken_d` tinyint(2) NOT NULL DEFAULT '0',
  `create_y` smallint(4) NOT NULL DEFAULT '0',
  `create_m` tinyint(2) NOT NULL DEFAULT '0',
  `create_d` tinyint(2) NOT NULL DEFAULT '0',
  `desc` longtext,
  `exif` longtext,
  `tags` varchar(255) DEFAULT NULL,
  `is_cover` tinyint(1) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `album_id` (`album_id`),
  KEY `cate_id` (`cate_id`)
) TYPE=MyISAM ;

DROP TABLE IF EXISTS `meu_plugins`;
CREATE TABLE `meu_plugins` (
  `plugin_id` varchar(32) NOT NULL,
  `plugin_name` varchar(200) NOT NULL,
  `description` varchar(255) NOT NULL,
  `plugin_config` longtext,
  `local_ver` varchar(20) NOT NULL,
  `remote_ver` varchar(20) DEFAULT NULL,
  `available` enum('true','false') NOT NULL DEFAULT 'false',
  `author_name` varchar(100) DEFAULT NULL,
  `author_url` varchar(100) DEFAULT NULL,
  `author_email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`plugin_id`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `meu_setting`;
CREATE TABLE `meu_setting` (
  `name` varchar(50) NOT NULL,
  `value` longtext NOT NULL,
  PRIMARY KEY (`name`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `meu_tags`;
CREATE TABLE `meu_tags` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `name` varchar(100) NOT NULL,
  `count` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `type` (`type`,`name`),
  KEY `taglist` (`type`,`count`)
) TYPE=MyISAM ;

DROP TABLE IF EXISTS `meu_tag_rel`;
CREATE TABLE `meu_tag_rel` (
  `tag_id` bigint(20) unsigned NOT NULL,
  `rel_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`tag_id`,`rel_id`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `meu_usermeta`;
CREATE TABLE `meu_usermeta` (
  `umeta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `userid` bigint(20) unsigned NOT NULL,
  `meta_key` varchar(255) NOT NULL,
  `meta_value` longtext,
  PRIMARY KEY (`umeta_id`),
  KEY `userid` (`userid`),
  KEY `meta_key` (`meta_key`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `meu_users`;
CREATE TABLE `meu_users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(50) NOT NULL,
  `user_pass` varchar(50) NOT NULL,
  `user_nicename` varchar(100) NOT NULL,
  `create_time` int(11) NOT NULL DEFAULT '0',
  `user_status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) TYPE=MyISAM ;

DROP TABLE IF EXISTS `meu_cate`;
CREATE TABLE `meu_cate` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `par_id` int(4) NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL,
  `cate_path` varchar(255) DEFAULT NULL,
  `sort` int(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `par_id` (`par_id`)
) TYPE=MyISAM ;

DROP TABLE IF EXISTS `meu_nav`;
CREATE TABLE `meu_nav` (
`id` smallint(4) NOT NULL AUTO_INCREMENT ,
`type` tinyint(1) NOT NULL DEFAULT '1',
`name` varchar(50) NOT NULL ,
`url` varchar(200) NOT NULL ,
`sort` smallint(4) NOT NULL DEFAULT '100',
`enable` tinyint(1) NOT NULL DEFAULT '1',
PRIMARY KEY ( `id` )
) TYPE=MyISAM ;