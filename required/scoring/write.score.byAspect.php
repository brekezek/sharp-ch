<?php 
function writeScoreByAspect($fp, $category, $jsonAspect) {
	global $fixEC_16;
	
	$scores = array();
	$scoresNb = array();
	
	foreach($jsonAspect as $numQuest => $answer) {
		$scoreRes = evalScoreForQuestion($category, $numQuest, $answer);
		
		$score = $scoreRes["score"];
		$scoringType = $scoreRes["scoring-type"];
		
		if($score >= 0) {
			if(!isset($scores[$scoringType])) {
				$scores[$scoringType] = $score;
				$scoresNb[$scoringType] = 1;
			} else {
				$scores[$scoringType] += $score;
				$scoresNb[$scoringType]++;
			}
		}
	}
	
	foreach($scores as $typeScore => $score) {
		if($scoresNb[$typeScore] > 0) {
			$score = $score / $scoresNb[$typeScore];
		} 
		$score =  str_replace(".", ",", round($score, 2));
		$toWrite = (($fixEC_16 && $category == "EC_16") ? "EC_05" : $category).";".$typeScore.";".$score."\n";		
		//echo $toWrite;
		fwrite($fp, $toWrite);		
	}
}
?>