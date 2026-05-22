-- MySQL dump 10.13  Distrib 9.4.0, for macos15.4 (arm64)
--
-- Host: localhost    Database: mundoasiatico_dev
-- ------------------------------------------------------
-- Server version	9.4.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `brands`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `brands` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `brands_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brands`
--

LOCK TABLES `brands` WRITE;
/*!40000 ALTER TABLE `brands` DISABLE KEYS */;
INSERT IGNORE INTO `brands` VALUES (1,'Chery','chery',NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(2,'Great Wall','great-wall',NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(3,'MG','mg',NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(4,'Geely','geely',NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(5,'Changan','changan',NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(6,'Jac','jac',NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(7,'Foton','foton',NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(8,'Baic','baic',NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(9,'DFM','dfm',NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(10,'Hafei','hafei',NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(11,'Lifan','lifan',NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(12,'Zotye','zotye',NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(13,'Chevrolet','chevrolet',NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(14,'BYD','byd',NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(15,'Gac Gonow','gac-gonow',NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(16,'DFSK','dfsk',NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(17,'Maxus','maxus',NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(18,'Torch','torch',NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29');
/*!40000 ALTER TABLE `brands` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `car_model_product`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `car_model_product` (
  `product_id` bigint unsigned NOT NULL,
  `car_model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`product_id`,`car_model_id`),
  KEY `car_model_product_car_model_id_foreign` (`car_model_id`),
  CONSTRAINT `car_model_product_car_model_id_foreign` FOREIGN KEY (`car_model_id`) REFERENCES `car_models` (`id`) ON DELETE CASCADE,
  CONSTRAINT `car_model_product_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `car_model_product`
--

LOCK TABLES `car_model_product` WRITE;
/*!40000 ALTER TABLE `car_model_product` DISABLE KEYS */;
INSERT IGNORE INTO `car_model_product` VALUES (1,1),(5,1),(7,1),(10,1),(1,2),(1,3),(10,3),(1,4),(5,4),(7,4),(1,5),(1,6),(1,7),(7,7),(10,7),(1,8),(5,8),(7,8),(1,9),(1,10),(5,10),(1,11),(1,12),(1,13),(1,14),(1,15),(1,16),(1,17),(5,17),(7,17),(2,18),(5,18),(2,19),(3,20),(3,21),(3,22),(3,23),(4,23),(3,24),(4,24),(3,25),(5,25),(3,26),(5,26),(3,27),(3,28),(3,29),(9,29),(10,29),(4,30),(4,31),(4,32),(6,32),(4,33),(6,33),(4,34),(6,34),(4,35),(6,35),(4,36),(4,37),(4,38),(4,39),(4,40),(4,41),(4,42),(4,43),(4,44),(4,45),(4,46),(4,47),(4,48),(4,49),(4,50),(4,51),(4,52),(4,53),(4,54),(4,55),(4,56),(4,57),(4,58),(6,58),(4,59),(5,60),(5,61),(7,61),(5,62),(5,63),(7,63),(5,64),(5,65),(7,65),(5,66),(5,67),(9,67),(6,68),(6,69),(6,70),(6,71),(6,72),(6,73),(7,74),(7,75),(7,76),(7,77),(7,78),(7,79),(7,80);
/*!40000 ALTER TABLE `car_model_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `car_models`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `car_models` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand_id` bigint unsigned NOT NULL,
  `year_start` smallint unsigned DEFAULT NULL,
  `year_end` smallint unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `car_models_slug_unique` (`slug`),
  KEY `car_models_brand_id_foreign` (`brand_id`),
  CONSTRAINT `car_models_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `car_models`
--

LOCK TABLES `car_models` WRITE;
/*!40000 ALTER TABLE `car_models` DISABLE KEYS */;
INSERT IGNORE INTO `car_models` VALUES (1,'Tiggo 2','chery-tiggo-2',1,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(2,'IQ','chery-iq',1,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(3,'Grand Tiggo','chery-grand-tiggo',1,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(4,'Arrizo 3','chery-arrizo-3',1,2016,2017,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(5,'Haval H3','great-wall-haval-h3',2,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(6,'Haval H5','great-wall-haval-h5',2,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(7,'Beat','chery-beat',1,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(8,'Fulwin','chery-fulwin',1,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(9,'Destiny','chery-destiny',1,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(10,'Skin','chery-skin',1,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(11,'Wingle 6','great-wall-wingle-6',2,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(12,'ZS','mg-zs',3,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(13,'ZX','mg-zx',3,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(14,'350','mg-350',3,2013,2018,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(15,'Haval H6','great-wall-haval-h6',2,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(16,'GS','geely-gs',4,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(17,'Tiggo 2 Pro','chery-tiggo-2-pro',1,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(18,'Tiggo','chery-tiggo',1,2008,2013,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(19,'New Tiggo','chery-new-tiggo',1,2008,2013,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(20,'CS35 Plus','changan-cs35-plus',5,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(21,'CS55','changan-cs55',5,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(22,'CS55 Plus','changan-cs55-plus',5,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(23,'MD201','changan-md201',5,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(24,'MS201','changan-ms201',5,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(25,'Tiggo 3','chery-tiggo-3',1,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(26,'Tiggo 4','chery-tiggo-4',1,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(27,'S2','jac-s2',6,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(28,'Midi','foton-midi',7,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(29,'Plus','baic-plus',8,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(30,'Cargo Van','dfm-cargo-van',9,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(31,'MK','geely-mk',4,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(32,'New S100','changan-new-s100',5,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(33,'S200','changan-s200',5,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(34,'S100','changan-s100',5,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(35,'S300 Old','changan-s300-old',5,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(36,'Ruiyi','hafei-ruiyi',10,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(37,'Zhongi','hafei-zhongi',10,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(38,'J3 Sport VVT','jac-j3-sport-vvt',6,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(39,'320','lifan-320',11,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(40,'Hunter','zotye-hunter',12,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(41,'N300 Max','chevrolet-n300-max',13,2011,2013,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(42,'F0','byd-f0',14,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(43,'LC','geely-lc',4,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(44,'J2','jac-j2',6,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(45,'New CK','geely-new-ck',4,2010,2012,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(46,'G3','byd-g3',14,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(47,'MK Sedan','geely-mk-sedan',4,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(48,'CV1','changan-cv1',5,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(49,'Cargo Truck Serie V','dfsk-cargo-truck-serie-v',16,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(50,'CK Old','geely-ck-old',4,2008,2010,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(51,'New Midi Truck','foton-new-midi-truck',7,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(52,'Cargo Van 1.0','dfsk-cargo-van-10',16,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(53,'Cargo Van 1.1','dfsk-cargo-van-11',16,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(54,'New N300 Max','chevrolet-new-n300-max',13,2014,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(55,'M201','changan-m201',5,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(56,'CS1','changan-cs1',5,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(57,'IQ 1.1','chery-iq-11',1,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(58,'Midi Cargo','foton-midi-cargo',7,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(59,'TM3','foton-tm3',7,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(60,'Tiggo 7','chery-tiggo-7',1,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(61,'Arrizo 5','chery-arrizo-5',1,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(62,'X60','lifan-x60',11,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(63,'New Tiggo 3','chery-new-tiggo-3',1,2020,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(64,'Alsvin','changan-alsvin',5,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(65,'K60','chery-k60',1,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(66,'EC7','geely-ec7',4,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(67,'X35','baic-x35',8,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(68,'Cargo Van 1.3','dfm-cargo-van-13',9,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(69,'New S200','changan-new-s200',5,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(70,'New S300','changan-new-s300',5,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(71,'New CK','changan-new-ck',5,2010,2012,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(72,'Midi Truck','foton-midi-truck',7,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(73,'Cargo Van 1.0','dfm-cargo-van-10',9,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(74,'Fulwin 2','chery-fulwin-2',1,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(75,'Face','chery-face',1,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(76,'S21','chery-s21',1,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(77,'Tiggo 7 Pro','chery-tiggo-7-pro',1,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(78,'T60','maxus-t60',17,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(79,'T90','maxus-t90',17,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(80,'V90','maxus-v90',17,NULL,NULL,1,'2026-04-15 03:01:29','2026-04-15 03:01:29');
/*!40000 ALTER TABLE `car_models` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT IGNORE INTO `categories` VALUES (1,'Encendido','encendido','categories/01KP8MH2H5E3VEGJ48YCJ6TPGM.jpeg','⚡',1,'2026-04-15 03:01:29','2026-04-15 17:15:43'),(2,'Filtros','filtros','categories/01KP8MHFC6F7CRREPAB1EG5K7W.jpeg','🔩',1,'2026-04-15 03:01:29','2026-04-15 17:15:56'),(3,'Inyección','inyeccion','categories/01KP8PWB5M1N6D3HMZJS5FRTH2.png','💉',1,'2026-04-15 03:01:29','2026-04-15 17:56:49'),(4,'Frenos','frenos','categories/01KP8PWSV8QYBB99335NMW33CQ.jpeg','🛑',1,'2026-04-15 03:01:29','2026-04-15 17:57:05'),(5,'Sensores','sensores','categories/01KP8PXA8MMZXQG0H24X7F6DPP.jpeg','📡',1,'2026-04-15 03:01:29','2026-04-15 17:57:21');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupons`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coupons` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('percent','fixed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` int NOT NULL,
  `min_amount` int NOT NULL DEFAULT '0',
  `max_uses` int DEFAULT NULL,
  `used_count` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `coupons_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupons`
--

LOCK TABLES `coupons` WRITE;
/*!40000 ALTER TABLE `coupons` DISABLE KEYS */;
/*!40000 ALTER TABLE `coupons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leads`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leads` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `patente` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `status` enum('new','contacted','converted','lost') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leads`
--

LOCK TABLES `leads` WRITE;
/*!40000 ALTER TABLE `leads` DISABLE KEYS */;
/*!40000 ALTER TABLE `leads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `marketing_scripts`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `marketing_scripts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'custom',
  `code` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `placement` enum('head','body') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'head',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marketing_scripts`
--

LOCK TABLES `marketing_scripts` WRITE;
/*!40000 ALTER TABLE `marketing_scripts` DISABLE KEYS */;
/*!40000 ALTER TABLE `marketing_scripts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int unsigned NOT NULL,
  `unit_price` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT IGNORE INTO `order_items` VALUES (1,1,9,'TEST','1234',1,1000,'2026-05-08 00:18:55','2026-05-08 00:18:55'),(2,2,9,'TEST','1234',1,1000,'2026-05-08 00:19:25','2026-05-08 00:19:25'),(3,3,9,'TEST','1234',1,1000,'2026-05-08 00:25:56','2026-05-08 00:25:56'),(4,4,9,'TEST','1234',1,1000,'2026-05-08 23:28:32','2026-05-08 23:28:32');
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_rut` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','paid','failed','processing','shipped','delivered','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `abandoned_reminder_sent` tinyint(1) NOT NULL DEFAULT '0',
  `total_amount` bigint unsigned NOT NULL,
  `shipping_type` enum('retiro_stgo','retiro_pm','starken') COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_address` json DEFAULT NULL,
  `tracking_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_carrier` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipped_at` timestamp NULL DEFAULT NULL,
  `coupon_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount_amount` int NOT NULL DEFAULT '0',
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `banchile_request_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banchile_process_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transbank_token` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transbank_authorization_code` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transbank_transaction_date` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `orders_user_id_foreign` (`user_id`),
  KEY `orders_transbank_token_index` (`transbank_token`),
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT IGNORE INTO `orders` VALUES (1,NULL,'Sebastian Rodriguez','contacto@srweb.cl','+56953810178','176258187','failed',0,1000,'retiro_stgo',NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-08 00:18:55','2026-05-08 00:18:55'),(2,NULL,'Sebastian','contacto@srweb.cl','+56 9 53810178','17625818-7','delivered',0,1000,'retiro_stgo',NULL,'99999999','starken','2026-05-08 19:19:45',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-08 00:19:25','2026-05-08 23:24:52'),(3,NULL,'Sebastian','contacto@srweb.cl','+56 9 53810178','17625818-7','delivered',0,1000,'retiro_stgo',NULL,'XXXX12345','starken','2026-05-09 03:17:27',NULL,0,NULL,'1275','https://checkout.test.banchilepagos.cl/spa/session/1275/6e82c151db435f36dafea2fb62500aee','ORD-3-1778185556',NULL,NULL,'2026-05-08 00:25:56','2026-05-08 10:03:52'),(4,NULL,'Sebastian Rodriguez','sebastian.rodriguezmilla@gmail.com','+56983806054','17625818-7','pending',0,1000,'retiro_stgo',NULL,NULL,NULL,NULL,NULL,0,NULL,'1308','https://checkout.test.banchilepagos.cl/spa/session/1308/d008d886dfe23187a0113c3c76fe0cd8','ORD-4-1778268512',NULL,NULL,'2026-05-08 23:28:31','2026-05-08 23:28:32');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `popups`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `popups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `button_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `button_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delay_seconds` int NOT NULL DEFAULT '3',
  `target_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Vacío para todas las páginas',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `popups`
--

LOCK TABLES `popups` WRITE;
/*!40000 ALTER TABLE `popups` DISABLE KEYS */;
/*!40000 ALTER TABLE `popups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `regular_price` bigint unsigned NOT NULL,
  `wholesale_price` bigint unsigned NOT NULL,
  `stock` int unsigned NOT NULL DEFAULT '0',
  `stock_reserved` int NOT NULL DEFAULT '0',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gallery` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `category_id` bigint unsigned NOT NULL,
  `brand_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_sku_unique` (`sku`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  KEY `products_category_id_foreign` (`category_id`),
  KEY `products_brand_id_foreign` (`brand_id`),
  CONSTRAINT `products_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT IGNORE INTO `products` VALUES (1,'111233707100','Bujías Juego Iridium','bujias-juego-iridium-7100','Bujías punta Iridium de alta performance. Mayor durabilidad y eficiencia de combustión.',NULL,NULL,40000,32000,6,0,NULL,NULL,1,1,1,18,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(2,'111301109111','Filtro de Aire','filtro-de-aire-9111','Filtro aire 2.0 Tiggo 250x225x46. Compatible con motores 1.6 y 2.0.',NULL,NULL,100000,80000,10,0,NULL,NULL,1,1,2,NULL,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(3,'191181117010','Filtro de Bencina','filtro-de-bencina-7010','Filtro de combustible bencina. Alta filtración para motores de inyección electrónica.',NULL,NULL,100000,80000,10,0,NULL,NULL,1,1,2,NULL,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(4,'291101135011','Sensor IAC','sensor-iac-5011','Sensor IAC (Idle Air Control). Controla el ralentí del motor en condición de reposo.',NULL,NULL,100000,80000,5,0,NULL,NULL,1,0,3,NULL,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(5,'111173501080','Pastillas de Freno Delantera','pastillas-de-freno-delantera-1080','Pastillas de freno delantera certificadas. Alto rendimiento en frenado y baja generación de polvo.',NULL,NULL,100000,80000,3,0,NULL,NULL,1,0,4,NULL,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(6,'111101205110','Sensor Oxígeno','sensor-oxigeno-5110','Sensor de oxígeno (sonda lambda). Mide el contenido de O2 en los gases de escape para optimizar la mezcla aire-combustible.',NULL,NULL,100000,80000,3,0,NULL,NULL,1,0,5,NULL,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(7,'111171012010','Filtro de Aceite','filtro-de-aceite-2010','Filtro de aceite de motor. Elimina partículas e impurezas para proteger el motor y prolongar su vida útil.',NULL,NULL,100000,80000,5,0,NULL,NULL,1,0,2,NULL,'2026-04-15 03:01:29','2026-04-15 03:01:29'),(9,'1234','TEST','test','<p>TEST</p>',NULL,NULL,1000,900,100,4,'products/01KP8KKPT39VVY653PA9ZJX825.jpeg','[\"products/01KP8P7CKYSFFB6F49TC0954QD.jpeg\", \"products/01KP8P7CKZZV99DGDV3XD0JJ0A.jpeg\"]',1,1,1,NULL,'2026-04-15 16:59:41','2026-05-08 23:28:32'),(10,'B-1234','TEST 2','test-2','<p>TEST 2</p>',NULL,NULL,10000,9000,100,0,'products/01KP8REBV8MF85FGTXKWSQ0AJD.jpeg','[\"products/01KP8REBV9B8BJRNFXWYNCT655.jpeg\", \"products/01KP8REBVAEX12TMR5SVR0T9BP.jpeg\"]',1,0,2,NULL,'2026-04-15 18:24:09','2026-04-15 18:24:09');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT IGNORE INTO `settings` VALUES (1,'promo_bar_text','SUCURSALES EN SANTIAGO Y PUERTO MONTT','Texto de la Barra Promocional','text','2026-05-08 10:24:49','2026-05-08 10:42:23'),(2,'promo_bar_enabled','1','Mostrar Barra Promocional','boolean','2026-05-08 10:24:49','2026-05-08 10:42:23'),(3,'promo_bar_color','#0d0d0d','Color de Fondo de la Barra','color','2026-05-08 10:32:43','2026-05-08 10:42:23'),(4,'promo_bar_text_color','#faff00','Color del Texto','color','2026-05-08 10:37:14','2026-05-08 10:42:23'),(5,'promo_bar_url',NULL,'Enlace (URL)','text','2026-05-08 10:37:14','2026-05-08 10:42:23'),(6,'promo_bar_icon','heroicon-o-truck','Icono','text','2026-05-08 10:37:14','2026-05-08 10:42:23'),(7,'promo_bar_font_weight','bold','Grosor de Fuente','text','2026-05-08 10:37:14','2026-05-08 10:42:23'),(8,'promo_bar_start_at',NULL,'Fecha de Inicio','datetime','2026-05-08 10:37:14','2026-05-08 10:42:23'),(9,'promo_bar_end_at',NULL,'Fecha de Fin','datetime','2026-05-08 10:37:14','2026-05-08 10:42:23'),(10,'promo_bar_animate','0','Animar (Pulso)','boolean','2026-05-08 10:37:14','2026-05-08 10:42:23');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_addresses`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_addresses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Principal',
  `region` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `street` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apto` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_addresses_user_id_foreign` (`user_id`),
  CONSTRAINT `user_addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_addresses`
--

LOCK TABLES `user_addresses` WRITE;
/*!40000 ALTER TABLE `user_addresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rut` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `patente` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','customer','wholesale') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_rut_unique` (`rut`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT IGNORE INTO `users` VALUES (1,'Antonio Yañez','admin@mundoasiatico.cl',NULL,NULL,NULL,NULL,'$2y$12$QSZh8tDdgj1tTc1vi6mCZuBOAIE9IUCME82ExXDV0lpLaZCTk9qGS','admin',NULL,'2026-04-15 05:17:38','2026-05-08 10:56:19'),(2,'Sebastian Rodriguez','contacto@srweb.cl',NULL,NULL,NULL,NULL,'$2y$12$doPrZyiwPHT4/2u6jw1G3.DYMrcBBZS.o0fAsBnGtvqRTluW7mnVi','admin',NULL,'2026-05-13 17:36:03','2026-05-13 17:36:03');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-13 14:19:32
