<?php
/**
 * Funzione per pubblicare un post testuale.
 * DA RIVEDERE: non funziona la pubblicazione di un post con immagine.
 * @param PDO $db_connection Connessione al DB 
 * @param int $blog_id ID del blog su cui pubblicare il post
 * @param string $author_name Nome dell'autore del post
 * @param string $post_title Titolo del post
 * @param string $post_content Contenuto del post
 * @param array|null $image_file opzionale, è il $_FILES['nome_campo'] dell'immagine da allegare al post, qualora una sia stata selezionata
 * @throws Exception in caso di errore
 * @return bool|int Restituisce l'ID dell'ultimo post 
 */
function add_post(PDO $db_connection, int $blog_id, string $username, string $post_title, string $post_content, array|null $image_file = null)
{
  try {
    $db_connection->beginTransaction();
    $columns = [POST_BLOG, POST_AUTHOR, POST_TITLE, POST_TEXT];

    $placeholders_to_values = [
      ':blog_id' => $blog_id,
      ':author' => $username,
      ':title' => $post_title,
      ':content' => $post_content
    ];

    $placeholders = array_keys($placeholders_to_values);

    if (!is_null($image_file)) {
      $new_image_name = calculate_new_image_name($image_file);
      $columns[] = POST_IMAGE; // Aggiungo la colonna delle immagini
      $placeholders_to_values[':image'] = $new_image_name; // Aggiungo un placeholder per le immagini
      $placeholders = array_keys($placeholders_to_values);
    }

    $sql = 'INSERT INTO ' . POST_TABLE . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';

    $stmt = $db_connection->prepare($sql);

    foreach ($placeholders_to_values as $placeholder => $value) {
      $stmt->bindValue($placeholder, $value); // BindParam crea problemi, dà un errore di chiave esterna violata rispetto a blog_id
    }

    // Esecuzione della query
    $stmt->execute();

    $post_id = $db_connection->lastInsertId();

    if (!is_null($image_file)) {
      save_image($image_file, $new_image_name);
    }

    $db_connection->commit();

    return $post_id;

  } catch (Exception $e) {
    rollback_if_in_transaction($db_connection);
    echo $e->getMessage();
    throw new Exception('Errore nella funzione add_post: ' . $e->getMessage());
  }
}

/**
 * Cancella un post.
 * La cancellazione di un post comporta:
 *  - La cancellazione di una sua eventuale immagine, a meno che la stessa non sai usata da un altro post
 *  - La cancellazione di tutti i suoi commenti
 * @param PDO $db_connection Connessione al database
 * @param int $post_id Post da cancellare
 * @throws \Exception In caso di errore
 * @return void 
 */
function delete_post(PDO $db_connection, int $post_id)
{
  try {

    $db_connection->beginTransaction();
    $sql = 'DELETE FROM ' . POST_TABLE . ' WHERE ' . POST_ID . ' = :post_id';
    $stmt = $db_connection->prepare($sql);
    $stmt->bindValue(':post_id', $post_id, PDO::PARAM_INT);
    $stmt->execute();
    $db_connection->commit();

  } catch (PDOException $e) {
    rollback_if_in_transaction($db_connection);
    throw new Exception('Errore nella funzione del_post: ' . $e->getMessage());
  }
}


/**
 * Funzione per l'aggiornamento del titolo di un post.
 * @param PDO $db_connection Connessione al DB
 * @param int $post_id ID del post
 * @param string $new_title Nuovo titolo
 * @throws Exception in caso di errore.
 * @return void
 */
function update_post_title(PDO $db_connection, int $post_id, string $new_title)
{
  try {
    $old_title = get_post_info($db_connection, $post_id)[POST_TITLE];

    if ($old_title !== $new_title) {

      $db_connection->beginTransaction();
      $sql = 'UPDATE ' . POST_TABLE . ' 
      SET ' . POST_TITLE . ' = :new_title 
      WHERE ' . POST_ID . ' = :post_id';

      $stmt = $db_connection->prepare($sql);
      $stmt->bindValue(':new_title', $new_title);
      $stmt->bindValue(':post_id', $post_id);
      $stmt->execute();
      $db_connection->commit();
    }

  } catch (Exception $e) {
    rollback_if_in_transaction($db_connection);
    throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
  }
}

