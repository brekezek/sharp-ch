<?php 
function getAbsolutePath() {
    $absolutePath = $_SERVER['DOCUMENT_ROOT']."/";
    $part2 = str_replace($_SERVER['DOCUMENT_ROOT'], "", $_SERVER['SCRIPT_FILENAME']);
    $explode = explode("/", $part2);
    if(isset($explode[1])) {
        $absolutePath .= $explode[1]."/";
    }
    return $absolutePath;
}

define('DIR_VERSIONS', '_versions');
define('DIR_STR', 'str');
define('DIR_ANSWERS', 'feedback');
define('DIR_SCORING', 'required/scoring');
define('DIR_OUTPUT_SCORES', 'feedback/scores');
define('DIR_TEMP', 'temp');

define('LIFE_COOKIE_QUEST_PENDING', 15);
define('LIFE_COOKIE_VERSION', 365);
define('LIFE_COOKIE_LIST_QUESTS', 1100);

define('OTHER_INPUT_TAG', "_OTHER_INPUT::");

define('MIN_FEEDBACK_FILE_SIZE', 28000); // 28 kB

/**
 * Détails de connexion à la base de données
 */
define("HOST", "localhost");   // hébergeur
define("USER", "sec_user");    // nom d’utilisateur de la base de données.
define("DB_NAME", "sharp");    // Le nom de la base de données.
define("SESSION_PERSIST_DAYS", 90); // Combien de temps la session admin reste active sans avoir besoin de se reconnecter

// Inclusion des mots de passes
include_once getAbsolutePath().'required/passwd.php';