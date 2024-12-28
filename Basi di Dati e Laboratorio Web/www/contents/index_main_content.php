<?php
redirect_if_page_is_accessed_directly(__FILE__);

if (is_user_logged_in()): // Controllo che l'utente abbia effettuato l'accesso ?>
    <?php $current_user_username = get_current_user_username(); ?>

    <?php $feed = get_user_feed($db_connection, $current_user_username); ?>

    <div class="loggedin">
        <h3 class="loggedin_h3"> Bentornato <?php echo $current_user_username; ?> !</h3>
        <?php if (is_null($feed)): ?>
            <p class="welcome_message"> Cerca qualche blog da seguire: prova a <a class="first_link" href="browsebycategory.php">navigare tra le categorie</a> </p>
        <?php else: ?>
            <div class="usser_blogs_list">
                <h3>Ecco qui le ultime notizie dai blog che stai seguendo</h3>
                <div class="blog_list">
                    <?php
                    foreach ($feed as $blog_id => $feed_element) {

                        $blog_title = $feed_element[BLOG_TITLE];
                        $posts = $feed_element['posts'];

                        foreach ($posts as $post_id => $post_info) {
                            include 'feed_post_view.php';
                        }
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?> <!-- Se la pagina viene visualizzata da un utente non loggato, visualizzo un menù opportuno -->
        <section class="intro">
            <h2 class="intro_h2">Benvenuto su Idearium!</h2>
            <div class="introduction_div">
                <h3 class="intro_h3">Sei già registrato con noi?</h3>
                <p class="intro_text">Allora accedi con le tue credenziali!</p>
                <a href="login.php" class="button_style"><span class="fas fa-sign-in"></span>Accedi</a>
            </div>
            <div class="introduction_div">
                <h3 class="intro_h3">Non sei ancora registrato con noi?</h3>
                <p class="intro_text">Registrati subito e non perderti niente!</p>
                <a href="signup.php" class="button_style"><span class="fa-solid fa-user"></span>Registrati</a>
            </div>
        </section>
        <?php
endif; // Fine primo IF
?>