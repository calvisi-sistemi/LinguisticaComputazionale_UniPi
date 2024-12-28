<?php
// DA FARE: gestire login per utenti salvati.

/**
 * Gestione dei meccanismi di autenticazione e di salvataggio dell'accesso di un utente.
 * Il cookie dell'utente ha questa struttura:
 *  'remember_me':
 *  [
 *      'username': 'nome_utente'
 *      'token': 'caratteri_casuali'
 *      'id': id per verifica su database
 *  ] 
 * 
 * Sul database NON viene salvato il token, ma un suo hash: 
 * 
 * $hashed_identifier = hash(dati_casuali); <-- SALVATO SUL DB 
 *
 * In questo modo, anche nell'ipotesi in cui qualcuno violasse il database, non potrebbe accedere agli account utente in nessun modo:
 *  - Non attraverso la password, poiché è hashata
 *  - Non attraverso questo valore, in quanto pure questo è offuscato.
 * 
 * L'hashing è effettuato attraverso password_hash
 */

/**
 * 30 giorni.
 */
const MAX_SAVED_LOGINS_DURATION_IN_SECONDS = 2592000;
const REMEMBER_ME_COOKIE = 'remember_me';
const COOKIE_TOKEN = 'token';
const COOKIE_USERNAME = 'username';
const COOKIE_SAVED_LOGIN_ID = 'saved_login_id';
const ALL = 'all_cookie_data';
const REMEMBER_ME_COOKIE_FIELDS = [COOKIE_TOKEN, COOKIE_USERNAME, COOKIE_SAVED_LOGIN_ID];
const DATETIME_SQL_FORMAT = 'Y-m-d H:i:s';

define(
    'REMEMBER_ME_COOKIE_OPTIONS',
    [
        'path' => '/',
        'expires' => time() + MAX_SAVED_LOGINS_DURATION_IN_SECONDS,
        'httponly' => true
    ]
);

/**
 * Effettua il login di un utente
 * @param PDO $db_connection Connessione al datbase
 * @param string $username Nome utente di cui effettuare il login
 * @param string $given_password Password in chiaro inserita dall'utente nella pagina di login
 * @param bool $user_wants_to_be_remembered Scelta se ricordare o meno l'utente sulla macchina da cui sta accedendo, in maniera da risparmiargli una seconda autenticazione con password. Di default FALSE.
 * @throws Exception in caso di errore
 * @return void
 */
function login_user(PDO $db_connection, string $username, string $given_password, bool $user_wants_to_be_remembered = false)
{
    try {
        $user_does_not_exist = !do_user_exist($db_connection, $username);

        if ($user_does_not_exist) {
            throw new Exception("L'utente $username non esiste");
        }

        $user_info = get_user_info($db_connection, $username);

        $password_is_not_correct = !password_verify($given_password, $user_info[USER_PASSWORD]);

        if ($password_is_not_correct) {
            throw new Exception('Password non corretta');
        }

        if ($user_wants_to_be_remembered) {
            remember_user($db_connection, $username);
        }

        unset($user_info[USER_PASSWORD]);

        $_SESSION['current_user'] = $user_info;

    } catch (Exception $e) {
        throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : ' . $e->getMessage());
    }
}

/**
 * Se c'è un utente salvato nella macchina corrente, fanne il login senza chiedere la password.
 * L'assunto di base è che sulla stessa macchina (in realtà sullo stesso browser) non possa essere salvato più di un utente
 * @param PDO $db_connection Connessione al database
 * @return void
 */
function login_user_if_is_saved(PDO $db_connection)
{
    $user_is_not_saved = !is_user_saved();
    if ($user_is_not_saved) {
        return;
    }

    $username = get_saved_user_data(COOKIE_USERNAME);

    $user_info = get_user_info($db_connection, $username);

    unset($user_info[USER_PASSWORD]); // Evito di tenere in memoria la password dell'utente senza motivo, anche se hashata.

    $login_is_valid = is_saved_login_valid($db_connection, $username);

    if ($login_is_valid) {
        $_SESSION['current_user'] = $user_info;
    }
}

/**
 * Logout dell'utente.
 * Distrugge la sessione corrente e porta l'utente in una destinazione scelta.
 * @param PDO $db_connection Connessione al database
 * @param string $destination_after_logout Dove portare l'utente una volta fatto il logout. Di default: login.php
 * @return void
 */