/**
 * Funzione per verificare l'esistenza di un blog.
 * La funzione prende in input un ID e, prima di consultare il database, ne controlla la validità.
 * Qualora l'ID non sia un numero intero assoluto, ma contenga altri caratteri, assume che il blog non esista, senza consultare inutilmente il DB.
 * @param PDO $db_connection Connessione al database
 * @param mixed $post_id ID del blog da consultare. Il tipo è mixed perché deve poter prendere in input un qualunque valore (anche un $_GET['id'] non filtrato) senza il rischio di sollevare un TypeError
 * @throws Exception Qualora non sia stato possibile eseguire la query o qualora la query abbia restituito un numero di blog superiore a 1
 * @return bool TRUE se il blog esiste ed è unico, FALSE in ogni altro caso
 */
function does_post_exist(PDO $db_connection, mixed $post_id): bool
{
  try {
    $post_id_is_valid = (ctype_digit($post_id) && $post_id >= 0);

    if (!$post_id_is_valid) {
      return false;
    }

    $sql = 'SELECT COUNT(*) FROM ' . POST_TABLE . ' WHERE ' . POST_ID . ' = :post_id';
    $stmt = $db_connection->prepare($sql);
    $stmt->bindValue(':post_id', $post_id, PDO::PARAM_INT);
    $stmt->execute();

    $post_count = $stmt->fetchColumn();

    if ($post_count > 1) {
      throw new Exception("C'è un problema nel tuo database: $post_count post hanno lo stesso ID");
    }

    $post_existance = $post_count === 1;

    return $post_existance;

  } catch (Exception $e) {
    throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
  }
}


/**
 * Aggiornamento del contentuo dei post.
 * @param PDO $db_connection connessione al DB
 * @param int $post_id ID del post di cui modificare il contenuto
 * @param string $new_text Nuovo contenuto del post
 * @throws Exception in caso di errore.
 * @return string|null  se il nuovo testo è identico al precedente, restituisce NULL, altrimenti restituisce il nuovo testo.
 */
function update_post_text(PDO $db_connection, int $post_id, string $new_text): void
{
  try {
    $old_content = get_post_info($db_connection, $post_id)[POST_TEXT];

    if ($old_content === $new_text) { // Effettuo un aggiornamento solo se il nuovo contenuto è effettivamente diverso dal vecchio
      return;
    }

    $db_connection->beginTransaction();
    $sql = 'UPDATE ' . POST_TABLE . ' SET ' . POST_TEXT . ' = :new_content, ' . POST_LAST_EDIT . ' = NOW() WHERE ' . POST_ID . ' = :post_id';
    $stmt = $db_connection->prepare($sql);
    $stmt->bindValue(':new_content', $new_text, PDO::PARAM_STR);
    $stmt->bindValue(':post_id', $post_id, PDO::PARAM_INT);
    $stmt->execute();
    $db_connection->commit();

  } catch (Exception $e) {
    rollback_if_in_transaction($db_connection);
    throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
  }
}


/**
 * Funzione per eliminare un'immagine da un post.
 * Provvede rimuovere il nome dell'immagine dal database e, dopo essersi assicurata che l'immagine non sia utilizzata anche da altri post, a rimuovere il file dal filesystem
 * @param PDO $db_connection
 * @param int $post_id
 * @throws \Exception
 * @return void
 */
function delete_post_image(PDO $db_connection, int $post_id)
{
  try {
    /* Recupero i valori da usare nell'elaborazione successiva.
    Per ragioni di chiarezza, uso delle variabili anche per il valore booleano di certe funzioni, 
    piuttosto che inserire quelle funzioni direttamente nella condizione dell'if */
    $post_image_name = get_post_info($db_connection, $post_id)[POST_IMAGE];
    $post_has_image = !is_null($post_image_name);
    $post_image_is_unique = is_image_unique($db_connection, $post_image_name);

    if ($post_has_image) { // Mi assicuro che il post sia dotato di immagine, prima di intraprendere qualunque operazione ulteriore
      $db_connection->beginTransaction();

      // Rimozione del valore dal database
      $sql = 'UPDATE  ' . POST_TABLE . ' SET ' . POST_IMAGE . ' = NULL WHERE ' . POST_ID . ' = :post_id';
      $stmt = $db_connection->prepare($sql);
      $stmt->bindValue(':post_id', $post_id);
      $stmt->execute();

      // Rimozione del file dal filesystem
      if ($post_image_is_unique) {
        delete_image($post_image_name);
      }

      $db_connection->commit();
    }

  } catch (Exception $e) {
    rollback_if_in_transaction($db_connection);
    throw new Exception('');

  }
}

