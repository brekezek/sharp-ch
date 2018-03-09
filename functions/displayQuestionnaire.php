<?php 
require_once("require.php");

if(isset($_POST['id'])) {
	$id = findQuestId($_POST['id']);
	if(!is_null($id)) {
		$scoringFile = file_get_contents($feedbackDir.$id);
		$json = json_decode($scoringFile, true);
		ksort($json);
		
		echo '<pre>';
		foreach($json as $aspect => $jsonAspect) {
			if($aspect == "filename") continue;
			echo '<h3>'.$aspect.'</h3>';
			print_r($jsonAspect);
		}
	} else {
		echo "Désolé, aucun questionnaire n'existe avec cet identifiant";
	}
}

function findQuestId($hash) {
	global $feedbackDir;
	foreach(scanAllDir($feedbackDir) as $quest) {
		if(hash_str($quest) == $hash) {
			return $quest;
		}
	}
	return null;
}
?>