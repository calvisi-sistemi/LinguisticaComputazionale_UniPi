<?php
require_once 'tools/general.php';
redirect_if_not_logged_in();
if (!there_is_a_current_blog() || get_current_user_username() !== get_current_blog_owner()) {
    redirect();
}

$subscribers = get_blog_subscribers($db_connection, get_current_blog_id());
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Iscritti di <?php echo get_current_blog_title(); ?></title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <?php include_once 'header.php'; ?>
    <h1> Iscritti di <?php echo get_current_blog_title() ?></h1>
    <div>

    </div>
    <?php if (!is_array($subscribers)): ?>
        Ancora nessun iscritto
    <?php else: ?>
        <ul class="subscriber_list">
            <?php foreach ($subscribers as $subscriber): ?>
                <li>
                    <a class="subscriber" href="user.php?id=<?php echo $subscriber ?> ">
                        <?php echo $subscriber ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <?php include 'footer.php'; ?>
</body>

</html>