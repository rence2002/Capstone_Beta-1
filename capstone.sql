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

insert  into `tbl_admin_info`(`Admin_ID`,`Last_Name`,`First_Name`,`Middle_Name`,`Home_Address`,`Email_Address`,`Mobile_Number`,`Status`,`Password`,`PicPath`,`verification_code`,`verification_code_expiry`) values ('c5PtVnM9ap','Mantua','Clarence','Badilla','388 Marlboro Country, San Vicente, Santa Rita, Pampanga','rence.b.m@gmail.com','+639622100810','Active','$2y$10$P/e1LC13QHtsnaoG48jsHu7sP0leD32cblL4RLO1ggk1DOGtkXKpS','uploads/admin/profile.jpg',NULL,NULL);

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
  `Payment_Status` enum('downpayment_paid','fully_paid') NOT NULL DEFAULT 'downpayment_paid',
  `Tracking_Number` varchar(255) DEFAULT NULL,
  `Total_Price` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`Customization_ID`),
  KEY `User_ID` (`User_ID`),
  CONSTRAINT `tbl_customizations_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `tbl_user_info` (`User_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
  `Request_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Last_Update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`Temp_Customization_ID`),
  KEY `User_ID` (`User_ID`),
  CONSTRAINT `tbl_customizations_temp_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `tbl_user_info` (`User_ID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_customizations_temp` */

insert  into `tbl_customizations_temp`(`Temp_Customization_ID`,`User_ID`,`Furniture_Type`,`Furniture_Type_Additional_Info`,`Standard_Size`,`Desired_Size`,`Color`,`Color_Image_URL`,`Color_Additional_Info`,`Texture`,`Texture_Image_URL`,`Texture_Additional_Info`,`Wood_Type`,`Wood_Image_URL`,`Wood_Additional_Info`,`Foam_Type`,`Foam_Image_URL`,`Foam_Additional_Info`,`Cover_Type`,`Cover_Image_URL`,`Cover_Additional_Info`,`Design`,`Design_Image_URL`,`Design_Additional_Info`,`Tile_Type`,`Tile_Image_URL`,`Tile_Additional_Info`,`Metal_Type`,`Metal_Image_URL`,`Metal_Additional_Info`,`Request_Date`,`Last_Update`) values (1,'user_680dbe07d942d','table','','Table 8 seater - L: 8 ft. // W: 41 in. // H: 30 in.','','custom','/Capstone_Beta/uploads/custom/680dccb4a9ac5_Color.png','dsadasd','Smooth',NULL,'','custom','/Capstone_Beta/uploads/custom/680dccb4aa008_Wood.jpg','Mahogany','Uratex',NULL,'','Korean Leather',NULL,'','Shabby Chic',NULL,'','',NULL,'','Tubular',NULL,'','2025-04-27 14:20:36','2025-04-27 14:20:36'),(2,'user_680dbe07d942d','salaset','','Sala Set 9x9 ft.','','Black',NULL,'','Duco Finish',NULL,'','custom','/Capstone_Beta/uploads/custom/680dcd0abb0f7_Wood.jpg','Mahogany','Uratex',NULL,'','Velvet',NULL,'','Asian Inspired',NULL,'','',NULL,'','Tubular',NULL,'','2025-04-27 14:22:02','2025-04-27 14:22:02');

/*Table structure for table `tbl_order_request` */

DROP TABLE IF EXISTS `tbl_order_request`;

CREATE TABLE `tbl_order_request` (
  `Request_ID` int(11) NOT NULL AUTO_INCREMENT,
  `User_ID` varchar(150) NOT NULL,
  `Product_ID` int(11) DEFAULT NULL,
  `Customization_ID` int(11) DEFAULT NULL,
  `Quantity` int(11) NOT NULL,
  `Order_Type` enum('ready_made','pre_order','custom') NOT NULL DEFAULT 'ready_made',
  `Total_Price` decimal(10,2) NOT NULL,
  `Payment_Status` enum('downpayment_paid','fully_paid','Pending') NOT NULL DEFAULT 'Pending',
  `Payment_Reference_Number` varchar(255) DEFAULT NULL,
  `Request_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Processed` tinyint(1) DEFAULT 0,
  `Submission_Attempts` int(11) DEFAULT 0,
  PRIMARY KEY (`Request_ID`),
  KEY `Product_ID` (`Product_ID`),
  KEY `User_ID` (`User_ID`),
  KEY `Customization_ID` (`Customization_ID`),
  CONSTRAINT `tbl_order_request_ibfk_1` FOREIGN KEY (`Product_ID`) REFERENCES `tbl_prod_info` (`Product_ID`) ON DELETE CASCADE,
  CONSTRAINT `tbl_order_request_ibfk_2` FOREIGN KEY (`User_ID`) REFERENCES `tbl_user_info` (`User_ID`) ON DELETE CASCADE,
  CONSTRAINT `tbl_order_request_ibfk_3` FOREIGN KEY (`Customization_ID`) REFERENCES `tbl_customizations_temp` (`Temp_Customization_ID`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_order_request` */

/*Table structure for table `tbl_preorder` */

DROP TABLE IF EXISTS `tbl_preorder`;

CREATE TABLE `tbl_preorder` (
  `Preorder_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Product_ID` int(11) NOT NULL,
  `User_ID` varchar(150) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `Total_Price` decimal(10,2) NOT NULL,
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
  `Payment_Status` enum('downpayment_paid','fully_paid') NOT NULL DEFAULT 'downpayment_paid',
  `Tracking_Number` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`Preorder_ID`),
  KEY `Product_ID` (`Product_ID`),
  KEY `User_ID` (`User_ID`),
  CONSTRAINT `tbl_preorder_info_ibfk_1` FOREIGN KEY (`Product_ID`) REFERENCES `tbl_prod_info` (`Product_ID`),
  CONSTRAINT `tbl_preorder_info_ibfk_2` FOREIGN KEY (`User_ID`) REFERENCES `tbl_user_info` (`User_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_prod_info` */

insert  into `tbl_prod_info`(`Product_ID`,`Product_Name`,`Description`,`Category`,`Sizes`,`Color`,`Stock`,`Assembly_Required`,`ImageURL`,`Price`,`Sold`,`DateAdded`,`LastUpdate`,`GLB_File_URL`,`product_type`) values (1,'Sofa','A comfortable, upholstered seating furniture designed for lounging or relaxing, typically featuring plush cushions and a backrest, perfect for living rooms or family spaces.','','L Shape Sofa 6-7 seater - L: 9 ft // W: 3 ft // H: 3.5 ft','Brown','200','Yes','uploads/product/images/SofaSet1.jpg,uploads/product/images/SofaSet2.jpeg,uploads/product/images/SofaSet1.jpg,uploads/product/images/SofaSet2.jpeg','2000','','2025-04-27 12:05:51','2025-04-27 14:19:21','uploads/product/3d/Sofa.glb','readymade'),(2,'Bed',' A piece of furniture primarily used for sleeping, consisting of a mattress supported by a frame, offering comfort and support for rest and relaxation.','','Bed Frame - King 76x80 in.','White','199','Yes','uploads/product/images/BedFrame1.jpg,uploads/product/images/BedFrame2.jpg,uploads/product/images/BedFrame1.jpg,uploads/product/images/BedFrame2.jpg','1400','','2025-04-27 12:18:10','2025-04-27 15:33:33','uploads/product/3d/Bed.glb','readymade'),(3,'Dining Set','A coordinated furniture set designed for dining spaces, typically including a table and chairs, offering a stylish and functional solution for enjoying meals with family and friends.','','Dining Set 8 seater - L: 8 ft // W: 4 ft // H: 30 in.','Black','0','Yes','uploads/product/images/DiningSet1.jpg,uploads/product/images/DiningSet2.jpg,uploads/product/images/DiningSet3.jpg,uploads/product/images/DiningSet1.jpg,uploads/product/images/DiningSet2.jpg,uploads/product/images/DiningSet3.jpg','1400','','2025-04-27 12:25:07','2025-04-27 14:52:13','uploads/product/3d/Dining.glb','readymade'),(4,'Custom Table Order','Custom order from request #1',NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-04-27 14:20:36','2025-04-27 14:20:36',NULL,'custom'),(5,'Custom Salaset Order','Custom order from request #2',NULL,NULL,NULL,NULL,NULL,NULL,'0.00',NULL,'2025-04-27 14:22:02','2025-04-27 14:22:02',NULL,'custom');

/*Table structure for table `tbl_progress` */

DROP TABLE IF EXISTS `tbl_progress`;

CREATE TABLE `tbl_progress` (
  `Progress_ID` int(11) NOT NULL AUTO_INCREMENT,
  `User_ID` varchar(150) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `Product_Name` varchar(255) NOT NULL,
  `Order_Type` varchar(100) NOT NULL,
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
  `Tracking_Number` varchar(255) DEFAULT NULL,
  `Progress_Pic_10` varchar(255) DEFAULT NULL,
  `Order_Received` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`Progress_ID`),
  KEY `User_ID` (`User_ID`),
  KEY `Product_ID` (`Product_ID`),
  CONSTRAINT `tbl_progress_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `tbl_user_info` (`User_ID`) ON DELETE CASCADE,
  CONSTRAINT `tbl_progress_ibfk_2` FOREIGN KEY (`Product_ID`) REFERENCES `tbl_prod_info` (`Product_ID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_progress` */

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
  `Product_Status` int(3) NOT NULL DEFAULT 0,
  PRIMARY KEY (`Purchase_ID`),
  KEY `User_ID` (`User_ID`),
  KEY `Product_ID` (`Product_ID`),
  CONSTRAINT `tbl_purchase_history_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `tbl_user_info` (`User_ID`) ON DELETE CASCADE,
  CONSTRAINT `tbl_purchase_history_ibfk_2` FOREIGN KEY (`Product_ID`) REFERENCES `tbl_prod_info` (`Product_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_purchase_history` */

insert  into `tbl_purchase_history`(`Purchase_ID`,`User_ID`,`Product_ID`,`Product_Name`,`Quantity`,`Total_Price`,`Order_Type`,`Purchase_Date`,`Product_Status`) values (1,'user_680dbe07d942d',2,'Bed',1,'1400.00','ready_made','2025-04-27 15:59:14',100);

/*Table structure for table `tbl_ready_made_orders` */

DROP TABLE IF EXISTS `tbl_ready_made_orders`;

CREATE TABLE `tbl_ready_made_orders` (
  `ReadyMadeOrder_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Product_ID` int(11) NOT NULL,
  `User_ID` varchar(150) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `Total_Price` decimal(10,2) NOT NULL,
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
  `Payment_Status` enum('downpayment_paid','fully_paid') NOT NULL DEFAULT 'downpayment_paid',
  `Tracking_Number` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ReadyMadeOrder_ID`),
  KEY `Product_ID` (`Product_ID`),
  KEY `User_ID` (`User_ID`),
  CONSTRAINT `tbl_ready_made_orders_ibfk_1` FOREIGN KEY (`Product_ID`) REFERENCES `tbl_prod_info` (`Product_ID`),
  CONSTRAINT `tbl_ready_made_orders_ibfk_2` FOREIGN KEY (`User_ID`) REFERENCES `tbl_user_info` (`User_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_ready_made_orders` */

insert  into `tbl_ready_made_orders`(`ReadyMadeOrder_ID`,`Product_ID`,`User_ID`,`Quantity`,`Total_Price`,`Order_Date`,`Product_Status`,`Progress_Pic_10`,`Progress_Pic_20`,`Progress_Pic_30`,`Progress_Pic_40`,`Progress_Pic_50`,`Progress_Pic_60`,`Progress_Pic_70`,`Progress_Pic_80`,`Progress_Pic_90`,`Progress_Pic_100`,`Stop_Reason`,`Payment_Status`,`Tracking_Number`) values (1,2,'user_680dbe07d942d',1,'1400.00','2025-04-27 15:33:33',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'fully_paid',NULL);

/*Table structure for table `tbl_reviews` */

DROP TABLE IF EXISTS `tbl_reviews`;

CREATE TABLE `tbl_reviews` (
  `Review_ID` int(11) NOT NULL AUTO_INCREMENT,
  `User_ID` varchar(150) NOT NULL,
  `Product_ID` int(11) DEFAULT NULL,
  `Purchase_ID` int(11) DEFAULT NULL,
  `Rating` int(1) NOT NULL,
  `Review_Text` text DEFAULT NULL,
  `Review_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `PicPath` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`Review_ID`),
  KEY `User_ID` (`User_ID`),
  KEY `tbl_reviews_ibfk_2` (`Product_ID`),
  KEY `tbl_reviews_ibfk_3` (`Purchase_ID`),
  CONSTRAINT `tbl_reviews_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `tbl_user_info` (`User_ID`) ON DELETE CASCADE,
  CONSTRAINT `tbl_reviews_ibfk_2` FOREIGN KEY (`Product_ID`) REFERENCES `tbl_prod_info` (`Product_ID`) ON DELETE SET NULL,
  CONSTRAINT `tbl_reviews_ibfk_3` FOREIGN KEY (`Purchase_ID`) REFERENCES `tbl_purchase_history` (`Purchase_ID`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_reviews` */

insert  into `tbl_reviews`(`Review_ID`,`User_ID`,`Product_ID`,`Purchase_ID`,`Rating`,`Review_Text`,`Review_Date`,`PicPath`) values (1,'user_680dbe07d942d',2,NULL,5,'ewqeqwewqeq','2025-04-27 16:00:45','[\"uploads/reviews/680de42dddcb8_Tile.jpg\"]');

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
  `Valid_ID_Path` varchar(255) DEFAULT NULL,
  `ID_Verification_Status` enum('Valid','Invalid','Unverified') DEFAULT 'Unverified',
  `reset_code` int(11) DEFAULT NULL,
  `reset_code_expiry` datetime DEFAULT NULL,
  PRIMARY KEY (`User_ID`),
  UNIQUE KEY `UQ_Email_Address` (`Email_Address`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `tbl_user_info` */

insert  into `tbl_user_info`(`User_ID`,`Last_Name`,`First_Name`,`Middle_Name`,`Home_Address`,`Email_Address`,`Mobile_Number`,`Status`,`Password`,`PicPath`,`Valid_ID_Path`,`ID_Verification_Status`,`reset_code`,`reset_code_expiry`) values ('user_680dbe07d942d','Mantua','Clarence','Badilla','388 Marlboro Country, San Vicente, Santa Rita, Pampanga','rence.b.m@gmail.com','09622100810','Active','$2y$10$VGukOCskr4k4Zx7elJpoG.k8WkY.A3MxiHqv/vc9dbguJ5Q0pkN6q','uploads/user/user_680dbe07d942d_profile.jpg','uploads/user/validid/user_680dbe07d942d_validid.png','Valid',NULL,NULL);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
