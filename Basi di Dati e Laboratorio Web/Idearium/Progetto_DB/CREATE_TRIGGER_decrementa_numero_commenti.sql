DELIMITER //

CREATE TRIGGER decrementa_numero_commenti
AFTER DELETE ON Commenti
FOR EACH ROW
BEGIN
    UPDATE Post
    SET numero_commenti = numero_commenti - 1
    WHERE id_post = OLD.id_post_commentato;
END//

DELIMITER ;
