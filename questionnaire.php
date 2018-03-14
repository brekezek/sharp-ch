<?php 
if(isset($_COOKIE['version'])) {
	if(isset($_COOKIE['filename'])) {
		if(file_exists(DIR_VERSIONS."/".$_COOKIE['version'])) {
			$questManager = QuestionnaireManager::getInstance($_COOKIE['version']);
			
			$questManager->goToAspect($_COOKIE['indexAspect']);
			
			$nbAspects = $questManager->getNumberAspects();
			$currentIndex = $questManager->getCurrentIndex();
			?>
			
			<div style="position: fixed; top:56px; left:0; width:100%">
				<div id="quest-progress" class="w-100 d-flex justify-space-between">
					<?php for($i = 1; $i <= $nbAspects; $i++) {?>
					<div class="<?= $questManager->getColorAspectByIndex($i)->getClass() ?>" style="opacity:<?= ($i <= $currentIndex) ? "1" : "0.25" ?>; height: 4px; width:<?= (100.05/$nbAspects) ?>%;"></div>
					<?php } ?>
				</div>
			</div>
			
			<div class="d-flex" style="opacity:0">-</div>
			
			<div id="questionnaire" class="container" >
				<?php
				$questManager->collectAnswers();
				$questManager->draw();
				$debug = false;
				if($debug) {
					echo '<pre>';
					print_r( $questManager );
				}
				?>
			</div>
			
			
			<div class="container-fluid bg-white w-75 fixed-top p-3 border-left" id="aspects">
				<?php $questManager->drawThumbnails(); ?>
			</div>
			
			
			<div class="modal-backdrop fade show" style="top:56px; display: none"></div>
		<?php
		} else {?>
		<div class="alert alert-danger mt-3" role="alert">
		  Cette version de questionnaire n'existe malheureusement pas!
		</div>
		<?php }
	} else {?>
		<div class="alert alert-danger mt-3" role="alert">
		Le fichier n'existe pas
		</div>
		<?php
	}
} else { ?>
	<div class="alert alert-danger mt-3" role="alert">
		Vous devez d'abord choisir la version du questionnaire avant de démarrer une nouvelle session.
	</div>
<?php }?>