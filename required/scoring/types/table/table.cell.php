<?php
function processTable($answers, $typeCell, $scoring, $jsonCol, $indexCol, $questId, $resultsDefined = array()) {
	$score = -1;
	/*
	if($questId == "ENV_11.1") {
		display($answers, $indexCol, $scoring, $jsonCol, $questId); 
	} else {
		return -1;
	}
	*/
	//display($answers, $indexCol, $scoring, $jsonCol, $questId); 

	switch($typeCell) {
		case "binary":
		    $score = processTableBinary($answers, $indexCol, $scoring, $jsonCol, $questId, $resultsDefined);
		break;
		
		case "toggle":
		    $score = processTableToggle($answers, $indexCol, $scoring, $jsonCol, $questId, $resultsDefined);
		break;
		
		case "toggle_exactly_one":
		    $score = processTableToggleExactlyOne($answers, $indexCol, $scoring, $jsonCol, $questId, $resultsDefined);
		break;
		
		case "integer":
		    $score = processTableInteger($answers, $indexCol, $scoring, $jsonCol, $questId, $resultsDefined);
		break;
		
		case "text":
		    $score = processTableText($answers, $indexCol, $scoring, $jsonCol, $questId, $resultsDefined);
		break;
		
		case "choice":
		case "choice_multiple":
			if(isset($answers[1][$indexCol]['answer']) && is_array($answers[1][$indexCol]['answer'])) 
			    $score = processTableChoiceMultiple($answers, $indexCol, $scoring, $jsonCol, $questId, $resultsDefined);
			else
			    $score = processTableChoice($answers, $indexCol, $scoring, $jsonCol, $questId, $resultsDefined);
		break;
	}
	
	//echo "<br>score final : ".$score."<br>";

	return $score;
}
?>