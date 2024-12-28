<?php
require_once 'tools/general.php';

if (!isset($_GET['id'])) {
    redirect();
}

$post_id = $_GET['id'];
$post_exists = does_post_exist($db_connection, $post_id);

if ($post_exists) {
    set_current_post(get_post_info($db_connection, $post_id, get_current_user_username()));
    set_current_blog(get_blog_info($db_connection, get_current_post_blog()));
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php
        if ($post_exists) {
            echo get_current_post_title();
        } else {
            echo 'Post non trovato';
        }
        ?>
    </title>
    <link rel="stylesheet" href="styles.css" />
    <link rel="icon" type="image/x-icon" href="favicon.ico">
</head>

<body>
    <?php
    include 'header.php';
    ?>
    <main>
        <?php
        if ($post_exists) {
            include_once 'contents/post_content.php';
        } else {
            $non_existant_content = 'post';
            include_once 'contents/content_does_not_exist.php';
        }
        ?>
        <?php
        include 'footer.php';
        include 'outcome_message.php'
    ?>
    </main>
</body>

</html>