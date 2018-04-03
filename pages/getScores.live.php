<?php
// retourne le score pour une question particulière
include_once '../required/common.php';
include_once '../required/db_connect.php';
include_once '../required/securite.fct.php';
require_once("../required/scoring/required.php");

foreach(scanAllDir("../".DIR_SCORING) as $dep) {
    include_once("../".DIR_SCORING."/".$dep);
}

sec_session_start();
$logged = login_check($mysqli);

if(!$logged) {
    echo 'Not logged in';
} else {

    if(isset($_POST['numQuest'], $_POST['answer'], $_POST['aspectId'])) {
        $numQuest = intval($_POST['numQuest']);
        $answer = !is_array($_POST['answer']) ? trim($_POST['answer']) : $_POST['answer'];
        $aspectId = $_POST['aspectId'];
        
        $questId = $aspectId.".".$numQuest;
        
        $scoreParts = evalScoreInLive($aspectId, $numQuest, $answer);
        //echo $questId." : ".$answer;
        //print_r($scoreParts);
        
        echo '<small>Score :</small> <span class="badge '.($scoreParts['score'] < 0 ? "badge-secondary" : "badge-success").' p-1">'.($scoreParts['score'] < 0 ? "ignoré" : $scoreParts['score']).'</span>';
    }
}
?>