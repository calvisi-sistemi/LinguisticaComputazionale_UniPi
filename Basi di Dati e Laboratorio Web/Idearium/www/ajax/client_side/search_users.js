const QUERY_FIELD = "#search_a_new_coauthor"
const OUTPUT_FIELD = "#new_coauthors"

$(document).ready(start_search);

function start_search() {
    $(QUERY_FIELD).on('keyup', search_user);
}

function search_user() {

    let search_query = $(QUERY_FIELD).val();

    let query_is_not_empty = search_query.trim() !== "";

    if (query_is_not_empty) {

        let ajax_request_for_users = {
            url: SEARCH_USERS_SERVERSIDE_SCRIPT_URL,
            type: 'POST',
            data: { users_query: search_query },
            dataType: 'json',
            success: show_results,
            error: handle_error_in_ajax_request
        }

        $.ajax(ajax_request_for_users);

    } else {
        $(OUTPUT_FIELD).empty();
    }
}

function show_results(response) {
    let output = '';

    if (response.error !== null) {
        output = response.error;
    } else {
        let at_least_one_user_was_found = (response.found_users !== null);

        if (at_least_one_user_was_found) {
            response.found_users.forEach(user => {

                nome_utente = user.nome_utente
                nome_completo = user.nome_visualizzato
                email = user.email

                output += `
                <div>
                    <label for="${nome_utente}">
                    ${nome_completo} (<span>${nome_utente}</span> <span>${email}</span>)
                    </label>
                    <input type="checkbox" id="${nome_utente}" name="choosen_coauthors[]" value="${nome_utente}"/>
                </div>
                `
            });
        } else {
            output = `<p>Nessun utente trovato</p>`;
        }

    }

    $(OUTPUT_FIELD).html(
        output
    );
}