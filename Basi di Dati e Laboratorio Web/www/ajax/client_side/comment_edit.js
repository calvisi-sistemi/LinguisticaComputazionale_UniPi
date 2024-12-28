/*
    DA FARE: implementare la chiusura del form una volta che il commento è stato pubblicato.
*/
$(document).ready(main_function);

function main_function() {
    $(COMMENTS_GENERAL_LIST).on('click', SUBMIT_EDITED_COMMENT_BUTTON_CLASS, edit_comment);
}

function edit_comment() {
    const EDITED_COMMENT_TEXTAREA = $(SUBMIT_EDITED_COMMENT_BUTTON_CLASS).closest('.comment_container').find('.new_comment_text');
    const comment_id = get_comment_id_from_button(this);

    let old_comment_text = $(EDIT_COMMENT_BUTTON_CLASS).closest('.comment_container').find('.comment_text').text();
    let new_comment_text = EDITED_COMMENT_TEXTAREA.val();

    if (old_comment_text === new_comment_text) return;

    const comment_to_edit = {
        comment_id: comment_id,
        new_text: new_comment_text,
        old_text: old_comment_text
    };

    show_edited_comment(new_comment_text);

    const ajax_request = {
        url: EDIT_COMMENT_SERVERSIDE_SCRIPT_URL,
        type: 'POST',
        data: comment_to_edit,
        dataType: 'json',
        success: handle_success_editing,
        error: handle_error_in_ajax_request
    }

    $.ajax(ajax_request);
}

function handle_success_editing(response) {
    if (!response.success) {
        alert('Impossibile modificare il commento : ' + response.error);
        return;
    }
}

/**
 * Funzione per mostrare il nuovo commento editato con i dati immessi dall'utente, ancora prima di invarlo al server.
 * @param {string} new_comment_text Testo editato.
 */
function show_edited_comment(new_comment_text) {
    $('.edit_comment').hide();
    $('.comment_text').show();
    current_timestamp = Date.now();

    $(COMMENT_TEXT).text(new_comment_text);
    $(COMMENT_LAST_EDIT).text(get_readable_datetime(current_timestamp));
    $(COMMENT_LAST_EDIT).attr('datetime', get_iso_datetime(current_timestamp));
}