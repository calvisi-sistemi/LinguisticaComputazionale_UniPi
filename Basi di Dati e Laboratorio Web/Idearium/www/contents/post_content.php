<?php
$post_creation_datetime_iso = date(DATETIME_ISO_FORMAT, get_current_post_creation_date());
$post_creation_datetime_readable = date(DATETIME_READABLE_FORMAT, get_current_post_creation_date());
$post_last_edit_datetime_iso = date(DATETIME_ISO_FORMAT, get_current_post_last_edit_date());
$post_last_edit_datetime_readable = date(DATETIME_READABLE_FORMAT, get_current_post_last_edit_date());
$comments_list = get_zero_level_post_comments($db_connection, get_current_post_id());
$printable_current_feedback = get_current_user_feedback() === null ? 'null' : get_current_user_feedback();
?>

<script>
    // Variabile globale adoperata dallo script AJAX.
    var current_user_feedback = <?php echo $printable_current_feedback ?>;    
</script>

<script src="ajax/client_side/feedback_handler.js" defer></script>

<article>
    <h1>
        <?php echo get_current_post_title(); ?>
    </h1>
    <address>
        Autore: <a class="red_link"
            href="user.php?id=<?php echo get_current_post_author() ?>"><?php echo get_current_post_author(); ?></a>
    </address>

    <time date="<?php echo $post_creation_datetime_iso; ?>" id="creation">
        Pubblicato il: <?php echo $post_creation_datetime_readable; ?>
    </time>

    <time date="<?php echo $post_last_edit_datetime_iso; ?>" id="last_edit">
        Ultima modifica <?php echo $post_last_edit_datetime_readable; ?>
    </time>

    <section class="post_content">
        <div class="post_content_buttons">
            <?php if (can_current_user_edit_current_post()): ?>
                <a href="editpost.php" class="post_content_link">
                    <span class="fas fa-edit"></span>Modifica il post
                </a>
            <?php endif; ?>
            <a href="#new_comment" class="post_content_link"><span class="fas fa-comment"></span>Commenta il post</a>
            <button type="button" class="feedback positive_feedback" id="upvote">
                <span class="fa-solid fa-thumbs-up"></span>Upvote <span
                    id="upvote_counter"><?php echo get_current_post_good_feedbacks_number() ?></span>
            </button>
            <button type="button" class="feedback negative_feedback" id="downvote">
                <span class="fas fa-thumbs-down"></span> Downvote <span
                    id="downvote_counter"><?php echo get_current_post_bad_feedbacks_number() ?></span>
            </button>
        </div>

        <div class="post_content_text">
            <p>
                <?php echo get_current_post_text(); ?>
            </p>
        </div>

        <?php if (has_current_post_image()):
            $img_url = get_image_path(get_current_post_image()); ?>
            <img class="article_image" src="<?php echo $img_url; ?>" alt="<?php echo get_current_post_image(); ?>" />
        <?php endif; ?>

    </section>



    <section class="post_comments">
        <h2>
            Commenti: <span id="comments_count"><?php echo get_current_post_comments_number() ?></span>
        </h2>
        <section class="new_comment" id="new_comment">
            <h3>Scrivi il tuo commento</h3>
            <?php show_comment_form(); ?>
        </section>
        <ul class="comments_list" id="comments_general_list">

            <?php
            if (empty($comments_list)) {
                return;
            }
            
            foreach ($comments_list as $comment) {
                include 'comment_template.php';
            }
            ?>

        </ul>
    </section>
</article>