<?php $readonly = isset($_COOKIE['indexAspect'], $_COOKIE['readonly']) && $_COOKIE['readonly'] == "true"; ?>

<nav class="navbar navbar-expand-sm navbar-dark fixed-top bg-dark justify-content-between">
	
	<div class="navbar-brand">
		<?php if($readonly || $logged) {?>
		<a href="admin.php?page=1" id="back" class="btn btn-outline-secondary mr-1">
			<span class="oi oi-chevron-left"></span>
		</a>
		<?php } ?>
		
		SHARP <span class="badge badge-danger">CH</span>
	</div>
  	
	<?php if(isset($_COOKIE['indexAspect'])) {?>
	<div class="text-light" >
		<?php if($readonly) {
		    echo '<span class="oi oi-eye mr-1"></span> <span id="name-ro">Read-Only</span>';
		} else {
		    echo $_COOKIE['version'];
		}?>
	</div>
	
	<div>
		<?php if(!$readonly) {?>
		<div class="dropdown d-inline mr-2">
			<button id="others" class="btn btn-secondary dropdown-toggle" id="dropd-settings" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<span class="oi oi-cog"></span>
			</button>
			<div class="dropdown-menu mt-3" aria-labelledby="dropd-settings" style="margin-left:-90px">
				<a href="?quit" id="quit" class="dropdown-item"><?= $t['quit_questionnaire'] ?></a>
			</div>	
		</div>
		
		<?php if(isset($_COOKIE['indexAspect'], $_COOKIE['readonly']) && $_COOKIE['readonly'] == "false") { ?>
		<button id="switch-readonly" class="btn btn-primary" type="button">
			<span class="oi oi-lock-locked mr-1"></span> Read-Only
		</button>
		<?php }?>
		
		<?php } else { ?>
		<button id="edit" class="btn btn-primary" type="button">
			<span class="oi oi-pencil mr-1"></span> Editer
		</button>
		<?php } ?>
		
		<?php if(!isset($_COOKIE['scores-display']) && !isset($_REQUEST['end'])) {?>
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
	
		<ul class="navbar-nav mr-auto">
		  <li class="nav-item active d-none">
			<a class="nav-link" href="#">Home <span class="sr-only">(current)</span></a>
		  </li>
		  
		  <?php if(!isset($_COOKIE['scores-display'])) { ?>
		  <li class="nav-item dropdown">
			<a class="btn btn-primary btn-md dropdown-toggle" href="#" id="dropdown-version" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<?= isset($_COOKIE['version']) ? $_COOKIE['version'] : $t['choose_version'] ?>
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
		  </li>
		  <?php } ?>
		</ul>
	
	   
	   <?php if(!isset($_COOKIE['scores-display'])) { ?>
		<button id="new-quest" class="d-none btn btn-light" type="submit">
			<span class="oi oi-plus pr-1"></span>
			<?= $t['new_questionnaire']?>
		</button>
		<?php } else { ?>
		
		
		<button id="" class="btn btn-light px-3 mr-1" type="submit">
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