/**
 * Funzione per aggiungere o cambiare l'immagine a un post.
 * @param PDO $db_connection Connessione al database
 * @param int $post_id ID del post su cui si vuole operare
 * @param array $new_image_file corrisponde a $_FILES['nome_campo_campo_immagine']
 * @throws Exception in caso di errore
 * @return string | null NULL se l'utente sceglie un'immagine identica alla precedente, STRING con il nome della nuova immagine altrimenti.
 */
function update_post_image(PDO $db_connection, int $post_id, array $new_image_file): void
{
  try {
    $old_image_name = get_post_info($db_connection, $post_id)[POST_IMAGE];
    $new_image_name = calculate_new_image_name($new_image_file);

    // Se la nuova immagine è identica alla vecchia, non faccio nulla
    if ($new_image_name === $old_image_name) {
      return;
    }

    $db_connection->beginTransaction();

    $sql = 'UPDATE ' . POST_TABLE . ' 
      SET ' . POST_IMAGE . ' = :new_image, 
      ' . POST_LAST_EDIT . ' = NOW() 
      WHERE ' . POST_ID . ' = :post_id';

    $stmt = $db_connection->prepare($sql);
    $stmt->bindValue(':new_image', $new_image_name, PDO::PARAM_STR);
    $stmt->bindValue(':post_id', $post_id, PDO::PARAM_INT);
    $stmt->execute();

    save_image($new_image_file, $new_image_name); // Copia il file con un nome opportuno nella giusta cartella del server

    if (is_image_unique($db_connection, $old_image_name)) {
      delete_image($old_image_name);
    }

    $db_connection->commit();

  } catch (Exception $e) {
    rollback_if_in_transaction($db_connection);
    throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
  }
}

/**
 * Ottieni l'elenco dei post pubblicati in un certo blog
 * @param PDO $db_connection Connessione al database
 * @param int $blog_id ID del blog
 * @throws Exception in caso di errore
 * @return array | null array associativo contenente i post del blog. Se il blog non ha post, viene restituito NULL
 */
function get_posts_list_from_blog(PDO $db_connection, int $blog_id): array|null
{
  try {
    $sql = 'SELECT * FROM `' . POST_TABLE . '`
    WHERE `' . POST_BLOG . '` = :blog_id
    ORDER BY Post.' . POST_LAST_EDIT . ' DESC';

    $stmt = $db_connection->prepare($sql);
    $stmt->bindValue(':blog_id', $blog_id, PDO::PARAM_INT);
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($posts)) {
      $posts = null;
    }

    return $posts;

  } catch (Exception $e) {
    throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
  }
}

/**
 * Ottieni la lista di post pubblicati da un determinato utente.
 * @param PDO $db_connection Connessione al DB
 * @param string $username Utente di cui si vogliono ottenere i post
 * @throws Exception In caso di errore 
 * @return array Contenete i post dell'utente scelto 
 */
function get_posts_list_by_author(PDO $db_connection, string $username)
{
  try {

    $sql = 'SELECT p.* FROM ' . POST_TABLE . ' p
              JOIN ' . USER_TABLE . ' u ON p.' . POST_AUTHOR . ' = u.' . USER_USERNAME . '
              WHERE u.' . USER_USERNAME . ' = :username
              ORDER BY p.' . POST_LAST_EDIT . ' DESC';
    $stmt = $db_connection->prepare($sql);
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);

  } catch (Exception $e) {
    throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
  }
}

