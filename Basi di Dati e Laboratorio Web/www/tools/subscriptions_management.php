<?php
/**
 * Funzioni per gestire le iscrizioni.
 */

/**
 * Funzione per iscriversi ad un blog.
 * La funzione impedisce che un utente si iscriva da solo a un proprio blogs
 * @param PDO $db_connection Connessione al database
 * @param string $username Username dell'utente che si vuole iscrivere
 * @param int $blog_id ID del blog a cui iscriversi
 * @throws Exception in caso di errore
 * @return void
 */
function subscribe(PDO $db_connection, string $username, int $blog_id)
{
    $sql = 'INSERT INTO `' . SUBSCRIPTION_TABLE . '` 
    (
    `' . SUBSCRIPTION_USERNAME . '`, 
    `' . SUBSCRIPTION_BLOG_ID . '`
    )
    VALUES (:username, :blog_id)';
    
    try {
        $blog_owner = get_blog_info($db_connection, $blog_id)[BLOG_OWNER];
        // Impedisci a un utente di iscriversi da solo ai propri blog
        if ($username === $blog_owner) {
            return;
        }
        $db_connection->beginTransaction();
        $stmt = $db_connection->prepare($sql);
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->bindValue(':blog_id', $blog_id, PDO::PARAM_INT);
        $stmt->execute();
        $db_connection->commit();
    } catch (Exception $e) {
        rollback_if_in_transaction($db_connection);
        throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
    }
}

/**
 * Funzione per disiscriversi da un blog.
 * La funzione verifica se l'utente che ha chiesto di disiscriversi sia il proprietario del blog e, in tal caso, esce senza effettuare alcuna operazione.
 * @param PDO $db_connection Connessione al database
 * @param string $username Username dell'utente che si vuole iscrivere
 * @param int $blog_id ID del blog a cui iscriversi
 * @throws Exception in caso di errore
 * @return void
 */
function unsubscribe(PDO $db_connection, string $username, int $blog_id)
{
    $sql = 'DELETE FROM `' . SUBSCRIPTION_TABLE . '` 
        WHERE `' . SUBSCRIPTION_USERNAME . '` = :username
        AND  `' . SUBSCRIPTION_BLOG_ID . '` = :blog_id';
    
    try {
        // Evita di eseguire qualunque operazione se l'utente che ha chiesto la disiscrizione è il proprietario del blog
        $blog_owner = get_blog_info($db_connection, $blog_id)[BLOG_OWNER];
        if($blog_owner === $username) {
            return;
        }

        $db_connection->beginTransaction();
        $stmt = $db_connection->prepare($sql);
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->bindValue(':blog_id', $blog_id, PDO::PARAM_INT);
        $stmt->execute();
        $db_connection->commit();
    } catch (Exception $e) {
        rollback_if_in_transaction($db_connection);
        throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
    }
}

/**
 * Ottieni l'elenco dei blog (titoli e ID) a cui è iscritto un utente.
 * @param PDO $db_connection Connessione al DB
 * @param string $username Utente di cui si vogliono conoscere le iscrizioni
 * @throws Exception In caso di errore
 * @return array|null qualora l'utente non sia iscritto ad alcun blog, restituisce NULL; altrimenti, resituisce un array associativo con l'elenco dei blog, avente struttura:
 * ```
 * [
 *  0: [BLOG_ID] => id_del_blog, [BLOG_TITLE] => "Titolo del blog"
 *  1: ...
 *  ...
 * ]
 * ```
 */
function get_user_subscriptions(PDO $db_connection, string $username): array|null
{
    try {
        $sql = 'SELECT blog.*
        FROM `' . BLOG_TABLE . '` AS blog
        INNER JOIN `' . SUBSCRIPTION_TABLE . '` AS subscriptions
        ON blog.`' . BLOG_ID . '` = subscriptions.`' . SUBSCRIPTION_BLOG_ID . '`
        WHERE
        subscriptions.`' . SUBSCRIPTION_USERNAME . '` = :username';

        $stmt = $db_connection->prepare($sql);
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $subscriptions = empty($subscriptions) ? null : $subscriptions;

        return $subscriptions;

    } catch (Exception $e) {
        throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
    }
}

