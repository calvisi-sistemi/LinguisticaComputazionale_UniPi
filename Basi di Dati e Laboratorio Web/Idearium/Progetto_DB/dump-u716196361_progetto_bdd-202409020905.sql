-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Linux (x86_64)
--
-- Host: calvisisistemi.it    Database: u716196361_progetto_bdd
-- ------------------------------------------------------
-- Server version	10.11.8-MariaDB-cll-lve

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `Autori`
--

DROP TABLE IF EXISTS `Autori`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Autori` (
  `id_autore` varchar(200) NOT NULL,
  `id_blog` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_autore`,`id_blog`),
  KEY `idx_id_autore` (`id_autore`) USING BTREE,
  KEY `idx_id_blog` (`id_blog`) USING BTREE,
  CONSTRAINT `Autori_ibfk_1` FOREIGN KEY (`id_autore`) REFERENCES `Utenti` (`nome_utente`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_autori_blog` FOREIGN KEY (`id_blog`) REFERENCES `Blog` (`id_blog`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Elenco degli autori (dunque non amministratori) dei blog. Quale utente può scrivere su quale blog senza esserne amministratore.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Autori`
--

LOCK TABLES `Autori` WRITE;
/*!40000 ALTER TABLE `Autori` DISABLE KEYS */;
INSERT INTO `Autori` VALUES ('mariorossi',57),('mariorossi',73);
/*!40000 ALTER TABLE `Blog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Categoria`
--

DROP TABLE IF EXISTS `Categoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Categoria` (
  `nome_categoria` varchar(100) NOT NULL,
  PRIMARY KEY (`nome_categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Elenco delle categorie';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Categoria`
--

LOCK TABLES `Categoria` WRITE;
/*!40000 ALTER TABLE `Categoria` DISABLE KEYS */;
INSERT INTO `Categoria` VALUES ('Acquario'),('Altri animali'),('Animali'),('Arte e Cultura'),('Astronomia'),('Atletica leggera'),('Basket'),('Biologia'),('Blog Personale'),('Calcio'),('Cani'),('Cardiologia'),('Chimica'),('Chirurgia'),('Cinema'),('Corsi speciali'),('Criptovalute'),('CyberSec'),('Dottorato e specializzazione'),('Economia'),('Economia globalizzata'),('Elementari'),('Elettronica'),('Finanza'),('Fisica'),('Gatti'),('Genetica'),('Geologia'),('Informatica'),('Istruzione'),('Letteratura e critica letteraria'),('Mangiare sano'),('Matematica'),('Medicina generale'),('Medie'),('Neurologia'),('Nuoto'),('Olimpiadi'),('Paleontologia'),('Pallavolo'),('Pittura'),('Risparmio personale'),('Salute e Benessere'),('Scienza'),('Scultura'),('Sport'),('Storia delle arti'),('Superiori'),('Teatro'),('Tecnologia'),('Telecomunicazioni'),('Telefonia'),('Tennis'),('Terrario'),('Università'),('Veterinaria'),('Viaggi'),('Viaggi di lusso'),('Viaggi in ostello'),('Viaggiare in bicicletta');
/*!40000 ALTER TABLE `Categoria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Commenti`
--

DROP TABLE IF EXISTS `Commenti`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Commenti` (
  `id_commento` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nome_autore_commento` varchar(200) NOT NULL,
  `id_post_commentato` int(20) unsigned NOT NULL,
  `contenuto` text NOT NULL,
  `creazione_commento` datetime NOT NULL DEFAULT current_timestamp(),
  `ultima_modifica` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_commento`),
  KEY `fk_commento_utente` (`nome_autore_commento`),
  KEY `fk_commento_post` (`id_post_commentato`),
  CONSTRAINT `fk_commento_post` FOREIGN KEY (`id_post_commentato`) REFERENCES `Post` (`id_post`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_commento_utente` FOREIGN KEY (`nome_autore_commento`) REFERENCES `Utenti` (`nome_utente`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Insieme dei commenti con dati relativi';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Commenti`
--

LOCK TABLES `Commenti` WRITE;
/*!40000 ALTER TABLE `Commenti` DISABLE KEYS */;
INSERT INTO `Commenti` VALUES (66,'zero',84,'irijrijtgijgtij','2024-09-01 17:32:00','2024-09-01 17:32:00'),(68,'lino',80,'Luca','2024-09-01 18:50:40','2024-09-01 18:50:40'),(69,'lino',86,'luca l','2024-09-01 19:13:09','2024-09-02 06:36:52');
/*!40000 ALTER TABLE `FeedbackPost` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'IGNORE_SPACE,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`u716196361_bdd_project`@`%`*/ /*!50003 TRIGGER calcolo_upvotes_totali_utente
AFTER INSERT ON FeedbackPost
FOR EACH ROW
BEGIN
  -- Dichiarazione delle variabili
  DECLARE total_upvotes INT;
  DECLARE author_of_the_upvoted_post VARCHAR(200); -- Aggiunta della dichiarazione per la variabile
  
  -- Verifica se tipo_feedback è 1 (upvote)
  IF NEW.tipo_feedback = 1 THEN
    -- Ottenere il nome dell'autore del post
    SELECT nome_autore_post INTO author_of_the_upvoted_post
    FROM Post
    WHERE id_post = NEW.id_post_riferimento;

    -- Calcolare il totale degli upvotes ricevuti per l'autore del post
    SELECT COUNT(*) INTO total_upvotes
    FROM FeedbackPost
    JOIN Post ON FeedbackPost.id_post_riferimento = Post.id_post
    WHERE Post.nome_autore_post = author_of_the_upvoted_post
    AND FeedbackPost.tipo_feedback = 1;

    -- Aggiornare la colonna UpvotesRicevuti per l'utente
    UPDATE Utenti
    SET UpvotesRicevuti = total_upvotes
    WHERE nome_utente = author_of_the_upvoted_post;
  END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `Iscrizioni`
--

DROP TABLE IF EXISTS `Iscrizioni`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Iscrizioni` (
  `nome_utente` varchar(200) NOT NULL,
  `id_blog` int(10) unsigned NOT NULL,
  PRIMARY KEY (`nome_utente`,`id_blog`),
  KEY `FK_BlogID` (`id_blog`),
  CONSTRAINT `FK_BlogID` FOREIGN KEY (`id_blog`) REFERENCES `Blog` (`id_blog`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_Username` FOREIGN KEY (`nome_utente`) REFERENCES `Utenti` (`nome_utente`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Iscrizioni`
--

LOCK TABLES `Iscrizioni` WRITE;
/*!40000 ALTER TABLE `Iscrizioni` DISABLE KEYS */;
INSERT INTO `Iscrizioni` VALUES ('lino',74),('mariorossi',74),('zero',55),('zero',73),('zero',74);
/*!40000 ALTER TABLE `Iscrizioni` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Post`
--

DROP TABLE IF EXISTS `Post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Post` (
  `id_post` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `titolo_post` varchar(200) NOT NULL,
  `nome_autore_post` varchar(200) NOT NULL,
  `id_blog_appartenenza` int(10) unsigned NOT NULL COMMENT 'ID del blog di appartenenza del post',
  `creazione` datetime NOT NULL DEFAULT current_timestamp(),
  `ultima_modifica` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `contenuto_post` text NOT NULL COMMENT 'Contenuto del post in Markdown',
  `immagine_post` varchar(300) DEFAULT NULL COMMENT 'Percorso di un''immagine eventualmente allegata al post. La possibilità di allegare immagini è una delle caratteristiche premium.',
  PRIMARY KEY (`id_post`),
  KEY `idx_blog_appartenenza` (`id_blog_appartenenza`) USING BTREE,
  KEY `idx_autore` (`nome_autore_post`) USING BTREE,
  CONSTRAINT `fk_post_blog` FOREIGN KEY (`id_blog_appartenenza`) REFERENCES `Blog` (`id_blog`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_post_utente` FOREIGN KEY (`nome_autore_post`) REFERENCES `Utenti` (`nome_utente`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Elenco dei posto con il relativo contenuto';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Post`
--

LOCK TABLES `Post` WRITE;
/*!40000 ALTER TABLE `Post` DISABLE KEYS */;
INSERT INTO `Post` VALUES (77,'lacqua','lino',57,'2024-08-14 08:26:18','2024-08-14 08:38:05','          xh                      lacqua                            ',NULL),(78,'ol','lino',57,'2024-08-14 09:24:17','2024-08-14 09:24:17','z',NULL),(79,'p','lino',57,'2024-08-14 09:27:37','2024-08-14 09:27:37','sss',NULL),(80,'e','lino',58,'2024-08-14 09:40:27','2024-08-31 08:40:45','                                prova   esempio                         ',NULL),(81,'Post con datetime corretto','mariorossi',54,'2024-08-16 07:57:23','2024-08-19 10:08:47','                                ddddaaa','8d50ef0b5c5c0cfe7ccdaae6543e44a97cd90b69.png'),(82,'Nuova prova di timestamp','mariorossi',54,'2024-08-16 07:58:18','2024-08-16 07:58:18','eififjirjfirjfrijrijfrijfrijf',NULL),(83,'HHHHH','zero',73,'2024-08-16 16:26:06','2024-08-19 16:21:39','&lt;script&gt;alert(&quot;Vediamo se questo script funziona&quot;);&lt;/script&gt;',NULL),(84,'HHHH','zero',74,'2024-08-20 08:26:34','2024-08-20 08:26:34','HHHHHHHHH',NULL),(85,'Questo è un post in un blog in cui sono coautore','mariorossi',73,'2024-08-21 15:02:23','2024-08-21 15:02:23','Non sono il creatore di questo blog, però posso postarci dentro.',NULL),(86,'lollo','lino',58,'2024-08-31 15:28:21','2024-08-31 15:28:29','                                bel ragazzino                            ',NULL);
/*!40000 ALTER TABLE `RicordaUtenti` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RisposteCommenti`
--

DROP TABLE IF EXISTS `RisposteCommenti`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RisposteCommenti` (
  `id_commento_risposta` bigint(20) unsigned NOT NULL COMMENT 'ID della risposta.',
  `id_commento_riferimento` bigint(20) unsigned NOT NULL COMMENT 'ID del commento di riferimento (quello a cui la risposta sta rispondendo)',
  PRIMARY KEY (`id_commento_risposta`,`id_commento_riferimento`),
  KEY `idx_commento_risposta` (`id_commento_risposta`),
  KEY `idx_commento_riferimento` (`id_commento_riferimento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Relazioni di risposta tra commenti';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RisposteCommenti`
--

LOCK TABLES `RisposteCommenti` WRITE;
/*!40000 ALTER TABLE `RisposteCommenti` DISABLE KEYS */;
INSERT INTO `RisposteCommenti` VALUES (27,16),(28,16),(29,16),(30,29),(31,30),(32,23),(33,16),(34,25),(35,34),(37,36),(38,20),(39,38),(40,37),(41,40),(43,42),(44,43),(45,44),(46,44),(49,48),(50,7),(54,53),(55,54),(56,55),(58,57),(59,58),(61,60),(62,61),(63,62),(65,64),(67,66);
/*!40000 ALTER TABLE `Sottocategoria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Utenti`
--

DROP TABLE IF EXISTS `Utenti`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Utenti` (
  `nome_utente` varchar(200) NOT NULL,
  `nome_visualizzato` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `bio` text DEFAULT NULL,
  `avatar` varchar(300) NOT NULL DEFAULT 'default.svg',
  `premium` tinyint(1) NOT NULL DEFAULT 0,
  `data_ora_iscrizione` datetime NOT NULL DEFAULT current_timestamp(),
  `UpvotesRicevuti` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`nome_utente`),
  UNIQUE KEY `e-mail` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Elenco degli utenti con relative informazioni';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Utenti`
--

LOCK TABLES `Utenti` WRITE;
/*!40000 ALTER TABLE `Utenti` DISABLE KEYS */;
INSERT INTO `Utenti` VALUES ('costine','costine','$2y$10$JAfS77gnrLba3vpmujfWkeO4mjHYBUAdKVnJOy0.w0vDLMToo2Fv6','cos@tine.it',NULL,'default.svg',0,'0000-00-00 00:00:00',0),('jarule11','Ja Rule','$2y$10$YH1e1cHfBqIw5A6xV3SC0OLRPXHKmShNevXMvcKYQKinDs2/FHCJ6','ja@rule.com',NULL,'default.svg',0,'0000-00-00 00:00:00',0),('lino','lino','$2y$10$CZrRUDIYSCxUULpH0liLyOF6F8QVs5kC6QwEatqjyJwOxHvxgySay','le@le.a','Sono Lino e vengo da Lucca','4c995096f4059cdde5780295572ad8971f659e90.jpg',0,'0000-00-00 00:00:00',0),('mariorossi','Mario Rossi','$2y$10$YCbCxw07jfXK.oFBtVeF7.M2.ArPPzTE3RneLGRrBzc9TJ9hqMegW','mario.rossi@gmail.com',NULL,'4c995096f4059cdde5780295572ad8971f659e90.jpg',1,'0000-00-00 00:00:00',0),('music','Is Like Magic','$2y$10$XdkyIJJfU7v.bJYNnHxSZeLCbRSKGdUCB.YjFedoNEcNlc2BIqYpC','music@is.magic',NULL,'default.svg',0,'2024-08-30 14:08:57',0),('zero','ZeroZeroZero ','$2y$10$Bvi6Wo6ii1/MFpQ5UcxMtO4nqRoOP8HXGRQlRfGslezrTgqhPhjrK','kdkd@dkkd.com',NULL,'default.svg',0,'0000-00-00 00:00:00',0);
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-09-02  9:05:24
