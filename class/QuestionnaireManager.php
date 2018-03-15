<?php
class QuestionnaireManager {
	const PATH_VERSIONS = DIR_VERSIONS;
	
	private static $_instance = null;
	
	private $version;
	private $aspects;
	private $currentIndex;
	private $filename;
	
	
	private function __construct($version) {
		$this->version = $version;
		$this->aspects = array();
		$this->currentIndex = 1;
		$this->filename = $_COOKIE['filename'];
		$this->parseVersion();
	}
	
	public static function getInstance($version) {
		if(is_null(self::$_instance)) {
			self::$_instance = new QuestionnaireManager($version);
		}
		return self::$_instance;
	}
	
	function addAspect($aspect) {
		array_push($this->aspects, $aspect);
	}
	
	function next() {
		$this->currentIndex++;
	}
	
	function previous() {
		$this->currentIndex--;
	}
	
	function getCurrentIndex() {
		return $this->currentIndex;
	}
	
	function getNumberAspects() {
		return count($this->aspects);
	}
	
	function goToAspect($index) {
		if($index > 0 && $index <= $this->getNumberAspects())
			$this->currentIndex = max(1, min($this->getNumberAspects(), $index));
	}
	
	function draw() {
		if(count($this->aspects) >= $this->currentIndex) {
			$aspectToDraw = $this->aspects[$this->currentIndex-1];
			$aspectToDraw->setCurrentIndex($this->currentIndex);
			
			echo '<form method="post" action="#">';
				$aspectToDraw->draw($this->currentIndex, count($this->aspects));
				$this->drawNavButtons();
			echo '</form>';
		}
	}
	
	function drawNavButtons() {
		global $t;
		$html = '
		<div class="bg-light clearfix rounded mb-4">';
			if($this->currentIndex > 1 && $this->currentIndex <= $this->getNumberAspects()) {
				$html.= '<button type="submit" id="prev" class="btn btn-primary float-left">'.$t['previous'].'</button>';
			}
			if($this->currentIndex < $this->getNumberAspects()) {
				$html.= '<button type="submit" id="next" class="btn btn-primary float-right">'.$t['next'].'</button>';
			} else { 
				$html.= '<button name="end" type="submit" id="end" class="btn btn-success float-right">'.$t['finish'].'</button>';
			}
		$html.='</div>';
		echo $html;
	}
	
	function drawThumbnails() {
		foreach($this->aspects as $aspect) {
			$aspect->drawThumbnail();
		}
	}
	
	function collectAnswers() {
		if(isset($_POST['answers'])) {
			
			//echo '<pre>';
			//print_r($_POST['answers']);
			
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
			} else {
				$handle = fopen($filepath, "w");
				$_POST['answers']['filename'] = $this->filename;
				$_POST['answers']['version'] = $this->version;
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
	
	function parseVersion() {
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
	
	function getAspects() {
		return $this->aspects;
	}
	
	function getColorAspectByIndex($index) {
		return $this->aspects[$index-1]->getColor();
	}
}
?>