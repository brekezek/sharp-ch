<?php 
function processTableBinary($answers, $indexCol, $scoring, $jsonCol, $questId, $resultsDefined = array()) {
	$score = -1;
	
	if($scoring == "by-entries") {
		$nbChecked = 0;
		foreach($answers as $answer) {
			if(isset($answer[$indexCol]['answer']) && trim($answer[$indexCol]['answer']) == "1") {
				$nbChecked++;
			}
		}
		$score = evalScoringGrid($nbChecked, $jsonCol['scoring-grid']);
	}
	
	if($scoring == "by-value") {
		$score = 0;
		$i = 0;
		
		$nbEntriesSet = 0;
		if(isset($jsonCol['col-based-on'])) {
			foreach($answers as $answer) {
				if(trim($answer[$jsonCol['col-based-on']]['answer']) != "") {
					if(isset($answer[$indexCol]['answer'])) {
						$answ = trim($answer[$indexCol]['answer']);
						if(isset($jsonCol['score-reversed'])) {
							$score += ($answ == "0" || $answ == "") ? 10 : 0;
						} else {
							$score += ($answ == "1") ? 10 : 0;
						}
					}
					$nbEntriesSet++;
				}
			}
		} else {		
			foreach($answers as $answer) {
				if(isset($answer[$indexCol]['answer'])) {
					$answ = trim($answer[$indexCol]['answer']);
					if(isset($jsonCol['score-reversed'])) {
						$score += ($answ == "0" || $answ == "") ? 10 : 0;
					} else {
						$score += ($answ == "1") ? 10 : 0;
					}
				}
				$i++;
			}
		}
		
		if(isset($jsonCol['scoring2']) && $jsonCol['scoring2'] == "average") {
			if($nbEntriesSet > 0) $score /= $nbEntriesSet;
		} else {
			if($score > 10) $score /= $i;
		}
	}
	
	//echo "score: ".$score."<br>";
	
	return $score;
}
?>