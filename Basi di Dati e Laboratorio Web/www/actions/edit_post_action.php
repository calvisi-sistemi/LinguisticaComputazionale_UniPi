<?php
require_once '../tools/general.php';
redirect_if_not_accessed_through_post();

if(!can_current_user_edit_current_post()){
    redirect();
}

try {
    $redirect_destination = '../index.php';

    if (isset($_POST['delete_post'])) { // Se è stato scelto di eliminare il post
        $blog_id = get_post_info($db_connection, get_current_post_id())[POST_BLOG];

        delete_post($db_connection, get_current_post_id());

        $redirect_destination = '../blog.php?id=' . get_current_post_blog();

    } else { // Se è stato scelto di modificarlo

        if (isset($_POST['update_post'])) { // Se è stato scelto di modificare titolo, testo o immagine del post
            $new_title = htmlspecialchars($_POST['post_title']);
            $new_text = htmlspecialchars($_POST['post_text']);

            if (!empty($new_title) && $new_title !== get_current_post_title()) {
                update_post_title($db_connection, get_current_post_id(), $new_title);
            }

            if (!empty($new_text) && $new_text !== get_current_post_text()) {
                update_post_text($db_connection, get_current_post_id(), $new_text);
            }

            if (is_uploaded_file($_FILES['post_image']['tmp_name'])) {
                $post_image_file = $_FILES['post_image'];
                update_post_image($db_connection, get_current_post_id(), $post_image_file);
                unset($_FILES['post_image']);
            }

        } else if (isset($_POST['delete_post_image'])) { // Se è stato scelto di eliminare l'immagine
            delete_post_image($db_connection, get_current_post_id());
        }

        $redirect_destination = '../post.php?id=' . get_current_post_id();
    }

} catch (Exception $e) {

    $redirect_destination = '../editpost.php';
    $error = 'Errore nella modifica del post: ' . $e->getMessage();
    
    set_error($error);

} finally {
    redirect($redirect_destination);
}