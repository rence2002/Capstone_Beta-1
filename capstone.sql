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

insert  into `tbl_admin_info`(`Admin_ID`,`Last_Name`,`First_Name`,`Middle_Name`,`Home_Address`,`Email_Address`,`Mobile_Number`,`Status`,`Password`,`PicPath`,`verification_code`,`verification_code_expiry`) values ('xAUV8soBGn','Mantua','Clarence','Badilla','388 Marlboro Country, San Vicente, Santa Rita, Pampanga','rence.b.m@gmail.com','+639622100810','Active','$2y$10$MHDmuAiA929BEfVM9pjZs.VwsIybz8Dpaw7vvcuEV43IG8LxQdU1K','uploads/admin/1743557097_profile.jpg',NULL,NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_customizations` */

insert  into `tbl_customizations`(`Customization_ID`,`User_ID`,`Furniture_Type`,`Furniture_Type_Additional_Info`,`Standard_Size`,`Desired_Size`,`Color`,`Color_Image_URL`,`Color_Additional_Info`,`Texture`,`Texture_Image_URL`,`Texture_Additional_Info`,`Wood_Type`,`Wood_Image_URL`,`Wood_Additional_Info`,`Foam_Type`,`Foam_Image_URL`,`Foam_Additional_Info`,`Cover_Type`,`Cover_Image_URL`,`Cover_Additional_Info`,`Design`,`Design_Image_URL`,`Design_Additional_Info`,`Tile_Type`,`Tile_Image_URL`,`Tile_Additional_Info`,`Metal_Type`,`Metal_Image_URL`,`Metal_Additional_Info`,`Order_Status`,`Product_Status`,`Request_Date`,`Last_Update`,`Product_ID`,`Stop_Reason`,`Progress_Pic_10`,`Progress_Pic_20`,`Progress_Pic_30`,`Progress_Pic_40`,`Progress_Pic_50`,`Progress_Pic_60`,`Progress_Pic_70`,`Progress_Pic_80`,`Progress_Pic_90`,`Progress_Pic_100`) values (1,'0001','salaset','','Sala Set 9x9 ft.','','custom','/Capstone_Beta/Capstone_Client/uploads/custom/67ee2e5ae4f8d_Color.png','dsasdsad','custom','/Capstone_Beta/Capstone_Client/uploads/custom/67ee2e5ae5315_Texture.jpg','dsadasdas','custom','/Capstone_Beta/Capstone_Client/uploads/custom/67ee2e5ae54c9_Wood.jpg','dsadsadasd','custom','/Capstone_Beta/Capstone_Client/uploads/custom/67ee2e5ae55ec_Foam.jpg','dsadsadsad','custom','/Capstone_Beta/Capstone_Client/uploads/custom/67ee2e5ae5715_Cover.jpg','dsadsadasdas','custom','/Capstone_Beta/Capstone_Client/uploads/custom/67ee2e5ae5841_Design.jpg','dsadsadasdasdas','custom','/Capstone_Beta/Capstone_Client/uploads/custom/67ee2e5ae5940_Tile.jpg','dsadsadasdasd','custom','/Capstone_Beta/Capstone_Client/uploads/custom/67ee2e5ae5a5d_Metal.jpg','dsadsadasdasdas',10,0,'2025-04-03 14:49:21','2025-04-03 14:49:21',19,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_customizations_temp` */

insert  into `tbl_customizations_temp`(`Temp_Customization_ID`,`User_ID`,`Furniture_Type`,`Furniture_Type_Additional_Info`,`Standard_Size`,`Desired_Size`,`Color`,`Color_Image_URL`,`Color_Additional_Info`,`Texture`,`Texture_Image_URL`,`Texture_Additional_Info`,`Wood_Type`,`Wood_Image_URL`,`Wood_Additional_Info`,`Foam_Type`,`Foam_Image_URL`,`Foam_Additional_Info`,`Cover_Type`,`Cover_Image_URL`,`Cover_Additional_Info`,`Design`,`Design_Image_URL`,`Design_Additional_Info`,`Tile_Type`,`Tile_Image_URL`,`Tile_Additional_Info`,`Metal_Type`,`Metal_Image_URL`,`Metal_Additional_Info`,`Order_Status`,`Request_Date`,`Last_Update`) values (14,'0001','bedframe','','Full XL 54x80 in.','','Weathered Oak',NULL,'','Semi Glossy',NULL,'','Plywood 1/8',NULL,'','Uratex',NULL,'','Velvet',NULL,'','Shabby Chic',NULL,'','Quartz',NULL,'','Tubular',NULL,'',0,'2025-04-03 14:13:03','2025-04-03 14:13:03');

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_preorder` */

insert  into `tbl_preorder`(`Preorder_ID`,`Product_ID`,`User_ID`,`Quantity`,`Total_Price`,`Preorder_Status`,`Order_Date`,`Product_Status`,`Progress_Pic_10`,`Progress_Pic_20`,`Progress_Pic_30`,`Progress_Pic_40`,`Progress_Pic_50`,`Progress_Pic_60`,`Progress_Pic_70`,`Progress_Pic_80`,`Progress_Pic_90`,`Progress_Pic_100`,`Stop_Reason`) values (1,3,'0001',1,'2000.00',10,'2025-04-03 09:30:07',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_prod_info` */

insert  into `tbl_prod_info`(`Product_ID`,`Product_Name`,`Description`,`Category`,`Sizes`,`Color`,`Stock`,`Assembly_Required`,`ImageURL`,`Price`,`Sold`,`DateAdded`,`LastUpdate`,`GLB_File_URL`,`product_type`) values (1,'Sofa','A comfortable and stylish seating option for your living room, perfect for relaxation and entertaining guests.','sofa','Sofa 3 seater - L: 7 ft // W: 3 ft // H: 3.5 ft','Brown','200','Yes','../uploads/product/sofa3.jpg,../uploads/product/sofa2.jpg,../uploads/product/sofa.jpg','2000','','2025-04-02 09:27:11','2025-04-02 09:27:11','../uploads/product/3d/Sofa(Commission).glb','readymade'),(2,'Bed','A cozy and supportive sleeping space designed for restful nights, available in various sizes and styles.','bedframe','Bed Frame - Full XL 54x80 in.','Blue','200','Yes','../uploads/product/bed 3.jpg,../uploads/product/bed 1.jpg,../uploads/product/bed 2.jpg','2000','','2025-04-02 09:27:50','2025-04-02 09:27:50','../uploads/product/3d/Bed(Commission).glb','readymade'),(3,'Dining Set','A functional and elegant table with matching chairs, ideal for family meals and gatherings.','salaset','Sala Set 9x9 ft.','Blue','0','Yes','../uploads/product/dining 1.jpg,../uploads/product/dining 2.jpg,../uploads/product/dining 3.jpg','2000','','2025-04-02 09:28:57','2025-04-02 09:28:57','../uploads/product/3d/Dining(Commission).glb','readymade'),(4,'Custom Chair Order','Custom order from request #1',NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-04-02 13:59:06','2025-04-02 13:59:06',NULL,'custom'),(5,'Custom Chair Order','Custom order from request #2',NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-04-02 14:23:20','2025-04-02 14:23:20',NULL,'custom'),(6,'Custom Table Order','Custom order from request #3',NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-04-02 14:25:30','2025-04-02 14:25:30',NULL,'custom'),(7,'Custom Table Order','Custom order from request #4',NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-04-02 14:26:37','2025-04-02 14:26:37',NULL,'custom'),(8,'Custom Chair Order','Custom order from request #5',NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-04-02 14:35:08','2025-04-02 14:35:08',NULL,'custom'),(9,'Custom Sala Set Order','Custom order from request #6',NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-04-02 14:35:51','2025-04-02 14:35:51',NULL,'custom'),(10,'Custom Sala Set Order','Custom order from request #7',NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-04-03 12:53:30','2025-04-03 12:53:30',NULL,'custom'),(11,'Custom Sala Set Order','Custom order from request #8',NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-04-03 12:54:50','2025-04-03 12:54:50',NULL,'custom'),(12,'Custom Sala Set Order','Custom order from request #9',NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-04-03 12:56:47','2025-04-03 12:56:47',NULL,'custom'),(13,'Custom Sala Set Order','Custom order from request #10',NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-04-03 12:58:47','2025-04-03 12:58:47',NULL,'custom'),(14,'Custom Bed Frame Order','Custom order from request #11',NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-04-03 13:32:21','2025-04-03 13:32:21',NULL,'custom'),(15,'Custom Bed Frame Order','Custom order from request #12',NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-04-03 13:32:58','2025-04-03 13:32:58',NULL,'custom'),(16,'Custom Table Order','Custom order from request #13',NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-04-03 13:55:56','2025-04-03 13:55:56',NULL,'custom'),(17,'Custom Bedframe Order','Custom order from request #14',NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-04-03 14:13:03','2025-04-03 14:13:03',NULL,'custom'),(18,'Custom Salaset Order','Custom order from request #15',NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-04-03 14:44:42','2025-04-03 14:44:42',NULL,'custom'),(19,'Custom salaset','Custom order from request #5','Custom Furniture',NULL,NULL,NULL,NULL,NULL,'',NULL,'2025-04-03 14:49:21','2025-04-03 14:49:21',NULL,'custom');

/*Table structure for table `tbl_progress` */

DROP TABLE IF EXISTS `tbl_progress`;

CREATE TABLE `tbl_progress` (
  `Progress_ID` int(11) NOT NULL AUTO_INCREMENT,
  `User_ID` varchar(150) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `Product_Name` varchar(255) NOT NULL,
  `Order_Type` varchar(100) NOT NULL,
  `Order_Status` int(3) NOT NULL,
  `Product_Status` int(3) NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_progress` */

insert  into `tbl_progress`(`Progress_ID`,`User_ID`,`Product_ID`,`Product_Name`,`Order_Type`,`Order_Status`,`Product_Status`,`Quantity`,`Total_Price`,`Date_Added`,`LastUpdate`,`Progress_Pic_20`,`Progress_Pic_30`,`Progress_Pic_40`,`Progress_Pic_50`,`Progress_Pic_60`,`Progress_Pic_70`,`Progress_Pic_80`,`Progress_Pic_90`,`Progress_Pic_100`,`Stop_Reason`,`Progress_Pic_10`) values (1,'0001',1,'Sofa','ready_made',50,90,1,'2000.00','2025-04-03 09:29:03','2025-04-03 14:08:17','../uploads/progress_pics/67edf191c99cb_sofa2.jpg','../uploads/progress_pics/67edf191c9ab5_sofa.jpg','../uploads/progress_pics/67edf191c9bcf_ck.png','../uploads/progress_pics/67edf191c9ef8_Foam.jpg','../uploads/progress_pics/67edf191ca012_OC 2.jpg','../uploads/progress_pics/67edf191ca0cb_Office Chair.jpg','../uploads/progress_pics/67edf191ca191_Cover.jpg','../uploads/progress_pics/67edf191ca2c0_bed 3.jpg','../uploads/progress_pics/67edf191ca39a_Design.jpg','typhoon','../uploads/progress_pics/67edf191c8f83_sofa3.jpg'),(3,'0001',19,'N/A','custom',20,20,1,'0.00','2025-04-03 14:49:21','2025-04-04 11:17:04',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL);

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_purchase_history` */

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

insert  into `tbl_ready_made_orders`(`ReadyMadeOrder_ID`,`Product_ID`,`User_ID`,`Quantity`,`Total_Price`,`Order_Status`,`Order_Date`,`Product_Status`,`Progress_Pic_10`,`Progress_Pic_20`,`Progress_Pic_30`,`Progress_Pic_40`,`Progress_Pic_50`,`Progress_Pic_60`,`Progress_Pic_70`,`Progress_Pic_80`,`Progress_Pic_90`,`Progress_Pic_100`,`Stop_Reason`) values (1,1,'0001',1,'2000.00',50,'2025-04-03 09:29:03',90,'../uploads/progress_pics/67edf191c8f83_sofa3.jpg','../uploads/progress_pics/67edf191c99cb_sofa2.jpg','../uploads/progress_pics/67edf191c9ab5_sofa.jpg','../uploads/progress_pics/67edf191c9bcf_ck.png','../uploads/progress_pics/67edf191c9ef8_Foam.jpg','../uploads/progress_pics/67edf191ca012_OC 2.jpg','../uploads/progress_pics/67edf191ca0cb_Office Chair.jpg','../uploads/progress_pics/67edf191ca191_Cover.jpg','../uploads/progress_pics/67edf191ca2c0_bed 3.jpg','../uploads/progress_pics/67edf191ca39a_Design.jpg','typhoon');

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_reviews` */

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

insert  into `tbl_user_info`(`User_ID`,`Last_Name`,`First_Name`,`Middle_Name`,`Home_Address`,`Email_Address`,`Mobile_Number`,`Status`,`Password`,`PicPath`,`reset_code`,`reset_code_expiry`) values ('0001','Mantua','Clarence','Badilla','388 Marlboro Country, San Vicente, Santa Rita, Pampanga','rence.b.m@gmail.com','09622100810','Active','$2y$10$IvXk3AOaDgxjWK23VBEy/eZkzJugIfKCQquiB8DASFBu6b9JBE5wa','uploads/user/0001_profile.jpg',NULL,NULL);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
