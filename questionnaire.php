<?php 
if(isset($_COOKIE['version'])) {
	
	$questManager = new QuestionnaireManager($_COOKIE['version']);
	
	$goTo = max(1, min($questManager->getNumberAspects(), $_COOKIE['indexAspect']));
	
	$questManager->goToAspect($goTo);
	
	$currentIndex = $questManager->getCurrentIndex();
	?>
	<div id="questionnaire" class="container" style="margin-top:80px">
		
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
				<?php if($currentIndex > 1 && $currentIndex < $questManager->getNumberAspects()) {?>
					<button type="submit" id="prev" class="btn btn-primary float-left"><?= $t['previous']?></button>
				<?php } ?>
				<?php if($currentIndex < $questManager->getNumberAspects()) {?>
					<button type="submit" id="next" class="btn btn-primary float-right"><?= $t['next']?></button>
				<?php } else { ?>
					<button type="submit" id="end" class="btn btn-success w-100"><?= $t['finish']?></button>
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
} else {
	echo 'Vous devez d\'abord choisir une version de questionnaire';
}?>