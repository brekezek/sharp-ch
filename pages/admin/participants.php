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
	<h1 class="h2">Liste des questionnaires</h1>
</div>

<?php
if ($stmt = $mysqli->prepare("SELECT * FROM participants ORDER BY lastname ASC, firstname ASC")) {
    $stmt->execute();    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    echo '<table id="repondants" class="table table-striped table-hover table-sm" data-page-length="15">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>#</th>';
    foreach($row as $key => $info) {
        if($key == "pid") continue;
        echo '<th class="text-capitalize">'.$key.'</th>';
    }
    echo '<th>File</th>';
    echo '</tr>';
    echo '</thead>';
    
    echo '<tbody>';
    $i = 0; 
    $result->data_seek(0);
    while ($row = $result->fetch_assoc()) {
        ++$i;
        echo '<tr>';
        echo '<td class="text-capitalize">'.$i.'</td>';
        foreach($row as $key => $info) {
            if($key == "pid") continue;
            echo '<td class="text-capitalize">'.$info.'</td>';
        }
        echo
        '<td>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="customCheck1">
              <label class="custom-control-label" for="customCheck1"></label>
            </div>
        </td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
}	
?>
		
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script  src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/fixedheader/3.1.3/js/dataTables.fixedHeader.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap4.min.css">

<script>
	$(document).ready(function() {
		$('#repondants').dataTable( {
			"pagingType": "full_numbers",
			fixedHeader: true,
			"columnDefs": [
			    { "orderable": false, "searchable": false, "targets": 7 },
			    { "orderable": false, "searchable": false, "targets": 0 }
			]
		});
		$('#mainSearch').attr("aria-controls", "repondants");
	});
</script>
