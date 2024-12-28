<?php
/**
 * Aggiungi dei coautori a un blog.
 * La funzione prende in input un array con i nomi utente dei coautori desiderati 
 * e costruisce una query SQL opportuna, per aggiungerli tutti in un colpo solo.
 * Non viene effettuato alcun controllo sui nomi degli utenti: ci si limita a controllare che l'array non sia vuoto, prima di eseguire qualunque operazione.
 * @param PDO $db_connection Connessione al database
 * @param int $blog_id ID del blog a cui si vogliono aggiungere i coautori
 * @param array $suitable_coauthors array di stringhe contenente i nomi utente dei coautori che si vogliono aggiungere al blog.
 * @throws Exception in caso di errore
 * @return void
 */
function add_coauthors(PDO $db_connection, int $blog_id, array $suitable_coauthors)
{
  try {

    if (!empty($suitable_coauthors)) {
      $db_connection->beginTransaction();

      $first_part_of_sql_query = "INSERT INTO " . COAUTHORS_TABLE . " (" . COAUTHORS_BLOG . ", " . COAUTHORS_USER . ")";

      // Costruisco la seconda parte con tanti campi VALUES(:blog_id, :coauthor_numero) quanti sono i coautori scelti
      foreach ($suitable_coauthors as $coauthor_index => $coauthor_username) {
        $current_placeholder = ":coauthor_$coauthor_index";
        $values_for_each_coauthor = "VALUES(:blog_id, $current_placeholder)";
      }

      $full_sql_query = $first_part_of_sql_query . $values_for_each_coauthor;

      $stmt = $db_connection->prepare($full_sql_query);
      $stmt->bindValue(':blog_id', $blog_id, PDO::PARAM_INT);

      // Faccio il binding dei nomi utente dei coautori 
      foreach ($suitable_coauthors as $coauthor_index => $coauthor_username) {
        $current_placeholder = ":coauthor_$coauthor_index";
        $stmt->bindValue("$current_placeholder", $coauthor_username, PDO::PARAM_STR);
      }

      $stmt->execute();

      $db_connection->commit();
    }

  } catch (PDOException $e) {
rollback_if_in_transaction($db_connection);
    throw new Exception("Errore nella funzione add_author: " . $e->getMessage());
  }
}

function is_user_already_coauthor(PDO $db_connection, string $username, int $blog_id)
{
  try {
    $sql = "SELECT COUNT(*) FROM `" . COAUTHORS_TABLE . "`
    WHERE `" . COAUTHORS_BLOG . "` = :blog_id AND `" . COAUTHORS_USER . "` = :username";

    $stmt = $db_connection->prepare($sql);
    $stmt->bindValue(":username", $username, PDO::PARAM_STR);
    $stmt->bindValue(":blog_id", $blog_id, PDO::PARAM_INT);
    $stmt->execute();
    $times_user_was_set_as_coauthor_of_given_blog = $stmt->fetchColumn();

    if ($times_user_was_set_as_coauthor_of_given_blog > 1) {
      throw new Exception("C'è un problema nel tuo database: l'utente $username è stato impostato come autore del blog con ID $blog_id per $times_user_was_set_as_coauthor_of_given_blog volte.");
    } else {
      $user_is_already_coauthor = ($times_user_was_set_as_coauthor_of_given_blog == 1);
    }

    return $user_is_already_coauthor;

  } catch (Exception $e) {
    throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : '  . $e->getMessage());
  }

}
/**
 * Ottieni la lista degli utenti adatti ad essere rimossi o aggiunti come coautori di un blog.
 * Un utente è adatto ad essere aggiunto se
 *  - Esiste
 *  - Non è già tra i coautori
 * Un utente è adatto ad essere rimosso se:
 *  - Esiste
 *  - Si trova già tra i coautori 
 * @param PDO $db_connection Connessione al database
 * @param array $candidates Array di stringhe contenenti i nomi utente da valutare
 * @param int $blog_id ID del blog di riferimento
 * @param bool $action Cosa fare. Può avere due valori: TO_ADD e TO_REMOVE, rispettivamente "true" e "false"
 * @return array Restituisce un array con i nomi utente adatti ad essere, rispettivamente, aggiunti o rimossi dai coautori di un dato blog.
 */