/**
 * Ottieni gli iscritti di un blog.
 * @param PDO $db_connection Connessione al database
 * @param int $blog_id ID del blog di cui si vogliono ottenere gli iscritti
 * @throws Exception in caso di errore 
 * @return null|array NULL se il blog non ha nessun iscritto, un array monodimensionale con la lista dei nomi utenti degli iscritti altrimenti.
 */
function get_blog_subscribers(PDO $db_connection, int $blog_id): null|array
{
    try {
        $sql = 'SELECT `' . SUBSCRIPTION_USERNAME . '` FROM `' . SUBSCRIPTION_TABLE . '`
        WHERE `' . SUBSCRIPTION_BLOG_ID . '` = :blog_id';

        $stmt = $db_connection->prepare($sql);
        $stmt->bindValue(':blog_id', $blog_id, PDO::PARAM_INT);
        $stmt->execute();

        $subscribers = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $subscribers = empty($subscribers) ? null : $subscribers;

        return $subscribers;
    } catch (Exception $e) {
        throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
    }
}

/**
 * Funzione per conoscere lo stato di iscrizione di un utente a un blog.
 * @param PDO $db_connection Connessione al DB
 * @param string $username Nome utente dell'utente da valutare
 * @param int $blog_id ID del blog di cui si vuol sapere se l'utente è un iscritto
 * @throws Exception In caso di errore
 * @return bool TRUE se l'utente è iscritto, FALSE altrimenti.
 */
function is_user_subscribed(PDO $db_connection, string $username, int $blog_id): bool
{
    try {
        $sql = 'SELECT COUNT(*) FROM `' . SUBSCRIPTION_TABLE . '` 
        WHERE `' . SUBSCRIPTION_USERNAME . '` = :username
        AND `' . SUBSCRIPTION_BLOG_ID . '` = :blog_id';
        $stmt = $db_connection->prepare($sql);
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->bindValue(':blog_id', $blog_id, PDO::PARAM_INT);
        $stmt->execute();

        $number_of_subscriptions = $stmt->fetchColumn();

        if ($number_of_subscriptions > 1) {
            throw new UnexpectedValueException("C'è un problema con il tuo database: l'utente $username risulta iscritto $number_of_subscriptions al blog con ID $blog_id");
        }

        $user_is_already_subscribed = $number_of_subscriptions == 1;

        return $user_is_already_subscribed;

    } catch (Exception $e) {
        throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
    }
}

/**
 * Cambia lo stato di iscrizione di un utente e restituisce il nuovo stato.
 * Ovvero: disiscrive gli utenti iscritti a un blog, iscrive i non iscritti.
 * Scelgo qui di violare il principio di singola responsabilità, scrivendo una funzione che
 *  1. Modifica lo stato di iscrizione di un utente
 *  2. Restituisce il nuovo stato
 * Per evitare, dopo aver effettuato l'operazione 1., di dover eseguire una seconda query sul database 
 * per conoscere lo stato dell'utente o di aggiungere altro codice in altri punti del sito,
 * restituisco direttamente il nuovo status dell'utente.
 * @param PDO $db_connection Connessione al database
 * @param string $username Utente di cui si vuole cambiare lo stato di iscrizione a un blog
 * @param string $blog_id ID del blog da cui iscrivere/disicrivere l'utente
 * @param bool $subscription_status Stato attuale dell'iscrizione dell'utente a quel blog, SUBSCRIBED o NOT_SUBSCRIBED
 * @throws Exception in caso di errore
 * @return bool il nuovo stato dell'utente, SUBSCRIBED (true) o NOT_SUBSCRIBED (false). 
 */
function toggle_subscription(PDO $db_connection, string $username, string $blog_id, bool $subscription_status = SUBSCRIBED | NOT_SUBSCRIBED): bool
{
    try {

        if ($subscription_status === SUBSCRIBED) {
            unsubscribe($db_connection, $username, $blog_id);
        }

        if ($subscription_status === NOT_SUBSCRIBED) {
            subscribe($db_connection, $username, $blog_id);
        }

        $new_subscription_status = !$subscription_status;

        return $new_subscription_status;

    } catch (Exception $e) {
        throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
    }
}

