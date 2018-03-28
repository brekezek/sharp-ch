<?php
include_once("login.redirect.php");


$repondants = array();
$hidden = array();

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
	//$infos['Download'] = '<a class="btn btn-success btn-sm" href="download.php?file='.DIR_ANSWERS."/".$quest.'">Down <span class="oi oi-cloud-download ml-1"></span></a>';
	$infos['Consulter'] = '<a class="btn btn-primary text-white btn-sm reviewFile" data-file='.$quest.'">Afficher <span class="oi oi-eye ml-1"></span></a>';
	$infos['Score'] = '<a class="btn btn-danger text-white btn-sm" data-file='.$quest.'">Scores <span class="oi oi-bar-chart ml-1"></span></a>';
    
	$name = remAccent(mb_strtolower($infos["Nom"]."_".$infos["Prénom"]));
	if(strlen($name) < 3)
	    $name = "";
	
	unset($json['filename']);
	$fileContent = json_encode($json);
	$hash = sha1($fileContent);
	
	$nbInfoFilled = 0;
	foreach($infos as $key => $info) {
		if($key == "filename" || $key == "creation_date") continue;
		if(strlen(trim($info)) > 2) $nbInfoFilled++;
	}
	
	if($nbInfoFilled > 0) {
		$repondants[$hash] = $infos;
		$hidden[$hash] = array(
		    "file" => $quest,
		    "name" => $name
		);
	}
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
	return str_replace(
	    array('é','ë','è','ö','ü','ä','ù','ô','/'),
	    array('e','e','e','o','u','ae','u','o','-'),
	    $arg);
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

<div class="bg-secondary text-white p-2" id="tools" style="display:none">
	<div class="d-flex justify-content-between align-items-center">
		<div>
    		<span id="nb-selected" class="badge badge-light px-3 mr-1" style="padding: .25rem .5rem; font-size:1.1em"><span>0</span> sélectionnés</span>
    		<button id="unselect" class="btn btn-light btn-sm" style="padding: .25rem .5rem; vertical-align:baseline; line-height:1">Désélectionner</button> 
    	</div>
    	
    	<div>
    		<a class="btn btn-primary btn-sm" id="download"><span class="oi oi-cloud-download mr-1"></span> <span class="text">Télécharger</span> <span data-track-row class="badge badge-light ml-1">0</span></a>
    		<a class="btn btn-danger btn-sm" id="delete"><span class="oi oi-x mr-1"></span> <span class="text">Supprimer</span> <span data-track-row class="badge badge-light ml-1">0</span></a>
    	</div>
	</div>
</div>

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
			echo '<tr data-file="'.$hidden[$key]['file'].'" data-name="'.$hidden[$key]['name'].'">';
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

		$('body').on('click', '#repondants tr', function(e){
			if(!$(e.target).is('a')) {
    			$(this).toggleClass("active bg-dark").find('td').toggleClass('text-white');

				var nbSelected = $('#repondants tr.active').length;
    			$('#tools').toggle(nbSelected > 0);
    			$('#nb-selected span, [data-track-row]').text(nbSelected);
			}
		});

		$('#tools #unselect').click(function(){
			$('#repondants tr.active').removeClass("active bg-dark").find("td").removeClass("text-white");
			$('#tools').toggle($('#repondants tr.active').length > 0);
		});

		$('#tools #download').click(function(){
			var selectedRows = $('#repondants tr.active');
			if(selectedRows.length == 1) {
				document.location = 'download.php?file=<?= DIR_ANSWERS ?>/'+selectedRows.attr("data-file")+"&name="+selectedRows.attr("data-name");
			} else {
				$(this).attr("disabled","disabled").find("span.text").text("Génération...");
    			var files = "";
    			selectedRows.each(function(){
    				files += $(this).attr("data-file")+":"+$(this).attr("data-name")+",";
    			});
    			$.post('pages/admin/download.questionnaires.php', { f:files }, function(resp) {
    				$('#tools #download').removeAttr("disabled").find("span.text").text("Télécharger");
					if(resp == "ok") 
    					document.location = 'download.php?file=pages/admin/questionnaires.zip';
					else
						alert(resp);
    			});
			}
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
