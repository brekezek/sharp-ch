<?php 
function evalScoringGrid($value, $grid) {
	//print_r($grid);
	
	$score = -1;
	foreach($grid as $intervalles) {
		foreach($intervalles as $interval => $scoreAttributed) {
			$splittedInterval = explode('-', $interval);
			$minVal = $splittedInterval[0];
			$maxVal = $splittedInterval[1];
			
			if($minVal == "<") $minVal = -99999;
			if($maxVal == "+") $maxVal = 999999;
			
			if($minVal <= $value && $maxVal >= $value) {
				$score = $scoreAttributed;
				//echo $value." est dans l'interval ".$interval." et le score est ".$scoreAttributed;
			}
		}
	}
	return $score;
}
?>