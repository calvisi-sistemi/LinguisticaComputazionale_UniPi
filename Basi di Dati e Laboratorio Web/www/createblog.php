<?php
require_once 'tools/general.php';
redirect_if_not_logged_in();
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creazione Blog - Idearium</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
</head>

<body>
    <div class="wrapper">
        <?php include 'header.php'; ?>
        <main>
            <div class="container">
                <section class="create_blog">
                    <h2>Crea il tuo Blog!</h2>
                    <form action="actions/create_blog_action.php" method="post" enctype="multipart/form-data"
                        class="create_blog_form" id="create_blog_form">

                        <div class="form_group">
                            <label for="blog-title" class="bold_text">Titolo Visualizzato *</label>
                            <input type="text" id="blog_title" name="blog_title" required>
                            <span class="filter_error_message" id="blogtitle_error"></span>
                        </div>

                        <div class="form_group">
                            <label for="blog_description" class="bold_text">Descrizione *</label>
                            <textarea id="blog_description" name="blog_description" required rows="5"></textarea>
                            <span class="filter_error_message" id="blogdescription_error"></span>
                        </div>

                        <div class="form_group">
                            <?php
                            include_once 'blog_category_selection.php';
                            ?>
                        </div>

                        <div class="form_group">
                            <label for="blog-logo" class="bold_text">Logo</label>
                            <input type="file" id="blog_logo" name="blog_logo" accept="image/*" />
                            <span class="filter_error_message" id="bloglogo_error"></span>
                        </div>
                        <button type="submit" class="classic_button">Crea Blog</button>
                    </form>
                </section>
            </div>
        </main>
    </div>
    
    <?php
        include 'footer.php';
        include 'outcome_message.php'
    ?>
</body>

</html>