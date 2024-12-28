<?php
require_once '../tools/general.php';
redirect_if_not_accessed_through_post();

if (!can_current_user_edit_current_blog()) {
    redirect();
}

try {
    $redirect_destination = '../blog.php?id=' . get_current_blog_id();

    if (isset($_POST['delete_blog_button'])) {
        $redirect_destination = '../user.php?id=' . get_current_blog_owner();
        delete_blog($db_connection, get_current_blog_id());

    } else if (isset($_POST['delete_blog_logo_button'])) {
        delete_blog_logo($db_connection, get_current_blog_id());

    } else if (isset($_POST['update_blog_button'])) {
        $new_blog_title = clean_text($_POST['new_blog_title']);

        $new_blog_description = clean_text($_POST['new_blog_description']);

        $new_category = htmlspecialchars($_POST['blog_category']);
        $new_subcategory = htmlspecialchars($_POST['blog_subcategory']);

        $user_chose_new_logo = is_uploaded_file($_FILES['blog_logo']['tmp_name']);

        if (!empty($new_blog_description) && get_current_blog_description() !== $new_blog_description) {
            update_blog_description($db_connection, get_current_blog_id(), $new_blog_description);
        }

        if (!empty($new_blog_title) && get_current_blog_title() !== $new_blog_title) {
            update_blog_title($db_connection, get_current_blog_id(), $new_blog_title);
        }

        if ($user_chose_new_logo) {
            $new_logo_file = $_FILES['blog_logo'];
            unset($_FILES['blog_logo']);
            check_uploaded_file_errors($new_logo_file);
            set_blog_logo($db_connection, get_current_blog_id(), $new_logo_file);
        }

        if (!empty($new_category) && get_current_blog_category() !== $new_category) {
            // Se è stata scelta una nuova sottocategoria, questa ha la precedenza sulla categoria.
            $category_to_set = !empty($new_subcategory) ? $new_subcategory : $new_category;
            update_blog_category($db_connection, get_current_blog_id(), $category_to_set);
        }
    }

    if (isset($_POST['set_new_coauthors']) && isset($_POST['choosen_coauthors'])) { // Se si vogliono scegliere dei nuovi coautori
        $choosen_coauthors = $_POST['choosen_coauthors'];
        $suitable_coauthors = get_suitable_coauthors($db_connection, $choosen_coauthors, get_current_blog_id(), TO_ADD);

        add_coauthors($db_connection, get_current_blog_id(), $suitable_coauthors);
    }

    if (isset($_POST['remove_coauthors'])) { // Se si vogliono rimuovere dei coautori esistenti
        if (isset($_POST['coauthors_to_remove'])) {
            $choosen_coauthors = $_POST['coauthors_to_remove'];
            $removable_users = get_suitable_coauthors($db_connection, $choosen_coauthors, get_current_blog_id(), TO_REMOVE);

            delete_coauthors($db_connection, get_current_blog_id(), $removable_users);
        }
    }

} catch (Exception $e) {

    $redirect_destination = '../editblog.php' . get_current_blog_id();

    if (isset($_POST['delete_blog_button'])) {
        $message = 'Errore nell\'eliminazione del blog: ';
    }

    if (isset($_POST['update_blog_button'])) {
        $message = 'Errore nell\'aggiornamento del blog: ';
    }

    if (isset($_POST['delete_logo_button'])) {
        $message = 'Errore nell\'eliminazione del logo del blog: ';
    }

    if (isset($_POST['set_new_coauthors'])) {
        $message = 'Errore nell\'impostazione di nuovi coautori: ';
    }

    if (isset($_POST['remove_coauthors'])) {
        $message = 'Errore nella rimozione dei coautori: ';
    }

    $error = $message . $e->getMessage();

    set_error($error);

} finally {
    redirect($redirect_destination);
}