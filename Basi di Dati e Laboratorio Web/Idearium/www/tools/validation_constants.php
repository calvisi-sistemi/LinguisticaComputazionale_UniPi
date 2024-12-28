<?php
/**
 * Costanti di validazione:
 *  - Usate nel validare un input (IMAGE_MAX_SIZE, MINIMUM_PASSWORD_LENGHT...)
 *  - Usate per specificare con comodità pattern attraverso le regex o altri formati
 *  - Usate per riferirsi in maniera sintetica ad alcuni percorsi del filesystem
 */

const IMAGE_MAX_SIZE = 2097152; // 2 MB
const AVATAR_DEFAULT_NAME = 'default.svg';

const USER_USERNAME_PATTERN = '/^[a-z0-9]+$/';
const USER_COMPLETE_NAME_PATTERN = '/^[a-zA-Z ]+$/';
const USER_EMAIL_PATTERN = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';

const MINIMUM_PASSWORD_LENGHT = 8;

const MAX_BIO_LENGHT = 200;

const DATETIME_ISO_FORMAT = 'Y-m-d\TH:i:s';
const DATETIME_READABLE_FORMAT = 'j F Y H:i:s';

const HASH_ALGORITHM_FOR_IMAGE_NAMES = 'sha1'; // Lo SHA1 espone al rischio di mancata unicità, ma lo SHA256 produce nomi di file troppo lunghi per Windows.

const GOOD_FEEDBACK = 1;
const BAD_FEEDBACK = -1;

const SUBSCRIBED = true;
const NOT_SUBSCRIBED = false;

// Sezioni dell'elenco di blog in user.php
const OWN_BLOGS_SECTION = 1;
const AS_COAUTHOR_SECTION = 2;
const SUBSCRIPTION_SECTION = 3;

const SUITABLE_TABLES_FOR_IMAGES = [
    USER_TABLE,
    BLOG_TABLE,
    POST_TABLE
];

const HOME_PAGE = 'index.php';
const LOGIN_PAGE = 'login.php';
const SIGNUP_PAGE = 'signup.php';

const PAGES_THAT_CAN_BE_VISITED_WITHOUT_LOGIN = [
    HOME_PAGE,
    LOGIN_PAGE,
    SIGNUP_PAGE
];
const DEFAULT_REDIRECT_DESTINATION = HOME_PAGE;

const IMAGE_DIRECTORY = 'images' . DIRECTORY_SEPARATOR;
const IMAGE_DIRECTORY_FROM_TOOLS_POV = '..' . DIRECTORY_SEPARATOR . IMAGE_DIRECTORY;

/**
 * Chiavi di un array rappresentante le informazioni di un post
 * @var array
*/
const POST_KEYS = [
    POST_TITLE,
    POST_AUTHOR,
    POST_BLOG,
    POST_COMMENTS_NUMBER,
    POST_CREATION,
    POST_ID,
    POST_IMAGE,
    POST_LAST_EDIT,
    POST_TEXT,
    POST_CREATION,
    POST_LAST_EDIT,
    POST_IMAGE
];