<?php 
class Questionnaire {
    
    private $filepath;
    private $version;
    private $json;
    private $bufferResults;
    private $infosPerson;
    
    function __construct($filename, $version, $infosPerson) {
        $this->bufferResults = array();
        $this->filepath = getAbsolutePath().DIR_ANSWERS."/".$filename;
        $this->filename = $filename;
        $this->json = getJSONFromFile($this->filepath);
       
        $this->setPersonInfos($infosPerson);
        $this->setVersion($version);
    }
    
    public function getFilename() {
        return $this->filename;
    }
    
    private function setPersonInfos($infos) {
        $binding = array("lastname" => 2, "firstname" => 3, "uid" => 4);
        foreach($binding as $label => $index) {
            if(!isset($infos['label']) || (isset($infos[$label]) && empty($infos[$label]) && !empty($this->optInfoAdm($index)))) {
                $infos[$label] = remAccent($this->optInfoAdm($index));
            }
        }
        
        $this->infosPerson = $infos;
    }
    
    private function optInfoAdm($index) {
        return (isset($this->json['ADM_01'][$index]['answer'])) ?
            remAccent(trim($this->json['ADM_01'][$index]['answer'])) : "";
    }
    
    // Détermine la version du questionnaire utilisé
    private function setVersion($version) {
        $this->version = $version;
        
        if(!empty(getFileVersion($this->json))) {
            $this->version = getFileVersion($this->json);
        } else {
            $versions = getVersions();
            if(isGerman(getGermanAnswers(), $this->filepath)) {
                $this->version = $versions['DE'][0]['file'];
            }
        }
        
        if($this->version == null)
            echo "La version du questionnaire n'a pas pu être déterminée";
    }
    
    public function feedLive($aspectId, $numQuest, $answer) {
        if(!isset($this->json[$aspectId])) {
            $this->json[$aspectId] = array();
        }
        if(!isset($this->json[$aspectId][$numQuest])) {
            $this->json[$aspectId][$numQuest] = array();
        }
        $this->json[$aspectId][$numQuest]["answer"] = $answer;
    }
    
    public function getPersonInfos() {
        return $this->infosPerson;
    }
    
    public function getBufferResults() {
        return $this->bufferResults;
    }
    
    // Détermine si le questionnaire contient un aspect EC_05 qui est en fait EC_16
    public function needFixEC_16() {
        return !(isset($this->json['EC_05']) && isset($this->json['EC_16']));
    }
    
    public function getVersion() {
        return $this->version;
    }
    
    public function getTagsToIgnore($exclude=array()) {
        return array_diff(array("ADM_01", "filename", "meta", "version"), $exclude);
    }
    
    public function getAnswers() {
        return $this->json;
    }
    
    public function getDBId() {
        global $mysqli;
        
        $filename = "%".basename($this->getFilename());
      
        if ($stmt = $mysqli->prepare("SELECT qid FROM questionnaires WHERE file LIKE ? LIMIT 1")) {
            $stmt->bind_param("s", $filename);
            $stmt->execute();
            $stmt->bind_result($qid);
            $stmt->fetch();
            $stmt->free_result();
            return $qid; 
        } else {
            print_r($mysqli->error);
        }
        
    }
    
    public function writeDB($typeScore, $bufferData) {
        global $mysqli;
        
        $qid = $this->getDBId();
        if (!empty($qid)) {
            // Supprimer tous les scores de cette personne, avant de les réécrires
            if($stmt = $mysqli->prepare("DELETE FROM scores WHERE qid=?")) {
                $stmt->bind_param("i", $qid);
                $stmt->execute();
                $stmt->close();
            }
    
            $typesWanted = array("resilience", "academic", "importance", "indicator");
            $strValues = "";
            $aspectsList = $this->getAspectsDB();
            foreach($bufferData as $typeScore => $scoresByAspects) {
                if(in_array($typeScore, $typesWanted)) {
                    foreach($scoresByAspects as $aspect => $score) {
                        $aid = $typeScore == "indicator" ? $aspect : $aspectsList[$aspect];
                        if(trim($score) == "") $score = "NULL";
                        $strValues .= "(".$qid.", ".$aid.", '".$typeScore."', ".$score."), ";
                    }
                }
            }
            $strValues = substr($strValues, 0, -2);
            
            return $mysqli->query("INSERT INTO scores (qid, aid, type, score) VALUES ".$strValues);
            
        }
        return false;
    }
    
    public function getAspectsDB() {
        global $mysqli;
        $assoc = array();
        foreach($mysqli->query("SELECT aspectId, aid FROM label_aspects") as $row) {
            $assoc[$row['aspectId']] = $row['aid'];
        }
        return $assoc;
    }
    
