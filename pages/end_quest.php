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

<link rel="stylesheet" href="css/circle-chart.css">
	
<div class="container">
	<div class="d-flex" style="opacity:0">-</div>
	
	<?php if(!isset($_GET['score'])) {
	    $endingTime = $_COOKIE['expirationQuest'];
	    $startingTime = $endingTime - LIFE_COOKIE_QUEST_PENDING*60*60*24;
	    
	    $timeUsed = time() - $startingTime;
	    $timeLeft = $endingTime - time();
       ?>
	
	
	<div class="d-flex justify-content-center align-items-center">
		
		<?php if(isset($_COOKIE['expirationQuest'])) {?>
    		<div class="mr-4 border-right" style="min-width: 250px;">
            	<?php drawCircleChartForTime("blue", $timeUsed, $t['time-used']); ?>
            	<hr class="my-4">
            	<?php drawCircleChartForTime("orange", $timeLeft, $t['time-left']); ?>
        	</div>
    	<?php } ?>
    	
    	<div class="">
        	<div class="my-3 text-justify">
        		<h1 class="display-4"><?= $t['thank_you'] ?>!</h1>
        		<p class="lead"><?= $t['txt1_end_quest'] ?> !</p>	
        		<div class="border-top border-bottom py-3 d-flex align-items-center justify-content-center">
        			<div class="oi oi-bar-chart text-center display-4 mx-2 mr-4" style="min-width: 64px; color: #7f8c8d"></div>
        			<div><?= $t['txt2_end_quest'] ?></div>
        		</div>
        		
        		<div class="border-bottom py-3 d-flex align-items-center justify-content-center">
        			<div class="oi oi-print text-center display-4 mx-2 mr-4" style="min-width: 64px; color: #7f8c8d"></div>
        			<div><?= $t['txt_print_end_quest'] ?></div>
        		</div>
        	</div>
        	
        	
        	<p class="text-center"><a class="btn btn-dark btn-lg" href="scores" role="button"><?= $t['display_my_score'] ?> <span class="oi oi-pie-chart ml-1"></span></a></p>
		</div>
	</div>
	
	<script>
    $(function(){
        $('#finishScoreDisplay').remove();
    	invokeScoreGeneration(function(resp) {});
    });
    </script>
	
	<?php
	} else {?>
	
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
    body { overflow-x:hidden; }
    .pill {
        margin:auto;
        vertical-align:middle;
        border-radius:35px;
        height:16px;
        width:16px;
        transition: all 0.2s linear;
    }
    .pill:hover {
        box-shadow: inset 0 0 100px 100px rgba(0,0,0,0.5); 
    }
    .pill.green { background: #4CC790; }
    .pill.blue { background:#3c9ee5; }
    .pill.void { background: #ccc; }
	</style>
	
	<?php 
	$qid = null;
	$name = "";
	
	if ($stmt = $mysqli->prepare(
	    "SELECT qid, firstname, lastname, p.rid, cluster, rlabel_".getLang().", pslabel_".getLang()." FROM questionnaires q
        LEFT JOIN participants p ON p.pid = q.pid
        LEFT JOIN regions r ON r.rid=p.rid
        LEFT JOIN prod_systems ps ON ps.psid=p.cluster
        WHERE file LIKE ?")) {
        
        $filenameBN = "%".basename($filename);
       
        $stmt->bind_param("s", $filenameBN);
        $stmt->execute();
        $stmt->bind_result($qid, $firstname, $lastname, $rid, $cluster, $rlabel, $pslabel);
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
            if(trim($name) == "") {
                $name = '<span class="text-muted">'.$t['noname'].'</span>';   
            }
            ?>
            
        	<h1 class="display-4 border-bottom text-capitalize mb-4 d-flex justify-content-start align-items-center" id="title">
        		<div class="mr-auto"><?= $name ?></div>   
        	</h1>
        	
        	<?php 
        	$nbParticipantsClusterWithScores = 0;
        	$nbParticipantsClusterTotal = 0;
        	$nbParticipantsClusterWithQuestionnaire = 0;
        	
        	$nbParticipantsRegionWithScores = 0;
        	$nbParticipantsRegionTotal = 0;
        	$nbParticipantsRegionWithQuestionnaire = 0;
        	
        	if($rid !== null || $cluster !== null) {
        	    
        	    
        	    if($cluster !== null) {
            	    $queryParticipantsCluster = 
            	       "SELECT * FROM (
                            SELECT p.pid, q.qid, firstname, lastname, r.rlabel_".getLang().", COUNT(s.sid) as nbScores, 1 as res FROM participants p
                            LEFT JOIN questionnaires q ON q.pid=p.pid
                            LEFT JOIN scores s ON s.qid=q.qid
                            LEFT JOIN regions r ON r.rid=p.rid
                            WHERE cluster=".$cluster." AND s.type='resilience' AND (p.deleted IS NULL OR p.deleted = 0) AND (q.deleted IS NULL OR q.deleted=0)
                            GROUP BY s.qid
                            
                            UNION 
                            
                            SELECT p.pid, q.qid, firstname, lastname, r.rlabel_".getLang().", COUNT(s.sid) as nbScores, 0 as res FROM participants p
                            LEFT JOIN questionnaires q ON q.pid=p.pid
                            LEFT JOIN scores s ON s.qid=q.qid
                            LEFT JOIN regions r ON r.rid=p.rid
                            WHERE cluster=".$cluster." AND (p.deleted IS NULL OR p.deleted = 0) AND (q.deleted IS NULL OR q.deleted=0)
                            GROUP BY s.qid
                        ) req1 
                        GROUP BY req1.pid 
                        ORDER BY lastname, firstname";
                    $participantsCluster = $mysqli->query($queryParticipantsCluster);
                    foreach($participantsCluster as $p) {
                        $nbParticipantsClusterTotal++;
                        if($p['qid'] !== NULL) $nbParticipantsClusterWithQuestionnaire++;
                        if($p['nbScores'] > 25 && $p['res'] == 1) $nbParticipantsClusterWithScores++;
                    }
        	    }
        	    
        	    if($rid !== null) {
                    $participantsRegion = $mysqli->query(
                    "SELECT * FROM (
                            SELECT p.pid, q.qid, firstname, lastname, pslabel_".getLang().", COUNT(s.sid) as nbScores, 1 as res FROM participants p
                            LEFT JOIN questionnaires q ON q.pid=p.pid
                            LEFT JOIN scores s ON s.qid=q.qid
                            LEFT JOIN prod_systems ps ON ps.psid=p.cluster
                            WHERE rid=".$rid." AND s.type='resilience' AND (p.deleted IS NULL OR p.deleted = 0) AND (q.deleted IS NULL OR q.deleted=0)
                            GROUP BY s.qid
    	    
                            UNION
    	    
                            SELECT p.pid, q.qid, firstname, lastname, pslabel_".getLang().", COUNT(s.sid) as nbScores, 0 as res FROM participants p
                            LEFT JOIN questionnaires q ON q.pid=p.pid
                            LEFT JOIN scores s ON s.qid=q.qid
                            LEFT JOIN prod_systems ps ON ps.psid=p.cluster
                            WHERE rid=".$rid." AND (p.deleted IS NULL OR p.deleted = 0) AND (q.deleted IS NULL OR q.deleted=0)
                            GROUP BY s.qid
                        ) req1
                        GROUP BY req1.pid
                        ORDER BY lastname, firstname");
                   
                    foreach($participantsRegion as $p) {
                        $nbParticipantsRegionTotal++;
                        if($p['qid'] !== NULL) $nbParticipantsRegionWithQuestionnaire++;
                        if($p['nbScores'] > 25 && $p['res'] == 1) $nbParticipantsRegionWithScores++;
                    }
        	    }
                ?>
            	
            	<div class="display-4" style="font-size:2rem"><?= $t['graphs-title-data-cluster']?></div>
            	
            	<div class="bg-light rounded p-3 my-3 mb-4 d-flex" id="general-data">
            		
            		<?php if($nbParticipantsClusterTotal > 0) {?>
            		<div class="<?= ($nbParticipantsRegionTotal > 0) ? "w-50 border-right" : "w-100" ?>">
            			<div class="text-center lead mb-1" >
                    		<div class="font-weight-bold text-uppercase" style="font-size:1.8rem; line-height:1em"><?= $t['cluster']?></div>
                    		<div class="text-muted"><?= $pslabel ?></div>
                		</div>
                		<hr>
                		
                		<div class="d-flex mb-3 align-items-center" style="justify-content:space-evenly">
                    	<?php  
                        echo '<div style="min-width: 180px; max-width:50%">';
                        drawCircleChart("blue", $nbParticipantsClusterTotal, $nbParticipantsClusterWithQuestionnaire, $t['part-started-quest'], "n");
                        echo '</div>';
                        
                        echo '<div style="min-width: 180px; max-width:50%">';
                        drawCircleChart("green", $nbParticipantsClusterTotal, $nbParticipantsClusterWithScores, $t['part-contribute-sysprod'], "n");
                        echo '</div>';
                        ?>
                        </div>
                        
                        <table class="table mr-3 table-striped table-hover table-sm" style="max-width:98%; margin-right:13px">
                        	<thead>
                        		<tr>
                        			<th><?= $t['lastname']." ".$t['firstname']?></th>
                        			<th class="text-center"><?= $t['started']?></th>
                        			<th class="text-center"><?= $t['count-in-average']?></th>
                        		</tr>
                        	</thead>
                        	<tbody>
                        		<?php
                        		$i = 0;
                        		foreach($participantsCluster as $p) {
                        		    $cond1 = ($p['qid'] !== NULL); $cond2 = ($p['nbScores'] > 25 && $p['res'] == 1); ?>
                        		<tr title="<?= $p['rlabel_'.getLang()]?>" data-toggle="tooltip" data-placement="right" style="<?php if($i >= 5) { echo 'display:none;'; } if($p['qid'] == $qid) { echo 'background: rgb(46, 204, 113, 0.3)'; }?>">
                        			<td width="245px" class="text-capitalize"><?= sprintf("<span class='text-uppercase'>%s</span> %s", $p['lastname'], $p['firstname']) ?></td>
                        			<td class="text-center align-middle"><?php echo '<div title="'.($cond1 ? $t['started-fill-quest'] : $t['not-started-fill-quest']).'" data-toggle="tooltip" data-placement="top" class="pill '.($cond1 ? 'blue' : 'void').'"></div>'; ?></td>
                        			<td class="text-center align-middle"><?php echo '<div title="'.($cond2 ? $t['contribute-avg-score'] : $t['not-contribute-avg-score']).'" data-toggle="tooltip" data-placement="top" class="pill '.($cond2 ? 'green' : 'void').'"></div>'; ?></td>
                        		</tr>
                        		<?php $i++; } ?>
                        	</tbody>
                        	<?php if($i >= 5) {?>
                        	<tfoot>
                        		<tr><td colspan="3">
                        			<div class="btn btn-secondary btn-sm w-100"><?= $t['display-all']?> (<?= sprintf("%s %s", $i - 5, $t['others'])?>)</div>
                        		</td></tr>
                        	</tfoot>
                        	<?php } ?>
                        </table>
                        
                        <?php if($nbParticipantsClusterWithScores <= 1) {?>
                        <div class="bg-dark rounded text-white text-center py-1 px-1 small" style="width:98%;">
                        <?= $t['only-you-answered-sysprod']?><br>
                        <?= $t['avg-graph-not-reliable']?>
                        </div>
                        <?php } ?>
                    </div>
                    <?php } ?>
                    
                    <?php if($nbParticipantsRegionTotal > 0) {?>
                    <div class="<?= ($nbParticipantsClusterTotal > 0) ? "w-50" : "w-100" ?>">
                    	<div class="text-center lead mb-1" >
                    		<div class="font-weight-bold text-uppercase" style="font-size:1.8rem; line-height:1em"><?= $t['region']?></div>
                    		<div class="text-muted"><?= $rlabel ?></div>
                		</div>
                		<hr>

                        <div class="d-flex mb-3 align-items-center" style="justify-content:space-evenly">
                    	<?php  
                        echo '<div style="min-width: 180px; max-width:50%">';
                        drawCircleChart("blue", $nbParticipantsRegionTotal, $nbParticipantsRegionWithQuestionnaire, $t['part-started-quest'], "n");
                        echo '</div>';
                        
                        echo '<div style="min-width: 180px; max-width:50%">';
                        drawCircleChart("green", $nbParticipantsRegionTotal, $nbParticipantsRegionWithScores, $t['part-contribute-region'], "n");
                        echo '</div>';
                        ?>
                        </div>
                    
                    	
                    	<table class="table table-striped table-hover table-sm" style="max-width:98%; margin-left:13px">
                        	<thead>
                        		<tr>
                        			<th><?= $t['lastname']." ".$t['firstname']?></th>
                        			<th class="text-center"><?= $t['started']?></th>
                        			<th class="text-center"><?= $t['count-in-average']?></th>
                        		</tr>
                        	</thead>
                        	<tbody>
                        		<?php
                        		$i = 0;
                        		foreach($participantsRegion as $p) {
                        		    $cond1 = ($p['qid'] !== NULL); $cond2 = ($p['nbScores'] > 25 && $p['res'] == 1); ?>
                        		<tr title="<?= $p['pslabel_'.getLang()]?>" data-toggle="tooltip" data-placement="left" style="<?php if($i >= 5) { echo 'display:none;'; } if($p['qid'] == $qid) { echo 'background: rgb(46, 204, 113, 0.3)'; }?>">
                        			<td width="245px" class="text-capitalize"><?= sprintf("<span class='text-uppercase'>%s</span> %s", $p['lastname'], $p['firstname']) ?></td>
                        			<td class="text-center align-middle"><?php echo '<div title="'.($cond1 ? $t['started-fill-quest'] : $t['not-started-fill-quest']).'" data-toggle="tooltip" data-placement="top" class="pill '.($cond1 ? 'blue' : 'void').'"></div>'; ?></td>
                        			<td class="text-center align-middle"><?php echo '<div title="'.($cond2 ? $t['contribute-avg-score'] : $t['not-contribute-avg-score']).'" data-toggle="tooltip" data-placement="top" class="pill '.($cond2 ? 'green' : 'void').'"></div>'; ?></td>
                        		</tr>
                        		<?php $i++; } ?>
                        	</tbody>
                        	<?php if($i >= 5) {?>
                        	<tfoot>
                        		<tr><td colspan="3">
                        			<div class="btn btn-secondary btn-sm w-100"><?= $t['display-all']?> (<?= sprintf("%s %s", $i - 5, $t['others'])?>)</div>
                        		</td></tr>
                        	</tfoot>
                        	<?php } ?>
                        </table>
                        
                        <?php if($nbParticipantsRegionWithScores <= 1) {?>
                        <div class="bg-dark rounded text-white text-center py-1 px-2 small" style="width:98%; margin-left:13px">
                        <?= $t['only-you-answered-region']?><br>
                        <?= $t['avg-graph-not-reliable']?>
                        </div>
                        <?php } ?>
                    </div>
                    <?php } ?>
                    
                </div>
                
                <?php 
            }
            ?>
        	
        	<div class="display-4" style="font-size:2rem"><?= $t['your-evaluation']?></div>
        	
        	<div class="border rounded p-3 my-3 mb-4">
        	
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
                            	<canvas id="chart_<?= $section ?>" data-section="<?= $section?>" height="<?= floor(80 + $nbLabels[$section]*1.2 + $maxLabelLength[$section]*1.2) ?>px" style="margin-left:0px"></canvas>
                            	 
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
        	<div class="pb-4">
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
                        	<canvas id="chart_<?= $section ?>" data-section="<?= $section?>" height="140px" ></canvas>
                        	 
                        	<div style="position:absolute; top:-50000px">
                              <canvas id="hidden_chart_<?= $section ?>" data-section="<?= $section?>" width="1200" height="650"></canvas>
                            </div>
                            
                    	<?php } 
    				}?>
            	</div>
			</div>
 			</div>
 			
            <script>
			$(function(){
				
				$('#finishScoreDisplay').click(function(){
	    			deleteCookie("scores-display");
	    			deleteCookie("filename");
	    			
	    			document.location = '<?= getBase() ?>home';
	    		});

	    		$('#general-data table tfoot .btn').click(function(){
					$(this).parents("table").find("tbody tr:hidden").fadeIn("fast");
					$(this).hide();
	    		});

	    		<?php if(($totalOtherThanResilience <= 0 || trim($labelsStr) == "") && !isset($_GET['refresh']) ) {?>
	    		invokeScoreGeneration(function(resp) {
	    			document.location = '<?= getBase() ?>scores<?= (isset($_GET['setData']) ? "/data/".$_GET['setData'] : "") ?>/refresh';
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
						filename: "<?= $filename ?>",
						version: "<?= $version ?>",
						person:"<?= $name ?>",
						sections:"<?= urlencode(serialize($sections)) ?>", 
						tableResilience: "<?= urlencode(serialize($tableResilienceForPDF)) ?>"
					},
					function(filename) {
						
						$('#save').removeAttr("disabled").html($('#save').attr("data-text-oncomplete")).click(function() {
							document.location = '<?= getBase() ?>download.php?file='+filename;
	    				});
				    	
				    });	
				}
			}
			
		

			function getGraphJSONData(obj) {
				
				var labelsVals = obj.labels === false ? "" : obj.labels.split(";");

				var dataVals = obj.personnal === false ? "" : obj.personnal.split(";");
				for(i in dataVals) { dataVals[i] = +dataVals[i]; } 
				
				var dataRegion = <?php if($nbParticipantsRegionWithScores <= 1) {?>[];<?php } else {?>obj.rid == "" ? [] : obj.rid.split(";");<?php } ?>
				var dataCluster = <?php if($nbParticipantsClusterWithScores <= 1) {?>[];<?php } else {?>obj.cluster == "" ? [] : obj.cluster.split(";");<?php } ?>

				console.log(labelsVals);
				console.log(dataVals);

				var colorScore = obj.colors.label_text;

				var datasetsArray = [];
				if(dataRegion.length > 0) {
    				datasetsArray.push({
    	                label: "<?= sprintf($t['score_moyen_atelier'], $nbParticipantsRegionWithScores) ?>",
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
		                label: "<?= sprintf($t['score_moyen_cluster'], $nbParticipantsClusterWithScores) ?>",
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
			        		onComplete: function(e) {
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