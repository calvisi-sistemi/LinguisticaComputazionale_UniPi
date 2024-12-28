$(document).ready(main_function);

function main_function() {
    /*
        Uso un event-delegation (ovvero dico "quando si fa CLICK su DELETE_COMMENT_BUTTON_CLASS all'interno
        di , esegui DELETE_COMMENT") per evitare di dover riaggiungere un event listener ad ogni
        nuovo pulsante di cancellazione quando viene creato un nuovo commento.
     */
    $(COMMENTS_GENERAL_LIST).on('click', DELETE_COMMENT_BUTTON_CLASS, delete_comment);
}

/**
 * Elimina un commento selezionato inviando una richiesta AJAX al server.
 *
 * La funzione gestisce il successo e l'errore della richiesta tramite le callback
 * `error_in_comment_removal` e `handle_error_in_ajax_request`.
 * 
 * La logica è dunque quella di rimuovere prima il commento dalla pagina, e dopo inviare una richiesta AJAX
 * per rimuovere i dati dal server. In questo modo, l'utente percepisce una maggiore reattività del sito.
 * 
 * Il rovescio della medaglia è il rischio che l'utente, dopo aver scelto di rimuovere un commento,
 * veda un messaggio di errore che avvisa di un errore nella sua rimozione e che, nel riaggiornare la pagina,
 * ritrovi il suo commento tale e quale.
 * 
 * Questa possibilità di discrepanza nell'esperienza utente ci sembra un compromesso accettabile per un incremento notevole 
 * nella reattività dell'interfaccia.
 * 
 * @listens click - Richiamata da un evento di clic associato a un elemento con un ID commento.
 */
function delete_comment() {
    const comment_id = $(this).data('comment-id');

    remove_comment(comment_id);

    const comment_to_delete = {
        comment_to_delete: comment_id
    };

    const ajax_request = {
        url: DELETE_COMMENT_SERVERSIDE_SCRIPT_URL,
        type: 'POST',
        data: comment_to_delete,
        dataType: 'json',
        success: error_in_comment_removal,
        error: handle_error_in_ajax_request
    };

    $.ajax(ajax_request);
}

/**
 * Gestisce l'errore durante la rimozione di un commento.
 * 
 * Controlla la risposta dal server e, in caso di fallimento, mostra un messaggio
 * di errore specifico in un'alert. Se `response.success` è `true`, la funzione 
 * non esegue alcuna azione aggiuntiva.
 * 
 * Questa funzione viene invocata qualora la richiesta AJAX sia stata inviata con successo, ma il server abbia risposto con un errore. 
 * @param {Object} response - Oggetto contenente i dati della risposta del server. 
 */
function error_in_comment_removal(response) {
    if (!response.success) {
        alert('Errore: ' + response.message);
        return;
    }
}

/**
 * Rimuove il nodo del DOM contenente il commento scelto.
 * Si occupa solo della modifica del DOM, NON invia alcuna richiesta al server
 * @param {Number} comment_id ID del commento da eliminare
 */
function remove_comment(comment_id) {
    /**
     * Numero di commenti cancellati / da cancellare, 
     * calcolato come numero di risposte al commento + 1 (il commento stesso)
     */
    const number_of_deleted_comments = count_replies(comment_id) + 1;

    if (comment_id < 0) return;

    /**
     * Selettore di un commento e di tutte le sue risposte.
     */
    const FULL_COMMENT_SELECTOR = $('li#' + comment_id);

    FULL_COMMENT_SELECTOR.remove();

    update_comments_count(-number_of_deleted_comments);
}