<?php
$posts_list = get_posts_list_from_blog($db_connection, $blog_id);
$admin_info = get_user_info($db_connection, get_current_blog_owner());
$avatar_path = IMAGE_DIRECTORY . $admin_info[USER_AVATAR];
?>

<section class="blog_details">
    <div class="blog_data">
        <h2 class="blog_data_h2"><?php echo get_current_blog_title(); ?></h2>

        <?php if (get_current_user_username() === get_current_blog_owner()): ?>
            <?php if (is_current_user_premium()): ?>
                <a href="subscribers.php" class="see_subscribers_blog">Vedi gli iscritti del tuo blog</a>
            <?php else: ?>
                Diventa premium per vedere gli iscritti del tuo blog
            <?php endif; ?>
        <?php endif; ?>

        <?php if (get_current_user_username() === get_current_blog_owner()): ?>
            <a href="editblog.php" class="redirect_link">
                Modifica Blog
            </a>
        <?php endif; ?>
        
        <?php if (has_current_blog_logo()): ?>
            <div class="blog_logo" id="blog_logo">
                <img class="blog_logo_image" src="<?php echo $blog_logo_path; ?>" alt="Logo del blog">
            </div>
        <?php endif; ?>
    </div>
    <div class="blog_description" id="blog_description">
        <p class="blog_description_text">
            <img class="BLOG_OWNER_avatar" src="<?php echo $avatar_path ?>" alt="Avatar dell'amministratore">
            <?php echo get_current_blog_owner(); ?>
        </p>
        <div class="blog_category_button">
            <a href="browsebycategory.php?category=<?php echo get_current_blog_category(); ?>">
                <?php echo get_current_blog_category(); ?>
            </a>
        </div>
    </div>

    <?php if (get_current_user_username() !== get_current_blog_owner()): ?>
        <script src="ajax/client_side/subscription_handler.js"></script>
        <button type="button" id="subscription_button" name="subscription_button" class="subscription_button">
        </button>
    <?php endif; ?>

</section>

<!-- Stampo la lista dei post del blog in questione -->
<section id="post_section">
    <?php if (is_null($posts_list)): ?>
        <p class="nothing_to_show_message"> Ancora nulla qui </p>
    <?php else: ?>
        <?php foreach ($posts_list as $post): ?>
            <a href="post.php?id=<?php echo $post[POST_ID] ?>">
                <article id="<?php echo $post[POST_ID]; ?>" class="tile_selection post_tile">

                    <h3>
                        <?php echo $post[POST_TITLE]; ?>
                        <span class="post_id">id: <?php echo $post[POST_ID]; ?></span>
                    </h3>
                    <div class="post_text">
                        <p>
                            <?php echo $post[POST_TEXT]; ?>
                        </p>
                        <?php if (!is_null($post[POST_IMAGE])): ?> <!-- Se è presente un'immagine nei post -->
                            <div class="post_image">
                                <img class="article_image" src="<?php echo get_image_path($post[POST_IMAGE]) ?>"
                                    alt="Immagine del post <?php echo $post[POST_TITLE] ?>" />
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</section>