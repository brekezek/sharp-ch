<?php 
require_once '../required/common.php';
require_once '../required/db_connect.php';
?>
<select class="dataSource form-control w-100 rounded mb-1">
   
    	<option style="color:#ddd"><?= $t['choose...']." <b>".$t['atelier']."</b>" ?></option>


        <option class=" bg-success text-white" value="OTHER"><?= $t['other'] ?></option>
        <?php 
        foreach($mysqli->query("SELECT rlabel_".getLang().", rid FROM regions ORDER BY rlabel_".getLang()) as $item) {
            echo '<option value="'.$item["rid"].'">'.$item["rlabel_".getLang()]."</option>";
        } 
        ?>

</select>
