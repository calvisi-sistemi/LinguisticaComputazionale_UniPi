<?php
require_once 'tools/general.php';
redirect_if_page_is_accessed_directly(__FILE__);

$blog_id = $blog[BLOG_ID];
$blog_pic = $blog[BLOG_PIC];
$BLOG_OWNER = $blog[BLOG_OWNER];
$blog_description = $blog[BLOG_DESCRIPTION];
?>

<div id="blog_<?php echo $blog_id ?>" class="tile_selection blog_tiles">

    <?php if ($section === AS_COAUTHOR_SECTION || $section === SUBSCRIPTION_SECTION): ?>
        <?php $admin_profile = "user.php?id=$BLOG_OWNER" ?>
        <p>
            Creato da <a class="BLOG_OWNER_profile_link" href="<?php echo $admin_profile ?>"><?php echo $BLOG_OWNER; ?></a>
        </p>
    <?php endif; ?>

    <a href="blog.php?id=<?php echo $blog_id ?>">

        <h3 class="blog_title">
            <?php echo $blog[BLOG_TITLE] ?>
        </h3>

        <?php if (!is_null($blog_pic)): ?>
            <div class="blog_logo"> 
                <img class="blog_logo_image" src="<?php echo get_image_path($blog_pic) ?>" alt="Logo del blog"> 
            </div>
        <?php endif; ?>

        <p class="blog_description">
            <?php echo $blog_description; ?>
        </p>
    </a>
</div>