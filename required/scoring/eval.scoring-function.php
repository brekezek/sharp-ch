<?php 
function evalScoringFunction($answer, $function) {
	$score = -1;
	//echo "x = "; print_r($answer);
	//echo "<br>function : "; print_r($function); echo '<br>';
	
	$explode = explode("(", $function);
	
	$final = explode(')', $explode[count($explode)-1]);
	array_pop($final);
	array_pop($explode);
	
	$explode = array_reverse($explode);
	$operations = array();
	for($i = 0; $i < count($final); ++$i) {
		$splitArgs = explode(",", $final[$i]);
		for($j = 0; $j < count($splitArgs); ++$j) {
			if(trim($splitArgs[$j]) == "x")
				$splitArgs[$j] = $answer;
			else
				$splitArgs[$j] = trim($splitArgs[$j]);
		}
		$operations[$explode[$i]."_".$i] = $splitArgs;
	}

	$res = array();
	$i = 0;
	foreach($operations as $op => $args) {
		if(trim($args[0]) == "") {
			$args[0] = $res[$i-1];
		}
		$res[$i] = performOperation(explode("_", $op)[0], $args);
		$i++;
	}
	
	$score = $res[$i-1];
	//echo "<br>Score apres evaluation : ".$score."<br>";
	
	return $score;
}

function performOperation($operation, $args) {
	$res = 0;
	switch($operation) {
		case "multiply": 	$res = $args[0] * $args[1]; break;
		case "divide": 		$res = $args[0] / $args[1]; break;
		case "add":			$res = $args[0] + $args[1]; break;
		case "subtract":	$res = $args[0] - $args[1]; break;
	}
	return $res;
}
?>