function logout_user(PDO $db_connection, string $destination_after_logout = DEFAULT_REDIRECT_DESTINATION)
{
    $user_is_remembered = is_user_saved();

    if ($user_is_remembered) {
        forget_user($db_connection);
    }

    session_destroy();
    redirect($destination_after_logout);
}

/**
 * Ricorda un utente al momento del login.
 * La funzione imposta un cookie contenente un token identificativo del login salvato e un ID per la corrispondenza col database.
 * @param PDO $db_connection Connessione al database
 * @param string $username Utente di cui si vuole ricordare l'accesso
 * @throws Exception In caso di errore
 * @return void
 */
function remember_user(PDO $db_connection, string $username): void
{
    try {
        $cookie_expiration = REMEMBER_ME_COOKIE_OPTIONS['expires'];
        $token = calculate_random_token_for_user_cookie();
        $hashed_token = password_hash($token, PASSWORD_BCRYPT);

        $saved_login_id = store_cookie_data_in_database($db_connection, $username, $hashed_token, $cookie_expiration);

        $cookie_value = [
            'username' => $username,
            'saved_login_id' => $saved_login_id,
            'token' => $token
        ];

        // Converto l'array in una stringa JSON poiché solo le stringhe possono essere salvate nei cookie
        $json_encoded_cookie_value = json_encode($cookie_value);

        setcookie(REMEMBER_ME_COOKIE, $json_encoded_cookie_value, REMEMBER_ME_COOKIE_OPTIONS);
    } catch (PDOException $e) {
        throw new Exception('Errore nel salvataggio dei dati del cookie nel database: ' . $e->getMessage());
    } catch (Exception $e) {
        throw new Exception('Errore nella creazione del cookie: ' . $e->getMessage());
    }
}

/**
 * Dimentica un utente.
 * La funzione cancella i dati del login salvato dal database e il REMEMBER_ME_COOKIE dalla macchina corrente.
 * @param PDO $db_connection Connessione al database
 * @return void
 */
function forget_user(PDO $db_connection)
{
    $saved_login_id = get_saved_user_data(COOKIE_SAVED_LOGIN_ID);

    delete_saved_login_data_from_database($db_connection, $saved_login_id);
    delete_cookie(REMEMBER_ME_COOKIE);
}

/**
 * Controlla se l'utente corrente ha un cookie REMEMBER_ME salvato
 * @return bool TRUE se l'utente ha il cookie salvato, FALSE altrimenti
 */
function is_user_saved(): bool
{
    $user_is_saved = false;
    if (isset($_COOKIE[REMEMBER_ME_COOKIE])) {
        $user_is_saved = true;
    }

    return $user_is_saved;
}

/**
 * Verifica che il token salvato sulla macchina dell'utente corrisponda effettivamente a quello salvato sul DB, escludendo dunque furti d'identità di altri utenti attraverso manomissioni del cookie.
 * @param PDO $db_connection Connessione al database
 * @param string $username Utente di cui si vuole verificare il login
 * @return bool TRUE se il login è valido, FALSE altrimenti
 */
function is_saved_login_valid(PDO $db_connection, string $username): bool
{
    $saved_login_id = get_saved_user_data(COOKIE_SAVED_LOGIN_ID);
    $token = get_saved_user_data(COOKIE_TOKEN);

    $hashed_token = get_hashed_login_token($db_connection, $saved_login_id);

    $login_is_valid = password_verify($token, $hashed_token);

    return $login_is_valid;
}

/**
 * Salva i dati del cookie nel database. 
 * @param PDO $db_connection Connessione al database
 * @param string $username Utente di cui si vuol salvare i valori del cookie di sessione nel database
 * @throws PDOException In caso di errore nella scrittura del dato sul db
 * @return int ID dell'ultimo login memorizzato sulla tabella.
 */
function store_cookie_data_in_database(PDO $db_connection, string $username, string $hashed_identifier, int $expiration_timestamp): int
{
    try {

        $expiration_datetime = date(DATETIME_SQL_FORMAT, $expiration_timestamp);

        $sql = 'INSERT INTO `' . SAVED_LOGINS_TABLE . '` 
        (`' . SAVED_LOGINS_USERNAME . '`, `' . SAVED_LOGINS_HASHED_IDENTIFIER . '`, `' . SAVED_LOGINS_EXPIRATION . '`) 
        VALUES (:username, :hashed_identifier, :expiration)';

        $db_connection->beginTransaction();

        $stmt = $db_connection->prepare($sql);
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->bindValue(':hashed_identifier', $hashed_identifier, PDO::PARAM_STR);
        $stmt->bindValue(':expiration', $expiration_datetime, PDO::PARAM_STR); // Non esiste una costante specifica per il datetime.
        $stmt->execute();
        $saved_login_id = $db_connection->lastInsertId();

        $db_connection->commit();

        return $saved_login_id;
    } catch (PDOException $e) {
        rollback_if_in_transaction($db_connection);
        throw new PDOException($e->getMessage());
    }
}

