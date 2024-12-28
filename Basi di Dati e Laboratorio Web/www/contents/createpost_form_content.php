<?php

$blogs_created_by_user = get_blogs_list_by_creator($db_connection, $_SESSION['current_user'][USER_USERNAME]);
$blogs_in_which_user_is_coauthor = get_blogs_list_by_coauthor($db_connection, $_SESSION['current_user'][USER_USERNAME]);
$user_have_not_created_any_blog = count($blogs_created_by_user) == 0;

if ($user_have_not_created_any_blog): ?>
    <p>Per pubblicare un post devi prima <a href="createblog.php">creare un blog</a>.</p>
<?php else: ?>
    <form action="actions/createpost_action.php" enctype="multipart/form-data" method="POST">

        <select name="blog_id" id="blog_id" required>

            <option value="">-- Seleziona un Blog --</option>

            <optgroup id="blogs_created_by_user" label="Blog che hai creato">
                <?php foreach ($blogs_created_by_user as $blog): ?>
                    <option value="<?php echo htmlspecialchars($blog[BLOG_ID]); ?>">
                        <?php echo htmlspecialchars($blog[BLOG_TITLE]); ?>
                    </option>
                <?php endforeach; ?>
            </optgroup>

            <optgroup id="blogs_in_which_user_is_coauthor" label="Blog di cui sei co-autore">
                <?php foreach ($blogs_in_which_user_is_coauthor as $blog): ?>
                    <option value="<?php echo htmlspecialchars($blog[BLOG_ID]); ?>">
                        <?php echo htmlspecialchars($blog[BLOG_TITLE]); ?>
                    </option>
                <?php endforeach; ?>
            </optgroup>

        </select>

        <div class="form_group">
            <label for="article_title">Titolo:</label>
            <input type="text" id="article_title" name="post_title" required>
        </div>

        <div class="form_group">
            <label for="article-content">Contenuto:</label>
            <textarea id="article_content" name="post_content" rows="15" required></textarea>
        </div>
        
        <div class="form_group">
            <label for="article-logo" class="bold_text">Immagine dell'articolo</label>
            <input type="file" id="article_image" name="post_image" accept="image/*">
        </div>
        <button type="submit" class="classic_button">Crea Articolo</button>
    </form>
<?php endif; ?>