<?php
require_once '../../tools/general.php';
redirect_if_not_accessed_through_post();

try {
    $subcategories = null;
    $error_message = null;

    if (!isset($_POST['main_category'])) {
        throw new Exception('Il parametro main_category non è stato inviato');
    }

    $main_category = $_POST['main_category'];

    $subcategories = get_subcategories($db_connection, $main_category);

} catch (Exception $e) {

    $error_message = 'Errore nella ricerca delle sottocategorie: ' . $e->getMessage();

} finally {

    $output_message = [
        'subcategories' => $subcategories,
        'error' => $error_message
    ];

    $json_encoded_output_message = json_encode($output_message);

    echo $json_encoded_output_message;
}