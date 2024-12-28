<?php
/**
 * Creazione dei blog
 * @param PDO $db_connection Connessione al database
 * @param string $title Titolo del blog
 * @param mixed $category Categoria o sottocategoria, la funzione non fa distinzione fra le due
 * @param string $description Descrizione del blog
 * @param string $admin Amministratore del blog
 * @throws Exception in caso di errore
 * @return bool|int Restituisce l'ID del blog appena creato
 */
function create_blog(PDO $db_connection, string $title, $category, string $description, string $admin)
{
  try {
    $db_connection->beginTransaction();
    $sql = 'INSERT INTO ' . BLOG_TABLE .
      '('
      . BLOG_TITLE . ','
      . BLOG_CATEGORY . ','
      . BLOG_DESCRIPTION . ','
      . BLOG_OWNER . ')
      VALUES (:title, :category, :description, :admin)';

    $stmt = $db_connection->prepare($sql);
    $stmt->bindValue(':title', $title);
    $stmt->bindValue(':category', $category);
    $stmt->bindValue(':description', $description);
    $stmt->bindValue(':admin', $admin);

    $stmt->execute();
    $blog_id = $db_connection->lastInsertId();
    $db_connection->commit();

    return $blog_id;
    
  } catch (PDOException $e) {
    rollback_if_in_transaction($db_connection);
 
    $message = $e->errorInfo[1] === '1062' ? 
      FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . 'un blog con quel titolo esiste già' 
      : 
      FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage() . 'nel file ' . $e->getFile() . ' alla riga ' . $e->getLine();

    throw new Exception($message);
  }
}

/**
 * Funzione per verificare l'esistenza di un blog.
 * La funzione prende in input un ID e, prima di consultare il database, ne controlla la validità.
 * Qualora l'ID non sia un numero intero assoluto, ma contenga altri caratteri, assume che il blog non esista, senza consultare inutilmente il DB.
 * @param PDO $db_connection Connessione al database
 * @param mixed $blog_id ID del blog da consultare. Il tipo è mixed perché deve poter prendere in input un qualunque valore (anche un $_GET['id'] non filtrato) senza il rischio di sollevare un TypeError
 * @throws Exception Qualora non sia stato possibile eseguire la query o qualora la query abbia restituito un numero di blog superiore a 1
 * @return bool TRUE se il blog esiste ed è unico, FALSE in ogni altro caso
 */
function does_blog_exist(PDO $db_connection, mixed $blog_id): bool
{
  try {
    $blog_existance = false;

    $blog_id_is_valid = (ctype_digit($blog_id) && $blog_id >= 0);

    if ($blog_id_is_valid) {

      $sql = "SELECT COUNT(*) FROM " . BLOG_TABLE . " WHERE " . BLOG_ID . " = :blog_id";
      $stmt = $db_connection->prepare($sql);
      $stmt->bindValue(":blog_id", $blog_id, PDO::PARAM_INT);
      $stmt->execute();

      $number_of_blogs = $stmt->fetchColumn();

      if ($number_of_blogs > 1) {
        throw new UnexpectedValueException("C'è un problema nel tuo database: $number_of_blogs blog hanno lo stesso ID");
      } else if ($number_of_blogs == 1) {
        $blog_existance = true;
      }

    }
    return $blog_existance;
  } catch (Exception $e) {
    throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
  }

}

function delete_blog(PDO $db_connection, $blog_id)
{
  try {
    $db_connection->beginTransaction();

    $blog_logo = get_blog_info($db_connection, $blog_id)[BLOG_PIC];
    $only_this_blog_had_this_logo = is_image_unique($db_connection, $blog_logo);

    $sql = "DELETE FROM " . BLOG_TABLE . " WHERE 
      " . BLOG_ID . " = :blog_id";
    $stmt = $db_connection->prepare($sql);
    $stmt->bindValue(":blog_id", $blog_id);
    $stmt->execute();

    if ($only_this_blog_had_this_logo) {
      delete_image($blog_logo);
    }

    $db_connection->commit();

  } catch (Exception $e) {
    rollback_if_in_transaction($db_connection);
    throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
  }
}


