<?php

redirect_if_page_is_accessed_directly(__FILE__);

$main_categories = get_main_categories($db_connection);
?>

<script src="ajax/client_side/subcategory_selection.js"></script>

<label for="blog-category" class="bold_text">Categoria *</label>

<select id="blog_category" name="blog_category">

    <option value="" disabled selected>Seleziona una categoria</option>

    <?php foreach ($main_categories as $category): ?>
        <option value="<?php echo $category ?>">
            <?php echo $category ?>
        </option>
    <?php endforeach; ?>

</select>

<span class="filter_error_message" id="blogcategory_error"></span>
<div class="hidden" id="subcategory_block">
    <label for="subcategory_selection" class="bold_text">Sottocategoria</label>
    <select id="subcategory_selection" name="blog_subcategory">
        <option value="" disabled selected>Seleziona una sottocategoria</option>
    </select>
</div>

<span class="filter_error_message" id="blogsubcategory_error"></span>