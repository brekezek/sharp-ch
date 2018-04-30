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
    
    if(isset($_POST['action'])) {
        if($_POST['action'] == "save") {
            if(isset($_POST['values'])) {
                $values = explode("#", $_POST['values']);
                $hashSet = array();
                foreach($values as $val) {
                    $set = explode("->", $val);
                    $hashSet[$set[0]] = $set[1];
                }
                
                $bindParamType = "ssssiis";
                if(isset($_POST['pid'])) {
                    $pid = intval($_POST['pid']);
                    $query = "UPDATE participants SET firstname=?, lastname=?, region=?, commune=?, cluster=?, rid=?, email=? WHERE pid=?";
                    $bindParamType .= "i";
                } else {
                    $query = "INSERT INTO participants (firstname, lastname, region, commune, cluster, rid, email) VALUES(?,?,?,?,?,?,?)";
                }
                
                if($stmt = $mysqli->prepare($query)) {
                    if(isset($_POST['pid'])) {
                        $stmt->bind_param($bindParamType, $hashSet['firstname'], $hashSet['lastname'], $hashSet['region'], $hashSet['commune'], $hashSet['cluster'], $hashSet['rid'], $hashSet['email'], $pid);
                    } else {
                        $stmt->bind_param($bindParamType, $hashSet['firstname'], $hashSet['lastname'], $hashSet['region'], $hashSet['commune'], $hashSet['cluster'], $hashSet['rid'], $hashSet['email']);
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
                $region = $row['region'];
                $village = $row['commune'];
                $cluster = $row['cluster'];
                $atelier = $row['rid'];
                $email = $row['email'];
            }
            ?>
            <form>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                       		<label for="recipient-name" class="col-form-label"><?= $t['firstname']?></label>
                        	<input type="text" class="form-control text-capitalize" name="firstname" required value="<?= $firstname ?>">
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                        	<label for="recipient-name" class="col-form-label"><?= $t['lastname']?></label>
                        	<input type="text" class="form-control text-capitalize" name="lastname" required value="<?= $lastname ?>">
                        </div>
                    </div>
                </div>
              
            	<div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label"><?= $t['region']?></label>
                	        <input type="text" class="form-control text-capitalize" name="region" value="<?= $region ?>">
                        </div>
                    </div>
                    
                    <div class="col">
                        <div class="form-group">
                    	    <label for="recipient-name" class="col-form-label"><?= $t['village']?></label>
                        	<input type="text" class="form-control text-capitalize" name="commune" value="<?= $village ?>">
                        </div>
                    </div>
            	</div>
            	
            	<div class="row">
                    <div class="col">
                        <div class="form-group">
                       	 	<label for="recipient-name" class="col-form-label"><?= $t['cluster']?></label>
                        	<input type="number" class="form-control" name="cluster" value="<?= $cluster ?>">
                        </div>
                   </div>
                   
                   <div class="col">
                        <div class="form-group">
                        	<label for="recipient-name" class="col-form-label"><?= $t['atelier']?></label>
                        	<input type="number" class="form-control" name="rid" value="<?= $atelier ?>">
                        </div>
              		</div>
            	</div>
            	
            	
            	<div class="row">
                    <div class="col">
                        <div class="form-group">
                       	 	<label for="recipient-name" class="col-form-label"><?= $t['email']?></label>
                        	<input type="text" class="form-control" name="email" value="<?= $email ?>">
                        </div>
                   </div>
                   
                   
            	</div>
            	<input type="submit" value="" style="opacity:0; overflow:hidden; width:0px; height:0px; position:absolute">
            </form>
         <?php
        }
    }
} ?>