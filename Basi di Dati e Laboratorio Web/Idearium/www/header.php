<?php
require_once 'tools/general.php';

login_user_if_is_saved($db_connection);

redirect_if_not_logged_in();

?>

<header>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="js_scripts/comments_general_functions.js" defer></script>
    <script src="js_scripts/comments_constants.js" defer></script>
    <script src="ajax/client_side/ajax_constants.js" defer></script>
    <script src="ajax/client_side/ajax_general_functions.js" defer></script>
    <script src="ajax/client_side/live_search.js" defer></script>
    <script src="ajax/client_side/comment_delete.js" defer></script>
    <script src="ajax/client_side/comment_create.js" defer></script>
    <script src="ajax/client_side/comment_edit.js" defer></script>
    <div class="container">
        <a href="index.php">
            <h1 class="header_title">Idearium</h1>
        </a>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <?php
                if (is_user_logged_in()):
                    $avatar_path = get_image_path(get_current_user_avatar());
                    if (!file_exists($avatar_path)) {
                        $avatar_path = get_image_path(AVATAR_DEFAULT_NAME);
                        $_SESSION['current_user'][USER_AVATAR] = AVATAR_DEFAULT_NAME;
                    }
                    ?>
                    <li><a href="browsebycategory.php">Naviga per categoria</a></li>
                    <li><a href="createblog.php">Crea Blog</a></li>
                    <li><a href="createpost.php">Scrivi post</a></li>
                    <li id="avatar_container">
                        <div class="dropdown">
                            <img src="<?php echo $avatar_path; ?>" alt="avatar" class="little_avatar" id="avatar">
                            <div id="dropdownmenu" class="dropdown_content">
                                <a class="dropdown_content_link" href="settings.php">Impostazioni</a>
                                <a class="dropdown_content_link"
                                    href="user.php?id=<?php echo $_SESSION['current_user'][USER_USERNAME] ?>">Il mio
                                    profilo</a>
                                <a class="dropdown_logout" href="logout.php">Logout</a>
                            </div>
                        </div>
                    </li>
                </ul>
            <?php else: ?>
                <li><a href="signup.php">Registrati</a></li>
                <li><a href="login.php">Login</a></li>
                <?php
                endif;
                ?>
            </ul>

            <div class="search_container">
                <input type="text" placeholder="Cerca..." id="search_bar" class="search_bar">
                <div id="search_results" class="search_results">
                </div>
            </div>
        </nav>
    </div>
</header>