/**
 * Funzione per ottenere le informazioni su un post.
 * Le informazioni sono organizzate in un array associativo, le cui chiavi sono le colonne della tabella POST_TABLE, ossia:
 * - POST_AUTHOR => username dell'autore del Post
 * - POST_BLOG => ID del blog su cui è stato pubblicato il post
 * - POST_TITLE => titolo del post
 * - POST_TEXT => contenuto testuale del post
 * - POST_IMAGE => Nome di un eventuale immagine associata a un post
 * - POST_CREATION => Timestamp della data e ora di creazione del post
 * - POST_LAST_EDIT => Timestamp della data e ora dell'ultima creazione del post
 * - GOOD_FEEDBACKS => Numero di feedback positivi totalizzati dal post
 * - BAD_FEEDBACKS => Numero di feedback negativi totalizzati dal post
 * @param PDO $db_connection Connessione al database
 * @param int $post_id ID del post di cui ottenere le informazioni
 * @param string | null $user Nome utente corrente. Qualora sia diverso da NULL, viene usato per conoscere il feedback che $user ha dato al post
 * @throws Exception in caso di errore
 * @return array | bool Restituisce un array con le informazioni del post qualora il post esista, FALSE altrimenti. Qualora l'ID passato sia negativo, viene restituito FALSE senza eseguire alcuna query
 */
function get_post_info(PDO $db_connection, int $post_id, ?string $user = null): array|bool
{
  if ($post_id < 0) {
    throw new Exception('Il post non può avere un ID negativo');
  }

  /*
    Interrogando una tabella contenente una data con un orario, MySQL restituisce un datetime 
    nella forma YYYY-MM-DD hh:mm:ss sia che la colonna sia di tipo TIMESTAMP che di tipo DATETIME.
    Per ottenere un timestamp vero e proprio (il numero di secondi passati dalla Unix Epoch) da utilizzare poi nella funzione date(), effettuo la conversione con UNIX_TIMESTAMP 
  */
  $sql = '
    SELECT posts.*, 
          UNIX_TIMESTAMP(posts.`' . POST_CREATION . '`) AS `' . POST_CREATION . '`,
          UNIX_TIMESTAMP(posts.`' . POST_LAST_EDIT . '`) AS `' . POST_LAST_EDIT . '`,
          SUM(CASE WHEN feedbacks.`' . FEEDBACK_TYPE . '` = :good_feedback THEN 1 ELSE 0 END) AS GOOD_FEEDBACKS,
          SUM(CASE WHEN feedbacks.`' . FEEDBACK_TYPE . '` = :bad_feedback THEN 1 ELSE 0 END) AS BAD_FEEDBACKS,
          MAX(user_feedback.`' . FEEDBACK_TYPE . '`) AS CURRENT_USER_FEEDBACK
    FROM `' . POST_TABLE . '` AS posts
    LEFT JOIN `' . FEEDBACK_TABLE . '` AS feedbacks 
          ON posts.`' . POST_ID . '` = feedbacks.`' . FEEDBACK_POST . '`
    LEFT JOIN `' . FEEDBACK_TABLE . '` AS user_feedback 
          ON posts.`' . POST_ID . '` = user_feedback.`' . FEEDBACK_POST . '`
          AND user_feedback.`' . FEEDBACK_AUTHOR . '` = :user
    WHERE posts.`' . POST_ID . '` = :post_id
    GROUP BY posts.`' . POST_ID . '`
  ';


  $stmt = $db_connection->prepare($sql);
  $stmt->bindValue(':post_id', $post_id, PDO::PARAM_INT);
  $stmt->bindValue(':user', $user,  PDO::PARAM_STR);
  $stmt->bindValue(':good_feedback', GOOD_FEEDBACK, PDO::PARAM_INT);
  $stmt->bindValue(':bad_feedback', BAD_FEEDBACK, PDO::PARAM_INT);
  $stmt->execute();

  $post_info = $stmt->fetch(PDO::FETCH_ASSOC);

  return $post_info;
}

/**
 * Funzione per ottenere l'ID del post attualmente visualizzato
 * @return int ID del post
 */
function get_current_post_id(): int
{
  return $_SESSION['current_post'][POST_ID];
}

