<?php 
function processTableText($answers, $indexCol, $scoring, $jsonCol, $questId, $resultsDefined = array()) {
	$score = -1;
	
	if($scoring == "by-entries") {
		$nbEntries = 0;
		$nbScored = 0;
		foreach($answers as $answer) {
			if(trim($answer[$indexCol]['answer']) != "") {
				$nbEntries += substr_count($answer[$indexCol]['answer'], ",");
				if($nbEntries > 0 || strlen(trim($answer[$indexCol]['answer'])) > 1) $nbEntries++;
			}			
		}
		if(isset($jsonCol['scoring-grid'])) {
			//echo "entries:".$nbEntries;
			$score = evalScoringGrid($nbEntries, $jsonCol['scoring-grid']);
		}
		if($questId == "EC_08.1") {
			$score = 2.5*$nbEntries;
			if($score > 10) $score = 10;
		}
	}

	return $score;
}
?>