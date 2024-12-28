<?php
/**
 * Inizializzazione della sessione e inclusione delle dipendenze principali per la gestione del database e delle funzionalità del sito.
 *
 * Questo file si occupa di avviare la sessione e di caricare tutte le costanti e le funzioni 
 * necessarie per gestire le operazioni principali del sito web. Include le 
 * dipendenze per la gestione degli utenti, blog, commenti, coautori, post, categorie, 
 * utilità di ricerca, funzioni ausiliarie per il database e la validazione.
*/

session_start();

const FUNCTION_ERROR_MESSAGE = 'Errore nella funzione ';

function rollback_if_in_transaction(PDO $db_connection){
    if($db_connection->inTransaction())
    {
        $db_connection->rollBack();
    }
}

require_once 'database_constants.php';
require_once 'connection.php';
require_once 'users_management.php';
require_once 'blogs_management.php';
require_once 'comments_management.php';
require_once 'coauthors_management.php';
require_once 'posts_management.php';
require_once 'categories_management.php';
require_once 'search_utilities.php';
require_once 'feedback_management.php';
require_once 'subscriptions_management.php';
require_once 'auxiliary_database_functions.php';
require_once 'validation.php';
require_once 'redirect_functions.php';
require_once 'login_and_logout_management.php';
require_once 'images_management_functions.php';
require_once 'errors_management.php';