CREATE TABLE FeedbackPost
(
	id_feedback BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	id_post_riferimento INT(20) UNSIGNED NOT NULL,
	username_autore_feedback VARCHAR(200) NOT NULL,
	feedback_type TINYINT NOT NULL, 
	
	CONSTRAINT CHK_Feedback_Consentiti CHECK (
		feedback_type = 1 OR feedback_type = -1
	),
	
	CONSTRAINT unico_feedback_per_post_per_utente UNIQUE (id_post_riferimento, username_autore_feedback),
	CONSTRAINT PK_FeedbackPost PRIMARY KEY (id_feedback),
	CONSTRAINT FK_PostRiferimento FOREIGN KEY (id_post_riferimento) REFERENCES Post(id_post) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT FK_AutoreFeedback FOREIGN KEY (username_autore_feedback) REFERENCES Utenti(nome_utente)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;