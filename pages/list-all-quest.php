<?php
include_once("required/common.php");

$repondants = array();

foreach(scanAllDir(DIR_ANSWERS) as $quest) {
    $scoringFile = file_get_contents(DIR_ANSWERS."/".$quest);
	$json = json_decode($scoringFile, true);
	
	$infos = array();
	
	$infos['Collecté par'] = optInfoAdm($json, 1);
	$infos['Prénom'] = optInfoAdm($json, 3);
	$infos['Nom'] = optInfoAdm($json, 2);
	$infos['Chef d\'exploitation'] = optInfoAdm($json, 6);
	$infos['Commune'] = optInfoAdm($json, 9); 
	$infos['Village'] = optInfoAdm($json, 10); 
	$infos['uid'] = optInfoAdm($json, 4); 
	$infos['Age'] = optInfoAdm($json, 15);
	$infos['Date de création'] = date ("d.m.Y H:i:s", filemtime(DIR_ANSWERS."/".$quest));
	$infos['Fichier'] = '<a class="btn btn-success btn-sm" href="'.DIR_ANSWERS."/".$quest.'">Télécharger</a>';
	//$infos['Fichier'] = '<a class="btn btn-success btn-sm" href="'.DIR_ANSWERS."/".$quest.'">Télécharger</a>';
	
	unset($json['filename']);
	$fileContent = json_encode($json);
	$hash = sha1($fileContent);
	
	$nbInfoFilled = 0;
	foreach($infos as $key => $info) {
		if($key == "filename" || $key == "creation_date") continue;
		if(strlen(trim($info)) > 2) $nbInfoFilled++;
	}
	
	if($nbInfoFilled > 0)
		$repondants[$hash] = $infos;
}

function hash_str($str) {
	$sel = 'asl!$Ds5Ll%w#ca*d?aS$';
	for($i = 0; $i < 10; $i++) 
		$hash = "1d!".strrev(sha1($sel.$str))."h";
	return $hash;
}

function optInfoAdm($json, $index) {
	return (isset($json['ADM_01'][$index]['answer'])) ?
		remAccent(trim($json['ADM_01'][$index]['answer'])) : "";
}

function remAccent($arg) {
	return str_replace(array('é','ë','è','ö','ü','ä','ù','ô','/'), array('e','e','e','o','u','ae','u','o','-'), $arg);
}
?>

<style>
#repondants_wrapper .row:nth-child(1) {
	background : rgba(52, 73, 94, 1.0);
	padding-top: 8px;
	color: white;
	margin-bottom: 14px;
}
</style>

<?php
$lastHash = "";
foreach($repondants as $hash => $infos)
	$lastHash = $hash;
	
echo '<table id="repondants" class="table table-striped table-hover display">';
	echo '<thead>';
		echo '<tr>';
		echo '<th>#</th>';
		foreach($repondants[$lastHash] as $key => $info) {
			echo '<th>'.$key.'</th>';
		}
		echo '</tr>';
	echo '</thead>';
	
	echo '<tbody>';
		$i = 0; 
		foreach($repondants as $key => $rep) {
			++$i;
			echo '<tr>';
				echo '<td>'.$i.'</td>';
				foreach($rep as $key => $info) {
					echo '<td>'.$info.'</td>';
				}
			echo '</tr>';
		}
	echo '</tbody>';
echo '</table>';
?>
		
