<?php 
class ScoreWriter {
    
    private $fp;
    private $typeScore;
    private $output;
    private $bufferStr;
    private $bufferData;
    private $questionnaires;
    private $filename;
    
    function __construct($typeScore, $questionnaires, $output = "csv") {
        if(!in_array($output, array("csv", "print", "db"))){
            throw new Exception("Ce type de sortie n'est pas prévu par l'application.");
        }
        if(!in_array($typeScore, array("byQuestion", "byAspect", "bySection", "byIndicator", "resilience", "db_all", "csv_answers"))){
            throw new Exception("Le type de score donné n'est pas reconnu.");
        }
        if($typeScore == "db_all" && $output != "db") {
            throw new Exception("Le type db_all n'est possible qu'avec la db en sortie");
        }
        if($typeScore == "csv_answers" && $output == "db") {
            throw new Exception("Le type csv_answers n'est possible qu'avec un fichier csv ou l'écran comme buffer de sortie");
        }
        
        $this->bufferStr = "";
        $this->bufferData = array();
        if($typeScore == "db_all") {
            $this->bufferData['resilience'] = array();
            $this->bufferData['importance'] = array();
            $this->bufferData['academic'] = array();
            $this->bufferData['indicator'] = array();
        }
        $this->output = $output;
        
        if(is_array($questionnaires)) {
            switch($typeScore) {
                case "byAspect": $filename = "scoresByAspect"; break;
                case "bySection": $filename = "scoresBySection"; break;
                case "byIndicator": $filename = "scoresByIndicator"; break;
                case "resilience": $filename = "resilience"; break;
                case "csv_answers": $filename = "answers-extracted"; break;
                default: $filename = "scoresByQuestion"; break;
            }
        } else {
            $filename = str_replace(".json", "", basename($questionnaires->getFilename()))."_".$typeScore;
        } 
        $filename .= ".csv";
        
        $this->filename = $filename;      
        $this->typeScore = $typeScore;
        $this->questionnaires = is_array($questionnaires) ? $questionnaires : array($questionnaires);
    }
    
    function getFilename() {
        return $this->filename;
    }
    
