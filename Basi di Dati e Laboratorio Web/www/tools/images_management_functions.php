<?php

/**
 * Verifica l'esistenza di un'immagine
 * @param string $image_name Nome dell'immagine
 * @return bool TRUE qualora $image_name esista e sia un file, FALSE altrimenti
 */
function does_image_exist(string $image_name): bool
{
    $image_path = IMAGE_DIRECTORY_FROM_TOOLS_POV . $image_name;
    return is_file($image_path);
}

/**
 * Verifica se un'immagine avatar, il logo di un blog o l'immagine allegata a un post è unica.
 * @param PDO $db_connection Connessione al database
 * @param ?string $image_name Nome dell'immagine di cui verificare l'unicità: è il valore che viene confrontato con quelli presi dal DB
 * @throws Exception in caso di errore
 * @return bool TRUE se l'immagine scelta è unica all'interno del database (ha un solo impiego), FALSE altrimenti, oppure se $image_name ha valore NULL.
 */
function is_image_unique(PDO $db_connection, ?string $image_name): bool
{
    if (is_null($image_name)) {
        return false;
    }

    $number_of_images = count_images($db_connection, $image_name);

    $image_is_unique = $number_of_images === 1;

    return $image_is_unique;

}

/**
 * Dato un elenco di nomi di immagini e una tabella, ottiene l'elenco delle immagini che compaiono una volta sola in quella tabella.
 * @param PDO $db_connection Connessione al database
 * @param array $pics Array con i nomi delle immagini da valutare
 * @param string $table Tabella in cui cercare
 * @throws Exception in caso di errore 
 * @return array | null NULL se non ci sono immagini uniche, altrimenti un array con i nomi delle immagini 
 */
function get_unique_images(PDO $db_connection, array $pics): array|null
{
    foreach ($pics as $pic) {
        if (is_image_unique($db_connection, $pic)) {
            $unique_pics[] = $pic;
        }
    }

    return $unique_pics;
}

/**
 * Dammi il numero di account, blog o post che utilizzano lo stesso fil rispettivamente per il loro avatar, logo o immagine allegata.
 * @param PDO $db_connection Connessione al database
 * @param string $image_name Nome dell'immagine di cui verificare l'unicità: è il valore che viene confrontato con quelli presi dal DB
 * @param string $table Tabella in cui cercare il nome dell'immagine. Tabelle ammesse: USER_TABLE, BLOG_TABLE, POST_TABLE
 * @throws Exception in caso di errore
 * @return int restituisce il numero delle immagini.
 */
function count_images(PDO $db_connection, string $image_name): int
{
    $query = '
      SELECT (
        (SELECT COUNT(*) FROM ' . USER_TABLE . ' WHERE ' . USER_AVATAR . ' = :image_name) +
        (SELECT COUNT(*) FROM ' . BLOG_TABLE . ' WHERE ' . BLOG_PIC . ' = :image_name) +
        (SELECT COUNT(*) FROM ' . POST_TABLE . ' WHERE ' . POST_IMAGE . ' = :image_name)
      ) AS total_count
    ';

    $stmt = $db_connection->prepare($query);
    $stmt->bindValue(':image_name', $image_name, PDO::PARAM_STR);
    $stmt->execute();
    $total_count = $stmt->fetchColumn();

    return $total_count;
}

/**
 * Funzione per cancellare i file immagine degli avatar, dei blog o dei post.
 * Si limita a cancellare i file, non effettua alcuna operazione sul database.
 * @param string $image_name_to_delete Nome dell'immagine da cancellare (valore da recuperare dal DB)
 * @throws Exception se il file specificato non esiste, la directory indicata non è valida o non si hanno i permessi di scrittura su di lei
 * @return void 
 */
function delete_image(string $image_name_to_delete): void
{

    check_if_image_directory_is_suitable(IMAGE_DIRECTORY_FROM_TOOLS_POV);
    check_image_file($image_name_to_delete);

    $full_image_path = IMAGE_DIRECTORY_FROM_TOOLS_POV . $image_name_to_delete;

    $deletion_not_succeded = !unlink($full_image_path);

    if ($deletion_not_succeded) {
        throw new Exception("Impossibile cancellare: $full_image_path");
    }

}

/**
 * Dato il nome di un file, ne ottiene l'estensione, assicurandosi di portarla in caratteri minuscoli.
 * Esempio di utilizzo:
 * ```
 * get_lowercase_file_extension('MiaFoto.JPG') => 'jpg'
 * ```
 * @param string $file_name nome del file di cui ottenere l'estensione
 * @return string stringa con l'estensione del file in minuscolo. 
 */
function get_lowercase_file_extension(string $file_name): string
{
    $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
    $lowercase_file_extension = strtolower($file_extension);
    return $lowercase_file_extension;
}

/**
 * Calcolo del nome definitivo di un'immagine.
 * Ogni immagine avrà la seguente struttura: sh256_del_file.estensione_originale
 * @param array $new_image_file corrisponde a $_FILES['nome_campo_upload']
 * @return string Nome definitivo dell'immagine
 */
function calculate_new_image_name(array $new_image_file): string
{
    $new_image_name = $new_image_file['name'];
    $new_image_tmp_path = $new_image_file['tmp_name'];
    $extension = get_lowercase_file_extension($new_image_name);

    $image_hash = hash_file(HASH_ALGORITHM_FOR_IMAGE_NAMES, $new_image_tmp_path);

    $def_image_name = "$image_hash.$extension";

    return $def_image_name;
}

/**
 * Funzione per impostare gli avatar degli utenti, i loghi dei blog e le immagini dei post.
 * Impostare significa:
 * - Calcolare il percorso completo in cui salvarle (differente per BLOG, POST e AVATAR)
 * - Spostare effettivamente l'immagine nella sua destinazione definitiva col suo nome definitivo
 * Non scrive nulla sul DB: l'aggiornamento dei record è lasciato alle funzioni specifiche di ogni caso.
 * 
 * @param array $new_image_file array associativo $_FILE['nome_campo']
 * @param string $image_definitive_name Nome definitivo dell'immagine con cui salvarla nel file system
 * @throws Exception in caso di errore
 * @return void
 */
function save_image(array $new_image_file, string $image_definitive_name): void
{
    $image_temporary_path = $new_image_file['tmp_name'];

    check_if_image_directory_is_suitable(IMAGE_DIRECTORY_FROM_TOOLS_POV);

    $full_destination_path = IMAGE_DIRECTORY_FROM_TOOLS_POV . $image_definitive_name;

    $move_status = move_uploaded_file($image_temporary_path, $full_destination_path);

    if (!$move_status) {
        throw new Exception("Impossibile spostare il file in $full_destination_path");
    }
}

/**
 * Calcola il percorso di un immagine data.
 * @param string $image_name Il nome dell'immagine
 * @throws Exception
 * @return string
 */
function get_image_path(string $image_name): string
{
    return IMAGE_DIRECTORY . $image_name;
}

