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

	<!-- Fonts -->
	<link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
	
	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
	<link href="open-iconic/font/css/open-iconic-bootstrap.min.css" rel="stylesheet">
	
	<!-- Custom -->
	<link rel="stylesheet" href="css/style.css">
	
	<title>SHARP-CH</title>
	<link rel="shortcut icon" href="img/favicon.png">
</head>
<body>
	
	<?php
	include_once('menu.php');
	?>
	
	<div id="content" class="position-relative" style="margin-top: 56px;">
	<?php
	if(isset($_COOKIE['indexAspect'])) {
		include_once('pages/questionnaire.php');
	} else {
	    if($logged) {
	        if(isset($_SESSION['resultsDefined'])) {
	          unset($_SESSION['resultsDefined']);  
	        }
	    }
	    ?>
		<div class="jumbotron">
			<div class="container">
			  <div class="text-center">
    			  <img src="img/logo_round.jpg" class="mb-3" width="220">
    			  <h1 class="display-4 d-none"><?= $t['welcome_msg_h1'] ?> <span class="badge badge-light">CH</span></h1>
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
			</div>
		</div>
	<?php 
	}
	?>
	</div>
	
	<!-- Bootstrap & JQuery -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
	<script src="js/cookie.js"></script>

	
	<script>
	$(function(){
		<?php if($reset) { ?>
			deleteCookie('device-opt');
			deleteCookie('cookie_avert');
			deleteCookie("indexAspect");
			deleteCookie("version");
			deleteCookie('readonly');
		<?php }
		
		if(isset($_REQUEST['end'])) {?>
			deleteCookie("indexAspect");
		<?php } ?>
		
		<?php if(isset($_COOKIE['indexAspect'])) {?>
		function goToAspect(index) {
			setCookie("indexAspect", index, <?= LIFE_COOKIE_QUEST_PENDING ?>);
			//alert("index="+index);
			//alert("indexAspect="+getCookie("indexAspect"));
		}
		
		$('a#quit').click(function(e){
			deleteCookie("indexAspect");
			document.location = '?quit';
			e.preventDefault();
		});
		
		$('#questionnaire').find('#next, #prev').click(function(e){
			var isPrev = $(this).attr("id") == "prev";
			var canUpdateCookie = true;
			
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
					goToAspect(parseInt($(this).attr("data-index")));
					$("#questionnaire form").find('[required]').removeAttr("required");
					$('#questionnaire #submitHidden').trigger("click");
				});
				resizeAspectPanel();
			}
			
			$(this).toggleClass("active btn-dark");
		});

		$('#quest-progress .item[data-index]').click(function(){
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

		/*
		$('#refresh-scores').click(function(){
	    	$("#questionnaire form").attr("action", "?refreshContent").find('[required]').removeAttr("required");
			$('#questionnaire #submitHidden').trigger("click");
	    });
		*/
		
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
		
		$('#new-quest, .start-new-quest').click(function(){
			var version = getCookie("version");
			if(version == "") {
				alert("<?= $t['alert_choose_quest_version'] ?>");
			} else {
				
				$.post('functions/getUniqueName.php', {}, function(html){
					var json = JSON.parse(html);
					if(json.filename != "error") {
						setCookie("filename", json.filename, <?= LIFE_COOKIE_QUEST_PENDING ?>);
						setCookie("indexAspect", "1", <?= LIFE_COOKIE_QUEST_PENDING ?>);
						document.location = '?start';
					} else {
						alert("Erreur : le nom de fichier unique n'a pas pu �tre distribu�. R�essayez et si le probl�me persiste, veuillez nous le signaler.");
					}
					
				});
				
			}
		});
		
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
	});
	</script>
	

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
	
	<div id="device-not-optimized" class="fixed-bottom w-100 mb-0 alert alert-warning justify-content-between align-items-end">
		<div >
			<h6><?= $t['opt_alert_h6']?>.</h6>
			<?= $t['opt_alert_content']?>
		</div>
		<div>
			<button class="btn btn-success btn-gradient btn-sm" id="unset-device-message"><?= $t['understood']?></button>
		</div>
	</div>
	
</body>
</html>