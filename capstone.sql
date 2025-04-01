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

insert  into `tbl_admin_info`(`Admin_ID`,`Last_Name`,`First_Name`,`Middle_Name`,`Home_Address`,`Email_Address`,`Mobile_Number`,`Status`,`Password`,`PicPath`,`verification_code`,`verification_code_expiry`) values ('ryg59u0ZTR','Mantua','Clarence','Badilla','388 Marlboro Country, San Vicente, Santa Rita, Pampanga','rence.b.m@gmail.com','+639622100810','Active','$2y$10$RnjsME/DA.oNLJHeUs1ZJOZoM8WuRiZzAy0V78a2.VyCBRftuQdRa','uploads/admin/1741931137_profile.jpg',NULL,NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_cart` */

insert  into `tbl_cart`(`Cart_ID`,`User_ID`,`Product_ID`,`Quantity`,`Price`,`Total_Price`,`Order_Type`,`Date_Added`) values (86,'0001',9,1,'1400.00','1400.00','','2025-04-01 10:29:43');

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
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_customizations_temp` */

insert  into `tbl_customizations_temp`(`Temp_Customization_ID`,`User_ID`,`Furniture_Type`,`Furniture_Type_Additional_Info`,`Standard_Size`,`Desired_Size`,`Color`,`Color_Image_URL`,`Color_Additional_Info`,`Texture`,`Texture_Image_URL`,`Texture_Additional_Info`,`Wood_Type`,`Wood_Image_URL`,`Wood_Additional_Info`,`Foam_Type`,`Foam_Image_URL`,`Foam_Additional_Info`,`Cover_Type`,`Cover_Image_URL`,`Cover_Additional_Info`,`Design`,`Design_Image_URL`,`Design_Additional_Info`,`Tile_Type`,`Tile_Image_URL`,`Tile_Additional_Info`,`Metal_Type`,`Metal_Image_URL`,`Metal_Additional_Info`,`Order_Status`,`Request_Date`,`Last_Update`) values (5,'0001','bedframe',NULL,'bedframe5',NULL,'natural_oak',NULL,'','matte',NULL,'',NULL,NULL,'','uratex',NULL,'','velvet',NULL,'','modern',NULL,'','marble',NULL,'','flat',NULL,'',0,'2025-03-24 07:56:52','2025-03-24 07:56:52'),(6,'0001','bedframe',NULL,'bedframe5',NULL,'natural_oak',NULL,'','matte',NULL,'',NULL,NULL,'','uratex',NULL,'','velvet',NULL,'','modern',NULL,'','marble',NULL,'','flat',NULL,'',0,'2025-03-24 07:57:53','2025-03-24 07:57:53'),(7,'0001','bedframe',NULL,'bedframe5',NULL,'natural_oak',NULL,'','matte',NULL,'',NULL,NULL,'','uratex',NULL,'','velvet',NULL,'','modern',NULL,'','marble',NULL,'','flat',NULL,'',0,'2025-03-24 07:59:39','2025-03-24 07:59:39'),(8,'0001','bedframe',NULL,'bedframe5',NULL,'natural_oak',NULL,'','matte',NULL,'',NULL,NULL,'','uratex',NULL,'','velvet',NULL,'','modern',NULL,'','marble',NULL,'','flat',NULL,'',0,'2025-03-24 08:00:09','2025-03-24 08:00:09'),(9,'0001','chair',NULL,'chair-stan',NULL,'dark_walnut',NULL,'','matte',NULL,'',NULL,NULL,'','uratex',NULL,'','velvet',NULL,'','modern',NULL,'','marble',NULL,'','flat',NULL,'',0,'2025-03-24 08:02:05','2025-03-24 08:02:05'),(10,'0001','bedframe',NULL,'bedframe7','','dark_walnut',NULL,'','matte',NULL,'','mahogany',NULL,'','uratex',NULL,'','velvet',NULL,'','modern',NULL,'','marble',NULL,'','flat',NULL,'',0,'2025-03-24 08:19:41','2025-03-24 08:19:41'),(11,'0001','chair',NULL,'Chair - 20x21 in. // B-T-F: 37 in. // S-F: 18 in.','','dark_walnut',NULL,'','custom','../uploads/custom/67e0b1c033e52_Texture.jpg','smooth','mahogany',NULL,'','custom','../uploads/custom/67e0b1c03406a_Foam.jpg','Memory Foam','linen',NULL,'','modern',NULL,'','quartz',NULL,'','tubular',NULL,'',0,'2025-03-24 09:13:36','2025-03-24 09:13:36'),(12,'0001','chair',NULL,'Chair - 20x21 in. // B-T-F: 37 in. // S-F: 18 in.','','dark_walnut',NULL,'','custom','../uploads/custom/67e0b1c90eac2_Texture.jpg','smooth','mahogany',NULL,'','custom','../uploads/custom/67e0b1c90ebf6_Foam.jpg','Memory Foam','linen',NULL,'','modern',NULL,'','quartz',NULL,'','tubular',NULL,'',0,'2025-03-24 09:13:45','2025-03-24 09:13:45'),(13,'0001','chair',NULL,'Chair - 20x21 in. // B-T-F: 37 in. // S-F: 18 in.','','dark_walnut',NULL,'','custom','../uploads/custom/67e0b1f1ca7f6_Texture.jpg','smooth','mahogany',NULL,'','custom','../uploads/custom/67e0b1f1caa06_Foam.jpg','Memory Foam','linen',NULL,'','modern',NULL,'','quartz',NULL,'','tubular',NULL,'',0,'2025-03-24 09:14:25','2025-03-24 09:14:25'),(14,'0001','chair',NULL,'Chair - 20x21 in. // B-T-F: 37 in. // S-F: 18 in.','','dark_walnut',NULL,'','custom','../uploads/custom/67e0b4b840205_Texture.jpg','smooth','mahogany',NULL,'','custom','../uploads/custom/67e0b4b840399_Foam.jpg','Memory Foam','linen',NULL,'','modern',NULL,'','quartz',NULL,'','tubular',NULL,'',0,'2025-03-24 09:26:16','2025-03-24 09:26:16'),(15,'0001','salaset',NULL,'Sala Set 10x10 ft.','','white',NULL,'','distressed',NULL,'','custom','../uploads/custom/67e0b56b9073e_Wood.jpg','Mahogany','uratex',NULL,'','linen',NULL,'','custom','../uploads/custom/67e0b56b9093b_Design.jpg','Classic','granite',NULL,'','custom','../uploads/custom/67e0b56b90a4d_Metal.jpg','Metal',0,'2025-03-24 09:29:15','2025-03-24 09:29:15'),(16,'0001','bedframe',NULL,'Bed Frame - California King 72x84 in.',NULL,'natural_oak',NULL,NULL,'custom','../uploads/custom/67e0d710bb783_Texture.jpg',NULL,'custom','../uploads/custom/67e0d710bb93f_Wood.jpg',NULL,'uratex',NULL,NULL,'custom','../uploads/custom/67e0d710bba33_Cover.jpg',NULL,'modern',NULL,NULL,'granite',NULL,NULL,'tubular',NULL,NULL,0,'2025-03-24 11:52:48','2025-03-24 11:52:48'),(17,'0001','chair',NULL,'Chair - 20x21 in. // B-T-F: 37 in. // S-F: 18 in.',NULL,'custom','../uploads/custom/67e244c2021b7_Color.png',NULL,'glossy',NULL,NULL,'custom','../uploads/custom/67e244c2028f4_Wood.jpg',NULL,'uratex',NULL,NULL,'velvet',NULL,NULL,'modern',NULL,NULL,'',NULL,NULL,'tubular',NULL,NULL,0,'2025-03-25 13:53:06','2025-03-25 13:53:06'),(18,'0001','chair',NULL,'Chair - 20x21 in. // B-T-F: 37 in. // S-F: 18 in.',NULL,'custom','../uploads/custom/67e245d0a86af_Color.png',NULL,'glossy',NULL,NULL,'custom','../uploads/custom/67e245d0a8e3b_Wood.jpg',NULL,'uratex',NULL,NULL,'velvet',NULL,NULL,'modern',NULL,NULL,'',NULL,NULL,'tubular',NULL,NULL,0,'2025-03-25 13:57:36','2025-03-25 13:57:36'),(19,'0001','table',NULL,'Table 10 seater - L: 9 ft. // W: 41 in. // H: 30 in.',NULL,'espresso',NULL,NULL,'semi_glossy',NULL,NULL,'plywood1',NULL,NULL,'uratex',NULL,NULL,'suede',NULL,NULL,'farmhouse',NULL,NULL,'porcelain',NULL,NULL,'tubular',NULL,NULL,0,'2025-03-25 13:58:02','2025-03-25 13:58:02'),(20,'0001','salaset',NULL,'Sala Set 8x8 ft.',NULL,'driftwood_gray',NULL,NULL,'semi_glossy',NULL,NULL,'plywood2',NULL,NULL,'uratex',NULL,NULL,'linen',NULL,NULL,'traditional',NULL,NULL,'marble',NULL,NULL,'tubular',NULL,NULL,0,'2025-03-25 14:01:36','2025-03-25 14:01:36'),(21,'0001','table',NULL,'Table 8 seater - L: 8 ft. // W: 41 in. // H: 30 in.',NULL,'black',NULL,NULL,'weathered',NULL,NULL,'nara',NULL,NULL,'uratex',NULL,NULL,'frenchleather',NULL,NULL,'bohemian',NULL,NULL,'quartz',NULL,NULL,'tubular',NULL,NULL,0,'2025-03-25 14:02:17','2025-03-25 14:02:17'),(22,'0001','Table',NULL,'Table 10 seater - L: 9 ft. // W: 41 in. // H: 30 in.',NULL,'Mahogany',NULL,NULL,'Distressed',NULL,NULL,'Nara',NULL,NULL,'Uratex',NULL,NULL,'Suede',NULL,NULL,'Industrial',NULL,NULL,'Granite',NULL,NULL,'Flat Bar',NULL,NULL,0,'2025-03-26 11:55:18','2025-03-26 11:55:18'),(23,'0001','Table',NULL,'Table 8 seater - L: 8 ft. // W: 41 in. // H: 30 in.',NULL,'Honey Pine',NULL,NULL,'Brushed',NULL,NULL,'Plywood 1/2',NULL,NULL,'Uratex',NULL,NULL,'French Leather',NULL,NULL,'Farmhouse',NULL,NULL,'Marble',NULL,NULL,'Flat Bar',NULL,NULL,0,'2025-03-26 12:19:15','2025-03-26 12:19:15'),(24,'0001','Bed Frame',NULL,'Bed Frame - Full XL 54x80 in.',NULL,'Teak',NULL,NULL,'Wood Grain',NULL,NULL,'Plywood 1/8',NULL,NULL,'Uratex',NULL,NULL,'French Leather',NULL,NULL,'Traditional',NULL,NULL,'Marble',NULL,NULL,'Tubular',NULL,NULL,0,'2025-03-26 12:22:17','2025-03-26 12:22:17'),(25,'0001','Bed Frame',NULL,'Bed Frame - Full XL 54x80 in.',NULL,'Teak',NULL,NULL,'Wood Grain',NULL,NULL,'Plywood 1/8',NULL,NULL,'Uratex',NULL,NULL,'French Leather',NULL,NULL,'Traditional',NULL,NULL,'Marble',NULL,NULL,'Tubular',NULL,NULL,0,'2025-03-26 12:22:29','2025-03-26 12:22:29'),(26,'0001','Bed Frame',NULL,'Bed Frame - Full XL 54x80 in.',NULL,'Teak',NULL,NULL,'Wood Grain',NULL,NULL,'Plywood 1/8',NULL,NULL,'Uratex',NULL,NULL,'French Leather',NULL,NULL,'Traditional',NULL,NULL,'Marble',NULL,NULL,'Tubular',NULL,NULL,0,'2025-03-26 12:25:29','2025-03-26 12:25:29'),(27,'0001','Table',NULL,'Table 8 seater - L: 8 ft. // W: 41 in. // H: 30 in.',NULL,'Driftwood Gray',NULL,NULL,'Duco Finish',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,0,'2025-03-28 13:17:14','2025-03-28 13:17:14'),(28,'0001','Table',NULL,'Table 8 seater - L: 8 ft. // W: 41 in. // H: 30 in.',NULL,'Driftwood Gray',NULL,NULL,'Duco Finish',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,0,'2025-03-28 13:17:34','2025-03-28 13:17:34'),(29,'0001','Table',NULL,'Table 8 seater - L: 8 ft. // W: 41 in. // H: 30 in.',NULL,'Driftwood Gray',NULL,NULL,'Duco Finish',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,0,'2025-03-28 13:17:44','2025-03-28 13:17:44'),(30,'0001','Sala Set',NULL,'Sala Set 9x9 ft.',NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Marble',NULL,NULL,'Select one',NULL,NULL,0,'2025-03-28 13:18:01','2025-03-28 13:18:01'),(31,'0001','Bed Frame',NULL,'Bed Frame - Full XL 54x80 in.',NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,0,'2025-03-28 13:26:41','2025-03-28 13:26:41'),(32,'0001','Chair',NULL,'Chair - 20x21 in. // B-T-F: 37 in. // S-F: 18 in.',NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,0,'2025-03-28 13:34:47','2025-03-28 13:34:47'),(33,'0001','Bed Frame',NULL,'Bed Frame - Full XL 54x80 in.',NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,0,'2025-03-28 13:35:33','2025-03-28 13:35:33'),(34,'0001','Sala Set',NULL,'Sala Set 9x9 ft.',NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,0,'2025-03-28 13:40:09','2025-03-28 13:40:09'),(35,'0001','Sala Set',NULL,'Sala Set 10x10 ft.',NULL,'Espresso',NULL,NULL,'Duco Finish',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,0,'2025-03-28 13:51:11','2025-03-28 13:51:11'),(36,'0001','Sala Set',NULL,'Sala Set 9x9 ft.',NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,0,'2025-03-28 14:00:26','2025-03-28 14:00:26'),(37,'0001','Sala Set',NULL,'Sala Set 10x10 ft.',NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,0,'2025-03-28 14:12:21','2025-03-28 14:12:21'),(38,'0001','Sala Set',NULL,'Sala Set 9x9 ft.',NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,'Select one',NULL,NULL,0,'2025-03-28 14:12:57','2025-03-28 14:12:57');

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
) ENGINE=InnoDB AUTO_INCREMENT=116 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_order_request` */

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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_preorder` */

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
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_prod_info` */

insert  into `tbl_prod_info`(`Product_ID`,`Product_Name`,`Description`,`Category`,`Sizes`,`Color`,`Stock`,`Assembly_Required`,`ImageURL`,`Price`,`Sold`,`DateAdded`,`LastUpdate`,`GLB_File_URL`,`product_type`) values (4,'Sofa','A comfortable and stylish seating option, perfect for lounging and entertaining guests. Available in various designs and materials to suit any space.A comfortable and stylish seating option, perfect for lounging and entertaining guests. Available in various designs and materials to suit any space.','sofa','L Shape Sofa 6-7 seater - L: 9 ft // W: 3 ft // H: 3.5 ft','brown','57','Yes','../uploads/product/sofa3.jpg,../uploads/product/sofa2.jpg,../uploads/product/sofa.jpg','1500','','2025-03-13 14:44:34','2025-03-31 13:49:04','../uploads/product/3d/Sofa(Commission).glb','readymade'),(5,'Bed','A cozy and supportive sleeping solution designed for restful nights. Comes in different sizes and styles to match your comfort and décor preferences.','','Bed Frame - King 76x80 in.','Blue','0','Yes','../uploads/product/bed 3.jpg,../uploads/product/bed 1.jpg,../uploads/product/bed 2.jpg','2000','','2025-03-13 14:45:14','2025-03-28 15:42:04','../uploads/product/3d/Bed(Commission).glb','readymade'),(9,'Dining Set','ewan','salaset','Sala Set 9x9 ft.','brown','0','Yes','../uploads/product/dining 1.jpg,../uploads/product/dining 2.jpg,../uploads/product/dining 3.jpg','1400','','2025-03-24 13:28:58','2025-03-25 10:46:50','../uploads/product/3d/Dining(Commission).glb','readymade'),(15,'Custom Table Order','Custom order from request #27',NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-03-28 13:17:14','2025-03-28 13:17:14',NULL,'custom'),(16,'Custom Table Order','Custom order from request #28',NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-03-28 13:17:34','2025-03-28 13:17:34',NULL,'custom'),(17,'Custom Table Order','Custom order from request #29',NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-03-28 13:17:44','2025-03-28 13:17:44',NULL,'custom'),(18,'Custom Sala Set Order','Custom order from request #30',NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-03-28 13:18:01','2025-03-28 13:18:01',NULL,'custom'),(19,'Custom Bed Frame Order','Custom order from request #31',NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-03-28 13:26:41','2025-03-28 13:26:41',NULL,'custom'),(20,'Custom Chair Order','Custom order from request #32',NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-03-28 13:34:47','2025-03-28 13:34:47',NULL,'custom'),(21,'Custom Bed Frame Order','Custom order from request #33',NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-03-28 13:35:33','2025-03-28 13:35:33',NULL,'custom'),(22,'Custom Sala Set Order','Custom order from request #34',NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-03-28 13:40:09','2025-03-28 13:40:09',NULL,'custom'),(23,'Custom Sala Set Order','Custom order from request #35',NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-03-28 13:51:11','2025-03-28 13:51:11',NULL,'custom'),(24,'Custom Sala Set Order','Custom order from request #36',NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-03-28 14:00:26','2025-03-28 14:00:26',NULL,'custom'),(25,'Custom Sala Set Order','Custom order from request #37',NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-03-28 14:12:21','2025-03-28 14:12:21',NULL,'custom'),(26,'Custom Sala Set Order','Custom order from request #38',NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-03-28 14:12:57','2025-03-28 14:12:57',NULL,'custom');

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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_progress` */

insert  into `tbl_progress`(`Progress_ID`,`User_ID`,`Product_ID`,`Product_Name`,`Order_Type`,`Order_Status`,`Product_Status`,`Quantity`,`Total_Price`,`Date_Added`,`LastUpdate`,`Progress_Pic_20`,`Progress_Pic_30`,`Progress_Pic_40`,`Progress_Pic_50`,`Progress_Pic_60`,`Progress_Pic_70`,`Progress_Pic_80`,`Progress_Pic_90`,`Progress_Pic_100`,`Stop_Reason`,`Progress_Pic_10`) values (18,'0001',4,'Sofa','ready_made','70','90',1,'1500.00','2025-04-01 14:06:44','2025-04-01 14:08:56',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_purchase_history` */

insert  into `tbl_purchase_history`(`Purchase_ID`,`User_ID`,`Product_ID`,`Product_Name`,`Quantity`,`Total_Price`,`Order_Type`,`Purchase_Date`,`Order_Status`,`Product_Status`) values (4,'0001',4,'Sofa',1,'1500.00','ready_made','2025-03-31 10:23:16',100,100);

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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_ready_made_orders` */

insert  into `tbl_ready_made_orders`(`ReadyMadeOrder_ID`,`Product_ID`,`User_ID`,`Quantity`,`Total_Price`,`Order_Status`,`Order_Date`,`Product_Status`,`Progress_Pic_10`,`Progress_Pic_20`,`Progress_Pic_30`,`Progress_Pic_40`,`Progress_Pic_50`,`Progress_Pic_60`,`Progress_Pic_70`,`Progress_Pic_80`,`Progress_Pic_90`,`Progress_Pic_100`,`Stop_Reason`) values (13,4,'0001',1,'1500.00',70,'2025-04-01 14:06:44',90,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_reviews` */

insert  into `tbl_reviews`(`Review_ID`,`User_ID`,`Product_ID`,`Rating`,`Review_Text`,`Review_Date`,`PicPath`) values (13,'0001',4,5,'meow wow','2025-03-31 10:43:20','[\"../uploads/review_pics/67ea0148e6513_dining 2.jpg\",\"../uploads/review_pics/67ea0148e6725_dining 3.jpg\",\"../uploads/review_pics/67ea0148e684a_bed 3.jpg\",\"../uploads/review_pics/67ea0148e6941_bed 1.jpg\",\"../uploads/review_pics/67ea0148e6a25_bed 2.jpg\"]');

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

insert  into `tbl_user_info`(`User_ID`,`Last_Name`,`First_Name`,`Middle_Name`,`Home_Address`,`Email_Address`,`Mobile_Number`,`Status`,`Password`,`PicPath`,`reset_code`,`reset_code_expiry`) values ('0001','Mantua','Clarence','Badilla','388 Marlboro Country, San Vicente, Santa Rita, Pampanga','rence.b.m@gmail.com','09622100810','Active','$2y$10$aOYwdqt/KKJ8d2NTTkuAWe.EWz.i0YTu2qkJ7ZrIy0HA.68vy2dTm','uploads/user/0001_profile.jpg',NULL,NULL);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
