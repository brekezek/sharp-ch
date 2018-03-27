<?php
function sec_session_start() {
    $session_name = 'sec_session_id';
   
    // Force la session à n’utiliser que les cookies
    if (ini_set('session.use_only_cookies', 1) === FALSE) {
        header("Location: ../admin.php?err=1");
        exit();
    }
    
    // Récupère les paramètres actuels de cookies
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params(
        $cookieParams["lifetime"],
        $cookieParams["path"],
        $cookieParams["domain"],
        $https = false,
        $httponly = true  // Empêche Javascript d’accéder à l’id de session
    );
    
    session_name($session_name); // Donne à la session le nom configuré plus haut
    session_start();            
    session_regenerate_id(); // Génère une nouvelle session et efface la précédente
}

function login($email, $password, $mysqli, $persistent = false) {
    if(filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        return 7;
    } else {
        if ($stmt = $mysqli->prepare("SELECT uid, name, password, salt FROM users WHERE email = ? LIMIT 1")) { // Empêche les injections SQL
            $stmt->bind_param('s', $email); 
            $stmt->execute();    
            $stmt->store_result();
      
            $stmt->bind_result($user_id, $name, $db_password, $salt);
            $stmt->fetch();
            
            $password = hash_fct($password, $salt);
            if ($stmt->num_rows == 1) {
                // L’utilisateur existe, on vérifie qu’il n’est pas verrouillé à cause d’essais de connexion trop répétés 
                if (checkbrute($user_id, $mysqli) == true) { // Le compte est verrouillé
                    return 4;
                } else {
                    if ($db_password == $password) { // Le mot de passe est correct!
                        
                        $_SESSION['user_id'] = preg_replace("/[^0-9]+/", "", $user_id); // Protection XSS  
                        $_SESSION['name'] = preg_replace("/[^a-zA-Z0-9_\- ]+/", "", $name); // Protection XSS 
                        $_SESSION['login_string'] = hash_fct($password, $_SERVER['HTTP_USER_AGENT']);
                        
                        // Création d'une connexion persistente
                        if($persistent) {
                            $cookieParams = session_get_cookie_params();
                            setcookie("sec_session_persist",
                                $email.":".hash('sha256', $_SESSION['login_string'].$_SESSION['user_id']),
                                time() + SESSION_PERSIST_DAYS * 60 * 60 * 24,
                                $cookieParams["path"],
                                $cookieParams["domain"],
                                $https = false,
                                $httponly = true);
                        }
                        // Ouverture de session réussie.
                        return 0;
                    } else {
                        // Le mot de passe n’est pas correct: On enregistrons cet essai dans la base de données
                        $now = time();
                        $mysqli->query("INSERT INTO login_attempts(uid, time) VALUES ('$user_id', '$now')");
                        return 3;
                    }
                }
            } else {
                // L’utilisateur n’existe pas.
                return 5;
            }
        }
    }
}

function checkbrute($user_id, $mysqli) {
    // On veut récupérer les essais de connexion des 10 dernières minutes
    $valid_attempts = time() - (10 * 60);
    
    if ($stmt = $mysqli->prepare("SELECT time FROM login_attempts WHERE uid = ? AND time > '$valid_attempts'")) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->store_result();
           
        return ($stmt->num_rows > 5); // true si plus de 5 essais de connexion
    }
}

function login_check($mysqli) {
    // Vérifie que toutes les variables de session existent
    if (isset($_SESSION['user_id'], $_SESSION['name'], $_SESSION['login_string'])) {
            
            $user_id = $_SESSION['user_id'];
            $login_string = $_SESSION['login_string'];
            $name = $_SESSION['name'];
            
            $user_browser = $_SERVER['HTTP_USER_AGENT'];
            
            if ($stmt = $mysqli->prepare("SELECT password FROM users WHERE uid = ? LIMIT 1")) {
                // Lie "$user_id" aux paramètres.
                $stmt->bind_param('i', $user_id);
                $stmt->execute();   // Exécute la déclaration.
                $stmt->store_result();
                
                if ($stmt->num_rows == 1) { // L'utilisateur existe
                    $stmt->bind_result($password);
                    $stmt->fetch();
                    $login_check = hash_fct($password, $user_browser);
                    
                    return ($login_check == $login_string); // on est connecté si true
                } else {
                    // Pas connecté
                    return false;
                }
            } else {
                // Pas connecté
                return false;
            }
        } else {
            // Si une session persistente (cookie) est enregistré
            if(isset($_COOKIE['sec_session_persist'])) {
                $parts = explode(":", $_COOKIE['sec_session_persist']);
                if(count($parts) == 2) {
                    $email = $parts[0];
                    $securite = $parts[1];
                    
                    if ($stmt = $mysqli->prepare("SELECT uid, name, password FROM users WHERE email = ? LIMIT 1")) {
                        $stmt->bind_param('s', $email);  
                        $stmt->execute();    
                        $stmt->store_result();
                        
                        if ($stmt->num_rows == 1) {
                            $stmt->bind_result($user_id, $name, $password);
                            $stmt->fetch();
                            
                            $login_string = hash_fct($password, $_SERVER['HTTP_USER_AGENT']);
                            $user_id = preg_replace("/[^0-9]+/", "", $user_id);
                            
                            $login_check = hash('sha256', $login_string.$user_id);
                            if($securite == $login_check) { // On créé les variables de session pour ignorer le cookie ensuite
                                $_SESSION['user_id'] = $user_id;
                                $_SESSION['name'] = preg_replace("/[^a-zA-Z0-9_\- ]+/", "", $name);
                                $_SESSION['login_string'] = hash_fct($password, $_SERVER['HTTP_USER_AGENT']);
                                
                                return true;
                            }
                        }
                    }
                }
            }
            return false; 
        }
}

/*
function esc_url($url) {
    
    if ('' == $url) return $url;
   
    $url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url);
    
    $strip = array('%0d', '%0a', '%0D', '%0A');
    $url = (string) $url;
    
    $count = 1;
    while ($count) {
        $url = str_replace($strip, '', $url, $count);
    }
    
    $url = str_replace(';//', '://', $url);
    $url = htmlentities($url);
    $url = str_replace('&amp;', '&#038;', $url);
    $url = str_replace("'", '&#039;', $url);
    
    if ($url[0] !== '/') {
        return '';
    } else {
        return $url;
    }
}
*/

function hash_fct($plain, $salt) {
    $cipher = $plain;
    $ciphers = array();
    for($i = 0; $i < 5; $i++) { 
        $ciphers[] = hash('sha512', $i == 0 ? $cipher : $cipher[$i-1] . $salt);
    }
    $sum_cipher = hash('sha512', $salt . implode("!".$salt, $ciphers));
    return $sum_cipher;
}
?>