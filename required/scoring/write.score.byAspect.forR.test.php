<?php 
function writeScoreByAspectForRtest($fp, $category, $jsonAspect, $nameRepondant, $idinit, $infosParticipant) {
	global $fixEC_16;
	
	$scores = array("academic" => -1, "adequacy" => -1, "importance" => -1);
	$scoresNb = array("academic" => -1, "adequacy" => -1, "importance" => -1);
	
	if($category == "PSP_11") {
		$scores["-"] = -1;
		$scoresNb['-'] = -1;
	}
	
	foreach($jsonAspect as $numQuest => $answer) {
		$scoreRes = evalScoreForQuestiontest($category, $numQuest, $answer);
		
		$score = $scoreRes["score"];
		$scoringType = $scoreRes["scoring-type"];
		
		if($score >= 0) {
			if(!isset($scores[$scoringType])) {
				$scores[$scoringType] = $score;
				$scoresNb[$scoringType] = 1;
			} else {
				if($scores[$scoringType] < 0) {
					$scores[$scoringType] = $score;
					$scoresNb[$scoringType] = 1;
				} else {
					$scores[$scoringType] += $score;
					$scoresNb[$scoringType]++;
				}
			}
		}
	}
	
	$nameSplit = explode("_", $nameRepondant);
	$nom = $nameSplit[0];
	$prenom = isset($nameSplit[1]) ? $nameSplit[1] : "";
	$section = explode("_", $category)[0];
	$cluster = "0"; 
	$atelier = "0";
	if(count($infosParticipant) > 0) {
		$cluster = $infosParticipant['cluster'];
		$atelier = $infosParticipant['atelier'];
	}
	
	foreach($scores as $typeScore => $score) {
		if($scoresNb[$typeScore] > 0) {
			$score = $score / $scoresNb[$typeScore];
		} 
		$score =  str_replace(".", ",", round($score, 2));
		if($score < 0) $score = " ";
		if($typeScore == "adequacy" && trim($score) == "") {
			$toWrite = $section.";".(($fixEC_16 && $category == "EC_16") ? "EC_05" : $category).";".$typeScore.";".$score.";".$nom.";".$prenom.";".remAccent($idinit).";".$atelier.";".$cluster.";\n";		
			//fwrite($fp, $toWrite);		
			return $category;
		}
	}
}

?>