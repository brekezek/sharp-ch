<?php 
require_once('required/common.php');

if(isset($_POST['json'])) {
	
	//$_POST['json']= '{"answers[PSP_04][1][answer]" : "Non","answers[PSP_04][1][answer]" : "Non","answers[PSP_04][3][answer]" : "Oui","answers[PSP_04][3][answer]" : "Oui","answers[PSP_04][3][comment]" : "-","answers[PSP_04][5][answer]" : "Un peu","answers[PSP_04][6][answer]" : "Très élevée"}';
	$finalArray = array();
	$json = json_decode(stripslashes($_POST['json']), true);
	
	if(is_array($json)) {
		// Format the data received
		foreach($json as $key => $value) {
			$name = explode('][', str_replace('answers', '', $key));
			
			$aspectId = str_replace(array("[","]"), "", $name[0]);
			$questNb = str_replace(array("[","]"), "", $name[1]);
			$typeAnswer = str_replace(array("[","]"), "", $name[2]);
			
			echo $typeAnswer."<br>";
			
			if(!isset($finalArray[$aspectId])) {
				$finalArray[$aspectId] = array();
				$finalArray[$aspectId][$questNb] = array();
			}
			$finalArray[$aspectId][$questNb][$typeAnswer] = $value;
		}
		//echo '<pre>';
		//print_r($finalArray);
		
		// update the object to be written
		$filepath = DIR_ANSWERS."/".$_COOKIE['filename'];	
		if(file_exists($filepath)) {
			$toEncode = getJSONFromFile($filepath);
			if(is_array($toEncode)) {
				$handle = fopen($filepath, "w+");
				foreach($finalArray as $id => $answers) {
					$toEncode[$id] = $answers;
				}
			} else {
				foreach($_COOKIE as $c) {
					unset($c);
				}
			}
		} else {
			$handle = fopen($filepath, "w");
			$_POST['answers']['filename'] = $_COOKIE['filename'];
			$_POST['answers']['version'] = $_COOKIE['version'];
			$toEncode = $finalArray;
		}
		
		// json stringify the updated object
		$json = json_encode($toEncode, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
		
		// write the result
		if(is_array($toEncode)) {
			fwrite($handle, $json);
		}

		fclose($handle);
	}
}
?>