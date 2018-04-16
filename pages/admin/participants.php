<?php
include_once("login.redirect.php");
?>

<style>
#repondants_wrapper .row:nth-child(1) {
	background : rgba(52, 73, 94, 1.0);
	padding-top: 8px;
	color: white;
	margin-bottom: 14px;
}
</style>


<div class="d-none justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
	<h1 class="h2"><?= $t['list-questionnaires']?></h1>
</div>



<?php
function optInfoAdm($json, $index) {
    return (isset($json['ADM_01'][$index]['answer'])) ?
    remAccent(trim($json['ADM_01'][$index]['answer'])) : "";
}

if ($stmt = $mysqli->prepare("SELECT firstname, lastname, region, commune, cluster, atelier, qid FROM participants p
                                LEFT JOIN questionnaires q ON q.pid = p.pid
                                ORDER BY lastname ASC, firstname ASC")) {
    $stmt->execute();    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    echo '<table id="repondants" class="table table-striped table-hover table-sm" data-page-length="15">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>#</th>';
    foreach($row as $key => $info) {
        if(in_array($key, array("pid", "qid"))) continue;
        echo '<th class="text-capitalize">'.$key.'</th>';
    }
    echo '</tr>';
    echo '</thead>';
    
    echo '<tbody>';
    $i = 0; 
    $result->data_seek(0);
    while ($row = $result->fetch_assoc()) {
        ++$i;
        
        if(empty($row['qid'])) {
            echo '<tr class="bg-secondary text-white">';
            echo '<td class="text-capitalize"><span class="oi oi-link-broken"></span></td>';
        } else {
            echo '<tr>';
            echo '<td class="text-capitalize">'.$i.'</td>';
        }
        
        
        foreach($row as $key => $info) {
            if(in_array($key, array("pid", "qid"))) continue;
            echo '<td class="text-capitalize">'.$info.'</td>';
        }
        /*
        echo
        '<td>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="customCheck1">
              <label class="custom-control-label" for="customCheck1"></label>
            </div>
        </td>';
        */
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
    
}	
?>

<br>
		
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script  src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap4.min.js"></script>
<script src="js/table.selectable.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap4.min.css">

<script>
	$(document).ready(function() {
		$('#repondants').dataTable( {
			language: {
		        url: '//cdn.datatables.net/plug-ins/1.10.16/i18n/French.json'
		    },
			pagingType: "full_numbers",
			columnDefs: [
			    { "orderable": false, "searchable": false, "targets": 0 }
			]
		});

		$('table#repondants').selectableRows()
		.addButton("Supprimer", "delete", "danger", "x", function(){
			alert("Implémenté bientot");
		})
		.addButton("Editer", "edit", "primary", "pencil", function(){
			alert("Implémenté bientot");
		})
		.addButton("Générer lien", "link", "info", "link-intact", function(){
			alert("Implémenté bientot");
		});

	});
</script>
