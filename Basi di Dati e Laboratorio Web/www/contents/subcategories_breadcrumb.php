<?php redirect_if_page_is_accessed_directly(__FILE__); ?>

<?php if (isset($chosen_category)): ?>
   <?php     
    if(is_this_an_already_visited_category($chosen_category)){
        go_to_higher_category($chosen_category);
    }else{
        one_step_deeper_in_subcategories_breadcrumb($chosen_category);
    }
   ?>

    <a href="browsebycategory.php" class="category_breadcrumb">
        Tutte le categorie 
    </a>
    <i class="fa-solid fa-chevron-right"></i>
    <?php if (subcategories_breadcrumb_is_started()): ?>
        <?php foreach (get_full_subcategories_breadcrumb() as $subcategory): ?>
            <a class="category_breadcrumb" href="browsebycategory.php?category=<?php echo $subcategory ?>">
                <?php echo $subcategory ?> 
            </a>
            <i class="fa-solid fa-chevron-right"></i>
        <?php endforeach ?>
    <?php endif; ?>
<?php endif; ?>