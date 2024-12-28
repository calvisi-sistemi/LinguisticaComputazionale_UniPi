<?php

/**
 * Funzione per la ricerca degli utenti.
 * @param PDO $db_connection Connessione al DB
 * @param string $query testo da cercare, che viene raffrontato con il nome utente, nome completo e data di nascita.
 * @param int $number_of_results_to_show numero di risultati da mostrare, 10 di default
 * @throws Exception in caso di errore 
 * @return array | null NULL in caso di ricerca vuota, array associativo contenente nomi utenti, nomi completi ed email degli utenti trovati.
 * La struttura dell'array è
 * [
 *    0: [USER_USERNAME] => "nome_utente" [USER_COMPLETE_NAME] => "nome completo" [USER_EMAIL] => email@utente.com
 *    1: [USER_USERNAME] => "nome_utente" [USER_COMPLETE_NAME] => "nome completo" [USER_EMAIL] => email@utente.com
 *    ...
 *  ]
 */
function search_users(PDO $db_connection, string $query, int $number_of_results_to_show = 10): array|null
{
    try {
        $search_results = [];

        if (is_query_blank($query)) {
            throw new InvalidArgumentException('La query non può essere vuota');
        }
        $sql = "SELECT `" . USER_USERNAME . "`, 
            `" . USER_COMPLETE_NAME . "`, 
            `" . USER_EMAIL . "` ,
            `" . USER_AVATAR . "` 
        FROM `" . USER_TABLE . "`
        WHERE `" . USER_USERNAME . "` LIKE :query_with_wildcards
        OR `" . USER_COMPLETE_NAME . "` LIKE :query_with_wildcards
        OR `" . USER_EMAIL . "` LIKE :query_with_wildcards
        OR `" . USER_BIO . "` LIKE :query_with_wildcards
        LIMIT :number_of_results_to_show";

        $query_with_wildcards = "%{$query}%";

        $stmt = $db_connection->prepare($sql);
        $stmt->bindValue(":query_with_wildcards", $query_with_wildcards, PDO::PARAM_STR);
        $stmt->bindValue(":number_of_results_to_show", $number_of_results_to_show, PDO::PARAM_INT);
        $stmt->execute();
        $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $there_are_not_results = empty($search_results);
        if ($there_are_not_results) {
            $search_results = null;
        }

        return $search_results;

    } catch (Exception $e) {
        throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
    }
}

/**
 * Ricerca dei post attraverso il loro contenuto.
 * @param PDO $db_connection Connessione al database
 * @param string $query Stringa di ricerca
 * @param int $number_of_results_to_show Numero di risultati da mostrare, di default 10
 * @throws Exception in caso di errore
 * @return array | null NULL in caso di risultati vuoti, array associativo con i risultati della ricerca. La sua struttura è la seguente:
 *  [
 *      0 : [POST_ID] => ..., [POST_TITLE] => ...., [POST_TEXT] => ...., 
 *      1 : ...
 *      ...
 *  ]
 */
function search_post(PDO $db_connection, string $query, int $number_of_results_to_show = 10): array | null
{
    try {
        $search_results = [];

        if (is_query_blank($query)) {
            throw new InvalidArgumentException('La query non può essere vuota');
        }

        $sql = "SELECT * FROM `" . POST_TABLE . "`
        WHERE `" . POST_TEXT . "` LIKE :query_with_wildcards
        LIMIT :number_of_results_to_show";

        $query_with_wildcards = "%{$query}%";

        $stmt = $db_connection->prepare($sql);
        $stmt->bindValue(":query_with_wildcards", $query_with_wildcards, PDO::PARAM_STR);
        $stmt->bindValue(":number_of_results_to_show", $number_of_results_to_show, PDO::PARAM_INT);
        $stmt->execute();
        $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $there_are_not_results = empty($search_results);
        if ($there_are_not_results) {
            $search_results = null;
        }

        return $search_results;

    } catch (Exception $e) {
        throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
    }
}

/**
 * Ricerca dei blog attraverso il loro titolo e la loro descrizione.
 * @param PDO $db_connection Connessione al database
 * @param string $query Stringa di ricerca
 * @param int $number_of_results_to_show Numero di risultati da mostrare, di default 10
 * @throws Exception in caso di errore
 * @return array | null NULL in caso ricerca vuota, array associativo con i risultati della ricerca. La sua struttura è la seguente:
 *  [
 *      0 : [BLOG_ID] => ..., [BLOG_TITLE] => ...., [BOG_DESCRIPTION] => ...., 
 *      1 : ...
 *      ...
 *  ]
 */
function search_blogs(PDO $db_connection, string $query, int $number_of_results_to_show = 10): array | null
{
    try {
        $search_results = [];

        if (is_query_blank($query)) {
            throw new InvalidArgumentException('La query non può essere vuota');
        }

        $sql = "SELECT * FROM `" . BLOG_TABLE . "`
        WHERE `" . BLOG_TITLE . "` LIKE :query_with_wildcards
        OR `" . BLOG_DESCRIPTION . "` LIKE :query_with_wildcards
        LIMIT :number_of_results_to_show";

        $query_with_wildcards = "%{$query}%";

        $stmt = $db_connection->prepare($sql);
        $stmt->bindValue(":query_with_wildcards", $query_with_wildcards, PDO::PARAM_STR);
        $stmt->bindValue(":number_of_results_to_show", $number_of_results_to_show, PDO::PARAM_INT);
        $stmt->execute();
        $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $there_are_not_results = empty($search_results);
        if ($there_are_not_results) {
            $search_results = null;
        }

        return $search_results;

    } catch (Exception $e) {
        throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
    }
}