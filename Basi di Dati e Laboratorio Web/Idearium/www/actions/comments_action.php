<?php
require_once '../tools/general.php';

redirect_if_not_accessed_through_post();

$post_id = get_current_post_id();
$redirect_destination = "../post.php?id=$post_id";

try {

    $comment_author = get_current_user_username();

    if (isset($_POST['submit_new_comment'])) {

        $comment_text = clean_text($_POST['comment_text']);

        if (empty($comment_author)) {
            throw new Exception('Il nome dell\'autore non può essere vuoto');
        }

        if (empty($comment_text)) {
            throw new Exception('Il commento non può essere vuoto.');
        }

        add_comment($db_connection, $post_id, $comment_author, $comment_text);

    } else if (isset($_POST['submit_reply'])) {
        $comment_to_reply = $_POST['comment_to_reply'];

        $comment_does_not_exist = !does_comment_exist($db_connection, $comment_to_reply);

        if ($comment_does_not_exist) {
            throw new Exception('Il commento non esiste');
        }

        $reply_text = clean_text($_POST['reply_text']);

        if (empty($reply_text)) {
            throw new Exception('La risposta non può essere vuota.');
        }

        add_comment($db_connection, $post_id, $comment_author, $reply_text, $comment_to_reply);

    } else { // L'utente vuole operare su un commento già presente
        $comment_id = $_POST['comment_id'];
        $comment_does_not_exist = !does_comment_exist($db_connection, $comment_id);

        if ($comment_does_not_exist) {
            throw new Exception('Il commento non esiste');
        }

        if (isset($_POST['edit_comment'])) {
            $old_text = $_POST['old_comment_text'];
            $new_text = clean_text($_POST['new_comment_text']);

            if (empty($new_text)) {
                throw new Exception('Il commento non può essere vuoto.');
            }

            if ($old_text !== $new_text) {
                update_comment($db_connection, $comment_id, $new_text);
            }
        }

        if (isset($_POST['delete_comment'])) {
            delete_comment($db_connection, $comment_id);
        }

    }


} catch (Exception $e) {
    set_error('Impossibile pubblicare il commento: ' . $e->getMessage());
} finally {
    redirect($redirect_destination);
}