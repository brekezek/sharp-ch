<?php
class AspectFilter {
    
    public static $self;
    
    private $aspectId;
    private $questionIndex;
    private $answerType;
    private $answerIndex;
    private $answer;
    private $scope;
    private $ban;
    
    public static function setSelf($self) {
        AspectFilter::$self = $self;    
    }
    
    public function __construct($scope, $lookAt, $expectedAnswer) {
        $this->scope = $scope === NULL ? array() : $scope;
        $this->ban = array();
        
        $splitLookAt = explode(".", $lookAt);
        if(count($splitLookAt) != 2)
            throw new Exception("Le paramètre lookAt du filtre est malformé: ".$lookAt." et devrait être formé de la sorte: aspectId.questionIndex (ex. ADM_01.15)");
        
        $this->aspectId = ($splitLookAt[0] == "self") ? AspectFilter::$self : $splitLookAt[0];
        $this->questionIndex = $splitLookAt[1];
        
        if(!isset($expectedAnswer['type'])) throw new Exception("L'attribut answer->type n'existe pas");
        if(!isset($expectedAnswer['index'])) throw new Exception("L'attribut answer->value n'existe pas");
        
        $this->answerType = $expectedAnswer['type'];
        $this->answerIndex = $expectedAnswer['index'];
        $this->answer = array("given" => "", "expected" => "", "questNum" => $this->questionIndex, "aspectId" => $this->aspectId);
    }
    
    public function evaluate() {
        // Récupérer la valeur de la réponse attendue
        $answerExpected = "";
        if ($this->answerType == "choices") {
            $sectionId = explode("_", $this->aspectId)[0];
            $filepathLookAt = getAbsolutePath().DIR_VERSIONS."/".$_COOKIE['version']."/".$sectionId."/".$this->aspectId."/".$this->questionIndex.".json";
            $lookAtJson = getJSONFromFile($filepathLookAt);
            $this->answer['questNum'] = $lookAtJson['title'];
            $answerExpected = $lookAtJson['choices'][$this->answerIndex];
            $this->answer['expected'] = $answerExpected;
        }
        
        // Récupérer la réponse donnée 
        $filepathAnswers = getAbsolutePath().DIR_ANSWERS."/".$_COOKIE['filename'];
        if(file_exists($filepathAnswers)) {
            $jsonAnswers = getJSONFromFile($filepathAnswers);
            if(isset($jsonAnswers[$this->aspectId][$this->questionIndex]['answer'])) {
                $answerGiven = $jsonAnswers[$this->aspectId][$this->questionIndex]['answer'];
                
                $this->answer['given'] = $answerGiven;
                
                if($answerGiven == $answerExpected) {
                    return true;
                }
            }
        }
        return false;
    }
    
    public function getDependenciesAspectId() {
        return $this->aspectId;
    }
    
    public function getDependenciesQuestionIndex() {
        return $this->questionIndex;
    }
    
    public function getAnswerIndex() {
        return $this->answerIndex;
    }
    
    public function setBan($ban) {
        $this->ban = $ban;
    }
    public function getBan() {
        return $this->ban;    
    }
    
    public function getAnswer() {
        return $this->answer;
    }
    
    public function getScope() {
        return $this->scope;
    }
    
    
    public static function parseFilters($filters) {
        if($filters === null) return null;
        
        $filtersObjects = array();
        foreach($filters as $filter) {
            if(!isset($filter['dependencies'])) return null;
            
            $dependencies = $filter['dependencies'];
            if(is_array($dependencies)) {
                foreach($dependencies as $dependency) {
                    if(isset($dependency['lookAt']) && isset($dependency['expectedAnswer'])) {
                        $obj = new AspectFilter(isset($filter['scope']) ? $filter['scope'] : null, $dependency['lookAt'], $dependency['expectedAnswer']);
                        if(isset($filter['ban'])) {
                            $obj->setBan($filter['ban']);
                        }
                        $filtersObjects[] = $obj;
                    } else {
                        echo 'Erreur: un des filtres attaché à cet aspect est malformé: ';
                        print_r($dependency);
                    }
                }
            }
        }
        return $filtersObjects;
    }

    
    public static function evalFilters($filtersObj) {
        global $t;
        if($filtersObj === null) return "";
        
        $txt = "<ul class='mt-2 ml-2 mb-1'>";
        foreach($filtersObj as $f) {
            if($f->evaluate()) {
                $answer = $f->getAnswer();
                if($answer['given'] == $answer['expected']) {
                    $txt .= '<li>'.sprintf($t['item_filter_state_true'],$answer['expected'],$answer['questNum'],$answer['aspectId']).'</li>';
                } else {
                    $txt .= '<li>'.sprintf($t['item_filter_state_false'], $answer['expected'], $answer['questNum'], $answer['aspectId']).'</li>';
                }
            }
        }
        $txt.= '</ul>';
        return $txt;
    }
  
}