<?php
include_once("login.redirect.php");


if(isset($_POST['filter'])) {
    $query = "UPDATE users SET params=? WHERE uid=?";
    if(isset($_POST['filter']['version']) && $_POST['filter']['version'] == "all") unset($_POST['filter']['version']);
    
    if(isset($_POST['filter']['date']['start'])) {
        if(empty($_POST['filter']['date']['start']) && empty($_POST['filter']['date']['end'])) unset($_POST['filter']['date']);
        if(empty($_POST['filter']['date']['start'])) unset($_POST['filter']['date']['start']);
        if(empty($_POST['filter']['date']['end'])) unset($_POST['filter']['date']['end']);
    }
    
    if(isset($_POST['filter']['origin']) && $_POST['filter']['origin'] == "all") unset($_POST['filter']['origin']);
    
    $filters = serialize($_POST['filter']);
    if(isset($_REQUEST['reset'])) {
        $filters = NULL;
    }
    if($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("si", $filters, $_SESSION['user_id']);
        $stmt->execute();
    }
}

$filters = null;
foreach($mysqli->query("SELECT params FROM users WHERE uid=".$_SESSION['user_id']) as $row) {
    $filters = $row['params'];
}
if($filters !== null) {
    $filters = unserialize($filters);
}
$hasFilters = $filters != null && is_array($filters) && count($filters) > 0;

