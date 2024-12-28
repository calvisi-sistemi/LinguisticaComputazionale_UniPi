<?php
require_once 'tools/general.php';
redirect_if_already_logged_in();
?>
<!DOCTYPE html>
<html lang="it">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrazione - Idearium</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="icon" type="image/x-icon" href="favicon.ico">
</head>

<body class="wrapper">
  <?php include "header.php"; ?>
  <main>
    <div class="container">
      <section class="register_form">
        <h2>Registrazione</h2>

        <form id="register-form" method="post" enctype="multipart/form-data" action="actions/signup_action.php">

          <div class="form_group">
            <label for="nome_utente" class="bold_text">Nome Utente (lettere e numeri) *</label>
            <input type="text" class="text_input" id="nome_utente" name="nome_utente" required>
            <span class="filter_error_message" id="username_error"></span>
          </div>

          <div class="form_group">
            <label for="nome_visualizzato" class="bold_text">Nome visualizzato (lettere e spazi bianchi) *</label>
            <input type="text" class="text_input" id="nome_visualizzato" name="nome_visualizzato" required>
            <span class="filter_error_message" id="completename_error"></span>
          </div>

          <div class="form_group">
            <label for="email" class="bold_text">Email *</label>
            <input type="email" class="text_input" id="email" name="email" required>
            <span class="filter_error_message" id="email_error"></span>
          </div>

          <div class="form_group">
            <label for="user_bio">Bio</label>
            <textarea class="text_input" id="user_bio" name="user_bio" rows=5></textarea>
            <span class="filter_error_message" id="bio_error"></span>
          </div>

          <div class="form_group">

            <div>
              <label for="password" class="bold_text">Password (8 caratteri min.) *</label>
              <input type="password" class="text_input" id="password" name="password" required>
              <button type="button" class="help_button" id="show_password" name="show_password">
                <span class="fa-solid fa-eye"></span> Mostra Password
              </button>
            </div>
            
            <div>
              <label for="password_confirm" class="bold_text">Conferma password *</label>
              <input type="password" class="text_input" id="password_confirm" name="password_confirm" required>
            </div>
          </div>

          <div class="form_group">
            <label for="user_avatar" class="bold_text">Avatar (opzionale)</label>
            <input type="file" id="user_avatar" name="user_avatar" accept="image/*">
          </div>

          <button type="submit" class="classic_button">Registrati</button>

        </form>

      </section>
    </div>
    <script src = "js_scripts/show_password_jquery.js" defer></script>
  </main>
  <?php
    include 'outcome_message.php';
    include 'footer.php';
  ?>
</body>
</html>