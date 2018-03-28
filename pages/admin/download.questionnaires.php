<?php
include_once '../../required/const.php';
include_once '../../required/db_connect.php';
include_once '../../required/securite.fct.php';


sec_session_start();
$logged = login_check($mysqli);

if(!$logged) {
    echo 'Not logged in';
}

if(isset($_POST['f'])) {
    $files = explode(",",$_POST['f']);
    
    $file_list = array();
    foreach($files as $file) {
        $filepath = "../../".DIR_ANSWERS."/".$file;
        
        if(!empty($file)) {
            $file_list[] = $filepath;
        }
    }

    if(!create_zip($file_list, "questionnaires.zip", true)) {
        echo 'Error in creating the archive file';
    } else {
        echo 'ok';
    }
} else {
    echo 'Bad parameters';
}

function create_zip($files = array(), $destination = '', $overwrite = false) {
    //if the zip file already exists and overwrite is false, return false
    if(file_exists($destination) && !$overwrite) {
        return false;
    }
    //vars
    $valid_files = array();
    //if files were passed in...
    if(is_array($files)) {
        //cycle through each file
        foreach($files as $file) {
            $split = explode(":", $file);
            $filename = $split[0];
            //make sure the file exists
            if(file_exists($filename)) {
                $valid_files[] = $file;
            }
        }
    }
    
    //if we have good files...
    if(count($valid_files)) {
        //create the archive
        $zip = new ZipArchive();
        if($zip->open($destination, $overwrite && file_exists($destination) ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
            return false;
        }
        //add the files
        foreach($valid_files as $file) {
            $split = explode(":", $file);
            $filename = $split[0];
            $name = $filename;
            if(isset($split[1]) && !empty($split[1]))
                $name = $split[1].".json";
            
            $zip->addFile($filename, str_replace("../../".DIR_ANSWERS."/", "", $name));
        }

        //close the zip -- done!
        $zip->close();
        
        //check to make sure the file exists
        return file_exists($destination);
    }

    return false;
} 
?>