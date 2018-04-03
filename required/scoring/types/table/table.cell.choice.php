<?php 
function processTableChoice($answers, $indexCol, $scoring, $jsonCol, $questId, $resultsDefined = array()) {
	$score = -1;
	
	if($scoring == "by-entries") {
		$nbEntries = 0;
		foreach($answers as $answer) {
			$ignore = false;
			
			if(isset($answer[$indexCol]['answer'])) {
				$answ = trim($answer[$indexCol]['answer']);
				
				if(isset($jsonCol['ignore-choice-number'])) {
					if(is_array($jsonCol['ignore-choice-number'])) {
						foreach($jsonCol['ignore-choice-number'] as $toIgnore) {
							if($jsonCol['choices'][$toIgnore] == $answ)
								$ignore = true;
						}
					}
				}
				if($ignore) continue;
					
				if(count($jsonCol['choices']) == 3) {
					if($answ == $jsonCol['choices'][0]) { // Oui
						$nbEntries++;
					} else if($answ == $jsonCol['choices'][1]) { // Non	
					} else { // Non applicable
					}
				} else {
					foreach($jsonCol['choices'] as $choice) {
						if($answ != "" && $answ == substr($choice, 0, strlen($answ)))
							$nbEntries++;
					}
				}
			}
		}
		$score = evalScoringGrid($nbEntries, $jsonCol['scoring-grid']);
	}
	if($scoring == "by-value") {
		$nbScored = 0;
		$OuiNonProcess = false;
		
		$nbEntriesSet = 0;
		if(isset($jsonCol['col-based-on'])) {
			foreach($answers as $answer) {
				if(trim($answer[$jsonCol['col-based-on']]['answer']) != "")
					$nbEntriesSet++;
			}
		}
		
		foreach($answers as $answer) {
			if(isset($answer[$indexCol]['answer'])) {
				$answ = trim($answer[$indexCol]['answer']);
				foreach($jsonCol['choices'] as $choice) {
					if(is_array($choice)) {
						foreach($choice as $text => $s) {
							if($text == $answ) {
								if($score <= 0) $score = 0;
								$score += $s;
								$nbScored++;
							}
						}
					} else {
						$OuiNonProcess = true;
					}
				}
				
				if($OuiNonProcess) {
					if($answ == $jsonCol['choices'][0]) {
						if($score <= 0) $score = 0;
						$score += 10;
						$nbScored++;
					} else if(trim($answ) == $jsonCol['choices'][1]) {
						$nbScored++;
					}
				}
			}
		}
		
		if($nbEntriesSet > 0)  $score /= $nbEntriesSet;
		else if($nbScored > 0)	$score /= $nbScored;
	}
	
	if($scoring == "special") {
		if($questId == "EC_10.1") { // rang donn� � l'item education
			$rang = -1;
			$pos = 1;
			$educationTxt = $jsonCol['choices'][9];
			foreach($answers as $answer) {
				if(isset($answer[0]['answer'])) {
					$answ = trim($answer[0]['answer']);
					if($answ == $educationTxt)
						$rang = $pos;
					$pos++;
				}
			}	
			$score = ($rang < 0) ? 10 : evalScoringGrid($rang, $jsonCol['scoring-grid']);			
		}
		else if($questId == "EC_03.1") {
			$nbScored = 0;
			if(isset($answers[0][$indexCol]['answer']))
				$score += ($answers[0][$indexCol]['answer'] == $jsonCol['choices'][0]) ? 10 : 0;
			if(isset($answers[1][$indexCol]['answer']))
				$score += ($answers[1][$indexCol]['answer'] == $jsonCol['choices'][1]) ? 10 : 0;
			if(isset($answers[2][$indexCol]['answer']))
				$score += ($answers[2][$indexCol]['answer'] == $jsonCol['choices'][1]) ? 10 : 0;
			
			foreach($answers as $answer) {
				if(isset($answer[$indexCol]['answer'])) {
					if($answer[$indexCol]['answer'] != $jsonCol['choices'][2]) $nbScored++;
				}
			}
			if($nbScored > 0) {
				$score++; // Pour passer de -1 � 0
				$score /= $nbScored;
			}
		}
		else {
			//display($answers, $indexCol, $scoring, $jsonCol, $questId); 
		}
		
		//echo "Score : ".$score;
		// echo '<hr><br><br><br>';
	}
	
	return $score;
}
?>