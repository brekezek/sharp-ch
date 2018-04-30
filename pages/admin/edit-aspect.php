<?php
$colorPanel = "secondary";

if(!isset($_POST['version'])) {
?>

<div class="alert alert-info d-none" id="message">
    <?= $t['message-edit-aspect-part-1']?>
    <br>
    <?= $t['message-edit-aspect-part-2']?>
    <div class="text-center mt-2 float-right">
    	<button class="btn btn-primary btn-sm d-block" id="understood"><?= $t['understood']?></button>
    </div>
    <div class="clearfix"></div>
</div>

<div class="d-flex justify-content-evenly align-items-start mb-2 rounded">

	<div class="d-flex bg-<?= $colorPanel ?> rounded" id="panel">
        <div class="group-vertical" role="group" aria-label="versions" id="versions">
            <div class="text-white text-center py-1 border-bottom"><?= $t['version']?></div>
            <div class="container p-1">
            <?php 
            foreach(getVersions() as $version) {
                echo '<button data-version="'.$version[0]['file'].'" class="btn btn-'.$colorPanel.' btn-ls d-block mt-1"><small>'.$version[0]['file'].'</small></button>';
            }
            ?>
            </div>
        </div>
        
        <div class="group-vertical" role="group" aria-label="sections" id="sections" style="display:none">
        	<div class="text-white text-center py-1 border-bottom"><?= $t['section']?></div>
			<div class="container p-1"></div>
        </div>
        
        <div class="group-vertical" role="group" aria-label="aspects" id="aspect" style="display:none">
        	<div class="text-white text-center py-1 border-bottom"><?= $t['aspect']?></div>
			<div class="container p-1"></div>
        </div>
	</div>
	
	<div class="ml-3 w-100" id="main">
	
		<div class="btn-group bg-<?= $colorPanel ?> rounded w-100 mb-2 ml-0" id="numbers" aria-label="numbers" role="group" style="display:none">
			<div class="text-white text-center px-3 border-right" style="border-radius: 5px 0 0px 5px; padding-top: 6px"><?= $t['question']?></div>
			<div class="container ml-0 pl-0"></div>
		</div>
		
		<div class="rounded" id="content"></div>
	</div>
	
</div>

<script>
$(function(){
	var json;
	var version;
	var section;
	var aspect;
	var question;
	var tmpDataset;
	
	$('body').tooltip({
	    selector: '[data-toggle=tooltip]',
	    trigger: 'hover'
	});

	// Affichage / Masquage du message d'aide la premiere fois ------
	if(getCookie('disableEditAspectMessage') == "") {
		$('#message').removeClass("d-none");
	} else {
		$('#message').remove();
	}
	$('#understood').click(function(){
		setCookie('disableEditAspectMessage', 'true', 30*6);
		$('#message').remove();
	});
	// --------------------------------------------------------------
	

	$('#panel .btn').click(function(){
		$('.tooltip').remove();
	});
	
	$('#versions .btn').click(function(){
		version = $(this).data("version");
		section = aspect = question = "";
		clickedBtn = $(this);
		
		$('#versions .btn.active').removeClass("active");
		clickedBtn.addClass("active").attr("disabled","disabled").find('small').html('<?= $t['loading']?> <img src="img/loader-score.svg">');

		$('#aspect, #numbers, #sections').hide();
		$.post('pages/admin/edit-aspect.php', { version: version}, function(response){
			console.log(response);
			
			clickedBtn.removeAttr("disabled").find('small').html(version);
			try {
    			json = JSON.parse(response);
    			console.log(json);
    			
    			$('#sections .container, #content').html("");
    			for (const sectionLabel in json.sections) {
    				var title = json.sections[sectionLabel].title;
    				$('#sections .container').append('<button data-toggle="tooltip" data-placement="right" title="'+title+'" data-section="'+sectionLabel+'" class="btn btn-<?= $colorPanel ?> btn-lsm d-block mt-1 py-1"><small>'+sectionLabel+'</small></button>');
    			}
    			$('#sections').show();
			} catch(e) {
				alert(e);
			}
		});
	});

	$('#panel').on('click', '#sections .btn', function(){
		section = $(this).data("section");
		aspect = question = "";
		$('#sections .btn.active').removeClass("active");
		$(this).addClass("active");

		$('#numbers').hide();
		$('#aspect .container, #content').html("");
		for(const aspectLabel of json.sections[section].meta.order ) {
			var title = json.aspects[aspectLabel].title;
			$('#aspect .container').append('<button data-toggle="tooltip" data-placement="right" title="'+title+'" data-aspect="'+aspectLabel+'" class="btn btn-<?= $colorPanel ?> btn-lsm d-block mt-1 py-1"><small>'+aspectLabel+'</small></button>');
		}

		startContent();
		appendContent(json.sections[section].meta);
		endContent();
		
		$('#aspect').show();
	});

	$('#panel').on('click', '#aspect .btn', function(){
		aspect = $(this).data("aspect");
		question = "";
		$('#aspect .btn.active').removeClass("active");
		$(this).addClass("active");

		$('#numbers .container, #content').html("");
		for(const num in json.aspects[aspect].questions) {
			var title = json.aspects[aspect].questions[num].title;
			$('#numbers .container').append('<button data-toggle="tooltip" data-placement="bottom" title="'+title+'" data-question="'+num+'" class="btn btn-<?= $colorPanel ?> btn-ls py-1 rounded-0"><small>'+num+'</small></button>');
		}

		startContent();
		appendContent(json.aspects[aspect]);
		endContent();

		$('#numbers').show();
	});

	$('#main').on('click', '#numbers .btn', function(){
		question = $(this).data("question");
		$('#numbers .btn.active').removeClass("active");
		$(this).addClass("active");

		startContent();
		if(typeof json.aspects[aspect].questions[question].mandatory == "undefined") {
			json.aspects[aspect].questions[question].mandatory = "false";
		}
		appendContent(json.aspects[aspect].questions[question]);
		endContent();

	});

	$('#content').on('submit', 'form', function(e){
		var form = $(this);
		
		$('#panel .btn, #main .btn').attr("disabled","disabled");
		var jsonText = $('#editor[contenteditable]').text();
		try {
			JSON.parse(jsonText);
			
    		form.find('button[type="submit"]').prop('disabled', 'disabled').html('<?= $t['loading']?> <img src="img/loader-score.svg">');
    		var props = { version: version, section:section, aspect:aspect, question:question, jsonText:jsonText };
    		$.post('pages/admin/edit-aspect.php', props, function(response){
    			
    			$.post('pages/admin/edit-aspect.php', { version: version}, function(response){
    				try {
    					json = JSON.parse(response);
    					$('.alert').hide();
    				} catch(e) {
    					$('#content').html("");
    					startContent();
    					$('#content').append('<div class="alert alert-info"><?= addslashes($t['alert-edit-aspect-1']) ?></div>');
    					appendContent(tmpDataset);
    					endContent();
    					$('#content form').trigger("submit");
    				} finally {
    					$('#panel .btn, #main .btn').removeAttr("disabled");
    					form.find('button[type="submit"]').removeAttr('disabled').html('<?= $t['save']?>');
    				}
    				
    			});
    		});
		} catch(e) {
			$('#error').html("<?= $t['alert-edit-aspect-2']?><br><?= $t['error']?>: <i>"+e+"</i>").show();
			form.find('button[type="submit"]').removeAttr('disabled').html('<?= $t['save']?>');
			$('#panel .btn, #main .btn').removeAttr("disabled");
			
		} finally {
			e.preventDefault();
		}
	});

	function startContent() {
		$('#content').html('<div class="alert alert-warning" style="display:none" id="error"></div><form>');
	}
	function endContent() {
		$('#content').html($('#content').html()+'</form>');
	}
	function appendContent(dataset) {
		var jsonClone = jQuery.extend({}, dataset);
		delete jsonClone.questions;

		var jsonString = JSON.stringify(jsonClone, null, 2);
		var formattedJSON = syntaxHighlight(jsonString);
		tmpDataset = dataset;
		
		$('#content').append(
		'<form method="post" action="#">'+
    		'<div class="form-group mb-1">'+
    			'<div contenteditable style="min-height:300px; max-width:100%" id="editor" class="form-control" name=""><pre>'+formattedJSON+'</pre></div>'+
    		'</div>'+
    		'<div class="form-group text-right mb-0">'+
    			'<button type="submit" class="btn btn-primary"><?= $t['save']?></button>'+
			'</div>'+
		'</form>');	
	}

	function syntaxHighlight(json) {
	    json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
	    return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
	        var cls = 'number';
	        if (/^"/.test(match)) {
	            if (/:$/.test(match)) {
	                cls = 'key';
	            } else {
	                cls = 'string';
	            }
	        } else if (/true|false/.test(match)) {
	            cls = 'boolean';
	        } else if (/null/.test(match)) {
	            cls = 'null';
	        }
	        var html = '';
	        html += '<span class="' + cls + '">' + match + '</span>';
	        return html;
	    });
	}

});
</script>
<style>
#editor { background: #333; }
pre { padding: 5px; margin: 5px; color: white; }
.string { color: rgb(255, 198, 0); font-weight:bold; }
.number { color: rgb(210, 82, 66); }
.boolean { color: rgb(3, 168, 216); }
.null { color: magenta; }
.key { color: rgb(190, 214, 255); }
</style>
<?php } else {
    require_once("../../required/common.php");
    include_once getAbsolutePath().'required/db_connect.php';
    include_once getAbsolutePath().'required/securite.fct.php';
    
    if(!isset($_POST['jsonText'])) {
        $version = $_POST['version'];
        $sections = getQuestionnaireSections($version);
        $sectionsForJson = array();
        foreach($sections as $label => $array) { 
            $metaSection = getJSONFromFile(getAbsolutePath().DIR_VERSIONS."/".$version."/".$label."/_meta_category.json");
            $sectionsForJson[$label] = array_merge(array("meta" => $metaSection), $array);   
        }
        
        $aspectsForJson = array();
        foreach($sections as $label => $array) {
            foreach($sectionsForJson[$label]['meta']['order'] as $aspectLabel) {
                $aspectsForJson[$aspectLabel] = getJSONFromFile(getAbsolutePath().DIR_VERSIONS."/".$version."/".$label."/".$aspectLabel."/_meta_aspect.json");
                
                $listQuestions = array();
                foreach(scandir(getAbsolutePath().DIR_VERSIONS."/".$version."/".$label."/".$aspectLabel) as $questionFile) {
                    $nb = str_replace('.json', '', $questionFile);
                    if($questionFile != '.' && $questionFile != '..' && is_numeric($nb))
                        $listQuestions[] = $nb;
                }
                asort($listQuestions);
                
                $questJson = array();
                foreach($listQuestions as $nb) {
                    $questJson[$nb] = getJSONFromFile(getAbsolutePath().DIR_VERSIONS."/".$version."/".$label."/".$aspectLabel."/".$nb.".json");
                }
                $aspectsForJson[$aspectLabel]['questions'] = $questJson;
                
            }
        }
        
        $jsonFinal = array("sections" => $sectionsForJson, "aspects" => $aspectsForJson);
        echo json_encode($jsonFinal);
    } else {
        $jsonText =  isset($_POST['jsonText']) ? $_POST['jsonText'] : null;
        $version =  isset($_POST['version']) ? $_POST['version'] : null;
        $section =  isset($_POST['section']) ? $_POST['section'] : null;
        $aspect = isset($_POST['aspect']) ? $_POST['aspect'] : null;
        $question =  isset($_POST['question']) ? $_POST['question'] : null;
        
        $toEncode = json_decode($jsonText, true);
        
        $filepathBuilt = false;
        $filepath = getAbsolutePath().DIR_VERSIONS."/".$version."/";
        if($section != null) $filepath .= $section."/";
        if($aspect != null && !$filepathBuilt) $filepath .= $aspect."/"; else { $filepath .= "_meta_category.json"; $filepathBuilt = true; }
        if($question != null && !$filepathBuilt) $filepath .= $question.".json"; else { if(!$filepathBuilt) $filepath .= "_meta_aspect.json"; }

        
        if(file_exists($filepath)) {
            if(is_array($toEncode)) {
                $json = json_encode($toEncode, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
                if(strlen($json) > 15) {
                    $handle = fopen($filepath, "w+");
                    fwrite($handle, $json);
                    fclose($handle);
                }
            }
        }
    }
} ?>