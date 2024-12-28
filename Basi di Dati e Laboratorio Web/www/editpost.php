<?php
require_once "tools/general.php";
redirect_if_not_logged_in();

$must_redirect = true; // Assumo di default che il post non esista o che l'utente non abbia l'autorizzazione a modificarlo (ovvero non ne sia l'autore)

$current_user = get_current_user_username();

if(!there_is_a_current_post() || !can_current_user_edit_current_post())
{
    redirect();
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica Post - Idearium</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
</head>

<body class="wrapper">
    <?php include "header.php"; ?>
    <main>
        <div class="container">
            <section class="edit_post">
                <h2>Modifica il tuo Post</h2>
                <form action="actions/edit_post_action.php" method="post" enctype="multipart/form-data"
                    class="edit-post-form">
                    <div class="form_group">
                        <label for="post-title" class="bold_text">Nuovo titolo del post</label>
                        <input type="text" id="post_title" name="post_title"
                            value="<?php echo get_current_post_title(); ?>">
                    </div>
                    <div class="form_group">
                        <label for="post-image" class="bold_text">Nuova immagine del post (opzionale)</label>
                        <input type="file" id="post_image" name="post_image" accept="image/*">

                        <?php if (has_current_post_image()): ?>
                            <button type="submit" name="delete_post_image">Elimina l'immagine dal post</button>
                        <?php endif; ?>

                    </div>

                    <div class="form_group">
                        <label for="post-content" class="bold_text">Modifica il testo</label>
                        <textarea id="post_content" name="post_text" rows="10">
                                <?php echo get_current_post_text(); ?>
                            </textarea>
                    </div>
                    <div class="edit_group">
                        <button type="submit" name="update_post" class="classic_button">Conferma modifiche</button>
                        <button type="submit" name="delete_post" class="delete_button">Elimina post</button>
                    </div>
                </form>
            </section>
        </div>
    </main>
    <?php
    include_once 'footer.php';
    include_once 'outcome_message.php';
    ?>
</body>

</html>