/**
 * Ottiene la lista di blog amministrati (ossia creati) da un determinato utente, e li restituisce in un array associativo
 * @param PDO $db_connection Connessione al database
 * @param string $admin_name Nome utente dell'amministratore
 * @throws Exception in caso di errore
 * @return array Lista dei blog
 */
function get_blogs_list_by_creator(PDO $db_connection, string $admin_name): array
{
  $sql = 'SELECT * FROM ' . BLOG_TABLE . ' WHERE ' . BLOG_OWNER . ' = :blog_owner';

  try {
    $stmt = $db_connection->prepare($sql);
    $stmt->bindValue(':blog_owner', $admin_name, PDO::PARAM_STR);
    $stmt->execute();
    $blogs_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $blogs_list;
  } catch (PDOException $e) {
    throw new Exception('Errore nella funzione get_blogs_list: ' . $e->getMessage());
  }
}


function get_blogs_by_category(PDO $db_connection, string $category): array
{
  $sql = 'SELECT * FROM '. BLOG_TABLE . ' WHERE ' . BLOG_CATEGORY . ' = :blog_category';

  try{
    $stmt = $db_connection->prepare($sql);
    $stmt->bindValue(':blog_category', $category, PDO::PARAM_STR);
    $stmt->execute();
    $blogs_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $blogs_list;
  } catch (Exception $e) {
    throw new Exception('Errore nell\'ottenere la lista di blog per categoria: ' . $e->getMessage());
  }
}



/**
 * Ottiene la lista di blog di cui un utente è coautore.
 * @param PDO $db_connection Connessione al DB
 * @param string $username Nome utente
 * @throws Exception qualora l'utente non esista o vi siano errori a livello di DB
 * @return array Array con gli ID dei blog di cui un utente è coautore.
 */
function get_blogs_list_by_coauthor(PDO $db_connection, string $username): array
{
  try {
    // Verifica se l'utente esiste
    if (!do_user_exist($db_connection, $username)) {
      throw new Exception("Utente non trovato");
    }

    // Query per ottenere la lista dei blog dove l'utente è co-autore
    $sql = "SELECT b.* FROM " . BLOG_TABLE . " b
              JOIN " . COAUTHORS_TABLE . " a ON b." . BLOG_ID . " = a." . COAUTHORS_BLOG . "
              JOIN " . USER_TABLE . " u ON a." . COAUTHORS_USER . " = u." . USER_USERNAME . "
              WHERE u." . USER_USERNAME . " = :username";
    $stmt = $db_connection->prepare($sql);
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $blogs;
  } catch (Exception $e) {
    throw new Exception("Errore nella funzione get_blogs_list_by_coauthor: " . $e->getMessage());
  }
}

/**
 * Funzione per cambiare il titolo di un blog.
 * Il titolo viene cambiato solo se il nuovo è effettivamente diverso da quello vecchio, per evitare una query inutile al DB.
 * @param PDO $db_connection Connessione al Database
 * @param int $blog_id ID del blog di cui cambiare il titolo
 * @param string $new_title Nuovo titolo
 * @throws Exception in caso di errore
 * @return void
 */
function update_blog_title(PDO $db_connection, int $blog_id, string $new_title)
{
  try {
    $old_title = get_blog_info($db_connection, $blog_id)[BLOG_TITLE];

    if ($old_title !== $new_title) {

      $db_connection->beginTransaction();
      $sql = "UPDATE " . BLOG_TABLE . " SET " . BLOG_TITLE . " = :new_title WHERE " . BLOG_ID . " = :blog_id";
      $stmt = $db_connection->prepare($sql);

      $stmt->bindValue(':new_title', $new_title, PDO::PARAM_STR);
      $stmt->bindValue(':blog_id', $blog_id, PDO::PARAM_INT);

      $stmt->execute();
      $db_connection->commit();
    }

  } catch (Exception $e) {
    rollback_if_in_transaction($db_connection);

    throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
  }
}