    function write() {

        if($this->output == "csv") {
            $this->fp = fopen("../".DIR_OUTPUT_SCORES."/".$this->filename, "w");
            fprintf( $this->fp, "\xEF\xBB\xBF");
        }
        switch($this->typeScore) {
            case "byQuestion":
                if($this->output == "csv")
                    fwrite($this->fp, "id_section;id_aspect;num_question;type_score;score;".$this->getAdditionnalHeader()."\n");
            
                foreach($this->questionnaires as $quest)
                    $this->writeByQuestion($quest);
                break;
                
            case "byAspect":
                if($this->output == "csv")
                    fwrite($this->fp, "id_section;id_aspect;type_score;score;".$this->getAdditionnalHeader()."\n");
                
                foreach($this->questionnaires as $quest)
                    $this->writeByAspect($quest);
                break;
            
            case "bySection":
                if($this->output == "csv")
                    fwrite($this->fp, "id_section;score;".$this->getAdditionnalHeader()."\n");
                    
                foreach($this->questionnaires as $quest)
                    $this->writeBySection($quest);
                break;
                            
            case "byIndicator":
                if($this->output == "csv") 
                    fwrite($this->fp, "indicator;score;".$this->getAdditionnalHeader()."\n");
                
                foreach($this->questionnaires as $quest)
                    $this->writeByIndicator($quest);
                break;
            
            case "resilience":
                if($this->output == "csv")
                    fwrite($this->fp, "id_aspect;score;".$this->getAdditionnalHeader()."\n");
                    
                foreach($this->questionnaires as $quest)
                    $this->writeResilience($quest);
                break;
            
            case "db_all":
                if($this->output == "db") {
                    foreach($this->questionnaires as $quest) {
                        $this->writeByAspect($quest);
                        $this->writeResilience($quest);
                        $this->writeByIndicator($quest);
                    }
                }
            break;
            
            case "csv_answers": 
                if($this->output == "csv")
                    fwrite($this->fp, "id_section;id_aspect;questionNum;intitulé;answer;".$this->getAdditionnalheader()."\n");
                    
                foreach($this->questionnaires as $quest)
                    $this->writeAnswers($quest);
            break;
        }
        
        if(trim($this->bufferStr) == "" && in_array($this->output, array("csv", "print"))) {
            echo "Aucun score ne peut être calculé sur la base des données reçues. Le questionnaire n'est peut être pas complet.";
        }
        
        if($this->output == "csv") {
            fwrite($this->fp, $this->bufferStr);
            fclose($this->fp);
        } else if($this->output == "print") {
            $lines = explode("\n", $this->bufferStr);
            echo '<table style="border:1px solid #ccc; border-collapse: collapse">';
            foreach($lines as $line) {
                if(trim($line) == "") continue;
                echo '<tr>';
                if(substr($line, -1) == ";") $line = substr($line, 0, -1);
                $cols = explode(";", $line);
                $i = 0;
                foreach($cols as $col) {
                    echo '<td style="border:1px solid #ccc; padding: 2px 4px">'.$col.'</td>';
                    $i++;
                }
                echo '</tr>';
            }
            echo '</table>';
        }
        
    }
    
    
    function writeAnswers($questionnaire) {
        
        $answers = $questionnaire->getAnswers();
        foreach(getAspectsList($questionnaire->getVersion()) as $aspectId) {
            // Ignore les tags qui ne sont pas des aspects, et les aspects non scorés
            if(in_array($aspectId, $questionnaire->getTagsToIgnore($exclude = array("ADM_01")))) continue;
            // Ignore EC_05 si le EC_05 est contenu dans EC_16 à cause d'un problème
            if($questionnaire->needFixEC_16() && $aspectId == "EC_05" && !in_array($this->output, array("csv", "print"))) continue;
            
            $section = explode("_", $aspectId)[0];
            
            $jsonAnswerAspect = isset($answers[$aspectId]) ? $answers[$aspectId] : NULL;
            
            $aspectLabel = $aspectId;
            if($questionnaire->needFixEC_16()){
                if($aspectId == "EC_16") $aspectLabel = "EC_05";
                if($aspectId == "EC_05") $aspectLabel = "EC_16";
            }
            
            $pathJSONQuestion = getAbsolutePath().DIR_VERSIONS."/".$questionnaire->getVersion()."/".$section."/".$aspectLabel;
            foreach(getQuestionsList($pathJSONQuestion) as $questNum) {
                $jsonQuestQuestion = getJSONFromFile($pathJSONQuestion."/".$questNum.".json");
                
                $intituleQuestion = $jsonQuestQuestion['title'];
                
                $jsonAnswerQuestion = ($jsonAnswerAspect !== NULL && isset($jsonAnswerAspect[$questNum])) ? $jsonAnswerAspect[$questNum] : NULL;
                
                if($jsonAnswerQuestion !== NULL && !isset($jsonAnswerQuestion['answer'])) { // Tableaux seulement
                    $answer = count($jsonAnswerQuestion) == 0 ? "" : '<TABLE>';
                } else { // Tout ce qui n'est pas tableau
                    $answer = ($jsonAnswerQuestion !== NULL && isset($jsonAnswerQuestion['answer'])) ? $jsonAnswerQuestion['answer'] : "";
                    $answer = !is_array($answer) ? $answer : implode(", ", $answer);
                    if($jsonAnswerQuestion !== NULL && isset($jsonAnswerQuestion['comment']) && !empty($jsonAnswerQuestion['comment'])) {
                        $answer .= ". ".$jsonAnswerQuestion['comment'];
                    }
                }
                
                $this->bufferStr .= $section.";".$aspectLabel.";".$questNum.";".$intituleQuestion.";".$answer.";".$this->getAdditionnalInfos($questionnaire)."\n"; 
            }
        } // end foreach aspects

    } // end function
    
    
    
