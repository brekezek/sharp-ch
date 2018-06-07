<?php
require_once("../../required/common.php");
include_once getAbsolutePath().'required/db_connect.php';
include_once getAbsolutePath().'required/securite.fct.php';

sec_session_start();
$logged = login_check($mysqli);

if($logged) {

    $firstname = "";
    $lastname = "";
    $region = "";
    $village = "";
    $cluster = "";
    $atelier = "";
    $email = "";
    $ktidb = "";
    $ofs = "";
    
    if(isset($_POST['action'])) {
        if($_POST['action'] == "save") {
            if(isset($_POST['values'])) {
                $values = explode("#", $_POST['values']);
                $hashSet = array();
                foreach($values as $val) {
                    $set = explode("->", $val);
                    if(empty($set[1])) $set[1] = NULL;
                    $hashSet[$set[0]] = $set[1];
                }
                
                $bindParamType = "sssiisss";
                if(isset($_POST['pid'])) {
                    $pid = intval($_POST['pid']);
                    $query = "UPDATE participants SET firstname=?, lastname=?, commune=?, cluster=?, rid=?, email=?, ktidb=?, ofs=? WHERE pid=?";
                    $bindParamType .= "i";
                } else {
                    $query = "INSERT INTO participants (firstname, lastname, commune, cluster, rid, email, ktidb, ofs) VALUES(?,?,?,?,?,?,?,?)";
                }
                
                if($stmt = $mysqli->prepare($query)) {
                    if(isset($_POST['pid'])) {
                        $stmt->bind_param($bindParamType, $hashSet['firstname'], $hashSet['lastname'], $hashSet['commune'], $hashSet['psid'], $hashSet['rid'], $hashSet['email'], $hashSet['ktidb'], $hashSet['ofs'], $pid);
                    } else {
                        $stmt->bind_param($bindParamType, $hashSet['firstname'], $hashSet['lastname'],  $hashSet['commune'], $hashSet['psid'], $hashSet['rid'], $hashSet['email'], $hashSet['ktidb'], $hashSet['ofs']);
                    }
                    $stmt->execute();
                }
  
            }
        } else if($_POST['action'] == "load") {
            if(isset($_POST['pid'])) {
                $pid = intval($_POST['pid']);
                $query = $mysqli->query("SELECT * FROM participants WHERE pid=".$pid);
                $row = $query->fetch_assoc();
                
                $firstname = $row['firstname'];
                $lastname = $row['lastname'];
                $region = $row['rid'];
                $village = $row['commune'];
                $cluster = $row['cluster'];
                $email = $row['email'];
                $ktidb = $row['ktidb'];
                $ofs = $row['ofs'];
            }
            ?>
            <form method="post" action="#">
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                       		<label for="recipient-name" class="col-form-label"><?= $t['firstname']?></label>
                        	<input type="text" class="form-control text-capitalize" name="firstname" pattern="[a-zA-Z]+" maxlength="50" required value="<?= $firstname ?>">
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                        	<label for="recipient-name" class="col-form-label"><?= $t['lastname']?></label>
                        	<input type="text" class="form-control text-capitalize" name="lastname" pattern="[a-zA-Z]+" maxlength="50" required value="<?= $lastname ?>">
                        </div>
                    </div>
                </div>
              
            	<div class="row">
            		<div class="col">
                        <div class="form-group">
                    	    <label for="commune" class="col-form-label"><?= $t['village']?></label>
                        	<input type="text" class="form-control text-capitalize" name="commune" maxlength="100" value="<?= $village ?>">
                        </div>
                    </div>
                    
                    <div class="col">
                        <div class="form-group">
                       	 	<label for="email" class="col-form-label"><?= $t['email']?></label>
                        	<input type="email" class="form-control" name="email" value="<?= $email ?>" maxlength="60">
                        </div>
                   </div>
            	</div>
            	
            	<div class="row">
            		<div class="col">
                        <div class="form-group">
                    	    <label for="ktidb" class="col-form-label">ktidb</label>
                        	<input type="text" class="form-control text-capitalize" pattern="([A-Z]){2}([0-9]){8}" maxlength="10" name="ktidb" value="<?= $ktidb ?>">
                        </div>
                    </div>
                    
                    <div class="col">
                        <div class="form-group">
                       	 	<label for="ofs" class="col-form-label">ofs</label>
                        	<input type="number" class="form-control" name="ofs" maxlength="4" value="<?= $ofs ?>">
                        </div>
                   </div>
            	</div>
            	
            	<div class="row">
                    <div class="col">
                        <div class="form-group">
                       	 	<label for="recipient-name" class="col-form-label"><?= $t['cluster']?></label>
                           	<div class="d-flex align-items-center">
                           	 	<select class="form-control" name="psid">
                           	 		<option value="">-</option>
                           	 		<?php foreach($mysqli->query("SELECT psid, pslabel_".getLang()." FROM prod_systems ORDER BY psid") as $row) { ?>
                           	 		<option value="<?= $row['psid']?>" <?php if($cluster == $row['psid']) { echo 'selected'; } ?>><?= $row['pslabel_'.getLang()]." (".$row['psid'].")"?></option>
                           	 		<?php } ?>
                           	 	</select>
                           	 	<a href="<?= getBase() ?>admin/dashboard/translate/prod_systems" class="btn btn-secondary text-white btn-sm ml-1"><span class="oi oi-plus small"></span></a>
                       	 	</div>
                        </div>
                   </div>
                   
                   <div class="col">
                        <div class="form-group ">
                        	<label for="recipient-name" class="col-form-label"><?= $t['atelier']?></label>
                        	<div class="d-flex align-items-center">
                            	<select class="form-control" name="rid">
                            		<option value="">-</option>
                           	 		<?php foreach($mysqli->query("SELECT rid, rlabel_".getLang()." FROM regions ORDER BY rid") as $row) { ?>
                           	 		<option value="<?= $row['rid']?>" <?php if($region == $row['rid']) { echo 'selected'; } ?>><?= $row['rlabel_'.getLang()]." (".$row['rid'].")"?></option>
                           	 		<?php } ?>
                           	 	</select>
                           	 	<a href="<?= getBase() ?>admin/dashboard/translate/regions" class="btn btn-secondary text-white btn-sm ml-1"><span class="oi oi-plus small"></span></a>
                       	 	</div>
                        </div>
              		</div>
            	</div>
            	
            	

            	<input type="submit" value="" style="opacity:0; width:0px; height:0px; position:absolute">
            </form>
         <?php
        }
    }
} ?>