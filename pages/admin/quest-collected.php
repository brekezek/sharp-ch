<?php
include_once("login.redirect.php");


$repondants = array();

foreach(scanAllDir(DIR_ANSWERS) as $quest) {
    $scoringFile = file_get_contents(DIR_ANSWERS."/".$quest);
	$json = json_decode($scoringFile, true);
	
	$infos = array();
	
	$infos['Collecté par'] = optInfoAdm($json, 1);
	$infos['Prénom'] = optInfoAdm($json, 3);
	$infos['Nom'] = optInfoAdm($json, 2);
	$infos['Commune'] = optInfoAdm($json, 9); 
	$infos['Village'] = optInfoAdm($json, 10); 
	$infos['uid'] = optInfoAdm($json, 4); 
	//$infos['Age'] = optInfoAdm($json, 15);
	$infos['Création'] = date("d.m.y", isset($json['meta']) ? $json['meta']['creation-date'] :  filemtime(DIR_ANSWERS."/".$quest));
	$infos['Download'] = '<a class="btn btn-success btn-sm" href="download.php?file='.DIR_ANSWERS."/".$quest.'">Down <span class="oi oi-cloud-download ml-1"></span></a>';
	$infos['Consulter'] = '<a class="btn btn-primary text-white btn-sm reviewFile" data-file='.$quest.'">Afficher <span class="oi oi-eye ml-1"></span></a>';
	$infos['Score'] = '<a class="btn btn-danger text-white btn-sm" data-file='.$quest.'">Scores <span class="oi oi-bar-chart ml-1"></span></a>';
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


<div class=" justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom d-none">
	<h1 class="h2">Liste des questionnaires</h1>
</div>
<?php
$lastHash = "";
foreach($repondants as $hash => $infos)
	$lastHash = $hash;
?>	

<div style="display:none;" id="tools">outils</div>

<?php 
echo '<table id="repondants" class="table table-striped table-hover display table-sm" data-page-length="11">';
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
				echo '<td class="align-middle text-center">'.$i.'</td>';
				foreach($rep as $key => $info) {
					echo '<td class="align-middle">'.$info.'</td>';
				}
			echo '</tr>';
		}
	echo '</tbody>';
echo '</table>';
?>
<br>
		
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script  src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/fixedheader/3.1.3/js/dataTables.fixedHeader.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap4.min.css">

<script>
	$(document).ready(function() {
		deleteCookie('indexAspect');
		deleteCookie('readonly');
		
		$('#repondants').dataTable( {
			"pagingType": "full_numbers"
		});

		$('body').on('click', '#repondants tr', function(){
			$(this).toggleClass("active bg-dark").find('td').toggleClass('text-white');
			
			$('#tools').toggle($('#repondants tr.active').length > 0);
			
		});

		$('body').on('click', '.reviewFile', function(){
			var fileURL = $(this).attr("data-file");
			var lifespan = 1;
			setCookie("filename", fileURL, lifespan);
			setCookie("readonly", "true", lifespan);
			setCookie("indexAspect", 1, lifespan);
			document.location = 'index.php?readonly';
		});

		
				
	});
</script>
