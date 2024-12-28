const GOOD_FEEDBACK = 1;
const BAD_FEEDBACK = -1;
const NO_FEEDABCK = null;

const UPVOTE_COUNTER = $('#upvote_counter');
const DOWNVOTE_COUNTER = $('#downvote_counter');

const UPVOTE = "upvote";
const DOWNVOTE = "downvote";

const UPVOTE_ID = "#upvote";
const DOWNVOTE_ID = "#downvote";

const FEEDBACK_BUTTON = $('.feedback');

$(document).ready(main_function);

function main_function() {
    highlight_user_choice(current_user_feedback);
    FEEDBACK_BUTTON.on('click', send_new_feedback);
}

function fetch_feedback_counts() {
    const ajax_request = {
        url: FEEDBACK_HANDLER_SERVERSIDE_SCRIPT_URL,
        type: "POST",
        dataType: "json",
        success: function (response) {
            show_feedback(response);
            current_user_feedback = response.current_user_feedback;
        },
        error: handle_error_in_ajax_request
    };

    $.ajax(ajax_request);
}

function send_new_feedback(e) {
    let feedback_type;

    if (this.id === UPVOTE) {
        feedback_type = GOOD_FEEDBACK;
    } else if (this.id === DOWNVOTE) {
        feedback_type = BAD_FEEDBACK;
    }

    // Aggiorna immediatamente il feedback sul client
    update_feedback_ui(feedback_type);

    // Invia la richiesta AJAX per confermare il feedback
    const feedback = {
        new_feedback: feedback_type
    };

    const ajax_request = {
        url: FEEDBACK_HANDLER_SERVERSIDE_SCRIPT_URL,
        type: "POST",
        data: feedback,
        dataType: "json",
        success: handle_response_error,
        error: function () {
            // Ripristina lo stato se c'è un errore
            update_feedback_ui(current_user_feedback);
            handle_error_in_ajax_request();
        }
    };

    $.ajax(ajax_request);
}

/**
 * Aggiorna il feedback visualizzato dall'utente
 * @param {Number} new_feedback Feedback scelto dall'utente a seguito della pressione di un tasto
 */
function update_feedback_ui(new_feedback) {
    remove_feedback_ui(current_user_feedback);

    if (new_feedback !== current_user_feedback) {
        current_user_feedback = new_feedback;
        add_new_feedback_ui(new_feedback);
    } else {
        current_user_feedback = NO_FEEDABCK;
    }

    highlight_user_choice(current_user_feedback);
}

/**
 * Mostra i feedback del post al momento del caricamento della pagina, secondo quanto inviato dal server
 * @param {Array} response Risposta del server contenente le informazioni sul numero dei feedback e su quello scelto dall'utente corrente.
 */
function show_feedback(response) {
    UPVOTE_COUNTER.text(response.total_post_feedbacks.GOOD_FEEDBACKS);
    DOWNVOTE_COUNTER.text(response.total_post_feedbacks.BAD_FEEDBACKS);

    highlight_user_choice(response.current_user_feedback);
}

/**
 * Mette in evidenza il feedback scelto dall'utente corrente, applicando una classe opportuna al tasto corrispondente
 * @param {Number} user_choice GOOD_FEEDBACK o BAD_FEEDBACK
 */
function highlight_user_choice(user_choice) {
    $(UPVOTE_ID).removeClass("current_feedback");
    $(DOWNVOTE_ID).removeClass("current_feedback");

    if (user_choice === null) return;

    if (user_choice === GOOD_FEEDBACK) {
        $(UPVOTE_ID).addClass("current_feedback");
    }

    if (user_choice === BAD_FEEDBACK) {
        $(DOWNVOTE_ID).addClass("current_feedback");
    }
}

/**
 * Decrementa il contatore del feedback dato lato client.
 * @param {Number} feedback GOOD_FEEDBACK o BAD_FEEDBACK 
 */
function remove_feedback_ui(feedback) {
    if (feedback === GOOD_FEEDBACK) {
        let new_upvote_count = parseInt(UPVOTE_COUNTER.text()) - 1;
        UPVOTE_COUNTER.text(new_upvote_count);
    } else if (feedback === BAD_FEEDBACK) {
        let new_downvote_count = parseInt(DOWNVOTE_COUNTER.text()) - 1;
        DOWNVOTE_COUNTER.text(new_downvote_count);
    }
}

/**
 * Incrementa il contatore del feedback dato lato client
 * @param {Number} new_feedback Feedback dato.
 */
function add_new_feedback_ui(new_feedback) {
    if (new_feedback === GOOD_FEEDBACK) {
        let new_upvote_count = parseInt(UPVOTE_COUNTER.text()) + 1;
        UPVOTE_COUNTER.text(new_upvote_count);
    } else if (new_feedback === BAD_FEEDBACK) {
        let new_downvote_count = parseInt(DOWNVOTE_COUNTER.text()) + 1;
        DOWNVOTE_COUNTER.text(new_downvote_count);
    }
}

/**
 * Funzione invocata qualora la richiesta AJAX vada a buon fine, ma il server restituisca un errore.
 * @param {Array} response Array contenente la risposta del server
 */
function handle_response_error(response) {
    if (response.error) {
        alert(response.message);
    }
}

function handle_error(response) {
    handle_error_in_ajax_request(response);
}