<?php
require_once '../tools/general.php';
redirect_if_not_accessed_through_post();

try {

    $redirect_destination = "../createblog.php"; // Destinazione di default del redirect

    $admin = get_current_user_username();

    $title = htmlspecialchars($_POST["blog_title"]);

    $description = htmlspecialchars($_POST["blog_description"]);

    $category = htmlspecialchars($_POST["blog_category"]);

    $subcategory = htmlspecialchars($_POST['blog_subcategory']);

    if (empty($title)) {
        throw new Exception("Il titolo del blog è obbligatorio.");
    }

    if (empty($description)) {
        throw new Exception("La descrizione del blog è obbligatoria.");
    }

    if (empty($category)) {
        throw new Exception("Il blog deve appartenere ad una categoria.");
    }
    
    // Se l'utente ha scelto una sottocategoria, quella ha la priorità sulla categoria generale 
    if(!empty($subcategory)){
        $category = $subcategory;
    }

    $blog_id = create_blog($db_connection, $title, $category, $description, $admin);

    $user_chose_a_blog_logo = is_uploaded_file($_FILES['blog_logo']['tmp_name']);

    if ($user_chose_a_blog_logo) {

        $blog_logo_file = $_FILES['blog_logo'];
        unset($_FILES['blog_logo']);
        check_uploaded_file_errors($blog_logo_file);
        set_blog_logo($db_connection, $blog_id, $blog_logo_file);

    }

    $_SESSION['success'] = 'Blog creato con successo!';

    $redirect_destination = "../blog.php?id=$blog_id"; // Solo se non sono mai state sollevate eccezioni imposto una $redirec_destination diversa dal default
} catch (Exception $e) {

    set_error('Errore nella creazione del blog: ' . $e->getMessage());

} finally {
    redirect($redirect_destination);
}