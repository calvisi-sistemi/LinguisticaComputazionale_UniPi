<?php
/**
 * Funzioni per la gestione degli utenti.
 */

/**
 * Creazione di un utente. La funzione controlla se un utente con lo stesso username o email già esiste, prima di scrivere una query sul database.
 * @param PDO $db_connection Connessione al database
 * @param string $username Nome utente
 * @param string $user_password Password dell'utente in chiaro (la funzione si occupa autonomamente del suo hashing) 
 * @param string $user_email E-mail dell'utente
 * @param string $user_complete_name Nome completo dell'utente
 * @param array | null $avatar_file File dell'avatar dell'utente, $_FILES['nome_campo_avatar']. Qualora sia null, viene usato l'avatar di default 
 * @throws Exception in caso di errore o di informazioni non valide fornite dall'utente.
 * @return void
 */
function create_user(PDO $db_connection, string $username, string $user_password, string $user_email, string $user_complete_name, ?string $user_bio, ?array $avatar_file = null): void
{

  $sql = 'INSERT INTO ' . USER_TABLE . ' (
    `' . USER_USERNAME . '`, 
    `' . USER_PASSWORD . '`, 
    `' . USER_EMAIL . '`, 
    `' . USER_COMPLETE_NAME . '`,
    `' . USER_BIO . '`,
    `' . USER_AVATAR . '`
) VALUES (:username, :password, :email, :complete_name, :user_bio, :avatar)';

  try {
    $hashed_password = password_hash(password: $user_password, algo: PASSWORD_BCRYPT);
    $user_chose_an_avatar = is_array($avatar_file);
    $new_avatar_name = $user_chose_an_avatar ? calculate_new_image_name($avatar_file) : AVATAR_DEFAULT_NAME;

    $db_connection->beginTransaction();

    $stmt = $db_connection->prepare($sql);

    $stmt->bindValue(param: ':username', value: $username, type: PDO::PARAM_STR);
    $stmt->bindValue(param: ':password', value: $hashed_password, type: PDO::PARAM_STR);
    $stmt->bindValue(param: ':email', value: $user_email, type: PDO::PARAM_STR);
    $stmt->bindValue(param: ':complete_name', value: $user_complete_name, type: PDO::PARAM_STR);
    $stmt->bindValue(param: ':user_bio', value: $user_bio, type: PDO::PARAM_STR);
    $stmt->bindValue(param: ':avatar', value: $new_avatar_name, type: PDO::PARAM_STR);

    $stmt->execute();

    if ($user_chose_an_avatar) {
      save_image($avatar_file, $new_avatar_name);
    }

    $db_connection->commit();

  } catch (Exception $e) {
    rollback_if_in_transaction(db_connection: $db_connection);
    throw new Exception(message: FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
  }
}

/**
 * Cancella un utente dal database ed elimina il relativo avatar.
 * La cancellazione di un utente comporta la cancellazione di:
 *  - tutti i blog di cui è amministratore con relative immagini
 *  - tutti i post di cui è autore con relative immagini
 *  - tutti i commenti di cui è autore.
 * @param PDO $db_connection connessione al database
 * @param string $username username dell'utente
 * @throws Exception in caso di errore
 * @return void
 */
