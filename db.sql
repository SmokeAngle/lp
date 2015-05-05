/*
Navicat MySQL Data Transfer

Source Server         : 10.10.15.10
Source Server Version : 50142
Source Host           : 10.10.15.10:3307
Source Database       : niux_game_active

Target Server Type    : MYSQL
Target Server Version : 50142
File Encoding         : 65001

Date: 2015-05-05 18:00:31
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for `tmp_dts11yzd`
-- ----------------------------
DROP TABLE IF EXISTS `tmp_dts11yzd`;
CREATE TABLE `tmp_dts11yzd` (
  `userid` int(11) unsigned NOT NULL,
  `serverid` int(8) unsigned NOT NULL,
  `addtime` datetime NOT NULL,
  `act` char(15) NOT NULL DEFAULT 'dts11yzd',
  UNIQUE KEY `uni_act_userid_serverid` (`act`,`userid`,`serverid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of tmp_dts11yzd
-- ----------------------------

-- ----------------------------
-- Table structure for `tmp_dts12yback`
-- ----------------------------
DROP TABLE IF EXISTS `tmp_dts12yback`;
CREATE TABLE `tmp_dts12yback` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `serverid` smallint(3) unsigned NOT NULL,
  `rolename` varchar(21) COLLATE utf8_unicode_ci NOT NULL,
  `encryptname` char(15) COLLATE utf8_unicode_ci NOT NULL,
  `level` smallint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx` (`serverid`)
) ENGINE=InnoDB AUTO_INCREMENT=32772 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of tmp_dts12yback
-- ----------------------------

-- ----------------------------
-- Table structure for `tmp_table`
-- ----------------------------
DROP TABLE IF EXISTS `tmp_table`;
CREATE TABLE `tmp_table` (
  `timu` text,
  `a` text,
  `b` text,
  `c` text,
  `d` text,
  `right` char(1) DEFAULT NULL,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of tmp_table
-- ----------------------------

-- ----------------------------
-- Table structure for `t_address_info`
-- ----------------------------
DROP TABLE IF EXISTS `t_address_info`;
CREATE TABLE `t_address_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `name` varchar(45) COLLATE utf8_unicode_ci DEFAULT '',
  `act` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `mobile` varchar(15) COLLATE utf8_unicode_ci DEFAULT '',
  `telephone` varchar(15) COLLATE utf8_unicode_ci DEFAULT '',
  `zipcode` varchar(10) COLLATE utf8_unicode_ci DEFAULT '',
  `address` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ip` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `status` int(1) DEFAULT '1',
  `addtime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_userid_act` (`userid`,`act`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of t_address_info
-- ----------------------------

-- ----------------------------
-- Table structure for `t_answers`
-- ----------------------------
DROP TABLE IF EXISTS `t_answers`;
CREATE TABLE `t_answers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `answer2question` int(8) unsigned NOT NULL,
  `answer` text,
  PRIMARY KEY (`id`),
  KEY `idx_a2q` (`answer2question`)
) ENGINE=MyISAM AUTO_INCREMENT=371 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of t_answers
-- ----------------------------

-- ----------------------------
-- Table structure for `t_appointment`
-- ----------------------------
DROP TABLE IF EXISTS `t_appointment`;
CREATE TABLE `t_appointment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `act` varchar(20) NOT NULL DEFAULT 'niux',
  `userid` int(11) unsigned NOT NULL,
  `gameid` char(6) NOT NULL DEFAULT '000000',
  `serverid` int(8) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(15) NOT NULL DEFAULT '127.0.0.1',
  `addtime` datetime NOT NULL,
  `status` int(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_act_userid` (`act`,`userid`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of t_appointment
-- ----------------------------

-- ----------------------------
-- Table structure for `t_bind_server`
-- ----------------------------
DROP TABLE IF EXISTS `t_bind_server`;
CREATE TABLE `t_bind_server` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `act` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `userid` int(11) unsigned NOT NULL,
  `gameid` char(6) COLLATE utf8_unicode_ci NOT NULL DEFAULT '000000',
  `serverid` int(8) unsigned NOT NULL DEFAULT '0',
  `roleid` int(8) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '127.0.0.1',
  `addtime` datetime NOT NULL,
  `ext` int(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_act_userid` (`act`,`userid`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of t_bind_server
-- ----------------------------

-- ----------------------------
-- Table structure for `t_checkin`
-- ----------------------------
DROP TABLE IF EXISTS `t_checkin`;
CREATE TABLE `t_checkin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `gameid` varchar(10) COLLATE utf8_unicode_ci DEFAULT '',
  `act` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `signcount` int(10) NOT NULL DEFAULT '0',
  `lastmodify` datetime NOT NULL,
  `totalscore` int(10) NOT NULL DEFAULT '0',
  `history` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `ext` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `ip` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `status` int(1) DEFAULT '1',
  `addtime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_userid_act` (`userid`,`act`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of t_checkin
-- ----------------------------

-- ----------------------------
-- Table structure for `t_comment`
-- ----------------------------
DROP TABLE IF EXISTS `t_comment`;
CREATE TABLE `t_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `gameid` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `act` varchar(45) COLLATE utf8_unicode_ci DEFAULT '',
  `msg` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ip` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `status` int(1) DEFAULT '1',
  `addtime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_gameid_username` (`gameid`,`username`),
  KEY `idx_gameid_userid` (`gameid`,`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of t_comment
-- ----------------------------

-- ----------------------------
-- Table structure for `t_dtsback_fanli`
-- ----------------------------
DROP TABLE IF EXISTS `t_dtsback_fanli`;
CREATE TABLE `t_dtsback_fanli` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) unsigned NOT NULL,
  `sum` int(7) unsigned NOT NULL DEFAULT '0',
  `lvl1` int(3) unsigned NOT NULL DEFAULT '0',
  `lvl2` int(3) unsigned NOT NULL DEFAULT '0',
  `lvl3` int(3) unsigned NOT NULL DEFAULT '0',
  `lvl4` int(3) unsigned NOT NULL DEFAULT '0',
  `gameid` char(6) DEFAULT '000000',
  `serverid` int(6) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid_UNIQUE` (`userid`)
) ENGINE=InnoDB AUTO_INCREMENT=28505 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of t_dtsback_fanli
-- ----------------------------

-- ----------------------------
-- Table structure for `t_export_user`
-- ----------------------------
DROP TABLE IF EXISTS `t_export_user`;
CREATE TABLE `t_export_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `act` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `addtime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_act_userid` (`act`,`userid`)
) ENGINE=InnoDB AUTO_INCREMENT=38066 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of t_export_user
-- ----------------------------

-- ----------------------------
-- Table structure for `t_export_user_money_ext1`
-- ----------------------------
DROP TABLE IF EXISTS `t_export_user_money_ext1`;
CREATE TABLE `t_export_user_money_ext1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `act` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `userid` int(11) NOT NULL,
  `money` int(11) NOT NULL DEFAULT '0',
  `addtime` datetime NOT NULL,
  `ext1` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=183801 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of t_export_user_money_ext1
-- ----------------------------

-- ----------------------------
-- Table structure for `t_gift`
-- ----------------------------
DROP TABLE IF EXISTS `t_gift`;
CREATE TABLE `t_gift` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf8_unicode_ci DEFAULT '',
  `gameid` char(6) COLLATE utf8_unicode_ci NOT NULL DEFAULT '000000',
  `act` varchar(45) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `taskid` varchar(10) COLLATE utf8_unicode_ci DEFAULT '',
  `serverid` int(6) DEFAULT '0',
  `roleid` varchar(125) COLLATE utf8_unicode_ci DEFAULT NULL,
  `giftid` int(10) NOT NULL,
  `giftname` varchar(128) COLLATE utf8_unicode_ci DEFAULT '',
  `num` varchar(7) COLLATE utf8_unicode_ci DEFAULT NULL,
  `code` varchar(50) COLLATE utf8_unicode_ci DEFAULT '',
  `coupon_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `coupon_pwd` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `type` varchar(10) COLLATE utf8_unicode_ci DEFAULT '',
  `ip` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `status` int(1) DEFAULT '0',
  `addtime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_act_userid` (`act`,`userid`),
  KEY `idx_giftid` (`giftid`)
) ENGINE=InnoDB AUTO_INCREMENT=2519 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of t_gift
-- ----------------------------

-- ----------------------------
-- Table structure for `t_invite`
-- ----------------------------
DROP TABLE IF EXISTS `t_invite`;
CREATE TABLE `t_invite` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `act` varchar(45) NOT NULL DEFAULT 'default',
  `userid` int(11) unsigned NOT NULL DEFAULT '0',
  `salt` char(6) NOT NULL DEFAULT 'mysalt',
  `giftid` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uni_act_userid` (`act`,`userid`),
  KEY `idx_giftid` (`giftid`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8 COMMENT='invitelink';

-- ----------------------------
-- Records of t_invite
-- ----------------------------

-- ----------------------------
-- Table structure for `t_invite_records`
-- ----------------------------
DROP TABLE IF EXISTS `t_invite_records`;
CREATE TABLE `t_invite_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auserid` int(11) NOT NULL DEFAULT '0',
  `ausername` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `buserid` int(11) NOT NULL DEFAULT '0',
  `busername` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `addtime` datetime NOT NULL,
  `act` varchar(45) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_auserid_buserid` (`auserid`,`buserid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of t_invite_records
-- ----------------------------

-- ----------------------------
-- Table structure for `t_like`
-- ----------------------------
DROP TABLE IF EXISTS `t_like`;
CREATE TABLE `t_like` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `gameid` varchar(10) COLLATE utf8_unicode_ci DEFAULT '',
  `act` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `serverid` int(6) NOT NULL DEFAULT '0',
  `like_num` int(6) NOT NULL DEFAULT '0',
  `ip` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `status` int(1) DEFAULT '1',
  `addtime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_userid_act` (`userid`,`act`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of t_like
-- ----------------------------

-- ----------------------------
-- Table structure for `t_lot`
-- ----------------------------
DROP TABLE IF EXISTS `t_lot`;
CREATE TABLE `t_lot` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf8_unicode_ci DEFAULT '',
  `gameid` varchar(10) COLLATE utf8_unicode_ci DEFAULT '',
  `act` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `serverid` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `roleid` varchar(45) COLLATE utf8_unicode_ci DEFAULT '',
  `giftid` int(10) NOT NULL,
  `code` varchar(50) COLLATE utf8_unicode_ci DEFAULT '',
  `module` int(2) DEFAULT '1',
  `type` varchar(10) COLLATE utf8_unicode_ci DEFAULT '',
  `ip` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `status` int(1) DEFAULT '1',
  `addtime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_userid_act` (`userid`,`act`)
) ENGINE=InnoDB AUTO_INCREMENT=671 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of t_lot
-- ----------------------------

-- ----------------------------
-- Table structure for `t_lotcode`
-- ----------------------------
DROP TABLE IF EXISTS `t_lotcode`;
CREATE TABLE `t_lotcode` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `gameid` varchar(10) COLLATE utf8_unicode_ci DEFAULT '',
  `serverid` int(10) DEFAULT '0',
  `rolename` varchar(20) COLLATE utf8_unicode_ci DEFAULT '',
  `username` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `module` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `act` varchar(45) COLLATE utf8_unicode_ci DEFAULT '',
  `num` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` int(1) DEFAULT '1',
  `addtime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_userid_code` (`userid`,`code`),
  KEY `idx_userid_module_num` (`userid`,`module`,`num`)
) ENGINE=InnoDB AUTO_INCREMENT=2026 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of t_lotcode
-- ----------------------------

-- ----------------------------
-- Table structure for `t_lotinfo`
-- ----------------------------
DROP TABLE IF EXISTS `t_lotinfo`;
CREATE TABLE `t_lotinfo` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `act` char(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'unknown',
  `userid` int(11) unsigned NOT NULL,
  `gameid` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `serverid` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `totaltimes` int(5) unsigned NOT NULL,
  `lottimes` int(5) unsigned NOT NULL,
  `lastlottime` datetime NOT NULL,
  `lastaddtime` datetime NOT NULL,
  `lastip` char(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '127.0.0.1',
  PRIMARY KEY (`id`),
  KEY `uni_act_userid_gameid_serverid` (`act`,`userid`,`gameid`,`serverid`)
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of t_lotinfo
-- ----------------------------

-- ----------------------------
-- Table structure for `t_mail_info`
-- ----------------------------
DROP TABLE IF EXISTS `t_mail_info`;
CREATE TABLE `t_mail_info` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `act` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `userid` int(11) unsigned NOT NULL,
  `username` varchar(45) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'unknown',
  `name` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8_unicode_ci,
  `qq` varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` char(12) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zipcode` char(6) COLLATE utf8_unicode_ci DEFAULT '000000',
  `edittime` datetime NOT NULL,
  `editip` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '127.0.0.1',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uni_act_userid` (`act`,`userid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of t_mail_info
-- ----------------------------

-- ----------------------------
-- Table structure for `t_pay_callback`
-- ----------------------------
DROP TABLE IF EXISTS `t_pay_callback`;
CREATE TABLE `t_pay_callback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf8_unicode_ci DEFAULT '',
  `money` int(11) NOT NULL,
  `gameid` varchar(10) COLLATE utf8_unicode_ci DEFAULT '',
  `act` varchar(45) COLLATE utf8_unicode_ci DEFAULT '',
  `serverid` varchar(10) COLLATE utf8_unicode_ci DEFAULT '',
  `roleid` varchar(15) COLLATE utf8_unicode_ci DEFAULT '',
  `type` varchar(10) COLLATE utf8_unicode_ci DEFAULT '',
  `orderid` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `status` int(1) DEFAULT '1',
  `timetype` int(3) unsigned NOT NULL DEFAULT '0' COMMENT '1：日，2：月，3：年',
  `timenum` int(3) unsigned NOT NULL DEFAULT '0',
  `ext` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `addtime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_userid_act_orderid` (`userid`,`act`,`orderid`)
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of t_pay_callback
-- ----------------------------

-- ----------------------------
-- Table structure for `t_phone_bind`
-- ----------------------------
DROP TABLE IF EXISTS `t_phone_bind`;
CREATE TABLE `t_phone_bind` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `gameid` char(6) COLLATE utf8_unicode_ci NOT NULL DEFAULT '000000',
  `act` varchar(45) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `phone` char(13) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0000000000000',
  `email` varchar(45) COLLATE utf8_unicode_ci DEFAULT '',
  `uin` int(14) DEFAULT NULL,
  `ip` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '127.0.0.1',
  `status` int(1) DEFAULT '1',
  `addtime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_act_userid` (`act`,`userid`)
) ENGINE=InnoDB AUTO_INCREMENT=210 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of t_phone_bind
-- ----------------------------

-- ----------------------------
-- Table structure for `t_progress`
-- ----------------------------
DROP TABLE IF EXISTS `t_progress`;
CREATE TABLE `t_progress` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) unsigned NOT NULL,
  `act` varchar(45) NOT NULL,
  `current` int(11) unsigned NOT NULL,
  `lastsubmit` datetime NOT NULL,
  `score` int(4) unsigned NOT NULL DEFAULT '0',
  `finished` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uni_act_userid` (`act`,`userid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of t_progress
-- ----------------------------

-- ----------------------------
-- Table structure for `t_questions`
-- ----------------------------
DROP TABLE IF EXISTS `t_questions`;
CREATE TABLE `t_questions` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `act` varchar(45) NOT NULL,
  `question` text NOT NULL,
  `answers` varchar(45) NOT NULL DEFAULT '0',
  `right` int(11) unsigned NOT NULL DEFAULT '0',
  `isActive` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_act` (`act`),
  KEY `idx_right` (`right`)
) ENGINE=MyISAM AUTO_INCREMENT=101 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of t_questions
-- ----------------------------

-- ----------------------------
-- Table structure for `t_server_bind`
-- ----------------------------
DROP TABLE IF EXISTS `t_server_bind`;
CREATE TABLE `t_server_bind` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf8_unicode_ci DEFAULT '',
  `gameid` varchar(10) COLLATE utf8_unicode_ci DEFAULT '',
  `act` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `serverid` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `role` varchar(45) COLLATE utf8_unicode_ci DEFAULT '',
  `ip` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `status` int(1) DEFAULT '1',
  `addtime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_userid_act` (`userid`,`act`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of t_server_bind
-- ----------------------------

-- ----------------------------
-- Table structure for `t_sign`
-- ----------------------------
DROP TABLE IF EXISTS `t_sign`;
CREATE TABLE `t_sign` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `act` varchar(45) NOT NULL,
  `userid` int(11) unsigned NOT NULL,
  `signtimes` int(5) unsigned NOT NULL DEFAULT '0',
  `lasttimes` int(5) unsigned NOT NULL DEFAULT '0',
  `lastsign` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_act_userid` (`act`,`userid`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8 COMMENT='ç­¾åˆ°è¡¨';

-- ----------------------------
-- Records of t_sign
-- ----------------------------

-- ----------------------------
-- Table structure for `t_sign_detail`
-- ----------------------------
DROP TABLE IF EXISTS `t_sign_detail`;
CREATE TABLE `t_sign_detail` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `link` int(11) unsigned NOT NULL,
  `yearMonth` char(6) NOT NULL DEFAULT '000000',
  `sign` char(8) NOT NULL DEFAULT '00000000',
  PRIMARY KEY (`id`),
  KEY `idx_link` (`link`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of t_sign_detail
-- ----------------------------

-- ----------------------------
-- Table structure for `t_task`
-- ----------------------------
DROP TABLE IF EXISTS `t_task`;
CREATE TABLE `t_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `taskid` int(4) NOT NULL DEFAULT '0',
  `gameid` varchar(10) COLLATE utf8_unicode_ci DEFAULT '',
  `act` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `serverid` int(6) NOT NULL DEFAULT '0',
  `num` int(6) NOT NULL DEFAULT '0',
  `ip` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `status` int(1) DEFAULT '1',
  `addtime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_userid_act` (`userid`,`act`)
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of t_task
-- ----------------------------

-- ----------------------------
-- Table structure for `t_vir_goods`
-- ----------------------------
DROP TABLE IF EXISTS `t_vir_goods`;
CREATE TABLE `t_vir_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gameid` varchar(10) COLLATE utf8_unicode_ci DEFAULT '',
  `act` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `serverid` int(6) NOT NULL DEFAULT '0',
  `num` int(6) NOT NULL DEFAULT '0',
  `ip` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `status` int(1) DEFAULT '1',
  `addtime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_userid_act` (`userid`,`act`),
  KEY `idx_num` (`num`),
  KEY `idx_act_num` (`act`,`num`)
) ENGINE=InnoDB AUTO_INCREMENT=153 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of t_vir_goods
-- ----------------------------

-- ----------------------------
-- Table structure for `t_vote`
-- ----------------------------
DROP TABLE IF EXISTS `t_vote`;
CREATE TABLE `t_vote` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `act` varchar(45) NOT NULL,
  `voteid` varchar(45) NOT NULL,
  `num` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_act` (`act`),
  KEY `idx_voteid` (`voteid`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of t_vote
-- ----------------------------

-- ----------------------------
-- Table structure for `t_vote_info`
-- ----------------------------
DROP TABLE IF EXISTS `t_vote_info`;
CREATE TABLE `t_vote_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `act` varchar(45) NOT NULL,
  `userid` int(11) unsigned NOT NULL,
  `votefor` varchar(45) NOT NULL,
  `ip` varchar(15) NOT NULL DEFAULT '127.0.0.1',
  `addtime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_act_userid` (`act`,`userid`),
  KEY `idx_votefor` (`votefor`),
  CONSTRAINT `fk_votefor_voteid` FOREIGN KEY (`votefor`) REFERENCES `t_vote` (`voteid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of t_vote_info
-- ----------------------------

-- ----------------------------
-- Table structure for `t_xl_rtx`
-- ----------------------------
DROP TABLE IF EXISTS `t_xl_rtx`;
CREATE TABLE `t_xl_rtx` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(125) COLLATE utf8_unicode_ci NOT NULL,
  `status` int(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=7239 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of t_xl_rtx
-- ----------------------------
