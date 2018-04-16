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
        if(!in_array($typeScore, array("byQuestion", "byAspect", "byIndicator", "resilience"))){
            throw new Exception("Le type de score donné n'est pas reconnu.");
        }
        
        $this->bufferStr = "";
        $this->bufferData = array();
        $this->output = $output;
        
        if(is_array($questionnaires)) {
            switch($typeScore) {
                case "byAspect": $filename = "scoresByAspect"; break;
                case "byIndicator": $filename = "scoresByIndicator"; break;
                case "resilience": $filename = "resilience"; break;
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

        if($this->output == "csv")
            $this->fp = fopen("../".DIR_OUTPUT_SCORES."/".$this->filename, "w");
        
        switch($this->typeScore) {
            case "byQuestion":
                if($this->output == "csv")
                    fwrite($this->fp, "id_section;id_aspect;num_question;type_score;score;nom;prenom;idinit;atelier;cluster\n");
            
                foreach($this->questionnaires as $quest)
                    $this->writeByQuestion($quest);
                break;
                
            case "byAspect":
                if($this->output == "csv")
                    fwrite($this->fp, "id_section;id_aspect;type_score;score;nom;prenom;idinit;atelier;cluster\n");
                
                foreach($this->questionnaires as $quest)
                    $this->writeByAspect($quest);
                break;
                
            case "byIndicator":
                if($this->output == "csv") 
                    fwrite($this->fp, "indicator;score;nom;prenom;idinit;atelier;cluster\n");
                
                foreach($this->questionnaires as $quest)
                    $this->writeByIndicator($quest);
                break;
            
            case "resilience":
                foreach($this->questionnaires as $quest)
                    $this->writeResilience($quest);
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
                $this->bufferStr .= $indic.";".$score."\n";
            }
            
            if(!isset($scores[$indic])) {
                //echo $scoresIndic['lastname']." ".$scoresIndic['firstname']." : indicateur ".$indic." - existe pas<br>";
            }
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
                    $this->bufferData[(($questionnaire->needFixEC_16() && $aspectId == "EC_16") ? "EC_05" : $aspectId)] = $scoresResilience;
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
            if($questionnaire->needFixEC_16() && $aspectId == "EC_05") continue;
            
            /*
            if($aspectId == "PSP_11") {
                $scores["-"] = -1;
                $scoresNb['-'] = -1;
            }
            */
            
            if(isset($answers[$aspectId])) {
                $jsonAspect = $answers[$aspectId];   
                
                $scores = $this->getArrayScoresByType($questionnaire, $aspectId, $jsonAspect);
                
                $section = explode("_", $aspectId)[0];
                
                foreach($scores as $typeScore => $score) {
                    $score = ($score < 0) ? " " : $this->formatScore($score);
                    
                    if(in_array($this->output, array("csv", "print"))) {
                        $this->bufferStr .= $section.";".(($questionnaire->needFixEC_16() && $aspectId == "EC_16") ? "EC_05" : $aspectId).";".$typeScore.";".$score.";".$this->getAdditionnalInfos($questionnaire)."\n";
                    } else {
                        $aspectLabel = (($questionnaire->needFixEC_16() && $aspectId == "EC_16") ? "EC_05" : $aspectId);
                        if(!isset($this->bufferData[$aspectLabel])) {
                            $this->bufferData[$aspectLabel] = array();
                        }
                        $this->bufferData[$aspectLabel][$typeScore] = $score;
                    }
                }
                
            } // end if
        } // end version
        
        $this->writeDB($questionnaire);
    } // end function
    
    function getArrayScoresByType($questionnaire, $aspectId, $jsonAspect) {
        $scores     = array("academic" => -1, "adequacy" => -1, "importance" => -1);
        $scoresNb   = array("academic" => -1, "adequacy" => -1, "importance" => -1);
        
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
            if($questionnaire->needFixEC_16() && $aspectId == "EC_05") continue;
           
            if(isset($answers[$aspectId])) {
                $jsonAspect = $answers[$aspectId];
                
                foreach($jsonAspect as $numQuest => $jsonQuestion) { 
                    $scoreRes = $questionnaire->evalScoreForQuestion($aspectId, $numQuest);
                    
                    $score = $scoreRes['score'];
                    $score = ($score < 0) ? " " : $this->formatScore($score);
                    $scoringType = $scoreRes["scoring-type"];
                    
                    if($scoringType == "-") continue;
                    
                    $section = explode("_", $aspectId)[0];
                    
                    if(in_array($this->output, array("csv", "print"))) { 
                        $this->bufferStr .= $section.";".(($questionnaire->needFixEC_16() && $aspectId == "EC_16") ? "EC_05" : $aspectId).";".$numQuest.";".$scoringType.";".$score.";".$this->getAdditionnalInfos($questionnaire)."\n";
                    } else {
                        $aspectLabel = (($questionnaire->needFixEC_16() && $aspectId == "EC_16") ? "EC_05" : $aspectId);
                        if(!isset($this->bufferData[$aspectLabel])) {
                            $this->bufferData[$aspectLabel] = array();
                        }
                        $this->bufferData[$aspectLabel][$numQuest] = $score;
                    }
                } // end aspect
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
        $order = array("lastname", "firstname", "uid", "atelier", "cluster");
        $str = "";
        
        // nom;prenom;idinit;atelier;cluster
        foreach($order as $o) {
            if(isset($infos[$o])) $str .= $infos[$o].";";
        }
        
        return $str;
    }
    
    private function formatScore($score) {
        $score = round($score, 2);
        if(in_array($this->output, array("csv","print"))) {
            return str_replace(".", ",", $score);
        }
        return $score;
    }
    
}
