<?php
/**
 * Funzione per ottenere l'elenco dei commenti sotto un certo post.
 * Questa funzione ottiene solo i commenti di livello zero, ossia solo i commenti postati direttamente sotto il post dato,
 * escludendo i commenti di risposta ad altri commenti.
 * @param PDO $db_connection connessione al database
 * @param int $post_id ID del post di cui recuperare commenti
 * @throws Exception in caso di errore
 * @return array array associativo con i commenti del post. L'array ha struttura: 
 * [
 *  0: [COMMENT_ID] => id_commento, [COMMENT_AUTHOR] => 'nomeutente_autore' ...
 *  1: ...
 * ]
 */
function get_zero_level_post_comments(PDO $db_connection, int $post_id): array
{
  $sql = 'SELECT
  commenti.*, 
  UNIX_TIMESTAMP(commenti.' . COMMENT_CREATION_DATE . ') AS ' . COMMENT_CREATION_DATE . ',
  UNIX_TIMESTAMP(commenti.' . COMMENT_LAST_EDIT . ') AS ' . COMMENT_LAST_EDIT . '
  FROM ' . COMMENT_TABLE . ' AS commenti
  LEFT JOIN ' . COMMENT_REPLY_TABLE . ' AS risposte
  ON commenti.' . COMMENT_ID . ' = risposte.' . COMMENT_REPLY_ID . '
  WHERE commenti.' . COMMENT_POST . ' = :post_id
  AND risposte.' . COMMENT_REPLY_ID . ' IS NULL
  ORDER BY commenti.' . COMMENT_CREATION_DATE . ' DESC';

  try {
    $stmt = $db_connection->prepare($sql);
    $stmt->bindValue(':post_id', $post_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
  }
}

/**
 * Funzione per aggiungere un commento a un post.
 * @param PDO $db_connection Connessione al database
 * @param int $post_id ID del post sotto cui mettere un commento
 * @param string $comment_author Username dell'autore del commento
 * @param string $comment_text Testo effettivo del commento
 * @param int|null $main_comment_id Qualora il commento sia una risposta, questo è l'ID del commento principale. Di default ha valore null, e assume quindi che il commento sia un commento 'di livello 0', dunque che ad essere commentato sia il post stesso
 * @throws Exception in caso di errore
 * @return int ID del commento appena creato
 */
function add_comment(PDO $db_connection, int $post_id, string $comment_author, string $comment_text, int|null $main_comment_id = null): int
{
  $this_comment_is_a_reply = (!is_null($main_comment_id) && does_comment_exist($db_connection, $main_comment_id)) ? true : false;
  $sql = 'INSERT INTO ' . COMMENT_TABLE . ' (
    ' . COMMENT_POST . ', 
    ' . COMMENT_AUTHOR . ', 
    ' . COMMENT_CREATION_DATE . ', 
    ' . COMMENT_TEXT . ') 
    VALUES (:post_id, :author_name, NOW(), :content)';

  try {
    $db_connection->beginTransaction();

    $stmt = $db_connection->prepare($sql);
    $stmt->bindValue(':post_id', $post_id, PDO::PARAM_INT);
    $stmt->bindValue(':author_name', $comment_author, PDO::PARAM_STR);
    $stmt->bindValue(':content', $comment_text, PDO::PARAM_STR);
    $stmt->execute();
    $current_comment_id = $db_connection->lastInsertId();

    if ($this_comment_is_a_reply) {
      add_reply($db_connection, $main_comment_id, $current_comment_id);
    }

    $db_connection->commit();

    return $current_comment_id;

  } catch (PDOException $e) {
    rollback_if_in_transaction($db_connection);
    throw new Exception('Errore nella funzione add_comment: ' . $e->getMessage());
  }
}

/**
 * Elimina un commento.
 * Eliminare un commento implica l'eliminazione di tutte le sue risposte.
 * @param PDO $db_connection Connessione al database
 * @param int $comment_id ID del commento da eliminare
 * @throws Exception In caso di errore
 * @return void
 */
function delete_comment(PDO $db_connection, int $comment_id): void
{
  try {
    $comment_and_replies_ids = get_comment_and_its_replies_id_cascade($db_connection, $comment_id);
    $db_connection->beginTransaction();

    $sql_first_part = 'DELETE FROM ' . COMMENT_TABLE . ' WHERE ' . COMMENT_ID . ' IN ';

    $placeholder_to_value = build_placeholder_to_value_from_an_array($comment_and_replies_ids, 'comment');

    $placeholders = array_keys($placeholder_to_value);
    $placeholder_list = implode(', ', $placeholders);
    $sql_condition = "($placeholder_list)";

    $full_query = $sql_first_part . $sql_condition;

    $stmt = $db_connection->prepare($full_query);

    foreach ($placeholder_to_value as $placeholder => $value) {
      $stmt->bindValue($placeholder, $value, PDO::PARAM_INT);
    }

    $stmt->execute();
    $db_connection->commit();
    
  } catch (Exception $e) {
    rollback_if_in_transaction($db_connection);
    throw new Exception('Errore nella funzione del_comment: ' . $e->getMessage());
  }
}

