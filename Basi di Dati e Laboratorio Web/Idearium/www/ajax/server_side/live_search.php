<?php
require_once '../../tools/general.php';
redirect_if_not_accessed_through_post();

try {
    $found_results = [];//Utilizzo una logica differente, usando un array, vuoto inizialmente, per l'output
    $error_message = null;
    $at_least_one_result_is_found = false;

    if (!isset($_POST['users_live_search'])) {
        throw new Exception('Il parametro POST non è stato inviato');

    }

    $query = $_POST['users_live_search'];

    //Eseguiamo le tre funzioni di ricerca
    $blog_results = search_blogs($db_connection, $query);
    $post_results = search_post($db_connection, $query);
    $user_results = search_users($db_connection, $query);

    //Qui combiniamo in un solo array i risultati delle tre ricerche
    $found_results['blogs'] = $blog_results;
    $found_results['posts'] = $post_results;
    $found_results['users'] = $user_results;
    
    /*
    //Verifichiamo che almeno un risultato esista
    $at_least_one_result_is_found = !empty($found_results);

    if ($at_least_one_result_is_found) {
        $output_data = $found_results;
    }*/

} catch (Exception $e) {
    $error_message = 'Errore nella ricerca: ' . $e->getMessage();
} finally {
    $output_message = [
        'found_results' => $found_results,
        'error' => $error_message
    ];

    $json_encoded_output_message = json_encode($output_message);
    echo $json_encoded_output_message;
}