/**
 * Elimina i dati del login salvato dal database.
 * Da usarsi per "dimenticare" completamente un utente al momento del logout.
 * @param PDO $db_connection Connessione al database
 * @param string $saved_login_id ID del login salvato
 * @throws PDOException In caso di errore
 * @return void
 */
function delete_saved_login_data_from_database(PDO $db_connection, string $saved_login_id)
{
    $sql = 'DELETE FROM `' . SAVED_LOGINS_TABLE . '` 
    WHERE `' . SAVED_LOGINS_ID . '` = :saved_login_id';

    try {
        $db_connection->beginTransaction();
        $stmt = $db_connection->prepare($sql);
        $stmt->bindValue(':saved_login_id', $saved_login_id, PDO::PARAM_STR);
        $stmt->execute();
        $db_connection->commit();
    } catch (Exception $e) {
        rollback_if_in_transaction($db_connection);
        throw new Exception($e->getMessage());
    }
}

/**
 * Funzione per ottenere il token di controllo salvato sul database dato l'ID di un login salvato.
 * @param PDO $db_connection Connessione al database
 * @param int $saved_login_id ID del login di riferimento
 * @return string | null NULL se l'ID dato non esiste, una stringa col token di controllo altrimenti.
 */
function get_hashed_login_token(PDO $db_connection, int $saved_login_id): string|null
{
    $sql = 'SELECT `' . SAVED_LOGINS_HASHED_IDENTIFIER . '` 
    FROM `' . SAVED_LOGINS_TABLE . '`
    WHERE `' . SAVED_LOGINS_ID . '` = :saved_login_id';
    $stmt = $db_connection->prepare($sql);
    $stmt->bindValue(':saved_login_id', $saved_login_id, PDO::PARAM_INT);
    $stmt->execute();
    $hashed_token = $stmt->fetchColumn();
    return $hashed_token;
}

/**
 * Funzione per ottenere i dati dell'utente salvato.
 * I dati sono salvati nel cookie sotto forma di una stringa JSON. 
 * Questa funzione si occupa di portare la stringa in forma di array associativo ed estrarre il valore del campo scelto dall'utente.
 * @param string $data_to_get Informazione richiesta. Usando ALL viene restituito un array con tutte le informazioni del cookie.
 * @throws InvalidArgumentException In caso di richiesta di un campo inesistente.
 * @return int|string|array INT in caso si richieda l'ID del login salvato, STRING per le altre informazioni, ARRAY qualora si scelga di avere tutte le informazioni del cookie
 */
function get_saved_user_data(string $data_to_get = ALL | COOKIE_USERNAME | COOKIE_TOKEN | COOKIE_SAVED_LOGIN_ID): int|string|array
{
    $wanted_data_are_not_valid = !in_array($data_to_get, REMEMBER_ME_COOKIE_FIELDS);

    if ($wanted_data_are_not_valid) {
        throw new InvalidArgumentException('Il dato che hai richiesto non esiste.');
    }

    $saved_user_data = json_decode($_COOKIE[REMEMBER_ME_COOKIE], true);
    $wanted_data = $saved_user_data[$data_to_get];

    return $wanted_data;
}

/**
 * Restituisce una stringa casuale di 512 caratteri per identificare il cookie di un utente. 
 * @return string Token casuale
 */
function calculate_random_token_for_user_cookie(): string
{
    $random_data = random_bytes(256);
    $cookie_value = bin2hex($random_data);
    return $cookie_value;
}

/**
 * Funzione per cancellare il cookie indicato.
 * Si limita a calcolare un timestamp passato e a passarlo come "expiration" al cookie indicato.
 * Funzione resa utile dal fatto che PHP non offre una funzione built-in per cancellare i cookie, 
 * ma solo questa tecnica.
 * @param string $name_of_cookie_to_delete Nome del cookie da cancellare
 * @return void
 */
function delete_cookie(string $name_of_cookie_to_delete)
{
    $past_timestamp = time() - 10000000000000000;

    $expiration_in_the_past = [
        'expires' => $past_timestamp,
    ];
    setcookie($name_of_cookie_to_delete, "", $expiration_in_the_past);
}