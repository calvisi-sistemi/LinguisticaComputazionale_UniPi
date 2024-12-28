<?php
/**
 * Effettua il redirect dell'utente nella destinazione scelta, assicurandosi che lo script in cui la funzione è richiamata venga terminato.
 * Esempio d'uso:
 * ```
 * redirect('index.php');
 * ```
 * @param string $redirect_destination Destinazione scelta 
 * La destinazione di default è index.php
 * @return void
 */
function redirect(string $redirect_destination = DEFAULT_REDIRECT_DESTINATION){
    header("Location: $redirect_destination");
    exit;
}

/**
 * Funzione per effettuare il redirect nelle pagine che devono poter essere visualizzate
 * solo da utenti autenticati.
 * Questa funzione evita di effettuare il redirect quando chiamata all'interno di HOME_PAGE, LOGIN_PAGE e SIGNUP_PAGE, poiché:
 *  - La HOME_PAGE deve essere visualizzata da tutti, anche  dagli utenti non autenticati, essendo la pagina in cui di default vengono rimandati gli utenti che non si autenticano.
 *  - LOGIN_PAGE e SIGNUP_PAGE allo stesso modo devono essere visualizzabili da tutti, poiché, altrimenti, agli utenti sarebbe impossibile effettuare il login o registrarsi
 * @return void
 */
function redirect_if_not_logged_in(string $redirect_destination = DEFAULT_REDIRECT_DESTINATION): void
{
    if(in_array(get_current_page_name(), PAGES_THAT_CAN_BE_VISITED_WITHOUT_LOGIN)) return;
    
    if(is_user_logged_in()) return;
    
    redirect($redirect_destination);
}

/**
 * Effettua il redirect di utenti già loggati.
 * @return void
 */
function redirect_if_already_logged_in(string $redirect_destination = DEFAULT_REDIRECT_DESTINATION): void
{
    if(is_user_logged_in()){
        redirect($redirect_destination);
    }
}

/**
 * Effettua il redirect dalle pagine a cui si può accedere solo tramite POST qualora l'utente vi acceda in altro modo.
 * @return void
 */
function redirect_if_not_accessed_through_post(string $redirect_destination = DEFAULT_REDIRECT_DESTINATION): void
{
    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        redirect($redirect_destination);
    }
}

/**
 * Effettua il redirect altrove dalle pagine che devono essere usate solo all'interno di include o require.
 * @param string $file_magic_constant deve avere il valore di __FILE__. 
 * Poiché __FILE__ viene sostituito con il percorso del file in cui viene adoperato al momento del parsing, 
 * se lo inserissi direttamente, verrebbe sostituito dal percorso del file corrente, 
 * in cui sto definendo la funzione, e non con il percorso del file in cui la funzione è richiamata. 
 * @return void
 */
function redirect_if_page_is_accessed_directly(string $file_magic_constant, string $redirect_destination = DEFAULT_REDIRECT_DESTINATION){
    $page_is_being_accessed_directly = is_page_being_accessed_directly($file_magic_constant);
    if($page_is_being_accessed_directly){
        redirect($redirect_destination);
    }
}

/**
 * Funzione per ottenere il nome della pagina corrente richiesta dall'utente.
 * @return string Nome della pagina, estratto dal suo percorso relativo
 */
function get_current_page_name(): string
{
    return basename($_SERVER['SCRIPT_NAME']);
}