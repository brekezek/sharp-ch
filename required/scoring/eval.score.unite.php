<?php 
function evalScoreInLive($aspect, $numQuest, $answer) {
    $fileToRead = "../".DIR_VERSIONS."/".$_COOKIE['version']."/".explode("_", $aspect)[0]."/".$aspect."/".$numQuest.".json";
	$json = getJSONFromFile($fileToRead);

	$questionType = $json['question-type'];
	$scoringType = isset($json['scoring-type']) ? $json['scoring-type'] : "-";
	$scoring = isset($json['scoring']) ? $json['scoring'] : "-";
	
	if(!isset($json['scoring']) && $questionType != "table") {
		return array(
			"score" => -1,
			"scoring-type" => $scoringType,
		);
	}

	$questId = $aspect.".".$numQuest;
    
	if(isset($json['result-define'])) {
		addToResultDefined($answer, $json['result-define']);
	}

	$scoreIndicators = -1;
	$indicators = array();
	$score = -1;
	$live = true;
	
	switch($questionType) {

		case "text_answer":
			$score = processTextAnswer($answer, $scoringType, $scoring, $json, $questId, array(), $live);
		break;
		
		case "multiple_multiple_solution":
			$score = processMultipleMultipleAnswer($answer, $scoringType, $scoring, $json);
		break;
		
		case "multiple_one_solution":
			$score = processMultipleOneSolution($answer, $scoringType, $scoring, $json);
		break;
		
		case "binary_answer_with_comment":
		case "binary_answer":
			$score = processBinaryAnswer($answer, $scoringType, $scoring, $json, $questId, array(), $live);
		break;
		
		case "integer_answer":
			$score = processIntegerAnswer($answer, $scoringType, $scoring, $json);
		break;
		
		// ------------------------------------------
		case "text": 
		case "table": 
		default: break;
		
	}

	// Spécial pour EC_02 : Si EC_02.1 == Non, le score de l'aspect vaut 0.
	if($aspect == "EC_02" && $numQuest != 1) {
		if(isset(getResultDefined()["EC_02.1"]) && getResultDefined()["EC_02.1"] == 0) {
			if($score >= 0) $score = 0;
		}
	}
	
	if(is_float($score)) 
		$score = round($score, 2);
	
	$score = min(10, max(-1, $score));
	
	
	if($questionType != "table") {
	    return array(
	        "score" => $score,
	        "scoring-type" => $scoringType
	    );
	}	
	
}

function addToResultDefined($value, $key = "") {
    if(isset($_SESSION['resultsDefined'])){
        $current = unserialize($_SESSION['resultsDefined']);
        if(empty($key)) $current[] = $value;
        else            $current[$key] = $value;
        $_SESSION['resultsDefined'] = serialize($current);
    } else {
        echo "error: session resultsDefined doesn't exist";
    }
}
function getResultDefined() {
    if(isset($_SESSION['resultsDefined'])) {
        return unserialize($_SESSION['resultsDefined']);
    } else {
        echo 'session doesn not exist';
    }
    return array();
}
?>