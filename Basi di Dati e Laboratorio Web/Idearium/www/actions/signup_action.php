<?php
require_once '../tools/general.php';
redirect_if_not_accessed_through_post();

try {
  $redirect_destination = '../login.php';


  if (!is_string($_POST['nome_utente']) || !is_string($_POST['nome_visualizzato']) || !is_string($_POST['email']) || !is_string($_POST['password'])) {
    throw new Exception('Devi compilare tutti i campi obbligatori');
  }

  $username = strtolower(clean($_POST['nome_utente'])); // Ripulisce il nome utente e porta tutti i caratteri in minuscolo
  $complete_name = htmlspecialchars(stripslashes($_POST['nome_visualizzato'])); // Rimuove gli slash e i converte i caratteri speciali in entità HTML
  $email = $_POST['email'];
  $password = $_POST['password'];
  $password_confirm = $_POST['password_confirm'];
  $bio = htmlspecialchars($_POST['user_bio']);
  $user_chose_an_avatar = is_uploaded_file($_FILES['user_avatar']['tmp_name']);

  if($password !== $password_confirm){
    throw new Exception('La password scelta e quella di conferma non corrispondono');
  }

  check_new_user_information(
    db_connection: $db_connection,
    username: $username,
    complete_name: $complete_name,
    email: $email,
    password: $password,
    bio: $bio
  );

  if ($user_chose_an_avatar) {
    $avatar_file = $_FILES['user_avatar'];
    unset($_FILES['user_avatar']);
    check_uploaded_file_errors($avatar_file);
  }

  create_user(
    db_connection: $db_connection, 
    username: $username,  
    user_password: $password, 
    user_email: $email, 
    user_complete_name: $complete_name, 
    user_bio: $bio, 
    avatar_file: $avatar_file
  );

} catch (Exception $e) {
  $redirect_destination = '../signup.php';
  $error = 'Errore nella registrazione: ' . $e->getMessage(); 
  set_error($error);
} finally {
  redirect($redirect_destination);
}