    function writeByIndicator($questionnaire) {
        $answers = $questionnaire->getAnswers();
        
        $scoresIndic = array(
            "object" => new ScoresIndicators()
        );
        
        foreach(getAspectsList($questionnaire->getVersion()) as $aspectId) {
            // Ignore les tags qui ne sont pas des aspects, et les aspects non scorés
            if(in_array($aspectId, $questionnaire->getTagsToIgnore())) continue;
            // Ignore EC_05 si le EC_05 est contenu dans EC_16 à cause d'un problème
            if($questionnaire->needFixEC_16() && $aspectId == "EC_05") continue;
            
            if(isset($answers[$aspectId])) {
                $jsonAspect = $answers[$aspectId];
                
                foreach($jsonAspect as $numQuest => $jsonQuestion) {
                    $scoreRes = $questionnaire->evalScoreForQuestion($aspectId, $numQuest);
                    
                    $score = $scoreRes['score-for-indicators'];
                    if($score >= 0)
                        $scoresIndic['object']->addScoreToIndicators($scoreRes["indicators"], $score);
                } // end aspect
            }
        } // end version
        
        
        for($indic = 1; $indic <= 13; $indic++) {
            $scores = $scoresIndic['object']->getScores();
            
            $score = "";
            if(isset($scores[$indic])) {
                $score = $scores[$indic];
                $score = ($score < 0) ? " " : $this->formatScore($score);
            }
            
            if(in_array($this->output, array("csv", "print"))) {
                $this->bufferStr .= $indic.";".$score.";".$this->getAdditionnalInfos($questionnaire)."\n";
            } else {
                $this->bufferData['indicator'][$indic] = $score;
            }
            /*
            if(!isset($scores[$indic])) {
                //echo $scoresIndic['lastname']." ".$scoresIndic['firstname']." : indicateur ".$indic." - existe pas<br>";
            }*/
        }
        
        $this->writeDB($questionnaire);
    }
    
