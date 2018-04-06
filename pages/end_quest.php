<div class="container">
	<div class="d-flex" style="opacity:0">-</div>
	
	<?php if(!isset($_GET['score'])) { ?>
	<div class="alert alert-success mt-3 text-center" role="alert">
		<h1 class="display-4"><?= $t['thank_you'] ?>!</h1>
		<p class="lead"><?= $t['txt1_end_quest'] ?> !</p>
		<hr class="my-4">
		<p><?= $t['txt2_end_quest'] ?></p>
		<p><a class="btn btn-success btn-lg" href="?score" role="button"><?= $t['display_my_score'] ?> »</a></p>
	</div>
	<?php } else {?>
	
	<script src="js/Chart.min.js"></script>
	<script src="js/chartjs-plugin-datalabels.min.js"></script>
	
	
	
	
	<?php 
	if ($stmt = $mysqli->prepare("SELECT qid, firstname, lastname, atelier, cluster FROM questionnaires q LEFT JOIN participants p ON p.pid = q.pid WHERE file LIKE ?")) {
        $filename = "%".basename($_COOKIE['filename']);
        $stmt->bind_param("s", $filename);
        $stmt->execute();
        $stmt->bind_result($qid, $firstname, $lastname, $atelier, $cluster);
        $stmt->fetch();
        $stmt->free_result();
        $data = $_COOKIE['filename'].":".$_COOKIE['version'].":".urlencode(serialize(array()));
        
        $scoresAtelierByAspect = array();
        if($atelier !== null) {
            $queryAtelier = "
            SELECT aspectId, avg(score) as scoreAtelier FROM scores s
            LEFT JOIN label_aspects a ON a.aid=s.aid
            WHERE type='resilience' AND a.aspectId LIKE 'PSP_%' AND 
            s.qid IN (
                SELECT qid FROM questionnaires q LEFT JOIN participants p ON p.pid=q.pid
                WHERE atelier=(SELECT atelier FROM participants p INNER JOIN questionnaires q ON q.pid=p.pid WHERE qid=".$qid." LIMIT 1)
            )
            GROUP BY s.aid
            ORDER BY s.aid ASC";
            
            foreach($mysqli->query($queryAtelier) as $row) {
                $scoresAtelierByAspect[$row['aspectId']] = round($row['scoreAtelier'],2);
            }
        }
        
        $scoresClusterByAspect = array();
        if($cluster !== null) {
            $queryCluster = "
            SELECT aspectId, avg(score) as scoreCluster FROM scores s
            LEFT JOIN label_aspects a ON a.aid=s.aid
            WHERE type='resilience' AND a.aspectId LIKE 'PSP_%' AND
            s.qid IN (
                SELECT qid FROM questionnaires q LEFT JOIN participants p ON p.pid=q.pid
                WHERE cluster=(SELECT cluster FROM participants p INNER JOIN questionnaires q ON q.pid=p.pid WHERE qid=".$qid." LIMIT 1)
            )
            GROUP BY s.aid
            ORDER BY s.aid ASC";
            
            foreach($mysqli->query($queryCluster) as $row) {
                $scoresClusterByAspect[$row['aspectId']] = round($row['scoreCluster'],2);
            }
        }
        
        $labelsStr = $dataChart = $atelierData = $clusterData = "";
        $query = "SELECT aspectId, label, score FROM scores s LEFT JOIN label_aspects a ON a.aid=s.aid WHERE s.qid=".$qid." AND type='resilience' AND a.aspectId LIKE 'PSP_%' ORDER BY score ASC, aspectId ASC";
        $results = $mysqli->query($query);
        foreach($results as $row) {
            
            if(trim($row['score']) != "") {
                $labelsStr .= $row['label'].";";
                if(isset($scoresAtelierByAspect[$row['aspectId']])) {
                    $atelierData .= $scoresAtelierByAspect[$row['aspectId']].";";
                    $clusterData .= $scoresClusterByAspect[$row['aspectId']].";";
                }
            }
            
            if($row['score'] != "")
                $dataChart .= round($row["score"], 1).";";
        }
        
        $results->data_seek(0);
        foreach($results as $row) {
            if(trim($row['score']) === null) {
                $labelsStr .= $row['label'].";";
                if(isset($scoresAtelierByAspect[$row['aspectId']])) {
                    $atelierData .= $scoresAtelierByAspect[$row['aspectId']].";";
                    $clusterData .= $scoresClusterByAspect[$row['aspectId']].";";
                }
            }
        }
        $labelsStr = substr($labelsStr, 0, -1);
        $dataChart = substr($dataChart, 0, -1);
        
        $atelierData = ($atelier !== null) ? substr($atelierData, 0, -1) : "";
        $clusterData = ($cluster !== null) ? substr($clusterData, 0, -1) : "";
        
        if($qid !== null) { ?>
        	<h1 class="display-4 border-bottom text-capitalize" id="title"><?= ucfirst($firstname)." ".ucfirst($lastname) ?></h1>
        	
        	<h3 class="my-3">Scores de résilience par aspects</h3>
        	
        	<div class="lead">
        	Section <b>Systèmes de Production et Pratiques</b> en comparaison avec les scores moyens du canton
        	</div>
        	<br>
        	<div id="js-legend" class="chart-legend"></div>
        	<canvas id="myChart" height="130px" style="margin-left:-25px"></canvas>
        	<br>
        	
        	<style>
        	.chart-legend ul { margin-bottom: 5px; }
        	.chart-legend li {
        	   display: inline-block;
        	   margin-right: 10px;
        	}
        	.chart-legend li span{
                display: inline-block;
                width: 12px;
                border-radius: 20px;
                height: 12px;
                margin-right: 5px;
            }
        	</style>
 
            <script>
			$(function(){
				var typesScores = ["byQuestion", "byAspect", "byIndicator"];
				var completed = 0;
				
				
				$.post('pages/generateScores.php', {
    				data:"<?= $data ?>",
    				typeScore: "resilience",
    				output:"db"
				}, function(resp) {
    				completed++;
    				generateChart();
				});	
				
			});
				

			function generateChart() {
				var dataVals = "<?= $dataChart ?>".split(";");
				for(i in dataVals) { dataVals[i] = +dataVals[i]; } 
				var labelsVals = "<?= $labelsStr ?>".split(";");
				var dataAtelier = <?php if($atelier !== null) { ?>"<?= $atelierData ?>".split(";")<?php } else {?>[]<?php }?>;
				var dataCluster = <?php if($cluster !== null) { ?>"<?= $clusterData ?>".split(";")<?php } else {?>[]<?php }?>;
				
				var ctx = document.getElementById("myChart").getContext('2d');
			    var myChart = new Chart(ctx, {
			        type: 'bar',
			        data: {
			            labels: labelsVals,
			            datasets: [{
			                label: "Score moyen atelier",
			                type: "line",
			                pointBackgroundColor: '#f1c40f',
			                backgroundColor: '#f1c40f',
			                data: dataAtelier,
			                fill: false,
			                showLine: false,
			                pointRadius: 4,
			                hitRadius:3,
			                datalabels: {
								display: false
							}
			            }, {
			                label: "Score moyen cluster",
			                type: "line",
			                pointBackgroundColor: '#c0392b',
			                backgroundColor: '#c0392b',
			                data: dataCluster,
			                fill: false,
			                showLine: false,
			                pointRadius: 5,
			                hitRadius:3,
			                datalabels: {
								display: false
							}
			            }, {
			                label: 'Score obtenu',
			                data: dataVals,
			                backgroundColor: 'rgba(189, 195, 199,0.9)',
			                datalabels: {
								align: 'top',
								anchor: 'center'
							}
			            }] 
			        },
			        options: {
			        	legend: { display: false },
			        	plugins: {
							datalabels: {
								color: 'black',
								font: {
									weight: 'bold',
									family: 'Roboto',
									size: 11
								}
							}
						},
			            scales: {
			                yAxes: [{
				                scaleLabel: {
									display: true,
									labelString:'Score obtenu'
				                },
			                    ticks: {
			                        beginAtZero:true
			                    }
			                }],
			                xAxes: [{
			                    ticks: {
			                        autoSkip: false
			                    }
			                }]
			            }
			        }
			    });

			    document.getElementById('js-legend').innerHTML = myChart.generateLegend();
			}
            </script>
            <?php 
        } else {
            echo '<div class="alert alert-danger">Ce questionnaire n\'est pas enregistré dans notre base de données.</div>';   
        }
	}
    ?>
	
  
	<?php } ?>
	
</div>