/**
 * Funzione per determinare l'esistenza o meno di un commento.
 * Un commento viene considerato non esistente qualora:
 *  - l'ID fornito non fosse un numero intero
 *  - l'ID fornito fosse un numero negativo
 *  - la ricerca nel database restituisce 0.
 * Qualora ci siano più commenti con lo stesso ID, viene sollevata un eccezione.
 * @param PDO $db_connection Connessione al db
 * @param int $comment_id ID del commento. Deve essere un intero maggiore o uguale a zero
 * @throws Exception In caso di errore della query o qualora la ricerca nel database restituisca più commenti col medesimo ID
 * @return bool TRUE qualora il commento esista e sia unico, FALSE o un eccezione in tutti gli altri casi.
 */
function does_comment_exist(PDO $db_connection, int $comment_id): bool
{
  if ($comment_id <= 0) {
    return false;
  }

  $sql = 'SELECT COUNT(*) FROM ' . COMMENT_TABLE . ' WHERE ' . COMMENT_ID . ' = :comment_id';

  try {

    $stmt = $db_connection->prepare($sql);
    $stmt->bindValue(':comment_id', $comment_id, PDO::PARAM_INT);
    $stmt->execute();
    $comments_count = $stmt->fetchColumn();

    if ($comments_count > 1) {
      throw new Exception("Errore nel tuo database: hai $comments_count commenti con lo stesso ID");
    }

    $comment_existance = $comments_count === 1;

    return $comment_existance;

  } catch (Exception $e) {
    throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
  }
}


/**
 * Funzione per aggiornare un commento, ovvero per cambiarne il testo.
 * @param PDO $db_connection Connessione al DB
 * @param int $comment_id ID del commento da aggiornare
 * @param string $new_text Nuovo testo del commento
 * @throws Exception In caso di errore
 */
function update_comment(PDO $db_connection, int $comment_id, string $new_text)
{
  $sql = 'UPDATE `' . COMMENT_TABLE . '` SET 
  `' . COMMENT_TEXT . '` = :new_text, 
  `' . COMMENT_LAST_EDIT . '` = NOW() 
  WHERE `' . COMMENT_ID . '` = :comment_id ';

  try {
    $db_connection->beginTransaction();
    $stmt = $db_connection->prepare($sql);
    $stmt->bindValue(':new_text', $new_text, PDO::PARAM_STR);
    $stmt->bindValue(':comment_id', $comment_id, PDO::PARAM_INT);
    $stmt->execute();

    $db_connection->commit();

  } catch (Exception $e) {
    rollback_if_in_transaction($db_connection);
    throw new Exception('Errore nella funzione update_comment: ' . $e->getMessage());
  }
}


/**
 * Recupera le risposte a un commento principale dal database.
 *
 * @param PDO $db_connection La connessione al database tramite PDO.
 * @param int $main_comment_id L'ID del commento principale.
 * @return array Un array contenente le risposte al commento.
 * @throws Exception in caso di errore Se si verifica un errore nel recupero delle risposte.
 */
function get_replies(PDO $db_connection, int $main_comment_id): array
{
  $sql = '
  SELECT 
      commenti.*,
      UNIX_TIMESTAMP(commenti.' . COMMENT_CREATION_DATE . ') AS ' . COMMENT_CREATION_DATE . ',
      UNIX_TIMESTAMP(commenti.' . COMMENT_LAST_EDIT . ') AS ' . COMMENT_LAST_EDIT . '
  FROM 
      ' . COMMENT_TABLE . ' commenti
  INNER JOIN 
      ' . COMMENT_REPLY_TABLE . ' risposte ON commenti.`' . COMMENT_ID . '` = risposte.' . COMMENT_REPLY_ID . '
  WHERE 
      risposte.' . COMMENT_REPLY_MAIN_ID . ' = :main_comment_id 
  ORDER BY 
      commenti.' . COMMENT_CREATION_DATE . ' ASC';

  try {
    $stmt = $db_connection->prepare($sql);
    $stmt->bindValue(':main_comment_id', $main_comment_id, PDO::PARAM_INT);
    $stmt->execute();

    // Recupera tutte le risposte come array associativo
    $replies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $replies;
  } catch (PDOException $e) {
    throw new Exception('Errore nel recupero delle risposte: ' . $e->getMessage());
  }
}


