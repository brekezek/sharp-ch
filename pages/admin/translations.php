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
    }
    
    if($type == "aspect_title") {
        if($stmt = $mysqli->prepare("UPDATE label_aspects SET label_".$lang."=? WHERE aspectId=?")) {
            $stmt->bind_param("ss", $value, $key);
            $stmt->execute();
        }
    }
} else {
?>
<ul class="nav nav-tabs mt-1 mx-1">
  <li class="nav-item">
    <a class="nav-link <?php if(!isset($_GET['aspects'])) {?>active<?php }?>" href="?page=<?= $_GET['page']?>&text">
    	<span class="oi oi-text mr-1"></span> 
    	<?= $t['text-app']?>
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php if(isset($_GET['aspects'])) {?>active<?php }?>" href="?page=<?= $_GET['page']?>&aspects">
    	<span class="oi oi-pie-chart mr-1"></span>
    	<?= $t['text-charts']?>
    </a>
  </li>
</ul>

<?php if(!isset($_GET['aspects'])) {?>
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
    
$results = $mysqli->query("SELECT * FROM label_aspects ORDER BY aspectId ASC"); ?>

<table class="table table-sm table-striped">
	<thead class="thead-dark">
		<tr>
			<th class="text-center" width="100px">Aspect</th>
			<?php foreach($langs as $lang) {?>
			<th class="align-middle text-center"><img src="img/<?= $lang ?>.png"> <span class="badge badge-light text-uppercase"><?= $lang ?></span></th>
			<?php }?>
		</tr>
	</thead>
	<tbody>
		<?php foreach($results as $row) { ?>
		<tr>
			<td class="text-center align-middle"><?= $row['aspectId'] ?></td>
			<?php foreach($langs as $lang) {?>
				<td class="border-right">
    				<input type="text" maxlength="50"
    					data-lang="<?= $lang ?>" data-changed="false" data-key="<?= $row['aspectId'] ?>"
    					data-type="aspect_title"
        				class="form-control"
        				style="font-size:10pt"
                        <?php if($row["label_".$lang] === null) echo 'placeholder="'.$t['empty'].'"'; ?>
                        value="<?= $row["label_".$lang] !== null ? $row["label_".$lang] : "" ?>">
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
		var value = $(this).val();
		var elm = $(this);

		if(elm.attr("data-changed") == "true") {
    		$.post('pages/admin/translations.php', {
    			langue: lang,
    			key:key,
    			value:value,
    			type:type
    		}, function(html) {
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