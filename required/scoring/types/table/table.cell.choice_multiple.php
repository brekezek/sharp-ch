<?php 
function processTableChoiceMultiple($answers, $indexCol, $scoring, $jsonCol, $questId, $resultsDefined = array()) {
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
				foreach($answer[$indexCol]['answer'] as $choice) {
					$answ = trim($choice);
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
				$nbChoices = count($answer[$indexCol]['answer']);
				if($nbChoices > 0) $score /= $nbChoices;
			}
		}
		
		if($nbEntriesSet > 0)  $score /= $nbEntriesSet;
		else if($nbScored > 0)	$score /= $nbScored;
	}
	
	return $score;
}
?>