DELIMITER //

CREATE TRIGGER incrementa_numero_commenti
AFTER INSERT ON Commenti
FOR EACH ROW
BEGIN
    UPDATE Post
    SET numero_commenti = numero_commenti + 1
    WHERE id_post = NEW.id_post_commentato;
END//

DELIMITER ;
