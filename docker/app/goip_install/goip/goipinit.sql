-- MySQL dump 10.9
--
-- Host: localhost    Database: goip
-- ------------------------------------------------------
-- Server version       4.1.20

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


--
-- Table structure for table `crowd`
--
DROP database IF EXISTS `goip`;
create database goip;
use goip;

DROP TABLE IF EXISTS `crowd`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `crowd` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(20) NOT NULL default '',
  `info` varchar(100) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `crowd`
--

LOCK TABLES `crowd` WRITE;
/*!40000 ALTER TABLE `crowd` DISABLE KEYS */;
INSERT INTO `crowd` VALUES (1,'TEST','');
/*!40000 ALTER TABLE `crowd` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `goip`
--

DROP TABLE IF EXISTS `goip`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `goip` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(64) NOT NULL default '',
  `provider` int(11) NOT NULL,
  `host` varchar(50) NOT NULL default '',
  `port` int(11) NOT NULL default '0',
  `password` varchar(64) NOT NULL default '',
  `alive` tinyint(1) NOT NULL default '0',
  `num`  varchar(30) NOT NULL default '',
  `signal` int(11) default NULL,
  `gsm_status` VARCHAR(30) not NULL,
  `voip_status` VARCHAR(30) not NULL,
  `voip_state` VARCHAR(30) not NULL,
  `bal` FLOAT default '-1',
  `CELLINFO` varchar(160) NOT NULL,
  `CGATT` varchar(32) NOT NULL,
  `BCCH` varchar(160) NOT NULL,
  `bal_time` datetime default '0000-00-00 00:00:00',
  `keepalive_time` datetime default '0000-00-00 00:00:00',
  `gsm_login_time` datetime default '0000-00-00 00:00:00',
  `gsm_login_time_t` int(11) NOT NULL default '0',
  `keepalive_time_t` int(11) NOT NULL default '0',
  `remain_time` int(11) NOT NULL default '-1',
  `imei` varchar(15) default NULL,
  `imsi` varchar(32) default NULL,
  `iccid` varchar(32) default NULL,
  `last_call_record_id` int(11) NOT NULL,
  `remain_count` int(11) NOT NULL default '-1',
  `count_limit` int(11) NOT NULL default '-1',
  `remain_count_d` int(11) NOT NULL default '-1',
  `count_limit_d` int(11) NOT NULL default '-1',
  `group_id` int(11) NOT NULL default '0',
  `report_mail` varchar(64) NOT NULL,
  `fwd_mail_enable` tinyint(1) NOT NULL default '0',
  `report_http` varchar(64) NOT NULL,
  `fwd_http_enable` tinyint(1) NOT NULL default '0',
  `carrier` varchar(32) NOT NULL,
  `auto_num_c` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `password` (`password`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `goip`
--

LOCK TABLES `goip` WRITE;
/*!40000 ALTER TABLE `goip` DISABLE KEYS */;
/*!40000 ALTER TABLE `goip` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `goip_group`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE IF NOT EXISTS `goip_group` (
  `id` int(11) NOT NULL auto_increment,
  `group_name` varchar(32) character set utf8 NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`group_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `groups` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(20) NOT NULL default '',
  `info` varchar(100) default NULL,
  `crowdid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `crowdid` (`crowdid`),
  CONSTRAINT `groups_ibfk_1` FOREIGN KEY (`crowdid`) REFERENCES `crowd` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` VALUES (1,'TEST','',1);
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message`
--

DROP TABLE IF EXISTS `message`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `message` (
  `id` int(11) NOT NULL auto_increment,
  `crontime` int(10) unsigned NOT NULL default '0',
  `time` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `userid` int(11) NOT NULL default '0',
  `cronid` int(11) NOT NULL default '0',
  `msg` text NOT NULL,
  `type` int(1) NOT NULL default '0',
  `receiverid` text,
  `receiverid1` text,
  `receiverid2` text,
  `groupid` text,
  `groupid1` text,
  `groupid2` text,
  `recv` tinyint(1) NOT NULL default '0',
  `recv1` tinyint(1) NOT NULL default '0',
  `recv2` tinyint(1) NOT NULL default '0',
  `over` int(1) NOT NULL default '0',
  `stoptime` INT(10) UNSIGNED NULL DEFAULT '0',
  `tel` text,
  `prov` VARCHAR( 30 ) NULL,
  `goipid` int(11) default '0', 
  `uid` VARCHAR( 64 ) NULL,
  `msgid` INT(10) UNSIGNED NULL DEFAULT '0',
  `total` int(11) NOT NULL default '0',
  `card_id` int(11) NOT NULL,
  `card` varchar(64) NOT NULL,
  `resend` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `crontime` (`crontime`),
  KEY `type` (`type`),
  KEY `over` (`over`),
  KEY `prov` (`prov`),
  KEY `time` (`time`),
  KEY `stoptime` (`stoptime`),
  KEY `card_id` (`card_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `message`
--

LOCK TABLES `message` WRITE;
/*!40000 ALTER TABLE `message` DISABLE KEYS */;
/*!40000 ALTER TABLE `message` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prov`
--

DROP TABLE IF EXISTS `prov`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `prov` (
  `id` int(11) NOT NULL auto_increment,
  `prov` varchar(30) default NULL,
  `inter` varchar(10) character set ascii default NULL,
  `local` varchar(10) character set ascii default NULL,
  `recharge_ok_r` varchar(64) NOT NULL,
  `auto_num_ussd` varchar(20) NOT NULL,
  `num_prefix` varchar(20) NOT NULL,
  `num_postfix` varchar(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `prov`
--

LOCK TABLES `prov` WRITE;
/*!40000 ALTER TABLE `prov` DISABLE KEYS */;
INSERT INTO `prov` VALUES (1,'test','','','','','',''),(2,'','','','','','',''),(3,'','','','','','',''),(4,'','','','','','',''),(5,'','','','','','',''),(6,'','','','','','',''),(7,'','','','','','',''),(8,'','','','','','',''),(9,'','','','','','',''),(10,'','','','','','','');
/*!40000 ALTER TABLE `prov` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `receiver`
--

DROP TABLE IF EXISTS `receiver`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `receiver` (
  `id` int(11) NOT NULL auto_increment,
  `no` varchar(20) NOT NULL default '',
  `name` varchar(20) NOT NULL default '',
  `name_l` varchar(20) NOT NULL,
  `ename_f` varchar(30) NOT NULL,
  `ename_l` varchar(30) NOT NULL,
  `gender` varchar(1) NOT NULL,
  `info` varchar(100) NOT NULL default '',
  `tel` varchar(20) default NULL,
  `hometel` varchar(20) NOT NULL,
  `officetel` varchar(20) NOT NULL,
  `provider` varchar(20) NOT NULL default '',
  `dead` int(1) NOT NULL,
  `reject` int(1) NOT NULL,
  `name1` varchar(20) NOT NULL default '',
  `tel1` varchar(20) default NULL,
  `provider1` varchar(20) NOT NULL default '',
  `name2` varchar(20) NOT NULL default '',
  `tel2` varchar(20) default NULL,
  `provider2` varchar(20) NOT NULL default '',
  `upload_time` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `receiver`
--

LOCK TABLES `receiver` WRITE;
/*!40000 ALTER TABLE `receiver` DISABLE KEYS */;
/*!40000 ALTER TABLE `receiver` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recvgroup`
--

DROP TABLE IF EXISTS `recvgroup`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `recvgroup` (
  `id` int(11) NOT NULL auto_increment,
  `groupsid` int(11) NOT NULL default '0',
  `recvid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `groupsid` (`groupsid`),
  KEY `recvid` (`recvid`),
  CONSTRAINT `recvgroup_ibfk_1` FOREIGN KEY (`groupsid`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `recvgroup_ibfk_2` FOREIGN KEY (`recvid`) REFERENCES `receiver` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `recvgroup`
--

LOCK TABLES `recvgroup` WRITE;
/*!40000 ALTER TABLE `recvgroup` DISABLE KEYS */;
/*!40000 ALTER TABLE `recvgroup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `refcrowd`
--

DROP TABLE IF EXISTS `refcrowd`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `refcrowd` (
  `id` int(11) NOT NULL auto_increment,
  `userid` int(11) NOT NULL default '0',
  `crowdid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `userid` (`userid`),
  KEY `crowdid` (`crowdid`),
  CONSTRAINT `refcrowd_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `refcrowd_ibfk_2` FOREIGN KEY (`crowdid`) REFERENCES `crowd` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `refcrowd`
--

LOCK TABLES `refcrowd` WRITE;
/*!40000 ALTER TABLE `refcrowd` DISABLE KEYS */;
/*!40000 ALTER TABLE `refcrowd` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `refgroup`
--

DROP TABLE IF EXISTS `refgroup`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `refgroup` (
  `id` int(11) NOT NULL auto_increment,
  `groupsid` int(11) NOT NULL default '0',
  `userid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `groupsid` (`groupsid`),
  KEY `userid` (`userid`),
  CONSTRAINT `refgroup_ibfk_1` FOREIGN KEY (`groupsid`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `refgroup_ibfk_2` FOREIGN KEY (`userid`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `refgroup`
--

LOCK TABLES `refgroup` WRITE;
/*!40000 ALTER TABLE `refgroup` DISABLE KEYS */;
/*!40000 ALTER TABLE `refgroup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sends`
--

DROP TABLE IF EXISTS `sends`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `sends` (
  `id` int(11) NOT NULL auto_increment,
  `time` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `userid` int(11) NOT NULL default '0',
  `messageid` int(11) NOT NULL default '0',
  `goipid` int(11) NOT NULL default '0',
  `provider` varchar(20) NOT NULL default '',
  `telnum` varchar(20) NOT NULL default '',
  `recvlev` int(1) NOT NULL default '0',
  `recvid` int(11) NOT NULL default '0',
  `over` tinyint(1) default '0',
  `error_no` int(11) default NULL,
  `msg` text NOT NULL,
  `received` tinyint(1) NOT NULL,
  `sms_no` int(11) NOT NULL default '-1',
  `total` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `messageid` (`messageid`),
  KEY `over` (`over`),
  KEY `goipid` (`goipid`),
  KEY `recvid` (`recvid`),
  KEY `sms_no` (`sms_no`),
  KEY `received` (`received`),
  CONSTRAINT `sends_ibfk_1` FOREIGN KEY (`messageid`) REFERENCES `message` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `sends`
--

LOCK TABLES `sends` WRITE;
/*!40000 ALTER TABLE `sends` DISABLE KEYS */;
/*!40000 ALTER TABLE `sends` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system`
--

DROP TABLE IF EXISTS `system`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `system` (
  `maxword` int(11) NOT NULL default '70',
  `sysname` varchar(20) NOT NULL default 'goipsms',
  `lan` int(1) NOT NULL default '3',
  `version` int(11) NOT NULL default '114',
  `smtp_user` varchar(64) NOT NULL,
  `smtp_pass` varchar(64) NOT NULL,
  `smtp_mail` varchar(64) NOT NULL,
  `smtp_server` varchar(64) NOT NULL,
  `smtp_port` int(11) NOT NULL default '25',
  `email_report_gsm_logout_enable` tinyint(1) NOT NULL default '0',
  `email_report_gsm_logout_time_limit` int(11) NOT NULL default '5',
  `email_report_reg_logout_enable` tinyint(1) NOT NULL default '0',
  `email_report_reg_logout_time_limit` int(11) NOT NULL default '5',
  `report_mail` varchar(64) NOT NULL,
  `email_report_remain_timeout_enable` tinyint(1) NOT NULL default '0',
  `send_page_jump_enable` tinyint(1) NOT NULL default '0',
  `endless_send_enable` tinyint(1) NOT NULL default '0',
  `re_ask_timer` int(11) NOT NULL default '3',
  `total` int(11) NOT NULL default '0',
  `this_day` int(11) NOT NULL default '0',
  `email_forward_sms_enable` tinyint(1) NOT NULL default '0',
  `email_remain_count_enable` tinyint(1) NOT NULL default '0',
  `email_remain_count_d_enable` tinyint(1) NOT NULL default '0',
  `session_time` int(11) NOT NULL default '24',
  `disable_status` tinyint(1) NOT NULL default '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `system`
--

LOCK TABLES `system` WRITE;
/*!40000 ALTER TABLE `system` DISABLE KEYS */;
INSERT INTO `system` VALUES (70,'goipsms',3,119,'','','','',25,0,5,0,5,'',0,1,0,3,0,0,0,0,0,24,0);

