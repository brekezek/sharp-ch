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

if ($stmt = $mysqli->prepare("SELECT p.pid, firstname, lastname, region, commune, cluster, atelier, qid FROM participants p
                                LEFT JOIN questionnaires q ON q.pid = p.pid
                                GROUP BY p.pid 
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
        
        echo '<tr class="';
        if(empty($row['qid'])) echo 'bg-secondary text-white';
        echo '" data-pid="'.$row['pid'].'">';
        
        if(empty($row['qid'])) {
            echo '<td class="text-capitalize"><span class="oi oi-link-broken"></span></td>';
        } else {
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
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= $t['close']?></button>
        <button type="button" class="btn btn-primary" id="submit"><?= $t['confirm']?></button>
      </div>
    </div>
  </div>
</div>
		
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
		.addButton("<?= $t['delete']?>", "delete", "danger", "x", function(){
			if($('#repondants tbody tr.active').length > 5) {
				alert("Pour des raisons de sécurité, vous ne pouvez pas supprimer plus de 5 éléments à la fois");
			} else {
    			var modal = $('#exampleModalCenter');
    			modal.modal();
    			modal.find('#submit').removeAttr("disabled").bind("click", function(){
    				modal.find('#submit').attr("disabled","disabled");	
    				
    				var data = "";
    				$('#repondants tbody tr.active').each(function(){
    					data += $(this).attr("data-pid")+",";
    				});
    				data = data.substring(0, data.length-1);
    				
    				$.post('pages/delete.php', {
    					data:data,
    					actionId:"participants"
    				}, function(html){
    					modal.find('#submit').unbind("click");
    					$('#repondants tbody tr.active').remove();
    				});
    				
    				modal.modal('hide');
    			});
			}
		})
		.addButton("<?= $t['edit'] ?>", "edit", "primary", "pencil", function(){
			alert("Implémenté bientot");
		})
		.addButton("Générer lien", "link", "info", "link-intact", function(){
			alert("Implémenté bientot");
		});

	});
</script>
