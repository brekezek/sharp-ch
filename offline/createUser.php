<?php 
include_once '../required/common.php';
include_once '../required/securite.fct.php';
include_once '../required/db_connect.php';
/*
$name = "Dominique Roduit";
$email = "dominique@roduit.com";
$password = hash('sha512', "poi10yxcKolat10s");
*/

$name = "";
$email = "";

if(isset($_POST['name'], $_POST['email'], $_POST['password'])) {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);
    
    $error_msg = '';
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg .= '<p class="error">L’adresse email que vous avez entrée n’est pas valide</p>';
    }
    
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    if (strlen($password) != 128) {
        $error_msg .= '<p class="error">Mot de passe invalide.</p>';
    }
    
    $prep_stmt = "SELECT uid FROM users WHERE email = ? LIMIT 1";
    $stmt = $mysqli->prepare($prep_stmt);
    
    if ($stmt) {
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows == 1) {
            $error_msg .= '<p class="error">Il existe déjà un utilisateur enregistré avec cette adresse e-mail.</p>';
        }
    } else {
        $error_msg .= '<p class="error">Erreur de base de données</p>';
    }

    if (empty($error_msg)) {
        // Crée un salt au hasard
        $random_salt = hash('sha512', uniqid(openssl_random_pseudo_bytes(16), TRUE));
        
        $password = hash_fct($password, $random_salt);
        
        if ($insert_stmt = $mysqli->prepare("INSERT INTO users (name, email, password, salt) VALUES (?, ?, ?, ?)")) {
        	$insert_stmt->bind_param('ssss', $name, $email, $password, $random_salt);
        	if (! $insert_stmt->execute()) {
        	    $error_msg = 'Registration failure: INSERT';
        	} else {
        	    header('Location: '.getBase().'admin');
        	}
        }
    }
}?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>SHARP - Inscription</title>
		
		<meta charset="UTF-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1">
    	
    	<base href="<?= ($_SERVER['SERVER_NAME'] == "localhost") ? "/sharp-site/" : getBase() ?>offline/">
		
    	<!-- Bootstrap CSS -->
    	<link rel="stylesheet" href="../css/bootstrap.min.css">
    	<link href="../open-iconic/font/css/open-iconic-bootstrap.min.css" rel="stylesheet">
    	<link href="../css/signin.css" rel="stylesheet">
    	
    	<script src="../js/jquery.min.js"></script>
    	<script type="text/JavaScript" src="../js/sha256.js"></script>
    	
    	<style>
    	.form-signin input[type="text"] { border-radius: .25rem .25rem 0 0; border-bottom: none; }
    	.form-signin input[type="email"] { border-radius: 0; border-bottom: 0; }
    	.form-signin input[name="password"] { border-radius: 0; margin-bottom: 0; border-bottom:0; }
    	.form-signin input[type="password"]:last { border-radius: 0 0 .25rem .25rem; }
    	</style>
    	
    	<script>
		$(function(){
			$('#inputConfirmation, #inputPassword').keyup(function(){
				if($('#inputPassword').val().trim().length > 3 && $('#inputConfirmation').val() == $('#inputPassword').val()) {
					$('button[type="submit"]').addClass("btn-success").removeAttr("disabled");
				} else {
					$('button[type="submit"]').addClass("btn-primary").attr("disabled", "disabled");
				}
			});

			$('form[name="registration_form"]').submit(function(){
	    		var pass_elm = $(this).find('[name="password"]');
	    		pass_elm.attr("name", "fakepass");
	    		pass_elm.after('<input type="hidden" name="password" value="">');
	    		$('input[name="password"]').val(hex_sha512(pass_elm.val()));
				var fakestr = "*".repeat(pass_elm.val().length);	
	    		pass_elm.val(fakestr);
	    	});
		});
    	</script>
    </head>
    <body class="text-center">
    	
        <form class="form-signin" action="<?= $_SERVER['REQUEST_URI']?>" method="post" name="registration_form">
        	<img class="mb-4" src="../img/logo2.jpg" alt="" height="72">
        	
        	<h1 class="h3 mb-3 font-weight-normal">Enregistrement</h1>
        	
        	<?php 
        	if (!empty($error_msg)) {
        	    echo '<div class="alert alert-danger">'.$error_msg.'</div>';
        	}
        	?>
        
            <label for="inputEmail" class="sr-only">Prénom Nom</label>
            <input type="text" id="inputName" class="form-control" placeholder="Prénom Nom" value="<?= $name ?>" required autofocus name="name">
            
            <label for="inputEmail" class="sr-only">Adresse email</label>
            <input type="email" id="inputEmail" class="form-control" placeholder="Adresse e-mail" value="<?= $email ?>" required name="email">
            
            <label for="inputPassword" class="sr-only">Mot de passe</label>
            <input type="password" id="inputPassword" class="form-control" placeholder="Mot de passe" required name="password">
            
            <label for="inputPassword" class="sr-only">Confirmez le mot de passe</label>
            <input type="password" id="inputConfirmation" class="form-control" placeholder="Confirmation" required name="confirmpwd">
            
            
            <button class="btn btn-lg btn-primary btn-block" type="submit" disabled>S’enregistrer</button>
            
            <p class="mt-5 mb-3 text-muted">© 2018</p>
        </form>
    </body>
    
</html>