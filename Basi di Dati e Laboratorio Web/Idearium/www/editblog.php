<?php
require_once 'tools/general.php';
redirect_if_not_logged_in();

$user_profile_page = 'user.php?id=' . get_current_user_username();

if(!there_is_a_current_blog() || !can_current_user_edit_current_blog()){
    redirect();
}

$_SESSION['current_blog']['coauthors'] = get_blog_coauthors($db_connection, get_current_blog_id());

if(there_is_an_error()){
    get_error();
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Modifica Blog - Idearium </title>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
</head>

<body class="wrapper">
    <?php include_once "header.php"; ?>
    <main>
        <script src="ajax/client_side/search_users.js" defer></script>

        <div class="container">
            <section class="edit-blog">
                <h2>Modifica il tuo Blog</h2>
                <form action="actions/edit_blog_action.php" method="post" enctype="multipart/form-data"
                    class="edit-blog-form">

                    <div class="form_group">
                        <label for="blog-title" class="bold_text">Nuovo titolo visualizzato</label>
                        <input type="text" id="blog_title" name="new_blog_title"
                            value="<?php echo $_SESSION['current_blog'][BLOG_TITLE]; ?>">
                    </div>

                    <div class="form_group">
                        <label for="blog-description" class="bold_text">Modifica descrizione</label>
                        <textarea id="edit_description" name="new_blog_description"
                            rows="5"><?php echo $_SESSION['current_blog'][BLOG_DESCRIPTION]; ?></textarea>
                        <div class="form_group">
                            <label for="blog-logo" class="bold_text">Nuovo logo</label>
                            <input type="file" id="blog_logo" name="blog_logo" accept="image/*">
                            <?php
                            if (has_current_blog_logo()):
                                ?>
                                <button type="submit" name="delete_blog_logo_button" class="delete_button"> Elimina il logo
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="form_group">
                        <h3>Nuova categoria</h3>
                        <?php include_once 'blog_category_selection.php'; ?>
                    </div>
                    <div class="form_group">
                        <label for="search_a_new_coauthor">Scegli un nuovo autore:</label>
                        <input type="text" id="search_a_new_coauthor" name="search_a_new_coauthor" />
                        <div id="new_coauthors">
                            <!-- Qui viene visualizzata dinamicamente, tramite AJAX, la lista dei nuovi autori da scegliere -->
                        </div>
                        <button type="submit" class="add_coauthors_button" name="set_new_coauthors"
                            id="set_new_coauthors">Aggiungi autori</button>
                    </div>

                    <div class="form_group">
                        <label>Rimuovi coautori</label>
                        <ul>
                            <?php foreach (get_current_blog_coauthors() as $coauthor): ?>
                                <?php
                                $username = $coauthor[USER_USERNAME];
                                $email = $coauthor[USER_EMAIL];
                                $complete_name = $coauthor[USER_COMPLETE_NAME];
                                ?>
                                <li>
                                    <label for="<?php echo $username ?>">
                                        <?php echo $complete_name ?> (<?php echo $username ?>, <?php echo $email ?>)
                                    </label>
                                    <input type="checkbox" name="coauthors_to_remove[]" value="<?php echo $username ?>" />
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="submit" name="remove_coauthors" class="remove_coauthors_button">Elimina
                            coautori</button>
                    </div>
                    <div class="edit_group">
                        <button type="submit" name="update_blog_button" class="classic_button">Conferma le modifiche al
                            blog
                        </button>
                        <button type="submit" class="delete_button" name="delete_blog_button"
                            onclick="return confirm('Sei sicuro di voler eliminare questo blog?');">Elimina il
                            blog
                        </button>
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