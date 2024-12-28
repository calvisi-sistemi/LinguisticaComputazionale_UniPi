<?php
/**
 * Gestione delle iscrizioni (iscrizione, disiscrizione) a un blog tramite AJAX-
 */

 require_once '../../tools/general.php';
redirect_if_not_accessed_through_post();

try {
    $error_message = null;
    $username = get_current_user_username();
    $blog_id = get_current_blog_id();
    
    $subscription_status = is_user_subscribed($db_connection, $username, $blog_id);

    $user_toggled_subscription = isset($_POST['user_pressed_subscription_button']);

    if($user_toggled_subscription){
        $subscription_status = toggle_subscription($db_connection, $username, $blog_id, $subscription_status);
    }

} catch (Exception $e) {
    $error_message = 'Errore: ' . $e->getMessage();
}finally{
    $output_message = [
        'subscription_status' => $subscription_status,
        'error' => $error_message
    ];

    $json_encoded_output_message = json_encode($output_message);
    echo $json_encoded_output_message;
}