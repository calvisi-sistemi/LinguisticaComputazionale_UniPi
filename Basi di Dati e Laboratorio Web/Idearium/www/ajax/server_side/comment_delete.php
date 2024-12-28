<?php
require_once '../../tools/general.php';

redirect_if_not_accessed_through_post();

$post_id = get_current_post_id();

try {
    if (!isset($_POST['comment_to_delete'])) {
        throw new Exception('Non è stato selezionato nessun commento da modificare.');
    }

    $comment_id = $_POST['comment_to_delete'];

    if (!does_comment_exist($db_connection, $comment_id)) {
        throw new Exception('Il commento non esiste.');
    }

    delete_comment($db_connection, comment_id: $comment_id);
    
    $response = 
    [
        'success' => true,
    ];

} catch (Exception $e) {

    $response =  
    [
        'success' => false, 
        'message' => 'Errore nella cancellazione del commento: ' . $e->getMessage()
    ];
} finally {
    echo json_encode($response);
}