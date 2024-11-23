/*
SQLyog Community v13.1.7 (64 bit)
MySQL - 10.4.32-MariaDB : Database - library
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`library` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `library`;

/*Table structure for table `audit_logs` */

DROP TABLE IF EXISTS `audit_logs`;

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `audit_logs` */

insert  into `audit_logs`(`id`,`userid`,`action`,`message`,`timestamp`) values 
(1,20,'lock_account','Account locked','2024-10-18 03:05:41'),
(2,20,'lock_account','Account locked','2024-10-18 03:07:30'),
(3,20,'lock_account','Account locked','2024-10-18 03:07:30'),
(4,20,'lock_account','Account locked','2024-10-18 03:07:31'),
(5,20,'unlock_account','Account unlocked','2024-10-18 03:08:46'),
(6,20,'unlock_account','Account unlocked','2024-10-18 03:08:59'),
(7,22,'lock_account','Account locked','2024-10-18 10:46:20');

/*Table structure for table `authors` */

DROP TABLE IF EXISTS `authors`;

CREATE TABLE `authors` (
  `authorid` int(9) NOT NULL AUTO_INCREMENT,
  `name` char(255) NOT NULL,
  PRIMARY KEY (`authorid`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `authors` */

insert  into `authors`(`authorid`,`name`) values 
(95,'Author Name'),
(96,'Author Names'),
(97,'Arthur'),
(98,'J.K. Rowling'),
(99,'Kisses'),
(100,'Prince panget');

/*Table structure for table `books` */

DROP TABLE IF EXISTS `books`;

CREATE TABLE `books` (
  `bookid` int(9) NOT NULL AUTO_INCREMENT,
  `title` char(255) NOT NULL,
  `authorid` int(9) NOT NULL,
  PRIMARY KEY (`bookid`),
  KEY `authorid` (`authorid`),
  CONSTRAINT `books_ibfk_1` FOREIGN KEY (`authorid`) REFERENCES `authors` (`authorid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=226 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `books` */

insert  into `books`(`bookid`,`title`,`authorid`) values 
(221,'New Book Title',95),
(222,'New Book Titles',96),
(223,'New Book Titles',97),
(224,'New Book Titls',97),
(225,'To the Moon',99);

/*Table structure for table `books_author` */

DROP TABLE IF EXISTS `books_author`;

CREATE TABLE `books_author` (
  `bookid` int(9) NOT NULL,
  `authorid` int(9) NOT NULL,
  `collectionid` int(9) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`collectionid`),
  KEY `books_author_ibfk_1` (`bookid`),
  KEY `books_author_ibfk_2` (`authorid`),
  KEY `collectionid` (`collectionid`),
  CONSTRAINT `books_author_ibfk_1` FOREIGN KEY (`bookid`) REFERENCES `books` (`bookid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `books_author_ibfk_2` FOREIGN KEY (`authorid`) REFERENCES `authors` (`authorid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=185 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `books_author` */

/*Table structure for table `used_tokens` */

DROP TABLE IF EXISTS `used_tokens`;

CREATE TABLE `used_tokens` (
  `token` varchar(512) NOT NULL,
  `userid` int(9) DEFAULT NULL,
  `expires_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`token`),
  KEY `userid` (`userid`),
  CONSTRAINT `used_tokens_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `used_tokens` */

insert  into `used_tokens`(`token`,`userid`,`expires_at`,`used`,`created_at`) values 
('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbGlicmFyeS5vcmciLCJhdWQiOiJodHRwOi8vbGlicmFyeS5jb20iLCJpYXQiOjE3Mjg5MjExMzIsImV4cCI6MTcyODkyMjkzMiwiZGF0YSI6eyJ1c2VyaWQiOjEwfX0.L4Yj83KWtrfknHAaPf7CEBnzMH_TGRf7uktgYThTIF4',10,'2024-10-14 23:52:12',0,'2024-10-14 23:40:08'),
('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbGlicmFyeS5vcmciLCJhdWQiOiJodHRwOi8vbGlicmFyeS5jb20iLCJpYXQiOjE3Mjg5MjgyMDAsImV4cCI6MTcyODkzMDAwMCwiZGF0YSI6eyJ1c2VyaWQiOjE2fX0.CZR2KDUpmwH8aZjBePOR9jbLCm14PM6RVCjzMo8tNBg',16,'2024-10-15 01:50:00',0,'2024-10-15 01:47:30'),
('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbGlicmFyeS5vcmciLCJhdWQiOiJodHRwOi8vbGlicmFyeS5jb20iLCJpYXQiOjE3Mjg5Mjk0MjYsImV4cCI6MTcyODkzMTIyNiwiZGF0YSI6eyJ1c2VyaWQiOjEzfX0.O5a79JPuaJnt3i6PZWOCNfa5IwfFuO2KqR7hAyca0lo',13,'2024-10-15 02:10:26',0,'2024-10-15 01:49:13'),
('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbGlicmFyeS5vcmciLCJhdWQiOiJodHRwOi8vbGlicmFyeS5jb20iLCJpYXQiOjE3Mjg5MjUxMzIsImV4cCI6MTcyODkyNjkzMiwiZGF0YSI6eyJ1c2VyaWQiOjE0fX0.PxeoZqP88Q2LIoJNHhIhV5BUae44957lid4kvFY79fQ',14,'2024-10-15 00:58:52',0,'2024-10-15 00:28:52'),
('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbGlicmFyeS5vcmciLCJhdWQiOiJodHRwOi8vbGlicmFyeS5jb20iLCJpYXQiOjE3Mjk1NjM3NDksImV4cCI6MTcyOTU2NTU0OSwiZGF0YSI6eyJ1c2VyaWQiOjI0fX0.bPvZHvnewUl9CN5hxl5ves-t-rfCJLwkFOVF-8VeYFI',24,'2024-10-22 10:22:29',0,'2024-10-22 10:17:49'),
('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbGlicmFyeS5vcmciLCJhdWQiOiJodHRwOi8vbGlicmFyeS5jb20iLCJpYXQiOjE3MjkwMDEzOTgsImV4cCI6MTcyOTAwMzE5OCwiZGF0YSI6eyJ1c2VyaWQiOjE1fX0.C2iiepNK50WRk_yh9KqOxxU-ppXPRTaeBo0z8yRldn8',15,'2024-10-15 22:09:58',0,'2024-10-15 01:03:35'),
('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbGlicmFyeS5vcmciLCJhdWQiOiJodHRwOi8vbGlicmFyeS5jb20iLCJpYXQiOjE3MjkwMTE4ODEsImV4cCI6MTcyOTAxMzY4MSwiZGF0YSI6eyJ1c2VyaWQiOjE3fX0.mJD5BFYtoPlC3AYFQieuKBr8Pw_cFcTZ5dNC5AyrBmY',17,'2024-10-16 01:04:41',0,'2024-10-15 22:19:42'),
('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbGlicmFyeS5vcmciLCJhdWQiOiJodHRwOi8vbGlicmFyeS5jb20iLCJpYXQiOjE3MjkxOTIxMTQsImV4cCI6MTcyOTE5MzkxNCwiZGF0YSI6eyJ1c2VyaWQiOjIwfX0.9TweD3xJscz8WEliwMC4cyNRX85BINp_212PLQXIqTg',20,'2024-10-18 03:08:34',0,'2024-10-18 02:49:49'),
('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbGlicmFyeS5vcmciLCJhdWQiOiJodHRwOi8vbGlicmFyeS5jb20iLCJpYXQiOjE3MjkyMTk0NTEsImV4cCI6MTcyOTIyMTI1MSwiZGF0YSI6eyJ1c2VyaWQiOjIxfX0.Uzy2Al8VPmHz5g2WxMvKPyA7E2fPvsuCLjurqVfEYG4',21,'2024-10-18 10:44:11',0,'2024-10-18 10:42:58'),
('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbGlicmFyeS5vcmciLCJhdWQiOiJodHRwOi8vbGlicmFyeS5jb20iLCJpYXQiOjE3MjkyMTk3ODUsImV4cCI6MTcyOTIyMTU4NSwiZGF0YSI6eyJ1c2VyaWQiOjIyfX0.bRSwf07tXo0jiJUtXgtX6PXlUT7ZNsusoqj1VI4eiV8',22,'2024-10-18 10:49:45',0,'2024-10-18 10:45:53'),
('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbGlicmFyeS5vcmciLCJhdWQiOiJodHRwOi8vbGlicmFyeS5jb20iLCJpYXQiOjE3MjkyMTk4MjgsImV4cCI6MTcyOTIyMTYyOCwiZGF0YSI6eyJ1c2VyaWQiOjIzfX0.ofNQB8zfL7m6AbZ8VjE2JMYTBIx9mvJCspVNfRlzAlE',23,'2024-10-18 10:50:28',0,'2024-10-18 10:50:28');

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `userid` int(9) NOT NULL AUTO_INCREMENT,
  `username` char(255) NOT NULL,
  `password` text NOT NULL,
  `token` text NOT NULL,
  `account_locked` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`userid`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `users` */

insert  into `users`(`userid`,`username`,`password`,`token`,`account_locked`) values 
(10,'updateduser','ea7ae57ab3a49711488ae1f88b76f0a3805f4b93a25952e7137786fa39007ce3','',0),
(13,'kissther','9f735e0df9a1ddc702bf0a1a7b83033f9f7153a00c29de82cedadc9957289b05','',0),
(14,'kisstherrose','9f735e0df9a1ddc702bf0a1a7b83033f9f7153a00c29de82cedadc9957289b05','',0),
(15,'kisstherjane','9f735e0df9a1ddc702bf0a1a7b83033f9f7153a00c29de82cedadc9957289b05','',0),
(16,'kisstherubungen','9f735e0df9a1ddc702bf0a1a7b83033f9f7153a00c29de82cedadc9957289b05','',0),
(17,'jacky','9f735e0df9a1ddc702bf0a1a7b83033f9f7153a00c29de82cedadc9957289b05','',0),
(18,'jacky1','9f735e0df9a1ddc702bf0a1a7b83033f9f7153a00c29de82cedadc9957289b05','',0),
(19,'jacky12','9f735e0df9a1ddc702bf0a1a7b83033f9f7153a00c29de82cedadc9957289b05','',0),
(20,'jacky123','9f735e0df9a1ddc702bf0a1a7b83033f9f7153a00c29de82cedadc9957289b05','',0),
(21,'arnold','49222775bfbacb5afb36385ae3c9cc5f0f29346718b53c3e18248a83cb84be04','',0),
(22,'kiss','9f735e0df9a1ddc702bf0a1a7b83033f9f7153a00c29de82cedadc9957289b05','',1),
(23,'kisst','9f735e0df9a1ddc702bf0a1a7b83033f9f7153a00c29de82cedadc9957289b05','',0),
(24,'ktr','9f735e0df9a1ddc702bf0a1a7b83033f9f7153a00c29de82cedadc9957289b05','',0);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
