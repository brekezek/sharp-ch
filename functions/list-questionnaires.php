<?php
include_once("require.php");

$repondants = array();

foreach(scanAllDir($feedbackDir) as $quest) {
	$scoringFile = file_get_contents($feedbackDir.$quest);
	$json = json_decode($scoringFile, true);
	
	$infos = array();
	
	$infos['filename'] = '<a href="?display='.hash_str($quest).'">'.$quest.'</a>';
	$infos['collecte_par'] = optInfoAdm($json, 1);
	$infos['prenom'] = optInfoAdm($json, 3);
	$infos['nom'] = optInfoAdm($json, 2);
	$infos['chef_exploitation'] = optInfoAdm($json, 6);
	$infos['commune'] = optInfoAdm($json, 9); 
	$infos['village'] = optInfoAdm($json, 10); 
	$infos['uid'] = optInfoAdm($json, 4); 
	$infos['age'] = optInfoAdm($json, 15);
	$infos['creation_date'] = date ("d.m.Y H:i:s", filemtime($feedbackDir.$quest));
	
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
?>

<!DOCTYPE HTML>
<html>
	<head>
		<title>Liste des répondants</title>
		<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
		
		<script src="https://code.jquery.com/jquery-1.12.4.js"></script> 
		<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
		<script  src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap4.min.js"></script>
		<script src="https://cdn.datatables.net/fixedheader/3.1.3/js/dataTables.fixedHeader.min.js"></script>
		
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0/css/bootstrap.css" >
		<link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap4.min.css">
		
		<script>
			$(document).ready(function() {
				$('#repondants').dataTable( {
					"pagingType": "full_numbers",
					fixedHeader: true
				});
				
				<?php if(isset($_GET['display'])) { ?>
					var display = "<?php echo $_GET['display']; ?>".replace("?display=", "");
					$.post('displayQuestionnaire.php', {id:display}, function(html){
						$('body').css("overflow-x","hidden");
						$('#modal .content').html(html);
						$('#modal').css("display","block");
					});
				
					$('#modal .top').click(function(){
						document.location.href="?";
					});
				<?php } ?>
			});
		</script>
	    <style>
	    #modal {
			position: fixed;
			left:0; top:0;
			width: 100%;
			height: 100%;
			overflow-y:auto;
			background: #ecf0f1;
			z-index: 99;
			display: none;
		}
		#modal .top {
			position:fixed;
			left:0; top:0;
			width: 100%;
			background: rgba(52, 73, 94, 0.8);
			padding: 10px;
			text-align: center;
			cursor: pointer;
			color: white;
			transition: all 0.2s linear;
		}
		#modal .top:hover {
			padding: 14px;
			background: rgba(52, 73, 94, 1.0);
		}
		#modal .content {
			margin-top: 38px;
			padding: 14px;
		}
		#modal .content h3 {
			margin-top: 10px;
			background: rgba(41, 128, 185, 0.2);
			padding: 6px 10px;
		}
		#repondants_wrapper .row:nth-child(1) {
			background : rgba(52, 73, 94, 1.0);
			padding-top: 8px;
			color: white;
			margin-bottom: 14px;
		}
		</style>
	</head>
	<body>
		<div id="modal">
			<div class="top">
				Retour
			</div>
			<div class="content">
			
			</div>
		</div>
		
		<?php
		$lastHash = "";
		foreach($repondants as $hash => $infos)
			$lastHash = $hash;
			
		echo '<table id="repondants" class=" table-condensed table-striped table-hover display">';
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
						echo '<td>'.$i.'</td>';
						foreach($rep as $key => $info) {
							echo '<td>'.$info.'</td>';
						}
					echo '</tr>';
				}
			echo '</tbody>';
		echo '</table>';
		?>
		
		
	</body>
</html>