/**
 * Ottiene gli ultimi post dai blog che un utente sta seguendo.
 * @param PDO $db_connection Connessione al database
 * @param string $username Utente di cui si vogliono ottenere gli ultimi post dei blog seguiti
 * @param int $number_of_posts_per_blog_to_show Numero di post da mostrare per ogni blog, 10 di default. Qualora Il numero di post disponibile sia minore al numero indicato, vengono mostrati tutti i post disponibili.
 * @throws Exception In caso di errore
 * @return null | array NULL in caso di risultato vuoto, ARRAY associativo avente la seguente struttura negli altri:
 * ```
 *  [
 *      blog_id_1 => [ 
 *                      'blog_title' => 'TitoloDelBlog',
 * 
 *                      'posts' => [
 *                                           post_id1 => [
 *                                                          Informazioni sul post... con costanti POST_* come chiavi
 *                                                      ]
 * 
 *                                            post_id2 => [ ... ]
 *                                            ....
 *                                         ]
 *                     ],
 * 
 *      blog_id_2 => [ ... ]
 *      ...
 *      
 *  ]
 * ```
 */
function get_user_feed(PDO $db_connection, string $username, int $number_of_posts_per_blog_to_show = 10): array|null
{
    try {

        if ($number_of_posts_per_blog_to_show < 1) {
            throw new Exception('Il numero di post visualizzati non può essere inferiore a 1');
        }

        $feed = [];
        $user_blog_subscriptions = get_user_subscriptions($db_connection, $username);

        $user_is_not_subscribed_to_any_blog = is_null($user_blog_subscriptions);

        if ($user_is_not_subscribed_to_any_blog) {
            return null;
        }

        foreach ($user_blog_subscriptions as $blog) {
            $current_blog_id = $blog[BLOG_ID];
            $current_blog_info = get_blog_info($db_connection, $current_blog_id);
            $current_blog_posts = get_posts_list_from_blog($db_connection, $current_blog_id);

            $current_blog_does_not_have_any_post = is_null($current_blog_posts);

            // Non perdo tempo ad aggiungere all'array finale informazioni sui blog privi di post.
            if ($current_blog_does_not_have_any_post) {
                continue;
            }

            $feed[$current_blog_id][BLOG_TITLE] = $current_blog_info[BLOG_TITLE];

            $number_of_posts = count($current_blog_posts);

            // Qualora il numero di post per blog sia inferiore al massimo, mostro il numero di post presenti
            $number_of_posts_per_blog_to_show = $number_of_posts < $number_of_posts_per_blog_to_show ? $number_of_posts : $number_of_posts_per_blog_to_show;

            $posts_to_show = array_slice($current_blog_posts, 0, $number_of_posts_per_blog_to_show);

            foreach ($posts_to_show as $post) {
                $current_post_id = $post[POST_ID];
                $feed[$current_blog_id]['posts'][$current_post_id] = $post;
            }

        }

        $there_are_not_posts_to_show = empty($feed);

        if ($there_are_not_posts_to_show) {
            $feed = null;
        }

        return $feed;

    } catch (Exception $e) {
        throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
    }
}

/**
 * Ricava il testo da mostrare nell'anteprima di un post, all'interno del feed dell'utente.
 * @param string $post_text Testo del post
 * @param int $abstract_lenght Lunghezza dell'anteprima. Di default 50 caratteri.
 * @throws InvalidArgumentException Qualora $abstract_lenght sia un numero negativo.
 * @return string stringa contenente il testo di anteprima. 
 */
function get_post_preview_abstract(string $post_text, int $abstract_lenght = 50): string
{
    if ($abstract_lenght < 0) {
        throw new InvalidArgumentException('Non puoi avere una stringa di lunghezza negativa.');
    }

    $post_lenght = strlen($post_text);

    $abstract_lenght = $post_lenght < $abstract_lenght ? $post_lenght : $abstract_lenght;

    // I puntini finali servono ad indicare all'utente che il testo del post è più lungo di quel che vede.
    $abstract = substr($post_text, 0, $abstract_lenght) . ' ... ';

    return $abstract;
}