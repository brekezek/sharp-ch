<?php
include_once 'required/securite.fct.php';
sec_session_start();

$reset = false;
$debug = false;
require_once('required/common.php');
include_once 'required/db_connect.php';

includeDependencies();

$logged = login_check($mysqli);

if(isset($_GET['admin'])) {
    header('Location: admin.php');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="author" content="Dominique Roduit">
	<meta name="description" content="">

	<base href="<?= ($_SERVER['SERVER_NAME'] == "localhost") ? "/sharp-site/" : getBase() ?>">
	
	<!-- Fonts 
	<link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet"> -->
	
	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link href="open-iconic/font/css/open-iconic-bootstrap.min.css" rel="stylesheet">
	
	<!-- Custom -->
	<link rel="stylesheet" href="css/style.css">
	<script src="js/cookie.js"></script>
	
	<title>SHARP-CH</title>
	<link rel="shortcut icon" href="img/favicon.png">
</head>
<body>
	
	<?php
	$newQuests = array();
	if(!isset($_COOKIE['questsList'])) {
	    foreach(scanAllDir(DIR_ANSWERS) as $file) {
	        $explodedFile = explode("/", $file);
	        if(count($explodedFile) == 1) {
	            $json = getJSONFromFile(DIR_ANSWERS."/".$file);
	            if(isset($json['meta']['client-ip']) && $json['meta']['client-ip'] == getClientIP()) {
	                if(isset($json['ADM_01'])) {
	                    
	                    $filename = $json['meta']['filename'];
	                    $version = $json['meta']['version'];
	                    $firstname = $json['ADM_01'][2]['answer'];
	                    $lastname = $json['ADM_01'][3]['answer'];
	                    
	                    if(!empty(trim($firstname).trim($lastname))) {
	                        $newQuests[] = array(
	                            "filename" => $filename,
	                            "version" => $version
	                        );
	                    }
	                }
	            }
	        }
	    }
	}
	?>

	<?php
	$displayScorePage = isset($_COOKIE['scores-display']) || isset($_GET['score'], $_GET['setData']);
	
	include_once('menu.php');
	?>
	
	<?php if($displayScorePage) include_once('js_dependencies.php'); ?>
	
	<div id="content" class="position-relative" style="margin-top: 56px;">
	<?php
	if(isset($_GET['error']) && $_GET['error'] == "404") {?>
	   <div class="jumbotron rounded-0 bg-warning mb-0 pt-4 pb-3 text-white text-center">
	   		<div class="lead display-4"><?= $t['error-404']?></div>
	   		<div class="lead"><?= $t['text-error-404']?></div>
	   </div>
	<?php 
	}
	
	if($displayScorePage) {
	    include_once('pages/end_quest.php');  
	} else {
    	if(isset($_COOKIE['indexAspect'])) {
    		include_once('pages/questionnaire.php');
    	} else {
    	    if($logged) {
    	        if(isset($_SESSION['resultsDefined'])) {
    	          unset($_SESSION['resultsDefined']);  
    	        }
    	    }
            ?>
            
    		<div class="jumbotron rounded-0 mb-0">
    			<div class="container" id="main">
    				<?php
    				if(isset($_COOKIE['questsList'])) {
    				    $json = json_decode($_COOKIE['questsList'], true);

    				    $rows = array();
    				    $stmt = $mysqli->prepare(
				            "SELECT file, version, creation_date, firstname, lastname, region, commune FROM questionnaires q
                             LEFT JOIN participants p ON p.pid=q.pid
                             WHERE file=? AND version=? LIMIT 1"
				        );
    				    foreach($json['list'] as $item) {
    				        if(isset($item['filename'], $item['version']) && file_exists(DIR_ANSWERS."/".$item['filename'])) {
    				            $stmt->bind_param("ss", $item['filename'], $item['version']);
    				            $stmt->execute();
    				            $result = $stmt->get_result();
    				            while ($row = $result->fetch_assoc()) {
    				                $rows[strtotime($row['creation_date'])] = $row;
    				            }
    				        }
    				    }
    				    $stmt->close();
    				    ksort($rows);
    				    $rows = array_reverse($rows);
    				}
    				
    				if(!isset($_COOKIE['questsList']) || (isset($_COOKIE['questsList']) && count($rows) == 0)) { ?>
                        <div class="text-center">
                          <img id="logo" src="img/logo_round_<?= getLang() ?>.jpg" class="mb-3" width="220" style="min-width: 220px">
                          <h1 class="display-4 d-none"><?= $t['welcome_msg_h1'] ?> <span class="badge badge-light text-uppercase"><?= getLang() ?></span></h1>
                          <p class="lead"><?= $t['sharp_meaning'] ?></p>
                        </div>
                        
                        <hr class="my-4">
                        <p><?= $t['msg1_welcome'] ?></p>
                        <hr class="my-4">
                        
                        <p class="text-center">
                        	<a class="btn btn-primary btn-lg start-new-quest d-none" href="#" role="button">
                        		<?= $t['new_questionnaire']?>
                          		<span class="oi oi-media-play ml-1"></span>
                          	</a>
                    	</p>
                    <?php
                    } else {?>
        			  
    			  		<div class="d-flex align-items-center" style="justify-content:space-evenly">
                          <img id="logo" src="img/logo_round_<?= getLang() ?>.jpg" class="mb-3" width="150px" height="150px" style="min-width:150px; min-height:150px;">
                          <div class="ml-4">
                              <p class="lead font-weight-bold"><?= $t['sharp_meaning'] ?></p>
                              <p class="text-justify"><?= $t['msg1_welcome'] ?></p>
                          </div>
                        </div>
                        
                        <hr class="my-3">
						
						<h5 class="mb-3 text-center"><?= $t['your-questionnaires']?></h5>
						<div id="list-quest">
            			<?php 
            			$langByVersion = array();
            			foreach(getVersions() as $lang => $info) {
            			    $langByVersion[$info[0]['file']] = mb_strtolower($lang);   
            			}
            		    foreach($rows as $row) {
    			            $firstname = empty($row['firstname']) ? "" : ucfirst($row['firstname']);
    			            $lastname = empty($row['lastname']) ? "" : ucfirst($row['lastname']);
    			            $name = $firstname." ".$lastname;
    			            if(empty(trim($name))) $name = '<span class="text-muted">'.$t['noname'].'</span>';
    			            
    			            $setData = base64_encode(urlencode(serialize(array(
    			                "filename" => $row['file'],
    			                "version" => $row['version']
    			            ))));
    			            ?>
    			            
        			     	<div class="card rounded mb-1 quest-item" data-filename="<?= $row['file'] ?>" data-version="<?= $row['version'] ?>" data-setData="<?= $setData ?>">
                              <div class="card-body d-flex align-items-center py-2 pr-2">
                                 <div class="mr-auto" style="cursor:default">
                                 	 <img data-toggle="tooltip" data-placement="top" title="<?= $row['version']?>" src="img/<?= $langByVersion[$row['version']] ?>.png" class="mr-1" style="margin-bottom:3px">
                                     <b class="name" style="cursor:pointer"><?= $name ?></b> · 
                                     <span class="text-muted"><?= date("d.m.Y, H:m", strtotime($row['creation_date'])) ?></span>
                                 </div>
                                 <div>
                                 	<div class="btn btn-success btn-sm" data-action="scores" data-toggle="tooltip" data-placement="top" title="<?= $t['graphiques-scores']?>"><span class="oi oi-pie-chart"></span></div>
                                 	<div class="btn btn-primary btn-sm" data-action="edit" data-toggle="tooltip" data-placement="top" title="<?= $t['display-questionnaire']?>"><span class="oi oi-chevron-right"></span></div>
                                 </div>
                              </div>
                            </div>
                            
    			     	 <?php   
            			 } ?>
            			 </div>
            		<?php 
        			} ?>
        			</div>
        			
        			<div id="pre-quest-instructions" class="container" style="display:none"></div>
    		</div>
		<?php
    	}
	}
	?>
	</div>
	
	
	<!-- Bootstrap & JQuery -->
	<?php if(!$displayScorePage) include_once('js_dependencies.php'); ?>
	
	
	<script>
	$(function(){
		
		<?php if($reset) { ?>
			deleteCookie('device-opt');
			deleteCookie('cookie_avert');
			deleteCookie("indexAspect");
			deleteCookie("version");
			deleteCookie('readonly');
			deleteCookie("lang");
			deleteCookie("score-live");
			deleteCookie("scores-display");
			deleteCookie("filename");
			deleteCookie("expirationQuest");
			deleteCookie('questsList');
		<?php }
		
		if(isset($endQuestionnaire) && $endQuestionnaire === true) {?>
			deleteCookie("indexAspect");
			setCookie("scores-display", "true", 1);
		<?php } ?>


		function loading() {
			bootbox.dialog({
				message: '<div class="text-center"><?= $t['loading']?><br><img src="img/loader-score.svg"></div>',
				size: 'small',
				closeButton:false,
				animate:false
			});
		}

		
		<?php if(isset($_COOKIE['indexAspect'])) {?>
		function goToAspect(index) {
			var lifeCookie = getCookie('expirationQuest');
			if(lifeCookie == "") lifeCookie = <?= LIFE_COOKIE_QUEST_PENDING ?>;
			setCookie("indexAspect", index, lifeCookie);
			//alert("index="+index);
			//alert("indexAspect="+getCookie("indexAspect"));
		}

		$('#questionnaire .form-group.filtered').remove();
		
		$('a#quit').click(function(e){
			deleteCookie("indexAspect");
			document.location = '?quit';
			e.preventDefault();
		});
		
		$('#questionnaire').find('#next, #prev').click(function(e){
			var isPrev = $(this).attr("id") == "prev";
			var canUpdateCookie = true;

			loading();
			
			if(!isPrev) {
				$("#questionnaire form").find('[required]').each(function() {
					var elem = $("#questionnaire form [name=\""+$(this).attr("name")+"\"]");
					if(!elem.is('[type="radio"]')) {
						if(elem.is(":hidden")) {
							elem.remove();
						} else {
							if(elem.is(":invalid")) { canUpdateCookie = false;  }
						}
					}
				});
			} else {
				$("#questionnaire form").find('[required]').removeAttr("required");
			}
			
			if(canUpdateCookie)
				goToAspect(parseInt(getCookie("indexAspect")) + (isPrev ? -1 : 1));
		});
	
		function resizeAspectPanel() {
			var screenH = $(window).height() - $('nav').outerHeight(true);
			$('#aspects').css({height: screenH+"px"});
		}
		
		$('#show-aspects').click(function(){
			if($('#aspects').css("display") != "none") {
				$("body").css("overflow","auto");
				$('#aspects').animate({right:"-75%", opacity:0}, 600, function(){ $('#aspects').css("display","none") });
				$('.modal-backdrop').fadeOut(400).unbind("click");
				$(window).unbind("resize");
				$('#aspects .card:not(.cat-active)').unbind('click');
			} else {
				$("body").css("overflow-y","hidden");
				$('#aspects').css({display:"block"}).animate({right:0, opacity:1 }, 500, function(){
					if($('#aspects').scrollTop() == 0) {
						$('#aspects').animate({scrollTop: $("#aspects .card.cat-active").offset().top + 8 - ($("#aspects .card.cat-active").height()/2) }, 600);
					}
				});
				$('.modal-backdrop').fadeIn(0).bind("click", function(){
					$('#show-aspects').trigger("click");
				});
				$(window).bind("resize",resizeAspectPanel);
				$('#aspects .card:not(.cat-active)').bind('click', function(){
					$('#aspects').hide();
					loading();
					goToAspect(parseInt($(this).attr("data-index")));
					$("#questionnaire form").find('[required]').removeAttr("required");
					$('#questionnaire #submitHidden').trigger("click");
				});
				resizeAspectPanel();
			}
			
			$(this).toggleClass("active btn-dark");
		});

		$('#quest-progress .item[data-index]').click(function(){
			loading();
			goToAspect(parseInt($(this).attr("data-index")));
			$("#questionnaire form").find('[required]').removeAttr("required");
			$('#questionnaire #submitHidden').trigger("click");
		});
		
		$('.binary_comment').on("change", function(){
			var comment_input = $(this).parent().parent().find('textarea');
			if($(this).find("input").attr("index") == "0") { // Oui
				comment_input.show();
			} else { 
				comment_input.hide();
			}
		});
		
		$('select[multiple][other-exist]').on('change', function(){
			var otherSelected = false;
			var vals = $(this).val();
			if(Array.isArray(vals) && vals.length > 0) {
				var elem = $(this).find('option[value="'+vals[vals.length-1]+'"]');
				var parentNode = $(this).parent();
				if(elem.attr("isOther") == "true") {
					parentNode.find('small[id^="help_"]').hide();
					parentNode.parent().find('textarea').show();
				} else {
					parentNode.find('small[id^="help_"]').show();
					parentNode.parent().find('textarea').hide();
				}
			}
		});

		// Checkboxes ----------------------------
		$('table:not([data-type="toggle"]) tbody td[data-type="toggle"]').click(function(e){
			if(!$(e.target).is('label')) {
				if($(this).find("span.display-manager").css("display") != "none") {
        			var elem = $(this).find('label[type="checkbox"]');
        			elem.trigger("click");
				}
			}
		});
		$('table[data-type="toggle"] tbody tr').click(function(e){
			if(!$(e.target).is('label')) {
    			var elem = $(this).find('label[type="checkbox"]');
    			elem.trigger("click");
			}
		});
		$('table input[type="checkbox"]').on("change", function(){
			var elem = $(this).parent().find('input[name="'+$(this).attr("trigger")+'"][type="hidden"]');
			if(elem.val() == "0") elem.val("1");
			else elem.val("0");
		});

		// Radio buttons ---------------------------
		$('table input[type="radio"]').on("change", function(){
			$('input[radio-group="'+$(this).attr("name").replace('radio_', '')+'"][type="hidden"]').val("");
			
			var elem = $(this).parent().find('input[name="'+$(this).attr("value")+'"][type="hidden"]');
			if($(this).is(":checked")) elem.val("1");
		});
		$('table:not([data-type="toggle_one"]) tbody td[data-type="toggle_one"]').click(function(e){
			if(!$(e.target).is('label')) {
				if($(this).find("span.display-manager").css("display") != "none") {
        			var elem = $(this).find('label[type="radio"]');
        			elem.trigger("click");
				}
			}
		});

		// Tables "other" fields
		/*
		$('table tbody tr[other] td').on('click', function(){
			var row = $(this).parent().parent().parent("table").find('tr[other-field]');
			row.show();
		});
		*/
		$('table tr td[trigger-display]').find('input:not([type="text"]), select').on('change', function(){
			displayRowCells($(this));
		});
		$('table tr td[trigger-display]').find('textarea, input[type="text"], input[type="number"]').on('keyup', function(){
			displayRowCells($(this));
		});

		$('#edit').click(function(){
			setCookie("readonly", "false", <?= LIFE_COOKIE_QUEST_PENDING ?>);
			document.location = '?edit';
		});
		$('#switch-readonly').click(function(){
			$("#questionnaire form").attr("action","?readonly").find('[required]').removeAttr("required");
			$('#questionnaire #submitHidden').trigger("click");
		});

		$('.time-left').click(function(){
			$.post('pages/getQuestTimeCharts.php', {}, function(html){
    			bootbox.dialog({
    				message: html,
    				backdrop:true,
    				onEscape:true
    			});
			});
		});

		
		
		// Geolocalisation
		// ----------------------------------------------------------------------
		if($('input[data-request-location]').length > 0 && navigator.geolocation) {
			if(getCookie("permission-location") == 'granted') {
				navigator.geolocation.getCurrentPosition(showPosition, showError);
				$('input[data-request-location]').attr("data-location-loaded", "loaded");
			}
			
    		$('input[data-request-location]').focus(function(){
    			var input = $(this);
    			if(navigator.geolocation) {
        			if(["","granted"].includes(getCookie("permission-location"))) {
        				if(input.data("location-loaded") != "loaded" && input.val().length < 3) {
        					if(getCookie("permission-location") == 'granted') {
        						navigator.geolocation.getCurrentPosition(showPosition, showError);
        					} else {
            					var modal = $('#content #modal-quest');
            					modal.modal({backdrop:'static'});
            					modal.find('#submit').bind('click', function(){
            					    navigator.geolocation.getCurrentPosition(showPosition, showError);
            					}); 
            					modal.find('#cancel').bind('click', function(){
            						markLocationAsLoadedAndClose();
            					});
            					modal.on('hide.bs.modal', function (e) {
            						modal.find('#submit,#cancel').unbind("click");
            					});
        					}
        				}		
        			}
    			}
    		});
    		
    		function showPosition(position) {
    			setCookie("permission-location", "granted", 7);

    			var lat = position.coords.latitude, longit = position.coords.longitude;
    			$('[data-location="latitude"]').val(lat); 
    			$('[data-location="longitude"]').val(longit); 

    			$.ajax({ url:'https://maps.googleapis.com/maps/api/geocode/json?latlng='+lat+','+longit+'&sensor=true&key=<?= API_KEY_GEOCODE?>',
                     success: function(data){
                         console.log(data);
    	                 var country = "", region  = "", district = "", commune = "";
    	                 var addr_components = data.results[0].address_components;
    	                 for(comp of addr_components) {
    						if(comp.types.includes("country")) {
    							country = comp.long_name;
    						}
    						if(comp.types.includes("administrative_area_level_1")) {
    							region = comp.long_name;
    						}
    						if(comp.types.includes("postal_town")) {
    							district = comp.long_name;
    						}
    						if(district == "" && comp.types.includes("administrative_area_level_2")) {
								district = comp.long_name;
    						}
    						if(comp.types.includes("locality")) {
    							commune = comp.long_name;
    						}
    	                 }
    
    					 $('[data-location="country"]').val(country); 
    					 $('[data-location="region"]').val(region); 
    					 $('[data-location="district"]').val(district); 
    					 $('[data-location="commune"]').val(commune); 
                     }
    			});
    			
    			markLocationAsLoadedAndClose();
    		}
    		function showError(error) {
    			
    		    switch(error.code) {
    		        case error.PERMISSION_DENIED:
    		        	locationErrorMessage('<?= $t['geolocation_denied'] ?>');
    		        	//setCookie("permission-location", "denied", 7);
    		            break;
    		        case error.POSITION_UNAVAILABLE:
    		        	locationErrorMessage('<?= $t['position_not_found'] ?>');
    		            break;
    		        case error.TIMEOUT: 
    		        	locationErrorMessage('<?= $t['request_location_expired'] ?>');
    		            break;
    		        case error.UNKNOWN_ERROR:
    		        	locationErrorMessage("<? $t['unknown_error'] ?>");
    		            break;
    		    }
    		}
    		function markLocationAsLoadedAndClose() {
    			$('input[data-request-location]').attr("data-location-loaded", "loaded");
    			$('#content #modal-quest').modal("hide");
    		}
    		function locationErrorMessage(message) {
    			$('#content #modal-quest .modal-body').html(message);
            	$('#content #modal-quest #cancel').html("Ok");
            	$('#content #modal-quest #submit').hide();
    		}
		}
		// ----------------------------------------------------------------------

		<?php 
		if(isset($_GET['readonly'])) {?>
    		setCookie("readonly", "true", <?= LIFE_COOKIE_QUEST_PENDING ?>);
    		document.location='?read-only';
		<?php
		}
		?>

		function displayRowCells(elm) {
			var display = true;
			var type = elm.attr('type');
			var tagname = elm.prop("tagName");
			
			if(type == "checkbox") {
				display = elm.is(":checked"); 
			} else if(tagname == "TEXTAREA" || type == "number" || type == "text" || tagname == "SELECT") {
				display = elm.val().trim() != "";
			} 

			var elems = $('table tr[indexRow="'+elm.parents("td[trigger-display]").attr("trigger-display")+'"] td:not([trigger-display]) span.display-manager');

			if(display)
				elems.slideDown(450);
			else
				elems.slideUp(450);
		}

		$('#questionnaire').on( 'change keyup keydown paste cut', 'textarea', function (){
	        var diff = parseInt($(this).height(),10) - $(this).outerHeight();
		    $(this).css("overflow-y","hidden").height(0).height(Math.max(22, this.scrollHeight + diff)+"px");
		}).find( 'textarea' ).change();
		
		<?php if($logged) {?>
		if(getCookie("score-live") == "true") {
			$('#enabled-live-score').addClass("btn-success");
			$('[scored]:not([isInTable])').each(function(){
				processScore($(this));
			});
		}
		
		$('#enabled-live-score').click(function(){
			$(this).toggleClass("btn-success");
			if($(this).hasClass("btn-success")) {
				setCookie("score-live", "true", 1);
				if($('.score-live').length > 0) {
					$('.score-live').show();
				} else {
    				$('[scored]:not([isInTable])').each(function(){
    					processScore($(this));
    				});
				}
			} else {
				deleteCookie("score-live");
			}
			$('.score-live').toggle($(this).hasClass("btn-success"));
		});

		
		$('[scored]:not([isInTable])').change(function(){
    	    processScore($(this));
		});

		function processScore(input) {
			if(input.is('[result-define]')) {
				var group = input.parents(".form-group:first");
				
				$.post('pages/getScores.live.php', {
	    	    	aspectId : $('#aspect-header').attr("data-aspect-id"),
					numQuest: group.attr("numQuest"),
					answer: input.val()
	    	    }, function(html) {
	        	    input = $('[scored][result-required="'+input.attr("result-define")+'"]:checked');

	        	    if(!input.is('[type="radio"]') || (input.is('[type="radio"]') && input.is(":checked")) ) {
		    	    	getScore(input);
		        	}
	        	});
			} else {
				getScore(input);
			}
		}

		function getScore(input) {
			if(getCookie("score-live") == "true") {
        	    var group = input.parents(".form-group:first");
				var value = input.val();

				if(input.is("[multiple]") && value == "")
					value = " ";

				if(input.is('[type="radio"]') && !input.is(":checked"))
					value = "";
				
				if(value != "") {
            	    if(!input.is("[result-define]")) {
                	    if(group.find('.score').length == 0) {
                	    	group.append('<div class="score score-live py-0 pb-1 px-2 border mt-1 rounded bg-white"><img src="img/loader-score.svg"> Chargement du score...</div>');
                	    } else {
							group.find('.score').html('<img src="img/loader-score.svg"> Chargement du score...');
                	    }
            	    }
            	 	
        	    	$.post('pages/getScores.live.php', {
            	    	aspectId : $('#aspect-header').attr("data-aspect-id"),
    					numQuest: group.attr("numQuest"),
    					answer: value
            	    }, function(html) {
            	    	if(!input.is("[result-define]")) 
            	    		group.find('.score').html(html).show();
        	    		if(html.trim() == "")
            	    		group.find('.score').hide();
        		    });
				} else {
					group.find('.score').hide();
				}
    	    }
		}
	    <?php } ?>

		
		<?php } else { ?>
		deleteCookie('readonly');
		
		if(getCookie("version") == "") {
			$('#modalVersions').modal('show');
			$('#modalVersions .list-group .list-group-item').click(function(){
				$('#modalVersions .modal-footer').removeClass("d-none");
			});
			$('#modalVersions #save').click(function(){
				var value = $('#modalVersions').find('.list-group .list-group-item.active').attr("version");
				if(value != "undefined") {
					setCookie("version", value, <?= LIFE_COOKIE_VERSION ?>);
					$('.dropdown-toggle#dropdown-version').text(value);
					$('#modalVersions').modal('hide');
					$('#new-quest, .start-new-quest').removeClass("d-none");
				} else {
					$('.alert#version-empty').show();
				}
			});
		} else {
			$('#new-quest, .start-new-quest').removeClass("d-none");
		}
		
		var userLang = navigator.language || navigator.userLanguage; 
		if(userLang != "" && getCookie("lang") == "") {
			setCookie("lang", userLang, <?= LIFE_COOKIE_VERSION ?>);
		}
		
		$('#version.dropdown-menu .dropdown-item').click(function(){
			var previousLang = "";
			if(getCookie("lang") != "") {
				previousLang = getCookie("lang");
			}
			
			setCookie("version", $(this).attr("version"), <?= LIFE_COOKIE_VERSION ?>);
			setCookie("lang", $(this).attr("lang").toLowerCase(), <?= LIFE_COOKIE_VERSION ?>);

			$(this).parent().find(".active").removeClass("active");
			$(this).addClass("active");

			$('.dropdown-toggle#dropdown-version').text($(this).attr("version"));
			$('#new-quest').removeClass("d-none");

			if(previousLang != "" && $(this).attr("lang") != previousLang) {
				document.location = '';
			}
		});

		$('.dropdown[data-toggle="hover"]').hover(function() {
		  $(this).find('.dropdown-menu').stop(true, true).delay(80).fadeIn(100);
		  $(this).find(".dropdown-toggle").addClass("active");
		}, function() {
		  $(this).find('.dropdown-menu').stop(true, true).delay(80).fadeOut(100);
		  $(this).find(".dropdown-toggle").removeClass("active");
		});

		$('#lang.dropdown-menu .dropdown-item').click(function(){
			var previousLang = "";
			if(getCookie("lang") != "") {
				previousLang = getCookie("lang");
			}
			setCookie("lang", $(this).attr("lang").toLowerCase(), 365*3);
			if(previousLang != "" && $(this).attr("lang") != previousLang) {
				document.location = '';
			}
		});
		
		$('#new-quest, .start-new-quest').click(function(){
			var button = $('#new-quest, .start-new-quest');
			var initHTML = button.html();
			
			if(!button.is("[ready]")){
    			button.hide();
    			$('#dropdown-version').hide();
    			$.post('pages/instructions.php', {}, function(html) {
        			$('#main.container').slideUp("fast", function(){
        				$('#pre-quest-instructions.container').html(html).slideDown("fast", function(){
        					button.attr("ready","ready").before('<div style="display:none" id="cancel-quest" class="btn btn-info mr-1"><?= $t['cancel']?> <span class="oi oi-x ml-1"></span></div>');
        					button.removeClass("btn-light").addClass("btn-success").html('<?= $t['start']?> <span class="oi oi-power-standby ml-1"></span>').fadeIn();
        					$('#cancel-quest').fadeIn();
        					$('#navbarHome').on('click', '#cancel-quest', function(){
            					$(this).remove();
            					button.hide();
        						$('#pre-quest-instructions.container').fadeOut("fast", function(){
        		    				$('#main.container').fadeIn("fast", function(){
    									button.removeAttr("ready").removeClass("btn-success").addClass("btn-light").html(initHTML).fadeIn();
    									$('.start-new-quest').removeClass("btn-light").addClass("btn-primary");
    									$('#dropdown-version').fadeIn();
        		    				});
        						});
        					});
        				});
        			});
    			});
			} else {
				var version = getCookie("version");
				if(version == "") {
					bootbox.alert("<?= $t['alert_choose_quest_version'] ?>");
				} else {
					loading();
					
					initHTML = button.html();
					button.html('<?= $t['loading']?> <img src="img/loader-score.svg">');
					$.post('functions/getUniqueName.php', {}, function(html){
						var json = JSON.parse(html);
						if(json.filename != "error") {
							setCookie("filename", json.filename, <?= LIFE_COOKIE_QUEST_PENDING ?>);
							setCookie("indexAspect", "1", <?= LIFE_COOKIE_QUEST_PENDING ?>);
							setCookie("expirationQuest", <?= time() + 60*60*24*LIFE_COOKIE_QUEST_PENDING ?>, <?= LIFE_COOKIE_QUEST_PENDING ?>);

							if(getCookie('questsList') == "") {
								var jsonObj = {
										"list":[
											{
												"filename": json.filename,
												"version": version
											}
										],
										"expiry": <?= (time() + 60*60*24*LIFE_COOKIE_LIST_QUESTS) ?>
								};
								
							} else {
								var jsonObj = JSON.parse(getCookie('questsList'));
								jsonObj['list'].push({"filename":json.filename,"version":version});
							}
							setCookie('questsList', JSON.stringify(jsonObj), <?= LIFE_COOKIE_LIST_QUESTS ?>);

							document.location = '?start';
						} else {
							bootbox.alert("<?= $t['error_get_unique_name']?>");
						}
						//button.html(initHTML);
					});
					
				}
			}
		});
		

		$('#list-quest .quest-item div[data-action="edit"], #list-quest .quest-item .name').click(function(){
			var parent = $(this).parents(".quest-item");
			var filename = parent.attr("data-filename");
			var version = parent.attr("data-version");

			loading();
			
			$.get("<?= DIR_ANSWERS ?>/"+filename).done(function() { 
    			deleteCookie("expirationQuest");
    			setCookie("filename", filename, <?= LIFE_COOKIE_QUEST_PENDING ?>);
    			setCookie("version", version, <?= LIFE_COOKIE_VERSION ?>);
    			setCookie("indexAspect", "1", <?= LIFE_COOKIE_QUEST_PENDING ?>);
    			setCookie("readonly", "true", <?= LIFE_COOKIE_QUEST_PENDING ?>);
    			document.location = '?review';
		    }).fail(function() { 
		    	bootbox.hideAll();
		        bootbox.alert("Le fichier de questionnaire n'existe pas");
		    });
		});
		$('#list-quest .quest-item div[data-action="scores"]').click(function(){
			var parent = $(this).parents(".quest-item");
			var setData = parent.attr("data-setData");
			var filename = parent.attr("data-filename");
			loading();
			
			$.get("<?= DIR_ANSWERS ?>/"+filename).done(function() { 
				document.location = 'scores/data/'+setData;
		    }).fail(function() { 
		    	bootbox.hideAll();
		        bootbox.alert("Le fichier de questionnaire n'existe pas");
		    });
		});

		<?php
		if(count($newQuests) > 0) {
    		foreach($newQuests as $quest) {?>
    		if(getCookie('questsList') == "") {
				var jsonObj = {
						"list":[
							{
								"filename": "<?= $quest['filename']?>",
								"version": "<?= $quest['version']?>"
							}
						],
						"expiry": <?= (time() + 60*60*24*LIFE_COOKIE_LIST_QUESTS) ?>
				};
				
			} else {
				var jsonObj = JSON.parse(getCookie('questsList'));
				jsonObj['list'].push({"filename":"<?= $quest['filename']?>","version":"<?= $quest['version']?>"});
			}
			setCookie('questsList', JSON.stringify(jsonObj), <?= LIFE_COOKIE_LIST_QUESTS ?>);   
    		<?php 
    		} ?>

			document.location = '?restart';
        <?php 
		}
		?>

		$('#dropdown-version').dropdown();
		$('#dropdown-version').tooltip();
		
		if(getCookie("cookie_avert") == "") { // si le cookie n'existe pas
			var banner_text = '<div class="d-inline"><?= $t['cookies_message'] ?> <button class="btn btn-info btn-gradient btn-sm" onclick="window.open(\'https://cookiesandyou.com/\', \'_blank\')" id="info-cookie">Plus d\'infos</button></div> <button class="btn btn-success btn-gradient btn-sm" id="accept-cookie">Ok</button>';
			$("body").prepend('<div id="cookies-banner" class="clearfix fixed-bottom w-100 mb-0 alert alert-warning justify-content-between text-center">' + banner_text + '</div>');
			
			$("#accept-cookie").click(function(){
				setCookie("cookie_avert", "set", 365);
				$("#cookies-banner").slideUp(550);
			});
			
			setTimeout(function(){
				$("#cookies-banner").slideUp(550);
			}, 35000);
		}	
		<?php } ?>
		
		if(getCookie("device-opt") == "") {
			$('#unset-device-message').click(function(){
				setCookie("device-opt", "set", 365);
				$('#device-not-optimized').slideUp(550);
			});
		} else {
			$('#device-not-optimized').addClass("d-none");
		}	

		$('[data-toggle="tooltip"]').tooltip();

	});
	</script>
	
    <?php if(!isset($_COOKIE['indexAspect']) && !isset($_COOKIE['version'])) {?>
	<div class="modal" id="modalVersions" data-backdrop="static" role="dialog">
	  <div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="exampleModalLongTitle"><?= $t['choose_version'] ?></h5>
		  </div>
		  <div class="modal-body">
		  
			<div class="list-group">
			<?php
			$i = 0;
			$versionsByLang = getVersions();
			foreach($versionsByLang as $lang => $versions) {
				echo '<div class="text-white mb-1 rounded text-center bg-dark">'.$lang.'</div>';
				foreach($versions as $v) {
					$version = getVersionText($v); ?>
					<a version="<?= $v['file'] ?>" data-toggle="list" class="list-group-item list-group-item-action" href="#"><?= $version ?></a>
					<?php
				}
				if($i != count($versionsByLang)-1) {
					echo '<div class="mt-1"></div>';
				}
				$i++;
			}?>
			</div>
			
		  </div>
		  <div class="modal-footer d-none">
			<button type="button" class="btn btn-primary" id="save"><?= $t['save'] ?></button>
		  </div>
		</div>
	  </div>
	</div>
	<?php } ?>
	
	<?php if(!isset($_COOKIE['indexAspect'])) {?>
	<div id="device-not-optimized" class="fixed-bottom w-100 mb-0 alert alert-warning justify-content-between align-items-end">
		<div >
			<h6><?= $t['opt_alert_h6']?>.</h6>
			<?= $t['opt_alert_content']?>
		</div>
		<div>
			<button class="btn btn-success btn-gradient btn-sm" id="unset-device-message"><?= $t['understood']?></button>
		</div>
	</div>
	<?php } ?>
</body>
</html>