<?php
require_once '../tools/general.php';

redirect_if_not_accessed_through_post();


try {
    $redirect_destination = '../createpost.php'; // Default destinazione di redirect

    $post_author = htmlspecialchars($_SESSION['current_user'][USER_USERNAME]);


    $blog_id = $_POST['blog_id'];
    $blog_id_is_not_valid = !ctype_digit($_POST['blog_id']) || $blog_id < 0;

    if ($blog_id_is_not_valid) {
        throw new Exception(message: 'ID del blog non valido');
    }

    $blogs_owned_by_post_creator = get_blogs_list_by_creator($db_connection, $post_author); 
    $id_of_the_blogs_owned_by_post_creator = array_column($blogs_owned_by_post_creator, BLOG_ID); // Estraggo gli ID dalla lista dei blog

    $blogs_where_user_is_coauthor = get_blogs_list_by_coauthor($db_connection, $post_author); // Estraggo gli ID dalla lista dei blog
    $id_of_the_blogs_where_user_is_coauthor = array_column($blogs_where_user_is_coauthor, BLOG_ID);

    $suitable_blogs = array_merge($id_of_the_blogs_owned_by_post_creator, $id_of_the_blogs_where_user_is_coauthor);

    if (!in_array($blog_id, $suitable_blogs)) {
        throw new Exception('Non puoi pubblicare sul blog scelto');
    }

    $post_title = htmlspecialchars($_POST['post_title']);
    $post_content = htmlspecialchars($_POST['post_content']);
    $post_image = null; // Assumo che non sia presente alcuna immagine

    if (empty($post_title)) {
        throw new Exception('Il titolo del post non può essere vuoto');
    }

    if (empty($post_content)) {
        throw new Exception('Il contenuto del post non può essere vuoto');
    }

    $there_is_an_image = is_uploaded_file($_FILES['post_image']['tmp_name']);
    if ($there_is_an_image) {
        $post_image = $_FILES['post_image'];
        check_uploaded_file_errors($post_image);
    }

    add_post($db_connection, $blog_id, $post_author, $post_title, $post_content, $post_image);

    $redirect_destination = "../blog.php?id=$blog_id";

} catch (Exception $e) {

    set_error('Errore nella creazione del post: ' . $e->getMessage());

} finally {
    redirect($redirect_destination);
}

