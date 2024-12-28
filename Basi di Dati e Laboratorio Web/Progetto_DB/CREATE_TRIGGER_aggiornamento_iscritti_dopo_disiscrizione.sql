DELIMITER //
CREATE TRIGGER aggiornamento_iscritti_dopo_disiscrizione
AFTER DELETE ON Iscrizioni
FOR EACH ROW
BEGIN
    DECLARE blog_owner VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

    -- Trova il proprietario del blog a cui l'utente era iscritto
    SELECT Blog.nome_amministratore_blog INTO blog_owner
    FROM Blog
    WHERE Blog.id_blog = OLD.id_blog_iscrizione;

    -- Decrementa il numero totale di iscrizioni ai blog posseduti da questo utente
    UPDATE Utenti
    SET Utenti.totale_iscritti_ai_propri_blog = Utenti.totale_iscritti_ai_propri_blog - 1
    WHERE Utenti.nome_utente = blog_owner;
END //
DELIMITER ;