/**
 * Funzione per l'aggiunta di una risposta a un commento.
 * Dato l'ID di un commento e quello di un commento principale, viene inserita nella tabella COMMENT_REPLY_TABLE una riga con gli ID dei due commenti.
 * @param PDO $db_connection Connessione al database
 * @param int $main_comment_id ID del commento principale
 * @param int $current_comment_id ID del commento attuale (risposta)
 * @throws \Exception In caso di errore durante l'esecuzione della query
 * @return void
 */
function add_reply(PDO $db_connection, int $main_comment_id, int $current_comment_id)
{
  $sql = 'INSERT INTO ' . COMMENT_REPLY_TABLE . ' (' . COMMENT_REPLY_MAIN_ID . ', ' . COMMENT_REPLY_ID . ')
                VALUES (:main_comment_id, :current_comment_id)';
  try {
    $stmt = $db_connection->prepare($sql);
    $stmt->bindParam(':main_comment_id', $main_comment_id, PDO::PARAM_INT);
    $stmt->bindParam(':current_comment_id', $current_comment_id, PDO::PARAM_INT);
    $stmt->execute();
  } catch (Exception $e) {
    throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
  }
}

/**
 * Ottiene un commento e tutte le sue risposte, così come le risposte alle risposte ecc...
 * @param PDO $db_connection
 * @param int $comment_id
 * @return array
 */
function get_comment_and_its_replies_id_cascade(PDO $db_connection, int $comment_id): array
{
  static $complete_replies = [];

  $complete_replies[] = $comment_id;

  $comment_replies_ids = array_column(get_replies($db_connection, $comment_id), COMMENT_ID);

  foreach ($comment_replies_ids as $reply_id) {
    get_comment_and_its_replies_id_cascade($db_connection, $reply_id);
  }

  return $complete_replies;
}

/**
 * Ottieni le informazioni di un singolo commento.
 * @param PDO $db_connection Connessione al database
 * @param int $comment_id ID del commento di cui ottenere le informazioni
 * @throws Exception Qualora il commento non esista
 * @return array
 */
function get_comment_info(PDO $db_connection, int $comment_id): array
{
  if ($comment_id < 0) {
    throw new Exception('Il commento non esiste');
  }

  $sql = 'SELECT * FROM ' . COMMENT_TABLE .
    ' WHERE ' . COMMENT_ID . ' = :comment_id';

  $stmt = $db_connection->prepare($sql);
  $stmt->bindValue(':comment_id', $comment_id, PDO::PARAM_INT);
  $stmt->execute();
  $comment_info = $stmt->fetch(PDO::FETCH_ASSOC);
  return $comment_info;
}

/**
 * Funzione per mostrare il form per la pubblicazione di un nuovo commento.
 * I dettagli relattivi al fatto che il commento sia un commento di livello zero o una risposta sono cambiati automaticamente dalla funzione.
 * Ad esempio, la funzione si occupa di scrivere "Commenta" nel pulsante dei commenti di livello zero e "Rispondi" nei commenti di risposta.
 * @param int | null $main_comment_id ID del commento a cui si sta rispondendo. 
 * Qualora questo argomento sia NULL, si assume che il commento sia di primo livello; se è un numero intero, si assume che sia una risposta. 
 * Default: NULL
 * @throws InvalidArgumentException Qualora l'ID del commento sia un numero negativo
 * @return void
 */
function show_comment_form(?int $main_comment_id = null): void
{
  $is_a_reply = is_int($main_comment_id);

  if($is_a_reply && $main_comment_id < 0){
    throw new InvalidArgumentException('Il commento principale non può avere un ID negativo');
  }

  $comment_author = get_current_user_username();
  $comment_author_avatar_path = get_image_path(get_current_user_avatar());
  $button_text = $is_a_reply ? 'Rispondi' : 'Commenta';
  $attribute_list = ['comment_form', 'form_group'];
  if($is_a_reply){
    $attribute_list = array_merge($attribute_list, ['reply_form']);
  }
  $attribute_list_ready_to_print = implode(' ', $attribute_list);
  
  ?>

  <div class="<?php echo $attribute_list_ready_to_print ?>" >
    <div class="textarea_group">
      
      <img class="comment_avatar" src="<?php echo $comment_author_avatar_path; ?>"
        alt="Avatar di <?php echo $comment_author ?>" />
      <textarea class="new_comment_textarea" id="new_comment_textarea"name="comment_text" rows="1" placeholder="Scrivi il tuo commento" required></textarea>
      
      <div class="hidden">
        <?php
        if ($is_a_reply): ?>
          <input type="hidden" value="<?php echo $main_comment_id ?>" data-type=""/>
        <?php endif; ?>
        <input type="reset" class="little_button" value="Annulla" />
        <button type="button" class="little_button new_comment_button" id="new_comment_button" data-main-comment-id="<?php echo $main_comment_id?>">
          <?php echo $button_text ?>
        </button>
      </div>
    </div>
    <span class="filter_error_messages" id="comment_error"></span>
  </div>

  <?php
}