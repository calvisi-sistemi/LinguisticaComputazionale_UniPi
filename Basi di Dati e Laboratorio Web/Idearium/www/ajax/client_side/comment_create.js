$(document).ready(main_function);

/**
 * Funzione principale che gestisce l'inizializzazione degli eventi.
 */
function main_function() {
    // Assegna listener generico per la creazione di commenti e risposte
    $(document).on('click', NEW_COMMENT_BUTTON_CLASS, create_new_comment);
}

/**
 * Gestisce la creazione di un nuovo commento o risposta.
 * 
 * @param {Event} event - L'evento del click sul pulsante di invio del commento.
 */
function create_new_comment(event) {
    event.preventDefault();

    const current_button = $(event.currentTarget);

    const textarea = current_button.closest('.textarea_group').find(NEW_COMMENT_TEXTAREA_CLASS);
    const comment_text = textarea.val();

    if (!comment_text) {
        alert('Il commento non può essere vuoto.');
        return;
    }

    let new_comment = {
        'new_comment_text': comment_text,
    };

    // Determina se il commento è una risposta

    const main_comment_id = current_button.data('main-comment-id');

    if (main_comment_id !== "") {

        let main_comment_id_obj = {
            'main_comment_id': current_button.data('main-comment-id')
        };

        Object.assign(new_comment, main_comment_id_obj);
    }

    $.ajax({
        url: CREATE_COMMENT_SERVERSIDE_SCRIPT_URL,
        type: 'POST',
        data: new_comment,
        dataType: 'json',
        success: create_html_comment,
        error: handle_error_in_ajax_request
    });
}

/**
 * Inserimento del commento HTML nella lista corretta
 * @param {Object} response risposta del server
 */
function create_html_comment(response) {
    if (!response.success) {
        alert('Errore: ' + response.error);
        return;
    }

    const target_list = response.replied_comment_id !== null ?
        '#replies_to_' + response.replied_comment_id :
        COMMENTS_GENERAL_LIST;

    $(target_list).prepend(response.new_comment_html);
    empty_input_field($(NEW_COMMENT_TEXTAREA_CLASS));
    update_comments_count(+1);
    hide_comment_forms(); // nel dubbio, chiudo qualunque form apparte quello principale. 
}