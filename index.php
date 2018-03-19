<!DOCTYPE html>
<?php 
$reset = false;
$debug = false;
require_once('required/common.php');
includeDependencies();
?>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

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
		include_once('questionnaire.php');
	} else {?>
		<div class="jumbotron">
			<div class="container">
			  <h1 class="display-4"><?= $t['welcome_msg_h1'] ?>!</h1>
			  <p class="lead"><?= $t['sharp_meaning'] ?></p>
			  <hr class="my-4">
			  <p><?= $t['msg1_welcome'] ?></p>
			  <p><a class="btn btn-primary btn-lg start-new-quest d-none" href="#" role="button"><?= $t['new_questionnaire']?> Â»</a></p>
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
			var screenH = $(document).outerHeight(true) - $('nav').outerHeight(true);
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
				$('#aspects').css({display:"flex", overflowY:"auto"}).animate({right:0, opacity:1 }, 500, function(){
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
    			var elem = $(this).find('label[type="checkbox"]');
    			elem.trigger("click");
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
    			var elem = $(this).find('label[type="radio"]');
    			elem.trigger("click");
			}
		});

		// Tables "other" fields
		/*
		$('table tbody tr[other] td').on('click', function(){
			var row = $(this).parent().parent().parent("table").find('tr[other-field]');
			row.show();
		});
		*/
		
		<?php } else { ?>
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
			setCookie("version", $(this).attr("version"), <?= LIFE_COOKIE_VERSION ?>);
			setCookie("lang", $(this).attr("lang"), <?= LIFE_COOKIE_VERSION ?>);

			$(this).parent().find(".active").removeClass("active");
			$(this).addClass("active");

			$('.dropdown-toggle#dropdown-version').text($(this).attr("version"));
			$('#new-quest').removeClass("d-none");
		});
		
		$('#new-quest, .start-new-quest').click(function(){
			var version = getCookie("version");
			if(version == "") {
				alert("<?= $t['alert_choose_quest_version'] ?>");
			} else {
				
				$.post('getUniqueName.php', {}, function(html){
					var json = JSON.parse(html);
					if(json.filename != "error") {
						setCookie("filename", json.filename, <?= LIFE_COOKIE_QUEST_PENDING ?>);
						setCookie("indexAspect", "1", <?= LIFE_COOKIE_QUEST_PENDING ?>);
						document.location = '?start';
					} else {
						alert("Erreur : le nom de fichier unique n'a pas pu être distribué. Réessayez et si le problème persiste, veuillez nous le signaler.");
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