<?php 
if(isset($_COOKIE['version'])) {
	
	if(file_exists(DIR_VERSIONS."/".$_COOKIE['version'])) {
		$questManager = QuestionnaireManager::getInstance($_COOKIE['version']);
		
		$goTo = max(1, min($questManager->getNumberAspects(), $_COOKIE['indexAspect']));
		
		$questManager->goToAspect($goTo);
		
		$nbAspects = $questManager->getNumberAspects();
		$currentIndex = $questManager->getCurrentIndex();
		?>
		<div id="quest-progress" class="w-100 d-flex justify-space-between">
			<?php for($i = 1; $i <= $nbAspects; $i++) {?>
			<div class="<?= $questManager->getColorAspectByIndex($i)->getClass() ?>" style="opacity:<?= ($i <= $currentIndex) ? "1" : "0.25" ?>; height: 4px; width:<?= (100/$nbAspects) ?>%;"></div>
			<?php } ?>
		</div>
		
		<div id="questionnaire" class="container" style="margin-top:20px">
			
			<form method="post" action="">
				
				<?php
				$questManager->draw();
				$debug = false;
				if($debug) {
					echo '<pre>';
					print_r( $questManager );
				}
				?>
				

				<div class="bg-light clearfix rounded">
					<?php if($currentIndex > 1 && $currentIndex <= $nbAspects) {?>
						<button type="submit" id="prev" class="btn btn-primary float-left"><?= $t['previous']?></button>
					<?php } ?>
					<?php if($currentIndex < $nbAspects) {?>
						<button type="submit" id="next" class="btn btn-primary float-right"><?= $t['next']?></button>
					<?php } else { ?>
						<button type="submit" id="end" class="btn btn-success float-right"><?= $t['finish']?></button>
					<?php } ?>
				</div>
			</form>
		</div>
		
		
		
		<div class="modal-backdrop fade show" style="top:56px; display: none"></div>
		<div class="container-fluid bg-white w-75 fixed-top p-3 border-left" id="aspects">
			<?php 
			foreach($questManager->getAspects() as $aspect) {
				$aspect->drawThumbnail();
			}
			?>
		</div>
	
	<?php
	} else {?>
	<div class="alert alert-danger mt-3" role="alert">
	  Cette version de questionnaire n'existe malheureusement pas!
	</div>
	<?php }
} else { ?>
	<div class="alert alert-danger mt-3" role="alert">
		Vous devez d'abord choisir la version du questionnaire avant de démarrer une nouvelle session.
	</div>
<?php }?>