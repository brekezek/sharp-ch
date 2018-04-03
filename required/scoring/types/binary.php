<?php 
function processBinaryAnswer($answer, $scoringType, $scoring, $json, $questId, $resultsDefined = array(), $live = false) {
    $score = -1;
	
	$answer = parseBinaryAnswer($answer, $json['choices']);
	
	if($live) {
	    $resultsDefined = getResultDefined();
	}

	// que des "by-value" pour cette catégorie
	if($scoring == "by-value") {
	    
		foreach($json['choices'] as $choice) {
			if(is_array($choice)) {
				foreach($choice as $texte => $scor) {
					if($answer == $texte) {
						$score = $scor;
					}
				}
			} else {
				$score = ($answer == $json['choices'][0]) ? 10 : 0;
			}
		}
	}
	
	if($questId == "EC_02.1") {
	    if($live) {
	       addToResultDefined(($answer == $json['choices'][1]) ? 0 : 1, "EC_02.1");
	    } else {
	        $resultsDefined["EC_02.1"] = ($answer == $json['choices'][1]) ? 0 : 1;
	    }
	}
	
	if($questId == "ENV_11.2") {
		$result = 0;
		
		if($answer == $json['choices'][0]) $result++;
		
		if(!isset($resultsDefined[$json['result-required']]))
		    return -1;
		
		if(parseBinaryAnswer($resultsDefined[$json['result-required']][0], $json['choices']) == $json['choices'][0]) $result++;
		
		$score = evalScoringGrid($result, $json['scoring-grid']);
		//display($answer, $scoringType, $scoring, $json, $questId);
	}
	
	if($questId == "ENV_11.7") {
		//display($answer, $scoringType, $scoring, $json, $questId);
		
		$nbRes = 0;
		
		if(!isset($resultsDefined[$json['result-required']]))
		    return -1;
		
		$prevQuestions = $resultsDefined[$json['result-required']];
		array_pop($prevQuestions); // On supprime le résultat de la réponse pour "Autre", car sinon on calcul que cette réponse est à non lorsqu'elle n'est en fait pas touchée.
		foreach($prevQuestions as $prevQuest) {
			$res = ($answer == $json['choices'][0]) ? 1 : 0;
			if($prevQuest == 1) $res++;
			$score += evalScoringGrid($res, $json['scoring-grid']);
			$nbRes++;
		}
		if($score > -1) $score++;
		if($nbRes > 0) $score /= $nbRes;
	}
	
	return $score;
}

function parseBinaryAnswer($answer, $choices) {
    if(is_numeric($answer) && in_array($answer, array(0,1))) {
        $answer = $choices[1-$answer];
        if(is_array($answer)) {
            foreach($answer as $text => $score)
                $answer = $text;
        }
    }
    return $answer;
}
?>