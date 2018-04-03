<?php 
class ScoreWriter {
    
    private $fp;
    private $typeScore;
    private $forR;
    private $bufferStr;
    private $questionnaires;
    private $filename;
    
    
    function __construct($typeScore, $questionnaires, $csv = true) {
        $this->forR = $csv;
        
        if(is_array($questionnaires)) {
            switch($typeScore) {
                case "byAspect": $filename = "scoresByAspect"; break;
                case "byIndicator": $filename = "scoresByIndicator"; break;
                default: $filename = "scoresByQuestion"; break;
            }
            
        } else {
            $filename = str_replace(".json", "", $questionnaires->getFilename())."_".$typeScore;
        } 
        $filename .= ".csv";
        
        $this->filename = $filename;      
        $this->typeScore = $typeScore;
        $this->bufferStr = "";
        $this->questionnaires = is_array($questionnaires) ? $questionnaires : array($questionnaires);
    }
    
    function getFilename() {
        return $this->filename;
    }
    
    function write() {
        if($this->forR)
            $this->fp = fopen("../".DIR_OUTPUT_SCORES."/".$this->filename, "w");
        
        switch($this->typeScore) {
            case "byQuestion":
                if($this->forR)
                    fwrite($this->fp, "id_section;id_aspect;num_question;type_score;score;nom;prenom;idinit;atelier;cluster\n");
            
                foreach($this->questionnaires as $quest)
                    $this->writeByQuestion($quest);
                break;
                
            case "byAspect":
                if($this->forR)
                    fwrite($this->fp, "id_section;id_aspect;type_score;score;nom;prenom;idinit;atelier;cluster\n");
                
                foreach($this->questionnaires as $quest)
                    $this->writeByAspect($quest);
                break;
                
            case "byIndicator":
                if($this->forR) 
                    fwrite($this->fp, "indicator;score;nom;prenom;idinit;atelier;cluster\n");
                
                foreach($this->questionnaires as $quest)
                    $this->writeByIndicator($quest);
                break;
        }
        
        if($this->forR) {
            fwrite($this->fp, $this->bufferStr);
            fclose($this->fp);
        }
        
    }
    
    function writeByIndicator($questionnaire) {
        $answers = $questionnaire->getAnswers();
        
        $scoresIndic = array(
            "object" => new ScoresIndicators()/*,
            
            "firstname" => $prenom,
            "lastname" => $nom,
            "idinit" => $idinit,
            "cluster" => $cluster,
            "atelier" => $atelier
            */
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
                $score = ($score < 0) ? " " : str_replace(".", ",", round($score, 2));
            }
            
            // $scoresIndic['lastname'].";".$scoresIndic['firstname'].";".$scoresIndic['idinit'].";".$scoresIndic['atelier'].";".$scoresIndic['cluster']
            $this->bufferStr .= $indic.";".$score.";".$this->getAdditionnalInfos($questionnaire)."\n";
            
            if(!isset($scores[$indic])) {
                //echo $scoresIndic['lastname']." ".$scoresIndic['firstname']." : indicateur ".$indic." - existe pas<br>";
            }
        }
        
    }
    
    function writeByAspect($questionnaire) {

        $scores     = array("academic" => -1, "adequacy" => -1, "importance" => -1);
        $scoresNb   = array("academic" => -1, "adequacy" => -1, "importance" => -1);

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
                
                $section = explode("_", $aspectId)[0];
                
                foreach($scores as $typeScore => $score) {
                    if($scoresNb[$typeScore] > 0) {
                        $score = $score / $scoresNb[$typeScore];
                    }
                    
                    $score = ($score < 0) ? " " : str_replace(".", ",", round($score, 2));
                    
                    $aspectId = ($questionnaire->needFixEC_16() && $aspectId == "EC_16") ? "EC_05" : $aspectId;
                    $this->bufferStr .= $section.";".$aspectId.";".$typeScore.";".$score.";".$this->getAdditionnalInfos($questionnaire)."\n";
                }
                
            } // end if
        } // end version
        
    } // end function
  
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
                    $score = ($score < 0) ? " " : str_replace(".", ",", round($score, 2));
                    $scoringType = $scoreRes["scoring-type"];
                    
                    if($scoringType == "-") continue;
                    
                    $section = explode("_", $aspectId)[0];
                    $aspectId = ($questionnaire->needFixEC_16() && $aspectId == "EC_16") ? "EC_05" : $aspectId;
                    
                    if($this->forR) {
                        $this->bufferStr .= $section.";".$aspectId.";".$numQuest.";".$scoringType.";".$score.";".$this->getAdditionnalInfos($questionnaire)."\n";
                    }
                } // end aspect
            }
        } // end version
        
    } // end function
    
    
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
    
}
