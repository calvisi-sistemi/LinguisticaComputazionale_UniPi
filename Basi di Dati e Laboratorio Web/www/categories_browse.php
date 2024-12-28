<?php

require_once 'tools/general.php';

$categories = get_main_categories($db_connection);

?>

<!DOCTYPE html>

<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width-device-width, initial scale=1.0">
    <title>Ricerca per categoria</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="wrapper">
        <?php include_once 'header.php' ?>
        <main>
            <div class="container">        
                <h3>Ricerca per categoria</h3>
                <?php foreach($categories as $category): ?>
                    <a class="category_search">
                        <?php echo $category ?>
                    </a>
                    <?php endforeach ?>
            </div>
        </main>
    </div>
    <?php 
    include_once 'footer.php';   
    ?>
</body>
</html>