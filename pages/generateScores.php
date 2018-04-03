<?php
require_once("../required/common.php");
include_once '../required/db_connect.php';
include_once '../required/securite.fct.php';
require_once("../required/scoring/required.php");


foreach(scanAllDir("../".DIR_SCORING) as $dep) {
    include_once("../".DIR_SCORING."/".$dep);
}

sec_session_start();
$logged = login_check($mysqli);


//$_POST['data'] = "android/fr/billaud_christian.json:v-1.0.6-FR:a%3A4%3A%7Bs%3A9%3A%22firstname%22%3Bs%3A9%3A%22Christian%22%3Bs%3A8%3A%22lastname%22%3Bs%3A7%3A%22Billaud%22%3Bs%3A7%3A%22cluster%22%3Bi%3A13%3Bs%3A7%3A%22atelier%22%3Bi%3A0%3B%7D";
//$_POST['output'] = "db";

if(isset($_POST['data']) && strlen($_POST['data']) > 5) {
   
    // Traitement des input ----------------
    $personnes = processPostData();
    $typeScore = isset($_POST['typeScore']) ? $_POST['typeScore'] : "byIndicator";
    $output = isset($_POST['output']) ? $_POST['output'] : "print";
    // -------------------------------------
    
    if(count($personnes) > 1) {
        $questionnaires = array();
        foreach($personnes as $person)
            $questionnaires[] = new Questionnaire($person['file'], $person['version'], $person['infos']);
    } else {
        $person = $personnes[0];
        $questionnaires = new Questionnaire($person['file'], $person['version'], $person['infos']);
    }
    
    $scoreByQuestion = new ScoreWriter($typeScore, $questionnaires, $output);
    $scoreByQuestion->write();
    
    if($output != "db")
        echo $scoreByQuestion->getFilename();
    else
        echo 'DB updated';
} else {
    echo 'error';
}

function processPostData() {
    $questionnairesInfos = array();
    $personnes = explode(",", $_POST['data']);
    
    foreach($personnes as $p) {
        $info = explode(":", $p);
        if(count($info) >= 2) {
            $file = $info[0];
            $version = $info[1];
            
            $infos = array();
            if(count($info) >= 3) {
                $infos = unserialize(urldecode($info[2]));
            }
            
            $questionnairesInfos[] = array(
                "file"  => $file,
                "version" => (!empty($version) && $version != "?") ? $version : null,
                "infos" => $infos
            ); 
        }
    }
    return $questionnairesInfos;
}
?>