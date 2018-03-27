<?php
include_once 'db_connect.php';
include_once 'securite.fct.php';
 
sec_session_start(); // Notre façon personnalisée de démarrer la session PHP
 
if (isset($_POST['email'], $_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password']; // Le mot de passe hashé.
    $remember = (isset($_POST['remember']) && $_POST['remember'] == "1");
    
    $loginRet = login($email, $password, $mysqli, $remember);
    if ($loginRet == 0) {  // Connecté 
        header('Location: ../admin.php');
    } else { // Pas connecté 
        header('Location: ../admin.php?err='.intval($loginRet));
    }
} else {
    // Les variables POST correctes n’ont pas été envoyées à cette page
    header('Location: ../admin.php?err=2');
}