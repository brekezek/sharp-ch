<?php
require_once("../required/common.php");
include_once getAbsolutePath().'required/db_connect.php';
include_once getAbsolutePath().'required/securite.fct.php';

sec_session_start();
$logged = login_check($mysqli);

/*
$_POST['data'] = "e6a0118eef8ebf48ac7732623239b07ddd6ca8d6.json";
$_POST['actionId'] = "delete-files";
$_POST['deleteParticipant'] = "true";
*/

if($logged) {
    if(isset($_POST['data'], $_POST['actionId'])) {
      
        // Traitement des input ----------------
        $actionId = $_POST['actionId'];
        $data = explode(",", $_POST['data']);
        if(isset($_POST['deleteParticipant'])) {
            $deleteParticipant = $_POST['deleteParticipant'];
        }
        // -------------------------------------
        
        if(is_array($data) && count($data) > 5) {
            echo 'Interdiction de supprimer plus plus de 5 éléments à la fois';
            exit();
        }
        
        if($actionId == "delete-files") {
            // Suppression des participants
            if($deleteParticipant == "true") {
                if($stmtP = $mysqli->prepare("DELETE FROM participants WHERE pid=(SELECT pid FROM questionnaires WHERE file=? LIMIT 1)")) {
                    foreach($data as $file) {
                        $stmtP->bind_param("s", $file);
                        $stmtP->execute();
                    }
                    $stmtP->close();
                }
                echo 'suppression des participants';
            }
            
            // Suppression des questionnaires dans la db
            if($stmt = $mysqli->prepare("DELETE FROM questionnaires WHERE file=?")) {
                foreach($data as $file) {
                    $stmt->bind_param("s", $file);
                    $stmt->execute();
                    
                    // Suppression physique des questionnaires
                    $filepath = getAbsolutePath().DIR_ANSWERS."/".$file;
                    if(file_exists($filepath)) {
                        unlink($filepath);
                    }
                }
                $stmt->close();
                $stmt = null;
                
                
            } else {
                echo $mysqli->error;
            } 
        }
        
        if($actionId == "participants") {
            $stmt = null;
            $qids = implode(",",$data);
            if($stmt = $mysqli->prepare("DELETE FROM participants WHERE pid=?")) {
                foreach($data as $qid) {
                    $stmt->bind_param("i", $qid);
                    $stmt->execute();
                }
                $stmt->close();
                echo $qids;
            } else {
                echo $stmt->error;
            }
        }
        
    }
} else {
    echo 'error';
}
?>