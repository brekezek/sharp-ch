<?php
if(isset($_POST['langue'], $_POST['key'], $_POST['value'])) {
    require_once("../../required/common.php");
    include_once getAbsolutePath().'required/db_connect.php';
}

$langs = getLanguageList();
$langFile = array();
foreach($langs as $lang) {
    $filepath = getAbsolutePath()."str/lang.".$lang.".json";
    if(file_exists($filepath)) {
        $langFile[$lang] = getJSONFromFile($filepath);
    }
}
asort($langFile['fr']);

if(isset($_POST['langue'], $_POST['key'], $_POST['value'], $_POST['type'])) {
    $lang = $_POST['langue'];
    $key = $_POST['key'];
    $value = $_POST['value'];
    $type = $_POST['type'];
    
    if($type == "text") {
        $toEncode = $langFile[$lang];
        $toEncode[$key] = $value;
        
        $json = json_encode($toEncode, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
        
        $handle = fopen(getAbsolutePath()."str/lang.".$lang.".json", "w+");
        if(is_array($toEncode)) {
            fwrite($handle, $json);
        }
        fclose($handle);
    } else if($type == "aspect_title") {
        if($key == "new") {
            $sql = "INSERT INTO label_aspects (aspectId) VALUES (?)";
        } else {
            $sql = "UPDATE label_aspects SET label_".$lang."=? WHERE aspectId=?";
        }
        if($stmt = $mysqli->prepare($sql)) {
            if($key != "new") $stmt->bind_param("ss", $value, $key);
            else $stmt->bind_param("s", $value);
            
            $stmt->execute();
            
            if($key == "new") {
                echo $value;
            }
        }
    } else if($type == "prod_sys") {
        if($key == "new") {
            $sql = "INSERT INTO prod_systems (psid) VALUES (?)";
        } else {
            $sql = "UPDATE prod_systems SET pslabel_".$lang."=? WHERE psid=?";
        }
        if($stmt = $mysqli->prepare($sql)) {
            if($key != "new") $stmt->bind_param("ss", $value, $key);
            else $stmt->bind_param("i", intval($value));
            $stmt->execute();
            if($key == "new") {
                echo intval($value);
            }
        }
    }  else if($type == "regions") {
        if($key == "new") {
            $sql = "INSERT INTO regions (rid) VALUES (?)";
        } else {
            $sql = "UPDATE regions SET rlabel_".$lang."=? WHERE rid=?";
        }
        if($stmt = $mysqli->prepare($sql)) {
            if($key != "new") $stmt->bind_param("ss", $value, $key);
            else $stmt->bind_param("i", intval($value));
            $stmt->execute();
            if($key == "new") {
                echo intval($value);
            }
        }
    } else if($type == "indicators") {
        if($key == "new") {
            $sql = "INSERT INTO indicators (iid) VALUES (?)";
        } else {
            $sql = "UPDATE indicators SET ilabel_".$lang."=? WHERE iid=?";
        }
        if($stmt = $mysqli->prepare($sql)) {
            if($key != "new") $stmt->bind_param("ss", $value, $key);
            else $stmt->bind_param("i", intval($value));
            $stmt->execute();
            if($key == "new") {
                echo intval($value);
            }
        }
    }
    
} else {
?>
<ul class="nav nav-tabs mt-1 mx-1">
  <li class="nav-item">
    <a class="nav-link <?php if(!isset($_GET['tab'])) {?>active<?php }?>" href="?page=<?= $_GET['page']?>">
    	<span class="oi oi-text mr-1"></span> 
    	<?= $t['text-app']?>
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php if(isset($_GET['tab']) && $_GET['tab'] == "aspects") {?>active<?php }?>" href="?page=<?= $_GET['page']?>&tab=aspects">
    	<span class="oi oi-pie-chart mr-1"></span>
    	<?= $t['text-charts']?>
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php if(isset($_GET['tab']) && $_GET['tab'] == "prod_systems") {?>active<?php }?>" href="?page=<?= $_GET['page']?>&tab=prod_systems">
    	<span class="oi oi-list mr-1"></span>
    	<?= $t['cluster']?>
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php if(isset($_GET['tab']) && $_GET['tab'] == "regions") {?>active<?php }?>" href="?page=<?= $_GET['page']?>&tab=regions">
    	<span class="oi oi-map-marker mr-1"></span>
    	<?= $t['atelier']?>
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php if(isset($_GET['tab']) && $_GET['tab'] == "indicators") {?>active<?php }?>" href="?page=<?= $_GET['page']?>&tab=indicators">
    	<span class="oi oi-tags mr-1"></span>
    	<?= $t['indicators']?>
    </a>
  </li>
</ul>

<?php if(!isset($_GET['tab'])) {?>
<table class="table table-sm table-striped">
	<thead class="thead-dark">
		<tr>
			<?php foreach($langs as $lang) {?>
			<th class="align-middle text-center"><img src="img/<?= $lang ?>.png"> <span class="badge badge-light text-uppercase"><?= $lang ?></span></th>
			<?php }?>
		</tr>
	</thead>
	<tbody>
		<?php foreach($langFile['fr'] as $key => $value) {?>
		<tr>
			<?php foreach($langs as $lang) {?>
				<td class="border-right">
    				<textarea
    					data-lang="<?= $lang ?>" data-changed="false" data-key="<?= $key ?>" data-type="text"
        				class="form-control <?php if(!isset($langFile[$lang][$key])) echo 'bg-danger text-white'; ?>"
        				style="font-size:10pt"
        				<?php if(!isset($langFile[$lang][$key])) echo 'placeholder="'.$t['empty'].'"'; ?>><?= isset($langFile[$lang][$key]) ? $langFile[$lang][$key] : "" ?></textarea>
				</td>
			<?php }?>
		</tr>
		<?php } ?>
	</tbody>
</table>
<?php } else {
    
    $tab = $_GET['tab'];
    
    if($tab == "aspects") {
        $query = "SELECT * FROM label_aspects ORDER BY aspectId ASC";   
        $firstColHeader = $t['aspect'];
        $firstColFieldName = "aspectId";
        $subseqColFieldName = "label_%lang";
        $dataType = "aspect_title";
        
    } else if($tab == "prod_systems") {
        $query = "SELECT * FROM prod_systems ORDER BY psid ASC";
        $firstColHeader = "id";
        $firstColFieldName = "psid";
        $subseqColFieldName = "pslabel_%lang";
        $dataType = "prod_sys";
    } else if($tab == "regions") {
        $query = "SELECT * FROM regions ORDER BY rid ASC";
        $firstColHeader = "id";
        $firstColFieldName = "rid";
        $subseqColFieldName = "rlabel_%lang";
        $dataType = "regions";
    } else if($tab == "indicators") {
        $query = "SELECT * FROM indicators ORDER BY iid ASC";
        $firstColHeader = "id";
        $firstColFieldName = "iid";
        $subseqColFieldName = "ilabel_%lang";
        $dataType = "indicators";
    } 
    $results = $mysqli->query($query); ?>
    
    <table class="table table-sm table-striped">
    	<thead class="thead-dark">
    		<tr>
    			<th class="text-center" width="100px"><?= $firstColHeader ?></th>
    			<?php foreach($langs as $lang) {?>
    			<th class="align-middle text-center"><img src="img/<?= $lang ?>.png"> <span class="badge badge-light text-uppercase"><?= $lang ?></span></th>
    			<?php }?>
    		</tr>
    	</thead>
    	<tbody>
    		<tr class="bg-success">
    			<td class="text-center align-middle"><input data-lang="any" data-key="new" data-id data-type="<?= $dataType ?>" type="text" placeholder="New" class="form-control d-inline" style="font-size:10pt; width: 60px"></td>
    			<?php foreach($langs as $lang) {?>
    			<td class="border-right" class="text-center align-middle"><input data-lang="<?= $lang ?>" data-key="new" data-type="<?= $dataType ?>" type="text" placeholder="New" class="form-control" style="font-size:10pt"></td>
    			<?php } ?>
    		</tr>
    		<?php foreach($results as $row) { ?>
    		<tr>
    			<td class="text-center align-middle"><?= $row[$firstColFieldName] ?></td>
    			<?php foreach($langs as $lang) {
    			    $langFieldName = str_replace("%lang", $lang, $subseqColFieldName); ?>
    				<td class="border-right">
        				<input type="text" maxlength="50"
        					data-lang="<?= $lang ?>" data-changed="false" data-key="<?= $row[$firstColFieldName] ?>"
        					data-type="<?= $dataType ?>"
            				class="form-control"
            				style="font-size:10pt"
    			    <?php if($row[$langFieldName] === null) echo 'placeholder="'.$t['empty'].'"'; ?>
                            value="<?= $row[$langFieldName] !== null ? $row[$langFieldName] : "" ?>">
    				</td>
    			<?php }?>
    		</tr>
    		<?php } ?>
    	</tbody>
    </table>
<?php } ?>

<script>
$(function(){
	$('table').on('change keyup keydown paste cut', 'textarea', function (){
        var diff = parseInt($(this).height(),10) - $(this).outerHeight();
	    $(this).css("overflow-y","hidden").height(0).height(Math.max(22, this.scrollHeight + diff)+"px");
	}).find('textarea').change();

	$('textarea, input[type="text"]').keyup(function(){
		$(this).attr("data-changed","true");
	});

	$('textarea, input[type="text"]').blur(function(){
		var lang = $(this).attr("data-lang");
		var key = $(this).attr("data-key");
		var type = $(this).attr("data-type");
		var value = $(this).val().trim();
		var elm = $(this);
		var isId = elm.is("[data-id]");
		
		if(elm.attr("data-changed") == "true" && value.length > 0) {
    		$.post('pages/admin/translations.php', {
    			langue: lang,
    			key:key,
    			value:value,
    			type:type
    		}, function(html) {
    			if(isId) {
    				$('[data-key="new"]').attr("data-key", html);
    				elm.parents("td").html(value);
    			}
    			elm.addClass("text-success");
    			elm.removeClass("bg-danger text-white");
    			elm.attr("data-changed","false");
    			setTimeout(function(){ elm.removeClass("text-success"); }, 2500);	
    		});
		}
	});
});
</script>
<?php } ?>