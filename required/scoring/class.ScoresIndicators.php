<?php 
class ScoresIndicators {
	
	private $scoresByIndic;
	
	public function __construct() {
		$this->scoresByIndic = array();
	}
	
	public function addScore($indicator, $score) {
		if(isset($this->scoresByIndic[$indicator])) 
			$this->scoresByIndic[$indicator][] = $score;
		else
			$this->scoresByIndic[$indicator] = array($score);
	}
	
	public function addScoreToIndicators($indicators, $score) {
		foreach($indicators as $indicator) {
			$this->addScore($indicator, $score);
		}
	}
	
	public function getScores() {
		$indicators = array();
		foreach($this->scoresByIndic as $indic => $scoresArray) {	
			$indicators[$indic] = array_sum($scoresArray); 
	
			if(count($scoresArray) > 0)
				$indicators[$indic] /= count($scoresArray);
		}
		return $indicators;
	}
}
?>