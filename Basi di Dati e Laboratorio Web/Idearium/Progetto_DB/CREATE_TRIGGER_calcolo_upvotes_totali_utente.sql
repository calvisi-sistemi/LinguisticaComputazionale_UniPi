CREATE TRIGGER calcolo_upvotes_totali_utente
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

END