<?php 
include_once '../required/securite.fct.php';
include_once '../required/db_connect.php';

$name = "Dominique Roduit";
$email = "dominique@roduit.com";
$password = hash('sha512', "poi10yxcKolat10s");


// Cr�e un salt au hasard
$random_salt = hash('sha512', uniqid(openssl_random_pseudo_bytes(16), TRUE));

// Cr�e le mot de passe en se servant du salt g�n�r� ci-dessus 
$password = hash_fct($password, $random_salt);

// Enregistre le nouvel utilisateur dans la base de donn�es
if ($insert_stmt = $mysqli->prepare("INSERT INTO users (name, email, password, salt) VALUES (?, ?, ?, ?)")) {
	$insert_stmt->bind_param('ssss', $name, $email, $password, $random_salt);
	// Ex�cute la d�claration.
	if (! $insert_stmt->execute()) {
		header('Location: ../error.php?err=Registration failure: INSERT');
	}
}
?>