function get_suitable_coauthors(PDO $db_connection, array $candidates, int $blog_id, bool $action = TO_ADD | TO_REMOVE): array
{
    foreach ($candidates as $user) {
        $user_does_not_exist = !do_user_exist($db_connection, $user);
        if ($user_does_not_exist) {
            continue;
        }

        $is_coauthor = is_user_already_coauthor($db_connection, $user, $blog_id);
        $should_add = $action === TO_ADD && !$is_coauthor;
        $should_remove = $action === TO_REMOVE && $is_coauthor;

        if ($should_add || $should_remove) {
            $suitable_users[] = $user;
        }
    }

    return $suitable_users;
}

/**
 * Funzione per togliere uno o più utenti dall'elenco dei coautori di un blog.
 * La funzione si limita ad assicurarsi che l'elenco degli utenti non sia vuoto, senza effettuare alcun controllo ulteriore.
 * @param PDO $db_connection Connessione al database
 * @param int $blog_id ID del blog da cui si vogliono rimuovere gli utenti
 * @param array $removable_users Array di stringhe, con i nomi utenti di cui 
 * @throws InvalidArgumentException qualora la lista degli utenti sia vuota
 * @throws Exception in ogni altro caso di errore
 * @return void
 */
function delete_coauthors(PDO $db_connection, int $blog_id, array $removable_users)
{
  try {

    if (empty($removable_users)) {
      throw new InvalidArgumentException("La lista degli utenti da rimuovere non può essere vuota.");
    }

    $db_connection->beginTransaction();
    
    $placeholder_prefix = "coauthor";
    $placeholder_to_user = build_placeholder_to_value_from_an_array($removable_users, $placeholder_prefix);
    $placeholders = array_keys($placeholder_to_user);
    $placeholders_list_with_commas = implode(", ", $placeholders); // Creazione di una stringa di testo con i placeholder separati da virgole

    $sql = "DELETE FROM " . COAUTHORS_TABLE . " WHERE " . COAUTHORS_BLOG . " = :blog_id AND " . COAUTHORS_USER . " IN ({$placeholders_list_with_commas})";

    $stmt = $db_connection->prepare($sql);
    $stmt->bindValue(":blog_id", $blog_id, PDO::PARAM_INT);
    foreach ($placeholder_to_user as $placeholder => $value) {
      $stmt->bindValue($placeholder, $value, PDO::PARAM_STR);
    }
    
    $stmt->execute();
  
    $db_connection->commit();
  
  } catch (Exception $e) {
rollback_if_in_transaction($db_connection);
    throw new Exception("Errore nella funzione del_author: " . $e->getMessage());
  }
}

/**
 * Ottieni la lista dei coautori di un blog.
 * @param PDO $db_connection Connessioni al database
 * @param int $blog_id id del blog di cui si vogliono conoscere i coautori
 * @throws Exception In caso di errore
 * @return array contenente la lista dei nomi utente, nome completo ed e-mail dei coautori
 */
function get_blog_coauthors(PDO $db_connection, int $blog_id): array
{
  try {
    $sql = "SELECT u." . USER_USERNAME . ", u." . USER_COMPLETE_NAME . ", u." . USER_EMAIL . "
        FROM " . COAUTHORS_TABLE . " c JOIN " . USER_TABLE . " u ON c." . COAUTHORS_USER . " = u." . USER_USERNAME . "
        WHERE c." . COAUTHORS_BLOG . " = :blog_id";

    $stmt = $db_connection->prepare($sql);
    $stmt->bindValue(':blog_id', $blog_id, PDO::PARAM_INT);
    $stmt->execute();
    $coauthors_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $coauthors_list;
  } catch (Exception $e) {
    throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : '  . $e->getMessage());
  } 
}