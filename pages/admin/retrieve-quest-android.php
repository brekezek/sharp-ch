<style>
#repondants_wrapper .row:nth-child(1) {
	background : rgba(52, 73, 94, 1.0);
	padding-top: 8px;
	color: white;
	margin-bottom: 14px;
}
table#repondants tbody tr td {
    text-transform: capitalize;
}
</style>

<table id="repondants" class="table table-striped table-hover table-sm" data-page-length="15" style="border-collapse:collapse!important;">
    <thead>
        <tr>
            <th class="">Prénom</th>
	        <th class="">Nom</th>
	        <th class="" width="80px">Date</th>
	        <th class="" width="80px">Heure</th>
	        <th width="100px"></th>
        </tr>
    </thead>
    <tbody>
		
	</tbody>
</table>

<?php 
$dropdown_version = '<div class="dropdown w-100 my-1">'.
  '<button class="btn btn-primary dropdown-toggle w-100" type="button" id="dropdownversion" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.
    $t['version'].
  '</button>'.
  '<div class="dropdown-menu w-100" aria-labelledby="dropdownversion">';
    foreach(getVersionsFolders() as $v) {
        $dropdown_version .= '<div class="dropdown-item">'.$v.'</div>';
    }
    $dropdown_version .= '</div>'.
'</div>';
?>

<link rel="stylesheet" href="css/dataTables.bootstrap4.min.css">	
<script src="js/jquery.dataTables.min.js"></script>
<script src="js/moment.min.js"></script>
<script src="js/datetime-moment.js"></script>
<script  src="js/dataTables.bootstrap4.min.js"></script>
<script src="js/table.selectable.js"></script>

<script>
$(function(){
	$('body').on('click', '.dropdown .dropdown-item', function(){
		var bt = $(this).closest('.dropdown').find('.dropdown-toggle');
		bt.html($(this).text());
		bt.attr("data-version", $(this).text());
	});
	
	$.fn.dataTable.moment('DD.MM.YYYY');
	var table = $('#repondants').dataTable( {
		<?php if(getLang() != "en") { ?>
		language: {
	        url: '//cdn.datatables.net/plug-ins/1.10.16/i18n/<?= strtolower(getLang()) == "de" ? "German" : "French" ?>.json'
	    },
	    <?php } ?>
	    ajax: {
	    	url: 'http://www.demotz.ch/savide/api.php?q=list-files',
	    	//dataType: "jsonp",
	    	dataSrc: function ( json ) {
    			//console.log(json);
    			var data = [];
                for (key in json) {
                	//console.log(key);
					var person = json[key];
					var date = new Date(person.creation_date*1000);
					var dateFD = date.toLocaleDateString('fr-CH', { year: 'numeric', month: 'numeric', day: 'numeric' });
					var dateFH = date.toLocaleTimeString('fr-CH');
                	data.push([
						person.prenom,
						person.nom,
						dateFD,
						dateFH,
						'<button data-filename="'+person.filename+'" data-creation_date="'+person.creation_date+'" class="import btn btn-sm btn-primary py-0" type="button">Importer</button>'
                    ]);
                }
                return data;
    	    }
	    },
	    deferRender: true,
		pagingType: "full_numbers",
		order: [[ 2, "desc" ],[3,"desc"]],
		initComplete: function(settings, json) {

		}
	});

	$('table tbody').on('click', '.import', function(){

		var elm = $(this);
		elm.closest("tr").addClass("bg-dark text-white");
		bootbox.dialog({
			message: '<div class="pb-1 w-100 text-center">Quel est la version du questionnaire ?</div><?= $dropdown_version ?>',
			
			buttons: {
				cancel: {
		            label: '<?= $t['cancel'] ?>', 
		            callback: function() {
		            	callbackModal(false, elm);
		            }
		        },
		        custom: {
		            label: 'Importer',
		            className: 'btn-success',
		            callback: function() {
		            	if(!$('#dropdownversion').is('[data-version]')) {
							$('#dropdownversion').click();
							return false;
						}
		            	callbackModal(true, elm);
		        	}
		        }
		    }	
		});
	});

	function callbackModal(res, elm) {
		if(res) {
			bootbox.hideAll();
			loading();

			$.ajax({
				url:'http://www.demotz.ch/savide/api.php?q=file&file='+elm.data("filename"),
                success: function(data){
                	$.post('pages/admin/importFeedback.php', { b64: data, filename:elm.data("filename"), creation_date: elm.data("creation_date"), version: $('#dropdownversion').data('version') }, function(html){
						bootbox.hideAll();
						bootbox.alert(html);
						elm.hide();
						elm.closest("td").append("Importé!");
						elm.closest("tr").css("opacity", "0.4");
                	});
                }, 
                error: function(jqXHR, textStatus, errorThrown) {
					bootbox.hideAll();
					bootbox.alert(textStatus);
                }
			});
		}
		elm.closest("tr").removeClass("bg-dark text-white");
	}
	
});
</script>