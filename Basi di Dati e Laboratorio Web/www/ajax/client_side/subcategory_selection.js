const CATEGORY_SELECTION_ID = "#blog_category";
const SUBCATEGORY_FIELD = "#subcategory_selection";
const SUBCATEGORY_BLOCK = "#subcategory_block";

$(document).ready(main)

function main() {
    $(CATEGORY_SELECTION_ID).on('change', request_sub_categories);
}

function request_sub_categories() {
    let choosen_category = $(this).val();

    let main_category_data = {
        main_category: choosen_category
    };

    let ajax_request = {
        url: SUBCATEGORY_SELECTION_SERVERSIDE_SCRIPT_URL,
        type: 'POST',
        data: main_category_data,
        dataType: 'JSON',
        success: show_subcategories,
        error: handle_error_in_ajax_request
    };

    $.ajax(ajax_request);
}

function show_subcategories(response) {
    $(SUBCATEGORY_BLOCK).addClass("hidden");
    let available_subcategories = '';
    let output = '<option value="" disabled selected>Seleziona una sottocategoria</option>';

    if (response.error !== null) {
        alert(response.error);
        return;
    }

    let there_are_subcategories = (response.subcategories !== null);

    if (there_are_subcategories) {
        $(SUBCATEGORY_BLOCK).removeClass("hidden");
        response.subcategories.forEach(subcategory => {

            available_subcategories += `
                <option value="${subcategory}"> ${subcategory} </option>
                `
        });
        output = output + available_subcategories;
    }

    $(SUBCATEGORY_FIELD).html(
        output
    );
}