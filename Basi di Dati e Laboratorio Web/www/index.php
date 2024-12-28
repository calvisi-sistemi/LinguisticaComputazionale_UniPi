<?php
    require_once 'tools/general.php';
    reset_current_blog();
    reset_current_post();
?>
    
<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Idearium</title>
        <link rel="stylesheet" href="styles.css">
        <link rel="icon" type="image/x-icon" href="favicon.ico">
    </head>
    <body class="wrapper">
        <?php include "header.php"; ?>
            <main>
                <div class="container">
                    <?php include "contents/index_main_content.php"; ?>
                </div>
            </main>
        <?php 
            include_once 'footer.php';
            include_once 'outcome_message.php';
        ?>
    </body>
</html>

