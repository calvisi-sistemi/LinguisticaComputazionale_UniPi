<?php
require_once 'tools/general.php';
redirect_if_already_logged_in();
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body class="wrapper">
    <?php
    include "header.php";
    ?>
    <main>
        <div class="container">
            <section class="login_form">
                <h2>Login</h2>
                <form method="post" action="actions/login_action.php">
                    <div class="form_group">
                        <label for="nome_utente">Nome Utente:</label>
                        <input type="text" class="text_input" id="nome_utente" name="nome_utente" required>
                    </div>
                    <div class="form_group">
                        <label for="password">Password:</label>
                        <input type="password" class="text_input" id="password" name="password" required>
                        <button type="button" class="help_button" id="show_password" name="show_password">
                          <span class="fa-solid fa-eye"></span> Mostra password
                        </button>
                    </div>
                    <div>
                        <label for="remember_me">Ricorda l'accesso</label>
                        <input type="checkbox" name="remember_me" id="remember_me"/>
                    </div>
                    <button type="submit" class="classic_button">Accedi</button>
                </form>
                <?php
                if (isset($_SESSION["login_outcome_error"])): ?>
                <div class="error_message">
                    <p><?php echo $_SESSION["login_outcome_error"];
                    ?></p>
                </div>
                <?php
                unset($_SESSION["login_outcome_error"]);
                endif; ?>
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