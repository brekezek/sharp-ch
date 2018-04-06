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

define('LIFE_COOKIE_QUEST_PENDING', 15);
define('LIFE_COOKIE_VERSION', 90);

define('OTHER_INPUT_TAG', "_OTHER_INPUT::");

/**
 * Détails de connexion à la base de données
 */
define("HOST", "localhost");   // hébergeur
define("USER", "sec_user");    // nom d’utilisateur de la base de données.
define("DB_NAME", "sharp");    // Le nom de la base de données.
define("SESSION_PERSIST_DAYS", 90); // Combien de temps la session admin reste active sans avoir besoin de se reconnecter

// Inclusion des mots de passes
include_once getAbsolutePath().'required/passwd.php';