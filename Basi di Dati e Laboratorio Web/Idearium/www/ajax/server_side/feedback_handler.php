<?php
/**
 * Script per l'aggiornamento dei feedback tramite AJAX.
 * Qui come altrove, l'ID del post e il nome utente dell'autore del feedback vengono presi da $_SESSION piuttosto che passati
 * con $_POST per evitare di ripetere controlli di validità dell'input, che vengono fatti rispettivamente al momento dell'apertura della pagina del post e al momento del login dell'utente.
 * 
 * Se i dati fossero stati inviati attraverso POST, esisteva il rischio che un utente, aprendo gli strumenti di sviluppo del suo browser, 
 * cambiasse il valore dell'attributo "value" dei relativi campi di "input", rendendo dunque necessaria una validazione ulteriore.
 */

require_once '../../tools/general.php';
redirect_if_not_accessed_through_post();

$post_id = get_current_post_id();

try {
    $current_feedback = null;
    $error_message = null;

    $username = get_current_user_username();
    $current_feedback = get_current_user_feedback();
    $user_already_gave_a_feedback = !is_null($current_feedback);
    $there_is_a_new_feedback = isset($_POST['new_feedback']);

    if (!$there_is_a_new_feedback) return;

    $new_feedback = (int) $_POST['new_feedback'];

    $user_wants_to_give_a_new_feedback = $current_feedback !== $new_feedback;

    // Se l'utente aveva già dato un feedback, sicuramente devo rimuovere il vecchio
    if ($user_already_gave_a_feedback) {
        remove_feedback($db_connection, $username, $post_id);
        $current_feedback = null;
    }

    if ($user_wants_to_give_a_new_feedback) {
        add_feedback($db_connection, $username, $post_id, $new_feedback);
        $current_feedback = $new_feedback;
    }
    
    set_current_feedback($current_feedback);
    $total_post_feedbacks = get_post_feedbacks($db_connection, $post_id);

} catch (Exception $e) {

    $error_message = 'Errore nell\'aggiornare i feedback: ' . $e->getMessage();

} finally {
    $output_message = [
        'current_user_feedback' => $current_feedback,
        'total_post_feedbacks' => $total_post_feedbacks,
        'error' => $error_message
    ];

    $json_encoded_output_message = json_encode($output_message);
    echo $json_encoded_output_message;
}