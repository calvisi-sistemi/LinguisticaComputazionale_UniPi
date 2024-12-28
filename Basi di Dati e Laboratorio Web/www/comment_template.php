<?php
redirect_if_page_is_accessed_directly(__FILE__);

$current_user_is_comment_author = false;

$current_user_is_comment_author = get_current_user_username() === $comment[COMMENT_AUTHOR];

// Funzioni per formattare le date
$comment_creation_datetime_iso = date(DATETIME_ISO_FORMAT, $comment[COMMENT_CREATION_DATE]); // Standard ISO 8601 per la rappresentazione del datetime. \T serve a separare la data dall'orario
$comment_creation_datetime_readable = date(DATETIME_READABLE_FORMAT, $comment[COMMENT_CREATION_DATE]);

$comment_last_edit_datetime_iso = date(DATETIME_ISO_FORMAT, $comment[COMMENT_LAST_EDIT]); // Standard ISO 8601 per la rappresentazione del datetime. \T serve a separare la data dall'orario
$comment_last_edit_datetime_readable = date(DATETIME_READABLE_FORMAT, $comment[COMMENT_LAST_EDIT]);

//$comment_was_modified = $comment_last_edit_datetime_iso !== $comment_creation_datetime_iso;

// Ottenere le risposte per il commento corrente
$comment_is_a_reply = isset($replied_comment);
$replies = get_replies($db_connection, $comment[COMMENT_ID]);
$there_are_replies = !empty($replies);
?>
<li class="comment_container" id="<?php echo $comment[COMMENT_ID] ?>">
    
    <!-- Commento corrente -->
    <article>
        <header class="comment_header">
            <address class="comment_author">
                <a class="red_link" href="user.php?id=<?php echo $comment[COMMENT_AUTHOR] ?>"><?php echo $comment[COMMENT_AUTHOR]; ?></a>
            </address>
            <time class="comment_time" datetime="<?php echo $comment_creation_datetime_iso; ?>">
                <?php echo $comment_creation_datetime_readable; ?>
            </time>

            Ultima modifica:
            <time class="comment_time comment_last_edit" datetime="<?php echo $comment_last_edit_datetime_iso; ?>">
                <?php echo $comment_last_edit_datetime_readable ?>
            </time>

            <div class="comment_id"> id: <?php echo $comment[COMMENT_ID] ?> </div>
            <?php if ($comment_is_a_reply): ?>
                <div class="single_reply">
                    Risposta a <a href="#<?php echo $replied_comment ?>">#<?php echo $replied_comment ?> </a>
                </div>
            <?php endif; ?>
        </header>

        <div>
            <?php if ($current_user_is_comment_author): ?>
                <div class="edit_comment_container">
                    <button type="button" class="delete_comment_button little_delete_button"
                        data-comment-id="<?php echo $comment[COMMENT_ID] ?>">
                        <span class="fa-solid fa-trash"></span>Elimina commento
                    </button>
                    <button class="edit_comment_button">
                        <span class="fas fa-edit"></span> Modifica commento
                    </button>
                </div>
                <div class="edit_comment" style="display: none">
                    <data class="comment_id" value="<?php echo $comment[COMMENT_ID]; ?>"></data>
                    <textarea class="hidden_textarea new_comment_text" rows="4"></textarea>
                    <button type="submit" class="hidden_button submit_edited_comment">Aggiorna commento</button>
                    <input type="reset" class="hidden_button" value="Annulla" />
                </div>
            <?php endif; ?>

            <p class="comment_text">
                <?php echo $comment[COMMENT_TEXT]; ?>
            </p>
            <button class="reply_button">
                <span class="fas fa-reply"></span>Rispondi
            </button>
            <?php show_comment_form($comment[COMMENT_ID]) ?>
        </div>
    </article>

    <!-- Sezione delle risposte al commento corrente -->
    <ul class="comment-replies" id="replies_to_<?php echo $comment[COMMENT_ID] ?>">
        <?php if ($there_are_replies): ?>
            <?php foreach ($replies as $reply): ?>

                <?php
                // Passa la risposta come commento
                $replied_comment = $comment[COMMENT_ID];
                $comment = $reply;
                include 'comment_template.php';
                ?>

            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</li>