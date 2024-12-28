<?php
require_once 'tools/general.php';
redirect_if_not_logged_in();
?>

<!DOCTYPE html>
<html lang="it">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Idearium - Impostazioni</title>
  <link rel="stylesheet" href="styles.css">
</head>

<body>
  <?php
  include_once "header.php";
  ?>

  <div class="wrapper">
    <main>
      <div class="container" id="setting_container">
        <h2>Benvenuto <?php echo $_SESSION['current_user'][USER_USERNAME]; ?></h2>

        <form action="actions/settings_action.php" method="post" id="settings_form" enctype="multipart/form-data">

          <div class="setting_group">
            <label for="complete_name" class="bold_text">Modifica il tuo nome:</label>
            <input type="text" class="text_input" name="complete_name" id="new_complete_name"
              placeholder="<?php echo $_SESSION['current_user'][USER_COMPLETE_NAME] ?>">
            <span class="filter_error_message" id="new_completename_error"></span>
          </div>

          <div class="setting_group">
            <label for="user_bio" class="bold_text">Modifica la tua bio:</label>
            <textarea class="text_input" name="user_bio" rows="5"
              placeholder="<?php echo $_SESSION['current_user'][USER_BIO]; ?>"></textarea>
          </div>

          <div class="setting_group">
            <label for="email" class="bold_text">Modifica la tua email:</label>
            <input type="text" class="text_input" name="email" id="new_email"
              placeholder="<?php echo $_SESSION['current_user'][USER_EMAIL] ?>">
            <span class="filter_error_message" id="new_email_error"></span>
          </div>

          <div class="setting_group">
            <label for="password" class="bold_text">Modifica la tua password (8 caratteri min.):</label>
            <input type="text" class="text_input" name="password" id="new_password"
              placeholder="&bull; &bull; &bull; &bull; &bull; &bull; &bull; &bull;">
            <span class="filter_error_message" id="new_password_error"></span>
          </div>

          <div class="setting_group">
            <label for="new-avatar" class="bold_text">Modifica il tuo avatar</label>
            <input type="file" id="new_avatar" name="new_avatar" accept="image/*">
            <span class="filter_error_message" id="new_avatar_error"></span>
            <button name="remove_avatar" type="submit" class="little_delete_button">Elimina il tuo avatar</button>
          </div>

          <div class="edit_group">
            <button type="submit" class="classic_button" id="setting_button" name="edit_account">Salva le
              modifiche</button>
            <button type="submit" class="delete_button" name="delete_account" id="delete_account">Cancella il tuo
              account</button>
          </div>
        </form>
      </div>
    </main>
    <?php
    include_once 'outcome_message.php';
    include_once 'footer.php';
    ?>
  </div>
  
</body>

</html>