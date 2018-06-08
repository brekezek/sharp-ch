<?php 
require_once '../required/common.php';

$toConvert = array("PSP_10.4", "PSP_10.7", "PSP_14.3", "PSP_17.3", "ENV_02.1", "ENV_05.2", "EC_14.2");

foreach(scanAllDir(getAbsolutePath().DIR_ANSWERS) as $file) {
    /*
    $jsonAnswerFilepath = getAbsolutePath().DIR_ANSWERS."/".$file;
    $jsonAnswer = getJSONFromFile($jsonAnswerFilepath);
    
    foreach($toConvert as $idCode) {
        $split = explode(".", $idCode);
        $aspectId = $split[0];
        $questId = $split[1];
        $sectionId = substr($split[0], 0, strpos($split[0], "_"));
    
        if(isset($jsonAnswer[$aspectId][$questId]['answer'])) {
            $answersArray = $jsonAnswer[$aspectId][$questId]['answer'];
            
            // On récupère les infos du fichier json de la question
            $jsonQuestion = getJSONFromFile(getAbsolutePath().DIR_VERSIONS."/".$jsonAnswer['meta']['version']."/".$sectionId."/".$aspectId."/".$questId.".json");
            if(is_array($jsonQuestion['lines']) && count($answersArray) > 0) {
                $lines = $jsonQuestion['lines'];
                // -----------
    
                $reconstruct = array();          
                foreach($lines as $index => $txt) {
                    $reconstruct[$index] = array();
                    $reconstruct[$index][] = in_array($txt, $answersArray) ? array("answer" => 1) : array("answer" => 0); 
                }
                
                $others = array_diff($answersArray, $lines);
                if(count($others) > 0) {
                    foreach($others as $txt) {
                        $reconstruct[] = array(array("answer" => 1, "label" => $txt));
                    }
                }
                
                $jsonAnswer[$aspectId][$questId] = $reconstruct;
            }
        }
    }
    
    $handle = fopen($jsonAnswerFilepath, "w+");
    $json = json_encode($jsonAnswer, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    fwrite($handle, $json);
    fclose($handle);
    */
}
?>