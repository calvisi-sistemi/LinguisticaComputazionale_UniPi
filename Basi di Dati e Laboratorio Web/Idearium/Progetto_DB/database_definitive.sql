-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Dic 05, 2024 alle 23:12
-- Versione del server: 10.11.9-MariaDB
-- Versione PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u716196361_progetto_bdd`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `Autori`
--

CREATE TABLE `Autori` (
  `id_autore` varchar(200) NOT NULL,
  `id_blog` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Elenco degli autori (dunque non amministratori) dei blog. Quale utente può scrivere su quale blog senza esserne amministratore.';

-- --------------------------------------------------------

--
-- Struttura della tabella `Blog`
--

CREATE TABLE `Blog` (
  `id_blog` int(10) UNSIGNED NOT NULL,
  `titolo_visualizzato` varchar(200) NOT NULL,
  `categoria` varchar(100) NOT NULL,
  `descrizione` varchar(200) NOT NULL COMMENT 'Descrizione del blog (max 200 caratteri)',
  `nome_amministratore_blog` varchar(200) NOT NULL,
  `data_creazione` timestamp NOT NULL DEFAULT current_timestamp(),
  `logo` varchar(300) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Dati dei blog';

--
-- Dump dei dati per la tabella `Blog`
--

INSERT INTO `Blog` (`id_blog`, `titolo_visualizzato`, `categoria`, `descrizione`, `nome_amministratore_blog`, `data_creazione`, `logo`) VALUES
(102, 'Il mio acquario e i suoi dintorni', 'Acquario', 'In questo blog parlerò del mio acquario d&#039;acqua dolce tropicale.\r\n\r\nAspettatevi tante belle foto dei miei Poecilia sphoenops!', 'zero', '2024-10-01 08:34:30', NULL),
(108, 'Scrittori dimenticati', 'Letteratura e critica letteraria', 'Un blog sugli scrittori italiani meno noti', 'giovanni', '2024-12-02 16:27:54', NULL),
(110, 'Ossa e terra', 'Paleontologia', 'Il regno della paleontologia', 'hero', '2024-12-05 16:45:21', 'f96ae82805abc42f672c850f47bdd231c6bdddea.gif');

-- --------------------------------------------------------

--
-- Struttura della tabella `Categoria`
--

CREATE TABLE `Categoria` (
  `nome_categoria` varchar(100) NOT NULL,
  `Creatore` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Elenco delle categorie';

--
-- Dump dei dati per la tabella `Categoria`
--

INSERT INTO `Categoria` (`nome_categoria`, `Creatore`) VALUES
('Acquario', NULL),
('Altri animali', NULL),
('Animali', NULL),
('Arte e Cultura', NULL),
('Astronomia', NULL),
('Atletica leggera', NULL),
('Basket', NULL),
('Biologia', NULL),
('Blog Personale', NULL),
('Calcio', NULL),
('Cani', NULL),
('Cardiologia', NULL),
('Chimica', NULL),
('Chirurgia', NULL),
('Cinema', NULL),
('Corsi speciali', NULL),
('Criptovalute', NULL),
('CyberSec', NULL),
('Dottorato e specializzazione', NULL),
('Economia', NULL),
('Economia globalizzata', NULL),
('Elementari', NULL),
('Elettronica', NULL),
('Finanza', NULL),
('Fisica', NULL),
('Gatti', NULL),
('Genetica', NULL),
('Geologia', NULL),
('Informatica', NULL),
('Istruzione', NULL),
('Letteratura e critica letteraria', NULL),
('Mangiare sano', NULL),
('Matematica', NULL),
('Medicina generale', NULL),
('Medie', NULL),
('Neurologia', NULL),
('Nuoto', NULL),
('Olimpiadi', NULL),
('Paleontologia', NULL),
('Pallavolo', NULL),
('Pittura', NULL),
('Risparmio personale', NULL),
('Salute e Benessere', NULL),
('Scienza', NULL),
('Scultura', NULL),
('Sport', NULL),
('Storia delle arti', NULL),
('Superiori', NULL),
('Teatro', NULL),
('Tecnologia', NULL),
('Telecomunicazioni', NULL),
('Telefonia', NULL),
('Tennis', NULL),
('Terrario', NULL),
('Università', NULL),
('Veterinaria', NULL),
('Viaggi', NULL),
('Viaggi di lusso', NULL),
('Viaggi in ostello', NULL),
('Viaggiare in bicicletta', NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `Commenti`
--

CREATE TABLE `Commenti` (
  `id_commento` bigint(20) UNSIGNED NOT NULL,
  `nome_autore_commento` varchar(200) NOT NULL,
  `id_post_commentato` int(20) UNSIGNED NOT NULL,
  `contenuto` text NOT NULL,
  `creazione_commento` datetime NOT NULL DEFAULT current_timestamp(),
  `ultima_modifica` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Insieme dei commenti con dati relativi';

--
-- Dump dei dati per la tabella `Commenti`
--

INSERT INTO `Commenti` (`id_commento`, `nome_autore_commento`, `id_post_commentato`, `contenuto`, `creazione_commento`, `ultima_modifica`) VALUES
(208, 'zero', 110, 'Pavese è stato un grande scrittore, e in vita sono stati osannati secondo me più che altro i suoi lavori meno meritevoli. I \"Dialoghi con Leucò\" sono un gioiello poco noto', '2024-12-05 16:36:14', '2024-12-05 16:36:14'),
(209, 'hero', 110, 'Sono d\'accordo, quel libro è veramente poco noto', '2024-12-05 16:41:03', '2024-12-05 16:41:03');

--
-- Trigger `Commenti`
--
DELIMITER $$
CREATE TRIGGER `decrementa_numero_commenti` AFTER DELETE ON `Commenti` FOR EACH ROW BEGIN
    UPDATE Post
    SET numero_commenti = numero_commenti - 1
    WHERE id_post = OLD.id_post_commentato;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `incrementa_numero_commenti` AFTER INSERT ON `Commenti` FOR EACH ROW BEGIN
    UPDATE Post
    SET numero_commenti = numero_commenti + 1
    WHERE id_post = NEW.id_post_commentato;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struttura della tabella `FeedbackPost`
--

CREATE TABLE `FeedbackPost` (
  `id_feedback` bigint(20) UNSIGNED NOT NULL,
  `id_post_riferimento` int(20) UNSIGNED NOT NULL,
  `username_autore_feedback` varchar(200) NOT NULL,
  `feedback_type` tinyint(4) NOT NULL
) ;

--
-- Dump dei dati per la tabella `FeedbackPost`
--

INSERT INTO `FeedbackPost` (`id_feedback`, `id_post_riferimento`, `username_autore_feedback`, `feedback_type`) VALUES
(97, 110, 'zero', 1),
(100, 110, 'hero', 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `Iscrizioni`
--

CREATE TABLE `Iscrizioni` (
  `id_iscrizione` bigint(20) UNSIGNED NOT NULL,
  `nome_utente_iscritto` varchar(200) NOT NULL,
  `id_blog_iscrizione` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `Iscrizioni`
--

INSERT INTO `Iscrizioni` (`id_iscrizione`, `nome_utente_iscritto`, `id_blog_iscrizione`) VALUES
(80, 'giovanni', 102),
(84, 'hero', 102),
(83, 'hero', 108),
(77, 'zero', 108);

--
-- Trigger `Iscrizioni`
--
DELIMITER $$
CREATE TRIGGER `aggiornamento_iscritti_dopo_disiscrizione` AFTER DELETE ON `Iscrizioni` FOR EACH ROW BEGIN
    DECLARE blog_owner VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

    -- Trova il proprietario del blog a cui l'utente era iscritto
    SELECT Blog.nome_amministratore_blog INTO blog_owner
    FROM Blog
    WHERE Blog.id_blog = OLD.id_blog_iscrizione;

    -- Decrementa il numero totale di iscrizioni ai blog posseduti da questo utente
    UPDATE Utenti
    SET Utenti.totale_iscritti_ai_propri_blog = Utenti.totale_iscritti_ai_propri_blog - 1
    WHERE Utenti.nome_utente = blog_owner;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `aggiornamento_iscritti_dopo_nuova_iscrizione` AFTER INSERT ON `Iscrizioni` FOR EACH ROW BEGIN
    DECLARE blog_owner VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

    -- Trova il proprietario del blog a cui l'utente si è iscritto
    SELECT Blog.nome_amministratore_blog INTO blog_owner
    FROM Blog
    WHERE Blog.id_blog = NEW.id_blog_iscrizione; -- N.B. NEW.id_blog_iscrizione significa "l'ultimo dato Iscrizioni.id_blog inserito"

    -- Aggiorna il numero totale di iscrizioni ai blog posseduti da questo utente
    UPDATE Utenti
    SET Utenti.totale_iscritti_ai_propri_blog = totale_iscritti_ai_propri_blog + 1
    WHERE Utenti.nome_utente = blog_owner;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struttura della tabella `Post`
--

CREATE TABLE `Post` (
  `id_post` int(20) UNSIGNED NOT NULL,
  `titolo_post` varchar(200) NOT NULL,
  `nome_autore_post` varchar(200) NOT NULL,
  `id_blog_appartenenza` int(10) UNSIGNED NOT NULL COMMENT 'ID del blog di appartenenza del post',
  `creazione` datetime NOT NULL DEFAULT current_timestamp(),
  `ultima_modifica` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `contenuto_post` text NOT NULL COMMENT 'Contenuto del post in Markdown',
  `immagine_post` varchar(300) DEFAULT NULL COMMENT 'Percorso di un''immagine eventualmente allegata al post. La possibilità di allegare immagini è una delle caratteristiche premium.',
  `numero_commenti` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Elenco dei posto con il relativo contenuto';

--
-- Dump dei dati per la tabella `Post`
--

INSERT INTO `Post` (`id_post`, `titolo_post`, `nome_autore_post`, `id_blog_appartenenza`, `creazione`, `ultima_modifica`, `contenuto_post`, `immagine_post`, `numero_commenti`) VALUES
(109, 'Presentazione', 'zero', 102, '2024-11-28 21:47:47', '2024-12-05 16:17:29', 'Ciao a tutti! Sono un appassionato di acquariofilia, e questo blog nasce per condividere la mia passione per gli ecosistemi sommersi. Qui troverete consigli pratici, guide dettagliate, e racconti delle mie esperienze con acquari d’acqua dolce e marina.\r\n\r\nDall’allestimento del primo acquario, alla cura delle piante acquatiche, fino ai segreti per mantenere felici pesci e invertebrati: esploreremo insieme ogni aspetto di questo fantastico hobby.\r\n\r\nChe siate principianti o esperti, spero che i miei articoli vi ispirino e vi aiutino a creare il vostro angolo di natura in casa. 💧', 'f0dacfc5d6f5c34f54c41253fcb2f2f5c9e5703d.jpg', 0),
(110, 'Cesare Pavese', 'giovanni', 108, '2024-12-02 16:36:40', '2024-12-05 16:41:03', 'Scrittore che avrebbe meritato maggior gloria per i suoi scritti vicini alle vicende contadine del suo Piemonte, Cesare Pavese fu contraddistinto da una costante tristezza che degenerò, a livello sanitario, in una brutta depressione che sfociò, il 27 Agosto 1950, nel suicidio.\r\nNei suoi passi si può trovare l&#039;atmosfera della dura vita contadina dell&#039;Italia della prima metà del ventesimo secolo, una vita molto aspra, piena di fatica e lavoro intenso, intrisa di problematiche sociali come le violenze domestiche e l&#039;abuso di alcol. Tra le sue opere migliori non possiamo dimenticare &quot;La luna e i falò&quot;, pubblicato nel 1950.', NULL, 2),
(112, 'Due parole sui Poecilia sphoenops', 'zero', 102, '2024-12-05 16:21:20', '2024-12-05 16:21:20', 'I Poecilia Sphenops, meglio noti come Molly, sono tra i pesci d’acqua dolce più apprezzati dagli acquariofili. Originari delle acque calme dell’America Centrale e Meridionale, questi pesciolini vivaci sono perfetti per chi cerca un tocco di colore e dinamismo nel proprio acquario.\r\n\r\nI Molly sono famosi per la loro resistenza e adattabilità, ideali anche per chi è alle prime armi. Amano vivere in gruppo e prediligono acquari ricchi di piante, con acqua leggermente alcalina e temperature tra i 24 e i 28°C.\r\n\r\nLa loro dieta è onnivora: mangiano volentieri scaglie, cibi congelati e vegetali come zucchine o spinaci. Attenzione, però, alla sovralimentazione!\r\n\r\nFacili da allevare, i Molly sono ovovivipari, dando vita a piccoli già formati. Per un acquario di comunità, sono ottimi compagni per altri pesci pacifici come Guppy e Platy.', '56a7b53d4da8bdae01c5e07fafc639291625ebad.jpg', 0),
(113, 'Mare nostrum', 'zero', 102, '2024-12-05 16:34:56', '2024-12-05 16:34:56', 'Riprodurre un ecosistema mediterraneo in acquario è un’esperienza affascinante e unica, perfetta per chi vuole portare a casa la bellezza del nostro mare. Protagonisti come gamberi, anemoni, stelle marine e piccoli pesci come ghiozzi e blennidi creano un ambiente ricco di vita. L’acqua fresca, tra 18 e 22°C, richiede un sistema di raffreddamento adeguato e una filtrazione impeccabile. Spesso le specie mediterranee non sono reperibili in negozio e devono essere raccolte nel rispetto delle normative. Le alghe, essenziali per un acquario realistico, completano l’ecosistema. Questo acquario è un omaggio al nostro mare e una grande occasione per sensibilizzare sulla sua tutela.', '18f50b88869fadbdf78a97c72a6610ffdcdd3ec7.jpg', 0),
(114, 'RECENSIONE: G. Pinna, &quot;Paleontologia ed evoluzione&quot;', 'hero', 110, '2024-12-05 16:48:49', '2024-12-05 16:48:49', 'Il testo di Giovanni Pinna si distingue per un approccio analitico e critico alla teoria degli equilibri punteggiati di Gould ed Eldredge. L’autore mette in discussione i fondamenti di questa visione evolutiva, proponendo un ritorno a una concezione più gradualistica. Sebbene l&#039;argomento sia affrontato con rigore e profondità, il libro soffre di una struttura a tratti prolissa e poco accessibile per i non specialisti. L&#039;assenza di illustrazioni o diagrammi rende i concetti esposti più difficili da seguire, soprattutto per chi non ha una conoscenza approfondita della materia. Pinna offre spunti interessanti, ma il suo tono polemico rischia di alienare il lettore, oscurando le potenzialità del dibattito scientifico che intende stimolare. Un’opera valida per gli esperti, ma meno indicata per chi cerca una panoramica introduttiva.', 'aa78036a9385a5a1bd9db443dfe73e1903987705.jpg', 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `RicordaUtenti`
--

CREATE TABLE `RicordaUtenti` (
  `id_login_salvato` bigint(20) UNSIGNED NOT NULL,
  `nome_utente` varchar(200) NOT NULL,
  `hashed_cookie_token` varchar(200) NOT NULL COMMENT 'Hash dell''identificatore del cookie, NON identificatore vero.',
  `scadenza_del_cookie` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `RicordaUtenti`
--

INSERT INTO `RicordaUtenti` (`id_login_salvato`, `nome_utente`, `hashed_cookie_token`, `scadenza_del_cookie`) VALUES
(17, 'zero', '$2y$10$GGAwgDG5aJV8NfHkPkO4b.ttHYxxWIX6ER6WPMaIBr.MfYinGc5ue', '2024-11-22 13:54:23');

-- --------------------------------------------------------

--
-- Struttura della tabella `RisposteCommenti`
--

CREATE TABLE `RisposteCommenti` (
  `id_commento_risposta` bigint(20) UNSIGNED NOT NULL COMMENT 'ID della risposta.',
  `id_commento_riferimento` bigint(20) UNSIGNED NOT NULL COMMENT 'ID del commento di riferimento (quello a cui la risposta sta rispondendo)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `RisposteCommenti`
--

INSERT INTO `RisposteCommenti` (`id_commento_risposta`, `id_commento_riferimento`) VALUES
(209, 208);

-- --------------------------------------------------------

--
-- Struttura della tabella `Sottocategoria`
--

CREATE TABLE `Sottocategoria` (
  `categoria_principale` varchar(100) NOT NULL,
  `sotto_categoria` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `Sottocategoria`
--

INSERT INTO `Sottocategoria` (`categoria_principale`, `sotto_categoria`) VALUES
('Animali', 'Acquario'),
('Animali', 'Altri animali'),
('Animali', 'Cani'),
('Animali', 'Gatti'),
('Animali', 'Terrario'),
('Animali', 'Veterinaria'),
('Arte e Cultura', 'Cinema'),
('Arte e Cultura', 'Letteratura e critica letteraria'),
('Arte e Cultura', 'Pittura'),
('Arte e Cultura', 'Scultura'),
('Arte e Cultura', 'Storia delle arti'),
('Arte e Cultura', 'Teatro'),
('Economia', 'Criptovalute'),
('Economia', 'Economia globalizzata'),
('Economia', 'Finanza'),
('Economia', 'Risparmio personale'),
('Istruzione', 'Corsi speciali'),
('Istruzione', 'Dottorato e specializzazione'),
('Istruzione', 'Elementari'),
('Istruzione', 'Medie'),
('Istruzione', 'Superiori'),
('Istruzione', 'Università'),
('Salute e benessere', 'Cardiologia'),
('Salute e benessere', 'Chirurgia'),
('Salute e benessere', 'Mangiare sano'),
('Salute e benessere', 'Medicina generale'),
('Salute e benessere', 'Neurologia'),
('Scienza', 'Astronomia'),
('Scienza', 'Biologia'),
('Scienza', 'Chimica'),
('Scienza', 'Fisica'),
('Scienza', 'Genetica'),
('Scienza', 'Geologia'),
('Scienza', 'Matematica'),
('Scienza', 'Paleontologia'),
('Sport', 'Atletica leggera'),
('Sport', 'Basket'),
('Sport', 'Calcio'),
('Sport', 'Nuoto'),
('Sport', 'Olimpiadi'),
('Sport', 'Pallavolo'),
('Sport', 'Tennis'),
('Tecnologia', 'CyberSec'),
('Tecnologia', 'Elettronica'),
('Tecnologia', 'Informatica'),
('Tecnologia', 'Telecomunicazioni'),
('Tecnologia', 'Telefonia'),
('Viaggi', 'Viaggi di lusso'),
('Viaggi', 'Viaggi in ostello'),
('Viaggi', 'Viaggiare in bicicletta');

-- --------------------------------------------------------

--
-- Struttura della tabella `Utenti`
--

CREATE TABLE `Utenti` (
  `nome_utente` varchar(200) NOT NULL,
  `nome_visualizzato` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `bio` text DEFAULT NULL,
  `avatar` varchar(300) DEFAULT NULL,
  `premium` tinyint(1) NOT NULL DEFAULT 0,
  `data_ora_iscrizione` datetime NOT NULL DEFAULT current_timestamp(),
  `totale_iscritti_ai_propri_blog` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Elenco degli utenti con relative informazioni';

--
-- Dump dei dati per la tabella `Utenti`
--

INSERT INTO `Utenti` (`nome_utente`, `nome_visualizzato`, `password`, `email`, `bio`, `avatar`, `premium`, `data_ora_iscrizione`, `totale_iscritti_ai_propri_blog`) VALUES
('giovanni', 'Giovanni Bianchi', '$2y$10$19ZXsnF9jxZ9s228V.mhu.3I4ae6mZMA855C4S2kJWlxRxmVHyuSK', 'gb@libero.it', 'Sono Giovanni e amo la letteratura', 'default.svg', 0, '2024-12-02 16:26:09', 3),
('hero', 'hero', '$2y$10$MQG6K5v1GALdA4oqYyLCGuX01dxbgkt3HMiw9TLAEXEP/l/kI.QWK', 'hero@hero.com', 'herohero', 'c254c6802e093f231fede8bd84557170e65b8d14.jpg', 0, '2024-12-03 22:09:20', 0),
('mariorossi', 'Mario Rossi', '$2y$10$qYLEjeN.BpgqwNOvHACxSu48GmEI7lSe08dtS66aLBMxZwkOViWQS', 'mario@rossi.com', 'Ciao! Sono il sig. Rossi', 'default.svg', 0, '2024-11-26 17:25:08', 0),
('prova', 'Prova', '$2y$10$iHg6zrd7QfzGTF.YrB2J5eBEo/QrS3.5ddfGP.DYhY5G29HpY971K', 'prova@prova.it', 'Account di prova', 'default.svg', 0, '2024-10-10 11:39:50', 0),
('zero', 'Zero Solo', '$2y$10$dp/Y9tortxcBArYQl1k.Y./Pp75DJn6MorxHjBUrK/qcj/Jak3lru', 'zero@gmail.com', 'Ciao, mi chiamo Zero!', 'd6706c654c6c79299ecc6415e7415469c50b022d.jpg', 1, '2024-10-01 08:29:04', 6);

--
-- Trigger `Utenti`
--
DELIMITER $$
CREATE TRIGGER `rendi_utente_premium` BEFORE UPDATE ON `Utenti` FOR EACH ROW BEGIN
    -- Verifica se il numero di iscritti ai propri blog supera il minimo
    IF NEW.totale_iscritti_ai_propri_blog >= 5 AND OLD.premium = FALSE THEN
        -- Imposta il flag premium a TRUE
        SET NEW.premium = TRUE;
    END IF;
END
$$
DELIMITER ;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `Autori`
--
ALTER TABLE `Autori`
  ADD PRIMARY KEY (`id_autore`,`id_blog`),
  ADD KEY `idx_id_autore` (`id_autore`) USING BTREE,
  ADD KEY `idx_id_blog` (`id_blog`) USING BTREE;

--
-- Indici per le tabelle `Blog`
--
ALTER TABLE `Blog`
  ADD PRIMARY KEY (`id_blog`),
  ADD UNIQUE KEY `titolo_visualizzato` (`titolo_visualizzato`),
  ADD KEY `chi_amministra` (`nome_amministratore_blog`),
  ADD KEY `FK_Blog_Category` (`categoria`),
  ADD KEY `FK_Blog_Logo` (`logo`);

--
-- Indici per le tabelle `Categoria`
--
ALTER TABLE `Categoria`
  ADD PRIMARY KEY (`nome_categoria`);

--
-- Indici per le tabelle `Commenti`
--
ALTER TABLE `Commenti`
  ADD PRIMARY KEY (`id_commento`),
  ADD KEY `fk_commento_utente` (`nome_autore_commento`),
  ADD KEY `fk_commento_post` (`id_post_commentato`);

--
-- Indici per le tabelle `FeedbackPost`
--
ALTER TABLE `FeedbackPost`
  ADD PRIMARY KEY (`id_feedback`),
  ADD UNIQUE KEY `unico_feedback_per_post_per_utente` (`id_post_riferimento`,`username_autore_feedback`),
  ADD KEY `FK_AutoreFeedback` (`username_autore_feedback`);

--
-- Indici per le tabelle `Iscrizioni`
--
ALTER TABLE `Iscrizioni`
  ADD PRIMARY KEY (`id_iscrizione`),
  ADD UNIQUE KEY `una_sola_iscrizione_ad_utente_per_blog` (`nome_utente_iscritto`,`id_blog_iscrizione`),
  ADD KEY `FK_Blog_ID` (`id_blog_iscrizione`);

--
-- Indici per le tabelle `Post`
--
ALTER TABLE `Post`
  ADD PRIMARY KEY (`id_post`),
  ADD KEY `idx_blog_appartenenza` (`id_blog_appartenenza`) USING BTREE,
  ADD KEY `idx_autore` (`nome_autore_post`) USING BTREE,
  ADD KEY `FK_Post_Pic` (`immagine_post`);

--
-- Indici per le tabelle `RicordaUtenti`
--
ALTER TABLE `RicordaUtenti`
  ADD PRIMARY KEY (`id_login_salvato`),
  ADD UNIQUE KEY `Unique_Hashed_Token` (`hashed_cookie_token`),
  ADD KEY `FK_NomeUtente` (`nome_utente`);

--
-- Indici per le tabelle `RisposteCommenti`
--
ALTER TABLE `RisposteCommenti`
  ADD PRIMARY KEY (`id_commento_risposta`,`id_commento_riferimento`),
  ADD KEY `FK_Commento_Riferimento` (`id_commento_riferimento`);

--
-- Indici per le tabelle `Sottocategoria`
--
ALTER TABLE `Sottocategoria`
  ADD PRIMARY KEY (`categoria_principale`,`sotto_categoria`),
  ADD UNIQUE KEY `Max_One_Main_Category_per_Subcategory` (`sotto_categoria`);

--
-- Indici per le tabelle `Utenti`
--
ALTER TABLE `Utenti`
  ADD PRIMARY KEY (`nome_utente`),
  ADD UNIQUE KEY `e-mail` (`email`),
  ADD KEY `FK_Avatar` (`avatar`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `Blog`
--
ALTER TABLE `Blog`
  MODIFY `id_blog` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT per la tabella `Commenti`
--
ALTER TABLE `Commenti`
  MODIFY `id_commento` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=210;

--
-- AUTO_INCREMENT per la tabella `FeedbackPost`
--
ALTER TABLE `FeedbackPost`
  MODIFY `id_feedback` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `Iscrizioni`
--
ALTER TABLE `Iscrizioni`
  MODIFY `id_iscrizione` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT per la tabella `Post`
--
ALTER TABLE `Post`
  MODIFY `id_post` int(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT per la tabella `RicordaUtenti`
--
ALTER TABLE `RicordaUtenti`
  MODIFY `id_login_salvato` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `Autori`
--
ALTER TABLE `Autori`
  ADD CONSTRAINT `Autori_ibfk_1` FOREIGN KEY (`id_autore`) REFERENCES `Utenti` (`nome_utente`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_autori_blog` FOREIGN KEY (`id_blog`) REFERENCES `Blog` (`id_blog`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `Blog`
--
ALTER TABLE `Blog`
  ADD CONSTRAINT `FK_Blog_Category` FOREIGN KEY (`categoria`) REFERENCES `Categoria` (`nome_categoria`),
  ADD CONSTRAINT `chi_amministra` FOREIGN KEY (`nome_amministratore_blog`) REFERENCES `Utenti` (`nome_utente`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_blog_utente` FOREIGN KEY (`nome_amministratore_blog`) REFERENCES `Utenti` (`nome_utente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `Commenti`
--
ALTER TABLE `Commenti`
  ADD CONSTRAINT `fk_commento_post` FOREIGN KEY (`id_post_commentato`) REFERENCES `Post` (`id_post`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_commento_utente` FOREIGN KEY (`nome_autore_commento`) REFERENCES `Utenti` (`nome_utente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `FeedbackPost`
--
ALTER TABLE `FeedbackPost`
  ADD CONSTRAINT `FK_AutoreFeedback` FOREIGN KEY (`username_autore_feedback`) REFERENCES `Utenti` (`nome_utente`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_PostRiferimento` FOREIGN KEY (`id_post_riferimento`) REFERENCES `Post` (`id_post`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `Iscrizioni`
--
ALTER TABLE `Iscrizioni`
  ADD CONSTRAINT `FK_Blog_ID` FOREIGN KEY (`id_blog_iscrizione`) REFERENCES `Blog` (`id_blog`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_Utente_Iscritto` FOREIGN KEY (`nome_utente_iscritto`) REFERENCES `Utenti` (`nome_utente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `Post`
--
ALTER TABLE `Post`
  ADD CONSTRAINT `fk_post_blog` FOREIGN KEY (`id_blog_appartenenza`) REFERENCES `Blog` (`id_blog`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_post_utente` FOREIGN KEY (`nome_autore_post`) REFERENCES `Utenti` (`nome_utente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `RicordaUtenti`
--
ALTER TABLE `RicordaUtenti`
  ADD CONSTRAINT `FK_NomeUtente` FOREIGN KEY (`nome_utente`) REFERENCES `Utenti` (`nome_utente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `RisposteCommenti`
--
ALTER TABLE `RisposteCommenti`
  ADD CONSTRAINT `FK_Commento_Riferimento` FOREIGN KEY (`id_commento_riferimento`) REFERENCES `Commenti` (`id_commento`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_Commento_Risposta` FOREIGN KEY (`id_commento_risposta`) REFERENCES `Commenti` (`id_commento`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `Sottocategoria`
--
ALTER TABLE `Sottocategoria`
  ADD CONSTRAINT `FK_Main_Category` FOREIGN KEY (`categoria_principale`) REFERENCES `Categoria` (`nome_categoria`),
  ADD CONSTRAINT `FK_Subcategory` FOREIGN KEY (`sotto_categoria`) REFERENCES `Categoria` (`nome_categoria`);

DELIMITER $$
--
-- Eventi
--
CREATE DEFINER=`u716196361_bdd_project`@`%` EVENT `elimina_cookie_scaduti` ON SCHEDULE EVERY 1 DAY STARTS '2024-09-01 21:48:30' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM RicordaUtenti
  WHERE scadenza_del_cookie < NOW()$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
