<?php
/**
 * Funzioni per la validazione dell'input.
 */

require_once 'validation_constants.php';

/**
 * Controllo che un file sia stato caricato correttamente, valutando il campo $_FILES['nome_campo_file']['error'] e sollevando un'eccezione opportuna in caso di errore.
 * @param mixed $uploaded_file L'array con del file caricato, $_FILES['nome_campo_file']
 * @throws Exception Un'eccezione diversa per ogni errore
 * @return bool TRUE qualora in file sia stato caricato senza problemi
 */
function check_uploaded_file_errors($uploaded_file): bool
{
    // Funzione che controlla l'upload dei file (valutando la funzione $_FILES['nome_campo']['error']) e solleva opportune eccezioni dove necessario
    switch ($uploaded_file['error']) { // Controllo degli errori nel caricamento del file
        case UPLOAD_ERR_OK:
            return true;

        case UPLOAD_ERR_INI_SIZE:
            throw new Exception('Il file supera la dimensione massima consentita dal server.');

        case UPLOAD_ERR_FORM_SIZE:
            throw new Exception('Il file supera le dimensioni massime consentite dal modulo HTML.');

        case UPLOAD_ERR_PARTIAL:
            throw new Exception('Il file è stato caricato solo parzialmente.');

        case UPLOAD_ERR_NO_FILE:
            throw new Exception('Errore durante il caricamento del file.');

        case UPLOAD_ERR_NO_TMP_DIR:
            throw new Exception('Manca una cartella temporanea.');

        case UPLOAD_ERR_CANT_WRITE:
            throw new Exception('Errore nello scrivere il file su disco.');

        case UPLOAD_ERR_EXTENSION:
            throw new Exception('Un\'estensione PHP ha bloccato il caricamento del file.');

        default:
            throw new Exception('Errore sconosciuto durante il caricamento del file.');
    }
}

function clean($data): string
{
    $data = str_replace(' ', '', $data); // Rimozione degli spazi bianchi
    $data = stripslashes($data); // Rimozione degli slash
    $data = htmlspecialchars($data); // Conversione dei caratteri speciali in entità HTML
    return $data;
}
;

/**
 * Funzione per la "pulizia del testo", ovvero
 * 1. Rimozione degli spazi al principio ed alla fine di una stringa
 * 2. Conversione dei caratteri HTML in HTML Entities
 * @param string $data testo da pulire
 * @return string testo pulito
 */
