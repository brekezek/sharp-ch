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
	<h1 class="h2"><?= $t['list-questionnaires']?></h1>
</div>
<?php
$lastHash = "";
foreach($repondants as $hash => $infos)
	$lastHash = $hash;
?>	

<div class="bg-secondary text-white p-2" id="tools" style="display:none">
	<div class="d-flex justify-content-between align-items-center">
		<div>
    		<span id="nb-selected" class="badge badge-light px-3 mr-1" style="padding: .25rem .5rem; font-size:1.1em"><span>0</span> <?= $t['selected']?></span>
    		<button id="unselect" class="btn btn-light btn-sm" style="padding: .25rem .5rem; vertical-align:baseline; line-height:1"><?= $t['unselect']?></button> 
    	</div>
    	
    	<div>
    		<a class="btn btn-primary btn-sm" id="download"><span class="oi oi-cloud-download mr-1"></span> <span class="text"><?= $t['download']?></span> <span data-track-row class="badge badge-light ml-1">0</span></a>
    		<a class="btn btn-danger btn-sm" id="delete"><span class="oi oi-x mr-1"></span> <span class="text"><?= $t['delete']?></span> <span data-track-row class="badge badge-light ml-1">0</span></a>
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
		$colsHead = array("", $t['firstname'], $t['lastname'], $t['village'], $t['collected_by'], $t['atelier'], $t['cluster'], $t['creation'], $t['version'], "");
		foreach($colsHead as $head) {
		    echo '<th>'.$head.'</th>';
		}
		echo '</tr>';
	echo '</thead>';
	
	echo '<tbody>';
		$i = 0; 
		if ($stmt = $mysqli->prepare(
		    "SELECT collecte_par, firstname, lastname, commune, creation_date, version, cluster, atelier, file, COUNT(q.pid) as nbQuest FROM questionnaires q
            LEFT JOIN participants p ON q.pid = p.pid
            GROUP BY q.pid 
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
                
                /*
                $data = $row['file'].":".$row['version'].":".urlencode(serialize(array()));
                ?>
                <script>
                $(function(){
                    $.post('pages/generateScores.php', {
        				data:"<?= $data ?>",
        				typeScore: "resilience",
        				output:"db"
    				}, function(resp) {
        				
    				});	
                });
                </script>
                <?php 
                */
                
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
                echo '<td class="align-middle text-center">'.$row['atelier'].'</td>';
                echo '<td class="align-middle text-center">'.$row['cluster'].'</td>';
                echo '<td class="align-middle text-center">'.date("d.m.y", strtotime($row['creation_date'])).'</td>';
                echo '<td class="align-middle text-center">'.$row['version'].'</td>';

                echo '<td class="align-middle text-center" width="100px">
                       
                        <div class="dropleft d-inline" id="actions">
                    	 	<button class="btn btn-primary btn-sm mr-1 dropdown-toggle" type="button" id="action_'.$i.'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    	 		 <span class="text">'.$t['actions'].'</span> <span class="oi oi-menu ml-1"></span>
                        	</button>
                    		<div class="dropdown-menu" aria-labelledby="action_'.$i.'">
                    			<div class="dropdown-item" style="cursor:pointer" data-action="reviewFile"><span class="oi oi-eye m-1"></span> '.$t['display-questionnaire'].'</div>
                                <div class="dropdown-item" style="cursor:pointer" data-action="score"><span class="oi oi-bar-chart m-1"></span> '.$t['graphiques-scores'].'</div>
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

<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle"><?= $t['confirmation']?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p class="text-center"><?= $t['confirm-deletion']?></p>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="deleteParticipants">
            <label class="form-check-label" for="deleteParticipants"><?= $t['delete-participants-also']?></label>
      	</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= $t['close']?></button>
        <button type="button" class="btn btn-primary" id="submit"><?= $t['confirm']?></button>
      </div>
    </div>
  </div>
</div>
		
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.4/moment.min.js"></script>
<script src="https://cdn.datatables.net/plug-ins/1.10.16/sorting/datetime-moment.js"></script>
<script  src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap4.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap4.min.css">
<script src="js/table.selectable.js"></script>

<script>
	$(document).ready(function() {
		deleteCookie('indexAspect');
		deleteCookie('readonly');
		deleteCookie('scores-display');

		if(getCookie("savedVersion") != "") {
			setCookie("version", getCookie("savedVersion"), <?= LIFE_COOKIE_VERSION ?>);
			deleteCookie('savedVersion');
		}

		$.fn.dataTable.moment('DD.MM.YY');
		$('#repondants').dataTable({
			language: {
		        url: '//cdn.datatables.net/plug-ins/1.10.16/i18n/<?= strtolower(getLang()) == "de" ? "German" : "French" ?>.json'
		    },
			pagingType: "full_numbers",
			order: [[ 7, "desc" ]],
			columnDefs: [
			    { orderable: false, searchable: false, targets: [0,<?= (count($colsHead)-1) ?>] }
			]
		});
		
		$('body').tooltip({
		    selector: '[data-toggle=tooltip]'
		});
		
		var itemsScore = [
			{text:"<?= $t['byQuestion']?>", action:"byQuestion"},
			{text:"<?= $t['byAspect']?>", action:"byAspect"},
			{text:"<?= $t['byIndicator']?>", action:"byIndicator"},
			{text:"<?= $t['resilience']?>", action:"resilience"}
		];
		
		$('table#repondants').selectableRows()
		.addButton("<?= $t['delete']?>", "delete", "danger", "x", function(){
			if($('#repondants tbody tr.active').length > 5) {
				alert("<?= $t['security-message-1']?>");
			} else {
    			var modal = $('#exampleModalCenter');
    			modal.modal();
    			modal.find('#submit').removeAttr("disabled").bind("click", function(){
    				modal.find('#submit').attr("disabled","disabled");	
    				
    				var files = "";
    				$('#repondants tbody tr.active').each(function(){
    					files += $(this).attr("data-file")+",";
    				});
    				files = files.substring(0, files.length-1);
    				
    				$.post('pages/delete.php', {
    					data:files,
    					deleteParticipant:$('input[type="checkbox"]#deleteParticipants').is(":checked"),
    					actionId:"delete-files"
    				}, function(html){
    					modal.find('#submit').unbind("click");
    					$('#repondants tbody tr.active').remove();
    				});
    				
    				modal.modal('hide');
    			});
			}
		})
		.addDropdown("<?= $t['generate-score-R']?>", "scores", itemsScore, "success", "bar-chart", function(){
			var selectedRows = $('#repondants tbody tr.active');
			var button = $(this).parents(".dropdown").find("button");
			var initBtText = button.find("span.text").html();
			
			button.attr("disabled","disabled").find("span.text").html('<?= $t['generation']?> <img src="img/loader-score.svg">');
			
			var files = "";
			selectedRows.each(function(){
				files += $(this).attr("data-file")+":"+$(this).attr("data-version")+":"+$(this).attr("data-infos")+",";
			});
			files = files.substring(0, files.length-1);
			//$('input[type="search"]').val(files);
			
			$.post('pages/generateScores.php', {
				data:files,
				typeScore:$(this).attr("data-action"),
				output:"csv"
			}, function(resp) {
				button.removeAttr("disabled").find("span.text").html(initBtText);
				if(resp != "error") {
					document.location = 'download.php?file=<?= DIR_ANSWERS ?>/scores/'+resp;
				} else {
					alert(resp);
				}
			});	
			
			
		})
		.addButton("<?= $t['download']?>", "download", "primary", "cloud-download", function(){
			var selectedRows = $('#repondants tr.active');
			if(selectedRows.length == 1) {
				document.location = 'download.php?file=<?= DIR_ANSWERS ?>/'+selectedRows.attr("data-file")+"&name="+selectedRows.attr("data-name");
			} else {
				$(this).attr("disabled","disabled").find("span.text").text("<?= $t['generation']?>...");
    			var files = "";
    			selectedRows.each(function(){
    				files += $(this).attr("data-file")+":"+$(this).attr("data-name")+",";
    			});
    			$.post('pages/admin/download.questionnaires.php', { f:files }, function(resp) {
    				$('#tools #download').removeAttr("disabled").find("span.text").text("<?= $t['generation']?>");
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
			var version = row.attr("data-version");
			
			$.get("<?= DIR_ANSWERS ?>/"+fileURL)
		    .done(function() { 
			    deleteCookie("indexAspect");
			    setCookie("filename", fileURL, 1);

			    if(getCookie("version") != "" && version != getCookie("version")) {
					setCookie('savedVersion', getCookie('version'), <?= LIFE_COOKIE_VERSION ?>);
		    	}
		    	
		    	if(version != '?' && version != getCookie('version')) {
			    	setCookie("version", version, <?= LIFE_COOKIE_VERSION ?>);
		    	} 
		    	
			    setCookie("scores-display", "true", 1);
				document.location = 'index.php?score';
		    }).fail(function() { 
		        alert("le fichier de questionnaire n'existe pas");
		    });
		});
		
				
	});
</script>
