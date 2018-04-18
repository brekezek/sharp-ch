<?php
class QuestionnaireManager {
	const PATH_VERSIONS = DIR_VERSIONS;
	
	private static $_instance = null;
	private $version;
	private $aspects;
	private $currentIndex;
	private $filename;
	private $readonly;
	
	private function __construct($version) {
		$this->version = $version;
		$this->aspects = array();
		$this->currentIndex = 1;
		$this->filename = $_COOKIE['filename'];
		$this->readonly = false;
		$this->parseVersion();
	}
	
	public static function getInstance($version) {
		if(is_null(self::$_instance)) {
			self::$_instance = new QuestionnaireManager($version);
		}
		return self::$_instance;
	}
	
	public function refreshContent() {
	    $this->aspects = array();
	    $this->parseVersion();
	}
	
	public function next() {
		$this->currentIndex++;
	}
	
	public function previous() {
		$this->currentIndex--;
	}
	
	public function goToAspect($index) {
		if($index > 0 && $index <= $this->getNumberAspects())
			$this->currentIndex = max(1, min($this->getNumberAspects(), $index));
	}
	
	public function draw() {
		if(count($this->aspects) >= $this->currentIndex) {
			$aspectToDraw = $this->aspects[$this->currentIndex-1];
			$aspectToDraw->setCurrentIndex($this->currentIndex);
			$aspectToDraw->setReadOnly($this->readonly);
			
			echo '<form method="post" action="#">';
				$aspectToDraw->draw($this->currentIndex, count($this->aspects));
				$this->drawNavButtons();
			echo '</form>';
		}
	}
	
	private function drawNavButtons() {
		global $t;
		$html = '<button type="submit" id="submitHidden" tabindex="999" style="opacity:0; position:absolute; z-index: -99"></button>'.
		'<div class="bg-light clearfix rounded mb-4">';
			if($this->currentIndex > 1 && $this->currentIndex <= $this->getNumberAspects()) {
				$html.=
				'<button type="submit" id="prev" tabindex="55" class="btn btn-primary float-left">'.
				    '<span class="oi oi-chevron-left mr-2"></span>'.
				    $t['previous'].
				'</button>';
			}
			if($this->currentIndex < $this->getNumberAspects()) {
				$html.=
				'<button type="submit" id="next" class="btn btn-primary float-right">'.
				    $t['next'].
				    '<span class="oi oi-chevron-right ml-2"></span>'.
				'</button>';
			} else { 
			    if(!$this->readonly) {
    				$html.=
    				'<button name="end" type="submit" id="end" class="btn btn-success float-right">'.
    				   $t['finish'].
    				    '<span class="oi oi-check ml-2"></span>'.
    				'</button>';
			    }
			}
		$html.='</div>';
		echo $html;
	}
	
	public function drawThumbnails() {
		foreach($this->aspects as $aspect) {
			$aspect->drawThumbnail();
		}
	}
	
