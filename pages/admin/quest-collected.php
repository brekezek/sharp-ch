<?php
include_once("login.redirect.php");


$repondants = array();
$hidden = array();



/*
foreach(scanAllDir(DIR_ANSWERS) as $quest) {
    if(strstr($quest,".json") === false) continue;
    
    $scoringFile = file_get_contents(DIR_ANSWERS."/".$quest);
	$json = json_decode($scoringFile, true);
	
	$infos = array();
	
	$infos['Collecté par'] = optInfoAdm($json, 1);
	$infos['Prénom'] = optInfoAdm($json, 3);
	$infos['Nom'] = optInfoAdm($json, 2);
	//$infos['Commune'] = optInfoAdm($json, 9); 
	$infos['Village'] = optInfoAdm($json, 10); 
	//$infos['uid'] = optInfoAdm($json, 4); 
	//$infos['Age'] = optInfoAdm($json, 15);
	$infos['Création'] = date("d.m.y", isset($json['meta']) && isset($json['meta']['creation-date']) ? $json['meta']['creation-date'] :  filemtime(DIR_ANSWERS."/".$quest));
	$infos['Version'] = isset($json['meta']) ? $json['meta']['version'] : "?";
	//$infos['Download'] = '<a class="btn btn-success btn-sm" href="download.php?file='.DIR_ANSWERS."/".$quest.'">Down <span class="oi oi-cloud-download ml-1"></span></a>';
	$infos['Consulter'] = '<a class="btn btn-primary text-white btn-sm reviewFile" data-file="'.$quest.'">Afficher <span class="oi oi-eye ml-1"></span></a>';
	$infos['Score'] = '<a class="btn btn-success text-white btn-sm generateScore" data-file="'.$quest.'">Scores <span class="oi oi-bar-chart ml-1"></span></a>';
    
	$name = remAccent(mb_strtolower($infos["Nom"]."_".$infos["Prénom"]));
	if(strlen($name) < 3)
	    $name = "";
	
    /*
    if(!isset($json['meta'])) {
        $json['meta'] = array(
            'version' => "v-1.0.4-DE",
            "filename" => $json['filename']
        );
        if(isset($json['filename'])) {
            unset($json['filename']);
        }
        
        $handle = fopen(DIR_ANSWERS."/".$quest, "w+");
        $json = json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
        fwrite($handle, $json);
        fclose($handle);
    }
	*
	    


    

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
		    "name" => $name,
		    "version" => $infos['Version']
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
*/
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
		/*
		echo '<th>#</th>';
		foreach($repondants[$lastHash] as $key => $info) {
			echo '<th>'.$key.'</th>';
		}*/
		$colsHead = array("", "Prénom", "Nom", "Village", "Collecté par", "Création", "Version", "");
		foreach($colsHead as $head) {
		    echo '<th>'.$head.'</th>';
		}
		echo '</tr>';
	echo '</thead>';
	
	echo '<tbody>';
		$i = 0; 
		if ($stmt = $mysqli->prepare(
		    "SELECT collecte_par, firstname, lastname, commune, creation_date, version, cluster, atelier, file FROM questionnaires q
            LEFT JOIN participants p ON q.pid = p.pid
            ORDER BY lastname ASC, firstname ASC")) {
                
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                
                $isFromAndroid = strstr($row['file'],"android") !== false;
                
                $source = '<span class="oi oi-globe text-success" data-toggle="tooltip" data-placement="right" title="Collecté en ligne"></span>';
                if($isFromAndroid) $source = '<span class="oi oi-tablet text-primary" data-toggle="tooltip" data-placement="right" title="Collecté sur tablette"></span>';
                
                $name = mb_strtolower(remAccent($row['lastname']."_".$row['firstname']));
                
                $infos = urlencode(serialize(array(
                    "firstname" => ucfirst($row['firstname']),
                    "lastname" => ucfirst($row['lastname']),
                    "cluster" => $row['cluster'],
                    "atelier" => $row['atelier']
                )));
                
                echo '<tr
                        data-file="'.$row['file'].'"
                        data-name="'.$name.'"
                        data-infos="'.$infos.'"
                        data-version="'.$row['version'].'">';
               
                echo '<td class="align-middle text-center">'.$source.'</td>';
                echo '<td class="align-middle text-capitalize font-weight-bold">'.$row['firstname'].'</td>';
                echo '<td class="align-middle text-capitalize font-weight-bold">'.$row['lastname'].'</td>';
                echo '<td class="align-middle text-capitalize">'.$row['commune'].'</td>';
                echo '<td class="align-middle text-capitalize">'.$row['collecte_par'].'</td>';
                echo '<td class="align-middle text-center">'.date("d.m.Y", strtotime($row['creation_date'])).'</td>';
                echo '<td class="align-middle text-center">'.$row['version'].'</td>';

                echo '<td class="align-middle text-center" width="100px">
                        

                        <div class="dropleft d-inline" id="actions">
                    	 	<button class="btn btn-primary btn-sm mr-1 dropdown-toggle" type="button" id="action_'.$i.'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    	 		 <span class="text">Actions</span> <span class="oi oi-menu ml-1"></span>
                        	</button>
                    		<div class="dropdown-menu" aria-labelledby="action_'.$i.'">
                    			<div class="dropdown-item" style="cursor:pointer" data-action="reviewFile"><span class="oi oi-eye m-1"></span> Afficher le questionnaire</div>
                                <div class="dropdown-item" style="cursor:pointer" data-action="score"><span class="oi oi-bar-chart m-1"></span> Graphiques des scores</div>
                    		</div>
                    	</div>
                      </td>';
                
                echo '</tr>';
            }
		}
		/*
		foreach($repondants as $key => $rep) {
			++$i;
			echo '<tr data-file="'.$hidden[$key]['file'].'" data-name="'.$hidden[$key]['name'].'" data-version="'.$hidden[$key]['version'].'">';
				echo '<td class="align-middle text-center">'.$i.'</td>';
				foreach($rep as $key => $info) {
					echo '<td class="align-middle text-capitalize '.(in_array($key, array("Nom", "Prénom")) ? "font-weight-bold" : "").'">'.$info.'</td>';
				}
			echo '</tr>';
		}
		*/
	echo '</tbody>';
