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
			  <h1 class="display-4">Bienvenue sur SHARP!</h1>
			  <p class="lead">Schéma Holistique pour l’Autoévaluation Paysanne de la Résilience climatique</p>
			  <hr class="my-4">
			  <p>Cet outil est conçu pour remplir les questionnaires plus simplement et plus rapidement.</p>
			  <p><a class="btn btn-primary btn-lg start-new-quest d-none" href="#" role="button">Nouveau questionnaire »</a></p>
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
		<?php } ?>
		
		<?php if(isset($_COOKIE['indexAspect'])) {?>
		$('a#quit').click(function(e){
			deleteCookie("indexAspect");
			document.location = '?quit';
			e.preventDefault();
		});
		
		$('#questionnaire #next, #questionnaire #prev').click(function(e){
			var isPrev = $(this).attr("id") == "prev";
			var toAdd = isPrev ? -1 : 1;
			var canUpdateCookie = true;
			
			if(!isPrev) {
			   $("#questionnaire form").find('[required]').each(function() {
				  if (!$(this).val() || $(this).val().length == 0 || $(this).val().length == null) { canUpdateCookie = false;  }
			   });
			}
			
			if(canUpdateCookie)
				setCookie("indexAspect", parseInt(getCookie("indexAspect")) + toAdd, 3);
			
			if(isPrev) {
				document.location = "?nav";
				e.preventDefault();
			}
		});
		
		
		function resizeAspectPanel() {
			var screenH = $(document).height() - $('nav').height() - 16;
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
				$("body").css("overflow","hidden");
				$('#aspects').css("display","flex").animate({right:0, opacity:1 }, 500, function(){
					if($('#aspects').scrollTop() == 0) {
						$('#aspects').animate({scrollTop: $("#aspects .card.cat-active").offset().top + 8 - ($("#aspects .card.cat-active").height()/2) }, 600);
					}
				});
				$('.modal-backdrop').fadeIn(0).bind("click", function(){
					$('#show-aspects').trigger("click");
				});
				$(window).bind("resize",resizeAspectPanel);
				$('#aspects .card:not(.cat-active)').bind('click', function(){
					setCookie("indexAspect", $(this).attr("data-index"), 3);
					document.location = '?nav';
				});
			}
			resizeAspectPanel();
			$(this).toggleClass("active btn-dark");
		});
		
		<?php } else { ?>
		if(getCookie("version") == "") {
			$('#modalVersions').modal('show');
			$('#modalVersions .list-group .list-group-item').click(function(){
				$('#modalVersions .modal-footer').removeClass("d-none");
			});
			$('#modalVersions #save').click(function(){
				var value = $('#modalVersions').find('.list-group .list-group-item.active').attr("version");
				if(value != "undefined") {
					setCookie("version", value, 90);
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
		$('#version.dropdown-menu .dropdown-item').click(function(){
			setCookie("version", $(this).attr("version"), 90);
			$('.dropdown-toggle#dropdown-version').text($(this).attr("version"));
			$('#new-quest').removeClass("d-none");
		});
		$('#new-quest, .start-new-quest').click(function(){
			var version = getCookie("version");
			if(version == "") {
				alert("Tu dois d'abord choisir une version !");
			} else {
				setCookie("indexAspect", "1", 3);
				$.post('getUniqueName.php', {}, function(html){ setCookie("filename", html, 3); });
				document.location = '?start';
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
			<h6>Application optimisée pour les tablettes et ordinateurs.</h6>
			La dimension de votre appareil ne nous permettra pas de vous fournir une expérience agréable lorsque vous remplirez un questionnaire.
		</div>
		<div>
			<button class="btn btn-success btn-gradient btn-sm" id="unset-device-message">J'ai compris</button>
		</div>
	</div>
	
</body>
</html>