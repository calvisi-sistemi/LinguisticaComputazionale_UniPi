<?php
require_once '../../tools/general.php';

redirect_if_not_accessed_through_post();

$post_id = get_current_post_id();
$redirect_destination = "../post.php?id=$post_id";
$comment_author = get_current_user_username();

try {
    if (!isset($_POST['new_comment_text'])) {
        throw new Exception('Non è stato ricevuto alcun commento.');
    }
    
    $new_comment_text = $_POST['new_comment_text'];
    $main_comment_id = isset($_POST['main_comment_id']) && !is_null($_POST['main_comment_id']) ? $_POST['main_comment_id'] : null;

    // Controllo se il commento è una risposta
    $is_a_reply = !is_null($main_comment_id);

    // Cast main_comment_id se è una risposta
    $main_comment_id = $is_a_reply ? (int) $main_comment_id : null;

    if ($is_a_reply && !does_comment_exist($db_connection, $main_comment_id)) {
        throw new Exception("Il commento con ID $main_comment_id non esiste.");
    }

    // Aggiungi il commento
    $new_comment_id = add_comment($db_connection, $post_id, $comment_author, $new_comment_text, $main_comment_id);

    // Genero l'HTML del commento
    $comment = [
        COMMENT_ID => $new_comment_id,
        COMMENT_AUTHOR => $comment_author,
        COMMENT_TEXT => htmlspecialchars($new_comment_text),
        COMMENT_CREATION_DATE => time(),
        COMMENT_LAST_EDIT => time(),
    ];
    $replied_comment = $is_a_reply ? $main_comment_id : null;

    ob_start();
    include '../../comment_template.php';
    $comment_html = ob_get_clean();

    $response = [
        'success' => true, 
        'new_comment_html' => $comment_html,
        'replied_comment_id' => $replied_comment
    ];

} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => 'Impossibile pubblicare il commento: ' . $e->getMessage()
    ];
} finally {
    echo json_encode($response);
}