echo '</table>';
?>
<br>
		
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script  src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap4.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap4.min.css">
<script src="js/table.selectable.js"></script>

<script>
	$(document).ready(function() {
		deleteCookie('indexAspect');
		deleteCookie('readonly');

		if(getCookie("savedVersion") != "") {
			setCookie("version", getCookie("savedVersion"), <?= LIFE_COOKIE_VERSION ?>);
			deleteCookie('savedVersion');
		}
		
		$('#repondants').dataTable( {
			"pagingType": "full_numbers",
			"columnDefs": [
			    { "orderable": false, "searchable": false, "targets": [0,<?= (count($colsHead)-1) ?>] }
			]
		});
		
		$('body').tooltip({
		    selector: '[data-toggle=tooltip]'
		});
		
		var itemsScore = [
			{text:"Par Question", action:"byQuestion"},
			{text:"Par Aspect", action:"byAspect"},
			{text:"Par Indicateur", action:"byIndicator"}
		];
		
		$('table#repondants').selectableRows()
		.addButton("Supprimer", "delete", "danger", "x", function(){
			alert("Implémenté bientot");
		})
		.addDropdown("Générer scores pour <b>R</b>", "scores", itemsScore, "success", "bar-chart", function(){
			var selectedRows = $('#repondants tr.active');
			var button = $(this).parents(".dropdown").find("button");
			var initBtText = button.find("span.text").html();
			
			button.attr("disabled","disabled").find("span.text").text("Génération...");
			
			var files = "";
			selectedRows.each(function(){
				files += $(this).attr("data-file")+":"+$(this).attr("data-version")+":"+$(this).attr("data-infos")+",";
			});
			files = files.substring(0, files.length-1);
			//$('input[type="search"]').val(files);
			
			$.post('pages/generateScores.php', { data:files, typeScore:$(this).attr("data-action") }, function(resp) {
				button.removeAttr("disabled").find("span.text").html(initBtText);
				if(resp != "error") 
					document.location = 'download.php?file=<?= DIR_ANSWERS ?>/scores/'+resp;
				else
					alert(resp);
			});	
			
			
		})
		.addButton("Télécharger", "download", "primary", "cloud-download", function(){
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
		
		$('body').on('click', '#actions .dropdown-item[data-action="reviewFile"]', function(){
			var row = $(this).parents("tr");
			var fileURL = row.attr("data-file");
			var version = row.attr("data-version");
			
			$.get("<?= DIR_ANSWERS ?>/"+fileURL)
		    .done(function() { 
		    	var lifespan = <?= LIFE_COOKIE_QUEST_PENDING ?>;
				
		    	if(getCookie("version") != "" && version != getCookie("version")) {
					setCookie('savedVersion', getCookie('version'), <?= LIFE_COOKIE_VERSION ?>);
		    	}
		    	
		    	if(version != '?' && version != getCookie('version')) {
			    	setCookie("version", version, <?= LIFE_COOKIE_VERSION ?>);
		    	} 
		    	
		    	setCookie("filename", fileURL, lifespan);
				setCookie("readonly", "true", lifespan);
				setCookie("indexAspect", 1, lifespan);
				document.location = 'index.php?readonly';
		    }).fail(function() { 
		        alert("le fichier de questionnaire n'existe pas");
		    })
			
		});

		$('body').on('click', '#actions .dropdown-item[data-action="score"]', function(){
			var row = $(this).parents("tr");
			var fileURL = row.attr("data-file");

			$.get("<?= DIR_ANSWERS ?>/"+fileURL)
		    .done(function() { 
			    /*
				$.post('pages/generateScore.byAspect.php',
				{
					data: fileURL+":"+row.attr("data-version")
				},
				function(html){
					alert(html);
				});
				*/
				alert("Bientot disponible");
		    }).fail(function() { 
		        alert("le fichier de questionnaire n'existe pas");
		    });
		});
		
				
	});
</script>
