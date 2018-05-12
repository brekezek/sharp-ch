<?php $readonly = isset($_COOKIE['indexAspect'], $_COOKIE['readonly']) && $_COOKIE['readonly'] == "true"; ?>

<nav class="navbar navbar-expand-sm navbar-dark fixed-top bg-dark justify-content-between">
	
	<div class="navbar-brand mr-0">
		
		
		<img src="img/logo_menu.jpg" width="80px"> <span class="badge badge-danger text-uppercase"><?= getLang() == "fr" ? "CH" : getLang() ?></span>
		
		<div class="d-inline-flex">
    		<?php if($readonly || $logged) {?>
    		<a href="admin/dashboard" id="back" class="btn btn-secondary ml-2">
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
	<div class="text-light" >
		<?php if($readonly) {
		    echo '<span class="oi oi-eye mr-1"></span> <span id="name-ro">'.$t['read-only'].'</span>';
		} else {
		    if(isset($_COOKIE['expirationQuest'])) {
    		    $expirationQuest = $_COOKIE['expirationQuest'];
                $time = ($expirationQuest - time());
                $days = floor($time / (3600*24));
                $hours = floor(($time - $days*24*3600) / 3600);
                $min = floor(($time - $days*24*3600 - $hours*3600) /  60);
                echo '<div class="time-left" data-toggle="tooltip" data-placement="bottom" title="'.$t['quest-time-left'].'"><span class="oi oi-clock mr-1"></span> <b>'.$t['restant'].'</b>: '.$days." ".$t['jours'].", ".$hours."h".$min.'</div>';
		    } else {
		        echo $_COOKIE['version'];   
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
    		<button id="switch-readonly" class="btn btn-primary" type="button">
    			<span class="oi oi-lock-locked mr-1"></span> <?= $t['read-only'] ?>
    		</button>
    		<?php }?>
    		
		<?php } else { ?>
    		<button id="edit" class="btn btn-primary" type="button">
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
		  <?php if(!$displayScorePage) { ?>
		  <div class="nav-item dropdown">
			<a class="btn btn-primary btn-md dropdown-toggle" href="#" id="dropdown-version" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<span class="oi oi-cog mr-1"></span> <?= isset($_COOKIE['version']) ? $_COOKIE['version'] : $t['choose_version'] ?>
			</a>
			<div class="dropdown-menu" id="version" aria-labelledby="dropdown-version">
				<?php
				$i = 0;
				$versionsByLang = getVersions();
				foreach($versionsByLang as $lang => $versions) {
					echo '<h6 class="d-flex align-items-center justify-content-start p-1 bg-light mb-0"><img src="img/'.strtolower($lang).'.png" class="mr-2"> '.$lang.' </h6>';
					foreach($versions as $v) {
						$version = getVersionText($v); ?>
						<a version="<?= $v['file'] ?>" lang="<?= $lang ?>" class="dropdown-item <?php if(isset($_COOKIE['version']) && $_COOKIE['version'] == $v['file']) {?>active<?php } ?>" href="#">
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
    		<button id="new-quest" class="d-none btn btn-light" type="submit">
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