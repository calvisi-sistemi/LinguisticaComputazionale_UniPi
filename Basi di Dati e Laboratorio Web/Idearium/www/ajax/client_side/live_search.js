const SEARCH_FIELD = "#search_bar";
const SEARCH_OUTPUT = "#search_results";

$(document).ready(live_search_start);

function live_search_start() {
    $(SEARCH_FIELD).on('keyup', live_search);
}

function live_search() {

    let live_search_query = $(SEARCH_FIELD).val();

    let live_search_is_not_empty = live_search_query.trim() !== "";

    if (live_search_is_not_empty) {

        let ajax_request = {
            url: LIVESEARCH_SERVERSIDE_SCRIPT_URL,
            type: 'POST',
            data: { users_live_search: live_search_query },
            dataType: 'json',
            success: show_live_search_results,
            error: handle_error_in_ajax_request
        }
        $.ajax(ajax_request);
    } else {
        $(SEARCH_OUTPUT).empty();
    }
}

function show_live_search_results(response) {
    let output = '';

    if (response.error !== null) {
        output = response.error;
        return;
    }

    let nothing_was_found = response.found_results === null;

    if (nothing_was_found) {
        output = `<p>Nessun risultato trovato</p>`
        return;
    }

    blogs = response.found_results.blogs;
    posts = response.found_results.posts;
    users = response.found_results.users;

    output += get_blogs_results(blogs);
    output += get_posts_results(posts);
    output += get_users_results(users);

    $(SEARCH_OUTPUT).html(output);
}

function get_users_results(users) {
    let users_result = '';

    if (users === null) {
        return null;
    }

    users.forEach(user => {
        let username = user.nome_utente;
        let avatar_path = IMAGE_DIRECTORY + user.avatar;

        users_result += `<p><img src="${avatar_path}" alt="avatar" class="little_avatar"><a class="livesearch_output" href="user.php?id=${username}">${username}(Utente)</a></p>`;
    });

    return users_result;
}

function get_blogs_results(blogs) {
    let blogs_result = '';

    if (blogs === null) {
        return null;
    }

    blogs.forEach(blog => {
        let blog_id = blog.id_blog;
        let blog_title = blog.titolo_visualizzato;
        blogs_result += `<p><a class="livesearch_output" href="blog.php?id=${blog_id}">${blog_title}(Blog)</a></p>`;
    });

    return blogs_result;
}

function get_posts_results(posts) {
    let posts_results = '';

    if (posts === null) {
        return null;
    }

    posts.forEach(post => {
        let post_id = post.id_post;
        let post_title = post.titolo_post;
        posts_results += `<p><a class="livesearch_output" href="post.php?id=${post_id}">${post_title}(Post)</a></p>`;
    });

    return posts_results;
}