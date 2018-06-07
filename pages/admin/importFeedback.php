<?php
require_once '../../required/common.php';
require_once '../../required/db_connect.php';

$b64 = $_POST['b64'];
$content = base64_decode($b64);
$filename = $_POST['filename'];
$version = $_POST['version'];
$langFile = strtolower(substr($version, strlen($version)-2, 2));

$path = getAbsolutePath().DIR_ANSWERS."/android/".$langFile;

if(file_exists($path)) {
    $filepath = $path."/".$filename;
    $filenameDB = "android/".$langFile."/".$filename;
} else {
    $filepath = getAbsolutePath().DIR_ANSWERS."/android/".$filename;
    $filenameDB = "android/".$filename;
}

if(!file_exists($filepath)) {
    file_put_contents($filepath, $content);
    
    $json = json_decode($content, true); 
    
    $prenom = optInfoAdm($json, 3);
    $nom = optInfoAdm($json, 2);
    $collecte_par = optInfoAdm($json, 1);
    $region = optInfoAdm($json, 9); 
    $commune = optInfoAdm($json, 10);
    
    $creation_date = date ("Y-m-d H:i:s", $_POST['creation_date']);
    
    
    if($stmt = $mysqli->prepare("INSERT INTO participants (firstname, lastname, region, commune) VALUES (?,?,?,?)")) {
        $stmt->bind_param("ssss", $prenom, $nom, $region, $commune);
        $stmt->execute();
        $pid = $stmt->insert_id;
        $stmt->close();
        
        if($pid != null) {
            if($stmt2 = $mysqli->prepare("INSERT INTO questionnaires (pid, collecte_par, version, creation_date, file) VALUES (?,?,?,?,?)")) {
                $stmt2->bind_param("issss", $pid, $collecte_par, $version, $creation_date, $filenameDB);
                $stmt2->execute();
                $stmt2->close();
                
                echo 'Importé avec succès';
            } else {
                echo "Erreur: le questionnaire n'a pas pu être enregistré en base de données. Rééssayer!";
            }
        } 
    } else {
        echo "Erreur: le participants n'a pas pu être enregistré en base de données. Rééssayer!";
    }
    
} else {
    echo 'Error: le fichier existe déjà sur le serveur.';
}
?>