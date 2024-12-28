DELIMITER //
CREATE TRIGGER aggiornamento_iscritti_dopo_nuova_iscrizione
AFTER INSERT ON Iscrizioni
FOR EACH ROW
BEGIN
    DECLARE blog_owner VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

    -- Trova il proprietario del blog a cui l'utente si è iscritto
    SELECT Blog.nome_amministratore_blog INTO blog_owner
    FROM Blog
    WHERE Blog.id_blog = NEW.id_blog_iscrizione; -- N.B. NEW.id_blog_iscrizione significa "l'ultimo dato Iscrizioni.id_blog inserito"

    -- Aggiorna il numero totale di iscrizioni ai blog posseduti da questo utente
    UPDATE Utenti
    SET Utenti.totale_iscritti_ai_propri_blog = totale_iscritti_ai_propri_blog + 1
    WHERE Utenti.nome_utente = blog_owner;
END //
DELIMITER ;
