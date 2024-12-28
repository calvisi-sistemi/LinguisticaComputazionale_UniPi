<?php
/** 
 * Funzioni per la gestione delle categorie e sotto categorie
*/

/**
 * Funzione per ottenere l'elenco di tutte le categorie principali, ossia che non sono sottocategorie di nessun altra categoria.
 * @param PDO $db_connection Connessione al database
 * @throws Exception In caso di errore
 * @return array array monodimensionale contenente l'elenco delle categorie principali
 *
 */
function get_main_categories(PDO $db_connection): array
{
  try {
    $sql = 'SELECT category.`'. CATEGORY_NAME .'`
        FROM `' . CATEGORY_TABLE . '` AS category
        LEFT JOIN `' . SUBCATEGORY_TABLE . '` AS subcategory
        ON category.`' . CATEGORY_NAME . '` = subcategory.`' . SUBCATEGORY_SUB_CATEGORY . '`
        WHERE subcategory.`' . SUBCATEGORY_SUB_CATEGORY . '` IS NULL;';
    $stmt = $db_connection->prepare($sql);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN); // Per ottenere un array di stringhe, non un array associativo
    return $categories;
  } catch (Exception $e) {
    throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : '  . $e->getMessage());
  }
}

/**
 * Ottieni le sottocategorie di una categoria data.
 * @param PDO $db_connection Connessione al DB
 * @param string $main_category_name nome della categoria principale di cui ottenere le sottocategorie
 * @throws Exception In caso di errore
 * @return array | null array monodimensionale contenente la lista delle sottocategorie della categoria data. 
 * Se la categoria scelta non ha sottocategorie, viene restituito NULL
 */
function get_subcategories(PDO $db_connection, string $main_category_name): array | null
{
  try {
    $sql = 'SELECT `'. SUBCATEGORY_SUB_CATEGORY .'` FROM `'. SUBCATEGORY_TABLE .'` 
    WHERE `'. SUBCATEGORY_MAIN_CATEGORY .'` = :main_category_name';

    $stmt = $db_connection->prepare($sql);
    $stmt->bindValue(":main_category_name", $main_category_name, PDO::PARAM_STR);
    $stmt->execute();
    $subcategories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $there_are_not_subcategories = empty($subcategories);
    
    if($there_are_not_subcategories){
      $subcategories = null;
    }

    return $subcategories;

  } catch (Exception $e) {
    throw new Exception(FUNCTION_ERROR_MESSAGE . __FUNCTION__ . ' : '  . $e->getMessage());
  }
}

function subcategories_breadcrumb_is_started(): bool
{
  return isset($_SESSION['current_subcategories_breadcrumb']) && is_array($_SESSION['current_subcategories_breadcrumb']);
}

function get_full_subcategories_breadcrumb(): array
{
  return $_SESSION['current_subcategories_breadcrumb'];
}

function set_current_category(string $category): void
{
  $_SESSION['current_category'] = $category;
}

function destroy_current_category(): void
{
  unset($_SESSION['current_category']);
}

function get_current_category(): string
{
  return $_SESSION['current_category'];
}

function one_step_deeper_in_subcategories_breadcrumb(string $category): void
{
  $_SESSION['current_subcategories_breadcrumb'][] = $category;
} 

/**
 * Risali il breadcrumb delle categorie fino a una categoria data.
 * @param string $category La categoria fino alla quale devo risalire.
 * @return void
 */
function go_to_higher_category(string $category): void
{
  $where_i_am = array_search($category, get_full_subcategories_breadcrumb());
  array_splice($_SESSION['current_subcategories_breadcrumb'], $where_i_am + 1);  
}

function destroy_subcategories_breadcrumb(): void
{
  unset($_SESSION['current_subcategories_breadcrumb']);
}

function is_this_an_already_visited_category(string $category): bool
{
  return subcategories_breadcrumb_is_started() && in_array($category, get_full_subcategories_breadcrumb());
}