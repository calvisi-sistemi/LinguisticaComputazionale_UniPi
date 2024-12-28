CREATE TABLE Iscrizioni
(
	id_iscrizione BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	nome_utente_iscritto VARCHAR(200) NOT NULL,
	id_blog_iscrizione INT(10) UNSIGNED NOT NULL,
	CONSTRAINT PK_Iscrizione PRIMARY KEY (id_iscrizione),
	CONSTRAINT una_sola_iscrizione_ad_utente_per_blog UNIQUE(nome_utente_iscritto, id_blog_iscrizione),
	CONSTRAINT FK_Blog_ID FOREIGN KEY (id_blog_iscrizione) REFERENCES Blog(id_blog) 
		ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT FK_Utente_Iscritto FOREIGN KEY (nome_utente_iscritto) REFERENCES Utenti(nome_utente)
		ON UPDATE CASCADE ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;