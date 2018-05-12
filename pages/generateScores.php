<?php
if(!isset($included)) {
    require_once("../required/common.php");
    include_once getAbsolutePath().'required/db_connect.php';
    include_once getAbsolutePath().'required/securite.fct.php';
}

require_once(getAbsolutePath()."required/scoring/required.php");


foreach(scanAllDir(getAbsolutePath().DIR_SCORING) as $dep) {
    include_once(getAbsolutePath().DIR_SCORING."/".$dep);
}

if(!isset($included)) {
    sec_session_start();
    $logged = login_check($mysqli);
}

/*
$_POST['data']      = "f2abdf19dd0fbdfa5d2ce49bbdcfd9c0b18a9a23.json:v-1.0.6-FR:a%3A5%3A%7Bs%3A9%3A%22firstname%22%3Bs%3A9%3A%22Christian%22%3Bs%3A8%3A%22lastname%22%3Bs%3A9%3A%22Hockenjos%22%3Bs%3A12%3A%22systeme_prod%22%3Bi%3A12%3Bs%3A6%3A%22region%22%3Bi%3A1%3Bs%3A5%3A%22ktidb%22%3Bs%3A10%3A%22VD57950031%22%3B%7D";
$_POST['typeScore'] = "byIndicator";
$_POST['output']    = "print";
*/

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
    
    
    //$questionnaires->getAspectsDB();
    
    $scoreByQuestion = new ScoreWriter($typeScore, $questionnaires, $output);
    $scoreByQuestion->write();
    
    
    if($output == "csv")
        echo $scoreByQuestion->getFilename();
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