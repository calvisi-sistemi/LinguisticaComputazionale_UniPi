<?php redirect_if_page_is_accessed_directly(__FILE__); ?>

<?php
$blogs_belonging_to_that_category = null;
if (isset($chosen_category)) {
    $these_are_subcategories = true;
    $categories = get_subcategories($db_connection, $chosen_category);
    $blogs_belonging_to_that_category = get_blogs_by_category($db_connection, $chosen_category);
} else {
    $these_are_subcategories = false;
    $categories = get_main_categories($db_connection);
}
?>

<?php if (empty($blogs_belonging_to_that_category) && empty($categories)): ?>
    <p> Nulla da mostrare. Questa categoria non contiene né blog né sottocategorie. </p>
<?php endif; ?>

<!-- Sezione delle sottocategorie -->
<?php if (!empty($categories)): ?>
    <section id="subcategories" class="category_section">
        <h2>
            <?php
            if ($these_are_subcategories) {
                echo "Sottocategorie";
            } else {
                echo "Categorie";
            }
            ?>
        </h2>
        <nav>
            <?php foreach ($categories as $category): ?>
                <a href="browsebycategory.php?category=<?php echo $category ?>">
                    <div id="category_<?php echo $category ?>" class="tile_selection category_tiles">
                        <?php echo $category; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </nav>
    </section>
<?php endif; ?>

<!-- Sezione dei blog -->
<?php if (!empty($blogs_belonging_to_that_category)): ?>
    <section id="blogs" class="blogs_section">
        <h2>Blogs</h2>
        <nav>
            <?php foreach ($blogs_belonging_to_that_category as $blog): ?>
                <?php
                $blog_id = $blog[BLOG_ID];
                $blog_title = $blog[BLOG_TITLE];
                $blog_owner = $blog[BLOG_OWNER];
                $blog_description = $blog[BLOG_DESCRIPTION];
                ?>

                <div id="blog_<?php echo $blog_id; ?>" class="tile_selection blog_tiles">
                    <a href="blog.php?id=<?php echo $blog_id ?>">
                        <div class="blog_owner"><?php echo $blog_owner; ?></div>
                        <div class="blog_title"><?php echo $blog_title; ?></div>
                        <div class="blog_description"><?php echo $blog_description; ?></div>
                    </a>
                </div>
            <?php endforeach; ?>
        </nav>
    </section>
<?php endif; ?>