/*!40000 ALTER TABLE `system` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `user` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(20) character set utf8 NOT NULL default '',
  `password` varchar(50) NOT NULL default '',
  `permissions` int(1) NOT NULL default '0',
  `info` text character set utf8,
  `msg1` varchar(320) character set utf8 NOT NULL default '',
  `msg2` varchar(320) character set utf8 NOT NULL default '',
  `msg3` varchar(320) character set utf8 NOT NULL default '',
  `msg4` varchar(320) character set utf8 NOT NULL default '',
  `msg5` varchar(320) character set utf8 NOT NULL default '',
  `msg6` varchar(320) character set utf8 NOT NULL default '',
  `msg7` varchar(320) character set utf8 NOT NULL default '',
  `msg8` varchar(320) character set utf8 NOT NULL default '',
  `msg9` varchar(320) character set utf8 NOT NULL default '',
  `msg10` varchar(320) character set utf8 NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'root','63a9f0ea7bb98050796b649e85481845',0,'Super Adminstrator','','','','','','','','','','');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- 表的结构 `receive`
--

DROP TABLE IF EXISTS `receive`;
CREATE TABLE IF NOT EXISTS `receive` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `srcnum` varchar(30) NOT NULL default '',
  `provid` int(10) unsigned NOT NULL default '0',
  `msg` text NOT NULL,
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `goipid` int(11) NOT NULL default '0',
  `goipname` varchar(30) NOT NULL default '',
  `srcid` int(11) NOT NULL default '0',
  `srcname` varchar(30) NOT NULL default '',
  `srclevel` int(1) NOT NULL default '0',
  `status` int(1) NOT NULL default '0',
  `smscnum` varchar(32) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `record`;
CREATE TABLE IF NOT EXISTS `record` (
  `id` int(10) unsigned NOT NULL auto_increment,                                                                  
  `goipid` int(10) unsigned NOT NULL default '0',
  `dir` int(1) NOT NULL default '0',
  `num` varchar(64) NOT NULL default '',
  `time` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `expiry` int(11) default '-1',
  `HANGUP_CAUSE` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `expiry` (`expiry`),
  KEY `goipid` (`goipid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


DROP TABLE IF EXISTS `USSD`;
CREATE TABLE IF NOT EXISTS `USSD` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `TERMID` varchar(64) NOT NULL default '',
  `USSD_MSG` varchar(255) NOT NULL default '',
  `USSD_RETURN` varchar(255) NOT NULL default '',
  `ERROR_MSG` varchar(64) NOT NULL default '',
  `INSERTTIME` timestamp NOT NULL default '0000-00-00 00:00:00' on update CURRENT_TIMESTAMP,
  `type` int(11) NOT NULL default '0',
  `card` varchar(64) NOT NULL,
  `recharge_ok` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


DROP TABLE IF EXISTS `auto`;
CREATE TABLE IF NOT EXISTS `auto` (
  `auto_reply` tinyint(1) default '0',
  `reply_num_except` text NOT NULL,
  `reply_msg` text NOT NULL,
  `auto_send` tinyint(1) default '0',
  `auto_send_num` varchar(64) NOT NULL default '',
  `auto_send_msg` text NOT NULL,
  `auto_send_timeout` int(11) NOT NULL default '0',
  `all_send_num` varchar(64) NOT NULL default '',
  `all_send_msg` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `auto` WRITE;
/*!40000 ALTER TABLE `auto` DISABLE KEYS */;
INSERT INTO `auto` (`auto_reply`, `reply_num_except`, `reply_msg`, `auto_send`, `auto_send_num`, `auto_send_msg`, `auto_send_timeout`, `all_send_num`, `all_send_msg`) VALUES(0, '', '', 0, '', '', 15, '', '');
/*!40000 ALTER TABLE `auto` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `auto_ussd`;
CREATE TABLE IF NOT EXISTS `auto_ussd` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(32) character set utf8 NOT NULL,
  `type` int(11) NOT NULL,
  `auto_sms_num` varchar(32) NOT NULL,
  `auto_sms_msg` varchar(320) character set utf8 NOT NULL,
  `auto_ussd` varchar(160) character set utf8 NOT NULL,
  `prov_id` int(11) NOT NULL default '0',
  `goip_id` int(11) NOT NULL default '0',
  `crontime` int(11) NOT NULL,
  `bal_sms_r` varchar(160) character set utf8 NOT NULL,
  `bal_ussd_r` varchar(160) character set utf8 NOT NULL,
  `bal_limit` float NOT NULL,
  `recharge_ussd` varchar(160) character set utf8 NOT NULL,
  `last_time` timestamp NOT NULL default '0000-00-00 00:00:00',
  `next_time` int(10) unsigned NOT NULL default '0',
  `send_sms` varchar(128) NOT NULL,
  `send_email` varchar(32) NOT NULL,
  `recharge_type` int(1) NOT NULL default '0',
  `recharge_ussd1` varchar(160) NOT NULL,
  `recharge_ussd1_goip` int(11) NOT NULL,
  `recharge_ok_r` varchar(64) character set utf8 NOT NULL,
  `recharge_ok_r2` varchar(64) character set utf8 NOT NULL,
  `bal_ussd_zero_match_char` varchar(160) character set utf8 NOT NULL,
  `bal_sms_zero_match_char` varchar(160) character set utf8 NOT NULL,
  `disable_if_low_bal` tinyint(1) NOT NULL,
  `group_id` int(11) NOT NULL default '0',
  `auto_disconnect_after_bal` tinyint(1) NOT NULL default '0',
  `disable_callout_when_bal` tinyint(1) NOT NULL default '0',
  `ussd2` varchar(160) NOT NULL,
  `ussd2_ok_match` varchar(160) NOT NULL,
  `ussd22` varchar(160) NOT NULL,
  `ussd22_ok_match` varchar(160) NOT NULL,
  `send_mail2` char(32) NOT NULL,
  `disable_if_ussd2_undone` tinyint(1) NOT NULL default '0',
  `recharge_limit` int(11) NOT NULL default '0',
  `send_sms2` varchar(32) NOT NULL,
  `recharge_sms_num` varchar(32) character set utf8 NOT NULL,
  `recharge_sms_msg` varchar(160) character set utf8 NOT NULL,
  `recharge_sms_ok_num` varchar(32) character set utf8 NOT NULL,
  `auto_ussd_step2` varchar(160) character set utf8 NOT NULL,
  `auto_ussd_step2_start_r` varchar(160) character set utf8 NOT NULL,
  `sms_report_goip` int(11) NOT NULL,
  `bal_delay` int(11) NOT NULL default '0',
  `re_step2_enable` tinyint(1) NOT NULL,
  `re_step2_cmd` varchar(64) NOT NULL,
  `re_step2_ok_r` varchar(64) character set utf8 NOT NULL,
  `auto_reset_remain_enable` tinyint(1) NOT NULL default '0',
  `auto_ussd_step3` varchar(160) character set utf8 NOT NULL,
  `auto_ussd_step3_start_r` varchar(160) character set utf8 NOT NULL,
  `auto_ussd_step4` varchar(160) character set utf8 NOT NULL,
  `auto_ussd_step4_start_r` varchar(160) character set utf8 NOT NULL,
  `recharge_con_type` int(11) NOT NULL default '0',
  `fixed_time` varchar(10) NOT NULL,
  `remain_limit` int(11) NOT NULL default '0',
  `remain_set` int(11) NOT NULL default '0',
  `fixed_next_time` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `prov_id` (`prov_id`,`goip_id`),
  KEY `recharge_ussd1_goip` (`recharge_ussd1_goip`),
  KEY `group_id` (`group_id`),
  KEY `sms_report_goip` (`sms_report_goip`),
  KEY `recharge_con_type` (`recharge_con_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `recharge_card`;
CREATE TABLE IF NOT EXISTS `recharge_card` (
  `id` int(11) NOT NULL auto_increment,
  `card` varchar(64) NOT NULL,
  `prov_id` int(11) NOT NULL,
  `used` tinyint(1) NOT NULL default '0',
  `use_time` datetime default '0000-00-00 00:00:00',
  `goipid` int(11) default '0',
  PRIMARY KEY  (`id`),
  KEY `prov_id` (`prov_id`),
  KEY `goipid` (`goipid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `imei_db`;
CREATE TABLE IF NOT EXISTS `imei_db` (
  `id` int(11) NOT NULL auto_increment,
  `imei` varchar(15) NOT NULL,
  `goipid` int(11) NOT NULL,
  `goipname` varchar(64) NOT NULL,
  `used` int(1) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `used` (`used`),
  KEY `goipid` (`goipid`),
  KEY `imei` (`imei`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2009-07-08  3:41:46