/**
 * Funzione per ottenere la lista di tutti i nomi dei loghi di tutti i blog creati da un determinato utente.
 * Viene utilizzato nella funzione delete_user per assicurarsi di eliminare tutte le immagini scelte da un utente come logo dei suoi blog.
 * @param PDO $db_connection Connessione al database
 * @param string $username Nome utente
 * @throws Exception in caso di errore
 * @return array Array contenente la lista dei nomi delle immagini.
 */
function get_blog_pics_by_user(PDO $db_connection, string $username): array
{
  try {
    $blog_list = get_blogs_list_by_creator($db_connection, $username);
    $blog_pics = [];

    foreach ($blog_list as $blog) {
      $blog_has_a_logo = !is_null($blog[BLOG_PIC]);
      if ($blog_has_a_logo) {
        $blog_pics[] = $blog[BLOG_PIC];
      }
    }

    return $blog_pics;
  } catch (Exception $e) {
    throw new Exception("Errore nella funzione get_blogs_pics_by_user: " . $e->getMessage());
  }
}

/**
 * Aggiornamento della descrizione di un blog.
 * La funzione si occupa di controllare che la nuova descrizione sia diversa dalla vecchia, per evitare una query inutile al DB
 * @param PDO $db_connection Connessione al database
 * @param int $blog_id ID del blog di cui cambiare la descrizione
 * @param string $new_description Nuova descrizione
 * @throws Exception in caso di errore
 * @return void
 */
function update_blog_description(PDO $db_connection, int $blog_id, string $new_description)
{
  try {
    $old_description = get_blog_info($db_connection, $blog_id)[BLOG_DESCRIPTION];
    if ($old_description != $new_description) {
      $db_connection->beginTransaction();

      $sql = "UPDATE " . BLOG_TABLE . " SET " . BLOG_DESCRIPTION . " = :new_description WHERE " . BLOG_ID . " = :blog_id";
      $stmt = $db_connection->prepare($sql);

      $stmt->bindValue(':new_description', $new_description, PDO::PARAM_STR);
      $stmt->bindValue(':blog_id', $blog_id, PDO::PARAM_INT);

      $stmt->execute();

      $db_connection->commit();
    }
  } catch (Exception $e) {
    rollback_if_in_transaction($db_connection);

    throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
  }
}

/**
 * Ottieni le informazioni su un blog, organizzate in un array associativo le cui chiavi sono le colonne della tabella BLOG_TABLE
 * @param PDO $db_connection Connessione al DB
 * @param int $blog_id ID del blog di cui si vogliono trovare le informazioni
 * @throws Exception in caso di errore
 * @return array|bool Contenente le informazioni su un blog. Restituisce FALSE senza effettuarealcuna query in caso di ID negativo.
 */
