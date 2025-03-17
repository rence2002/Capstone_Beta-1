/*
SQLyog Ultimate - MySQL GUI v8.2 
MySQL - 5.5.5-10.4.32-MariaDB : Database - db_api_capstone
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`db_api_capstone` /*!40100 DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci */;

USE `db_api_capstone`;

/*Table structure for table `tbl_admin_info` */

DROP TABLE IF EXISTS `tbl_admin_info`;

CREATE TABLE `tbl_admin_info` (
  `Admin_ID` varchar(50) NOT NULL,
  `Last_Name` varchar(100) DEFAULT NULL,
  `First_Name` varchar(100) DEFAULT NULL,
  `Middle_Name` varchar(100) DEFAULT NULL,
  `Home_Address` varchar(100) DEFAULT NULL,
  `Email_Address` varchar(100) DEFAULT NULL,
  `Mobile_Number` varchar(100) DEFAULT NULL,
  `Status` varchar(100) DEFAULT NULL,
  `Password` varchar(500) DEFAULT NULL,
  `PicPath` varchar(250) DEFAULT NULL,
  `verification_code` varchar(255) DEFAULT NULL,
  `verification_code_expiry` datetime DEFAULT NULL,
  PRIMARY KEY (`Admin_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_admin_info` */

insert  into `tbl_admin_info`(`Admin_ID`,`Last_Name`,`First_Name`,`Middle_Name`,`Home_Address`,`Email_Address`,`Mobile_Number`,`Status`,`Password`,`PicPath`,`verification_code`,`verification_code_expiry`) values ('E5VFwGvTtf','Mantua','Clarence','Badilla','388 Marlboro Country, San Vicente, Santa Rita, Pampanga','rence.b.m@gmail.com','09622100810','Active','$2y$10$96RElObwsav1yBydFg2OweJOx9k5xe7hVP3M0edAyYQhM5skr2slq','/Capstone_Beta/uploads/admin/1740988854_profile.jpg',NULL,NULL);

/*Table structure for table `tbl_cart` */

DROP TABLE IF EXISTS `tbl_cart`;

CREATE TABLE `tbl_cart` (
  `Cart_ID` int(11) NOT NULL AUTO_INCREMENT,
  `User_ID` varchar(150) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `Price` decimal(10,2) NOT NULL,
  `Total_Price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `Order_Type` enum('pre_order','ready_made') NOT NULL DEFAULT 'ready_made',
  `Date_Added` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`Cart_ID`),
  KEY `User_ID` (`User_ID`),
  KEY `Product_ID` (`Product_ID`),
  CONSTRAINT `tbl_cart_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `tbl_user_info` (`User_ID`),
  CONSTRAINT `tbl_cart_ibfk_2` FOREIGN KEY (`Product_ID`) REFERENCES `tbl_prod_info` (`Product_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_cart` */

/*Table structure for table `tbl_customizations` */

DROP TABLE IF EXISTS `tbl_customizations`;

CREATE TABLE `tbl_customizations` (
  `Customization_ID` int(11) NOT NULL AUTO_INCREMENT,
  `User_ID` varchar(150) NOT NULL,
  `Furniture_Type` varchar(255) NOT NULL,
  `Furniture_Type_Additional_Info` text DEFAULT NULL,
  `Standard_Size` varchar(100) DEFAULT NULL,
  `Desired_Size` varchar(100) DEFAULT NULL,
  `Color` varchar(50) DEFAULT NULL,
  `Color_Image_URL` varchar(255) DEFAULT NULL,
  `Color_Additional_Info` text DEFAULT NULL,
  `Texture` varchar(50) DEFAULT NULL,
  `Texture_Image_URL` varchar(255) DEFAULT NULL,
  `Texture_Additional_Info` text DEFAULT NULL,
  `Wood_Type` varchar(50) DEFAULT NULL,
  `Wood_Image_URL` varchar(255) DEFAULT NULL,
  `Wood_Additional_Info` text DEFAULT NULL,
  `Foam_Type` varchar(50) DEFAULT NULL,
  `Foam_Image_URL` varchar(255) DEFAULT NULL,
  `Foam_Additional_Info` text DEFAULT NULL,
  `Cover_Type` varchar(50) DEFAULT NULL,
  `Cover_Image_URL` varchar(255) DEFAULT NULL,
  `Cover_Additional_Info` text DEFAULT NULL,
  `Design` varchar(255) DEFAULT NULL,
  `Design_Image_URL` varchar(255) DEFAULT NULL,
  `Design_Additional_Info` text DEFAULT NULL,
  `Tile_Type` varchar(50) DEFAULT NULL,
  `Tile_Image_URL` varchar(255) DEFAULT NULL,
  `Tile_Additional_Info` text DEFAULT NULL,
  `Metal_Type` varchar(50) DEFAULT NULL,
  `Metal_Image_URL` varchar(255) DEFAULT NULL,
  `Metal_Additional_Info` text DEFAULT NULL,
  `Order_Status` int(3) NOT NULL DEFAULT 0,
  `Product_Status` int(3) NOT NULL DEFAULT 0,
  `Request_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Last_Update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Product_ID` int(11) DEFAULT NULL,
  `Stop_Reason` varchar(255) DEFAULT NULL,
  `Progress_Pic_10` varchar(255) DEFAULT NULL,
  `Progress_Pic_20` varchar(255) DEFAULT NULL,
  `Progress_Pic_30` varchar(255) DEFAULT NULL,
  `Progress_Pic_40` varchar(255) DEFAULT NULL,
  `Progress_Pic_50` varchar(255) DEFAULT NULL,
  `Progress_Pic_60` varchar(255) DEFAULT NULL,
  `Progress_Pic_70` varchar(255) DEFAULT NULL,
  `Progress_Pic_80` varchar(255) DEFAULT NULL,
  `Progress_Pic_90` varchar(255) DEFAULT NULL,
  `Progress_Pic_100` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`Customization_ID`),
  KEY `User_ID` (`User_ID`),
  CONSTRAINT `tbl_customizations_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `tbl_user_info` (`User_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_customizations` */

insert  into `tbl_customizations`(`Customization_ID`,`User_ID`,`Furniture_Type`,`Furniture_Type_Additional_Info`,`Standard_Size`,`Desired_Size`,`Color`,`Color_Image_URL`,`Color_Additional_Info`,`Texture`,`Texture_Image_URL`,`Texture_Additional_Info`,`Wood_Type`,`Wood_Image_URL`,`Wood_Additional_Info`,`Foam_Type`,`Foam_Image_URL`,`Foam_Additional_Info`,`Cover_Type`,`Cover_Image_URL`,`Cover_Additional_Info`,`Design`,`Design_Image_URL`,`Design_Additional_Info`,`Tile_Type`,`Tile_Image_URL`,`Tile_Additional_Info`,`Metal_Type`,`Metal_Image_URL`,`Metal_Additional_Info`,`Order_Status`,`Product_Status`,`Request_Date`,`Last_Update`,`Product_ID`,`Stop_Reason`,`Progress_Pic_10`,`Progress_Pic_20`,`Progress_Pic_30`,`Progress_Pic_40`,`Progress_Pic_50`,`Progress_Pic_60`,`Progress_Pic_70`,`Progress_Pic_80`,`Progress_Pic_90`,`Progress_Pic_100`) values (2,'0002','chair','furniture','chair-stan','sizes','natural_oak','../uploads/custom/Color_1.png','Color','matte','../uploads/custom/Texture_1.jpg','texture','mahogany','../uploads/custom/Wood_1.jpg','wood','uratex','../uploads/custom/Foam_1.jpg','foam','velvet','../uploads/custom/Cover_1.jpg','cover','modern','../uploads/custom/Design_1.jpg','design',NULL,'../uploads/custom/Tile_1.jpg','tile','tubular','../uploads/custom/Metal_1.jpg','metal',40,40,'2025-03-13 08:05:32','2025-03-13 08:28:06',30,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);

/*Table structure for table `tbl_customizations_temp` */

DROP TABLE IF EXISTS `tbl_customizations_temp`;

CREATE TABLE `tbl_customizations_temp` (
  `Temp_Customization_ID` int(11) NOT NULL AUTO_INCREMENT,
  `User_ID` varchar(150) NOT NULL,
  `Furniture_Type` varchar(255) NOT NULL,
  `Furniture_Type_Additional_Info` text DEFAULT NULL,
  `Standard_Size` varchar(100) DEFAULT NULL,
  `Desired_Size` varchar(100) DEFAULT NULL,
  `Color` varchar(50) DEFAULT NULL,
  `Color_Image_URL` varchar(255) DEFAULT NULL,
  `Color_Additional_Info` text DEFAULT NULL,
  `Texture` varchar(50) DEFAULT NULL,
  `Texture_Image_URL` varchar(255) DEFAULT NULL,
  `Texture_Additional_Info` text DEFAULT NULL,
  `Wood_Type` varchar(50) DEFAULT NULL,
  `Wood_Image_URL` varchar(255) DEFAULT NULL,
  `Wood_Additional_Info` text DEFAULT NULL,
  `Foam_Type` varchar(50) DEFAULT NULL,
  `Foam_Image_URL` varchar(255) DEFAULT NULL,
  `Foam_Additional_Info` text DEFAULT NULL,
  `Cover_Type` varchar(50) DEFAULT NULL,
  `Cover_Image_URL` varchar(255) DEFAULT NULL,
  `Cover_Additional_Info` text DEFAULT NULL,
  `Design` varchar(255) DEFAULT NULL,
  `Design_Image_URL` varchar(255) DEFAULT NULL,
  `Design_Additional_Info` text DEFAULT NULL,
  `Tile_Type` varchar(50) DEFAULT NULL,
  `Tile_Image_URL` varchar(255) DEFAULT NULL,
  `Tile_Additional_Info` text DEFAULT NULL,
  `Metal_Type` varchar(50) DEFAULT NULL,
  `Metal_Image_URL` varchar(255) DEFAULT NULL,
  `Metal_Additional_Info` text DEFAULT NULL,
  `Order_Status` int(3) NOT NULL DEFAULT 0,
  `Request_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Last_Update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`Temp_Customization_ID`),
  KEY `User_ID` (`User_ID`),
  CONSTRAINT `tbl_customizations_temp_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `tbl_user_info` (`User_ID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_customizations_temp` */

insert  into `tbl_customizations_temp`(`Temp_Customization_ID`,`User_ID`,`Furniture_Type`,`Furniture_Type_Additional_Info`,`Standard_Size`,`Desired_Size`,`Color`,`Color_Image_URL`,`Color_Additional_Info`,`Texture`,`Texture_Image_URL`,`Texture_Additional_Info`,`Wood_Type`,`Wood_Image_URL`,`Wood_Additional_Info`,`Foam_Type`,`Foam_Image_URL`,`Foam_Additional_Info`,`Cover_Type`,`Cover_Image_URL`,`Cover_Additional_Info`,`Design`,`Design_Image_URL`,`Design_Additional_Info`,`Tile_Type`,`Tile_Image_URL`,`Tile_Additional_Info`,`Metal_Type`,`Metal_Image_URL`,`Metal_Additional_Info`,`Order_Status`,`Request_Date`,`Last_Update`) values (5,'0002','chair','furniture','chair-stan','sizes','natural_oak','../uploads/custom/Color_2.png','Color','matte','../uploads/custom/Texture_2.jpg','texture','mahogany','../uploads/custom/Wood_2.jpg','wood','uratex','../uploads/custom/Foam_2.jpg','foam','velvet','../uploads/custom/Cover_2.jpg','cover','modern','../uploads/custom/Design_2.jpg','design',NULL,'../uploads/custom/Tile_2.jpg','tile','tubular','../uploads/custom/Metal_2.jpg','metal',0,'2025-03-10 15:38:25','2025-03-10 15:38:25');

/*Table structure for table `tbl_order_request` */

DROP TABLE IF EXISTS `tbl_order_request`;

CREATE TABLE `tbl_order_request` (
  `Request_ID` int(11) NOT NULL AUTO_INCREMENT,
  `User_ID` varchar(150) NOT NULL,
  `Product_ID` int(11) DEFAULT NULL,
  `Customization_ID` int(11) DEFAULT NULL,
  `Quantity` int(11) NOT NULL,
  `Order_Type` enum('ready_made','pre_order','custom') NOT NULL DEFAULT 'ready_made',
  `Order_Status` int(3) NOT NULL DEFAULT 0,
  `Total_Price` decimal(10,2) NOT NULL,
  `Request_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`Request_ID`),
  KEY `Product_ID` (`Product_ID`),
  KEY `User_ID` (`User_ID`),
  KEY `Customization_ID` (`Customization_ID`),
  CONSTRAINT `tbl_order_request_ibfk_1` FOREIGN KEY (`Product_ID`) REFERENCES `tbl_prod_info` (`Product_ID`) ON DELETE CASCADE,
  CONSTRAINT `tbl_order_request_ibfk_2` FOREIGN KEY (`User_ID`) REFERENCES `tbl_user_info` (`User_ID`) ON DELETE CASCADE,
  CONSTRAINT `tbl_order_request_ibfk_3` FOREIGN KEY (`Customization_ID`) REFERENCES `tbl_customizations_temp` (`Temp_Customization_ID`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_order_request` */

insert  into `tbl_order_request`(`Request_ID`,`User_ID`,`Product_ID`,`Customization_ID`,`Quantity`,`Order_Type`,`Order_Status`,`Total_Price`,`Request_Date`) values (12,'0002',3,NULL,3,'ready_made',0,'6000.00','2025-03-11 08:08:01');

/*Table structure for table `tbl_preorder` */

DROP TABLE IF EXISTS `tbl_preorder`;

CREATE TABLE `tbl_preorder` (
  `Preorder_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Product_ID` int(11) NOT NULL,
  `User_ID` varchar(150) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `Total_Price` decimal(10,2) NOT NULL,
  `Preorder_Status` int(3) NOT NULL DEFAULT 0,
  `Order_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Product_Status` int(3) NOT NULL DEFAULT 0,
  `Progress_Pic_10` varchar(255) DEFAULT NULL,
  `Progress_Pic_20` varchar(255) DEFAULT NULL,
  `Progress_Pic_30` varchar(255) DEFAULT NULL,
  `Progress_Pic_40` varchar(255) DEFAULT NULL,
  `Progress_Pic_50` varchar(255) DEFAULT NULL,
  `Progress_Pic_60` varchar(255) DEFAULT NULL,
  `Progress_Pic_70` varchar(255) DEFAULT NULL,
  `Progress_Pic_80` varchar(255) DEFAULT NULL,
  `Progress_Pic_90` varchar(255) DEFAULT NULL,
  `Progress_Pic_100` varchar(255) DEFAULT NULL,
  `Stop_Reason` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`Preorder_ID`),
  KEY `Product_ID` (`Product_ID`),
  KEY `User_ID` (`User_ID`),
  CONSTRAINT `tbl_preorder_info_ibfk_1` FOREIGN KEY (`Product_ID`) REFERENCES `tbl_prod_info` (`Product_ID`),
  CONSTRAINT `tbl_preorder_info_ibfk_2` FOREIGN KEY (`User_ID`) REFERENCES `tbl_user_info` (`User_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_preorder` */

insert  into `tbl_preorder`(`Preorder_ID`,`Product_ID`,`User_ID`,`Quantity`,`Total_Price`,`Preorder_Status`,`Order_Date`,`Product_Status`,`Progress_Pic_10`,`Progress_Pic_20`,`Progress_Pic_30`,`Progress_Pic_40`,`Progress_Pic_50`,`Progress_Pic_60`,`Progress_Pic_70`,`Progress_Pic_80`,`Progress_Pic_90`,`Progress_Pic_100`,`Stop_Reason`) values (1,1,'001',1,'1500.00',80,'2025-03-08 16:16:40',100,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'../uploads/progress_pics/pre_order_Sofa_100_1741421495_2f1def4532.jpg',''),(2,2,'0002',1,'1500.00',100,'2025-03-11 16:03:41',100,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'');

/*Table structure for table `tbl_prod_info` */

DROP TABLE IF EXISTS `tbl_prod_info`;

CREATE TABLE `tbl_prod_info` (
  `Product_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Product_Name` varchar(255) NOT NULL,
  `Description` text DEFAULT NULL,
  `Category` varchar(100) DEFAULT NULL,
  `Sizes` varchar(100) DEFAULT NULL,
  `Color` varchar(50) DEFAULT NULL,
  `Stock` varchar(100) DEFAULT NULL,
  `Assembly_Required` varchar(255) DEFAULT NULL,
  `ImageURL` varchar(255) DEFAULT NULL,
  `Price` varchar(255) NOT NULL,
  `Sold` varchar(255) DEFAULT NULL,
  `DateAdded` timestamp NOT NULL DEFAULT current_timestamp(),
  `LastUpdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `GLB_File_URL` varchar(255) DEFAULT NULL,
  `product_type` enum('readymade','custom') DEFAULT 'readymade',
  PRIMARY KEY (`Product_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_prod_info` */

insert  into `tbl_prod_info`(`Product_ID`,`Product_Name`,`Description`,`Category`,`Sizes`,`Color`,`Stock`,`Assembly_Required`,`ImageURL`,`Price`,`Sold`,`DateAdded`,`LastUpdate`,`GLB_File_URL`,`product_type`) values (1,'Sofa','A comfortable and stylish seating option, perfect for lounging and entertaining guests. Available in various designs and materials to suit any space.','sofa','\'L Shape Sofa 6-7 seater  - L: 9 ft // W: 3 ft // H: 3.5 ft\'','brown','98','Yes','../uploads/product/sofa3.jpg,../uploads/product/sofa2.jpg,../uploads/product/sofa.jpg','1500','','2025-03-02 09:34:24','2025-03-10 09:16:57','../uploads/product/3d/Sofa(Commission).glb','readymade'),(2,'Bed',' A cozy and supportive sleeping solution designed for restful nights. Comes in different sizes and styles to match your comfort and décor preferences.','bedframe','\'Bed Frame -  King  76x80 in.\'','brown','50','Yes','../uploads/product/bed 3.jpg,../uploads/product/bed 1.jpg,../uploads/product/bed 2.jpg','1500','','2025-03-02 09:35:06','2025-03-10 09:12:45','../uploads/product/3d/Bed(Commission).glb','readymade'),(3,'Dining Set','A functional and elegant ensemble of a dining table and chairs, ideal for family meals and gatherings. Available in various materials and designs to complement your dining space.','salaset','\'Table 8 seater - L: 8 ft. // W: 41 in. // H: 30 in.\'','brown','50','Yes','../uploads/product/dining 1.jpg,../uploads/product/dining 2.jpg,../uploads/product/dining 3.jpg','2000','','2025-03-02 09:36:05','2025-03-10 09:13:37','../uploads/product/3d/Dining(Commission).glb','readymade'),(4,'Custom chair','Custom order from request #3','Custom Furniture',NULL,NULL,NULL,NULL,NULL,'',NULL,'2025-03-02 09:55:51','2025-03-02 09:55:51',NULL,'custom'),(26,'Custom chair','Custom order from request #10','Custom Furniture',NULL,NULL,NULL,NULL,NULL,'',NULL,'2025-03-13 08:05:32','2025-03-13 08:05:32',NULL,'custom'),(27,'chair',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-03-13 08:18:29','2025-03-13 08:18:29',NULL,'custom'),(28,'chair',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-03-13 08:18:55','2025-03-13 08:18:55',NULL,'custom'),(29,'chair',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-03-13 08:27:43','2025-03-13 08:27:43',NULL,'custom'),(30,'chair',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-03-13 08:28:06','2025-03-13 08:28:06',NULL,'custom');

/*Table structure for table `tbl_progress` */

DROP TABLE IF EXISTS `tbl_progress`;

CREATE TABLE `tbl_progress` (
  `Progress_ID` int(11) NOT NULL AUTO_INCREMENT,
  `User_ID` varchar(150) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `Product_Name` varchar(255) NOT NULL,
  `Order_Type` varchar(100) NOT NULL,
  `Order_Status` varchar(100) NOT NULL,
  `Product_Status` varchar(100) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `Total_Price` decimal(10,2) NOT NULL,
  `Date_Added` timestamp NOT NULL DEFAULT current_timestamp(),
  `LastUpdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Progress_Pic_20` varchar(255) DEFAULT NULL,
  `Progress_Pic_30` varchar(255) DEFAULT NULL,
  `Progress_Pic_40` varchar(255) DEFAULT NULL,
  `Progress_Pic_50` varchar(255) DEFAULT NULL,
  `Progress_Pic_60` varchar(255) DEFAULT NULL,
  `Progress_Pic_70` varchar(255) DEFAULT NULL,
  `Progress_Pic_80` varchar(255) DEFAULT NULL,
  `Progress_Pic_90` varchar(255) DEFAULT NULL,
  `Progress_Pic_100` varchar(255) DEFAULT NULL,
  `Stop_Reason` varchar(255) DEFAULT NULL,
  `Progress_Pic_10` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`Progress_ID`),
  KEY `User_ID` (`User_ID`),
  KEY `Product_ID` (`Product_ID`),
  CONSTRAINT `tbl_progress_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `tbl_user_info` (`User_ID`) ON DELETE CASCADE,
  CONSTRAINT `tbl_progress_ibfk_2` FOREIGN KEY (`Product_ID`) REFERENCES `tbl_prod_info` (`Product_ID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_progress` */

insert  into `tbl_progress`(`Progress_ID`,`User_ID`,`Product_ID`,`Product_Name`,`Order_Type`,`Order_Status`,`Product_Status`,`Quantity`,`Total_Price`,`Date_Added`,`LastUpdate`,`Progress_Pic_20`,`Progress_Pic_30`,`Progress_Pic_40`,`Progress_Pic_50`,`Progress_Pic_60`,`Progress_Pic_70`,`Progress_Pic_80`,`Progress_Pic_90`,`Progress_Pic_100`,`Stop_Reason`,`Progress_Pic_10`) values (1,'001',4,'','','0','',1,'0.00','2025-03-02 09:55:51','2025-03-02 09:55:51',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(2,'0002',26,'','','0','',1,'0.00','2025-03-13 08:05:32','2025-03-13 08:05:32',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);

/*Table structure for table `tbl_purchase_history` */

DROP TABLE IF EXISTS `tbl_purchase_history`;

CREATE TABLE `tbl_purchase_history` (
  `Purchase_ID` int(11) NOT NULL AUTO_INCREMENT,
  `User_ID` varchar(150) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `Product_Name` varchar(255) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `Total_Price` decimal(10,2) NOT NULL,
  `Order_Type` enum('ready_made','pre_order','custom') NOT NULL DEFAULT 'ready_made',
  `Purchase_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Order_Status` int(3) NOT NULL DEFAULT 0,
  `Product_Status` int(3) NOT NULL DEFAULT 0,
  PRIMARY KEY (`Purchase_ID`),
  KEY `User_ID` (`User_ID`),
  KEY `Product_ID` (`Product_ID`),
  CONSTRAINT `tbl_purchase_history_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `tbl_user_info` (`User_ID`) ON DELETE CASCADE,
  CONSTRAINT `tbl_purchase_history_ibfk_2` FOREIGN KEY (`Product_ID`) REFERENCES `tbl_prod_info` (`Product_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_purchase_history` */

insert  into `tbl_purchase_history`(`Purchase_ID`,`User_ID`,`Product_ID`,`Product_Name`,`Quantity`,`Total_Price`,`Order_Type`,`Purchase_Date`,`Order_Status`,`Product_Status`) values (2,'001',4,'Custom chair',1,'0.00','custom','2025-03-02 11:48:31',100,100),(3,'001',1,'Sofa',1,'1500.00','pre_order','2025-03-02 11:59:00',20,0),(4,'001',1,'Sofa',1,'1500.00','pre_order','2025-03-02 12:12:30',100,0),(5,'001',1,'Sofa',1,'1500.00','pre_order','2025-03-02 12:13:04',80,0),(6,'0002',2,'Bed',1,'1500.00','pre_order','2025-03-11 16:17:20',100,0);

/*Table structure for table `tbl_ready_made_orders` */

DROP TABLE IF EXISTS `tbl_ready_made_orders`;

CREATE TABLE `tbl_ready_made_orders` (
  `ReadyMadeOrder_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Product_ID` int(11) NOT NULL,
  `User_ID` varchar(150) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `Total_Price` decimal(10,2) NOT NULL,
  `Order_Status` int(3) NOT NULL DEFAULT 0,
  `Order_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Product_Status` int(3) NOT NULL DEFAULT 0,
  `Progress_Pic_10` varchar(255) DEFAULT NULL,
  `Progress_Pic_20` varchar(255) DEFAULT NULL,
  `Progress_Pic_30` varchar(255) DEFAULT NULL,
  `Progress_Pic_40` varchar(255) DEFAULT NULL,
  `Progress_Pic_50` varchar(255) DEFAULT NULL,
  `Progress_Pic_60` varchar(255) DEFAULT NULL,
  `Progress_Pic_70` varchar(255) DEFAULT NULL,
  `Progress_Pic_80` varchar(255) DEFAULT NULL,
  `Progress_Pic_90` varchar(255) DEFAULT NULL,
  `Progress_Pic_100` varchar(255) DEFAULT NULL,
  `Stop_Reason` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ReadyMadeOrder_ID`),
  KEY `Product_ID` (`Product_ID`),
  KEY `User_ID` (`User_ID`),
  CONSTRAINT `tbl_ready_made_orders_ibfk_1` FOREIGN KEY (`Product_ID`) REFERENCES `tbl_prod_info` (`Product_ID`),
  CONSTRAINT `tbl_ready_made_orders_ibfk_2` FOREIGN KEY (`User_ID`) REFERENCES `tbl_user_info` (`User_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_ready_made_orders` */

insert  into `tbl_ready_made_orders`(`ReadyMadeOrder_ID`,`Product_ID`,`User_ID`,`Quantity`,`Total_Price`,`Order_Status`,`Order_Date`,`Product_Status`,`Progress_Pic_10`,`Progress_Pic_20`,`Progress_Pic_30`,`Progress_Pic_40`,`Progress_Pic_50`,`Progress_Pic_60`,`Progress_Pic_70`,`Progress_Pic_80`,`Progress_Pic_90`,`Progress_Pic_100`,`Stop_Reason`) values (1,2,'002',1,'1500.00',10,'2025-03-02 09:55:52',90,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);

/*Table structure for table `tbl_reviews` */

DROP TABLE IF EXISTS `tbl_reviews`;

CREATE TABLE `tbl_reviews` (
  `Review_ID` int(11) NOT NULL AUTO_INCREMENT,
  `User_ID` varchar(150) NOT NULL,
  `Product_ID` int(11) DEFAULT NULL,
  `Rating` int(1) NOT NULL,
  `Review_Text` text DEFAULT NULL,
  `Review_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `PicPath` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`Review_ID`),
  KEY `User_ID` (`User_ID`),
  KEY `tbl_reviews_ibfk_2` (`Product_ID`),
  CONSTRAINT `tbl_reviews_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `tbl_user_info` (`User_ID`) ON DELETE CASCADE,
  CONSTRAINT `tbl_reviews_ibfk_2` FOREIGN KEY (`Product_ID`) REFERENCES `tbl_prod_info` (`Product_ID`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_reviews` */

insert  into `tbl_reviews`(`Review_ID`,`User_ID`,`Product_ID`,`Rating`,`Review_Text`,`Review_Date`,`PicPath`) values (2,'002',2,5,'very good','2025-03-02 12:48:52','[\"..\\/uploads\\/review_pics\\/review_2_1741503619_81160ab27f.jpg\",\"..\\/uploads\\/review_pics\\/review_2_1741503619_d6ba826c3c.jpg\",\"..\\/uploads\\/review_pics\\/review_2_1741503619_0d519c82e0.jpg\"]');

/*Table structure for table `tbl_user_info` */

DROP TABLE IF EXISTS `tbl_user_info`;

CREATE TABLE `tbl_user_info` (
  `User_ID` varchar(150) NOT NULL,
  `Last_Name` varchar(300) DEFAULT NULL,
  `First_Name` varchar(300) DEFAULT NULL,
  `Middle_Name` varchar(300) DEFAULT NULL,
  `Home_Address` varchar(300) DEFAULT NULL,
  `Email_Address` varchar(300) DEFAULT NULL,
  `Mobile_Number` varchar(300) DEFAULT NULL,
  `Status` varchar(300) DEFAULT NULL,
  `Password` varchar(150) DEFAULT NULL,
  `PicPath` varchar(750) DEFAULT NULL,
  `reset_code` int(11) DEFAULT NULL,
  `reset_code_expiry` datetime DEFAULT NULL,
  PRIMARY KEY (`User_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_user_info` */

insert  into `tbl_user_info`(`User_ID`,`Last_Name`,`First_Name`,`Middle_Name`,`Home_Address`,`Email_Address`,`Mobile_Number`,`Status`,`Password`,`PicPath`,`reset_code`,`reset_code_expiry`) values ('0001','Lugtu','John Llyod','Ewan','B2 L7 Westville Homes San Juan CSFP','lloydies02@gmail.com','09000000000','Active','$2y$10$ZfnILwaWLJZ/2sHf1LP5UeMNkcOphULSUsj.EIhpBiCOshUMyB9ei',NULL,NULL,NULL),('0002','Mantua','Clarence','Badilla','388 Marlboro Country, San Vicente, Santa Rita, Pampanga','rence.b.m@gmail.com','09622100810','Active','$2y$10$V3RLt5lZX5YpbHa89ycL..K.UdNB0VbCSEdKyAFAmpam71VGEEDZa','../uploads/user/0002_profile.jpg',NULL,NULL),('001','Lugtu','John Llyod','Ewan','B2 L7 Westville Homes San Juan CSFP','jl@gmail.com','09000000001','Active','$2y$10$IFTFxs4lXRxepaHYCh9yN..soGsW7gnQHlAnQby0mX2ZjyinHeycq','../uploads/user/jl.png',NULL,NULL),('002','Pamintuan','Charleskent','Grasya','Pampanga','ck@gmail.com','09111111111','Active','$2y$10$iUfG3XpPoJh7o0AbRf2Cl.88TwgfnX2/0RksSnyFwNckzGEANKJWm','../uploads/user/ck.png',NULL,NULL);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