function delete_user(PDO $db_connection, string $username): void
{
  try {
    $db_connection->beginTransaction();

    $user_avatar = get_user_info(db_connection: $db_connection, username: $username)[USER_AVATAR];

    // Essendo le immagini salvate con il loro SHA1 come nome, bisogna controllare se una stessa immagine è utilizzata o meno da più utenti, prima di cancellarla
    $only_this_user_had_this_avatar = is_image_unique($db_connection, $user_avatar);
    $user_avatar_is_not_default = $user_avatar !== AVATAR_DEFAULT_NAME;
    $user_blog_pics = get_blog_pics_by_user($db_connection, $username);
    $user_posted_pics = get_user_posted_pics($db_connection, $username);

    $user_blog_pics_to_delete = get_unique_images($db_connection, $user_blog_pics);
    $user_posted_pics_to_delete = get_unique_images($db_connection, $user_posted_pics);

    // Passo 2: Eliminare l'utente dal database
    $delete_query = 'DELETE FROM ' . USER_TABLE . ' WHERE ' . USER_USERNAME . ' = :username';
    $delete_stmt = $db_connection->prepare(query: $delete_query);
    $delete_stmt->bindValue(param: ':username', value: $username, type: PDO::PARAM_STR);
    $delete_stmt->execute();

    // Passo 3: Eliminare il file dell'avatar
    if ($only_this_user_had_this_avatar && $user_avatar_is_not_default) {
      delete_image($user_avatar);
    }

    // Passo 4: Eliminare le immagini dei blog creati dall'utente
    if (!empty($user_blog_pics_to_delete)) {
      foreach ($user_blog_pics_to_delete as $pic) {
        delete_image($pic);
      }
    }

    // Passo 5: Eliminare le immagini allegate ai post dell'utente
    if (!empty($user_posted_pics_to_delete)) {
      foreach ($user_posted_pics_to_delete as $pic) {
        delete_image($pic);
      }
    }

    $db_connection->commit();

  } catch (Exception $e) {
    rollback_if_in_transaction(db_connection: $db_connection);
    throw new Exception(message: FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
  }
}

/**
 * Cancella l'avatar personalizzato dell'utente, mentre lascia intatto l'avatar di default.
 * Si occupa sia di cancellare il file dell'avatar sia di 
 * @param PDO $db_connection Connessione al database
 * @param string $username Nome utente di cui cancellare l'avatar
 * @throws Exception in caso di errore
 * @return void
 */
function delete_user_avatar(PDO $db_connection, string $username): void
{
  try {
    $avatar_name = get_user_info(db_connection: $db_connection, username: $username)[USER_AVATAR];

    if ($avatar_name !== AVATAR_DEFAULT_NAME) {
      $only_this_user_had_this_avatar = is_image_unique($db_connection, $avatar_name);

      $db_connection->beginTransaction();

      $sql = 'UPDATE ' . USER_TABLE . ' SET `' . USER_AVATAR . '` = \'' . AVATAR_DEFAULT_NAME . '\' WHERE `' . USER_USERNAME . '` = :username';
      $stmt = $db_connection->prepare(query: $sql);
      $stmt->bindValue(param: ':username', value: $username, type: PDO::PARAM_STR);
      $stmt->execute();

      if ($only_this_user_had_this_avatar) {
        delete_image($avatar_name);
      }

      $db_connection->commit();

    }

  } catch (Exception $e) {
    rollback_if_in_transaction(db_connection: $db_connection);
    throw new Exception(message: FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
  }
}

/**
 * Ottieni l'elenco dei nomi delle immagini presenti nei post di un dato utente.
 * @param PDO $db_connection Connessione al DB
 * @param string $username dell'utente di cui si vuole ottenere l'elenco delle immagini postate
 * @throws Exception in caso di errore
 * @return array contenente l'elenco dei nomi di immagini postate.
 */
function get_user_posted_pics(PDO $db_connection, string $username): array
{
  try {
    $users_post_list = get_posts_list_by_author($db_connection, $username);
    $pics = [];

    foreach ($users_post_list as $post) {
      $pics[] = $post[POST_IMAGE];
    }

    return $pics;
  } catch (Exception $e) {
    throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
  }
}

/**
 * Funzione per iil controllo dell'esistenza di un utente
 * @param PDO $db_connection Connessione al database
 * @param mixed $username Utente di cui si vuole controllare l'esistenza
 * @throws Exception in caso di errore
 * @return bool Restituisce TRUE se l'utente esiste, FALSE altrimenti.
 */
function do_user_exist(PDO $db_connection, $username)
{
  $sql = 'SELECT COUNT(*) FROM ' . USER_TABLE . ' WHERE ' . USER_USERNAME . ' = :username';

  try {
    $stmt = $db_connection->prepare($sql);
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $number_of_users = $stmt->fetchColumn();

    if ($number_of_users > 1) {
      throw new Exception("C'è un problema nel tuo database: ci sono $number_of_users con il nome utente $username.");
    }

    $user_exists = $number_of_users == 1;
    return $user_exists;
  } catch (Exception $e) {
    throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
  }
}

/**
 * Controlla se un indirizzo email è già stato usato o meno da un utente.
 * @param PDO $db_connection Connessione al database
 * @param string $email Indirizzo e-mail da controllare
 * @throws Exception In caso di errore nell'esecuzione della query, o qualora l'indirizzo e-mail sia stato usato più di una volta nel database.
 * @return bool TRUE qualora l'indirizzo e-mail sia già stato usato, FALSE altrimenti
 */
function is_email_already_in_use(PDO $db_connection, string $email): bool
{
  try {
    $sql = 'SELECT COUNT(*) FROM ' . USER_TABLE . ' WHERE `' . USER_EMAIL . '` = :email';
    $stmt = $db_connection->prepare($sql);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $times_email_was_used = $stmt->fetchColumn();

    if ($times_email_was_used > 1) {
      throw new UnexpectedValueException("C'è un problema nel tuo database: l'email $email è stat usata $times_email_was_used volte invece di una sola.");
    }

    $email_is_already_used = $times_email_was_used == 1;

    return $email_is_already_used;

  } catch (Exception $e) {
    throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
  }
}

/**
 * Ottieni le informazioni su un utente, e restituiscile sotto forma di dizionario o array associativo.
 * @param PDO $db_connection Connessione al DB su cui lavorare
 * @param string $username Nome utente di cui si vogliono ottenere le informazioni
 * @throws Exception in caso di errore
 * @return array Restituisce un array associativo con le informazioni di un utente, dove ogni chiave corrisponde a una colonna della tabella USER_TABLE.
 */
function get_user_info(PDO $db_connection, string $username): array
{
  $sql = 'SELECT * FROM ' . USER_TABLE . ' WHERE ' . USER_USERNAME . ' = :username';
  try {
    $stmt = $db_connection->prepare($sql);
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $user_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user_info === false) {
      throw new Exception('Nessun utente trovato con il nome utente: $username.');
    }

    return $user_info;
  } catch (Exception $e) {
    throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
  }
}

/**
 * Funzione per la verifica dello status premium di un utente.
 * @param PDO $db_connection Connessione al database
 * @param string $username Utente di cui si vuole verificare lo status
 * @throws Exception in caso di errore
 * @return bool Restituzione di TRUE in caso di utente premium, FALSE altrimenti
 */
function is_user_premium(PDO $db_connection, string $username): bool
{
  $sql = 'SELECT ' . USER_PREMIUM_STATUS . ' FROM `' . USER_TABLE . '` WHERE `' . USER_USERNAME . '` = :username';

  try {
    $user_does_not_exist = !do_user_exist($db_connection, $username);

    if ($user_does_not_exist) {
      throw new Exception("L'utente $username non esiste");
    }

    $stmt = $db_connection->prepare($sql);
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    $user_is_premium = (bool) $stmt->fetchColumn();

    return $user_is_premium;
  } catch (Exception $e) {
    throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
  }
}

/**
 * Verifica che un utente sia amministratore di un dato blog
 * @param PDO $db_connection Connessione al database
 * @param string $username Utente di cui si vuole verificare lo status di amministratore
 * @param int $blog_id ID del blog da controllare
 * @throws Exception in caso di errore
 * @return bool Restituisce TRUE nel caso in cui l'utente dato sia amministratore del dato blog, FALSE altrimenti
 */
function is_user_admin(PDO $db_connection, string $username, int $blog_id): bool
{
  try {

    return get_blog_info($db_connection, $blog_id)[BLOG_OWNER] === $username; // Recupero l'amministratore del dato blog e controllo lo username sia identico allo username dato

  } catch (Exception $e) {

    throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());

  }
}

