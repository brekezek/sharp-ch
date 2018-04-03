<?php
function processTableSpecial($answers, $scoringType, $scoring, $json, $questId, $resultsDefined = array()) {
	$score = -1;
	if($questId == "ENV_09.1") {	    
		foreach($json['scoring-grid'] as $choicesStr => $scoreToAdd) {
			$choices = explode(",", $choicesStr);
			foreach($choices as $choice) {
				foreach($answers[$choice] as $answer) {
				    
					if(isset($answer['answer']) && !in_array(trim($answer['answer']), array("","0")) ) {
						$score += $scoreToAdd;
					}
				}
			}
		}
		if($score > -1) $score++;
	}
	
	if($questId == "PSP_17.1") {
		foreach($answers as $answer) {
			$str1 = "0"; $str2 = "0";
			if(trim($answer[0]["answer"]) == "1") $str1 = "1";
			if(trim($answer[1]["answer"]) == "1") $str2 = "1";
			$str = $str1 . $str2;
			$score += $json['scoring-grid'][$str];
		}
		if($score > -1) $score++;
		$score /= 3;
	}

	if($score > 10) $score = 10;
	
	//echo "score:".$score."<br>";
	//display($answers, $scoringType, $scoring, $json, $questId); 
	return $score;
}
?>