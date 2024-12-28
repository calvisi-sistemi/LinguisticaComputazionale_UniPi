/**
 * Classe dei pulsanti di risposta a un commento.
 * @type {string}
 */
const REPLY_BUTTON_CLASS = '.reply_button';

/**
 * Classe della textarea per la creazione di un nuovo commento, sia di livello zero che risposta
 * @type {string}
 */
const NEW_COMMENT_TEXTAREA_CLASS= '.new_comment_textarea';

/**
 * Classe dei pulsanti per la creazione di un nuovo commento
 * @type {string}
 */
const NEW_COMMENT_BUTTON_CLASS = '.new_comment_button';

/**
 * Classe dei pulsanti di cancellazione dei commenti
 * Si tratta di una stringa e non di un oggetto poiché viene usato all'interno di comment_delete.js
 * in una event-delegation
 * @type {string} 
 */
const DELETE_COMMENT_BUTTON_CLASS = '.delete_comment_button';

/**
 * Classe dei pulsanti per visualizzare il form di modifica di un commento.
 * @type {string}
 */
const EDIT_COMMENT_BUTTON_CLASS = '.edit_comment_button';

/**
 * Classe dei pulsanti per inviare un commento modificato.
 * @type {string}
 */
const SUBMIT_EDITED_COMMENT_BUTTON_CLASS = '.submit_edited_comment';

/**
 * Classe del testo dei commenti
 * @type {string}
 */
const COMMENT_TEXT = '.comment_text';

/**
 * Classe della data di ultima modifica di un commento.
 * @type {string}
 */
const COMMENT_LAST_EDIT = '.comment_last_edit';

/**
 * ID del numero di commenti presenti sotto a un post
 * @type {string}
 */
const COMMENTS_COUNT_ID = '#comments_count';

/**
 * ID della lista <ul> che contiene tutti i commenti. I suoi elementi <li> sono i commenti di livello zero
 * @type {string}
 */
const COMMENTS_GENERAL_LIST = '#comments_general_list';