    public function evalScoreForQuestion($aspectId, $numQuest) {
        $resultsDefined = $this->bufferResults;
        $fixEC_16 = $this->needFixEC_16();
        

        $answer = array();
        if(isset($this->json[$aspectId][$numQuest])) 
            $answer = $this->json[$aspectId][$numQuest];
            
        // Corrige le probleme dans la version 1.0.6 avant correction, ou EC_05 ecrasait les reponses de EC_16
        if($fixEC_16 && $aspectId == "EC_16") $aspectId = "EC_05"; 
            
        $fileToRead = getAbsolutePath().DIR_VERSIONS."/".$this->getVersion()."/".explode("_", $aspectId)[0]."/".$aspectId."/".$numQuest.".json";
        $json = getJSONFromFile($fileToRead);
        
        $questId = $aspectId.".".$numQuest;
        $questionType = $json['question-type'];
        $scoringType = isset($json['scoring-type']) ? $json['scoring-type'] : "-";
        $scoring = isset($json['scoring']) ? $json['scoring'] : "-";
       
        
        if((!isset($json['scoring']) && $questionType != "table") ) {
            return array(
                "score" => -1,
                "scoring-type" => $scoringType,
                "indicators" => array(),
                "score-for-indicators" => -1
            );
        }
        
        if(isset($json['result-define'])) {
            $newTab = array();
            foreach($answer as $answ) {
                $newTab[] = $answer['answer'];
            }
            $resultsDefined[$json['result-define']] = $newTab;
        }
        
        $scoreIndicators = -1;
        $indicators = array();
        $score = -1;
        switch($questionType) {
            case "text": // -------------------------------------------------
                break;
            
            case "text_answer": // ------------------------------------------
                if(isset($answer['answer']))
                    $score = processTextAnswer($answer['answer'], $scoringType, $scoring, $json, $questId, $resultsDefined);
                    break;
                    
            case "multiple_multiple_solution": // -----------------------------
                if(isset($answer['answer']))
                    $score = processMultipleMultipleAnswer($answer['answer'], $scoringType, $scoring, $json, $resultsDefined);
                    break;
                    
            case "multiple_one_solution": // ----------------------------------
                if(isset($answer['answer'])) {
                    $score = processMultipleOneSolution($answer['answer'], $scoringType, $scoring, $json, $questId, $resultsDefined);
                }
                break;
                
            case "binary_answer_with_comment":
            case "binary_answer": // ------------------------------------------
                if(isset($answer['answer']))
                    $score = processBinaryAnswer($answer['answer'], $scoringType, $scoring, $json, $questId, $resultsDefined);
                    break;
                    
            case "integer_answer": // ------------------------------------------
                $score = processIntegerAnswer($answer['answer'], $scoringType, $scoring, $json, $resultsDefined);
                break;
                
            case "table": // ---------------------------------------------------
                
                if(isset($json['scoring']) && $scoring == "special") {
                    $score = processTableSpecial($answer, $scoringType, $scoring, $json, $questId, $resultsDefined);
                } else {
                    $indexCol = 0;
                    $scoreTable = -1;
                    $nbScoredCols = 0;
                    foreach($json['columns'] as $col) {
                        // Création d'un tableau contenant les résultats qui servent pour une autre question
                        if(isset($col['result-define'])) {
                            $newTab = array();
                            foreach($answer as $answ) {
                                if(isset($answ[$indexCol]['answer'])) {
                                    $newTab[] = $answ[$indexCol]['answer'];
                                }
                            }
                            $resultsDefined[$col['result-define']] = $newTab;
                            
                        }
                        
                        if(isset($col['scoring'])) {
                            if($scoreTable < 0) $scoreTable = 0;
                            
                            $scoreQuestion = processTable($answer, $col['type'], $col['scoring'], $col, $indexCol, $questId, $resultsDefined);
                            /*
                            if($aspectId == "PSP_08") {
                                echo $category.".".$numQuest."[".$indexCol."] = ".$scoreQuestion."<br>";
                            }
                            */
                            
                            if($scoreQuestion >= 0)
                                $nbScoredCols++;
                                
                            if(isset($col['indicateur'])) {
                                $indicators = getIndicators($col['indicateur']);
                                $scoreIndicators = $scoreQuestion;
                                
                                if($questId == "EC_03.1") {
                                    if(isset($answer[0][$indexCol]['answer']))
                                        $indicators = getIndicators(10);
                                    if(isset($answer[1][$indexCol]['answer']))
                                        $indicators = array(4,5);
                                    if(isset($answer[2][$indexCol]['answer']))
                                        $indicators = array(4,5);
                                }
                                //echo $questId."[".$indexCol."].score = ".$scoreQuestion."<br>";
                            }
                           
                            if($scoreQuestion >= 0)
                                $scoreTable += $scoreQuestion;
                        }
                        $indexCol++;
                    }
                    if($nbScoredCols > 0)
                        $score = $scoreTable / $nbScoredCols;
                }
                break;
                
            default:
                $score = -1;
                break;
                
        }
        
        // Spécial pour EC_02 : Si EC_02.1 == Non, le score de l'aspect vaut 0.
        if($aspectId == "EC_02" && $numQuest != 1) {
            if(isset($resultsDefined["EC_02.1"]) && $resultsDefined["EC_02.1"] == 0) {
                if($score >= 0) $score = 0;
            }
        }
        
        $this->bufferResults = $resultsDefined;
        
      
        
        if(is_float($score))
            $score = round($score, 2);
            
            if(isset($json['indicateur'])) {
                $indicators = getIndicators($json['indicateur']);
                $scoreIndicators = $score;
            }
            
            $score = min(10, max(-1, $score));
            
            return array(
                "score" => $score,
                "scoring-type" => $scoringType,
                "indicators" => $indicators,
                "score-for-indicators" => $scoreIndicators
            );
    }
}
?>