function clean_text(string $data): string
{
    $data = trim($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Verifica che una query sia vuota.
 * Una query si considera vuota se:
 *  - Non contiene alcun valore
 *  - Contiene solo spazi bianchi
 * @param string $query Query da controllare
 * @return bool TRUE se la query è vuota, FALSE altrimenti
 */
function is_query_blank(string $query): bool
{
    $query_is_blank = ctype_space($query) || empty($query);
    return $query_is_blank;
}

/**
 * Controlla che la directory data sia adatta a effettuare le operazioni con le immagini (cancellazione e salvataggio del file), sollevando opportune eccezioni altrimenti.
 * @param string $given_directory Directory da valutare
 * @throws Exception In tutti gli altri casi 
 * @return void
 */
function check_if_image_directory_is_suitable(string $given_directory): void
{

    if (!is_dir($given_directory)) {
        throw new Exception("Il percorso $given_directory non punta ad una directory.");
    }

    if (!is_readable($given_directory)) {
        throw new Exception("La directory $given_directory non è leggibile");
    }

    if (!is_writable($given_directory)) {
        throw new Exception("Non hai i permessi di scrittura su $given_directory");
    }
}


/**
 * Controllo delle informazioni di un utente prima della sua creazione.
 * Se anche una sola delle informazioni dell'utente non rispetta i criteri stabiliti, viene sollevata un'eccezione opportuna.
 * @param PDO $db_connection Connessione al database
 * @param string $username Si assicura che sia composto solo da lettere minuscole e numeri e che non sia già in uso
 * @param string $complete_name Si assicura che sia composto solo da lettere e spazi bianchi
 * @param string $email Si assicura che sia effettivamente formattata come e-mail e che non sia già in uso
 * @param string $password Si assicura che sia della lunghezza minima richiesta
 * @param ?string $bio Si assicura che non ecceda la lunghezza massima consentita. Parametro opzionale
 * @throws Exception Solleva un eccezione opportuna per ogni condizione che non viene rispettata.
 * @return void
 */
function check_new_user_information(PDO $db_connection, string $username, string $complete_name, string $email, string $password, ?string $bio = null): void
{
    check_username_validity($username);
    check_email_validity($email);
    check_complete_name_validity($complete_name);
    check_bio_lenght($bio);
    check_password_lenght($password);

    $user_already_exists = do_user_exist($db_connection, $username);

    if ($user_already_exists) {
        throw new Exception("L'utente $username è già registrato. <a href=\'/login.php\'> Accedi </a>");
    }

    $email_already_used = is_email_already_in_use($db_connection, $email);

    if ($email_already_used) {
        throw new Exception("L'indirizzo email $email è già stato usato");
    }
}

function check_username_validity(string $username): void
{
    $username_is_not_valid = !preg_match(USER_USERNAME_PATTERN, $username);
    if ($username_is_not_valid) {
        throw new Exception('Il nome utente scelto non è formmattato correttamente.');
    }
}

function check_email_validity(string $email): void
{
    $email_is_not_valid = !preg_match(USER_EMAIL_PATTERN, $email);
    if ($email_is_not_valid) {
        throw new Exception('L\'email scelta non è formattata correttamente.');
    }
}

function check_password_lenght(string $password): void
{
    $password_is_too_short = strlen($password) < MINIMUM_PASSWORD_LENGHT;
    if ($password_is_too_short) {
        throw new LengthException('La password deve essere di almeno ' . MINIMUM_PASSWORD_LENGHT . ' caratteri');
    }
}

function check_complete_name_validity(string $complete_name): void
{
    $complete_name_is_not_valid = !preg_match(USER_COMPLETE_NAME_PATTERN, $complete_name);
    if ($complete_name_is_not_valid) {
        throw new Exception('Il nome da visualizzare non è formattato correttamente');
    }
}

function check_bio_lenght(?string $bio = null): void
{
    if (is_null($bio)) {
        return;
    }

    $bio_is_too_long = strlen($bio) > MAX_BIO_LENGHT;

    if ($bio_is_too_long) {
        throw new LengthException('La bio è troppo lunga. Può contenere massimo ' . MAX_BIO_LENGHT . ' caratteri');
    }
}

function is_user_logged_in(): bool
{
    $user_is_logged_in = isset($_SESSION['current_user'][USER_USERNAME]);
    return $user_is_logged_in;
}

function is_page_being_accessed_directly(string $file_magic_constant): bool
{
    $page_is_accessed_directly = $file_magic_constant === $_SERVER['SCRIPT_FILENAME'];
    return $page_is_accessed_directly;
}

/**
 * Controlli sui file delle immagini. Si assicura che $image_name:
 * - Esista
 * - Sia un file regolare
 * - Sia leggibile
 * 
 * @param string $image_name Nome dell'immagine da valutare
 * @throws Exception Qualora il file non rispetti le condizioni sopradette.
 * @return void
 */
function check_image_file(string $image_name): void
{
    $image_path = IMAGE_DIRECTORY_FROM_TOOLS_POV . $image_name;

    if (!file_exists($image_path)) {
        throw new Exception("L'immagine $image_path non esiste");
    }

    if (!is_file($image_path)) {
        throw new Exception("L'immagine $image_path non è un file regolare");
    }

    if (!is_readable($image_path)) {
        throw new Exception("L'immagine $image_path non è leggibile");
    }
}

/**
 * Controlla che un feedback sia valido, ovvero che il suo valore sia uno dei seguenti:
 *  - `NULL` - Adoperato per la rimozione di un feedback
 *  - `GOOD_FEEDBACK` - feedback positivo
 *  - `BAD_FEEDBACK` - feedback negativo
 * 
 * @param mixed $feedback Valore da valutare
 * @return bool `TRUE` se il valore fornito rientra tra quegli su elencati, `FALSE` altrimenti
 */
function is_feedback_valid(mixed $feedback): bool
{
    return $feedback === null || $feedback === GOOD_FEEDBACK || $feedback === BAD_FEEDBACK;
}

/**
 * Verifica che le chiavi di un array corrispondano a un insieme predefinito di chiavi valide.
 *
 * La funzione confronta le chiavi di `$to_evaluate` con l'elenco di chiavi definite in `$valid_keys`.
 * Se ci sono chiavi mancanti o extra, solleva un'eccezione.
 * 
 * L'ordine in cui queste chiavi compaiono non è valutato.
 * 
 * @param array $valid_keys   Elenco delle chiavi valide che devono essere presenti in `$to_evaluate`.
 * @param array $to_evaluate  L'array da valutare, le cui chiavi verranno confrontate con `$valid_keys`.
 *
 * @throws Exception Se le chiavi di `$to_evaluate` non corrispondono a quelle in `$valid_keys`.
 *
 * @return void
 */
function check_array_keys(array $valid_keys, array $to_evaluate): void
{
    $keys_to_evaluate = array_keys($to_evaluate);
    
    $missing_keys = array_diff($valid_keys, $keys_to_evaluate);
    $extra_keys = array_diff($keys_to_evaluate, $valid_keys);

    if (!empty($missing_keys) || !empty($extra_keys)) {
        throw new Exception('La struttura dell\'array non è valida');
    }
}