function get_blog_info(PDO $db_connection, int $blog_id): array|bool
{
  try {
    $blog_info = false; // Assumo di defaul che il blog non esista

    if ($blog_id >= 0) { // Eseguo la query se e solo se l'ID è maggiore o uguale a zero
      $sql = "SELECT * FROM " . BLOG_TABLE . " WHERE " . BLOG_ID . " = :blog_id";
      $stmt = $db_connection->prepare($sql);
      $stmt->bindValue(':blog_id', $blog_id, PDO::PARAM_INT);
      $stmt->execute();
      $blog_info = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    return $blog_info;
  } catch (Exception $e) {
    throw new Exception("Errore nella funzione get_blog_info: " . $e->getMessage());
  }
}

/**
 * Rimuove il logo associato a un blog.
 * Prima di eseguire qualunque operazione, si assicura che il logo esista.
 * Prima di cancellare il file di un logo, si assicura che non ci siano altri blog che non lo stiano utilizzando.
 * @param PDO $db_connection La connessione al database.
 * @param int $blog_id L'ID del blog.
 * @throws Exception in caso di errore Se si verifica un errore durante la query al database o la cancellazione del file.
 */
function delete_blog_logo(PDO $db_connection, int $blog_id)
{
  try {
    $logo = get_blog_info($db_connection, $blog_id)[BLOG_PIC]; // Recupero il nome del logo del blog corrente
    $there_was_a_logo = !is_null($logo);

    if ($there_was_a_logo) {
      $db_connection->beginTransaction();

      if (is_image_unique($db_connection, $logo)) {
        delete_image($logo);
      }

      // Aggiornare il database per impostare il logo del blog su NULL
      $sql = "UPDATE " . BLOG_TABLE . " SET " . BLOG_PIC . " = NULL WHERE " . BLOG_ID . " = :blog_id";
      $stmt = $db_connection->prepare($sql);
      $stmt->bindValue(':blog_id', $blog_id, PDO::PARAM_NULL);
      $stmt->execute();
      $db_connection->commit();
    }

  } catch (Exception $e) {
    rollback_if_in_transaction($db_connection);
    throw new Exception("Errore nella funzione del_blog_logo: " . $e->getMessage());
  }
}

/**
 * Funzione per impostare il logo di un blog. Si occupa sia della copiatura del logo nel filesystem che del salvataggio del nome nel DB.
 * Prima di effettuare qualunque operazione, si occupa di controllare che i due loghi, il vecchio e il nuovo, siano effettivamente diversi.
 * Si occupa altresì della cancellazione intelligente delle immagini: dopo aver cambiato un logo, cancella il vecchio file se e solo se non ci sono altre risorse che lo utilizzano
 *
 * @param PDO $db_connection Connessione al DB
 * @param int $blog_id ID del blog di cui si vuole cambiare o cancellare il logo
 * @param array $blog_logo_file Corrisponde a $_FILES['nome_campo_logo_blog'].
 * @throws Exception in caso di errore
 * @return void
 */
function set_blog_logo(PDO $db_connection, int $blog_id, array $blog_logo_file): void
{
  try {
    // Calcola il nome definitivo del logo
    $db_connection->beginTransaction();

    $new_logo_name = calculate_new_image_name($blog_logo_file);
    $old_logo_name = get_blog_info($db_connection, $blog_id)[BLOG_PIC];

    if ($new_logo_name === $old_logo_name) return;

    $there_was_an_old_logo = !is_null($old_logo_name);

    if ($there_was_an_old_logo) {
      $old_logo_was_an_unique_image = is_image_unique($db_connection, $old_logo_name);
    }

    $sql = "UPDATE " . BLOG_TABLE . " SET " . BLOG_PIC . " = :blog_logo_name WHERE " . BLOG_ID . " = :blog_id";
    $stmt = $db_connection->prepare($sql);
    $stmt->bindValue(':blog_logo_name', $new_logo_name, PDO::PARAM_STR);
    $stmt->bindValue(':blog_id', $blog_id, PDO::PARAM_INT);
    $stmt->execute();

    $full_new_logo_path = IMAGE_DIRECTORY . $new_logo_name;

    if (!file_exists($full_new_logo_path)) {
      save_image($blog_logo_file, $new_logo_name);
    }

    if ($there_was_an_old_logo && $old_logo_was_an_unique_image) {
      delete_image($old_logo_name);
    }

    $db_connection->commit();

  } catch (Exception $e) {
    rollback_if_in_transaction($db_connection);

    throw new Exception("Errore nell'impostare il logo del blog: " . $e->getMessage());
  }
}

/**
 * Aggiorna la categoria di un blog.
 * @param PDO $db_connection Connessione al database
 * @param int $blog_id ID del blog di cui si vuole aggiornare la categoria
 * @param string $new_category Nuova categoria
 * @throws Exception in caso di errore
 * @return void
 */
function update_blog_category(PDO $db_connection, int $blog_id, string $new_category)
{
  try {
    $db_connection->beginTransaction();
    $sql = 'UPDATE `' . BLOG_TABLE . '`
    SET `' . BLOG_CATEGORY . '` = :new_category
    WHERE `' . BLOG_ID . '` = :blog_id';
    $stmt = $db_connection->prepare($sql);
    $stmt->bindValue(':blog_id', $blog_id, PDO::PARAM_INT);
    $stmt->bindValue(':new_category', $new_category, PDO::PARAM_STR);
    $stmt->execute();
    $db_connection->commit();
  } catch (Exception $e) {
    rollback_if_in_transaction($db_connection);
    throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
  }
}

/**
 * Ottiene l'ID del blog corrente
 * @return int ID del blog corrente
 */
function get_current_blog_id(): int
{
  return $_SESSION['current_blog'][BLOG_ID];
}

/**
 * Ottien il nome utente del proprietario del blog corrente
 * @return string
 */
function get_current_blog_owner(): string
{
  return $_SESSION['current_blog'][BLOG_OWNER];
}

/**
 * Ottiene la categoria del blog corrente
 * @return string Categoria del blog corrente
 */
function get_current_blog_category(): string
{
  return $_SESSION['current_blog'][BLOG_CATEGORY];
}

/**
 * Ottiene il titolo del blog corrente
 * @return string Titolo del blog corrente
 */
function get_current_blog_title(): string
{
  return $_SESSION['current_blog'][BLOG_TITLE];
}

/**
 * Ottiene la descrizione del blog corrente
 * @return string Descrizione del blog corrente
 */
function get_current_blog_description(): string
{
  return $_SESSION['current_blog'][BLOG_DESCRIPTION];
}

/**
 * Ottiene il nome dell'immagine che fa da logo al blog corrente
 * @return string|null Nome dell'immagine del logo del blog corrente, NULL se il blog ne è privo
 */
function get_current_blog_logo(): string | null
{
  return $_SESSION['current_blog'][BLOG_PIC];
}

/**
 * Ottieni l'elenco degli eventuali coautori del blog corrente
 * @return array|null Array di stringhe contenete gli username dei coautori, ```null``` altrimenti
 */
function get_current_blog_coauthors(): array | null
{
  return $_SESSION['current_blog']['coauthors'];
}

/**
 * Verifica che il blog della sessione corrente abbia un logo.
 * @return bool
 */
function has_current_blog_logo(): bool
{
  return is_string(get_current_blog_logo());
}

/**
 * Imposta il blog della sessione corrente.
 * @param array $blog Array contenete le informazioni del blog.g
 * @return void
 */
function set_current_blog(array $blog): void
{
  if(session_status() === PHP_SESSION_NONE) session_start();

  $_SESSION['current_blog'] = $blog;
}

/**
 * Verifica che sia stato selezionato un blog corrente.
 * @return bool ```TRUE``` se esiste $_SESSION['current_blog'], è un'array e la sua struttura è quella di informazioni di un blog. ```FALSE``` in tutti gli altri casi
 */
function there_is_a_current_blog(): bool
{
  return isset($_SESSION['current_blog']) && is_array($_SESSION['current_blog']);
}

/**
 * Elimina il blog corrente, ovvero, dopo la sua esecuzione
 * ```there_is_a_current_blog() === false```.
 * Serve ad evitare che rimanga "in memoria" un vecchio blog corrente: 
 * ad esempio, quando un utente va sulla home page, questa funzione viene richiamata, 
 * in maniera tale che, visitando ad esempio editblog.php, 
 * non torni ad editare un eventuale vecchio blog rimasto in memoria.
 * @return void
 */
function reset_current_blog(): void
{
  if(there_is_a_current_blog()){
    unset($_SESSION['current_blog']);
  }
}

/**
 * Verifica che l'utente attualmente loggato sia il proprietario del blog corrente.
 * @return bool ```TRUE``` se l'utente corrente è il proprietario del blog corrente, ```FALSE``` altrimenti, o se un blog corrente non è stato impostato.
 */
function can_current_user_edit_current_blog(): bool
{
  return there_is_a_current_blog() && get_current_blog_owner() === get_current_user_username();
}