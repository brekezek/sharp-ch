<?php 
if(isset($_COOKIE['filename'])) {
    $filename = $_COOKIE['filename'];
}
if(isset($_COOKIE['version'])) {
    $version = $_COOKIE['version'];
}

if(isset($_GET['score'], $_GET['setData'])) {
    $data = unserialize(urldecode(base64_decode($_GET['setData'])));
    $filename = $data['filename'];
    $version = $data['version'];
}
?>

<script>
function invokeScoreGeneration(callback) {
	<?php $data = $filename.":".$version.":".urlencode(serialize(array())); ?>
	$.post('pages/generateScores.php', {
		data:"<?= $data ?>",
		typeScore: "db_all",
		output:"db"
	}, callback);
}
</script>
	
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
	<script>
    $(function(){
    	invokeScoreGeneration(function(resp) {});
    });
    </script>
	<?php } else {?>
	
	<script src="js/Chart.min.js"></script>
	<script src="js/chartjs-plugin-datalabels.min.js"></script>
	<!--  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.2.61/jspdf.min.js"></script> 
	<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.3/FileSaver.min.js"></script> -->
	
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
	
	
	<?php 
	$qid = null;
	$name = "";
	
	if ($stmt = $mysqli->prepare(
	    "SELECT qid, firstname, lastname, rid, cluster FROM questionnaires q
        LEFT JOIN participants p ON p.pid = q.pid
        WHERE file LIKE ?")) {
        
        $filename = "%".basename($filename);
        $stmt->bind_param("s", $filename);
        $stmt->execute();
        $stmt->bind_result($qid, $firstname, $lastname, $rid, $cluster);
        $stmt->fetch();
        $stmt->free_result();
        $stmt->close();
        
        $sections = getQuestionnaireSections($version, array("ADM"));
        
        $dataForChart = array("qid" => $qid, "rid" => $rid, "cluster" => $cluster);
        $dataChart = array();
        $dataSectionPDF = array();

        if($qid !== null) {
            
            $scores = $mysqli->query("SELECT sid FROM scores WHERE qid=".$qid);
            $haveScores = $scores->num_rows;
            
            $name = ucfirst($firstname)." ".ucfirst($lastname);
            ?>
        	<h1 class="display-4 border-bottom text-capitalize mb-4" id="title"><?= $name ?></h1>
        	
        	<h4 class="my-3">
        	<?= $t['score_resilience_par_aspect']?>, 
        	<div class="d-inline text-muted lead"><?= $t['comparaison_score_avg_canton']?></div>
        	</h4>

        	<?php 
        	$objectsCharData = array();
        	$maxLabelLength = array();
        	$nbLabels = array();
        	foreach($sections as $section => $infoSection) {
        	    if(!isset($maxLabelLength[$section])) {
        	        $maxLabelLength[$section] = -1;
        	    }
        	    if(!isset($nbLabels[$section])) {
        	        $nbLabels[$section] = 0;
        	    }
        	    
        	    $objectsCharData[$section] = new ChartData($dataForChart, $section);
        	    $dataChart[$section] = $objectsCharData[$section]->getValues();
        	    
        	    foreach(explode(";", $dataChart[$section]['labels']) as $label) {
        	        $nbLabels[$section]++;
            	    $lengthCurrentLabel = strlen($label);
            	    if($lengthCurrentLabel > $maxLabelLength[$section]) {
            	       $maxLabelLength[$section] = $lengthCurrentLabel;  
            	    }
        	    }
        	}
        	
        	$labelsStr = "";
        	foreach($sections as $section => $infoSection) {
        	    $sectionColor = new Color($infoSection['color']);

        	    $labelsStr .= $dataChart[$section]['labels'];
        	    
                $dataChart[$section]['colors'] = array(
                    "personnal" => $sectionColor->getRGBA(),
                    "label_text" => $sectionColor->getTextColor()
                );
            	?>
            	<div class="pb-4 mb-4">
                	<div class="lead p-2 px-3 rounded mb-3 <?= $sectionColor->getClass() ?>">
                	<?= $infoSection['title'] ?>
                	</div>
        			<div class="chart-wrapper">
        				<?php
        				if($haveScores == 0) {
        				    echo '<div class="text-center"><img src="img/loader-56.svg"></div>';
        				} else {
            				if($objectsCharData[$section]->hasScores() == false) {?>
            					<div class="alert alert-warning"><?= $t['not-enough-data-display-chart']?></div>
            				<?php } else { ?>
                            	<div class="chart-legend"></div>
                            	<canvas id="chart_<?= $section ?>" data-section="<?= $section?>" height="<?= floor(80 + $nbLabels[$section]*1.2 + $maxLabelLength[$section]*1.2) ?>px" style="margin-left:-25px"></canvas>
                            	 
                            	<div style="position:absolute; top:-50000px">
                                  <canvas id="hidden_chart_<?= $section ?>" data-section="<?= $section?>" width="1200" height="650"></canvas>
                                </div>
                                
                        	<?php } 
        				}?>
                	</div>
    			</div>
        	<?php }
        	?>
        	


        	
        	<div class="my-4 border-top border-bottom py-5">
            	<h4 class="my-2 mb-3">
            	<?= $t['resilience_importance_par_section']?>
            	</h4>
            	
            	<table class="table table-sm table-striped">
            	<thead>
            		<tr>
            			<th></th>
            			<th class="align-middle text-center"><?= $t['conduite-exploitation']?></th>
            			<th class="align-middle text-center"><?= $t['resilience']?></th>
            			<th class="align-middle text-center"><?= $t['importance']?></th>
            		</tr>
            	</thead>
            	<tbody>
            	<?php 
            	$totalOtherThanResilience = -1;
            	$tableResilienceForPDF = array();
            	foreach($sections as $section => $infoSection) {
            	    if($section == "byIndicator") continue;
            	    $values = $objectsCharData[$section]->getValues();
            	    
            	    $totalOtherThanResilience += $values['conduiteExploitation'] + $values['importance'];
            	    $conduiteExploitation = round($values['conduiteExploitation'],1);
            	    $avgResilience = round($values['avgPersonnalResilience'],1);
            	    $avgImportance = round($values['importance'],1);
            	    
            	    $tableResilienceForPDF[] = array($infoSection['title'], $conduiteExploitation, $avgResilience, $avgImportance);
            	    ?>
            	<tr>
            		<td><?= $infoSection['title'] ?></td>
            		<td class="align-middle text-center"><?= $conduiteExploitation?></td>
            		<td class="align-middle text-center"><?= $avgResilience ?></td>
            		<td class="align-middle text-center"><?= $avgImportance ?></td>
            	</tr>
            	<?php } ?>
            	</tbody>
            	</table>
 			</div>
 			
 			        	
        	
        	<h4 class="my-3 mt-5">
            	<?= $t['scores_by_indicators']?>, 
            	<div class="d-inline text-muted lead"><?= $t['comparaison_score_avg_canton']?></div>
        	</h4>
        	<div class="pb-4 border-bottom">
    			<div class="chart-wrapper">
    				<?php
    				if($haveScores == 0) {
    				    echo '<div class="text-center"><img src="img/loader-56.svg"></div>';
    				} else {
    				    $sections['byIndicator'] = array("title" => $t['scores_by_indicators'], "color" => "blue");
    				    $section = "byIndicator";
    				    $objectsCharData[$section] = new ChartData($dataForChart, $section);
    				    $dataChart[$section] = $objectsCharData[$section]->getValues();
    				    $dataChart[$section]['colors'] = array(
    				        "personnal" => "#fad390",
    				        "label_text" => "#664600"
    				    );
    				    
        				if($objectsCharData[$section]->hasScores() == false) {?>
        					<div class="alert alert-warning"><?= $t['not-enough-data-display-chart']?></div>
        				<?php } else { ?>
                        	<div class="chart-legend"></div>
                        	<canvas id="chart_<?= $section ?>" data-section="<?= $section?>" height="140px" style="margin-left:-25px"></canvas>
                        	 
                        	<div style="position:absolute; top:-50000px">
                              <canvas id="hidden_chart_<?= $section ?>" data-section="<?= $section?>" width="1200" height="650"></canvas>
                            </div>
                            
                    	<?php } 
    				}?>
            	</div>
			</div>
 		
            <script>
			$(function(){
				
				$('#finishScoreDisplay').click(function(){
	    			deleteCookie("scores-display");
	    			document.location = '?success';
	    		});

	    		<?php if(($totalOtherThanResilience <= 0 || trim($labelsStr) == "") && !isset($_GET['refresh']) ) {?>
	    		invokeScoreGeneration(function(resp) {
	    			document.location = '?<?= $_SERVER['QUERY_STRING'] ?>&refresh';
			    });
	    		<?php } else {?>
	    			invokeScoreGeneration(function(resp) {});
					startChartGeneration();
	    		<?php } ?>

			});

			function startChartGeneration() {
				<?php $json = base64_encode(json_encode($dataChart, JSON_FORCE_OBJECT)); ?>
				var obj = jQuery.parseJSON ( atob('<?php echo $json; ?>') );
				
				var sections = "<?= implode(";", array_keys($sections)) ?>".split(";");
				for(i in sections) {
					if($('#chart_'+sections[i]).length > 0) {
						generateChart(sections[i], obj[sections[i]]);
					}
				}	
			}
				
			
			function generateChart(sectionName, json) {
				var backgroundColor = 'white';
				Chart.plugins.register({
				    beforeDraw: function(c) {
				        var ctx = c.chart.ctx;
				        ctx.fillStyle = backgroundColor;
				        ctx.fillRect(0, 0, c.chart.width, c.chart.height);
				    }
				});
				

				// Canvas affiché
				var ctx = document.getElementById("chart_"+sectionName).getContext('2d');
				var data = getGraphJSONData(json);
			    myChart = new Chart(ctx, data);

			    
				// Canvas caché, pour la generation de PDF
			    var ctxHidden = document.getElementById('hidden_chart_'+sectionName).getContext('2d');
			    //Chart.defaults.global.defaultFontSize = 13;
			    var canvasForPDF = new Chart(ctxHidden, data);
				
		    
			    $('#chart_'+sectionName).parents(".chart-wrapper").find('.chart-legend').html(myChart.generateLegend());
			}


			var graphsB64 = "";
			var idSectionsStr = "";
			function enableSave(id) {
				var canvas = $('canvas#'+id);
				var b64Text = canvas.get(0).toDataURL();
				
				idSectionsStr += id.replace("hidden_chart_","")+",";
				graphsB64 += b64Text.replace('data:image/png;base64,', '')+",";
				
				if($('canvas:last').attr("id") == id) {
					$('#save').fadeIn("fast");
					$.post('pages/chartsToPDF.php',
					{
						b64: graphsB64,
						idSections:idSectionsStr,
						person:"<?= $name ?>",
						sections:"<?= urlencode(serialize($sections)) ?>", 
						tableResilience: "<?= urlencode(serialize($tableResilienceForPDF)) ?>"
					},
					function(filename) {
						
						$('#save').removeAttr("disabled").html($('#save').attr("data-text-oncomplete")).click(function() {
							document.location = 'download.php?file='+filename;
	    				});
				    	
				    });	
				}
			}
			
		

			function getGraphJSONData(obj) {
				
				var labelsVals = obj.labels === false ? "" : obj.labels.split(";");

				var dataVals = obj.personnal === false ? "" : obj.personnal.split(";");
				for(i in dataVals) { dataVals[i] = +dataVals[i]; } 
				
				var dataRegion = obj.rid == "" ? [] : obj.rid.split(";");
				var dataCluster = obj.cluster == "" ? [] : obj.cluster.split(";");

				console.log(labelsVals);
				console.log(dataVals);

				var colorScore = obj.colors.label_text;

				var datasetsArray = [];
				if(dataRegion.length > 0) {
    				datasetsArray.push({
    	                label: "<?= $t['score_moyen_atelier']?>",
    	                type: "line",
    	                pointBackgroundColor: '#f1c40f',
    	                backgroundColor: '#f1c40f',
    	                data: dataRegion,
    	                fill: false,
    	                showLine: false,
    	                pointRadius: 4,
    	                hitRadius:3,
    	                datalabels: {
    						display: false
    					}
    	            });
				}

				if(dataCluster.length > 0) {
					datasetsArray.push({
		                label: "<?= $t['score_moyen_cluster']?>",
		                type: "line",
		                pointBackgroundColor: '#8e44ad',
		                backgroundColor: '#8e44ad',
		                data: dataCluster,
		                fill: false,
		                showLine: false,
		                pointRadius: 5,
		                hitRadius:3,
		                datalabels: {
							display: false
						}
		            });
				}

				datasetsArray.push({
	                label: '<?= $t['mon_score_obtenu']?>',
	                data: dataVals,
	                backgroundColor: obj.colors.personnal,
	                datalabels: {
						align: 'top',
						anchor: 'center'
					}
	            });
				
				return {
			        type: 'bar',
			        data: {
			        	hoverBorderColor: "orange",
			            labels: labelsVals,
			            datasets: datasetsArray
			        },
			        devicePixelRatio: 5*window.devicePixelRatio,
			        options: {
			        	animation : {
			        		onComplete(e) {
		        		      this.options.animation.onComplete = null; //disable after first render to avoid firing each time we hover
		        		      //console.log(e);
		        		      if(e.chart.canvas.id.indexOf("hidden") > -1) {
		        		      	enableSave(e.chart.canvas.id);
		        		      }
		        		      //this.initExport();
		        		   }
			            },
			        	legend: {
			        		display: false
			            },
			        	plugins: {
							datalabels: {
								color: function(context) {
								    var index = context.dataIndex;
								    var value = context.dataset.data[index];
								    return value < 2 ? "black" : colorScore;
								},
								font: {
									weight: 'bold',
									family: 'Roboto',
									size: 14
								}
							}
						},
			            scales: {
			                yAxes: [{
				                scaleLabel: {
									display: true,
									labelString:'<?= $t['score_obtenu']?>'
				                },
			                    ticks: {
			                        beginAtZero:true,
			                        min:0,
			                        max:10,
			                        fontColor: "#000"
			                    }
			                }],
			                xAxes: [{
			                    ticks: {
			                        autoSkip: false,
			                        fontColor: "#000"
			                    }
			                }]
			            }
			        }
			    };
			}
            </script>
            <?php 
        } else {
            echo '<div class="alert alert-danger">'.$t['quest-not-existing'].'</div>';   
        }
	}
}
    ?>
    
    <script>
	$(function(){
		$('#infosScores').click(function(){
			alert("pas encore implémenté");
		});
	});
    </script>

	
</div>