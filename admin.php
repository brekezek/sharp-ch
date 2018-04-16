<?php 
require_once 'required/common.php';
include_once 'required/db_connect.php';
include_once 'required/securite.fct.php';
includeDependencies();


sec_session_start();

if(login_check($mysqli)) {
    $uri = "";
    if(!is_array($_SERVER['QUERY_STRING'])) {
        $uri = "?".$_SERVER['QUERY_STRING'];
    }
    header('Location: admin.dashboard.php'.$uri);
} else {

    function alert($txt) {
        echo '<div class="alert alert-danger" role="alert">'.$txt.'</div>';
    }
    function success($txt) {
        echo '<div class="alert alert-success" role="alert">'.$txt.'</div>';
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="author" content="Dominique Roduit">

	<!-- Fonts -->
	<link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
	
	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
	<link href="open-iconic/font/css/open-iconic-bootstrap.min.css" rel="stylesheet">
	<link href="css/signin.css" rel="stylesheet">
	
	<!-- Custom -->
	<link rel="stylesheet" href="css/style.css">
	<script type="text/JavaScript" src="js/sha256.js"></script>
	
	<title>SHARP-CH</title>
	<link rel="icon" href="img/favicon.png">
</head>
<body class="text-center">
	
	
    <form class="form-signin" action="required/process_login.php" method="post" name="login_form">
        <img class="mb-4" src="img/logo1.jpg" alt="" height="72">
        
        <?php
        //print_r($_COOKIE);
        if(isset($_GET['logout'])) { success($t['deconnecte']); }
        if(isset($_GET['err'])) {
            $txt = "";
            $err = filter_input(INPUT_GET, 'err', $filter = FILTER_SANITIZE_STRING);
            switch($err) {
                case 1: $txt = "N'a pas pu initier une session sécurisée (ini_set)"; break;
                case 2: $txt = "Requête invalide"; break;
                case 3: $txt = "Mot de passe erroné"; break;
                case 4: $txt = "Mot de passe faux 5x de suite. Vérouillé pour 10 minutes"; break;
                case 5: $txt = "L'utilisateur n'existe pas"; break;
                case 6: $txt = ""; break;
                case 7: $txt = "E-mail malformé"; break;
            }
            if($txt != "") alert($txt);
        }
        ?>

        <h1 class="h3 mb-3 font-weight-normal"></h1>
        
        <label for="inputEmail" class="sr-only"><?= $t['email']?></label>
        <input type="email" id="inputEmail" class="form-control" placeholder="<?= $t['email']?>" required autofocus name="email">
        
        <label for="inputPassword" class="sr-only"><?= $t['password']?></label>
        <input type="password" id="inputPassword" class="form-control" placeholder="<?= $t['password']?>" required name="password">
        
        <div class="checkbox mb-3">
        <label>
    		<input type="checkbox" name="remember" value="1" checked> <?= $t['remember-me']?>
        </label>
        </div>
        
        <button class="btn btn-lg btn-primary btn-block" type="submit"><?= $t['connexion']?></button>
        <p class="mt-5 mb-3 text-muted">© 2018</p>
    </form>

	<!-- Bootstrap & JQuery -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
	<script src="js/cookie.js"></script>
	
	
	<script>
	$(function(){
    	$('form[name="login_form"]').submit(function(){
    		var pass_elm = $(this).find('[name="password"]');
    		pass_elm.attr("name", "fakepass");
    		pass_elm.after('<input type="hidden" name="password" value="">');
    		$('input[name="password"]').val(hex_sha512(pass_elm.val()));
			var fakestr = "*".repeat(pass_elm.val().length);	
    		pass_elm.val(fakestr);
    	});
    });
	</script>
	

</body>
</html>
<?php } ?>