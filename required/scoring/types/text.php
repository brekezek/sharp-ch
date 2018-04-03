<?php 
function processTextAnswer($answer, $scoringType, $scoring, $json, $questId, $resultsDefined = array(), $live = false) {
	$score = -1;
	
	if($live) {
	    $resultsDefined = getResultDefined();
	}
	
	if($scoring == "by-value") {	
		if(trim($answer) == "") return -1;
		$score = evalScoringGrid($answer, $json['scoring-grid']);
	}
	if($scoring == "by-entries") {
		$numVirgules = substr_count($answer, ",");
		if($numVirgules > 0 || strlen(trim($answer)) > 1) $numVirgules++;
		$score = evalScoringGrid($numVirgules, $json['scoring-grid']);
	}
	if($questId == "ENV_04.2") {
		if($answer == 0) return -1;
		if(!isset($resultsDefined[$json['result-required']])){
		    return -1;
		}
		$score = array_sum($resultsDefined[$json['result-required']]) / $answer * 10;
		if($score > 10) $score = 10;
	}
	
	return $score;
}
?>