    function writeResilience($questionnaire) {
        $answers = $questionnaire->getAnswers();
        foreach(getAspectsList($questionnaire->getVersion()) as $aspectId) {
            // Ignore les tags qui ne sont pas des aspects, et les aspects non scorés
            if(in_array($aspectId, $questionnaire->getTagsToIgnore())) continue;
            // Ignore EC_05 si le EC_05 est contenu dans EC_16 à cause d'un problème
            if($questionnaire->needFixEC_16() && $aspectId == "EC_05") continue;
            
            if(isset($answers[$aspectId])) {
                $jsonAspect = $answers[$aspectId];
                
                $scores = $this->getArrayScoresByType($questionnaire, $aspectId, $jsonAspect);
                
                $scoresResilience = -1;
                $definedScore = 0;
                if($scores['academic'] >= 0) {
                    $definedScore++;
                    $scoresResilience = $scores['academic'];
                }
                if($scores['adequacy'] >= 0) {
                    if($scoresResilience < 0) $scoresResilience = 0;
                    $definedScore++;
                    $scoresResilience += $scores['adequacy'];
                }
                
                if($definedScore > 0 && $scoresResilience > 0)
                    $scoresResilience /= $definedScore;
                
                $scoresResilience = ($scoresResilience < 0) ? " " : $this->formatScore($scoresResilience);
                
                if(in_array($this->output, array("csv", "print"))) {
                    $this->bufferStr .= (($questionnaire->needFixEC_16() && $aspectId == "EC_16") ? "EC_05" : $aspectId).";".$scoresResilience.";".$this->getAdditionnalInfos($questionnaire)."\n";
                } else {
                    $aspectLabel = (($questionnaire->needFixEC_16() && $aspectId == "EC_16") ? "EC_05" : $aspectId);
                    $this->bufferData['resilience'][$aspectLabel] = $scoresResilience;
                }
                
                
            } // end if
        } // end version
        
        $this->writeDB($questionnaire);
    }
    
    
    function writeByAspect($questionnaire) {

        $answers = $questionnaire->getAnswers();
        foreach(getAspectsList($questionnaire->getVersion()) as $aspectId) {
            // Ignore les tags qui ne sont pas des aspects, et les aspects non scorés
            if(in_array($aspectId, $questionnaire->getTagsToIgnore())) continue;
            // Ignore EC_05 si le EC_05 est contenu dans EC_16 à cause d'un problème
            if($questionnaire->needFixEC_16() && $aspectId == "EC_05" && !in_array($this->output, array("csv", "print"))) continue;
            
            $section = explode("_", $aspectId)[0];
             
            $jsonAspect = isset($answers[$aspectId]) ? $answers[$aspectId] : null;   
            $scores = $this->getArrayScoresByType($questionnaire, $aspectId, $jsonAspect);
            
            foreach($scores as $typeScore => $score) {
                $score = ($score < 0) ? " " : $this->formatScore($score);
                
                $aspectLabel = $aspectId;
                if($questionnaire->needFixEC_16()){
                    if($aspectId == "EC_16") $aspectLabel = "EC_05";
                    if($aspectId == "EC_05") $aspectLabel = "EC_16";
                }
                
                if(in_array($this->output, array("csv", "print"))) {
                    $this->bufferStr .= $section.";".$aspectLabel.";".$typeScore.";".$score.";".$this->getAdditionnalInfos($questionnaire)."\n";
                } else {
                    
                    if(isset($answers[$aspectId])) {
                        if(!isset($this->bufferData[$typeScore])) {
                            $this->bufferData[$typeScore] = array();
                        }
                        $this->bufferData[$typeScore][$aspectLabel] = $score;
                    }
                }
                
            }
 
        } // end version
        
        $this->writeDB($questionnaire);
    } // end function
    
    
    
  
    function writeBySection($questionnaire) {
        
        $answers = $questionnaire->getAnswers();
        $scoresBySection = array();
        $sectionsHashmap = array();
        
        foreach(getAspectsList($questionnaire->getVersion()) as $aspectId) {
            // Ignore les tags qui ne sont pas des aspects, et les aspects non scorés
            if(in_array($aspectId, $questionnaire->getTagsToIgnore())) continue;
            // Ignore EC_05 si le EC_05 est contenu dans EC_16 à cause d'un problème
            if($questionnaire->needFixEC_16() && $aspectId == "EC_05") continue;
           
            $sectionId = explode("_", $aspectId)[0];
            
            $sectionsHashmap[$sectionId] = 1;
           
            if(isset($answers[$aspectId])) {
                $jsonAspect = $answers[$aspectId];
                
                $scores = $this->getArrayScoresByType($questionnaire, $aspectId, $jsonAspect);
                
                
                if(!isset($scoresBySection[$sectionId])) {
                    $scoresBySection[$sectionId] = array();
                }
                
                // Tri des scores dans un tableau section => array(score1, score2, ...)
                foreach($scores as $typeScore => $score) {
                    if($score >= 0) {
                        $scoresBySection[$sectionId][] = $score;
                    }
                }
                
            } // end if
        } // end version
        
        $sectionsList = array_keys($sectionsHashmap);
        
        // Moyenne du tableau des scores pour chaque section, pour avoir la moyenne par section
        foreach($scoresBySection as $section => $scoresArray) {
            $sumScores = array_sum($scoresArray);
            $nbScores = count($scoresArray);
            
            $scoresBySection[$section] = ($nbScores > 0) ? $this->formatScore($sumScores / $nbScores) : -1;
        }
        
        foreach($sectionsList as $section) {
            if(!isset($scoresBySection[$section])) {
                $scoresBySection[$section] = -1;
            }
        }
        
        foreach($scoresBySection as $section => $score) {
            if(in_array($this->output, array("csv", "print"))) {
                $score = ($score < 0) ? " " : $score;
                $this->bufferStr .= $section.";".$score.";".$this->getAdditionnalInfos($questionnaire)."\n";
            } else {
                
                //$this->bufferData[$typeScore][$aspectLabel] = $score;
            }
        }
        
        $this->writeDB($questionnaire);
    } // end function
    
    
    
    function getArrayScoresByType($questionnaire, $aspectId, $jsonAspect) {
        $scores     = array("academic" => -1, "adequacy" => -1, "importance" => -1);
        $scoresNb   = array("academic" => -1, "adequacy" => -1, "importance" => -1);
        
        if($jsonAspect === null) return $scores;
        
        foreach($jsonAspect as $numQuest => $answer) {
            $scoreRes = $questionnaire->evalScoreForQuestion($aspectId, $numQuest);
            
            $score = $scoreRes["score"];
            $scoringType = $scoreRes["scoring-type"];
            
            if($scoringType == "-") continue;
            
            if($score >= 0) {
                if(!isset($scores[$scoringType])) {
                    $scores[$scoringType] = $score;
                    $scoresNb[$scoringType] = 1;
                } else {
                    if($scores[$scoringType] < 0) {
                        $scores[$scoringType] = $score;
                        $scoresNb[$scoringType] = 1;
                    } else {
                        $scores[$scoringType] += $score;
                        $scoresNb[$scoringType]++;
                    }
                }
            }
        }
        
        foreach($scores as $typeScore => $score) {
            if($scoresNb[$typeScore] > 0) {
                $scores[$typeScore] = $score / $scoresNb[$typeScore];
            }
        }
        
        return $scores;
    }
  
