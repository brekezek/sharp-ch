<?php 
function processTableToggle($answers, $indexCol, $scoring, $jsonCol, $questId, $resultsDefined = array()) {
	$score = -1;
	
	if($scoring == "by-entries") {
		
		$nbChecked = 0;
		foreach($answers as $answer) {
			if(trim($answer[$indexCol]['answer']) == "1") {
				$nbChecked++;
			}
		}
		$score = evalScoringGrid($nbChecked, $jsonCol['scoring-grid']);
			
	}
	if($scoring == "by-value") {
		if(isset($jsonCol['scoring-grid'])) {
			$answerIdx = 0;
			$idChecked = array();
			foreach($answers as $answer) {
				if(trim($answer[$indexCol]['answer']) == "1") {
					$idChecked[] = $answerIdx;
				}
				$answerIdx++;
			}
			foreach($jsonCol['scoring-grid'] as $grid) {
				foreach($idChecked as $checked) {
					$score = $grid[$checked];
				}
			}
		} else {
			$score = 0;
			$i = 0;
			foreach($answers as $answer) {
				if(trim($answer[$indexCol]['answer'] == "1")) {
					$score += 10;
				}
				$i++;
			}
			if($score > 10) $score /= $i;
		}
		
		if(isset($jsonCol['scoring2']) && $jsonCol['scoring2'] == "average") {
			$score /= count($answers);
		}
	}
	
	return $score;
}
?>