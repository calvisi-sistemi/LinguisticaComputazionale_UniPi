<?php
redirect_if_page_is_accessed_directly(__FILE__);

$avatar_path = IMAGE_DIRECTORY . $profile_owner[USER_AVATAR];
?>

<div class="user_introduction">
    <img class="middle_size_avatar" src="<?php echo $avatar_path ?>" />
    <h3><?php echo $profile_owner[USER_COMPLETE_NAME] ?></h3>
</div>

<div class="user_bio">
    <h4><?php echo $profile_owner_username ?></h4>

    <?php if ($user_is_premium): ?>
        <span class="premium_user_tag">
            <span class="fa-solid fa-star"></span> Utente premium
        </span>
        <?php if(!is_current_user_premium()): ?>
            <p class="how_to_be_premium_message">Raggiungi almeno 5 iscritti tra tutti i tuoi blog per diventare premium e scoprire chi ti sta seguendo. </p>
        <?php endif;?>
    <?php endif; ?>

    <?php if ($profile_owner_has_a_bio): ?>
        <p class="user_bio_text"><?php echo $profile_owner[USER_BIO] ?></p>
    <?php endif; ?>

    <p>
        Totale iscrizioni ai suoi blog: <?php echo $profile_owner[USER_TOTAL_SUBSCRIBERS] ?>
    </p>
</div>

<div class="user_blogs_list">

    <?php if ($profile_owner_has_created_some_blogs): ?>
        <h4 class="user_blogs_list_h4">I blog di <?php echo $profile_owner[USER_COMPLETE_NAME] ?> </h4>
        <div class="blog_list" id="administered_blogs">

            <?php foreach ($blogs_created_by_user as $blog): ?>
                <?php
                $section = OWN_BLOGS_SECTION;
                include 'blog_list_in_user_profile.php';
                ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($user_is_coauthor_of_some_blogs): ?>
        <h4><?php echo $profile_owner[USER_COMPLETE_NAME] ?> è coautore di </h4>
        <div class="blog_list" id="coauthor_blogs">

            <?php foreach ($blogs_in_which_user_is_coauthor as $blog): ?>
                <?php
                $section = AS_COAUTHOR_SECTION;
                include 'blog_list_in_user_profile.php';
                ?>
            <?php endforeach; ?>

        </div>
    <?php endif; ?>

    <?php if ($profile_owner_is_subscribed_to_any_blog): ?>
        <h4><?php echo $profile_owner[USER_USERNAME] ?> sta seguendo </h4>
        <div class="blog_list" id="subscriptions">
            <?php foreach ($profile_owner_subscriptions as $blog): ?>
                <?php
                $section = SUBSCRIPTION_SECTION;
                include 'blog_list_in_user_profile.php';
                ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>