$(document).ready(main_function);

function main_function() {
    hide_comment_forms();

    // Aggiungo dinamicamente gli eventListener agli elementi riconosciuti dai selettori quando vengono creati.
    $(document).on('click', EDIT_COMMENT_BUTTON_CLASS, show_edit_comment_form);
    $(document).on('click', REPLY_BUTTON_CLASS, show_reply_form);
}

/**
 * Animazione della modifica al commento.
 * Compie tre operazioni
 *  1. Nasconde il testo del commento
 *  2. Visualizza il form (textarea e pulsanti) per la modifica
 *  3. Inserisce il testo vecchio del commento nella textarea
 * @param {Event} event 
 */
function show_edit_comment_form(event) {
    const button = $(event.currentTarget);
    const comment_container = button.closest('.comment_container');
    const comment_text = comment_container.find('.comment_text');
    const actual_text = comment_text.text();
    const edit_form = comment_container.find('.edit_comment');
    
    if (comment_text.length === 0 || edit_form.length === 0) return;

    const textarea = edit_form.find('textarea');
    textarea.val(actual_text);

    $(comment_text).hide();
    $(edit_form).show();

    const cancel_button = edit_form.find('input[type="reset"]');
    if (!cancel_button.length) return;

    cancel_button.on('click', hide_edit_comment_form);
}

/**
 * Funzione che nasconde i due form legati ai commenti:
 *  - Quello per la modifica del commento
 *  - Quello per la risposta al commento
 */
function hide_comment_forms(){
    $('.reply_form').hide();
    $('.edit_comment').hide();
}

/**
 * Ottiene l'ID del commento a cui si riferisce un pulsante cliccato dall'utente
 * @param {Object} button Elemento rappresentante il pulsante cliccato
 * @returns {number} id del commento a cui si riferisce il pulsante cliccato
 */
function get_comment_id_from_button(button) {
    const comment_id = Number($(button).closest('li').attr('id'));
    return comment_id;
}

/**
 * Svuota, ossia rimuove il testo, contenuto in un campo di input, ad esempio una textarea.
 * @param {Object} field Oggetto rappresentante il campo.
 */
function empty_input_field(field) {
    field.val('');
}

/**
 * Aggiorna il numero di commenti sotto un post
 * @param {number} delta Variazione del numero di commenti. Ad esempio, +2 lo fa aumentare di 2, -1 lo fa diminuire di 1
 */
function update_comments_count(delta) {
    const old_comments_count = Number($(COMMENTS_COUNT_ID).text());
    const difference_old_new_comments = old_comments_count + delta; 
    
    // Il numero di commenti non può mai essere negativo 
    const new_comments_number = difference_old_new_comments < 0 ? 
    0 :  
    difference_old_new_comments;

    $(COMMENTS_COUNT_ID).text(new_comments_number);
}

/**
 * Animazione per la risposta a un commento. Visualizza il form con tanto di pulsanti per rispondere.
 * @param {Event} event  
 */
function show_reply_form(event) {
    const button = event.currentTarget;
    const comment_container = button.closest('.comment_container');
    if (!comment_container) return;

    const reply_form = comment_container.querySelector('.reply_form');
    if (!reply_form) return;

    $(reply_form).show();

    const cancel_button = reply_form.querySelector('input[type="reset"]');
    if (!cancel_button) return;

    $(cancel_button).on('click', hide_reply_form);
}

/**
 * Annulla le modifiche a un commento, ovvero:
 * 1. Nasconde il form di modifica
 * 2. Visualizza nuovamente il testo vecchio del commento
 * @param {Event} event 
 */
function hide_edit_comment_form(event) {
    event.preventDefault();
    const cancel_button = event.currentTarget;

    const edit_form = cancel_button.closest('div');
    const comment_container = edit_form.closest('.comment_container');
    const comment_text = comment_container.querySelector('.comment_text');

    $(edit_form).hide();
    $(comment_text).show();
}

/**
 * Nascondi il form della risposta.
 * @param {Event} event 
 */
function hide_reply_form(event) {
    event.preventDefault();
    const cancel_button = event.currentTarget;

    const reply_form = cancel_button.closest('.reply_form');
    if (!reply_form) return;

    $(reply_form).hide(); 
}

/**
 * Ottieni il numero di risposte a un commento
 * @param {number} comment_id ID del commento di cui si vuole ottenere il numero di risposte
 * @returns {number} Numero di risposte
 */
function count_replies(comment_id) {
    // Trova il <ul> delle risposte basandoti sull'ID del commento
    const replies_list = document.querySelector(`#replies_to_${comment_id}`);
    
    if (!replies_list) return 0;

    // Conta gli elementi <li> figli diretti della lista di risposte
    const replies = replies_list.querySelectorAll('li.comment_container');
    return replies.length;
}