/**
 * Ottieni il testo del post attualmente visualizzato
 * @return string Testo del post
 */
function get_current_post_text(): string
{
  return $_SESSION['current_post'][POST_TEXT];
}

/**
 * Ottieni il nome dell'immagine del post attualmente visualizzato
 * @return string | null nome dell'immagine del post, NULL se il post ne è privo
 */
function get_current_post_image(): ?string
{
  return $_SESSION['current_post'][POST_IMAGE];
}

/**
 * Ottieni il nome dell'autore del post attualmente visualizzato
 * @return string Nome dell'autore del post
 */
function get_current_post_author(): string
{
  return $_SESSION['current_post'][POST_AUTHOR];
}

/**
 * Ottieni la data di creazione del post attualmente visualizzato
 * @return int La data di creazione del post, in formato unix timestamp
 */
function get_current_post_creation_date(): int
{
  return $_SESSION['current_post'][POST_CREATION];
}

/**
 * Ottieni la data di ultima modifica del post attualmente visualizzato
 * @return int La data di ultima modifica del post, in formato unix timestamp
 */
function get_current_post_last_edit_date(): int
{
  return $_SESSION['current_post'][POST_LAST_EDIT];
}

/**
 * Ottieni l'ID del blog a cui il post appartiene.
 * @return int ID del blog a cui il post appartiene.
 */
function get_current_post_blog(): int
{
  return $_SESSION['current_post'][POST_BLOG];
}

/**
 * Ottiene il titolo del post corrente
 * @return string titolo del post corrente
 */
function get_current_post_title(): string
{
  return $_SESSION['current_post'][POST_TITLE];
}

/**
 * Ottiene il numero di commenti del post corrente
 * @return int Numero di commenti del post corrente
 */
function get_current_post_comments_number(): int
{
  return $_SESSION['current_post'][POST_COMMENTS_NUMBER];
}

/**
 * Ottiene il numero di feedback negativi ricevuti dal post corrente.
 * @return int Numero di feedback negativi del post corrente
 */
function get_current_post_bad_feedbacks_number(): int
{
  return $_SESSION['current_post']['BAD_FEEDBACKS'];
}

/**
 * Ottiene il numero di feedback positivi ricevuti dal post corrente.
 * @return int Numero di feedback positivi del post corrente
 */
function get_current_post_good_feedbacks_number(): int
{
  return $_SESSION['current_post']['GOOD_FEEDBACKS'];
}


/**
 * Ottieni il feedback che l'utente corrente ha dato al post corrente.
 * @return int | null feedback dell'utente corrente (GOOD_FEEDBACK, BAD_FEEDBACK o null)
 */
function get_current_user_feedback(): int | null
{
  $current_user_feedback = $_SESSION['current_post']['CURRENT_USER_FEEDBACK'];
  
  if(!is_feedback_valid($current_user_feedback)){
    throw new UnexpectedValueException("$current_user_feedback non è un valore valido per un feedback");
  }

  return $current_user_feedback;
}

/**
 * Funzione per sapere se il post corrente è dotato o meno di immagine.
 * @return bool TRUE se il post ha un'immagine allegata, FALSE altrimenti
 */
function has_current_post_image(): bool
{
  return is_string($_SESSION['current_post'][POST_IMAGE]);
}

function set_current_feedback(?int $feedback): void
{
  if (!is_feedback_valid($feedback)) return;
  
  $_SESSION['current_post']['CURRENT_USER_FEEDBACK'] = $feedback;
}

function set_current_post(array $post): void
{
  if(session_status() === PHP_SESSION_NONE) session_start();

  $_SESSION['current_post'] = $post;
}

function there_is_a_current_post(): bool
{
  return isset($_SESSION['current_post']) && is_array($_SESSION['current_post']);
}

/**
 * Cancella dalla variabile di sessione il post corrente, per evitare, ad esempio, che qualcuno,
 * visitando la pagina editpost.php dopo aver visto vari post, finisca ad editare un post rimasto in memoria. 
 * @return void
 */
function reset_current_post(): void
{
  if(there_is_a_current_post()){
    unset($_SESSION['current_post']);
  }
}