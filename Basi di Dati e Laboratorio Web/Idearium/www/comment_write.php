<?php
redirect_if_page_is_accessed_directly(__FILE__);
?>

<div id="comment_form form_group">
    <div class="textarea_group">
        <img class="comment_avatar" src="<?php echo $avatar_path; ?>"
            alt="Avatar di <?php echo get_current_user_username()?>" />
        <textarea class="new_comment_textarea" rows="1" placeholder="Scrivi il tuo commento" required></textarea>
        <div class="hidden">
            <input type="reset" class="little_button" value="Annulla" />
            <button type="button" class="little_button" id="new_comment_button">Commenta</button>
        </div>
    </div>
    <span class="filter_error_messages" id="comment_error"></span>
</div>