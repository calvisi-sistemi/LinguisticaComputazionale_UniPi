<?php

require_once '../tools/general.php';
redirect_if_not_accessed_through_post();

try {

    $redirect_destination = '../settings.php';

    $username = get_current_user_username();

    if (isset($_POST['delete_account'])) { // In caso si scelga di cancellare l'account
        delete_user($db_connection, $username);
        unset($_SESSION['current_user'][USER_USERNAME]); // Equivale a fare un 'logout' ma senza terminare l'intera sessione.
        $_SESSION['success'] = 'Utente eliminato con successo!';
        $redirect_destination = '../signup.php';

    } else if (isset($_POST['edit_account'])) { // In caso si scelga di modificarne un impostazione
        $new_avatar_file = $_FILES['new_avatar'];

        $new_complete_name = htmlspecialchars($_POST['complete_name']);
        $new_email = htmlspecialchars($_POST['email']);
        $new_password = $_POST['password'];
        $new_bio = htmlspecialchars(trim($_POST['user_bio']));

        $new_complete_name_was_choosen = !empty($new_complete_name);
        $new_email_was_choosen = !empty($new_email);
        $new_password_was_choosen = !empty($new_password);
        $new_bio_was_choosen = !empty($new_bio);
        $new_avatar_was_choosen = is_uploaded_file($new_avatar_file['tmp_name']);

        if ($new_complete_name_was_choosen) {
            update_user_complete_name($db_connection, $username, $new_complete_name);
            $_SESSION['current_user'][USER_COMPLETE_NAME] = $new_complete_name;
        }

        if ($new_email_was_choosen) {
            update_user_email($db_connection, $username, $new_email);
            $_SESSION['current_user'][USER_EMAIL] = $new_email;
        }

        if ($new_password_was_choosen) {
            update_user_password($db_connection, $username, $new_password);
        }

        if ($new_bio_was_choosen) {
            update_user_bio($db_connection, $username, $new_bio);
            $_SESSION['current_user'][USER_BIO] = $new_bio;
        }

        if ($new_avatar_was_choosen) {
            check_uploaded_file_errors($new_avatar_file);
            $_SESSION['current_user'][USER_AVATAR] = update_user_avatar($db_connection, $username, $new_avatar_file);
            unset($_FILES['new_avatar']);
        }

        $_SESSION['success'] = 'Impostazioni aggiornate correttamente!';

    } else if (isset($_POST['remove_avatar'])) {
        delete_user_avatar($db_connection, $username);

        $_SESSION['success'] = 'Rimozione dell\'avatar riuscita';

    } else {
        throw new Exception('I dati non sono stati inviati correttamente');
    }

} catch (Exception $e) {
    $error = 'Errore nella modifica delle impostazioni: ' . $e->getMessage();
    set_error($error);
    $redirect_destination = '../settings.php';
} finally {
    redirect($redirect_destination);
}