	public function collectAnswers() {
	    if($this->readonly) return;
	    
		if(!$this->readonly && isset($_POST['answers'])) {
		   // echo '<h1>Ecriture</h1>';
			/*
			echo '<pre>';
			print_r($_POST['answers']);
			echo '</pre>';
			*/
			$filepath = DIR_ANSWERS."/".$this->filename;
			
			if(file_exists($filepath)) {
				$toEncode = getJSONFromFile($filepath);
				if(is_array($toEncode)) {
					$handle = fopen($filepath, "w+");
					foreach($_POST['answers'] as $id => $answers) {
						$toEncode[$id] = $answers;
					}
				} else {
					foreach($_COOKIE as $c) {
						unset($c);
					}
				}
				
				if(isset($_POST['answers']['ADM_01'])) {
				    global $mysqli;
				    
				    if ($stmt = $mysqli->prepare("SELECT pid FROM questionnaires WHERE file LIKE ?")) {
				        $filename = "%".basename($this->filename);
				        $stmt->bind_param("s", $filename);
				        $stmt->execute();
				        $stmt->bind_result($pid);
				        $stmt->fetch();
				        
				        if(!empty($pid)) {
				            $lastname = strtolower(trim($this->optADM(2)));
				            $firstname = strtolower(trim($this->optADM(3)));
				            $region = $this->optADM(9);
				            $commune = $this->optADM(10);
				            
                            $stmt = null;	
				            if ($stmt = $mysqli->prepare("UPDATE participants SET firstname=?, lastname=?, region=?, commune=? WHERE pid=?")) {
				                $stmt->bind_param("sssss", $firstname, $lastname, $region, $commune, $pid);
				                $stmt->execute();
				            } 
				        }
				    }
				}
			} else {
				$handle = fopen($filepath, "w");
				$_POST['answers']['meta'] = array(
				    'filename'      =>  $this->filename,
    				'version'       =>  $this->version,
    				'creation-date' =>  time(),
    				'client-ip'     =>  getClientIP()
				);
				
				
				if(isset($_POST['answers']['ADM_01'])) {
				    global $mysqli;
				    
				    $collecte_par = $this->optADM(1);
				    $lastname = $this->optADM(2);
				    $firstname = $this->optADM(3);
				    $region = $this->optADM(9);
				    $commune = $this->optADM(10);
				    $creation_date = date('Y-m-d H:i:s');
				    
				    if(!empty(trim($firstname)) && !empty(trim($lastname))) {
    				    if ($stmt = $mysqli->prepare("SELECT pid FROM participants WHERE (firstname=? AND lastname=?) OR (lastname=? AND firstname=?)")) {
    				        $firstname = strtolower(trim($firstname));
    				        $lastname = strtolower(trim($lastname));
    				        
    				        $stmt->bind_param("ssss", $firstname, $lastname, $firstname, $lastname);
    				        $stmt->execute();
    				        $stmt->bind_result($pid);
    				        $stmt->fetch();
    				        
    				        $queryParticipant = "INSERT INTO participants (firstname, lastname, region, commune) VALUES (?,?,?,?)";
    				        if(!empty($pid)) {
    				            $queryParticipant = "UPDATE participants SET firstname=?, lastname=?, region=?, commune=?) VALUES (?,?,?,?)";
    				        }
    				        if ($stmt = $mysqli->prepare($queryParticipant)) {
    				            $stmt->bind_param("ssss", $firstname, $lastname, $region, $commune);
    				            $stmt->execute();
    				            $pid = $stmt->insert_id;
    				        }
    				        
    				        if(!empty($pid))
    				            $_POST['answers']['meta']['pid'] = $pid;
    				            
    				            if ($stmt = $mysqli->prepare("INSERT INTO questionnaires (pid, collecte_par, version, creation_date, file) VALUES (?,?,?,?,?)")) {
    				                $stmt->bind_param("sssss", $pid, $collecte_par, $this->version, $creation_date, $this->filename);
    				                $stmt->execute();
    				            }
    				    }
				    }
				}
				
				
				$toEncode = $_POST['answers'];
			}
			
			$json = json_encode($toEncode, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
			
			if(is_array($toEncode)) {
				fwrite($handle, $json);
			}

			fclose($handle);
		
			//echo '<pre>';
			//print_r($json);
		}
	}
	
	public function getCurrentIndex() {
		return $this->currentIndex;
	}
	
	public function getNumberAspects() {
		return count($this->aspects);
	}
	
	public function getAspects() {
		return $this->aspects;
	}
	
	public function getColorAspectByIndex($index) {
		return $this->aspects[$index-1]->getColor();
	}
	
	public function setReadOnly($readonly) {
	    $this->readonly = $readonly;
	}
	
	private function optADM($index) {
	    if(isset($_POST['answers']['ADM_01'][$index]['answer']))
	        return $_POST['answers']['ADM_01'][$index]['answer'];
	    return null;
	}
	
	private function addAspect($aspect) {
		array_push($this->aspects, $aspect);
	}
	
	private function parseVersion() {
		$filepath = DIR_ANSWERS."/".$this->filename;
		$jsonAnswers = array();
		if(file_exists($filepath)) {
			$jsonAnswers = getJSONFromFile($filepath);
		}
		
		$orderedAspectsList = array();
		$pathVersion = DIR_VERSIONS."/".$this->version;
		$packages = getJSONFromFile($pathVersion."/_meta_package.json")['order'];
		foreach($packages as $package) {
			$pathPackage = $pathVersion."/".$package;
			$categories = getJSONFromFile($pathPackage."/_meta_category.json");
			foreach($categories['order'] as $cat) {
				$pathAspect = $pathPackage."/".$cat;
				$aspectMeta = getJSONFromFile($pathAspect."/_meta_aspect.json");
				
				$title = $categories['title'];
				$color = new Color($categories['color']);
				$id = $aspectMeta['id'];
				$subtitle = $aspectMeta['title'];
				$img = $aspectMeta['img'];
	
				$aspect = new Aspect($id, $title, $color, $subtitle, $this->getNumberAspects()+1);
				if(isset($jsonAnswers[$id]))
					$aspect->setJSONAnswers($jsonAnswers[$id]);
			
				$listQuestions = array();
				foreach(scandir($pathAspect) as $questionFile) {
					$nb = str_replace('.json', '', $questionFile);
					if($questionFile != '.' && $questionFile != '..' && is_numeric($nb))
						$listQuestions[] = $nb;
				}
				asort($listQuestions);
				
				foreach($listQuestions as $nb) {
					$questionJSON = getJSONFromFile($pathAspect."/".$nb.".json");
					$questFactory = new QuestionFactory($nb, $questionJSON);
					$question = $questFactory->getQuestion();
					if($question != null)
						$aspect->addQuestion($question);				
				}
							
				$this->addAspect($aspect);
			}
		}
	}
	
	
}
?>