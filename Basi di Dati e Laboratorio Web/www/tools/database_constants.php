<?php
/**
 * Costanti per riferirsi più comodamente a tabelle e colonne del database.
 */

// Connessione a PHPMyAdmin con le credenziali di default (non sicuro ma richiesto così dall'esame)
const DB_TYPE = 'mysql';
const DB_SERVER = '127.0.0.1';
const DB_NAME = 'calvisi_difalco';
const DB_USER = 'root'; 
const DB_PASSWORD = '';

// Sezione delle categorie
const CATEGORY_TABLE = 'Categoria';
const CATEGORY_NAME = 'nome_categoria';

// Sezione delle sottocategorie
const SUBCATEGORY_TABLE = 'Sottocategoria';
const SUBCATEGORY_MAIN_CATEGORY = 'categoria_principale';
const SUBCATEGORY_SUB_CATEGORY = 'sotto_categoria';

// Sezione UTENTI   
const USER_TABLE = 'Utenti';
const USER_USERNAME = 'nome_utente';
const USER_PASSWORD = 'password';
const USER_COMPLETE_NAME = 'nome_visualizzato';
const USER_EMAIL = 'email';
const USER_BIO = 'bio';
const USER_SIGNUP_DATETIME = 'data_ora_di_iscrizione';
const USER_AVATAR = 'avatar';
const USER_PREMIUM_STATUS = 'premium';
const USER_TOTAL_SUBSCRIBERS = 'totale_iscritti_ai_propri_blog';

// Sezione BLOG
const BLOG_TABLE = 'Blog';
const BLOG_ID = 'id_blog';
const BLOG_TITLE = 'titolo_visualizzato';
const BLOG_OWNER = 'nome_amministratore_blog';
const BLOG_CREATION_DATE = 'data_creazione';
const BLOG_CATEGORY = 'categoria';
const BLOG_DESCRIPTION = 'descrizione';
const BLOG_PIC = 'logo';

// Sezione CO-AUTORI (ossia: chi scrive su quale blog senza esserne amministratore)
const COAUTHORS_TABLE = 'Autori';
const COAUTHORS_USER = 'id_autore';
const COAUTHORS_BLOG = 'id_blog';
    // Costanti per l'aggiunta e rimozione di coautori
const TO_ADD = true;
const TO_REMOVE = false;

// Sezione dei POST
const POST_TABLE = 'Post';
const POST_ID = 'id_post';
const POST_AUTHOR = 'nome_autore_post';
const POST_BLOG = 'id_blog_appartenenza';
const POST_TITLE = 'titolo_post';
const POST_CREATION = 'creazione';
const POST_LAST_EDIT = 'ultima_modifica';
const POST_TEXT = 'contenuto_post';
const POST_IMAGE = 'immagine_post';
const POST_COMMENTS_NUMBER = 'numero_commenti';

// Sezione dei COMMENTI
const COMMENT_TABLE = 'Commenti';
const COMMENT_ID = 'id_commento';
const COMMENT_AUTHOR = 'nome_autore_commento';
const COMMENT_POST = 'id_post_commentato';
const COMMENT_CREATION_DATE = 'creazione_commento';
const COMMENT_LAST_EDIT = 'ultima_modifica';
const COMMENT_TEXT = 'contenuto';

// Sezione RISPOSTE COMMENTI
const COMMENT_REPLY_TABLE = 'RisposteCommenti';
const COMMENT_REPLY_ID = 'id_commento_risposta';
const COMMENT_REPLY_MAIN_ID = 'id_commento_riferimento';

// Sezione dei FEEDBACK ai Post
const FEEDBACK_TABLE = 'FeedbackPost';
const FEEDBACK_ID = 'id_feedback';
const FEEDBACK_AUTHOR = 'username_autore_feedback';
const FEEDBACK_POST = 'id_post_riferimento';
const FEEDBACK_TYPE = 'feedback_type';

// Sezione delle ISCRIZIONI
const SUBSCRIPTION_TABLE = 'Iscrizioni';
const SUBSCRIPTION_ID = 'id_iscrizione';
const SUBSCRIPTION_BLOG_ID = 'id_blog_iscrizione';
const SUBSCRIPTION_USERNAME = 'nome_utente_iscritto';

// Sezione dei COOKIE
const SAVED_LOGINS_TABLE = 'RicordaUtenti';
const SAVED_LOGINS_ID = 'id_login_salvato';
const SAVED_LOGINS_USERNAME = 'nome_utente';
const SAVED_LOGINS_HASHED_IDENTIFIER = 'hashed_cookie_token';
const SAVED_LOGINS_EXPIRATION = 'scadenza_del_cookie';
