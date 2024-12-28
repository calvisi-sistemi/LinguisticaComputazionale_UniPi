<?php
require_once 'tools/general.php';
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creazione Articolo - Idearium</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
</head>

<body>
    <div class="wrapper">
        <?php
        include "header.php";
        ?>
        <main>
            <div class="container">
                <section class="article_creation">
                    <h2>Scrivi un articolo!</h2>
                    <h3>Scegli il blog in cui pubblicare il post!</h3>
                    <?php include 'contents/createpost_form_content.php'; ?>

                </section>
            </div>
        </main>
        <?php
        include 'footer.php';
        include 'outcome_message.php';
        ?>
    </div>
</body>

</html>