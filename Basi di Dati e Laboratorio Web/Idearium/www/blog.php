<?php
require_once 'tools/general.php';

if (!isset($_GET['id'])) {
    redirect();
}

$blog_id = $_GET['id'];

$blog_exists = does_blog_exist($db_connection, $blog_id);

if ($blog_exists) {
    $_SESSION['current_blog'] = get_blog_info($db_connection, $blog_id);
    if (has_current_blog_logo()) {
        $blog_logo = get_current_blog_logo();
        $blog_logo_path = get_image_path($blog_logo);
    }
}

?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php
        if ($blog_exists) {
            echo get_current_blog_title();
        } else {
            echo "Blog non trovato";
        }
        ?>
    </title>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
</head>

<body>
    <?php include_once 'header.php'; ?>
    <main class="wrapper">
        <div class="container">
            <?php
            if ($blog_exists) {
                include_once 'contents/blog_content.php';
            } else {
                $non_existant_content = 'blog';
                include_once 'contents/content_does_not_exist.php';
            }
            ?>
        </div>
        <?php
        include_once 'footer.php';
        include_once 'outcome_message.php';
        ?>
    </main>
</body>

</html>