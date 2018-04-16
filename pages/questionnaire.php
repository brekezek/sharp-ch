<?php 
if(isset($_COOKIE['version'])) {
	if(isset($_COOKIE['filename'])) {
		if(file_exists(DIR_VERSIONS."/".$_COOKIE['version'])) {
		    
		    $filepath = getAbsolutePath().DIR_ANSWERS."/".$_COOKIE['filename'];
		    
			$questManager = QuestionnaireManager::getInstance($_COOKIE['version']);
			$questManager->setReadOnly($readonly);
			$questManager->collectAnswers();
			$questManager->goToAspect($_COOKIE['indexAspect']);
			
			$nbAspects = $questManager->getNumberAspects();
			$currentIndex = $questManager->getCurrentIndex();
			
			if(isset($_REQUEST['end']) && $currentIndex == $nbAspects) {
			     
			     if(file_exists($filepath) && filesize($filepath) < MIN_FEEDBACK_FILE_SIZE) {
			         //unlink($filepath);
			         ?>
			         <script>
					 deleteCookie("indexAspect");
					 deleteCookie("filename");
					 document.location = '?home';
			         </script>
			         <?php 
			     } else {
			         $endQuestionnaire = true;
			         include_once('pages/end_quest.php');
			     }
			} else {?>
				<div id="quest-progress-wrapper" style="top: <?= $logged ? "64px" : "56px" ?>;">
					<div id="quest-progress" class="w-100 d-flex justify-space-between">
						<?php for($i = 1; $i <= $nbAspects; $i++) {?>
						<div data-index="<?= $i ?>" class="<?= $questManager->getColorAspectByIndex($i)->getClass() ?> item" style="opacity:<?= ($i <= $currentIndex) ? "1" : "0.25" ?>; width:<?= (100.05/$nbAspects) ?>%;">
							<span><?= $i ?></span>
						</div>
						<?php } ?>
					</div>
				</div>
				
				<div class="d-flex" style="opacity:0; height:35px">-</div>
				
				<div id="questionnaire" class="container" >
					<?php
					if(isset($_COOKIE['score-live']) && $_COOKIE['score-live'] == "true") {
					    if(!isset($_SESSION['resultsDefined'])) {
					       $_SESSION['resultsDefined'] = serialize(array());
					    }  					   
					}

					$questManager->draw();
					$debug = false;
					if($debug) {
						echo '<pre>';
						print_r( $questManager );
					}
					?>
				</div>
				
				
				<div class="container-fluid bg-white w-75 fixed-top p-3 border-left" id="aspects">
					<div class="aspect-container">
						<?php $questManager->drawThumbnails(); ?>
					</div>
				</div>
				
				
				<div class="modal-backdrop fade show" style="top:56px; display: none"></div>
				
				<br>
				
				<?php if($logged) {?>
				<div class="fixed-bottom bg-light border-top border-right p-2" style="left:0; width: 110px" id="score-live">
					<a class="btn btn-secondary text-white w-100" id="enabled-live-score">Live score</a>
				</div>
				<?php } ?>
				
				<?php 
			} 
		} else {?>
			<div class="alert alert-danger mt-3" role="alert"><?= $t['quest_version_not_exist'] ?></div>
		<?php }
	} else {?>
		<div class="alert alert-danger mt-3" role="alert"><?= $t['error_restart_quest'] ?></div>
		<?php
		if(isset($_COOKIE['indexAspect'])) unset($_COOKIE['indexAspect']);
	}
} else { ?>
	<div class="alert alert-danger mt-3" role="alert"><?= $t['alert_choose_quest_version'] ?></div>
<?php }?>