/**
 * Cambiamento dell'e-mail di un utente
 * @param PDO $db_connection Connessione al DB
 * @param string $username Nome utente di cui cambiare l'e-mail
 * @param string $new_email Nuova e-mail
 * @throws Exception in caso di errore
 * @return void
 */
function update_user_email(PDO $db_connection, string $username, string $new_email): void
{
  $sql = 'UPDATE ' . USER_TABLE . ' SET `' . USER_EMAIL . '` = :new_email WHERE ' . USER_USERNAME . ' = :username';
  
  try {
    $db_connection->beginTransaction();
    $stmt = $db_connection->prepare($sql);
    $stmt->bindValue(':new_email', $new_email, PDO::PARAM_STR);
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $db_connection->commit();
  } catch (Exception $e) {
    rollback_if_in_transaction($db_connection);
    throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
  }
}

function update_user_complete_name(PDO $db_connection, $username, $new_complete_name): void
{
  $sql = 'UPDATE ' . USER_TABLE . ' SET ' . USER_COMPLETE_NAME . ' = :new_complete_name WHERE ' . USER_USERNAME . ' = :username';
  
  try {
    $db_connection->beginTransaction();
    $stmt = $db_connection->prepare($sql);
    $stmt->bindValue(':new_complete_name', $new_complete_name, PDO::PARAM_STR);
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $db_connection->commit();
  } catch (Exception $e) {
    rollback_if_in_transaction($db_connection);
    throw new Exception('Errore nella funzione change_complete_name: ' . $e->getMessage());
  }
}

