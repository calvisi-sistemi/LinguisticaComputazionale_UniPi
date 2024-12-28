<?php
require_once '../tools/general.php';
redirect_if_not_accessed_through_post();

try {

    $redirect_destination = '../index.php';

    if (empty($_POST['nome_utente'])) {
        throw new Exception('Non hai inserito il nome utente');
    }

    if (empty($_POST['password'])) {
        throw new Exception('Non hai inserito la password');
    }

    $username = $_POST['nome_utente'];
    $password = $_POST['password'];
    $save_login = isset($_POST['remember_me']);

    login_user($db_connection, $username, $password, $save_login);

} catch (Exception $e) {

    set_error($e->getMessage());
    
    $redirect_destination = '../login.php';

} finally {
    redirect($redirect_destination);
}