<?php 
function processMultipleMultipleAnswer($answer, $scoringType, $scoring, $json, $resultsDefined = array()) {
	$score = -1;
	
	//print_r($scoring);
	//print_r($answer);
	
	if(!is_array($answer) && $answer == "") {
	    $answer = array();
	}
	
	if($scoring == "by-value") {	
		foreach($json['choices'] as $choice) {
			//print_r($choice);
			if(is_array($choice)) {
				foreach($choice as $texte => $scor) {
					foreach($answer as $answ) {
						if($answ == $texte) {
							$score = $scor;
						}
					}
				}
			}
		}
		// echo "Score:".$score;
	}
	
	if($scoring == "by-entries") {
		$score = evalScoringGrid(count($answer), $json['scoring-grid']);
	}
	
	return $score;
}
?>