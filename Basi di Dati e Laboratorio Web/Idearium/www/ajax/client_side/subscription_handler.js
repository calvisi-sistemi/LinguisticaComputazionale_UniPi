const SUBSCRIPTION_BUTTON = "#subscription_button";
const SUBSCRIBED = true;
const NOT_SUBSCRIBED = false;
const PLUS_ICON_HTML = `<span class="fa-solid fa-plus"></span>`;

$(document).ready(main);

function main() {
    get_current_subscription_status();
    $(SUBSCRIPTION_BUTTON).on('click', toggle_subscription);
}

function toggle_subscription() {

    /* 
    Mi limito a comunicare al server che l'utente ha premuto il pulsante.
    Cosa fare viene deciso lato backend.
    */
    let message_to_server = {
        user_pressed_subscription_button: true
    };

    let ajax_request = {
        url: SUBSCRIPTION_HANDLER_SERVERSIDE_SCRIPT_URL,
        type: 'POST',
        data: message_to_server,
        dataType: 'json',
        success: show_subscription_status,
        error: handle_error_in_ajax_request    
    }

    $.ajax(ajax_request);
}

function get_current_subscription_status() {

    let current_subscription_status_request = {
        url: SUBSCRIPTION_HANDLER_SERVERSIDE_SCRIPT_URL,
        type: 'POST',
        dataType: 'json',
        success: show_subscription_status,
        error: handle_error_in_ajax_request    
    }

    $.ajax(current_subscription_status_request);
}

function show_subscription_status(response) {
    if (response.error !== null) {
        alert('Error: ' + response.error);
        return;
    }
    
    $(SUBSCRIPTION_BUTTON).removeClass('subscribed not_subscribed');

    if (response.subscription_status === SUBSCRIBED) {
        $(SUBSCRIPTION_BUTTON).addClass('subscribed');
        button_content =  'Disiscriviti';
    }

    if (response.subscription_status === NOT_SUBSCRIBED) {
        $(SUBSCRIPTION_BUTTON).addClass('not_subscribed');
        button_content = PLUS_ICON_HTML + 'Iscriviti';
    }

    $(SUBSCRIPTION_BUTTON).html(button_content);
}