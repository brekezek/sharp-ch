<?php 
function processMultipleOneSolution($answer, $scoringType, $scoring, $json, $questId, $resultsDefined = array()) {
	$score = -1;
	$choices = array();
	
	if(strtolower($answer) == "non-applicable")
		return -1;
	
	foreach($json['choices'] as $choice) {
		if(is_array($choice)) {
			foreach($choice as $texte => $score) {
				$choices[$texte] = $score;
			}
		}
	}
	
	if(isset($choices[$answer])) {
		$score = $choices[$answer];
	}
	
	return $score;
}
?>