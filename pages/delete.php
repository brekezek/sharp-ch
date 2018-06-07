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
        $definitive = false;
        if(isset($_POST['definitive']) && $_POST['definitive'] == 1) {
            $definitive = true;
        }
        $deleteFlag = 1;
        if(isset($_POST['recover'])) { $deleteFlag = 0; }
        // -------------------------------------
        
        if(is_array($data) && count($data) > 5 && $deleteFlag == 1) {
            echo 'Interdiction de supprimer plus de 5 éléments à la fois';
            exit();
        }
        
        if($actionId == "delete-files") { // Suppression d'un questionnaire et eventuellement du participant lié
            // Suppression des participants
            if($deleteParticipant == "true") {
               
                $sql = "UPDATE participants SET deleted=".$deleteFlag." WHERE pid=(SELECT pid FROM questionnaires WHERE file=? LIMIT 1)";
                if($definitive) {
                    $sql = "DELETE FROM participants WHERE pid=(SELECT pid FROM questionnaires WHERE file=? LIMIT 1)";
                }
                if($stmtP = $mysqli->prepare($sql)) {
                    foreach($data as $file) {
                        $stmtP->bind_param("s", $file);
                        $stmtP->execute();
                    }
                    $stmtP->close();
                }
                echo 'suppression des participants';
            }
            
            // Suppression des questionnaires dans la db
            $sql = "UPDATE questionnaires SET deleted=".$deleteFlag." WHERE file=?";
            if($definitive) {
                $sql = "DELETE FROM questionnaires WHERE file=?";
                $sqlScores = "DELETE FROM scores WHERE qid=(SELECT qid FROM questionnaires WHERE file='".$file."' LIMIT 1)";
            }
            if($stmt = $mysqli->prepare($sql)) {
                foreach($data as $file) {
                    $stmt->bind_param("s", $file);
                    $stmt->execute();
                    
                    // Suppression physique des questionnaires
                    if($definitive) {
                        $filepath = getAbsolutePath().DIR_ANSWERS."/".$file;
                        if(file_exists($filepath)) {
                            unlink($filepath);
                        }
                    }
                }
                $stmt->close();
                $stmt = null;
                
                if($definitive) {
                    $mysqli->query($sqlScores);
                }
                
            } else {
                echo $mysqli->error;
            } 
        }
        
        if($actionId == "participants") {
            $stmt = null;
            $qids = implode(",",$data);
            $sql = "UPDATE participants SET deleted=".$deleteFlag." WHERE pid=?";
            if($definitive) {
                $sql = "DELETE FROM participants WHERE pid=?";
            }
            if($stmt = $mysqli->prepare($sql)) {
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