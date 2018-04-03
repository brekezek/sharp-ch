<?php 
function dsaevalScoreForQuestion($category, $numQuest, $answer) {
	global $resultsDefined, $fixEC_16, $versionsDir, $questionnaireVersion;
	
	// Corrige le probleme dans la version 1.0.6 avant correction, ou EC_05 ecrasait les reponses de EC_16
	if($fixEC_16 && $category == "EC_16") $category = "EC_05";
	
	
	$fileToRead = $versionsDir."/".$questionnaireVersion."/".explode("_", $category)[0]."/".$category."/".$numQuest.".json";
	$json = getJSONFromFile($fileToRead);

	$questionType = $json['question-type'];
	$scoringType = isset($json['scoring-type']) ? $json['scoring-type'] : "-";
	$scoring = isset($json['scoring']) ? $json['scoring'] : "-";
	
	if(!isset($json['scoring']) && $questionType != "table")
		return array("score" => -1, "scoring-type" => $scoringType);

	$questId = $category.".".$numQuest;

	if(isset($json['result-define'])) {
		$newTab = array();
		foreach($answer as $answ) {
			$newTab[] = $answer['answer'];
		}
		$resultsDefined[$json['result-define']] = $newTab;
	}

	$score = -1;
	switch($questionType) {
		case "text": break;
		
		// ------------------------------------------
		
		case "text_answer":
			if(isset($answer['answer'])) 
				$score = processTextAnswer($answer['answer'], $scoringType, $scoring, $json, $questId);
		break;
		
		// ------------------------------------------
		
		case "multiple_multiple_solution":
			if(isset($answer['answer']))
				$score = processMultipleMultipleAnswer($answer['answer'], $scoringType, $scoring, $json);
		break;
		
		// ------------------------------------------
		
		case "multiple_one_solution":
			if(isset($answer['answer'])) {
				$score = processMultipleOneSolution($answer['answer'], $scoringType, $scoring, $json);
			}
		break;
		
		// ------------------------------------------
		
		case "binary_answer_with_comment":
		case "binary_answer":
			if(isset($answer['answer']))
				$score = processBinaryAnswer($answer['answer'], $scoringType, $scoring, $json, $questId);
		break;
		
		// ------------------------------------------
		case "integer_answer":
			$score = processIntegerAnswer($answer['answer'], $scoringType, $scoring, $json);
		break;
		
		// ------------------------------------------
		case "table":

			if(isset($json['scoring']) && $scoring == "special") {
				$score = processTableSpecial($answer, $scoringType, $scoring, $json, $questId);
			} else {
				$indexCol = 0;
				$scoreTable = -1;
				$nbScoredCols = 0;
				foreach($json['columns'] as $col) {
					// Création d'un tableau contenant les résultats qui servent pour une autre question
					if(isset($col['result-define'])) {
						$newTab = array();
						foreach($answer as $answ) {
							$newTab[] = $answ[$indexCol]['answer'];
						}
						$resultsDefined[$col['result-define']] = $newTab;
					}
					
					if(isset($col['scoring'])) {
						if($scoreTable < 0) $scoreTable = 0;
						
						$scoreQuestion = processTable($answer, $col['type'], $col['scoring'], $col, $indexCol, $questId);
						//echo $category.".".$numQuest."[".$indexCol."] = ".$scoreQuestion."<br>";
						if($scoreQuestion >= 0)
							$nbScoredCols++;
						
						$scoreTable += $scoreQuestion;
					}
					$indexCol++;
				}
				if($nbScoredCols > 0)
					$score = $scoreTable / $nbScoredCols;
			}
		break;
		
		default:
			$score = -1;
			break;
		
	}

	// Spécial pour EC_02 : Si EC_02.1 == Non, le score de l'aspect vaut 0.
	if($category == "EC_02" && $numQuest != 1) {
		if(isset($resultsDefined["EC_02.1"]) && $resultsDefined["EC_02.1"] == 0) {
			if($score >= 0) $score = 0;
		}
	}
	
	if(is_float($score)) 
		$score = round($score, 2);
	
	return array(
		"score" => $score,
		"scoring-type" => $scoringType
	);
}
?>