$repondants = array();
$hidden = array();
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
echo '<table id="repondants" class="table table-striped table-hover display table-sm" data-page-length="11" style="border-collapse:collapse!important">';
	echo '<thead>';
		echo '<tr>';

		$colsHead = array("", $t['firstname'], $t['lastname'], //$t['village'],
		    $t['collected_by'], $t['atelier'], $t['cluster'], $t['creation'], $t['version'], "");
		foreach($colsHead as $head) {
		    echo '<th>'.$head.'</th>';
		}
		echo '</tr>';
	echo '</thead>';
	
	echo '<tbody>';
		$i = 0; 
		$sqlCond = " (q.deleted IS NULL OR q.deleted = 0) ";
		if(isset($_GET['display']) && $_GET['display'] == "archive") {
		    $sqlCond = "q.deleted = 1";
		}
		
		if($filters !== null) {
		    if(isset($filters['version']) && $filters['version'] != "all") {
		      $sqlCond .= " AND version='".$filters['version']."' ";
		    }
		    if(isset($filters['collected_by']) && is_array($filters['collected_by']) && count($filters['collected_by']) > 0) {
		        
		        $sqlCond .= " AND (";
		        foreach($filters['collected_by'] as $filter) {
		          $sqlCond .= " collecte_par NOT LIKE '".$filter."%' OR";
		        }
		        $sqlCond = substr($sqlCond, 0, -2).") ";
		    }
		    if(isset($filters['date'])) {
		        if(isset($filters['date']['start'])) {
		          $sqlCond .= " AND creation_date >= '".$filters['date']['start']." 23:59:59' ";   
		        }
		        if(isset($filters['date']['end'])) {
		            $sqlCond .= " AND creation_date <= '".$filters['date']['end']." 23:59:59' ";
		        }
		    }
		    if(isset($filters['origin']) && $filters['origin'] != "all") {
		        $sqlCond .= " AND file ".($filters['origin'] == "tablet" ? "" : "NOT")." LIKE '%android%' ";
		    }
		}
		
		$queryQuest = "SELECT p.pid, collecte_par, firstname, lastname, commune, creation_date, version, cluster, ktidb, p.rid, file, rlabel_".getLang().", pslabel_".getLang()." FROM questionnaires q
            LEFT JOIN participants p ON q.pid = p.pid
            LEFT JOIN regions re ON re.rid=p.rid
            LEFT JOIN prod_systems ps ON ps.psid=cluster
            WHERE ".$sqlCond."
            ORDER BY lastname ASC, firstname ASC";
	
		
		if ($stmt = $mysqli->prepare($queryQuest)) {
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                
                $isFromAndroid = strstr($row['file'],"android") !== false;
                
                $source = '<span class="oi oi-globe text-success" data-toggle="tooltip" data-placement="right" title="'.$t['collected-online'].'"></span>';
                if($isFromAndroid) $source = '<span class="oi oi-tablet text-primary" data-toggle="tooltip" data-placement="right" title="'.$t['collected-on-tablet'].'"></span>';
                
                $name = mb_strtolower(remAccent($row['lastname']."_".$row['firstname']));
                
                $infos = urlencode(serialize(array(
                    "firstname" => ucfirst($row['firstname']),
                    "lastname" => ucfirst($row['lastname']),
                    "systeme_prod" => $row['cluster'],
                    "region" => $row['rid'],
                    "ktidb" => $row['ktidb']
                )));
                

                $urlScores = getURLScores($row['file'], $row['version']);
                
                /*
                $data = $row['file'].":".$row['version'].":".urlencode(serialize(array()));
                ?>
                <script>
                $(function(){
                    $.post('pages/generateScores.php', {
        				data:"<?= $data ?>",
        				typeScore: "db_all",
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
                        data-url-scores="'.$urlScores.'"
                        data-pid="'.$row['pid'].'" 
                        data-version="'.$row['version'].'">';
               
                echo '<td class="align-middle text-center">'.$source.'</td>';
                echo '<td class="align-middle text-capitalize '.(empty($row['firstname']) ? 'text-muted' : 'font-weight-bold').'">'.(empty($row['firstname']) ? $t['empty'] : $row['firstname']).'</td>';
                echo '<td class="align-middle text-capitalize '.(empty($row['lastname']) ? 'text-muted' : 'font-weight-bold').'">'.(empty($row['lastname']) ? $t['empty'] :  $row['lastname']).'</td>';
                //echo '<td class="align-middle text-capitalize">'.$row['commune'].'</td>';
                echo '<td class="align-middle text-capitalize">'.$row['collecte_par'].'</td>';
                echo '<td class="align-middle">'.$row['rlabel_'.getLang()].'</td>';
                echo '<td class="align-middle text-center">'.($row['cluster'] == null ? '-' : '<span class="badge badge-light" data-toggle="tooltip" data-placement="top" title="'.$row['pslabel_'.getLang()].'">'.$row['cluster'].'</span>').'</td>';
                echo '<td class="align-middle text-center">'.date("d.m.y", strtotime($row['creation_date'])).'</td>';
                echo '<td class="align-middle text-center">'.$row['version'].'</td>';

                echo '<td class="align-middle text-center" width="100px">';
                       
                
          
                echo
                '<div class="dropleft d-inline" id="actions">
            	 	<button class="btn btn-primary btn-sm mr-1 dropdown-toggle" type="button" id="action_'.$i.'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            	 		 <span class="text">'.$t['actions'].'</span> <span class="oi oi-menu ml-1"></span>
                	</button>
            		<div class="dropdown-menu" aria-labelledby="action_'.$i.'">
            			<div class="dropdown-item" style="cursor:pointer" data-action="reviewFile"><span class="oi oi-eye m-1"></span> '.$t['display-questionnaire'].'</div>
                        <div class="dropdown-item" style="cursor:pointer" data-action="score"><span class="oi oi-bar-chart m-1"></span> '.$t['graphiques-scores'].'</div>
            		    <hr>
                        <div class="dropdown-item" style="cursor:pointer" data-action="view-participant"><span class="oi oi-person m-1"></span> '.$t['view-participant'].'</div>
                    </div>
            	</div>';

                echo '</td>
                </tr>';
            }
		}
	
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
        <p class="text-center"><?= $t[isset($_GET['display']) && $_GET['display'] == "archive" ? 'confirm-deletion' : 'trash-confirm-deletion']?></p>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="deleteParticipants">
            <label class="form-check-label" for="deleteParticipants"><?= $t[isset($_GET['display']) && $_GET['display'] == "archive" ? 'delete-participants-also' : 'trash-delete-participants-also']?></label>
      	</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= $t['close']?></button>
        <button type="button" class="btn btn-primary" id="submit"><?= $t['confirm']?></button>
      </div>
    </div>
  </div>
</div>

<link rel="stylesheet" href="css/dataTables.bootstrap4.min.css">		
<script src="js/jquery.dataTables.min.js"></script>
<script src="js/moment.min.js"></script>
<script src="js/datetime-moment.js"></script>
<script  src="js/dataTables.bootstrap4.min.js"></script>
<script src="js/table.selectable.js"></script>

<?php
$nbItemsInTrash = 0;
if($stmt = $mysqli->query("SELECT qid FROM questionnaires WHERE deleted=1")) {
    $nbItemsInTrash = $stmt->num_rows;
}
?>

<script>
	$(document).ready(function() {
		deleteCookie('indexAspect');
		deleteCookie('readonly');
		deleteCookie('scores-display');

		if(getCookie("savedVersion") != "") {
			setCookie("version", getCookie("savedVersion"), <?= LIFE_COOKIE_VERSION ?>);
			deleteCookie('savedVersion');
		}

		<?php if(isset($_GET['display']) && $_GET['display'] == "archive") {?>
		$('nav.navbar #buttons').append('<div class="d-flex align-items-center"><a href="admin/dashboard/<?php if(isset($_GET['page'])) { echo $_GET['page']; } ?>" class="btn btn-light btn-sm mr-3"><span class="oi oi-chevron-left mr-1"></span></a> <h5 class="text-white mb-0"><?= $t['corbeille']?></h5></div>');
		<?php } else {?>
		$('nav.navbar #buttons').append('<a href="admin/dashboard/1/archive" id="trash-link" class="btn text-white btn-info btn-sm <?php if($nbItemsInTrash<=0){ echo 'disabled'; }?>"><span class="oi oi-trash mr-1"></span> <?= $t['display_corbeille']?> <span class="badge badge-light ml-1" id="nb-in-trash"><?= $nbItemsInTrash ?></span></a>');
		<?php } ?>
		
		$.fn.dataTable.moment('DD.MM.YY');

		var itemsScore = [
			{text:"<?= $t['byQuestion']?>", action:"byQuestion"},
			{text:"<?= $t['byAspect']?>", action:"byAspect"},
			{text:"<?= $t['bySection'] ?>", action:"bySection"},
			{text:"<?= $t['byIndicator']?>", action:"byIndicator"},
			{text:"<?= $t['resilience']?>", action:"resilience"}
		];

		var itemsDownload = [
			{text: "<?= $t['brut_file']?>", action:"brut"},
			{text: "<?= $t['csv_answer_file']?>", action:"csv_answers"}		
		];

		function callbackGenerateScores(resp, button, initBtText) {
			button.removeAttr("disabled").find("span.text").html(initBtText);
			bootbox.hideAll();
			
			if(resp != "error" && resp.length > 2 && resp.length < 200) {
				document.location = '<?= getBase() ?>download.php?file=<?= DIR_ANSWERS ?>/scores/'+resp;
			} else {
				bootbox.alert(resp);
			}
		}
		
		$('#repondants').dataTable({
			<?php if(getLang() != "en") { ?>
			language: {
		        url: '//cdn.datatables.net/plug-ins/1.10.16/i18n/<?= strtolower(getLang()) == "de" ? "German" : "French" ?>.json'
		    },
		    <?php } ?>
			pagingType: "full_numbers",
			order: [[ 6, "desc" ]],
			columnDefs: [
			    { orderable: false, searchable: false, targets: [0,<?= (count($colsHead)-1) ?>] }
			],
			initComplete: function(){
				$('table#repondants').selectableRows()
				<?php if(isset($_GET['display']) && $_GET['display'] == "archive") {?>
				.addButton("<?= $t['recover']?>", "recover", "info", "loop-circular", function(){
					$('input[type="checkbox"]#deleteParticipants').attr("checked", "checked");	
					var files = "";
    				$('#repondants tbody tr.active').each(function(){
    					files += $(this).attr("data-file")+",";
    				});
    				files = files.substring(0, files.length-1);
    				
    				$.post('pages/delete.php', {
    					data:files,
    					deleteParticipant:$('input[type="checkbox"]#deleteParticipants').is(":checked"),
    					actionId:"delete-files",
    					recover:1
    				}, function(html){
    					$('#repondants tbody tr.active').remove();
    					$('#tools').hide();
    					if($('#repondants tbody tr').length == 0) {
							document.location = '<?= getBase() ?>admin/dashboard/<?= (isset($_GET['page']) ? $_GET['page'] : "") ?>';
    					}
    				});
				})
				<?php } ?>
				.addButton("<?= isset($_GET['display']) && $_GET['display'] == "archive" ? $t['delete'] : $t['put_in_trash']?>", "delete", "danger", "trash", function(){
					if($('#repondants tbody tr.active').length > 5) {
						bootbox.alert("<?= $t['security-message-1']?>");
					} else {
		    			var modal = $('#exampleModalCenter');
		    			modal.modal();
		    			modal.find('#submit').removeAttr("disabled").bind("click", function(){
			    			loading();
		    				modal.find('#submit').attr("disabled","disabled");	
		    				var nbSelected = $('#repondants tbody tr.active').length;
		    				var files = "";
		    				$('#repondants tbody tr.active').each(function(){
		    					files += $(this).attr("data-file")+",";
		    				});
		    				files = files.substring(0, files.length-1);
		    				
		    				$.post('pages/delete.php', {
		    					data:files,
		    					deleteParticipant:$('input[type="checkbox"]#deleteParticipants').is(":checked"),
		    					actionId:"delete-files",
		    					definitive:"<?= isset($_GET['display']) && $_GET['display'] == "archive" ? "1": "0" ?>"
		    				}, function(html){
		    					bootbox.hideAll();
		    					modal.find('#submit').unbind("click");
		    					$('#repondants tbody tr.active').remove();
		    					$('#tools').hide();
		    					$('#trash-link').removeClass("disabled");
		    					$('#nb-in-trash').text(parseInt($('#nb-in-trash').text())+nbSelected);
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

					loading();
					
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
						callbackGenerateScores(resp, button, initBtText);
					});
						
				})
				.addDropdown("<?= $t['download']?>", "download", itemsDownload, "primary", "cloud-download", function(){
					var selectedRows = $('#repondants tr.active');
					var button = $(this).parents(".dropdown").find("button");
					var initBtText = button.find("span.text").html();
					var action = $(this).attr("data-action");
					
					button.attr("disabled","disabled").find("span.text").html('<?= $t['generation']?> <img src="img/loader-score.svg">');

					loading();

					var files = "";
	    			
	    			
					if(action == "brut") {
    					if(selectedRows.length == 1) {
    						button.removeAttr("disabled").find("span.text").html(initBtText);
    						bootbox.hideAll();
    						document.location = '<?= getBase() ?>download.php?file=<?= DIR_ANSWERS ?>/'+selectedRows.attr("data-file")+"&name="+selectedRows.attr("data-name");
    					} else {
    						selectedRows.each(function(){
    		    				files += $(this).attr("data-file")+":"+$(this).attr("data-name")+",";
    		    			});
    		    			$.post('pages/admin/download.questionnaires.php', { f:files }, function(resp) {
    		    				button.removeAttr("disabled").find("span.text").html(initBtText);
    							bootbox.hideAll();
    							
    							if(resp == "ok") 
    		    					document.location = '<?= getBase() ?>download.php?file=pages/admin/questionnaires.zip';
    							else
    								bootbox.alert(resp);
    		    			});
    					}
					} else {
						selectedRows.each(function(){
							files += $(this).attr("data-file")+":"+$(this).attr("data-version")+":"+$(this).attr("data-infos")+",";
		    			});
						$.post('pages/generateScores.php', {
							data:files,
							typeScore:$(this).attr("data-action"),
							output:"csv"
						}, function(resp) {
							callbackGenerateScores(resp, button, initBtText);
		    			});
					}
				});

				
				$('#repondants_filter').append('<div id="filters-toggle" data-toggle="popover" title="<?= $t['filters']?>" data-content="<?= $t['loading']?>" style="vertical-align:top; font-size:12px;" class="btn btn-<?= ($hasFilters) ? "success" : "primary" ?> ml-1"><span class="oi oi-sort-descending mr-1"></span> <?= $t['filters']?> <?php if($hasFilters) echo '('.(count(array_keys($filters))).')'; ?></div>');
				$.post('pages/admin/quest.filters.php', {}, function(html){
					$('#filters-toggle').attr("data-content", html);
				});
				$('[data-toggle="popover"]').popover({html:true});

				$('#loader').fadeOut();
				$('#content-loaded').fadeIn();
			}
		});
		
		$('body').on('click', '#actions .dropdown-item[data-action="reviewFile"]', function(){
			var row = $(this).parents("tr");
			var fileURL = row.attr("data-file");
			var version = row.attr("data-version");

			loading();
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
				document.location = '<?= getBase() ?>index.php?readonly';
		    }).fail(function() { 
		    	bootbox.hideAll();
		        bootbox.alert("le fichier de questionnaire n'existe pas");
		    });
			
		});

		$('body').on('click', '#actions .dropdown-item[data-action="score"]', function(){
			var row = $(this).parents("tr");
			var fileURL = row.attr("data-file");
			var version = row.attr("data-version");

			loading();
			
			$.get("<?= DIR_ANSWERS ?>/"+fileURL)
		    .done(function() { 
				document.location = row.data("url-scores");
		    }).fail(function() { 
		    	bootbox.hideAll();
		        bootbox.alert("Le fichier de questionnaire n'existe pas");
		    });
		});


		$('body').on('click', '#actions .dropdown-item[data-action="view-participant"]', function(){
			loading();

			$.post('pages/admin/participant.view.advanced.php', { pid:$(this).closest("tr").data("pid") }, function(html) {
				bootbox.hideAll();
				bootbox.dialog({
					size: "large",
					animate:false,
					message: html
				});
			}); 
		});
		
				
	});
</script>