function update_user_password(PDO $db_connection, $username, $new_password): void
{
  $sql = 'UPDATE ' . USER_TABLE . 
  ' SET ' . USER_PASSWORD . ' = :new_password 
  WHERE ' . USER_USERNAME . ' = :username';
  
  $new_password = password_hash($new_password, PASSWORD_BCRYPT);
  try {
    $db_connection->beginTransaction();
    $stmt = $db_connection->prepare($sql);
    $stmt->bindValue(':new_password', $new_password, PDO::PARAM_STR);
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $db_connection->commit();
  } catch (Exception $e) {
    rollback_if_in_transaction($db_connection);
    throw new Exception('Errore nella funzione change_password: ' . $e->getMessage());
  }
}

/**
 * Funzione per la modifica dell'avatar di un utente.
 * @param PDO $db_connection Connessione al DB
 * @param string $username Utente di cui cambiare l'avatar
 * @param array $new_avatar_file Corrisponde a $_FILE['nome_campo_upload_avatar']
 * @throws Exception in caso di errore
 * @return string restituisce il nome dell'avatar appena impostato se tutto va bene, non restituisce nulla in caso di errore.
 */
function update_user_avatar(PDO $db_connection, string $username, array $new_avatar_file): string
{
  
  $sql = 'UPDATE ' . USER_TABLE . ' 
  SET `' . USER_AVATAR . '` = :new_avatar 
  WHERE ' . USER_USERNAME . ' = :username';

  try {
    $old_avatar_name = get_user_info($db_connection, $username)[USER_AVATAR];

    $old_avatar_was_not_default = ($old_avatar_name !== AVATAR_DEFAULT_NAME);

    $new_avatar_name = calculate_new_image_name($new_avatar_file);

    // Se l'utente ha scelto di nuovo l'avatar che è già presente, non fare nulla
    if ($old_avatar_name === $new_avatar_name) {
      return $old_avatar_name;
    }

    $db_connection->beginTransaction();

    $stmt = $db_connection->prepare($sql);
    $stmt->bindValue(':new_avatar', $new_avatar_name, PDO::PARAM_STR);
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    if (!does_image_exist($new_avatar_name)) {
      save_image($new_avatar_file, $new_avatar_name);
    }

    if ($old_avatar_was_not_default && is_image_unique($db_connection, $old_avatar_name)) {
      delete_image($old_avatar_name); // Eliminane il file
    }

    $db_connection->commit();

    return $new_avatar_name;

  } catch (Exception $e) {
    rollback_if_in_transaction($db_connection);
    throw new Exception('Errore nella funzione update_user_avatar: ' . $e->getMessage());
  }
}

/**
 * Aggiorna la bio di un utente.
 * @param PDO $db_connection Connessione al database
 * @param string $username Username dell'utente di cui si vuole cambiare la bio
 * @param string $new_bio Nuova bio che si vuole inserire
 * @throws Exception in caso di errore.
 * @return void
 */
