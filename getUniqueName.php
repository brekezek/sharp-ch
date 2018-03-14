<?php 
$res = array();
if(isset($_COOKIE['version'])) {
	$res['filename'] = sha1(uniqid().$_COOKIE['version']).".json";
} else {
	$res['filename'] = "error"; 
}
echo json_encode($res);
?>