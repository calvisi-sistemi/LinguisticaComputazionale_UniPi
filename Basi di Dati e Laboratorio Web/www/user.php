<?php
require_once 'tools/general.php';

redirect_if_not_logged_in();

$profile_owner_username = $_GET['id'];

$profile_owner_username_exists = do_user_exist($db_connection, $profile_owner_username);

if ($profile_owner_username_exists) {

    $profile_owner = get_user_info($db_connection, $profile_owner_username);
    $user_is_premium = $profile_owner[USER_PREMIUM_STATUS];

    $blogs_created_by_user = get_blogs_list_by_creator($db_connection, $profile_owner_username);
    $blogs_in_which_user_is_coauthor = get_blogs_list_by_coauthor($db_connection, $profile_owner_username);
    $profile_owner_subscriptions = get_user_subscriptions($db_connection, $profile_owner_username);

    $profile_owner_is_subscribed_to_any_blog = !is_null($profile_owner_subscriptions);
    $profile_owner_has_a_bio = !empty($profile_owner[USER_BIO]);
    $profile_owner_has_created_some_blogs = !empty($blogs_created_by_user);
    $pofile_owner_total_subscribers = $profile_owner[USER_TOTAL_SUBSCRIBERS];
    $user_is_coauthor_of_some_blogs = !empty($blogs_in_which_user_is_coauthor);
}

?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php
        if ($profile_owner_username_exists) {
            echo "{$profile_owner_username} - Pagina utente";
        } else {
            'Utente non trovato';
        }
        ?>
    </title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="stylesheet" href="styles.css">
</head>

<body class="wrapper">
    <?php
    include 'header.php';
    ?>
    <main class="main content">
        <?php
        if ($profile_owner_username_exists) {
            include_once "contents/user_content.php";
        } else {
            $non_existant_content = "Utente";
            include_once "contents/content_does_not_exist.php";
        }
        ?>
    </main>

    <?php
    include 'footer.php';
    include 'outcome_message.php'
        ?>

</body>

</html>