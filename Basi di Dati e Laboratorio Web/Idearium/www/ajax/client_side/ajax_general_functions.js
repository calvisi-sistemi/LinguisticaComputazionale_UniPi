function handle_error_in_ajax_request(errorThrown) {
    let readable_error = JSON.stringify(errorThrown);
    alert("Error in AJAX request: " + readable_error);
}

/**
 * Converte un timestamp in millisecondi in una stringa leggibile nel formato "j F Y H:i:s".
 * 
 * @param {number} timestamp - Il timestamp in millisecondi da convertire.
 * @returns {string} La data formattata in modo leggibile, nel formato "j F Y H:i:s".
 */
function get_readable_datetime(timestamp) {
    const date = new Date(timestamp);
    const day = date.getUTCDate();
    const month = date.toLocaleString('it-IT', { month: 'long', timeZone: 'UTC' });
    const year = date.getUTCFullYear();
    const hours = date.getUTCHours().toString().padStart(2, '0');
    const minutes = date.getUTCMinutes().toString().padStart(2, '0');
    const seconds = date.getUTCSeconds().toString().padStart(2, '0');

    return `${day} ${month} ${year} ${hours}:${minutes}:${seconds}`;
}

/**
 * Converti un timestamp in millisecondi in una stringa ISO.
 * 
 * @param {number} timestamp il timestamp in millisecondi 
 * @returns {string} Una stringa in formato ISO
 */
function get_iso_datetime(timestamp) {
    const iso_datetime = new Date(current_timestamp).toISOString();
    return iso_datetime;
}