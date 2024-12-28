<?php

/**
 * Salva un errore in maniera tale da poterlo visualizzare in un altra parte del sito.
 * @param string $error Messaggio di errore
 * @return void
 */
function set_error(string $error): void
{
    $_SESSION['error'] = $error;
}

/**
 * Verifica che sia presente o meno un errore.
 * @return bool
 */
function there_is_an_error(): bool
{
    return isset($_SESSION['error']) && is_string($_SESSION['error']);
}

/**
 * Ottieni il testo dell'errore attualmente presente in memoria.
 * Una volta restituito il testo, il messaggio di errore viene eliminato,
 * ovvero la funzione ```there_is_an_error()``` restituisce a quel punto ```false```
 * @return string Testo dell'errore
 */
function get_error(): string
{
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
    return $error;
}