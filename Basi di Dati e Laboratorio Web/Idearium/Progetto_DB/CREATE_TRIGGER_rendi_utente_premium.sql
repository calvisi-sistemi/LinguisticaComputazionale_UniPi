DELIMITER //

CREATE TRIGGER rendi_utente_premium
BEFORE UPDATE ON Utenti
FOR EACH ROW
BEGIN
    -- Verifica se il numero di iscritti ai propri blog supera il minimo
    IF NEW.totale_iscritti_ai_propri_blog >= 5 AND OLD.premium = FALSE THEN
        -- Imposta il flag premium a TRUE
        SET NEW.premium = TRUE;
    END IF;
END //

DELIMITER ;
