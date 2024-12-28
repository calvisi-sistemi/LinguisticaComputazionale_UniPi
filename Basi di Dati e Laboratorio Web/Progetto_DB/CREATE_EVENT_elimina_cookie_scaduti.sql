CREATE EVENT elimina_cookie_scaduti
ON SCHEDULE EVERY 1 DAY
DO
  DELETE FROM RicordaUtenti
  WHERE scadenza_del_cookie < NOW();

