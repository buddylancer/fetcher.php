-- MySQL dump 10.13  Distrib 5.1.44, for Win32 (ia32)
--
-- Host: localhost    Database: alerting
-- ------------------------------------------------------
-- Server version	5.1.44-community

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `as_of_time`
--

DROP TABLE IF EXISTS `as_of_time`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `as_of_time` (
  `i_Id` int(11) DEFAULT '1',
  `d_Time` datetime DEFAULT NULL,
  UNIQUE KEY `i_Id` (`i_Id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sources`
--

DROP TABLE IF EXISTS `sources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sources` (
  `i_SourceId` int(11) NOT NULL AUTO_INCREMENT,
  `s_SourceName` varchar(250) NOT NULL DEFAULT '',
  `b_SourceActive` int(11) DEFAULT NULL,
  `b_SourceFetched` int(11) DEFAULT NULL,
  `s_External` varchar(1024) DEFAULT NULL,
  `s_Feed` varchar(1024) DEFAULT NULL,
  UNIQUE KEY `i_SourceId` (`i_SourceId`),
  KEY `IX_SourceName` (`s_SourceName`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `s_CatId` varchar(250) NOT NULL,
  `s_Name` varchar(250) NOT NULL,
  `s_Filter` varchar(250) DEFAULT NULL,
  `i_Counter` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `s_CatId` (`s_CatId`),
  KEY `IX_Name` (`s_Name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `items` (
  `i_ItemId` int(11) NOT NULL AUTO_INCREMENT,
  `i_SourceLink` int(11) NOT NULL DEFAULT '0',
  `d_Date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `s_Link` varchar(256) DEFAULT NULL,
  `s_Title` varchar(1024) NOT NULL,
  `s_FullTitle` varchar(1024) NOT NULL,
  `s_Url` varchar(255) DEFAULT NULL,
  `s_Category` varchar(1024) DEFAULT NULL,
  `s_Creator` varchar(60) DEFAULT NULL,
  `s_Custom1` varchar(60) DEFAULT NULL,
  `s_Custom2` varchar(60) DEFAULT NULL,
  `t_Description` text DEFAULT NULL,
  `t_FullDescription` text DEFAULT NULL,
  UNIQUE KEY `i_ItemId` (`i_ItemId`),
  KEY `IX_Link` (`s_Link`(256)),
  KEY `IX_Date` (`d_Date`),
  KEY `IX_SourceLink` (`i_SourceLink`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2020-03-28 17:40:52
