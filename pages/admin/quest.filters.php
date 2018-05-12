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
    <div class="form-group row">
        <label for="filterversion" class="col-sm-2 col-form-label"><?= $t['version']?></label>
        <div class="col-sm-10">
            <select name="filter[version]" class="form-control" id="filterversion">
            <option value="all" <?php if($filters === null || !isset($filters['version']) || (isset($filters['version']) && $filters['version'] == "all")) {?>selected<?php } ?>><?= $t['all']?></option>
            <?php foreach(getVersionsFolders() as $v) {?>
            	<option value="<?= $v ?>" <?php if(isset($filters['version']) && $filters['version'] == $v) {?>selected<?php } ?>><?= $v ?></option>
            <?php } ?>
            </select>
        </div>
    </div>
    
    <div class="form-group">
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
    
	<button type="submit" class="btn btn-primary"><?= $t['save']?></button>
	<button type="submit" name="reset" class="btn btn-secondary"><?= $t['reset']?></button>
</form>