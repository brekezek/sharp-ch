<?php
class QuestionnaireManager {
	const PATH_VERSIONS = DIR_VERSIONS;
	
	private $version;
	private $aspects;
	private $currentIndex;
	
	function __construct($version) {
		$this->version = $version;
		$this->aspects = array();
		$this->currentIndex = 1;
		$this->parseVersion();
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
			$this->currentIndex = $index;
	}
	
	function draw() {
		if(count($this->aspects) >= $this->currentIndex) {
			$aspectToDraw = $this->aspects[$this->currentIndex-1];
			$aspectToDraw->setCurrentIndex($this->currentIndex);
			$aspectToDraw->draw($this->currentIndex, count($this->aspects));
		}
	}
	
	function parseVersion() {
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
			
				$listQuestions = array();
				foreach(scandir($pathAspect) as $questionFile) {
					$nb = str_replace('.json', '', $questionFile);
					if($questionFile != '.' && $questionFile != '..' && is_numeric($nb))
						$listQuestions[] = $nb;
				}
				asort($listQuestions);
				
				foreach($listQuestions as $nb) {
					$questionJSON = getJSONFromFile($pathAspect."/".$nb.".json");
					$aspect->addQuestion(new Question($nb, $questionJSON));				
				}
							
				$this->addAspect($aspect);
			}
		}
	}
	
	function getAspects() {
		return $this->aspects;
	}
}
?>