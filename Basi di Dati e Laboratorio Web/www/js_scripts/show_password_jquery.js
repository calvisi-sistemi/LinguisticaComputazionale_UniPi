const SHOW_PASSWORD_BUTTON = "#show_password";
const PASSWORD_INPUT = "#password";
const CONFIRM_PASSWORD_INPUT = "#password_confirm";

$(document).ready(main);

function main() {
    $(SHOW_PASSWORD_BUTTON).on('click', modify_password_visibility);
}

function modify_password_visibility() {
    show_password(PASSWORD_INPUT);
    show_password(CONFIRM_PASSWORD_INPUT);

    let password_button = $(SHOW_PASSWORD_BUTTON);

    if(!password_button) {
        return;
    }

    if(password_button.text().includes("Mostra")) {
        password_button.html('<span class="fa-solid fa-eye-slash"></span> Nascondi Password');
    }

    else if(password_button.text().includes("Nascondi")) {
        password_button.html('<span class="fa-solid fa-eye"></span> Mostra Password');
    }

}

function show_password(inputSelector) {
    const PASSWORD_FIELD = $(inputSelector);
    let password_type = PASSWORD_FIELD.attr('type');

    if (!PASSWORD_FIELD){
        return;
    }
    
    else if (password_type === 'password') {
        PASSWORD_FIELD.attr('type', 'text');
    }

    else if(password_type === 'text') {
        PASSWORD_FIELD.attr('type', 'password');
    }
}
