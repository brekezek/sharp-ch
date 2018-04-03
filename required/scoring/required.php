<?php 
function display($answers, $indexCol, $scoring, $jsonCol, $questId) {
	echo '<br><br><hr>';
	echo 'QuestID: '; print_r($questId); echo '<br>';
	echo 'indexCol: '; print_r($indexCol); echo '<br>';
	echo 'answers: '; print_r($answers); echo '<br>';
	echo 'scoring: '; print_r($scoring); echo '<br>';
	echo 'jsonCol: '; print_r($jsonCol);	echo '<hr><br><br>';
}


function isGerman($germanInfos, $questionnairePath) {
	$germanChoicesUnique = $germanInfos[1];
	$questToCheck = $germanInfos[0];

	$categories = getJSONFromFile($questionnairePath);

	$germanQuestionNb = 0;
	$questInSetChoiceConsidered = 0;
	foreach($categories as $cat => $questions) {
		if($cat == "filename") continue;

		foreach($questions as $num => $answer) {
			if(in_array($cat.".".$num, $questToCheck)) {
				if(isset($answer['answer']) && trim($answer['answer']) != "") {
					if(in_array($answer['answer'], $germanChoicesUnique)) 
						$germanQuestionNb++;
					$questInSetChoiceConsidered++;
				}
			}
		}
	}
	
	return $germanQuestionNb > ($questInSetChoiceConsidered / 2);
}

function getGermanAnswers() {
    $versionsDir = getAbsolutePath().DIR_VERSIONS;
    
	
	// Sélectionner toutes les réponses des choices du questionnaire en allemand
	// --------------------------------------------
	$versions = getVersions();
	$versionGerman = $versions['DE'][0]['file'];
	$germanChoicesList = array();
	$questToCheck = array();
	foreach(getAspectsList($versionGerman, true) as $listFiles) {
		$split = explode(".", $listFiles);
		if(count($split) < 2) continue;
		
		$cat = $split[0];
		$numQuest = $split[1];
		
		$json = getJSONFromFile($versionsDir."/".$versionGerman."/".explode("_", $cat)[0]."/".$cat."/".$numQuest.".json");
		
		if(isset($json['choices']) && $json['question-type'] == "multiple_one_solution") {
			if(count($questToCheck) < 25) 
				$questToCheck[] = $cat.".".$numQuest;

			foreach($json['choices'] as $choice) {
				if(is_array($choice)) {
					foreach($choice as $key => $score) {
						$germanChoicesList[$key] = 1;
					}
				} else {
					$germanChoicesList[$choice] = 1;
				}
			}
			
		}
	}

	$germanChoicesUnique = array();
	foreach($germanChoicesList as $choice => $_1) {
		$germanChoicesUnique[] = $choice;
	}
	// --------------------------------------------
	return array($questToCheck, $germanChoicesUnique);
}

function getAspectsList($version, $includeQuestions = false) {
    $versionsDir = getAbsolutePath().DIR_VERSIONS;
	
	$orderedAspectsList = array();
	$packages = getJSONFromFile($versionsDir."/".$version."/_meta_package.json")['order'];
	foreach($packages as $package) {
		$categories = getJSONFromFile($versionsDir."/".$version."/".$package."/_meta_category.json")['order'];
		foreach($categories as $cat) {
			$orderedAspectsList[] = $cat;
			if($includeQuestions) {
				$list = getQuestionsList($versionsDir."/".$version."/".$package."/".$cat);
				foreach($list as $num) {
					$orderedAspectsList[] = $cat.".".$num;
				}
			}
		}
	}
	return $orderedAspectsList;
}

function getQuestionsList($aspect) {
	$list = getListDir($aspect);
	$formattedList = array();
	foreach($list as $item) {
		$item = str_replace('.json', '', $item);
		if(is_numeric($item)) 
			$formattedList[] = $item;
	}
	asort($formattedList);
	return $formattedList;
}

function getListDir($dir) {
	$list = scandir($dir);
	$formattedList = array();
	foreach($list as $item) {
		if($item != '.' && $item != '..')
			$formattedList[] = $item;
	}
	return $formattedList;
}
?>