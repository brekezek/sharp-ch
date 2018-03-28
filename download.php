<?php
require_once 'required/common.php';
include_once 'required/db_connect.php';
include_once 'required/securite.fct.php';
includeDependencies();

sec_session_start();

if(!login_check($mysqli)) {
    header('Location: admin.php');
}

// grab the requested file's name
$file_name = $_GET['file'];

// make sure it's a file before doing anything!
if( !is_file($file_name) )
	exit();

// required for IE
if(ini_get('zlib.output_compression')) 
	ini_set('zlib.output_compression', 'Off');	

// get the file mime type using the file extension
$ext = strtolower(substr(strrchr($file_name,'.'),1));
switch($ext) {
    case 'json': $mime = 'application/json'; break;
	case 'pdf': $mime = 'application/pdf'; break;
	case 'zip': $mime = 'application/zip'; break;
	case 'jpeg':
	case 'jpg': $mime = 'image/jpg'; break;
	default: exit();
}

$name = basename($file_name);
if(isset($_GET['name']) && !empty($_GET['name']))
    $name = preg_replace( '/[^a-z0-9_]+/', '-', strtolower( $_GET['name'] ) ).".".$ext;

header('Pragma: public'); 	// required
header('Expires: 0');		// no cache
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Last-Modified: '.gmdate ('D, d M Y H:i:s', filemtime ($file_name)).' GMT');
header('Cache-Control: private',false);
header('Content-Type: '.$mime);
header('Content-Disposition: attachment; filename="'.$name.'"');
header('Content-Transfer-Encoding: binary');
header('Content-Length: '.filesize($file_name));	// provide file size
header('Connection: close');
readfile($file_name);		// push it out
exit();