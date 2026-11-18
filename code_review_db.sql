CREATE DATABASE  IF NOT EXISTS `code_review_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `code_review_db`;
-- MySQL dump 10.13  Distrib 8.0.45, for Win64 (x86_64)
--
-- Host: localhost    Database: code_review_db
-- ------------------------------------------------------
-- Server version	8.4.10

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `codeissue`
--

DROP TABLE IF EXISTS `codeissue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `codeissue` (
  `IssueID` int NOT NULL AUTO_INCREMENT,
  `SubmissionID` int NOT NULL,
  `IssueType` varchar(255) NOT NULL,
  `LineNumber` int NOT NULL,
  PRIMARY KEY (`IssueID`),
  KEY `SubmissionID` (`SubmissionID`),
  CONSTRAINT `codeissue_ibfk_1` FOREIGN KEY (`SubmissionID`) REFERENCES `codesubmission` (`SubmissionID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `codeissue`
--

LOCK TABLES `codeissue` WRITE;
/*!40000 ALTER TABLE `codeissue` DISABLE KEYS */;
INSERT INTO `codeissue` VALUES (1,1,'Major - Logic Error (PHPStan): Variable $unknownVariable might not be defined.',3),(2,1,'Major - Logic Bug (PHPStan): Assignment (=) used inside if() condition instead of comparison (== or ===).',4),(3,1,'Minor - Coding Standard Violation (PHPCS): Tab indentation found; use 4 spaces (PSR-12).',2),(4,1,'Minor - Coding Standard Violation (PHPCS): Tab indentation found; use 4 spaces (PSR-12).',3),(5,1,'Minor - Coding Standard Violation (PHPCS): Tab indentation found; use 4 spaces (PSR-12).',4),(6,1,'Minor - Coding Standard Violation (PHPCS): Tab indentation found; use 4 spaces (PSR-12).',5),(7,1,'Minor - Coding Standard Violation (PHPCS): Tab indentation found; use 4 spaces (PSR-12).',6),(8,2,'Major - Syntax Error (PHPStan): [Tue Nov 17 14:21:56.262390 2026] [mpm_winnt:crit] [pid 1780:tid 500] AH02965: Child: Unable to retrieve my generation from the parent',1),(9,2,'Major - Logic Error (PHPStan): Variable $uninitializedUser might not be defined.',3),(10,2,'Major - Logic Bug (PHPStan): Assignment (=) used inside if() condition instead of comparison (== or ===).',4),(11,2,'Minor - Coding Standard Violation (PHPCS): Tab indentation found; use 4 spaces (PSR-12).',2),(12,2,'Minor - Coding Standard Violation (PHPCS): Tab indentation found; use 4 spaces (PSR-12).',3),(13,2,'Minor - Coding Standard Violation (PHPCS): Tab indentation found; use 4 spaces (PSR-12).',4),(14,2,'Minor - Coding Standard Violation (PHPCS): Tab indentation found; use 4 spaces (PSR-12).',5),(15,2,'Minor - Coding Standard Violation (PHPCS): Tab indentation found; use 4 spaces (PSR-12).',6),(16,3,'Major - Syntax Error (PHPStan): [Tue Nov 17 21:49:13.512275 2026] [mpm_winnt:crit] [pid 9212:tid 476] AH02965: Child: Unable to retrieve my generation from the parent',1),(17,3,'Major - Logic Error (PHPStan): Variable $missingVariable might not be defined.',3),(18,3,'Major - Logic Bug (PHPStan): Assignment (=) used inside if() condition instead of comparison (== or ===).',4),(19,3,'Minor - Coding Standard Violation (PHPCS): Tab indentation found; use 4 spaces (PSR-12).',2),(20,3,'Minor - Coding Standard Violation (PHPCS): Tab indentation found; use 4 spaces (PSR-12).',3),(21,3,'Minor - Coding Standard Violation (PHPCS): Tab indentation found; use 4 spaces (PSR-12).',4),(22,3,'Minor - Coding Standard Violation (PHPCS): Tab indentation found; use 4 spaces (PSR-12).',5),(23,3,'Minor - Coding Standard Violation (PHPCS): Tab indentation found; use 4 spaces (PSR-12).',6),(24,4,'Major - Syntax Error (PHPStan): [Tue Nov 17 22:09:20.254908 2026] [mpm_winnt:crit] [pid 22444:tid 452] AH02965: Child: Unable to retrieve my generation from the parent',1),(25,4,'Major - Logic Error (PHPStan): Variable $missingVariable might not be defined.',3),(26,4,'Major - Logic Bug (PHPStan): Assignment (=) used inside if() condition instead of comparison (== or ===).',4),(27,4,'Minor - Coding Standard Violation (PHPCS): Tab indentation found; use 4 spaces (PSR-12).',2),(28,4,'Minor - Coding Standard Violation (PHPCS): Tab indentation found; use 4 spaces (PSR-12).',3),(29,4,'Minor - Coding Standard Violation (PHPCS): Tab indentation found; use 4 spaces (PSR-12).',4),(30,4,'Minor - Coding Standard Violation (PHPCS): Tab indentation found; use 4 spaces (PSR-12).',5),(31,4,'Minor - Coding Standard Violation (PHPCS): Tab indentation found; use 4 spaces (PSR-12).',6);
/*!40000 ALTER TABLE `codeissue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `codesubmission`
--

DROP TABLE IF EXISTS `codesubmission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `codesubmission` (
  `SubmissionID` int NOT NULL AUTO_INCREMENT,
  `UserID` int NOT NULL,
  `ProjectName` varchar(150) NOT NULL,
  `PhpCode` mediumtext NOT NULL,
  `SubmitDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`SubmissionID`),
  KEY `UserID` (`UserID`),
  CONSTRAINT `codesubmission_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `codesubmission`
--

LOCK TABLES `codesubmission` WRITE;
/*!40000 ALTER TABLE `codesubmission` DISABLE KEYS */;
INSERT INTO `codesubmission` VALUES (1,3,'Sample log in Check','<?php\r\n	$user = \"Sanjoe\";\r\n	echo $unknownVariable;\r\n	if ($user = \"Admin\") {\r\n		echo \"Welcome\";\r\n	}','2026-11-17 13:53:58'),(2,5,'Sample Login Verification Script','<?php\r\n	$accountStatus = \"Pending\";\r\n	echo $uninitializedUser;\r\n	if ($accountStatus = \"Approved\") {\r\n		echo \"Access Granted\";\r\n	}\r\n','2026-11-17 14:21:55'),(3,4,'User Session Validation Module','<?php\r\n	$status = \"Pending\";\r\n	echo $missingVariable;\r\n	if ($status = \"Approved\") {\r\n		echo \"Access Granted\";\r\n	}\r\n','2026-11-17 21:49:13'),(4,6,'User Session Validation Module','<?php\r\n	$status = \"Pending\";\r\n	echo $missingVariable;\r\n	if ($status = \"Approved\") {\r\n		echo \"Access Granted\";\r\n	}\r\n','2026-11-17 22:09:19');
/*!40000 ALTER TABLE `codesubmission` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qualityscore`
--

DROP TABLE IF EXISTS `qualityscore`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `qualityscore` (
  `ScoreID` int NOT NULL AUTO_INCREMENT,
  `SubmissionID` int NOT NULL,
  `OverallScore` int NOT NULL,
  `AnalyzeDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ScoreID`),
  UNIQUE KEY `SubmissionID` (`SubmissionID`),
  CONSTRAINT `qualityscore_ibfk_1` FOREIGN KEY (`SubmissionID`) REFERENCES `codesubmission` (`SubmissionID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qualityscore`
--

LOCK TABLES `qualityscore` WRITE;
/*!40000 ALTER TABLE `qualityscore` DISABLE KEYS */;
INSERT INTO `qualityscore` VALUES (1,1,70,'2026-11-17 13:53:58'),(2,2,60,'2026-11-17 14:21:56'),(3,3,60,'2026-11-17 21:49:13'),(4,4,60,'2026-11-17 22:09:20');
/*!40000 ALTER TABLE `qualityscore` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviewreport`
--

DROP TABLE IF EXISTS `reviewreport`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reviewreport` (
  `ReportID` int NOT NULL AUTO_INCREMENT,
  `SubmissionID` int NOT NULL,
  `ScoreID` int NOT NULL,
  `GeneratedDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ReportID`),
  UNIQUE KEY `SubmissionID` (`SubmissionID`),
  UNIQUE KEY `ScoreID` (`ScoreID`),
  CONSTRAINT `reviewreport_ibfk_1` FOREIGN KEY (`SubmissionID`) REFERENCES `codesubmission` (`SubmissionID`) ON DELETE CASCADE,
  CONSTRAINT `reviewreport_ibfk_2` FOREIGN KEY (`ScoreID`) REFERENCES `qualityscore` (`ScoreID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviewreport`
--

LOCK TABLES `reviewreport` WRITE;
/*!40000 ALTER TABLE `reviewreport` DISABLE KEYS */;
INSERT INTO `reviewreport` VALUES (1,1,1,'2026-11-17 13:53:58'),(2,2,2,'2026-11-17 14:21:56'),(3,3,3,'2026-11-17 21:49:13'),(4,4,4,'2026-11-17 22:09:20');
/*!40000 ALTER TABLE `reviewreport` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `UserID` int NOT NULL AUTO_INCREMENT,
  `Username` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `AccountStatus` varchar(20) NOT NULL DEFAULT 'Pending',
  `VerifiedBy` varchar(100) DEFAULT NULL,
  `VerifiedDate` datetime DEFAULT NULL,
  PRIMARY KEY (`UserID`),
  UNIQUE KEY `Username` (`Username`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'testuser123','$2y$10$uJeWks10eRvU25cR.D.2Tujg0EPGTtnb6WiPSOTvRd1Rrj6edeVVG','Rejected','admin','2026-11-17 14:04:20'),(2,'admin','$2y$10$bXSQ17P3RAVfDOtdTYHk9OalQuqwpn54RRV90Xk8xtM.73gRQNd7C','Rejected','admin','2026-11-17 14:04:22'),(3,'testuser','$2y$10$RYphd13YmCppe1BlOdGyw.KhPwtSuVn1GWBz7R7YGNByU7ks.7BfK','Approved','admin','2026-11-17 13:43:13'),(4,'sanjoe','$2y$10$xwZHBVWcxAsOzbEAxUqwL.ijXwJZ8OqdG/KqqQ2X.RIKlotXWZZiu','Approved','admin','2026-11-17 14:12:23'),(5,'prinz','$2y$10$boRwsTpUJZM166I2U/IDe.N8MLSTlRM3s0ExX2E.hw3D2SYmAtvvG','Approved','admin','2026-11-17 14:18:48'),(6,'jessica','$2y$10$bRzGZjhVMxp0pEFeG6PYw.rbx3v/xe7ETj0Mr3JeKNxSP.RexvE9y','Approved','admin','2026-11-17 22:07:54');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-18 13:29:20
