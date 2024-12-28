<?php
require_once 'tools/general.php';
redirect_if_page_is_accessed_directly(__FILE__);
$post_title = $post_info[POST_TITLE];
$post_author = $post_info[POST_AUTHOR];
$post_image = $post_info[POST_IMAGE];
$post_abstract = get_post_preview_abstract($post_info[POST_TEXT]);

$post_has_image = !is_null($post_image);
if($post_has_image){
    $post_image_path = IMAGE_DIRECTORY . $post_image;    
    $post_has_image = file_exists($post_image_path);
}

?>

<div class="blogs_list_in_user_page">
    <h3 class="post_preview_title">
        <a href="post.php?id=<?php echo $post_id; ?>">
            <?php echo $post_title; ?>
        </a>
    </h3>

    <address class="clear_address">
        Pubblicato da
        <a class="red_link" href="user.php?id=<?php echo $post_author ?>"> <?php echo $post_author ?> </a>
        su
        <a class="red_link" href="blog.php?id=<?php echo $blog_id ?>"> <?php echo $blog_title ?> </a>
    </address>
    
    <p class="post_preview_content">
        <?php echo $post_abstract; ?>
    </p>
    <?php if($post_has_image): ?>
        <img width="50%" src="<?php echo $post_image_path; ?>" alt="Immagine del post <?php echo $post_title ?>"/>
    <?php endif;?>
    
    <a href="post.php?id=<?php echo $post_id; ?>">Leggi</a>
</div>