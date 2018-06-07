<?php
/* Classe composite qui contient un groupe de plusieurs objects AspectFilter
 * et permet de les évaluer en groupe avec l'aggrégateur AND */
class AspectFilterGroupAnd {
    /**
     * Array d'objects AspectFilter
     * @var Array<AspectFilter>
     */
    private $filters;
    
    public function __construct($filters = array()) {
        $this->filters = $filters;
    }
    
    public function addFilter($filter) {
        $this->filters[] = $filter;
    }
    
    public function evaluate() {
        foreach($this->filters as $f) {
            if(!$f->evaluate()) return false;
        }
        return true;
    }
    
    public function evalFilters() {
        global $t;
        $txt = "";
        
        $cond = true;
        foreach($this->filters as $f) {
            if(!$f->evaluate()) {
                $cond = false;
                break;
            }
        }
        
        if($cond) {
            $txt = '<li>'.sprintf($t['text-aspect-filter-group-and'], $this->getDependenciesAspectId()).'</li>';
        }
        
        return $txt;
    }
    
    public function getBanJSON() {
        return $this->filters[0]->getBanJSON();
    }
    
    public function getDependenciesAspectId() {
        $aspects = "";
        $arrayAspects = array();
        foreach($this->filters as $f) {
            $arrayAspects[] = $f->getDependenciesAspectId();
        }
        $arrayAspects = array_unique($arrayAspects);
        foreach($arrayAspects as $a) {
            $aspects .= $a.", ";
        }
        return substr($aspects, 0, -2);
    }
    
    public function getDependenciesQuestionIndex() {
        $indexs = '';
        foreach($this->filters as $f) {
            $indexs .= $f->getDependenciesQuestionIndex();
            if($f->isTable()) $indexs .= ".".$f->getRowIndex().".".$f->getColIndex().", ";
        }
        $indexs = substr($indexs, 0, -2);
        return '['.$indexs.']';
    }
    
    public function getScope() {
        return $this->filters[0]->getScope();
    }
    
    public function getJSONForJSValue() {
        $jsonTxt = "";
        foreach($this->filters as $f) {
            $jsonTxt .= $f->getJSONForJSValue().",";
        }
        return '['.substr($jsonTxt, 0, -1).']';
    }
}

class AspectFilter {
    
    public static $self;
    
    private $aspectId;
    private $questionIndex;
    private $rowIndex;
    private $colIndex;
    
    private $answerType;
    private $answerIndex;
    private $answer;
    private $scope;
    private $ban;
    private $isTable;
    
    public static function setSelf($self) {
        AspectFilter::$self = $self;    
    }
    
    public function __construct($scope, $lookAt, $expectedAnswer) {
        $this->scope = $scope === NULL ? array() : $scope;
        $this->ban = array();
        
        $splitLookAt = explode(".", $lookAt);
        if(count($splitLookAt) < 2 || count($splitLookAt) > 4)
            throw new Exception("Le paramètre lookAt du filtre est malformé: ".$lookAt." et devrait être formé de la sorte: aspectId.questionIndex (ex. ADM_01.15) et aspectId.questionIndex.rowIndex.colIndex (pour les tableaux)");
        
        $this->aspectId = ($splitLookAt[0] == "self") ? AspectFilter::$self : $splitLookAt[0];
        $this->questionIndex = $splitLookAt[1];
        
        if(count($splitLookAt) == 4) {
            $this->isTable = true;
            $this->rowIndex = $splitLookAt[2];
            $this->colIndex = $splitLookAt[3];
        }
        
        if(!isset($expectedAnswer['type'])) throw new Exception("L'attribut answer->type n'existe pas");
        if(!isset($expectedAnswer['index']) && !isset($expectedAnswer['value'])) {
            throw new Exception("Aucun des attributs answer->value ou index n'existe");
        }
        
        $this->answerType = $expectedAnswer['type'];
        $this->answerIndex = "";
        if($this->answerType == "value") {
            $this->answerIndex = $expectedAnswer['value'];
        } else if($this->answerType == "choices") {
            $this->answerIndex = $expectedAnswer['index'];
        }
        $this->answer = array("given" => "", "expected" => "", "questNum" => $this->questionIndex, "aspectId" => $this->aspectId);
    }
    
    public function evaluate() {
        // Récupérer la valeur de la réponse attendue
        $answerExpected = "";
        $sectionId = explode("_", $this->aspectId)[0];
        $filepathLookAt = getAbsolutePath().DIR_VERSIONS."/".$_COOKIE['version']."/".$sectionId."/".$this->aspectId."/".$this->questionIndex.".json";
        $lookAtJson = getJSONFromFile($filepathLookAt);
        $this->answer['questNum'] = $lookAtJson['title'];
        
        if ($this->answerType == "choices") {
            $answerExpected = $lookAtJson['choices'][$this->answerIndex];
            if(is_array($answerExpected)) $answerExpected = array_keys($answerExpected)[0];
        } else if($this->answerType == "value") {
            $answerExpected = $this->answerIndex;
        }
        $this->answer['expected'] = $answerExpected;
        
        // Récupérer la réponse donnée 
        $filepathAnswers = getAbsolutePath().DIR_ANSWERS."/".$_COOKIE['filename'];
        if(file_exists($filepathAnswers)) {
            $jsonAnswers = getJSONFromFile($filepathAnswers);
            if(isset($jsonAnswers[$this->aspectId][$this->questionIndex])) {
                $answerGiven = $jsonAnswers[$this->aspectId][$this->questionIndex];
                
                if(isset($answerGiven['answer'])) {
                    $answerGiven = $answerGiven['answer'];
                }
                
                if($this->isTable) {
                    if(isset($answerGiven[$this->rowIndex][$this->colIndex]['answer'])) {
                        $answerGiven = $answerGiven[$this->rowIndex][$this->colIndex]['answer'];
                    }
                    
                }
                
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
    
    public function getRowIndex() {
        return $this->rowIndex;
    }
    public function getColIndex() {
        return $this->colIndex;
    }
    
    public function isTable() {
        return $this->isTable;
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
    
    public function getBanJSON() {
        return is_array($this->getBan()) && count($this->getBan()) > 0 ? '"'.implode("\",\"", $this->getBan()).'"' : "";
    }
    
    public function getJSONForJSValue() {
        return '{"text":"'.$this->getAnswer()['expected'].'", "index":'.$this->getAnswerIndex().'}';
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
                        
                        if(isset($filter['id'])) {
                            if(!isset($filtersObjects[$filter['id']]))
                                $filtersObjects[$filter['id']] = new AspectFilterGroupAnd();
                            $filtersObjects[$filter['id']]->addFilter($obj);
                        } else {
                            $filtersObjects[] = $obj;
                        }
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
        
        $txt = "";
        foreach($filtersObj as $f) {
            if(get_class($f) == "AspectFilterGroupAnd") {
                $txt .= $f->evalFilters();
            } else {
                $condition = $f->evaluate();
                if($condition && !$f->isTable()) {
                    $answer = $f->getAnswer();
                    if($answer['given'] == $answer['expected']) {
                        $txt .= '<li>'.sprintf($t['item_filter_state_true'],$answer['expected'],$answer['questNum'],$answer['aspectId']).'</li>';
                    } else {
                        $txt .= '<li>'.sprintf($t['item_filter_state_false'], $answer['expected'], $answer['questNum'], $answer['aspectId']).'</li>';
                    }
                }
            }
        }
        
        if(trim($txt) != "") {
            $txt = "<ul class='mt-2 ml-2 mb-1'>".$txt.'</ul>';
        }
        
        return $txt;
    }
  
}