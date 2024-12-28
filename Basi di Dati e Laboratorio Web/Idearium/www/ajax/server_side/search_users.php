<?php
require_once '../../tools/general.php';
redirect_if_not_accessed_through_post();

try {
    $found_users = null;
    $error_message = null;

    if (!isset($_POST['users_query'])) {
        throw new Exception('Il parametro POST non è stato inviato');
    }

    $query = $_POST['users_query'];

    $found_users = search_users($db_connection, $query);

} catch (Exception $e) {
    $error_message = 'Errore nella ricerca: ' . $e->getMessage();
} finally {
    $output_message = [
        'found_users' => $found_users,
        'error' => $error_message
    ];

    $json_encoded_output_message = json_encode($output_message);
    echo $json_encoded_output_message;
}

