<?php
/**
 * Gestione dei feedback (Upvote e Downvote)
 */


/**
 * Ottieni il numero di feedback di un post distinti in positivi e negativi.
 * @param PDO $db_connection Connessione al database
 * @param int $post_id ID del post di cui ottenere i feedback
 * @throws Exception in caso di errore
 * @return array array associativo avente la struttura:
 * [
 *  'GOOD_FEEDBACKS' => numero_feedback_positivi,
 *  'BAD_FEEDBACKS' => numero_feedback_negativi
 * ]
 */
function get_post_feedbacks(PDO $db_connection, int $post_id): array
{
    try {
        $feedbacks = ['GOOD_FEEDBACKS' => 0, 'BAD_FEEDBACKS' => 0];
        
        $sql = 'SELECT 
            SUM(CASE WHEN `' . FEEDBACK_TYPE . '` = :good_feedback THEN 1 ELSE 0 END) AS GOOD_FEEDBACKS,
            SUM(CASE WHEN `' . FEEDBACK_TYPE . '` = :bad_feedback THEN 1 ELSE 0 END) AS BAD_FEEDBACKS
        FROM `' . FEEDBACK_TABLE . '`
        WHERE `' . FEEDBACK_POST . '` = :post_id';
        
        $stmt = $db_connection->prepare($sql);
        $stmt->bindValue(':post_id', $post_id, PDO::PARAM_INT);
        $stmt->bindValue(':good_feedback', GOOD_FEEDBACK, PDO::PARAM_INT);
        $stmt->bindValue(':bad_feedback', BAD_FEEDBACK, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!empty($result)){
            $feedbacks = $result;
        }

        return $feedbacks;

    } catch (Exception $e) {
        throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : '  . $e->getMessage());
    }
}

function add_feedback(PDO $db_connection, string $username, int $post_id, int $feedback_type = GOOD_FEEDBACK | BAD_FEEDBACK): void
{
    try {
        $db_connection->beginTransaction();
        $sql = 'INSERT INTO `' . FEEDBACK_TABLE . '` 
            (`' . FEEDBACK_POST . '`, `' . FEEDBACK_AUTHOR . '`, `' . FEEDBACK_TYPE . '`)
            VALUES (:post_id, :username, :feedback_type)';
        $stmt = $db_connection->prepare($sql);
        $stmt->bindValue(':post_id', $post_id, PDO::PARAM_INT);
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->bindValue(':feedback_type', $feedback_type, PDO::PARAM_INT);
        $stmt->execute();
        $db_connection->commit();
    } catch (Exception $e) {
        if ($db_connection->inTransaction()) {
            $db_connection->rollBack();
        }
        throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : '  . $e->getMessage());
    }
}

function remove_feedback(PDO $db_connection, string $username, int $post_id): void
{
    try {
        $db_connection->beginTransaction();
        $sql = 'DELETE FROM `' . FEEDBACK_TABLE . '` 
                WHERE `' . FEEDBACK_POST . '` = :post_id 
                AND `' . FEEDBACK_AUTHOR . '` = :username';
        $stmt = $db_connection->prepare($sql);
        $stmt->bindValue(':post_id', $post_id, PDO::PARAM_INT);
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $db_connection->commit();
    } catch (Exception $e) {
        if ($db_connection->inTransaction()) {
            $db_connection->rollBack();
        }
        throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : '  . $e->getMessage());
    }
}

/**
 * Ottiene il tipo di feedback che un utente ha dato ad un certo post.
 * @param PDO $db_connection Connessione al database
 * @param string $username Username dell'utente
 * @param int $post_id ID del post di cui si vogliono ottenere i feedback dell'utente
 * @throws UnexpectedValueException In caso di più di un feedback da parte dello stesso utente per lo stesso post. 
 * @throws Exception In tutti gli altri casi di errore
 * @return int|null restituisce un intero con il tipo di feedback qualora ve ne siano (GOOD_FEEDBACK == 1, BAD_FEEDBACK == -1), 
 * mentre resituisce NULL qualora l'utente non abbia dato alcun feedback al post in questione
 */
function which_feedback_user_gave_to_this_post(PDO $db_connection, string $username, int $post_id): int | null{
    try{
        $given_feedback = null;
        $sql = 'SELECT `'. FEEDBACK_TYPE .'` FROM `'. FEEDBACK_TABLE .'` WHERE 
        `'. FEEDBACK_AUTHOR .'` = :username AND
        `'. FEEDBACK_POST .'` = :post_id';
        $stmt = $db_connection->prepare($sql);
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->bindValue(':post_id', $post_id, PDO::PARAM_INT);
        $stmt->execute();
        $feedback = $stmt->fetchColumn();

        if($feedback !== false){
            $given_feedback = (int) $feedback;
        }

        return $given_feedback;

    }catch(Exception $e){
        throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
    }
}