function update_user_bio(PDO $db_connection, string $username, string $new_bio)
{
  $sql = 'UPDATE `' . USER_TABLE . '` 
  SET `' . USER_BIO . '` = :new_bio 
  WHERE `' . USER_USERNAME . '` = :username';

  try {
    check_bio_lenght($new_bio);

    $db_connection->beginTransaction();

    $stmt = $db_connection->prepare($sql);
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->bindValue(':new_bio', $new_bio, PDO::PARAM_STR);
    $stmt->execute();

    $db_connection->commit();

  } catch (Exception $e) {
    rollback_if_in_transaction($db_connection);
    throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
  }
}

/**
 * Ottieni il nome utente dell'utente correntemente loggato
 * @throws Exception qualora nessun utente fosse loggato
 * @return string Username dell'utente corrente
 */
function get_current_user_username(): string
{
  if (!is_user_logged_in()) {
    throw new Exception('Nessun utente loggato');
  }

  return $_SESSION['current_user'][USER_USERNAME];
}

/**
 * Ottieni la bio dell'utente corrente
 * @throws Exception qualora nessun utente fosse loggato
 * @return string Bio dell'utente corrente
 */
function get_current_user_bio(): string
{
  if (!is_user_logged_in()) {
    throw new Exception('Nessun utente loggato');
  }

  return $_SESSION['current_user'][USER_BIO];
}

/**
 * Nome dell'avatar dell'utente corrente
 * @throws Exception qualora nessun utente fosse loggato
 * @return string Avatar dell'utente corrente
 */
function get_current_user_avatar(): string
{
  if (!is_user_logged_in()) {
    throw new Exception('Nessun utente loggato');
  }

  return $_SESSION['current_user'][USER_AVATAR];
}

/**
 * Ottieni il nome completo dell'utente corrente
 * @throws Exception qualora nessun utente fosse loggato
 * @return string Nome completo dell'utente corrente
 */
function get_current_user_complete_name(): string
{
  if (!is_user_logged_in()) {
    throw new Exception('Nessun utente loggato');
  }

  return $_SESSION['current_user'][USER_COMPLETE_NAME];
}

/**
 * Ottieni l'email dell'utente corrente
 * @throws Exception qualora nessun utente fosse loggato
 * @return string E-mail dell'utente corrente
 */
function get_current_user_email(): string
{
  if (!is_user_logged_in()) {
    throw new Exception('Nessun utente loggato');
  }

  return $_SESSION['current_user'][USER_EMAIL];
}

/**
 * Ottieni la data di iscrizione dell'utente corrente
 * @throws Exception qualora nessun utente fosse loggato
 * @return int Data di iscrizione dell'utente corrente in formato unix timestamp
 */
function get_current_user_singup_datetime(): int
{
  if (!is_user_logged_in()) {
    throw new Exception('Nessun utente loggato');
  }

  return $_SESSION['current_user'][USER_SIGNUP_DATETIME];
}

/**
 * Ottieni il numero totale di iscrizioni che un utente ha totalizzato nei suoi blog
 * @throws Exception qualora nessun utente fosse loggato
 * @return string Numero totale di iscrizioni ricevute da un utente
 */
function get_current_user_total_subscribers(): int
{
  if (!is_user_logged_in()) {
    throw new Exception('Nessun utente loggato');
  }

  return $_SESSION['current_user'][USER_TOTAL_SUBSCRIBERS];
}

/** 
 * Funzione per decidere se l'utente corrente ha il permesso di editare il post corrente.
 * @return bool TRUE qualora l'utente sia l'autore del post o il proprietario del blog, FALSE in tutti gli altri casi.
*/
function can_current_user_edit_current_post(): bool
{
  if(!is_user_logged_in()){
    return false;
  }

  $current_user_is_post_author = get_current_user_username() === get_current_post_author();
  $current_user_is_blog_owner = get_current_user_username() === get_current_blog_owner();
  return $current_user_is_blog_owner || $current_user_is_post_author;
}
/**
 * Funzione per conoscere lo status Premium o meno dell'utente corrente
 * @return bool TRUE se l'utente è premium, FALSE altrimenti
 */
function is_current_user_premium(): bool
{
  return $_SESSION['current_user'][USER_PREMIUM_STATUS];
}