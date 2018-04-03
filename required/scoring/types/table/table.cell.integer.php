<?php 
function processTableInteger($answers, $indexCol, $scoring, $jsonCol, $questId, $resultsDefined = array()) {

	$score = -1;
	
	$nbEntriesSet = 0;
	if(isset($jsonCol['col-based-on'])) {
		foreach($answers as $answer) {
			if(trim($answer[$jsonCol['col-based-on']]['answer']) != "")
				$nbEntriesSet++;
		}
	}
	
	if($scoring == "by-entries") {
		$nbEntries = 0;
		foreach($answers as $answer) {
			if(is_numeric(trim($answer[$indexCol]['answer']))) {
				$nbEntries++;
			}			
		}
		if(isset($jsonCol['scoring-grid'])) {
			$score = evalScoringGrid($nbEntries, $jsonCol['scoring-grid']);
		}
	}
	
	
	if($scoring == "by-value") {
		$nbScored = 0;
		foreach($answers as $answer) {
			if(is_numeric(trim($answer[$indexCol]['answer']))) {
				if($score == -1) $score = 0;
				if(isset($jsonCol['col-based-on'])) {
					if(trim($answer[$jsonCol['col-based-on']]['answer']) != "") {
						$score += evalScoringGrid(trim($answer[$indexCol]['answer']), $jsonCol['scoring-grid']);
					}
				} else {
					$score += evalScoringGrid(trim($answer[$indexCol]['answer']), $jsonCol['scoring-grid']);
				}
				$nbScored++;
			}			
		}
		
		if(isset($jsonCol['scoring2']) && $jsonCol['scoring2'] == "average") {
			if($nbEntriesSet > 0) $score /= $nbEntriesSet;
		} else {
			if($nbScored > 0) $score /= $nbScored;
		}
	}
	
	
	if($scoring == "function") {
		
		if($questId == "PSP_01.1") {
			$valuesRequired = array_sum($resultsDefined[$jsonCol['result-required']]);
	
			$sumX = 0;
			foreach($answers as $answer) {
				$x = trim($answer[$indexCol]['answer']);
				if(!empty($x)) $sumX += $x;
					
			}
			
			if($valuesRequired != 0) {
				if($indexCol == 5) {
					$result = $sumX / $valuesRequired;
					$score = evalScoringGrid($result, $jsonCol['scoring-range']);
				}
				
				if($indexCol == 6) {
					$score = ($sumX / $valuesRequired) * 10;
				}
			}
		} else {
			//display($answers, $indexCol, $scoring, $jsonCol, $questId); 
			// recup�rer ce qui est requis lorsqu'on a besoin d'une info suppl�mentaire
			if(isset($jsonCol['result-required'])) {
				$idxAnswer = 0;
				$definedScoring = 0;

				$nbEntriesValRequired = 0;
				if(isset($jsonCol['scoring2']) && $jsonCol['scoring2'] == "by-entries") {			
					if(isset($resultsDefined[$jsonCol['result-required']])) {
						$valuesRequired = $resultsDefined[$jsonCol['result-required']];
						foreach($valuesRequired as $val) {
							if(trim($val) != "")
								$nbEntriesValRequired++;
						}
					}
				}
				
				//echo "nbEntries in required values :".$nbEntriesValRequired;
				
				foreach($answers as $answer) {
					$x = trim($answer[$indexCol]['answer']);
					if(is_numeric($x)) {
						if(isset($jsonCol['scoring2']) && $jsonCol['scoring2'] == "by-entries") {
							$valueRequired = $nbEntriesValRequired;
						} else {
							$valueRequired = $resultsDefined[$jsonCol['result-required']][$idxAnswer];
						}
						//echo "<br><br>Valeur requise: ".$valueRequired."<br>";
						if(is_numeric($valueRequired)) {
							if(isset($jsonCol['scoring-function'])) {
								$scoringFunction = str_replace($jsonCol['result-required'], $valueRequired, $jsonCol['scoring-function']);
								$funcRes = evalScoringFunction($x, $scoringFunction);
							}
							
							if($score < 0) $score = 0;
							
							// r�cup�rer le r�sultat de la fonction selon le range
							if(isset($jsonCol['scoring-range'])) {
								$score += evalScoringGrid($funcRes, $jsonCol['scoring-range']);
							} else {
								$score += $funcRes;
							}
							
							$definedScoring++;
						}
					}
					
					$idxAnswer++;
				}
				if($definedScoring > 0)
					$score /= $definedScoring;

			} 
			if($score > 10) $score = 10;
		}
	}
	
	return $score;
}
?>