<?php 
require_once '../../required/common.php';
require_once '../../required/db_connect.php';
require_once '../../required/securite.fct.php';

sec_session_start();

$filters = null;
foreach($mysqli->query("SELECT params FROM users WHERE uid=".$_SESSION['user_id']) as $row) {
    $filters = $row['params'];
}
if($filters !== null) { 
    $filters = unserialize($filters);
}
?>
<form style="width:450px" id="filters-form" method="post">

	
    <div class="form-group mb-1">
    	<div class="card">
            <div class="card-header" data-toggle="collapse" data-target="#collapse-version">
              <h5 class="mb-0"><span class="oi oi-tag mr-2"></span> <?= $t['version']?></h5>
            </div>
    		
        	<div class="collapse card-body <?php if($filters !== null && isset($filters['version'])) { echo 'show'; }?>" id="collapse-version">
                <select name="filter[version]" class="form-control" id="filterversion">
                <option value="all" <?php if($filters === null || !isset($filters['version']) || (isset($filters['version']) && $filters['version'] == "all")) {?>selected<?php } ?>><?= $t['all']?></option>
                <?php foreach(getVersionsFolders() as $v) {?>
                	<option value="<?= $v ?>" <?php if(isset($filters['version']) && $filters['version'] == $v) {?>selected<?php } ?>><?= $v ?></option>
                <?php } ?>
                </select>
            </div>
        </div>
    </div>

    
    <div class="form-group  mb-1">
    	
    	<div class="card">
            <div class="card-header" data-toggle="collapse" data-target="#collapse-collectedby">
              <h5 class="mb-0"><span class="oi oi-person mr-2"></span> <?= $t['collected_by']?></h5>
            </div>
			
        	<div class="collapse card-body <?php if($filters !== null && isset($filters['collected_by'])) { echo 'show'; }?>" id="collapse-collectedby">
                <label for="filtercollected_by"style="max-width: 400px; width:400px"><?= $t['filters_collected_by']?>:</label>
            	<?php 
            	$persons = array();
            	foreach($mysqli->query("SELECT collecte_par FROM questionnaires WHERE deleted IS NULL OR deleted=0 GROUP BY collecte_par") as $row) {
            	    $name = mb_strtolower(preg_replace("/[^a-zA-Z]+/", "", $row['collecte_par']));
            	    $shortName = substr($name,0,3);
            	    if(!isset($persons[$shortName]) || strlen($persons[$shortName]) > $name) {
            	       $persons[$shortName] = $name;
            	    }
            	}
            	?>
                <select name="filter[collected_by][]" class="form-control" id="filtercollected_by" multiple>
                	<?php
                	foreach($mysqli->query("SELECT SUBSTR(collecte_par,1,3) as person, COUNT(collecte_par) as num FROM questionnaires WHERE deleted IS NULL OR deleted=0 GROUP BY person ORDER BY person ASC") as $row) {
                	    $personName = $row['person'];
                	    $shortName = $row['person'];
                	    if(trim($personName) != "") {
                    	    if(isset($persons[mb_strtolower($row['person'])])) {
                    	        $personName = $persons[mb_strtolower($row['person'])];
                    	    }
                    	    ?>
                        	<option value="<?= $shortName ?>" <?php if(isset($filters['collected_by']) && in_array($shortName, $filters['collected_by'])) {?>selected<?php } ?>><?= $personName." (".$row['num'].")" ?></option>
                    	<?php }
                	    } ?>
                </select>
                <small class="form-text text-muted text-center pc_only">
    					<?= $t['help_multiple_multiple'] ?>
    			</small>
			</div>
		</div>
    </div>
 
    
    <div class="form-group mb-1">
    	
    	<div class="card">
            <div class="card-header" data-toggle="collapse" data-target="#collapse-date">
              <h5 class="mb-0"><span class="oi oi-calendar mr-2"></span> <?= $t['date']?></h5>
            </div>
			
        	<div class="collapse card-body <?php if($filters !== null && isset($filters['date']['start']) || isset($filters['date']['end'])) { echo 'show'; }?>" id="collapse-date">
                <label for="filterdatestart"><?= $t['filter_label_date']?></label>
                <div class="row align-items-center" style="justify-content:space-around">
                	<div>
                    	<input type="date" min="2017-11-18" max="<?= date('Y-m-d')?>" name="filter[date][start]" class="form-control" id="filterdatestart" value="<?php if(isset($filters['date']['start'])) { echo $filters['date']['start']; }?>">
                	</div>
                	<div><span class="oi oi-arrow-right"></span></div>
                	<div>
                		<input type="date" min="2017-11-18" max="<?= date('Y-m-d')?>" name="filter[date][end]" class="form-control" id="filterdateend" value="<?php if(isset($filters['date']['end'])) { echo $filters['date']['end']; }?>">
            		</div>
            	</div>
        	</div>
    	</div>
    	
    </div>
    
    
     <div class="form-group mb-1">
    	
    	<div class="card">
            <div class="card-header" data-toggle="collapse" data-target="#collapse-origin">
              <h5 class="mb-0"><span class="oi oi-tablet mr-2"></span> <?= $t['filter_label_title']?></h5>
            </div>
			
        	<div class="collapse card-body <?php if($filters !== null && isset($filters['origin']) && $filters['origin'] != "all") { echo 'show'; }?>" id="collapse-origin">
                <select name="filter[origin]" class="form-control" id="filterorigin">
                	<?php
                	foreach(array($t['all'] => "all", $t['collected-online'] => "online", $t['collected-on-tablet'] => "tablet") as $txt => $origin) {?>
                    	<option value="<?= $origin ?>" <?php if($filters !== null && isset($filters['origin']) && $filters['origin'] == $origin) {?>selected<?php } ?>><?= $txt ?></option>
                	<?php 
            	    } ?>
            	</select>
        	</div>
    	</div>
    	
    </div>
    
    <hr class="display-4">
    
    <div class="text-right">
        <button type="submit" name="reset" class="btn btn-secondary"><span class="oi oi-circle-x mr-2"></span> <?= $t['reset']?></button>
    	<button type="submit" class="btn btn-primary"><span class="oi oi-circle-check mr-2"></span> <?= $t['save']?></button>
	</div>
</form>

<style>
#filters-form .card .card-header:hover {
    background: rgba(0,0,0,0.1);
    cursor: pointer;
}
</style>