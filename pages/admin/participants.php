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

if ($stmt = $mysqli->prepare("SELECT p.pid, qid, firstname, lastname, commune, pslabel_".getLang().", rlabel_".getLang()." FROM participants p
                                LEFT JOIN questionnaires q ON q.pid = p.pid
                                LEFT JOIN prod_systems ps ON ps.psid = cluster
                                LEFT JOIN regions re ON re.rid = p.rid
                                GROUP BY p.pid 
                                ORDER BY lastname ASC, firstname ASC")) {
    $stmt->execute();    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    echo '<table id="repondants" class="table table-striped table-hover table-sm" data-page-length="15" style="border-collapse:collapse!important">';
    echo '<thead>';
    echo '<tr>';
    echo '<th class="text-center"></th>';
    $j = 0;
    foreach(array($t['firstname'], $t['lastname'], $t['village'], $t['cluster'], $t['atelier']) as $key) {
        echo '<th class="text-capitalize '.($j >= 4 ? "text-center" : "").'">'.$key.'</th>';
        $j++;
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
            echo '<td class="text-capitalize text-center"><span class="oi oi-link-broken"></span></td>';
        } else {
            echo '<td class="text-capitalize text-center">'.$i.'</td>';
        }
        
        $j = 0;
        foreach($row as $key => $info) {
            if(in_array($key, array("pid", "qid"))) continue;
            if(substr($key, 0, strlen("pslabel_")) == "pslabel_") { $key = "psid"; }
            if(substr($key, 0, strlen("rlabel_")) == "rlabel_") $key = "rid";
            
            echo '<td data-'.$key.' class="text-capitalize '.($j >= 4 ? "text-center" : "").'">'.$info.'</td>';
            $j++;
        }
    
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
        <h5 class="modal-title" id="exampleModalLongTitle"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal"></button>
        <button type="button" class="btn btn-primary" id="submit"></button>
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
		var table = $('#repondants').dataTable( {
			<?php if(getLang() != "en") { ?>
			language: {
		        url: '//cdn.datatables.net/plug-ins/1.10.16/i18n/<?= strtolower(getLang()) == "de" ? "German" : "French" ?>.json'
		    },
		    <?php } ?>
			pagingType: "full_numbers",
			columnDefs: [
			    { "orderable": false, "searchable": false, "targets": 0 }
			],
			initComplete: function(settings, json) {

				$('#repondants_filter').append('<a class="btn btn-sm btn-success ml-2" style="vertical-align:top" id="add-table"><span class="oi oi-plus mr-1"></span> Add</a>');

				$('#repondants_filter').on('click', '#add-table', function(){
					showFormInModal("add", "Ajouter un participant");
				});
				
				$('table#repondants').selectableRows()
				.addButton("<?= $t['delete']?>", "delete", "danger", "x", function(){
					if($('#repondants tbody tr.active').length > 5) {
						alert("<?= $t['security-message-1']?>");
					} else {
		    			var modal = $('#exampleModalCenter');
		    			modal.modal();
		    			modal.find('.modal-body').html('<p class="text-center"><?= $t['confirm-deletion']?></p>');
		    			modal.find('.modal-title').html('<?= $t['confirmation']?>');
		    			modal.find('.modal-footer [data-dismiss]').show().html("<?= $t['close']?>");
		    			modal.find('button#submit').off("click").addClass("btn-primary").removeClass("btn-success").html("<?= $t['confirm']?>");
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
					var pid = $('#repondants tbody tr.active').data("pid");
					showFormInModal("edit", "Editer un participant", pid);
				})
				.addButton("Générer lien", "link", "info", "link-intact", function(){
					alert("Implémenté bientot");
				});
		  	}
		});


		function showFormInModal(type, title, pid) {
			var modal = $('#exampleModalCenter');

			modal.modal();
			modal.find('.modal-body').html("Chargement...");
			modal.find('.modal-title').html(title);
			modal.find('.modal-footer [data-dismiss]').hide();
			modal.find('button#submit').off("click").addClass("btn-success").removeClass("btn-primary").html("Enregistrer").attr("disabled","disabled");
			modal.on('hide.bs.modal', function(e){
				modal.find('button#submit').off("click");
			});
			
			var params = {
				pid: pid,
				action:"load"
			};
			if(pid == "undefined") params = {};
			
			$.post('pages/admin/addParticipant.php', params, function(html) {
				modal.find('.modal-body').html(html);
				modal.find('button#submit').removeAttr("disabled");
				
				modal.find('form').on("submit", function(){
					if(pid == "undefined") saveFromModalForm(type);
					else saveFromModalForm(type, pid);
					$(this).off("submit");
					return false;
				});
				modal.find('button#submit').on("click", function(){
					modal.find('form').trigger("submit");
					$(this).off("click");
				});
				
				modal.on('shown.bs.modal', function (e) {
					modal.find('.form-control:first').focus();
				});
			});

		}
		
		function saveFromModalForm(type, pid) {
			var modal = $('#exampleModalCenter');
			
			
			var values = "";
			var valuesDefined = 0;
			var htmlNewRow = "<td></td>";
			modal.find(".modal-body").find('input, select, textarea').each(function(){
				if($(this).attr("name") != "undefined") {
    				var val = $(this).val();
    				if($(this).is("select")) {
						val = $(this).find('option[value="'+val+'"]').html();
    				}
    				
    				if($(this).val().trim().length > 0) valuesDefined++;
    				values += $(this).attr("name")+"->"+$(this).val()+"#";
    				if(type == "edit") {
    					$('#repondants tbody tr[data-pid="'+pid+'"] td[data-'+$(this).attr("name")+']').html(val);
    				} else {
    					if($('#repondants tbody tr:first-child td[data-'+$(this).attr("name")+']').length > 0) {
    						htmlNewRow += '<td data-'+$(this).attr("name")+'>'+val+'</td>';
    					}
    				}
				}
			});
			values = values.substring(0, values.length-1);

			if(valuesDefined >= 2) {
    			var params = {action:"save", values:values};
    			if(pid != "undefined") params.pid = pid;

    			modal.find('button#submit').attr("disabled","disabled");
    			$.post('pages/admin/addParticipant.php', params, function(html) {
    				modal.modal("hide");
    				$('#repondants tbody tr.active td').trigger("click");

    				if(type == "add") {
    					$('#repondants tbody').prepend('<tr>'+htmlNewRow+'</tr>');
    				}
    			});
			}
		}
		

	});
</script>