    function writeByQuestion($questionnaire) {
        
        // $userInfos = $nom.";".$prenom.";".remAccent($idinit).";".$atelier.";".$cluster;
        $answers = $questionnaire->getAnswers();
       
        foreach(getAspectsList($questionnaire->getVersion()) as $aspectId) {
            // Ignore les tags qui ne sont pas des aspects, et les aspects non scorés
            if(in_array($aspectId, $questionnaire->getTagsToIgnore())) continue;
            // Ignore EC_05 si le EC_05 est contenu dans EC_16 à cause d'un problème
            if($questionnaire->needFixEC_16() && $aspectId == "EC_05" && !in_array($this->output, array("csv", "print"))) continue;
           
            $section = explode("_", $aspectId)[0];
            $aspectLabel = $aspectId;
            if($questionnaire->needFixEC_16()){
                if($aspectId == "EC_16") $aspectLabel = "EC_05";
                if($aspectId == "EC_05") $aspectLabel = "EC_16";
            }
            
            if(isset($answers[$aspectId])) {
                $jsonAspect = isset($answers[$aspectId]) ? $answers[$aspectId] : null;
                foreach($jsonAspect as $numQuest => $jsonQuestion) { 
                    $scoreRes = $questionnaire->evalScoreForQuestion($aspectId, $numQuest);
                    
                    $score = $scoreRes['score'];
                    $score = ($score < 0) ? " " : $this->formatScore($score);
                    $scoringType = $scoreRes["scoring-type"];
                    
                    if($scoringType == "-") continue;
                    
                    if(in_array($this->output, array("csv", "print"))) { 
                        $this->bufferStr .= $section.";".$aspectLabel.";".$numQuest.";".$scoringType.";".$score.";".$this->getAdditionnalInfos($questionnaire)."\n";
                    } else {
                        if(!isset($this->bufferData[$aspectLabel])) {
                            $this->bufferData[$aspectLabel] = array();
                        }
                        $this->bufferData[$aspectLabel][$numQuest] = $score;
                    }
                } // end aspect
            } else {
                if(in_array($this->output, array("csv", "print"))) {
                    foreach(getQuestionsList(getAbsolutePath().DIR_VERSIONS."/".$questionnaire->getVersion()."/".$section."/".$aspectLabel) as $numQuest) {
                        $jsonQuestion = getJSONFromFile(getAbsolutePath().DIR_VERSIONS."/".$questionnaire->getVersion()."/".$section."/".$aspectLabel."/".$numQuest.".json");
                        $scoringType = isset($jsonQuestion['scoring-type']) ? $jsonQuestion['scoring-type'] : "-";
                        $this->bufferStr .= $section.";".$aspectLabel.";".$numQuest.";".$scoringType."; ;".$this->getAdditionnalInfos($questionnaire)."\n";
                    }
                }
            }
        } // end version
        
        $this->writeDB($questionnaire);
    } // end function
    
    
    private function writeDB($quest) {
        if($this->output == "db") {
            // Ecriture du buffer en db
            $quest->writeDB($this->typeScore, $this->bufferData);
            // Flush du buffer
            $this->bufferStr = "";
        }
    }
    
    private function getAdditionnalInfos($questionnaire) {
        $infos = $questionnaire->getPersonInfos();
        $order = array("lastname", "firstname", "uid", "region", "systeme_prod", "ktidb");
        $str = "";
        
        // nom;prenom;idinit;atelier;cluster
        foreach($order as $o) {
            if(isset($infos[$o])) $str .= $infos[$o].";";
        }
        
        return $str;
    }
    
    private function getAdditionnalheader() {
        return "nom;prenom;idinit;region;systeme de production;ktidb";
    }
    
    private function formatScore($score) {
        $score = round($score, 2);
        if(in_array($this->output, array("csv","print"))) {
            return str_replace(".", ",", $score);
        }
        return $score;
    }
    
}
