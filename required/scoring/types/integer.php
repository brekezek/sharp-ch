<?php 
function processIntegerAnswer($answer, $scoringType, $scoring, $json, $resultsDefined = array()) {
	$score = -1;
	
	if(trim($answer) == "") return -1;

	if($scoring == "by-value") {	
		if(is_numeric($answer)) {
			$score = evalScoringGrid($answer, $json['scoring-grid']);
		} else {
			$numVirgules = substr_count($answer, ",");
			if($numVirgules > 0 || strlen(trim($answer)) > 1) $numVirgules++;
			$score = evalScoringGrid($numVirgules, $json['scoring-grid']);
		}
	}
	
	if($scoring == "function") {
		$score = evalScoringFunction($answer, $json['scoring-function']);
		if($score > 10) $score = 10;
	}
	
	return $score;
}
?>