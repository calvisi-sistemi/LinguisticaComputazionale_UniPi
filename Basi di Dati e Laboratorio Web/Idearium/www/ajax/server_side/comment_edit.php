<?php
require_once '../../tools/general.php';

redirect_if_not_accessed_through_post();

$post_id = get_current_post_id();

try {

    if (!isset($_POST['comment_id'])) {
        throw new Exception('Non è stato selezionato nessun commento da modificare.');
    }

    $comment_id = $_POST['comment_id'];

    if (!does_comment_exist($db_connection, $comment_id)) {
        throw new Exception('Il commento non esiste');
    }

    $old_text = clean_text($_POST['old_text']);
    $new_text = clean_text($_POST['new_text']);

    if (empty($new_text) || $old_text === $new_text || empty($old_text)) return;

    update_comment($db_connection, $comment_id, $new_text);

    $response = [
        'success' => true,
    ];

} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => $e->getMessage()
    ];

}finally{
    if(isset($response)) echo json_encode($response);
}