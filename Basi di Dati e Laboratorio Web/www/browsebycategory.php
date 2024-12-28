<?php
require_once 'tools/general.php';
redirect_if_not_logged_in();

if (isset($_GET['category'])) {
    $chosen_category = $_GET['category'];
}

if(!isset($_GET['category']) && subcategories_breadcrumb_is_started()){
    destroy_subcategories_breadcrumb();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Naviga per categoria - Idearium</title>
    <link rel="stylesheet" href="styles.css" />
</head>

<body>
    <?php include_once 'header.php'; ?>
    <h1>Naviga tra i blog per categoria</h1>
    <nav class="breadcrumb">
        <?php include_once 'contents/subcategories_breadcrumb.php'; ?>
    </nav>

    <?php include_once 'contents/blogs_by_category_selection.php'; ?>

    <?php include_once 'footer.php'; ?>
</body>

</html>