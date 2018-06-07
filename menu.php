<?php $readonly = isset($_COOKIE['indexAspect'], $_COOKIE['readonly']) && $_COOKIE['readonly'] == "true"; ?>

<nav class="navbar navbar-expand-sm navbar-dark sticky-top bg-dark justify-content-between">
	
	<div class="navbar-brand mr-0">
		
		
		<img src="img/logo_menu.jpg" width="80px"> <span class="badge badge-danger text-uppercase"><?= getLang() == "fr" ? "CH" : getLang() ?></span>
		
		<div class="d-inline-flex">
    		<?php if($logged) {?>
    		<a href="<?= getBase() ?>admin/dashboard" id="back" class="btn btn-secondary ml-2">
    			<span class="oi oi-spreadsheet mr-1"></span> Admin
    		</a>
    		<?php } ?>
    		
    		<?php if(!isset($_COOKIE['indexAspect']) && !$displayScorePage) { ?>
            <div class="nav-item dropdown ml-2" data-toggle="hover">
                <div class="btn btn-dark btn-md dropdown-toggle" id="dropdown-lang" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="vertical-align:baseline">
                	 <img src="img/<?= getLang() ?>.png">
                </div>
                <div class="dropdown-menu" id="lang" aria-labelledby="dropdown-lang" style="min-width:0; padding:2px 0">
                	<?php foreach(getLanguageList() as $lang) {?>
                	<div lang="<?= $lang ?>" class="dropdown-item <?php if(isset($_COOKIE['lang']) && $_COOKIE['lang'] == $lang) {?>active<?php } ?>">
						<img src="img/<?= $lang ?>.png">
					</div>
					<?php } ?>
                </div>
            </div>
            <?php } ?>
        </div>
	</div>
  	
	<?php if(isset($_COOKIE['indexAspect'])) {?>
	<div class="text-light position-relative" >
		<?php
		if($readonly) {
		    $jsonFileAnswersMenu = getJSONFromFile(DIR_ANSWERS."/".$_COOKIE['filename']);
		    $lastnameMenu = optInfoAdm($jsonFileAnswersMenu, 2);
		    $firstnameMenu = optInfoAdm($jsonFileAnswersMenu, 3); ?>
			<div style="line-height:1em">
				<div id="name-ro" class="small text-warning text-center text-uppercase"><?= $t['read-only'] ?></div>
				<div style="" class="text-white text-center"><?= sprintf("%s %s", $firstnameMenu, $lastnameMenu) ?></div>
			</div>
		<?php 
		} else {
		    if(isset($_COOKIE['expirationQuest']) && !isset($_COOKIE['readonly'])) {
		        $fTime = getFormattedTime($_COOKIE['expirationQuest'] - time(), "%02d %s, ", "%02d%s%02d");
                ?>
                <div style="line-height:1em" class="time-left" data-toggle="tooltip" data-placement="bottom" title="<?= $t['quest-time-left'] ?>">
                    <div class="small text-warning text-center text-uppercase"><?= $t['restant'] ?></div>
    				<div class="text-white text-center"><?= $fTime['jours'].$fTime['hours'] ?></div>
    			</div>
    			<?php 
		    } else {
		        $jsonFileAnswersMenu = getJSONFromFile(DIR_ANSWERS."/".$_COOKIE['filename']);
		        $lastnameMenu = optInfoAdm($jsonFileAnswersMenu, 2);
		        $firstnameMenu = optInfoAdm($jsonFileAnswersMenu, 3);
		        ?>
		        <div style="line-height:1em">
    				<div class="small text-warning text-center text-uppercase"><?= $t['edit-mode'] ?></div>
    				<div class="text-white text-center"><?= sprintf("%s %s", $firstnameMenu, $lastnameMenu) ?></div>
    			</div>
		        <?php    
		    }
		}?>
	</div>
	
	<div>
		<?php if(isset($_COOKIE['readonly'])) { ?>
		<div class="dropdown d-inline">
			<button id="others" class="btn btn-secondary dropdown-toggle" id="dropd-settings" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<span class="oi oi-cog"></span>
			</button>
			<div class="dropdown-menu mt-3" aria-labelledby="dropd-settings" style="margin-left:-90px">
				<a href="?quit" id="quit" class="dropdown-item"><?= $t['quit_questionnaire'] ?></a>
			</div>	
		</div>
		<?php }?>
		
		<?php if(!$readonly) {?>
		
    		<?php if(isset($_COOKIE['indexAspect'], $_COOKIE['readonly']) && $_COOKIE['readonly'] == "false") { ?>
    		<button id="switch-readonly" class="btn btn-info" type="button">
    			<span class="oi oi-lock-locked mr-1"></span> <?= $t['read-only'] ?>
    		</button>
    		<?php }?>
    		
		<?php } else { ?>
    		<button id="edit" class="btn btn-success" type="button">
    			<span class="oi oi-pencil mr-1"></span> <?= $t['edit'] ?>
    		</button>
		<?php } ?>
		
		<?php if(!$displayScorePage && !isset($_REQUEST['end'])) {?>
		<button id="show-aspects" class="btn btn-primary" type="button">
			<span class="oi oi-grid-three-up"></span>
		</button>
		<?php }?>
	</div>
	
	<?php } else {?>
	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarHome" aria-controls="navbarHome" aria-expanded="false">
		<span class="navbar-toggler-icon"></span>
	</button>
	
	<div class="collapse navbar-collapse" id="navbarHome">
	
		<div class="mr-auto"></div>
		
		<div class="navbar-nav mr-2">
		  <?php
		  if(!$displayScorePage) {
		      $versionsByLang = getVersions();
		      $langUpper = strtoupper(getLang());
		      $langVersion = isset($versionsByLang[$langUpper]) ? $langUpper : "FR"; ?>
		  <div class="nav-item dropdown">
			<div class="btn btn-primary btn-md dropdown-toggle align-items-center" style="display:flex" id="dropdown-version" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<span class="oi oi-cog pr-2"></span>
				<div class="pr-2">
					<div style="font-size:0.61rem; line-height:1.3em"><?= $t['version_quest']?></div>
					<div style="font-size:0.8rem; line-height:1em; font-weight: bold"><?= isset($_COOKIE['version']) ? $_COOKIE['version'] : $versionsByLang[$langVersion][0]['file'] ?></div>
				</div>
			</div>
			<div class="dropdown-menu" id="version" aria-labelledby="dropdown-version">
				<?php
				$i = 0;
				
				foreach($versionsByLang as $lang => $versions) {
					echo '<h6 class="d-flex align-items-center justify-content-start p-1 bg-light mb-0"><img src="img/'.strtolower($lang).'.png" class="mr-2"> '.$lang.' </h6>';
					foreach($versions as $v) {
						$version = getVersionText($v); ?>
						<a <?php if(!isset($_COOKIE['version']) && $langVersion == $lang) { echo 'auto-detected'; } ?> version="<?= $v['file'] ?>" lang="<?= $lang ?>" class="dropdown-item <?php if((!isset($_COOKIE['version']) && $langVersion == $lang) || (isset($_COOKIE['version']) && $_COOKIE['version'] == $v['file'])) {?>active<?php } ?>" href="#">
							<?= $version ?>
						</a>
						<?php
					}
					if($i != count($versionsByLang)-1) {
						echo '<div class="dropdown-divider"></div>';
					}
					$i++;
			  }?>
			</div>
		  </div>
		  <?php } ?>
		</div>

	   <?php if(!$displayScorePage) { ?>
    		<button id="new-quest" class="btn btn-light" type="submit">
    			<?= $t['new_questionnaire']?>
    			<span class="oi oi-caret-right ml-1"></span>
    		</button>
		<?php } else { ?>
			<button id="save" class="btn btn-success px-3 mr-1" style="display:none" type="submit" data-text-oncomplete="<?= $t['save_graphs']?> <span class='oi oi-cloud-download ml-1'></span>" disabled>
    			<?= $t['generation']?> PDF <img src="img/loader-score.svg">
    		</button>
    		
    		<button id="infosScores" class="btn btn-light px-3 mr-1 d-none" type="submit">
    			<span class="oi oi-info"></span>
    		</button>
    		
    		<button id="finishScoreDisplay" class="btn btn-outline-light" type="submit">
    			<?= $t['finish']?>
    			<span class="oi oi-chevron-right pl-1"></span>
    		</button>
		<?php } ?>
		
	</div>
	<?php } ?>
</nav>